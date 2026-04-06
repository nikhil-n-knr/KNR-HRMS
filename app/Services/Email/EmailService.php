<?php

namespace App\Services\Email;

use App\Services\Email\Contracts\EmailProviderInterface;
use App\Services\Email\Providers\ResendProvider;
use App\Services\Email\Providers\SparkPostProvider;
use App\Services\Infrastructure\LoggerService;
use Exception;
use Illuminate\Support\Facades\Log;

class EmailService
{
    protected $provider;
    protected $logger;
    protected $currentProviderName;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
        $this->resolveProvider();
    }

    protected function resolveProvider()
    {
        $this->currentProviderName = env('EMAIL_PROVIDER', 'resend'); // Default to resend

        switch ($this->currentProviderName) {
            case 'sparkpost':
                $this->provider = new SparkPostProvider();
                break;
            case 'resend':
            default:
                $this->provider = new ResendProvider();
                break;
        }
    }

    public function send(array $payload)
    {
        return $this->executeWithRetry(function () use ($payload) {
            $this->logger->log('email_service', 'send', "Sending email via {$this->currentProviderName} to " . json_encode($payload['to']));
            return $this->provider->send($payload);
        });
    }

    public function sendBatch(array $payloads)
    {
        return $this->executeWithRetry(function () use ($payloads) {
            $count = count($payloads);
            $this->logger->log('email_service', 'batch', "Sending batch of {$count} emails via {$this->currentProviderName}");
            return $this->provider->sendBatch($payloads);
        });
    }

    protected function executeWithRetry(callable $action, $attempts = 3, $sleepMs = 100)
    {
        return retry($attempts, function () use ($action) {
            try {
                return $action();
            } catch (Exception $e) {
                $this->logger->log('email_service', 'error', "Attempt failed: " . $e->getMessage());
                throw $e;
            }
        }, $sleepMs);
    }
}
