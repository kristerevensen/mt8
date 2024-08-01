<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use Illuminate\Support\Str;

class ProjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::create([
            'project_code' => Str::random(8),
            'project_name' => 'Mcminn',
            'project_domain' => 'mcminn.no',
            'project_language' => 'no_NB',
            'project_country' => 'NO',
            'project_category' => 'Brand',
            'owner_id' => 1,
            'team_id' => 1,
        ]);

        Project::create([
            'project_code' => Str::random(8),
            'project_name' => 'Din Firmabil',
            'project_domain' => 'dinfirmabil.net',
            'project_language' => 'no_NB',
            'project_country' => 'NO',
            'project_category' => 'Brand',
            'owner_id' => 1,
            'team_id' => 1,
        ]);

        Project::create([
            'project_code' => Str::random(8),
            'project_name' => 'Pixel & Co',
            'project_domain' => 'pixelco.no',
            'project_language' => 'no_NB',
            'project_country' => 'NO',
            'project_category' => 'Brand',
            'owner_id' => 1,
            'team_id' => 1,
        ]);

        Project::create([
            'project_code' => Str::random(8),
            'project_name' => 'Measuretank',
            'project_domain' => 'measuretank.com',
            'project_language' => 'en_US',
            'project_country' => 'US',
            'project_category' => 'Analytics',
            'owner_id' => 1,
            'team_id' => 1,
        ]);

        Project::create([
            'project_code' => Str::random(8),
            'project_name' => 'TechSolutions',
            'project_domain' => 'techsolutions.io',
            'project_language' => 'en_GB',
            'project_country' => 'GB',
            'project_category' => 'Consulting',
            'owner_id' => 1,
            'team_id' => 1,
        ]);

        Project::create([
            'project_code' => Str::random(8),
            'project_name' => 'GlobalReach',
            'project_domain' => 'globalreach.org',
            'project_language' => 'fr_FR',
            'project_country' => 'FR',
            'project_category' => 'Nonprofit',
            'owner_id' => 1,
            'team_id' => 1,
        ]);

        Project::create([
            'project_code' => Str::random(8),
            'project_name' => 'EduLearn',
            'project_domain' => 'edulearn.edu',
            'project_language' => 'es_ES',
            'project_country' => 'ES',
            'project_category' => 'Education',
            'owner_id' => 1,
            'team_id' => 1,
        ]);
    }
}
