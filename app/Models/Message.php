<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Message d'une conversation.
 */
class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = ['conversation_id', 'auteur_id', 'corps', 'piece_jointe', 'piece_jointe_nom', 'reponse_a_id', 'reaction', 'appel_id'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }

    /** Message auquel celui-ci répond (citation), s'il y en a un. */
    public function reponseA()
    {
        return $this->belongsTo(Message::class, 'reponse_a_id');
    }

    /** Appel rattaché à ce message (événement d'appel dans le fil). */
    public function appel()
    {
        return $this->belongsTo(Appel::class);
    }

    /** Vrai si ce message est un événement d'appel. */
    public function estAppel(): bool
    {
        return $this->appel_id !== null;
    }

    /** Vrai si une réaction émoji est posée sur ce message. */
    public function aReaction(): bool
    {
        return filled($this->reaction);
    }

    public function nomAuteur(): string
    {
        $auteur = $this->auteur;

        if (! $auteur) {
            return 'Utilisateur supprimé';
        }

        return trim(($auteur->nom.' '.$auteur->postnom.' '.$auteur->prenom)) ?: $auteur->username;
    }

    /** Adresse publique de la pièce jointe, ou null s'il n'y en a pas. */
    public function urlPieceJointe(): ?string
    {
        return $this->piece_jointe ? asset('storage/'.$this->piece_jointe) : null;
    }

    /** Vrai si la pièce jointe est une image (affichée en aperçu). */
    public function estImage(): bool
    {
        if (! $this->piece_jointe) {
            return false;
        }

        return in_array(
            strtolower(pathinfo($this->piece_jointe, PATHINFO_EXTENSION)),
            ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'],
            true
        );
    }

    /** Vrai si la pièce jointe est un message vocal (lue par un lecteur audio). */
    public function estAudio(): bool
    {
        if (! $this->piece_jointe) {
            return false;
        }

        return in_array(
            strtolower(pathinfo($this->piece_jointe, PATHINFO_EXTENSION)),
            ['webm', 'ogg', 'oga', 'mp3', 'm4a', 'mp4', 'wav', 'aac', 'opus'],
            true
        );
    }

    /**
     * Vrai si le corps n'est qu'un ou deux emojis : affiché comme un sticker,
     * sans le fond de bulle habituel.
     */
    public function estSticker(): bool
    {
        $corps = trim((string) $this->corps);

        if ($corps === '' || $this->piece_jointe || mb_strlen($corps) > 12) {
            return false;
        }

        return (bool) preg_match(
            '/^[\p{So}\p{Sk}\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}\x{200D}\x{1F3FB}-\x{1F3FF}\s]+$/u',
            $corps
        );
    }

    /** Nom affiché de la pièce jointe. */
    public function nomPieceJointe(): string
    {
        return $this->piece_jointe_nom ?: basename((string) $this->piece_jointe);
    }
}
