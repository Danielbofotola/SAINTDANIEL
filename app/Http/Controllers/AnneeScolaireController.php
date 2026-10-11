<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnneeScolaireController extends Controller
{
    /**
     * Affiche le formulaire de création et la liste des années scolaires.
     */
    public function index(): View
    {
        return view('admin.annees.index', [
            'annees' => AnneeScolaire::orderByDesc('date_debut')
                ->orderByDesc('libelle')
                ->get(),
        ]);
    }

    /**
     * Enregistre une nouvelle année scolaire.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $data['statut'] = 'actif';
        $data['is_encours'] = false;

        AnneeScolaire::create($data);

        return redirect()
            ->route('admin.annees.index')
            ->with('success', "L'année scolaire « {$data['libelle']} » a été créée avec succès.");
    }

    /**
     * Formulaire de modification d'une année scolaire.
     */
    public function edit(AnneeScolaire $annee): View
    {
        return view('admin.annees.edit', compact('annee'));
    }

    /**
     * Met à jour une année scolaire existante.
     */
    public function update(Request $request, AnneeScolaire $annee): RedirectResponse
    {
        $annee->update($this->validateData($request, $annee));

        return redirect()
            ->route('admin.annees.index')
            ->with('success', "L'année scolaire « {$annee->libelle} » a été modifiée avec succès.");
    }

    /**
     * Supprime une année scolaire, sauf si des données y sont rattachées.
     */
    public function destroy(AnneeScolaire $annee): RedirectResponse
    {
        if ($annee->isUtilisee()) {
            return back()->with('error', "Suppression impossible : l'année « {$annee->libelle} » est déjà rattachée à des inscriptions, périodes ou données financières.");
        }

        $libelle = $annee->libelle;
        $annee->delete();

        return redirect()
            ->route('admin.annees.index')
            ->with('success', "L'année scolaire « {$libelle} » a été supprimée.");
    }

    /**
     * Active / désactive une année scolaire.
     *
     * Une seule année peut être « en cours » à la fois : activer une année
     * désactive automatiquement les autres.
     */
    public function toggle(AnneeScolaire $annee): RedirectResponse
    {
        $activation = ! $annee->is_encours;

        DB::transaction(function () use ($annee, $activation) {
            AnneeScolaire::query()->update(['is_encours' => false]);
            // Mise à jour par requête : `$annee->update()` serait ignoré
            // si sa valeur en mémoire vaut déjà $activation (dirty tracking).
            AnneeScolaire::whereKey($annee->id)->update(['is_encours' => $activation]);
        });

        return back()->with(
            'success',
            $activation
                ? "L'année « {$annee->libelle} » est désormais l'année en cours."
                : "L'année « {$annee->libelle} » n'est plus l'année en cours."
        );
    }

    /**
     * Règles de validation communes à la création et à la modification.
     */
    private function validateData(Request $request, ?AnneeScolaire $annee = null): array
    {
        return $request->validate([
            'libelle' => ['required', 'string', 'max:20', Rule::unique('annees_scolaires', 'libelle')->ignore($annee?->id)],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after:date_debut'],
        ]);
    }
}
