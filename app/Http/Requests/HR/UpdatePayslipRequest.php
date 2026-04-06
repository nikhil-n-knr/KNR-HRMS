<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePayslipRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('manage_payroll'); // Or simpler role check
    }

    public function rules()
    {
        return [
            'gross_earnings' => 'required|numeric|min:0',
            'earnings_breakdown' => 'required|array',
            'total_deductions' => 'required|numeric|min:0',
            'deductions_breakdown' => 'required|array',
            'net_pay' => 'required|numeric|min:0',
            'remarks' => 'nullable|string'
        ];
    }
}
