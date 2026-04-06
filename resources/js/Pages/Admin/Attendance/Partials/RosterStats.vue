<template>
    <div class="space-y-12 animate-fade-in relative z-10">
        <!-- Roster Lifecycle Intelligence Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div>
                <h2 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-3 uppercase leading-none">
                    <span class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg group-hover:bg-emerald-600 transition-colors">
                        <i class="fas fa-microchip text-base"></i>
                    </span>
                    ROSTER_SYNERGY_OS
                </h2>
                <div class="flex items-center gap-3 mt-3">
                    <div class="flex items-center gap-2 px-2.5 py-1 bg-emerald-50 rounded-lg border border-emerald-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                        <span class="text-xs font-black text-emerald-600 uppercase tracking-widest">Logic Decoupled</span>
                    </div>
                    <span class="text-slate-400 font-black text-xs uppercase tracking-[0.2em] px-1">Operational Pulse • Coverage: {{ stats?.coverage }}%</span>
                </div>
            </div>
            
            <button @click="fetchData" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-emerald-600 bg-white rounded-xl border border-slate-200 shadow-sm transition-all active:scale-95 group">
                <i class="fas fa-arrows-rotate text-base" :class="{'animate-spin text-emerald-500': loading}"></i>
            </button>
        </div>

        <div v-if="loading" class="h-96 flex flex-col items-center justify-center space-y-6 bg-white/40 backdrop-blur-md rounded-[48px] border border-white border-dashed">
            <div class="w-16 h-16 border-4 border-emerald-500/10 border-t-emerald-600 rounded-full animate-spin"></div>
            <p class="text-sm font-black text-emerald-600 uppercase tracking-[0.3em] animate-pulse">Syncing Lifecycle Data...</p>
        </div>

        <div v-else class="space-y-10">
            <!-- Strategic KPI Matrix -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-3">
                        <div class="w-9 h-9 bg-slate-50 rounded-xl flex items-center justify-center text-emerald-500 group-hover:bg-slate-900 group-hover:text-white transition-all duration-500 shadow-inner">
                            <i class="fas fa-shield-check text-base"></i>
                        </div>
                        <span class="text-xs font-black text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-100 uppercase tracking-widest shadow-sm">Integrity</span>
                    </div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5 leading-none">Squad Coverage</p>
                    <div class="text-2xl font-black text-slate-800 tracking-tighter leading-none group-hover:text-emerald-700 transition-colors">{{ stats.coverage }}<span class="text-xs text-emerald-500 ml-1">%</span></div>
                    <div class="w-full bg-slate-100 rounded-full h-1 mt-4 overflow-hidden shadow-inner">
                        <div class="h-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.4)] transition-all duration-1000" :style="{ width: stats.coverage + '%' }"></div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-3">
                        <div class="w-9 h-9 bg-slate-50 rounded-xl flex items-center justify-center text-blue-500 group-hover:bg-slate-900 group-hover:text-white transition-all duration-500 shadow-inner">
                            <i class="fas fa-door-open text-base"></i>
                        </div>
                        <span class="text-xs font-black text-blue-500 bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-100 uppercase tracking-widest shadow-sm">Capacity</span>
                    </div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5 leading-none">Open Slots</p>
                    <div class="text-2xl font-black text-slate-800 tracking-tighter leading-none group-hover:text-blue-600 transition-colors">{{ stats.open_shifts }} <span class="text-xs text-blue-400 ml-1 uppercase">Allocation</span></div>
                    <p class="text-xs font-black text-blue-400 uppercase tracking-widest mt-3 px-1">Action Protocol Required</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-3">
                        <div class="w-9 h-9 bg-slate-50 rounded-xl flex items-center justify-center text-rose-500 group-hover:bg-slate-900 group-hover:text-white transition-all duration-500 shadow-inner">
                            <i class="fas fa-triangle-exclamation text-base"></i>
                        </div>
                        <span class="text-xs font-black text-rose-500 bg-rose-50 px-2 py-0.5 rounded-lg border border-rose-100 uppercase tracking-widest shadow-sm">Critical</span>
                    </div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5 leading-none">Logic Conflicts</p>
                    <div class="text-2xl font-black text-rose-600 tracking-tighter leading-none group-hover:text-rose-700 transition-colors">{{ stats.conflicts }} <span class="text-xs text-rose-300 ml-1 uppercase">Errors</span></div>
                    <p class="text-xs font-black text-rose-400 uppercase tracking-widest mt-3 px-1">Manual Intervention</p>
                </div>

                <div class="bg-slate-900 p-5 rounded-2xl shadow-lg relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
                    <div class="relative z-10 flex flex-col justify-between h-full">
                        <div>
                            <p class="text-xs font-black text-emerald-400 uppercase tracking-[0.2em] mb-2 leading-none">Autonomous Solver</p>
                            <h4 class="text-xs font-black text-white tracking-tight uppercase leading-none">Shift Optimization</h4>
                        </div>
                        <button class="mt-8 h-9 bg-emerald-600 text-white rounded-xl text-sm font-black uppercase tracking-widest shadow-lg shadow-emerald-900/40 hover:bg-emerald-500 active:scale-95 transition-all flex items-center justify-center px-4">
                            SYNC_OPTIM_ENGINE
                        </button>
                    </div>
                </div>
            </div>

            <!-- Visualization Matrix -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all duration-300 hover:border-emerald-500">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-sm font-black text-slate-800 tracking-tight uppercase">Coverage Breakdown</h3>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1">Operational Allocation Efficiency</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fas fa-chart-pie text-[14px]"></i>
                        </div>
                    </div>
                    <div class="h-64 relative">
                        <Doughnut :data="doughnutData" :options="chartOptions" />
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="text-center">
                                <span class="text-2xl font-black text-slate-800 tracking-tighter tabular-nums">{{ stats.coverage }}<span class="text-xs font-black text-emerald-500 ml-0.5">%</span></span>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Deployed</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all duration-300 hover:border-emerald-500">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-sm font-black text-slate-800 tracking-tight uppercase">Role Distribution</h3>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1">Workforce Specialization Density</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fas fa-chart-bar text-[14px]"></i>
                        </div>
                    </div>
                    <div class="h-64">
                        <Bar :data="barData" :options="chartOptions" />
                    </div>
                </div>
            </div>

            <!-- High-Density Distribution Matrix -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 transition-all hover:border-emerald-500/30">
                <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-xs font-black text-slate-800 tracking-tight uppercase">Shift Energy Matrix</h3>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-2 leading-none px-0.5">Real-time Allocation Signature</p>
                    </div>
                    <span class="px-4 py-1.5 bg-emerald-50 rounded-xl text-xs font-black text-emerald-600 uppercase tracking-widest border border-emerald-100 shadow-sm">Live Snapshot v2.0</span>
                </div>
                
                <div class="space-y-10">
                    <div class="w-full bg-slate-50 rounded-2xl h-10 overflow-hidden flex shadow-inner group p-1 border border-slate-100">
                        <div v-for="(val, key, index) in stats.shift_allocation" :key="key"
                            class="h-full flex items-center justify-center text-xs font-black text-white transition-all duration-500 hover:brightness-110 cursor-pointer relative overflow-hidden group-hover:opacity-80 hover:!opacity-100 border-r border-white/10 last:border-0 rounded-lg mr-0.5 last:mr-0"
                            :class="[
                                'bg-slate-900', 
                                'bg-emerald-600', 
                                'bg-slate-700', 
                                'bg-emerald-400'
                            ][index % 4]"
                            :style="{ width: val + '%' }"
                            :title="key + ': ' + val + '%'">
                            <span class="px-4 truncate relative z-10 uppercase tracking-tighter">{{ key }}</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                         <div v-for="(val, key, index) in stats.shift_allocation" :key="key" class="flex items-center gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100 group hover:bg-white hover:border-emerald-500 transition-all duration-300 shadow-sm">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shadow-lg shrink-0" 
                                :class="[
                                    'bg-slate-900', 
                                    'bg-emerald-600', 
                                    'bg-slate-700', 
                                    'bg-emerald-400'
                                ][index % 4]">
                                <i class="fas fa-layer-group text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none mb-2">{{ key }}</p>
                                <p class="text-xl font-black text-slate-800 tracking-tighter leading-none group-hover:text-emerald-700 transition-colors">{{ val }}<span class="text-sm text-emerald-500 ml-1">%</span></p>
                            </div>
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
import { Doughnut, Bar } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps(['filters']);
const stats = ref(null);
const loading = ref(true);

