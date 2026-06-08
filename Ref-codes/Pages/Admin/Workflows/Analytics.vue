<script setup>
import { ref, onMounted, watch } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    // 
});

const range = ref('30_days');
const loading = ref(false);
const data = ref({
    status_distribution: {},
    avg_approval_time: 0,
    bottlenecks: [],
    activity: []
});

const fetchStats = async () => {
    loading.value = true;
    try {
        const response = await window.axios.get(route('admin.workflows.analytics.data'), {
            params: { range: range.value }
        });
        data.value = response.data;
    } catch (e) {
        console.error("Failed to fetch analytics", e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchStats();
});

watch(range, () => {
    fetchStats();
});

const formatDuration = (hours) => {
    if (hours < 1) return '< 1 hr';
    if (hours < 24) return `${Math.round(hours)} hrs`;
    return `${Math.round(hours / 24)} days`;
};
</script>

<template>
    <Head title="Workflow Analytics" />

    <MainLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Workflow Analytics
                </h2>
                <div>
                    <select v-model="range" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm sm:text-sm">
                        <option value="7_days">Last 7 Days</option>
                        <option value="30_days">Last 30 Days</option>
                        <option value="90_days">Last 90 Days</option>
                        <option value="year">Last Year</option>
                    </select>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 text-sm font-medium uppercase">Pending Requests</div>
                        <div class="mt-2 text-3xl font-bold text-yellow-600">{{ data.status_distribution.pending || 0 }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 text-sm font-medium uppercase">Approved</div>
                        <div class="mt-2 text-3xl font-bold text-green-600">{{ data.status_distribution.approved || 0 }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 text-sm font-medium uppercase">Rejected</div>
                        <div class="mt-2 text-3xl font-bold text-red-600">{{ data.status_distribution.rejected || 0 }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 text-sm font-medium uppercase">Avg Approval Time</div>
                        <div class="mt-2 text-3xl font-bold text-indigo-600">{{ formatDuration(data.avg_approval_time) }}</div>
                    </div>
                </div>

                <!-- Bottlenecks -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Slowest Stages (Bottlenecks)</h3>
                    <div v-if="loading" class="text-center py-4 text-gray-500">Loading...</div>
                    <div v-else-if="data.bottlenecks.length === 0" class="text-center py-4 text-gray-500">No pending bottlenecks found.</div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Workflow</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stage</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pending Count</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Wait Time</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(item, index) in data.bottlenecks" :key="index">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.workflow }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.stage }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ item.pending_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-medium">{{ formatDuration(item.avg_wait_hours) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Activity Chart (Simple Bar Representation) -->
                 <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Request Activity (Daily)</h3>
                    <div class="h-64 flex items-end space-x-2 border-b border-l border-gray-200 p-4">
                         <div v-for="day in data.activity" :key="day.date" class="flex flex-col items-center flex-1">
                            <div 
                                class="w-full bg-indigo-500 hover:bg-indigo-600 rounded-t transition-all duration-300"
                                :style="{ height: `${Math.min(day.count * 10, 100)}%`, minHeight: '4px' }"
                                :title="`${day.date}: ${day.count} requests`"
                            ></div>
                            <div class="text-xs text-gray-400 mt-1 rotate-45 origin-left transform translate-y-2">{{ day.date.slice(5) }}</div>
                         </div>
                         <div v-if="data.activity.length === 0" class="w-full h-full flex items-center justify-center text-gray-400">
                             No activity in this range.
                         </div>
                    </div>
                </div>

            </div>
        </div>
    </MainLayout>
</template>
