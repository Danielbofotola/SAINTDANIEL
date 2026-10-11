<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Rémunération définie pour un membre du personnel (professeur ou autre).
 */
class Salaire extends Model
{
    protected $table = 'salaires';

    protected $fillable = ['beneficiaire_type', 'beneficiaire_id', 'salaire_base', 'devise', 'statut'];

    protected function casts(): array
    {
        return ['salaire_base' => 'decimal:2'];
    }

    public function beneficiaire(): MorphTo
    {
        return $this->morphTo();
    }

    public function paies()
    {
        return $this->hasMany(Paie::class);
    }

    /**
     * Nom lisible du bénéficiaire (professeur ou compte utilisateur).
     */
    public function nomBeneficiaire(): string
    {
        $beneficiaire = $this->beneficiaire;

        if (! $beneficiaire) {
            return 'Bénéficiaire supprimé';
        }

        if ($beneficiaire instanceof User) {
            return trim($beneficiaire->nom.' '.$beneficiaire->postnom.' '.$beneficiaire->prenom) ?: $beneficiaire->username;
        }

        return trim($beneficiaire->nom.' '.$beneficiaire->postnom.' '.$beneficiaire->prenom);
    }
}
