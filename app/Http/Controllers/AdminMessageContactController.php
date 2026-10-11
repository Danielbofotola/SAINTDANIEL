<?php

namespace App\Http\Controllers;

use App\Models\MessageContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Consultation, par l'administration, des messages reçus depuis la page
 * publique de contact (/site/contact).
 */
class AdminMessageContactController extends Controller
{
    public function index(Request $request): View
    {
        $statut = (string) $request->query('statut', '');

        return view('admin.messages-contact.index', [
            'messages' => MessageContact::with('traitePar')
                ->when($statut === MessageContact::STATUT_NOUVEAU, fn ($query) => $query->nouveaux())
                ->when($statut === MessageContact::STATUT_TRAITE, fn ($query) => $query->where('statut', MessageContact::STATUT_TRAITE))
                ->latest()
                ->paginate(25)
                ->withQueryString(),
            'statut' => $statut,
            'counts' => [
                MessageContact::STATUT_NOUVEAU => MessageContact::nouveaux()->count(),
                MessageContact::STATUT_TRAITE => MessageContact::where('statut', MessageContact::STATUT_TRAITE)->count(),
                'total' => MessageContact::count(),
            ],
        ]);
    }

    public function updateStatus(Request $request, MessageContact $messageContact): RedirectResponse
    {
        $data = $request->validate([
            'statut' => ['required', Rule::in([MessageContact::STATUT_NOUVEAU, MessageContact::STATUT_TRAITE])],
        ]);

        if ($data['statut'] === MessageContact::STATUT_TRAITE) {
            $messageContact->marquerTraite($request->user()?->id);
            $message = 'Message '.$messageContact->reference().' marqué comme traité.';
        } else {
            $messageContact->rouvrir();
            $message = 'Message '.$messageContact->reference().' remis dans les nouveaux messages.';
        }

        return back()->with('success', $message);
    }

    public function destroy(MessageContact $messageContact): RedirectResponse
    {
        $reference = $messageContact->reference();
        $messageContact->delete();

        return back()->with('success', 'Message '.$reference.' supprimé.');
    }
}
