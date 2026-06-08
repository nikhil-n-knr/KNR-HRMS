<template>
    <div class="space-y-12 animate-fade-in relative z-10">
        <!-- Shift Optimization Intelligence Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div>
                <h2 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-3 uppercase leading-none">
                    <span class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg group-hover:bg-emerald-600 transition-colors">
                        <i class="fas fa-network-wired text-base"></i>
                    </span>
                    SHIFT_TOPOLOGY_OS
                </h2>
                <div class="flex items-center gap-3 mt-3">
                    <div class="flex items-center gap-2 px-2.5 py-1 bg-emerald-50 rounded-lg border border-emerald-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                        <span class="text-xs font-black text-emerald-600 uppercase tracking-widest">Real-time Load Balancing</span>
                    </div>
                    <span class="text-slate-400 font-black text-xs uppercase tracking-[0.2em] px-1">Synchronization Protocol Alpha</span>
                </div>
            </div>
            
            <button @click="fetchData" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-emerald-600 bg-white rounded-xl border border-slate-200 shadow-sm transition-all active:scale-95 group">
                <i class="fas fa-arrows-rotate text-base" :class="{'animate-spin text-emerald-500': loading}"></i>
            </button>
        </div>

        <div v-if="loading" class="h-96 flex flex-col items-center justify-center space-y-6 bg-white/40 backdrop-blur-md rounded-[48px] border border-white border-dashed">
            <div class="w-16 h-16 border-4 border-emerald-500/10 border-t-emerald-600 rounded-full animate-spin"></div>
            <p class="text-sm font-black text-emerald-600 uppercase tracking-[0.3em] animate-pulse">Syncing Topology Data...</p>
        </div>

        <div v-else class="space-y-10">
            <!-- Strategic Performance Matrix -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-3">
                        <div class="w-9 h-9 bg-slate-50 rounded-xl flex items-center justify-center text-emerald-500 group-hover:bg-slate-900 group-hover:text-white transition-all duration-500 shadow-inner">
                            <i class="fas fa-stopwatch text-base"></i>
                        </div>
                        <span class="text-xs font-black text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-100 uppercase tracking-widest shadow-sm">Temporal</span>
                    </div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5 leading-none">Avg Runtime</p>
                    <div class="text-2xl font-black text-slate-800 tracking-tighter leading-none group-hover:text-emerald-700 transition-colors">{{ stats.avg_hours }} <span class="text-xs text-emerald-500 ml-1 uppercase">hrs/d</span></div>
                    <div class="w-full bg-slate-100 rounded-full h-1 mt-4 overflow-hidden shadow-inner">
                        <div class="h-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.4)] transition-all duration-1000" :style="{ width: (stats.avg_hours / 12 * 100) + '%' }"></div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-3">
                        <div class="w-9 h-9 bg-slate-50 rounded-xl flex items-center justify-center text-emerald-500 group-hover:bg-slate-900 group-hover:text-white transition-all duration-500 shadow-inner">
                            <i class="fas fa-bolt text-base"></i>
                        </div>
                        <span class="text-xs font-black text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-100 uppercase tracking-widest shadow-sm">Efficiency</span>
                    </div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5 leading-none">Workforce Utilization</p>
                    <div class="text-2xl font-black text-slate-800 tracking-tighter leading-none group-hover:text-emerald-700 transition-colors">{{ stats.utilization }}<span class="text-xs text-emerald-500 ml-1">%</span></div>
                    <div class="w-full bg-slate-100 rounded-full h-1 mt-4 overflow-hidden shadow-inner">
                        <div class="h-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.4)] transition-all duration-1000" :style="{ width: stats.utilization + '%' }"></div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-3">
                        <div class="w-9 h-9 bg-slate-50 rounded-xl flex items-center justify-center text-emerald-500 group-hover:bg-slate-900 group-hover:text-white transition-all duration-500 shadow-inner">
                            <i class="fas fa-layer-group text-base"></i>
                        </div>
                        <span class="text-xs font-black text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-100 uppercase tracking-widest shadow-sm">Structural</span>
                    </div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5 leading-none">Topology Nodes</p>
                    <div class="text-2xl font-black text-slate-800 tracking-tighter leading-none group-hover:text-emerald-700 transition-colors">{{ stats.shifts.length }} <span class="text-xs text-emerald-500 ml-1 uppercase">Units</span></div>
                    <p class="text-xs font-black text-emerald-400 uppercase tracking-widest mt-3 px-1">Node Integrity Stable</p>
                </div>
            </div>

            <!-- Deep Dive Topology Matrix -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
                <!-- Topological Distribution -->
                <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all duration-300 hover:border-emerald-500 group">
                    <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="text-xs font-black text-slate-800 tracking-tight uppercase">Staff Topology Density</h3>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-2 leading-none px-0.5">Personnel Allocation Weighting</p>
                        </div>
                        <div class="w-9 h-9 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-slate-900 group-hover:text-white transition-all duration-500 shadow-inner border border-slate-100">
                            <i class="fas fa-chart-column text-base"></i>
                        </div>
                    </div>
                    <div class="h-64">
                        <Bar :data="chartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Load Integrity Analysis -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all duration-300 hover:border-emerald-500 flex flex-col group">
                    <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100 shrink-0">
                        <div>
                            <h3 class="text-xs font-black text-slate-800 tracking-tight uppercase">Load Integrity</h3>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-2 leading-none px-0.5">Stress Distribution Analysis</p>
                        </div>
                        <div class="w-9 h-9 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-slate-900 group-hover:text-white transition-all duration-500 shadow-inner border border-slate-100">
                            <i class="fas fa-gauge-high text-base"></i>
                        </div>
                    </div>
                    
                    <div class="flex-1 space-y-7 overflow-y-auto pr-2 custom-scrollbar">
                        <div v-for="shift in stats.shifts" :key="shift.name" class="group/item">
                            <div class="flex justify-between items-center mb-2.5">
                                <span class="text-sm font-black text-slate-600 uppercase tracking-tighter leading-none">{{ shift.name }}</span>
                                <span class="px-2.5 py-1 bg-slate-50 rounded-lg text-xs font-black border border-slate-100 transition-all leading-none" 
                                    :class="shift.load > 80 ? 'text-rose-500 border-rose-100 bg-rose-50 shadow-sm' : 'text-emerald-500 border-emerald-100 bg-emerald-50 shadow-sm'">
                                    {{ shift.load }}%_LOAD
                                </span>
                            </div>
                            <div class="w-full bg-slate-100/50 rounded-full h-1.5 overflow-hidden shadow-inner">
                                <div class="h-full rounded-full transition-all duration-1000 group-hover/item:brightness-110"
                                     :class="shift.load > 80 ? 'bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.4)]' : (shift.load > 50 ? 'bg-blue-500' : 'bg-emerald-500')"
                                     :style="{ width: shift.load + '%' }"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tactical Intelligence Alert -->
                    <div class="mt-8 p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100 shrink-0 shadow-sm">
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-xl bg-emerald-600 flex items-center justify-center text-white text-sm shadow-lg shrink-0">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <p class="text-xs font-black text-emerald-900 leading-normal uppercase tracking-widest mt-0.5">
                                High load nodes detected. Strategic reallocation recommended to prevent operational fatigue.
                            </p>
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
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const stats = ref(null);
const loading = ref(true);
const chartData = ref({ labels: [], datasets: [] });

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false }
    },
    scales: {
        y: { 
            beginAtZero: true, 
            grid: { color: '#f1f5f9', drawBorder: false },
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
    }
};

const fetchData = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/admin/attendance/monitoring/data', { params: { type: 'shift' } });
        stats.value = res.data;

        chartData.value = {
            labels: res.data.shifts.map(s => s.name.toUpperCase()),
            datasets: [{
                label: 'Staff Count',
                data: res.data.shifts.map(s => s.count),
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)', 
                    'rgba(16, 185, 129, 0.8)', 
                    'rgba(99, 102, 241, 0.8)', 
                    'rgba(236, 72, 153, 0.8)', 
                    'rgba(139, 92, 246, 0.8)'
                ],
                hoverBackgroundColor: ['#3b82f6', '#10b981', '#6366f1', '#ec4899', '#8b5cf6'],
                borderRadius: 16,
                barThickness: 45
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
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
