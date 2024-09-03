<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GoalsController extends Controller
{
    /**
     * Display a listing of the goals.
     */
    public function index()
    {
        // Hent den autentiserte brukeren og det nåværende team ID
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        // Hent prosjektkoder assosiert med det nåværende teamet
        $projectCode = Project::where('team_id', $currentTeamId)->first();
        $project_code = $projectCode->project_code;

        // Fetch goals with pagination based on project_code and get how many conversions this goal has by joining in the table of converisons through goal_uuid
        $goals = Goal::where('project_code', $project_code)->withCount('conversions')->paginate(10);

        //$goals = Goal::where('project_code', $project_code)->paginate(10);

        //dd($goals);


        return Inertia::render('Goals/Index', [
            'goals' => $goals,
        ]);
    }

    /**
     * Display the specified goal with conversion snippet
     */
    public function show(Goal $goal)
    {
        $project_code = $goal->project_code;

        return Inertia::render('Goals/Show', [
            'goal' => $goal,
            'project_code' => $project_code,
        ]);
    }


    /**
     * Show the form for creating a new goal.
     */
    public function create()
    {
        return Inertia::render('Goals/Create', []);
    }

    /**
     * Show the form for editing the specified goal.
     */
    public function edit(Goal $goal)
    {
        return Inertia::render('Goals/Edit', [
            'goal' => $goal,
        ]);
    }

    /**
     * Store a newly created goal in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'goal_name' => 'required|string|max:255',
            'goal_type' => 'required|string|max:255',
            'goal_value' => 'nullable|numeric',
            'goal_description' => 'nullable|string',
        ]);


        // Get the current user's team and associated projects
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        // Get project codes associated with the current team
        $projectCode = Project::where('team_id', $currentTeamId)->first();
        $project_code = $projectCode->project_code;

        //create goal
        $goals = new Goal();
        $goals->project_code = $project_code;
        $goals->goal_name = $request->goal_name;
        $goals->goal_type = $request->goal_type;
        $goals->goal_value = $request->goal_value;
        $goals->goal_description = $request->goal_description;
        $goals->goal_uuid = (string) Str::uuid();

        if ($goals->save()) {
            return redirect()->route('goals.index')->with('success', 'Goal created successfully.');
        } else {
            return redirect()->route('goals.index')->with('error', 'Goal not created.');
        }
    }

    /**
     * Update the specified goal in storage.
     */
    public function update(Request $request, Goal $goal)
    {
        $validated = $request->validate([
            'goal_name' => 'required|string|max:255',
            'goal_type' => 'required|string|max:255',
            'goal_value' => 'nullable|numeric',
            'goal_description' => 'nullable|string',
        ]);

        $goal->update($validated);

        return redirect()->route('goals.index')->with('success', 'Goal updated successfully.');
    }

    /**
     * Remove the specified goal from storage.
     */
    public function destroy(Goal $goal)
    {
        $goal->delete();

        return redirect()->route('goals.index')->with('success', 'Goal deleted successfully.');
    }
}
