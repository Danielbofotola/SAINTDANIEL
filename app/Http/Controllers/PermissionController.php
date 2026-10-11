<?php

namespace App\Http\Controllers;

use App\Support\Permissions;
use Illuminate\View\View;

/**
 * Page de référence : affiche la matrice des permissions (source unique :
 * config/permissions.php).
 */
class PermissionController extends Controller
{
    public function index(): View
    {
        return view('admin.permissions.index', [
            'roles' => Permissions::roles(),
            'features' => Permissions::features(),
        ]);
    }
}
