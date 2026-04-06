<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import ShiftSwapList from '../Attendance/Partials/ShiftSwapList.vue';
import OvertimeList from '../Attendance/Partials/OvertimeList.vue';
import WfhList from '../Attendance/Partials/WfhList.vue';
import FloatingHolidayList from '../Attendance/Partials/FloatingHolidayList.vue';
import RegularizationList from '../Attendance/Partials/RegularizationList.vue';
import LeaveList from '../Attendance/Partials/LeaveList.vue';
import AttendanceHeader from '../Attendance/Partials/AttendanceHeader.vue'; // Import Header
import { FunnelIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    tab: String,
    filters: Object,
    requests: Object,
    swapOptions: Object, // For Swaps tab
    otOptions: Object, // For Overtime tab
    wfhOptions: Object,
    leaveTypes: Array 
});

const activeTab = ref(props.tab || 'swaps');

// Filters state
const selectedStatus = ref(props.filters?.status || 'All');
const selectedMonth = ref(props.filters?.month || new Date().getMonth() + 1);
const selectedYear = ref(props.filters?.year || new Date().getFullYear());
const selectedDirection = ref(props.filters?.direction || 'sent');

const months = [
    { value: 'All', label: 'All Months' },
    { value: 1, label: 'January' }, { value: 2, label: 'February' }, { value: 3, label: 'March' },
    { value: 4, label: 'April' }, { value: 5, label: 'May' }, { value: 6, label: 'June' },
    { value: 7, label: 'July' }, { value: 8, label: 'August' }, { value: 9, label: 'September' },
    { value: 10, label: 'October' }, { value: 11, label: 'November' }, { value: 12, label: 'December' }
];

const years = computed(() => {
    const current = new Date().getFullYear();
    const range = [];
    for (let i = current - 3; i <= current + 2; i++) {
        range.push(i);
    }
    return ['All', ...range];
});

const statusOptions = [
    { value: 'All', label: 'All Status' },
    { value: 'Pending', label: 'Pending' },
    { value: 'Approved', label: 'Approved' },
    { value: 'Rejected', label: 'Rejected' },
    { value: 'Cancellation Requested', label: 'Cancel Req.' }
];

// Tab Maps
const tabs = [
    { id: 'leaves', label: 'Leave Requests' },
    { id: 'swaps', label: 'Shift Swaps' },
    { id: 'overtime', label: 'Overtime' },
    { id: 'wfh', label: 'Work From Home' },
    { id: 'regularization', label: 'Regularization' },
    { id: 'floating', label: 'Restricted Holidays' }
];

// URL Update Logic (Debounced or On Change)
const updateParams = () => {
    router.get(route('employee.requests.index'), {
        tab: activeTab.value,
        status: selectedStatus.value,
        month: selectedMonth.value,
        year: selectedYear.value,
        direction: selectedDirection.value
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['requests', 'swapOptions', 'filters', 'tab', 'wfhOptions', 'otOptions'] 
    });
};

const changeTab = (id) => {
    activeTab.value = id;
    updateParams();
};

watch([selectedStatus, selectedMonth, selectedYear, selectedDirection], () => {
    updateParams();
});

</script>

<template>
    <MainLayout>
        <div class="space-y-6">
            <!-- Global Navigation Info -->
            <AttendanceHeader activeTab="requests" />

            <!-- Header & Filters -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">My Requests</h2>
                    <p class="text-sm text-gray-500 mt-1">Manage all your attendance deviations.</p>
                </div>
                
                <!-- Filter Bar -->
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-2 bg-gray-50 px-3 py-2 rounded-lg border border-gray-200">
                        <FunnelIcon class="w-4 h-4 text-gray-400" />
                        
                        <select v-if="activeTab === 'swaps'" v-model="selectedDirection" class="bg-transparent border-none text-sm focus:ring-0 p-0 text-gray-700 cursor-pointer">
                            <option value="sent">Sent (Outgoing)</option>
                            <option value="received">Received (Incoming)</option>
                        </select>
                        <div v-if="activeTab === 'swaps'" class="h-4 w-px bg-gray-300 mx-1"></div>

                        <select v-model="selectedStatus" class="bg-transparent border-none text-sm focus:ring-0 p-0 text-gray-700 cursor-pointer">
                            <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                        
                        <div class="h-4 w-px bg-gray-300 mx-1"></div>

                        <select v-model="selectedMonth" class="bg-transparent border-none text-sm focus:ring-0 p-0 text-gray-700 cursor-pointer">
                            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                        </select>
                         
                        <select v-model="selectedYear" class="bg-transparent border-none text-sm focus:ring-0 p-0 text-gray-700 cursor-pointer">
                            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
             <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex border-b border-gray-200 overflow-x-auto">
                    <button 
                        v-for="t in tabs" 
                        :key="t.id"
                        @click="changeTab(t.id)"
                        class="px-6 py-3 text-sm font-medium whitespace-nowrap transition-colors"
                        :class="activeTab === t.id ? 'border-b-2 border-emerald-500 text-emerald-600 bg-emerald-50/10' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    >
                        {{ t.label }}
                    </button>
                </div>

                <!-- Active Content -->
                <div class="p-6">
                    <LeaveList 
                        v-if="activeTab === 'leaves'" 
                        :data="requests" 
                        :leave-types="leaveTypes"
                    />

                    <ShiftSwapList 
                        v-if="activeTab === 'swaps'" 
                        :data="requests" 
                        :options="swapOptions" 
                    />
                    
                    <OvertimeList 
                        v-if="activeTab === 'overtime'"
                        :data="requests" 
                        :options="otOptions"
                    />

                    <WfhList 
                        v-if="activeTab === 'wfh'" 
                        :data="requests" 
                        :options="wfhOptions"
                    />

                    <FloatingHolidayList 
                        v-if="activeTab === 'floating'" 
                        :data="requests" 
                    />

                    <RegularizationList 
                        v-if="activeTab === 'regularization'" 
                        :data="requests" 
                    />
                </div>
            </div>
        </div>
    </MainLayout>
</template>
