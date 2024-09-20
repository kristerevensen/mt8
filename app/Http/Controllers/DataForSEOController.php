<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\Language;
use App\Models\Project;
use App\Models\SeoTask;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class DataForSEOController extends Controller
{
    /**
     * Fetch locations from DataForSEO and store them in the database.
     */
    public function getLocations()
    {
        $baseUrl = Config::get('dataforseo.base_url');
        $login = Config::get('dataforseo.login');
        $password = Config::get('dataforseo.password');

        $credentials = base64_encode("{$login}:{$password}");

        try {
            // Step 1: Fetch locations from DataForSEO
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Authorization' => "Basic {$credentials}",
                    'Content-Type' => 'application/json',
                ])
                ->get("{$baseUrl}/v3/keywords_data/google_ads/locations");

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch locations');
            }
            //dd($response);
            // Step 2: Prepare data for bulk insertion
            $locations = $response->json('tasks.0.result');

            $data = [];

            foreach ($locations as $location) {
                $data[] = [
                    'location_code' => $location['location_code'],
                    'location_name' => $location['location_name'],
                    'location_code_parent' => $location['location_code_parent'] ?? null,
                    'country_iso_code' => $location['country_iso_code'],
                    'location_type' => $location['location_type'],
                ];
            }

            // Step 3: Clear the existing data in the table
            Location::truncate();

            // Step 4: Bulk insert the new data
            // Use chunks to manage memory usage
            foreach (array_chunk($data, 500) as $chunk) { // Adjust chunk size as needed
                Location::insert($chunk);
            }

            return response()->json(['message' => 'Locations fetched, old records cleared, and new records stored successfully']);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error fetching locations: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch locations'], 500);
        }
    }


    /**
     * Fetch languages from DataForSEO and store them in the database.
     */
    public function getLanguages()
    {
        $baseUrl = Config::get('dataforseo.base_url');
        $login = Config::get('dataforseo.login');
        $password = Config::get('dataforseo.password');

        $credentials = base64_encode("{$login}:{$password}");

        // Assuming there is an endpoint for languages (this is hypothetical)
        $languagesResponse = Http::withOptions([
            'verify' => false,
        ])->withHeaders([
            'Authorization' => "Basic {$credentials}",
            'Content-Type' => 'application/json',
        ])->get("{$baseUrl}/v3/content_analysis/languages"); // Hypothetical endpoint

        if ($languagesResponse->successful()) {
            $languages = $languagesResponse->json('tasks.0.result');
            foreach ($languages as $language) {
                Language::updateOrCreate(
                    ['language_code' => $language['language_code']],
                    ['language_name' => $language['language_name']]
                );
            }
            return response()->json(['message' => 'Languages fetched and stored successfully']);
        } else {
            return response()->json(['error' => 'Failed to fetch languages'], 500);
        }
    }



    public function createSeoTask($projectId)
    {
        $project = Project::findOrFail($projectId);

        $baseUrl = config('dataforseo.url');
        $login = config('dataforseo.login');
        $password = config('dataforseo.password');

        $credentials = base64_encode("{$login}:{$password}");

        $post_array = [
            [
                "location_name" => $project->project_country, // Example, adapt based on your requirements
                "target" => $project->project_domain,
                "tag" => $project->project_code,
                "pingback_url" => 'http://www.measuretank.com/api/pingback' // Adjust this URL based on your setup
            ],
        ];

        //dd($post_array);

        $response = Http::withOptions([
            'verify' => false,
        ])->withHeaders([
            'Authorization' => "Basic {$credentials}",
            'Content-Type' => 'application/json',
        ])->post("{$baseUrl}/v3/keywords_data/google_ads/keywords_for_site/task_post", $post_array);
        dd($response->json());
        if ($response->successful()) {
            $taskId = $response->json('tasks.0.task_id');
            SeoTask::create([
                'project_id' => $projectId,
                'task_id' => $taskId,
                'location_name' => $project->project_country,
                'target' => $project->project_domain,
                'project_code' => $project->project_code,
                "pingback_url" => 'http://www.measuretank.com/api/pingback',
                'status' => 'pending',
            ]);

            return response()->json(['message' => 'Task created successfully']);
        } else {
            return response()->json(['error' => 'Failed to create task'], 500);
        }
    }

    public function handlePingback(Request $request)
    {
        $taskId = $request->query('id');
        $tag = $request->query('tag');

        $task = SeoTask::where('tag', $tag)->first();

        if ($task) {
            $task->status = 'completed';
            $task->result = $request->all(); // Assuming full data is sent
            $task->save();

            return response()->json(['message' => 'Pingback processed']);
        } else {
            return response()->json(['error' => 'Task not found'], 404);
        }
    }
}
