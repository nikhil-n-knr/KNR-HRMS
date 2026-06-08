<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import StatCard from '@/Components/StatCard.vue';

const metrics = ref(null);
const chartData = ref(null);
const loading = ref(true);

onMounted(async () => {
    try {
        const res = await axios.get(route('hr.loans.api.stats'));
        metrics.value = res.data.metrics;
        chartData.value = res.data.chart_data;
    } finally {
        loading.value = false;
    }
});

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val || 0);
</script>
<template>
    <div v-if="loading" class="p-6 text-center text-sm text-gray-500">
        Loading Dashboard...
    </div>

    <div v-else-if="!metrics" class="p-6 text-center text-sm text-red-500">
        Failed to load dashboard metrics.
    </div>

    <div v-else class="space-y-6">

        <!-- Metrics Cards (MATCHED STYLE) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

            <div class="relative min-h-[120px] rounded-xl border border-gray-100 bg-white px-4 py-4 shadow-sm">
                <p class="text-[13px] font-semibold uppercase tracking-wide text-gray-500">
                    Total Disbursed
                </p>
                <p class="mt-2 text-[22px] font-extrabold text-gray-900">
                    {{ formatCurrency(metrics.total_disbursed) }}
                </p>
                <span class="absolute right-0 top-0 h-full w-1 bg-blue-500 rounded-r-xl"></span>
            </div>

            <div class="relative min-h-[120px] rounded-xl border border-gray-100 bg-white px-4 py-4 shadow-sm">
                <p class="text-[13px] font-semibold uppercase tracking-wide text-gray-500">
                    Recovered (YTD)
                </p>
                <p class="mt-2 text-[22px] font-extrabold text-gray-900">
                    {{ formatCurrency(metrics.total_recovered) }}
                </p>
                <span class="absolute right-0 top-0 h-full w-1 bg-emerald-500 rounded-r-xl"></span>
            </div>

            <div class="relative min-h-[120px] rounded-xl border border-gray-100 bg-white px-4 py-4 shadow-sm">
                <p class="text-[13px] font-semibold uppercase tracking-wide text-gray-500">
                    Outstanding Portfolio
                </p>
                <p class="mt-2 text-[22px] font-extrabold text-gray-900">
                    {{ formatCurrency(metrics.outstanding_portfolio) }}
                </p>
                <span class="absolute right-0 top-0 h-full w-1 bg-yellow-500 rounded-r-xl"></span>
            </div>

            <div class="relative min-h-[120px] rounded-xl border border-gray-100 bg-white px-4 py-4 shadow-sm">
                <p class="text-[13px] font-semibold uppercase tracking-wide text-gray-500">
                    Loans At Risk
                </p>
                <p class="mt-2 text-[22px] font-extrabold text-gray-900">
                    {{ metrics.at_risk_count }}
                </p>
                <span class="absolute right-0 top-0 h-full w-1 bg-rose-500 rounded-r-xl"></span>
            </div>

        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            <!-- Disbursal Trends -->
            <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-black text-gray-900">Disbursal Trends</h3>
                </div>

                <div class="p-5">
                    <div class="h-56 flex items-center justify-center text-xs text-gray-400 bg-gray-50 rounded-lg">
                        [Bar Chart Visualization]
                    </div>
                </div>
            </div>

            <!-- Portfolio Risk -->
            <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-black text-gray-900">Portfolio Risk Distribution</h3>
                </div>

                <div class="p-5">
                    <div class="h-56 flex items-center justify-center text-xs text-gray-400 bg-gray-50 rounded-lg">
                        [Pie Chart]
                    </div>
                </div>
            </div>

        </div>

    </div>
</template>
