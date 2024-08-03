<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CampaignLink;
use App\Models\CampaignLinkClick;
use Faker\Factory as Faker;

class CampaignLinkClicksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Fetch all existing campaign links
        $links = CampaignLink::all();

        if ($links->isEmpty()) {
            $this->command->info('Please make sure there are campaign links in the database before running this seeder.');
            return;
        }

        // Create fake clicks for each campaign link
        foreach ($links as $link) {
            // Generate a random number of clicks for each link
            for ($i = 0; $i < rand(1, 10); $i++) {
                CampaignLinkClick::create([
                    'user_agent' => $faker->userAgent,
                    'referrer' => $faker->url,
                    'ip' => $faker->ipv4,
                    'platform' => $faker->randomElement(['Windows', 'Mac', 'Linux']),
                    'browser' => $faker->randomElement(['Chrome', 'Firefox', 'Safari']),
                    'device_type' => $faker->randomElement(['desktop', 'mobile', 'tablet']),
                    'screen_resolution' => $faker->randomElement(['1920x1080', '1280x720', '2560x1440']),
                    'language' => $faker->languageCode,
                    'session_id' => $faker->uuid,
                    'link_token' => $link->link_token,
                ]);
            }
        }
    }
}
