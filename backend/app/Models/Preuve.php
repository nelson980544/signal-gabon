<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Preuve extends Model
{
    protected $fillable = ['signalement_id', 'nom_fichier', 'chemin', 'type_fichier', 'taille'];

    // Le chemin de stockage n'a aucune utilité côté interface et décrit
    // l'arborescence du serveur : il reste côté API.
    protected $hidden = ['chemin'];

    public function signalement()
    {
        return $this->belongsTo(Signalement::class);
    }
}
