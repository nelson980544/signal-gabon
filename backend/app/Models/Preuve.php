<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Preuve extends Model
{
    protected $fillable = ['signalement_id', 'nom_fichier', 'chemin', 'type_fichier', 'taille'];

    public function signalement()
    {
        return $this->belongsTo(Signalement::class);
    }
}
