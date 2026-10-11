<?php

namespace App\Http\Controllers;

use App\Models\AffectationCours;
use App\Models\Classe;
use App\Models\EmploiDuTemps;
use App\Models\Professeurs;
use App\Models\Salle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmploiDuTempsController extends Controller
{
    private const JOURS = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];

    public function index(Request $request): View
    {
        $classeId = $request->query('classe_id');
        $professeurId = $request->query('professeur_id');

        $entrees = EmploiDuTemps::query()
            ->with(['affectation.professeur', 'affectation.cours', 'affectation.classe', 'salle'])
            ->whereHas('affectation', fn ($query) => $query->anneeEnCours())
            ->when($classeId, fn ($query) => $query->whereHas('affectation', fn ($affectation) => $affectation->where('classe_id', $classeId)))
            ->when($professeurId, fn ($query) => $query->whereHas('affectation', fn ($affectation) => $affectation->where('professeur_id', $professeurId)))
            ->orderByRaw("CASE jour WHEN 'lundi' THEN 1 WHEN 'mardi' THEN 2 WHEN 'mercredi' THEN 3 WHEN 'jeudi' THEN 4 WHEN 'vendredi' THEN 5 WHEN 'samedi' THEN 6 ELSE 7 END")
            ->orderBy('heure_debut')
            ->paginate(40)
            ->withQueryString();

        return view('admin.emplois-du-temps.index', [
            'entrees' => $entrees,
            'jours' => self::JOURS,
            'classeId' => $classeId,
            'professeurId' => $professeurId,
            'classes' => Classe::where('statut', 'actif')->orderBy('nom')->get(),
            'professeurs' => Professeurs::where('statut', 'actif')->orderBy('nom')->orderBy('prenom')->get(),
            'salles' => Salle::where('statut', 'actif')->orderBy('nom')->get(),
            'affectations' => AffectationCours::query()
                ->anneeEnCours()
                ->where('statut', 'actif')
                ->with(['professeur', 'cours', 'classe'])
                ->orderBy('classe_id')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'affectation_id' => ['required', Rule::exists('affectations_cours', 'id')->where('statut', 'actif')],
            'jour' => ['required', Rule::in(self::JOURS)],
            'heure_debut' => ['required', 'date_format:H:i'],
            'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
            'salle_id' => ['nullable', Rule::exists('salles', 'id')->where('statut', 'actif')],
        ], [
            'affectation_id.required' => 'Sélectionnez une affectation (professeur + cours + classe).',
            'jour.required' => 'Choisissez un jour.',
            'heure_debut.required' => 'L’heure de début est obligatoire.',
            'heure_fin.required' => 'L’heure de fin est obligatoire.',
            'heure_fin.after' => 'L’heure de fin doit être postérieure à l’heure de début.',
        ]);

        $affectation = AffectationCours::findOrFail($data['affectation_id']);

        if ($this->hasConflict($data, $affectation)) {
            return back()->withInput()->with('error', 'Ce créneau entre en conflit avec un autre cours (même classe, même professeur ou même salle).');
        }

        EmploiDuTemps::create($data + ['statut' => 'actif']);

        return redirect()->route('admin.emplois-du-temps.index')->with('success', 'Le créneau a été ajouté à l’emploi du temps.');
    }

    public function destroy(EmploiDuTemps $emploiDuTemps): RedirectResponse
    {
        $emploiDuTemps->delete();

        return redirect()->route('admin.emplois-du-temps.index')->with('success', 'Le créneau a été supprimé.');
    }

    /**
     * Détecte un chevauchement horaire : une même classe, un même professeur
     * ou une même salle ne peut pas avoir deux cours qui se recouvrent le même jour.
     */
    private function hasConflict(array $data, AffectationCours $affectation): bool
    {
        return EmploiDuTemps::query()
            ->where('jour', $data['jour'])
            ->where('statut', 'actif')
            ->where('heure_debut', '<', $data['heure_fin'])
            ->where('heure_fin', '>', $data['heure_debut'])
            ->where(function ($query) use ($data, $affectation) {
                $query->whereHas('affectation', fn ($affectationQuery) => $affectationQuery
                    ->where('classe_id', $affectation->classe_id)
                    ->orWhere('professeur_id', $affectation->professeur_id));

                if (! empty($data['salle_id'])) {
                    $query->orWhere('salle_id', $data['salle_id']);
                }
            })
            ->exists();
    }
}
