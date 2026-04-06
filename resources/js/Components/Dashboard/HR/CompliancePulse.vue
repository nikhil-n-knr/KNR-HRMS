<template>
    <div class="bg-rose-50/30 border border-rose-100 rounded-3xl p-5 shadow-xl h-full flex flex-col group relative overflow-hidden">
        <div class="flex justify-between items-center mb-6 relative z-10">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                <ShieldCheckIcon class="w-4 h-4 text-rose-600" />
                Compliance Pulse
            </h3>
            <span class="px-2 py-0.5 bg-rose-500 text-white text-[9px] font-black rounded-full shadow-lg shadow-rose-500/20">CRITICAL</span>
        </div>

        <div class="space-y-6 flex-grow relative z-10">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-rose-500 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-rose-500/20">
                    <i class="fas fa-user-slash text-lg"></i>
                </div>
                <div>
                    <h4 class="text-3xl font-black text-rose-600 tracking-tighter">{{ absentCount }}</h4>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Absent Unscheduled</p>
                </div>
            </div>

            <div class="p-4 bg-white/60 rounded-2xl border border-rose-100 shadow-sm">
                <p class="text-[9px] font-bold text-slate-500 leading-relaxed uppercase tracking-wide">
                    Deviation detected in <span class="text-rose-600 font-black">Attendance Policy Compliance</span>. 
                    Immediate follow-up required for unscheduled absences.
                </p>
            </div>
        </div>

        <button class="mt-6 w-full py-2.5 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-rose-600 transition-all active:scale-95 shadow-xl">
            Trigger Protocol
        </button>
    </div>
</template>

<script setup>
import { ShieldCheckIcon } from '@heroicons/vue/24/outline';

defineProps({
    absentCount: Number
});
</script>
