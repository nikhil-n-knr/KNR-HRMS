<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Filler
} from 'chart.js';
import { Doughnut, Line, Chart } from 'vue-chartjs';
import { SankeyController, Flow } from 'chartjs-chart-sankey';
import { 
    CpuChipIcon, 
    ArrowRightIcon, 
    DocumentArrowDownIcon,
    Cog8ToothIcon,
    ServerStackIcon,
    BanknotesIcon,
    UsersIcon,
    WrenchScrewdriverIcon
} from '@heroicons/vue/24/outline';

// Register ChartJS components including Sankey
ChartJS.register(
  Title, 
  Tooltip, 
  Legend, 
  ArcElement, 
  LineElement, 
  PointElement,
  CategoryScale, 
  LinearScale,
  Filler,
  SankeyController, 
  Flow
);

defineOptions({ layout: MainLayout });

const props = defineProps({
  distribution: Object,
  sankeyData: Array,
  kpis: Object
});

// Chart.js Default styling overriding for Premium Dark Look
ChartJS.defaults.color = '#94a3b8';
ChartJS.defaults.font.family = "'Outfit', sans-serif";
ChartJS.defaults.font.weight = '900';
ChartJS.defaults.plugins.tooltip.backgroundColor = '#0f172a';
ChartJS.defaults.plugins.tooltip.titleColor = '#f8fafc';
ChartJS.defaults.plugins.tooltip.bodyColor = '#94a3b8';
ChartJS.defaults.plugins.tooltip.borderColor = '#1e293b';
ChartJS.defaults.plugins.tooltip.borderWidth = 1;
ChartJS.defaults.plugins.tooltip.padding = 12;

// --- 1. Distribution Chart ---
const categoryLabels = Object.keys(props.distribution);
const categoryDataVals = Object.values(props.distribution).map(statusObj => {
  return Object.values(statusObj).reduce((a, b) => a + b, 0);
});

const distributionData = {
  labels: categoryLabels,
  datasets: [{
    backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#f43f5e', '#a855f7'],
    borderColor: '#ffffff',
    borderWidth: 4,
    hoverOffset: 10,
    data: categoryDataVals
  }]
};

const distributionOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '70%',
  plugins: {
    legend: { 
        position: 'bottom',
        labels: { padding: 20, usePointStyle: true, pointStyle: 'circle' }
    }
  }
};

// --- 2. Sankey Chart ---
const sankeyChartData = {
  datasets: [{
    label: 'Asset Flow Topology',
    data: props.sankeyData,
    colorFrom: (c) => c.dataset.data[c.dataIndex].from === 'Available' ? '#10b981' : '#334155',
    colorTo: (c) => '#1e293b',
    colorMode: 'gradient',
    borderWidth: 0,
    nodeWidth: 20,
    font: { size: 10, family: 'Outfit', weight: '900' }
  }]
};

const sankeyOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } }
};

// --- 3. Depreciation Chart ---
const depreciationData = {
  labels: ['Year 0', 'Year 1', 'Year 2', 'Year 3', 'Year 4', 'Year 5'],
  datasets: [
    {
      label: 'Book Value Matrix',
      backgroundColor: 'rgba(79, 70, 229, 0.1)',
      borderColor: '#4F46E5',
      borderWidth: 3,
      pointBackgroundColor: '#fff',
      pointBorderColor: '#4F46E5',
      pointBorderWidth: 3,
      pointRadius: 5,
      pointHoverRadius: 8,
      data: [100, 80, 60, 40, 20, 0].map(p => (props.kpis.total_value * p / 100)),
      fill: true,
      tension: 0.4
    },
    {
      label: 'Market Vector (Est)',
      borderColor: '#10B981',
      borderWidth: 3,
      borderDash: [5, 5],
      pointBackgroundColor: '#10B981',
      pointRadius: 0,
      pointHoverRadius: 6,
      data: [100, 70, 45, 30, 15, 5].map(p => (props.kpis.total_value * p / 100)),
      tension: 0.4
    }
  ]
};

const depreciationOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: { mode: 'index', intersect: false },
  scales: {
      x: { grid: { display: false, drawBorder: false } },
      y: { grid: { color: '#f1f5f9', drawBorder: false }, border: { display: false } }
  },
  plugins: { legend: { position: 'top', labels: { usePointStyle: true } } }
};

</script>

