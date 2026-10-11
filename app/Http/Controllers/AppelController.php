<?php

namespace App\Http\Controllers;

use App\Models\Appel;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Signalisation des appels audio/vidéo (WebRTC) sans serveur temps réel :
 * chaque partie dépose son offre, sa réponse et ses candidats ICE, et lit
 * l'état de l'appel en boucle courte.
 */
class AppelController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'conversation_id' => ['required', 'integer', 'exists:conversations,id'],
            'type' => ['required', 'in:audio,video'],
            'offre' => ['required', 'string'],
        ]);

        $conversation = Conversation::findOrFail($data['conversation_id']);
        abort_unless(
            $conversation->participants()->where('user_id', $request->user()->id)->exists(),
            403,
            'Cette conversation ne vous concerne pas.'
        );

        $destinataire = $conversation->participants()
            ->where('user_id', '!=', $request->user()->id)
            ->first();

        abort_if(! $destinataire, 422, 'Aucun correspondant joignable dans cette conversation.');

        // Une seule sonnerie en cours par appelant.
        Appel::query()
            ->where('appelant_id', $request->user()->id)
            ->where('statut', 'sonnerie')
            ->update(['statut' => 'termine', 'updated_at' => now()]);

        $appel = Appel::create([
            'conversation_id' => $conversation->id,
            'appelant_id' => $request->user()->id,
            'destinataire_id' => $destinataire->id,
            'type' => $data['type'],
            'statut' => 'sonnerie',
            'offre' => $data['offre'],
        ]);

        // Trace l'appel dans le fil : il s'y affichera comme un événement
        // (« sonnerie… », « manqué », « refusé », « terminé · mm:ss »).
        $conversation->messages()->create([
            'auteur_id' => $request->user()->id,
            'corps' => '',
            'appel_id' => $appel->id,
        ]);
        $conversation->forceFill(['derniere_activite_at' => now()])->save();

        return response()->json(['id' => $appel->id]);
    }

    /** Sonnerie en cours pour l'utilisateur connecté (interrogée en boucle). */
    public function entrant(Request $request): JsonResponse
    {
        $appel = Appel::query()
            ->with('appelant')
            ->where('destinataire_id', $request->user()->id)
            ->where('statut', 'sonnerie')
            ->where('created_at', '>=', now()->subSeconds(60))
            ->latest('id')
            ->first();

        $nom = 'Correspondant';
        if ($appel && $appel->appelant) {
            $nom = trim(($appel->appelant->nom ?? '').' '.($appel->appelant->prenom ?? '')) ?: $appel->appelant->username;
        }

        return response()->json([
            'appel' => $appel ? [
                'id' => $appel->id,
                'type' => $appel->type,
                'appelant' => $nom,
                'offre' => $appel->offre,
            ] : null,
        ]);
    }

    /** État de l'appel vu par l'un des deux participants. */
    public function show(Request $request, Appel $appel): JsonResponse
    {
        $this->autoriser($appel, $request);
        $jeSuisAppelant = $appel->appelant_id === $request->user()->id;

        return response()->json([
            'statut' => $appel->statut,
            'type' => $appel->type,
            'role' => $jeSuisAppelant ? 'appelant' : 'destinataire',
            'reponse' => $jeSuisAppelant ? $appel->reponse : null,
            'ice' => $appel->iceDuCorrespondant($request->user()->id),
        ]);
    }

    public function repondre(Request $request, Appel $appel): JsonResponse
    {
        $this->autoriser($appel, $request);
        abort_unless($appel->destinataire_id === $request->user()->id, 403);

        $data = $request->validate(['reponse' => ['required', 'string']]);
        $appel->update(['reponse' => $data['reponse'], 'statut' => 'accepte', 'repond_le' => now()]);

        return response()->json(['ok' => true]);
    }

    public function refuser(Request $request, Appel $appel): JsonResponse
    {
        $this->autoriser($appel, $request);
        $appel->update(['statut' => 'refuse']);

        return response()->json(['ok' => true]);
    }

    public function terminer(Request $request, Appel $appel): JsonResponse
    {
        $this->autoriser($appel, $request);
        $duree = $appel->repond_le ? max(0, (int) $appel->repond_le->diffInSeconds(now())) : null;
        $appel->update(['statut' => 'termine', 'duree' => $duree]);

        return response()->json(['ok' => true]);
    }

    /** Dépôt d'un candidat ICE par l'un des deux participants. */
    public function ice(Request $request, Appel $appel): JsonResponse
    {
        $this->autoriser($appel, $request);
        $data = $request->validate(['candidat' => ['required', 'string']]);

        $colonne = $appel->appelant_id === $request->user()->id ? 'ice_appelant' : 'ice_destinataire';
        $liste = json_decode((string) $appel->{$colonne} ?: '[]', true) ?: [];
        $liste[] = $data['candidat'];

        $appel->update([$colonne => json_encode($liste)]);

        return response()->json(['ok' => true, 'total' => count($liste)]);
    }

    private function autoriser(Appel $appel, Request $request): void
    {
        abort_unless(
            in_array($request->user()->id, [$appel->appelant_id, $appel->destinataire_id], true),
            403,
            'Cet appel ne vous concerne pas.'
        );
    }
}
