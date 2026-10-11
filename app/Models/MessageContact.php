<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Message envoyé depuis le formulaire de contact de la partie publique.
 */
class MessageContact extends Model
{
    public const STATUT_NOUVEAU = 'nouveau';

    public const STATUT_TRAITE = 'traite';

    /** Sujets proposés dans le formulaire public. */
    public const SUJETS = [
        'Information générale',
        'Inscription et admission',
        'Frais scolaires et paiements',
        'Accès à mon compte / connexion',
        'Réclamation ou suggestion',
        'Autre demande',
    ];

    protected $table = 'contact_messages';

    protected $fillable = [
        'nom', 'email', 'telephone', 'sujet', 'message',
        'statut', 'traite_le', 'traite_par', 'adresse_ip',
    ];

    protected function casts(): array
    {
        return [
            'traite_le' => 'datetime',
        ];
    }

    public function traitePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traite_par');
    }

    public function estNouveau(): bool
    {
        return $this->statut === self::STATUT_NOUVEAU;
    }

    /** @param  Builder<self>  $query */
    public function scopeNouveaux(Builder $query): Builder
    {
        return $query->where('statut', self::STATUT_NOUVEAU);
    }

    public function marquerTraite(?int $userId): void
    {
        $this->forceFill([
            'statut' => self::STATUT_TRAITE,
            'traite_le' => now(),
            'traite_par' => $userId,
        ])->save();
    }

    public function rouvrir(): void
    {
        $this->forceFill([
            'statut' => self::STATUT_NOUVEAU,
            'traite_le' => null,
            'traite_par' => null,
        ])->save();
    }

    /** Identifiant lisible affiché au visiteur après l'envoi (ex. « CMS-000042 »). */
    public function reference(): string
    {
        return 'MSG-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
