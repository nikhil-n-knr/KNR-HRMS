<?php

namespace App\Services\Email\Providers;

use App\Services\Email\Contracts\EmailProviderInterface;
use Exception;

class ResendProvider implements EmailProviderInterface
{
    protected $client;

    public function __construct()
    {
        $apiKey = config('services.resend.key');
        if (!$apiKey) {
            throw new Exception("Resend API Key is missing.");
        }
        $this->client = \Resend::client($apiKey);
    }

    public function send(array $payload)
    {
        return $this->client->emails->send($payload);
    }

    public function sendBatch(array $payloads)
    {
        return $this->client->batch->send($payloads);
    }
}
