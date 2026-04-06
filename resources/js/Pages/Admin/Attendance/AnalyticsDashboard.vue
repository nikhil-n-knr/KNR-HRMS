<script setup>
import { ref, onMounted } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useToastStore } from '@/stores/toast';
import axios from 'axios';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement
} from 'chart.js';
import { Bar, Doughnut } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement);

const toast = useToastStore();
const loading = ref(true);
const stats = ref({
    overview: {
        total_employees: 0,
        present_today: 0,
        attendance_rate: 0,
        late_today: 0
    },
    trend: { labels: [], present: [], late: [] },
    department_health: { labels: [], data: [] }
});

// Chart Data Structures
const trendChartData = ref({ labels: [], datasets: [] });
const deptChartData = ref({ labels: [], datasets: [] });

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom' }
    }
};

const fetchData = async () => {
    try {
        const res = await axios.get('/admin/attendance/analytics/data');
        stats.value = res.data;
        
        // Prepare Trend Chart
        trendChartData.value = {
            labels: stats.value.trend.labels,
            datasets: [
                {
                    label: 'Present',
                    backgroundColor: '#10b981', // Emerald 500
                    data: stats.value.trend.present
                },
                {
                    label: 'Late',
                    backgroundColor: '#f59e0b', // Amber 500
                    data: stats.value.trend.late
                }
            ]
        };

        // Prepare Dept Chart
        deptChartData.value = {
            labels: stats.value.department_health.labels,
            datasets: [
                {
                    label: 'Absenteeism Count',
                    backgroundColor: ['#ef4444', '#f97316', '#8b5cf6', '#3b82f6'],
                    data: stats.value.department_health.data
                }
            ]
        };
    } catch (e) {
        toast.error("Failed to load analytics");
        console.error(e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchData();
});
</script>

<template>
    <MainLayout>
        <div class="space-y-6 pb-12">
            <!-- Compact Header -->
            <div class="flex justify-between items-center bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div>
                    <h2 class="text-xs font-black text-slate-800 uppercase tracking-tight leading-none">Attendance Intelligence</h2>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1.5 px-0.5">Real-time workforce synchronization insights</p>
                </div>
                <div class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-2">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                    Live Data
                </div>
            </div>

            <!-- High-Density Intelligence Summary -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-emerald-500 transition-all duration-300">
                    <span class="text-sm font-black text-slate-400 uppercase tracking-widest leading-none">Total Force</span>
                    <h3 class="text-xl font-black text-slate-800 mt-2.5 tracking-tighter tabular-nums leading-none">{{ stats.overview.total_employees }}</h3>
                    <div class="absolute -right-2 -bottom-2 opacity-[0.03] group-hover:opacity-[0.08] group-hover:scale-110 transition-all">
                        <i class="fas fa-users text-4xl transform -rotate-12"></i>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-emerald-500 transition-all duration-300">
                    <span class="text-sm font-black text-slate-400 uppercase tracking-widest leading-none">Present Today</span>
                    <div class="flex items-baseline gap-2 mt-2.5">
                         <h3 class="text-xl font-black text-emerald-600 tracking-tighter tabular-nums leading-none">{{ stats.overview.present_today }}</h3>
                         <span class="text-sm text-emerald-300 font-black tracking-tight uppercase">({{ stats.overview.attendance_rate }}%)</span>
                    </div>
                    <div class="absolute -right-2 -bottom-2 opacity-[0.03] group-hover:opacity-[0.08] group-hover:scale-110 transition-all">
                        <i class="fas fa-user-check text-4xl transform -rotate-12 text-emerald-600"></i>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-emerald-500 transition-all duration-300">
                    <span class="text-sm font-black text-slate-400 uppercase tracking-widest leading-none">Late Arrivals</span>
                    <h3 class="text-xl font-black text-amber-500 mt-2.5 tracking-tighter tabular-nums leading-none">{{ stats.overview.late_today }}</h3>
                    <div class="absolute -right-2 -bottom-2 opacity-[0.03] group-hover:opacity-[0.08] group-hover:scale-110 transition-all">
                        <i class="fas fa-clock-rotate-left text-4xl transform -rotate-12 text-amber-500"></i>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-emerald-500 transition-all duration-300">
                    <span class="text-sm font-black text-slate-400 uppercase tracking-widest leading-none">Off-Duty Hub</span>
                    <h3 class="text-xl font-black text-slate-400 mt-2.5 tracking-tighter tabular-nums leading-none">{{ stats.overview.absent_today || 0 }}</h3>
                    <div class="absolute -right-2 -bottom-2 opacity-[0.03] group-hover:opacity-[0.08] group-hover:scale-110 transition-all">
                        <i class="fas fa-calendar-minus text-4xl transform -rotate-12"></i>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Weekly Deployment Pulse -->
                <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col hover:border-emerald-500 transition-all duration-300">
                    <div class="flex items-center justify-between mb-8 border-b border-slate-50 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center text-white shadow-sm">
                                <i class="fas fa-chart-line text-sm"></i>
                            </div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-tight">Weekly Deployment Pulse</h3>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.3)]"></div>
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none">Synchronized</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-500"></div>
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none">Delayed</span>
                            </div>
                        </div>
                    </div>
                    <div class="h-64 relative">
                        <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white/50 backdrop-blur-[2px] z-10">
                             <div class="w-8 h-8 border-2 border-slate-100 border-t-emerald-600 rounded-full animate-spin"></div>
                        </div>
                        <Bar v-else :data="trendChartData" :options="{...chartOptions, scales: {x: {ticks: {font: {size: 9, weight: '800'}}}, y: {ticks: {font: {size: 9, weight: '800'}}}}}" />
                    </div>
                </div>

                <!-- Node Health Matrix -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col hover:border-emerald-500 transition-all duration-300">
                    <div class="flex items-center gap-3 mb-8 border-b border-slate-50 pb-4">
                        <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center text-white shadow-sm">
                            <i class="fas fa-chart-pie text-sm"></i>
                        </div>
                        <h3 class="text-xs font-black text-slate-800 uppercase tracking-tight">Node Integrity Matrix</h3>
                    </div>
                    <div class="h-64 relative selection-none">
                        <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white/50 backdrop-blur-[2px] z-10">
                            <div class="w-8 h-8 border-2 border-slate-100 border-t-emerald-600 rounded-full animate-spin"></div>
                        </div>
                         <Doughnut v-else-if="deptChartData.datasets[0].data.length > 0" :data="deptChartData" :options="{...chartOptions, plugins: {legend: {labels: {font: {size: 8, weight: '800'}}}}}" />
                         <div v-else class="h-full flex items-center justify-center text-slate-400 text-sm font-black uppercase tracking-widest italic">No Intelligence Collected</div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>

</template>
