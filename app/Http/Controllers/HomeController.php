<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Point d'entrée « Tableau de bord » générique.
     *
     * Chaque rôle est renvoyé vers son propre espace : plus aucune page
     * d'accueil neutre ne peut être atteinte par erreur.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->dashboardRouteName());
    }
}
