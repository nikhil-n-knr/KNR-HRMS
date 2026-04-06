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
        const res = await axios.get(route('projects.devops.stats.prs', props.projectId));
        stats.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
});

// Histogram Data
const histogramData = computed(() => {
    if (!stats.value) return { labels: [], datasets: [] };
    const h = stats.value.lifespan;
    return {
        labels: ['0-4h', '4-24h', '1-2 Days', '3+ Days'],
        datasets: [{
            label: 'PRs Merged',
            data: [h['0-4h'], h['4-24h'], h['1-2d'], h['3d+']],
            backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444']
        }]
    };
});

// Load Balancer Data (Horizontal Bar)
const loadBalancerData = computed(() => {
    if (!stats.value) return { labels: [], datasets: [] };
    const lb = stats.value.load_balancer; // Array of {name, pending, merged}
    return {
        labels: lb.map(p => p.name),
        datasets: [
            {
                label: 'Pending Review',
                data: lb.map(p => p.pending),
                backgroundColor: '#f59e0b'
            },
            {
                label: 'Merged/Approved',
                data: lb.map(p => p.merged),
                backgroundColor: '#10b981'
            }
        ]
    };
});
</script>

<template>
    <div v-if="loading" class="animate-pulse space-y-4">
        <div class="h-40 bg-gray-200 rounded"></div>
    </div>
    <div v-else-if="stats" class="space-y-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Lifespan Histogram -->
            <div class="bg-white p-4 rounded-lg shadow border border-gray-100">
                <h3 class="text-sm font-medium text-gray-500 mb-4">PR Lifespan (Time to Merge)</h3>
                <div class="h-64">
                    <BaseChart type="bar" :data="histogramData" />
                </div>
                <p class="text-xs text-center mt-2 text-gray-500">Goal: Merge within 24 hours</p>
            </div>

            <!-- Reviewer Load Balancer -->
            <div class="bg-white p-4 rounded-lg shadow border border-gray-100">
                <h3 class="text-sm font-medium text-gray-500 mb-4">Reviewer Load Balancer</h3>
                <div class="h-64">
                    <BaseChart type="bar" :data="loadBalancerData" :options="{indexAxis: 'y', responsive: true, maintainAspectRatio: false}" />
                </div>
            </div>
        </div>

        <!-- Stuck List -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">The "Stuck" List</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">PRs needing immediate attention (Oldest First)</p>
            </div>
            <div class="border-t border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PR Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Repository</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                         <tr v-for="pr in stats.stuck_list" :key="pr.id" :class="pr.is_stale ? 'bg-red-50' : ''">
                             <td class="px-6 py-4">
                                 <div class="text-sm font-medium text-gray-900">{{ pr.title }}</div>
                                 <div class="text-sm text-gray-500">by {{ pr.author }}</div>
                             </td>
                             <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ pr.repo }}</td>
                             <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ Math.floor(pr.age_days) }} days</td>
                             <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                 <a v-if="pr.url" :href="pr.url" target="_blank" class="text-indigo-600 hover:text-indigo-900">View</a>
                             </td>
                         </tr>
                         <tr v-if="stats.stuck_list.length === 0">
                             <td colspan="4" class="px-6 py-4 text-center text-gray-500">Good job! No user-facing PR bottlenecks.</td>
                         </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>
