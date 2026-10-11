<?php

namespace App\Models;

use App\Support\Monnaie;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $table = 'paiements';

    protected $fillable = [
        'num_recu', 'inscription_id', 'frais_scolaire_id', 'periode_id', 'montant_paye',
        'devise', 'taux', 'montant_recu',
        'date_paiement', 'mode_paiement', 'reference_transaction', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'montant_paye' => 'decimal:2',
            'taux' => 'decimal:2',
            'montant_recu' => 'decimal:2',
            'date_paiement' => 'datetime',
        ];
    }

    /** Devise réellement encaissée (USD ou CDF). */
    public function deviseRecue(): string
    {
        return Monnaie::normaliser($this->devise);
    }

    /** Montant remis par le payeur (retombe sur le montant crédité si absent). */
    public function montantRecu(): float
    {
        return (float) ($this->montant_recu ?? $this->montant_paye);
    }

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function fraisScolaire()
    {
        return $this->belongsTo(FraisScolaire::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }
}
