<?php

namespace App\Http\Controllers;

use App\Models\AffectationCours;
use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Inscription;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TitulaireDashboardController extends Controller
{
    /**
     * Les classes suivies par le titulaire et leurs effectifs de l'année en cours.
     *
     * Un titulaire est rattaché à sa fiche professeur (users.professeur_id) ;
     * ses classes sont celles où il assure au moins une affectation de cours
     * pour l'année scolaire en cours.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $professor = $user->professeur;
        $schoolYear = AnneeScolaire::enCours();

        $classes = collect();
        $eleves = collect();
        $effectif = 0;
        $nbCours = 0;

        if ($professor && $schoolYear) {
            $affectations = AffectationCours::query()
                ->anneeEnCours()
                ->where('professeur_id', $professor->id);

            $nbCours = (clone $affectations)->count();
            $classeIds = (clone $affectations)->pluck('classe_id')->unique()->values();

            $classes = Classe::query()
                ->whereIn('id', $classeIds)
                ->with(['cycle', 'salle'])
                ->withCount(['inscriptions as effectif' => fn ($query) => $query
                    ->anneeEnCours()
                    ->where('statut', 'actif')])
                ->orderBy('nom')
                ->get();

            $inscriptions = Inscription::query()
                ->anneeEnCours()
                ->whereIn('classe_id', $classeIds)
                ->where('statut', 'actif');

            $effectif = (clone $inscriptions)->distinct()->count('eleve_id');

            $eleves = (clone $inscriptions)
                ->with(['eleve', 'classe'])
                ->latest('date_inscription')
                ->limit(50)
                ->get();
        }

        return view('titulaire.dashboard', [
            'user' => $user,
            'professor' => $professor,
            'schoolYear' => $schoolYear,
            'classes' => $classes,
            'eleves' => $eleves,
            'effectif' => $effectif,
            'nbCours' => $nbCours,
        ]);
    }
}
