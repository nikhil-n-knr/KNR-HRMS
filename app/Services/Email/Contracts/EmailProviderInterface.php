<?php

namespace App\Services\Email\Contracts;

interface EmailProviderInterface
{
    /**
     * Send a single email.
     *
     * @param array $payload should contain 'from', 'to', 'subject', 'html'
     * @return mixed Provider response
     */
    public function send(array $payload);

    /**
     * Send a batch of emails.
     *
     * @param array $payloads Array of email payloads
     * @return mixed Provider response
     */
    public function sendBatch(array $payloads);
}
