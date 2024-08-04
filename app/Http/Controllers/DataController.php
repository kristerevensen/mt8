<?php

namespace App\Http\Controllers;

use App\Models\DataPage;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\DB;






class DataController extends Controller
{
    /**
     * Display a listing of the data pages.
     */
    public function index(Request $request)
    {

        // Get projects associated with the current team
        $project_code = $this->getProjectCode();
        // Retrieve all data pages
        $dataPages = DataPage::with('project')
            ->whereIn('project_code', $project_code)
            ->get();

        $fromDate = $request->input('from'); // Retrieve 'from' date from the request
        $toDate = $request->input('to');     // Retrieve 'to' date from the request

        if (!$fromDate || !$toDate) {
            // Set $toDate to yesterday, as we are excluding today
            $toDate = Carbon::yesterday()->toDateString();

            // Set $fromDate to 28 days before $toDate
            $fromDate = Carbon::yesterday()->subDays(27)->toDateString();
        }

        ### MetricsQuery ###
        // Metrics query with date filtering
        $metricsQuery = DataPage::query()
            ->where('project_code', $project_code);

        if ($fromDate && $toDate) {
            $metricsQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }

        $metrics = $metricsQuery->selectRaw('
                count(distinct(session_id)) as sessions, count(*) as pageviews,
                sum(entrance) as entrances,
                sum(exits) as exits,
                sum(bounce) as bounce
                ')
            ->first();


        // Pages query with date filtering
        $pagesQuery = DataPage::query()
            ->where('project_code', $project_code);

        if ($fromDate && $toDate) {
            $pagesQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }

        $pages = $pagesQuery->selectRaw('
                COUNT(url) as pageviews,
                sum(bounce) as bounces,
                sum(entrance) as entrances,
                sum(exits) as exits,
                url,
                project_code,
                url_code
                ')
            ->groupBy('project_code', 'url', 'url_code')
            ->orderBy('pageviews', 'desc')
            ->paginate(10);

        // Append existing query parameters to the pagination links
        $pages->appends([
            'from' => $fromDate,
            'to' => $toDate
        ]);



        ### Pageview Query ###
        // Pageviews query with date filtering for chart
        $pageviewsQuery = DataPage::query()
            ->where('project_code', $project_code);

        if ($fromDate && $toDate) {
            $pageviewsQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }



        $chartPageviews = $pageviewsQuery->selectRaw('
            count(url) as pageviews,
            date(created_at) as date
            ')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get()
            ->keyBy('date'); // Key the collection by date for easy merging

        // Generate a date series for the given range
        $datePeriod = new \DatePeriod(
            new \DateTime($fromDate),
            new \DateInterval('P1D'),
            (new \DateTime($toDate))->modify('+1 day')
        );

        // Prepare a collection with zero pageviews for each date
        $allDatesPageviews = new Collection();
        foreach ($datePeriod as $date) {
            $formattedDate = $date->format('Y-m-d');
            $allDatesPageviews[$formattedDate] = ['date' => $formattedDate, 'pageviews' => 0];
        }

        // Merge the collections
        $finalPageviews = $allDatesPageviews->merge($chartPageviews)->values();

        return Inertia::render('Datapages/Index', [
            'pages' => $pages,
            'metrics' => $metrics,
            'pageviews' => $finalPageviews->values() // Ensure this is structured correctly
        ]);
    }

    /* Get the project code */
    public function getProjectCode()
    {
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;
        $project_code = Project::where('team_id', $currentTeamId)->pluck('project_code');
        return $project_code;
    }

    /**
     * Show the form for creating a new data page.
     */
    public function create()
    {
        // Return a view with a form to create a new data page
        return Inertia::render('Datapages/create');
    }

    /**
     * Store a newly created data page in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'url' => 'required|string',
            'url_code' => 'required|string|unique:data_pages,url_code',
            'event_type' => 'required|string',
            'project_code' => 'required|string|exists:projects,project_code',
            // Add other validation rules as needed
        ]);

        // Create a new DataPage record
        DataPage::create($request->all());

        // Redirect or return a response
        return redirect()->route('data_pages.index')->with('success', 'Data Page created successfully.');
    }

    /**
     * Display the specified data page.
     */
    public function show(Request $request, $url)
    {
        DB::enableQueryLog();

        // Get projects associated with the current team
        $project_code = $this->getProjectCode();

        $fromDate = $request->input('from'); // Retrieve 'from' date from the request
        $toDate = $request->input('to');     // Retrieve 'to' date from the request

        //check to see if the actual url is in the database
        $urlCheck = DB::table('data_pages')
            ->where('url_code', $url)
            ->select('url')
            ->first();  //returns the first result
        if (!$urlCheck) {
            return redirect()->route('pages')->with('message', 'The page you are looking for does not exist');
        }


        ### MetricsQuery ###
        // Metrics query with date filtering
        $metricsQuery = DataPage::query()
            ->where('project_code', $project_code)
            ->where('url_code', $url);

        if ($fromDate && $toDate) {
            $metricsQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }

        $metrics = $metricsQuery->selectRaw('
                count(distinct(session_id)) as sessions,
                count(*) as pageviews,
                sum(entrance) as entrances,
                sum(exits) as exits,
                sum(distinct(bounce)) as bounce
                ')
            ->orderBy('sessions', 'desc')
            ->first();

        ### Page Query ###
        $pageQuery = DataPage::query()
            ->where('project_code', $project_code)
            ->where('url_code', $url);

        if ($fromDate && $toDate) {
            $pageQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }

        $pageData = $pageQuery->selectRaw('
                *
                ')
            ->first();

        ### Pageview Query ###
        // Pageviews query with date filtering for chart
        $pageviewsQuery = DataPage::query()
            ->where('project_code', $project_code)
            ->where('url_code', $url);

        if ($fromDate && $toDate) {
            $pageviewsQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }

        $chartPageviews = $pageviewsQuery->selectRaw('
            count(url) as pageviews,
            date(created_at) as date
            ')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get()
            ->keyBy('date'); // Key the collection by date for easy merging

        // Generate a date series for the given range
        $datePeriod = new \DatePeriod(
            new \DateTime($fromDate),
            new \DateInterval('P1D'),
            (new \DateTime($toDate))->modify('+1 day')
        );

        // Prepare a collection with zero pageviews for each date
        $allDatesPageviews = new Collection();
        foreach ($datePeriod as $date) {
            $formattedDate = $date->format('Y-m-d');
            $allDatesPageviews[$formattedDate] = ['date' => $formattedDate, 'pageviews' => 0];
        }

        // Merge the collections
        $finalPageviews = $allDatesPageviews->merge($chartPageviews)->values();

        $pageviews = response()->json($finalPageviews);
        //dd  ($pageData ? $this->getUrlParameters($pageData->url) : []);
        return Inertia::render('Datapages/Show', [
            'pageviews' => $pageviews,
            'metrics' => $metrics,
            'page' => $pageData,
            'params' => $pageData ? $this->getUrlParameters($pageData->url) : [],
            'changes' => $pageData ? $this->getChanges($pageData->url) : [],
            'sources' => $pageData ? $this->getSources($pageData->url) : [],
        ]);
    }

    /**
     * Show the form for editing the specified data page.
     */
    public function edit(DataPage $dataPage)
    {
        // Return a view with a form to edit the data page
        return Inertia::render('Datapages/Edit', [
            'dataPage' => $dataPage
        ]);
    }

    /**
     * Update the specified data page in storage.
     */
    public function update(Request $request, DataPage $dataPage)
    {
        // Validate incoming request
        $request->validate([
            'url' => 'required|string',
            'url_code' => 'required|string|unique:data_pages,url_code,' . $dataPage->id,
            'event_type' => 'required|string',
            'project_code' => 'required|string|exists:projects,project_code',
            // Add other validation rules as needed
        ]);

        // Update the data page
        $dataPage->update($request->all());

        // Redirect or return a response
        return redirect()->route('Datapages/Index')->with('success', 'Data Page updated successfully.');
    }

    /**
     * Remove the specified data page from storage.
     */
    public function destroy(DataPage $dataPage)
    {
        // Delete the data page
        $dataPage->delete();

        // Redirect or return a response
        return redirect()->route('Datapages/Index')->with('success', 'Data Page deleted successfully.');
    }

    /**
     * Parse the URL to extract the pathname.
     */
    public function parseURL($url)
    {
        $parsedUrl = parse_url($url);
        $pathname = $parsedUrl['path'] ?? ''; // $pathname will be "/page"
        return $pathname;
    }

    /**
     * Get the url parameters
     */
    function getUrlParameters($url)
    {
        // Parse the URL and return its components
        $parsedUrl = parse_url($url);

        // Extract the query string
        $queryString = $parsedUrl['query'] ?? '';

        // Parse the query string into an associative array
        $queryParams = [];
        parse_str($queryString, $queryParams);
        return array_keys($queryParams) ?? '';
    }

    public function getSources($url)
    {
        $rawSources = DB::table('data_pages')
            ->where('project_code', $this->getProjectCode())
            ->where('url', $url)
            ->select(
                'referrer',
                DB::raw('count(*) as count'),
                DB::raw('count(*) as pageviews'),
                DB::raw('count(distinct(session_id)) as sessions'),
                DB::raw('sum(bounce) as bounces'),
                DB::raw('sum(entrance) as entrances'),
                DB::raw('sum(exits) as exits')
            )
            ->groupBy('referrer')
            ->get();

        $categorizedSources = $rawSources->mapToGroups(function ($item) {
            $sourceType = $this->categorizeSource($item->referrer);
            return [$sourceType => $item];
        });

        $summarizedSources = $categorizedSources->map(function ($group, $sourceType) {
            return [
                'source_type' => $sourceType,
                'count' => $group->sum('count'),
                'pageviews' => $group->sum('pageviews'),
                'sessions' => $group->sum('sessions'),
                'bounces' => $group->sum('bounces'),
                'entrances' => $group->sum('entrances'),
                'exits' => $group->sum('exits'),
            ];
        });

        return $summarizedSources;
    }
    private function categorizeSource($referrer)
    {
        if (empty($referrer)) {
            return 'Direct Traffic';
        }

        $parsedUrl = parse_url($referrer);
        $host = strtolower($parsedUrl['host'] ?? '');

        // Define arrays of hostnames for different categories
        $searchEngines = ['google', 'bing', 'yahoo', 'baidu', 'duckduckgo'];
        $socialMedia = ['facebook', 'twitter', 'linkedin', 'instagram', 'pinterest'];

        // Check if the referrer matches any known search engines
        foreach ($searchEngines as $searchEngine) {
            if (strpos($host, $searchEngine) !== false) {
                return 'Organic Search';
            }
        }

        // Check if the referrer matches any known social media platforms
        foreach ($socialMedia as $social) {
            if (strpos($host, $social) !== false) {
                return 'Social Media';
            }
        }
        // Check for Paid Search (basic approach, might require UTM parameter checks)
        if (strpos($referrer, 'utm_medium=cpc') !== false || strpos($referrer, 'gclid=') !== false) {
            return 'Paid Search';
        }

        // Add more checks here for other categories like 'Paid Search', 'Referral', etc.

        // Default category
        return 'Referral Traffic';
    }
    public function getChanges($url)
    {
        $changes = DB::table('data_pages')
            ->where('project_code', $this->getProjectCode())
            ->where('url', $url)
            ->selectRaw('
                distinct(content_hash),
                created_at
                ')
            ->orderBy('created_at', 'desc')
            ->groupBy('content_hash', 'created_at')
            ->get();

        return $changes;
    }
}
