<template>
    <div class="space-y-12 animate-fade-in relative z-10">
        <!-- Administrative Request Intelligence Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div>
                <h2 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-3 uppercase leading-none">
                    <span class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg group-hover:bg-emerald-600 transition-colors">
                        <i class="fas fa-file-signature text-base"></i>
                    </span>
                    REQUEST_FLOW_OS
                </h2>
                <div class="flex items-center gap-3 mt-3">
                    <div class="flex items-center gap-2 px-2.5 py-1 bg-emerald-50 rounded-lg border border-emerald-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                        <span class="text-xs font-black text-emerald-600 uppercase tracking-widest">Workflow Engine Online</span>
                    </div>
                    <span class="text-slate-400 font-black text-xs uppercase tracking-[0.2em] px-1">Administrative Lifecycle Tracking</span>
                </div>
            </div>
            
            <button @click="fetchData" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-emerald-600 bg-white rounded-xl border border-slate-200 shadow-sm transition-all active:scale-95 group">
                <i class="fas fa-arrows-rotate text-base" :class="{'animate-spin text-emerald-500': loading}"></i>
            </button>
        </div>

        <div v-if="loading" class="h-96 flex flex-col items-center justify-center space-y-6 bg-white/40 backdrop-blur-md rounded-[48px] border border-white border-dashed">
            <div class="w-16 h-16 border-4 border-emerald-500/10 border-t-emerald-600 rounded-full animate-spin"></div>
            <p class="text-sm font-black text-emerald-600 uppercase tracking-[0.3em] animate-pulse">Syncing Request Topology...</p>
        </div>

        <div v-else class="space-y-10">
            <!-- Strategic Throughput Matrix -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-3">
                        <div class="w-9 h-9 bg-slate-50 rounded-xl flex items-center justify-center text-emerald-500 group-hover:bg-slate-900 group-hover:text-white transition-all duration-500">
                            <i class="fas fa-check-double text-base"></i>
                        </div>
                        <span class="text-xs font-black text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-100 uppercase tracking-widest shadow-sm">Validated</span>
                    </div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5 leading-none">Total Approved</p>
                    <div class="text-2xl font-black text-slate-800 tracking-tighter tabular-nums leading-none group-hover:text-emerald-700 transition-colors">{{ stats.approved }}</div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-3">
                        <div class="w-9 h-9 bg-slate-50 rounded-xl flex items-center justify-center text-amber-500 group-hover:bg-slate-900 group-hover:text-white transition-all duration-500">
                            <i class="fas fa-hourglass-half text-base"></i>
                        </div>
                        <span class="text-xs font-black text-amber-500 bg-amber-50 px-2 py-0.5 rounded-lg border border-amber-100 uppercase tracking-widest shadow-sm">Awaiting</span>
                    </div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5 leading-none">Pending Action</p>
                    <div class="text-2xl font-black text-slate-800 tracking-tighter tabular-nums leading-none group-hover:text-amber-600 transition-colors">{{ stats.pending }}</div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-3">
                        <div class="w-9 h-9 bg-slate-50 rounded-xl flex items-center justify-center text-rose-500 group-hover:bg-slate-900 group-hover:text-white transition-all duration-500">
                            <i class="fas fa-rectangle-xmark text-base"></i>
                        </div>
                        <span class="text-xs font-black text-rose-500 bg-rose-50 px-2 py-0.5 rounded-lg border border-rose-100 uppercase tracking-widest shadow-sm">Rejected</span>
                    </div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5 leading-none">System Denials</p>
                    <div class="text-2xl font-black text-rose-600 tracking-tighter tabular-nums leading-none">{{ stats.rejected }}</div>
                </div>

                <div class="bg-slate-900 p-5 rounded-2xl shadow-lg text-white relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 bg-white/10 rounded-xl flex items-center justify-center text-white backdrop-blur-md">
                                <i class="fas fa-bolt-lightning text-base"></i>
                            </div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] leading-none">Velocity</p>
                        </div>
                        <div class="flex items-end gap-1.5">
                            <h3 class="text-2xl font-black text-white tracking-tighter tabular-nums leading-none">{{ stats.avg_response_time }}</h3>
                        </div>
                        <p class="text-xs font-black text-emerald-400 uppercase tracking-[0.2em] mt-3 leading-none">Mean Response Time</p>
                    </div>
                </div>
            </div>

            <!-- Deep Dive Analysis Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Incoming Request Pulse -->
                <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="text-xs font-black text-slate-800 tracking-tight uppercase">Incoming Request Pulse</h3>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-2 leading-none px-0.5">Temporal Workflow Distribution</p>
                        </div>
                        <div class="w-9 h-9 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-slate-900 group-hover:text-white transition-all duration-500 shadow-inner border border-slate-100">
                            <i class="fas fa-chart-line text-base"></i>
                        </div>
                    </div>
                    <div class="h-80">
                        <Line :data="trendData" :options="lineOptions" />
                    </div>
                </div>

                <!-- Status Integrity Ring -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center group hover:border-emerald-500 transition-all duration-300">
                    <div class="w-full flex items-center justify-between mb-8 pb-4 border-b border-slate-100 shrink-0">
                        <div>
                            <h3 class="text-xs font-black text-slate-800 tracking-tight uppercase">Status Composition</h3>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-2 leading-none px-0.5">Decision Integrity Ratios</p>
                        </div>
                        <div class="w-9 h-9 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-slate-900 group-hover:text-white transition-all duration-500 shadow-inner border border-slate-100">
                            <i class="fas fa-chart-pie text-base"></i>
                        </div>
                    </div>
                    
                    <div class="flex-1 relative w-full flex items-center justify-center min-h-[250px]">
                        <div class="w-full h-full">
                            <Doughnut :data="statusData" :options="doughnutOptions" />
                        </div>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none pt-2">
                            <span class="text-4xl font-black text-slate-800 tracking-tighter tabular-nums leading-none">{{ stats.approved + stats.pending + stats.rejected }}</span>
                            <span class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-2.5">Total_Logs</span>
                        </div>
                    </div>

                    <!-- Tactical Key -->
                    <div class="w-full mt-8 grid grid-cols-3 gap-3">
                        <div class="px-2 py-2.5 bg-emerald-50 rounded-xl border border-emerald-100 shadow-sm text-center group/key hover:scale-105 transition-transform">
                            <div class="text-sm font-black text-emerald-600 tracking-tighter tabular-nums">{{ Math.round((stats.approved / (stats.approved + stats.pending + stats.rejected)) * 100) }}%</div>
                            <div class="text-xs font-black text-emerald-400 uppercase tracking-widest mt-1">Pass</div>
                        </div>
                        <div class="px-2 py-2.5 bg-amber-50 rounded-xl border border-amber-100 shadow-sm text-center group/key hover:scale-105 transition-transform">
                            <div class="text-sm font-black text-amber-600 tracking-tighter tabular-nums">{{ Math.round((stats.pending / (stats.approved + stats.pending + stats.rejected)) * 100) }}%</div>
                            <div class="text-xs font-black text-amber-400 uppercase tracking-widest mt-1">Wait</div>
                        </div>
                        <div class="px-2 py-2.5 bg-rose-50 rounded-xl border border-rose-100 shadow-sm text-center group/key hover:scale-105 transition-transform">
                            <div class="text-sm font-black text-rose-600 tracking-tighter tabular-nums">{{ Math.round((stats.rejected / (stats.approved + stats.pending + stats.rejected)) * 100) }}%</div>
                            <div class="text-xs font-black text-rose-400 uppercase tracking-widest mt-1">Fail</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Line, Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, ArcElement, Filler } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, ArcElement, Filler);

