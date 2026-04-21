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

        <!-- View Toggle -->
        <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl w-fit">
            <button 
                @click="activeReportView = 'standard'"
                class="px-4 py-1.5 text-xs font-black uppercase tracking-widest rounded-lg transition-all"
                :class="activeReportView === 'standard' ? 'bg-white shadow text-indigo-600' : 'text-gray-400 hover:text-gray-600'"
            >
                Operations
            </button>
            <button 
                @click="activeReportView = 'extensions'"
                class="px-4 py-1.5 text-xs font-black uppercase tracking-widest rounded-lg transition-all"
                :class="activeReportView === 'extensions' ? 'bg-white shadow text-indigo-600' : 'text-gray-400 hover:text-gray-600'"
            >
                Extensions
            </button>
            <button 
                @click="activeReportView = 'performance'"
                class="px-4 py-1.5 text-xs font-black uppercase tracking-widest rounded-lg transition-all"
                :class="activeReportView === 'performance' ? 'bg-white shadow text-indigo-600' : 'text-gray-400 hover:text-gray-600'"
            >
                Performance
            </button>
        </div>

        <template v-if="activeReportView === 'standard'">
            <!-- KPI Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                 <div class="col-span-2 md:col-span-1 bg-white/90 backdrop-blur-xl p-4 rounded-xl border border-gray-200 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-transform">
                      <p class="text-xs font-bold text-gray-400 uppercase">Portfolio Scope (Hours)</p>
                      <h3 class="text-2xl font-black text-indigo-900 mt-1">{{ stats.total_scope }}h</h3>
                      <p class="text-xs text-gray-500 mt-2 flex justify-between">
                         <span>Invested: {{ stats.total_actual }}h</span>
                         <span :class="stats.remaining_hours < 0 ? 'text-red-500' : 'text-emerald-500'">{{ stats.remaining_hours }}h left</span>
                      </p>
                 </div>
                 <div class="bg-gradient-to-br from-amber-50 to-orange-50 p-4 rounded-xl border border-amber-100 shadow-sm relative overflow-hidden group hover:scale-[1.02] transition-transform">
                     <p class="text-xs font-bold text-amber-600 uppercase">Project Value</p>
                     <h3 class="text-xl md:text-2xl font-black text-amber-800 mt-1">{{ stats.total_points }} 🍪</h3>
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
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm min-h-[300px]">
                     <h4 class="text-sm font-bold text-gray-600 mb-4 flex items-center gap-2">Hours by Project</h4>
                     <div class="h-64 sm:h-72 relative"><Bar :data="projectChartData" :options="chartOptions" /></div>
                </div>
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm min-h-[300px]">
                     <h4 class="text-sm font-bold text-gray-600 mb-4 flex items-center gap-2">Hours by Employee</h4>
                     <div class="h-64 sm:h-72 relative"><Pie :data="employeeChartData" :options="chartOptions" /></div>
                </div>
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm min-h-[300px]">
                     <h4 class="text-sm font-bold text-amber-600 mb-4 flex items-center gap-2">Points Leaderboard 🏆</h4>
                     <div class="h-64 sm:h-72 relative"><Bar :data="pointsChartData" :options="chartOptions" /></div>
                </div>
            </div>
        </template>

        <template v-else-if="activeReportView === 'extensions'">
            <ProjectExtensionAnalytics 
                v-if="extensionData.project"
                :project="extensionData.project"
                :extensions="extensionData.extensions"
                :stats="extensionData.stats"
                :person-performance="extensionData.person_performance || { delayed: [], fast: [], all: [] }"
                @record="showExtensionModal = true"
                @export="exportExtensions"
            />
            <div v-else class="py-20 text-center bg-white rounded-xl border border-gray-100 shadow-inner">
                <p class="text-sm font-black text-gray-400 uppercase tracking-widest">Select a project to view detailed extension analytics</p>
            </div>
        </template>

        <template v-else-if="activeReportView === 'performance'">
            <ProjectPerformanceDeepDive 
                v-if="performanceData && filters.project_id"
                :metrics="performanceData"
                @task-click="$emit('task-click', $event)"
            />
            <div v-else class="py-20 text-center bg-white rounded-xl border border-gray-100 shadow-inner">
                <p class="text-sm font-black text-gray-400 uppercase tracking-widest">Select a project to analyze performance velocity</p>
            </div>
        </template>

        <ProjectExtensionModal 
            :show="showExtensionModal"
            :project-id="filters.project_id"
            @close="showExtensionModal = false"
            @success="fetchExtensionData"
        />

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

                <!-- NEW PERFORMANCE CELLS -->
                <template #cell-baseline_hours="{ value }">
                    <span class="text-xs font-bold text-gray-400">{{ value }}h</span>
                </template>
                <template #cell-allocated_hours="{ value }">
                    <span class="text-xs font-bold text-indigo-600">{{ value }}h</span>
                </template>
                <template #cell-actual_hours="{ item }">
                    <span class="text-xs font-bold" :class="item.actual_hours > item.allocated_hours ? 'text-rose-600' : 'text-emerald-600'">
                        {{ item.actual_hours }}h
                    </span>
                </template>
                <template #cell-extended_hours="{ value }">
                    <span class="text-xs font-bold text-amber-600" v-if="value > 0">+{{ value }}h</span>
                    <span class="text-gray-300" v-else>-</span>
                </template>
                <template #cell-delay_status="{ value }">
                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-tighter" :class="{
                        'bg-emerald-100 text-emerald-700': value === 'On Track',
                        'bg-amber-100 text-amber-700': value === 'Timeline Deviation' || value === 'Slow Progress',
                        'bg-rose-100 text-rose-700': value === 'Delayed & Slow' || value === 'Overdue'
                    }">{{ value }}</span>
                </template>
                <template #cell-delay_metrics="{ item }">
                    <div class="flex flex-col gap-1">
                        <span v-if="item.timeline_delay > 0" class="text-[10px] font-bold text-amber-800 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-100 italic">📅 {{ item.timeline_delay }}d Timeline Delay</span>
                        <span v-if="item.productivity_variance > 0" class="text-[10px] font-bold text-rose-800 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-100 italic">⚡ {{ item.productivity_variance }}h Productivity Variance</span>
                        <span v-if="!item.timeline_delay && !item.productivity_variance" class="text-[10px] text-gray-400 font-medium">Perfect Alignment</span>
                    </div>
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
import ProjectExtensionAnalytics from './ProjectExtensionAnalytics.vue';
import ProjectPerformanceDeepDive from './ProjectPerformanceDeepDive.vue';
import ProjectExtensionModal from '@/Components/Project/ProjectExtensionModal.vue';
import { Bar, Pie } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js';