const doughnutData = ref({ labels: [], datasets: [] });
const barData = ref({ labels: [], datasets: [] });

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '80%',
    plugins: {
        legend: { 
            position: 'bottom', 
            labels: { 
                usePointStyle: true, 
                padding: 30,
                font: {
                    family: "'Inter', sans-serif",
                    weight: '800',
                    size: 10
                },
                color: '#64748b',
                textTransform: 'uppercase'
            } 
        }
    }
};

const fetchData = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/admin/attendance/monitoring/data', { params: { type: 'roster' } });
        stats.value = res.data;
        
        // Prepare Charts
        doughnutData.value = {
            labels: ['SYNC COVERAGE', 'OPEN GAP'],
            datasets: [{
                data: [res.data.coverage, 100 - res.data.coverage],
                backgroundColor: ['#10b981', '#f1f5f9'],
                hoverBackgroundColor: ['#059669', '#e2e8f0'],
                borderWidth: 0,
                borderRadius: 12
            }]
        };

        barData.value = {
            labels: Object.keys(res.data.role_distribution).map(k => k.toUpperCase()),
            datasets: [{
                label: 'OPERATIVE DENSITY',
                data: Object.values(res.data.role_distribution),
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                hoverBackgroundColor: '#10b981',
                borderRadius: 8,
                barThickness: 32
            }]
        };
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const formatDate = () => {
    return new Date().toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' }).toUpperCase();
};

onMounted(() => {
    fetchData();
});
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
