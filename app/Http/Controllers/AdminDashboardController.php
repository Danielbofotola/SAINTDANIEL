<?php

namespace App\Http\Controllers;

use App\Models\Eleve;
use App\Models\Classe;
use App\Models\Professeurs;
use App\Models\Cycle;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'eleves' => Eleve::with(['inscriptions.classe.cycle'])
                ->inscritAnneeEnCours()
                ->latest()
                ->get(),
            'statistiques' => [
                'garcons' => Eleve::inscritAnneeEnCours()->where('statut', 'actif')->where('sexe', 'M')->count(),
                'filles' => Eleve::inscritAnneeEnCours()->where('statut', 'actif')->where('sexe', 'F')->count(),
                'classes' => Classe::where('statut', 'actif')->count(),
                'professeurs' => Professeurs::where('statut', 'actif')->count(),
            ],
            'occupationSalles' => Classe::with('salle')
                ->withCount(['inscriptions as eleves_count' => fn ($query) => $query->anneeEnCours()->where('statut', 'actif')])
                ->where('statut', 'actif')
                ->orderBy('nom')
                ->get(),
            'repartitionCycles' => Cycle::query()
                ->where('statut', 'actif')
                ->withCount(['inscriptions as total' => fn ($query) => $query
                    ->anneeEnCours()
                    ->where('inscriptions.statut', 'actif')])
                ->orderBy('nom')
                ->get(),
        ]);
    }
}
