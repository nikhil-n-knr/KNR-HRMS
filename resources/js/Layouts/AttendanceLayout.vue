<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const tabRouteMap = {
    'monitor_view': 'admin.attendance.monitoring',
    'daily_log': 'admin.attendance.monitoring', 
    'my_dashboard': 'admin.attendance.monitoring',
    'monitor_stats': 'admin.attendance.monitoring',
    'ai_logs': 'admin.attendance.ai-logs.index',
    'timesheets': 'admin.attendance.timesheets',
    'timesheet_view': 'admin.attendance.timesheets',
    'approvals': 'admin.attendance.regularization', 
    'overtime': 'admin.attendance.overtime',
    'wfh': 'admin.attendance.wfh',
    'my_holidays': 'admin.attendance.holidays',
    'floating_requests': 'admin.attendance.holidays',
    'holidays': 'admin.attendance.holidays',
    'analytics': 'admin.attendance.analytics',
    'shift_config': 'admin.attendance.shifts.list',
    'assignments': 'admin.attendance.roster',
    'swap_requests': 'admin.attendance.swaps',
    'team_approvals': 'manager.approvals.index',
    'policies': 'admin.attendance.policies', 
    'attendance_policies': 'admin.attendance.policies.index',
    'gamification': 'admin.attendance.gamification',
    'teams': 'admin.attendance.teams',
    'workflows': 'admin.attendance.workflows',
    'biometric': 'admin.attendance.devices',
    'manual_entry': 'admin.attendance.manual',
};

// Using FontAwesome for that premium CRM feel
const sidebarItems = [
    { 
        id: 'monitor', 
        label: 'Live Monitor', 
        icon: 'fas fa-tv',
        tabs: [
            { id: 'my_dashboard', label: 'Console Home' },
            { id: 'monitor_view', label: 'Real-time Hub' },
            { id: 'daily_log', label: 'Activity Streams' },
            { id: 'ai_logs', label: 'AI Analysis Logs' },
            { id: 'monitor_stats', label: 'System Pulse' },
        ]
    },
    { 
        id: 'timesheets', 
        label: 'Revenue Center', 
        icon: 'fas fa-stopwatch',
        tabs: [
            { id: 'timesheet_view', label: 'Attendance Logs' },
            { id: 'timesheets', label: 'Timesheet Entry' },
        ]
    },
    { 
        id: 'calendar', 
        label: 'Events & Plans', 
        icon: 'fas fa-calendar-alt',
        tabs: [
            { id: 'approvals', label: 'Regularization' },
            { id: 'overtime', label: 'Overtime Requests' },
            { id: 'wfh', label: 'WFH Requests' },
            { id: 'my_holidays', label: 'My Holidays (RH)' },
            { id: 'floating_requests', label: 'Floating Requests' },
            { id: 'holidays', label: 'Global Holidays' },
        ]
    },
    { 
        id: 'intelligence', 
        label: 'Intelligence', 
        icon: 'fas fa-brain-circuit',
        tabs: [
            { id: 'analytics', label: 'Strategic Analytics' },
        ]
    },
    { 
        id: 'roster', 
        label: 'Workforce', 
        icon: 'fas fa-users-cog',
        tabs: [
            { id: 'shift_config', label: 'Shift Architect' },
            { id: 'assignments', label: 'Deployment Roster' },
            { id: 'swap_requests', label: 'Exchange Hub' },
            { id: 'team_approvals', label: 'Team Approvals' },
        ]
    },
    { 
        id: 'config', 
        label: 'System Intel', 
        icon: 'fas fa-brain',
        tabs: [
            { id: 'policies', label: 'Timesheet Policy' },
            { id: 'gamification', label: 'Reward Engine' },
            { id: 'teams', label: 'Squad Management' },
            { id: 'workflows', label: 'Process Maps' },
            { id: 'biometric', label: 'Hardware Bridge' },
        ]
    },
    { 
        id: 'manual', 
        label: 'Operations', 
        icon: 'fas fa-keyboard',
        tabs: [
            { id: 'manual_entry', label: 'Override Console' },
        ]
    },
];

