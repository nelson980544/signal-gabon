<?php

namespace App\Http\Controllers;

use App\Models\User;

class AdminAgentController extends Controller
{
    public function index()
    {
        return response()->json(
            User::where('role', 'agent')->where('is_active', true)->get(['id', 'name', 'email'])
        );
    }
}
