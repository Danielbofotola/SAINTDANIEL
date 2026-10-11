<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Cycle;
use App\Models\Option;
use App\Models\Salle;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClasseController extends Controller
{
    public function index(): View
    {
        return view('admin.classes.index', [
            'classes' => Classe::with(['cycle', 'section', 'option', 'salle'])
                ->withCount(['inscriptions as eleves_count' => fn ($query) => $query->anneeEnCours()->where('statut', 'actif')])
                ->orderBy('nom')
                ->get(),
        ]);
    }

    public function students(Classe $classe): View
    {
        $classe->load(['cycle', 'section', 'option', 'salle']);

        $inscriptions = $classe->inscriptions()
            ->anneeEnCours()
            ->with('eleve')
            ->where('statut', 'actif')
            ->latest('date_inscription')
            ->get();

        return view('admin.classes.students', compact('classe', 'inscriptions'));
    }

    public function create(): View
    {
        return view('admin.classes.create', $this->formData());
    }

    public function edit(Classe $classe): View
    {
        return view('admin.classes.create', array_merge(['classe' => $classe], $this->formData($classe)));
    }

    public function store(Request $request): RedirectResponse
    {
        Classe::create($this->validatedData($request));

        return redirect()
            ->route('admin.classes.index')
            ->with('success', 'La classe a été créée avec succès.');
    }

    public function update(Request $request, Classe $classe): RedirectResponse
    {
        $classe->update($this->validatedData($request, $classe));

        return redirect()
            ->route('admin.classes.index')
            ->with('success', 'La classe a été modifiée avec succès.');
    }

    public function toggleStatus(Classe $classe): RedirectResponse
    {
        $classe->update(['statut' => $classe->statut === 'actif' ? 'inactif' : 'actif']);

        return back()->with('success', 'Le statut de la classe a été mis à jour.');
    }

    public function destroy(Classe $classe): RedirectResponse
    {
        if ($classe->inscriptions()->exists()) {
            return back()->with('error', 'Cette classe ne peut pas être supprimée car elle contient des inscriptions.');
        }

        $classe->delete();

        return redirect()
            ->route('admin.classes.index')
            ->with('success', 'La classe a été supprimée.');
    }

    private function formData(?Classe $classe = null): array
    {
        return [
            'cycles' => Cycle::where('statut', 'actif')->orderBy('nom')->get(),
            'salles' => Salle::where('statut', 'actif')
                ->where(function ($query) use ($classe) {
                    $query->whereDoesntHave('classes')
                        ->when($classe, fn ($query) => $query->orWhereHas('classes', fn ($classes) => $classes->whereKey($classe->id)));
                })
                ->orderBy('nom')
                ->get(),
            'sections' => Section::orderBy('nom')->get(),
            'options' => Option::orderBy('nom')->get(),
            'classesSuivantes' => Classe::with('cycle')
                ->where('statut', 'actif')
                ->when($classe, fn ($query) => $query->where('id', '!=', $classe->id))
                ->orderBy('nom')
                ->get(),
        ];
    }

    private function validatedData(Request $request, ?Classe $classe = null): array
    {
        return $request->validate([
            'cycle_id' => ['required', 'exists:cycles,id'],
            'salle_id' => [
                'nullable',
                'exists:salles,id',
                Rule::unique('classes', 'salle_id')->ignore($classe?->id),
            ],
            'nom' => ['required', 'string', 'max:50'],
            // Niveau de progression dans le cycle (1, 2, 3…). Sert au passage
            // de classe : la classe suivante a le niveau immédiatement supérieur.
            'ordre' => ['nullable', 'integer', 'min:1', 'max:30'],
            // Passage explicite (peut franchir un cycle). Vide = niveau supérieur.
            'classe_suivante_id' => array_filter([
                'nullable',
                'exists:classes,id',
                $classe ? Rule::notIn([$classe->id]) : null,
            ]),
            'section_id' => ['nullable', 'exists:sections,id'],
            'option_id' => [
                'nullable',
                Rule::exists('options', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('section_id')) {
                        $query->where('section_id', $request->input('section_id'));
                    }
                }),
            ],
            'statut' => ['required', 'in:actif,inactif'],
        ]);
    }
}