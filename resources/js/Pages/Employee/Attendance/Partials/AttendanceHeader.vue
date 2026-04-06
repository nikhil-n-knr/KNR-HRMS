<script setup>
import { Link } from '@inertiajs/vue3';
import { 
    ClockIcon, 
    TableCellsIcon, 
    ArrowsRightLeftIcon, 
    DocumentCheckIcon 
} from '@heroicons/vue/24/outline';

defineProps({
    activeTab: String
});

const tabs = [
    { id: 'dashboard', label: 'My Dashboard', icon: ClockIcon },
    { id: 'timesheets', label: 'Timesheet', icon: TableCellsIcon },
    { id: 'requests', label: 'My Requests', icon: DocumentCheckIcon },
    { id: 'leave', label: 'My Holiday', icon: ArrowsRightLeftIcon },
];
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sticky top-0 z-10 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                    <h1 class="text-2xl font-black text-gray-800 tracking-tight">Attendance Center</h1>
                    <p class="text-sm text-gray-500 font-medium">Track your work, request changes, and manage shifts.</p>
            </div>
            
            <!-- Pills Navigation -->
            <nav class="flex bg-gray-100 p-1.5 rounded-xl overflow-x-auto max-w-full">
                <Link 
                    v-for="t in tabs" 
                    :key="t.id"
                    :href="t.id === 'leave' ? route('leave.dashboard', { tab: 'restricted' }) : t.id === 'requests' ? route('attendance.requests.index') : route('employee.attendance.hub', { tab: t.id })"
                    class="px-5 py-2.5 rounded-lg text-sm font-bold transition-all whitespace-nowrap flex items-center gap-2"
                    :class="activeTab === t.id ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'"
                >
                    <component :is="t.icon" class="w-4 h-4" />
                    {{ t.label }}
                </Link>
            </nav>
        </div>
    </div>
</template>
