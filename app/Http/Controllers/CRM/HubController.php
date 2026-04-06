<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Lead;
use App\Models\CRM\Contact;
use App\Models\CRM\Account;
use App\Models\CRM\Deal;
use App\Models\CRM\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HubController extends Controller
{
    /**
     * Display the CRM Hub with sections and tabs
     */
    public function index(Request $request)
    {
        $section = $request->get('section', 'dashboard');
        
        $defaultTabs = [
            'dashboard' => 'revenue_overview',
            'leads' => 'all_leads',
            'contacts' => 'all_contacts',
            'accounts' => 'all_accounts',
            'segments' => 'all_segments',
            'sales' => 'kanban',
            'quotes' => 'all_quotes',
            'forecasting' => 'overview',
            'marketing' => 'campaigns',
            'marketing_pro' => 'live_dashboard',
            'revenue_tracker' => 'funnel',
            'insights_engine' => 'analytics',
            'abm_command' => 'targets',
            'reports' => 'overview',
            'partners' => 'overview',
            'achievements' => 'overview',
            'communications' => 'inbox',
            'platform_config' => 'email_settings',
        ];

        $tab = $request->get('tab');
        if (!$tab) {
            $tab = $defaultTabs[$section] ?? 'overview';
        }
        $tenantId = auth()->user()->tenant_id;
        
        $data = [
            'section' => $section,
            'tab' => $tab,
        ];

        // Map sections to their respective loaders
        $loaders = [
            'dashboard' => \App\Services\CRM\Loaders\DashboardLoader::class,
            'leads' => \App\Services\CRM\Loaders\LeadLoader::class,
            'contacts' => \App\Services\CRM\Loaders\ContactLoader::class,
            'accounts' => \App\Services\CRM\Loaders\AccountLoader::class,
            'segments' => \App\Services\CRM\Loaders\ContactSegmentLoader::class,
            'sales' => \App\Services\CRM\Loaders\SalesLoader::class,
            'quotes' => \App\Services\CRM\Loaders\QuoteLoader::class,
            'forecasting' => \App\Services\CRM\Loaders\SalesForecastLoader::class,
            'marketing' => \App\Services\CRM\Loaders\MarketingLoader::class,
            'marketing_pro' => \App\Services\CRM\Loaders\MarketingProLoader::class,
            'revenue_tracker' => \App\Services\CRM\Loaders\RevenueTrackerLoader::class,
            'insights_engine' => \App\Services\CRM\Loaders\InsightsEngineLoader::class,
            'abm_command' => \App\Services\CRM\Loaders\ABMCommandLoader::class,
            'config' => \App\Services\CRM\Loaders\ConfigLoader::class, 
            'reports' => \App\Services\CRM\Loaders\DashboardLoader::class,
            'partners' => \App\Services\CRM\Loaders\PartnerLoader::class,
            'achievements' => \App\Services\CRM\Loaders\AchievementLoader::class,
            'relationship_map' => \App\Services\CRM\Loaders\RelationshipMapLoader::class,
            'communications' => \App\Services\CRM\Loaders\CommunicationLoader::class,
            'meetings' => \App\Services\CRM\Loaders\MeetingLoader::class,
        ];

        if (isset($loaders[$section])) {
            $loaderClass = $loaders[$section];
            $loader = new $loaderClass($tenantId);
            $sectionData = $loader->load($tab, $request);
            $data = array_merge($data, $sectionData);
        }

        // Global KPIs for Header/Footer
        $data['global_kpis'] = [
            'pipeline_value' => '₹' . number_format(Deal::where('tenant_id', $tenantId)->where('status', 'open')->sum('value') / 10000000, 2) . 'Cr',
            'avg_health' => round(Deal::where('tenant_id', $tenantId)->where('status', 'open')->avg('health_score') ?? 85, 0),
            'alerts_count' => rand(2, 5) // Mock alert count logic for now
        ];

        return Inertia::render('CRM/Hub', $data);
    }
    
    /**
     * Calculate lead to contact conversion rate
     */
    private function calculateConversionRate($tenantId)
    {
        $totalLeads = Lead::where('tenant_id', $tenantId)->count();
        if ($totalLeads === 0) return 0;
        
        $convertedLeads = Lead::where('tenant_id', $tenantId)
            ->whereNotNull('converted_to_contact_id')
            ->count();
        
        return round(($convertedLeads / $totalLeads) * 100, 1);
    }
    
    /**
     * Calculate deal win rate
     */
    private function calculateWinRate($tenantId)
    {
        $closedDeals = Deal::where('tenant_id', $tenantId)
            ->whereIn('stage', ['won', 'lost'])
            ->count();
        
        if ($closedDeals === 0) return 0;
        
        $wonDeals = Deal::where('tenant_id', $tenantId)
            ->where('stage', 'won')
            ->count();
        
        return round(($wonDeals /  $closedDeals) * 100, 1);
    }

    public function settings()
    {
        return redirect()->route('crm.hub', ['section' => 'config', 'tab' => 'email_settings']);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->except(['_token']);
        
        foreach ($data as $key => $value) {
            // Prefix keys with 'crm.' to namespace them
            \App\Models\SystemSetting::updateOrCreate(
                ['key' => 'crm.' . $key],
                ['value' => $value, 'group' => 'crm']
            );
        }

        return redirect()->back()->with('success', 'CRM Settings updated.');
    }

    public function togglePermission(Request $request, \App\Models\Role $role)
    {
        $request->validate(['permission_id' => 'required|exists:permissions,id']);
        
        if ($role->permissions->contains($request->permission_id)) {
            $role->permissions()->detach($request->permission_id);
        } else {
            $role->permissions()->attach($request->permission_id, [
                'data_scope' => 'tenant', // Default to tenant scope for CRM
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        return redirect()->back()->with('success', 'Permissions updated.');
    }

    public function grantMasterAccess(Request $request, \App\Models\Role $role)
    {
        $crmPermissions = \App\Models\Permission::where('module', 'CRM')->pluck('id');
        
        $role->permissions()->syncWithPivotValues($crmPermissions, [
            'data_scope' => 'tenant',
            'updated_at' => now()
        ], false); // false = sync without detaching other modules' permissions
        
        return redirect()->back()->with('success', 'Master Access granted for CRM module.');
    }

    public function exportCsv(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $type = $request->get('type', 'leads');
        
        $filename = "crm_{$type}_" . now()->format('Y-m-d') . ".csv";
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($tenantId, $type) {
            $file = fopen('php://output', 'w');
            
            if ($type === 'leads') {
                fputcsv($file, ['ID', 'Name', 'Email', 'Source', 'Status', 'Created At']);
                $leads = Lead::where('tenant_id', $tenantId)->get();
                foreach ($leads as $lead) {
                    fputcsv($file, [$lead->id, $lead->name, $lead->email, $lead->source, $lead->status, $lead->created_at]);
                }
            } elseif ($type === 'deals') {
                fputcsv($file, ['ID', 'Name', 'Value', 'Stage', 'Created At']);
                $deals = Deal::where('tenant_id', $tenantId)->get();
                foreach ($deals as $deal) {
                    fputcsv($file, [$deal->id, $deal->name, $deal->value, $deal->stage, $deal->created_at]);
                }
            }
            
        };

        return response()->stream($callback, 200, $headers);
    }

    public function assignRole(Request $request, \App\Models\User $user)
    {
        $request->validate(['role_id' => 'required|exists:roles,id']);
        
        $user->roles()->syncWithoutDetaching([$request->role_id]);
        
        return redirect()->back()->with('success', 'User access updated.');
    }
}
