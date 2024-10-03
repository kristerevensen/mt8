<?php

namespace App\Http\Controllers;

use App\Models\KeywordList;
use App\Models\Project;
use App\Models\Keyword;
use App\Models\KeywordSearchVolume;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class KeywordListController extends Controller
{
    /**
     * Display a listing of the keyword lists.
     */
    public function index()
    {
        // Hent den autentiserte brukeren og det nåværende team ID
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        // Hent prosjektkoder assosiert med det nåværende teamet
        $project_code = Project::where('team_id', $currentTeamId)->pluck('project_code');
        if ($project_code->isEmpty()) {
            return redirect()->route('projects.index')->with('error', 'You need to create a project before you can create a keyword list.');
        }

        // Hent keyword lists knyttet til disse prosjektene
        $keywordLists = KeywordList::whereIn('project_code', $project_code)->paginate(10);

        return Inertia::render('Keywords/Lists/Index', [
            'project' => Project::where('team_id', $currentTeamId)->first(),
            'keywordLists' => $keywordLists,
        ]);
    }

    /**
     * Show the form for creating a new keyword list.
     */
    public function create()
    {
        return Inertia::render('Keywords/Lists/Create', []);
    }

    /**
     * Display the specified keyword list.
     */
    public function show($list_uuid)
    {
        // Hent keyword list ved hjelp av list_uuid
        $keywordList = KeywordList::where('list_uuid', $list_uuid)->firstOrFail();
        $keywordListName = $keywordList->name;

        // Hent søkeord relatert til denne listen, med relasjonen `monthlySearchVolumes`
        $keywords = $keywordList->keywords()->with('monthlySearchVolumes')->paginate(10);

        // Hent alle søkeord relatert til listen (uten paginering) for å hente alle volumene til grafen
        $allKeywords = $keywordList->keywords()->pluck('keyword_uuid');

        // Hent månedlig søkevolum for ALLE søkeord i listen (uavhengig av paginering)
        $searchVolumes = KeywordSearchVolume::whereIn('keyword_uuid', $allKeywords)
            ->select('year', 'month', 'search_volume')
            ->orderBy('year')
            ->orderBy('month')
            ->orderBy('search_volume')
            ->get()
            ->groupBy(function ($date) {
                return $date->year . '-' . $date->month;
            })
            ->map(function ($group) {
                return $group->sum('search_volume');
            })
            ->toArray();

        return Inertia::render('Keywords/Lists/Show', [
            'keywordList' => $keywordList,
            'keywordListName' => $keywordListName,
            'keywords' => $keywords, // Paginated keywords for the table
            'searchVolumes' => $searchVolumes, // All search volumes for the chart
            'project' => Project::where('project_code', $keywordList->project_code)->first(), // Send prosjektdata
        ]);
    }




    /**
     * Show the form for editing the specified keyword list.
     */
    public function edit($list_uuid)
    {
        // Hent keyword list ved hjelp av list_uuid
        $keywordList = KeywordList::where('list_uuid', $list_uuid)->firstOrFail();

        return Inertia::render('Keywords/Lists/Edit', [
            'keywordList' => $keywordList,
        ]);
    }

    /**
     * Store a newly created keyword list in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $user = Auth::user();
        $currentTeamId = $user->current_team_id;
        $project_code = Project::where('team_id', $currentTeamId)->first()->project_code;

        $keywordList = new KeywordList();
        $keywordList->project_code = $project_code;
        $keywordList->name = $request->name;
        $keywordList->description = $request->description;

        if ($keywordList->save()) {
            return redirect()->route('keyword-lists.index')->with('success', 'Keyword list created successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to create keyword list.');
        }
    }

    /**
     * Update the specified keyword list in storage.
     */
    public function update(Request $request, $list_uuid)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $keywordList = KeywordList::where('list_uuid', $list_uuid)->firstOrFail();
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;
        $projectCode = Project::where('team_id', $currentTeamId)->first()->project_code;

        if ($keywordList->project_code != $projectCode) {
            return redirect()->back()->with('error', 'You are not authorized to update this keyword list.');
        }

        $keywordList->name = $request->name;
        $keywordList->description = $request->description;

        if ($keywordList->save()) {
            return redirect()->route('keyword-lists.index')->with('success', 'Keyword list updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to update keyword list.');
        }
    }

    /**
     * Remove the specified keyword list from storage.
     */
    public function destroy($list_uuid)
    {
        // Hent keyword list ved hjelp av list_uuid
        $keywordList = KeywordList::where('list_uuid', $list_uuid)->firstOrFail();

        $keywordList->delete();

        return redirect()->route('keyword-lists.index')->with('success', 'Keyword list deleted successfully.');
    }
}