const props = defineProps({
    title: String,
    activeTab: String,
    activeSection: String,
    embedded: Boolean,
    manualNav: Boolean // If true, the layout only emits and doesn't do router.visit
});

const emit = defineEmits(['update:activeTab', 'update:activeSection']);

// Helper to find which group a tab belongs to
const findSidebarIdForTab = (tabId) => {
    if (!tabId) return 'monitor';
    for (const group of sidebarItems) {
        if (group.id === tabId || group.tabs.some(t => t.id === tabId)) {
            return group.id;
        }
    }
    return 'monitor';
};

const currentSidebarId = ref('monitor');
const currentTabId = ref(null);

const syncState = (tabId) => {
    const groupId = findSidebarIdForTab(tabId);
    currentSidebarId.value = groupId;
    
    const group = sidebarItems.find(i => i.id === groupId);
    if (group) {
        if (group.tabs.some(t => t.id === tabId)) {
             currentTabId.value = tabId;
        } else if (group.tabs.length > 0) {
             currentTabId.value = group.tabs[0].id;
        }
    }
};

const currentSidebar = computed(() => sidebarItems.find(i => i.id === currentSidebarId.value));

watch(() => props.activeTab, (newVal) => {
    syncState(newVal);
}, { immediate: true });

const handleNavigation = (tabId) => {
    // 1. Notify the parent
    emit('update:activeTab', tabId);

    // 2. Automated navigation if not embedded and manual nav is NOT requested
    if (!props.embedded && !props.manualNav && tabRouteMap[tabId]) {
         router.visit(route(tabRouteMap[tabId], { tab: tabId }), {
             preserveState: true,
             preserveScroll: true
         });
    }
};

const selectSidebar = (id) => {
    currentSidebarId.value = id;
    const group = sidebarItems.find(i => i.id === id);
    if (group && group.tabs.length > 0) {
        const firstTabId = group.tabs[0].id;
        currentTabId.value = firstTabId;
        handleNavigation(firstTabId);
    }
    emit('update:activeSection', id);
};

const selectTab = (tabId) => {
    currentTabId.value = tabId;
    handleNavigation(tabId);
};
</script>

