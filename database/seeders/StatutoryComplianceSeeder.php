<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatutoryComplianceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantId = \App\Models\Tenant::first()->id ?? 1;

        // 1. Locations
        \App\Models\Location::updateOrCreate(['code' => 'BLR-HQ'], [
            'tenant_id' => $tenantId,
            'name' => 'Bangalore HQ',
            'city' => 'Bangalore',
            'state' => 'Karnataka',
            'state_code' => 'KA',
            'pt_enabled' => true,
            'lwf_enabled' => true,
            'is_hq' => true,
        ]);

        \App\Models\Location::updateOrCreate(['code' => 'CHN-BR'], [
            'tenant_id' => $tenantId,
            'name' => 'Chennai Branch',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'state_code' => 'TN',
            'pt_enabled' => true,
            'lwf_enabled' => true,
        ]);

        \App\Models\Location::updateOrCreate(['code' => 'DEL-BR'], [
            'tenant_id' => $tenantId,
            'name' => 'Delhi Branch',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'state_code' => 'DL',
            'pt_enabled' => false,
            'lwf_enabled' => true,
        ]);

        // 2. Compliance Rules (KA - Karnataka)
        \App\Models\ComplianceStateRule::updateOrCreate(
            ['state_code' => 'KA', 'component' => 'professional_tax'],
            ['rules' => [
                'type' => 'monthly',
                'slabs' => [
                    ['min' => 0, 'max' => 15000, 'amount' => 0],
                    ['min' => 15001, 'max' => 9999999, 'amount' => 200]
                ],
                'max_annual' => 2400
            ]]
        );

        \App\Models\ComplianceStateRule::updateOrCreate(
            ['state_code' => 'KA', 'component' => 'lwf'],
            ['rules' => [
                'frequency' => 'annually',
                'month' => 12,
                'employee_share' => 50,
                'employer_share' => 100
            ]]
        );

        // 3. Compliance Rules (TN - Tamil Nadu)
        \App\Models\ComplianceStateRule::updateOrCreate(
            ['state_code' => 'TN', 'component' => 'professional_tax'],
            ['rules' => [
                'type' => 'half_yearly',
                'max_amount' => 1250,
                'slabs' => [
                    ['min' => 0, 'max' => 21000, 'amount' => 0],
                    ['min' => 21001, 'max' => 30000, 'amount' => 135],
                    ['min' => 30001, 'max' => 45000, 'amount' => 315],
                    ['min' => 45001, 'max' => 60000, 'amount' => 690],
                    ['min' => 60001, 'max' => 75000, 'amount' => 1025],
                    ['min' => 75001, 'max' => 9999999, 'amount' => 1250]
                ]
            ]]
        );

        \App\Models\ComplianceStateRule::updateOrCreate(
            ['state_code' => 'TN', 'component' => 'lwf'],
            ['rules' => [
                'frequency' => 'annually',
                'month' => 12,
                'employee_share' => 20,
                'employer_share' => 40
            ]]
        );

        // 4. Compliance Rules (TS - Telangana / Hyderabad)
        \App\Models\ComplianceStateRule::updateOrCreate(
            ['state_code' => 'TS', 'component' => 'professional_tax'],
            ['rules' => [
                'type' => 'monthly',
                'slabs' => [
                    ['min' => 0, 'max' => 15000, 'amount' => 0],
                    ['min' => 15001, 'max' => 20000, 'amount' => 150],
                    ['min' => 20001, 'max' => 9999999, 'amount' => 200]
                ]
            ]]
        );

        \App\Models\ComplianceStateRule::updateOrCreate(
            ['state_code' => 'TS', 'component' => 'lwf'],
            ['rules' => [
                'frequency' => 'annually',
                'month' => 12,
                'employee_share' => 24,
                'employer_share' => 48
            ]]
        );

        // 5. Compliance Rules (KL - Kerala)
        \App\Models\ComplianceStateRule::updateOrCreate(
            ['state_code' => 'KL', 'component' => 'professional_tax'],
            ['rules' => [
                'type' => 'half_yearly',
                'max_amount' => 1250,
                'slabs' => [
                    ['min' => 0, 'max' => 11999, 'amount' => 0],
                    ['min' => 12000, 'max' => 17999, 'amount' => 120],
                    ['min' => 18000, 'max' => 29999, 'amount' => 180],
                    ['min' => 30000, 'max' => 44999, 'amount' => 300],
                    ['min' => 45000, 'max' => 59999, 'amount' => 450],
                    ['min' => 60000, 'max' => 74999, 'amount' => 600],
                    ['min' => 75000, 'max' => 99999, 'amount' => 750],
                    ['min' => 100000, 'max' => 124999, 'amount' => 1000],
                    ['min' => 125000, 'max' => 9999999, 'amount' => 1250]
                ]
            ]]
        );

        \App\Models\ComplianceStateRule::updateOrCreate(
            ['state_code' => 'KL', 'component' => 'lwf'],
            ['rules' => [
                'frequency' => 'monthly',
                'employee_share' => 45,
                'employer_share' => 45
            ]]
        );

        // 6. Compliance Rules (DL - Delhi)
        \App\Models\ComplianceStateRule::updateOrCreate(
            ['state_code' => 'DL', 'component' => 'lwf'],
            ['rules' => [
                'frequency' => 'half_yearly',
                'months' => [6, 12],
                'employee_share' => 30,
                'employer_share' => 90
            ]]
        );

        // 7. Minimum Wages (KA - Bangalore Zone 1)
        \App\Models\MinimumWage::updateOrCreate(
            ['state_code' => 'KA', 'skill_level' => 'Highly Skilled', 'industry' => 'IT/Commercial'],
            [
                'zone' => 'Zone 1',
                'basic_wage' => 15423.40,
                'vda' => 4113.60,
                'effective_from' => '2025-04-01'
            ]
        );

        // 8. Licences
        if (\App\Models\Location::where('code', 'BLR-HQ')->exists()) {
            \App\Models\ComplianceLicence::updateOrCreate(
                ['name' => 'Karnataka Shops & Establishments Registration'],
                [
                    'state_code' => 'KA',
                    'location_id' => \App\Models\Location::where('code', 'BLR-HQ')->first()->id,
                    'expiry_date' => '2026-12-31',
                    'document_path' => 'compliance/licences/ka_shops_est.pdf'
                ]
            );
        }

        if (\App\Models\Location::where('code', 'CHN-BR')->exists()) {
            \App\Models\ComplianceLicence::updateOrCreate(
                ['name' => 'GHMC Trade Licence'],
                [
                    'state_code' => 'TS',
                    'location_id' => \App\Models\Location::where('code', 'CHN-BR')->first()->id, // Reusing Chennai for TS for demo
                    'expiry_date' => '2026-06-30',
                    'document_path' => 'compliance/licences/ts_trade.pdf'
                ]
            );
        }
    }
}
