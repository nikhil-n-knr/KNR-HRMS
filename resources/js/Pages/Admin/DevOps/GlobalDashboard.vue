<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import BaseChart from '@/Components/BaseChart.vue';
import { 
    CommandLineIcon, 
    ServerStackIcon, 
    QueueListIcon, 
    RocketLaunchIcon,
    ShieldExclamationIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    BoltIcon,
    FireIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    stats: Object,
    dora: Object,
    healthScore: Number,
    pulse: Array,
    distribution: Object,
    burnoutRadar: Array,
    projects: Array,
    contributors: { type: Array, default: () => [] }
});

const filters = ref({
    dateRange: '30',
    contributor: ''
});

const exportData = (type) => {
    window.open(route('admin.devops.export', { type, ...filters.value }), '_blank');
};

const tabs = [
    { name: 'Overview', route: 'admin.devops.dashboard', current: true },
    { name: 'Providers & Configuration', route: 'admin.devops.providers.index', current: false },
];

// --- CHARTS ---
// Pulse Chart (Line)
const pulseChartData = computed(() => {
    if (!props.pulse) return { labels: [], datasets: [] };
    return {
        labels: props.pulse.map(p => p.date),
        datasets: [{
            label: 'Daily Commits',
            data: props.pulse.map(p => p.commits),
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            fill: true,
            tension: 0.4
        }]
    };
});

// Distribution Chart (Doughnut)
const distChartData = computed(() => {
    if (!props.distribution) return { labels: [], datasets: [] };
    return {
        labels: ['Features', 'Tech Debt', 'Bugs/Hotfixes'],
        datasets: [{
            data: [props.distribution.features, props.distribution.tech_debt, props.distribution.bugs],
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
            borderWidth: 0,
            hoverOffset: 4
        }]
    };
});

// System Health Chart (Doughnut - Half)
const healthChartData = computed(() => {
    const score = props.healthScore || 0;
    const remain = Math.max(0, 100 - score);
    let color = '#10b981'; // green
    if (score < 75) color = '#f59e0b'; // amber
    if (score < 50) color = '#ef4444'; // red
    
    return {
        labels: ['Health', 'Risk'],
        datasets: [{
            data: [score, remain],
            backgroundColor: [color, '#e5e7eb'],
            borderWidth: 0,
            circumference: 180,
            rotation: 270,
            cutout: '80%'
        }]
    };
});
</script>

