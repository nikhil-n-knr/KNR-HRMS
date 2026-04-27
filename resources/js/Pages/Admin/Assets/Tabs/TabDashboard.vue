<script setup>
import { computed } from 'vue';
import BaseChart from '@/Components/BaseChart.vue';
import { 
    BanknotesIcon, 
    CheckBadgeIcon, 
    UserGroupIcon, 
    CalendarIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    ShieldCheckIcon,
    CpuChipIcon,
    InformationCircleIcon,
    WrenchScrewdriverIcon,
    BoltIcon,
    SparklesIcon,
    ChartBarIcon,
    CurrencyRupeeIcon as CashIcon,
    ArchiveBoxIcon,
    ClockIcon,
    BellAlertIcon,
    ArrowRightIcon,
    PresentationChartLineIcon
} from '@heroicons/vue/24/solid';

const props = defineProps({
    stats: Object
});

// Mock Chart Data for Visual Intelligence
const valuationTrendData = {
    labels: ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN'],
    datasets: [{
        label: 'Value (₹L)',
        data: [45, 52, 48, 61, 58, 65],
        borderColor: '#0d9488',
        backgroundColor: '#0d948822',
        fill: true,
        tension: 0.4,
        pointRadius: 4,
        borderWidth: 3
    }]
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#0f172a',
            titleFont: { family: 'Outfit', weight: '900', size: 12 },
            bodyFont: { family: 'Outfit', weight: '600' },
            padding: 12,
            cornerRadius: 12
        }
    },
    scales: {
        y: { display: false },
        x: {
            grid: { display: false },
            ticks: { font: { family: 'Outfit', weight: '900', size: 9 }, color: '#94a3b8' }
        }
    }
};
</script>

<template>
    <div class="space-y-8 font-outfit animate-in fade-in slide-in-from-bottom-5 duration-700">
        
        <!-- Operations Pulse -->
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                <div class="text-left">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-600">Operations Pulse</p>
                    <h2 class="text-3xl font-black text-slate-900 mt-2 uppercase tracking-tight">Intelligence Dashboard</h2>
                    <p class="text-xs font-semibold text-slate-400 mt-2">Real-time health and deployment progress across all assets.</p>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                    <div v-for="s in [
                        { label: 'Valuation', value: '₹' + (stats?.total_valuation || 0).toLocaleString(), color: 'text-indigo-600' },
                        { label: 'Compliance', value: (stats?.audit_compliance_pct || 0) + '%', color: 'text-emerald-600' },
                        { label: 'In Stock', value: stats?.in_stock || 0, color: 'text-slate-900' },
                        { label: 'Attention', value: stats?.critical_alerts || 0, color: 'text-rose-600' }
                    ]" :key="s.label" class="rounded-2xl border border-slate-100 bg-slate-50 px-6 py-4 min-w-[130px] text-left">
                        <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 leading-none">{{ s.label }}</p>
                        <p class="text-xl font-black mt-2 tabular-nums leading-none" :class="s.color">{{ s.value }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Analytics Overview -->
        <section class="grid grid-cols-1 xl:grid-cols-[1.4fr_1fr] gap-8">
            <!-- Valuation Trend -->
            <article class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm flex flex-col min-h-[450px]">
                <div class="flex items-center justify-between mb-8 border-b border-slate-50 pb-6 text-left">
                    <div class="flex items-center gap-6">
                        <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 shadow-sm shrink-0">
                            <PresentationChartLineIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Visual Intelligence</p>
                            <h3 class="text-xl font-black text-slate-900 tracking-tight uppercase">Valuation Drift</h3>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[9px] font-bold bg-indigo-50 text-indigo-600 border border-indigo-100 uppercase tracking-widest leading-none">Live Data</span>
                </div>
                
                <div class="flex-1 relative">
                    <BaseChart type="line" :data="valuationTrendData" :options="chartOptions" />
                </div>

                <div class="mt-8 grid grid-cols-3 gap-6 pt-6 border-t border-slate-50 text-left">
                    <div class="space-y-1">
                        <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Pace</p>
                        <p class="text-[13px] font-black text-slate-900">+12% vs LY</p>
                    </div>
                    <div class="space-y-1 text-center">
                        <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Avg Unit Cost</p>
                        <p class="text-[13px] font-black text-slate-900">₹42,500</p>
                    </div>
                    <div class="space-y-1 text-right">
                        <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Optimization</p>
                        <p class="text-[13px] font-black text-emerald-600 uppercase tracking-widest">Optimal</p>
                    </div>
                </div>
            </article>

            <!-- Risk Snapshot & Alerts -->
            <article class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-8 border-b border-slate-50 pb-6 text-left">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Risk Snapshot</p>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight uppercase">Priority Alerts</h3>
                    </div>
                    <BellAlertIcon class="w-6 h-6 text-rose-500 animate-pulse" />
                </div>
                
                <div class="space-y-4 flex-1 overflow-y-auto no-scrollbar pr-2 text-left">
                    <div v-for="i in 4" :key="i" class="p-5 bg-slate-50 border border-slate-100 rounded-2xl relative overflow-hidden group hover:bg-white hover:border-indigo-200 transition-all shadow-sm">
                        <div class="absolute left-0 top-0 w-1 h-full bg-slate-200 group-hover:bg-indigo-600 transition-colors"></div>
                        <div class="flex flex-col gap-2">
                                      <span class="text-[8px] font-bold text-indigo-600 uppercase tracking-widest leading-none">Security Alert</span>
                                      <p class="text-sm font-bold text-slate-700 leading-tight">Asset #{{ 1024 + i }} requires a mandatory physical audit.</p>
                             <div class="flex items-center justify-between mt-3">
                                <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">T-{{ i * 2 }}H Ago</span>
                                <ArrowRightIcon class="w-4 h-4 text-slate-300 group-hover:text-indigo-600 transition-all -translate-x-2 group-hover:translate-x-0" />
                             </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100 space-y-4 text-left">
                     <div class="flex items-center justify-between">
                         <span class="text-[9px] font-bold uppercase text-slate-400 tracking-widest">Warranty Pulse</span>
                         <span class="text-xs font-black text-rose-600">{{ stats?.warranty_expiring_soon || 0 }} Critical Units</span>
                     </div>
                     <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                         <div class="h-full bg-rose-500 rounded-full" :style="{ width: '35%' }"></div>
                     </div>
                </div>
            </article>
        </section>

        <!-- Key Insights -->
        <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <article v-for="card in [
                { label: 'Fixed Assets', value: stats?.fixed_assets || 0, caption: 'Core Capital hardware' },
                { label: 'Scan Status', value: 'Active', caption: 'Live monitoring enabled' },
                { label: 'Restock Queue', value: stats?.pending_requests || 0, caption: 'Procurement Pipeline' },
                { label: 'Physical Docs', value: stats?.doc_compliance || '92%', caption: 'Archive health' }
            ]" :key="card.label" class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm hover:border-indigo-200 transition-all group text-left">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 group-hover:text-indigo-600 transition-colors">{{ card.label }}</p>
                <div class="mt-4 flex items-end justify-between gap-4">
                    <p class="text-3xl font-black text-slate-900 tracking-tight">{{ card.value }}</p>
                    <p class="text-[9px] font-bold text-slate-400 text-right uppercase tracking-widest leading-tight w-24 opacity-60">{{ card.caption }}</p>
                </div>
            </article>
        </section>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
</style>

<style scoped>
.shadow-3xl {
    box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.08);
}
.no-scrollbar::-webkit-scrollbar { display: none; }
</style>
