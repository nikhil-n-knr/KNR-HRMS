<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\CampaignRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    public function trackOpen($id, Request $request)
    {
        try {
            $recipient = CampaignRecipient::find($id);

            if ($recipient) {
                // Record event
                \App\Models\CRM\CampaignEvent::create([
                    'campaign_id' => $recipient->campaign_id,
                    'contact_id' => $recipient->contact_id,
                    'event_type' => 'open',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                if (!$recipient->opened_at) {
                    // Update recipient first open
                    $recipient->update(['opened_at' => now()]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Tracking Open Error: " . $e->getMessage());
        }

        // Return 1x1 transparent GIF
        return response(base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw=='))
            ->header('Content-Type', 'image/gif')
            ->header('Content-Length', '43')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    public function trackClick($id, Request $request)
    {
        $url = $request->query('url', '/'); // Default to home if no URL

        try {
            $recipient = CampaignRecipient::find($id);

            if ($recipient) {
                // Record event
                \App\Models\CRM\CampaignEvent::create([
                    'campaign_id' => $recipient->campaign_id,
                    'contact_id' => $recipient->contact_id,
                    'event_type' => 'click',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'metadata' => ['url' => $url]
                ]);

                if (!$recipient->clicked_at) {
                    // Update recipient first click
                    $recipient->update(['clicked_at' => now()]);
                }

                // Also mark as opened if not already
                if (!$recipient->opened_at) {
                     $recipient->update(['opened_at' => now()]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Tracking Click Error: " . $e->getMessage());
        }

        return redirect()->away($url);
    }
}
