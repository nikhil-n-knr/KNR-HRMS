<?php

namespace App\Services\Email\Providers;

use App\Services\Email\Contracts\EmailProviderInterface;
use Illuminate\Support\Facades\Http;
use Exception;

class SparkPostProvider implements EmailProviderInterface
{
    protected $apiKey;
    protected $baseUrl = 'https://api.sparkpost.com/api/v1';

    public function __construct()
    {
        $this->apiKey = config('services.sparkpost.secret');
        if (!$this->apiKey) {
            throw new Exception("SparkPost API Key is missing.");
        }
    }

    public function send(array $payload)
    {
        // SparkPost Transmission API Format
        $body = [
            'content' => [
                'from' => $payload['from'],
                'subject' => $payload['subject'],
                'html' => $payload['html']
            ],
            'recipients' => array_map(function($email) {
                return ['address' => $email];
            }, (array) $payload['to'])
        ];

        return $this->post('/transmissions', $body);
    }

    public function sendBatch(array $payloads)
    {
        // SparkPost supports batch via 'recipients' list with distinct substitution data, 
        // OR simply sending one huge payload. 
        // For simplicity and alignment with the Resend 'Batch' concept (multiple distinct emails),
        // we might loop or use their bulk endpoint.
        // However, Resend's batch sends *different* emails. SparkPost transmission usually sends *same* email to many.
        // To match Resend structure [[from, to, html], [from, to, html]], we iterate.
        // Or if SparkPost has a true batch endpoint. Assuming explicit loop for mismatched payloads 
        // as SparkPost Transmissions refer to template+list usually.
        
        $responses = [];
        foreach ($payloads as $payload) {
            $responses[] = $this->send($payload);
        }
        return $responses;
    }

    protected function post($endpoint, $data)
    {
        $response = Http::withToken($this->apiKey)
            ->post($this->baseUrl . $endpoint, $data);

        return $response->json();
    }
}
