<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use App\Models\CRM\Product;
use App\Models\CRM\ProductCategory;
use App\Models\CRM\PipelineStage;
use App\Models\CRM\CrmCustomField;
use App\Models\Workflow;
use App\Models\SystemSetting;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class ConfigLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'products' => $this->loadProducts(),
            'stages' => $this->loadStages(),
            'custom_fields' => $this->loadCustomFields(),
            'workflows' => $this->loadWorkflows(),
            'email_settings' => $this->loadEmailSettings(),
            'permissions' => $this->loadPermissions(),
            default => [],
        };
    }

    private function loadProducts()
    {
        $allCategories = ProductCategory::where('tenant_id', $this->tenantId)->get();
        return [
            'products' => Product::where('tenant_id', $this->tenantId)
                ->with('category')
                ->latest()
                ->paginate(50),
            'categories' => $this->buildCategoryTree($allCategories),
        ];
    }

    private function loadStages()
    {
        return [
            'stages' => PipelineStage::where('tenant_id', $this->tenantId)->orderBy('order')->get(),
        ];
    }

    private function loadCustomFields()
    {
        return [
            'custom_fields' => CrmCustomField::where('tenant_id', $this->tenantId)->orderBy('order')->get(),
        ];
    }

    private function loadWorkflows()
    {
        return [
            'workflows' => \App\Models\CRM\CRMWorkflow::where('tenant_id', $this->tenantId)
                ->with('stages')
                ->get(),
        ];
    }

    private function loadEmailSettings()
    {
        $settings = SystemSetting::whereIn('key', [
            'crm.smtp_host', 'crm.smtp_port', 'crm.smtp_encryption', 'crm.sender_email', 'crm.sender_name'
        ])->pluck('value', 'key');

        return [
            'settings' => [
                'smtp_host' => $settings['crm.smtp_host'] ?? '',
                'smtp_port' => $settings['crm.smtp_port'] ?? '',
                'smtp_encryption' => $settings['crm.smtp_encryption'] ?? 'tls',
                'sender_email' => $settings['crm.sender_email'] ?? '',
                'sender_name' => $settings['crm.sender_name'] ?? '',
            ]
        ];
    }

    private function loadPermissions()
    {
        return [
            'roles' => Role::where('tenant_id', $this->tenantId)->with('permissions')->get(),
            'all_permissions' => Permission::where('module', 'CRM')->get(),
            'users' => User::where('tenant_id', $this->tenantId)->with('roles')->get(['id', 'name', 'email']),
        ];
    }

    private function buildCategoryTree($elements, $parentId = null)
    {
        $branch = [];
        foreach ($elements as $element) {
            if ($element->parent_id == $parentId) {
                $children = $this->buildCategoryTree($elements, $element->id);
                $element->children = $children;
                $branch[] = $element;
            }
        }
        return $branch;
    }
}
