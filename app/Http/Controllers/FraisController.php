<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\FraisScolaire;
use App\Models\TypeFrais;
use App\Support\Monnaie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class FraisController extends Controller
{
    public function index(): View
    {
        return view('admin.frais.index', [
            'annee' => AnneeScolaire::enCours(),
            'types' => TypeFrais::query()
                ->withCount('fraisScolaires')
                ->orderBy('nom')
                ->get(),
            'tarifs' => FraisScolaire::query()
                ->anneeEnCours()
                ->with(['typeFrais', 'classe'])
                ->orderBy('type_frais_id')
                ->orderBy('classe_id')
                ->get(),
            'classes' => Classe::where('statut', 'actif')->orderBy('nom')->get(),
            'typesActifs' => TypeFrais::where('statut', 'actif')->orderBy('nom')->get(),
        ]);
    }

    public function storeType(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100', 'unique:types_frais,nom'],
            'description' => ['nullable', 'string', 'max:1000'],
            'est_inscription' => ['nullable', 'boolean'],
        ], [
            'nom.required' => 'Le nom du type de frais est obligatoire.',
            'nom.unique' => 'Ce type de frais existe déjà.',
        ]);

        TypeFrais::create($data + ['est_inscription' => $request->boolean('est_inscription'), 'statut' => 'actif']);

        return redirect()->route('admin.frais.index')->with('success', 'Le type de frais a été créé.');
    }

    public function toggleType(TypeFrais $typeFrais): RedirectResponse
    {
        $typeFrais->update(['statut' => $typeFrais->statut === 'actif' ? 'inactif' : 'actif']);

        return back()->with('success', 'Le statut du type de frais a été mis à jour.');
    }

    public function destroyType(TypeFrais $typeFrais): RedirectResponse
    {
        if ($typeFrais->fraisScolaires()->exists()) {
            return back()->with('error', 'Ce type de frais est utilisé par des tarifs : supprimez-les d’abord.');
        }

        $typeFrais->delete();

        return redirect()->route('admin.frais.index')->with('success', 'Le type de frais a été supprimé.');
    }

    public function storeTarif(Request $request): RedirectResponse
    {
        $annee = AnneeScolaire::enCours();

        if (! $annee) {
            return back()->withInput()->with('error', 'Aucune année scolaire en cours : impossible d’enregistrer un tarif.');
        }

        $data = $request->validate([
            'type_frais_id' => ['required', Rule::exists('types_frais', 'id')->where('statut', 'actif')],
            'classe_id' => ['required', Rule::exists('classes', 'id')->where('statut', 'actif')],
            'montant_exige' => ['required', 'numeric', 'min:0'],
            'devise' => ['nullable', Rule::in(array_keys(Monnaie::DEVISES))],
            'date_echeance' => ['required', 'date'],
        ], [
            'type_frais_id.required' => 'Sélectionnez un type de frais.',
            'classe_id.required' => 'Sélectionnez une classe.',
            'montant_exige.required' => 'Le montant exigé est obligatoire.',
            'date_echeance.required' => 'La date d’échéance est obligatoire.',
        ]);

        $exists = FraisScolaire::query()
            ->where('type_frais_id', $data['type_frais_id'])
            ->where('classe_id', $data['classe_id'])
            ->where('annee_scolaire_id', $annee->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Un tarif existe déjà pour ce type de frais et cette classe cette année.');
        }

        // La devise du tarif n'est enregistrée que si la colonne existe
        // (base non migrée ou schéma de test partiel).
        $data['devise'] = Monnaie::normaliser($data['devise'] ?? null);
        if (! Schema::hasColumn('frais_scolaires', 'devise')) {
            unset($data['devise']);
        }

        FraisScolaire::create($data + ['annee_scolaire_id' => $annee->id, 'statut' => 'actif']);

        return redirect()->route('admin.frais.index')->with('success', 'Le tarif a été enregistré.');
    }

    public function destroyTarif(FraisScolaire $fraisScolaire): RedirectResponse
    {
        if ($fraisScolaire->paiements()->exists()) {
            return back()->with('error', 'Ce tarif a déjà des paiements enregistrés : supprimez-les d’abord.');
        }

        $fraisScolaire->delete();

        return redirect()->route('admin.frais.index')->with('success', 'Le tarif a été supprimé.');
    }
}
