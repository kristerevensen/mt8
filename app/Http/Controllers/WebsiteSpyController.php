<?php

namespace App\Http\Controllers;

use App\Jobs\AnalyzeTechnologyJob;
use App\Jobs\AnalyzeWebsiteJob;
use App\Models\Project;
use App\Models\WebsiteAnalysis;
use App\Models\WebsiteSpyTechnologies;
use App\Models\WebsiteSpyMetadata;
use App\Models\WebsiteHistoricalRank;
use App\Models\WebsiteKeyword;
use App\Services\DataForSEOService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use Inertia\Inertia;

class WebsiteSpyController extends Controller
{

    public function __construct() {
        $this->dataForSEO = new DataForSEOService(); // Initialize the property with an appropriate instance
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Fetch all website analyses with pagination
        $websiteSpyRequests = WebsiteAnalysis::where('project_code', Project::where('team_id', Auth::user()->current_team_id)->first()->project_code)
            ->when($request->search, function ($query, $search) {
                return $query->where('url', 'like', '%' . $search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Fetch unique locations from language_location table
        $locations = DB::table('language_location')
            ->select('location_code', 'location_name', 'country_iso_code')
            ->distinct()
            ->orderBy('location_name')
            ->get()
            ->map(function ($location) {
                // Get available languages for this location
                $languages = DB::table('language_location')
                    ->select('language_code', 'language_name')
                    ->where('location_code', $location->location_code)
                    ->distinct()
                    ->orderBy('language_name')
                    ->get();

                return [
                    'id' => $location->location_code,
                    'location_code' => $location->location_code,
                    'location_name' => $location->location_name,
                    'country_iso_code' => $location->country_iso_code,
                    'available_languages' => $languages->map(function ($language) {
                        return [
                            'id' => $language->language_code,
                            'language_code' => $language->language_code,
                            'language_name' => $language->language_name
                        ];
                    })->values()
                ];
            })->values();

        Log::info('Data loaded', [
            'locations_count' => $locations->count(),
            'first_location' => $locations->first(),
            'first_location_languages' => $locations->first() ? $locations->first()['available_languages'] : null
        ]);

        // Fetch unique languages from language_location table
        $languages = DB::table('language_location')
            ->select('language_code', 'language_name')
            ->distinct()
            ->orderBy('language_name')
            ->get()
            ->map(function ($language) {
                return [
                    'id' => $language->language_code,
                    'language_code' => $language->language_code,
                    'language_name' => $language->language_name
                ];
            });

        Log::info('Data loaded', [
            'locations_count' => $locations->count(),
            'languages_count' => $languages->count(),
            'first_location' => $locations->first(),
            'first_language' => $languages->first(),
        ]);

        // Pass data to Inertia for the frontend
        return Inertia::render('Spy/Index', [
            'websiteSpyRequests' => $websiteSpyRequests,
            'languages' => $languages,
            'locations' => $locations,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * This method is used to spy on a website and get several resources about a website
     */
    public function spy(Request $request)
    {
        $data = $request->input('data', []);
        $target = $data['target'] ?? $request->target;

        // Check to see if the url has an ending slash, if so, remove it
        if (substr($target, -1) === '/') {
            $target = substr($target, 0, -1);
        }

        //strip target of https://
        $target = str_replace('https://', '', $target);
        $locationCode = $data['location_code'] ?? $request->location_code;
        $languageCode = $data['language_code'] ?? $request->language_code;

        try {
            // Validate required fields
            if (empty($locationCode) || $locationCode === "0" || 
                empty($languageCode) || $languageCode === "null") {
                throw new Exception('Location code and language code are required.');
            }

            // Validate that location and language exist in database
            $validLocation = DB::table('language_location')
                ->where('location_code', $locationCode)
                ->where('language_code', $languageCode)
                ->exists();

            if (!$validLocation) {
                throw new Exception('Invalid location code or language code combination.');
            }

            // Generate UUID for this spy operation
            $uuid = (string) Str::uuid();
            
            // Get project code
            $project = Project::where('team_id', Auth::user()->current_team_id)->first();
            
            if (!$project) {
                throw new Exception('No project found for current team');
            }

            // Create website analysis record
            $analysis = WebsiteAnalysis::create([
                'uuid' => $uuid,
                'project_code' => $project->project_code,
                'url' => $target,
                'location_code' => $locationCode,
                'language_code' => $languageCode,
                'status' => 'pending'
            ]);

            // Get technologies directly
            $dataForSEO = app(DataForSEOService::class);
            $techData = $dataForSEO->getTechnologies($target);
            
            \Log::info('Raw technology data received', [
                'url' => $target,
                'data_structure' => array_keys($techData),
                'technologies' => $techData['technologies'] ?? null,
                'full_data' => $techData
            ]);

            $this->processWebsiteTechnologies($analysis, $techData);

            // Store metadata
            if (!empty($techData)) {
                WebsiteSpyMetadata::create([
                    'uuid' => $analysis->uuid,
                    'project_code' => $analysis->project_code,
                    'website_analysis_id' => $analysis->id,
                    'title' => $techData['title'] ?? null,
                    'description' => $techData['description'] ?? null,
                    'meta_keywords' => $techData['meta_keywords'] ?? null,
                    'phone_numbers' => $techData['phone_numbers'] ?? [],
                    'email_addresses' => $techData['email_addresses'] ?? [],
                    'social_media_urls' => $techData['social_media_urls'] ?? [],
                    'country_iso_code' => $techData['country_iso_code'] ?? null,
                    'language_code' => $techData['language_code'] ?? null,
                ]);

                // Store additional data
                $analysis->domain_rank = $techData['domain_rank'] ?? null;
                $analysis->last_visited = $techData['last_visited'] ?? null;
                $analysis->content_language = $techData['content_language'] ?? null;
                $analysis->save();
            }

            // Get and process historical ranks
            $rankData = $dataForSEO->getHistoricalRanks($target, $locationCode, $languageCode);
            
            \Log::info('Historical rank data received', [
                'url' => $target,
                'location_code' => $locationCode,
                'language_code' => $languageCode,
                'data_count' => count($rankData),
                'sample_data' => array_slice($rankData, 0, 2)
            ]);
            
            if (!empty($rankData)) {
                $this->processHistoricalRanks($analysis, $rankData);
            }

            // Get and process ranked keywords
            $keywordData = $dataForSEO->getRankedKeywords($target, $locationCode, $languageCode);
            
            \Log::info('Ranked keywords data received', [
                'url' => $target,
                'data_count' => count($keywordData)
            ]);
            
            if (!empty($keywordData)) {
                $this->processRankedKeywords($analysis, $keywordData);
            }

            // Get and process competitors using existing functionality
            $competitorsRequest = new Request([
                'target' => $target,
                'location_code' => $locationCode,
                'project_code' => $project->project_code
            ]);
            
            $this->getCompetitors($competitorsRequest, $analysis->uuid);
        

            $analysis->status = 'completed';
            $analysis->save();

            return redirect()->route('website-spy.show', ['uuid' => $uuid])
                           ->with('success', 'Analysis completed successfully');

        } catch (Exception $e) {
            Log::error('Website spy failed', [
                'error' => $e->getMessage(),
                'target' => $target ?? null,
                'location_code' => $locationCode ?? null,
                'language_code' => $languageCode ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            if (isset($analysis)) {
                $analysis->status = 'failed';
                $analysis->save();
            }

            return redirect()->route('website-spy.index')
                           ->with('error', 'Failed to analyze website: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }
    /**
     * Display the specified resource.
     */
    public function show($uuid)
    {
        $analysis = WebsiteAnalysis::where('uuid', $uuid)
            ->with([
                'technologies', 
                'competitors', 
                'metadata' => function($query) {
                    $query->whereNotNull('title')
                          ->orWhereNotNull('description')
                          ->orWhereNotNull('meta_keywords')
                          ->orWhereNotNull('phone_numbers')
                          ->orWhereNotNull('email_addresses')
                          ->orWhereNotNull('social_media_urls');
                },
                'historicalRanks',
                'keywords'
            ])
            ->firstOrFail();

        Log::info('Analysis data', [
            'uuid' => $analysis->uuid,
            'historical_ranks_count' => $analysis->historicalRanks->count(),
            'historical_ranks_sample' => $analysis->historicalRanks->take(2)->toArray(),
            'keywords_count' => $analysis->keywords->count()
        ]);

        // Check if screenshot exists and is stored
        if ($analysis->screenshot_path && Storage::disk('public')->exists($analysis->screenshot_path)) {
            $analysis->screenshot_url = Storage::disk('public')->url($analysis->screenshot_path);
        }

        // Format data for frontend
        $formattedAnalysis = [
            'uuid' => $analysis->uuid,
            'url' => $analysis->url,
            'status' => $analysis->status,
            'error_message' => $analysis->error_message,
            'created_at' => $analysis->created_at,
            'updated_at' => $analysis->updated_at,
            'metrics' => $analysis->metrics ? (is_string($analysis->metrics) ? json_decode($analysis->metrics) : $analysis->metrics) : null,
            'screenshot_url' => $analysis->screenshot_url ?? null,
            'screenshot_path' => $analysis->screenshot_path,
            'domain_rank' => $analysis->domain_rank,
            'last_visited' => $analysis->last_visited,
            'metadata' => $analysis->metadata ? [
                'title' => $analysis->metadata->title,
                'description' => $analysis->metadata->description,
                'meta_keywords' => $analysis->metadata->meta_keywords,
                'phone_numbers' => is_string($analysis->metadata->phone_numbers) ? json_decode($analysis->metadata->phone_numbers) : $analysis->metadata->phone_numbers,
                'email_addresses' => is_string($analysis->metadata->email_addresses) ? json_decode($analysis->metadata->email_addresses) : $analysis->metadata->email_addresses,
                'social_media_urls' => is_string($analysis->metadata->social_media_urls) ? json_decode($analysis->metadata->social_media_urls) : $analysis->metadata->social_media_urls,
                'country_iso_code' => $analysis->metadata->country_iso_code,
                'language_code' => $analysis->metadata->language_code,
            ] : null,
            'historical_ranks' => $analysis->historicalRanks->map(function ($rank) {
                // Calculate total rank from position data
                $totalRank = 0;
                if ($rank->organic_pos_1) $totalRank += $rank->organic_pos_1;
                if ($rank->organic_pos_2_3) $totalRank += $rank->organic_pos_2_3;
                if ($rank->organic_pos_4_10) $totalRank += $rank->organic_pos_4_10;
                if ($rank->organic_pos_11_20) $totalRank += $rank->organic_pos_11_20;
                if ($rank->organic_pos_21_30) $totalRank += $rank->organic_pos_21_30;
                
                // Create date from year and month
                $date = $rank->year && $rank->month ? 
                    Carbon::createFromDate($rank->year, $rank->month, 1)->format('Y-m-d') : 
                    null;
                
                return [
                    'date' => $date,
                    'rank' => $totalRank,
                    'se_type' => 'organic'
                ];
            })->filter(function ($rank) {
                // Filter out entries with no date or rank
                return $rank['date'] !== null && $rank['rank'] > 0;
            })->values(),
            'competitors' => $analysis->competitors->map(function ($competitor) {
                $metrics = is_string($competitor->metrics) ? 
                    json_decode($competitor->metrics, true) : 
                    $competitor->metrics;
                    
                return [
                    'id' => $competitor->id,
                    'competitor_domain' => $competitor->competitor_domain,
                    'avg_position' => (float)$competitor->avg_position,
                    'intersections' => (int)$competitor->intersections,
                    'location_code' => $competitor->location_code,
                    'se_type' => $competitor->se_type,
                    'metrics' => [
                        'organic' => [
                            'etv' => (float)($metrics['organic']['etv'] ?? 0),
                            'count' => (int)($metrics['organic']['count'] ?? 0),
                            'is_up' => (int)($metrics['organic']['is_up'] ?? 0),
                            'is_new' => (int)($metrics['organic']['is_new'] ?? 0),
                            'is_down' => (int)($metrics['organic']['is_down'] ?? 0),
                            'is_lost' => (int)($metrics['organic']['is_lost'] ?? 0),
                            'pos_1' => (int)($metrics['organic']['pos_1'] ?? 0),
                            'pos_2_3' => (int)($metrics['organic']['pos_2_3'] ?? 0),
                            'pos_4_10' => (int)($metrics['organic']['pos_4_10'] ?? 0),
                            'pos_11_20' => (int)($metrics['organic']['pos_11_20'] ?? 0),
                            'pos_21_30' => (int)($metrics['organic']['pos_21_30'] ?? 0),
                            'pos_31_40' => (int)($metrics['organic']['pos_31_40'] ?? 0),
                            'pos_41_50' => (int)($metrics['organic']['pos_41_50'] ?? 0),
                            'pos_51_60' => (int)($metrics['organic']['pos_51_60'] ?? 0),
                            'pos_61_70' => (int)($metrics['organic']['pos_61_70'] ?? 0),
                            'pos_71_80' => (int)($metrics['organic']['pos_71_80'] ?? 0),
                            'pos_81_90' => (int)($metrics['organic']['pos_81_90'] ?? 0),
                            'pos_91_100' => (int)($metrics['organic']['pos_91_100'] ?? 0),
                            'impressions_etv' => (float)($metrics['organic']['impressions_etv'] ?? 0),
                            'estimated_paid_traffic_cost' => (float)($metrics['organic']['estimated_paid_traffic_cost'] ?? 0)
                        ],
                        'paid' => [
                            'etv' => (float)($metrics['paid']['etv'] ?? 0),
                            'count' => (int)($metrics['paid']['count'] ?? 0),
                            'is_up' => (int)($metrics['paid']['is_up'] ?? 0),
                            'is_new' => (int)($metrics['paid']['is_new'] ?? 0),
                            'is_down' => (int)($metrics['paid']['is_down'] ?? 0),
                            'is_lost' => (int)($metrics['paid']['is_lost'] ?? 0),
                            'pos_1' => (int)($metrics['paid']['pos_1'] ?? 0),
                            'pos_2_3' => (int)($metrics['paid']['pos_2_3'] ?? 0),
                            'pos_4_10' => (int)($metrics['paid']['pos_4_10'] ?? 0),
                            'pos_11_20' => (int)($metrics['paid']['pos_11_20'] ?? 0),
                            'pos_21_30' => (int)($metrics['paid']['pos_21_30'] ?? 0),
                            'pos_31_40' => (int)($metrics['paid']['pos_31_40'] ?? 0),
                            'pos_41_50' => (int)($metrics['paid']['pos_41_50'] ?? 0),
                            'pos_51_60' => (int)($metrics['paid']['pos_51_60'] ?? 0),
                            'pos_61_70' => (int)($metrics['paid']['pos_61_70'] ?? 0),
                            'pos_71_80' => (int)($metrics['paid']['pos_71_80'] ?? 0),
                            'pos_81_90' => (int)($metrics['paid']['pos_81_90'] ?? 0),
                            'pos_91_100' => (int)($metrics['paid']['pos_91_100'] ?? 0),
                            'impressions_etv' => (float)($metrics['paid']['impressions_etv'] ?? 0),
                            'estimated_paid_traffic_cost' => (float)($metrics['paid']['estimated_paid_traffic_cost'] ?? 0)
                        ]
                    ]
                ];
            })->values(),
            'technologies' => $analysis->technologies->map(function ($tech) {
                return [
                    'name' => $tech->name,
                    'version' => $tech->version,
                    'icon' => $tech->icon,
                    'website' => $tech->website,
                    'categories' => $tech->categories ? (is_string($tech->categories) ? json_decode($tech->categories) : $tech->categories) : []
                ];
            })->values(),
            'keywords' => $analysis->keywords->map(function ($keyword) {
                return [
                    'keyword' => $keyword->keyword,
                    'rank_group' => (int)$keyword->rank_group,
                    'rank_absolute' => (int)$keyword->rank_absolute,
                    'position' => (int)$keyword->position,
                    'type' => $keyword->type,
                    'is_featured_snippet' => (bool)$keyword->is_featured_snippet,
                    'search_volume' => (int)$keyword->search_volume,
                    'competition' => (float)$keyword->competition,
                    'competition_level' => $keyword->competition_level,
                    'cpc' => (float)$keyword->cpc,
                    'monthly_searches' => is_string($keyword->monthly_searches) ? 
                        json_decode($keyword->monthly_searches, true) : 
                        $keyword->monthly_searches,
                    'traffic_cost' => (float)$keyword->traffic_cost,
                ];
            })->values(),
            'competitors' => $analysis->competitors->map(function ($competitor) {
                $metrics = is_string($competitor->metrics) ? 
                    json_decode($competitor->metrics, true) : 
                    $competitor->metrics;
                    
                return [
                    'id' => $competitor->id,
                    'competitor_domain' => $competitor->competitor_domain,
                    'avg_position' => (float)$competitor->avg_position,
                    'intersections' => (int)$competitor->intersections,
                    'location_code' => $competitor->location_code,
                    'se_type' => $competitor->se_type,
                    'metrics' => [
                        'organic' => [
                            'etv' => (float)($metrics['organic']['etv'] ?? 0),
                            'count' => (int)($metrics['organic']['count'] ?? 0),
                            'is_up' => (int)($metrics['organic']['is_up'] ?? 0),
                            'is_new' => (int)($metrics['organic']['is_new'] ?? 0),
                            'is_down' => (int)($metrics['organic']['is_down'] ?? 0),
                            'is_lost' => (int)($metrics['organic']['is_lost'] ?? 0),
                            'pos_1' => (int)($metrics['organic']['pos_1'] ?? 0),
                            'pos_2_3' => (int)($metrics['organic']['pos_2_3'] ?? 0),
                            'pos_4_10' => (int)($metrics['organic']['pos_4_10'] ?? 0),
                            'pos_11_20' => (int)($metrics['organic']['pos_11_20'] ?? 0),
                            'pos_21_30' => (int)($metrics['organic']['pos_21_30'] ?? 0),
                            'pos_31_40' => (int)($metrics['organic']['pos_31_40'] ?? 0),
                            'pos_41_50' => (int)($metrics['organic']['pos_41_50'] ?? 0),
                            'pos_51_60' => (int)($metrics['organic']['pos_51_60'] ?? 0),
                            'pos_61_70' => (int)($metrics['organic']['pos_61_70'] ?? 0),
                            'pos_71_80' => (int)($metrics['organic']['pos_71_80'] ?? 0),
                            'pos_81_90' => (int)($metrics['organic']['pos_81_90'] ?? 0),
                            'pos_91_100' => (int)($metrics['organic']['pos_91_100'] ?? 0),
                            'impressions_etv' => (float)($metrics['organic']['impressions_etv'] ?? 0),
                            'estimated_paid_traffic_cost' => (float)($metrics['organic']['estimated_paid_traffic_cost'] ?? 0)
                        ],
                        'paid' => [
                            'etv' => (float)($metrics['paid']['etv'] ?? 0),
                            'count' => (int)($metrics['paid']['count'] ?? 0),
                            'is_up' => (int)($metrics['paid']['is_up'] ?? 0),
                            'is_new' => (int)($metrics['paid']['is_new'] ?? 0),
                            'is_down' => (int)($metrics['paid']['is_down'] ?? 0),
                            'is_lost' => (int)($metrics['paid']['is_lost'] ?? 0),
                            'pos_1' => (int)($metrics['paid']['pos_1'] ?? 0),
                            'pos_2_3' => (int)($metrics['paid']['pos_2_3'] ?? 0),
                            'pos_4_10' => (int)($metrics['paid']['pos_4_10'] ?? 0),
                            'pos_11_20' => (int)($metrics['paid']['pos_11_20'] ?? 0),
                            'pos_21_30' => (int)($metrics['paid']['pos_21_30'] ?? 0),
                            'pos_31_40' => (int)($metrics['paid']['pos_31_40'] ?? 0),
                            'pos_41_50' => (int)($metrics['paid']['pos_41_50'] ?? 0),
                            'pos_51_60' => (int)($metrics['paid']['pos_51_60'] ?? 0),
                            'pos_61_70' => (int)($metrics['paid']['pos_61_70'] ?? 0),
                            'pos_71_80' => (int)($metrics['paid']['pos_71_80'] ?? 0),
                            'pos_81_90' => (int)($metrics['paid']['pos_81_90'] ?? 0),
                            'pos_91_100' => (int)($metrics['paid']['pos_91_100'] ?? 0),
                            'impressions_etv' => (float)($metrics['paid']['impressions_etv'] ?? 0),
                            'estimated_paid_traffic_cost' => (float)($metrics['paid']['estimated_paid_traffic_cost'] ?? 0)
                        ]
                    ]
                ];
            })->values(),
            'results' => $analysis->analysis_data ? (is_string($analysis->analysis_data) ? json_decode($analysis->analysis_data) : $analysis->analysis_data) : null,
        ];

        return Inertia::render('Spy/Show', [
            'analysis' => $formattedAnalysis
        ]);
    }

    /**
     * Display the analysis results
     */
    public function showAnalysis(string $uuid)
    {
        try {
            $analysis = WebsiteAnalysis::where('uuid', $uuid)
                ->where('project_code', Project::where('team_id', Auth::user()->current_team_id)->first()->project_code)
                ->firstOrFail();

            return inertia('Spy/Show', [
                'analysis' => [
                    'uuid' => $analysis->uuid,
                    'url' => $analysis->url,
                    'status' => $analysis->status,
                    'screenshot_url' => $analysis->screenshot_path ? Storage::disk('public')->url($analysis->screenshot_path) : null,
                    'created_at' => $analysis->created_at,
                    'results' => $analysis->status === 'completed' ? $analysis->analysis_data : null,
                    'error_message' => $analysis->error_message,
                ]
            ]);
        } catch (Exception $e) {
            Log::error("Error fetching analysis: " . $e->getMessage());
            return redirect()->route('website-spy.index')->with('error', 'Failed to fetch the analysis results');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /** Get the language code from the language_location table based on location_code. */
    public function getLanguageCode($location_code)
    {
        $language_code = DB::table('language_location')->where('location_code', $location_code)->first()->language_code;
        return $language_code;
    }


    public function getCompetitors(Request $request, $uuid)
    {
        $login = Config::get('dataforseo.login');
        $password = Config::get('dataforseo.password');
        $baseUrl = Config::get('dataforseo.base_url');

        $credentials = base64_encode("{$login}:{$password}");

        try {
            // Get the website analysis record
            $analysis = WebsiteAnalysis::where('uuid', $uuid)->firstOrFail();

            $postArray = [
                [
                    'target' => $request->target,
                    'location_code' => $request->location_code,
                    'language_code' => $this->getLanguageCode($request->location_code),
                    'exclude_top_domains' => true,
                ]
            ];

            // Make API call
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Authorization' => "Basic {$credentials}",
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/v3/dataforseo_labs/google/competitors_domain/live", $postArray);

            // Check if API response is successful
            if ($response->successful()) {
                $data = $response->json();
                
                // Check if we have valid data structure
                if (isset($data['tasks'][0]['result'][0]['items']) && is_array($data['tasks'][0]['result'][0]['items'])) {
                    $items = $data['tasks'][0]['result'][0]['items'];
                    $locationCode = $data['tasks'][0]['result'][0]['location_code'];
                    $languageCode = $data['tasks'][0]['result'][0]['language_code'];
                    
                    // Delete existing competitors for this analysis
                    DB::table('competitors')->where('uuid', $uuid)->delete();
                    
                    // Store each competitor
                    foreach ($items as $item) {
                        // Debug metrics data
                        Log::info("Raw metrics data for competitor: " . $item['domain'], [
                            'metrics' => $item['metrics']
                        ]);
                        
                        $metrics = $item['metrics'];
                        
                        // Ensure metrics is properly decoded if it's a string
                        if (is_string($metrics)) {
                            $metrics = json_decode($metrics, true);
                        }
                        
                        // Debug processed metrics
                        Log::info("Processed metrics for competitor: " . $item['domain'], [
                            'organic_pos_1' => $metrics['organic']['pos_1'] ?? null,
                            'organic_pos_2_3' => $metrics['organic']['pos_2_3'] ?? null,
                            'metrics_structure' => array_keys($metrics['organic'])
                        ]);
                        
                        // Store competitor data
                        DB::table('competitors')->insert([
                            'uuid' => $uuid,
                            'website_analysis_id' => $analysis->id,
                            'project_code' => $request->project_code,
                            'target_domain' => $request->target,
                            'competitor_domain' => $item['domain'],
                            'se_type' => $item['se_type'],
                            'location_code' => $locationCode,
                            'language_code' => $languageCode,
                            'avg_position' => $item['avg_position'],
                            'sum_position' => $item['sum_position'],
                            'intersections' => $item['intersections'],
                            'metrics' => json_encode($metrics),
                            'full_domain_metrics' => json_encode($item['full_domain_metrics']),
                            'competitor_metrics' => json_encode($item['competitor_metrics']),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                    
                    Log::info("Competitors fetched and stored successfully for UUID: $uuid", [
                        'count' => count($items)
                    ]);
                } else {
                    Log::warning("Invalid data structure in API response for UUID: $uuid", [
                        'response' => $data
                    ]);
                }
            } else {
                Log::error("Failed to fetch competitor data from API for UUID: $uuid", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error fetching competitor data: ' . $e->getMessage(), [
                'uuid' => $uuid,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Analyze a website for SEO, CRO, and tracking information
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        $uuid = (string) Str::uuid();
        $projectCode = Project::where('team_id', Auth::user()->current_team_id)->first()->project_code;

        // Create initial record
        WebsiteAnalysis::create([
            'uuid' => $uuid,
            'project_code' => $projectCode,
            'url' => $request->url,
            'status' => 'pending'
        ]);

        // Dispatch the job to process the analysis
        AnalyzeWebsiteJob::dispatch($uuid, $projectCode);

        return redirect()->route('website-spy.show', ['uuid' => $uuid])
            ->with('success', 'Analysis started successfully');
    }

    protected function processWebsiteTechnologies(WebsiteAnalysis $analysis, array $data): void
    {
        // Delete existing technologies for this analysis
        WebsiteSpyTechnologies::where('website_analysis_id', $analysis->id)->delete();

        if (!empty($data[0]['technologies'])) {
            foreach ($data[0]['technologies'] as $category => $techGroups) {
                foreach ($techGroups as $group => $technologies) {
                    foreach ($technologies as $techName) {
                        WebsiteSpyTechnologies::create([
                            'uuid' => $analysis->uuid,
                            'project_code' => $analysis->project_code,
                            'website_analysis_id' => $analysis->id,
                            'name' => $techName,
                            'version' => null,
                            'icon' => null,
                            'website' => null,
                            'cpe' => null,
                            'categories' => [$category, $group],
                        ]);
                    }
                }
            }
        }

        // Store metadata
        if (!empty($data[0])) {
            $item = $data[0];
            WebsiteSpyMetadata::create([
                'uuid' => $analysis->uuid,
                'project_code' => $analysis->project_code,
                'website_analysis_id' => $analysis->id,
                'title' => $item['title'] ?? null,
                'description' => $item['description'] ?? null,
                'meta_keywords' => $item['meta_keywords'] ?? null,
                'phone_numbers' => $item['phone_numbers'] ?? [],
                'email_addresses' => $item['emails'] ?? [],
                'social_media_urls' => $item['social_graph_urls'] ?? [],
                'country_iso_code' => $item['country_iso_code'] ?? null,
                'language_code' => $item['language_code'] ?? null,
            ]);

            // Store additional data
            $analysis->domain_rank = $item['domain_rank'] ?? null;
            $analysis->last_visited = $item['last_visited'] ?? null;
            $analysis->content_language = $item['content_language_code'] ?? null;
            $analysis->save();
        }
    }

    /**
     * Analyze a website for SEO, CRO, and tracking information
     */
    public function analyzeDataForSEO(Request $request)
    {
        try {
            $url = $request->input('url');
            $projectCode = $request->input('project_code');
            $languageCode = $request->input('language_code', 'en');
            $locationCode = $request->input('location_code', 2840); // Default to US

            // Create website analysis record
            $analysis = WebsiteAnalysis::create([
                'uuid' => Str::uuid(),
                'project_code' => $projectCode,
                'url' => $url,
                'status' => 'processing'
            ]);

            // Get technologies data
            $techData = $this->dataForSEO->getTechnologies($url);
            $this->processWebsiteTechnologies($analysis, $techData);

            // Get historical rank data
            
            $rankData = $this->dataForSEO->getHistoricalRanks($url, $languageCode, $locationCode);
            $this->processHistoricalRanks($analysis, $rankData);

            // Update analysis status
            $analysis->update(['status' => 'completed']);

            return response()->json([
                'success' => true,
                'message' => 'Website analysis completed successfully',
                'data' => $analysis
            ]);

        } catch (Exception $e) {
            \Log::error('Website analysis failed', [
                'error' => $e->getMessage(),
                'url' => $url ?? null
            ]);

            if (isset($analysis)) {
                $analysis->update(['status' => 'failed']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Website analysis failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process ranked keywords data from DataForSEO
     */
    protected function processRankedKeywords(WebsiteAnalysis $analysis, array $data): void
    {
        // Delete existing keywords for this analysis
        WebsiteKeyword::where('website_analysis_id', $analysis->id)->delete();

        if (!empty($data[0]['result'][0]['items'])) {
            foreach ($data[0]['result'][0]['items'] as $item) {
                $keywordData = $item['keyword_data'];
                $rankedElement = $item['ranked_serp_element'];

                WebsiteKeyword::create([
                    'uuid' => $analysis->uuid,
                    'project_code' => $analysis->project_code,
                    'website_analysis_id' => $analysis->id,
                    'location_code' => $data[0]['result'][0]['location_code'],
                    'language_code' => $data[0]['result'][0]['language_code'],
                    'keyword' => $keywordData['keyword'],
                    'se_type' => $keywordData['se_type'],
                    'rank_group' => $rankedElement['serp_item']['rank_group'] ?? null,
                    'rank_absolute' => $rankedElement['serp_item']['rank_absolute'] ?? null,
                    'position' => $rankedElement['serp_item']['position'] ?? null,
                    'type' => $rankedElement['serp_item']['type'] ?? null,
                    'is_featured_snippet' => $rankedElement['serp_item']['is_featured_snippet'] ?? false,
                    'search_volume' => $keywordData['keyword_info']['search_volume'] ?? null,
                    'competition' => $keywordData['keyword_info']['competition'] ?? null,
                    'competition_level' => $keywordData['keyword_info']['competition_level'] ?? null,
                    'cpc' => $keywordData['keyword_info']['cpc'] ?? null,
                    'monthly_searches' => $keywordData['keyword_info']['monthly_searches'] ?? null,
                    'traffic_cost' => $rankedElement['serp_item']['estimated_paid_traffic_cost'] ?? null,
                ]);
            }
        }
    }

    protected function processHistoricalRanks(WebsiteAnalysis $analysis, array $items): void
    {
        // Delete existing historical ranks for this analysis
        WebsiteHistoricalRank::where('website_analysis_id', $analysis->id)->delete();

        foreach ($items as $item) {
            if (!isset($item['year']) || !isset($item['month']) || !isset($item['metrics'])) {
                Log::warning('Missing required fields in historical rank item', [
                    'item' => $item
                ]);
                continue;
            }

            Log::info('Processing historical rank item', [
                'year' => $item['year'],
                'month' => $item['month'],
                'raw_metrics' => $item['metrics']
            ]);

            $organic = $item['metrics']['organic'] ?? [];
            $paid = $item['metrics']['paid'] ?? [];
            
            // Log the extracted metrics
            Log::info('Extracted metrics for historical rank', [
                'organic_metrics' => $organic,
                'paid_metrics' => $paid,
                'organic_pos_1' => $organic['pos_1'] ?? 0,
                'organic_pos_2_3' => $organic['pos_2_3'] ?? 0
            ]);
            
            WebsiteHistoricalRank::create([
                'uuid' => $analysis->uuid,
                'project_code' => $analysis->project_code,
                'website_analysis_id' => $analysis->id,
                'year' => $item['year'],
                'month' => $item['month'],
                'organic_pos_1' => $organic['pos_1'] ?? 0,
                'organic_pos_2_3' => $organic['pos_2_3'] ?? 0,
                'organic_pos_4_10' => $organic['pos_4_10'] ?? 0,
                'organic_pos_11_20' => $organic['pos_11_20'] ?? 0,
                'organic_pos_21_30' => $organic['pos_21_30'] ?? 0,
                'organic_pos_31_40' => $organic['pos_31_40'] ?? 0,
                'organic_pos_41_50' => $organic['pos_41_50'] ?? 0,
                'organic_pos_51_60' => $organic['pos_51_60'] ?? 0,
                'organic_pos_61_70' => $organic['pos_61_70'] ?? 0,
                'organic_pos_71_80' => $organic['pos_71_80'] ?? 0,
                'organic_pos_81_90' => $organic['pos_81_90'] ?? 0,
                'organic_pos_91_100' => $organic['pos_91_100'] ?? 0,
                'organic_etv' => $organic['etv'] ?? 0,
                'organic_impressions_etv' => $organic['impressions_etv'] ?? 0,
                'organic_count' => $organic['count'] ?? 0,
                'organic_estimated_paid_traffic_cost' => $organic['estimated_paid_traffic_cost'] ?? 0,
                'organic_new' => $organic['is_new'] ?? 0,
                'organic_up' => $organic['is_up'] ?? 0,
                'organic_down' => $organic['is_down'] ?? 0,
                'organic_lost' => $organic['is_lost'] ?? 0,
                'paid_pos_1' => $paid['pos_1'] ?? 0,
                'paid_pos_2_3' => $paid['pos_2_3'] ?? 0,
                'paid_pos_4_10' => $paid['pos_4_10'] ?? 0,
                'paid_etv' => $paid['etv'] ?? 0,
                'paid_impressions_etv' => $paid['impressions_etv'] ?? 0,
                'paid_count' => $paid['count'] ?? 0,
                'paid_estimated_traffic_cost' => $paid['estimated_paid_traffic_cost'] ?? 0,
                'paid_new' => $paid['is_new'] ?? 0,
                'paid_up' => $paid['is_up'] ?? 0,
                'paid_down' => $paid['is_down'] ?? 0,
                'paid_lost' => $paid['is_lost'] ?? 0,
            ]);
        }
    }

    protected function processCompetitors(WebsiteAnalysis $analysis, array $data): void
    {
        // Delete existing competitors for this analysis
        DB::table('competitors')->where('uuid', $analysis->uuid)->delete();

        if (!empty($data[0]['result'][0]['items'])) {
            foreach ($data[0]['result'][0]['items'] as $item) {
                DB::table('competitors')->insert([
                    'uuid' => $analysis->uuid,
                    'project_code' => $analysis->project_code,
                    'target_domain' => $item['domain'],
                    'competitor_domain' => $item['domain'],
                    'se_type' => $item['se_type'],
                    'location_code' => $data[0]['result'][0]['location_code'],
                    'language_code' => $data[0]['result'][0]['language_code'],
                    'avg_position' => $item['avg_position'],
                    'sum_position' => $item['sum_position'],
                    'intersections' => $item['intersections'],
                    'full_domain_metrics' => $item['full_domain_metrics'],
                    'metrics' => $item['metrics'],
                    'competitor_metrics' => $item['competitor_metrics'],
                ]);
            }
        }
    }

    public function deleteCompetitor($uuid, $competitorId)
    {
        try {
            $analysis = WebsiteAnalysis::where('uuid', $uuid)
                ->where('project_code', Project::where('team_id', Auth::user()->current_team_id)->first()->project_code)
                ->firstOrFail();

            $competitor = $analysis->competitors()->findOrFail($competitorId);
            $competitor->delete();

            // Hent oppdatert analyse med gjenværende konkurrenter
            $updatedAnalysis = WebsiteAnalysis::where('uuid', $uuid)
                ->with(['technologies', 'competitors'])
                ->firstOrFail();

            // Formater data for frontend
            $formattedAnalysis = [
                'uuid' => $updatedAnalysis->uuid,
                'url' => $updatedAnalysis->url,
                'status' => $updatedAnalysis->status,
                'error_message' => $updatedAnalysis->error_message,
                'created_at' => $updatedAnalysis->created_at,
                'updated_at' => $updatedAnalysis->updated_at,
                'metrics' => $updatedAnalysis->metrics ? (is_string($updatedAnalysis->metrics) ? json_decode($updatedAnalysis->metrics) : $updatedAnalysis->metrics) : null,
                'screenshot_url' => $updatedAnalysis->screenshot_url ?? null,
                'screenshot_path' => $updatedAnalysis->screenshot_path,
                'competitors' => $updatedAnalysis->competitors->map(function ($comp) {
                    return [
                        'id' => $comp->id,
                        'competitor_domain' => $comp->competitor_domain,
                        'avg_position' => $comp->avg_position,
                        'intersections' => $comp->intersections,
                        'location_code' => $comp->location_code,
                        'se_type' => $comp->se_type,
                        'metrics' => $comp->competitor_metrics ? (is_string($comp->competitor_metrics) ? json_decode($comp->competitor_metrics) : $comp->competitor_metrics) : null
                    ];
                })->values(),
                'technologies' => $updatedAnalysis->technologies->map(function ($tech) {
                    return [
                        'name' => $tech->name,
                        'version' => $tech->version,
                        'icon' => $tech->icon,
                        'website' => $tech->website,
                        'categories' => $tech->categories ? (is_string($tech->categories) ? json_decode($tech->categories) : $tech->categories) : []
                    ];
                })->values()
            ];

            return redirect()->route('website-spy.show', ['uuid' => $uuid]);

           
        } catch (Exception $e) {
            Log::error('Error deleting competitor: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete competitor');
        }
    }
}
