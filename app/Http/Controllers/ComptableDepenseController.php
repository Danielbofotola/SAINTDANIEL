<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\CategorieDepense;
use App\Models\Depense;
use App\Support\Tresorerie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Dépenses du caissier : saisie d'une dépense avec sa NATURE (la nature =
 * catégorie de dépense), suivi par nature et par mois, et création rapide
 * d'une nouvelle nature.
 */
class ComptableDepenseController extends Controller
{
    public function index(Request $request): View
    {
        $annee = AnneeScolaire::enCours();

        $depenses = $annee
            ? Depense::query()
                ->where('annee_scolaire_id', $annee->id)
                ->with('categorie')
                ->latest('date_depense')
                ->latest('id')
                ->get()
            : collect();

        $parNature = $depenses
            ->groupBy(fn (Depense $depense) => $depense->categorie?->nom ?? 'Autre')
            ->map(fn ($groupe) => round((float) $groupe->sum('montant'), 2))
            ->sortDesc();

        $parMois = $depenses
            ->groupBy(fn (Depense $depense) => $depense->date_depense?->format('Y-m') ?? '—')
            ->sortKeys()
            ->map(fn ($groupe) => round((float) $groupe->sum('montant'), 2));

        $moisCourant = $depenses
            ->filter(fn (Depense $depense) => $depense->date_depense?->format('Y-m') === now()->format('Y-m'))
            ->sum('montant');

        return view('comptable.depenses', [
            'user' => $request->user(),
            'annee' => $annee,
            'depenses' => $depenses,
            'natures' => CategorieDepense::where('statut', 'actif')->orderBy('nom')->get(),
            'parNature' => $parNature,
            'parMois' => $parMois,
            'total' => round((float) $depenses->sum('montant'), 2),
            'moisCourant' => round((float) $moisCourant, 2),
            'alertes' => Tresorerie::alertes($annee),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $annee = AnneeScolaire::enCours();

        if (! $annee) {
            return back()->withInput()->with('error', 'Aucune année scolaire en cours : impossible d’enregistrer une dépense.');
        }

        $data = $request->validate([
            'nature_id' => ['required', Rule::exists('categories_depenses', 'id')->where('statut', 'actif')],
            'motif' => ['required', 'string', 'max:255'],
            'montant' => ['required', 'numeric', 'min:0.01'],
            'date_depense' => ['required', 'date'],
            'piece_justificative' => ['nullable', 'string', 'max:255'],
        ], [
            'nature_id.required' => 'Choisissez la nature de la dépense.',
            'nature_id.exists' => 'Cette nature de dépense n’existe pas ou est inactive.',
            'motif.required' => 'Le motif de la dépense est obligatoire.',
            'montant.required' => 'Le montant est obligatoire.',
            'montant.min' => 'Le montant doit être supérieur à zéro.',
            'date_depense.required' => 'La date de la dépense est obligatoire.',
        ]);

        $nature = CategorieDepense::findOrFail($data['nature_id']);

        Depense::create([
            'annee_scolaire_id' => $annee->id,
            'category_id' => $nature->id,
            'motif' => $data['motif'],
            'montant' => $data['montant'],
            'date_depense' => $data['date_depense'],
            'piece_justificative' => $data['piece_justificative'] ?? null,
            'statut' => 'actif',
        ]);

        return redirect()
            ->route('comptable.depenses.index')
            ->with('success', 'Dépense enregistrée — nature : '.$nature->nom.'.')
            ->with(Tresorerie::alertePrincipale($annee) ? 'error' : 'success', Tresorerie::alertePrincipale($annee) ?? 'Dépense enregistrée — nature : '.$nature->nom.'.');
    }

    public function storeNature(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100', 'unique:categories_depenses,nom'],
        ], [
            'nom.required' => 'Le nom de la nature est obligatoire.',
            'nom.unique' => 'Cette nature de dépense existe déjà.',
        ]);

        CategorieDepense::create($data + ['statut' => 'actif']);

        return back()->with('success', 'La nature de dépense a été créée.');
    }

    public function destroy(Depense $depense): RedirectResponse
    {
        $depense->delete();

        return back()->with('success', 'La dépense a été supprimée.');
    }
}
