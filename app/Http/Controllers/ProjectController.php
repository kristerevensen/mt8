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

        //dd($request->all());
        // get all data from the location_code selected from the locations table
        $location = Location::where('country_iso_code', $request->project_country)->first();
        // dd($location);
        // get location_name, location_code, location_code_parent, country_iso_code, location_type from the locations table
        $location_name = $location->location_name;
        $location_code = $location->location_code;
        $location_code_parent = $location->location_code_parent;
        $country_iso_code = $location->country_iso_code;
        $location_type = $location->location_type;




        $project = new Project();
        $project->project_code = $this->generateUniqueCode();
        $project->project_name = $request->project_name;
        $project->project_domain = $request->project_domain;
        $project->project_language = $request->project_language;
        $project->project_country = $location_name;
        $project->project_location_code = $location_code;
        $project->project_category = serialize($request->input('project_category')); // Store as serialized string
        $project->owner_id = Auth::user()->id;
        $project->team_id = $request->team_id;


        if ($project->save()) {

            $dataForSeoController = new DataForSEOController();
            $dataForSeoController->getWebsiteKeywords($project->project_code);

            return redirect()->route('projects.index')->with('success', 'Project created successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to create project.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function settings($project_code)
    {
        //$project = Project::where('project_code', $project_code)->firstOrFail();
        //return Inertia::render('Projects/Show', ['project' => $project]);

        $project = Project::where('project_code', $project_code)
            ->where('owner_id', Auth::user()->id)
            ->firstOrFail();

        //dd($project);
        return Inertia::render('Projects/Show', [
            'project' => $project,
            'project_code' => $project_code
        ]);
    }

    public function edit($project_code)
    {
        $project = Project::where('project_code', $project_code)->firstOrFail();
        // Deserialize the project_category
        if ($project->project_category != null) {
            $project->project_category = unserialize($project->project_category);
        } else {
            $project->project_category = null;
        }


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
        //dd($request->project_location_code);
        // get all data from the location_code selected from the locations table
        $location = Location::where('location_code', $request->project_location_code)->first();
        // dd($location);
        // get location_name, location_code, location_code_parent, country_iso_code, location_type from the locations table
        $location_name = $location->location_name;
        $location_code = $location->location_code;
        $location_code_parent = $location->location_code_parent;
        $country_iso_code = $location->country_iso_code;
        $location_type = $location->location_type;





        $request->validate([
            'project_name' => 'required|string',
            'project_domain' => 'required|string',
            'project_language' => 'required|string',
            'project_location_code' => 'required|string',
            'team_id' => 'required|exists:teams,id',
        ]);

        $project = Project::where('project_code', $project_code)->firstOrFail();
        $project->project_name = $request->project_name;
        $project->project_domain = $request->project_domain;
        $project->project_language = $request->project_language;
        $project->project_country = $location_name;
        $project->project_location_code = $location_code;
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
