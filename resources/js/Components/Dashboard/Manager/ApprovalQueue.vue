<template>
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-2xl h-full flex flex-col group relative overflow-hidden text-white">
        <div class="flex justify-between items-center mb-6 relative z-10">
            <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-2">
                <ClipboardDocumentCheckIcon class="w-4 h-4 text-emerald-400" />
                Approval Queue
            </h3>
            <span v-if="pendingCount > 0" class="w-2 h-2 bg-rose-500 rounded-full animate-ping"></span>
        </div>

        <div class="space-y-6 flex-grow relative z-10">
            <div>
                <h4 class="text-4xl font-black tracking-tighter">{{ pendingCount }}</h4>
                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mt-1">Decisions Required</p>
            </div>

            <div class="space-y-2">
                <div v-for="i in 2" :key="i" class="p-3 bg-white/5 rounded-2xl border border-white/10 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase">Leave Request</p>
                        <p class="text-[8px] text-slate-400">Sent 2h ago</p>
                    </div>
                    <i class="fas fa-chevron-right text-[10px] text-slate-600"></i>
                </div>
            </div>
        </div>

        <button class="mt-6 w-full py-2.5 bg-emerald-500 text-slate-900 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-400 transition-all active:scale-95">
            Process All
        </button>
    </div>
</template>

<script setup>
import { ClipboardDocumentCheckIcon } from '@heroicons/vue/24/outline';

defineProps({
    pendingCount: Number
});
</script>
