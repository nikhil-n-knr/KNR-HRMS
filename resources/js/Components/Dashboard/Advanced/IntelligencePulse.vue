<template>
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl h-full flex flex-col group relative overflow-hidden text-white">
        <!-- Decoration -->
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl opacity-20"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-900 to-emerald-950 opacity-50"></div>

        <div class="flex justify-between items-center mb-8 relative z-10">
            <div>
                <h3 class="text-xs font-black uppercase tracking-[0.2em] text-emerald-400 flex items-center gap-2">
                    <SparklesIcon class="w-4 h-4" />
                    Intelligence_Pulse
                </h3>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">{{ subtitle }}</p>
            </div>
            <div class="flex items-center gap-2 px-3 py-1 bg-white/5 border border-white/10 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                <span class="text-[9px] font-black uppercase tracking-widest text-emerald-500">Live_Stream</span>
            </div>
        </div>

        <!-- Metric Grid -->
        <div class="grid grid-cols-2 gap-4 mb-8 relative z-10">
            <div v-for="metric in metrics" :key="metric.label" 
                class="bg-white/5 border border-white/5 p-4 rounded-2xl hover:bg-white/10 transition-all cursor-pointer group/item"
            >
                <div class="flex items-center gap-3 mb-2">
                    <component :is="metric.icon" class="w-4 h-4 text-emerald-400 opacity-50 group-hover/item:opacity-100 transition-opacity" />
                    <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest">{{ metric.label }}</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-xl font-black text-white leading-none tracking-tighter">{{ metric.value }}</span>
                    <span v-if="metric.growth" class="text-[9px] font-bold text-emerald-400">{{ metric.growth }}</span>
                </div>
            </div>
        </div>

        <!-- Activity Feed -->
        <div class="space-y-4 flex-grow relative z-10 overflow-y-auto pr-2 custom-scrollbar">
            <div v-for="(event, idx) in events" :key="idx" 
                class="flex gap-4 group/event hover:bg-white/5 p-2 rounded-xl transition-all border border-transparent hover:border-white/5"
            >
                <div class="flex flex-col items-center gap-1 mt-1">
                    <div class="w-2 h-2 rounded-full border border-emerald-500/50 flex items-center justify-center p-0.5">
                        <div class="w-full h-full rounded-full bg-emerald-500"></div>
                    </div>
                    <div v-if="idx !== events.length - 1" class="w-px h-full bg-gradient-to-b from-emerald-500/50 to-transparent"></div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start mb-0.5">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-200 truncate pr-4">{{ event.title }}</span>
                        <span class="text-[8px] font-bold text-slate-500 uppercase whitespace-nowrap">{{ event.time }}</span>
                    </div>
                    <p class="text-[9px] text-slate-400 font-medium leading-relaxed line-clamp-2">{{ event.description }}</p>
                </div>
            </div>
        </div>

        <!-- Action Area -->
        <div class="mt-6 pt-4 border-t border-white/5 flex gap-3 relative z-10">
           <button class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl py-2 text-[9px] font-black uppercase tracking-widest transition-all shadow-xl shadow-emerald-500/20 active:scale-95">
                Audit_Report
           </button>
           <button class="flex-1 bg-white/5 hover:bg-white/10 text-slate-300 border border-white/10 rounded-xl py-2 text-[9px] font-black uppercase tracking-widest transition-all active:scale-95">
                Sys_Sync
           </button>
        </div>
    </div>
</template>

<script setup>
import { SparklesIcon } from '@heroicons/vue/24/outline';

defineProps({
    subtitle: { type: String, default: 'Real-time Operations Monitoring' },
    metrics: { type: Array, default: () => [] },
    events: { type: Array, default: () => [] }
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 2px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(16, 185, 129, 0.2);
    border-radius: 10px;
}
</style>
