<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Bulletin;
use App\Models\Classe;
use App\Models\Cycle;
use App\Models\DocumentProfesseur;
use App\Models\Eleve;
use App\Models\EmploiDuTemps;
use App\Models\Evaluation;
use App\Models\Option;
use App\Models\Periode;
use App\Models\Presence;
use App\Models\Professeurs;
use App\Models\Section;
use App\Models\Tuteur;
use App\Models\User;
use App\Support\SituationEleve;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PrefectureDashboardController extends Controller
{
    /**
     * Espace préfecture : vue de supervision de l'établissement pour l'année en cours.
     *
     * Consultation seule — aucun écran d'administration n'est exposé ici.
     */
    public function index(Request $request): View
    {
        return view('prefecture.dashboard', [
            'user' => $request->user(),
            'schoolYear' => AnneeScolaire::enCours(),
            'effectifTotal' => Eleve::inscritAnneeEnCours()->where('statut', 'actif')->count(),
            'classesActives' => Classe::where('statut', 'actif')->count(),
            'professeursActifs' => Professeurs::where('statut', 'actif')->count(),
            'sectionsCount' => Section::count(),
            'optionsCount' => Option::count(),
            'effectifsParCycle' => Cycle::query()
                ->where('statut', 'actif')
                ->withCount(['inscriptions as effectif' => fn ($query) => $query
                    ->anneeEnCours()
                    ->where('inscriptions.statut', 'actif')])
                ->orderBy('nom')
                ->get(),
            'classes' => Classe::query()
                ->with(['cycle', 'salle'])
                ->withCount(['inscriptions as effectif' => fn ($query) => $query
                    ->anneeEnCours()
                    ->where('statut', 'actif')])
                ->where('statut', 'actif')
                ->orderBy('nom')
                ->get(),
            'personnel' => User::query()
                ->selectRaw('role, COUNT(*) as total')
                ->groupBy('role')
                ->pluck('total', 'role'),
        ]);
    }

    /**
     * Effectifs : répartition par cycle, par classe et par sexe (année en cours).
     */
    public function effectifs(Request $request): View
    {
        $parSexe = Eleve::query()
            ->inscritAnneeEnCours()
            ->where('statut', 'actif')
            ->selectRaw('sexe, COUNT(*) as total')
            ->groupBy('sexe')
            ->pluck('total', 'sexe');

        $classes = Classe::query()
            ->with('cycle')
            ->withCount(['inscriptions as effectif' => fn ($query) => $query
                ->anneeEnCours()
                ->where('inscriptions.statut', 'actif')])
            ->where('statut', 'actif')
            ->orderBy('nom')
            ->get();

        return view('prefecture.effectifs', [
            'user' => $request->user(),
            'schoolYear' => AnneeScolaire::enCours(),
            'effectifTotal' => (int) $parSexe->sum(),
            'parSexe' => $parSexe,
            'cycles' => Cycle::query()
                ->where('statut', 'actif')
                ->withCount(['inscriptions as effectif' => fn ($query) => $query
                    ->anneeEnCours()
                    ->where('inscriptions.statut', 'actif')])
                ->orderBy('nom')
                ->get(),
            'classesParCycle' => $classes->groupBy(fn (Classe $classe) => $classe->cycle?->nom ?? 'Sans cycle'),
            'nbClasses' => $classes->count(),
        ]);
    }

    /**
     * Classes actives : organisation, locaux et effectifs.
     */
    public function classes(Request $request): View
    {
        return view('prefecture.classes', [
            'user' => $request->user(),
            'schoolYear' => AnneeScolaire::enCours(),
            'classes' => Classe::query()
                ->with(['cycle', 'salle', 'section', 'option'])
                ->withCount(['inscriptions as effectif' => fn ($query) => $query
                    ->anneeEnCours()
                    ->where('inscriptions.statut', 'actif')])
                ->where('statut', 'actif')
                ->orderBy('nom')
                ->get(),
        ]);
    }

    /**
     * Personnel : comptes utilisateurs par rôle et enseignants enregistrés.
     */
    public function personnel(Request $request): View
    {
        return view('prefecture.personnel', [
            'user' => $request->user(),
            'schoolYear' => AnneeScolaire::enCours(),
            'parRole' => User::query()
                ->selectRaw('role, COUNT(*) as total')
                ->groupBy('role')
                ->pluck('total', 'role'),
            'comptes' => User::query()
                ->orderBy('role')
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get(),
            'professeurs' => Professeurs::query()->orderBy('nom')->orderBy('prenom')->get(),
            'documentsProfesseurs' => DocumentProfesseur::query()
                ->with('professeur')
                ->latest('id')
                ->limit(60)
                ->get(),
        ]);
    }

    /**
     * Profil du préfet : identité, coordonnées, activité et périmètre du rôle.
     */
    public function profil(Request $request): View
    {
        $utilisateur = $request->user();

        return view('prefecture.profil', [
            'user' => $utilisateur,
            'schoolYear' => AnneeScolaire::enCours(),
            'notificationsTotal' => $utilisateur->notifications()->count(),
            'notificationsNonLues' => $utilisateur->notifications()->whereNull('read_at')->count(),
            'dernieresNotifications' => $utilisateur->notifications()->latest()->limit(5)->get(),
            'effectifTotal' => Eleve::inscritAnneeEnCours()->where('statut', 'actif')->count(),
            'classesActives' => Classe::where('statut', 'actif')->count(),
            'professeursActifs' => Professeurs::where('statut', 'actif')->count(),
            'comptesTotal' => User::count(),
        ]);
    }

    /**
     * Présence & ponctualité : assiduité des élèves d'une journée, et
     * enseignants attendus d'après l'emploi du temps.
     */
    public function presences(Request $request): View
    {
        $date = (string) $request->query('date', now()->toDateString());
        $classeId = $request->query('classe');
        $jour = $this->jourDeLaDate($date);

        $presences = Presence::query()
            ->where('date_seance', $date)
            ->when($classeId, fn ($query) => $query->whereHas('eleve.inscriptions', fn ($inscription) => $inscription
                ->anneeEnCours()
                ->where('classe_id', $classeId)))
            ->with('eleve')
            ->get();

        $attendus = EmploiDuTemps::query()
            ->where('statut', 'actif')
            ->when($jour, fn ($query) => $query->where('jour', $jour))
            ->with(['affectation.cours', 'affectation.professeur', 'affectation.classe', 'salle'])
            ->orderBy('heure_debut')
            ->get();

        return view('prefecture.presences', [
            'user' => $request->user(),
            'schoolYear' => AnneeScolaire::enCours(),
            'date' => $date,
            'jour' => $jour,
            'classeId' => $classeId,
            'classes' => Classe::query()->where('statut', 'actif')->orderBy('nom')->get(),
            'presences' => $presences,
            'parStatut' => $presences->groupBy('statut_presence')->map->count(),
            'seances' => $presences->pluck('affectation_id')->unique()->count(),
            'attendus' => $attendus,
            'professeursAttendus' => $attendus->pluck('affectation.professeur')->filter()->unique('id')->values(),
        ]);
    }

    /**
     * Horaire : vue globale de l'emploi du temps de chaque classe et de chaque
     * enseignant, sous forme de grille hebdomadaire. Filtrable par classe ou
     * par enseignant.
     */
    public function horaires(Request $request): View
    {
        $classeId = $request->query('classe');
        $professeurId = $request->query('professeur');

        $emplois = EmploiDuTemps::query()
            ->where('statut', 'actif')
            ->when($classeId, fn ($query) => $query->whereHas('affectation', fn ($affectation) => $affectation->where('classe_id', $classeId)))
            ->when($professeurId, fn ($query) => $query->whereHas('affectation', fn ($affectation) => $affectation->where('professeur_id', $professeurId)))
            ->with(['affectation.cours', 'affectation.professeur', 'affectation.classe', 'salle'])
            ->orderBy('heure_debut')
            ->get();

        $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
        $aujourdhui = $jours[now()->dayOfWeekIso - 1] ?? null;

        // Séances normalisées, réexploitables pour la grille d'une classe ou d'un enseignant.
        $seances = $emplois->map(fn (EmploiDuTemps $emploi) => [
            'classe_id' => $emploi->affectation?->classe_id,
            'professeur_id' => $emploi->affectation?->professeur_id,
            'jour' => $emploi->jour,
            'debut' => substr((string) $emploi->heure_debut, 0, 5),
            'fin' => substr((string) $emploi->heure_fin, 0, 5),
            'cours' => $emploi->affectation?->cours?->intitule ?? 'Cours',
            'classe' => $emploi->affectation?->classe?->nom ?? 'Classe —',
            'professeur' => trim(($emploi->affectation?->professeur?->nom ?? '') . ' ' . ($emploi->affectation?->professeur?->prenom ?? '')) ?: '—',
            'salle' => $emploi->salle?->nom,
        ]);

        $classes = Classe::query()
            ->where('statut', 'actif')
            ->when($classeId, fn ($query) => $query->where('id', $classeId))
            ->orderBy('nom')
            ->get();

        $professeurs = Professeurs::query()
            ->where('statut', 'actif')
            ->when($professeurId, fn ($query) => $query->where('id', $professeurId))
            ->orderBy('nom')
            ->get();

        return view('prefecture.horaires', [
            'user' => $request->user(),
            'schoolYear' => AnneeScolaire::enCours(),
            'classeId' => $classeId,
            'professeurId' => $professeurId,
            'jours' => $jours,
            'aujourdhui' => $aujourdhui,
            'classes' => Classe::query()->where('statut', 'actif')->orderBy('nom')->get(),
            'professeurs' => Professeurs::query()->where('statut', 'actif')->orderBy('nom')->get(),
            'total' => $emplois->count(),
            'parClasse' => $classes->map(fn (Classe $classe) => [
                'titre' => $classe->nom,
                'seances' => $seances->where('classe_id', $classe->id)->values()->all(),
            ]),
            'parProfesseur' => $professeurs->map(fn (Professeurs $professeur) => [
                'titre' => trim($professeur->nom . ' ' . $professeur->prenom),
                'seances' => $seances->where('professeur_id', $professeur->id)->values()->all(),
            ]),
        ]);
    }

    /**
     * Évaluations programmées par les enseignants (interrogations, devoirs, examens).
     */
    public function evaluations(Request $request): View
    {
        $periodeId = $request->query('periode');
        $classeId = $request->query('classe');

        $evaluations = Evaluation::query()
            ->whereHas('affectation', fn ($affectation) => $affectation->anneeEnCours())
            ->when($periodeId, fn ($query) => $query->where('periode_id', $periodeId))
            ->when($classeId, fn ($query) => $query->whereHas('affectation', fn ($affectation) => $affectation->where('classe_id', $classeId)))
            ->with(['affectation.cours', 'affectation.classe', 'affectation.professeur', 'periode'])
            ->withCount('notes')
            ->orderBy('date_evaluation')
            ->get();

        return view('prefecture.evaluations', [
            'user' => $request->user(),
            'schoolYear' => AnneeScolaire::enCours(),
            'periodeId' => $periodeId,
            'classeId' => $classeId,
            'types' => ['interro' => 'Interrogation', 'devoir' => 'Devoir', 'examen' => 'Examen', 'autre' => 'Autre'],
            'periodes' => \App\Models\Periode::query()->anneeEnCours()->orderBy('nom')->get(),
            'classes' => Classe::query()->where('statut', 'actif')->orderBy('nom')->get(),
            'evaluations' => $evaluations,
            'parType' => $evaluations->groupBy('type_evaluation')->map->count(),
            'notesSaisies' => (int) $evaluations->sum('notes_count'),
        ]);
    }

    /**
     * Dossiers élèves : recherche et synthèse (classe, tuteur, santé).
     */
    public function eleves(Request $request): View
    {
        $recherche = trim((string) $request->query('q', ''));

        $eleves = Eleve::query()
            // Les élèves désactivés sont invisibles dans l'application.
            ->visibles()
            ->when($recherche !== '', fn ($query) => $query->where(function ($sous) use ($recherche) {
                $sous->where('nom', 'like', "%{$recherche}%")
                    ->orWhere('postnom', 'like', "%{$recherche}%")
                    ->orWhere('prenom', 'like', "%{$recherche}%")
                    ->orWhere('matricule', 'like', "%{$recherche}%");
            }))
            ->with([
                'tuteur',
                'sante',
                'inscriptions' => fn ($query) => $query->anneeEnCours()->with('classe'),
            ])
            ->withCount('documents')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->paginate(20)
            ->withQueryString();

        return view('prefecture.eleves', [
            'user' => $request->user(),
            'schoolYear' => AnneeScolaire::enCours(),
            'recherche' => $recherche,
            'eleves' => $eleves,
        ]);
    }

    /**
     * Fiche d'un élève : inscription, santé, assiduité, tuteur et comptes parents.
     */
    public function eleveFiche(Request $request, Eleve $eleve): View
    {
        $eleve->load(['tuteur', 'sante', 'parents', 'documents', 'inscriptions.classe']);

        // Parcours complet (années passées comprises) et état financier.
        $annee = AnneeScolaire::enCours();
        $inscriptionEnCours = $eleve->inscriptions->first(fn ($inscription) => (int) $inscription->annee_scolaire_id === (int) ($annee?->id));
        $parcours = SituationEleve::parcours($eleve);

        return view('prefecture.eleve-fiche', [
            'user' => $request->user(),
            'schoolYear' => $annee,
            'eleve' => $eleve,
            'parcours' => $parcours,
            'detteTotale' => SituationEleve::detteTotale($parcours),
            'paiements' => $inscriptionEnCours ? SituationEleve::paiements($inscriptionEnCours) : collect(),
            'documentLabels' => [
                'acte_naissance' => 'Acte de naissance',
                'bulletin' => 'Bulletin',
                'certificat_medical' => 'Certificat médical',
                'autre' => 'Autre document',
            ],
            'presences' => Presence::query()
                ->where('eleve_id', $eleve->id)
                ->orderByDesc('date_seance')
                ->limit(40)
                ->get(),
            'parStatut' => Presence::query()
                ->where('eleve_id', $eleve->id)
                ->selectRaw('statut_presence, COUNT(*) as total')
                ->groupBy('statut_presence')
                ->pluck('total', 'statut_presence'),
        ]);
    }

    /**
     * Tuteurs & parents : contacts, enfants rattachés et comptes parents.
     */
    public function tuteurs(Request $request): View
    {
        $eleves = Eleve::query()
            ->visibles()
            ->with([
                'tuteur',
                'parents',
                'inscriptions' => fn ($query) => $query->anneeEnCours()->with('classe'),
            ])
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        return view('prefecture.tuteurs', [
            'user' => $request->user(),
            'schoolYear' => AnneeScolaire::enCours(),
            'parTuteur' => $eleves->filter(fn (Eleve $eleve) => $eleve->tuteur_id !== null)->groupBy('tuteur_id'),
            'sansTuteur' => $eleves->whereNull('tuteur_id')->count(),
            'comptesParents' => User::query()
                ->where('role', 'parent')
                ->with('eleves')
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get(),
        ]);
    }

    /**
     * Périodes académiques : ouverture / fermeture par le préfet.
     */
    public function periodes(Request $request): View
    {
        return view('prefecture.periodes', [
            'user' => $request->user(),
            'schoolYear' => AnneeScolaire::enCours(),
            'periodes' => Periode::query()
                ->anneeEnCours()
                ->withCount(['evaluations', 'bulletins'])
                ->orderBy('nom')
                ->get(),
        ]);
    }

    /** Ouvre ou ferme une période (statut actif ↔ inactif). */
    public function togglePeriode(Request $request, Periode $periode): RedirectResponse
    {
        $periode->update(['statut' => $periode->statut === 'actif' ? 'inactif' : 'actif']);

        return back()->with('success', $periode->statut === 'actif'
            ? 'La période « '.$periode->nom.' » est ouverte.'
            : 'La période « '.$periode->nom.' » est fermée.');
    }

    /**
     * Résultats : notes et bulletins par période et par classe.
     */
    public function bulletins(Request $request): View
    {
        $periodeId = $request->query('periode');
        $classeId = $request->query('classe');

        $bulletins = Bulletin::query()
            ->when($periodeId, fn ($query) => $query->where('periode_id', $periodeId))
            ->when($classeId, fn ($query) => $query->whereHas('inscription', fn ($inscription) => $inscription->where('classe_id', $classeId)))
            ->with(['periode', 'inscription.eleve', 'inscription.classe'])
            ->orderByDesc('pourcentage')
            ->get();

        return view('prefecture.bulletins', [
            'user' => $request->user(),
            'schoolYear' => AnneeScolaire::enCours(),
            'periodeId' => $periodeId,
            'classeId' => $classeId,
            'periodes' => Periode::query()->anneeEnCours()->orderBy('nom')->get(),
            'classes' => Classe::query()->where('statut', 'actif')->orderBy('nom')->get(),
            'bulletins' => $bulletins,
            'approuves' => $bulletins->filter(fn (Bulletin $bulletin) => $bulletin->approuve_at !== null)->count(),
            'avecPdf' => $bulletins->filter(fn (Bulletin $bulletin) => filled($bulletin->chemin_pdf))->count(),
        ]);
    }

    /** Approuve un bulletin (trace la date et l'auteur). */
    public function approuverBulletin(Request $request, Bulletin $bulletin): RedirectResponse
    {
        $bulletin->forceFill([
            'approuve_at' => now(),
            'approuve_par' => $request->user()->id,
        ])->save();

        return back()->with('success', 'Bulletin approuvé.');
    }

    /** Valide une évaluation programmée par un enseignant. */
    public function validerEvaluation(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $evaluation->forceFill([
            'validee_at' => now(),
            'validee_par' => $request->user()->id,
        ])->save();

        return back()->with('success', 'Évaluation « '.$evaluation->titre.' » validée.');
    }

    /** Statut disciplinaire d'un élève : actif, suspendu ou exclu. */
    public function modifierStatutEleve(Request $request, Eleve $eleve): RedirectResponse
    {
        $data = $request->validate([
            'statut' => ['required', 'in:actif,inactif,suspendu,exclu'],
        ]);

        $eleve->update(['statut' => $data['statut']]);

        return back()->with('success', 'Statut de l\'élève mis à jour.');
    }

    /** Jour de la semaine (en français) correspondant à une date « AAAA-MM-JJ ». */
    private function jourDeLaDate(string $date): ?string
    {
        try {
            return ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'][Carbon::parse($date)->dayOfWeek];
        } catch (\Throwable) {
            return null;
        }
    }
}
