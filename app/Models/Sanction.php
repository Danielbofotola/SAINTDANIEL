<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Sanction disciplinaire prononcée par le Préfet des Études pour un élève.
 */
class Sanction extends Model
{
    public const TYPES = [
        'consigne' => 'Consigne',
        'avertissement' => 'Avertissement',
        'exclusion_temporaire' => 'Exclusion temporaire',
        'exclusion_definitive' => 'Exclusion définitive',
    ];

    public const STATUTS = [
        'en_cours' => 'En cours',
        'levee' => 'Levée',
    ];

    protected $fillable = [
        'eleve_id', 'classe_id', 'annee_scolaire_id', 'type', 'motif',
        'date_sanction', 'date_fin', 'observations', 'statut', 'prononce_par', 'levee_le',
    ];

    protected function casts(): array
    {
        return [
            'date_sanction' => 'date',
            'date_fin' => 'date',
            'levee_le' => 'datetime',
        ];
    }

    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function prononcePar()
    {
        return $this->belongsTo(User::class, 'prononce_par');
    }

    /** Libellé lisible du type de sanction. */
    public function libelleType(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
