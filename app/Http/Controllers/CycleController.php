<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CycleController extends Controller
{
    public function index(): View
    {
        return view('admin.cycles.index', [
            'cycles' => Cycle::query()
                ->withCount('classes')
                ->orderByRaw('ordre is null')
                ->orderBy('ordre')
                ->orderBy('nom')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:50', 'unique:cycles,nom'],
            // Rang du cycle dans le parcours (1 = premier). Sert à déterminer
            // la fin de cycle et le dernier cycle (diplômation).
            'ordre' => ['nullable', 'integer', 'min:1', 'max:20'],
            // Fin de cycle : aucune | certificat | diplome, avec l'intitulé remis.
            'fin_type' => ['nullable', Rule::in(array_keys(Cycle::FINS))],
            'fin_libelle' => ['nullable', 'string', 'max:100'],
        ], [
            'nom.required' => 'Le nom du cycle est obligatoire.',
            'nom.unique' => 'Ce cycle existe déjà.',
        ]);

        Cycle::create($data + ['statut' => 'actif', 'fin_type' => $data['fin_type'] ?? 'aucune']);

        return redirect()->route('admin.cycles.index')->with('success', 'Le cycle a été créé.');
    }

    /** Met à jour la fin de cycle (type, intitulé) et le rang d'un cycle existant. */
    public function update(Request $request, Cycle $cycle): RedirectResponse
    {
        $data = $request->validate([
            'ordre' => ['nullable', 'integer', 'min:1', 'max:20'],
            'fin_type' => ['required', Rule::in(array_keys(Cycle::FINS))],
            'fin_libelle' => ['nullable', 'string', 'max:100'],
        ]);

        $cycle->update($data);

        return back()->with('success', 'Le cycle « '.$cycle->nom.' » a été mis à jour.');
    }

    public function toggleStatus(Cycle $cycle): RedirectResponse
    {
        $cycle->update(['statut' => $cycle->statut === 'actif' ? 'inactif' : 'actif']);

        return back()->with('success', 'Le statut du cycle a été mis à jour.');
    }

    public function destroy(Cycle $cycle): RedirectResponse
    {
        if ($cycle->classes()->exists()) {
            return back()->with('error', 'Ce cycle contient des classes : supprimez-les d’abord.');
        }

        $cycle->delete();

        return redirect()->route('admin.cycles.index')->with('success', 'Le cycle a été supprimé.');
    }
}
