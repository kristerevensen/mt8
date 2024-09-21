<?php

namespace App\Http\Controllers;

use App\Models\Keyword;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class KeywordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        // Hent prosjektkoder assosiert med det nåværende teamet
        $project_codes = Project::where('team_id', $currentTeamId)->pluck('project_code');

        // Hent keywords knyttet til disse prosjektene og legg til paginering
        $keywords = Keyword::whereIn('project_code', $project_codes)
            ->paginate(10);

        // Return keywords to the view
        return Inertia::render('Keywords/Index', [
            'keywords' => $keywords,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // You can add logic for creating keywords
        return Inertia::render('Keywords/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validering av keywords input
        $request->validate([
            'keywords' => 'required|string',
            'list_id' => 'nullable|exists:keyword_lists,id',  // Validering av list_id
        ]);

        // Hent brukeren og teamets aktive prosjekt
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        // Hent prosjektet som er knyttet til teamet
        $project = Project::where('team_id', $currentTeamId)->firstOrFail();

        // Split keywords by new lines and trim whitespace
        $keywords = array_filter(array_map('trim', explode("\n", $request->keywords)));

        // Iterer over keywords og lagre dem i databasen
        foreach ($keywords as $keyword) {
            Keyword::firstOrCreate([
                'keyword' => $keyword,
                'project_code' => $project->project_code, // Henter project_code fra prosjektet
            ], [
                'keyword_uuid' => Str::uuid(), // Genererer en UUID
                'location_code' => $project->project_country, // Bruker location_code fra prosjektet
                'language_code' => $project->project_language, // Bruker language_code fra prosjektet
                'list_id' => $request->list_id, // Lagrer valgt keyword list, hvis tilgjengelig
            ]);
        }

        return redirect()->route('keywords.index')->with('success', 'Keywords created successfully!');
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
}
