<?php

namespace App\Services\Expenses;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ExpensePolicyService
{
    /**
     * Validate an expense request against the category policy.
     * Throws ValidationException if policy is breached.
     * 
     * @param array $data Input data (amount, incurred_date, etc.)
     * @param ExpenseCategory $category
     * @param int $employeeId
     */
    public function validate(array $data, ExpenseCategory $category, int $employeeId)
    {
        $settings = $category->policy_settings ?? [];
        $amount = $data['amount'];
        $date = Carbon::parse($data['incurred_date']);
        
        // 1. Min Amount Check
        if (isset($settings['min_amount']) && $amount < $settings['min_amount']) {
            throw ValidationException::withMessages([
                'amount' => "Minimum claim amount is ₹{$settings['min_amount']}. Small expenses are not reimbursable."
            ]);
        }

        // 2. Transaction Cap (Hard Cap)
        // Note: category->hard_limit exists as column, but we might override or use specific 'per_transaction_limit' from JSON
        $hardLimit = $settings['per_transaction_limit'] ?? $category->hard_limit;
        if ($hardLimit && $amount > $hardLimit) {
             throw ValidationException::withMessages([
                'amount' => "Amount exceeds the limit of ₹{$hardLimit} for this category."
            ]);
        }

        // 3. Backdating Rule
        $maxDays = $settings['max_claim_age_days'] ?? $category->max_days_backdating ?? 60;
        if ($date->diffInDays(now(), false) > $maxDays) {
             throw ValidationException::withMessages([
                'incurred_date' => "Claims older than {$maxDays} days are not accepted."
            ]);
        }

        // 4. Future Dating
        if ($date->isFuture()) {
             throw ValidationException::withMessages([
                'incurred_date' => "Cannot claim for future dates."
            ]);
        }

        // 5. Frequency Cap (Max claims per day)
        if (isset($settings['max_claims_per_day'])) {
            $count = Expense::where('employee_id', $employeeId)
                ->where('expense_category_id', $category->id)
                ->whereDate('incurred_date', $date)
                ->count();
            
            if ($count >= $settings['max_claims_per_day']) {
                 throw ValidationException::withMessages([
                    'incurred_date' => "You can only submit {$settings['max_claims_per_day']} claim(s) per day for this category."
                ]);
            }
        }

        // 6. GST Requirement
        $gstThreshold = $settings['gst_required_above'] ?? 5000;
        if ($amount > $gstThreshold && empty($data['gst_number'])) {
             // Only if category 'require_gst' flag is true OR we force it via threshold logic?
             // User requirement: "For amounts > 5000, force user".
             if (!($data['gst_number'] ?? null)) {
                  throw ValidationException::withMessages([
                    'gst_number' => "GST Number is mandatory for expenses above ₹{$gstThreshold}."
                ]);
             }
        }
        
        // 7. Receipt Requirement
        $receiptThreshold = $settings['receipt_required_above'] ?? ($category->requires_bill_proof ? 0 : 999999);
        if ($amount > $receiptThreshold && empty($data['receipt']) && empty($data['receipt_path'])) {
             // Note: Controller handles file upload, $data['receipt'] might be file object.
             // If validation runs before upload, we check request->hasFile. Here we assume passed data.
             throw ValidationException::withMessages([
                'receipt' => "Receipt is mandatory for expenses above ₹{$receiptThreshold}."
            ]);
        }
    }

    /**
     * Calculate Reimbursable Amount (e.g. Mileage)
     */
    public function calculateLikelyAmount(array $data, ExpenseCategory $category)
    {
        $settings = $category->policy_settings ?? [];
        
        // Mileage Type
        if (($settings['type'] ?? '') === 'mileage' && isset($data['distance_km'])) {
            $rate = $settings['mileage_rate'] ?? 0;
            return $data['distance_km'] * $rate;
        }
        
        // Per Diem
        if (($settings['type'] ?? '') === 'per_diem') {
             return $settings['daily_rate'] ?? 0;
        }

        return $data['amount'] ?? 0;
    }
}
