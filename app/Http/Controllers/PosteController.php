<?php

namespace App\Http\Controllers;

use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Poste de travail des rôles pilotés par la matrice des permissions (« Système »,
 * « Secrétaire », « Directeur des Études ») : la page liste les fonctionnalités
 * auxquelles le poste a droit, selon config/permissions.php.
 */
class PosteController extends Controller
{
    public function show(Request $request): View
    {
        return view('poste', [
            'user' => $request->user(),
            'droits' => Permissions::pour($request->user()?->role),
            'features' => Permissions::features(),
        ]);
    }
}
