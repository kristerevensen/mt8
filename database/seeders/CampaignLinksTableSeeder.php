<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campaign;
use App\Models\CampaignLink;
use Illuminate\Support\Str;

class CampaignLinksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch all existing campaigns
        $campaigns = Campaign::all();

        if ($campaigns->isEmpty()) {
            $this->command->info('Please make sure there are campaigns in the database before running this seeder.');
            return;
        }

        // Create links for each campaign
        foreach ($campaigns as $campaign) {
            // Create multiple links for each campaign
            for ($i = 0; $i < rand(1, 5); $i++) {
                CampaignLink::create([
                    'landing_page' => 'https://example.com/landing-page-' . Str::random(5),
                    'link_token' => Str::random(8), // Unique token for each link
                    'project_code' => $campaign->project_code,
                    'source' => 'Google',
                    'medium' => 'CPC',
                    'term' => 'keyword' . $i,
                    'content' => 'content-' . $i,
                    'target' => 'https://example.com/target-page-' . Str::random(5),
                    'tagged_url' => 'https://example.com/tagged-url-' . Str::random(5),
                    'campaign_id' => $campaign->id,
                    'custom_parameters' => json_encode(['custom_param' => 'value']),
                    'description' => 'Description for link ' . $i,
                ]);
            }
        }
    }
}
