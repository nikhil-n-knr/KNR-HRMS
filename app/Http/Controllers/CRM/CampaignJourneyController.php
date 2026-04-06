<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\CampaignJourney;
use Illuminate\Http\Request;

class CampaignJourneyController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        return response()->json(CampaignJourney::where('tenant_id', $tenantId)->withCount('steps')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:broadcast,drip',
        ]);

        $journey = CampaignJourney::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $request->name,
            'type' => $request->type,
            'status' => 'draft',
        ]);

        return redirect()->back()->with('success', 'Campaign Journey draft created.');
    }

    public function update(Request $request, CampaignJourney $campaignJourney)
    {
        $campaignJourney->update($request->all());
        return response()->json($campaignJourney);
    }

    public function destroy(CampaignJourney $campaignJourney)
    {
        $campaignJourney->delete();
        return response()->json(['message' => 'Journey deleted']);
    }
}
