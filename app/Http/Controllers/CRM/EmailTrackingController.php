<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\EmailMessage;
use App\Models\CRM\EmailStat;
use App\Events\CRM\CommunicationUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailTrackingController extends Controller
{
    /**
     * Handle Resend Webhooks for tracking events.
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        $type = $payload['type'] ?? null;
        $data = $payload['data'] ?? [];
        
        // Resend sends tags in 'tags' array
        $tags = $data['tags'] ?? [];
        $messageId = null;
        
        foreach ($tags as $tag) {
            if ($tag['name'] === 'message_id') {
                $messageId = $tag['value'];
                break;
            }
        }

        if (!$messageId || !$type) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $message = EmailMessage::find($messageId);
        if (!$message) {
            Log::warning("Webhook received for unknown message ID: {$messageId}");
            return response()->json(['status' => 'not_found'], 200);
        }

        // Map Resend events to CRM events
        $eventMap = [
            'email.opened' => 'open',
            'email.clicked' => 'click',
            'email.bounced' => 'bounce',
            'email.delivered' => 'delivered',
            'email.complained' => 'spam',
        ];

        $eventType = $eventMap[$type] ?? null;

        if ($eventType) {
            EmailStat::create([
                'message_id' => $message->id,
                'event_type' => $eventType,
                'occurred_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'payload' => json_encode($data['click'] ?? []), // Store URL if it's a click
            ]);

            // Update message status if relevant
            if (in_array($eventType, ['bounce', 'delivered'])) {
                $message->update(['status' => $eventType]);
            }

            // Dispatch real-time update event
            $userId = $message->thread->account->user_id ?? 1;
            event(new CommunicationUpdated($userId, [
                'type' => 'interaction',
                'event' => $eventType,
                'message_id' => $message->id,
            ]));
        }

        return response()->json(['status' => 'processed'], 200);
    }
}
