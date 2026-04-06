<template>
    <div class="bg-white/40 backdrop-blur-3xl border border-white/50 rounded-3xl p-6 shadow-xl h-full flex flex-col group relative overflow-hidden">
        <!-- Decoration -->
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-teal-500/5 rounded-full blur-2xl"></div>

        <div class="flex justify-between items-center mb-8 relative z-10">
            <div>
                <h3 class="text-xs font-black uppercase tracking-[0.2em] text-emerald-800/60 flex items-center gap-2">
                    <ChartBarIcon class="w-4 h-4" />
                    Strategic_Intelligence
                </h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ subtitle }}</p>
            </div>
            
            <div class="flex gap-2">
                <button v-for="t in ['7D', '30D', '90D']" :key="t" 
                    @click="activeTimeframe = t"
                    class="px-2.5 py-1 rounded-lg text-[9px] font-black transition-all border uppercase tracking-tighter"
                    :class="activeTimeframe === t ? 'bg-emerald-600 text-white border-emerald-500 shadow-md shadow-emerald-500/20' : 'bg-white/50 text-slate-400 border-white hover:bg-white'"
                >
                    {{ t }}
                </button>
            </div>
        </div>

        <!-- Main Chart Container -->
        <div class="flex-grow min-h-[220px] relative z-10">
            <v-chart class="chart w-full h-full" :option="chartOption" autoresize />
        </div>

        <!-- Metric Footer -->
        <div class="mt-8 grid grid-cols-3 gap-4 border-t border-white/40 pt-6 relative z-10">
            <div v-for="metric in metrics" :key="metric.label" class="space-y-1">
                <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">{{ metric.label }}</span>
                <div class="flex items-end gap-2">
                    <span class="text-lg font-black text-slate-900 leading-none tracking-tighter">{{ metric.value }}</span>
                    <span class="text-[9px] font-bold" :class="metric.trend > 0 ? 'text-emerald-600' : 'text-rose-500'">
                        {{ metric.trend > 0 ? '+' : '' }}{{ metric.trend }}%
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { LineChart, BarChart } from 'echarts/charts';
import { 
    GridComponent, 
    TooltipComponent, 
    LegendComponent, 
    TitleComponent,
    VisualMapComponent,
    MarkAreaComponent 
} from 'echarts/components';
import VChart from 'vue-echarts';
import { ChartBarIcon } from '@heroicons/vue/24/outline';

use([
    CanvasRenderer,
    LineChart,
    BarChart,
    GridComponent,
    TooltipComponent,
    LegendComponent,
    TitleComponent,
    VisualMapComponent,
    MarkAreaComponent
]);

const props = defineProps({
    title: { type: String, default: 'Workforce Velocity' },
    subtitle: { type: String, default: 'Quarterly Output Audit' },
    chartData: { type: Array, default: () => [] },
    metrics: { type: Array, default: () => [] },
    themeColor: { type: String, default: '#10b981' } // Emerald-500
});

const activeTimeframe = ref('30D');

const chartOption = computed(() => ({
    backgroundColor: 'transparent',
    grid: {
        top: 20,
        left: 0,
        right: 10,
        bottom: 0,
        containLabel: false
    },
    tooltip: {
        trigger: 'axis',
        backgroundColor: 'rgba(15, 23, 42, 0.9)',
        borderColor: 'rgba(255, 255, 255, 0.1)',
        textStyle: { color: '#fff', fontSize: 10, fontFamily: 'Inter' },
        padding: [8, 12],
        borderRadius: 12,
        formatter: (params) => {
            let res = `<div class="font-black mb-1 uppercase tracking-widest text-[8px] text-slate-400">${params[0].name}</div>`;
            params.forEach(p => {
                res += `<div class="flex items-center gap-2">
                    <div class="w-1.5 h-1.5 rounded-full" style="background:${p.color}"></div>
                    <span class="font-bold text-[10px] text-white tracking-tighter">${p.seriesName}: ${p.value}</span>
                </div>`;
            });
            return res;
        }
    },
    xAxis: {
        type: 'category',
        data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        show: false
    },
    yAxis: {
        type: 'value',
        show: false
    },
    series: [
        {
            name: 'Primary Metric',
            type: 'line',
            smooth: 0.4,
            symbol: 'none',
            lineStyle: {
                width: 3,
                color: props.themeColor,
                shadowColor: props.themeColor + '40',
                shadowBlur: 10,
                shadowOffsetY: 5
            },
            areaStyle: {
                color: {
                    type: 'linear',
                    x: 0, y: 0, x2: 0, y2: 1,
                    colorStops: [
                        { offset: 0, color: props.themeColor + '20' },
                        { offset: 1, color: props.themeColor + '00' }
                    ]
                }
            },
            data: props.chartData.length ? props.chartData : [45, 52, 48, 70, 65, 80, 78],
            animationDuration: 1500,
            animationDurationUpdate: 800
        }
    ]
}));
</script>

<style scoped>
.chart {
    height: 100%;
}
</style>
