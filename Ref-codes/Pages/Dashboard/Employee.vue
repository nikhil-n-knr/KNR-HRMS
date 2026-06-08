<template>
    <Head title="Employee Dashboard" />
    <div class="bg-[#f4f5fa] pb-16">
        <GradientHeroHeader
            kicker="Employee"
            :title="`Hi, ${(user?.name || 'Employee').split(' ')[0]}!`"
            subtitle="Your work, insights, and attendance at a glance."
        >
            <template #right>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[170px]">
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Sync</p>
                        <p class="text-2xl font-extrabold text-white leading-none">{{ currentTime }}</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[170px]">
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Mission Capacity</p>
                        <p class="text-3xl font-extrabold text-white leading-none">88%</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[170px]">
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Growth Index</p>
                        <p class="text-3xl font-extrabold text-white leading-none">+14%</p>
                    </div>
                </div>
            </template>
        </GradientHeroHeader>

        <div class="mx-0 sm:mx-6 mt-5">

        <!-- Smart Insights Row -->
        <div class="px-8 mb-10 animate-fade-in-up">
             <SmartInsights />
        </div>

        <!-- Draggable Dashboard Grid -->
        <div class="px-8 dashboard-grid">
            <grid-layout
                v-model:layout="layout"
                :col-num="12"
                :row-height="30"
                :is-draggable="true"
                :is-resizable="true"
                :vertical-compact="true"
                :use-css-transforms="true"
                @layout-updated="handleLayoutUpdate"
            >
                <grid-item
                    v-for="item in layout"
                    :key="item.i"
                    :x="item.x"
                    :y="item.y"
                    :w="item.w"
                    :h="item.h"
                    :i="item.i"
                    class="grid-item-container"
                >
                    <div class="h-full relative group">
                        <!-- Advanced Glass Decoration -->
                        <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity rounded-[2.5rem] -z-10 bg-white/10 backdrop-blur-md border border-white/20 shadow-2xl"></div>
                        
                        <component 
                            :is="getWidgetComponent(item.i)" 
                            v-bind="getWidgetProps(item.i)"
                            v-if="isModuleEnabled(item.i)"
                            class="h-full"
                        />
                    </div>
                </grid-item>
            </grid-layout>
        </div>

        <!-- Floating UI Elements -->
        <DashboardDock class="z-50" />
        <CommandPalette class="z-50" />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { GridLayout, GridItem } from 'grid-layout-plus';
import axios from 'axios';
import { ActivityIcon } from 'lucide-vue-next';
import GradientHeroHeader from '@/Components/UI/GradientHeroHeader.vue';

// Widgets
import SmartInsights from '@/Components/Dashboard/Widgets/SmartInsights.vue';
import ActivityPulse3D from '@/Components/Dashboard/Widgets/ActivityPulse3D.vue';
import AttendanceWidget from '@/Components/Dashboard/Widgets/AttendanceWidget.vue';
import LeaveWidget from '@/Components/Dashboard/Widgets/LeaveWidget.vue';
import CompensationHub from '@/Components/Dashboard/Widgets/CompensationHub.vue';
import CheckInCard from '@/Components/Dashboard/Widgets/CheckInCard.vue';
import CareerCatalyst from '@/Components/Dashboard/Widgets/CareerCatalyst.vue';
import DashboardDock from '@/Components/Dashboard/DashboardDock.vue';
import CommandPalette from '@/Components/Dashboard/CommandPalette.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    user: Object,
    enabledModules: Array,
    quickStats: Object
});

const layout = ref(props.user.preferences?.dashboard_layout || [
    { x: 0, y: 0, w: 8, h: 14, i: 'pulse' },
    { x: 8, y: 0, w: 4, h: 14, i: 'catalyst' },
    { x: 0, y: 14, w: 4, h: 10, i: 'checkin' },
    { x: 4, y: 14, w: 4, h: 10, i: 'attendance' },
    { x: 8, y: 14, w: 4, h: 10, i: 'leave' },
    { x: 0, y: 24, w: 4, h: 10, i: 'compensation' },
]);

const currentTime = ref('');
const timer = ref(null);

const updateClock = () => {
    currentTime.value = new Date().toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: true 
    });
};

const getWidgetComponent = (id) => {
    const map = {
        'pulse': ActivityPulse3D,
        'attendance': AttendanceWidget,
        'leave': LeaveWidget,
        'compensation': CompensationHub,
        'catalyst': CareerCatalyst,
        'checkin': CheckInCard
    };
    return map[id];
};

const getWidgetProps = (id) => {
    if (id === 'pulse') return { stats: props.quickStats };
    return {};
};

const isModuleEnabled = (id) => {
    const map = {
        'pulse': 'performance', 
        'attendance': 'attendance',
        'leave': 'leave_management',
        'compensation': 'payroll',
        'catalyst': 'all',
        'checkin': 'attendance'
    };
    if (map[id] === 'all') return true;
    return props.enabledModules.includes(map[id]) || id === 'pulse'; 
};

const handleLayoutUpdate = async (newLayout) => {
    try {
        await axios.post('/api/employee/dashboard/layout', { layout: newLayout });
    } catch (e) {
        console.error('Failed to save layout preferences');
    }
};

onMounted(() => {
    updateClock();
    timer.value = setInterval(updateClock, 1000);
});

onUnmounted(() => {
    if (timer.value) clearInterval(timer.value);
});
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.8s ease-out forwards; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in-up { animation: fadeInUp 1s ease-out forwards; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.animate-pulse-slow { animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
</style>
