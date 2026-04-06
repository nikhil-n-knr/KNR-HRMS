<template>
    <div class="h-screen bg-gray-50 flex overflow-hidden">
        <!-- Sidebar -->
        <Sidebar :current-section="section" />

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Header -->
            <Header :title="sectionTitle" :current-tab="tab" :tabs="currentTabs" :section="section" :global-kpis="global_kpis" />

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-6 pb-24 relative">
                <div class="max-w-7xl mx-auto space-y-6">
                    <component 
                        :is="activeSectionComponent" 
                        v-bind="$page.props"
                    />
                </div>

                <!-- Premium Hub Footer -->
                <div class="fixed bottom-0 left-64 right-0 p-4 bg-white/40 backdrop-blur-2xl border-t border-gray-100/50 flex items-center justify-between z-40 px-8">
                    <div class="flex items-center gap-4">
                        <button class="px-5 py-2 rounded-xl text-xs font-black bg-gray-900 text-white hover:bg-black transition-all shadow-lg flex items-center gap-2">
                            <i class="fas fa-file-export"></i> Export Report
                        </button>
                        <button class="px-5 py-2 rounded-xl text-xs font-black bg-white border border-gray-200 text-gray-700 hover:border-emerald-500 transition-all flex items-center gap-2">
                            <i class="fas fa-print"></i> Print View
                        </button>
                        <button class="px-5 py-2 rounded-xl text-xs font-black bg-white border border-gray-200 text-gray-700 hover:border-emerald-500 transition-all flex items-center gap-2">
                            <i class="fas fa-share-alt"></i> Share Hub
                        </button>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full border-2 border-white bg-emerald-500 flex items-center justify-center text-xs text-white font-black">AI</div>
                            <div class="w-8 h-8 rounded-full border-2 border-white bg-blue-500 flex items-center justify-center text-xs text-white font-black">BK</div>
                        </div>
                        <p class="text-sm font-black text-gray-400 uppercase tracking-widest leading-none">
                            AI Summary Available<br/>
                            <span class="text-emerald-500 text-sm">Last Sync: Just Now</span>
                        </p>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue';
import { Head } from '@inertiajs/vue3';
import Sidebar from './Components/Sidebar.vue'; // Assuming existence or I will create it
import Header from './Components/Header.vue';   // Assuming existence or I will create it

const props = defineProps({
    section: { type: String, default: 'dashboard' },
    tab: { type: String, default: 'overview' },
    // Data props passed from HubbleController
    quotes: { type: Array, default: () => [] },
    accounts: { type: Array, default: () => [] },
    products: { type: [Array, Object], default: () => [] },
    categories: { type: Array, default: () => [] },
    leads: { type: Array, default: () => [] },
    contacts: { type: Array, default: () => [] },
    deals: { type: Array, default: () => [] },
    campaigns: { type: Array, default: () => [] },
    tickets: { type: Array, default: () => [] },
    metrics: { type: Object, default: () => ({}) },
    activities: { type: Array, default: () => [] },
    global_kpis: { type: Object, default: () => ({}) },
    // ... add more as needed
});

// Computed Title
const sectionTitle = computed(() => {
    return props.section.charAt(0).toUpperCase() + props.section.slice(1);
});

