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
            'project_code' => 'P00000001',
            'project_name' => 'Mcminn',
            'project_domain' => 'mcminn.no',
            'project_language' => 'no_NB',
            'project_country' => 'NO',
            'project_category' => serialize('Brand'),
            'owner_id' => 1,
            'team_id' => 1,
        ]);

        Project::create([
            'project_code' => 'jnSUXqub',
            'project_name' => 'Din Firmabil',
            'project_domain' => 'dinfirmabil.net',
            'project_language' => 'no_NB',
            'project_country' => 'NO',
            'project_category' => serialize('Brand'),
            'owner_id' => 1,
            'team_id' => 3,
        ]);

        Project::create([
            'project_code' => 'IavCYemn',
            'project_name' => 'Pixel & Co',
            'project_domain' => 'pixelco.no',
            'project_language' => 'no_NB',
            'project_country' => 'NO',
            'project_category' => serialize('Brand'),
            'owner_id' => 1,
            'team_id' => 2,
        ]);
    }
}
