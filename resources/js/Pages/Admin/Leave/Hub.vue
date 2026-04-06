<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import LeaveTypeConfig from '@/Pages/LeaveManagement/LeaveTypeConfig.vue';
import HolidayList from '@/Pages/Admin/LeaveManagement/HolidayList.vue';
import LeaveRequestsList from '@/Pages/LeaveManagement/LeaveRequestsList.vue';

import { 
    Squares2X2Icon, 
    CalendarDaysIcon, 
    ClipboardDocumentCheckIcon, 
    Cog6ToothIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    tab: String
});

const tabs = [
    { id: 'dashboard', label: 'Dashboard', icon: Squares2X2Icon },
    { id: 'calendar', label: 'Holiday Calendar', icon: CalendarDaysIcon },
    { id: 'approvals', label: 'Leave Approvals', icon: ClipboardDocumentCheckIcon },
    { id: 'types', label: 'Leave Types', icon: Cog6ToothIcon },
];

const activeTab = computed(() => props.tab || 'dashboard');
</script>

<template>
    <AttendanceLayout title="Leave Management" activeTab="leave-management">
        <Head title="Leave Management" />

        <div class="mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Leave Management</h1>
                    <p class="text-gray-500 text-sm">Configure leave policies, holidays, and manage approvals.</p>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                <Link 
                    v-for="t in tabs" 
                    :key="t.id"
                    :href="`?tab=${t.id}`"
                    class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors whitespace-nowrap"
                    :class="activeTab === t.id 
                        ? 'border-indigo-500 text-indigo-600' 
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                >
                    <component 
                        :is="t.icon" 
                        class="mr-2 h-5 w-5"
                        :class="activeTab === t.id ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'" 
                    />
                    {{ t.label }}
                </Link>
            </nav>
        </div>

        <!-- Content Area -->
        <div class="min-h-[400px]">
            <div v-if="activeTab === 'dashboard'">
               <!-- Dashboard can be a summary or just reuse Approvals for now which has stats -->
               <LeaveRequestsList />
            </div>

            <div v-else-if="activeTab === 'calendar'">
                <HolidayList />
            </div>

            <div v-else-if="activeTab === 'approvals'">
                 <LeaveRequestsList :filters="{ status: 'pending' }" />
            </div>

            <div v-else-if="activeTab === 'types'">
                 <LeaveTypeConfig />
            </div>
        </div>

    </AttendanceLayout>
</template>
