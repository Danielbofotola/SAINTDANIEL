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

class DepenseController extends Controller
{
    public function index(Request $request): View
    {
        $annee = AnneeScolaire::enCours();

        $depenses = Depense::query()
            ->anneeEnCours()
            ->with('categorie')
            ->latest('date_depense')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.depenses.index', [
            'depenses' => $depenses,
            'annee' => $annee,
            'categories' => CategorieDepense::query()->withCount('depenses')->orderBy('nom')->get(),
            'categoriesActives' => CategorieDepense::where('statut', 'actif')->orderBy('nom')->get(),
            'totalDepenses' => $annee
                ? (float) Depense::query()->where('annee_scolaire_id', $annee->id)->where('statut', 'actif')->sum('montant')
                : 0.0,
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
            'category_id' => ['required', Rule::exists('categories_depenses', 'id')->where('statut', 'actif')],
            'motif' => ['required', 'string', 'max:255'],
            'montant' => ['required', 'numeric', 'min:0.01'],
            'date_depense' => ['required', 'date'],
            'piece_justificative' => ['nullable', 'string', 'max:255'],
        ], [
            'category_id.required' => 'Sélectionnez une catégorie.',
            'motif.required' => 'Le motif de la dépense est obligatoire.',
            'montant.required' => 'Le montant est obligatoire.',
            'montant.min' => 'Le montant doit être supérieur à zéro.',
            'date_depense.required' => 'La date de la dépense est obligatoire.',
        ]);

        Depense::create($data + ['annee_scolaire_id' => $annee->id, 'statut' => 'actif']);

        // Signal immédiat si cette charge met la trésorerie en tension.
        $signal = Tresorerie::alertePrincipale($annee);

        return redirect()->route('admin.depenses.index')
            ->with('success', 'La dépense a été enregistrée.')
            ->with($signal ? 'error' : 'success', $signal ?? 'La dépense a été enregistrée.');
    }

    public function destroy(Depense $depense): RedirectResponse
    {
        $depense->delete();

        return redirect()->route('admin.depenses.index')->with('success', 'La dépense a été supprimée.');
    }

    public function storeCategorie(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100', 'unique:categories_depenses,nom'],
        ], [
            'nom.required' => 'Le nom de la catégorie est obligatoire.',
            'nom.unique' => 'Cette catégorie existe déjà.',
        ]);

        CategorieDepense::create($data + ['statut' => 'actif']);

        return redirect()->route('admin.depenses.index')->with('success', 'La catégorie a été créée.');
    }

    public function destroyCategorie(CategorieDepense $categorieDepense): RedirectResponse
    {
        if ($categorieDepense->depenses()->exists()) {
            return back()->with('error', 'Cette catégorie contient des dépenses : supprimez-les d’abord.');
        }

        $categorieDepense->delete();

        return redirect()->route('admin.depenses.index')->with('success', 'La catégorie a été supprimée.');
    }
}
