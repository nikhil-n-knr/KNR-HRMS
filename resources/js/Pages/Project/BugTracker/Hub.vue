<template>
    <MainLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <h2 class="font-bold text-xl text-gray-900 leading-tight">Bug Tracker Command Center</h2>
                <div class="flex space-x-1 bg-gray-100/80 p-1 rounded-xl overflow-x-auto scroll-smooth snap-x border border-gray-200/50">
                     <button 
                        v-for="t in tabs" 
                        :key="t.id"
                        @click="router.visit(route('bugs.index', { tab: t.id }))"
                        :class="[
                            activeTab === t.id 
                                ? 'bg-white shadow-md text-emerald-600' 
                                : 'text-gray-500 hover:text-gray-800 hover:bg-white/40',
                            'px-4 py-2 text-sm font-bold rounded-lg transition-all duration-200 whitespace-nowrap flex-shrink-0 snap-center'
                        ]"
                    >
                        {{ t.name }}
                    </button>
                    <div class="flex-shrink-0 w-8 md:hidden"></div>
                </div>
            </div>
        </template>

        <BugTrackerHeader :projects="projects">
            <template #actions>
                <div class="flex gap-2">
                    <a :href="route('portal.login')" target="_blank" class="px-3 py-1.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 flex items-center gap-2 shadow-sm transition-all">
                        <ArrowTopRightOnSquareIcon class="w-4 h-4 text-emerald-600" />
                        Client Login UI
                    </a>
                    <a :href="route('bugs.export.pdf')" target="_blank" class="px-3 py-1.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 flex items-center gap-2">
                        <DocumentArrowDownIcon class="w-4 h-4" />
                        Sheet
                    </a>
                  
                </div>
            </template>
        </BugTrackerHeader>

        <div class="p-6">
            <!-- Dynamic Component Loading based on Tab -->
            <component 
                :is="activeComponent" 
                v-bind="$props" 
                @switch-to-intelligence="router.visit(route('bugs.index', { tab: 'intelligence' }))"
                class="flex-1" 
            />
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { DocumentArrowDownIcon, CodeBracketIcon, ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/outline';
import Analytics from './Analytics.vue';
import GlobalTicketMatrix from './GlobalTicketMatrix.vue';
import Create from './Create.vue';
import ExternalPortalPartial from './Components/ExternalPortalPartial.vue';
import ClientHub from './ClientHub.vue';
import WorkflowArchitect from './WorkflowArchitect.vue';
import ReportBuilder from './ReportBuilder.vue';
import BugTrackerHeader from './Partials/BugTrackerHeader.vue';
import { useBugTrackerStore } from '@/Stores/bugTrackerStore';

// Define layout option to avoid nested layouts if components also define it
defineOptions({ layout: null }); 

const props = defineProps({
    tab: String,
    // Analytics props
    hotspots: Array,
    status_breakdown: Array,
    sla_breaches: Array,
    velocity: Object,
    avg_resolution_hours: Number,
    leaderboard: Array,
    // List props
    bugs: Object,
    filters: Object,
    projects: Array,
    stages: Array,
    custom_views: Array,
    open_critical_count: Number,
    lookup: Object,
    // Workflow props
    workflow: Object,
    teams: Array,
    roles: Array
});

const store = useBugTrackerStore();

const tabs = computed(() => {
    if (store.viewMode === 'client') {
        return [{ id: 'portal', name: 'Client Portal' }];
    }
    if (store.viewMode === 'audit') {
        return [{ id: 'security', name: 'Security & Access' }];
    }
    return [
        { id: 'tracker', name: 'Global Matrix' },
        { id: 'dashboard', name: 'Analytics' },
        { id: 'intelligence', name: 'Intelligence' },
        { id: 'workflow', name: 'Workflow Architect' },
        { id: 'report', name: 'Report Issue' } 
    ];
});

const activeTab = computed(() => {
    if (store.viewMode === 'client') return 'portal';
    if (store.viewMode === 'audit') return 'security';
    return props.tab || 'tracker';
});

const activeComponent = computed(() => {
    if (store.viewMode === 'client') return ExternalPortalPartial;
    if (store.viewMode === 'audit') return ClientHub;
    
    switch (activeTab.value) {
        case 'dashboard': return Analytics;
        case 'tracker': return GlobalTicketMatrix;
        case 'intelligence': return ReportBuilder;
        case 'workflow': return WorkflowArchitect;
        case 'report': return Create;
        default: return GlobalTicketMatrix;
    }
});
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
