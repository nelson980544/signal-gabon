<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignalementHistorique extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'signalement_id',
        'agent_id',
        'ancien_statut',
        'nouveau_statut',
        'ancien_agent_id',
        'nouveau_agent_id',
        'commentaire',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
