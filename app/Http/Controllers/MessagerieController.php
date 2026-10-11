<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Support\Messagerie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Messagerie interne, ouverte à tous les rôles : discussion directe avec un ou
 * plusieurs collègues, ou diffusion à tous les utilisateurs.
 */
class MessagerieController extends Controller
{
    public function index(Request $request): View
    {
        $utilisateur = $request->user();
        $conversations = Messagerie::conversationsDe($utilisateur);

        $active = $request->query('conversation')
            ? $conversations->firstWhere('id', (int) $request->query('conversation'))
            : $conversations->first();

        if ($active) {
            Messagerie::marquerLu($active, $utilisateur);
            $active->setAttribute('non_lus', 0);
        }

        return view('messagerie.index', [
            'user' => $utilisateur,
            'conversations' => $conversations,
            'active' => $active,
            'messages' => $active ? $active->messages()->with('auteur')->get() : collect(),
            'destinataires' => Messagerie::correspondantsQuery($utilisateur)->get(),
            'totalUtilisateurs' => User::where('statut', 'actif')->count(),
            'nonLus' => (int) $conversations->sum('non_lus'),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'sujet' => ['required', 'string', 'max:150'],
            'corps' => ['nullable', 'string', 'max:2000', 'required_without:piece_jointe'],
            'piece_jointe' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,bmp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,webm,ogg,oga,mp3,m4a,mp4,wav,aac,opus', 'required_without:corps'],
            'destinataires' => ['required', 'array', 'min:1'],
        ], [
            'sujet.required' => 'Indiquez l’objet du message.',
            'corps.required' => 'Écrivez votre message.',
            'destinataires.required' => 'Choisissez au moins un destinataire.',
        ]);

        $choix = (array) $data['destinataires'];

        // Les destinataires sont restreints aux correspondants autorisés :
        // un parent ne peut écrire qu'au préfet, même en forçant la requête.
        $correspondants = Messagerie::correspondantsQuery($request->user());

        $ids = in_array('tous', $choix, true)
            ? $correspondants->pluck('id')->all()
            : (clone $correspondants)
                ->whereIn('id', array_values(array_filter($choix, fn ($valeur) => ctype_digit((string) $valeur))))
                ->pluck('id')
                ->all();

        if ($ids === []) {
            return back()->withInput()->with('error', 'Aucun destinataire valide : vérifiez votre sélection.');
        }

        [$cheminJoint, $nomJoint] = $this->stockerPieceJointe($request);

        $conversation = Messagerie::creer($request->user(), $ids, $data['sujet'], (string) ($data['corps'] ?? ''), $cheminJoint, $nomJoint);

        if ($request->wantsJson()) {
            return response()->json([
                'conversation_id' => $conversation->id,
                'url' => route('messagerie.index', ['conversation' => $conversation->id]),
                'non_lus' => Messagerie::nonLus($request->user()),
            ]);
        }

        return redirect()
            ->route('messagerie.index', ['conversation' => $conversation->id])
            ->with('success', 'Message envoyé à '.count($ids).' destinataire(s).');
    }

    public function repondre(Request $request, Conversation $conversation): RedirectResponse|JsonResponse
    {
        $this->autoriser($conversation, $request);

        $data = $request->validate([
            'corps' => ['nullable', 'string', 'max:2000', 'required_without:piece_jointe'],
            'piece_jointe' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,bmp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,webm,ogg,oga,mp3,m4a,mp4,wav,aac,opus', 'required_without:corps'],
            'reponse_a_id' => ['nullable', 'integer'],
        ], [
            'corps.required' => 'Écrivez votre message.',
            'piece_jointe.max' => 'La pièce jointe ne doit pas dépasser 5 Mo.',
            'piece_jointe.mimes' => 'Format de pièce jointe non autorisé (images, PDF, documents Office, audio, zip).',
        ]);

        // La réponse citée doit appartenir à la même conversation.
        $reponseAId = isset($data['reponse_a_id'])
            ? $conversation->messages()->whereKey($data['reponse_a_id'])->value('id')
            : null;

        [$cheminJoint, $nomJoint] = $this->stockerPieceJointe($request);

        Messagerie::envoyer($conversation, $request->user(), (string) ($data['corps'] ?? ''), $cheminJoint, $nomJoint, $reponseAId);

        if ($request->wantsJson()) {
            $message = $conversation->messages()->with('auteur')->latest('id')->first();

            return response()->json([
                'message' => [
                    'id' => $message->id,
                    'html' => view('messagerie._bulle', [
                        'message' => $message, 'user' => $request->user(), 'active' => $conversation,
                    ])->render(),
                ],
                'non_lus' => Messagerie::nonLus($request->user()),
            ]);
        }

        return redirect()
            ->route('messagerie.index', ['conversation' => $conversation->id])
            ->with('success', 'Message envoyé.');
    }

