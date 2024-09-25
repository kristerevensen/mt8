<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Technical;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\TechnicalPage; // Assuming you have a model for technical pages
use Illuminate\Support\Facades\Auth;

class TechnicalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch technical pages from the database
        //$technicalPages = Technical::all();
        // Hent den autentiserte brukeren og det nåværende team ID
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        // Hent prosjektkoder assosiert med det nåværende teamet
        $project_code = Project::where('team_id', $currentTeamId)->pluck('project_code');
        if ($project_code->isEmpty()) {
            return redirect()->route('projects.index')->with('error', 'You need to create a project before you can view the technical pages.');
        }

        // Return the view with the fetched technical pages
        return Inertia::render('Technical/Index', [
            // 'technicalPages' => $technicalPages,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Return the form view for creating a new technical page
        return Inertia::render('Technical/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Create a new technical page
        Technical::create($validatedData);

        // Redirect to the index page with a success message
        return redirect()->route('technical.index')->with('success', 'Technical page created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Fetch the technical page by ID
        $technicalPage = Technical::findOrFail($id);

        // Return the view with the fetched technical page
        return Inertia::render('Technical/Show', [
            'technicalPage' => $technicalPage,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Fetch the technical page by ID
        $technicalPage = Technical::findOrFail($id);

        // Return the form view for editing the technical page
        return Inertia::render('Technical/Edit', [
            'technicalPage' => $technicalPage,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Fetch the technical page by ID
        $technicalPage = Technical::findOrFail($id);

        // Update the technical page with the validated data
        $technicalPage->update($validatedData);

        // Redirect to the index page with a success message
        return redirect()->route('technical.index')->with('success', 'Technical page updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Fetch the technical page by ID
        $technicalPage = Technical::findOrFail($id);

        // Delete the technical page
        $technicalPage->delete();

        // Redirect to the index page with a success message
        return redirect()->route('technical.index')->with('success', 'Technical page deleted successfully.');
    }
}
