<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use App\Models\User;
use App\Notifications\AnnonceNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Annonces : l'administrateur lance une notification vers tout ou partie
 * des utilisateurs (cloche + page « Mes notifications »).
 */
class AnnonceController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.annonces', [
            'user' => $request->user(),
            'annonces' => Annonce::with('auteur')->latest()->paginate(15),
            'niveaux' => Annonce::NIVEAUX,
            'cibles' => Annonce::CIBLES,
            'totalComptes' => User::count(),
            'comptesParRole' => User::query()->selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role'),
            'notificationsEnvoyees' => DB::table('notifications')->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'titre' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:1000'],
            'niveau' => ['required', Rule::in(array_keys(Annonce::NIVEAUX))],
            'cible' => ['required', Rule::in(array_keys(Annonce::CIBLES))],
            'lien' => ['nullable', 'string', 'max:255'],
        ], [
            'titre.required' => 'Le titre de l’annonce est obligatoire.',
            'message.required' => 'Le message de l’annonce est obligatoire.',
            'niveau.in' => 'Niveau d’annonce inconnu.',
            'cible.in' => 'Destinataires inconnus.',
        ]);

        $destinataires = User::query()
            ->when($data['cible'] !== 'tous', fn ($query) => $query->where('role', $data['cible']))
            ->get();

        if ($destinataires->isEmpty()) {
            return back()->withInput()->with('error', 'Aucun utilisateur ne correspond à ces destinataires.');
        }

        $annonce = Annonce::create($data + [
            'envoye_par' => $request->user()->id,
            'destinataires' => $destinataires->count(),
        ]);

        $annonce->loadMissing('auteur');

        Notification::send($destinataires, new AnnonceNotification($annonce));

        return back()->with('success', 'Annonce « '.$annonce->titre.' » envoyée à '
            .$destinataires->count().' utilisateur(s).');
    }

    public function destroy(Annonce $annonce): RedirectResponse
    {
        // Les notifications déjà déposées pour cette annonce sont retirées.
        $retirees = DB::table('notifications')->where('data->annonce_id', $annonce->id)->delete();

        $annonce->delete();

        return back()->with('success', 'Annonce supprimée.'
            .($retirees > 0 ? ' '.$retirees.' notification(s) retirée(s) de la cloche.' : ''));
    }
}
