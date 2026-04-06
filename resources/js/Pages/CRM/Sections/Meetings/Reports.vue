<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
    employees: { type: Array, default: () => [] }
});

const reportData = ref({
    noShows: 0,
    byProvider: [],
    trends: []
});

const filters = ref({
    employee_id: null,
    date_range: '30days'
});

const fetchReports = async () => {
    const response = await axios.get('/api/meetings/analytics', { params: filters.value });
    reportData.value = response.data;
};

const providerSeries = computed(() => reportData.value.byProvider.map(p => p.count));
const providerLabels = computed(() => reportData.value.byProvider.map(p => p.provider.toUpperCase()));

onMounted(fetchReports);
</script>

<template>
    <div class="space-y-12 animate-in fade-in slide-in-from-bottom-8 duration-700">
        <header class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-black text-gray-900 mb-2">Efficiency Reports</h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Real-time attendance & provider metrics</p>
            </div>
            <div class="flex gap-4">
                <select v-model="filters.employee_id" @change="fetchReports" class="bg-white border border-gray-100 rounded-2xl px-6 py-4 text-[10px] font-black uppercase tracking-widest shadow-sm">
                    <option :value="null">All Employees</option>
                    <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.first_name }} {{ e.last_name }}</option>
                </select>
                <select v-model="filters.date_range" @change="fetchReports" class="bg-white border border-gray-100 rounded-2xl px-6 py-4 text-[10px] font-black uppercase tracking-widest shadow-sm">
                    <option value="7days">Last 7 Days</option>
                    <option value="30days">Last 30 Days</option>
                    <option value="90days">Quarterly</option>
                </select>
            </div>
        </header>

        <div class="grid grid-cols-12 gap-10">
            <!-- No Show Analytics -->
            <div class="col-span-12 lg:col-span-4 bg-white rounded-[40px] p-10 border border-gray-50 shadow-sm flex flex-col justify-between">
                <div>
                    <i class="fas fa-user-slash text-rose-500 text-2xl mb-6"></i>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Average No-Show Rate</p>
                    <p class="text-6xl font-black text-gray-900">{{ reportData.noShows }}%</p>
                </div>
                <div class="pt-10 border-t border-gray-50 flex items-center justify-between text-[10px] font-black uppercase tracking-widest text-indigo-600">
                    <span>Performance Target: 2%</span>
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>

            <!-- Provider Pie Chart -->
            <div class="col-span-12 lg:col-span-8 bg-white rounded-[40px] p-10 border border-gray-50 shadow-sm">
                <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest mb-10">Meeting Infrastructure Mix</h3>
                <div class="h-64">
                    <VueApexCharts 
                        type="donut" 
                        height="100%" 
                        :options="{
                            labels: providerLabels,
                            legend: { position: 'bottom', labels: { useSeriesColors: true } },
                            plotOptions: { pie: { donut: { size: '75%' } } }
                        }" 
                        :series="providerSeries" 
                    />
                </div>
            </div>
        </div>
    </div>
</template>
