<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignLink;
use App\Models\CampaignLinkClick;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    /**
     * Display a listing of the campaigns.
     */
    public function index()
    {
        // Fetch campaigns for the project related to the user's current team
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        // Get projects associated with the current team
        $project_code = Project::where('team_id', $currentTeamId)->pluck('project_code');

        // Fetch campaigns related to those projects
        $campaigns = Campaign::whereIn('project_code', $project_code)
            ->with('project')
            ->where('created_by', Auth::id())
            ->get();

        return Inertia::render('Campaigns/Index', [
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Show the form for creating a new campaign.
     */
    public function create()
    {
        // Fetch campaigns for the project related to the user's current team
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        // Get projects associated with the current team
        $projects = Project::where('team_id', $currentTeamId)->pluck('project_code');


        return Inertia::render('Campaigns/Create', [
            'projects' => $projects,
        ]);
    }

    /**
     * Store a newly created campaign in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'campaign_name' => 'required|string',
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
            'reporting' => 'boolean',
            'force_lowercase' => 'boolean',
            'utm_activated' => 'boolean',
            'monitor_urls' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $campaign = new Campaign();
        $campaign->campaign_name = $request->campaign_name;
        $campaign->project_code = $this->getProjectCode($request); // Get project_code from the project associated with the selected team
        $campaign->campaign_token = Str::random(8); // Generate a unique token
        $campaign->created_by = auth()->id();
        $campaign->start = $request->start;
        $campaign->end = $request->end;
        $campaign->status = $request->status;
        $campaign->reporting = $request->reporting;
        $campaign->force_lowercase = $request->force_lowercase;
        $campaign->utm_activated = $request->utm_activated;
        $campaign->monitor_urls = $request->monitor_urls;
        $campaign->description = $request->description;

        if ($campaign->save()) {
            return redirect()->route('campaigns.index')->with('success', 'Campaign created successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to create campaign.');
        }
    }

    /**
     * Display the specified campaign.
     */
    public function show($campaign_token)
    {
        // Fetch the campaign by ID and ensure it belongs to the authenticated user
        $campaign = Campaign::where('campaign_token', $campaign_token)->where('created_by', Auth::id())->firstOrFail();

        // Fetch links associated with the campaign
        $links = CampaignLink::where('campaign_id', $campaign->id)
            ->paginate(10); // Paginering av lenkene

        // Fetch link clicks associated with the campaign
        $clicks = CampaignLinkClick::whereIn('link_token', $links->pluck('link_token'))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        //dd($clicks);

        // Return data to the Inertia page
        return Inertia::render('Campaigns/Show', [
            'campaign' => $campaign,
            'links' => $links,
            'clicks' => $clicks,
        ]);
    }

    /**
     * Show the form for editing the specified campaign.
     */
    public function edit($id)
    {
        // Fetch the campaign by ID and ensure it belongs to the authenticated user
        $campaign = Campaign::where('id', $id)->where('created_by', Auth::id())->firstOrFail();

        // Fetch projects associated with the authenticated user
        $projects = Project::where('owner_id', Auth::id())->get();

        return Inertia::render('Campaigns/Edit', [
            'campaign' => $campaign,
            'projects' => $projects,
        ]);
    }

    /**
     * Update the specified campaign in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'campaign_name' => 'required|string',
            'project_code' => 'required|string',
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
            'status' => 'boolean',
            'reporting' => 'boolean',
            'force_lowercase' => 'boolean',
            'utm_activated' => 'boolean',
            'monitor_urls' => 'boolean',
            'description' => 'nullable|string',
        ]);

        // Fetch the campaign by ID and ensure it belongs to the authenticated user
        $campaign = Campaign::where('id', $id)->where('created_by', Auth::id())->firstOrFail();

        // Update campaign properties
        $campaign->campaign_name = $request->campaign_name;
        $campaign->project_code = $this->getProjectCode($request);
        $campaign->start = $request->start;
        $campaign->end = $request->end;
        $campaign->status = $request->status;
        $campaign->reporting = $request->reporting;
        $campaign->force_lowercase = $request->force_lowercase;
        $campaign->utm_activated = $request->utm_activated;
        $campaign->monitor_urls = $request->monitor_urls;
        $campaign->description = $request->description;

        // Save changes
        if ($campaign->save()) {
            return redirect()->route('campaigns.index')->with('success', 'Campaign updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to update campaign.');
        }
    }

    /**
     * Remove the specified campaign from storage.
     */
    public function destroy(Campaign $campaign)
    {
        //$this->authorize('delete', $campaign);

        $campaign->delete();

        return redirect()->route('campaigns.index')->with('success', 'Campaign deleted successfully.');
    }

    /**
     * Generate a unique 8-character campaign token.
     */
    private function generateUniqueToken()
    {
        do {
            $token = Str::random(8);
        } while (Campaign::where('campaign_token', $token)->exists());

        return $token;
    }

    public function getProjectCode(Request $request)
    {
        // Get the current team ID from the authenticated user
        $currentTeamId = Auth::user()->current_team_id;

        // Retrieve the project associated with the current team ID
        $project = Project::where('team_id', $currentTeamId)->first();

        // Check if the project exists and return the project code
        if ($project) {
            $projectCode = $project->project_code;
            return $projectCode;
        }
    }
}
