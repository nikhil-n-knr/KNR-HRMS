<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import MyLeaveDashboard from '@/Pages/LeaveManagement/MyLeaveDashboard.vue';
import MyHolidays from './MyHolidays.vue';
import FloatingHolidays from '../Attendance/FloatingHolidays.vue';
import AttendanceHeader from '../Attendance/Partials/AttendanceHeader.vue';

defineOptions({ layout: MainLayout });

const params = new URLSearchParams(window.location.search);
const initialTab = params.get('tab');
const defaultTab = 'my_leaves';

const tabs = [
    { id: 'my_leaves', label: 'My Requests' },
    { id: 'holidays', label: 'Holiday Calendar' },
    { id: 'restricted', label: 'Restricted Holidays' },
];

const activeTab = ref(
    initialTab && tabs.some(t => t.id === initialTab) ? initialTab : defaultTab
);

// Optional: Sync params if they change (though usually navigation triggers reload in MPA/Inertia unless using shallow)
onMounted(() => {
    // Double check if needed, but synchronous init covers the load case.
});

defineProps({
    balances: Array,
    leaves: Object,
    leaveTypes: Array
});
</script>

<template>
    <Head title="Leave Management" />
    
    <div class="space-y-6">
        <!-- Global Navigation Info -->
        <AttendanceHeader activeTab="leave" />

        <!-- Hub Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">My Leave & Holidays</h1>
                        <p class="text-sm text-gray-500">Manage your time off and view upcoming holidays.</p>
                </div>
                
                <!-- Tabs -->
                <div class="flex bg-gray-100 p-1 rounded-xl overflow-x-auto max-w-full">
                    <button 
                        v-for="tab in tabs" 
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        class="px-6 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap"
                        :class="activeTab === tab.id ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    >
                        {{ tab.label }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="bg-gray-50 min-h-[500px]">
                <component 
                    :is="activeTab === 'my_leaves' ? MyLeaveDashboard : 
                            activeTab === 'holidays' ? MyHolidays : 
                            activeTab === 'restricted' ? FloatingHolidays : null"
                    v-bind="$props" 
                />
        </div>
    </div>
</template>
