<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // Sans ce trait, $this->authorize() n'existe pas et toute route qui
    // l'appelle répond 500 (détail et mise à jour d'un signalement).
    use AuthorizesRequests;
}
