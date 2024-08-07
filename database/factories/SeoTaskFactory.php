<?php

namespace Database\Factories;

use App\Models\SeoTask;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeoTaskFactory extends Factory
{
    protected $model = SeoTask::class;

    public function definition()
    {
        return [
            'project_code' => 'P00000001', // Assumes you have a Project factory
            'location_name' => 'NO',
            'target' => $this->faker->url,
            'tag' => $this->faker->word,
            'pingback_url' => $this->faker->url,
            'postback_url' => $this->faker->url,
            'status' => 'pending',
            'result' => [],
        ];
    }
}
