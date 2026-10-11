<?php

namespace App\Http\Controllers;

use App\Models\AffectationCours;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Cours;
use App\Models\Professeurs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AffectationCoursController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $classeId = $request->query('classe_id');

        $affectations = AffectationCours::query()
            ->with(['professeur', 'cours', 'classe'])
            ->anneeEnCours()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->whereHas('professeur', fn ($professeur) => $professeur
                    ->where('nom', 'like', "%{$search}%")
                    ->orWhere('postnom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%"))
                    ->orWhereHas('cours', fn ($cours) => $cours
                        ->where('intitule', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%"))
                    ->orWhereHas('classe', fn ($classe) => $classe->where('nom', 'like', "%{$search}%"));
            }))
            ->when($classeId, fn ($query) => $query->where('classe_id', $classeId))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.affectations.index', [
            'affectations' => $affectations,
            'annee' => AnneeScolaire::enCours(),
            'search' => $search,
            'classeId' => $classeId,
            'professeurs' => Professeurs::where('statut', 'actif')->orderBy('nom')->orderBy('prenom')->get(),
            'cours' => Cours::where('statut', 'actif')->orderBy('intitule')->get(),
            'classes' => Classe::where('statut', 'actif')->orderBy('nom')->get(),
            // Liste complète (non paginée) pour les sélecteurs de permutation.
            'toutesAffectations' => AffectationCours::query()
                ->with(['professeur', 'cours', 'classe'])
                ->anneeEnCours()
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $annee = AnneeScolaire::enCours();

        if (! $annee) {
            return back()->withInput()->with('error', 'Aucune année scolaire en cours : impossible de créer une affectation.');
        }

        $data = $request->validate([
            'professeur_id' => ['required', Rule::exists('professeurs', 'id')->where('statut', 'actif')],
            'cours_id' => ['required', Rule::exists('cours', 'id')->where('statut', 'actif')],
            'classe_id' => ['required', Rule::exists('classes', 'id')->where('statut', 'actif')],
        ], [
            'professeur_id.required' => 'Sélectionnez un professeur.',
            'cours_id.required' => 'Sélectionnez un cours.',
            'classe_id.required' => 'Sélectionnez une classe.',
        ]);

        $exists = AffectationCours::query()
            ->where('professeur_id', $data['professeur_id'])
            ->where('cours_id', $data['cours_id'])
            ->where('classe_id', $data['classe_id'])
            ->where('annee_scolaire_id', $annee->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Cette affectation existe déjà pour l’année en cours.');
        }

        AffectationCours::create($data + ['annee_scolaire_id' => $annee->id, 'statut' => 'actif']);

        return redirect()->route('admin.affectations.index')->with('success', 'L’affectation a été enregistrée.');
    }

    public function destroy(AffectationCours $affectation): RedirectResponse
    {
        if ($affectation->emploisDuTemps()->exists() || $affectation->evaluations()->exists()) {
            return back()->with('error', 'Cette affectation est liée à un emploi du temps ou à des évaluations : supprimez-les d’abord.');
        }

        $affectation->delete();

        return redirect()->route('admin.affectations.index')->with('success', 'L’affectation a été supprimée.');
    }

    /**
     * Mutation : réaffecte une affectation existante (professeur / cours / classe).
     */
    public function muter(Request $request, AffectationCours $affectation): RedirectResponse
    {
        $data = $request->validate([
            'professeur_id' => ['required', Rule::exists('professeurs', 'id')->where('statut', 'actif')],
            'cours_id' => ['required', Rule::exists('cours', 'id')->where('statut', 'actif')],
            'classe_id' => ['required', Rule::exists('classes', 'id')->where('statut', 'actif')],
        ], [
            'professeur_id.required' => 'Sélectionnez un professeur.',
            'cours_id.required' => 'Sélectionnez un cours.',
            'classe_id.required' => 'Sélectionnez une classe.',
        ]);

        $doublon = AffectationCours::query()
            ->whereKeyNot($affectation->id)
            ->where('annee_scolaire_id', $affectation->annee_scolaire_id)
            ->where('professeur_id', $data['professeur_id'])
            ->where('cours_id', $data['cours_id'])
            ->where('classe_id', $data['classe_id'])
            ->exists();

        if ($doublon) {
            return back()->with('error', 'Une affectation identique existe déjà pour cette année.');
        }

        $affectation->update($data);

        return back()->with('success', 'L’affectation a été mutée.');
    }

    /**
     * Permutation : échange les professeurs de deux affectations de la même année.
     */
    public function permuter(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'affectation_a' => ['required', 'exists:affectations_cours,id'],
            'affectation_b' => ['required', 'different:affectation_a', 'exists:affectations_cours,id'],
        ], [
            'affectation_a.required' => 'Sélectionnez la première affectation.',
            'affectation_b.required' => 'Sélectionnez la seconde affectation.',
            'affectation_b.different' => 'Choisissez deux affectations différentes.',
        ]);

        $a = AffectationCours::findOrFail($data['affectation_a']);
        $b = AffectationCours::findOrFail($data['affectation_b']);

        if ((int) $a->annee_scolaire_id !== (int) $b->annee_scolaire_id) {
            return back()->with('error', 'Les deux affectations doivent appartenir à la même année scolaire.');
        }

        // Après échange, aucune des deux combinaisons ne doit exister ailleurs.
        $collision = fn (AffectationCours $cible, int $professeurId) => AffectationCours::query()
            ->whereKeyNot([$a->id, $b->id])
            ->where('annee_scolaire_id', $a->annee_scolaire_id)
            ->where('professeur_id', $professeurId)
            ->where('cours_id', $cible->cours_id)
            ->where('classe_id', $cible->classe_id)
            ->exists();

        if ($collision($a, (int) $b->professeur_id) || $collision($b, (int) $a->professeur_id)) {
            return back()->with('error', 'La permutation créerait une affectation en double : elle est refusée.');
        }

        DB::transaction(function () use ($a, $b): void {
            $professeurA = $a->professeur_id;
            $a->update(['professeur_id' => $b->professeur_id]);
            $b->update(['professeur_id' => $professeurA]);
        });

        return back()->with('success', 'Les professeurs des deux affectations ont été permutés.');
    }
}
