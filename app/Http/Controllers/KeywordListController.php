<?php

namespace App\Http\Controllers;

use App\Models\KeywordList;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
        $project_codes = Project::where('team_id', $currentTeamId)->pluck('project_code');

        // Hent keyword lists knyttet til disse prosjektene
        $keywordLists = KeywordList::whereIn('project_code', $project_codes)
            ->paginate(10);

        return Inertia::render('Keywords/Lists/Index', [
            'keywordLists' => $keywordLists,
        ]);
    }

    /**
     * Show the form for creating a new keyword list.
     */
    public function create()
    {
        // Hent den autentiserte brukeren og det nåværende team ID
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        // Hent prosjektkoder assosiert med det nåværende teamet
        $projects = Project::where('team_id', $currentTeamId)->pluck('project_code');

        return Inertia::render('Keywords/Lists/Create', [
            'projects' => $projects,
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

        // Generer unikt list_uuid
        $list_uuid = (string) Str::uuid();

        // Hent den autentiserte brukeren og det nåværende team ID
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        // Hent prosjektkoder assosiert med det nåværende teamet
        $projectCode = Project::where('team_id', $currentTeamId)->first()->project_code;

        // Lagre ny keyword list
        $keywordList = new KeywordList();
        $keywordList->list_uuid = $list_uuid;
        $keywordList->project_code = $projectCode;
        $keywordList->name = $request->name;
        $keywordList->description = $request->description;

        if ($keywordList->save()) {
            return redirect()->route('keyword-lists.index')->with('success', 'Keyword list created successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to create keyword list.');
        }
    }

    /**
     * Display the specified keyword list.
     */
    public function show($list_uuid)
    {
        // Hent keyword list ved hjelp av list_uuid
        $keywordList = KeywordList::where('list_uuid', $list_uuid)->firstOrFail();

        return Inertia::render('Keywords/Lists/Show', [
            'keywordList' => $keywordList,
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
     * Update the specified keyword list in storage.
     */
    public function update(Request $request, $list_uuid)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Hent keyword list ved hjelp av list_uuid
        $keywordList = KeywordList::where('list_uuid', $list_uuid)->firstOrFail();

        // Oppdater keyword list
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