// Register ChartJS
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

const emit = defineEmits(['task-click']);

const normalizeReportView = (value) => {
    const normalized = String(value || '').toLowerCase();
    if (normalized === 'extensions') return 'extensions';
    if (normalized === 'performance') return 'performance';
    if (normalized === 'operations') return 'standard';
    if (normalized === 'standard') return 'standard';
    return 'standard';
};

const props = defineProps({
    projects: { type: Array, default: () => [] },
    initialProjectId: { type: [Number, String], default: null },
    initialReportView: { type: String, default: 'standard' }
});

const loading = ref(false);
const activeReportView = ref(normalizeReportView(props.initialReportView || new URLSearchParams(window.location.search).get('tab')));
const showExtensionModal = ref(false);
const tableData = ref([]);
const extensionData = ref({ project: null, extensions: [], stats: {} });
const performanceData = ref(null);
const filters = ref({
    start_date: dayjs().startOf('month').format('YYYY-MM-DD'),
    end_date: dayjs().endOf('month').format('YYYY-MM-DD'),
    project_id: props.initialProjectId || ''
});

const stats = ref({ 
    total_hours: 0, 
    total_actual: 0,
    holiday_hours: 0, 
    resource_count: 0, 
    avg_daily: 0,
    total_scope: 0,
    total_points: 0,
    remaining_hours: 0,
    health_score: 'N/A',
    forecast_finish: 'Unknown'
});

const chartData = ref({ projects: {}, employees: {}, points: {} });

const syncReportQuery = (view = activeReportView.value) => {
    const params = new URLSearchParams(window.location.search);

    if (filters.value.project_id) {
        params.set('project', filters.value.project_id);
    }

    params.set('tab', view === 'standard' ? 'operations' : view);
    window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);
};

