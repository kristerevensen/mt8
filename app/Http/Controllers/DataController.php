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
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;


        // Get projects associated with the current team
        $project_code = Project::where('team_id', $currentTeamId)->pluck('project_code');
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

        $pageviews = response()->json($finalPageviews);


        return Inertia::render('Datapages/Index', [
            'pages' => $pages,
            'metrics' => $metrics,
            'pageviews' => $finalPageviews->values() // Ensure this is structured correctly
        ]);
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
    public function show(DataPage $dataPage)
    {
        // Return a view or JSON response for the specified data page
        return Inertia::render('Datapages/Index', [
            'dataPage' => $dataPage
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
}
