<template>
    <div class="h-full flex flex-col font-inter bg-slate-900 border border-slate-800 rounded-[3rem] overflow-hidden shadow-2xl relative">
        <!-- Abstract Background -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(99,102,241,0.05),transparent)] pointer-events-none"></div>

        <!-- Header -->
        <div class="z-20 bg-slate-900/80 backdrop-blur-xl border-b border-white/5 px-6 md:px-12 py-6 md:py-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex items-center justify-between w-full md:w-auto">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <span class="px-3 py-1 bg-indigo-500/10 text-indigo-400 text-sm font-black uppercase tracking-[0.2em] rounded-lg border border-indigo-500/20">Alpha v2.0</span>
                        <h1 class="text-2xl md:text-3xl font-black text-white tracking-widest uppercase">Matrix</h1>
                    </div>
                    <p class="text-sm md:text-xs text-slate-500 font-bold uppercase tracking-widest">Custom Architecture Intelligence</p>
                </div>
                <!-- Mobile Sidebar Toggle -->
                <button @click="showMobileSidebar = !showMobileSidebar" class="md:hidden p-3 bg-white/5 rounded-xl text-white">
                    <AdjustmentsHorizontalIcon v-if="!showMobileSidebar" class="w-6 h-6" />
                    <XMarkIcon v-else class="w-6 h-6" />
                </button>
            </div>
            <button @click="runQuery" :disabled="loading" 
                    :class="[
                        'w-full md:w-auto px-10 py-4 md:py-5 rounded-2xl font-black text-sm md:text-base uppercase tracking-[0.2em] transition-all flex items-center justify-center gap-3 shadow-2xl active:scale-95',
                        loading ? 'bg-slate-800 text-slate-600' : 'bg-white text-slate-900 hover:bg-slate-50 shadow-white/5'
                    ]">
                <component :is="loading ? ArrowPathIcon : PlayIcon" :class="['w-5 h-5', loading ? 'animate-spin' : '']" />
                {{ loading ? 'Synchronizing...' : 'Calibrate' }}
            </button>
        </div>

        <div class="flex-1 overflow-hidden flex z-10">
            <!-- Sidebar: Parameters Builder -->
            <div :class="[
                'fixed inset-0 z-30 md:relative md:inset-auto md:z-auto transition-transform duration-500 ease-in-out md:translate-x-0',
                showMobileSidebar ? 'translate-x-0' : '-translate-x-full'
            ]" class="w-full md:w-96 bg-slate-900 md:bg-black/20 border-r border-white/5 flex flex-col overflow-y-auto pt-[120px] md:pt-0">
                <div class="p-8 md:p-10 space-y-10 md:space-y-12">
                     <div class="md:hidden mb-6 flex items-center justify-between">
                        <h3 class="text-white font-black uppercase tracking-widest text-sm">Control Deck</h3>
                        <button @click="showMobileSidebar = false" class="text-slate-400">Close</button>
                    </div>

                    <!-- Dimension Section -->
                    <div class="space-y-5 md:space-y-6">
                        <label class="flex items-center gap-2 text-sm font-black text-indigo-400 uppercase tracking-[0.25em]">
                            <Squares2X2Icon class="w-4 h-4" /> Dimension
                        </label>
                        <div class="grid grid-cols-1 gap-2.5">
                            <button v-for="opt in dimensionOptions" :key="opt.id"
                                    @click="selectDimension(opt.id)"
                                    :class="[
                                        'p-4 md:p-5 rounded-2xl border-2 text-left transition-all group relative overflow-hidden',
                                        query.groupBy === opt.id ? 'border-indigo-500 bg-indigo-500/10' : 'border-white/5 hover:border-white/10'
                                    ]">
                                <div class="relative z-10 flex items-center justify-between">
                                    <span :class="['text-base font-black uppercase tracking-widest', query.groupBy === opt.id ? 'text-white' : 'text-slate-500 group-hover:text-slate-300']">
                                        {{ opt.name }}
                                    </span>
                                    <div v-if="query.groupBy === opt.id" class="h-1.5 w-1.5 rounded-full bg-indigo-500 shadow-[0_0_10px_rgba(99,102,241,1)]"></div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Metric Section -->
                    <div class="space-y-5 md:space-y-6">
                        <label class="flex items-center gap-2 text-sm font-black text-emerald-400 uppercase tracking-[0.25em]">
                            <BoltIcon class="w-4 h-4" /> Metrics
                        </label>
                        <div class="space-y-3 md:space-y-4">
                            <div v-for="opt in metricOptions" :key="opt.id" 
                                 @click="query.metric = opt.id"
                                 :class="[
                                    'flex items-center py-4 px-5 rounded-2xl cursor-pointer transition-all border-2',
                                    query.metric === opt.id ? 'border-emerald-500 bg-emerald-500/10' : 'border-transparent hover:bg-white/5'
                                 ]">
                                <div :class="['h-4 w-4 rounded-full border-2 flex items-center justify-center mr-4', query.metric === opt.id ? 'border-emerald-500' : 'border-slate-700']">
                                    <div v-if="query.metric === opt.id" class="h-1.5 w-1.5 rounded-full bg-emerald-500"></div>
                                </div>
                                <div>
                                    <div :class="['text-sm md:text-base font-black uppercase tracking-widest', query.metric === opt.id ? 'text-white' : 'text-slate-500']">{{ opt.name }}</div>
                                    <div class="text-xs md:text-sm font-bold text-slate-500 mt-1 uppercase tracking-tight">{{ opt.desc }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeframe -->
                    <div class="space-y-5 md:space-y-6 pb-20 md:pb-0">
                        <label class="flex items-center gap-2 text-sm font-black text-slate-500 uppercase tracking-[0.25em]">
                            <ClockIcon class="w-4 h-4" /> Temporal
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <span class="text-xs font-black uppercase text-slate-600 ml-1">Begin</span>
                                <input v-model="query.date_start" type="date" class="w-full bg-white/5 border-none rounded-xl text-sm text-white p-3 focus:ring-1 focus:ring-indigo-500 transition-all font-mono" />
                            </div>
                            <div class="space-y-2">
                                <span class="text-xs font-black uppercase text-slate-600 ml-1">End</span>
                                <input v-model="query.date_end" type="date" class="w-full bg-white/5 border-none rounded-xl text-sm text-white p-3 focus:ring-1 focus:ring-indigo-500 transition-all font-mono" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content: Intelligence Visualizer -->
            <div class="flex-1 p-6 md:p-16 overflow-y-auto bg-slate-900/10">
                <div v-if="loading" class="h-full flex flex-col items-center justify-center space-y-6">
                    <div class="h-1.5 relative w-full max-w-xs bg-white/5 rounded-full overflow-hidden">
                        <div class="absolute inset-0 bg-indigo-500 animate-loading-bar"></div>
                    </div>
                    <p class="text-sm font-black uppercase tracking-[0.4em] text-indigo-400">Harvesting Points</p>
                </div>
                
                <div v-else-if="results.length > 0" class="space-y-12 md:space-y-16 max-w-5xl mx-auto">
                    <!-- Visual Board -->
                    <div class="space-y-6 md:space-y-8">
                         <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-2 px-2">
                             <h4 class="text-sm font-black text-indigo-300 uppercase tracking-[0.4em]">Projection</h4>
                             <span class="px-3 py-1 rounded-lg bg-white/5 text-xs font-black text-slate-600 border border-white/5 uppercase tracking-widest">{{ results.length }} Channels</span>
                         </div>
                         <div class="bg-black/40 p-6 md:p-12 rounded-[2rem] md:rounded-[3.5rem] border border-white/5 shadow-2xl h-[350px] md:h-[500px] relative overflow-hidden group">
                             <Bar :data="chartData" :options="chartOptions" />
                             <div class="absolute inset-0 pointer-events-none bg-[linear-gradient(rgba(18,16,16,0)_50%,rgba(0,0,0,0.1)_50%),linear-gradient(90deg,rgba(255,0,0,0.02),rgba(0,255,0,0.01),rgba(0,0,255,0.02))] z-20 bg-[length:100%_4px,3px_100%] opacity-20"></div>
                         </div>
                    </div>

                    <!-- Intelligence Grid (Table) -->
                    <div class="space-y-6 md:space-y-8">
                        <h4 class="text-sm font-black text-emerald-300 uppercase tracking-[0.4em] px-2">Intelligence Matrix</h4>
                        <div class="bg-white/[0.02] rounded-[1.5rem] md:rounded-[2.5rem] border border-white/5 overflow-hidden shadow-2xl">
                             <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse min-w-[600px] md:min-w-0">
                                    <thead>
                                        <tr class="border-b border-white/5 bg-white/5">
                                            <th class="px-6 md:px-10 py-5 text-xs md:text-sm font-black text-slate-500 uppercase tracking-[0.25em]">Operational Node</th>
                                            <th class="px-6 md:px-10 py-5 text-xs md:text-sm font-black text-slate-500 uppercase tracking-[0.25em] text-right">Mapping</th>
                                            <th class="px-6 md:px-10 py-5 text-xs md:text-sm font-black text-slate-500 uppercase tracking-[0.25em] text-center">Density</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5">
                                        <tr v-for="(row, idx) in results" :key="idx" class="group hover:bg-white/[0.03] transition-colors">
                                            <td class="px-6 md:px-10 py-6 md:py-8">
                                                <div class="text-base md:text-sm font-black text-white tracking-widest uppercase">{{ formatLabel(row) }}</div>
                                            </td>
                                            <td class="px-6 md:px-10 py-6 md:py-8 text-right tabular-nums">
                                                <span class="text-lg md:text-xl font-black text-emerald-400">{{ row.value }}</span>
                                                <span class="text-xs md:text-sm font-black text-slate-700 ml-2 uppercase tracking-tighter">{{ query.metric === 'count' ? 'Issues' : 'Hours' }}</span>
                                            </td>
                                            <td class="px-6 md:px-10 py-6 md:py-8">
                                                <div class="flex items-center justify-center">
                                                    <div class="w-16 md:w-24 h-1 bg-white/5 rounded-full overflow-hidden">
                                                        <div :style="{ width: (row.value / maxVal * 100) + '%' }" class="h-full bg-indigo-500 rounded-full"></div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                             </div>
                        </div>
                    </div>
                </div>

                <div v-else class="h-full flex flex-col items-center justify-center text-slate-600 animate-pulse">
                    <ChartBarIcon class="w-24 h-24 mb-10 opacity-10" />
                    <h3 class="text-xs font-black uppercase tracking-[1em] text-slate-500">Awaiting Query Input</h3>
                    <p class="text-sm font-bold text-slate-700 uppercase tracking-widest mt-4">Calibrate parameters in the control deck to projected data.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { 
    PlayIcon, 
    ArrowPathIcon, 
    ChartBarIcon, 
    Squares2X2Icon, 
    BoltIcon, 
    ClockIcon,
    AdjustmentsHorizontalIcon,
    XMarkIcon
} from '@heroicons/vue/24/solid';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import axios from 'axios';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const showMobileSidebar = ref(false);
const loading = ref(false);
const results = ref([]);
const query = ref({
    groupBy: 'module_id',
    metric: 'count',
    date_start: '',
    date_end: ''
});

const props = defineProps(['lookup']);

const dimensionOptions = [
    { id: 'module_id', name: 'Zone (Modules)' },
    { id: 'severity', name: 'Criticality (Sev)' },
    { id: 'priority', name: 'Efficiency (Prio)' },
    { id: 'workflow_stage_id', name: 'Lifecycle (Stage)' },
    { id: 'assignee_id', name: 'Human Resource' }
];

const metricOptions = [
    { id: 'count', name: 'Ticket Signal Mass', desc: 'Volume of incoming anomalies detected.' },
    { id: 'avg_resolution_time', name: 'Phase Stability (TTR)', desc: 'Average duration to reach terminal state.' }
];

const selectDimension = (id) => {
    query.value.groupBy = id;
    if (window.innerWidth < 768) {
        showMobileSidebar.value = false;
    }
};

const runQuery = async () => {
    loading.value = true;
    showMobileSidebar.value = false;
    try {
        const { data } = await axios.post(route('bugs.analytics.query'), query.value);
        results.value = data;
    } catch (e) {
        console.error("Query failed", e);
    } finally {
        setTimeout(() => { loading.value = false; }, 800); // Visual sustain
    }
};

const formatLabel = (row) => {
    const val = row[query.value.groupBy];
    if (query.value.groupBy === 'module_id' && row.module) return row.module.name;
    if (query.value.groupBy === 'workflow_stage_id' && row.stage) return row.stage.name;
    if (query.value.groupBy === 'assignee_id' && row.assignee) return row.assignee.name;
    return props.lookup?.[query.value.groupBy.replace('_id', 's')]?.[val] || val || 'Unmapped'; 
};

const maxVal = computed(() => Math.max(...results.value.map(r => r.value), 1));

const chartData = computed(() => {
    return {
        labels: results.value.map(r => formatLabel(r)),
        datasets: [{
            label: query.value.metric === 'count' ? 'MASS' : 'STABILITY',
            backgroundColor: (context) => {
                const ctx = context.chart.ctx;
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, '#6366f1');
                gradient.addColorStop(1, '#059669');
                return gradient;
            },
            borderRadius: 12,
            borderWidth: 0,
            barThickness: 40,
            data: results.value.map(r => r.value)
        }]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#0f172a',
            titleFont: { family: 'Inter', weight: 'bold', size: 14 },
            bodyFont: { family: 'Inter', size: 12 },
            padding: 18,
            cornerRadius: 16,
            displayColors: false
        }
    },
    scales: {
        y: { 
            beginAtZero: true, 
            grid: { color: 'rgba(255,255,255,0.03)', drawBorder: false },
            ticks: { color: '#475569', font: { weight: 'bold', size: 10 } }
        },
        x: { 
            grid: { display: false },
            ticks: { color: '#475569', font: { weight: 'black', size: 9 }, callback: function(val) {
                const label = this.getLabelForValue(val);
                return label.length > 8 ? label.substring(0, 8) + '...' : label;
            }}
        }
    }
};
</script>

<style scoped>
@keyframes loading-bar { 
    0% { left: -100%; width: 50%; } 
    50% { left: 25%; width: 75%; }
    100% { left: 100%; width: 50%; }
}
.animate-loading-bar { position: absolute; animation: loading-bar 1.5s infinite ease-in-out; }
</style>
