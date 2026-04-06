<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import DashboardView from './Dashboard.vue';
import DailyLog from './DailyLog.vue';
import ShiftList from './ShiftList.vue';
import ShiftRoster from './ShiftRoster.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import RotationManager from './Components/RotationManager.vue';

defineOptions({ layout: MainLayout });
import ShiftSwapList from './ShiftSwapList.vue';
import DeviceList from './DeviceList.vue';
import ZoneManager from './ZoneManager.vue';
import CompOffManager from './CompOffManager.vue';

const props = defineProps({
    shifts: Array,
    employees: Object,
    swaps: Object,
    locations: Array,
    departments: Array,
    logs: Object,
    filters: Object,
});

// Internal Tabs Configuration
const tabs = [
    { id: 'dashboard', label: 'Monitor' },
    { id: 'daily_log', label: 'Daily Log' },
    { id: 'roster', label: 'Roster' },
    { id: 'shifts', label: 'Shifts' },
    { id: 'rotations', label: 'Patterns' },
    { id: 'swaps', label: 'Requests' },
    { id: 'devices', label: 'Devices' },
    { id: 'zones', label: 'Geo-Zones' },
    { id: 'compoffs', label: 'Comp-Offs' },
];

const activeTab = computed(() => {
    const params = new URLSearchParams(window.location.search);
    return params.get('tab') || 'dashboard';
});
</script>

<template>
    <AttendanceLayout title="Attendance Hub" activeTab="shifts">
        <Head title="Attendance Hub" />
        
        <!-- Tab Navigation Bar -->
        <div class="mb-6 sticky top-0 z-10 bg-white/90 backdrop-blur-sm border-b border-gray-200">
            <nav class="-mb-px flex space-x-6 overflow-x-auto hide-scrollbar px-2" aria-label="Tabs">
                <Link 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    :href="`?tab=${tab.id}`"
                    preserve-state
                    replace
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                    :class="activeTab === tab.id 
                        ? 'border-indigo-500 text-indigo-600' 
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                >
                    {{ tab.label }}
                </Link>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="px-2 pb-10">
             <component 
                :is="activeTab === 'dashboard' ? DashboardView :
                     activeTab === 'daily_log' ? DailyLog :
                     activeTab === 'shifts' ? ShiftList : 
                     activeTab === 'rotations' ? RotationManager :
                     activeTab === 'roster' ? ShiftRoster : 
                     activeTab === 'swaps' ? ShiftSwapList : 
                     activeTab === 'devices' ? DeviceList : 
                     activeTab === 'zones' ? ZoneManager : 
                     activeTab === 'compoffs' ? CompOffManager : null" 
                v-bind="props" 
            />
        </div>
    </AttendanceLayout>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