<template>
    <Head title="DevOps Intelligence" />

    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Engineering Operations Hub
            </h2>
        </template>

        <div class="py-8 bg-gray-50 min-h-screen">
            <div class="max-w-[100rem] mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- Header Tabs -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <Link v-for="tab in tabs" :key="tab.name" :href="route(tab.route)"
                             :class="[tab.current ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                            {{ tab.name }}
                        </Link>
                    </nav>
                </div>

                <!-- Global Control Bar -->
                <div class="bg-white p-4 rounded-2xl shadow-sm flex flex-wrap items-center justify-between gap-4 border border-gray-100">
                    <div class="flex items-center space-x-4">
                        <span class="text-sm font-semibold text-gray-700 uppercase tracking-wider ml-2">Filters:</span>
                        <select v-model="filters.dateRange" class="block w-40 text-sm border-gray-200 focus:ring-indigo-500 focus:border-indigo-500 rounded-lg bg-gray-50">
                            <option value="7">Last 7 Days</option>
                            <option value="30">Last 30 Days</option>
                            <option value="90">Last 90 Days</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button @click="exportData('pdf')" class="btn-secondary text-sm px-4 py-2 border border-slate-200 rounded-lg shadow-sm bg-white hover:bg-slate-50 transition">
                            Export PDF
                        </button>
                        <button @click="exportData('excel')" class="btn-primary text-sm px-4 py-2 rounded-lg shadow-sm bg-slate-900 text-white hover:bg-slate-800 transition">
                            Export Excel
                        </button>
                    </div>
                </div>

                <!-- HERO SECTION: System Health & DORA -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Master Status Indicator -->
                    <div class="col-span-1 bg-white rounded-3xl shadow-sm border border-gray-100 p-8 flex flex-col items-center justify-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 to-white z-0"></div>
                        <h3 class="z-10 text-sm font-bold text-gray-500 uppercase tracking-widest mb-2">Operational Resilience</h3>
                        
                        <div class="relative w-48 h-32 z-10 mt-4">
                            <BaseChart type="doughnut" :data="healthChartData" 
                                :options="{ plugins: { legend: { display:false }, tooltip: { enabled: false } }, maintainAspectRatio: false }" />
                            <div class="absolute bottom-0 left-0 right-0 flex flex-col items-center translate-y-2">
                                <span class="text-5xl font-black" :class="healthScore >= 75 ? 'text-emerald-500' : (healthScore >= 50 ? 'text-amber-500' : 'text-red-500')">
                                    {{ healthScore }}
                                </span>
                                <span class="text-xs text-gray-400 font-semibold uppercase mt-1">/ 100 Score</span>
                            </div>
                        </div>
                    </div>

                    <!-- DORA Metrics Grid -->
                    <div class="col-span-1 lg:col-span-3 grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-6 text-white shadow-lg relative overflow-hidden group">
                            <div class="absolute -right-6 -bottom-6 opacity-10 group-hover:scale-110 transition duration-500">
                                <RocketLaunchIcon class="w-24 h-24" />
                            </div>
                            <p class="text-slate-300 text-xs font-semibold uppercase tracking-wider">Deployment Freq</p>
                            <p class="text-4xl font-bold mt-2">{{ dora.deployment_frequency }}<span class="text-lg font-normal text-slate-400 ml-1">/day</span></p>
                            <p class="mt-4 text-xs text-emerald-400 flex items-center bg-slate-800/50 w-fit px-2 py-1 rounded-full border border-slate-700">
                                <ArrowTrendingUpIcon class="w-3 h-3 mr-1" /> Elite Status
                            </p>
                        </div>

                        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm relative overflow-hidden group">
                            <div class="absolute -right-6 -bottom-6 opacity-5 text-indigo-600 group-hover:scale-110 transition duration-500">
                                <CommandLineIcon class="w-24 h-24" />
                            </div>
                            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Lead Time</p>
                            <p class="text-4xl font-bold text-gray-900 mt-2">{{ dora.lead_time_hours }}<span class="text-lg font-normal text-gray-400 ml-1">hrs</span></p>
                            <p class="mt-4 text-xs text-amber-500 flex items-center bg-amber-50 w-fit px-2 py-1 rounded-full border border-amber-100">
                                <ArrowTrendingDownIcon class="w-3 h-3 mr-1" /> High
                            </p>
                        </div>

                        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm relative overflow-hidden group">
                            <div class="absolute -right-6 -bottom-6 opacity-5 text-rose-600 group-hover:scale-110 transition duration-500">
                                <ShieldExclamationIcon class="w-24 h-24" />
                            </div>
                            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Change Failure Rate</p>
                            <p class="text-4xl font-bold text-gray-900 mt-2">{{ dora.change_failure_rate }}<span class="text-lg font-normal text-gray-400 ml-1">%</span></p>
                            <p class="mt-4 text-xs text-emerald-600 flex items-center bg-emerald-50 w-fit px-2 py-1 rounded-full border border-emerald-100">
                                <ArrowTrendingDownIcon class="w-3 h-3 mr-1" /> Improving
                            </p>
                        </div>

                        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm relative overflow-hidden group">
                            <div class="absolute -right-6 -bottom-6 opacity-5 text-indigo-600 group-hover:scale-110 transition duration-500">
                                <BoltIcon class="w-24 h-24" />
                            </div>
                            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">MTTR</p>
                            <p class="text-4xl font-bold text-gray-900 mt-2">{{ dora.mttr_hours }}<span class="text-lg font-normal text-gray-400 ml-1">hrs</span></p>
                            <p class="mt-4 text-xs text-emerald-600 flex items-center bg-emerald-50 w-fit px-2 py-1 rounded-full border border-emerald-100">
                                <ArrowTrendingDownIcon class="w-3 h-3 mr-1" /> Elite
                            </p>
                        </div>
                    </div>
                </div>

                <!-- SECONDARY ROW: Analytics -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Delivery Pulse -->
                    <div class="col-span-1 lg:col-span-2 bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-base font-bold text-gray-900">30-Day Delivery Pulse</h3>
                            <span class="text-xs font-medium bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full border border-indigo-100">Commits / Day</span>
                        </div>
                        <div class="h-64">
                            <BaseChart type="line" :data="pulseChartData" 
                                :options="{ 
                                    plugins: { legend: { display: false } },
                                    scales: { y: { beginAtZero: true, grid: { borderDash: [2, 2] } }, x: { grid: { display: false } } },
                                    elements: { point: { radius: 0, hitRadius: 10, hoverRadius: 4 } }
                                }" 
                            />
                        </div>
                    </div>

                    <!-- Focus Allocation -->
                    <div class="col-span-1 bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col">
                        <h3 class="text-base font-bold text-gray-900 mb-6">Code Allocation Taxonomy</h3>
                        <div class="flex-1 relative pb-4">
                            <BaseChart type="doughnut" :data="distChartData"
                                :options="{ plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } }, cutout: '70%' }" />
                            <div class="absolute inset-0 flex flex-col items-center justify-center pb-12 pointer-events-none">
                                <span class="text-2xl font-bold text-gray-800">100%</span>
                                <span class="text-xs text-gray-400 uppercase">Total Output</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TERTIARY ROW: Risk & Assets -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 pb-12">
                    
                    <!-- Top Driven Projects -->
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                        <div class="px-6 py-5 border-b border-gray-50 flex justify-between items-center bg-slate-50/50">
                            <h3 class="text-base font-bold text-gray-900 flex items-center"><ServerStackIcon class="w-5 h-5 mr-2 text-indigo-500"/> Core Infrastructure Projects</h3>
                            <Link :href="route('admin.devops.providers.index')" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Manage Systems</Link>
                        </div>
                        <div class="flex-1 overflow-y-auto">
                            <ul role="list" class="divide-y divide-gray-100">
                                <li v-for="project in projects" :key="project.id" class="p-4 hover:bg-slate-50 transition group">
                                    <Link :href="project.dashboard_url" class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition">{{ project.name }}</p>
                                            <div class="flex items-center mt-1 space-x-2">
                                                <span v-if="project.repo_count > 0" class="inline-flex items-center px-2 py-0.5 rounded text-sm font-semibold bg-gray-100 text-gray-600 border border-gray-200 uppercase tracking-wider">
                                                    {{ project.repo_count }} Repos
                                                </span>
                                                <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-sm font-semibold bg-red-50 text-red-600 border border-red-100 uppercase tracking-wider">
                                                    Unlinked
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-right flex items-center space-x-4">
                                            <div class="flex flex-col items-end mr-4">
                                                <span class="text-xs font-semibold text-gray-500 mb-1">Health</span>
                                                <div class="w-16 bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                                    <div class="bg-indigo-600 h-1.5 rounded-full" :style="{ width: project.health + '%' }"></div>
                                                </div>
                                            </div>
                                            <span class="px-3 py-1 text-xs font-bold rounded-xl" :class="project.status === 'Active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800'">
                                                {{ project.status }}
                                            </span>
                                        </div>
                                    </Link>
                                </li>
                                <li v-if="!projects.length" class="p-8 text-center text-gray-500 font-medium text-sm">No connected projects found.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Predictve Burnout Radar -->
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                        <div class="px-6 py-5 border-b border-gray-50 flex justify-between items-center bg-rose-50/30">
                            <h3 class="text-base font-bold text-gray-900 flex items-center"><FireIcon class="w-5 h-5 mr-2 text-rose-500"/> Burnout Radar Analytics</h3>
                            <span class="text-xs text-gray-500 font-medium bg-white px-2 py-1 rounded-md border border-gray-200 shadow-sm text-sm uppercase tracking-widest">AI Prediction</span>
                        </div>
                        <div class="flex-1 overflow-y-auto">
                            <ul role="list" class="divide-y divide-gray-100">
                                <li v-for="(person, idx) in burnoutRadar" :key="idx" class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-100 to-rose-100 border-2 border-white shadow flex items-center justify-center font-bold text-indigo-800">
                                            {{ person.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900">{{ person.name }}</p>
                                            <p class="text-xs text-gray-500 mt-1 max-w-xs truncate">{{ person.reason }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center shrink-0">
                                        <div class="flex flex-col items-center px-4 py-1.5 rounded-xl border border-gray-100 shadow-inner" 
                                            :class="person.risk === 'High' ? 'bg-rose-50 border-rose-100' : (person.risk === 'Moderate' ? 'bg-amber-50 border-amber-100' : 'bg-emerald-50 border-emerald-100')">
                                            <span class="text-sm uppercase tracking-widest font-black" 
                                                :class="person.risk === 'High' ? 'text-rose-600' : (person.risk === 'Moderate' ? 'text-amber-600' : 'text-emerald-600')">
                                                {{ person.risk }} Risk
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </MainLayout>
</template>
