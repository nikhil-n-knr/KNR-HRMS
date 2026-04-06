<template>
    <div class="h-screen bg-gray-50 flex overflow-hidden">
        <!-- Dedicated Communication Sidebar -->
        <CommsSidebar :section="section" :tab="tab" />

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Dedicated Communication Header -->
            <CommsHeader :title="sectionTitle" :section="section" :tab="tab" />

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-6 pb-24 relative">
                <div class="max-w-7xl mx-auto space-y-6">
                    <component 
                        :is="activeSectionComponent" 
                        :key="`${section}-${tab}`"
                        v-bind="$page.props"
                        @action="handleAction"
                    />
                </div>

                <div class="fixed bottom-0 left-64 right-0 p-4 bg-white/40 backdrop-blur-2xl border-t border-gray-100/50 flex items-center justify-between z-40 px-8">
                    <div class="flex items-center gap-4">
                        <button @click="exportLogs" class="px-5 py-2 rounded-xl text-xs font-black bg-gray-900 text-white hover:bg-black transition-all shadow-lg flex items-center gap-2">
                            <i class="fas fa-file-export"></i> Export Hub Logs
                        </button>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full border-2 border-white bg-emerald-500 flex items-center justify-center text-xs text-white font-black">AI</div>
                        </div>
                        <p class="text-sm font-black text-gray-400 uppercase tracking-widest leading-none">
                            Contact Intelligence Active<br/>
                            <span class="text-emerald-500 text-sm">Real-time Syncing</span>
                        </p>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import CommsSidebar from './Components/CommsSidebar.vue';
import CommsHeader from './Components/CommsHeader.vue';

const props = defineProps({
    section: { type: String, default: 'communications' },
    tab: { type: String, default: 'inbox' },
    // Data props passed from CommunicationHubController
    threads: { type: Object, default: () => ({ data: [] }) },
    accounts: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    meetings: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
    type_distribution: { type: Array, default: () => [] },
    kpis: { type: Array, default: () => [] },
    signals: { type: Array, default: () => [] },
    agenda: { type: Array, default: () => [] },
});

// Computed Title
const sectionTitle = computed(() => {
    return "Communication Hub";
});

// Dynamic Imports Mapping
const sectionComponents = {
    'communications-dashboard': defineAsyncComponent(() => import('./Sections/Communications/Dashboard.vue')),
    'communications-client360': defineAsyncComponent(() => import('./Sections/Communications/Client360.vue')),
    'communications-analytics': defineAsyncComponent(() => import('./Sections/Communications/Analytics.vue')),
    'communications-inbox': defineAsyncComponent(() => import('./Sections/Communications/Inbox.vue')),
    'communications-sent': defineAsyncComponent(() => import('./Sections/Communications/Inbox.vue')), // Reusing Inbox for Sent items
    'communications-automations': defineAsyncComponent(() => import('./Sections/Communications/Automations.vue')),
    'communications-campaigns': defineAsyncComponent(() => import('./Sections/Communications/Campaigns.vue')),
    'communications-calendar': defineAsyncComponent(() => import('./Sections/Meetings/Calendar.vue')),
    'communications-settings': defineAsyncComponent(() => import('./Sections/Communications/Settings.vue')),
    'communications-assignments': defineAsyncComponent(() => import('./Sections/Communications/Assignments.vue')),
    'communications-governance': defineAsyncComponent(() => import('./Sections/Config/Permissions.vue')),

    'meetings-calendar': defineAsyncComponent(() => import('./Sections/Meetings/Calendar.vue')),
    'meetings-all_meetings': defineAsyncComponent(() => import('./Sections/Meetings/AllMeetings.vue')),
    'meetings-analytics': defineAsyncComponent(() => import('./Sections/Meetings/Analytics.vue')),
};

const activeSectionComponent = computed(() => {
    const key = `${props.section}-${props.tab}`;
    return sectionComponents[key] || null;
});

const currentTabs = computed(() => {
    if (props.section === 'meetings') {
        return [
            { id: 'calendar', label: 'Calendar' },
            { id: 'all_meetings', label: 'All Meetings' },
            { id: 'analytics', label: 'Analytics' },
        ];
    }
    return [
        { id: 'inbox', label: 'Inbox' },
        { id: 'automations', label: 'Automations' },
        { id: 'campaigns', label: 'Campaigns' },
        { id: 'assignments', label: 'Mapping & Reports' },
        { id: 'settings', label: 'Connect Account' },
    ];
});

const handleAction = (actionLabel) => {
    if (actionLabel === 'new-email') {
        router.get(route('crm.comms.hub', { section: 'communications', tab: 'inbox', compose: true }));
    } else if (actionLabel === 'schedule-meeting') {
        router.get(route('crm.comms.hub', { section: 'meetings', tab: 'calendar', new: true }));
    }
};

const exportLogs = () => {
    alert('Log export initiated. The file will download shortly once compiled.');
};
</script>

<script>
import MainLayout from '@/Layouts/MainLayout.vue';

export default {
    layout: MainLayout
}
</script>
