<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CRM\Deal;
use App\Models\CRM\Account;
use App\Models\CRM\Contact;
use App\Models\CRM\PipelineStage;
use Faker\Factory as Faker;
use Carbon\Carbon;

class PipelineSeeder extends Seeder
{
    public function run()
    {
        $tenantId = 1;
        $userId = 1;
        $faker = Faker::create();

        // 1. Create realistic accounts if needed
        $accountNames = [
            'Global Tech Industries', 'NexGen Systems', 'Starlight Logistics', 'Pacific Finance',
            'Quantum Health', 'Blue Horizon Media', 'Standard Manufacturing', 'Apex Retail Solutions',
            'Infinite Cloud Corp', 'Vertex Real Estate', 'Titan Automotive', 'Summit Energy'
        ];

        $accounts = [];
        foreach ($accountNames as $name) {
            $accounts[] = Account::updateOrCreate(
                ['name' => $name, 'tenant_id' => $tenantId],
                [
                    'industry' => $faker->randomElement(['Technology', 'Finance', 'Healthcare', 'Energy', 'Logistics']),
                    'website' => 'https://www.' . strtolower(str_replace(' ', '', $name)) . '.com',
                    'size' => $faker->randomElement(['50-200', '201-500', '501-1000', '1000+']),
                    'annual_revenue' => $faker->randomFloat(2, 5000000, 50000000),
                    'created_by' => $userId,
                ]
            );
        }

        // 2. Create contacts
        $contacts = [];
        foreach ($accounts as $account) {
            for ($i = 0; $i < 2; $i++) {
                $contacts[] = Contact::updateOrCreate(
                    ['email' => $faker->unique()->safeEmail, 'tenant_id' => $tenantId],
                    [
                        'account_id' => $account->id,
                        'first_name' => $faker->firstName,
                        'last_name' => $faker->lastName,
                        'phone' => $faker->phoneNumber,
                        'title' => $faker->randomElement(['CEO', 'VP Sales', 'CTO', 'Operations Director', 'Lead Architect']),
                        'status' => 'active',
                        'created_by' => $userId,
                    ]
                );
            }
        }

        // 3. Get Stages
        $stages = PipelineStage::where('tenant_id', $tenantId)->get();
        if ($stages->isEmpty()) return;

        // 4. Create many deals (50+)
        $prefixes = ['Enterprise', 'Strategic', 'Phase 1', 'Global', 'Direct', 'Expansion', 'Renewal', 'Modular'];
        $suffixes = ['License', 'Implementation', 'Integration', 'Support', 'Hardware Bundle', 'SaaS Access', 'Transformation'];

        for ($i = 0; $i < 60; $i++) {
            $stage = $stages->random();
            $account = $faker->randomElement($accounts);
            $title = $faker->randomElement($prefixes) . " " . $faker->randomElement($suffixes) . " - " . $account->name;
            
            $value = $faker->randomFloat(2, 10000, 250000);
            $probability = $stage->win_probability;
            
            $status = 'open';
            if ($stage->type === 'won') $status = 'won';
            if ($stage->type === 'lost') $status = 'lost';

            Deal::create([
                'tenant_id' => $tenantId,
                'account_id' => $account->id,
                'contact_id' => Contact::where('account_id', $account->id)->first()?->id,
                'title' => $title,
                'description' => 'Targeting strategic deployment for ' . $account->name . '. Requirements include high-scalability and security compliance.',
                'value' => $value,
                'weighted_value' => $value * ($probability / 100),
                'currency' => 'USD',
                'stage' => $stage->name,
                'probability' => $probability,
                'health_score' => $faker->numberBetween(30, 98),
                'expected_close_date' => Carbon::now()->addDays($faker->numberBetween(-20, 120)),
                'status' => $status,
                'created_by' => $userId,
                'assigned_to' => $userId,
                'tags' => [$faker->randomElement(['premium', 'urgent', 'strategic']), $faker->randomElement(['q3', 'q4', 'growth'])],
            ]);
        }
    }
}
