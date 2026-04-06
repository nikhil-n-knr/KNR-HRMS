<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\CampaignStep;
use Illuminate\Http\Request;

class CampaignStepController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'campaign_journey_id' => 'required|exists:crm_campaign_journeys,id',
            'type' => 'required|string',
            'order_index' => 'required|integer',
        ]);

        $step = CampaignStep::create($request->all());
        return response()->json($step, 201);
    }

    public function update(Request $request, CampaignStep $campaignStep)
    {
        $campaignStep->update($request->all());
        return response()->json($campaignStep);
    }

    public function destroy(CampaignStep $campaignStep)
    {
        $campaignStep->delete();
        return response()->json(['message' => 'Step deleted']);
    }
}
