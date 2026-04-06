<template>
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-2xl h-full flex flex-col group relative overflow-hidden text-white">
        <!-- Decoration -->
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-emerald-500/5 rounded-full blur-3xl"></div>

        <div class="flex justify-between items-center mb-6 relative z-10">
            <h3 class="text-[10px] font-black uppercase tracking-[0.2em] flex items-center gap-2 text-emerald-400">
                <CpuChipIcon class="w-4 h-4" />
                Infrastructure_Core
            </h3>
            <div class="flex items-center gap-3">
                <div class="flex -space-x-1">
                    <div v-for="i in 3" :key="i" class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]" :class="{'animate-pulse': i===1}"></div>
                </div>
                <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest">{{ uptime }} Uptime</span>
            </div>
        </div>

        <!-- Resource Gauges -->
        <div class="grid grid-cols-3 gap-4 mb-6 relative z-10">
            <div v-for="(val, label) in { CPU: health.cpu, RAM: health.memory, DISK: health.storage }" :key="label" class="text-center">
                <div class="relative w-full aspect-square flex items-center justify-center">
                    <svg class="w-full h-full -rotate-90">
                        <circle cx="50%" cy="50%" r="40%" stroke="currentColor" stroke-width="3" fill="transparent" class="text-slate-800" />
                        <circle cx="50%" cy="50%" r="40%" stroke="currentColor" stroke-width="4" fill="transparent" class="text-emerald-500" stroke-dasharray="125" :stroke-dashoffset="125 - (125 * val / 100)" stroke-linecap="round" />
                    </svg>
                    <span class="absolute text-[10px] font-black">{{ val }}%</span>
                </div>
                <p class="text-[7px] font-black text-slate-500 uppercase tracking-widest mt-1">{{ label }}</p>
            </div>
        </div>

        <!-- Topology Breakdown -->
        <div class="space-y-3 flex-grow relative z-10">
            <div v-for="loc in telemetry.topology" :key="loc.location" class="bg-white/5 border border-white/5 rounded-2xl p-3 hover:bg-white/10 transition-colors">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-300">{{ loc.location }}</span>
                    <span class="text-[8px] font-black text-emerald-400">{{ loc.nodes }} Nodes</span>
                </div>
                <div class="h-1 bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 transition-all duration-1000" :style="{ width: loc.load + '%' }"></div>
                </div>
                <div class="flex justify-between mt-1.5">
                    <span class="text-[7px] font-bold text-slate-600 uppercase">Load Balance</span>
                    <span class="text-[7px] font-black text-slate-400">{{ loc.load }}%</span>
                </div>
            </div>
        </div>

        <!-- Service Health -->
        <div class="mt-6 pt-4 border-t border-white/5 grid grid-cols-2 gap-2 relative z-10">
            <div v-for="svc in health.services" :key="svc.name" class="flex items-center gap-2">
                <div class="w-1.5 h-1.5 rounded-full" :class="svc.status === 'healthy' ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse'"></div>
                <span class="text-[7px] font-black text-slate-500 uppercase truncate">{{ svc.name }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { CpuChipIcon } from '@heroicons/vue/24/outline';

defineProps({
    telemetry: { type: Object, default: () => ({ active: 0, total: 0, topology: [] }) },
    health: { type: Object, default: () => ({ cpu: 0, memory: 0, storage: 0, services: [] }) },
    uptime: { type: String, default: '99.9%' }
});
</script>
