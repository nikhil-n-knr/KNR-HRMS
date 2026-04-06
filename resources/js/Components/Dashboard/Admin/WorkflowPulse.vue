<template>
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-2xl h-full flex flex-col group relative overflow-hidden">
        <!-- Grid Pattern Support -->
        <div class="absolute inset-0 opacity-10 pointer-events-none" :style="{backgroundImage: 'radial-gradient(#334155 1px, transparent 1px)', backgroundSize: '15px 15px'}"></div>

        <div class="flex justify-between items-center mb-6 relative z-10">
            <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-2">
                <Squares2X2Icon class="w-4 h-4 text-emerald-400" />
                Workflow Pulse
            </h3>
            <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-black rounded-full border border-emerald-500/20">OPERATIONAL</span>
        </div>

        <div class="space-y-4 flex-grow relative z-10">
            <div class="flex items-end justify-between">
                <div>
                    <h4 class="text-4xl font-black text-white tracking-tighter">{{ pendingCount }}</h4>
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mt-1">Stagnant Approvals</p>
                </div>
                <div class="h-12 w-24">
                     <!-- Micro sparkline could go here -->
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 mt-4">
                <div v-for="i in 3" :key="i" class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500" :style="{width: (70 + (i*5)) + '%'}"></div>
                </div>
            </div>
            
            <p class="text-[9px] font-bold text-slate-400 leading-relaxed uppercase tracking-wide">
                Procedency detected in <span class="text-emerald-400">Attendance Policy 4</span>. Automated SLAs are currently mitigating bottlenecks.
            </p>
        </div>

        <button class="mt-6 w-full py-2.5 bg-white text-slate-900 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-400 transition-all active:scale-95">
            Architect Studio
        </button>
    </div>
</template>

<script setup>
import { Squares2X2Icon } from '@heroicons/vue/24/outline';

defineProps({
    pendingCount: Number
});
</script>
