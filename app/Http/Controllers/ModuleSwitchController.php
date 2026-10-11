<?php

namespace App\Http\Controllers;

use App\Models\ParametreSysteme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ModuleSwitchController extends Controller
{
    public function index(): View
    {
        return view('admin.modules.index', [
            'settings' => ParametreSysteme::query()->first() ?? ParametreSysteme::defaults(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'saisie_notes_active' => ['nullable', 'boolean'],
            'portail_parents_actif' => ['nullable', 'boolean'],
            'deliberation_active' => ['nullable', 'boolean'],
            'module_recus_actif' => ['nullable', 'boolean'],
            'module_recouvrement_actif' => ['nullable', 'boolean'],
        ]);

        $settings = ParametreSysteme::query()->first()
            ?? ParametreSysteme::create(ParametreSysteme::defaults()->getAttributes());

        $donnees = [
            'saisie_notes_active' => $request->boolean('saisie_notes_active'),
            'portail_parents_actif' => $request->boolean('portail_parents_actif'),
            'deliberation_active' => $request->boolean('deliberation_active'),
        ];

        // Colonnes optionnelles : ignorées si la table n'est pas migrée.
        foreach (['module_recus_actif', 'module_recouvrement_actif'] as $colonne) {
            if (Schema::hasColumn('parametres_systeme', $colonne)) {
                $donnees[$colonne] = $request->boolean($colonne);
            }
        }

        $settings->update($donnees);

        return redirect()->route('admin.modules.index')->with('success', 'L’état des modules a été mis à jour.');
    }
}
