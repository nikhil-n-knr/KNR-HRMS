<template>
    <div class="bg-white/40 backdrop-blur-xl border border-white/40 rounded-3xl p-5 shadow-xl h-full flex flex-col group relative overflow-hidden transition-all hover:shadow-2xl">
        <!-- Decoration -->
        <div class="absolute -bottom-12 -left-12 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl"></div>

        <div class="flex justify-between items-center mb-6 relative z-10">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-2">
                <ClockIcon class="w-4 h-4 text-emerald-600" />
                Attendance_Telemetry
            </h3>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold text-orange-500 flex items-center gap-1">
                    <i class="fas fa-fire animate-bounce"></i> {{ data.streak || 0 }}D Streak
                </span>
            </div>
        </div>

        <div v-if="loading" class="flex-grow flex flex-col gap-6 animate-pulse">
            <div class="h-16 bg-emerald-100/30 rounded-2xl"></div>
            <div class="h-24 bg-emerald-100/10 rounded-2xl"></div>
        </div>

        <div v-else class="flex-grow space-y-6 relative z-10">
            <!-- Score & Dial -->
            <div class="flex items-center gap-6">
                <div class="relative w-16 h-16 flex items-center justify-center">
                    <svg class="w-full h-full -rotate-90">
                        <circle cx="50%" cy="50%" r="40%" stroke="currentColor" stroke-width="3" fill="transparent" class="text-slate-100" />
                        <circle cx="50%" cy="50%" r="40%" stroke="currentColor" stroke-width="4" fill="transparent" class="text-emerald-500" :stroke-dasharray="125" :stroke-dashoffset="125 - (125 * parseInt(data.stat) / 100)" stroke-linecap="round" />
                    </svg>
                    <span class="absolute text-lg font-black text-slate-800 tracking-tighter">{{ data.stat }}</span>
                </div>
                <div>
                    <h4 class="text-sm font-black text-slate-900 border-b border-slate-100 pb-1 uppercase tracking-tight">Active Cycle</h4>
                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Personnel Reliability Index</p>
                </div>
            </div>

            <!-- Coverage Heatmap (Mini) -->
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <p class="text-[7px] font-black text-slate-500 uppercase tracking-widest">Monthly_Coverage_Map</p>
                    <span class="text-[7px] font-bold text-slate-400 uppercase">Feb Cycle</span>
                </div>
                <div class="grid grid-cols-7 gap-1">
                    <div v-for="n in 28" :key="n" 
                         class="aspect-square rounded-[1px] transition-all cursor-help hover:scale-125"
                         :class="n % 7 === 0 ? 'bg-slate-100' : (n < 25 ? 'bg-emerald-500/80 shadow-[0_0_2px_rgba(16,185,129,0.3)]' : 'bg-slate-50 border border-slate-100')">
                    </div>
                </div>
            </div>

            <!-- Recent Logs -->
            <div class="space-y-1.5 pt-2">
                 <div v-for="log in data.recentLogs" :key="log.id" class="flex items-center justify-between py-1 border-b border-slate-50 last:border-0 hover:bg-emerald-50/50 px-1 rounded transition-colors group/log">
                    <span class="text-[9px] font-bold text-slate-600 uppercase tracking-tight">{{ formatDate(log.date) }}</span>
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] font-black text-slate-800">{{ log.time }}</span>
                        <div class="w-1 h-1 rounded-full bg-emerald-500 group-hover/log:animate-ping"></div>
                    </div>
                 </div>
            </div>
        </div>

        <button class="mt-6 w-full py-2.5 bg-slate-900 text-white text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-600 transition-all active:scale-95 shadow-lg">
            Analytics Console
        </button>
    </div>
</template>

<script setup>
import { ClockIcon } from '@heroicons/vue/24/outline';
import { onMounted, ref } from 'vue';
import axios from 'axios';

const loading = ref(true);
const data = ref({ recentLogs: [], stat: '0%', streak: 0 });

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', { day: 'numeric', month: 'short' });
};

onMounted(async () => {
    try {
        const response = await axios.get('/api/employee/dashboard/widgets/attendance');
        data.value = response.data;
    } catch (e) {
        console.error('Failed to load attendance telemetry:', e);
    } finally {
        loading.value = false;
    }
});
</script>
