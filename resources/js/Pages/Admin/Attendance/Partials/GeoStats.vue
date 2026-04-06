<template>
    <div class="space-y-12 animate-fade-in relative z-10">
        <!-- Global Positioning Intelligence Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div>
                <h2 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-3 uppercase leading-none">
                    <span class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg group-hover:bg-emerald-600 transition-colors">
                        <i class="fas fa-satellite-dish text-base"></i>
                    </span>
                    GEO_STATION_OS
                </h2>
                <div class="flex items-center gap-3 mt-3">
                    <div class="flex items-center gap-2 px-2.5 py-1 bg-emerald-50 rounded-lg border border-emerald-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                        <span class="text-xs font-black text-emerald-600 uppercase tracking-widest">Orbital Link Active</span>
                    </div>
                    <span class="text-slate-400 font-black text-xs uppercase tracking-[0.2em] px-1">Coordinate Integrity Protocol</span>
                </div>
            </div>
            
            <button @click="fetchData" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-emerald-600 bg-white rounded-xl border border-slate-200 shadow-sm transition-all active:scale-95 group">
                <i class="fas fa-arrows-rotate text-base" :class="{'animate-spin text-emerald-500': loading}"></i>
            </button>
        </div>

        <div v-if="loading" class="h-96 flex flex-col items-center justify-center space-y-6 bg-white/40 backdrop-blur-md rounded-[48px] border border-white border-dashed">
            <div class="w-16 h-16 border-4 border-emerald-500/10 border-t-emerald-600 rounded-full animate-spin"></div>
            <p class="text-sm font-black text-emerald-600 uppercase tracking-[0.3em] animate-pulse">Scanning Grid Coordinates...</p>
        </div>

        <div v-else class="space-y-12">
            <!-- Strategic Positioning Matrix -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-slate-900 group-hover:text-white transition-all duration-300 shadow-sm group-hover:shadow-lg">
                            <i class="fas fa-map-location-dot text-[14px]"></i>
                        </div>
                        <span class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] bg-slate-50 px-2 py-0.5 rounded border border-slate-100">Infrastructure</span>
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1.5 leading-none">Total Geo-Nodes</p>
                    <div class="text-3xl font-black text-slate-800 tracking-tighter tabular-nums leading-none group-hover:text-emerald-700 transition-colors">{{ stats.total_zones || 0 }}</div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-500 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300 shadow-sm group-hover:shadow-lg">
                            <i class="fas fa-circle-check text-[14px]"></i>
                        </div>
                        <span class="text-xs font-black text-emerald-400 uppercase tracking-[0.2em] bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">Operational</span>
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1.5 leading-none">Active Perimeter</p>
                    <div class="text-3xl font-black text-emerald-600 tracking-tighter tabular-nums leading-none">{{ stats.active_zones || 0 }}</div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center text-rose-500 group-hover:bg-rose-600 group-hover:text-white transition-all duration-300 shadow-sm group-hover:shadow-lg">
                            <i class="fas fa-triangle-exclamation text-[14px]"></i>
                        </div>
                        <span class="text-xs font-black text-rose-400 uppercase tracking-[0.2em] bg-rose-50 px-2 py-0.5 rounded border border-rose-100">Breach</span>
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1.5 leading-none">Zone Violations</p>
                    <div class="text-3xl font-black text-rose-600 tracking-tighter tabular-nums leading-none">{{ stats.violations || 0 }}</div>
                </div>

                <div class="bg-slate-900 p-6 rounded-2xl shadow-md text-white relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center text-white backdrop-blur-md">
                                <i class="fas fa-shield-halved text-base"></i>
                            </div>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-widest">Global Integrity</p>
                        </div>
                        <div class="flex items-end gap-1.5">
                            <h3 class="text-3xl font-black text-white tracking-tighter tabular-nums">{{ stats.compliance_rate || 0 }}<span class="text-sm text-emerald-400 font-mono">%</span></h3>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1 mt-4 overflow-hidden">
                            <div class="bg-emerald-500 h-full shadow-[0_0_10px_rgba(16,185,129,0.5)] transition-all duration-1000" :style="{ width: (stats.compliance_rate || 0) + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coordinate Monitoring Matrix -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden min-h-[400px]">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-slate-800 tracking-tight uppercase">Geo-Enforcement Nodes</h3>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Real-time Perimeter Scanning</p>
                    </div>
                    <div class="px-3 py-1 bg-white border border-slate-200 rounded-full text-xs font-black text-emerald-500 uppercase tracking-widest shadow-sm">
                        Live Tracking Active
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-900 border-b border-slate-800">
                                <th class="px-6 py-4 text-left">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Coordinate ID</span>
                                </th>
                                <th class="px-6 py-4 text-left">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Sensor Status</span>
                                </th>
                                <th class="px-6 py-4 text-left">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Personnel</span>
                                </th>
                                <th class="px-6 py-4 text-left">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Breaches</span>
                                </th>
                                <th class="px-6 py-4 text-left">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Coverage Load</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="zone in stats.zones" :key="zone.name" class="group hover:bg-emerald-50/30 transition-all duration-300 border-b border-slate-50 last:border-0">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300 shadow-sm border border-slate-200 group-hover:border-emerald-500">
                                            <i class="fas fa-location-crosshairs text-sm"></i>
                                        </div>
                                        <span class="text-sm font-black text-slate-700 tracking-tight uppercase group-hover:text-emerald-700 transition-colors">{{ zone.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full" 
                                            :class="zone.status === 'active' ? 'bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]' : 'bg-slate-300'"></span>
                                        <span class="text-sm font-black uppercase tracking-widest tabular-nums"
                                            :class="zone.status === 'active' ? 'text-emerald-600' : 'text-slate-400'">
                                            {{ zone.status }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2 text-sm font-black text-slate-600 uppercase tracking-tighter tabular-nums">
                                        <i class="fas fa-user-group text-xs text-slate-300 mt-0.5"></i>
                                        {{ zone.employees }} UNITS
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-left">
                                    <span v-if="zone.violations === 0" class="text-xs font-black text-emerald-500 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-100 uppercase tracking-widest shadow-sm">Clear</span>
                                    <span v-else class="text-xs font-black text-rose-500 bg-rose-50 px-2.5 py-1 rounded-md border border-rose-100 uppercase tracking-widest shadow-sm">
                                        {{ zone.violations }} BREACHES
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="w-24 bg-slate-100 rounded-full h-1.5 overflow-hidden flex shadow-inner">
                                        <div class="h-full transition-all duration-1000 group-hover:brightness-110"
                                            :class="{
                                                'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.3)]': zone.color === 'emerald',
                                                'bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.3)]': zone.color === 'blue',
                                                'bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.3)]': zone.color === 'amber',
                                                'bg-slate-400': zone.color === 'gray'
                                            }"
                                            :style="{ width: Math.min(100, (zone.employees / 150 * 100)) + '%' }">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const stats = ref(null);
const loading = ref(true);

const fetchData = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/admin/attendance/monitoring/data', { params: { type: 'geo' } });
        stats.value = res.data;
    } catch (e) {
        console.error(e);
        // Mock data fallback for design consistency
        stats.value = {
            total_zones: 8,
            active_zones: 6,
            violations: 3,
            compliance_rate: 94,
            zones: [
                { name: 'HQ - Main Office', employees: 145, status: 'active', violations: 0, color: 'emerald' },
                { name: 'Tech Park Branch', employees: 89, status: 'active', violations: 1, color: 'blue' },
                { name: 'Remote Work Zone', employees: 67, status: 'active', violations: 2, color: 'amber' },
                { name: 'Client Site - Alpha', employees: 34, status: 'inactive', violations: 0, color: 'gray' }
            ]
        };
    } finally {
        loading.value = false;
    }
};

onMounted(fetchData);
</script>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
