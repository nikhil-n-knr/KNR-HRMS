<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, defineAsyncComponent } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
    stats: { type: Object, default: () => ({ upcoming: 0, noShows: 0, utilization: '84%' }) },
    employees: Array,
    audience: Array,
    view: { type: String, default: 'calendar' }
});

const page = usePage();
const currentRoute = computed(() => page.url);

// Dynamic Views
const Calendar = defineAsyncComponent(() => import('./Calendar.vue'));
const Analytics = defineAsyncComponent(() => import('./Analytics.vue'));
const Integrations = defineAsyncComponent(() => import('./Integrations.vue'));

const activeComponent = computed(() => {
    if (props.view === 'reports' || props.view === 'analytics') return Analytics;
    if (props.view === 'config' || props.view === 'integrations') return Integrations;
    return Calendar; // Default to Schedule
});
</script>

<template>
    <MainLayout>
        <div class="h-[calc(100vh-100px)] bg-gray-50 flex overflow-hidden font-inter rounded-[40px] border border-gray-100/50 shadow-sm relative z-10 transition-all duration-500">
            <!-- Persistent Side Navigation -->
            <aside class="w-72 bg-white border-r border-gray-100 flex flex-col transition-all duration-300">
                <div class="p-8">
                    <div class="flex items-center gap-3 mb-10">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                            <i class="fas fa-magic"></i>
                        </div>
                        <h1 class="text-xl font-black text-gray-900 tracking-tight">Meetings <span class="text-indigo-600">Hub</span></h1>
                    </div>

                    <nav class="space-y-2">
                        <Link :href="route('crm.meetings.index')" :class="!currentRoute.includes('view') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-500 hover:bg-gray-50'" class="flex items-center gap-3 px-5 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all group">
                            <i class="fas fa-calendar-alt opacity-50 group-hover:opacity-100"></i> My Schedule
                        </Link>
                        <Link :href="route('crm.meetings.index', { view: 'team' })" :class="currentRoute.includes('view=team') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-500 hover:bg-gray-50'" class="flex items-center gap-3 px-5 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-500 hover:bg-gray-50 group transition-all">
                            <i class="fas fa-users opacity-50 group-hover:opacity-100"></i> Team View
                        </Link>
                        <Link :href="route('crm.meetings.index', { view: 'reports' })" :class="currentRoute.includes('view=reports') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-500'" class="flex items-center gap-3 px-5 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-100 group transition-all">
                            <i class="fas fa-chart-pie opacity-50 group-hover:opacity-100"></i> Performance
                        </Link>
                        <Link :href="route('crm.meetings.index', { view: 'config' })" :class="currentRoute.includes('view=config') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-500 hover:bg-gray-50'" class="flex items-center gap-3 px-5 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-500 hover:bg-gray-50 group transition-all">
                            <i class="fas fa-cog opacity-50 group-hover:opacity-100"></i> App Setup
                        </Link>
                    </nav>
                </div>

                <div class="mt-auto p-8">
                    <div class="p-6 bg-indigo-900 rounded-[32px] text-white relative overflow-hidden shadow-2xl shadow-indigo-100/50">
                        <i class="fas fa-rocket absolute -bottom-6 -right-6 text-8xl text-white/5 rotate-12"></i>
                        <h4 class="text-lg font-black mb-1">Scale: Active</h4>
                        <p class="text-[9px] font-bold text-indigo-300 uppercase tracking-widest">Optimized Infrastructure</p>
                    </div>
                </div>
            </aside>

            <!-- Dynamic Content Engine -->
            <main class="flex-1 overflow-y-auto custom-scrollbar bg-white/40 backdrop-blur-xl">
                <header class="h-24 bg-white/40 backdrop-blur-md border-b border-gray-100 sticky top-0 z-40 px-12 flex items-center justify-between">
                    <div class="flex gap-10">
                        <div v-for="(val, label) in { 'Active Session Range': stats.upcoming, 'No Show Ratio': stats.noShows }" :key="label">
                            <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ label }}</p>
                            <p class="text-lg font-black text-gray-900 leading-none">{{ val }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="h-10 w-[1px] bg-gray-100 mx-2"></div>
                        <div class="flex items-center gap-3">
                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Global Status</span>
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        </div>
                    </div>
                </header>

                <div class="p-12">
                    <component :is="activeComponent" v-bind="props" />
                </div>
            </main>
        </div>
    </MainLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
