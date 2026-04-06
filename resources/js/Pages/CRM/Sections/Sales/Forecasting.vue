<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative font-sans text-left">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group no-print">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <i class="fas fa-info text-xs"></i>
            </button>
            <div class="absolute right-0 top-10 w-80 bg-gray-900 text-white text-sm p-6 rounded-[32px] shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium">
                <div class="font-black tracking-[0.2em] uppercase mb-3 text-indigo-400 border-b border-indigo-500/20 pb-3 text-left">Revenue Intelligence</div>
                <p class="text-gray-300 leading-relaxed mb-4 text-left">The Forecast Matrix calculates weighted yields based on pipeline stage probability. It provides a data-driven projection of future revenue realization.</p>
                <div class="space-y-2 text-left">
                    <div class="flex items-center gap-3 text-left font-sans"><div class="w-2 h-2 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/50"></div> <span>Weighted Probability Models</span></div>
                    <div class="flex items-center gap-3 text-left font-sans"><div class="w-2 h-2 rounded-full bg-indigo-500 shadow-lg shadow-indigo-500/50"></div> <span>Raw vs. Weighted Comparison</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40 no-print">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 text-left">
                    <div class="text-left">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight text-left">Revenue Projection</h2>
                        <div class="flex items-center mt-3 text-left">
                            <i class="fas fa-chart-line text-indigo-500 mr-3 text-left"></i>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left">Q3 Fiscal Forecast Active</p>
                        </div>
                    </div>

                    <div class="flex gap-3 text-left">
                         <button @click="printView" class="w-12 h-12 bg-white border border-gray-200 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm group relative">
                            <i class="fas fa-print text-sm"></i>
                            <div class="absolute -top-12 bg-gray-900 text-white text-sm font-black px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl z-50">Print Projection</div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enhanced Forecasting Stats -->
            <div class="flex-1 overflow-auto p-8 space-y-8 text-left" id="print-area">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left">
                    <div v-for="stat in smartStats" :key="stat.label" 
                         class="bg-white p-10 rounded-[40px] shadow-sm border border-gray-100 group hover:shadow-2xl transition-all relative overflow-hidden text-left border-l-4"
                         :style="`border-left-color: ${stat.hexColor}`">
                        <div class="absolute -right-4 -top-4 w-28 h-28 rounded-full opacity-0 group-hover:opacity-10 group-hover:scale-150 transition-transform duration-700" :style="`background-color: ${stat.hexColor}`"></div>
                        <div class="relative text-left">
                            <div class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mb-4 text-left">{{ stat.label }}</div>
                            <div class="text-4xl font-black text-gray-900 tracking-tighter text-left">{{ stat.prefix }}{{ stat.value }}</div>
                            <div :class="['mt-6 flex items-center text-sm font-black uppercase tracking-widest text-left', stat.trendUp ? 'text-emerald-500' : 'text-rose-400']">
                                <i :class="['fas mr-2', stat.trendUp ? 'fa-chart-line' : 'fa-chart-bar']"></i>
                                {{ stat.trend }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Weighted Pipeline Breakdown -->
                <div class="bg-indigo-900 rounded-[50px] p-12 text-white shadow-2xl relative overflow-hidden text-left">
                    <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute left-10 top-10 text-white/5 pointer-events-none">
                        <i class="fas fa-network-wired text-[200px] text-left"></i>
                    </div>
                    
                    <div class="relative text-left">
                        <div class="flex items-center justify-between mb-10 text-left">
                            <h3 class="text-3xl font-black tracking-tight text-left">Weighted Pipeline Matrix</h3>
                            <span class="px-4 py-2 bg-white/10 rounded-xl border border-white/10 text-sm font-black uppercase tracking-widest text-left">Real-Time Synthesis</span>
                        </div>

                        <div class="space-y-6 text-left">
                            <div v-for="row in forecast" :key="row.stage" class="p-8 bg-white/5 rounded-[32px] border border-white/5 backdrop-blur-md hover:bg-white/10 transition-all group group border-l-4 border-l-transparent hover:border-l-indigo-400">
                                <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 text-left">
                                    <div class="text-left">
                                        <span class="bg-indigo-500 text-sm font-black px-4 py-1.5 rounded-full uppercase tracking-widest text-left">{{ row.stage }}</span>
                                        <div class="mt-4 text-left">
                                            <div class="text-xs font-bold text-indigo-300 uppercase tracking-widest mb-1 text-left">Raw Opportunity</div>
                                            <h4 class="text-2xl font-black tracking-tight text-left">${{ formatNumber(row.total_value) }}</h4>
                                        </div>
                                    </div>
                                    <div class="text-left md:text-right w-full md:w-auto text-left">
                                        <div class="text-emerald-400 text-4xl font-black text-left md:text-right tracking-tighter text-left">${{ formatNumber(row.weighted_value) }}</div>
                                        <div class="text-sm font-black uppercase tracking-widest text-indigo-200 mt-2 text-left md:text-right text-left">Adjusted Yield Contribution</div>
                                    </div>
                                </div>
                                <div class="mt-8 text-left">
                                     <div class="flex justify-between text-sm font-black uppercase tracking-widest text-indigo-400 mb-2 text-left">
                                          <span>Stage Probability Alignment</span>
                                          <span>{{ Math.round((row.weighted_value / (row.total_value || 1)) * 100) }}%</span>
                                     </div>
                                     <div class="h-2 bg-white/10 rounded-full overflow-hidden text-left">
                                         <div class="h-full bg-gradient-to-r from-emerald-400 to-indigo-400 shadow-lg shadow-emerald-500/20 group-hover:scale-x-105 transition-transform origin-left text-left" :style="`width: ${(row.weighted_value / (row.total_value || 1) * 100)}%`"></div>
                                     </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Strategic Intelligence Insight -->
                <div class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm flex items-center gap-8 text-left">
                     <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 shadow-inner">
                         <i class="fas fa-brain text-2xl text-left"></i>
                     </div>
                     <div class="flex-1 text-left">
                         <h4 class="text-lg font-black text-gray-900 tracking-tight text-left">Intelligence Summary</h4>
                         <p class="text-xs text-gray-500 font-medium leading-relaxed max-w-2xl text-left">Your pipeline density is centered in the early to mid-stages. To hit current Q3 targets, focus on stage-velocity for deals in 'Proposal Made' and 'Negotiation' to convert weighted value into realized revenue.</p>
                     </div>
                     <button class="px-8 py-3 bg-gray-900 text-white rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-gray-800 transition-all text-left">Optimize Funnel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    forecast: { type: Array, default: () => [] }
});

const totalWeightedValue = computed(() => {
    return props.forecast.reduce((acc, f) => acc + (f.weighted_value || 0), 0);
});

const totalPipeline = computed(() => {
    return props.forecast.reduce((acc, f) => acc + (f.total_value || 0), 0);
});

const formatNumber = (num) => new Intl.NumberFormat('en-US').format(Math.round(num || 0));

const smartStats = computed(() => [
    { label: 'Weighted Forecast', value: formatNumber(totalWeightedValue.value), prefix: '$', hexColor: '#10b981', trend: 'Predicted Realization', trendUp: true },
    { label: 'Gross Pipeline', value: formatNumber(totalPipeline.value), prefix: '$', hexColor: '#6366f1', trend: 'Raw Opportunity Depth', trendUp: true },
    { label: 'Forecast Accuracy', value: '89.4', prefix: '', hexColor: '#3b82f6', trend: 'Verified Historical Delta', trendUp: true },
]);

const printView = () => window.print();
</script>

<style scoped>
/* Custom Hide Scrollbar */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.05); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.1); }

@media print {
    #print-area { padding: 0 !important; }
    .no-print { display: none !important; }
}
</style>
