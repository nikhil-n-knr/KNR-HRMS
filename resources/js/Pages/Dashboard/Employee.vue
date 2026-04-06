<template>
    <div class="min-h-screen pb-24 relative overflow-hidden bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-50/50 via-white to-sky-50/50">
        <!-- Dashboard Header: Operative HUD -->
        <header class="mb-14 px-8 pt-6 flex flex-col md:flex-row md:items-center justify-between gap-10">
            <div class="space-y-4">
                <div class="flex items-center gap-3 animate-fade-in">
                    <div class="h-10 w-10 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-2xl shadow-indigo-600/30">
                        <ActivityIcon class="w-6 h-6 text-white animate-pulse-slow" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 bg-indigo-500/10 text-indigo-600 text-xs font-black rounded-full uppercase tracking-[0.2em] border border-indigo-500/20">Operational_Pulse</span>
                            <span class="text-slate-400 text-xs font-bold uppercase tracking-widest pl-2 border-l border-slate-200">{{ currentTime }} SYNC</span>
                        </div>
                        <h1 class="text-4xl font-black text-slate-900 tracking-tighter flex items-center gap-4">
                            Hi, {{ user.name.split(' ')[0] }}! 
                            <span class="text-indigo-600 font-mono text-sm tracking-tighter bg-indigo-500/10 px-3 py-1 rounded-xl border border-indigo-500/20 shadow-sm">OPERATIVE</span>
                        </h1>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <!-- Advanced Performance Hub -->
                <div class="p-4 bg-white/60 border border-white/80 rounded-[2rem] flex items-center gap-12 shadow-2xl shadow-black/5 backdrop-blur-3xl group transition-all hover:bg-white/80">
                    <div class="flex flex-col border-r border-slate-200/60 pr-12">
                        <span class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1 group-hover:text-indigo-600 transition">Mission Capacity</span>
                        <div class="flex items-baseline gap-2">
                             <span class="text-3xl font-black text-slate-900 tracking-tighter leading-none">88%</span>
                             <span class="text-sm font-black text-emerald-600 bg-emerald-500/10 px-1.5 py-0.5 rounded shadow-sm">HIGH_IMPACT</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-black text-slate-400 tracking-widest uppercase mb-1">Growth Index</span>
                        <div class="flex items-center gap-3">
                             <span class="text-3xl font-black text-indigo-600 tracking-tighter leading-none">+14%</span>
                             <span class="text-sm font-black text-slate-400 opacity-50 uppercase tracking-widest">A+ GRADE</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

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
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { GridLayout, GridItem } from 'grid-layout-plus';
import axios from 'axios';
import { ActivityIcon } from 'lucide-vue-next';

// Widgets
import SmartInsights from '@/Components/Dashboard/Widgets/SmartInsights.vue';
import ActivityPulse3D from '@/Components/Dashboard/Widgets/ActivityPulse3D.vue';
import AttendanceWidget from '@/Components/Dashboard/Widgets/AttendanceWidget.vue';
import LeaveWidget from '@/Components/Dashboard/Widgets/LeaveWidget.vue';
import CompensationHub from '@/Components/Dashboard/Widgets/CompensationHub.vue';
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
    { x: 0, y: 14, w: 4, h: 10, i: 'attendance' },
    { x: 4, y: 14, w: 4, h: 10, i: 'leave' },
    { x: 8, y: 14, w: 4, h: 10, i: 'compensation' },
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
        'catalyst': CareerCatalyst
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
        'catalyst': 'all' 
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
