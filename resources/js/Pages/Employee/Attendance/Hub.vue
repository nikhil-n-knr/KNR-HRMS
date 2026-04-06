<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import AttendanceDashboard from './AttendanceDashboard.vue';
import TimesheetDashboard from './TimesheetDashboard.vue';
import MyOvertimeRequests from './MyOvertimeRequests.vue';
import ShiftSwaps from './ShiftSwaps.vue';
import RegularizationList from './Partials/RegularizationList.vue';
import AttendanceHeader from './Partials/AttendanceHeader.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    tab: String,
    // Pass-through props
    todayLog: Object, 
    history: Array, 
    currentShift: Object, 
    error: String,
    timesheets: Object,
    projects: Array,
    all_projects: Array,
    requests: Object, // Overtime or Swaps or Reg
    outgoing: Array,
    incoming: Array,
    colleagues: Array
});

const activeTab = computed(() => props.tab || 'dashboard');
const subTab = computed(() => {
    // Basic logic for handling sub-tabs inside 'requests' if we want to merge them deeply
    // For now, let's keep Swaps separate as per user mention "Swap Request" being a top item
    return 'default';
});

</script>

<template>
    <Head title="f" />

    <div class="space-y-6">
        <!-- New Header Component -->
        <AttendanceHeader :activeTab="activeTab" />

        <!-- Content Area -->
        <div class="min-h-[500px]">
            <AttendanceDashboard v-if="activeTab === 'dashboard'" v-bind="$props" />
            
            <TimesheetDashboard v-if="activeTab === 'timesheets'" v-bind="$props" />
            
        </div>
    </div>
</template>
