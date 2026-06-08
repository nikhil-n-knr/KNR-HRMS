<template>
    <MainLayout>
        <Head title="Performance Intelligence" />
        
        <div class="space-y-10 pb-20 font-outfit animate-in fade-in slide-in-from-bottom-5 duration-700">
            <!-- Specialized Intelligence Header -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8 bg-slate-900 rounded-[2.5rem] p-10 shadow-2xl shadow-indigo-500/20 relative overflow-hidden group">
                <div class="absolute -right-16 -top-16 w-64 h-64 bg-indigo-500/10 rounded-full blur-[100px] group-hover:bg-indigo-500/20 transition-all duration-1000"></div>
                
                <div class="flex items-center gap-6 relative z-10">
                    <div class="w-16 h-16 bg-white/10 backdrop-blur-xl rounded-2xl flex items-center justify-center text-indigo-400 border border-white/10 shadow-2xl group-hover:rotate-6 transition-transform">
                        <ChartBarIcon class="w-10 h-10" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-white uppercase tracking-tight flex items-center gap-4">
                            Performance Intelligence
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-black bg-white/10 text-indigo-400 border border-white/5 uppercase tracking-[0.3em] backdrop-blur-md">Audit_Matrix</span>
                        </h1>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] mt-2.5 flex items-center gap-2">
                            <CpuChipIcon class="w-4 h-4 text-indigo-500" />
                            Real-time efficiency monitoring & strategic contract analytics
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 relative z-10">
                    <div class="px-6 py-3 bg-white/5 rounded-2xl border border-white/5 backdrop-blur-xl shrink-0">
                        <span class="text-sm font-black text-slate-500 uppercase tracking-widest block mb-1">Global Efficiency Index</span>
                        <div class="text-xl font-black text-emerald-400 tabular-nums">94.2%</div>
                    </div>
                </div>
            </div>

            <!-- Performance Grid Terminal -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/40 overflow-hidden relative group">
                <div class="absolute inset-0 bg-gradient-to-br from-slate-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                
                <div class="overflow-x-auto relative z-10">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900 border-b border-slate-800">
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Partner Node</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">SLA Threshold</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Avg Turnaround (RT)</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Resource Load (Cost)</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Intelligence Score</th>
                                <th class="px-8 py-6 text-right text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Node Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="vendor in vendors.data" :key="vendor.id" class="group/row hover:bg-slate-50/80 transition-all duration-300">
                                <td class="px-8 py-7">
                                    <div class="flex items-center gap-5">
                                        <div class="w-12 h-12 bg-slate-950 rounded-2xl flex items-center justify-center text-white font-black text-sm border-2 border-white shadow-xl flex-shrink-0 group-hover/row:bg-indigo-600 transition-all">
                                            {{ vendor.name[0] }}
                                        </div>
                                        <div>
                                            <div class="text-lg font-black text-slate-900 uppercase tracking-tight truncate max-w-[150px] group-hover/row:text-indigo-700 transition-colors">{{ vendor.name }}</div>
                                            <div class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1.5 opacity-60 flex items-center gap-2 leading-none italic">
                                                <CalendarIcon class="w-3 h-3" />
                                                Expires: {{ vendor.contract_end_date || 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-7">
                                    <div class="text-base font-black text-slate-900 tabular-nums uppercase tracking-widest leading-none">
                                        {{ vendor.sla_response_hours }} hrs_max
                                    </div>
                                </td>
                                <td class="px-8 py-7">
                                    <div class="flex items-center gap-3">
                                        <div class="text-base font-black tabular-nums transition-transform group-hover/row:scale-110" :class="vendor.avg_turnaround_time > vendor.sla_response_hours ? 'text-rose-500' : 'text-emerald-500'">
                                            {{ vendor.avg_turnaround_time }} HRS
                                        </div>
                                        <BoltIcon v-if="vendor.avg_turnaround_time <= vendor.sla_response_hours" class="w-4 h-4 text-emerald-400 animate-pulse" />
                                    </div>
                                </td>
                                <td class="px-8 py-7">
                                    <div class="text-lg font-black text-slate-900 font-mono tracking-tighter tabular-nums leading-none">
                                        ₹{{ vendor.avg_repair_cost }}
                                    </div>
                                </td>
                                <td class="px-8 py-7">
                                    <div class="flex flex-col gap-3">
                                        <div class="flex items-center justify-between text-sm font-black px-1 leading-none uppercase tracking-widest">
                                            <span :class="vendor.score >= 90 ? 'text-emerald-500' : 'text-slate-400'">{{ vendor.score }}% Efficiency</span>
                                            <span class="text-slate-300 font-mono opacity-50">{{ vendor.score < 80 ? 'CRITICAL' : 'OPTIMAL' }}</span>
                                        </div>
                                        <div class="w-48 h-2 bg-slate-100 rounded-full overflow-hidden shadow-inner border border-slate-200/50">
                                            <div class="h-full rounded-full transition-all duration-1000 shadow-sm" 
                                                :style="`width: ${vendor.score}%`"
                                                :class="{
                                                    'bg-gradient-to-r from-emerald-400 to-emerald-600': vendor.score >= 90,
                                                    'bg-gradient-to-r from-indigo-400 to-indigo-600': vendor.score >= 80 && vendor.score < 90,
                                                    'bg-gradient-to-r from-amber-400 to-amber-600': vendor.score < 80
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-7 text-right">
                                    <div class="flex justify-end">
                                        <div v-if="vendor.is_active" class="px-4 py-1.5 bg-emerald-950 text-emerald-400 rounded-xl text-xs font-black uppercase tracking-[0.2em] shadow-xl shadow-emerald-500/10 flex items-center gap-2 border border-emerald-500/20">
                                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></div>
                                            Active_Runtime
                                        </div>
                                        <div v-else class="px-4 py-1.5 bg-slate-100 text-slate-400 rounded-xl text-xs font-black uppercase tracking-[0.2em] flex items-center gap-2 border border-slate-200">
                                            Segment_Locked
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Intelligent Insights Card -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white shadow-2xl shadow-indigo-500/30 relative overflow-hidden group">
                    <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-white/10 rounded-full blur-3xl transition-transform duration-1000 group-hover:scale-110"></div>
                    <div class="flex items-start gap-6 relative z-10">
                        <div class="w-14 h-14 bg-white/10 border border-white/20 rounded-2xl flex items-center justify-center text-indigo-200 shadow-xl group-hover:rotate-12 transition-transform">
                            <LightBulbIcon class="w-8 h-8" />
                        </div>
                        <div>
                            <h4 class="text-xl font-black uppercase tracking-tight mb-2">Efficiency Insight</h4>
                            <p class="text-base font-black text-indigo-100 uppercase tracking-widest leading-relaxed opacity-80 italic">
                                " Cross-node performance analytics indicate a 12.4% latency increase in standard repair segments. Recommendation: Redistribute resource load to high-score partners. "
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-2xl shadow-slate-900/30 relative overflow-hidden group">
                    <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl transition-transform duration-1000 group-hover:scale-110"></div>
                    <div class="flex items-start gap-6 relative z-10">
                        <div class="w-14 h-14 bg-white/10 border border-white/20 rounded-2xl flex items-center justify-center text-emerald-400 shadow-xl group-hover:-rotate-12 transition-transform">
                            <ShieldCheckIcon class="w-8 h-8" />
                        </div>
                        <div>
                            <h4 class="text-xl font-black uppercase tracking-tight mb-2">Compliance Alert</h4>
                            <p class="text-base font-black text-slate-400 uppercase tracking-widest leading-relaxed opacity-80 italic">
                                " Three partners are approaching contract Omega within T-minus 30 days. Automated re-engagement protocol initialized. "
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head } from '@inertiajs/vue3';
import { 
    ChartBarIcon, 
    CpuChipIcon, 
    CalendarIcon, 
    BoltIcon, 
    LightBulbIcon, 
    ShieldCheckIcon,
    BuildingOfficeIcon
} from '@heroicons/vue/24/outline';

defineProps({
    vendors: Object
});
</script>

<style scoped>
.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
</style>
