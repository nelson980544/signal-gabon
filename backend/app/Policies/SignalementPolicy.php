<?php

namespace App\Policies;

use App\Models\Signalement;
use App\Models\User;

class SignalementPolicy
{
    public function view(User $user, Signalement $signalement): bool
    {
        return $user->role === 'superviseur' || $signalement->agent_id === $user->id;
    }

    public function update(User $user, Signalement $signalement): bool
    {
        return $user->role === 'superviseur' || $signalement->agent_id === $user->id;
    }
}
