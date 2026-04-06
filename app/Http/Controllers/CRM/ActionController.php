<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActionController extends Controller
{
    /**
     * Trigger a Voice AI Call.
     */
    public function voiceCall(Request $request, Lead $lead)
    {
        // Logic to interface with Voice AI service would go here
        Log::info("Voice AI Call triggered for Lead: {$lead->id} reaching {$lead->phone}");

        // Create an activity entry
        $lead->activities()->create([
            'tenant_id' => $lead->tenant_id,
            'type' => 'call',
            'subject' => 'Voice AI Outreach Initiated',
            'description' => 'Automated Voice AI call was triggered from the Leads dashboard.',
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Voice AI call initiated successfully.');
    }

    /**
     * Trigger a WhatsApp message flow.
     */
    public function whatsappMessage(Request $request, Lead $lead)
    {
        // Logic to interface with WhatsApp Business API would go here
        Log::info("WhatsApp nudge triggered for Lead: {$lead->id} on {$lead->phone}");

        $lead->activities()->create([
            'tenant_id' => $lead->tenant_id,
            'type' => 'sms', // Using sms type for WhatsApp as placeholder
            'subject' => 'WhatsApp Nudge Sent',
            'description' => 'A WhatsApp engagement message was triggered from the dashboard.',
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'WhatsApp message sent.');
    }

    /**
     * Adjust lead score manually.
     */
    public function adjustScore(Request $request, Lead $lead)
    {
        $adjustment = $request->input('adjustment', 0);
        $lead->increment('score', $adjustment);

        return back()->with('success', "Lead score adjusted by {$adjustment}.");
    }

    /**
     * Enroll in nurture sequence.
     */
    public function enrollNurture(Request $request, Lead $lead)
    {
        // Logic to enroll in Automation sequences
        Log::info("Lead {$lead->id} enrolled in nurture sequence.");

        $lead->activities()->create([
            'tenant_id' => $lead->tenant_id,
            'type' => 'note',
            'subject' => 'Enrolled in Nurture Sequence',
            'description' => 'Lead has been placed in an automated drip campaign.',
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Nurture sequence started.');
    }
}