<template>
  <div class="h-full bg-slate-50 flex flex-col lg:flex-row overflow-hidden pb-16 lg:pb-0">
    <Head :title="title || 'Attendance Console'" />

    <!-- Mobile Header (Category Strip) -->
    <div class="lg:hidden bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="flex overflow-x-auto hide-scrollbar px-2 py-2 gap-1.5 bg-slate-50/50">
            <button v-for="item in sidebarItems" :key="item.id" 
                @click="selectSidebar(item.id)"
                class="flex-shrink-0 flex items-center gap-1.5 px-3 py-1 rounded-full text-[9px] font-black transition-all duration-200 border"
                :class="[
                    currentSidebarId === item.id 
                        ? 'bg-slate-900 text-white border-slate-900 shadow-md transform scale-105 z-10' 
                        : 'bg-white text-slate-500 border-slate-200 hover:border-emerald-300 shadow-xs'
                ]"
            >
                <i :class="[item.icon, 'text-[9px]']"></i>
                <span class="uppercase tracking-tighter">{{ item.label }}</span>
            </button>
        </div>
    </div>

    <!-- Desktop Sidebar (Hidden on mobile) -->
    <aside class="hidden lg:flex w-56 bg-white border-r border-slate-200 flex-col h-full flex-shrink-0 relative transition-all duration-300 shadow-sm">
        <!-- Brand Area -->
        <div class="h-14 flex items-center px-5 border-b border-slate-100 bg-white">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg bg-emerald-600 flex items-center justify-center text-white shadow-md shadow-emerald-200 transition-transform hover:scale-105 cursor-pointer">
                    <i class="fas fa-clock-rotate-left text-[12px]"></i>
                </div>
                <div>
                    <span class="text-sm font-bold tracking-tight text-slate-800">Attendance<span class="text-emerald-600">.hub</span></span>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto hide-scrollbar">
            <button v-for="item in sidebarItems" :key="item.id" 
                @click="selectSidebar(item.id)"
                class="group w-full flex items-center px-3 py-2 text-[10px] font-black rounded-xl transition-all duration-200 relative uppercase tracking-wider"
                :class="[
                    currentSidebarId === item.id 
                        ? 'bg-emerald-600 text-white shadow-md shadow-emerald-200' 
                        : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900'
                ]"
            >
                <div class="w-5 flex justify-center mr-2.5">
                    <i :class="[item.icon, 'text-[12px] transition-colors', currentSidebarId === item.id ? 'text-white' : 'text-slate-400 group-hover:text-emerald-500']"></i>
                </div>
                {{ item.label }}
            </button>
        </nav>
        
        <!-- User Context Area -->
        <div class="p-3 border-t border-slate-100 bg-slate-50/50">
            <div class="flex items-center p-2 rounded-xl bg-white border border-slate-200 shadow-sm">
                <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center text-[10px] font-black text-white border border-white shadow-sm">
                    {{ $page.props.auth.user?.name?.charAt(0).toUpperCase() || 'U' }}
                </div>
                <div class="ml-2.5 overflow-hidden">
                    <p class="text-[10px] font-black text-slate-900 truncate uppercase tracking-tighter leading-none">{{ $page.props.auth.user?.name || 'System User' }}</p>
                    <p class="text-[8px] text-slate-400 font-black uppercase tracking-[0.2em] mt-1 leading-none">Console.Root</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
        <!-- Header (Adaptive) -->
        <header class="flex-shrink-0 bg-white border-b border-slate-200 sticky top-0 z-20">
            <!-- Top Section (Desktop Hidden) -->
            <div class="lg:h-14 flex items-center justify-between px-4 lg:px-6 py-2 lg:py-0 border-b border-slate-50 lg:border-none">
                <div>
                    <h1 class="text-[10px] lg:text-xs font-black text-slate-900 tracking-tight uppercase leading-none">{{ currentSidebar?.label || title }}</h1>
                    <p class="text-[8px] lg:text-[9px] font-black text-emerald-500 uppercase tracking-[0.2em] leading-none mt-1 lg:mt-1.5">{{ currentSidebar?.tabs.find(t => t.id === currentTabId)?.label || 'Overview' }}</p>
                </div>
                
                <!-- Desktop Tab Strip (Hidden on mobile) -->
                <div class="hidden lg:flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl border border-slate-200/50">
                        <button v-for="tab in currentSidebar?.tabs || []" :key="tab.id"
                        @click="selectTab(tab.id)"
                        class="px-3 py-1.5 rounded-lg text-[9px] font-bold transition-all duration-200 whitespace-nowrap uppercase tracking-wider"
                        :class="currentTabId === tab.id 
                            ? 'bg-white text-emerald-600 shadow-sm border border-slate-200/50' 
                            : 'text-slate-500 hover:text-slate-700 hover:bg-slate-200/50'"
                        >
                        {{ tab.label }}
                        </button>
                </div>
            </div>

            <!-- Mobile Sub-Tab Strip (New) -->
            <div v-if="currentSidebar?.tabs.length > 1" class="lg:hidden flex overflow-x-auto hide-scrollbar px-3 py-2 gap-1.5 bg-white border-b border-slate-100">
                <button v-for="tab in currentSidebar?.tabs || []" :key="tab.id"
                    @click="selectTab(tab.id)"
                    class="flex-shrink-0 px-3 py-1 rounded-lg text-[9px] font-black transition-all duration-200 uppercase tracking-tighter"
                    :class="currentTabId === tab.id 
                        ? 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-500/20 shadow-sm' 
                        : 'bg-slate-50/50 text-slate-400 border border-slate-100'"
                >
                    {{ tab.label }}
                </button>
            </div>
        </header>

        <!-- Content viewport -->
        <div class="flex-1 overflow-auto p-4 lg:p-6 bg-white relative">
            <div class="max-w-[1400px] mx-auto transition-all duration-300">
                <slot />
            </div>
        </div>
    </main>
  </div>

</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Section transition */
.v-enter-active,
.v-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.v-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.v-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>


<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
