<?php

namespace App\Http\Controllers;

use App\Models\AffectationCours;
use App\Models\Eleve;
use App\Support\EspaceProfesseur;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Espace enseignant : planning, cours, élèves — une page par fonction.
 */
class ProfesseurEspaceController extends Controller
{
    private const JOURS = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];

    public function planning(Request $request): View
    {
        $contexte = $this->contexte($request);
        $emplois = EspaceProfesseur::emplois($contexte['affectations']);

        $now = now();
        $jourCourant = self::JOURS[$now->dayOfWeekIso - 1] ?? null;

        $professeur = $contexte['professor'];
        $enseignantNom = trim(($professeur?->nom ?? '').' '.($professeur?->prenom ?? ''));

        // Séances normalisées pour la grille hebdomadaire réutilisable.
        $seances = $emplois->map(fn (object $creneau) => [
            'jour' => $creneau->jour,
            'debut' => substr((string) $creneau->heure_debut, 0, 5),
            'fin' => substr((string) $creneau->heure_fin, 0, 5),
            'cours' => $creneau->cours_nom,
            'classe' => $creneau->classe_nom,
            'professeur' => $enseignantNom,
            'salle' => $creneau->salle_nom,
        ])->values()->all();

        return view('professeur.planning', $contexte + [
            'emplois' => $emplois,
            'parJour' => $emplois->groupBy('jour'),
            'jours' => self::JOURS,
            'jourCourant' => $jourCourant,
            'aujourdhui' => $emplois->where('jour', $jourCourant)->values(),
            'heuresParSemaine' => $this->heuresParSemaine($emplois),
            'seances' => $seances,
        ]);
    }

    public function cours(Request $request): View
    {
        $contexte = $this->contexte($request);
        $emplois = EspaceProfesseur::emplois($contexte['affectations']);
        $evaluations = EspaceProfesseur::evaluations($contexte['affectations']);

        $effectifs = $this->effectifsParClasse($contexte['affectations'], $contexte['schoolYear']?->id);

        return view('professeur.cours', $contexte + [
            'effectifs' => $effectifs,
            'elevesCount' => (int) $effectifs->sum(),
            'evaluationsParAffectation' => $evaluations->groupBy('affectation_id')->map->count(),
            'creneauxParAffectation' => $emplois->groupBy('affectation_id')->map->count(),
            'heuresParSemaine' => $this->heuresParSemaine($emplois),
        ]);
    }

    /**
     * Profil de l'enseignant : informations, charges d'enseignement et
     * répartition hebdomadaire des heures par cours.
     */
    public function profil(Request $request): View
    {
        $contexte = $this->contexte($request);
        $emplois = EspaceProfesseur::emplois($contexte['affectations']);
        $evaluations = EspaceProfesseur::evaluations($contexte['affectations']);

        $notesSaisies = $evaluations->isEmpty()
            ? 0
            : DB::table('notes')
                ->whereIn('evaluation_id', $evaluations->pluck('id'))
                ->where('statut', 'actif')
                ->count();

        return view('professeur.profil', $contexte + [
            'emplois' => $emplois,
            'evaluations' => $evaluations,
            'eleves' => EspaceProfesseur::eleves($contexte['affectations'], $contexte['schoolYear']?->id),
            'effectifs' => $this->effectifsParClasse($contexte['affectations'], $contexte['schoolYear']?->id),
            'heuresParSemaine' => $this->heuresParSemaine($emplois),
            'heuresParCours' => $emplois
                ->groupBy('cours_nom')
                ->map(fn ($creneaux) => $this->heuresParSemaine($creneaux))
                ->sortDesc(),
            'notesSaisies' => $notesSaisies,
        ]);
    }

    public function coursShow(Request $request, AffectationCours $affectation): View
    {
        $this->autoriser($request, $affectation);

        $contexte = $this->contexte($request);
        $emplois = EspaceProfesseur::emplois($contexte['affectations'])->where('affectation_id', $affectation->id)->values();
        $evaluations = EspaceProfesseur::evaluations($contexte['affectations'])->where('affectation_id', $affectation->id)->values();
        $eleves = EspaceProfesseur::eleveParClasse(
            EspaceProfesseur::eleves($contexte['affectations'], $contexte['schoolYear']?->id),
            $affectation->classe_id
        );

        $detail = $contexte['affectations']->firstWhere('id', $affectation->id);

        return view('professeur.cours-show', $contexte + [
            'affectation' => $affectation,
            'detail' => $detail,
            'emplois' => $emplois,
            'evaluations' => $evaluations,
            'eleves' => $eleves,
        ]);
    }

    public function eleves(Request $request): View
    {
        $contexte = $this->contexte($request);
        $eleves = EspaceProfesseur::eleves($contexte['affectations'], $contexte['schoolYear']?->id);

        $recherche = trim((string) $request->query('q'));
        if ($recherche !== '') {
            $aiguille = mb_strtolower($recherche);
            $eleves = $eleves->filter(function (object $eleve) use ($aiguille) {
                $nom = mb_strtolower(trim($eleve->nom.' '.$eleve->postnom.' '.$eleve->prenom.' '.$eleve->matricule));

                return str_contains($nom, $aiguille);
            })->values();
        }

        $classe = $request->query('classe');
        if ($classe) {
            $eleves = $eleves->where('classe_id', (int) $classe)->values();
        }

        return view('professeur.eleves', $contexte + [
            'eleves' => $eleves,
            'recherche' => $recherche,
            'classeFiltree' => $classe ? (int) $classe : null,
            'parClasse' => $eleves->groupBy('classe_nom'),
        ]);
    }

    public function eleveShow(Request $request, Eleve $eleve): View
    {
        $contexte = $this->contexte($request);
        $eleves = EspaceProfesseur::eleves($contexte['affectations'], $contexte['schoolYear']?->id);

        abort_unless($eleves->contains('id', $eleve->id), 403, 'Cet élève n’est pas inscrit dans une de vos classes.');

        $affectationIds = $contexte['affectations']->pluck('id');
        $evaluations = EspaceProfesseur::evaluations($contexte['affectations']);

        $notes = DB::table('notes')
            ->join('evaluations', 'evaluations.id', '=', 'notes.evaluation_id')
            ->join('affectations_cours', 'affectations_cours.id', '=', 'evaluations.affectation_id')
            ->join('cours', 'cours.id', '=', 'affectations_cours.cours_id')
            ->where('notes.eleve_id', $eleve->id)
            ->whereIn('evaluations.affectation_id', $affectationIds)
            ->where('notes.statut', 'actif')
            ->orderByDesc('evaluations.date_evaluation')
            ->select([
                'notes.id',
                'notes.note_obtenue',
                'notes.observation',
                'evaluations.titre as evaluation_titre',
                'evaluations.type_evaluation',
                'evaluations.max_note',
                'evaluations.date_evaluation',
                'cours.intitule as cours_nom',
            ])
            ->get();

        $presences = DB::table('presences')
            ->where('eleve_id', $eleve->id)
            ->whereIn('affectation_id', $affectationIds)
            ->orderByDesc('date_seance')
            ->limit(20)
            ->get();

        return view('professeur.eleve-show', $contexte + [
            'eleve' => $eleve,
            'classeNom' => $eleves->firstWhere('id', $eleve->id)?->classe_nom,
            'notes' => $notes,
            'presences' => $presences,
            'moyenne' => $notes->count() > 0
                ? round($notes->sum(fn ($note) => (float) $note->note_obtenue) / $notes->count(), 2)
                : null,
            'evaluationsAvecNote' => $notes->pluck('evaluation_titre'),
            'evaluationsSansNote' => $evaluations->reject(fn ($e) => $notes->contains('evaluation_titre', $e->titre))->values(),
        ]);
    }

    /**
     * Contexte commun à toutes les pages (le professeur connecté).
     *
     * @return array<string, mixed>
     */
    private function contexte(Request $request): array
    {
        $user = $request->user();
        $professor = $user->professeur;
        $schoolYear = EspaceProfesseur::annee();
        $affectations = EspaceProfesseur::affectations($professor?->id, $schoolYear?->id);

        return [
            'user' => $user,
            'professor' => $professor,
            'schoolYear' => $schoolYear,
            'affectations' => $affectations,
        ];
    }

    private function autoriser(Request $request, AffectationCours $affectation): void
    {
        $professeurId = EspaceProfesseur::professeurId($request->user());

        abort_unless(
            $professeurId && (int) $affectation->professeur_id === $professeurId,
            403,
            'Ce cours ne vous est pas affecté.'
        );
    }

    private function heuresParSemaine($emplois): float
    {
        return round($emplois->sum(fn (object $creneau) => Carbon::parse($creneau->heure_debut)
            ->diffInMinutes(Carbon::parse($creneau->heure_fin)) / 60), 1);
    }

    private function effectifsParClasse($affectations, ?int $anneeId)
    {
        $classeIds = $affectations->pluck('classe_id')->unique()->values();

        if ($classeIds->isEmpty() || ! $anneeId) {
            return collect();
        }

        return DB::table('inscriptions')
            ->where('annee_scolaire_id', $anneeId)
            ->where('statut', 'actif')
            ->whereIn('classe_id', $classeIds)
            ->selectRaw('classe_id, COUNT(DISTINCT eleve_id) as total')
            ->groupBy('classe_id')
            ->pluck('total', 'classe_id');
    }
}
