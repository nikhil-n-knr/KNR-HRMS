<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CRM\ProductCategory;
use App\Models\CRM\Product;
use App\Models\CRM\PipelineStage;
use App\Models\CRM\CrmCustomField;
use App\Models\CRM\CRMWorkflow;

class CrmConfigurationSeeder extends Seeder
{
    public function run()
    {
        $tenantId = 1; // standard tenant

        // 1. Pipeline Stages
        $stages = [
            ['name' => 'Lead In', 'color' => '#6366f1', 'order' => 1, 'win_probability' => 10, 'is_default' => 1, 'type' => 'open'],
            ['name' => 'Contact Made', 'color' => '#8b5cf6', 'order' => 2, 'win_probability' => 25, 'is_default' => 0, 'type' => 'open'],
            ['name' => 'Qualification', 'color' => '#ec4899', 'order' => 3, 'win_probability' => 40, 'is_default' => 0, 'type' => 'open'],
            ['name' => 'Need Analysis', 'color' => '#f59e0b', 'order' => 4, 'win_probability' => 60, 'is_default' => 0, 'type' => 'open'],
            ['name' => 'Value Proposition', 'color' => '#10b981', 'order' => 5, 'win_probability' => 75, 'is_default' => 0, 'type' => 'open'],
            ['name' => 'Negotiation', 'color' => '#06b6d4', 'order' => 6, 'win_probability' => 90, 'is_default' => 0, 'type' => 'open'],
            ['name' => 'Closed Won', 'color' => '#059669', 'order' => 7, 'win_probability' => 100, 'is_default' => 0, 'type' => 'won'],
            ['name' => 'Closed Lost', 'color' => '#ef4444', 'order' => 8, 'win_probability' => 0, 'is_default' => 0, 'type' => 'lost'],
        ];
        
        foreach ($stages as $stage) {
            PipelineStage::updateOrCreate(
                ['name' => $stage['name'], 'tenant_id' => $tenantId],
                array_merge($stage, ['is_active' => true])
            );
        }

        // 2. Custom Fields
        $fields = [
            ['entity_type' => 'App\Models\CRM\Lead', 'label' => 'Industry Focus', 'name' => 'industry_focus', 'type' => 'text', 'is_required' => 0],
            ['entity_type' => 'App\Models\CRM\Deal', 'label' => 'Competitor', 'name' => 'competitor', 'type' => 'text', 'is_required' => 0],
            ['entity_type' => 'App\Models\CRM\Account', 'label' => 'Annual Revenue', 'name' => 'annual_revenue', 'type' => 'number', 'is_required' => 0],
            ['entity_type' => 'App\Models\CRM\Contact', 'label' => 'Preferred Contact Method', 'name' => 'preferred_contact_method', 'type' => 'dropdown', 'is_required' => 0, 'options' => json_encode(['Email', 'Phone', 'LinkedIn'])],
        ];

        foreach ($fields as $field) {
            CrmCustomField::updateOrCreate(
                ['name' => $field['name'], 'entity_type' => $field['entity_type'], 'tenant_id' => $tenantId],
                $field
            );
        }

        // 3. Product Categories (Hierarchical)
        $parentCats = [
            ['name' => 'SaaS Solutions', 'description' => 'Scalable cloud-based enterprise software'],
            ['name' => 'Hardware Architecture', 'description' => 'Core infrastructure and networking devices'],
            ['name' => 'Strategic Services', 'description' => 'High-level consulting and implementation'],
        ];

        foreach ($parentCats as $cat) {
            $parent = ProductCategory::updateOrCreate(
                ['name' => $cat['name'], 'tenant_id' => $tenantId],
                ['description' => $cat['description']]
            );

            // Sub-categories
            if ($cat['name'] === 'SaaS Solutions') {
                ProductCategory::updateOrCreate(['name' => 'Security Suite', 'parent_id' => $parent->id, 'tenant_id' => $tenantId]);
                ProductCategory::updateOrCreate(['name' => 'Core Platform', 'parent_id' => $parent->id, 'tenant_id' => $tenantId]);
            }
            if ($cat['name'] === 'Hardware Architecture') {
                ProductCategory::updateOrCreate(['name' => 'Edge Computing', 'parent_id' => $parent->id, 'tenant_id' => $tenantId]);
                ProductCategory::updateOrCreate(['name' => 'Cluster Nodes', 'parent_id' => $parent->id, 'tenant_id' => $tenantId]);
            }
        }

        // 4. Products (Realistic Payload)
        $products = [
            ['sku' => 'SW-CORE-ENT', 'name' => 'Enterprise OS v4.0', 'type' => 'digital', 'base_price' => 12500, 'cost_price' => 2000, 'cat_name' => 'Core Platform'],
            ['sku' => 'SW-SEC-NODE', 'name' => 'Sentinel Guard Node', 'type' => 'digital', 'base_price' => 4500, 'cost_price' => 500, 'cat_name' => 'Security Suite'],
            ['sku' => 'HW-EDGE-X1', 'name' => 'Edge-X1 Micro Server', 'type' => 'physical', 'base_price' => 2800, 'cost_price' => 1500, 'cat_name' => 'Edge Computing'],
            ['sku' => 'SRV-STR-CONS', 'name' => 'Architecture Consulting (Phase 1)', 'type' => 'service', 'base_price' => 8500, 'cost_price' => 1200, 'cat_name' => 'Strategic Services'],
            ['sku' => 'HW-NODE-Q', 'name' => 'Quantum Cluster Interface', 'type' => 'physical', 'base_price' => 15000, 'cost_price' => 9000, 'cat_name' => 'Cluster Nodes'],
        ];

        foreach ($products as $p) {
            $cat = ProductCategory::where('name', $p['cat_name'])->first();
            Product::updateOrCreate(
                ['sku' => $p['sku'], 'tenant_id' => $tenantId],
                [
                    'name' => $p['name'],
                    'type' => $p['type'],
                    'base_price' => $p['base_price'],
                    'cost_price' => $p['cost_price'],
                    'category_id' => $cat ? $cat->id : null,
                    'stock_qty' => ($p['type'] === 'physical' ? 25 : 99999),
                    'track_inventory' => ($p['type'] === 'physical'),
                    'description' => 'High-performance ' . $p['name'] . ' for mission-critical deployments.',
                ]
            );
        }

        // 5. Workflows
        $workflows = [
            ['name' => 'Lead Inbound Notification', 'trigger_event' => 'created', 'entity' => 'App\Models\CRM\Lead'],
            ['name' => 'Deal Progression Alert', 'trigger_event' => 'updated', 'entity' => 'App\Models\CRM\Deal'],
            ['name' => 'High Value Deal Warning', 'trigger_event' => 'updated', 'entity' => 'App\Models\CRM\Deal'],
            ['name' => 'Post-Sale Handover', 'trigger_event' => 'updated', 'entity' => 'App\Models\CRM\Deal'],
        ];
        foreach ($workflows as $wf) {
            CRMWorkflow::updateOrCreate(
                ['name' => $wf['name'], 'tenant_id' => $tenantId],
                [
                    'description' => 'Automated protocol for ' . $wf['name'],
                    'trigger_event' => $wf['trigger_event'],
                    'entity_type' => $wf['entity'],
                    'is_active' => true,
                    'priority' => 1
                ]
            );
        }

        // 6. Roles & Permissions (Custom Models)
        $roles = [
            ['name' => 'CRM Architect', 'slug' => 'crm_architect', 'module' => 'crm'],
            ['name' => 'Sales Intelligence Analyst', 'slug' => 'sales_intel', 'module' => 'crm'],
            ['name' => 'Marketing Operations', 'slug' => 'marketing_ops', 'module' => 'marketing'],
        ];
        foreach ($roles as $r) {
            \App\Models\Role::firstOrCreate(
                ['slug' => $r['slug']],
                ['name' => $r['name'], 'tenant_id' => $tenantId, 'is_system' => false, 'created_by' => 1]
            );
        }

        // 7. Email Settings
        $emailSettings = [
            'crm.smtp_host' => 'smtp.mailtrap.io',
            'crm.smtp_port' => '2525',
            'crm.smtp_encryption' => 'tls',
            'crm.sender_email' => 'crm-intel@company.system',
            'crm.sender_name' => 'CRM Intelligence Engine',
        ];
        foreach ($emailSettings as $key => $value) {
            \App\Models\SystemSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
