<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Fil de discussion interne : échange direct (un ou plusieurs collègues) ou
 * diffusion à tous les utilisateurs.
 */
class Conversation extends Model
{
    public const TYPES = [
        'directe' => 'Message direct',
        'diffusion' => 'Diffusion à tous',
    ];

    protected $fillable = ['sujet', 'type', 'createur_id', 'derniere_activite_at'];

    protected function casts(): array
    {
        return ['derniere_activite_at' => 'datetime'];
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_user')
            ->withPivot('dernier_lu_at', 'dernier_message_id', 'supprime_at')
            ->withTimestamps();
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    public function dernierMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Nombre de messages non lus par cet utilisateur dans cette conversation.
     */
    public function nonLusPour(User $utilisateur): int
    {
        $dernierLu = $this->participants()
            ->where('user_id', $utilisateur->id)
            ->value('dernier_message_id');

        return $this->messages()
            ->where('id', '>', (int) ($dernierLu ?? 0))
            ->where('auteur_id', '!=', $utilisateur->id)
            ->count();
    }

    public function libelleType(): string
    {
        return self::TYPES[$this->type] ?? 'Message direct';
    }
}
