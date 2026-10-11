<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Appel audio ou vidéo entre deux participants d'une conversation.
 */
class Appel extends Model
{
    public const TYPES = ['audio' => 'Appel vocal', 'video' => 'Appel vidéo'];

    public const STATUTS = ['sonnerie', 'accepte', 'refuse', 'termine'];

    protected $fillable = [
        'conversation_id', 'appelant_id', 'destinataire_id', 'type', 'statut',
        'offre', 'reponse', 'ice_appelant', 'ice_destinataire', 'repond_le', 'duree',
    ];

    protected function casts(): array
    {
        return ['repond_le' => 'datetime'];
    }

    /** Vrai si la sonnerie n'a jamais été décrochée (au-delà d'une minute). */
    public function estManque(): bool
    {
        return $this->statut === 'sonnerie'
            && $this->created_at !== null
            && $this->created_at->diffInSeconds(now()) > 60;
    }

    /** Libellé d'état affiché dans le fil. */
    public function libelleEtat(): string
    {
        if ($this->statut === 'refuse') {
            return 'refusé';
        }
        if ($this->estManque()) {
            return 'manqué';
        }
        if ($this->statut === 'sonnerie') {
            return 'sonnerie…';
        }
        if ($this->duree) {
            return 'terminé · '.gmdate('i:s', $this->duree);
        }

        return 'terminé';
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function appelant()
    {
        return $this->belongsTo(User::class, 'appelant_id');
    }

    public function destinataire()
    {
        return $this->belongsTo(User::class, 'destinataire_id');
    }

    public function libelleType(): string
    {
        return self::TYPES[$this->type] ?? 'Appel';
    }

    /** Candidats ICE déposés par le correspondant, pour ce participant. */
    public function iceDuCorrespondant(int $pourUserId): array
    {
        $colonne = $this->appelant_id === $pourUserId ? 'ice_destinataire' : 'ice_appelant';

        return json_decode((string) $this->{$colonne} ?: '[]', true) ?: [];
    }
}
