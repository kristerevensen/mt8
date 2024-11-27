<?php

namespace App\Http\Controllers;

use App\Models\Competitor;
use App\Models\Language;
use App\Models\Location;
use App\Models\Project;
use App\Models\WebsiteSpyRequestTechnologies;
use Illuminate\Http\Request;
use App\Models\WebsiteSpyRequest;
use App\Models\WebsiteSpyTechnologies;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WebsiteSpyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Fetch all website spy requests with pagination
        $websiteSpyRequests = WebsiteSpyTechnologies::selectRaw('
            MAX(id) as id, uuid, MAX(project_code) as project_code, MAX(status_message) as status_message, MAX(status_code) as status_code,
            MAX(time) as time, MAX(category) as category, MAX(subcategory) as subcategory, MAX(item_title) as item_title, MAX(description) as description,
            MAX(created_at) as created_at
        ')
            ->where('project_code', Project::where('team_id', Auth::user()->current_team_id)->first()->project_code)
            ->when($request->search, function ($query, $search) {
                return $query->where('target_url', 'like', '%' . $search . '%');
            })
            ->groupBy('uuid')  // Gruppér resultatene etter uuid
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Fetch all languages and locations
        $languages = Language::all();
        $locations = Location::all();

        // Pass data to Inertia for the frontend
        return inertia('Spy/Index', [
            'websiteSpyRequests' => $websiteSpyRequests,
            'languages' => $languages,
            'locations' => $locations,
            'filters' => $request->only(['search']),
        ]);
    }



    /**
     * This method is used to spy on a websit and get several resources about a website
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     * @throws \Illuminate\Http\Client\RequestException
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Http\Client\PendingRequest
     *
     * Get competitors
     * Get technologies
     */
    public function spy(Request $request)
    {
        // Øk minnegrensen hvis nødvendig
        ini_set('memory_limit', '256M');

        // Generer en UUID for denne spion-operasjonen
        $uuid = (string) Str::uuid();

        // Bruk try-catch for å fange feil under begge API-kallene
        try {
            // Kjør begge prosessene sekvensielt
            $this->fetchDomainTechnologies($request, $uuid); // Henter teknologi-data
            $this->getCompetitors($request, $uuid); // Henter konkurrentdata

            // Når begge operasjonene er ferdige, omdiriger tilbake til indekssiden med en suksessmelding
            return redirect()->route('website-spy.index')->with('success', 'Spying completed successfully'); // Flash-melding
        } catch (\Exception $e) {
            // Logg og returner feilmelding hvis noe går galt
            Log::error('Error during spying operation: ' . $e->getMessage());

            // Returner til forrige side med en feilmelding
            return redirect()->route('website-spy.index')->with('error', 'An error occurred during the spying operation');
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
    public function show(string $uuid)
    {
        try {
            // Finn spionforespørselen basert på ID , legg til where project_code is equal to the current team id project code to get only the data for the current team

            $spyRequest = WebsiteSpyTechnologies::where('uuid', $uuid)->firstOrFail();

            dd($spyRequest);
            // Hent tilhørende konkurrenter basert på uuid
            $competitors = Competitor::where('uuid', $uuid)->get();
            //dd($competitors);
            // Send data til Inertia for frontend

            //dd($competitors);
            return inertia('Spy/Show', [
                'spyRequest' => $spyRequest,
                'competitors' => $competitors, // Send konkurrentdata
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching spy request: ' . $e->getMessage());
            return redirect()->route('website-spy.index')->with('error', 'Failed to fetch the spy request');
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
            $postArray = [
                [
                    'target' => $request->target,
                    'location_code' => $request->location_code,
                    'language_code' => $this->getLanguageCode($request->location_code),
                    'exclude_top_domains' => true,
                ]
            ];

            // Gjør API-kall
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Authorization' => "Basic {$credentials}",
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/v3/dataforseo_labs/google/competitors_domain/live", $postArray);
            // dd($uuid);
            // Sjekk om API-responsen er vellykket
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['tasks'][0]['result'][0]['items'])) {
                    // Lagre hver konkurrent
                    foreach ($data['tasks'][0]['result'][0]['items'] as $item) {
                        Competitor::create([
                            'uuid' => $uuid,
                            'target_domain' => $request->target,
                            'competitor_domain' => $item['domain'],
                            'se_type' => $item['se_type'],
                            'location_code' => $data['tasks'][0]['result'][0]['location_code'],
                            'language_code' => $data['tasks'][0]['result'][0]['language_code'],
                            'avg_position' => $item['avg_position'],
                            'sum_position' => $item['sum_position'],
                            'intersections' => $item['intersections'],
                            'full_domain_metrics' => $item['full_domain_metrics'],
                            'metrics' => $item['metrics'],
                            'competitor_metrics' => $item['competitor_metrics'],
                        ]);
                    }
                    Log::info("Competitors fetched and stored successfully for UUID: $uuid");
                } else {
                    Log::warning("No competitor data found for UUID: $uuid");
                }
            } else {
                Log::error("Failed to fetch competitor data from API for UUID: $uuid");
            }
        } catch (\Exception $e) {
            Log::error('Error fetching competitor data: ' . $e->getMessage());
        }
    }



    public function fetchDomainTechnologies(Request $request, $uuid)
    {
        // Øk minnegrensen hvis nødvendig
        ini_set('memory_limit', '256M');

        try {
            $user = Auth::user();
            $currentTeamId = $user->current_team_id;

            $project = Project::where('team_id', $currentTeamId)->firstOrFail();
            $project_code = $project->project_code;

            $baseUrl = Config::get('dataforseo.base_url');
            $login = Config::get('dataforseo.login');
            $password = Config::get('dataforseo.password');
            $credentials = base64_encode("{$login}:{$password}");

            $postArray = [
                [
                    'target' => $request->target,           // Domenet eller URL-en som analyseres
                    'location_code' => $request->location_code, // Stedskoden som sendes til API-et
                    'language_code' => $this->getLanguageCode($request->location_code),      // Språkkoden hentet med $this->getLanguageCode()
                    'exclude_top_domains' => true,           // Ekskluder toppdomener
                ]
            ];

            // Gjør API-kall til DataForSEO
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Authorization' => "Basic {$credentials}",
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/v3/domain_analytics/technologies/domain_technologies/live", $postArray);
            dd($response->json());
            if ($response->successful()) {
                $result = $response->json('tasks.0.result.0');
                $lastVisited = isset($result['last_visited']) ? Carbon::parse($result['last_visited']) : null;

                // Debugg teknologistrukturen fra API-svaret
                //dd($result['technologies']); // For å se nøyaktig hvordan teknologidataene er strukturert

                // Eksempel på teknologier som kan være knyttet til flere kategorier
                $technologies = [
                    // Ecommerce-relaterte teknologier
                    'ecommerce' => $result['technologies']['ecommerce'] ?? [],
                    'cross_border_ecommerce' => $result['technologies']['cross_border_ecommerce'] ?? [],
                    'fulfillment' => $result['technologies']['fulfillment'] ?? [],
                    'ecommerce_frontends' => $result['technologies']['ecommerce_frontends'] ?? [],
                    'payment_processors' => $result['technologies']['payment_processors'] ?? [],
                    'loyalty_and_rewards' => $result['technologies']['loyalty_and_rewards'] ?? [],
                    'buy_now_pay_later' => $result['technologies']['buy_now_pay_later'] ?? [],
                    'referral_marketing' => $result['technologies']['referral_marketing'] ?? [],
                    'cart_abandonment' => $result['technologies']['cart_abandonment'] ?? [],
                    'shipping_carriers' => $result['technologies']['shipping_carriers'] ?? [],
                    'returns' => $result['technologies']['returns'] ?? [],
                    'livestreaming' => $result['technologies']['livestreaming'] ?? [],

                    // Marketing-relaterte teknologier
                    'customer_relationship_management' => $result['technologies']['marketing']['crm'] ?? [], // CRM under marketing
                    'search_engine_optimization' => $result['technologies']['seo'] ?? [],
                    'marketing_automation' => $result['technologies']['marketing']['marketing_automation'] ?? [], // Marketing automation under marketing
                    'advertising' => $result['technologies']['advertising'] ?? [],
                    'affiliate_programs' => $result['technologies']['affiliate_programs'] ?? [],
                    'email' => $result['technologies']['email'] ?? [],
                    'personalisation' => $result['technologies']['personalisation'] ?? [],
                    'retargeting' => $result['technologies']['retargeting'] ?? [],
                    'real_user_monitoring' => $result['technologies']['real_user_monitoring'] ?? [],
                    'segmentation' => $result['technologies']['segmentation'] ?? [],
                    'reviews' => $result['technologies']['reviews'] ?? [],
                    'content_curation' => $result['technologies']['content_curation'] ?? [],
                    'customer_data_platform' => $result['technologies']['customer_data_platform'] ?? [],

                    // Content-relaterte teknologier
                    'content_management_systems' => $result['technologies']['content']['cms'] ?? [], // CMS under content
                    'message_boards' => $result['technologies']['message_boards'] ?? [],
                    'photo_galleries' => $result['technologies']['photo_galleries'] ?? [],
                    'wikis' => $result['technologies']['wikis'] ?? [],
                    'blogs' => $result['technologies']['blogs'] ?? [],
                    'learning_management_systems' => $result['technologies']['learning_management_systems'] ?? [],
                    'feed_readers' => $result['technologies']['feed_readers'] ?? [],
                    'document_management_systems' => $result['technologies']['document_management_systems'] ?? [],
                    'documentation' => $result['technologies']['documentation'] ?? [],
                    'issue_trackers' => $result['technologies']['issue_trackers'] ?? [],
                    'search_engines' => $result['technologies']['search_engines'] ?? [],
                    'rich_text_editors' => $result['technologies']['rich_text_editors'] ?? [],
                    'comment_systems' => $result['technologies']['comment_systems'] ?? [],
                    'translation' => $result['technologies']['translation'] ?? [],

                    // Communication-relaterte teknologier
                    'remote_access' => $result['technologies']['remote_access'] ?? [],
                    'webmail' => $result['technologies']['webmail'] ?? [],
                    'live_chat' => $result['technologies']['live_chat'] ?? [],

                    // Utilities-relaterte teknologier
                    'database_managers' => $result['technologies']['database_managers'] ?? [],
                    'hosting_panels' => $result['technologies']['hosting_panels'] ?? [],
                    'cryptominers' => $result['technologies']['cryptominers'] ?? [],

                    // Servers-relaterte teknologier
                    'cdn' => $result['technologies']['servers']['cdn'] ?? [], // CDN under servers
                    'media_servers' => $result['technologies']['servers']['media_servers'] ?? [], // Media servers under servers
                    'network_devices' => $result['technologies']['servers']['network_devices'] ?? [], // Network devices under servers
                    'control_systems' => $result['technologies']['servers']['control_systems'] ?? [], // Control systems under servers
                    'databases' => $result['technologies']['servers']['databases'] ?? [], // Databases under servers
                    'operating_systems' => $result['technologies']['servers']['operating_systems'] ?? [], // Operating systems under servers
                    'caching' => $result['technologies']['servers']['caching'] ?? [], // Caching under servers
                    'web_server_extensions' => $result['technologies']['servers']['web_server_extensions'] ?? [], // Web server extensions under servers
                    'reverse_proxies' => $result['technologies']['servers']['reverse_proxies'] ?? [], // Reverse proxies under servers
                    'load_balancers' => $result['technologies']['servers']['load_balancers'] ?? [], // Load balancers under servers
                    'web_servers' => $result['technologies']['servers']['web_servers'] ?? [], // Web servers under servers

                    // Security-relaterte teknologier
                    'security' => $result['technologies']['security']['security'] ?? [], // Security under security

                    // Analytics-relaterte teknologier
                    'tag_management' => $result['technologies']['analytics']['tag_managers'] ?? [], // Tag managers under analytics

                    // Web development-relaterte teknologier
                    'javascript_libraries' => $result['technologies']['web_development']['javascript_libraries'] ?? [], // Javascript libraries under web development
                ];



                // Debugg teknologiene før iterasjonen
                //dd($technologies); // For å se hvordan $technologies ser ut før iterasjonen

                foreach ($technologies as $category => $techItems) {
                    // Debugg kategorien og teknologiene som itereres over

                    //hvis en category ikke innehar noen teknologier, gå til neste iterasjon
                    if (empty($techItems)) {
                        continue;
                    }

                    foreach ($techItems as $tech) {
                        $data = [
                            'uuid' => $uuid,
                            'domain' => $request->target,
                            'project_code' => $project_code,
                            'status_message' => $response->status(),
                            'status_code' => $response->status(),
                            'time' => now(),
                            'category' => 'Technologies',  // Hovedkategori
                            'subcategory' => ucfirst($category),  // Underkategori, f.eks. "Ecommerce"
                            'item_title' => is_array($tech) ? json_encode($tech) : $tech,  // Sørg for at item_title er en streng
                            'description' => $result['description'] ?? null,  // Ekstra informasjon, hvis relevant
                        ];

                        try {
                            if (WebsiteSpyTechnologies::create($data)) {
                                Log::info("Domain technologies fetched and saved successfully for UUID: $uuid");
                            } else {
                                Log::error("Failed to save domain technologies data for UUID: $uuid");
                                //dd($data); // Dump data hvis det feiler
                            }
                        } catch (\Exception $e) {
                            // Logg feilen
                            Log::error("SQL error occurred while saving domain technologies for UUID: $uuid");

                            // Dump data og SQL-feilmelding for feilsøking
                            dd([
                                'data' => $data,
                                'sql_error' => $e->getMessage(),  // Hent SQL-feilmeldingen
                            ]);
                        }
                    }
                }

                Log::info("Domain technologies fetched and saved successfully for UUID: $uuid");
            } else {
                Log::error("Failed to fetch domain technologies data from API for UUID: $uuid");
            }
        } catch (\Exception $e) {
            Log::error('Error fetching domain technologies data: ' . $e->getMessage());
        }
    }
}
