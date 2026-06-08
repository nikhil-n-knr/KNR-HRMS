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
    WrenchScrewdriverIcon,
    InformationCircleIcon,
    ChartPieIcon,
    PresentationChartLineIcon,
    ArrowPathIcon,
    WalletIcon,
    BoltIcon,
    ArrowUpRightIcon,
    SparklesIcon,
    ArrowTrendingUpIcon,
    AdjustmentsHorizontalIcon,
    ChevronDownIcon,
    EyeIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/solid';

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

// Chart.js Default styling overriding for Premium look
ChartJS.defaults.color = '#94a3b8';
ChartJS.defaults.font.family = "'Outfit', sans-serif";
ChartJS.defaults.font.weight = '900';
ChartJS.defaults.plugins.tooltip.backgroundColor = '#0f172a';
ChartJS.defaults.plugins.tooltip.titleColor = '#f8fafc';
ChartJS.defaults.plugins.tooltip.bodyColor = '#94a3b8';
ChartJS.defaults.plugins.tooltip.borderColor = '#334155';
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
    backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#f43f5e', '#8b5cf6'],
    borderColor: '#ffffff',
    borderWidth: 4,
    hoverOffset: 12,
    data: categoryDataVals
  }]
};

const distributionOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '75%',
  plugins: {
    legend: { 
        position: 'bottom',
        labels: { padding: 25, usePointStyle: true, pointStyle: 'circle', font: { size: 10 } }
    }
  }
};

