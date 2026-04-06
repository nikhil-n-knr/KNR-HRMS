<template>
    <div class="h-full flex flex-col p-6 space-y-6 overflow-y-auto custom-scrollbar bg-slate-50">
        
        <!-- Header & Filters -->
        <div class="flex flex-col md:flex-row justify-between items-end gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex gap-4 w-full md:w-auto">
                 <div>
                    <label class="text-xs font-bold text-gray-500 uppercase">Date Range</label>
                    <div class="flex gap-2 mt-1">
                        <input v-model="filters.start_date" type="date" class="text-sm border-gray-200 rounded-lg focus:ring-indigo-500 bg-gray-50">
                        <input v-model="filters.end_date" type="date" class="text-sm border-gray-200 rounded-lg focus:ring-indigo-500 bg-gray-50">
                    </div>
                 </div>
                 <div>
                    <label class="text-xs font-bold text-gray-500 uppercase">Project</label>
                    <select v-model="filters.project_id" class="block w-40 mt-1 text-sm border-gray-200 rounded-lg focus:ring-indigo-500 bg-gray-50">
                        <option value="">All Projects</option>
                        <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.text }}</option>
                    </select>
                 </div>
            </div>

            <div class="flex gap-3">
                <button 
                    @click="fetchData"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all flex items-center gap-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
                <button 
                    @click="exportExcel"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all flex items-center gap-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Export Excel
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
             <div class="col-span-2 md:col-span-1 bg-white/90 backdrop-blur-xl p-4 rounded-xl border border-gray-200 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-transform">
                 <p class="text-xs font-bold text-gray-400 uppercase">Total Scope (Hours)</p>
                 <h3 class="text-2xl font-black text-indigo-900 mt-1">{{ stats.total_scope }}h</h3>
                 <p class="text-xs text-gray-500 mt-2 flex justify-between">
                    <span>Allocated: {{ stats.total_hours }}h</span>
                    <span :class="stats.remaining_hours < 0 ? 'text-red-500' : 'text-emerald-500'">{{ stats.remaining_hours }}h left</span>
                 </p>
                 <div class="absolute right-0 top-0 p-3 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                 </div>
             </div>

             <div class="bg-gradient-to-br from-amber-50 to-orange-50 p-4 rounded-xl border border-amber-100 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-transform">
                 <p class="text-xs font-bold text-amber-600 uppercase">Project Value</p>
                 <h3 class="text-xl md:text-2xl font-black text-amber-800 mt-1">{{ stats.total_points }} 🍪</h3>
                 <p class="hidden md:block text-xs text-amber-600/80 mt-2">"Brownie Points" Available</p>
                 <div class="absolute right-0 top-0 p-3 opacity-10 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" /></svg>
                 </div>
             </div>

             <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm relative overflow-hidden">
                 <p class="text-xs font-bold text-gray-400 uppercase">Holiday Override</p>
                 <h3 class="text-xl md:text-2xl font-black text-rose-600 mt-1">{{ stats.holiday_hours }}h</h3>
             </div>

             <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm relative overflow-hidden">
                 <p class="text-xs font-bold text-gray-400 uppercase">Resources</p>
                 <h3 class="text-xl md:text-2xl font-black text-emerald-700 mt-1">{{ stats.resource_count }}</h3>
             </div>
             
              <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm relative overflow-hidden">
                 <p class="text-xs font-bold text-gray-400 uppercase">Avg. Burn</p>
                 <h3 class="text-xl md:text-2xl font-black text-blue-600 mt-1">{{ stats.avg_daily }}h</h3>
             </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6" v-if="chartData.projects && chartData.points">
            <!-- Charts always stack on mobile -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm min-h-[300px]">
                 <h4 class="text-sm font-bold text-gray-600 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Hours by Project
                 </h4>
                 <div class="h-64 sm:h-72 relative">
                    <Bar :data="projectChartData" :options="chartOptions" />
                 </div>
            </div>
            
             <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm min-h-[300px]">
                 <h4 class="text-sm font-bold text-gray-600 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Hours by Employee
                 </h4>
                 <div class="h-64 sm:h-72 relative">
                     <Pie :data="employeeChartData" :options="chartOptions" />
                 </div>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm min-h-[300px]">
                 <h4 class="text-sm font-bold text-amber-600 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    Points Leaderboard 🏆
                 </h4>
                 <div class="h-64 sm:h-72 relative">
                     <Bar :data="pointsChartData" :options="chartOptions" />
                 </div>
            </div>
        </div>

        <!-- Detail Table -->
        <div class="flex-1 min-h-[400px]">
            <BaseDataTable
                :columns="columns"
                :data="tableData"
                :loading="loading"
                :search-placeholder="'Search by Employee, Project etc...'"
                @search="handleSearch"
            >
                <!-- Custom Cells -->
                <template #cell-project="{ value }">
                    <span class="font-semibold text-indigo-700">{{ value }}</span>
                </template>
                
                <template #cell-task="{ item }">
                     <div class="flex flex-col">
                        <span class="text-sm font-medium text-gray-900 line-clamp-1" :title="item.task">{{ item.task }}</span>
                        <span class="text-sm uppercase tracking-wider font-bold" 
                            :class="{
                                'text-emerald-500': item.task_status === 'done',
                                'text-amber-500': item.task_status === 'in_progress',
                                'text-gray-400': item.task_status === 'todo'
                            }">{{ item.task_status }}</span>
                     </div>
                </template>

                 <template #cell-employee="{ item }">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-full bg-gray-200 overflow-hidden ring-2 ring-white shadow-sm" v-if="item.avatar">
                            <img :src="item.avatar" class="h-full w-full object-cover">
                        </div>
                         <div class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold ring-2 ring-white shadow-sm" v-else>
                            {{ item.employee_initials }}
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ item.employee_name }}</div>
                        </div>
                    </div>
                </template>
                
                <template #cell-hours="{ item }">
                    <span class="font-bold text-gray-800">{{ item.hours }}h</span>
                </template>

                <template #cell-points="{ item }">
                    <span class="font-bold text-amber-600">{{ item.points }}</span>
                </template>
            </BaseDataTable>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import dayjs from 'dayjs';
