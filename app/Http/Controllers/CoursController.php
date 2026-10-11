<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CoursController extends Controller
{
    /** Page dédiée à la gestion des cours (branches). */
    public function index(): View
    {
        return view('admin.cours.index', [
            'cours' => Cours::query()
                ->withCount('affectations')
                ->orderBy('intitule')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Cours::create($this->validated($request) + ['statut' => 'actif']);

        return redirect()->route('admin.cours.index')->with('success', 'Le cours a été ajouté.');
    }

    public function update(Request $request, Cours $cours): RedirectResponse
    {
        $cours->update($this->validated($request, $cours));

        return redirect()->route('admin.cours.index')->with('success', 'Le cours a été modifié.');
    }

    public function toggleStatus(Cours $cours): RedirectResponse
    {
        $cours->update(['statut' => $cours->statut === 'actif' ? 'inactif' : 'actif']);

        return back()->with('success', 'Le statut du cours a été mis à jour.');
    }

    public function destroy(Cours $cours): RedirectResponse
    {
        if ($cours->affectations()->exists()) {
            return back()->with('error', 'Ce cours est affecté à une ou plusieurs classes : supprimez d’abord les affectations.');
        }

        $cours->delete();

        return redirect()->route('admin.cours.index')->with('success', 'Le cours a été supprimé.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Cours $cours = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('cours', 'code')->ignore($cours?->id)],
            'intitule' => ['required', 'string', 'max:100'],
            'ponderation' => ['required', 'integer', 'min:1', 'max:20'],
            'max_travaux_journaliers' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'max_examen' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ], [
            'code.unique' => 'Ce code de cours est déjà utilisé.',
            'code.required' => 'Le code du cours est obligatoire.',
            'intitule.required' => 'L’intitulé du cours est obligatoire.',
            'ponderation.required' => 'La pondération est obligatoire.',
            'max_travaux_journaliers.integer' => 'Le maximum des travaux journaliers doit être un nombre entier.',
            'max_examen.integer' => 'Le maximum d’examen doit être un nombre entier.',
        ]);
    }
}
