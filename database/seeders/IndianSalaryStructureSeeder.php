<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalaryStructure;
use App\Models\SalaryComponent;

class IndianSalaryStructureSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Structure
        $structure = SalaryStructure::create([
            'name' => 'Review (Indian Standard)',
            'description' => 'Standard salary structure with Basic (50%), HRA, PF, and Special Allowance.',
            'is_active' => true,
        ]);

        // 2. Define Components
        $components = [
            // EARNINGS (Sum to 100% of CTC)
            [
                'name' => 'Basic Salary', 
                'type' => 'earning',
                'calculation_type' => 'percentage', // of CTC
                'value' => 50.00,
                'formula' => null,
                'order' => 1
            ],
            [
                'name' => 'HRA', 
                'type' => 'earning',
                'calculation_type' => 'percentage', // of CTC
                'value' => 20.00,
                'formula' => null,
                'order' => 2
            ],
            [
                'name' => 'PF (Employer Contribution)', 
                'type' => 'earning', // Included in CTC
                'calculation_type' => 'percentage',
                'value' => 6.00, // 12% of Basic (12% of 50% = 6%)
                'formula' => null,
                'order' => 3 
            ],
            [
                'name' => 'Special Allowance', 
                'type' => 'earning',
                'calculation_type' => 'percentage',
                'value' => 24.00, // Balancing: 100 - 50 - 20 - 6 = 24
                'formula' => null,
                'order' => 4
            ],

            // DEDUCTIONS (From Gross)
            [
                'name' => 'PF (Employee)', 
                'type' => 'deduction',
                'calculation_type' => 'percentage', 
                'value' => 6.00, // 12% of Basic (Simulated as 6% of CTC)
                'formula' => null, 
                'order' => 10
            ],
            [
                'name' => 'Professional Tax', 
                'type' => 'deduction',
                'calculation_type' => 'fixed',
                'value' => 200.00,
                'formula' => null,
                'order' => 11
            ],
            [
                'name' => 'Income Tax (TDS)', 
                'type' => 'deduction',
                'calculation_type' => 'fixed',
                'value' => 0.00,
                'formula' => null,
                'order' => 12
            ]
        ];

        foreach ($components as $comp) {
            $structure->components()->create($comp);
        }

        $this->command->info('Indian Standard Salary Structure Seeded!');
    }
}
