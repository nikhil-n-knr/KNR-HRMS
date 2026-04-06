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
    <div v-if="loading" class="p-8 text-center text-gray-500">Loading Dashboard...</div>
    <div v-else-if="!metrics" class="p-8 text-center text-red-500">Failed to load dashboard metrics.</div>
    <div v-else class="space-y-6">
        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <StatCard title="Total Disbursed" :value="formatCurrency(metrics.total_disbursed)" type="info" icon="CurrencyRupeeIcon" />
            <StatCard title="Recovered (YTD)" :value="formatCurrency(metrics.total_recovered)" type="success" icon="TrendingUpIcon" />
            <StatCard title="Outstanding Portfolio" :value="formatCurrency(metrics.outstanding_portfolio)" type="warning" icon="ScaleIcon" />
            <StatCard title="Loans At Risk" :value="String(metrics.at_risk_count)" type="danger" icon="ExclamationCircleIcon" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Disbursal Trend -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Disbursal Trends</h3>
                <div class="h-64 flex items-center justify-center text-gray-400 bg-gray-50 rounded">
                    <!-- Placeholder for Chart.js or equivalent -->
                    [Bar Chart Visualization]
                    <!-- In real app, mount Chart.js here with chartData.value -->
                </div>
            </div>
            
             <!-- Portfolio Health -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Portfolio Risk Distribution</h3>
                 <div class="h-64 flex items-center justify-center text-gray-400 bg-gray-50 rounded">
                    <!-- Placeholder for Pie Chart -->
                    [Pie Chart]
                </div>
            </div>
        </div>
    </div>
</template>
