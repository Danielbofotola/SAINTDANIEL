<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SectionOptionController extends Controller
{
    /**
     * Page de gestion des sections (liste + création).
     */
    public function indexSections(): View
    {
        return view('admin.sections.index', [
            'sections' => Section::query()
                ->withCount(['options', 'classes'])
                ->orderBy('nom')
                ->get(),
        ]);
    }

    /**
     * Page de gestion des options (liste + création).
     */
    public function indexOptions(Request $request): View
    {
        return view('admin.options.index', [
            'options' => Option::query()
                ->with('section')
                ->withCount('classes')
                ->orderBy('nom')
                ->get(),
            'sections' => Section::orderBy('nom')->get(),
            'selectedSectionId' => $request->query('section_id'),
        ]);
    }

    /**
     * Page de création rapide d'une section, ouverte depuis le formulaire de classe
     * (le retour ramène à ce formulaire).
     */
    public function createSection(): View
    {
        return view('admin.sections.create');
    }

    /**
     * Page de création rapide d'une option, ouverte depuis le formulaire de classe.
     */
    public function createOption(Request $request): View
    {
        return view('admin.options.create', [
            'sections' => Section::orderBy('nom')->get(),
            'selectedSectionId' => $request->query('section_id'),
        ]);
    }

    public function storeSection(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100', 'unique:sections,nom'],
        ], [
            'nom.required' => 'Le nom de la section est obligatoire.',
            'nom.unique' => 'Cette section existe déjà.',
        ]);

        $section = Section::create($data);

        // Depuis la page de gestion, on reste sur la gestion ; sinon on revient
        // au formulaire de classe qui avait ouvert la création rapide.
        if ($request->input('retour') === 'gestion') {
            return redirect()
                ->route('admin.sections.index')
                ->with('success', "La section « {$section->nom} » a été créée.");
        }

        return redirect()
            ->route('admin.classes.create', ['section_id' => $section->id])
            ->with('success', 'La section a été créée. Vous pouvez maintenant compléter la classe.');
    }

    public function storeOption(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'section_id' => ['required', Rule::exists('sections', 'id')],
            'nom' => [
                'required',
                'string',
                'max:100',
                Rule::unique('options', 'nom')->where('section_id', $request->input('section_id')),
            ],
        ], [
            'section_id.required' => 'Sélectionnez une section.',
            'section_id.exists' => 'La section sélectionnée est introuvable.',
            'nom.required' => 'Le nom de l’option est obligatoire.',
            'nom.unique' => 'Cette option existe déjà dans cette section.',
        ]);

        $option = Option::create($data);

        if ($request->input('retour') === 'gestion') {
            return redirect()
                ->route('admin.options.index')
                ->with('success', "L’option « {$option->nom} » a été créée.");
        }

        return redirect()
            ->route('admin.classes.create', [
                'section_id' => $option->section_id,
                'option_id' => $option->id,
            ])
            ->with('success', 'L’option a été créée. Vous pouvez maintenant compléter la classe.');
    }
}
