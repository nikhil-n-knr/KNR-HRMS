<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TaxRegime;
use App\Models\TaxSlab;
use App\Models\TaxSection;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Tax Sections
        $sections = [
            ['name' => 'Section 80C (PPF, EPF, LIC, ELSS)', 'section_code' => '80C', 'max_deduction' => 150000, 'is_active' => true],
            ['name' => 'Section 80D (Medical Insurance)', 'section_code' => '80D', 'max_deduction' => 75000, 'is_active' => true],
            ['name' => 'Section 80CCD(1B) (NPS)', 'section_code' => '80CCD(1B)', 'max_deduction' => 50000, 'is_active' => true],
            ['name' => 'House Rent Allowance (HRA)', 'section_code' => '10(13A)', 'max_deduction' => null, 'is_active' => true],
            ['name' => 'Interest on Home Loan', 'section_code' => '24(b)', 'max_deduction' => 200000, 'is_active' => true],
            ['name' => 'Section 80E (Education Loan Interest)', 'section_code' => '80E', 'max_deduction' => null, 'is_active' => true],
            ['name' => 'Standard Deduction', 'section_code' => '16(ia)', 'max_deduction' => 75000, 'is_active' => true], // Updated for FY25
        ];

        foreach ($sections as $section) {
            TaxSection::updateOrCreate(
                ['section_code' => $section['section_code']],
                $section
            );
        }

        // 2. Tax Regimes
        $regimes = [
            'New' => 'New Tax Regime (FY 2024-25)',
            'Old' => 'Old Tax Regime'
        ];

        foreach ($regimes as $key => $name) {
            // Check if exists by name
            $regime = TaxRegime::where('name', $key)->first();
            if (!$regime) {
                TaxRegime::create([
                    'name' => $key,
                    // 'description' => $name, // Column doesn't exist
                    'is_default' => ($key === 'New') // Default to New
                ]);
            }
        }

        // 3. Tax Slabs (FY 2024-25)
        
        // Clear old slabs to prevent duplication if running multiple times without fresh db
        TaxSlab::truncate();

        $newRegime = TaxRegime::where('name', 'New')->first();
        $oldRegime = TaxRegime::where('name', 'Old')->first();

        // New Regime Slabs
        // 0-3L: 0%
        // 3-7L: 5% (Rebate typically applies up to 7L, making tax 0, but slab rate is 5%)
        // 7-10L: 10%
        // 10-12L: 15%
        // 12-15L: 20%
        // 15L+: 30%
        $newSlabs = [
            ['min' => 0,       'max' => 300000,   'rate' => 0],
            ['min' => 300000,  'max' => 700000,   'rate' => 5],
            ['min' => 700000,  'max' => 1000000,  'rate' => 10],
            ['min' => 1000000, 'max' => 1200000,  'rate' => 15],
            ['min' => 1200000, 'max' => 1500000,  'rate' => 20],
            ['min' => 1500000, 'max' => null,     'rate' => 30],
        ];

        foreach ($newSlabs as $slab) {
            TaxSlab::create([
                'regime_id' => $newRegime->id, // Correct column
                'min_income' => $slab['min'],
                'max_income' => $slab['max'],
                'tax_rate_percentage' => $slab['rate']
            ]);
        }

        // Old Regime Slabs (General Citizen < 60)
        // 0-2.5L: 0%
        // 2.5-5L: 5%
        // 5-10L: 20%
        // 10L+: 30%
        $oldSlabs = [
            ['min' => 0,      'max' => 250000,   'rate' => 0],
            ['min' => 250000, 'max' => 500000,   'rate' => 5],
            ['min' => 500000, 'max' => 1000000,  'rate' => 20],
            ['min' => 1000000, 'max' => null,    'rate' => 30],
        ];

        foreach ($oldSlabs as $slab) {
            TaxSlab::create([
                'regime_id' => $oldRegime->id, // Correct column
                'min_income' => $slab['min'],
                'max_income' => $slab['max'],
                'tax_rate_percentage' => $slab['rate']
            ]);
        }
        
        $this->command->info('Tax Regimes and Slabs Seeded Successfully.');
    }
}
