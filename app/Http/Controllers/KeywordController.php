<?php

namespace App\Http\Controllers;

use App\Models\Keyword;
use App\Models\KeywordList;
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

    /**
     * Add selected keywords to a list.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addToList(Request $request)
    {

        // Validate incoming request
        $request->validate([
            'keywords' => 'required|array', // Array of keyword UUIDs
            'list_uuid' => 'required|exists:keyword_lists,list_uuid', // Ensure the list exists
        ]);

        if (empty($request->keywords)) {
            return redirect()->back()->with('error', 'No keywords selected.');
        }

        // Retrieve the keywords based on the UUIDs passed in the request
        $keywords = Keyword::whereIn('keyword_uuid', $request->keywords)->get();

        // Update each keyword's list_uuid to the selected list
        foreach ($keywords as $keyword) {
            $keyword->list_uuid = $request->list_uuid; // Assign to the list
            $keyword->save(); // Save the keyword with the updated list
        }

        // Return a success response with a message
        return redirect()->back()->with('success', 'Keywords added to list successfully.');
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
