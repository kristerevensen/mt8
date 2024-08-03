<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Team;


class TeamsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Team::create([
            'name' => 'Mcminn Team',
            'user_id' => 1, // Bruker ID fra UsersTableSeeder
            'personal_team' => true,
        ]);

        Team::create([
            'name' => 'Pixel & co Team',
            'user_id' => 1, // Bruker ID fra UsersTableSeeder
            'personal_team' => true,
        ]);

        Team::create([
            'name' => 'Din firmabil Team',
            'user_id' => 1, // Bruker ID fra UsersTableSeeder
            'personal_team' => true,
        ]);
    }
}
