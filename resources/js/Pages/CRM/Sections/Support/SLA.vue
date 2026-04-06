<template>
    <div class="space-y-8 text-left">
        <!-- SLA Intelligence -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div v-for="stat in slaStats" :key="stat.label" 
                 class="bg-white p-10 rounded-[40px] shadow-sm border border-gray-100 group hover:shadow-2xl transition-all relative overflow-hidden">
                <div :class="['absolute -right-4 -top-4 w-28 h-28 rounded-full opacity-10 group-hover:scale-150 transition-transform duration-700', stat.color]"></div>
                <div class="relative">
                    <div class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mb-4">{{ stat.label }}</div>
                    <div class="text-4xl font-black text-gray-900 tracking-tighter">{{ stat.value }}</div>
                    <div :class="['mt-6 flex items-center text-sm font-black uppercase tracking-widest', stat.trendUp ? 'text-emerald-500' : 'text-rose-400']">
                        <i :class="['fas mr-2', stat.trendUp ? 'fa-bolt' : 'fa-clock']"></i>
                        {{ stat.trend }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Compliance & Performance -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Service Level Matrix -->
            <div class="bg-slate-900 rounded-[50px] p-12 text-white shadow-2xl relative overflow-hidden group">
                 <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-white/5 rounded-full blur-3xl group-hover:bg-white/10 transition-all duration-700"></div>
                 <div class="relative space-y-8">
                    <div class="flex justify-between items-center">
                        <h3 class="text-2xl font-black tracking-tight">Service Level Matrix</h3>
                        <span class="text-sm font-black uppercase tracking-widest opacity-60">Live Compliance</span>
                    </div>
                    
                    <div class="space-y-6">
                        <div v-for="level in slaLevels" :key="level.name" class="space-y-3">
                            <div class="flex justify-between text-base font-black uppercase tracking-widest opacity-70">
                                <span>{{ level.name }} Compliance</span>
                                <span :class="level.percent < 95 ? 'text-amber-400' : 'text-emerald-400'">{{ level.percent }}%</span>
                            </div>
                            <div class="h-1.5 bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-white rounded-full transition-all duration-1000" :style="`width: ${level.percent}%`"></div>
                            </div>
                        </div>
                    </div>
                    <button class="w-full py-4 bg-white/5 border border-white/10 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-white/10 transition-all">RECONFIGURE RESPONSE THRESHOLDS</button>
                 </div>
            </div>

            <!-- Resolution Velocity -->
            <div class="bg-white rounded-[50px] p-12 shadow-sm border border-gray-100 flex flex-col justify-between relative group overflow-hidden">
                <i class="fas fa-history absolute -right-4 -top-4 text-[150px] text-gray-50 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></i>
                <div class="relative">
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight mb-10">Resolution Velocity</h3>
                    <div class="space-y-8">
                        <div class="flex items-center justify-between p-6 bg-gray-50 rounded-[30px] border border-gray-100">
                            <div>
                                <span class="text-sm font-black text-gray-400 uppercase tracking-widest">Initial Latency (FRT)</span>
                                <p class="text-2xl font-black text-gray-800 tracking-tighter mt-1">12.4m</p>
                            </div>
                            <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-emerald-500 shadow-sm border border-gray-100">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-6 bg-gray-50 rounded-[30px] border border-gray-100">
                            <div>
                                <span class="text-sm font-black text-gray-400 uppercase tracking-widest">Deep Resolve (TTR)</span>
                                <p class="text-2xl font-black text-gray-800 tracking-tighter mt-1">1.8h</p>
                            </div>
                            <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-sky-500 shadow-sm border border-gray-100">
                                <i class="fas fa-rocket"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pt-8 text-sm font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                     <i class="fas fa-info-circle text-sky-500"></i>
                     Performance is 15% above quarterly benchmark.
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const slaStats = ref([
    { label: 'SLA COMPLIANCE', value: '0%', color: 'bg-emerald-500', trend: 'Global Standard', trendUp: true },
    { label: 'AVG RESPONSE', value: '0m', color: 'bg-sky-500', trend: 'Live Feed', trendUp: true },
    { label: 'AVG RESOLUTION', value: '0h', color: 'bg-indigo-500', trend: 'Efficiency', trendUp: true },
    { label: 'CRITICAL ESCALATIONS', value: '0', color: 'bg-rose-500', trend: 'Clear Desk', trendUp: true },
]);

const slaLevels = ref([]);
const loading = ref(true);

const fetchSLAStats = async () => {
    try {
        const response = await axios.get(route('crm.tickets.sla-stats'));
        const data = response.data;

        slaStats.value[0].value = data.compliance + '%';
        slaStats.value[1].value = data.avg_response;
        slaStats.value[2].value = data.avg_resolution;
        slaStats.value[3].value = data.breaches.toString();

        slaLevels.value = data.levels;
    } catch (error) {
        console.error('Failed to fetch SLA stats', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchSLAStats();
});
</script>
