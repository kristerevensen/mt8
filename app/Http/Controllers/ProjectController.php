<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Location;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        // Hent den nåværende innloggede brukeren
        $user = Auth::user();

        // Get all team IDs the user is part of
        $teamIds = $user->teams->pluck('id');

        // Get all projects where the user is the owner or belongs to the team, with eager loading of the team

        $projects = DB::table('projects')
            ->join('teams', 'projects.team_id', '=', 'teams.id')
            ->select('projects.*', 'teams.name as team_name')
            ->get();

        //dd($projects);

        return Inertia::render('Projects/Index', ['projects' => $projects]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        // Hent den nåværende innloggede brukeren
        $user = Auth::user();

        // Hent alle team som brukeren er en del av
        $teams = DB::table('teams')
            ->leftJoin('projects', 'teams.id', '=', 'projects.team_id')
            ->whereNull('projects.team_id')
            ->select('teams.*')
            ->get();

        $locations = Location::orderBy('location_name')->get();
        //dd($teams);
        $languages = Language::orderby('language_name')->get();

        // Returner teams til Vue-komponenten
        return Inertia::render('Projects/Create', [
            'teams' => $teams,
            'locations' => $locations,
            'languages' => $languages,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string',
            'project_domain' => 'required|string',
            'project_language' => 'required|string',
            'project_country' => 'required|string',
            'project_category' => 'required',
            'team_id' => 'required|exists:teams,id',
        ]);



        $project = new Project();
        $project->project_code = $this->generateUniqueCode();
        $project->project_name = $request->project_name;
        $project->project_domain = $request->project_domain;
        $project->project_language = $request->project_language;
        $project->project_country = $request->project_country;
        $project->project_category = serialize($request->input('project_category')); // Store as serialized string
        $project->owner_id = auth()->id();
        $project->team_id = $request->team_id;


        if ($project->save()) {
            return redirect()->route('projects.index')->with('success', 'Project created successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to create project.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($project_code)
    {
        $project = Project::where('project_code', $project_code)->firstOrFail();
        return Inertia::render('Projects/Show', ['project' => $project]);
    }

    public function edit($project_code)
    {
        $project = Project::where('project_code', $project_code)->firstOrFail();
        // Deserialize the project_category
        $project->project_category = unserialize($project->project_category);

        // Hent den nåværende innloggede brukeren
        $user = Auth::user();

        // Hent alle team som brukeren er en del av, og ikke allerede har et prosjekt tilknyttet
        $teams = DB::table('teams')
            ->leftJoin('projects', 'teams.id', '=', 'projects.team_id')
            ->whereNull('projects.team_id')
            ->select('teams.*')
            ->orWhere('teams.id', $project->team_id) // Include the current project's team
            ->get();

        $locations = Location::orderBy('location_name')->get();
        $languages = Language::orderby('language_name')->get();

        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'teams' => $teams,
            'locations' => $locations,
            'languages' => $languages,
        ]);
    }

    public function update(Request $request, $project_code)
    {
        $request->validate([
            'project_name' => 'required|string',
            'project_domain' => 'required|string',
            'project_language' => 'required|string',
            'project_country' => 'required|string',
            'project_category' => 'required|array',
            'team_id' => 'required|exists:teams,id',
        ]);

        $project = Project::where('project_code', $project_code)->firstOrFail();
        $project->project_name = $request->project_name;
        $project->project_domain = $request->project_domain;
        $project->project_language = $request->project_language;
        $project->project_country = $request->project_country;
        $project->project_category = serialize($request->project_category); // Serialize categories array
        $project->team_id = $request->team_id;
        $project->save();

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }


    public function destroy($project_code)
    {
        $project = Project::where('project_code', $project_code)->firstOrFail();
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }

    private function generateUniqueCode()
    {
        return Str::random(8);
    }
}
