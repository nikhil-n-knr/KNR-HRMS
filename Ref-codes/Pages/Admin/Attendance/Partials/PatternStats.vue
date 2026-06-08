<template>
    <div class="space-y-12 animate-fade-in relative z-10">
        <!-- Cognitive Pattern Intelligence Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div>
                <h2 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-3 uppercase leading-none">
                    <span class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg group-hover:bg-emerald-600 transition-colors">
                        <i class="fas fa-brain text-base"></i>
                    </span>
                    PATTERN_RECOGNITION_OS
                </h2>
                <div class="flex items-center gap-3 mt-3">
                    <div class="flex items-center gap-2 px-2.5 py-1 bg-emerald-50 rounded-lg border border-emerald-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                        <span class="text-xs font-black text-emerald-600 uppercase tracking-widest">Neural Cluster Active</span>
                    </div>
                    <span class="text-slate-400 font-black text-xs uppercase tracking-[0.2em] px-1">Compliance Health Meta-Sync</span>
                </div>
            </div>
            
            <button @click="fetchData" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-emerald-600 bg-white rounded-xl border border-slate-200 shadow-sm transition-all active:scale-95 group">
                <i class="fas fa-arrows-rotate text-base" :class="{'animate-spin text-emerald-500': loading}"></i>
            </button>
        </div>

        <div v-if="loading" class="h-96 flex flex-col items-center justify-center space-y-6 bg-white/40 backdrop-blur-md rounded-[48px] border border-white border-dashed">
            <div class="w-16 h-16 border-4 border-emerald-500/10 border-t-emerald-600 rounded-full animate-spin"></div>
            <p class="text-sm font-black text-emerald-600 uppercase tracking-[0.3em] animate-pulse">Syncing Pattern Data...</p>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Strategic Performance Column -->
            <div class="space-y-6">
                <div class="bg-slate-900 p-8 rounded-2xl border border-slate-800 shadow-md text-white relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
                    <div class="relative z-10 flex flex-col justify-between h-full">
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center text-white backdrop-blur-md">
                                <i class="fas fa-shield-heart text-[16px]"></i>
                            </div>
                            <span class="text-sm font-black text-emerald-400 uppercase tracking-widest">Protocol Integrity</span>
                        </div>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Squad Adherence</p>
                        <div class="text-5xl font-black text-white tracking-tighter tabular-nums">{{ stats.adherence }}<span class="text-lg ml-1 text-emerald-500">%</span></div>
                        <p class="mt-4 text-emerald-400/60 text-sm font-black uppercase tracking-tight leading-relaxed italic">
                            Overall compliance threshold remains within optimal operating range.
                        </p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-500 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                            <i class="fas fa-circle-exclamation text-[14px]"></i>
                        </div>
                        <span class="text-sm font-black text-amber-500 uppercase tracking-widest">Deviation</span>
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Pattern Anomalies</p>
                    <div class="text-3xl font-black text-slate-800 tracking-tighter tabular-nums">{{ stats.deviations }} <span class="text-sm text-amber-500 ml-1 font-mono uppercase">D_MISC</span></div>
                    <p class="text-xs font-black text-amber-500/60 uppercase tracking-widest mt-3 italic">Action Recommended</p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-500 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                            <i class="fas fa-heart-pulse text-[14px]"></i>
                        </div>
                        <span class="text-sm font-black text-emerald-500 uppercase tracking-widest">Systematic</span>
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Rotation Bio-Health</p>
                    <div class="text-3xl font-black text-slate-800 tracking-tighter tabular-nums">{{ stats.rotation_health }}</div>
                    <p class="text-xs font-black text-emerald-500/60 uppercase tracking-widest mt-3 italic">Nominal Cycle</p>
                </div>
            </div>

            <!-- Neural Timeline Matrix -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col relative overflow-hidden group hover:border-emerald-500 transition-all duration-300">
                <div class="absolute top-0 right-0 w-48 h-48 bg-emerald-500/5 rounded-full blur-2xl -mr-24 -mt-24"></div>
                
                <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-50 relative z-10">
                    <div>
                        <h3 class="text-sm font-black text-slate-800 tracking-tight uppercase">Compliance Neural Timeline</h3>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1">Personnel Adherence Evolution</p>
                    </div>
                    <div class="flex gap-2">
                        <span class="px-3 py-1 bg-emerald-50 rounded-lg text-xs font-black text-emerald-600 uppercase tracking-widest border border-emerald-100 shadow-sm">Synchronized v4.2</span>
                    </div>
                </div>

                <div class="flex-1 relative min-h-[300px] z-10">
                    <Line :data="chartData" :options="chartOptions" />
                </div>

                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4 relative z-10">
                    <div class="p-4 bg-slate-50/50 rounded-xl border border-slate-100 transition-all group/card hover:bg-white hover:border-emerald-200 hover:shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center text-white shadow-sm group-hover/card:scale-110 transition-transform">
                                <i class="fas fa-wave-pulse text-base"></i>
                            </div>
                            <div>
                                <h5 class="text-sm font-black text-slate-800 uppercase tracking-widest">Stable Adherence</h5>
                                <p class="text-xs font-black text-slate-400 leading-relaxed mt-1 uppercase tracking-tighter">High compliance indicates robust shift assignment integrity and team stability.</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50/50 rounded-xl border border-slate-100 transition-all group/card hover:bg-white hover:border-emerald-200 hover:shadow-sm">
                         <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-200 flex items-center justify-center text-slate-500 shadow-sm group-hover/card:scale-110 transition-transform">
                                <i class="fas fa-mountain-sun text-base"></i>
                            </div>
                            <div>
                                <h5 class="text-sm font-black text-slate-800 uppercase tracking-widest">Temporal Variance</h5>
                                <p class="text-xs font-black text-slate-400 leading-relaxed mt-1 uppercase tracking-tighter">Strategic dips typically correlate with off-peak cycles or scheduled stand-downs.</p>
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
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

const stats = ref(null);
const loading = ref(true);
const chartData = ref({ labels: [], datasets: [] });

const chartOptions = {
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
            displayColors: false,
            callbacks: {
                label: (cx) => `INTEGRITY_INDEX: ${cx.parsed.y}%`
            }
        }
    },
    scales: {
        y: { 
            beginAtZero: true, 
            max: 100,
            grid: { color: '#f8fafc', drawBorder: false },
            ticks: {
                color: '#94a3b8',
                font: { family: "'Inter', sans-serif", weight: '800', size: 10 },
                callback: (v) => `${v}%`
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
    elements: {
        line: { tension: 0.45, borderWidth: 4 },
        point: { radius: 0, hoverRadius: 8, hoverBorderWidth: 4 }
    }
};

const fetchData = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/admin/attendance/monitoring/data', { params: { type: 'pattern' } });
        stats.value = res.data;

        chartData.value = {
            labels: res.data.timeline.map(t => t.day.toUpperCase()),
            datasets: [{
                label: 'Compliance %',
                data: res.data.timeline.map(t => t.compliance),
                borderColor: '#10b981',
                backgroundColor: (context) => {
                    const chart = context.chart;
                    const {ctx, chartArea} = chart;
                    if (!chartArea) return null;
                    const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                    gradient.addColorStop(0, 'rgba(16, 185, 129, 0)');
                    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.15)');
                    return gradient;
                },
                fill: true,
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
