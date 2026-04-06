<template>
    <div class="space-y-12 animate-fade-in relative z-10">
        <!-- Core Infrastructure Intelligence Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div>
                <h2 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-3 uppercase leading-none">
                    <span class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg group-hover:bg-emerald-600 transition-colors">
                        <i class="fas fa-microchip text-base"></i>
                    </span>
                    INFRA_TERMINAL_OS
                </h2>
                <div class="flex items-center gap-3 mt-3">
                    <div class="flex items-center gap-2 px-2.5 py-1 bg-emerald-50 rounded-lg border border-emerald-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                        <span class="text-xs font-black text-emerald-600 uppercase tracking-widest">Network Backbone Active</span>
                    </div>
                    <span class="text-slate-400 font-black text-xs uppercase tracking-[0.2em] px-1">Hardware Integrity Protocol</span>
                </div>
            </div>
            
            <button @click="fetchData" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-emerald-600 bg-white rounded-xl border border-slate-200 shadow-sm transition-all active:scale-95 group">
                <i class="fas fa-arrows-rotate text-base" :class="{'animate-spin text-emerald-500': loading}"></i>
            </button>
        </div>

        <div v-if="loading" class="h-96 flex flex-col items-center justify-center space-y-6 bg-white/40 backdrop-blur-md rounded-[48px] border border-white border-dashed">
            <div class="w-16 h-16 border-4 border-emerald-500/10 border-t-emerald-600 rounded-full animate-spin"></div>
            <p class="text-sm font-black text-emerald-600 uppercase tracking-[0.3em] animate-pulse">Querying Terminal Nodes...</p>
        </div>

        <div v-else class="space-y-12">
            <!-- strategic Infrastructure Matrix -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-slate-900 group-hover:text-white transition-all duration-300 shadow-sm group-hover:shadow-lg">
                            <i class="fas fa-server text-[14px]"></i>
                        </div>
                        <span class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] bg-slate-50 px-2 py-0.5 rounded border border-slate-100">Topology</span>
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1.5 leading-none">Total Terminals</p>
                    <div class="text-3xl font-black text-slate-800 tracking-tighter tabular-nums leading-none group-hover:text-emerald-700 transition-colors">{{ stats.total_devices || 0 }}</div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-500 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300 shadow-sm group-hover:shadow-lg">
                            <i class="fas fa-signal text-[14px]"></i>
                        </div>
                        <span class="text-xs font-black text-emerald-400 uppercase tracking-[0.2em] bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">Linked</span>
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1.5 leading-none">Online Nodes</p>
                    <div class="text-3xl font-black text-emerald-600 tracking-tighter tabular-nums leading-none">{{ stats.active_devices || 0 }}</div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center text-rose-500 group-hover:bg-rose-600 group-hover:text-white transition-all duration-300 shadow-sm group-hover:shadow-lg">
                            <i class="fas fa-triangle-exclamation text-[14px]"></i>
                        </div>
                        <span class="text-xs font-black text-rose-400 uppercase tracking-[0.2em] bg-rose-50 px-2 py-0.5 rounded border border-rose-100">Failure</span>
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1.5 leading-none">Offline Terminals</p>
                    <div class="text-3xl font-black text-rose-600 tracking-tighter tabular-nums leading-none">{{ stats.offline_devices || 0 }}</div>
                </div>

                <div class="bg-slate-900 p-6 rounded-2xl shadow-md text-white relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center text-white backdrop-blur-md">
                                <i class="fas fa-notes-medical text-base"></i>
                            </div>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-widest">System Health</p>
                        </div>
                        <div class="flex items-end gap-1.5">
                            <h3 class="text-3xl font-black text-white tracking-tighter tabular-nums">{{ stats.health_score || 0 }}<span class="text-sm text-emerald-400 font-mono">%</span></h3>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1 mt-4 overflow-hidden">
                            <div class="bg-emerald-500 h-full shadow-[0_0_10px_rgba(16,185,129,0.5)] transition-all duration-1000" :style="{ width: (stats.health_score || 0) + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terminal Inventory Matrix -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-slate-800 tracking-tight uppercase">Terminal Inventory Matrix</h3>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Real-time Hardware Telemetry</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex -space-x-1.5">
                            <div v-for="i in 3" :key="i" class="w-5 h-5 rounded-full bg-white border border-slate-200 flex items-center justify-center text-xs font-black text-slate-400">
                                <i class="fas fa-microchip"></i>
                            </div>
                        </div>
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Nodes Verified</span>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="device in stats.devices" :key="device.name" 
                        class="group p-4 rounded-xl bg-white border border-slate-200 hover:border-emerald-500 hover:shadow-md transition-all duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-sm transition-all duration-300 border shadow-sm"
                                    :class="{
                                        'bg-emerald-50 text-emerald-500 border-emerald-100 group-hover:bg-emerald-600 group-hover:text-white group-hover:border-emerald-500': device.status === 'healthy',
                                        'bg-amber-50 text-amber-500 border-amber-100 group-hover:bg-amber-500 group-hover:text-white group-hover:border-amber-500': device.status === 'warning',
                                        'bg-rose-50 text-rose-500 border-rose-100 group-hover:bg-rose-600 group-hover:text-white group-hover:border-rose-500': device.status === 'offline'
                                    }">
                                    <i :class="device.name.toLowerCase().includes('mobile') ? 'fas fa-mobile-screen' : (device.name.toLowerCase().includes('biometric') ? 'fas fa-fingerprint' : 'fas fa-globe')"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-black text-slate-400 uppercase tracking-widest mb-0.5">Terminal ID</div>
                                    <div class="text-sm font-black text-slate-700 uppercase tracking-tight group-hover:text-emerald-700 transition-colors">{{ device.name }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-black text-slate-400 uppercase tracking-widest mb-0.5">Throughput</div>
                                <div class="text-sm font-black text-slate-800 tabular-nums">{{ device.count }} UNITS</div>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-xs font-black uppercase tracking-widest">
                                <span class="text-slate-400">Operational Load</span>
                                <span :class="{
                                    'text-emerald-500': device.status === 'healthy',
                                    'text-amber-500': device.status === 'warning',
                                    'text-rose-500': device.status === 'offline'
                                }">{{ device.status }}</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1 overflow-hidden shadow-inner">
                                <div class="h-full transition-all duration-1000 group-hover:brightness-110"
                                    :class="{
                                        'bg-emerald-500': device.color === 'emerald',
                                        'bg-emerald-400': device.color === 'blue',
                                        'bg-amber-500': device.color === 'amber',
                                        'bg-rose-500': device.color === 'red'
                                    }"
                                    :style="{ width: Math.min(100, (device.count / 150 * 100)) + '%' }">
                                </div>
                            </div>
                        </div>
                    </div>
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
        const res = await axios.get('/admin/attendance/monitoring/data', { params: { type: 'device' } });
        stats.value = res.data;
    } catch (e) {
        console.error(e);
        // Mock data fallback for design demonstration
        stats.value = {
            total_devices: 24,
            active_devices: 18,
            offline_devices: 6,
            health_score: 75,
            devices: [
                { name: 'Mobile App Node', count: 145, status: 'healthy', color: 'emerald' },
                { name: 'Edge Browser Terminal', count: 89, status: 'healthy', color: 'blue' },
                { name: 'Biometric Scanner Alpha', count: 45, status: 'warning', color: 'amber' },
                { name: 'Gate Entry Sensor 02', count: 12, status: 'offline', color: 'red' }
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
