<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Preuve extends Model
{
    protected $fillable = ['signalement_id', 'nom_fichier', 'chemin', 'type_fichier', 'taille', 'chiffre'];

    // Le chemin interne n'est jamais exposé : les fichiers passent par la route de téléchargement.
    protected $hidden = ['chemin'];

    protected function casts(): array
    {
        return [
            'nom_fichier' => 'encrypted',
            'chiffre'     => 'boolean',
        ];
    }

    public function signalement()
    {
        return $this->belongsTo(Signalement::class);
    }
}
