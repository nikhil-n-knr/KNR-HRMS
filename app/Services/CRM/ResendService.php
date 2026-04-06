<?php

namespace App\Services\CRM;

use App\Models\CRM\EmailMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResendService
{
    protected string $apiKey;
    protected array $config;

    public function __construct()
    {
        $this->config = config('services.resend');
        $this->apiKey = $this->config['key'];
    }

    /**
     * Send an email via Resend and return the tracking ID.
     */
    public function send(EmailMessage $message): ?string
    {
        $payload = [
            'from' => "{$this->config['from']['name']} <{$this->config['from']['address']}>",
            'to' => $message->to_emails,
            'subject' => $message->thread->subject ?? 'CRM Notification',
            'html' => $message->body_html,
            'text' => $message->body_text,
            'tags' => [
                [
                    'name' => 'message_id',
                    'value' => (string) $message->id,
                ],
                [
                    'name' => 'tenant_id',
                    'value' => (string) ($message->thread->tenant_id ?? 0),
                ]
            ],
            'headers' => [
                'X-Entity-Ref-ID' => (string) $message->id,
            ]
        ];

        if (!empty($message->cc_emails)) {
            $payload['cc'] = $message->cc_emails;
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->post('https://api.resend.com/emails', $payload);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("Email sent successfully via Resend. ID: {$data['id']}");
                return $data['id'];
            }

            Log::error("Failed to send email via Resend: " . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error("Resend API communication error: " . $e->getMessage());
            return null;
        }
    }
}
