<template>
    <div class="space-y-6">
        <!-- Dashboard Toolbar -->
        <div class="flex justify-between items-center mb-2">
            <div>
                <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight">Intelligence Matrix</h3>
                <p class="text-[9px] text-slate-400 font-black uppercase tracking-widest mt-1">Real-time Procedural Telemetry</p>
            </div>
            <div class="flex items-center gap-3">
                <select v-model="filterEntityType" class="h-8 rounded-lg border-slate-200 bg-white text-[10px] font-bold uppercase tracking-widest px-3 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition-all hover:border-emerald-200 min-w-[120px]">
                    <option value="">All Procedures</option>
                    <option v-for="type in data.entity_types" :key="type" :value="type">{{ type.replace('_', ' ') }}</option>
                </select>
                <select v-model="range" class="h-8 rounded-lg border-slate-200 bg-white text-[10px] font-bold uppercase tracking-widest px-3 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition-all hover:border-emerald-200">
                    <option value="7_days">Last 7 Days</option>
                    <option value="30_days">Last 30 Days</option>
                    <option value="90_days">Last 90 Days</option>
                    <option value="year">Full Year</option>
                </select>
                <button @click="fetchStats" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm group" :disabled="loading">
                    <i class="fas fa-sync-alt text-[10px] group-hover:rotate-180 transition-transform duration-500" :class="{'animate-spin': loading}"></i>
                </button>
            </div>
        </div>

        <!-- Metric Command Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Total Throughput -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-all group overflow-hidden relative">
                <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-slate-50 rounded-full group-hover:scale-125 transition-transform duration-700 opacity-50"></div>
                <div class="relative z-10 flex flex-col h-full justify-between">
                    <div class="flex justify-between items-start">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Throughput</span>
                        <i class="fas fa-bolt text-[10px] text-amber-500"></i>
                    </div>
                    <div class="mt-4 flex flex-col">
                        <span class="text-3xl font-black text-slate-800 tracking-tighter">{{ stats.total }}</span>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[8px] font-black text-emerald-500 uppercase tracking-widest">Active Operations</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operational Velocity -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-all group overflow-hidden relative">
                <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-indigo-50/30 rounded-full group-hover:scale-125 transition-transform duration-700 opacity-50"></div>
                <div class="relative z-10 flex flex-col h-full justify-between">
                    <div class="flex justify-between items-start">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Velocity</span>
                        <i class="fas fa-tachometer-alt text-[10px] text-indigo-500"></i>
                    </div>
                    <div class="mt-4 flex flex-col">
                        <span class="text-3xl font-black text-slate-800 tracking-tighter">{{ formatDuration(data.avg_approval_time) }}</span>
                        <div class="flex items-center gap-1.5 mt-1">
                            <i class="fas fa-history text-[8px] text-slate-400"></i>
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Avg. Cycle Time</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval Density -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-all group overflow-hidden relative">
                 <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-emerald-50/50 rounded-full group-hover:scale-125 transition-transform duration-700 opacity-50"></div>
                <div class="relative z-10 flex flex-col h-full justify-between">
                    <div class="flex justify-between items-start">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Fidelity</span>
                        <i class="fas fa-check-double text-[10px] text-emerald-600"></i>
                    </div>
                    <div class="mt-4 flex flex-col">
                        <span class="text-3xl font-black text-slate-800 tracking-tighter">{{ stats.approved_pct }}%</span>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="text-[8px] font-black text-emerald-600 uppercase tracking-widest">Commit Rate</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottleneck Alert -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-all group overflow-hidden relative" :class="{'border-rose-100 bg-rose-50/10': data.bottlenecks.length > 0}">
                <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-rose-50 rounded-full group-hover:scale-125 transition-transform duration-700 opacity-50"></div>
                <div class="relative z-10 flex flex-col h-full justify-between">
                    <div class="flex justify-between items-start">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Bottlenecks</span>
                        <i class="fas fa-exclamation-triangle text-[10px]" :class="data.bottlenecks.length > 0 ? 'text-rose-600 animate-bounce' : 'text-slate-200'"></i>
                    </div>
                    <div class="mt-4 flex flex-col">
                        <span class="text-3xl font-black text-slate-800 tracking-tighter">{{ data.bottlenecks.length }}</span>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="text-[8px] font-black uppercase tracking-widest" :class="data.bottlenecks.length > 0 ? 'text-rose-600' : 'text-slate-300'">Nodes Blocked</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Intelligence Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Activity Pulse Chart -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 overflow-hidden">
                <div class="flex justify-between items-center mb-6">
                    <h4 class="text-[10px] font-black text-slate-800 uppercase tracking-[0.2em] flex items-center gap-2">
                        <span class="w-1.5 h-4 bg-emerald-600 rounded-full"></span>
                        Activity Telemetry
                    </h4>
                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Deployment Frequency</span>
                </div>
                <div class="h-64">
                    <BaseChart type="line" :data="activityChartData" :options="activityChartOptions" />
                </div>
            </div>

            <!-- Distribution Matrix -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h4 class="text-[10px] font-black text-slate-800 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                    <span class="w-1.5 h-4 bg-indigo-600 rounded-full"></span>
                    Operational State
                </h4>
                <div class="h-48 relative">
                    <BaseChart type="doughnut" :data="distChartData" :options="distChartOptions" />
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none pb-4">
                        <span class="text-xl font-black text-slate-800">{{ stats.total }}</span>
                        <span class="text-[7px] font-black text-slate-400 uppercase tracking-widest">Total Ops</span>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div v-for="(val, label) in data.status_distribution" :key="label" class="bg-slate-50 rounded-lg p-2 flex justify-between items-center group hover:bg-white border border-transparent hover:border-slate-100 transition-all">
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">{{ label }}</span>
                        <span class="text-[10px] font-black text-slate-700">{{ val }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottleneck Matrix Telemetry -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                <h4 class="text-[10px] font-black text-slate-800 uppercase tracking-[0.2em] flex items-center gap-2">
                    <i class="fas fa-stream text-rose-500 text-[10px]"></i>
                    Stalled Logical Nodes
                </h4>
                <div class="flex items-center gap-4">
                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest"><span class="w-2 h-2 rounded-full bg-rose-500 inline-block mr-1"></span> Critical Path</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-3 text-[8px] font-black text-slate-400 uppercase tracking-[0.15em]">Procedural Context</th>
                            <th class="px-6 py-3 text-[8px] font-black text-slate-400 uppercase tracking-[0.15em]">Stalled node</th>
                            <th class="px-6 py-3 text-[8px] font-black text-slate-400 uppercase tracking-[0.15em] text-center">Density</th>
                            <th class="px-6 py-3 text-[8px] font-black text-slate-400 uppercase tracking-[0.15em] text-right">Node Latency</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="(item, idx) in data.bottlenecks" :key="idx" class="hover:bg-slate-50/50 transition-all group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white border border-slate-100 shadow-sm flex items-center justify-center text-[10px] text-slate-400">
                                        <i class="fas fa-project-diagram"></i>
                                    </div>
                                    <span class="text-[11px] font-black text-slate-700 uppercase tracking-tight">{{ item.workflow }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-[10px] font-bold text-slate-600 bg-white px-2 py-1 rounded-md border border-slate-100 shadow-sm">{{ item.stage }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-rose-100 text-rose-600 px-2.5 py-1 rounded-full text-[10px] font-black shadow-sm shadow-rose-100">{{ item.pending_count }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-[11px] font-black text-rose-500 tracking-tighter">{{ formatDuration(item.avg_wait_hours) }}</span>
                                <div class="w-full h-1 bg-slate-100 rounded-full mt-2 overflow-hidden">
                                    <div class="h-full bg-rose-500 rounded-full" :style="{ width: Math.min((item.avg_wait_hours / 48) * 100, 100) + '%' }"></div>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="data.bottlenecks.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4 text-emerald-500 shadow-inner">
                                    <i class="fas fa-check"></i>
                                </div>
                                <span class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">All Logical Streams Clear</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';
import BaseChart from '@/Components/BaseChart.vue';

const props = defineProps({
    workflows: { type: Array, default: () => [] }
});

const range = ref('30_days');
const filterEntityType = ref('');
const loading = ref(false);
const data = ref({
    status_distribution: { pending: 0, approved: 0, rejected: 0, cancelled: 0 },
    avg_approval_time: 0,
    bottlenecks: [],
    activity: [],
    entity_types: []
});

const stats = computed(() => {
    const d = data.value.status_distribution;
    const total = (d.pending || 0) + (d.approved || 0) + (d.rejected || 0) + (d.cancelled || 0);
    const approved_pct = total > 0 ? Math.round((d.approved / total) * 100) : 0;
    return {
        total,
        approved_pct
    };
});

const fetchStats = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('admin.workflows.analytics.data'), {
            params: { 
                range: range.value,
                entity_type: filterEntityType.value
            }
        });
        data.value = response.data;
    } catch (e) {
        console.error("Failed to fetch analytics", e);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchStats);
watch([range, filterEntityType], fetchStats);

const formatDuration = (hours) => {
    if (!hours) return '0 hrs';
    if (hours < 1) return `${Math.round(hours * 60)} min`;
    if (hours < 24) return `${Math.round(hours)} hrs`;
    const days = Math.round(hours / 24);
    return `${days} day${days > 1 ? 's' : ''}`;
};

// --- Chart Configurations ---
const activityChartData = computed(() => {
    return {
        labels: data.value.activity.map(a => a.date.slice(5)), // MM-DD
        datasets: [{
            label: 'Requests',
            data: data.value.activity.map(a => a.count),
            borderColor: '#10b981', // Emerald-500
            backgroundColor: 'rgba(16, 185, 129, 0.05)',
            fill: true,
            tension: 0.4,
            borderWidth: 3,
            pointRadius: 0,
            pointHoverRadius: 6,
            pointHoverBackgroundColor: '#10b981',
            pointHoverBorderColor: '#fff',
            pointHoverBorderWidth: 2
        }]
    };
});

const activityChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#0f172a',
            titleFont: { size: 10, weight: 'bold', family: 'Outfit' },
            bodyFont: { size: 10, family: 'Outfit' },
            padding: 12,
            cornerRadius: 12,
            displayColors: false
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: { color: 'rgba(226, 232, 240, 0.5)', drawBorder: false },
            ticks: { font: { size: 8, family: 'Outfit', weight: 'bold' }, color: '#94a3b8' }
        },
        x: {
            grid: { display: false },
            ticks: { font: { size: 8, family: 'Outfit', weight: 'bold' }, color: '#94a3b8' }
        }
    }
};

const distChartData = computed(() => {
    const d = data.value.status_distribution;
    return {
        labels: ['Approved', 'Pending', 'Rejected', 'Cancelled'],
        datasets: [{
            data: [d.approved || 0, d.pending || 0, d.rejected || 0, d.cancelled || 0],
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#94a3b8'],
            borderWidth: 0,
            hoverOffset: 12
        }]
    };
});

const distChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '75%',
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#0f172a',
            padding: 12,
            cornerRadius: 12,
            bodyFont: { size: 10, family: 'Outfit', weight: 'bold' }
        }
    }
};
</script>

<style scoped>
.font-outfit { font-family: 'Outfit', sans-serif; }
.tracking-tight { letter-spacing: -0.025em; }
.tracking-tighter { letter-spacing: -0.05em; }

@keyframes contentFade {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
