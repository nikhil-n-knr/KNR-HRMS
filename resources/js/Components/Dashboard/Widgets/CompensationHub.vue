<template>
    <div class="bg-white/40 backdrop-blur-xl border border-white/40 rounded-3xl p-5 shadow-xl h-full flex flex-col group relative overflow-hidden">
        <div class="flex justify-between items-center mb-4 relative z-10">
            <h3 class="text-sm font-bold text-emerald-950 flex items-center gap-2">
                <BanknotesIcon class="w-4 h-4 text-emerald-600" />
                Compensation Hub
            </h3>
            <button class="p-1.5 hover:bg-emerald-100 rounded-lg transition" v-tooltip="'Download Latest'">
                <ArrowDownTrayIcon class="w-4 h-4 text-emerald-600" />
            </button>
        </div>

        <div v-if="loading" class="flex-grow flex flex-col justify-center animate-pulse">
            <div class="h-24 bg-emerald-100/20 rounded-2xl w-full mb-4"></div>
            <div class="h-8 bg-emerald-100/40 rounded-xl w-3/4 mx-auto"></div>
        </div>

        <div v-else class="flex-grow flex flex-col relative z-10">
            <!-- Sparkline Trend -->
            <div class="h-24 w-full">
                <v-chart class="chart" :option="chartOption" autoresize />
            </div>

            <div class="mt-4 flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Net Payable</p>
                    <p class="text-xl font-black text-emerald-950">$3,500.00</p>
                </div>
                <div class="flex flex-col items-end">
                    <span class="px-2 py-0.5 bg-emerald-600 text-white text-[9px] font-bold rounded-full">+4%</span>
                    <p class="text-[9px] text-gray-400 mt-1">vs Last Month</p>
                </div>
            </div>
            
            <button class="mt-4 w-full py-2 bg-emerald-950 text-white text-[10px] font-bold rounded-xl hover:bg-black transition shadow-lg">
                View Full Breakdown
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { BanknotesIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import { use } from 'echarts/core';
import { LineChart } from 'echarts/charts';
import { GridComponent } from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';
import VChart from 'vue-echarts';
import axios from 'axios';

use([LineChart, GridComponent, CanvasRenderer]);

const loading = ref(true);
const trendData = ref([3200, 3100, 3400, 3300, 3500, 3500]);

const chartOption = computed(() => ({
    grid: { left: 0, right: 0, top: 10, bottom: 0 },
    xAxis: { type: 'category', show: false },
    yAxis: { type: 'value', show: false, min: 'dataMin' },
    series: [{
        data: trendData.value,
        type: 'line',
        smooth: true,
        symbol: 'none',
        lineStyle: { width: 3, color: '#10b981' },
        areaStyle: {
            color: {
                type: 'linear',
                x: 0, y: 0, x2: 0, y2: 1,
                colorStops: [
                    { offset: 0, color: 'rgba(16, 185, 129, 0.3)' },
                    { offset: 1, color: 'rgba(16, 185, 129, 0)' }
                ]
            }
        }
    }]
}));

onMounted(async () => {
    try {
        const response = await axios.get('/api/employee/dashboard/widgets/payslips');
        trendData.value = response.data.trend;
    } catch (e) {
        console.error('Failed to load payslips widget:', e);
    } finally {
        loading.value = false;
    }
});
</script>

<style scoped>
.chart { height: 100%; }
</style>
