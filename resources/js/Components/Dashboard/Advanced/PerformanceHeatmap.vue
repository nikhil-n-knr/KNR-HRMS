<template>
    <div class="bg-white/40 backdrop-blur-3xl border border-white/50 rounded-3xl p-6 shadow-xl h-full flex flex-col group relative overflow-hidden">
        <!-- Decoration -->
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl"></div>

        <div class="flex justify-between items-center mb-8 relative z-10">
            <div>
                <h3 class="text-xs font-black uppercase tracking-[0.2em] text-emerald-800/60 flex items-center gap-2">
                    <UserGroupIcon class="w-4 h-4" />
                    Performance_Matrix
                </h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ subtitle }}</p>
            </div>
            <div class="flex gap-2 p-1 bg-white/50 rounded-xl border border-white/30">
                <button v-for="v in ['Grid', 'Trend']" :key="v" 
                    @click="activeView = v"
                    class="px-2.5 py-1 rounded-lg text-[8px] font-black transition-all uppercase"
                    :class="activeView === v ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-600'"
                >
                    {{ v }}
                </button>
            </div>
        </div>

        <!-- Heatmap Grid Container -->
        <div class="flex-grow flex flex-col relative z-10 overflow-x-auto custom-scrollbar">
            <div class="min-w-[400px]">
                <div class="flex gap-1.5 mb-2">
                    <div v-for="d in ['M', 'T', 'W', 'T', 'F', 'S', 'S']" :key="d" 
                        class="w-6 h-6 flex items-center justify-center text-[7px] font-black text-slate-300 uppercase"
                    >
                        {{ d }}
                    </div>
                </div>
                <div class="grid grid-cols-7 gap-1.5 w-max">
                    <div v-for="(cell, idx) in cells" :key="idx" 
                        class="w-6 h-6 rounded-lg transition-all hover:scale-110 cursor-pointer border border-white/40 backdrop-blur-sm relative group/cell"
                        :style="{ backgroundColor: getCellColor(cell.value) }"
                    >
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 p-1.5 bg-slate-900 rounded-lg text-white opacity-0 group-hover/cell:opacity-100 transition-opacity pointer-events-none z-20 shadow-xl min-w-[60px]">
                            <p class="text-[6px] font-black uppercase tracking-widest text-slate-500 mb-0.5">{{ cell.date }}</p>
                            <p class="text-[8px] font-black text-white tracking-tighter">{{ cell.value }}% Velocity</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matrix Insights -->
        <div class="mt-8 grid grid-cols-2 gap-4 border-t border-white/40 pt-6 relative z-10">
            <div class="bg-emerald-500/5 p-3 rounded-2xl border border-emerald-500/10">
                <p class="text-[7px] font-black text-emerald-800 uppercase tracking-widest mb-1.5">Top Velocity</p>
                <div class="flex items-center gap-3">
                    <div class="h-6 w-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-[10px] font-black shadow-lg shadow-emerald-500/20">
                        {{ velocity }}%
                    </div>
                    <span class="text-[8px] font-bold text-slate-600 leading-tight">{{ velocityTag }}</span>
                </div>
            </div>
            <div class="bg-rose-500/5 p-3 rounded-2xl border border-rose-500/10">
                <p class="text-[7px] font-black text-rose-800 uppercase tracking-widest mb-1.5">Critical Risk</p>
                <div class="flex items-center gap-3">
                    <div class="h-6 w-6 rounded-lg bg-rose-500 text-white flex items-center justify-center text-[10px] font-black shadow-lg shadow-rose-500/20">
                        {{ risk }}
                    </div>
                    <span class="text-[8px] font-bold text-slate-600 leading-tight">{{ riskTag }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { UserGroupIcon } from '@heroicons/vue/24/outline';

const activeView = ref('Grid');

defineProps({
    subtitle: { type: String, default: 'Team Velocity Distribution' },
    cells: { type: Array, default: () => [] },
    velocity: { type: Number, default: 92 },
    velocityTag: { type: String, default: 'Optimal Performance' },
    risk: { type: Number, default: 2 },
    riskTag: { type: String, default: 'Potential Blockers' }
});

const getCellColor = (value) => {
    if (value >= 90) return '#059669'; // Emerald-600
    if (value >= 75) return '#10b981'; // Emerald-500
    if (value >= 50) return '#34d399'; // Emerald-400
    if (value >= 25) return '#a7f3d0'; // Emerald-200
    return '#f1f1f1'; // Gray
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    height: 3px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(16, 185, 129, 0.2);
    border-radius: 10px;
}
</style>