// --- 2. Sankey Chart ---
const sankeyChartData = {
  datasets: [{
    label: 'Where Items Go',
    data: props.sankeyData,
    colorFrom: (c) => c.dataset.data[c.dataIndex].from === 'Available' ? '#10b981' : '#6366f1',
    colorTo: (c) => '#f1f5f9',
    colorMode: 'gradient',
    borderWidth: 0,
    nodeWidth: 24,
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
  labels: ['Start', 'Yr 1', 'Yr 2', 'Yr 3', 'Yr 4', 'Yr 5'],
  datasets: [
    {
      label: 'Market Value',
      backgroundColor: 'rgba(99, 102, 241, 0.1)',
      borderColor: '#6366f1',
      borderWidth: 4,
      pointBackgroundColor: '#fff',
      pointBorderColor: '#6366f1',
      pointBorderWidth: 3,
      pointRadius: 6,
      pointHoverRadius: 9,
      data: [100, 80, 60, 40, 20, 5].map(p => (props.kpis.total_value * p / 100)),
      fill: true,
      tension: 0.4
    },
    {
      label: 'Resell Value (Est)',
      borderColor: '#10B981',
      borderWidth: 3,
      borderDash: [8, 4],
      pointBackgroundColor: '#10B981',
      pointRadius: 0,
      pointHoverRadius: 6,
      data: [100, 75, 55, 35, 20, 10].map(p => (props.kpis.total_value * p / 100)),
      tension: 0.4
    }
  ]
};

const depreciationOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: { mode: 'index', intersect: false },
  scales: {
      x: { grid: { display: false } },
      y: { 
        grid: { color: '#f1f5f9' }, 
        border: { display: false },
        ticks: { callback: (val) => '₹' + (val / 1000000).toFixed(1) + 'M' }
      }
  },
  plugins: { legend: { position: 'top', labels: { usePointStyle: true, font: { size: 11 } } } }
};

</script>

<template>
  <Head title="Visual Intelligence Dashboard" />

  <div class="space-y-12 font-outfit pb-20 animate-in fade-in slide-in-from-bottom-5 duration-700 -m-8 p-8 bg-slate-50 min-h-screen">
    <!-- Immersive Header Control -->
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-10 bg-white rounded-3xl p-10 shadow-sm relative overflow-hidden border border-slate-200">
      <div class="absolute -right-32 -top-32 w-[600px] h-[600px] bg-indigo-50 rounded-full blur-[140px]"></div>
      <div class="absolute -left-32 bottom-0 w-96 h-96 bg-emerald-50 rounded-full blur-[120px]"></div>
        
      <div class="flex items-center gap-8 relative z-10 text-left">
        <div class="w-20 h-20 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 border border-indigo-100 shadow-sm shrink-0">
          <SparklesIcon class="w-10 h-10" />
            </div>
        <div>
          <div class="flex items-center gap-6">
            <h1 class="text-4xl font-black text-slate-900 uppercase tracking-tight leading-none">Visual Intelligence</h1>
                    <div class="group/tooltip relative flex items-center">
              <InformationCircleIcon class="w-7 h-7 text-indigo-500 cursor-help opacity-80 hover:opacity-100 transition-opacity" />
                    </div>
                </div>
          <p class="text-xs font-semibold text-slate-500 mt-3">
            Asset analytics, valuation trends, and movement visibility.
                </p>
            </div>
        </div>

      <div class="flex flex-wrap items-center gap-4 relative z-10 w-full xl:w-auto">
        <Link :href="route('admin.assets.index')" class="h-12 px-6 bg-white border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm hover:border-indigo-200 hover:text-indigo-600 transition-all active:scale-95 flex items-center justify-center gap-3 whitespace-nowrap">
          <EyeIcon class="w-4 h-4" />
          Live View
            </Link>
        <button class="h-12 px-8 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm hover:bg-indigo-700 transition-all active:scale-95 flex items-center justify-center gap-3 whitespace-nowrap">
          Export Report
          <DocumentArrowDownIcon class="w-4 h-4" />
            </button>
        <Link :href="route('admin.assets.hub')" class="w-12 h-12 bg-white border border-slate-200 text-slate-500 rounded-xl flex items-center justify-center hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm shrink-0 group/cfg active:scale-90">
          <AdjustmentsHorizontalIcon class="w-5 h-5" />
            </Link>
        </div>
    </div>

    <!-- Strategic Command Nodes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
      <div v-for="(stat, idx) in [
          { label: 'Total Assets', val: kpis.total_assets, sub: 'Assets', icon: ServerStackIcon, color: 'text-indigo-500', bg: 'bg-indigo-50', iconBg: 'bg-indigo-600', metric: 'Live' },
          { label: 'Asset Value', val: '₹' + (kpis.total_value / 1000000).toFixed(1) + 'M', sub: 'Valuation', icon: BanknotesIcon, color: 'text-emerald-500', bg: 'bg-emerald-50', iconBg: 'bg-emerald-600', metric: 'Audited' },
          { label: 'Utilization', val: kpis.assigned_percent + '%', sub: 'Assigned', icon: UsersIcon, color: 'text-amber-500', bg: 'bg-amber-50', iconBg: 'bg-amber-500', metric: 'Healthy' },
          { label: 'Maintenance Risk', val: kpis.maintenance_count || 0, sub: 'Tickets', icon: ExclamationTriangleIcon || WrenchScrewdriverIcon, color: 'text-rose-500', bg: 'bg-rose-50', iconBg: 'bg-rose-600', metric: 'Attention' }
      ]" :key="idx" class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm relative group hover:shadow-md transition-all duration-500 overflow-hidden flex flex-col justify-between h-56 group/card">
          <div class="absolute -right-10 -top-10 w-48 h-48 rounded-full blur-[80px] opacity-10 group-hover/card:opacity-30 transition-opacity duration-1000" :class="stat.bg"></div>
          
           <div class="relative z-10 flex justify-between items-start">
             <div class="flex flex-col gap-2">
               <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] leading-none group-hover/card:text-slate-900 transition-colors">{{ stat.label }}</span>
               <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest leading-none">{{ stat.sub }}</span>
             </div>
             <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-sm transition-transform duration-500 shrink-0" :class="stat.iconBg">
                 <component :is="stat.icon" class="w-7 h-7" />
             </div>
          </div>
          
            <div class="relative z-10 mt-8 text-left">
             <div class="text-4xl font-black text-slate-950 tracking-tighter leading-none uppercase">
                 {{ stat.val }}
             </div>
             <div class="mt-4 flex items-center justify-between">
              <div class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full" :class="stat.color === 'text-rose-500' ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500'"></div>
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Live Data</span>
                </div>
              <span class="text-[9px] font-black uppercase tracking-[0.2em]" :class="stat.color">{{ stat.metric }}</span>
             </div>
          </div>
      </div>
    </div>

    <!-- Analytical Matrix -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
      
      <!-- Classification Matrix -->
      <div class="bg-white p-12 rounded-[3.5rem] shadow-sm border border-slate-200 flex flex-col group hover:shadow-2xl transition-all duration-700 relative overflow-hidden">
        <div class="absolute -right-24 -top-24 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-1000"></div>
        <div class="flex items-center gap-6 mb-12 relative z-10 text-left">
          <div class="w-16 h-16 bg-indigo-50 border border-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 shadow-sm group-hover:rotate-180 transition-transform duration-1000 shrink-0">
                <ChartPieIcon class="w-8 h-8" />
            </div>
          <div>
            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tighter leading-none">Classifications</h3>
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mt-2 leading-none">Distribution Breakdown</p>
            </div>
        </div>
        <div class="h-88 flex-1 relative group-hover:scale-105 transition-transform duration-700">
          <div class="absolute inset-0 flex items-center justify-center flex-col pointer-events-none">
            <span class="text-5xl font-black text-slate-900 tabular-nums leading-none">{{ kpis.total_assets }}</span>
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] mt-4 font-mono">TOTAL ASSETS</span>
            </div>
            <Doughnut :data="distributionData" :options="distributionOptions" />
        </div>
      </div>

      <!-- Capitalization & Depreciation Node -->
      <div class="lg:col-span-2 bg-white p-12 rounded-[3.5rem] shadow-sm border border-slate-200 flex flex-col group hover:shadow-2xl transition-all duration-700 relative overflow-hidden">
        <div class="absolute -right-32 -top-32 w-80 h-80 bg-emerald-50 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-1000"></div>
        <div class="flex items-center justify-between mb-14 relative z-10 text-left">
          <div class="flex items-center gap-8">
            <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 shadow-inner border border-emerald-100 shrink-0">
                    <WalletIcon class="w-8 h-8" />
                </div>
            <div>
              <h3 class="text-xl font-black text-slate-900 uppercase tracking-tighter leading-none">Value Lifecycle</h3>
              <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mt-2 leading-none">Projection and depreciation analysis</p>
                </div>
            </div>
          <div class="hidden sm:flex items-center gap-6">
            <div class="flex -space-x-3">
              <div class="w-10 h-10 rounded-xl border-2 border-white bg-indigo-600 flex items-center justify-center text-[10px] font-black text-white shadow-xl">MV</div>
              <div class="w-10 h-10 rounded-xl border-2 border-white bg-emerald-500 flex items-center justify-center text-[10px] font-black text-white shadow-xl">RV</div>
                </div>
            <div class="h-10 px-6 bg-indigo-600 text-white rounded-full text-[9px] font-black uppercase tracking-[0.2em] flex items-center gap-3 shadow-sm">
                    <div class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></div>
              LIVE
                </div>
            </div>
        </div>
        <div class="h-88 flex-1 group-hover:translate-x-2 transition-transform duration-1000">
          <Line :data="depreciationData" :options="depreciationOptions" />
        </div>
      </div>

    </div>

    <!-- Flow Terminal: Resource Translocation -->
    <div class="bg-white p-10 rounded-3xl shadow-sm border border-slate-200 relative overflow-hidden group hover:shadow-md transition-all duration-500">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-50/30 via-transparent to-transparent pointer-events-none"></div>
      
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-10 mb-12 relative z-10">
        <div class="flex items-center gap-8 text-left">
          <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100">
            <ArrowPathIcon class="w-8 h-8" />
              </div>
          <div>
            <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight leading-none">Resource Flow</h3>
            <p class="text-xs font-semibold text-slate-500 mt-3 leading-none">Store to department to final lifecycle stage</p>
              </div>
          </div>
        <Link :href="route('admin.assets.hub')" class="h-12 px-8 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 shadow-sm active:scale-95 transition-all flex justify-center items-center gap-3 group/all overflow-hidden relative">
          <span class="relative z-10 flex items-center gap-3 font-black">
           Open Asset Hub <ArrowRightIcon class="w-4 h-4 group-hover/all:translate-x-1 transition-transform" />
              </span>
          </Link>
      </div>

      <div class="h-[600px] w-full relative z-10 bg-slate-50 border border-slate-200 rounded-[3rem] p-12 shadow-inner group-hover:bg-white transition-colors duration-1000 italic overflow-hidden">
        <Chart type="sankey" :data="sankeyChartData" :options="sankeyOptions" />
      </div>

        <div class="mt-12 flex flex-wrap justify-center gap-12 text-[10px] font-black text-slate-400 uppercase tracking-[0.4em] relative z-10">
          <div class="flex items-center gap-3"><div class="w-3.5 h-3.5 rounded bg-indigo-500"></div> Active</div>
          <div class="flex items-center gap-3"><div class="w-3.5 h-3.5 rounded bg-emerald-500"></div> Ready</div>
          <div class="flex items-center gap-3"><div class="w-3.5 h-3.5 rounded bg-slate-700"></div> End Of Life</div>
      </div>
    </div>

  </div>
</template>

<style scoped>
.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
</style>
