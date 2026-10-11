<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAnneeScolaire;
use Illuminate\Database\Eloquent\Model;

/**
 * Synthèse figée d'une année clôturée (archives_annees ne stocke que created_at).
 */
class ArchiveAnnee extends Model
{
    use BelongsToAnneeScolaire;

    protected $table = 'archives_annees';

    public const UPDATED_AT = null;

    protected $fillable = [
        'annee_scolaire_id', 'total_eleves_inscrits', 'total_recettes',
        'total_depenses', 'solde_final', 'total_attendu', 'total_impayes',
        'total_salaires', 'nombre_paies',
        'eleves_garcons', 'eleves_filles', 'total_bulletins', 'total_notes',
        'date_cloture', 'remarques',
    ];

    protected function casts(): array
    {
        return [
            'date_cloture' => 'datetime',
            'total_recettes' => 'decimal:2',
            'total_depenses' => 'decimal:2',
            'solde_final' => 'decimal:2',
            'total_attendu' => 'decimal:2',
            'total_impayes' => 'decimal:2',
            'total_salaires' => 'decimal:2',
        ];
    }
}
