<?php

namespace App\Interfaces;

use App\Models\Payroll;

interface BankingInterface
{
    /**
     * Generate Bank Specific Payment File
     * Returns: ['content' => string, 'filename' => string, 'mime' => string]
     */
    public function generatePaymentFile(Payroll $payroll): array;

    /**
     * Parse Response File
     * Returns: ['success' => int, 'failures' => array]
     */
    public function parseResponse($filePath): array;
}
