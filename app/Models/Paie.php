<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Paiement du salaire d'un membre du personnel pour une période (un mois).
 */
class Paie extends Model
{
    protected $table = 'paies';

    protected $fillable = [
        'num_paie', 'salaire_id', 'periode', 'montant_base', 'primes', 'retenues',
        'montant_net', 'date_paiement', 'mode_paiement', 'reference', 'observation', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'montant_base' => 'decimal:2',
            'primes' => 'decimal:2',
            'retenues' => 'decimal:2',
            'montant_net' => 'decimal:2',
            'date_paiement' => 'date',
        ];
    }

    public function salaire()
    {
        return $this->belongsTo(Salaire::class);
    }
}
