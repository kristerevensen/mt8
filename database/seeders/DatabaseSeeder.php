<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run all the seeders
        $this->call([
            UserSeeder::class,
            TeamsTableSeeder::class,
            ProjectsTableSeeder::class,
            CampaignsTableSeeder::class,
            CampaignLinksTableSeeder::class,
            CampaignLinkClicksTableSeeder::class,
        ]);
    }
}
