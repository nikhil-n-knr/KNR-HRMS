<template>
    <div class="bg-white/40 backdrop-blur-xl border border-white/40 rounded-3xl p-5 shadow-xl h-full flex flex-col group relative overflow-hidden">
        <div class="flex justify-between items-center mb-6 relative z-10">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                <CalendarIcon class="w-4 h-4 text-emerald-600" />
                Leave Velocity
            </h3>
        </div>

        <div class="flex-grow space-y-6 relative z-10">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-3xl font-black text-slate-800 tracking-tighter">{{ pendingLeaves }}</h4>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Pending Requests</p>
                </div>
                <div class="text-right">
                    <span class="text-xs font-black text-emerald-600">Active Cycle</span>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">SLA Tracked</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-50 border border-slate-100 p-3 rounded-2xl">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Avg Turnaround</p>
                    <p class="text-sm font-black text-slate-700">4.2 Hours</p>
                </div>
                <div class="bg-slate-50 border border-slate-100 p-3 rounded-2xl">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Approval Rate</p>
                    <p class="text-sm font-black text-slate-700">92%</p>
                </div>
            </div>
        </div>

        <button class="mt-6 w-full py-2.5 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-slate-900 transition-all active:scale-95 shadow-lg shadow-emerald-500/20">
            Batch Approval
        </button>
    </div>
</template>

<script setup>
import { CalendarIcon } from '@heroicons/vue/24/outline';

defineProps({
    pendingLeaves: Number
});
</script>
