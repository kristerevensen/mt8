<?php

namespace App\Http\Controllers;

use App\Models\Keyword;
use App\Models\KeywordList;
use App\Models\KeywordSearchVolume;
use App\Models\LanguageLocation;
use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\Language;
use App\Models\Project;
use App\Models\SeoTask;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DataForSEOController extends Controller
{
    /**
     * Fetch locations from DataForSEO and store them in the database.
     */
    public function getLocations()
    {
        ini_set('memory_limit', '256M'); // Øk minnegrensen hvis nødvendig

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
            Log::error('Error fetching locations: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch locations'], 500);
        }
    }
    /**
     * Fetch languages and locations from DataForSEO and store them in the database.
     * Database table: language_location
     *  $result = $client->get('/v3/dataforseo_labs/locations_and_languages');
     *
     */
    public function getLanguagesAndLocations()
    {
        $login = Config::get('dataforseo.login');
        $password = Config::get('dataforseo.password');
        $baseUrl = Config::get('dataforseo.base_url');

        $credentials = base64_encode("{$login}:{$password}");

        try {
            // Gjør API-kall til DataForSEO
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Authorization' => "Basic {$credentials}",
                    'Content-Type' => 'application/json',
                ])
                ->get("{$baseUrl}/v3/dataforseo_labs/locations_and_languages");
            //dd($response->json());
            // Sjekk om API-kallet var vellykket
            if ($response->successful()) {
                $data = $response->json();

                // Truncate tabellen før nye data legges inn
                LanguageLocation::truncate();

                // Sjekk om oppgaven er vellykket og har resultater
                if (isset($data['tasks']) && count($data['tasks']) > 0) {
                    foreach ($data['tasks'][0]['result'] as $location) {
                        // Iterer gjennom hver tilgjengelig lokasjon
                        foreach ($location['available_languages'] as $language) {
                            // Lagre eller oppdaterer språk- og lokasjonsdata i databasen
                            LanguageLocation::updateOrCreate(
                                [
                                    'location_code' => $location['location_code'],
                                    'language_code' => $language['language_code'],
                                ],
                                [
                                    'location_name' => $location['location_name'],
                                    'location_code_parent' => $location['location_code_parent'],
                                    'country_iso_code' => $location['country_iso_code'],
                                    'location_type' => $location['location_type'],
                                    'language_name' => $language['language_name'],
                                    'language_code' => $language['language_code'],
                                ]
                            );
                        }
                    }

                    return response()->json(['message' => 'Languages and locations fetched and stored successfully']);
                } else {
                    return response()->json(['error' => 'No tasks or results found in the response'], 500);
                }
            } else {
                // Returner feilmelding hvis API-kallet mislykkes
                return response()->json(['error' => 'Failed to fetch data from API'], 500);
            }
        } catch (\Exception $e) {
            // Logg feil og returner feilmelding
            Log::error('Error fetching languages and locations: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching data'], 500);
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

    public function getWebsiteKeywords($projectId)
    {
        try {
            $project = Project::findOrFail($projectId);

            // Check if keywords for the project have already been collected this month.
            $task = SeoTask::where('project_code', $project->project_code)
                ->where('tag', 'Website Keywords')
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($task && $task->created_at->isCurrentMonth()) {
                // If keywords have already been fetched this month, use the existing data.
                $results = $task->result;
            } else {
                // Fetch new data from DataForSEO.
                $results = $this->fetchKeywordsFromDataForSEO($project);

                if (!$results) {
                    return response()->json(['error' => 'Failed to fetch keywords from DataForSEO'], 500);
                }

                // Store the new task in the SeoTask table.
                SeoTask::create([
                    'project_id' => $projectId,
                    'task_id' => $results['task_id'],
                    'location_code' => $project->project_location_code,
                    'location_name' => $project->project_country,
                    'target' => $project->project_domain,
                    'project_code' => $project->project_code,
                    'result' => $results['data'],
                    'tag' => 'Website Keywords',
                    'status' => 'completed',
                ]);
                $results = $results['data'];
            }

            // Find or create the "Website" keyword list.
            $websiteKeywordList = KeywordList::firstOrCreate(
                ['name' => 'Website', 'project_code' => $project->project_code],
                ['list_uuid' => Str::uuid(), 'description' => 'Keywords for website']
            );

            $listUuid = $websiteKeywordList->list_uuid;

            // Store keywords and search volume data.
            $this->storeKeywordsAndVolumes($results, $listUuid, $project->project_code);

            return response()->json(['message' => 'Task processed successfully, keywords saved and assigned to Website list']);
        } catch (\Exception $e) {
            Log::error("Error processing website keywords: {$e->getMessage()}");
            return response()->json(['error' => 'Failed to process website keywords'], 500);
        }
    }

    /**
     * Fetch keywords from DataForSEO.
     *
     * @param Project $project
     * @return array|bool
     */
    private function fetchKeywordsFromDataForSEO(Project $project)
    {
        try {
            $baseUrl = config('dataforseo.url');
            $login = config('dataforseo.login');
            $password = config('dataforseo.password');
            $credentials = base64_encode("{$login}:{$password}");

            $projectDomain = preg_replace('#^https?://#', '', $project->project_domain);
            $projectDomain = parse_url($projectDomain, PHP_URL_HOST) ?? $projectDomain;

            $post_array = [
                [
                    "location_code" => $project->project_location_code,
                    "target" => $projectDomain,
                    'target_type' => 'site',
                    'tag' => 'Website Keywords',
                ],
            ];

            // Send request to DataForSEO.
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Authorization' => "Basic {$credentials}",
                    'Content-Type' => 'application/json',
                ])->post("{$baseUrl}/v3/keywords_data/google_ads/keywords_for_site/live", $post_array);

            if ($response->successful()) {
                $taskId = $response->json('tasks.0.id');
                $results = $response->json('tasks.0.result');
                return ['task_id' => $taskId, 'data' => $results];
            } else {
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Error fetching keywords from DataForSEO: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Store keywords and their respective search volumes.
     *
     * @param array $results
     * @param string $listUuid
     * @param string $projectCode
     */
    private function storeKeywordsAndVolumes(array $results, string $listUuid, string $projectCode)
    {
        try {
            foreach ($results as $item) {
                // Use a database transaction to maintain integrity.
                DB::transaction(function () use ($item, $listUuid, $projectCode) {
                    // Check if the keyword already exists.
                    $existingKeyword = Keyword::where('keyword', $item['keyword'])
                        ->where('project_code', $projectCode)
                        ->first();

                    // Determine whether to update or create the keyword.
                    $keyword = $existingKeyword ? $this->updateKeyword($existingKeyword, $item, $listUuid) : $this->createKeyword($item, $listUuid, $projectCode);

                    // Store monthly search volumes.
                    if ($keyword && isset($item['monthly_searches'])) {
                        $this->storeKeywordSearchVolumes($keyword->keyword_uuid, $item['monthly_searches'], $projectCode);
                    }
                });
            }
        } catch (\Exception $e) {
            Log::error("Error storing keywords and volumes: {$e->getMessage()}");
        }
    }

    /**
     * Update existing keyword with new data.
     *
     * @param Keyword $existingKeyword
     * @param array $item
     * @param string $listUuid
     * @return Keyword
     */
    private function updateKeyword(Keyword $existingKeyword, array $item, string $listUuid): Keyword
    {
        // Legg til logging for å se verdien av `listUuid` og om `update`-operasjonen blir kalt.
        Log::info("Updating keyword {$existingKeyword->keyword_uuid} with list_uuid: {$listUuid}");

        // Sørg for at `list_uuid` blir satt før oppdatering
        $existingKeyword->list_uuid = $listUuid;
        $existingKeyword->spell = $item['spell'] ?? null;
        $existingKeyword->location_code = $item['location_code'] ?? null;
        $existingKeyword->language_code = $item['language_code'] ?? null;
        $existingKeyword->search_partners = $item['search_partners'] ?? false;
        $existingKeyword->competition = $item['competition'] ?? 'LOW';
        $existingKeyword->competition_index = $item['competition_index'] ?? 0;
        $existingKeyword->search_volume = $item['search_volume'] ?? 0;
        $existingKeyword->low_top_of_page_bid = $item['low_top_of_page_bid'] ?? 0.0;
        $existingKeyword->high_top_of_page_bid = $item['high_top_of_page_bid'] ?? 0.0;
        $existingKeyword->cpc = $item['cpc'] ?? 0.0;
        $existingKeyword->analyzed_at = now();

        // Lagre endringene med `save()`-metoden
        $existingKeyword->save();

        Log::info("Keyword {$existingKeyword->keyword_uuid} updated successfully with list_uuid: {$existingKeyword->list_uuid}");

        return $existingKeyword;
    }

    /**
     * Create a new keyword.
     *
     * @param array $item
     * @param string $listUuid
     * @param string $projectCode
     * @return Keyword
     */
    private function createKeyword(array $item, string $listUuid, string $projectCode): Keyword
    {
        $keyword = Keyword::create([
            'keyword_uuid' => Str::uuid(),
            'keyword' => $item['keyword'],
            'list_uuid' => $listUuid, // Sørg for at list_uuid er med her
            'spell' => $item['spell'] ?? null,
            'location_code' => $item['location_code'] ?? null,
            'language_code' => $item['language_code'] ?? null,
            'search_partners' => $item['search_partners'] ?? false,
            'competition' => $item['competition'] ?? 'LOW',
            'competition_index' => $item['competition_index'] ?? 0,
            'search_volume' => $item['search_volume'] ?? 0,
            'low_top_of_page_bid' => $item['low_top_of_page_bid'] ?? 0.0,
            'high_top_of_page_bid' => $item['high_top_of_page_bid'] ?? 0.0,
            'cpc' => $item['cpc'] ?? 0.0,
            'project_code' => $projectCode,
            'analyzed_at' => now(),
        ]);

        // Log the creation of a new keyword
        Log::info("Created new keyword with UUID: {$keyword->keyword_uuid} and list_uuid: {$listUuid}");

        return $keyword;
    }


    /**
     * Store or update keyword search volumes.
     *
     * @param string $keywordUuid
     * @param array $monthlySearches
     * @param string $projectCode
     */
    private function storeKeywordSearchVolumes(string $keywordUuid, array $monthlySearches, string $projectCode)
    {
        foreach ($monthlySearches as $searchData) {
            KeywordSearchVolume::updateOrCreate(
                [
                    'keyword_uuid' => $keywordUuid,
                    'project_code' => $projectCode,
                    'year' => $searchData['year'],
                    'month' => $searchData['month'],
                ],
                [
                    'search_volume' => $searchData['search_volume'],
                ]
            );
        }
    }



    public function handlePingback(Request $request)
    {
        //  $taskId = $request->query('id');
        // the postback and pingback will send either post or get to the pingback url or postback url. And needs to collect the id from the request
        $taskId = $request->query('id') ?? $request->input('id');

        if (!$taskId) {
            return response()->json(['error' => 'Task ID not provided'], 400);
        }
        // check to see if the task exists, and update the specific task based on task_id. Leave the tags
        $task = SeoTask::where('task_id', $taskId)->first();

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
