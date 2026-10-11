<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAnneeScolaire;
use Illuminate\Database\Eloquent\Model;

class Inscription extends Model
{
    use BelongsToAnneeScolaire;

    public const STATUT_EN_ATTENTE = 'en_attente';

    public const STATUT_ACTIF = 'actif';

    protected $fillable = ['eleve_id', 'classe_id', 'annee_scolaire_id', 'date_inscription', 'statut'];

    /**
     * Valide l'inscription : elle devient active (généralement après le
     * règlement du frais d'inscription par la caisse).
     */
    public function valider(): void
    {
        if ($this->statut !== self::STATUT_ACTIF) {
            $this->update(['statut' => self::STATUT_ACTIF]);
        }
    }

    public function estEnAttente(): bool
    {
        return $this->statut === self::STATUT_EN_ATTENTE;
    }

    protected function casts(): array
    {
        return ['date_inscription' => 'date'];
    }

    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function bulletins()
    {
        return $this->hasMany(Bulletin::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }
}
