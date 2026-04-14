<?php

namespace App\Services\Communication;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsAppBusinessService
{
    /**
     * Send a WhatsApp message via Meta Business API
     * 
     * @param string $to Phone number with country code
     * @param string $template Template name (as registered in Meta Dashboard)
     * @param array $components Variables to inject into the template
     */
    public function sendTemplateMessage(string $to, string $template, array $components = [])
    {
        // 1. Authenticate with Meta credentials from .env
        $token = env('META_BUSINESS_TOKEN');
        $phoneId = env('META_PHONE_NUMBER_ID');
        
        if (empty($token) || empty($phoneId)) {
            Log::warning("WhatsApp Protocol Bypassed: Meta Business Credentials missing in .env", ['to' => $to]);
            return false;
        }

        // 2. Protocol Transmission
        try {
            $response = Http::withToken($token)->post("https://graph.facebook.com/v18.0/{$phoneId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => $template,
                    'language' => ['code' => 'en_US'],
                    'components' => $components
                ]
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp nudge transmitted successfully to {$to}", ['template' => $template]);
                return true;
            }

            Log::error("WhatsApp Protocol Failed: Meta API responded with error", [
                'status' => $response->status(),
                'body' => $response->json()
            ]);
            
        } catch (\Exception $e) {
            Log::error("WhatsApp Protocol Critical: Exception during packet transmission", ['error' => $e->getMessage()]);
        }

        return false;
    }
}
