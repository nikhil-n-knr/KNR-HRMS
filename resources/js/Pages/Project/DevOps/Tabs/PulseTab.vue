<script setup>
import { onMounted, ref, computed } from 'vue';
import axios from 'axios';
import BaseChart from '@/Components/BaseChart.vue';
import { ArrowDownIcon, ArrowUpIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    projectId: [String, Number]
});

const loading = ref(true);
const stats = ref(null);

onMounted(async () => {
    try {
        const res = await axios.get(route('projects.devops.pulse', props.projectId));
        stats.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
});

// Velocity Sparkline Data
const velocityData = computed(() => {
    if (!stats.value) return { labels: [], datasets: [] };
    const spark = stats.value.velocity.sparkline;
    return {
        labels: spark.map(d => d.date),
        datasets: [{
            label: 'Commits',
            data: spark.map(d => d.count),
            borderColor: '#6366f1',
            tension: 0.4,
            fill: false,
            pointRadius: 0
        }]
    };
});

// Throughput Bar Data
const throughputData = computed(() => {
    if (!stats.value) return { labels: [], datasets: [] };
    return {
        labels: ['Open', 'Merged'],
        datasets: [{
            label: 'PRs',
            data: [stats.value.throughput.opened, stats.value.throughput.merged],
            backgroundColor: ['#f59e0b', '#10b981']
        }]
    };
});

// Churn Doughnut
const churnData = computed(() => {
    if (!stats.value) return { labels: [], datasets: [] };
    return {
        labels: ['New Features', 'Churn/Refactor'],
        datasets: [{
            data: [stats.value.churn.new_code, stats.value.churn.rate],
            backgroundColor: ['#10b981', '#ef4444']
        }]
    };
});

// Heatmap Grid Logic
const heatmapGrid = computed(() => {
    if (!stats.value) return [];
    // Initialize 7x24 grid
    const grid = Array(7).fill().map(() => Array(24).fill(0));
    
    stats.value.heatmap.forEach(item => {
        // item.day (1-7), item.hour (0-23)
        // Convert day to 0-6 index (MySQL 1=Sun -> 0)
        const dayIdx = item.day - 1; 
        if(dayIdx >= 0 && dayIdx < 7) {
            grid[dayIdx][item.hour] = item.count;
        }
    });
    return grid;
});

const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const hours = Array.from({length: 24}, (_, i) => i);

const getHeatColor = (count) => {
    if (count === 0) return 'bg-gray-100';
    if (count < 2) return 'bg-indigo-100';
    if (count < 5) return 'bg-indigo-300';
    if (count < 10) return 'bg-indigo-500';
    return 'bg-indigo-700';
};
</script>

<template>
    <div v-if="loading" class="animate-pulse space-y-4">
        <div class="h-40 bg-gray-200 rounded"></div>
        <div class="h-60 bg-gray-200 rounded"></div>
    </div>
    <div v-else-if="stats" class="space-y-6">
        
        <!-- DORA Metrics -->
        <div>
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                DORA Metrics <span class="ml-2 text-xs font-normal text-gray-500 bg-gray-100 px-2 py-1 rounded">Approximation</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg p-6 text-white shadow">
                    <p class="text-sm font-medium opacity-80 mb-1">Deployment Frequency</p>
                    <div class="flex items-end">
                        <span class="text-4xl font-extrabold">{{ stats.dora?.deployment_frequency || 0 }}</span>
                        <span class="ml-2 text-sm opacity-80 mb-1">/ week</span>
                    </div>
                    <p class="text-xs mt-3 opacity-70">Based on merged PRs</p>
                </div>
                
                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg p-6 text-white shadow">
                    <p class="text-sm font-medium opacity-80 mb-1">Lead Time for Changes</p>
                    <div class="flex items-end">
                        <span class="text-4xl font-extrabold">{{ stats.dora?.lead_time || 0 }}</span>
                        <span class="ml-2 text-sm opacity-80 mb-1">hours</span>
                    </div>
                    <p class="text-xs mt-3 opacity-70">Avg PR lifespan</p>
                </div>
                
                <div class="bg-gradient-to-br from-rose-500 to-red-600 rounded-lg p-6 text-white shadow">
                    <p class="text-sm font-medium opacity-80 mb-1">Change Failure Rate</p>
                    <div class="flex items-end">
                        <span class="text-4xl font-extrabold">{{ stats.dora?.change_failure_rate || 0 }}</span>
                        <span class="ml-1 text-2xl font-bold">%</span>
                    </div>
                    <p class="text-xs mt-3 opacity-70">Hotfixes & reverts</p>
                </div>
            </div>
        </div>

        <!-- Top Row Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Velocity -->
            <div class="bg-white p-4 rounded-lg shadow border border-gray-100">
                <h3 class="text-sm font-medium text-gray-500">Commits Velocity (This Week)</h3>
                <div class="mt-2 flex items-baseline">
                    <p class="text-2xl font-semibold text-gray-900">{{ stats.velocity.current }}</p>
                    <p :class="[stats.velocity.change >= 0 ? 'text-green-600' : 'text-red-600', 'ml-2 flex items-baseline text-sm font-semibold']">
                        <ArrowUpIcon v-if="stats.velocity.change >= 0" class="self-center flex-shrink-0 h-4 w-4" />
                        <ArrowDownIcon v-else class="self-center flex-shrink-0 h-4 w-4" />
                        <span class="sr-only">Changed by</span>
                        {{ Math.abs(stats.velocity.change) }}%
                    </p>
                </div>
                <!-- Sparkline -->
                <div class="h-16 mt-4">
                    <BaseChart type="line" :data="velocityData" :options="{plugins: {legend: {display: false}}, scales: {x: {display: false}, y: {display: false}}}" />
                </div>
            </div>

            <!-- Throughput -->
            <div class="bg-white p-4 rounded-lg shadow border border-gray-100">
                 <h3 class="text-sm font-medium text-gray-500">PR Throughput (30 Days)</h3>
                 <div class="h-24 mt-4">
                    <BaseChart type="bar" :data="throughputData" :options="{plugins: {legend: {display: false}}, maintainAspectRatio: false}" />
                 </div>
                 <p class="text-xs text-center mt-2 text-gray-500">Opened vs Merged</p>
            </div>

            <!-- Churn -->
            <div class="bg-white p-4 rounded-lg shadow border border-gray-100">
                <h3 class="text-sm font-medium text-gray-500">Code Churn Rate</h3>
                <div class="flex items-center mt-4">
                    <div class="h-20 w-20 flex-shrink-0">
                         <BaseChart type="doughnut" :data="churnData" :options="{plugins: {legend: {display: false}}}" />
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold">{{ stats.churn.rate }}%</p>
                        <p class="text-xs text-gray-500">of code is refactored/deleted</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Heatmap -->
        <div class="bg-white p-6 rounded-lg shadow border border-gray-100 mt-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Productivity Heatmap</h3>
            <div class="overflow-x-auto">
                <div class="min-w-max">
                    <div class="flex">
                        <div class="w-10"></div> <!-- Y-Axis Label Spacer -->
                        <div class="flex flex-1 mb-1">
                            <div v-for="h in hours" :key="h" class="flex-1 text-xs text-center text-gray-400">{{ h }}</div>
                        </div>
                    </div>
                    <div v-for="(dayRow, dIdx) in heatmapGrid" :key="dIdx" class="flex mb-1">
                        <div class="w-10 text-xs text-gray-500 flex items-center">{{ days[dIdx] }}</div>
                        <div class="flex flex-1 gap-1">
                            <div v-for="(count, hIdx) in dayRow" :key="hIdx" 
                                :class="['flex-1 h-8 rounded-sm transition-all hover:opacity-80', getHeatColor(count)]"
                                :title="`${days[dIdx]} ${hIdx}:00 - ${count} commits`"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex justify-end items-center text-xs text-gray-500 space-x-2">
                <span>Less</span>
                <span class="block w-3 h-3 bg-gray-100 border border-gray-200"></span>
                <span class="block w-3 h-3 bg-indigo-300 border border-indigo-400"></span>
                <span class="block w-3 h-3 bg-indigo-700 border border-indigo-800"></span>
                <span>More</span>
            </div>
        </div>

    </div>
</template>
