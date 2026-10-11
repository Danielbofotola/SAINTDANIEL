<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Modération de la messagerie, réservée à l'administration : voir TOUTES les
 * conversations (y compris celles dont elle n'est pas participante), les lire,
 * et supprimer une conversation ou un message isolé.
 */
class AdminMessagerieController extends Controller
{
    public function index(Request $request): View
    {
        $recherche = trim((string) $request->query('q'));

        $conversations = Conversation::query()
            ->with(['createur', 'participants', 'dernierMessage.auteur'])
            ->withCount('messages')
            ->when($recherche !== '', function ($requete) use ($recherche): void {
                $requete->where(function ($q) use ($recherche): void {
                    $q->where('sujet', 'like', '%'.$recherche.'%')
                        ->orWhereHas('participants', fn ($p) => $p
                            ->where('nom', 'like', '%'.$recherche.'%')
                            ->orWhere('postnom', 'like', '%'.$recherche.'%')
                            ->orWhere('prenom', 'like', '%'.$recherche.'%')
                            ->orWhere('username', 'like', '%'.$recherche.'%'));
                });
            })
            ->orderByDesc('derniere_activite_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.messagerie.index', [
            'conversations' => $conversations,
            'recherche' => $recherche,
            'totalConversations' => Conversation::count(),
            'totalMessages' => Message::count(),
        ]);
    }

    public function show(Conversation $conversation): View
    {
        return view('admin.messagerie.show', [
            'conversation' => $conversation->load(['participants', 'createur']),
            'messages' => $conversation->messages()->with('auteur')->orderBy('id')->get(),
        ]);
    }

    /** Supprime une conversation, ses messages et ses participants. */
    public function destroy(Conversation $conversation): RedirectResponse
    {
        $sujet = $conversation->sujet;
        $messages = $conversation->messages()->count();

        DB::transaction(function () use ($conversation): void {
            $conversation->messages()->delete();
            $conversation->participants()->detach();
            $conversation->delete();
        });

        return redirect()
            ->route('admin.messagerie.index')
            ->with('success', 'Conversation « '.$sujet.' » supprimée ('.$messages.' message(s) retiré(s)).');
    }

    /** Supprime un message isolé (modération d'un contenu). */
    public function destroyMessage(Message $message): RedirectResponse
    {
        $conversation = $message->conversation_id;
        $message->delete();

        return redirect()
            ->route('admin.messagerie.show', $conversation)
            ->with('success', 'Message supprimé.');
    }
}
