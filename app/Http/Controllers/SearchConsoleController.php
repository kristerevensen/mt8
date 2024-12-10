<?php
namespace App\Http\Controllers;

use App\Services\GoogleSearchConsoleService;
use App\Models\SearchConsoleData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Log;

class SearchConsoleController extends Controller
{
    protected $searchConsoleService;

    public function __construct(GoogleSearchConsoleService $searchConsoleService)
    {
        $this->searchConsoleService = $searchConsoleService;
    }

    public function index()
    {
        try {
            $sites = $this->searchConsoleService->getSites();
            return Inertia::render('SearchConsole/Index', [
                'sites' => $sites
            ]);
        } catch (\Exception $e) {
            Log::error('Error in SearchConsole index: ' . $e->getMessage());
            return back()->with('error', 'Could not fetch Search Console sites. Please try again.');
        }
    }

    public function fetchDataPage()
    {
        try {
            $sites = $this->searchConsoleService->getSites();
            return Inertia::render('SearchConsole/Fetch', [
                'sites' => $sites
            ]);
        } catch (\Exception $e) {
            Log::error('Error in fetchDataPage: ' . $e->getMessage());
            return back()->with('error', 'Could not load fetch data page. Please try again.');
        }
    }

    public function fetchData(Request $request)
    {
        $request->validate([
            'site' => 'required|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date',
            'projectCode' => 'required|string'
        ]);

        try {
            // Fetch pages data
            $pagesData = $this->searchConsoleService->getTopPages(
                $request->site,
                $request->startDate,
                $request->endDate
            );

            // Fetch queries data
            $queriesData = $this->searchConsoleService->getTopQueries(
                $request->site,
                $request->startDate,
                $request->endDate
            );

            // Store the data
            foreach ($pagesData as $pageData) {
                SearchConsoleData::create([
                    'project_code' => $request->projectCode,
                    'url' => $pageData['url'],
                    'type' => 'page',
                    'clicks' => $pageData['clicks'],
                    'impressions' => $pageData['impressions'],
                    'ctr' => $pageData['ctr'],
                    'position' => $pageData['position'],
                    'date' => Carbon::parse($request->endDate)
                ]);
            }

            foreach ($queriesData as $queryData) {
                SearchConsoleData::create([
                    'project_code' => $request->projectCode,
                    'type' => 'query',
                    'dimension_value' => $queryData['query'],
                    'clicks' => $queryData['clicks'],
                    'impressions' => $queryData['impressions'],
                    'ctr' => $queryData['ctr'],
                    'position' => $queryData['position'],
                    'date' => Carbon::parse($request->endDate)
                ]);
            }

            return back()->with('success', 'Search Console data fetched and stored successfully.');
        } catch (\Exception $e) {
            Log::error('Error fetching Search Console data: ' . $e->getMessage());
            return back()->with('error', 'Failed to fetch Search Console data. Please try again.');
        }
    }

    public function getHistoricalData(Request $request)
    {
        $request->validate([
            'projectCode' => 'required|string',
            'type' => 'required|in:page,query',
            'startDate' => 'required|date',
            'endDate' => 'required|date'
        ]);

        try {
            $data = SearchConsoleData::where('project_code', $request->projectCode)
                ->where('type', $request->type)
                ->whereBetween('date', [$request->startDate, $request->endDate])
                ->orderBy('date', 'desc')
                ->get();

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error fetching historical data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch historical data'], 500);
        }
    }

    public function connect()
    {
        return redirect($this->searchConsoleService->getAuthUrl());
    }

    public function callback(Request $request)
    {
        try {
            $this->searchConsoleService->handleCallback($request->get('code'));
            return redirect()->route('search-console.index')
                ->with('success', 'Successfully connected to Google Search Console');
        } catch (\Exception $e) {
            return redirect()->route('search-console.index')
                ->with('error', 'Failed to connect to Google Search Console');
        }
    }

    public function disconnect()
    {
        $this->searchConsoleService->disconnect();
        return redirect()->route('search-console.index')
            ->with('success', 'Successfully disconnected from Google Search Console');
    }
}