<template>
  <Head title="Strategic Asset Intelligence" />

  <div class="space-y-10 font-outfit pb-20 animate-in fade-in slide-in-from-bottom-5 duration-700">
    <!-- Premium Header -->
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-8 bg-slate-900 rounded-[2.5rem] p-10 shadow-2xl shadow-indigo-500/20 relative overflow-hidden group">
        <div class="absolute -right-32 -top-32 w-96 h-96 bg-indigo-500/10 rounded-full blur-[120px] group-hover:bg-indigo-500/20 transition-all duration-1000"></div>
        
        <div class="flex items-center gap-6 relative z-10">
            <div class="w-16 h-16 bg-white/10 backdrop-blur-xl rounded-2xl flex items-center justify-center text-indigo-400 border border-white/10 shadow-xl group-hover:rotate-12 transition-transform">
                <CpuChipIcon class="w-10 h-10" />
            </div>
            <div>
                <h1 class="text-3xl font-black text-white uppercase tracking-tight flex items-center gap-4">
                    Strategic Asset OS
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-black bg-white/10 text-indigo-400 border border-white/5 uppercase tracking-[0.3em] backdrop-blur-md">Telemetry_Active</span>
                </h1>
                <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] mt-2.5">
                    Live hardware diagnostics, valuation metrics & distribution tracking
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4 relative z-10 w-full xl:w-auto">
            <Link :href="route('assets.index')" class="h-14 px-8 bg-white/5 border border-white/10 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] shadow-sm hover:bg-white/10 transition-all active:scale-95 flex items-center justify-center gap-3 whitespace-nowrap">
                Command Terminal
            </Link>
            <button class="h-14 px-8 bg-indigo-600 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] shadow-xl hover:bg-emerald-500 transition-all active:scale-95 flex items-center justify-center gap-3 whitespace-nowrap group/exp">
                Export Matrix
                <DocumentArrowDownIcon class="w-5 h-5 group-hover/exp:-translate-y-1 transition-transform" />
            </button>
            <Link :href="route('assets.configurations')" class="w-14 h-14 bg-white/5 border border-white/10 text-slate-300 rounded-2xl flex items-center justify-center hover:bg-white/10 hover:text-white transition-all shadow-sm shrink-0 group/cfg">
                <Cog8ToothIcon class="w-6 h-6 group-hover/cfg:rotate-90 transition-transform duration-500" />
            </Link>
        </div>
    </div>

    <!-- Strategic KPIs -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
      <div v-for="(stat, idx) in [
          { label: 'Total Network Nodes', val: kpis.total_assets, icon: ServerStackIcon, color: 'text-indigo-500', bg: 'bg-indigo-50', iconBg: 'bg-indigo-500' },
          { label: 'Cumulative Valuation', val: '$' + Number(kpis.total_value).toLocaleString(), icon: BanknotesIcon, color: 'text-emerald-500', bg: 'bg-emerald-50', iconBg: 'bg-emerald-500' },
          { label: 'Assigned Operatives', val: kpis.assigned_percent + '%', icon: UsersIcon, color: 'text-amber-500', bg: 'bg-amber-50', iconBg: 'bg-amber-500' },
          { label: 'Maintenance Queue', val: kpis.maintenance_count || 0, icon: WrenchScrewdriverIcon, color: 'text-rose-500', bg: 'bg-rose-50', iconBg: 'bg-rose-500' }
      ]" :key="idx" class="bg-white p-6 md:p-8 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/40 relative group hover:scale-[1.02] transition-all overflow-hidden flex flex-col justify-between h-48">
          <div class="absolute -right-6 -bottom-6 w-32 h-32 rounded-full blur-3xl opacity-50 group-hover:scale-150 transition-transform duration-1000" :class="stat.bg"></div>
          
          <div class="relative z-10 flex justify-between items-start">
             <span class="text-sm font-black text-slate-400 uppercase tracking-widest max-w-[100px] leading-tight">{{ stat.label }}</span>
             <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-xl group-hover:rotate-12 transition-transform" :class="stat.iconBg">
                 <component :is="stat.icon" class="w-6 h-6" />
             </div>
          </div>
          
          <div class="relative z-10 min-w-0">
             <div class="text-3xl md:text-4xl font-black text-slate-900 font-mono tracking-tighter truncate leading-none">
                 {{ stat.val }}
             </div>
          </div>
      </div>
    </div>

    <!-- Telemetry Charts Array -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      <!-- 1. Node Distribution -->
      <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-slate-100 flex flex-col group hover:shadow-2xl transition-shadow">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-indigo-400 shadow-md">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Category Distribution</h3>
                <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Asset physical spread</p>
            </div>
        </div>
        <div class="h-80 flex-1 relative">
            <div class="absolute inset-0 flex items-center justify-center flex-col pointer-events-none">
                <span class="text-3xl font-black text-slate-900 tabular-nums leading-none">{{ kpis.total_assets }}</span>
                <span class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Total Nodes</span>
            </div>
            <Doughnut :data="distributionData" :options="distributionOptions" />
        </div>
      </div>

      <!-- 2. Depreciation Trajectory -->
      <div class="lg:col-span-2 bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-slate-100 flex flex-col group hover:shadow-2xl transition-shadow">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-500 shadow-inner">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Financial Trajectory</h3>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-1">5-Year Depreciation Curve Simulation</p>
                </div>
            </div>
            <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-xs font-black uppercase tracking-widest border border-emerald-100 hidden md:block">Active Matrix</span>
        </div>
        <div class="h-80 flex-1">
          <Line :data="depreciationData" :options="depreciationOptions" />
        </div>
      </div>

    </div>

    <!-- 3. Sankey Topology Model -->
    <div class="bg-white p-10 rounded-[2.5rem] shadow-2xl shadow-indigo-500/10 border border-slate-100 relative overflow-hidden group">
      <div class="absolute inset-0 bg-slate-50/50 pointer-events-none"></div>
      
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 relative z-10">
          <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-slate-900 rounded-xl flex items-center justify-center text-indigo-400 shadow-lg group-hover:scale-110 transition-transform">
                  <i class="fas fa-network-wired text-xl"></i>
              </div>
              <div>
                  <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Allocation Topology</h3>
                  <p class="text-sm font-black text-slate-500 uppercase tracking-[0.3em] mt-1">Node Status flow into Operational Departments</p>
              </div>
          </div>
          <button class="h-10 px-5 bg-white border border-slate-200 text-slate-500 rounded-xl text-sm font-black uppercase tracking-widest hover:text-indigo-600 shadow-sm active:scale-95 transition-all flex justify-center items-center gap-2">
              <ArrowRightIcon class="w-4 h-4" /> Full View
          </button>
      </div>

      <div class="h-96 md:h-[500px] w-full relative z-10 bg-white/50 backdrop-blur-sm rounded-[2rem] border border-slate-100 p-4">
        <Chart type="sankey" :data="sankeyChartData" :options="sankeyOptions" />
      </div>
    </div>

  </div>
</template>

<style scoped>
.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
</style>
