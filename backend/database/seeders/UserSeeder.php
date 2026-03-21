<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Directeur Général', 'email' => 'superviseur@cnlcei.ga', 'password' => Hash::make('Admin1234!'), 'role' => 'superviseur', 'is_active' => true],
            ['name' => 'Jean-Baptiste Mboumba', 'email' => 'jean.mboumba@cnlcei.ga', 'password' => Hash::make('Agent1234!'), 'role' => 'agent', 'is_active' => true],
            ['name' => 'Marie-Claire Ondo', 'email' => 'marie.ondo@cnlcei.ga', 'password' => Hash::make('Agent1234!'), 'role' => 'agent', 'is_active' => true],
            ['name' => 'Pierre Nzoghe', 'email' => 'pierre.nzoghe@cnlcei.ga', 'password' => Hash::make('Agent1234!'), 'role' => 'agent', 'is_active' => true],
            ['name' => 'Sylvie Bekale', 'email' => 'sylvie.bekale@cnlcei.ga', 'password' => Hash::make('Agent1234!'), 'role' => 'agent', 'is_active' => true],
            ['name' => 'Antoine Moussavou', 'email' => 'antoine.moussavou@cnlcei.ga', 'password' => Hash::make('Agent1234!'), 'role' => 'agent', 'is_active' => true],
        ];
        foreach ($users as $u) {
            User::create($u);
        }
    }
}
