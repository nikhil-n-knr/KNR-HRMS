<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\MarketingCampaign;
use Illuminate\Http\Request;

class MarketingCampaignController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $campaigns = MarketingCampaign::where('tenant_id', $tenantId)
            ->with(['template', 'segment'])
            ->latest()
            ->paginate(10);
            
        return response()->json($campaigns);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'status' => 'required|in:draft,scheduled',
            'template_id' => 'nullable|exists:crm_marketing_templates,id',
            'segment_id' => 'nullable|exists:crm_contact_segments,id',
            'scheduled_at' => 'nullable|date',
            'content' => 'nullable|string',
        ]);

        $campaign = MarketingCampaign::create([
            'tenant_id' => auth()->user()->tenant_id,
            'created_by' => auth()->id(),
            ...$request->all()
        ]);

        return response()->json($campaign, 201);
    }
    
    public function show(MarketingCampaign $marketingCampaign)
    {
        $marketingCampaign->load(['template', 'segment', 'recipients']);
        return response()->json($marketingCampaign);
    }

    public function update(Request $request, MarketingCampaign $marketingCampaign)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
        ]);

        $marketingCampaign->update($request->all());

        return response()->json($marketingCampaign);
    }

    public function destroy(MarketingCampaign $marketingCampaign)
    {
        $marketingCampaign->delete();
        return response()->json(['message' => 'Campaign deleted']);
    }

    /**
     * Send the campaign immediately
     */
    public function send(MarketingCampaign $marketingCampaign)
    {
        if ($marketingCampaign->status !== 'draft' && $marketingCampaign->status !== 'scheduled') {
            return response()->json(['message' => 'Campaign already sent or processing'], 400);
        }

        $marketingCampaign->update(['status' => 'processing', 'sent_at' => now()]);

        // 1. Resolve recipients
        if ($marketingCampaign->segment) {
            $contacts = $marketingCampaign->segment->getContactsQuery()->get();
        } else {
            // Send to ALL contacts if no segment selected
            $contacts = \App\Models\CRM\Contact::where('tenant_id', $marketingCampaign->tenant_id)->get();
        }

        $count = 0;
        foreach ($contacts as $contact) {
            if (empty($contact->email)) continue;

            // 2. Create recipient record
            $recipient = \App\Models\CRM\CampaignRecipient::create([
                'campaign_id' => $marketingCampaign->id,
                'contact_id' => $contact->id,
                'recipient_email' => $contact->email,
                'recipient_name' => $contact->first_name . ' ' . $contact->last_name,
                'status' => 'pending',
            ]);

            // 3. Dispatch job
            \App\Jobs\CRM\SendCampaignEmail::dispatch($marketingCampaign, $recipient);
            $count++;
        }

        // Initialize stats
        $marketingCampaign->update([
            'stats' => ['total' => $count, 'sent' => 0, 'opened' => 0, 'clicked' => 0]
        ]);

        return response()->json(['message' => "Campaign dispatching started for {$count} recipients.", 'campaign' => $marketingCampaign]);
    }
}
