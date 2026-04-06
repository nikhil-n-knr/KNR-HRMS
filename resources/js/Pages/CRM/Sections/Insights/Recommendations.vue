<template>
    <div class="space-y-6">
        <!-- AI Recommendations Section -->
        <div class="bg-gray-900 p-12 rounded-[50px] shadow-2xl shadow-indigo-500/10 min-h-[700px] relative overflow-hidden group">
            <div class="flex justify-between items-start mb-20 relative z-10">
                <div>
                     <p class="text-sm font-black uppercase tracking-widest text-emerald-500 mb-2">Prescriptive Intelligence Engine</p>
                    <h2 class="text-3xl font-black text-white tracking-tight flex items-center gap-6">
                        AI Smart Recommendations
                        <span class="px-5 py-2 rounded-2xl bg-white/10 text-emerald-400 border border-white/10 text-xs font-black uppercase tracking-widest animate-pulse shadow-inner">
                            Live Optimization
                        </span>
                    </h2>
                    <p class="text-sm text-gray-400 font-medium tracking-wide mt-2">Personalized actions generated from machine learning analysis of your CRM data</p>
                </div>
                <div class="flex gap-4">
                    <button class="px-8 py-4 bg-white/10 text-white rounded-3xl text-sm font-black shadow-lg border border-white/10 hover:bg-white/20 transition-all uppercase tracking-widest backdrop-blur-md">🔍 EXPLAIN AI LOGIC</button>
                    <button class="px-8 py-4 bg-emerald-600 text-white rounded-3xl text-sm font-black shadow-2xl shadow-emerald-500/30 hover:scale-105 transition-all uppercase tracking-widest border border-emerald-500">EXECUTE ALL ACTIONS</button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 relative z-10">
                 <!-- Recommendation Cards -->
                 <div v-for="(rec, idx) in ai_insights" :key="rec" 
                    class="p-10 rounded-[50px] border border-white/10 bg-white/5 backdrop-blur-3xl hover:bg-white/10 transition-all group/item overflow-hidden relative flex flex-col justify-between"
                    :class="idx % 2 === 0 ? 'border-l-4 border-l-emerald-500' : 'border-l-4 border-l-indigo-500'"
                 >
                      <div>
                           <div class="flex justify-between items-center mb-10">
                               <div class="w-16 h-16 rounded-3xl flex items-center justify-center text-4xl border border-white/10 shadow-inner rotate-6 group-hover/item:rotate-[-6deg] transition-transform duration-500"
                                :class="idx % 2 === 0 ? 'bg-emerald-500/20 text-emerald-400' : 'bg-indigo-500/20 text-indigo-400'"
                               >
                                   <i :class="getIcon(rec)"></i>
                               </div>
                               <span class="text-sm font-black text-emerald-400 uppercase tracking-widest leading-none">Recommendation #{{ idx + 1 }}</span>
                           </div>
                           <h3 class="text-xl font-black tracking-tight leading-relaxed text-white">"{{ rec }}"</h3>
                           <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mt-4">Predicted Impact: <span class="text-emerald-400">{{ getImpact(rec) }} Increase</span></p>
                      </div>

                      <div class="flex gap-4 mt-12 overflow-hidden opacity-0 group-hover/item:opacity-100 translate-y-4 group-hover/item:translate-y-0 transition-all duration-500">
                          <button class="flex-1 py-4 bg-white text-gray-900 rounded-2xl text-sm font-black uppercase tracking-widest shadow-xl hover:-translate-y-1 transition-all">EXECUTE</button>
                          <button class="flex-1 py-4 bg-white/10 text-white rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-white/20 transition-all">DISMISS</button>
                      </div>
                      
                      <i class="fas fa-brain absolute -right-8 -top-8 text-[120px] text-white/5 opacity-50 group-hover/item:scale-125 transition-transform duration-1000"></i>
                 </div>
            </div>

            <!-- Dashboard Analytics Preview in Recommendations -->
             <div class="mt-20 p-12 bg-white/5 border border-white/10 rounded-[60px] relative overflow-hidden flex flex-col items-center">
                 <h4 class="text-xs font-black text-gray-500 uppercase tracking-[1em] mb-12">Optimization History</h4>
                 <div class="w-full flex justify-center gap-10">
                     <div v-for="h in [60, 40, 80, 50, 95]" :key="h" class="flex flex-col items-center gap-4 group/h transition-all">
                          <div class="w-12 bg-indigo-500/50 rounded-2xl group-hover/h:bg-emerald-500 transition-all" :style="{ height: h + 'px' }"></div>
                          <span class="text-sm font-black text-gray-500 uppercase tracking-widest">W{{ [1,2,3,4,5][Math.floor(Date.now() / 1000) % 5] }}</span>
                     </div>
                 </div>
             </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    ai_insights: Array
});

const getIcon = (rec) => {
    if (rec.includes('WhatsApp')) return 'fab fa-whatsapp';
    if (rec.includes('Bundle')) return 'fas fa-cubes-stacked';
    if (rec.includes('Drop-off')) return 'fas fa-user-slash';
    return 'fas fa-wand-magic-sparkles';
}

const getImpact = (rec) => {
    if (rec.includes('ROI')) return '340% ROI';
    if (rec.includes('LTV')) return '3.2x LTV';
    return '12x Revenue';
}
</script>
