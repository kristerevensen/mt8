<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Project;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

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
        $project_code = Project::where('team_id', $currentTeamId)->pluck('project_code');

        // Fetch goals with pagination based on project_code
        $goals = Goal::whereIn('project_code', $project_code)->paginate(10);

        return Inertia::render('Goals/Index', [
            'goals' => $goals,
        ]);
    }

    /**
     * Show the form for creating a new goal.
     */
    public function create()
    {
        // Hent den autentiserte brukeren og det nåværende team ID
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        // Hent prosjektkoder assosiert med det nåværende teamet
        $project_code = Project::where('team_id', $currentTeamId)->pluck('project_code');

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
            'project_code' => 'required|string|max:255',
            'goal_name' => 'required|string|max:255',
            'goal_type' => 'required|string|max:255',
            'goal_value' => 'nullable|numeric',
            'goal_description' => 'nullable|string',
        ]);

        // Hent den autentiserte brukeren og det nåværende team ID
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        // Hent prosjektkoder assosiert med det nåværende teamet
        $project_code = Project::where('team_id', $currentTeamId)->pluck('project_code');



        //create goal
        $goals = new Goal();
        $goals->project_code = $project_code;
        $goals->goal_name = $request->goal_name;
        $goals->goal_type = $request->goal_type;
        $goals->goal_value = $request->goal_value;
        $goals->goal_description = $request->goal_description;
        $goals->save();

        Goal::create($validated);

        return redirect()->route('goals.index')->with('success', 'Goal created successfully.');
    }

    /**
     * Update the specified goal in storage.
     */
    public function update(Request $request, Goal $goal)
    {
        $validated = $request->validate([
            'project_code' => 'required|string|max:255',
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
