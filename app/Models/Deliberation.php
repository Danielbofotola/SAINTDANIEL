<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Décision de fin d'année pour une inscription : la moyenne vient du bulletin,
 * la décision (admis / ajourné / redouble) du jury et de ses critères.
 */
class Deliberation extends Model
{
    public const DECISIONS = [
        'admis' => 'Admis (passe)',
        'ajourne' => 'Ajourné (repêchage)',
        'redouble' => 'Redoublant',
    ];

    protected $fillable = [
        'inscription_id', 'annee_scolaire_id', 'pourcentage_general', 'cours_echoues',
        'decision', 'critere', 'manuelle', 'classe_suivante_id', 'decide_par', 'decide_le', 'commentaire',
    ];

    protected function casts(): array
    {
        return [
            'pourcentage_general' => 'decimal:2',
            'manuelle' => 'boolean',
            'decide_le' => 'datetime',
        ];
    }

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function classeSuivante()
    {
        return $this->belongsTo(Classe::class, 'classe_suivante_id');
    }

    public function decidePar()
    {
        return $this->belongsTo(User::class, 'decide_par');
    }

    public function libelleDecision(): string
    {
        return self::DECISIONS[$this->decision] ?? $this->decision;
    }
}
