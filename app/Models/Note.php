<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $table = 'notes';

    protected $fillable = ['evaluation_id', 'eleve_id', 'note_obtenue', 'observation', 'statut'];

    protected function casts(): array
    {
        return ['note_obtenue' => 'decimal:2'];
    }

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }
}
