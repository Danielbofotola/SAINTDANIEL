<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $table = 'evaluations';

    protected $fillable = ['affectation_id', 'periode_id', 'titre', 'type_evaluation', 'max_note', 'date_evaluation', 'statut'];

    protected function casts(): array
    {
        return [
            'date_evaluation' => 'date',
            'max_note' => 'decimal:2',
            'validee_at' => 'datetime',
        ];
    }

    public function affectation()
    {
        return $this->belongsTo(AffectationCours::class, 'affectation_id');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
