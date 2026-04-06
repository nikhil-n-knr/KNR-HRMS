<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ComplianceRule;

class ComplianceRulesSeeder extends Seeder
{
    public function run()
    {
        // 1. Provident Fund (PF)
        ComplianceRule::create([
            'component' => 'PF',
            'effective_from' => '2024-04-01',
            'rules_json' => [
                'wage_ceiling' => 15000,
                'employee_contribution_rate' => 12.00,
                'employer_contribution_rate' => 12.00,
                'employer_pension_share' => 8.33,
                'employer_epf_share' => 3.67,
                'admin_charges_rate' => 0.50,
                'edli_charges_rate' => 0.50,
                'restrict_employer_share_to_ceiling' => true,
                'allow_uncapped_employee_share' => true
            ],
            'description' => 'Standard PF Rules FY 2024-25'
        ]);

        // 2. ESI
        ComplianceRule::create([
            'component' => 'ESI',
            'effective_from' => '2024-04-01',
            'rules_json' => [
                'wage_ceiling' => 21000,
                'employee_contribution_rate' => 0.75,
                'employer_contribution_rate' => 3.25,
                'enable_for_directors' => false
            ],
            'description' => 'Standard ESI Rules'
        ]);

        // 3. Professional Tax (Karnataka Default)
        // Note: Real PT is state-wise. We store a "Default" or specific state slabs here.
        // Usually PT needs a separate table for State Slabs, but for "Rules Engine" we can store structure.
        // For MVP upgrade: Storing generic rule structure.
        ComplianceRule::create([
            'component' => 'PT',
            'effective_from' => '2024-04-01',
            'rules_json' => [
                'slabs' => [
                    ['min' => 0, 'max' => 14999, 'amount' => 0],
                    ['min' => 15000, 'max' => 999999, 'amount' => 200]
                ],
                'state' => 'Karnataka' // Example
            ],
            'description' => 'Karnataka PT Slabs'
        ]);
    }
}
