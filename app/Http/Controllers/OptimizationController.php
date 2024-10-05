<?php

namespace App\Http\Controllers;

use App\Models\OptimizationCategory;
use App\Models\OptimizationCriteria;
use App\Models\CriteriaSolution;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Str;

class OptimizationController extends Controller
{
    public function index()
    {
        // Hent alle kategorier med relaterte kriterier og løsninger
        // Eager load for å unngå N+1 problemet
        // https://laravel.com/docs/8.x/eloquent-relationships#eager-loading
        // belongs to project
        $categories = OptimizationCategory::where('project_code', Project::where('team_id', Auth::user()->current_team_id)->first()->project_code)
            ->with('criterias')
            ->get();


        return Inertia::render('Optimization/Index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return Inertia::render('Optimization/Create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;
        $project_code = Project::where('team_id', $currentTeamId)->first()->project_code;

        $request->validate([
            'category_name' => 'required|string|max:255',
        ]);

        $existingCategories = OptimizationCategory::where('project_code', $project_code)
            ->whereIn('category_name', array_map('trim', explode(',', $request->category_name)))
            ->pluck('category_name')
            ->toArray();

        if (count($existingCategories) > 0) {
            return redirect()->route('optimization.index')
                ->with('error', 'Category(ies) already exist: ' . implode(', ', $existingCategories));
        }

        $categoryNames = array_map('trim', explode(',', $request->category_name));
        $createdCategories = [];

        foreach ($categoryNames as $name) {
            $category = OptimizationCategory::create([
                'category_name' => $name,
                'category_uuid' => Str::uuid(),
                'project_code' => $project_code,
            ]);
            $createdCategories[] = $category->category_name;
        }

        return redirect()->route('optimization.index')
            ->with('success', 'Categories created successfully: ' . implode(', ', $createdCategories));
    }

    // Funksjon for å vise detaljer om en kategori
    public function show($category_uuid)
    {
        $category = OptimizationCategory::where('category_uuid', $category_uuid)->firstOrFail();
        $criteria = OptimizationCriteria::where('category_uuid', $category_uuid)->get();

        return Inertia::render('Optimization/Show', [
            'category' => $category,
            'criteria' => $criteria,
        ]);
    }

    // Funksjon for å vise redigeringsskjema for en kategori
    public function edit($category_uuid)
    {
        // where category_uuid = $category_uuid, and where project_code = current selected project code
        $category = OptimizationCategory::where('category_uuid', $category_uuid)
            ->where('project_code', Project::where('team_id', Auth::user()->current_team_id)->first()->project_code)
            ->firstOrFail();


        return Inertia::render('Optimization/Edit', [
            'category' => $category,
        ]);
    }

    // Funksjon for å oppdatere en kategori
    public function update(Request $request, $category_uuid)
    {
        $category = OptimizationCategory::where('category_uuid', $category_uuid)->firstOrFail();
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;
        $project_code = Project::where('team_id', $currentTeamId)->first()->project_code;

        // Sjekk om kategorien tilhører det nåværende prosjektet
        if ($category->project_code != $project_code) {
            return redirect()->back()->with('error', 'You are not authorized to update this category.');
        }

        $request->validate([
            'category_name' => 'required|string|max:255',
        ]);

        // Oppdatere kategoriens navn
        $category->category_name = $request->category_name;
        if ($category->save()) {
            return redirect()->route('optimization.index')->with('success', 'Category updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to update category.');
        }
    }

    /**
     * Slett en kategori.
     */
    public function destroy($category_uuid)
    {
        $category = OptimizationCategory::where('category_uuid', $category_uuid)->firstOrFail();
        $category->delete();

        return redirect()->route('optimization.index')
            ->with('success', 'Category deleted successfully.');
    }
}
