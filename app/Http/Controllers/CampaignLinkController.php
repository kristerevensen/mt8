<?php

namespace App\Http\Controllers;

use App\Models\CampaignLink;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class CampaignLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        // Get the current user's team and associated projects
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        // Get project codes associated with the current team
        $projectCodes = Campaign::whereHas('project', function ($query) use ($currentTeamId) {
            $query->where('team_id', $currentTeamId);
        })->pluck('project_code');

        // Get campaign tokens associated with the project codes
        $campaignToken = Campaign::whereIn('project_code', $projectCodes)->pluck('id');

        // Fetch links related to those campaigns
        $links = CampaignLink::whereIn('id', $campaignToken)->get();

        return Inertia::render('CampaignLinks/Index', [
            'links' => $links,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        // Get the current team ID
        $currentTeamId = $user->current_team_id;

        //dd($request->query('campaign_token'));
        // Retrieve the campaign token from the request query
        $campaignToken = $request->query('campaign_token');  // Use the query method to retrieve the parameter

        //get campaign ID from campaign token
        $campaign = Campaign::where('campaign_token', $campaignToken)->first();
        $campaignId = $campaign->id;

        // Get campaigns related to the current team using the token
        $campaigns = Campaign::whereHas('project', function ($query) use ($currentTeamId, $campaignToken) {
            $query->where('team_id', $currentTeamId)
                ->where('campaign_token', $campaignToken);
        })->get();

        return Inertia::render('CampaignLinks/Create', [
            'campaigns' => $campaigns,
            'campaign_token' => $campaignToken,
            'campaign_id' => $campaignId,

        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //dd($request->all());

        /** Validate the request */
        $request->validate([
            'landing_page' => 'required|url', // Renamed to match the database field
            'campaign_id' => 'required',
            'source' => ['required', 'regex:/^(?!https?:\/\/)[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(\/\S*)?$/'],
            'medium' => 'required|string',
            'term' => 'nullable|string',
            'content' => 'nullable|string',
            'custom_parameters' => 'nullable|string',
            'description' => 'nullable|string',
        ]);


        $link = new CampaignLink();
        $link->landing_page = strtolower($request->landing_page);
        $link->link_token = Str::random(8); // Generate a unique link token
        $link->project_code = $this->getProjectCode($request->campaign_id); // Assuming you have a method to get project code
        $link->campaign_id = $request->campaign_id;
        $link->source = strtolower($request->source);
        $link->medium = strtolower($request->medium);
        $link->term = strtolower($request->term);
        $link->content = strtolower($request->content);
        $link->custom_parameters = $request->custom_parameters;
        $link->description = $request->description;

        // Construct the tagged URL
        $link->tagged_url = 'https://mttrack.link/' . $link->link_token;
        $campaignToken = $this->getCampaignCode($link->campaign_id);

        // Save the link and redirect
        if ($link->save()) {
            return redirect()->route('campaigns.show', ['campaign' => $campaignToken])->with('success', 'Link created successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to create link.');
        }
    }

    // Example method to get project code
    private function getProjectCode($campaignId)
    {
        $campaign = Campaign::find($campaignId);
        return $campaign ? $campaign->project_code : null;
    }

    // get campaign code from campaign id
    private function getCampaignCode($campaignId)
    {
        $campaign = Campaign::where('id', $campaignId)->first();
        return $campaign->campaign_token;
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $link = CampaignLink::where('id', $id)->firstOrFail();

        return Inertia::render('CampaignLinks/Show', [
            'link' => $link,
        ]);
    }

    /**
     * Show the form for copying the specified resource.
     */
    public function copy($linktoken)
    {
        $link = CampaignLink::where('link_token', $linktoken)->firstOrFail();

        // Fetch the campaign details using campaign_id from the link
        $campaign = Campaign::where('id', $link->campaign_id)->firstOrFail();

        // Extract the campaign_name and campaign_token
        $campaignName = $campaign->campaign_name;
        $campaignToken = $campaign->campaign_token;

        // Get campaigns related to the current team
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        $campaigns = Campaign::whereHas('project', function ($query) use ($currentTeamId) {
            $query->where('team_id', $currentTeamId);
        })->get();

        return Inertia::render('CampaignLinks/Copy', [
            'link' => $link,
            'campaigns' => $campaigns,
            'campaign_token' => $campaignToken,
            'campaign_name' => $campaignName,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($linktoken)
    {
        $link = CampaignLink::where('link_token', $linktoken)->firstOrFail();

        // Fetch the campaign details using campaign_id from the link
        $campaign = Campaign::where('id', $link->campaign_id)->firstOrFail();

        // Extract the campaign_name and campaign_token
        $campaignName = $campaign->campaign_name;
        $campaignToken = $campaign->campaign_token;

        // Get campaigns related to the current team
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        $campaigns = Campaign::whereHas('project', function ($query) use ($currentTeamId) {
            $query->where('team_id', $currentTeamId);
        })->get();

        return Inertia::render('CampaignLinks/Edit', [
            'link' => $link,
            'campaigns' => $campaigns,
            'campaign_token' => $campaignToken,
            'campaign_name' => $campaignName,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CampaignLink $campaignLink)
    {
        $request->validate([
            'landing_page' => 'required|url',
            'campaign_id' => 'required|exists:campaigns,id',
            'source' => 'nullable|string',
            'medium' => 'nullable|string',
            'term' => 'nullable|string',
            'content' => 'nullable|string',
            'custom_parameters' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $campaignLink->update([
            'landing_page' => $request->landing_page,
            'campaign_id' => $request->campaign_id,
            'source' => $request->source,
            'medium' => $request->medium,
            'term' => $request->term,
            'content' => $request->content,
            'custom_parameters' => $request->custom_parameters,
            'description' => $request->description,
        ]);

        $campaignToken = $this->getCampaignCode($request->campaign_id);
        return redirect()->route('campaigns.show', ['campaign' => $campaignToken])->with('success', 'Link created successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $link = CampaignLink::where('id', $id)->firstOrFail();
        $campaignToken = $this->getCampaignCode($link->campaign_id);
        $link->delete();

        return redirect()->route('campaigns.show', ['campaign' => $campaignToken])->with('success', 'Link created successfully.');
    }
}
