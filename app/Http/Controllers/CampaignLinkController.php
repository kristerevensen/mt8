<?php

namespace App\Http\Controllers;

use App\Models\CampaignLink;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CampaignLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($campaignToken)
    {
        // Get the current user's team and associated projects
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;


        // Fetch links related to those campaigns
        $links = CampaignLink::whereIn('campaign_token', $campaignToken)->get();

        return Inertia::render('CampaignLinks/Index', [
            'links' => $links,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fetch the campaigns related to the current team
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        // Get campaigns associated with the current team
        $campaigns = Campaign::whereHas('project', function ($query) use ($currentTeamId) {
            $query->where('team_id', $currentTeamId);
        })->get();

        return Inertia::render('CampaignLinks/Create', [
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'link_name' => 'required|string',
            'url' => 'required|url',
            'campaign_id' => 'required|exists:campaigns,id',
            'description' => 'nullable|string',
        ]);

        $link = new CampaignLink();
        $link->link_name = $request->link_name;
        $link->url = $request->url;
        $link->campaign_id = $request->campaign_id;
        $link->description = $request->description;

        if ($link->save()) {
            return redirect()->route('campaign-links.index')->with('success', 'Link created successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to create link.');
        }
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
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $link = CampaignLink::where('id', $id)->firstOrFail();

        // Get campaigns related to the current team
        $user = Auth::user();
        $currentTeamId = $user->current_team_id;

        $campaigns = Campaign::whereHas('project', function ($query) use ($currentTeamId) {
            $query->where('team_id', $currentTeamId);
        })->get();

        return Inertia::render('CampaignLinks/Edit', [
            'link' => $link,
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'link_name' => 'required|string',
            'url' => 'required|url',
            'campaign_id' => 'required|exists:campaigns,id',
            'description' => 'nullable|string',
        ]);

        $link = CampaignLink::where('id', $id)->firstOrFail();
        $link->link_name = $request->link_name;
        $link->url = $request->url;
        $link->campaign_id = $request->campaign_id;
        $link->description = $request->description;

        if ($link->save()) {
            return redirect()->route('campaign-links.index')->with('success', 'Link updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to update link.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $link = CampaignLink::where('id', $id)->firstOrFail();
        $link->delete();

        return redirect()->route('campaign-links.index')->with('success', 'Link deleted successfully.');
    }
}
