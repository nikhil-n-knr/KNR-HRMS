<script setup>
import { computed, ref, watch, defineAsyncComponent } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    tab: String,
    subTab: String,
    standalone: Boolean,
    filters: Object,
    locations: Array,
    departments: Array,
    // System Data
    policy: Object, // Global/Default
    policies: Array,
    rules: Array,
    teams: Array,
    badges: Array,
    shifts: Array,
    workflows: Array,
    roles: Array,
    users: Array,
    biometric_devices: Array,
    attendance_zones: Array,
    employees: Array,
    leave_types: Array,
    stats: Object,
    // Attendance Matrix (daily_log tab)
    matrix_employees: Object,
    matrix_stats: Object,

});

// --- Dynamic Component Imports ---
const components = {
    // Monitor
    'my_dashboard': defineAsyncComponent(() => import('./Dashboard.vue')),
    'monitor_view': defineAsyncComponent(() => import('./Monitoring.vue')),
    'daily_log': defineAsyncComponent(() => import('./AttendanceList.vue')),
    'ai_logs': defineAsyncComponent(() => import('@/Pages/Admin/Ai/LogViewer.vue')),
    'monitor_stats': defineAsyncComponent(() => import('./Partials/DeviceStats.vue')),
    
    // Timesheet
    'timesheets': defineAsyncComponent(() => import('./TimesheetList.vue')),
    'timesheet_view': defineAsyncComponent(() => import('./TimesheetList.vue')),
    
    // Calendar
    'approvals': defineAsyncComponent(() => import('./RegularizationList.vue')),
    'overtime': defineAsyncComponent(() => import('./OvertimeList.vue')),
    'wfh': defineAsyncComponent(() => import('./WfhList.vue')),
    'my_holidays': defineAsyncComponent(() => import('@/Pages/Employee/Attendance/FloatingHolidays.vue')),
    'floating_requests': defineAsyncComponent(() => import('./FloatingHolidayList.vue')),
    'holidays': defineAsyncComponent(() => import('@/Pages/Admin/LeaveManagement/HolidayList.vue')),
    
    // Workforce
    'shift_config': defineAsyncComponent(() => import('./ShiftList.vue')),
    'assignments': defineAsyncComponent(() => import('./ShiftRoster.vue')),
    'roster': defineAsyncComponent(() => import('./ShiftRoster.vue')),
    'swap_requests': defineAsyncComponent(() => import('./ShiftSwapList.vue')),
    'team_approvals': defineAsyncComponent(() => import('@/Pages/Manager/Approvals/ApprovalDashboard.vue')),
    
    // Intelligence
    'analytics': defineAsyncComponent(() => import('./Insights.vue')),
    
    // Config
    'policies': defineAsyncComponent(() => import('./PolicyBuilder.vue')),
    'attendance_policies': defineAsyncComponent(() => import('./PolicyBuilder.vue')),
    'gamification': defineAsyncComponent(() => import('@/Pages/Admin/Gamification/Index.vue')),
    'teams': defineAsyncComponent(() => import('./Partials/TeamManager.vue')),
    'workflows': defineAsyncComponent(() => import('./WorkflowBuilder.vue')),
    'biometric': defineAsyncComponent(() => import('./DeviceList.vue')),
    'devices': defineAsyncComponent(() => import('./DeviceList.vue')),
    'leave_types': defineAsyncComponent(() => import('@/Pages/LeaveManagement/LeaveTypeConfig.vue')),
    
    // Manual
    'manual_entry': defineAsyncComponent(() => import('./ManualEntry.vue')),
    'manual_entry_single': defineAsyncComponent(() => import('./ManualEntry.vue')),
    'manual_entry_bulk': defineAsyncComponent(() => import('./ManualEntry.vue')),
    'manual_entry_bulk_mark': defineAsyncComponent(() => import('./ManualEntry.vue')),

    // Config & System
    'gamification': defineAsyncComponent(() => import('@/Pages/Admin/Gamification/Index.vue')),
    'teams': defineAsyncComponent(() => import('./Partials/TeamManager.vue')),
    'workflows': defineAsyncComponent(() => import('./WorkflowBuilder.vue')),
};

