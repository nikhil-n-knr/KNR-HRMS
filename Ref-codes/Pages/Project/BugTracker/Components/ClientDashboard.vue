<template>
    <div class="space-y-6">
        <!-- Dashboard Header with Status Toggle (Management Only) -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/40 p-4 rounded-3xl border border-white/50 mb-4">
            <div>
                 <h2 class="text-xl font-black text-slate-800 tracking-tight">Execution Pulse</h2>
                 <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Real-time Project Trajectory & Health</p>
            </div>
            <div class="flex items-center gap-2">
                 <!-- Global Health Gauge -->
                 <div ref="healthGaugeRef" class="w-32 h-32"></div>
            </div>
        </div>

        <!-- Quick Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-for="stat in quickStats" :key="stat.label" 
                class="bg-white/80 backdrop-blur-xl p-5 rounded-2xl border border-white/50 shadow-sm transition-all duration-500 hover:shadow-md group overflow-hidden relative">
                <div class="absolute -right-4 -top-4 w-12 h-12 bg-emerald-500/5 rounded-full group-hover:scale-[3] transition-transform duration-700"></div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 relative z-10">{{ stat.label }}</p>
                <div class="flex items-end gap-2 relative z-10">
                    <h3 class="text-2xl font-black text-slate-800 tracking-tight">{{ stat.value }}</h3>
                    <span v-if="stat.trend" :class="stat.trend > 0 ? 'text-emerald-500' : 'text-rose-500'" class="text-[10px] font-bold mb-1">
                        {{ stat.trend > 0 ? '↑' : '↓' }} {{ Math.abs(stat.trend) }}%
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Project Progress (Radial) -->
            <div class="lg:col-span-1 bg-white/80 backdrop-blur-xl p-6 rounded-3xl border border-white/50 shadow-sm flex flex-col items-center justify-center">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 self-start">Aggregate Milestone Progress</h3>
                <div class="relative">
                    <div ref="chartRef"></div>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mt-4">
                        <span class="text-4xl font-black text-slate-800">{{ currentProgress }}%</span>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Completed</span>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-4 w-full">
                    <div class="p-3 bg-emerald-50/50 rounded-2xl border border-emerald-100 text-center">
                        <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest">Reliability Score</p>
                        <h4 class="text-lg font-black text-emerald-700">High</h4>
                    </div>
                    <div class="p-3 bg-slate-50/50 rounded-2xl border border-slate-100 text-center">
                         <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Next Target</p>
                         <h4 class="text-lg font-black text-slate-800">Q3-26</h4>
                    </div>
                </div>
            </div>

            <!-- Active Projects Timeline + Per-Milestone Progress -->
            <div class="lg:col-span-2 bg-white/80 backdrop-blur-xl p-6 rounded-3xl border border-white/50 shadow-sm overflow-hidden">
                <div class="flex justify-between items-center mb-8">
                    <div class="flex flex-col">
                        <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest">Active Development Trajectory</h3>
                        <div class="flex items-center gap-1.5 mt-2 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full border border-emerald-100 animate-pulse w-fit">
                            <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></div>
                            <span class="text-[9px] font-black uppercase tracking-tighter">Live Updates Enabled</span>
                        </div>
                    </div>
                    
                    <!-- Executive PDF Export (Management Only) -->
                    <div v-if="$page.props.auth.user.role === 'management' || $page.props.auth.user.role === 'admin'" class="flex gap-2">
                         <a v-for="project in projects" :key="'dl-' + project.id" 
                            :href="route('projects.portal.summary-pdf', { project: project.id })" target="_blank"
                            class="px-4 py-2.5 bg-slate-900 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-xl flex items-center gap-2 group/btn">
                             <i class="fas fa-file-pdf text-emerald-400 group-hover/btn:text-white transition-colors"></i>
                             Summary: {{ project.code }}
                         </a>
                    </div>
                </div>

                <div class="space-y-6 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                    <div v-for="project in projects" :key="project.id" class="p-5 rounded-2xl bg-white border border-slate-100 hover:border-emerald-200 transition-all duration-300 group cursor-pointer shadow-sm hover:shadow-md">
                        <div class="flex flex-col md:flex-row gap-6">
                            <!-- Minified Radial for each project -->
                            <div class="w-20 h-20 shrink-0">
                                <apexchart type="radialBar" height="120" :options="getMiniChartOptions(project)" :series="[project.manual_progress_percentage || 0]" />
                            </div>

                            <div class="flex-1">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 group-hover:text-emerald-600 transition-colors">{{ project.name }}</h4>
                                        <div class="flex items-center gap-3 mt-1">
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ project.code }}</p>
                                            <span class="h-1 w-1 bg-slate-200 rounded-full"></span>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Maturity: {{ project.project_health_index }}%</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button v-if="$page.props.auth.user.role === 'management' || $page.props.auth.user.role === 'admin'"
                                            @click.stop="openHistory(project.id)"
                                            class="p-1.5 rounded-lg border border-slate-100 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-all"
                                            title="View Governance History">
                                            <i class="fas fa-history text-[10px]"></i>
                                        </button>
                                        <div class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border"
                                            :class="getHealthClass(project.project_health_index)">
                                            {{ getHealthLabel(project.project_health_index) }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-4 mt-4">
                                    <div class="flex-1">
                                         <div class="flex justify-between text-[9px] font-bold uppercase mb-1.5">
                                            <span class="text-slate-500">{{ project.manual_status_label || 'Current Evolution' }}</span>
                                            <span class="text-emerald-600 font-black">{{ project.manual_progress_percentage }}%</span>
                                        </div>
                                        <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full transition-all duration-1000 ease-out rounded-full shadow-[0_0_8px_rgba(16,185,129,0.3)]"
                                                :class="getProgressColor(project.manual_progress_percentage)"
                                                :style="{ width: project.manual_progress_percentage + '%' }"></div>
                                        </div>
                                    </div>
                                    <div class="text-[9px] font-black text-slate-400 bg-slate-50 px-2 py-1 rounded-lg border border-slate-100 whitespace-nowrap">
                                        ETA: {{ formatDate(project.deadline) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Change History Drawer -->
        <ChangeHistoryDrawer 
            v-if="showHistoryDrawer"
            :show="showHistoryDrawer"
            :projectId="selectedProjectId"
            @close="showHistoryDrawer = false"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, onUnmounted } from 'vue';
import ApexCharts from 'apexcharts';
import ChangeHistoryDrawer from './ChangeHistoryDrawer.vue';

const props = defineProps(['stats', 'projects']);

const chartRef = ref(null);
const healthGaugeRef = ref(null);
let mainChart = null;
let healthChart = null;

const currentProgress = ref(0);
const showHistoryDrawer = ref(false);
const selectedProjectId = ref(null);

const openHistory = (projectId) => {
    selectedProjectId.value = projectId;
    showHistoryDrawer.value = true;
};

const quickStats = computed(() => [
    { label: 'Platform Portfolio', value: props.stats.total_projects, trend: 0 },
    { label: 'Active Compliance', value: props.stats.active_sprints || 8, trend: 14 },
    { label: 'Global Health Index', value: props.stats.health_average + '%', trend: props.stats.health_average > 80 ? 3 : -2 },
    { label: 'Document Sign-offs', value: '24', trend: 12 }
]);

const getProgressColor = (val) => {
    if (val >= 75) return 'bg-emerald-500';
    if (val >= 40) return 'bg-amber-500';
    return 'bg-rose-500';
};

const getHealthClass = (health) => {
    if (health >= 80) return 'bg-emerald-50 text-emerald-600 border-emerald-100';
    if (health >= 40) return 'bg-amber-50 text-amber-600 border-amber-100';
    return 'bg-rose-50 text-rose-600 border-rose-100';
};

const getHealthLabel = (health) => {
    if (health >= 80) return 'Stable';
    if (health >= 40) return 'Watching';
    return 'Critical';
};

const formatDate = (date) => {
    if (!date) return 'TBD';
    return new Date(date).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
};

const getMiniChartOptions = (project) => {
    const color = project.manual_progress_percentage >= 75 ? '#10b981' : (project.manual_progress_percentage >= 40 ? '#f59e0b' : '#ef4444');
    return {
        chart: { type: 'radialBar', sparkline: { enabled: true } },
        plotOptions: {
            radialBar: {
                hollow: { size: '40%' },
                dataLabels: { show: false },
                track: { background: '#f1f5f9' }
            }
        },
        colors: [color],
        stroke: { lineCap: 'round' }
    };
};

const initMainChart = () => {
    if (!chartRef.value) return;
    
    const options = {
        series: [props.stats.completion_average],
        chart: {
            height: 380,
            type: 'radialBar',
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 1500,
                animateGradually: { enabled: true, delay: 150 },
                dynamicAnimation: { enabled: true, speed: 600 }
            }
        },
        plotOptions: {
            radialBar: {
                startAngle: -135,
                endAngle: 135,
                hollow: { size: '70%', background: 'transparent' },
                track: {
                    background: '#f1f5f9',
                    strokeWidth: '100%',
                    margin: 5, 
                },
                dataLabels: {
                    name: { show: false },
                    value: { show: false }
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                type: 'horizontal',
                gradientToColors: ['#34d399'],
                stops: [0, 100]
            }
        },
        stroke: { lineCap: 'round' },
        colors: ['#10b981'],
        labels: ['Completion'],
    };

    mainChart = new ApexCharts(chartRef.value, options);
    mainChart.render();
    
    // Animate counter
    let start = 0;
    const end = props.stats.completion_average;
    const interval = setInterval(() => {
        if (start >= end) {
            currentProgress.value = end;
            clearInterval(interval);
        } else {
            start++;
            currentProgress.value = start;
        }
    }, 15);
};

onMounted(() => {
    initMainChart();
});

onUnmounted(() => {
    if (mainChart) mainChart.destroy();
});

// Watch for real-time updates
watch(() => props.stats.completion_average, (newVal) => {
    if (mainChart) {
        mainChart.updateSeries([newVal]);
        currentProgress.value = newVal;
    }
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
  background: #cbd5e1;
}
</style>
