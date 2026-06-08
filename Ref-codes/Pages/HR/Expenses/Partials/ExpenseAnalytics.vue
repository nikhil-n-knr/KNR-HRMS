<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const metrics = ref(null);
const loading = ref(true);

const fetchStats = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('hr.expenses.api.stats'));
        metrics.value = response.data.metrics;
    } catch (error) {
        console.error('Error fetching stats', error);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchStats);

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val || 0);
</script>

<template>
    <div v-if="loading" class="p-8 text-center text-gray-500">Loading Analytics...</div>
    <div v-else-if="!metrics" class="p-8 text-center text-red-500">Failed to load analytics data.</div>
    <div v-else class="space-y-6">
        <!-- Financial Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
             <div class="bg-white p-4 rounded-lg border border-gray-100 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-blue-500"></div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Claims (This Month)</p>
                <div class="mt-2 text-xl font-bold text-gray-900">{{ formatCurrency(metrics.total_claims_this_month) }}</div>
                <div class="mt-1 text-xs text-blue-600 font-medium">
                     {{ metrics.total_claims_this_month > metrics.total_claims_last_month ? '▲' : '▼' }} 
                     vs {{ formatCurrency(metrics.total_claims_last_month) }} last month
                </div>
             </div>

             <div class="bg-white p-4 rounded-lg border border-gray-100 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-yellow-400"></div>
                 <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Outstanding Liability</p>
                <div class="mt-2 text-xl font-bold text-gray-900">{{ formatCurrency(metrics.outstanding_liability) }}</div>
                <p class="mt-1 text-xs text-gray-400">Approved but Unpaid</p>
             </div>

             <div class="bg-white p-4 rounded-lg border border-gray-100 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-red-400"></div>
                 <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Rejected (YTD)</p>
                <div class="mt-2 text-xl font-bold text-gray-900">{{ formatCurrency(metrics.total_rejected_amount) }}</div>
                <p class="mt-1 text-xs text-red-500 font-medium">Compliance Savings</p>
             </div>

             <div class="bg-white p-4 rounded-lg border border-gray-100 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-green-500"></div>
                 <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Avg Claim Time</p>
                <div class="mt-2 text-xl font-bold text-gray-900">{{ Math.round(metrics.avg_claim_days) }} Days</div>
                <p class="mt-1 text-xs text-green-600">Submission to Payment</p>
             </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Pie Chart Mock -->
            <div class="bg-white p-4 rounded-lg border border-gray-100 shadow-sm col-span-2">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Spend by Category</h3>
                <!-- Simple Bar Chart Visualization for robustness -->
                <div class="space-y-4">
                    <div v-for="item in metrics.category_split" :key="item.name">
                         <div class="flex justify-between text-sm mb-1">
                             <span class="font-medium text-gray-700">{{ item.name }}</span>
                             <span class="font-bold text-gray-900">{{ formatCurrency(item.value) }}</span>
                         </div>
                         <div class="w-full bg-gray-100 rounded-full h-2.5">
                             <!-- Mock percentage calculation logic needed or just random color assignment -->
                             <div class="bg-indigo-600 h-2.5 rounded-full" :style="{ width: '50%' }"></div>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Aging Analysis -->
            <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm">
                 <h3 class="text-lg font-bold text-gray-800 mb-4">Overdue Payments (>30 Days)</h3>
                 <div class="space-y-3">
                     <!-- This data needs to come from the 'overdue' prop or api -->
                     <div class="p-3 bg-red-50 rounded border border-red-100 lg:text-xs">
                         Select overdue payments to see details.
                         (API logic included in controller, can be wired up)
                     </div>
                 </div>
            </div>
        </div>
    </div>
</template>
