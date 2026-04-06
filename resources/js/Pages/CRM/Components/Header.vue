<template>
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
        <div class="px-8 py-5 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight leading-none">{{ title }}</h1>
                    <p class="text-sm text-emerald-500 font-extrabold uppercase tracking-widest mt-1">{{ currentTabName }} Dashboard</p>
                </div>
                
                <div class="hidden xl:flex items-center gap-4">
                    <!-- My View Toggle -->
                    <Link 
                        :href="route('crm.hub', { section: section, tab: currentTab, view: currentView === 'my' ? 'all' : 'my' })"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black uppercase tracking-widest transition-all border shrink-0"
                        :class="currentView === 'my' ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white text-gray-400 border-gray-200 hover:border-emerald-200'"
                    >
                        <i class="fas fa-user-circle"></i> {{ currentView === 'my' ? 'My Data' : 'All Data' }}
                    </Link>

                    <!-- AI Global Search -->
                    <div class="flex items-center bg-gray-50 border border-gray-100 rounded-2xl px-4 py-2 w-80 group focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all">
                        <i class="fas fa-search text-gray-400 text-sm group-focus-within:text-emerald-500"></i>
                        <input type="text" placeholder='AI Search...' class="bg-transparent border-none text-xs font-bold text-gray-800 focus:ring-0 ml-2 w-full placeholder:text-gray-400">
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <!-- Status Stats -->
                <div class="hidden md:flex items-center gap-2">
                    <div class="flex items-center gap-2 bg-emerald-50 text-emerald-700 px-4 py-2 rounded-xl text-xs font-black border border-emerald-100/50">
                        <span class="text-base">💰</span> {{ globalKpis?.pipeline_value || '$0.00' }} Pipeline
                    </div>
                    <div class="flex items-center gap-2 bg-blue-50 text-blue-700 px-4 py-2 rounded-xl text-xs font-black border border-blue-100/50">
                        <span class="text-base text-emerald-500 animate-pulse">🟢</span> {{ globalKpis?.avg_health || '100' }} Health
                    </div>
                    <div class="flex items-center gap-2 bg-rose-50 text-rose-700 px-4 py-2 rounded-xl text-xs font-black border border-rose-100/50">
                        <span class="text-base">🔔</span> {{ globalKpis?.alerts_count || '0' }} Alerts
                    </div>
                </div>

                <div class="w-px h-8 bg-gray-100 mx-2"></div>

                <div class="flex items-center space-x-2">
                    <button class="w-10 h-10 rounded-full bg-gray-900 text-white flex items-center justify-center text-xs shadow-lg hover:scale-105 transition-all">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 transition-all">
                        <i class="fas fa-cog"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Tab Navigation -->
        <div v-if="tabs && tabs.length > 0" class="px-8 flex space-x-8 border-t border-gray-100 bg-gray-50/30 overflow-x-auto">
            <Link 
                v-for="tab in tabs" 
                :key="tab.id" 
                :href="route('crm.hub', { section: props.section, tab: tab.id })"
                class="py-4 text-sm font-bold border-b-2 transition-all px-1 whitespace-nowrap"
                :class="[
                    currentTab === tab.id 
                        ? 'border-amber-500 text-amber-600' 
                        : 'border-transparent text-gray-400 hover:text-gray-700 hover:border-gray-200'
                ]"
            >
                {{ tab.label }}
            </Link>
        </div>
    </header>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    title: String,
    currentTab: String,
    tabs: Array,
    section: { type: String, default: 'dashboard' },
    globalKpis: { type: Object, default: () => ({}) }
});

const currentTabName = computed(() => {
    const t = (props.tabs || []).find(t => t.id === props.currentTab);
    return t ? t.label : props.currentTab;
});

const currentView = computed(() => {
    return new URLSearchParams(window.location.search).get('view') || 'all';
});
</script>
