<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentEleve extends Model
{
    protected $table = 'documents_eleves';

    public $timestamps = false;

    protected $fillable = ['eleve_id', 'type_document', 'chemin_fichier', 'statut', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }
}