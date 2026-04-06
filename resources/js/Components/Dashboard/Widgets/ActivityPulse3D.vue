<template>
    <div class="bg-white/40 backdrop-blur-xl border border-white/40 rounded-3xl p-6 shadow-2xl h-full flex flex-col relative overflow-hidden group">
        <!-- Ambient Background Glow -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-emerald-400/10 blur-3xl rounded-full group-hover:bg-emerald-400/20 transition-all duration-700"></div>
        
        <div class="mb-4 flex justify-between items-start relative z-10">
            <div>
                <h3 class="text-lg font-bold text-emerald-950">Activity Pulse</h3>
                <p class="text-xs text-emerald-700/60 uppercase tracking-widest font-semibold">3D Performance Matrix</p>
            </div>
            <div class="p-2 bg-white/60 rounded-xl shadow-inner cursor-help group/info relative">
                <InformationCircleIcon class="w-5 h-5 text-emerald-600" />
                <!-- Tooltip -->
                <div class="absolute right-0 top-12 w-48 p-2 bg-emerald-900 text-white text-[10px] rounded-lg opacity-0 invisible group-hover/info:opacity-100 group-hover/info:visible transition-all duration-300 z-20">
                    Real-time data mapping of your work patterns, engagement, and growth.
                </div>
            </div>
        </div>

        <div class="flex-grow min-h-[300px] relative">
            <v-chart class="chart w-full h-full" :option="chartOption" autoresize />
        </div>

        <div class="mt-4 grid grid-cols-5 gap-2 relative z-10">
            <div v-for="metric in metrics" :key="metric.name" class="text-center">
                <p class="text-[10px] text-gray-500 font-medium truncate mb-1">{{ metric.name }}</p>
                <div class="h-1 bg-gray-100 rounded-full overflow-hidden">
                    <div 
                        class="h-full bg-emerald-500 rounded-full transition-all duration-1000"
                        :style="{ width: `${metric.value}%` }"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { RadarChart } from 'echarts/charts';
import {
    TitleComponent,
    TooltipComponent,
    LegendComponent,
    VisualMapComponent
} from 'echarts/components';
import VChart from 'vue-echarts';
import { InformationCircleIcon } from '@heroicons/vue/24/solid';

use([
    CanvasRenderer,
    RadarChart,
    TitleComponent,
    TooltipComponent,
    LegendComponent,
    VisualMapComponent
]);

const metrics = ref([
    { name: 'Punctuality', value: 85 },
    { name: 'Velocity', value: 70 },
    { name: 'Training', value: 92 },
    { name: 'Recognition', value: 60 },
    { name: 'Engagement', value: 78 }
]);

const chartOption = computed(() => ({
    backgroundColor: 'transparent',
    tooltip: {
        trigger: 'item',
        backgroundColor: 'rgba(255, 255, 255, 0.9)',
        borderWidth: 0,
        textStyle: { color: '#064e3b' }
    },
    radar: {
        indicator: metrics.value.map(m => ({ name: m.name, max: 100 })),
        shape: 'polygon',
        splitNumber: 4,
        axisName: {
            color: '#64748b',
            fontSize: 10,
            fontWeight: '600'
        },
        splitLine: {
            lineStyle: {
                color: [
                    'rgba(52, 168, 83, 0.1)',
                    'rgba(52, 168, 83, 0.1)',
                    'rgba(52, 168, 83, 0.1)',
                    'rgba(52, 168, 83, 0.2)'
                ].reverse()
            }
        },
        splitArea: {
            areaStyle: {
                color: ['rgba(255, 255, 255, 0.2)', 'rgba(52, 168, 83, 0.02)']
            }
        },
        axisLine: {
            lineStyle: {
                color: 'rgba(52, 168, 83, 0.1)'
            }
        }
    },
    series: [
        {
            type: 'radar',
            data: [
                {
                    value: metrics.value.map(m => m.value),
                    name: 'Performance',
                    symbol: 'none',
                    itemStyle: {
                        color: '#10b981'
                    },
                    areaStyle: {
                        color: {
                            type: 'radial',
                            x: 0.5,
                            y: 0.5,
                            r: 0.5,
                            colorStops: [
                                { offset: 0, color: 'rgba(16, 185, 129, 0.6)' },
                                { offset: 1, color: 'rgba(5, 150, 105, 0.1)' }
                            ]
                        }
                    },
                    lineStyle: {
                        width: 2,
                        type: 'solid',
                        color: 'rgba(16, 185, 129, 0.8)'
                    }
                }
            ],
            animationDuration: 2000,
            animationEasing: 'elasticOut'
        }
    ]
}));

// In prod, this would fetch from /api/employee/dashboard/widgets/pulse
onMounted(async () => {
    try {
        const response = await fetch('/api/employee/dashboard/widgets/pulse');
        const data = await response.json();
        if (data.metrics) {
            metrics.value = data.metrics;
        }
    } catch (e) {
        console.error('Pulse fetch failed');
    }
});
</script>

<style scoped>
.chart {
    height: 100%;
}
</style>
