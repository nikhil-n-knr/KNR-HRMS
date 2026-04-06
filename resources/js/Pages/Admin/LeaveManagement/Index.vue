<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import LeaveRequestsList from '@/Pages/LeaveManagement/LeaveRequestsList.vue';
import HolidayList from './HolidayList.vue';
import LeaveTypeList from '@/Pages/LeaveManagement/LeaveTypeList.vue';
import { 
    FingerPrintIcon, 
    CalendarIcon, 
    Square3Stack3DIcon,
    AdjustmentsHorizontalIcon,
    ShieldCheckIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline';

defineOptions({ layout: MainLayout });

const activeTab = ref('approvals');

const tabs = [
    { id: 'approvals', label: 'Approvals', icon: ShieldCheckIcon, color: 'indigo' },
    { id: 'holidays', label: 'Holidays', icon: CalendarIcon, color: 'rose' },
    { id: 'types', label: 'Leave Types', icon: Square3Stack3DIcon, color: 'blue' },
];

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('tab') && tabs.some(t => t.id === params.get('tab'))) {
        activeTab.value = params.get('tab');
    }
});
</script>

<template>
    <Head title="Leave Management Hub" />
    
    <div class="max-w-7xl mx-auto font-outfit px-4 md:px-8 space-y-8">
        <!-- Strategic Header Component -->
        <div class="py-8 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="flex items-center gap-5">
                <div class="p-4 bg-slate-900 border border-slate-800 rounded-3xl text-indigo-400 shadow-2xl shadow-slate-200/50 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <CalendarIcon class="w-8 h-8 relative z-10" />
                </div>
                <div>
                    <h1 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                        Leave Management Hub
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-indigo-50 text-indigo-600 border border-indigo-100 uppercase tracking-widest">Global Controller</span>
                    </h1>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Personnel absence tracking & calendar lifecycle logistics</p>
                </div>
            </div>

            <!-- Neural Tab Selector -->
            <div class="bg-white/80 backdrop-blur-md p-1.5 rounded-3xl flex border border-slate-100 shadow-2xl shadow-slate-200/40 w-full lg:w-auto overflow-x-auto no-scrollbar">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    class="flex-1 lg:flex-none px-6 py-3 rounded-2xl text-sm font-black uppercase tracking-[0.2em] transition-all duration-300 flex items-center justify-center gap-3 group whitespace-nowrap"
                    :class="[
                        activeTab === tab.id
                        ? 'bg-slate-900 text-white shadow-xl shadow-slate-400 scale-105 z-10'
                        : 'text-slate-400 hover:text-slate-900 hover:bg-slate-50'
                    ]"
                >
                     <component :is="tab.icon" class="w-4 h-4 transition-transform group-hover:scale-110" :class="activeTab === tab.id ? 'text-indigo-400' : 'text-slate-300'" />
                     {{ tab.label }}
                </button>
            </div>
        </div>

        <!-- Dynamic Content Engine -->
        <div class="min-h-[600px] relative">
            <Transition
                enter-active-class="transition duration-500 ease-out"
                enter-from-class="transform translate-y-4 opacity-0"
                enter-to-class="transform translate-y-0 opacity-100"
                leave-active-class="transition duration-300 ease-in"
                leave-from-class="transform translate-y-0 opacity-100"
                leave-to-class="transform translate-y-4 opacity-0"
                mode="out-in"
            >
                <div :key="activeTab" class="w-full">
                    <LeaveRequestsList v-if="activeTab === 'approvals'" embedded />
                    <HolidayList v-else-if="activeTab === 'holidays'" embedded />
                    <LeaveTypeList v-else-if="activeTab === 'types'" />
                </div>
            </Transition>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
