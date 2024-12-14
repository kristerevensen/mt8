<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\GoogleSearchConsoleService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SearchConsoleController extends Controller
{
    protected $searchConsoleService;

    public function __construct(GoogleSearchConsoleService $searchConsoleService)
    {
        $this->searchConsoleService = $searchConsoleService;
    }

    public function index()
    {
        return Inertia::render('SearchConsole/Index');
    }

    public function connect(Request $request)
    {
        try {
            Log::info('Starting Search Console connection', [
                'request_data' => $request->all()
            ]);

            $currentTeamId = $request->user()->currentTeam->id;
            Log::info('Current team ID: ' . $currentTeamId);
            
            // Hent prosjektkoder assosiert med det nåværende teamet
            $project = Project::where('team_id', $currentTeamId)->first();
            
            if (!$project) {
                Log::error('No project found for team', ['team_id' => $currentTeamId]);
                return redirect()->route('search-console.index')
                    ->with('error', 'No project found for your team.');
            }
            
            $project_code = $project->project_code;
            Log::info('Found project code: ' . $project_code);
            
            // Store configuration in session
            $config = [
                'project_code' => $project_code,
                'months' => $request->months,
                'includeQueries' => $request->includeQueries,
                'includePages' => $request->includePages,
                'includeCountries' => $request->includeCountries,
                'includeDevices' => $request->includeDevices,
            ];
            
            Log::info('Storing config in session', ['config' => $config]);
            Session::put('search_console_config', $config);

            // Get authorization URL
            $authUrl = $this->searchConsoleService->getAuthUrl();
            Log::info('Generated auth URL: ' . $authUrl);
            
            return redirect($authUrl);
        } catch (Exception $e) {
            Log::error('Failed to initiate Google Search Console connection', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->route('search-console.index')
                ->with('error', 'Failed to connect to Google Search Console. Please try again.');
        }
    }

    public function callback(Request $request)
    {
        try {
            if ($request->has('error')) {
                throw new Exception('Authorization denied: ' . $request->error);
            }

            if (!$request->has('code')) {
                throw new Exception('No authorization code received');
            }

            // Exchange code for access token
            $this->searchConsoleService->handleCallback($request->code);

            // Get configuration from session
            $config = Session::get('search_console_config');
            
            if (!$config) {
                throw new Exception('Configuration not found in session');
            }

            // Start data fetch process
            $this->searchConsoleService->fetchData(
                $config['project_code'],
                (int) $config['months'],
                [
                    'queries' => $config['includeQueries'],
                    'pages' => $config['includePages'],
                    'countries' => $config['includeCountries'],
                    'devices' => $config['includeDevices'],
                ]
            );

            Session::forget('search_console_config');

            return redirect()->route('search-console.index')
                ->with('success', 'Successfully connected to Google Search Console. Data fetch has been initiated.');

        } catch (Exception $e) {
            Log::error('Failed to handle Google Search Console callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('search-console.index')
                ->with('error', 'Failed to complete Google Search Console connection. Please try again.');
        }
    }
}
