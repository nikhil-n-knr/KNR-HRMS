<template>
    <Head title="Strategic Decision Engine" />
    <MainLayout>
        <div class="px-4 sm:px-6 lg:px-8 py-8 space-y-8">
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-500">
                        Strategic Decision Engine
                    </h1>
                    <p class="mt-2 text-sm text-gray-500">
                        Organizational health checks, budget analysis, and performance correlations.
                    </p>
                </div>
                <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
                    <select v-model="filter.department_id" @change="refresh" class="rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Departments</option>
                        <!-- Todo: Pass Departments Prop -->
                    </select>
                    <button class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none ring-offset-2">
                        Export Board Pack
                    </button>
                </div>
            </div>

            <!-- Top Cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                 <div class="bg-white/70 backdrop-blur-xl overflow-hidden shadow rounded-lg px-4 py-5 sm:p-6 border border-white/50">
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Annual Payroll</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">₹ 4.2 Cr</dd>
                    <dd class="mt-2 text-xs text-green-600 flex items-center">
                         <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                         12% Growth YoY
                    </dd>
                 </div>
                 <div class="bg-white/70 backdrop-blur-xl overflow-hidden shadow rounded-lg px-4 py-5 sm:p-6 border border-white/50">
                    <dt class="text-sm font-medium text-gray-500 truncate">Avg. Hike (Last Cycle)</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">11.4%</dd>
                    <dd class="mt-2 text-xs text-red-600 flex items-center">
                         <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>
                         Target was 10%
                    </dd>
                 </div>
                 <div class="bg-white/70 backdrop-blur-xl overflow-hidden shadow rounded-lg px-4 py-5 sm:p-6 border border-white/50">
                    <dt class="text-sm font-medium text-gray-500 truncate">Rating Inflation Risk</dt>
                    <dd class="mt-1 text-3xl font-semibold text-orange-600">High</dd>
                    <dd class="mt-2 text-xs text-gray-600">
                         35% of staff rated 5 Stars
                    </dd>
                 </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Bell Curve -->
                <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Performance Bell Curve</h3>
                    <div class="h-80">
                         <BarChart 
                            :labels="['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars']"
                            :datasets="[{ 
                                label: 'Employee Count', 
                                data: bellCurveData, 
                                backgroundColor: ['#ef4444', '#f97316', '#3b82f6', '#8b5cf6', '#10b981'],
                                borderRadius: 4
                            }]"
                         />
                    </div>
                     <p class="mt-2 text-xs text-gray-500 text-center">Ideally, 3 Stars should be 60%. Currently {{ bellCurveData[2] }}%.</p>
                </div>

                <!-- Pay vs Performance -->
                <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Pay vs Performance Matrix</h3>
                    <div class="h-80">
                        <ScatterChart 
                            :points="payVsPerf"
                        />
                    </div>
                     <div class="mt-4 flex gap-4 justify-center text-xs">
                         <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span> Normal</div>
                         <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span> Underpaid High Performer (Risk)</div>
                         <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-orange-500 mr-2"></span> Overpaid Low Performer</div>
                    </div>
                </div>
            </div>
            
            <!-- Row 2: Budget -->
             <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
                  <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Department Budget Consumption (Heatmap)</h3>
                  <div class="h-72">
                        <BarChart 
                            :labels="budgetHeatmap.map(i => i.x)"
                            :datasets="[{ 
                                label: 'Total Annual CTC (₹)', 
                                data: budgetHeatmap.map(i => i.y), 
                                backgroundColor: '#6366f1',
                                borderRadius: 4
                            }]"
                         />
                  </div>
             </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import BarChart from '@/Components/Charts/BarChart.vue';
import ScatterChart from '@/Components/Charts/ScatterChart.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    bellCurve: Object, // { '1': 10, '2': 20... }
    payVsPerf: Array,  // [{x:4, y:12, name:'John'}]
    budgetHeatmap: Array, // [{x:'Eng', y:500000}]
    filters: Object
});

const filter = ref({
    department_id: props.filters?.department_id || ''
});

const refresh = () => {
    router.get(route('analytics.command-center'), filter.value, { preserveState: true });
};

const bellCurveData = computed(() => {
    // Convert object {1:10} to array [10, 20...] ordered by key 1-5
    return [
        props.bellCurve['1'] || 0,
        props.bellCurve['2'] || 0,
        props.bellCurve['3'] || 0,
        props.bellCurve['4'] || 0,
        props.bellCurve['5'] || 0,
    ];
});
</script>
