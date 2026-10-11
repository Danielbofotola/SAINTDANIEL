<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bulletin extends Model
{
    protected $table = 'bulletins';

    protected $fillable = [
        'inscription_id', 'periode_id', 'total_points', 'max_possible', 'pourcentage',
        'place_classe', 'mention', 'application', 'conduite', 'chemin_pdf', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'total_points' => 'decimal:2',
            'max_possible' => 'decimal:2',
            'pourcentage' => 'decimal:2',
            'approuve_at' => 'datetime',
        ];
    }

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }
}