import axios from 'axios';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import { Bar, Pie } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js';

// Register ChartJS
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

const props = defineProps({
    projects: { type: Array, default: () => [] },
    initialProjectId: { type: [Number, String], default: null } 
});

const loading = ref(false);
const tableData = ref([]);
const filters = ref({
    start_date: dayjs().startOf('month').format('YYYY-MM-DD'),
    end_date: dayjs().endOf('month').format('YYYY-MM-DD'),
    project_id: props.initialProjectId || ''
});

const stats = ref({ 
    total_hours: 0, 
    holiday_hours: 0, 
    resource_count: 0, 
    avg_daily: 0,
    total_scope: 0,
    total_points: 0,
    remaining_hours: 0
});

const chartData = ref({ projects: {}, employees: {}, points: {} });

const columns = {
    project: { label: 'Project', sortable: true },
    task: { label: 'Task / Status', sortable: true },
    employee: { label: 'Employee', sortable: true },
    start_date: { label: 'Period Start', sortable: true },
    end_date: { label: 'Period End', sortable: true },
    hours: { label: 'Hours', sortable: true, align: 'right' },
    points: { label: 'Points', sortable: true, align: 'right' },
};
const fetchData = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('planner.reports'), { params: filters.value });
        if (res.data.success) {
            tableData.value = res.data.data;
            stats.value = res.data.stats;
            chartData.value = res.data.charts;
        }
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const handleSearch = (q) => {
    if (!q) {
        fetchData(); 
        return;
    }
    const lower = q.toLowerCase();
    tableData.value = tableData.value.filter(i => 
        i.employee_name.toLowerCase().includes(lower) || 
        i.project.toLowerCase().includes(lower) ||
        i.task.toLowerCase().includes(lower)
    );
};

const exportExcel = () => {
    const params = new URLSearchParams(filters.value).toString();
    window.location.href = route('planner.reports.export') + '?' + params;
};

// Charts Computed
const projectChartData = computed(() => ({
    labels: Object.keys(chartData.value.projects),
    datasets: [{ label: 'Planned Hours', data: Object.values(chartData.value.projects), backgroundColor: '#6366f1', borderRadius: 6 }]
}));

const employeeChartData = computed(() => ({
    labels: Object.keys(chartData.value.employees),
    datasets: [{ label: 'Hours', data: Object.values(chartData.value.employees), backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6'], borderWidth: 0 }]
}));

const pointsChartData = computed(() => ({
    labels: Object.keys(chartData.value.points || {}),
    datasets: [{ 
        label: 'Brownie Points 🍪', 
        data: Object.values(chartData.value.points || {}), 
        backgroundColor: '#f59e0b', 
        borderRadius: 4,
        barPercentage: 0.6
    }]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { beginAtZero: true, grid: { display: false } }, // Minimalist grid
        x: { grid: { display: false } }
    }
};

watch(filters, () => fetchData(), { deep: true });
onMounted(() => fetchData());

</script>
