<?php

namespace App\Support;

use App\Models\Eleve;
use App\Models\FraisScolaire;
use App\Models\Inscription;
use App\Models\Paiement;
use Illuminate\Support\Collection;

/**
 * Situation complète d'un élève : parcours année par année (années passées
 * comprises) et état financier — frais exigés, paiements encaissés, dette.
 *
 * Une seule logique, partagée par le dossier de l'administration et celui du
 * préfet des études, pour que les deux affichent exactement les mêmes chiffres.
 */
class SituationEleve
{
    /**
     * État financier d'une inscription : exigible, encaissé, reste à payer.
     *
     * @return array{attendu: float, paye: float, reste: float}
     */
    public static function finances(Inscription $inscription): array
    {
        $attendu = (float) FraisScolaire::query()
            ->where('classe_id', $inscription->classe_id)
            ->where('annee_scolaire_id', $inscription->annee_scolaire_id)
            ->where('statut', 'actif')
            ->sum('montant_exige');

        $paye = (float) Paiement::query()
            ->where('inscription_id', $inscription->id)
            ->where('statut', 'actif')
            ->sum('montant_paye');

        return ['attendu' => $attendu, 'paye' => $paye, 'reste' => round($attendu - $paye, 2)];
    }

    /**
     * Parcours de l'élève : toutes ses inscriptions, de l'année la plus récente
     * à la plus ancienne, enrichies de l'état financier de chaque année.
     *
     * @return Collection<int, array{inscription: Inscription, finances: array{attendu: float, paye: float, reste: float}, frais: array}>
     */
    public static function parcours(Eleve $eleve): Collection
    {
        return $eleve->inscriptions()
            ->with(['classe.cycle', 'classe.section', 'classe.option', 'anneeScolaire', 'bulletins'])
            ->get()
            ->sortByDesc(fn (Inscription $inscription) => (string) ($inscription->anneeScolaire?->libelle ?? ''))
            ->values()
            ->map(fn (Inscription $inscription) => [
                'inscription' => $inscription,
                'finances' => self::finances($inscription),
                'frais' => self::frais($inscription),
            ]);
    }

    /**
     * Détail des frais exigés d'une inscription : chaque ligne de frais, ce qui a
     * été encaissé dessus et le reste à payer. Les paiements non rattachés à une
     * ligne sont comptés à part, pour que le détail rejoigne le total de l'année.
     *
     * @return array{lignes: Collection<int, array{frais: FraisScolaire, exige: float, paye: float, reste: float}>, non_affectes: float, totaux: array{exige: float, paye: float, reste: float}}
     */
    public static function frais(Inscription $inscription): array
    {
        $lignes = FraisScolaire::query()
            ->with('typeFrais')
            ->where('classe_id', $inscription->classe_id)
            ->where('annee_scolaire_id', $inscription->annee_scolaire_id)
            ->where('statut', 'actif')
            ->orderBy('date_echeance')
            ->orderBy('id')
            ->get();

        $paiements = Paiement::query()
            ->where('inscription_id', $inscription->id)
            ->where('statut', 'actif')
            ->get(['frais_scolaire_id', 'montant_paye']);

        // Montant encaissé par ligne de frais.
        $parFrais = [];
        foreach ($paiements as $paiement) {
            if ($paiement->frais_scolaire_id === null) {
                continue;
            }
            $cle = (int) $paiement->frais_scolaire_id;
            $parFrais[$cle] = ($parFrais[$cle] ?? 0.0) + (float) $paiement->montant_paye;
        }

        $exigeTotal = round((float) $lignes->sum('montant_exige'), 2);
        $payeTotal = round((float) $paiements->sum('montant_paye'), 2);

        $detail = $lignes->map(function (FraisScolaire $frais) use ($parFrais) {
            $exige = (float) $frais->montant_exige;
            $paye = round($parFrais[(int) $frais->id] ?? 0.0, 2);

            return ['frais' => $frais, 'exige' => $exige, 'paye' => $paye, 'reste' => round($exige - $paye, 2)];
        });

        return [
            'lignes' => $detail,
            'non_affectes' => round($payeTotal - (float) $detail->sum('paye'), 2),
            'totaux' => [
                'exige' => $exigeTotal,
                'paye' => $payeTotal,
                'reste' => round($exigeTotal - $payeTotal, 2),
            ],
        ];
    }

    /**
     * Détail des paiements d'une inscription, du plus récent au plus ancien.
     *
     * @return Collection<int, Paiement>
     */
    public static function paiements(Inscription $inscription): Collection
    {
        return Paiement::query()
            ->with('fraisScolaire.typeFrais')
            ->where('inscription_id', $inscription->id)
            ->orderByDesc('date_paiement')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Dette cumulée sur l'ensemble du parcours.
     *
     * @param  Collection<int, array{inscription: Inscription, finances: array{attendu: float, paye: float, reste: float}}>  $parcours
     */
    public static function detteTotale(Collection $parcours): float
    {
        return round($parcours->sum(fn (array $ligne) => $ligne['finances']['reste']), 2);
    }
}
