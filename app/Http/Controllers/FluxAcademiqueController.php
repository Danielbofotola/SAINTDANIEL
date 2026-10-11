<?php

namespace App\Http\Controllers;

use App\Models\ParametreSysteme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Contrôle du flux académique par le Préfet : ouverture / fermeture de
 * l'encodage des points pour garantir l'intégrité des délibérations.
 */
class FluxAcademiqueController extends Controller
{
    public function index(): View
    {
        return view('prefecture.flux', [
            'parametres' => ParametreSysteme::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $parametres = ParametreSysteme::query()->first()
            ?? ParametreSysteme::create(ParametreSysteme::defaults()->getAttributes());

        $ouvert = $request->boolean('saisie_notes_active');
        $parametres->update(['saisie_notes_active' => $ouvert]);

        return back()->with('success', 'Encodage des points '.($ouvert ? 'ouvert' : 'fermé').' pour l’établissement.');
    }
}