// Dynamic Imports Mapping
const sectionComponents = {
    // Dashboard
    'dashboard-revenue_overview': defineAsyncComponent(() => import('./Sections/Dashboard/RevenueOverview.vue')),
    'dashboard-customer_health': defineAsyncComponent(() => import('./Sections/Dashboard/CustomerHealth.vue')),
    'dashboard-ai_insights': defineAsyncComponent(() => import('./Sections/Dashboard/AIInsights.vue')),
    'dashboard-overview': defineAsyncComponent(() => import('./Sections/Dashboard/Overview.vue')),
    'dashboard-sales_dash': defineAsyncComponent(() => import('./Sections/Dashboard/Sales.vue')),
    'dashboard-marketing_dash': defineAsyncComponent(() => import('./Sections/Dashboard/Marketing.vue')),
    'dashboard-support_dash': defineAsyncComponent(() => import('./Sections/Dashboard/Support.vue')),
    'dashboard-reports': defineAsyncComponent(() => import('./Sections/Dashboard/Reports.vue')),
    'dashboard-product_dash': defineAsyncComponent(() => import('./Sections/Analytics/ProductDashboard.vue')),

    // Leads & Contacts & Accounts
    'leads-all_leads': defineAsyncComponent(() => import('./Sections/Leads/AllLeads.vue')),
    'leads-scoring': defineAsyncComponent(() => import('./Sections/Leads/Scoring.vue')),
    'leads-assignment': defineAsyncComponent(() => import('./Sections/Leads/Assignment.vue')),
    'leads-conversion': defineAsyncComponent(() => import('./Sections/Leads/Conversion.vue')),
    
    'contacts-all_contacts': defineAsyncComponent(() => import('./Sections/Contacts/AllContacts.vue')),
    'contacts-companies': defineAsyncComponent(() => import('./Sections/Contacts/Companies.vue')),
    'contacts-segments': defineAsyncComponent(() => import('./Sections/Contacts/Segments.vue')),
    'contacts-import_export': defineAsyncComponent(() => import('./Sections/Contacts/ImportExport.vue')),
    'contacts-duplicates': defineAsyncComponent(() => import('./Sections/Contacts/Duplicates.vue')),
    'contacts-partners': defineAsyncComponent(() => import('./Sections/Contacts/Partners.vue')),

    'accounts-all_accounts': defineAsyncComponent(() => import('./Sections/Contacts/Companies.vue')), // Reusing companies view
    'accounts-abm_targets': defineAsyncComponent(() => import('./Sections/ABM/TargetAccounts.vue')), // Reusing target accounts
    'segments-all_segments': defineAsyncComponent(() => import('./Sections/Contacts/Segments.vue')),
    'segments-smart_segments': defineAsyncComponent(() => import('./Sections/Contacts/Segments.vue')), // Reuse segments view

    // Sales Pipeline & Smart Quotes
    'sales-kanban': defineAsyncComponent(() => import('./Sections/Sales/Kanban.vue')),
    'sales-all_deals': defineAsyncComponent(() => import('./Sections/Sales/AllDeals.vue')),
    'sales-quotes': defineAsyncComponent(() => import('./Sections/Sales/Quotes.vue')),
    'sales-forecasting': defineAsyncComponent(() => import('./Sections/Sales/Forecasting.vue')),
    'sales-won': defineAsyncComponent(() => import('./Sections/Sales/Won.vue')),
    'sales-lost': defineAsyncComponent(() => import('./Sections/Sales/Lost.vue')),
    'quotes-all_quotes': defineAsyncComponent(() => import('./Sections/Sales/Quotes.vue')),
    'quotes-approved_quotes': defineAsyncComponent(() => import('./Sections/Sales/Quotes.vue')), // Reusing quotes view
    'forecasting-overview': defineAsyncComponent(() => import('./Sections/Sales/Forecasting.vue')),
    'forecasting-prediction_hub': defineAsyncComponent(() => import('./Sections/Sales/PredictionHub.vue')),

    // Marketing Hub
    'marketing-campaigns': defineAsyncComponent(() => import('./Sections/Marketing/Campaigns.vue')),
    'marketing-templates': defineAsyncComponent(() => import('./Sections/Marketing/Templates.vue')),
    'marketing-analytics': defineAsyncComponent(() => import('./Sections/Marketing/Analytics.vue')),
    'marketing-nurturing': defineAsyncComponent(() => import('./Sections/Marketing/Nurturing.vue')),

    // Advanced Modules
    'marketing_pro-live_dashboard': defineAsyncComponent(() => import('./Sections/MarketingPro/LiveDashboard.vue')),
    'marketing_pro-multi_channel': defineAsyncComponent(() => import('./Sections/MarketingPro/MultiChannel.vue')),
    'marketing_pro-abm_command': defineAsyncComponent(() => import('./Sections/MarketingPro/ABMCommand.vue')),
    'marketing_pro-attribution': defineAsyncComponent(() => import('./Sections/MarketingPro/AttributionEngine.vue')),
    'marketing_pro-flow_builder': defineAsyncComponent(() => import('./Sections/MarketingPro/FlowBuilder.vue')),

    'revenue_tracker-funnel': defineAsyncComponent(() => import('./Sections/RevenueTracker/FunnelAnalytics.vue')),
    'revenue_tracker-journey': defineAsyncComponent(() => import('./Sections/RevenueTracker/JourneyMaps.vue')),
    'revenue_tracker-import_analyzer': defineAsyncComponent(() => import('./Sections/RevenueTracker/ImportAnalyzer.vue')),

    'insights_engine-analytics': defineAsyncComponent(() => import('./Sections/Insights/MLAnalytics.vue')),
    'insights_engine-recommendations': defineAsyncComponent(() => import('./Sections/Insights/Recommendations.vue')),

    'abm_command-targets': defineAsyncComponent(() => import('./Sections/ABM/TargetAccounts.vue')),
    'abm_command-analytics': defineAsyncComponent(() => import('./Sections/ABM/ABMAnalytics.vue')),

    'reports-overview': defineAsyncComponent(() => import('./Sections/Dashboard/Reports.vue')),
    'partners-overview': defineAsyncComponent(() => import('./Sections/Contacts/Partners.vue')),
    
    'partners-overview': defineAsyncComponent(() => import('./Sections/Contacts/Partners.vue')),
    
    // Achievements
    'achievements-overview': defineAsyncComponent(() => import('./Sections/Dashboard/Overview.vue')), // Temporary
    'achievements-badges': defineAsyncComponent(() => import('./Sections/Achievements/Badges.vue')),
    
    // Relationship
    'relationship_map-overview': defineAsyncComponent(() => import('./Sections/Relationship/InfluenceMap.vue')),
    'relationship_map-influence_score': defineAsyncComponent(() => import('./Sections/Relationship/InfluenceMap.vue')),
    
    // Config
    'config-products': defineAsyncComponent(() => import('./Sections/Config/Products.vue')),
    'config-stages': defineAsyncComponent(() => import('./Sections/Config/Stages.vue')),
    'config-workflows': defineAsyncComponent(() => import('./Sections/Config/Workflows.vue')),
    'config-custom_fields': defineAsyncComponent(() => import('./Sections/Config/CustomFields.vue')),
    'config-email_settings': defineAsyncComponent(() => import('./Sections/Config/EmailSettings.vue')),
    'config-permissions': defineAsyncComponent(() => import('./Sections/Config/Permissions.vue')),
    
    'platform_config-email_settings': defineAsyncComponent(() => import('./Sections/Config/EmailSettings.vue')),

    // Product Promotions (under Marketing)
    'marketing-promotions': defineAsyncComponent(() => import('./Sections/Marketing/Promotions.vue')),
};

