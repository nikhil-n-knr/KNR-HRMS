<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\CRM\Account;
use App\Models\CRM\Deal;
use App\Models\CRM\MarketingCampaign;
use App\Models\CRM\ABMAccount;
use App\Models\CRM\RevenueAttribution;
use App\Models\CRM\MarketingFlow;
use App\Models\CRM\ProductImportHistory;
use App\Models\CRM\ChannelStat;
use Carbon\Carbon;

class CRMMarketingFeaturesSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Seed CRM Permissions
        $sections = [
            'Dashboard', 'Leads', 'Contacts', 'Accounts', 'Segments', 
            'Sales', 'Quotes', 'Forecasting', 'Marketing', 'MarketingPro', 
            'RevenueTracker', 'InsightsEngine', 'ABMCommand', 'ConfigHub', 
            'Reports', 'Partners', 'Achievements', 'RelationshipMap'
        ];

        foreach ($sections as $sec) {
            \App\Models\Permission::updateOrCreate(
                ['module' => 'CRM', 'submodule' => $sec, 'action' => 'View'],
                ['description' => "View permission for CRM $sec submodule"]
            );
            \App\Models\Permission::updateOrCreate(
                ['module' => 'CRM', 'submodule' => $sec, 'action' => 'Manage'],
                ['description' => "Manage permission for CRM $sec submodule"]
            );
        }

        $tenant = Tenant::first() ?: Tenant::create(['name' => 'Default Tenant', 'domain' => 'default.com']);
        $user = User::first();
        
        // Ensure we have some accounts for ABM
        $accounts = Account::where('tenant_id', $tenant->id)->get();
        if ($accounts->isEmpty()) {
            $accounts = collect([
                Account::create(['tenant_id' => $tenant->id, 'name' => 'EdTechX Pvt Ltd', 'industry' => 'Education']),
                Account::create(['tenant_id' => $tenant->id, 'name' => 'FinLeap Solutions', 'industry' => 'Finance']),
                Account::create(['tenant_id' => $tenant->id, 'name' => 'HealthScale Inc', 'industry' => 'Healthcare']),
            ]);
        }

        // Ensure we have some campaigns for attribution
        $campaigns = MarketingCampaign::where('tenant_id', $tenant->id)->get();
        if ($campaigns->isEmpty()) {
            $campaigns = collect([
                MarketingCampaign::create([
                    'tenant_id' => $tenant->id, 
                    'name' => 'Q1 Global Launch', 
                    'subject' => 'Huge Savings Await!', 
                    'type' => 'Full Funnel', 
                    'status' => 'active',
                    'created_by' => $user->id ?? 1
                ]),
                MarketingCampaign::create([
                    'tenant_id' => $tenant->id, 
                    'name' => 'WhatsApp Retention', 
                    'subject' => 'We miss you!', 
                    'type' => 'Retention', 
                    'status' => 'active',
                    'created_by' => $user->id ?? 1
                ]),
            ]);
        }

        // Ensure we have some deals
        $deals = Deal::where('tenant_id', $tenant->id)->get();
        if ($deals->isEmpty()) {
             $deals = collect([
                Deal::create(['tenant_id' => $tenant->id, 'name' => 'Enterprise License - EdTechX', 'value' => 2500000, 'account_id' => $accounts[0]->id]),
                Deal::create(['tenant_id' => $tenant->id, 'name' => 'Strategic Hub - FinLeap', 'value' => 12000000, 'account_id' => $accounts[1]->id]),
             ]);
        }

        // 1. Seed ABM Accounts
        foreach ($accounts as $acc) {
            ABMAccount::updateOrCreate(
                ['account_id' => $acc->id],
                [
                    'tenant_id' => $tenant->id,
                    'abm_score' => rand(70, 98),
                    'status' => collect(['target', 'engaged', 'qualified', 'champion'])->random(),
                    'target_value' => rand(100000, 5000000),
                    'assigned_rep_id' => $user->id ?? null,
                    'journey_map' => [
                        ['date' => '2026-01-10', 'type' => 'Research', 'status' => 'completed'],
                        ['date' => '2026-01-15', 'type' => 'Personalized Outreach', 'status' => 'completed'],
                        ['date' => '2026-02-01', 'type' => 'AR Demo', 'status' => 'active'],
                        ['date' => '2026-03-01', 'type' => 'Contract', 'status' => 'pending'],
                    ]
                ]
            );
        }

        // 2. Seed Revenue Attribution
        foreach ($deals as $deal) {
            foreach (['first_touch', 'mid_touch', 'last_touch'] as $type) {
                RevenueAttribution::create([
                    'tenant_id' => $tenant->id,
                    'deal_id' => $deal->id,
                    'campaign_id' => $campaigns->random()->id,
                    'channel' => collect(['WhatsApp', 'Email', 'Voice', 'Social', 'AR Demo'])->random(),
                    'touchpoint_type' => $type,
                    'attributed_amount' => $deal->value * ($type === 'last_touch' ? 0.5 : 0.25),
                    'interaction_path' => [
                        ['step' => 'WhatsApp Click', 'weight' => '40%'],
                        ['step' => 'Email Open', 'weight' => '25%'],
                        ['step' => 'Voice Call', 'weight' => '20%'],
                        ['step' => 'AR Demo', 'weight' => '15%'],
                    ]
                ]);
            }
        }

        // 3. Seed Marketing Flows
        MarketingFlow::updateOrCreate(
            ['name' => 'Enterprise High-Value Nurture'],
            [
                'tenant_id' => $tenant->id,
                'nodes' => [
                    ['id' => '1', 'type' => 'trigger', 'data' => ['label' => 'WhatsApp Incoming: "Quote"']],
                    ['id' => '2', 'type' => 'logic', 'data' => ['label' => 'Score > 70']],
                    ['id' => '3', 'type' => 'action', 'data' => ['label' => 'Voice AI Intro Call']],
                ],
                'edges' => [
                    ['id' => 'e1-2', 'source' => '1', 'target' => '2'],
                    ['id' => 'e2-3', 'source' => '2', 'target' => '3'],
                ],
                'total_executions' => 1247,
                'conversions' => 286,
                'revenue_impact' => 42000000,
                'is_active' => true
            ]
        );

        // 4. Seed Product Import History
        ProductImportHistory::create([
            'tenant_id' => $tenant->id,
            'file_name' => 'legacy_products_export_2026.csv',
            'status' => 'processed',
            'total_rows' => 540,
            'ai_insights' => [
                ['product' => 'LMS Pro', 'revenue_share' => '67%', 'churn' => '12%'],
                ['bundle' => 'LMS + HRMS', 'ltv_impact' => '3.5x'],
                ['opportunity' => 'Upsell VR Modules', 'predicted_lift' => '24%']
            ],
            'mapping_data' => ['sku' => 'product_id', 'price' => 'unit_cost', 'desc' => 'marketing_copy']
        ]);

        // 5. Seed Channel Stats
        $channels = ['WhatsApp', 'Email', 'Voice', 'Social', 'Display'];
        foreach ($channels as $chan) {
            ChannelStat::create([
                'tenant_id' => $tenant->id,
                'channel' => $chan,
                'spend' => rand(50000, 200000),
                'revenue' => rand(500000, 5000000),
                'leads_generated' => rand(100, 1000),
                'roi_percentage' => rand(200, 3000),
                'recorded_at' => Carbon::now()
            ]);
        }
        // 6. Seed Quote Data
        \App\Models\CRM\Quote::updateOrCreate(
            ['quote_number' => 'QT-2026-001'],
            [
                'tenant_id' => $tenant->id,
                'deal_id' => $deals->first()->id,
                'title' => 'Enterprise License Quote',
                'total' => 124000,
                'status' => 'accepted',
                'created_by' => $user->id ?? 1,
                'valid_until' => \Carbon\Carbon::now()->addDays(30)
            ]
        );

        // 7. Seed Relationships for Influence Map
        $contacts = \App\Models\CRM\Contact::where('tenant_id', $tenant->id)->take(2)->get();
        if ($contacts->count() >= 2) {
            \App\Models\CRM\ContactRelationship::create([
                'tenant_id' => $tenant->id,
                'contact_id' => $contacts[0]->id,
                'related_contact_id' => $contacts[1]->id,
                'relation_type' => 'Manager',
                'strength' => 95,
                'notes' => 'Sarah reports directly to Mike.'
            ]);
        }

        // 8. Seed Achievement Stats for Users
        \App\Models\CRM\UserStat::updateOrCreate(
            ['user_id' => $user->id ?? 1, 'tenant_id' => $tenant->id],
            ['total_points' => 1450, 'deals_won_count' => 12]
        );
    }
}
