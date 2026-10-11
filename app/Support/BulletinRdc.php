<?php

namespace App\Support;

use App\Models\AffectationCours;
use App\Models\Inscription;
use App\Models\Note;
use App\Models\ParametreSysteme;
use App\Models\Periode;

/**
 * Calcul du bulletin annuel selon la norme congolaise (RDC — CTEB).
 *
 * Règles retenues :
 *  - Pour une branche et une période, les notes se répartissent en
 *    « Travaux journaliers » (interrogations / devoirs) et « Examen » ;
 *    le total obtenu est TJ + Examen, le maximum est la somme des notes
 *    maximales des évaluations notées de la période.
 *  - Le pourcentage d'une branche est le rapport total obtenu / total maximum,
 *    exprimé en pourcentage ; le pourcentage général est le rapport des sommes
 *    de tous les totaux sur tous les maxima (et non la moyenne des pourcentages).
 *  - L'élève passe à partir de 50 % (seuil de réussite) ; en dessous, il double,
 *    et les branches sous le seuil sont listées pour l'examen de repêchage.
 */
class BulletinRdc
{
    /** Seuil appliqué si l'administration n'en a configuré aucun. */
    public const SEUIL_REUSSITE = 50.0;
 
    public function __construct(
        private readonly Inscription $inscription,
        private readonly ?float $seuilReussite = null,
    ) {
    }
 
    /**
     * @param  float|null  $seuilReussite  Seuil de réussite à appliquer ; par défaut celui
     *                                     configuré dans les paramètres du système (50 %).
     */
    public static function for(Inscription $inscription, ?float $seuilReussite = null): self
    {
        return new self($inscription, $seuilReussite);
    }
 
    /** Seuil de réussite effectif (paramètre du système si aucun seuil n'est imposé). */
    public function seuil(): float
    {
        return $this->seuilReussite ?? ParametreSysteme::seuilReussiteBulletin();
    }

    /**
     * @return array{
     *     inscription: Inscription,
     *     periodes: \Illuminate\Support\Collection<int, Periode>,
     *     branches: array<int, array<string, mixed>>,
     *     total_general: float,
     *     max_general: float,
     *     pourcentage_general: float,
     *     decision: string,
     *     branches_repechage: array<int, string>
     * }
     */
    public function build(): array
    {
        $anneeId = $this->inscription->annee_scolaire_id;
        $classeId = $this->inscription->classe_id;

        $periodes = Periode::query()
            ->where('annee_scolaire_id', $anneeId)
            ->where('statut', 'actif')
            ->orderBy('id')
            ->get();

        $affectations = AffectationCours::query()
            ->where('classe_id', $classeId)
            ->where('annee_scolaire_id', $anneeId)
            ->where('statut', 'actif')
            ->with('cours')
            ->get()
            ->sortBy(fn ($affectation) => $affectation->cours?->intitule)
            ->values();

        $notes = Note::query()
            ->where('eleve_id', $this->inscription->eleve_id)
            ->where('statut', 'actif')
            ->with(['evaluation:id,affectation_id,periode_id,max_note,type_evaluation'])
            ->get()
            ->groupBy(fn ($note) => $note->evaluation?->affectation_id);

        $branches = [];
        $totalGeneral = 0.0;
        $maxGeneral = 0.0;

        foreach ($affectations as $affectation) {
            $cours = $affectation->cours;
            $lignes = [];
            $totalBranche = 0.0;
            $maxBranche = 0.0;

            foreach ($periodes as $periode) {
                $notesPeriode = $this->notesPeriode($notes->get($affectation->id, collect()), $periode->id);

                [$tjObtenu, $tjMaxCalcule] = $this->agrege($notesPeriode, examen: false);
                [$exObtenu, $exMaxCalcule] = $this->agrege($notesPeriode, examen: true);

                // Le maximum de référence (barème saisi sur la branche) prime ;
                // à défaut, on retombe sur le maximum calculé des évaluations.
                $maxTj = $cours?->max_travaux_journaliers !== null ? (float) $cours->max_travaux_journaliers : $tjMaxCalcule;
                $maxEx = $cours?->max_examen !== null ? (float) $cours->max_examen : $exMaxCalcule;

                $total = $tjObtenu + $exObtenu;
                $max = $maxTj + $maxEx;

                $lignes[$periode->id] = [
                    'tj' => round($tjObtenu, 2),
                    'tj_max' => round($maxTj, 2),
                    'examen' => round($exObtenu, 2),
                    'examen_max' => round($maxEx, 2),
                    'total' => round($total, 2),
                    'max' => round($max, 2),
                    'pourcentage' => $max > 0 ? round($total / $max * 100, 2) : null,
                ];

                $totalBranche += $total;
                $maxBranche += $max;
            }

            $branches[] = [
                'intitule' => $cours?->intitule ?? 'Branche',
                'code' => $cours?->code,
                'periodes' => $lignes,
                'total' => round($totalBranche, 2),
                'max' => round($maxBranche, 2),
                'pourcentage' => $maxBranche > 0 ? round($totalBranche / $maxBranche * 100, 2) : null,
            ];

            $totalGeneral += $totalBranche;
            $maxGeneral += $maxBranche;
        }

        $pourcentageGeneral = $maxGeneral > 0 ? round($totalGeneral / $maxGeneral * 100, 2) : 0.0;

        $seuil = $this->seuil();
 
        $branchesRepechage = array_values(array_map(
            fn (array $branche) => $branche['intitule'],
            array_filter($branches, fn (array $branche) => $branche['max'] > 0 && $branche['pourcentage'] < $seuil)
        ));

        return [
            'inscription' => $this->inscription,
            'periodes' => $periodes,
            'branches' => $branches,
            'total_general' => round($totalGeneral, 2),
            'max_general' => round($maxGeneral, 2),
            'pourcentage_general' => $pourcentageGeneral,
            'decision' => $pourcentageGeneral >= $seuil ? 'Passe' : 'Double',
            'seuil_reussite' => $seuil,
            'branches_repechage' => $branchesRepechage,
        ];
    }

    private function notesPeriode($notes, int $periodeId): \Illuminate\Support\Collection
    {
        return collect($notes)->filter(fn ($note) => (int) $note->evaluation?->periode_id === $periodeId)->values();
    }

    /**
     * @return array{0: float, 1: float} [obtenu, maximum]
     */
    private function agrege(\Illuminate\Support\Collection $notes, bool $examen): array
    {
        $filtrees = $notes->filter(fn ($note) => ($note->evaluation?->type_evaluation === 'examen') === $examen);

        return [
            round((float) $filtrees->sum('note_obtenue'), 2),
            round((float) $filtrees->sum(fn ($note) => (float) $note->evaluation?->max_note), 2),
        ];
    }
}
