<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';

const stats = ref({ noShows: 0, byProvider: [], trends: [] });

onMounted(async () => {
    const res = await axios.get('/api/meetings/analytics');
    stats.value = res.data;
});
</script>

<template>
    <div class="space-y-8 animate-in fade-in duration-500">
        <header>
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">Meeting Intelligence</h2>
            <p class="text-gray-400 font-bold uppercase tracking-widest text-[9px] mt-1">High-Density Performance Metrics</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-8 rounded-[40px] border border-gray-50 shadow-sm flex flex-col justify-between">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Global No-Shows</p>
                <h3 class="text-5xl font-black text-indigo-600 mt-4">{{ stats.noShows }}</h3>
                <p class="text-[9px] font-bold text-gray-300 mt-2 uppercase">System-wide monitoring active</p>
            </div>
            
            <div v-for="p in stats.byProvider" :key="p.provider" class="bg-white p-8 rounded-[40px] border border-gray-100 shadow-sm">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ p.provider }} Utilization</p>
                <h3 class="text-4xl font-black text-gray-900 mt-4">{{ p.count }} <span class="text-xs text-gray-400 font-black">SESSIONS</span></h3>
            </div>
        </div>

        <div class="bg-gray-900 rounded-[40px] p-10 text-white relative overflow-hidden shadow-2xl">
             <i class="fas fa-chart-line absolute -bottom-10 -right-10 text-[200px] text-white/5 rotate-12"></i>
             <h4 class="text-2xl font-black mb-6">Velocity Trends</h4>
             <div class="flex items-end gap-2 h-48">
                 <div v-for="t in stats.trends" :key="t.date" :style="{ height: (t.count * 20) + 'px' }" class="flex-1 bg-white/20 rounded-t-xl hover:bg-indigo-400 transition-all cursor-crosshair group relative">
                    <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-white text-gray-900 rounded-lg px-3 py-1 text-[9px] font-black opacity-0 group-hover:opacity-100 transition-all shadow-xl">
                        {{ t.count }} Meetings
                    </div>
                 </div>
             </div>
        </div>
    </div>
</template>
