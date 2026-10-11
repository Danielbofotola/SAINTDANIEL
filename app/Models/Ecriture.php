<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Écriture comptable (en-tête) : plusieurs lignes équilibrées débit/crédit.
 */
class Ecriture extends Model
{
    protected $table = 'ecritures';

    protected $fillable = [
        'date_ecriture', 'journal', 'libelle', 'reference',
        'source_type', 'source_id', 'total_debit', 'total_credit', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'date_ecriture' => 'date',
            'total_debit' => 'decimal:2',
            'total_credit' => 'decimal:2',
        ];
    }

    public function lignes()
    {
        return $this->hasMany(LigneEcriture::class);
    }

    public function estEquilibree(): bool
    {
        return abs((float) $this->total_debit - (float) $this->total_credit) < 0.005;
    }
}