const stats = ref(null);
const loading = ref(true);
const trendData = ref({ labels: [], datasets: [] });
const statusData = ref({ labels: [], datasets: [] });

const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    hover: { intersect: false, mode: 'index' },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#ffffff',
            titleColor: '#1e293b',
            bodyColor: '#64748b',
            titleFont: { family: "'Inter', sans-serif", weight: '900', size: 12 },
            bodyFont: { family: "'Inter', sans-serif", weight: 'bold', size: 11 },
            padding: 16,
            borderColor: '#e2e8f0',
            borderWidth: 1,
            displayColors: false
        }
    },
    scales: { 
        y: { 
            beginAtZero: true, 
            grid: { color: '#f8fafc', drawBorder: false },
            ticks: {
                color: '#94a3b8',
                font: { family: "'Inter', sans-serif", weight: '800', size: 10 }
            }
        }, 
        x: { 
            grid: { display: false },
            ticks: {
                color: '#64748b',
                font: { family: "'Inter', sans-serif", weight: '800', size: 10 }
            }
        } 
    },
    elements: { line: { tension: 0.45, borderWidth: 4 }, point: { radius: 0, hoverRadius: 6 } }
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '80%',
    plugins: { 
        legend: { display: false }
    },
    elements: {
        arc: {
            borderRadius: 16,
            borderWidth: 0
        }
    }
};

const fetchData = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/admin/attendance/monitoring/data', { params: { type: 'request' } });
        stats.value = res.data;

        trendData.value = {
            labels: res.data.trend.map(t => t.date.toUpperCase()),
            datasets: [{
                label: 'Requests',
                data: res.data.trend.map(t => t.count),
                borderColor: '#10b981',
                backgroundColor: (context) => {
                    const chart = context.chart;
                    const {ctx, chartArea} = chart;
                    if (!chartArea) return null;
                    const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                    gradient.addColorStop(0, 'rgba(16, 185, 129, 0)');
                    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.1)');
                    return gradient;
                },
                fill: true,
            }]
        };

        statusData.value = {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                data: [res.data.approved, res.data.pending, res.data.rejected],
                backgroundColor: ['#10b981', '#f59e0b', '#f43f5e'],
                hoverBackgroundColor: ['#10b981', '#f59e0b', '#f43f5e'],
                hoverOffset: 12
            }]
        };
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchData);
</script>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
