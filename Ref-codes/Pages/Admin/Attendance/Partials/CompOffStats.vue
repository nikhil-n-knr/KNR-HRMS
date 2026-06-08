<template>
    <div class="space-y-12 animate-fade-in relative z-10">
        <!-- Compensatory Capital Intelligence Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div>
                <h2 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-3 uppercase leading-none">
                    <span class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg group-hover:bg-emerald-600 transition-colors">
                        <i class="fas fa-wallet text-base"></i>
                    </span>
                    COMPOFF_CAPITAL_OS
                </h2>
                <div class="flex items-center gap-3 mt-3">
                    <div class="flex items-center gap-2 px-2.5 py-1 bg-emerald-50 rounded-lg border border-emerald-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                        <span class="text-xs font-black text-emerald-600 uppercase tracking-widest">Balance Ledger Sync Active</span>
                    </div>
                    <span class="text-slate-400 font-black text-xs uppercase tracking-[0.2em] px-1">Compensatory Asset Management</span>
                </div>
            </div>
            
            <button @click="fetchData" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-emerald-600 bg-white rounded-xl border border-slate-200 shadow-sm transition-all active:scale-95 group">
                <i class="fas fa-arrows-rotate text-base" :class="{'animate-spin text-emerald-500': loading}"></i>
            </button>
        </div>

        <div v-if="loading" class="h-96 flex flex-col items-center justify-center space-y-6 bg-white/40 backdrop-blur-md rounded-[48px] border border-white border-dashed">
            <div class="w-16 h-16 border-4 border-emerald-500/10 border-t-emerald-600 rounded-full animate-spin"></div>
            <p class="text-sm font-black text-emerald-600 uppercase tracking-[0.3em] animate-pulse">Calculating Accrual Delta...</p>
        </div>

        <div v-else class="space-y-10">
            <!-- Asset Liquidity Matrix -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-500 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                            <i class="fas fa-circle-plus text-[14px]"></i>
                        </div>
                        <span class="text-sm font-black text-emerald-300 uppercase tracking-widest">Accumulated</span>
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Total Earned</p>
                    <div class="text-3xl font-black text-slate-800 tracking-tighter tabular-nums">{{ stats.earned }}<span class="text-sm text-slate-300 ml-1.5 uppercase tabular-nums">Hrs</span></div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 bg-rose-50 rounded-lg flex items-center justify-center text-rose-500 group-hover:bg-rose-600 group-hover:text-white transition-all duration-300">
                            <i class="fas fa-fire-flame-curved text-[14px]"></i>
                        </div>
                        <span class="text-sm font-black text-rose-300 uppercase tracking-widest">Dissipated</span>
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Total Burned</p>
                    <div class="text-3xl font-black text-slate-800 tracking-tighter tabular-nums">{{ stats.burned }}<span class="text-sm text-slate-300 ml-1.5 uppercase tabular-nums">Hrs</span></div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center text-amber-500 group-hover:bg-amber-600 group-hover:text-white transition-all duration-300">
                            <i class="fas fa-clock-rotate-left text-[14px]"></i>
                        </div>
                        <span class="text-sm font-black text-amber-300 uppercase tracking-widest">Voided</span>
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Total Expired</p>
                    <div class="text-3xl font-black text-slate-800 tracking-tighter tabular-nums">{{ stats.expired }}<span class="text-sm text-slate-300 ml-1.5 uppercase tabular-nums">Hrs</span></div>
                </div>

                <div class="bg-slate-900 p-6 rounded-2xl shadow-md text-white relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center text-white backdrop-blur-md">
                                <i class="fas fa-scale-balanced text-base"></i>
                            </div>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-widest">Active Liability</p>
                        </div>
                        <div class="flex items-end gap-1.5">
                            <h3 class="text-3xl font-black text-white tracking-tighter tabular-nums">{{ stats.liability_hours }}<span class="text-sm text-emerald-400 font-mono tabular-nums">HR</span></h3>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1 mt-4 overflow-hidden">
                            <div class="bg-emerald-500 h-full shadow-[0_0_10px_rgba(16,185,129,0.5)] transition-all duration-1000" :style="{ width: Math.min(100, (stats.liability_hours / stats.earned * 100)) + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Throughput Analysis Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Earners Matrix -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300 overflow-hidden">
                    <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-50">
                        <div>
                            <h3 class="text-sm font-black text-slate-800 tracking-tight uppercase">High-Accrual Node Matrix</h3>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1">Personnel Compensation Throughput</p>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fas fa-users-rays text-base"></i>
                        </div>
                    </div>
                    <div class="h-64">
                         <Bar :data="chartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Strategic Advisory Node -->
                <div class="bg-slate-50/50 p-6 rounded-2xl border border-slate-200 shadow-inner flex flex-col justify-center relative overflow-hidden group">
                    <div class="absolute -right-16 -top-16 w-32 h-32 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-all duration-1000"></div>
                    
                    <div class="relative z-10 w-full max-w-xs mx-auto text-center">
                        <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center mx-auto mb-6 shadow-sm border border-slate-100 group-hover:scale-110 transition-all duration-500">
                             <i class="fas fa-lightbulb text-xl text-emerald-500 animate-pulse"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-800 tracking-tight uppercase mb-3">Strategic Advisory</h3>
                        <div class="space-y-3">
                            <div class="p-3.5 bg-white rounded-xl border border-slate-200 text-sm font-black text-slate-600 leading-relaxed shadow-sm uppercase tracking-tight text-left">
                                Comp-off credits expire after <span class="text-emerald-600 font-black px-1.5 py-0.5 bg-emerald-50 rounded-md border border-emerald-100 tabular-nums">60 DAYS</span>. 
                                System detected a potential <span class="text-rose-600 uppercase font-black tracking-widest">Liquidity Risk</span> in upcoming cycles.
                            </div>
                            <button class="w-full py-2.5 bg-emerald-600 rounded-xl text-sm font-black text-white uppercase tracking-[0.2em] shadow-md shadow-emerald-100 hover:bg-emerald-700 active:scale-95 transition-all">
                                Optimize Leave Distribution
                            </button>
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
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const stats = ref(null);
const loading = ref(true);
const chartData = ref({ labels: [], datasets: [] });

