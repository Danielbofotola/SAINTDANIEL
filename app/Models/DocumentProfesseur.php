<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentProfesseur extends Model
{
    protected $table = 'documents_professeurs';

    protected $fillable = ['professeur_id', 'titre', 'type_document', 'chemin_fichier', 'taille_ko', 'statut'];

    public function professeur()
    {
        return $this->belongsTo(Professeurs::class, 'professeur_id');
    }
}
