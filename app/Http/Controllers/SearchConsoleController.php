<?php
namespace App\Http\Controllers;

use App\Services\GoogleSearchConsoleService;
use App\Models\Project;
use App\Models\SearchConsoleData;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchConsoleController extends Controller
{
    protected $searchConsoleService;

    public function __construct(GoogleSearchConsoleService $searchConsoleService)
    {
        $this->searchConsoleService = $searchConsoleService;
    }

    // Viser Fetch Data-siden
    public function fetchDataPage()
    {
        $projects = Project::all(['id', 'name']); // Henter alle prosjekter

        return Inertia::render('GSC/Fetch', [
            'projects' => $projects,
        ]);
    }

    // Håndterer innhenting av data
    public function fetchData(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $project = Project::find($request->project_id);

        // Sjekk om data allerede er lagret for det valgte tidsrommet
        $existingData = SearchConsoleData::where('project_code', $project->code)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->exists();

        if ($existingData) {
            return redirect()->route('gsc.fetch')->withErrors('Data for det valgte tidsrommet er allerede lagret.');
        }

        // Hent data fra Google Search Console
        $searchConsoleData = $this->searchConsoleService->getSearchAnalytics(
            $project->url,
            $request->start_date,
            $request->end_date
        );

        foreach ($searchConsoleData as $data) {
            SearchConsoleData::create([
                'url' => $data['keys'][0],
                'clicks' => $data['clicks'],
                'impressions' => $data['impressions'],
                'date' => $data['date'],
                'project_code' => $project->code,
            ]);
        }

        return redirect()->route('gsc.index')->with('message', 'Data hentet og lagret.');
    }
}