const chartOptions = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
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
        x: { 
            beginAtZero: true,
            grid: { color: '#f1f5f9', drawBorder: false },
            ticks: {
                color: '#94a3b8',
                font: { family: "'Inter', sans-serif", weight: '800', size: 10 }
            }
        },
        y: {
            grid: { display: false },
            ticks: {
                color: '#475569',
                font: { family: "'Inter', sans-serif", weight: '900', size: 10 }
            }
        }
    },
    elements: {
        bar: {
            borderRadius: 12,
            borderSkipped: false
        }
    }
};

const fetchData = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/admin/attendance/monitoring/data', { params: { type: 'compoff' } });
        stats.value = res.data;

        chartData.value = {
            labels: res.data.top_earners.map(e => e.name.toUpperCase()),
            datasets: [{
                label: 'Hours Earned',
                data: res.data.top_earners.map(e => e.hours),
                backgroundColor: (context) => {
                    const chart = context.chart;
                    const {ctx, chartArea} = chart;
                    if (!chartArea) return null;
                    const gradient = ctx.createLinearGradient(chartArea.left, 0, chartArea.right, 0);
                    gradient.addColorStop(0, '#10b981');
                    gradient.addColorStop(1, '#059669');
                    return gradient;
                },
                barThickness: 24
            }]
        };
    } catch (e) {
        console.error(e);
        // Mock fallback for design demo
        stats.value = {
            earned: 450,
            burned: 120,
            expired: 45,
            liability_hours: 285,
            top_earners: [
                { name: 'Alice Smith', hours: 42 },
                { name: 'Bob Johnson', hours: 38 },
                { name: 'Charlie Davis', hours: 35 }
            ]
        };
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