const columns = computed(() => {
    if (activeReportView.value === 'performance') {
        return {
            task: { label: 'Task Analysis' },
            baseline_hours: { label: 'Baseline (B)' },
            allocated_hours: { label: 'Allocated (A)' },
            actual_hours: { label: 'Actual (Ac)' },
            extended_hours: { label: 'Ext. (E)' },
            delay_status: { label: 'Integrity' },
            delay_metrics: { label: 'Deviation Analysis' }
        };
    }
    return {
        project: { label: 'Project', sortable: true },
        task: { label: 'Task / Status', sortable: true },
        employee: { label: 'Employee', sortable: true },
        start_date: { label: 'Period Start', sortable: true },
        end_date: { label: 'Period End', sortable: true },
        hours: { label: 'Hours', sortable: true, align: 'right' },
        points: { label: 'Points', sortable: true, align: 'right' },
    };
});
const fetchData = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('planner.reports'), { params: filters.value });
        if (res.data.success) {
            allTimesheetData.value = res.data.data;
            allPerformanceData.value = res.data.performanceData || [];
            
            updateDisplayData();
            
            stats.value = res.data.stats;
            chartData.value = res.data.charts;
        }
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const allTimesheetData = ref([]);
const allPerformanceData = ref([]);

const updateDisplayData = () => {
    if (activeReportView.value === 'performance') {
        tableData.value = allPerformanceData.value;
    } else {
        tableData.value = allTimesheetData.value;
    }
};

watch(activeReportView, () => {
    updateDisplayData();
});

const fetchExtensionData = async () => {
    if (!filters.value.project_id) {
        extensionData.value = { project: null, extensions: [], stats: {} };
        return;
    }
    loading.value = true;
    try {
        const res = await axios.get(route('projects.extensions.analytics', filters.value.project_id));
        extensionData.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const fetchPerformanceData = async () => {
    if (!filters.value.project_id) return;
    loading.value = true;
    try {
        const res = await axios.get(route('projects.performance.metrics', filters.value.project_id));
        performanceData.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const exportExtensions = () => {
    if (!filters.value.project_id) return;
    window.location.href = route('projects.extensions.export', filters.value.project_id);
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
    const params = new URLSearchParams(filters.value);
    params.set('format', 'excel'); // Request the new multi-page excel format
    window.location.href = route('planner.reports.export') + '?' + params.toString();
};

const getHealthDotColor = (s) => {
    if (s?.includes('Healthy')) return 'bg-emerald-500';
    if (s?.includes('Critical')) return 'bg-red-500';
    return 'bg-amber-500';
};

const getHealthTextColor = (s) => {
    if (s?.includes('Healthy')) return 'text-emerald-700';
    if (s?.includes('Critical')) return 'text-red-700';
    return 'text-amber-700';
};

// Charts Computed
const projectChartData = computed(() => {
    const keys = Object.keys(chartData.value.projects || {});
    return {
        labels: keys,
        datasets: [
            { 
                label: 'Planned', 
                data: keys.map(k => chartData.value.projects[k].planned), 
                backgroundColor: '#e2e8f0', 
                borderRadius: 4 
            },
            { 
                label: 'Actual', 
                data: keys.map(k => chartData.value.projects[k].actual), 
                backgroundColor: '#6366f1', 
                borderRadius: 4 
            }
        ]
    };
});

const employeeChartData = computed(() => {
    const keys = Object.keys(chartData.value.employees || {});
    return {
        labels: keys,
        datasets: [
            { 
                label: 'Planned', 
                data: keys.map(k => chartData.value.employees[k].planned), 
                backgroundColor: '#cbd5e1', 
                borderRadius: 4 
            },
            { 
                label: 'Actual', 
                data: keys.map(k => chartData.value.employees[k].actual), 
                backgroundColor: '#10b981', 
                borderRadius: 4 
            }
        ]
    };
});

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
    plugins: { 
        legend: { display: true, position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } 
    },
    scales: {
        y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
        x: { grid: { display: false } }
    }
};

watch(() => activeReportView.value, (view) => {
    syncReportQuery(view);
    if (view === 'extensions') fetchExtensionData();
    if (view === 'performance') fetchPerformanceData();
}, { immediate: true });

watch(filters, () => {
    fetchData();
    if (activeReportView.value === 'extensions') fetchExtensionData();
    if (activeReportView.value === 'performance') fetchPerformanceData();
    syncReportQuery(activeReportView.value);
}, { deep: true });
onMounted(() => fetchData());

</script>
