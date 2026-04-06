<template>
    <div class="bg-white/40 backdrop-blur-xl border border-white/40 rounded-3xl p-5 shadow-xl h-full flex flex-col group relative overflow-hidden">
        <div class="flex justify-between items-center mb-6 relative z-10">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                <UsersIcon class="w-4 h-4 text-emerald-600" />
                User Intelligence
            </h3>
        </div>

        <div class="flex-grow space-y-6 relative z-10">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 relative flex items-center justify-center">
                    <svg class="w-full h-full -rotate-90">
                        <circle cx="40" cy="40" r="35" stroke="currentColor" stroke-width="8" fill="transparent" class="text-slate-100" />
                        <circle cx="40" cy="40" r="35" stroke="currentColor" stroke-width="8" fill="transparent" class="text-emerald-500" stroke-dasharray="220" stroke-dashoffset="44" />
                    </svg>
                    <span class="absolute text-lg font-black text-slate-800">80%</span>
                </div>
                <div>
                    <h4 class="text-2xl font-black text-slate-800 tracking-tight">{{ totalUsers }}</h4>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Personnel Active</p>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Growth Velocity</span>
                    <span class="text-[10px] font-black text-emerald-600">+12.5%</span>
                </div>
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 w-[80%]"></div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex gap-2">
            <button class="flex-1 py-2 bg-slate-50 text-slate-600 text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-slate-900 hover:text-white transition-all border border-slate-100 shadow-sm">
                Roster List
            </button>
            <button class="flex-1 py-2 bg-slate-50 text-slate-600 text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-slate-900 hover:text-white transition-all border border-slate-100 shadow-sm">
                Permissions
            </button>
        </div>
    </div>
</template>

<script setup>
import { UsersIcon } from '@heroicons/vue/24/outline';

defineProps({
    totalUsers: Number
});
</script>
