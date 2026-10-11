<?php

namespace App\Http\Controllers;

use App\Models\Salle;
use App\Models\AnneeScolaire;
use App\Support\OccupationSalles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalleController extends Controller
{
    public function index(): View
    {
        $annee = AnneeScolaire::enCours();

        // Effectif de chaque salle = inscriptions actives de l'année dans ses classes.
        $salles = Salle::query()
            ->with(['classes' => fn ($query) => $query
                ->where('statut', 'actif')
                ->withCount(['inscriptions as effectif' => fn ($inscriptions) => $inscriptions
                    ->where('statut', 'actif')
                    ->when($annee, fn ($q) => $q->where('annee_scolaire_id', $annee->id))])])
            ->orderBy('nom')
            ->get();

        $occupations = $salles->mapWithKeys(fn (Salle $salle) => [$salle->id => OccupationSalles::evaluer($salle)]);

        return view('admin.salles.index', [
            'salles' => $salles,
            'annee' => $annee,
            'occupations' => $occupations,
            'sallesARisque' => $salles->filter(fn (Salle $salle) => OccupationSalles::aRisque($occupations[$salle->id]))->values(),
        ]);
    }

    public function create(): View
    {
        return view('admin.salles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:50', 'unique:salles,nom'],
            'capacite' => ['nullable', 'integer', 'min:1'],
            'statut' => ['required', 'in:actif,inactif'],
        ]);

        Salle::create($data);

        return redirect()
            ->route('admin.salles.index')
            ->with('success', 'La salle a été créée avec succès.');
    }

    public function edit(Salle $salle): View
    {
        return view('admin.salles.edit', compact('salle'));
    }

    public function update(Request $request, Salle $salle): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:50', 'unique:salles,nom,' . $salle->id],
            'capacite' => ['nullable', 'integer', 'min:1'],
            'statut' => ['required', 'in:actif,inactif'],
        ]);

        $salle->update($data);

        return redirect()
            ->route('admin.salles.index')
            ->with('success', 'La salle a été modifiée avec succès.');
    }

    public function toggleStatus(Salle $salle): RedirectResponse
    {
        $salle->update([
            'statut' => $salle->statut === 'actif' ? 'inactif' : 'actif',
        ]);

        return back()->with('success', 'Le statut de la salle a été mis à jour.');
    }

    public function destroy(Salle $salle): RedirectResponse
    {
        $salle->delete();

        return redirect()
            ->route('admin.salles.index')
            ->with('success', 'La salle a été supprimée.');
    }
}