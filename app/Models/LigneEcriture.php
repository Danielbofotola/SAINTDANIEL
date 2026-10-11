<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ligne d'écriture : un compte mouvementé au débit ou au crédit.
 */
class LigneEcriture extends Model
{
    protected $table = 'lignes_ecriture';

    protected $fillable = ['ecriture_id', 'compte_id', 'libelle', 'debit', 'credit'];

    protected function casts(): array
    {
        return ['debit' => 'decimal:2', 'credit' => 'decimal:2'];
    }

    public function ecriture()
    {
        return $this->belongsTo(Ecriture::class);
    }

    public function compte()
    {
        return $this->belongsTo(CompteComptable::class, 'compte_id');
    }
}
