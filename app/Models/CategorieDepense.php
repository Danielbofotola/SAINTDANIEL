<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategorieDepense extends Model
{
    protected $table = 'categories_depenses';

    protected $fillable = ['nom', 'statut'];

    public function depenses()
    {
        return $this->hasMany(Depense::class, 'category_id');
    }
}