const activeTab = ref(props.tab || 'monitor_view');

// Sync with prop changes
watch(() => props.tab, (newVal) => {
    if (newVal) activeTab.value = newVal;
});

const tabRouteMap = {
    'monitor_view': 'admin.attendance.monitoring',
    'daily_log': 'admin.attendance.monitoring', 
    'my_dashboard': 'admin.attendance.monitoring',
    'monitor_stats': 'admin.attendance.monitoring',
    'ai_logs': 'admin.attendance.ai-logs.index',
    'timesheets': 'admin.attendance.timesheets',
    'timesheet_view': 'admin.attendance.analytics.standalone',
    'approvals': 'admin.attendance.regularization', 
    'overtime': 'admin.attendance.overtime',
    'wfh': 'admin.attendance.wfh',
    'my_holidays': 'admin.attendance.holidays',
    'floating_requests': 'admin.attendance.holidays',
    'holidays': 'admin.attendance.holidays',
    'analytics': 'admin.attendance.analytics.standalone',
    'manual_entry_single': 'admin.attendance.analytics.standalone',
    'manual_entry_bulk': 'admin.attendance.analytics.standalone',
    'manual_entry_bulk_mark': 'admin.attendance.analytics.standalone',
    'shift_config': 'admin.attendance.roster',
    'assignments': 'admin.attendance.roster',
    'swap_requests': 'admin.attendance.roster',
    'team_approvals': 'admin.attendance.roster',
    'policies': 'admin.attendance.policies.standalone', 
    'attendance_policies': 'admin.attendance.policies.index',
    'gamification': 'admin.attendance.gamification',
    'teams': 'admin.attendance.gamification',
    'workflows': 'admin.attendance.gamification',
    'biometric': 'admin.attendance.gamification',
    'manual_entry': 'admin.attendance.manual',
    'leave_types': 'admin.attendance.leave-types',
};

const tabDataMap = {
    'policies': ['tab', 'policy', 'policies'],
    'approvals': ['tab', 'requests', 'locations', 'departments'],
    'holidays': ['tab', 'holidays', 'requests'],
    'my_holidays': ['tab', 'holidays', 'requests'],
    'floating_requests': ['tab', 'holidays', 'requests'],
    'teams': ['tab', 'teams', 'users', 'roles', 'employees'],
    'workflows': ['tab', 'workflows', 'roles'],
    'biometric': ['tab', 'biometric_devices', 'attendance_zones'],
    'gamification': ['tab', 'rules', 'badges'],
    'shift_config': ['tab', 'shifts'],
    'timesheets': ['tab', 'projects', 'employees', 'departments'],
    'timesheet_view': ['tab', 'projects', 'employees', 'departments'],
    'overtime': ['tab', 'requests', 'departments', 'locations'],
    'wfh': ['tab', 'requests', 'departments', 'locations'],
    'swap_requests': ['tab', 'swaps', 'employees', 'shifts', 'departments', 'locations'],
    'daily_log': ['tab', 'matrix_employees', 'matrix_stats', 'departments', 'locations', 'filters'],
    'leave_types': ['tab', 'leave_types'],
};


const loading = ref(false);
const page = usePage();

const isHubMode = computed(() => {
    const url = page.url;
    return url.includes('/attendance/hub') || url.includes('hub=1') || url.includes('hub=true');
});

const handleTabChange = (tabId) => {
    console.log(`[AttHub] Navigating to: ${tabId}`);
    
    // If it's a route-mapped tab, use router visit for full state update
    if (tabRouteMap[tabId]) {
        loading.value = true;
        const params = { tab: tabId };
        if (isHubMode.value) {
            params.hub = 1;
        }
        router.visit(route(tabRouteMap[tabId], params), {
            preserveState: true,
            preserveScroll: true,
            only: tabDataMap[tabId] || ['tab'], 
            onSuccess: () => { 
                activeTab.value = tabId;
                loading.value = false;
            },
            onError: () => { 
                console.error("Tab switch failed");
                loading.value = false;
            }
        });
    } else {
        // Only update local state if no route logic applies
        activeTab.value = tabId;
    }
};

const activeComponent = computed(() => components[activeTab.value] || null);
</script>

