<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\ArchiveAnnee;
use App\Models\Bulletin;
use App\Models\Classe;
use App\Models\Depense;
use App\Models\Eleve;
use App\Models\FraisScolaire;
use App\Models\Inscription;
use App\Models\Note;
use App\Models\Paie;
use App\Models\Paiement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArchiveAnneeController extends Controller
{
    public function index(): View
    {
        $archives = ArchiveAnnee::query()
            ->with('anneeScolaire')
            ->orderByDesc('date_cloture')
            ->get();

        $anneesArchivees = $archives->pluck('annee_scolaire_id')->all();

        return view('admin.archives.index', [
            'archives' => $archives,
            'annees' => AnneeScolaire::orderByDesc('date_debut')->get(),
            'anneesDisponibles' => AnneeScolaire::query()
                ->whereNotIn('id', $anneesArchivees)
                ->orderByDesc('date_debut')
                ->get(),
        ]);
    }

    /**
     * Consultation en lecture seule d'une année clôturée : on « patrouille »
     * dans les données archivées de l'année (effectifs, recettes, dépenses).
     */
    public function show(ArchiveAnnee $archiveAnnee): View
    {
        $archiveAnnee->load('anneeScolaire');
        $anneeId = $archiveAnnee->annee_scolaire_id;

        $effectifs = Classe::query()
            ->orderBy('nom')
            ->withCount(['inscriptions as eleves_count' => fn ($query) => $query
                ->where('annee_scolaire_id', $anneeId)
                ->where('statut', 'actif')])
            ->get()
            ->filter(fn (Classe $classe) => $classe->eleves_count > 0)
            ->values();

        $filles = Eleve::query()
            ->where('sexe', 'F')
            ->whereHas('inscriptions', fn ($query) => $query->where('annee_scolaire_id', $anneeId)->where('statut', 'actif'))
            ->count();

        $garcons = Eleve::query()
            ->where('sexe', 'M')
            ->whereHas('inscriptions', fn ($query) => $query->where('annee_scolaire_id', $anneeId)->where('statut', 'actif'))
            ->count();

        $recettes = Paiement::query()
            ->where('statut', 'actif')
            ->whereHas('inscription', fn ($query) => $query->where('annee_scolaire_id', $anneeId))
            ->get();

        $recettesParType = $recettes
            ->groupBy(fn (Paiement $paiement) => $paiement->fraisScolaire?->typeFrais?->nom ?? 'Autre')
            ->map(fn ($groupe) => round((float) $groupe->sum('montant_paye'), 2));

        $recettesParMode = $recettes
            ->groupBy('mode_paiement')
            ->map(fn ($groupe) => round((float) $groupe->sum('montant_paye'), 2));

        $depenses = Depense::query()
            ->where('annee_scolaire_id', $anneeId)
            ->with('categorie')
            ->get();

        $depensesParCategorie = $depenses
            ->groupBy(fn (Depense $depense) => $depense->categorie?->nom ?? 'Autre')
            ->map(fn ($groupe) => round((float) $groupe->sum('montant'), 2));

        return view('admin.archives.show', [
            'archive' => $archiveAnnee,
            'annee' => $archiveAnnee->anneeScolaire,
            'effectifs' => $effectifs,
            'totalEleves' => $effectifs->sum('eleves_count'),
            'garcons' => $garcons,
            'filles' => $filles,
            'recettesParType' => $recettesParType,
            'recettesParMode' => $recettesParMode,
            'depensesParCategorie' => $depensesParCategorie,
            'paiements' => Paiement::query()
                ->where('statut', 'actif')
                ->whereHas('inscription', fn ($query) => $query->where('annee_scolaire_id', $anneeId))
                ->with(['inscription.eleve', 'inscription.classe', 'fraisScolaire.typeFrais'])
                ->latest('date_paiement')
                ->paginate(15, ['*'], 'paiements'),
            'depenses' => Depense::query()
                ->where('annee_scolaire_id', $anneeId)
                ->with('categorie')
                ->latest('date_depense')
                ->paginate(15, ['*'], 'depenses_page'),
            'modes' => ['especes' => 'Espèces', 'banque' => 'Banque', 'mobile_money' => 'Mobile Money'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'annee_scolaire_id' => ['required', Rule::exists('annees_scolaires', 'id'), 'unique:archives_annees,annee_scolaire_id'],
            'remarques' => ['nullable', 'string', 'max:2000'],
        ], [
            'annee_scolaire_id.required' => 'Sélectionnez une année scolaire.',
            'annee_scolaire_id.unique' => 'Cette année scolaire est déjà clôturée.',
        ]);

        $anneeId = (int) $data['annee_scolaire_id'];

        $recettes = (float) Paiement::query()
            ->where('statut', 'actif')
            ->whereHas('inscription', fn ($query) => $query->where('annee_scolaire_id', $anneeId))
            ->sum('montant_paye');

        $depenses = (float) Depense::query()
            ->where('annee_scolaire_id', $anneeId)
            ->where('statut', 'actif')
            ->sum('montant');

        // Salaires versés aux professeurs et au personnel : ils pèsent sur le
        // solde de l'année et sont donc figés eux aussi.
        $annee = AnneeScolaire::findOrFail($anneeId);
        $paiesDeLAnnee = Paie::query()->where('statut', 'actif');
        if ($annee->date_debut && $annee->date_fin) {
            $paiesDeLAnnee->whereBetween('periode', [
                $annee->date_debut->format('Y-m'),
                $annee->date_fin->format('Y-m'),
            ]);
        }
        $salaires = (float) $paiesDeLAnnee->sum('montant_net');
        $nombrePaies = (clone $paiesDeLAnnee)->count();

        // Tout ce qui reste à figer à la clôture : frais attendus, impayés,
        // effectifs par sexe et résultats (bulletins / notes).
        $totalEleves = Inscription::query()->where('annee_scolaire_id', $anneeId)->where('statut', 'actif')->count();
        $attendu = (float) FraisScolaire::query()->where('annee_scolaire_id', $anneeId)->where('statut', 'actif')->sum('montant_exige');
        $garcons = Eleve::query()->where('sexe', 'M')
            ->whereHas('inscriptions', fn ($query) => $query->where('annee_scolaire_id', $anneeId)->where('statut', 'actif'))
            ->count();
        $filles = Eleve::query()->where('sexe', 'F')
            ->whereHas('inscriptions', fn ($query) => $query->where('annee_scolaire_id', $anneeId)->where('statut', 'actif'))
            ->count();
        $bulletins = Bulletin::query()
            ->whereHas('inscription', fn ($query) => $query->where('annee_scolaire_id', $anneeId))
            ->count();
        $notes = Note::query()
            ->whereHas('evaluation.affectation', fn ($query) => $query->where('annee_scolaire_id', $anneeId))
            ->count();

        DB::transaction(function () use ($anneeId, $data, $recettes, $depenses, $salaires, $nombrePaies, $totalEleves, $attendu, $garcons, $filles, $bulletins, $notes) {
            ArchiveAnnee::create([
                'annee_scolaire_id' => $anneeId,
                'total_eleves_inscrits' => $totalEleves,
                'total_recettes' => $recettes,
                'total_depenses' => $depenses,
                'total_salaires' => $salaires,
                'nombre_paies' => $nombrePaies,
                'solde_final' => $recettes - $depenses - $salaires,
                'total_attendu' => $attendu,
                'total_impayes' => max($attendu - $recettes, 0),
                'eleves_garcons' => $garcons,
                'eleves_filles' => $filles,
                'total_bulletins' => $bulletins,
                'total_notes' => $notes,
                'date_cloture' => now(),
                'remarques' => $data['remarques'] ?? null,
            ]);
        });

        return redirect()->route('admin.archives.index')->with('success', 'L’année scolaire a été clôturée et archivée.');
    }

    public function destroy(ArchiveAnnee $archiveAnnee): RedirectResponse
    {
        $archiveAnnee->delete();

        return redirect()->route('admin.archives.index')->with('success', 'L’archive a été supprimée : l’année est de nouveau ouverte.');
    }
}
