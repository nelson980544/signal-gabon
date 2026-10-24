<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Signalement extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'categorie', 'description', 'date_faits',
        'province', 'ville', 'statut', 'agent_id', 'message_agent', 'email_contact',
    ];

    // L'email de contact est la seule donnée nominative du dossier. Chiffré,
    // il reste illisible pour quiconque obtiendrait une copie de la base ou
    // d'une sauvegarde sans la clé APP_KEY.
    protected $casts = ['email_contact' => 'encrypted'];

    /**
     * Cycle de vie d'un dossier : statut courant => statuts atteignables.
     *
     * Un dossier avance (reçu → examen → attribué → instruction → clos) sans
     * retour arrière ni saut de l'instruction ; il peut être classé sans suite
     * à tout moment tant qu'il est ouvert. « traité » et « classé » sont
     * définitifs, sauf réouverture par un superviseur (voir peutPasserA).
     */
    public const TRANSITIONS = [
        'recu'           => ['en_examen', 'attribue', 'classe'],
        'en_examen'      => ['attribue', 'en_instruction', 'classe'],
        'attribue'       => ['en_instruction', 'classe'],
        'en_instruction' => ['traite', 'classe'],
        'traite'         => [],
        'classe'         => [],
    ];

    /** Statut vers lequel un superviseur peut rouvrir un dossier clos. */
    public const STATUT_REOUVERTURE = 'en_instruction';

    public function peutPasserA(string $nouveau, User $user): bool
    {
        if ($nouveau === $this->statut) {
            return true;
        }

        if (in_array($this->statut, ['traite', 'classe'], true)) {
            return $user->role === 'superviseur' && $nouveau === self::STATUT_REOUVERTURE;
        }

        return in_array($nouveau, self::TRANSITIONS[$this->statut] ?? [], true);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function preuves()
    {
        return $this->hasMany(Preuve::class);
    }

    public function historiques()
    {
        return $this->hasMany(SignalementHistorique::class)->orderBy('created_at', 'asc');
    }

    public function scopeForAgent($query, User $user)
    {
        if ($user->role === 'agent') {
            return $query->where('agent_id', $user->id);
        }
        return $query;
    }
}