<template>
    <AttendanceLayout 
        title="Attendance Management" 
        :activeTab="activeTab || 'monitor_view'" 
        @update:activeTab="handleTabChange" 
        :manual-nav="true"
        :hideSidebar="!isHubMode"
        v-bind="$props"
    >
        <Head title="Attendance Console" />
        
        <div class="h-full font-outfit relative">
            <!-- Global Loading Interceptor -->
            <Transition name="fade">
                <div v-if="loading" class="absolute inset-0 z-50 bg-white/60 backdrop-blur-sm flex items-center justify-center pointer-events-none">
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-16 h-16 border-4 border-slate-900 border-t-emerald-500 rounded-full animate-spin shadow-2xl shadow-emerald-500/20"></div>
                        <span class="text-xs font-black uppercase tracking-[0.4em] text-slate-900 animate-pulse">Syncing Matrix...</span>
                    </div>
                </div>
            </Transition>

            <Transition name="fade-slide">
                <div :key="activeTab" class="h-full">
                    <!-- Specialized grouping for 'policies' tab -->
                    <div v-if="activeTab === 'policies'" class="space-y-8 pb-12 px-2 md:px-0">
                        <!-- Global Policy Architect -->
                        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-2xl shadow-slate-200/50 overflow-hidden flex flex-col group">
                            <div class="px-8 py-6 border-b border-gray-50 flex flex-col sm:flex-row justify-between items-center bg-slate-900 gap-4">
                                <div class="flex items-center gap-5">
                                    <div class="w-12 h-12 bg-emerald-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-emerald-500/20 shrink-0 group-hover:rotate-12 transition-transform">
                                        <i class="fas fa-shield-halved text-lg"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-sm md:text-md font-black text-white tracking-[0.2em] uppercase truncate">Global Policy Architect</h3>
                                        <p class="text-sm text-emerald-400 font-black uppercase tracking-[0.3em] mt-1 opacity-70">Core Logic & Compliance Protocols</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 md:p-8">
                                <component :is="activeComponent" v-bind="$props" :embedded="true" />
                            </div>
                        </div>

                        <!-- Integrated Roster Nodes -->
                        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-2xl shadow-slate-200/50 overflow-hidden flex flex-col group">
                            <div class="px-8 py-6 border-b border-gray-50 flex items-center bg-slate-900 gap-5">
                                <div class="w-12 h-12 bg-indigo-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/20 shrink-0 group-hover:-rotate-12 transition-transform">
                                    <i class="fas fa-clock-rotate-left text-lg"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm md:text-md font-black text-white tracking-[0.2em] uppercase truncate">Shift Master Registry</h3>
                                    <p class="text-sm text-indigo-400 font-black uppercase tracking-[0.3em] mt-1 opacity-70">Standard Hours & Rotation Nodes</p>
                                </div>
                            </div>
                            <div class="p-4 md:p-8">
                                <component :is="components['shift_config']" v-bind="$props" :embedded="true" />
                            </div>
                        </div>
                    </div>

                    <!-- Generic Component State -->
                    <div v-else-if="activeTab && components[activeTab]" class="animate-content-fade min-h-[400px]">
                        <Transition name="fade">
                            <component :is="activeComponent" v-bind="$props" :embedded="true" :key="activeTab" />
                        </Transition>
                    </div>

                    <!-- Empty State Fallback -->
                    <div v-else class="flex items-center justify-center min-h-[500px]">
                        <div class="flex flex-col items-center gap-4 animate-pulse opacity-40">
                            <div class="w-16 h-16 border-4 border-slate-200 border-t-slate-900 rounded-full animate-spin"></div>
                            <span class="text-xs font-black uppercase tracking-[0.4em]">Calibrating Registry Hub...</span>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
    </AttendanceLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

.fade-slide-enter-active, .fade-slide-leave-active { 
    transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.fade-slide-enter-from { 
    opacity: 0; 
    transform: translateY(30px) scale(0.98);
}
.fade-slide-leave-to { 
    opacity: 0; 
    transform: translateY(-30px) scale(0.98);
}

.animate-content-fade {
    animation: content-in 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes content-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
