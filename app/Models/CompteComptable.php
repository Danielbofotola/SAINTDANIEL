<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Compte du plan comptable SYSCOHADA (OHADA).
 */
class CompteComptable extends Model
{
    protected $table = 'comptes_comptables';

    protected $fillable = ['numero', 'libelle', 'classe', 'type', 'statut'];

    protected function casts(): array
    {
        return ['classe' => 'integer'];
    }

    public function lignes()
    {
        return $this->hasMany(LigneEcriture::class, 'compte_id');
    }

    /**
     * Comptes de ce numéro (ou de cette racine) : « 6 » retourne la classe 6.
     */
    public function scopeDeClasse($query, int $classe)
    {
        return $query->where('classe', $classe);
    }
}
