<?php

namespace App\Contracts\Banking;

use App\Models\Payroll;
use App\Models\Payslip;

interface BankServiceInterface
{
    /**
     * Authenticate and initiate a connection with the bank's H2H server.
     */
    public function authenticate(): bool;

    /**
     * Initiate a single payout transfer via REST API.
     */
    public function initiateSinglePayout(Payslip $payslip, array $options = []): array;

    /**
     * Initiate a bulk batch payout transfer.
     */
    public function initiateBatchPayout(Payroll $payroll, array $payslipIds): array;

    /**
     * Check status of a previously initiated payout.
     */
    public function checkStatus(string $payoutReference): array;

    /**
     * Handle incoming webhooks/callbacks from the bank.
     */
    public function handleCallback(array $payload): bool;
}
