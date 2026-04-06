<template>
    <Head title="Recruitment Intelligence" />
    <TalentLayout>
        <div class="py-10">
            <div class="mb-10">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Recruitment Intelligence</h1>
                <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mt-2">Deep analytics and pipeline efficiency metrics</p>
            </div>

            <!-- KPIs -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-10">
                <div class="bg-white/60 backdrop-blur-xl p-6 rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-xl transition-all duration-500">
                    <div class="absolute right-0 top-0 h-full w-1.5 bg-gradient-to-b from-indigo-400 to-indigo-600"></div>
                    <p class="text-sm font-black text-gray-400 uppercase tracking-widest leading-none">Total Applications</p>
                    <p class="text-3xl font-black text-slate-900 mt-4 group-hover:scale-105 transition-transform origin-left">{{ metrics.total_applications }}</p>
                    <div class="mt-4 flex items-center text-sm font-black text-indigo-600 bg-indigo-50 w-fit px-2 py-0.5 rounded-full uppercase tracking-widest">Database Size</div>
                </div>
                <div class="bg-white/60 backdrop-blur-xl p-6 rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-xl transition-all duration-500">
                    <div class="absolute right-0 top-0 h-full w-1.5 bg-gradient-to-b from-emerald-400 to-emerald-600"></div>
                    <p class="text-sm font-black text-gray-400 uppercase tracking-widest leading-none">Conversions</p>
                    <p class="text-3xl font-black text-slate-900 mt-4 group-hover:scale-105 transition-transform origin-left">{{ metrics.total_hired }}</p>
                    <div class="mt-4 flex items-center text-sm font-black text-emerald-600 bg-emerald-50 w-fit px-2 py-0.5 rounded-full uppercase tracking-widest">Successful Hires</div>
                </div>
                <div class="bg-white/60 backdrop-blur-xl p-6 rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-xl transition-all duration-500">
                     <div class="absolute right-0 top-0 h-full w-1.5 bg-gradient-to-b from-blue-400 to-blue-600"></div>
                     <p class="text-sm font-black text-gray-400 uppercase tracking-widest leading-none">Velocity</p>
                     <p class="text-3xl font-black text-slate-900 mt-4 group-hover:scale-105 transition-transform origin-left">{{ metrics.avg_time_to_hire }} <span class="text-xs text-gray-400 font-bold uppercase ml-1">Days</span></p>
                     <div class="mt-4 flex items-center text-sm font-black text-blue-600 bg-blue-50 w-fit px-2 py-0.5 rounded-full uppercase tracking-widest">Avg Time to Hire</div>
                 </div>
                 <div class="bg-white/60 backdrop-blur-xl p-6 rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-xl transition-all duration-500">
                     <div class="absolute right-0 top-0 h-full w-1.5 bg-gradient-to-b from-amber-400 to-amber-600"></div>
                     <p class="text-sm font-black text-gray-400 uppercase tracking-widest leading-none">Efficiency</p>
                     <p class="text-3xl font-black text-slate-900 mt-4 group-hover:scale-105 transition-transform origin-left">{{ metrics.acceptance_rate }}%</p>
                     <div class="mt-4 flex items-center text-sm font-black text-amber-600 bg-amber-50 w-fit px-2 py-0.5 rounded-full uppercase tracking-widest">Offer Acceptance</div>
                 </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Trend Chart -->
                <div class="bg-white/80 backdrop-blur-xl p-8 rounded-[2rem] border border-gray-100 shadow-2xl">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-xl font-black text-slate-900 tracking-tight">Application Momentum</h3>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-widest mt-1">Daily throughput monitor (30 days)</p>
                        </div>
                        <div class="h-10 w-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                    </div>
                    <div class="h-80">
                         <Line :data="trendData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Funnel Chart (Pie/Doughnut) -->
                <div class="bg-white/80 backdrop-blur-xl p-8 rounded-[2rem] border border-gray-100 shadow-2xl">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-xl font-black text-slate-900 tracking-tight">Pipeline Saturation</h3>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-widest mt-1">Stage distribution across all assets</p>
                        </div>
                        <div class="h-10 w-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                        </div>
                    </div>
                    <div class="h-80 flex items-center justify-center">
                        <Doughnut :data="pipelineData" :options="doughnutOptions" />
                    </div>
                </div>
            </div>
        </div>
    </TalentLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import TalentLayout from '@/Layouts/TalentLayout.vue';
import { computed } from 'vue';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  ArcElement
} from 'chart.js';
import { Line, Doughnut } from 'vue-chartjs';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  ArcElement
);

const props = defineProps({
    metrics: Object,
    charts: Object
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false }
    },
    scales: {
        y: { 
            beginAtZero: true,
            grid: { display: false },
            ticks: { font: { size: 10, weight: 'bold' }, color: '#94a3b8' }
        },
        x: {
            grid: { display: false },
            ticks: { font: { size: 10, weight: 'bold' }, color: '#94a3b8' }
        }
    }
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '75%',
    plugins: {
        legend: { 
            position: 'right',
            labels: {
                boxWidth: 12,
                font: { size: 11, weight: '900', family: 'Outfit' },
                padding: 20
            }
        }
    }
};

const trendData = computed(() => {
    return {
        labels: props.charts.trend.map(d => d.date),
        datasets: [{
            label: 'New Applications',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            borderColor: '#6366f1',
            borderWidth: 4,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#6366f1',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            fill: true,
            data: props.charts.trend.map(d => d.count),
            tension: 0.4
        }]
    };
});

const pipelineData = computed(() => {
    return {
        labels: props.charts.pipeline.map(d => d.status.toUpperCase()),
        datasets: [{
            backgroundColor: [
                '#94a3b8', // Applied
                '#818cf8', // Screening
                '#6366f1', // Interview
                '#10b981', // Offer
                '#059669', // Hired
                '#f43f5e'  // Rejected
            ],
            borderWidth: 0,
            hoverOffset: 20,
            data: props.charts.pipeline.map(d => d.count)
        }]
    };
});
</script>
