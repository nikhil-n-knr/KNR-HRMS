<template>
    <div class="space-y-6">
        <!-- Multi-Touch Model Visualizer -->
        <div class="bg-white p-10 rounded-3xl border border-gray-100 shadow-sm min-h-[500px] relative overflow-hidden group">
            <div class="flex justify-between items-start mb-12 relative z-10">
                <div>
                     <p class="text-sm font-black uppercase tracking-widest text-emerald-500 mb-2">Revenue Attribution Engine</p>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight flex items-center gap-4">
                        Multi-Touch Attribution
                        <span class="px-4 py-1 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100 text-sm font-black uppercase tracking-widest animate-pulse">
                            {{ attribution_model }}
                        </span>
                    </h2>
                    <div class="mt-6 flex gap-4">
                        <div v-for="(val, label) in roi_summary" :key="label" class="p-4 rounded-2xl bg-gray-50 border border-gray-100 min-w-[150px]">
                            <p class="text-sm font-black text-gray-400 uppercase tracking-widest">{{ label }}</p>
                            <p class="text-xl font-black text-gray-900 mt-1 uppercase">{{ val }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button class="px-8 py-3 bg-white text-gray-700 rounded-2xl text-xs font-black shadow-lg border border-gray-100 hover:-translate-y-1 transition-all"><i class="fas fa-sync-alt"></i> SWITCH MODEL</button>
                    <button class="px-8 py-3 bg-emerald-600 text-white rounded-2xl text-xs font-black shadow-2xl shadow-emerald-500/30 hover:scale-105 transition-all"><i class="fas fa-brain"></i> AI ATTRIBUTION</button>
                </div>
            </div>

            <!-- Mind Map / Sankey Mock -->
            <div class="py-10 flex justify-center items-center relative min-h-[300px]">
                <div class="absolute w-full h-full opacity-10 pointer-events-none bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-emerald-500 to-transparent"></div>
                <div class="relative z-10 flex flex-wrap justify-center gap-12 max-w-5xl">
                    <div v-for="step in journey_map" :key="step.step" class="relative group cursor-pointer">
                        <div class="w-32 h-32 rounded-3xl bg-white border-2 border-dashed border-gray-200 p-6 flex flex-col items-center justify-center text-center gap-2 group-hover:border-emerald-400 group-hover:scale-110 transition-all shadow-xl group-hover:shadow-emerald-500/10">
                            <span class="text-sm font-black text-gray-400 uppercase tracking-widest leading-tight">{{ step.step }}</span>
                            <span class="text-2xl font-black text-emerald-600">{{ step.weight }}</span>
                        </div>
                        <i v-if="journey_map.indexOf(step) < journey_map.length -1" class="fas fa-arrow-right absolute -right-8 top-1/2 -translate-y-1/2 text-emerald-200 text-2xl group-hover:animate-ping opacity-50"></i>
                    </div>
                </div>
            </div>

            <div class="mt-20 p-8 rounded-3xl bg-gray-900 text-white relative overflow-hidden flex justify-between items-center group/panel">
                <div class="relative z-10">
                    <h4 class="text-xl font-black tracking-tight">Budget Optimization Recommendation</h4>
                    <p class="text-sm text-gray-400 font-medium mt-2">AI suggests reallocating <span class="text-emerald-400 font-black">60% of LinkedIn budget</span> to WhatsApp for 12x better ROI.</p>
                </div>
                <button class="relative z-10 px-8 py-4 bg-emerald-600 text-white rounded-2xl text-sm font-black shadow-2xl shadow-emerald-500/20 hover:scale-105 transition-all uppercase tracking-widest border border-emerald-500 overflow-hidden group/btn">
                    <span class="relative z-10">REALLOCATE BUDGET</span>
                     <div class="absolute inset-0 bg-white/20 translate-x-[-100%] group-hover/btn:translate-x-[100%] transition-transform duration-700"></div>
                </button>
                 <i class="fas fa-coins absolute -right-10 -bottom-10 text-[180px] text-white/5 rotate-12 group-hover/panel:scale-110 transition-transform duration-1000"></i>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    attribution_model: String,
    roi_summary: Object,
    journey_map: Array
});
</script>