    /**
     * Nouveaux messages d'une conversation, en JSON : permet au fil de se
     * rafraîchir tout seul, sans recharger la page.
     */
    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        $this->autoriser($conversation, $request);

        $apres = (int) $request->query('apres', 0);

        $messages = $conversation->messages()
            ->with('auteur')
            ->when($apres > 0, fn ($requete) => $requete->where('id', '>', $apres))
            ->orderBy('id')
            ->get();

        // Consulter le fil vaut lecture : les compteurs se mettent à jour.
        Messagerie::marquerLu($conversation, $request->user());

        return response()->json([
            'messages' => $messages->map(fn ($message) => [
                'id' => $message->id,
                'html' => view('messagerie._bulle', [
                    'message' => $message, 'user' => $request->user(), 'active' => $conversation,
                ])->render(),
            ])->values(),
            'dernier_id' => (int) ($messages->last()->id ?? $apres),
            'non_lus' => Messagerie::nonLus($request->user()),
        ]);
    }

    private function autoriser(Conversation $conversation, Request $request): void
    {
        abort_unless(
            $conversation->participants()->where('user_id', $request->user()->id)->exists(),
            403,
            'Cette conversation ne vous concerne pas.'
        );

        // Un parent ne dialogue qu'avec le préfet : aucun accès aux échanges
        // avec les autres services, même en connaissant l'identifiant.
        if ($request->user()->role === 'parent') {
            abort_if(
                $conversation->participants()
                    ->where('user_id', '!=', $request->user()->id)
                    ->where('users.role', '!=', 'prefetture')
                    ->exists(),
                403,
                'Cette conversation ne vous concerne pas.'
            );
        }
    }

    /**
     * Ouvre (ou retrouve) la conversation directe avec un correspondant, puis
     * renvoie son identifiant : le client recharge la page sur ce fil.
     */
    public function demarrer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'destinataire' => ['required', 'integer', 'exists:users,id'],
        ]);

        $destinataire = Messagerie::correspondantsQuery($request->user())
            ->findOrFail($data['destinataire']);

        $conversation = Messagerie::ouvrirDirecte($request->user(), $destinataire);

        return response()->json(['conversation_id' => $conversation->id]);
    }

    /**
     * Retire la discussion de la liste de l'utilisateur courant (« supprimer pour
     * moi ») : rien n'est supprimé pour les autres participants, qui gardent
     * l'intégralité des messages.
     */
    public function supprimer(Request $request, Conversation $conversation): RedirectResponse|JsonResponse
    {
        $this->autoriser($conversation, $request);

        Messagerie::masquer($conversation, $request->user());

        $message = 'Discussion retirée de votre liste. Les autres participants conservent les messages.';

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'non_lus' => Messagerie::nonLus($request->user()),
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Pose (ou retire, avec une réaction vide) une réaction émoji sur un
     * message, et renvoie la bulle mise à jour.
     */
    public function reagir(Request $request, Message $message): JsonResponse
    {
        $this->autoriser($message->conversation, $request);

        $data = $request->validate([
            'reaction' => ['nullable', 'string', 'max:16'],
        ]);

        $message->forceFill(['reaction' => $data['reaction'] ?: null])->save();
        $message->loadMissing('auteur', 'reponseA');

        return response()->json([
            'message' => [
                'id' => $message->id,
                'html' => view('messagerie._bulle', [
                    'message' => $message, 'user' => $request->user(), 'active' => $message->conversation,
                ])->render(),
            ],
        ]);
    }

    /**
     * Enregistre la pièce jointe éventuelle sur le disque « public ».
     *
     * @return array{0: ?string, 1: ?string} chemin stocké et nom d'origine
     */
    private function stockerPieceJointe(Request $request): array
    {
        if (! $request->hasFile('piece_jointe')) {
            return [null, null];
        }

        $fichier = $request->file('piece_jointe');

        return [$fichier->store('messages', 'public'), $fichier->getClientOriginalName()];
    }
}