const activeSectionComponent = computed(() => {
    const key = `${props.section}-${props.tab}`;
    return sectionComponents[key] || null;
});

const currentTabs = computed(() => {
    switch(props.section) {
        case 'dashboard': return [
            { id: 'revenue_overview', label: 'Revenue Overview' },
            { id: 'customer_health', label: 'Customer Health' },
            { id: 'ai_insights', label: 'AI Insights Hub' },
            { id: 'overview', label: 'Global Stats' },
            { id: 'sales_dash', label: 'Sales Activity' },
            { id: 'marketing_dash', label: 'Channel Reach' },
        ];
        case 'leads': return [
            { id: 'all_leads', label: 'All Leads' },
            { id: 'scoring', label: 'Scoring Rules' },
            { id: 'assignment', label: 'Assignment Rules' },
            { id: 'conversion', label: 'Conversion Stats' },
        ];
        case 'contacts': return [
            { id: 'all_contacts', label: 'All Contacts' },
            { id: 'companies', label: 'Companies' },
            { id: 'segments', label: 'Segments' },
            { id: 'import_export', label: 'Import/Export' },
            { id: 'duplicates', label: 'Duplicates' },
            { id: 'partners', label: 'Partners' },
        ];
        case 'sales': return [
            { id: 'kanban', label: 'Pipeline Board' },
            { id: 'all_deals', label: 'All Deals' },
            { id: 'quotes', label: 'Quotes' },
            { id: 'forecasting', label: 'Forecast' },
            { id: 'won', label: 'Deals Won' },
            { id: 'lost', label: 'Deals Lost' },
        ];
        case 'marketing': return [
            { id: 'campaigns', label: 'Campaigns' },
            { id: 'templates', label: 'Templates' },
            { id: 'analytics', label: 'Analytics' },
            { id: 'nurturing', label: 'Automation' },
            { id: 'promotions', label: 'Product Promotions' },
        ];
        case 'support': return [
            { id: 'my_tickets', label: 'My Tickets' },
            { id: 'tickets', label: 'All Tickets' },
            { id: 'queue', label: 'Unassigned Queue' },
            { id: 'kb', label: 'Knowledge Base' },
        ];
        case 'support': return [
            { id: 'my_tickets', label: 'My Tickets' },
            { id: 'tickets', label: 'All Tickets' },
            { id: 'queue', label: 'Unassigned Queue' },
            { id: 'kb', label: 'Knowledge Base' },
        ];
        case 'config': return [
            { id: 'products', label: 'Product Catalog' },
            { id: 'stages', label: 'Pipeline Stages' },
            { id: 'workflows', label: 'Workflows' },
            { id: 'custom_fields', label: 'Custom Fields' },
            { id: 'email_settings', label: 'Email Settings' },
            { id: 'permissions', label: 'Permissions' },
        ];
        case 'marketing_pro': return [
            { id: 'live_dashboard', label: 'Live Dashboard' },
            { id: 'multi_channel', label: 'Campaigns' },
            { id: 'abm_command', label: 'ABM' },
            { id: 'attribution', label: 'Attribution' },
            { id: 'flow_builder', label: 'Auto-Flow' },
        ];
        case 'revenue_tracker': return [
            { id: 'funnel', label: '3D Funnel' },
            { id: 'journey', label: 'Journey Mind Maps' },
            { id: 'import_analyzer' , label: 'Import Analyzer' },
        ];
        case 'insights_engine': return [
            { id: 'analytics', label: 'ML Analytics' },
            { id: 'recommendations', label: 'AI Recs' },
        ];
        case 'abm_command': return [
            { id: 'targets', label: 'Target List' },
            { id: 'analytics', label: 'ABM Stats' },
        ];
        case 'relationship_map': return [
            { id: 'overview', label: 'Influence Map' },
            { id: 'influence_score', label: 'Node Analysis' },
        ];
        case 'accounts': return [
            { id: 'all_accounts', label: 'Account Matrix' },
            { id: 'abm_targets', label: 'ABM Focus' },
        ];
        case 'segments': return [
            { id: 'all_segments', label: 'Segment Hub' },
            { id: 'smart_segments', label: 'AI Smart Lists' },
        ];
        case 'quotes': return [
            { id: 'all_quotes', label: 'Smart Quotes' },
            { id: 'approved_quotes', label: 'Signed' },
        ];
        case 'forecasting': return [
            { id: 'overview', label: 'Revenue Forecast' },
            { id: 'prediction_hub', label: 'AI Prediction' },
        ];
        case 'reports': return [
            { id: 'overview', label: 'Executive Reports' },
        ];
        case 'achievements': return [
            { id: 'overview', label: 'Leaderboard' },
            { id: 'badges', label: 'Badges' },
        ];
        default: return [];
    }
});
</script>

<script>
import MainLayout from '@/Layouts/MainLayout.vue';

export default {
    layout: MainLayout
}
</script>
