<template>
    <div class="space-y-6">
        <!-- ABM Analytics Stats Section -->
        <div class="bg-gray-900 p-12 rounded-[60px] shadow-2xl shadow-indigo-500/10 min-h-[700px] relative overflow-hidden group">
            <div class="flex justify-between items-start mb-20 relative z-10">
                <div>
                     <p class="text-sm font-black uppercase tracking-widest text-indigo-400 mb-2">ABM Multi-Touch Revenue Impact</p>
                    <h2 class="text-4xl font-black text-white tracking-tight flex items-center gap-6 underline decoration-emerald-500 decoration-8 underline-offset-8">
                        ABM Intelligence Engine
                        <span class="px-5 py-2 rounded-2xl bg-white/10 text-emerald-400 border border-white/10 text-xs font-black uppercase tracking-widest animate-pulse shadow-inner">
                            Global Target Reach: 50%
                        </span>
                    </h2>
                    <p class="text-sm text-gray-400 font-medium tracking-wide mt-10 uppercase">Enterprise Lifecycle Focus</p>
                </div>
                <div class="flex gap-4">
                    <button class="px-8 py-4 bg-white/10 text-white rounded-3xl text-sm font-black shadow-lg border border-white/10 hover:bg-white/20 transition-all uppercase tracking-widest backdrop-blur-md">🔍 EXPLAIN ANALYTICS</button>
                    <button class="px-8 py-4 bg-emerald-600 text-white rounded-3xl text-sm font-black shadow-2xl shadow-emerald-500/30 hover:scale-105 transition-all uppercase tracking-widest border border-emerald-500">OPTIMIZE ABM PATH</button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 relative z-10 mb-20">
                 <!-- Individual Stats Cards -->
                 <div v-for="(val, label) in abm_overview" :key="label" 
                    class="p-10 rounded-[50px] border border-white/10 bg-white/5 backdrop-blur-3xl hover:bg-white/10 transition-all group/item overflow-hidden relative flex flex-col justify-between"
                 >
                      <div class="flex justify-between items-center mb-10">
                           <div class="w-16 h-16 rounded-3xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-4xl border border-white/10 shadow-inner group-hover/item:rotate-12 transition-transform duration-500">
                               <i :class="getIcon(label)"></i>
                           </div>
                           <span class="text-sm font-black text-gray-500 uppercase tracking-widest leading-none">{{ label }}</span>
                      </div>
                      <h3 class="text-5xl font-black text-white tracking-tighter">{{ val }}</h3>
                      <p class="text-xs font-bold text-gray-400 uppercase mt-4 tracking-widest">Target Account Reach</p>
                 </div>
            </div>

            <!-- Enhanced Visualization: Target Lifecycle Velocity -->
             <div class="bg-white/5 border border-white/10 rounded-[60px] p-12 min-h-[400px] flex flex-col items-center justify-center relative group/visual">
                 <h4 class="text-xs font-black text-gray-500 uppercase tracking-[1em] mb-12">ABM Target Lifecycle Velocity</h4>
                 <div class="w-full flex justify-between items-center gap-20 relative max-w-4xl py-20">
                     <div class="absolute w-full h-1 bg-gradient-to-r from-indigo-500 via-indigo-500 to-gray-800 rounded-full"></div>
                     <div v-for="(step, idx) in journey_map" :key="step" class="z-10 group/node cursor-pointer">
                         <div class="flex flex-col items-center gap-6">
                             <div class="w-20 h-20 bg-indigo-600 rounded-[30px] flex items-center justify-center border-4 border-gray-900 shadow-2xl group-hover/node:bg-emerald-500 transition-all group-hover/node:scale-125 duration-700">
                                 <i class="fas fa-check text-white text-2xl group-hover/node:animate-bounce"></i>
                             </div>
                             <span class="text-sm font-black uppercase text-center w-24 leading-none text-white tracking-widest">{{ step }}</span>
                         </div>
                     </div>
                 </div>
                 
                 <div class="absolute right-10 bottom-10 p-8 bg-black/50 backdrop-blur-xl border border-white/10 rounded-3xl shadow-2xl max-w-xs transition-all hover:scale-105">
                     <p class="text-sm font-black text-emerald-400 uppercase tracking-widest mb-4 leading-none">AI REACH DISCOVERY</p>
                     <p class="text-xs font-bold text-gray-200 leading-relaxed uppercase">Current ABM reach is 50%. Expanding to <span class="text-emerald-400 font-black">75% reach</span> will drive additional ₹24Cr pipeline.</p>
                 </div>
             </div>
             
             <!-- Animated Particles Layer -->
             <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-20">
                 <div class="particle w-4 h-4 rounded-full bg-emerald-400 absolute top-1/2 left-1/4 blur-lg animate-pulse"></div>
                 <div class="particle w-6 h-6 rounded-full bg-indigo-500 absolute top-1/4 left-3/4 blur-lg animate-pulse delay-700"></div>
                 <div class="particle w-2 h-2 rounded-full bg-violet-400 absolute top-3/4 left-1/2 blur-lg animate-pulse delay-1000"></div>
             </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    abm_overview: Object,
    journey_map: Array
});

const getIcon = (label) => {
    if (label.includes('target')) return 'fas fa-building';
    if (label.includes('engaged')) return 'fas fa-user-check';
    return 'fas fa-check-double';
}
</script>
