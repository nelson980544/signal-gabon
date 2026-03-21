<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed uniquement si aucun utilisateur n'existe (évite les doublons en production)
        if (User::count() === 0) {
            $this->call([UserSeeder::class, SignalementSeeder::class]);
        }
    }
}
