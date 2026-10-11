<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Boîte de réception des notifications, commune à tous les rôles.
 */
class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('notifications.index', [
            'user' => $user,
            'notifications' => $user->notifications()->paginate(20),
            'nonLues' => $user->unreadNotifications()->count(),
        ]);
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $modele = $request->user()->notifications()->whereKey($notification)->first();

        if (! $modele) {
            return back()->with('error', 'Cette notification n’existe pas.');
        }

        $modele->markAsRead();

        $lien = $modele->data['lien'] ?? null;

        return $lien ? redirect($lien) : back()->with('success', 'Notification marquée comme lue.');
    }

    public function readAll(Request $request): RedirectResponse
    {
        $lues = $request->user()->unreadNotifications()->count();
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', $lues === 0
            ? 'Aucune notification non lue.'
            : $lues.' notification(s) marquée(s) comme lue(s).');
    }
}
