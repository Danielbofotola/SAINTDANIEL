<?php

namespace App\Support;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Messagerie interne : conversation directe (un ou plusieurs collègues) ou
 * diffusion à tous les utilisateurs.
 */
class Messagerie
{
    /**
     * Comptes joignables par messagerie pour un utilisateur donné.
     *
     * Règle de contact : un parent ne s'adresse qu'au personnel de la
     * préfecture — le préfet est son interlocuteur unique. Les autres rôles
     * conservent l'accès à tout le personnel actif.
     */
    public static function correspondantsQuery(User $utilisateur): Builder
    {
        $query = User::query()
            ->where('statut', 'actif')
            ->where('id', '!=', $utilisateur->id);

        if ($utilisateur->role === 'parent') {
            $query->where('role', 'prefetture');
        }

        return $query->orderBy('nom')->orderBy('prenom');
    }

    /**
     * Crée une conversation, y inscrit l'auteur et les destinataires, puis
     * dépose le premier message.
     *
     * @param  array<int, int>  $destinatairesIds
     */
    public static function creer(User $auteur, array $destinatairesIds, string $sujet, string $corps): Conversation
    {
        $destinataires = User::query()
            ->whereIn('id', $destinatairesIds)
            ->where('id', '!=', $auteur->id)
            ->pluck('id');

        $conversation = Conversation::create([
            'sujet' => $sujet,
            'type' => $destinataires->count() > 1 ? 'diffusion' : 'directe',
            'createur_id' => $auteur->id,
            'derniere_activite_at' => now(),
        ]);

        $participants = collect([$auteur->id])->merge($destinataires)->unique()->values();

        $conversation->participants()->sync(
            $participants->mapWithKeys(fn (int $id) => [
                $id => ['dernier_lu_at' => $id === $auteur->id ? now() : null],
            ])->all()
        );

        self::envoyer($conversation, $auteur, $corps);

        return $conversation;
    }

    /**
     * Conversation directe entre deux utilisateurs : la retrouve si elle
     * existe déjà, sinon la crée (sans message initial). Permet d'écrire à un
     * correspondant d'un simple clic, sans passer par le formulaire complet.
     */
    public static function ouvrirDirecte(User $auteur, User $destinataire): Conversation
    {
        $existante = Conversation::query()
            ->where('type', 'directe')
            ->whereHas('participants', fn ($query) => $query->where('user_id', $auteur->id))
            ->whereHas('participants', fn ($query) => $query->where('user_id', $destinataire->id))
            ->has('participants', '=', 2)
            ->first();

        if ($existante) {
            return $existante;
        }

        $conversation = Conversation::create([
            'sujet' => 'Discussion',
            'type' => 'directe',
            'createur_id' => $auteur->id,
            'derniere_activite_at' => now(),
        ]);

        $conversation->participants()->sync([
            $auteur->id => ['dernier_lu_at' => now()],
            $destinataire->id => ['dernier_lu_at' => null],
        ]);

        return $conversation;
    }

    /**
     * Ajoute un message à une conversation.
     */
    public static function envoyer(
        Conversation $conversation,
        User $auteur,
        string $corps,
        ?string $pieceJointe = null,
        ?string $pieceJointeNom = null,
        ?int $reponseAId = null,
    ): Message {
        $message = $conversation->messages()->create([
            'auteur_id' => $auteur->id,
            'corps' => $corps,
            'piece_jointe' => $pieceJointe,
            'piece_jointe_nom' => $pieceJointeNom,
            'reponse_a_id' => $reponseAId,
        ]);

        $conversation->forceFill(['derniere_activite_at' => now()])->save();

        // Un nouveau message « réveille » la discussion pour tous : celui qui
        // l'avait retirée de sa liste la revoit, donc aucun message n'est perdu.
        DB::table('conversation_user')
            ->where('conversation_id', $conversation->id)
            ->update(['supprime_at' => null]);

        self::marquerLu($conversation, $auteur);

        return $message;
    }

    /**
     * Retire la discussion de la liste de cet utilisateur uniquement : les autres
     * participants conservent l'intégralité des messages. Tant qu'aucun nouveau
     * message n'est publié, la discussion reste masquée pour lui.
     */
    public static function masquer(Conversation $conversation, User $utilisateur): void
    {
        if ($conversation->participants()->where('user_id', $utilisateur->id)->exists()) {
            $conversation->participants()->updateExistingPivot($utilisateur->id, ['supprime_at' => now()]);
        }
    }

    /**
     * Marque la conversation comme lue par cet utilisateur.
     */
    public static function marquerLu(Conversation $conversation, User $utilisateur): void
    {
        if ($conversation->participants()->where('user_id', $utilisateur->id)->exists()) {
            $conversation->participants()->updateExistingPivot($utilisateur->id, [
                'dernier_lu_at' => now(),
                'dernier_message_id' => (int) $conversation->messages()->max('id'),
            ]);
        }
    }

    /**
     * Conversations d'un utilisateur, avec le nombre de messages non lus.
     */
    public static function conversationsDe(User $utilisateur): Collection
    {
        $query = Conversation::query()
            ->whereHas('participants', fn ($query) => $query->where('user_id', $utilisateur->id))
            ->with(['dernierMessage.auteur', 'createur', 'participants'])
            ->orderByDesc('derniere_activite_at');

        // Un parent ne dialogue qu'avec le préfet : les échanges avec les autres
        // services restent masqués de sa liste de discussions.
        if ($utilisateur->role === 'parent') {
            $query->whereDoesntHave('participants', fn ($query) => $query
                ->where('user_id', '!=', $utilisateur->id)
                ->where('users.role', '!=', 'prefetture'));
        }

        return $query->get()
            ->filter(function (Conversation $conversation) use ($utilisateur) {
                // Discussion retirée de la liste par cet utilisateur (« pour soi »).
                $pivot = $conversation->participants->firstWhere('id', $utilisateur->id)?->pivot;

                return $pivot === null || $pivot->supprime_at === null;
            })
            ->values()
            ->each(fn (Conversation $conversation) => $conversation->setAttribute('non_lus', $conversation->nonLusPour($utilisateur)));
    }

    /**
     * Total des messages non lus (badge du menu).
     */
    public static function nonLus(User $utilisateur): int
    {
        return (int) self::conversationsDe($utilisateur)->sum('non_lus');
    }
}
