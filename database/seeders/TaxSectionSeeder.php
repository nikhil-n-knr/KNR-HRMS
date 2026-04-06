<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            ['name' => 'Section 80C (PPF, EPF, LIC, ELSS)', 'section_code' => '80C', 'max_deduction' => 150000],
            ['name' => 'Section 80D (Medical Insurance)', 'section_code' => '80D', 'max_deduction' => 75000], // 25k self + 50k parents
            ['name' => 'Section 80CCD(1B) (NPS)', 'section_code' => '80CCD(1B)', 'max_deduction' => 50000],
            ['name' => 'House Rent Allowance (HRA)', 'section_code' => '10(13A)', 'max_deduction' => null], // Calc based
            ['name' => 'Interest on Home Loan', 'section_code' => '24(b)', 'max_deduction' => 200000],
            ['name' => 'Section 80E (Education Loan Interest)', 'section_code' => '80E', 'max_deduction' => null],
            ['name' => 'Standard Deduction', 'section_code' => '16(ia)', 'max_deduction' => 50000],
        ];

        foreach ($sections as $section) {
            \App\Models\TaxSection::updateOrCreate(
                ['section_code' => $section['section_code']],
                $section
            );
        }
    }
}
