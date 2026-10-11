<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\JournalDecision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Journal des décisions exécutives : le Préfet publie décisions et injonctions
 * (tracées, horodatées), le Système les consulte comme journal d'audit.
 */
class JournalDecisionController extends Controller
{
    /** Journal du Préfet : publication + historique. */
    public function index(): View
    {
        return view('prefecture.decisions', [
            'annee' => AnneeScolaire::enCours(),
            'decisions' => JournalDecision::with('auteur')->latest('publie_le')->latest('id')->paginate(20),
            'types' => JournalDecision::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(JournalDecision::TYPES))],
            'objet' => ['required', 'string', 'max:160'],
            'corps' => ['required', 'string', 'max:5000'],
            'destinataires' => ['nullable', 'string', 'max:255'],
            'echeance' => ['nullable', 'date'],
        ], [
            'type.required' => 'Choisissez le type (décision ou injonction).',
            'objet.required' => 'L’objet est obligatoire.',
            'corps.required' => 'Le texte de la décision est obligatoire.',
        ]);

        JournalDecision::create($data + [
            'annee_scolaire_id' => AnneeScolaire::enCours()?->id,
            'auteur_id' => $request->user()?->id,
            'publie_le' => now(),
        ]);

        return back()->with('success', 'La décision a été publiée et tracée au journal.');
    }

    /** Journal d'audit (Système) : toutes les décisions, tous les auteurs. */
    public function audit(): View
    {
        return view('admin.audit', [
            'decisions' => JournalDecision::with(['auteur', 'anneeScolaire'])
                ->latest('publie_le')
                ->latest('id')
                ->paginate(30),
        ]);
    }
}
