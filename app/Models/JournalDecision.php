<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Décision ou injonction publiée par la direction : trace horodatée avec son
 * auteur, consultable comme journal d'audit.
 */
class JournalDecision extends Model
{
    protected $table = 'journal_decisions';

    public const TYPES = [
        'decision' => 'Décision',
        'injonction' => 'Injonction',
    ];

    protected $fillable = [
        'annee_scolaire_id', 'auteur_id', 'type', 'objet', 'corps',
        'destinataires', 'echeance', 'publie_le',
    ];

    protected function casts(): array
    {
        return [
            'echeance' => 'date',
            'publie_le' => 'datetime',
        ];
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function libelleType(): string
    {
        return self::TYPES[$this->type] ?? ucfirst((string) $this->type);
    }
}
