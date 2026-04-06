<template>
    <aside class="w-64 bg-white/60 backdrop-blur-xl border-r border-gray-200 flex flex-col h-full flex-shrink-0 relative overflow-hidden">
        <!-- Brand/Logo Area -->
        <div class="h-20 flex items-center px-6 border-b border-gray-100 z-10 bg-white/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                    <i class="fas fa-chart-line text-sm"></i>
                </div>
                <span class="text-lg font-black tracking-tight text-gray-800">CRM<span class="text-emerald-600">.module</span></span>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-1 z-10 overflow-y-auto">
            <Link v-for="item in filteredNavigation" :key="item.name" 
                :href="getRoute(item)"
                class="group flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all mb-1"
                :class="[
                    currentSection === item.id 
                        ? 'bg-emerald-50 text-emerald-700 shadow-sm ring-1 ring-emerald-200' 
                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900 group-hover:shadow-sm'
                ]"
            >
                <div class="w-6 flex justify-center mr-3">
                    <i :class="['fas', `fa-${item.icon}`, 'text-base transition-transform group-hover:scale-110', currentSection === item.id ? 'text-emerald-600' : 'text-gray-400 group-hover:text-emerald-500']"></i>
                </div>
                {{ item.name }}
                
                <!-- Active Indicator -->
                <div v-if="currentSection === item.id" class="ml-auto w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
            </Link>
        </nav>
        
        <!-- User/Footer -->
        <div class="p-4 border-t border-gray-100 z-10 bg-gray-50/50">
            <div class="flex items-center p-3 rounded-xl bg-white border border-gray-100 shadow-sm">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center text-xs font-black text-emerald-700 border border-white shadow-inner">
                    {{ $page.props.auth.user.name.charAt(0) }}
                </div>
                <div class="ml-3 overflow-hidden">
                    <p class="text-xs font-bold text-gray-900 truncate">{{ $page.props.auth.user.name }}</p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                        <p class="text-sm text-gray-400 font-bold uppercase tracking-wider">Active</p>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    currentSection: String
});

const page = usePage();
const permissions = computed(() => page.props.auth?.user?.permissions || []);

const getRoute = (item) => {
    return route('crm.hub', { section: item.id });
};

const navigation = [
    { name: 'Control Tower', id: 'dashboard', permission: 'Dashboard', icon: 'chart-pie' },
    { name: 'Leads Hub', id: 'leads', permission: 'Leads', icon: 'filter' },
    { name: 'Contact Matrix', id: 'contacts', permission: 'Contacts', icon: 'address-book' },
    { name: 'Account ABM', id: 'accounts', permission: 'Accounts', icon: 'building' },
    { name: 'Audience Segments', id: 'segments', permission: 'Segments', icon: 'layer-group' },
    { name: 'Sales Pipeline', id: 'sales', permission: 'Sales', icon: 'briefcase' }, 
    { name: 'Smart Quotes', id: 'quotes', permission: 'Quotes', icon: 'file-invoice-dollar' },
    { name: 'Revenue Forecast', id: 'forecasting', permission: 'Forecasting', icon: 'chart-line' },
    { name: 'Marketing Hub', id: 'marketing', permission: 'Marketing', icon: 'bullhorn' },
    { name: 'Marketing PRO', id: 'marketing_pro', permission: 'MarketingPro', icon: 'envelope-open-text' },
    { name: 'Revenue Tracker', id: 'revenue_tracker', permission: 'RevenueTracker', icon: 'money-bill-trend-up' },
    { name: 'Insights Engine', id: 'insights_engine', permission: 'InsightsEngine', icon: 'brain' },
    { name: 'ABM Command', id: 'abm_command', permission: 'ABMCommand', icon: 'bullseye' },
    { name: 'Product Hub', id: 'config', permission: 'ConfigHub', icon: 'box-open' },
    { name: 'Advanced Reports', id: 'reports', permission: 'Reports', icon: 'chart-bar' },
    { name: 'Partner Ecosystem', id: 'partners', permission: 'Partners', icon: 'user-friends' },
    { name: 'Achievements Lab', id: 'achievements', permission: 'Achievements', icon: 'trophy' },
    { name: 'Relationship Map', id: 'relationship_map', permission: 'RelationshipMap', icon: 'share-alt' },
];

const filteredNavigation = computed(() => {
    if (permissions.value.includes('*')) return navigation;
    if (permissions.value.length === 0) return navigation; // Default fallback if not configured
    
    return navigation.filter(item => {
        if (permissions.value.length === 0) return true;
        return permissions.value.includes(`CRM.${item.permission}.View`);
    });
});
</script>
