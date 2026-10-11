<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CycleController extends Controller
{
    public function index(): View
    {
        return view('admin.cycles.index', [
            'cycles' => Cycle::query()
                ->withCount('classes')
                ->orderBy('nom')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:50', 'unique:cycles,nom'],
        ], [
            'nom.required' => 'Le nom du cycle est obligatoire.',
            'nom.unique' => 'Ce cycle existe déjà.',
        ]);

        Cycle::create($data + ['statut' => 'actif']);

        return redirect()->route('admin.cycles.index')->with('success', 'Le cycle a été créé.');
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
