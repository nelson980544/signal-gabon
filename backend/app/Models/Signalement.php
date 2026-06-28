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
