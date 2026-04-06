<template>
    <div class="space-y-6">
        <!-- Import Wizard & Insights Section -->
        <div class="bg-white p-12 rounded-[50px] border border-gray-100 shadow-md min-h-[700px] relative overflow-hidden group">
            <div class="flex justify-between items-start mb-16 relative z-10">
                <div>
                     <p class="text-sm font-black uppercase tracking-widest text-blue-500 mb-2">Legacy Data Transformation Engine</p>
                    <h2 class="text-4xl font-black text-gray-900 tracking-tight flex items-center gap-6">
                        Product History Import Analyzer
                        <span class="px-5 py-1.5 rounded-2xl bg-blue-50 text-blue-600 border border-blue-100 text-xs font-black uppercase tracking-widest shadow-inner">
                            AI Mapping Ready
                        </span>
                    </h2>
                    <p class="text-lg font-black text-gray-400 mt-2 uppercase tracking-widest">Enterprise Lifecycle Analysis</p>
                </div>
            </div>

            <!-- Import Wizard Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20">
                <div v-for="(step, idx) in ['Upload CSV/Excel', 'AI Field Mapping', 'Revenue Analytics']" :key="step" class="relative">
                    <div class="p-8 rounded-[40px] border transition-all relative overflow-hidden h-full flex flex-col justify-between"
                        :class="idx === 0 ? 'bg-blue-600 text-white shadow-2xl shadow-blue-500/30' : 'bg-gray-50 border-white shadow-sm' "
                    >
                        <div>
                             <p class="text-sm font-black uppercase tracking-widest mb-2" :class="idx === 0 ? 'text-blue-100' : 'text-gray-400'">Step {{ idx + 1 }}</p>
                             <h4 class="text-xl font-black uppercase tracking-tight">{{ step }}</h4>
                        </div>
                        <div v-if="idx === 0" class="mt-8 flex flex-col items-center py-6 border-2 border-dashed border-white/20 rounded-3xl group/upload hover:bg-white/10 cursor-pointer transition-all">
                             <i class="fas fa-file-excel text-4xl mb-4 group-hover/upload:rotate-12 transition-transform duration-500"></i>
                             <span class="text-xs font-black uppercase">DROP FILE TO START</span>
                        </div>
                        <div v-else class="mt-8 p-6 bg-white border border-gray-100 rounded-3xl flex items-center justify-center opacity-40">
                             <i class="fas fa-lock text-2xl text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Insight Grid (Displayed after simulated import/active state) -->
            <div class="bg-gray-900 rounded-[50px] p-12 text-white relative overflow-hidden group/insights">
                <div class="flex justify-between items-center mb-10 relative z-10">
                    <div>
                        <h3 class="text-2xl font-black tracking-tight"><i class="fas fa-brain text-blue-400 mr-4"></i> AI INSIGHTS GENERATED</h3>
                        <p class="text-sm text-gray-400 font-medium tracking-wide mt-1">Found patterns in 10K+ historical product records</p>
                    </div>
                    <button class="px-8 py-3 bg-blue-600 text-white rounded-2xl text-xs font-black shadow-lg hover:scale-105 transition-all uppercase tracking-widest border border-blue-500">REVENUE PATTERNS</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 relative z-10">
                    <div v-for="imp in import" :key="imp.product || imp.bundle" 
                        class="p-8 rounded-[40px] border border-white/10 bg-white/5 backdrop-blur-3xl hover:bg-white/10 transition-all group/item"
                    >
                         <h4 class="text-sm font-black text-blue-400 uppercase tracking-widest mb-6">Discovery</h4>
                         <p class="text-xl font-bold tracking-tight mb-4">
                             "{{ imp.product || imp.bundle }}" = 
                             <span class="text-4xl font-black text-white ml-2">{{ imp.revenue_share || imp.ltv_impact }}</span>
                             <span class="text-xs font-black text-blue-400 uppercase tracking-widest ml-4">{{ imp.revenue_share ? 'Revenue' : 'LTV BOOST' }}</span>
                         </p>
                         <div class="flex items-center gap-4 text-sm font-black text-gray-400 uppercase tracking-widest">
                             <span v-if="imp.churn">CHURN RISK: <span class="text-rose-400">{{ imp.churn }}</span></span>
                             <span v-if="imp.ltv_impact">Bundle Impact: HIGH</span>
                         </div>
                    </div>
                </div>

                <div class="mt-12 flex gap-4 relative z-10 justify-center">
                    <button class="px-10 py-5 bg-white text-gray-900 rounded-2xl text-xs font-black uppercase tracking-widest shadow-2xl shadow-blue-500/20 hover:scale-105 transition-all">🚀 REPOSITION ALL PRODUCTS</button>
                    <button class="px-10 py-5 bg-blue-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-2xl shadow-blue-500/20 hover:scale-105 transition-all border border-blue-500"><i class="fas fa-cubes-stacked"></i> BUNDLE CREATOR ENGINE</button>
                </div>

                 <i class="fas fa-chart-waterfall absolute -right-16 -bottom-16 text-[250px] text-white/5 rotate-[-15deg] group-hover/insights:scale-110 transition-transform duration-1000"></i>
            </div>
            
            <div class="mt-20 flex justify-center py-20 border-t border-gray-50 max-w-4xl mx-auto opacity-30 group-hover:opacity-100 transition-all duration-1000">
                <div class="text-center">
                     <i class="fas fa-database text-6xl text-gray-200 mb-6"></i>
                     <p class="text-sm font-black text-gray-400 uppercase tracking-[1em]">Enterprise Import Engine Ready</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    import: Array,
    visualization: String
});
</script>
