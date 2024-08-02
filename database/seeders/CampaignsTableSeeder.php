<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campaign;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CampaignsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Fetch some sample projects and users
        $projects = Project::all();
        $users = User::all();

        if ($projects->isEmpty() || $users->isEmpty()) {
            $this->command->info('Please make sure there are projects and users in the database before running this seeder.');
            return;
        }

        // Loop to create several campaigns
        foreach ($projects as $project) {
            Campaign::create([
                'campaign_name' => 'Campaign for ' . $project->project_name,
                'project_code' => $project->project_code,
                'campaign_token' => $this->generateUniqueToken(),
                'created_by' => $users->random()->id,
                'start' => Carbon::now()->subDays(rand(1, 10)),
                'end' => Carbon::now()->addDays(rand(1, 10)),
                'status' => rand(0, 1),
                'reporting' => rand(0, 1),
                'force_lowercase' => rand(0, 1),
                'utm_activated' => rand(0, 1),
                'monitor_urls' => rand(0, 1),
                'description' => 'Description for campaign for project ' . $project->project_name,
            ]);
        }
    }

    /**
     * Generate a unique campaign token.
     *
     * @return string
     */
    private function generateUniqueToken()
    {
        do {
            $token = Str::random(8);
        } while (Campaign::where('campaign_token', $token)->exists());

        return $token;
    }
}
