<script setup>
import { onMounted, ref, computed } from 'vue';
import axios from 'axios';
import BaseChart from '@/Components/BaseChart.vue';

const props = defineProps({
    projectId: [String, Number]
});

const loading = ref(true);
const stats = ref(null);

onMounted(async () => {
    try {
        const res = await axios.get(route('projects.devops.stats.reviews', props.projectId));
        stats.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
});

// Rubber Stamp Scatter Data
const rubberStampData = computed(() => {
    if (!stats.value) return { datasets: [] };
    const stamps = stats.value.rubber_stamps || [];
    return {
        datasets: [{
            label: 'Review Speed vs Size',
            data: stamps.map(s => ({ x: s.x, y: s.y, _raw: s })), // Store raw for Tooltip? 
            backgroundColor: stamps.map(s => s.flagged ? '#ef4444' : '#6366f1'),
            pointRadius: 6
        }]
    };
});

// Simple Leaderboard Data (Optional Chart or just Table)
// Let's use Table for Leaderboard as requested
</script>

<template>
    <div v-if="loading" class="animate-pulse space-y-4">
        <div class="h-40 bg-gray-200 rounded"></div>
    </div>
    <div v-else-if="stats" class="space-y-6">
        
        <!-- Rubber Stamp Detector -->
        <div class="bg-white p-4 rounded-lg shadow border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                 <div>
                    <h3 class="text-sm font-medium text-gray-500">The "Rubber Stamp" Detector</h3>
                    <p class="text-xs text-gray-400">Lines of Code (X) vs Minutes to Approve (Y). <span class="text-red-500 font-bold">Red</span> = Suspicious.</p>
                 </div>
            </div>
            <div class="h-80">
                <BaseChart type="scatter" :data="rubberStampData" 
                    :options="{
                        scales: {
                            x: { title: { display: true, text: 'Changes Size (Lines)' } },
                            y: { title: { display: true, text: 'Review Time (Minutes)' } }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => {
                                        const r = ctx.dataset.data[ctx.dataIndex]._raw;
                                        return `${r.reviewer}: ${r.pr_title} (${r.y} mins)`;
                                    }
                                }
                            }
                        }
                    }" 
                />
            </div>
        </div>

        <!-- Depth Leaderboard -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
             <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Review Depth Leaderboard</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Average comments per review.</p>
            </div>
            <div class="border-t border-gray-200">
                 <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reviewer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Comments</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Reviews</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(rev, idx) in stats.depth_leaderboard" :key="idx">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ rev.reviewer_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-indigo-600 font-bold">{{ Number(rev.avg_comments).toFixed(1) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ rev.total_reviews }}</td>
                        </tr>
                    </tbody>
                 </table>
            </div>
        </div>

    </div>
</template>
