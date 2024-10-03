<?php

namespace App\Http\Controllers;

use App\Models\Keyword;
use App\Models\KeywordList;
use App\Models\KeywordSearchVolume;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class KeywordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        // Hent prosjektkoder assosiert med det nåværende teamet
        $project = Project::where('team_id', $currentTeamId)->first();
        $project_code = $project->project_code;

        // Redirect to the projects index page if no project code is found
        if (!$project_code) {
            return redirect()->route('projects.index')->with('error', 'Please create a project first.');
        }

        // Get the search query
        $search = $request->input('search');


        // Fetch keywords based on the search query, or return all keywords if no search query
        $keywords = Keyword::where('project_code', $project_code)
            ->when($search, function ($query, $search) {
                // Perform the search query in the database
                return $query->where('keyword', 'like', '%' . $search . '%');
            })
            ->paginate(10); // Use pagination

        //hent keyword lists fra databasen, som er relatert til det valgte teamet og prosjektkoden
        $keywordLists = KeywordList::where('project_code', $project_code)->get();

        // Return the results to the frontend
        return Inertia::render('Keywords/Index', [
            'currentTeamId' => $currentTeamId,
            'project' => $project,
            'project_code' => $project_code,
            'keywords' => $keywords,
            'keywordLists' => KeywordList::all(), // Add other necessary data here
            'search' => $search, // Pass the search query back to the view
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // You can add logic for creating keywords

        //fetch the keyword lists from dabase, that are related to the selected team and project code
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;
        $project = Project::where('team_id', $currentTeamId)->firstOrFail();
        $keywordLists = KeywordList::where('project_code', $project->project_code)->get();

        return Inertia::render('Keywords/Create', [
            'keywordLists' => $keywordLists,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate keywords input
        $request->validate([
            'keywords' => 'required|string',
            'list_uuid' => 'nullable|exists:keyword_lists,list_uuid',
        ]);

        // Get the current user's active project
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        // Fetch the project associated with the team
        $project = Project::where('team_id', $currentTeamId)->firstOrFail();

        // Split keywords by new lines and trim whitespace
        $keywords = array_filter(array_map('trim', explode("\n", $request->keywords)));

        // Prepare the data for bulk insert
        $data = [];
        foreach ($keywords as $keyword) {
            $data[] = [
                'keyword' => $keyword,
                'keyword_uuid' => Str::uuid(), // Generate a UUID
                'project_code' => $project->project_code,
                'location_code' => $project->location_code,
                'language_code' => $project->project_language,
                'list_uuid' => $request->list_uuid,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        //dd($data);
        // Use the upsert method to bulk insert and ignore duplicates
        Keyword::upsert(
            $data, // Data to insert
            ['keyword', 'project_code'], // Unique columns to check for duplicates
            [] // No columns to update, so it effectively ignores duplicates
        );

        return redirect()->route('keywords.index')->with('success', 'Keywords created successfully, and duplicates were ignored.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Fetch the keyword by UUID and pass it to the view
        $keyword = Keyword::findOrFail($id);

        return Inertia::render('Keywords/Show', [
            'keyword' => $keyword,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $keyword = Keyword::findOrFail($id);

        return Inertia::render('Keywords/Edit', [
            'keyword' => $keyword,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'keyword' => 'required|string|max:255',
        ]);

        // Fetch and update the keyword
        $keyword = Keyword::findOrFail($id);
        $keyword->update([
            'keyword' => $request->keyword,
        ]);

        return redirect()->route('keywords.index')->with('success', 'Keyword updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $keyword = Keyword::findOrFail($id);
        $keyword->delete();

        return redirect()->route('keywords.index')->with('success', 'Keyword deleted successfully!');
    }

    public function getWebsiteKeywords()
    {
        // get the project code
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;
        $project = Project::where('team_id', $currentTeamId)->firstOrFail();
        $project_code = $project->project_code;

        $dataForSeoController = new DataForSEOController();
        $dataForSeoController->getWebsiteKeywords($project_code);

        //return back with message
        return redirect()->back()->with('success', 'Website keywords fetched successfully.');
    }

    /**
     * Add selected keywords to a list.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addToList(Request $request)
    {
        // dd($request->all());
        // multiple keywords can be selected, so validate that 'keywords' is an array
        $validation = $request->validate([
            'keywords' => 'required|array|exists:keywords,keyword_uuid', //keyword_uuid exists
            'list_uuid' => 'required|exists:keyword_lists,list_uuid', //list_uuid
        ]);
        // if validation fails, return an error message
        if (!$validation) {
            return redirect()->back()->with('error', 'Invalid data.');
        }

        if (empty($request->keywords)) {
            return redirect()->back()->with('error', 'No keywords selected.');
        }
        if (!$request->list_uuid) {
            return redirect()->back()->with('error', 'No list selected.');
        }


        // loop through all keywords in the array with a foreach and update the list_uuid. if there is a list_uuid there from before, add it to a list of plural list_uuids
        $listUuids = [];
        foreach ($request->keywords as $keywordUuid) {
            $keyword = Keyword::where('keyword_uuid', $keywordUuid)->first();
            $keyword->list_uuid = $request->list_uuid;
            $keyword->save();

            // Add the list_uuid to the list of list_uuids
            $listUuids[] = $keyword->list_uuid;
        }



        // Return a success response with a message
        return redirect()->back()->with('success', 'Keywords added to list successfully.');
    }


    public function importKeywordsFromJson(Request $request)
    {
        // Hent JSON-strengen fra request (eller direkte fra databasen)
        $jsonData = $request->input('json'); // Bruk 'json' som parameter i request
        $projectCode = $request->input('project_code'); // Hent prosjektkoden fra request
        $keywords = json_decode($jsonData, true); // Dekod JSON-strengen til en PHP-array

        if (!$keywords) {
            return response()->json(['error' => 'Invalid JSON data'], 400);
        }

        // Iterer gjennom hver keyword data i JSON-arrayen
        foreach ($keywords as $keywordData) {
            // Opprett en ny UUID for hvert keyword
            $keywordUuid = (string) Str::uuid();

            // Sett opp en ny Keyword-model og lagre den i keywords-tabellen
            $keyword = Keyword::create([
                'keyword_uuid' => $keywordUuid,
                'keyword' => $keywordData['keyword'],
                'project_code' => $projectCode, // Bruk prosjektkoden som tilhører nøkkelordet
                'location_code' => $keywordData['location_code'] ?? null,
                'language_code' => $keywordData['language_code'] ?? null,
                'search_partners' => $keywordData['search_partners'] ?? false,
                'competition' => $keywordData['competition'] ?? null,
                'competition_index' => $keywordData['competition_index'] ?? null,
                'search_volume' => $keywordData['search_volume'] ?? null,
                'low_top_of_page_bid' => $keywordData['low_top_of_page_bid'] ?? null,
                'high_top_of_page_bid' => $keywordData['high_top_of_page_bid'] ?? null,
                'cpc' => $keywordData['cpc'] ?? null,
                'analyzed_at' => now(), // Sett analyzed_at til nåværende tid
            ]);

            // Iterer gjennom månedlig søkevolum og lagre i keyword_search_volumes-tabellen
            foreach ($keywordData['monthly_searches'] as $monthlyData) {
                KeywordSearchVolume::create([
                    'keyword_uuid' => $keywordUuid,
                    'project_code' => $projectCode,
                    'year' => $monthlyData['year'],
                    'month' => $monthlyData['month'],
                    'search_volume' => $monthlyData['search_volume'],
                ]);
            }
        }

        return response()->json(['message' => 'Keywords and search volumes imported successfully']);
    }


    /**
     * Bulk delete selected keywords.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {
        // Valider at 'keywords' er en array
        $request->validate([
            'keywords' => 'required|array',
        ]);

        // Sjekk om det faktisk er noen keywords i arrayet
        if (!is_array($request->keywords) || count($request->keywords) === 0) {
            return redirect()->back()->with('error', 'No keywords selected.');
        }

        // Slett søkeordene
        Keyword::whereIn('keyword_uuid', $request->keywords)->delete();

        return redirect()->back()->with('success', 'Selected keywords deleted successfully.');
    }
}
