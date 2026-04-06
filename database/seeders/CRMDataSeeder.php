<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CRM\Lead;
use App\Models\CRM\Deal;
use App\Models\CRM\Account;
use App\Models\CRM\Contact;
use App\Models\CRM\Activity;
use App\Models\CRM\Partner;
use App\Models\CRM\ContactRelationship;
use App\Models\Tenant;
use App\Models\User;

class CRMDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first() ?? Tenant::factory()->create(['name' => 'Acme Corp']);
        $user = User::where('tenant_id', $tenant->id)->first() ?? User::factory()->create(['tenant_id' => $tenant->id, 'name' => 'CRM Admin']);

        // 1. Seed Partners first
        $partners = Partner::factory()->count(5)->create(['tenant_id' => $tenant->id]);

        // 2. Seed Accounts and Contacts if needed
        $accountCount = Account::where('tenant_id', $tenant->id)->count();
        if ($accountCount < 10) {
            Account::factory()->count(10 - $accountCount)->create(['tenant_id' => $tenant->id, 'created_by' => $user->id])
                ->each(function ($account) use ($tenant, $user) {
                    $contacts = Contact::factory()->count(rand(2, 4))->create([
                        'tenant_id' => $tenant->id,
                        'account_id' => $account->id,
                        'created_by' => $user->id
                    ]);

                    // Create some relationships within the account or between contacts
                    if ($contacts->count() >= 2) {
                        ContactRelationship::factory()->create([
                            'tenant_id' => $tenant->id,
                            'contact_id' => $contacts[0]->id,
                            'related_contact_id' => $contacts[1]->id,
                            'relation_type' => 'Colleague'
                        ]);
                    }
                });
        }

        // 3. Seed Leads with Partner attribution if needed
        $leadCount = Lead::where('tenant_id', $tenant->id)->count();
        if ($leadCount < 20) {
            Lead::factory()->count(20 - $leadCount)->create([
                'tenant_id' => $tenant->id,
                'partner_id' => $this->faker()->boolean(30) && $partners->isNotEmpty() ? $partners->random()->id : null
            ]);
        }

        // 4. Seed Deals linked to Accounts/Contacts if needed
        $accounts = Account::where('tenant_id', $tenant->id)->get();
        $contacts = Contact::where('tenant_id', $tenant->id)->get();
        $dealCount = Deal::where('tenant_id', $tenant->id)->count();

        if ($dealCount < 15 && $accounts->isNotEmpty() && $contacts->isNotEmpty()) {
            Deal::factory()->count(15 - $dealCount)->create([
                'tenant_id' => $tenant->id,
                'account_id' => function() use ($accounts) { return $accounts->random()->id; },
                'contact_id' => function() use ($contacts) { return $contacts->random()->id; },
            ]);
        }

        // 5. Seed Activities for Leads, Contacts, and Deals
        $leads = Lead::where('tenant_id', $tenant->id)->get();
        $deals = Deal::where('tenant_id', $tenant->id)->get();

        foreach($leads as $lead) {
            Activity::factory()->count(rand(2, 5))->create([
                'tenant_id' => $tenant->id,
                'activityable_type' => Lead::class,
                'activityable_id' => $lead->id,
                'created_by' => $user->id
            ]);
        }

        foreach($deals as $deal) {
            Activity::factory()->count(rand(1, 3))->create([
                'tenant_id' => $tenant->id,
                'activityable_type' => Deal::class,
                'activityable_id' => $deal->id,
                'created_by' => $user->id
            ]);
        }

        // 6. Initialize User Stats for Gamification
        \App\Models\CRM\UserStat::updateOrCreate(
            ['user_id' => $user->id, 'tenant_id' => $tenant->id],
            [
                'total_points' => 1250,
                'deals_won_count' => 5,
                'total_revenue' => 125000,
                'activities_count' => 15,
                'current_streak' => 3
            ]
        );
    }

    private function faker()
    {
        return \Faker\Factory::create();
    }
}
