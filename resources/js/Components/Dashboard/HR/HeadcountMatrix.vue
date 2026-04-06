<template>
    <div class="bg-white/40 backdrop-blur-xl border border-white/40 rounded-3xl p-5 shadow-xl h-full flex flex-col group relative overflow-hidden">
        <div class="flex justify-between items-center mb-6 relative z-10">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-2">
                <SquaresPlusIcon class="w-4 h-4 text-emerald-600" />
                Org_Intelligence_Matrix
            </h3>
            <div class="flex items-center gap-1">
                <span class="text-[10px] font-black text-emerald-600">{{ growth }}</span>
                <i class="fas fa-chart-line text-[8px] text-emerald-500"></i>
            </div>
        </div>

        <div class="flex-grow space-y-5 relative z-10">
            <div v-for="(count, dept) in distribution" :key="dept" class="group/item">
                <div class="flex justify-between items-center mb-1.5 px-0.5">
                    <span class="text-[9px] font-black text-slate-700 uppercase tracking-widest">{{ dept }}</span>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black text-slate-800">{{ count }}</span>
                        <span class="text-[7px] font-bold text-slate-400">/ {{ calculatePercentage(count) }}%</span>
                    </div>
                </div>
                <!-- Dual Bar System: Total + Diversity/Sub-Metric -->
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden flex">
                    <div class="h-full bg-emerald-500/80 rounded-full transition-all duration-1000" :style="{width: calculatePercentage(count) + '%'}"></div>
                </div>
                <!-- Visual Diversity Indicator (Mini dots) -->
                <div class="flex gap-0.5 mt-1 opacity-0 group-hover/item:opacity-100 transition-opacity">
                    <div v-for="n in 5" :key="n" class="w-1 h-1 rounded-full bg-slate-200"></div>
                </div>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100/50 flex flex-col gap-3">
             <div class="flex justify-between items-center">
                 <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Global Force Depth</p>
                 <span class="text-[10px] font-black text-slate-900">{{ totalCount }}</span>
             </div>
             <div class="grid grid-cols-2 gap-2">
                 <div class="bg-slate-50 p-2 rounded-xl text-center">
                     <p class="text-[7px] font-bold text-slate-400 uppercase">Retention</p>
                     <p class="text-[10px] font-black text-emerald-600">94.2%</p>
                 </div>
                 <div class="bg-slate-50 p-2 rounded-xl text-center">
                     <p class="text-[7px] font-bold text-slate-400 uppercase">Engagement</p>
                     <p class="text-[10px] font-black text-emerald-600">78%</p>
                 </div>
             </div>
        </div>
    </div>
</template>

<script setup>
import { SquaresPlusIcon } from '@heroicons/vue/24/outline';
import { computed } from 'vue';

const props = defineProps({
    distribution: { type: Object, default: () => ({}) },
    growth: { type: String, default: '+4.2%' }
});

const totalCount = computed(() => Object.values(props.distribution).reduce((a, b) => a + b, 0));

const calculatePercentage = (count) => {
    if (totalCount.value === 0) return 0;
    return Math.round((count / totalCount.value) * 100);
};
</script>
