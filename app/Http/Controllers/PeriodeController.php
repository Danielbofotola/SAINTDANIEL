<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Periode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PeriodeController extends Controller
{
    public function index(): View
    {
        return view('admin.periodes.index', [
            'annee' => AnneeScolaire::enCours(),
            'periodes' => Periode::query()
                ->anneeEnCours()
                ->withCount(['evaluations', 'bulletins'])
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $annee = AnneeScolaire::enCours();

        if (! $annee) {
            return back()->withInput()->with('error', 'Aucune année scolaire en cours : impossible de créer une période.');
        }

        $data = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:50',
                Rule::unique('periodes', 'nom')->where('annee_scolaire_id', $annee->id),
            ],
            // Dates facultatives : elles servent à reconnaître automatiquement
            // la période en cours lors des paiements.
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ], [
            'nom.required' => 'Le nom de la période est obligatoire.',
            'nom.unique' => 'Cette période existe déjà pour l’année en cours.',
            'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
        ]);

        if (! Schema::hasColumn('periodes', 'date_debut')) {
            unset($data['date_debut'], $data['date_fin']);
        }

        Periode::create($data + ['annee_scolaire_id' => $annee->id, 'statut' => 'actif']);

        return redirect()->route('admin.periodes.index')->with('success', 'La période a été créée.');
    }

    public function toggleStatus(Periode $periode): RedirectResponse
    {
        $periode->update(['statut' => $periode->statut === 'actif' ? 'inactif' : 'actif']);

        return back()->with('success', 'Le statut de la période a été mis à jour.');
    }

    public function destroy(Periode $periode): RedirectResponse
    {
        if ($periode->evaluations()->exists() || $periode->bulletins()->exists()) {
            return back()->with('error', 'Cette période contient des évaluations ou des bulletins : supprimez-les d’abord.');
        }

        $periode->delete();

        return redirect()->route('admin.periodes.index')->with('success', 'La période a été supprimée.');
    }
}
