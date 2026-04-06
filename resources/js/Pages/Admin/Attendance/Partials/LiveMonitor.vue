<template>
    <div class="space-y-12 animate-fade-in relative z-10">
        <!-- Live Command Intelligence Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all duration-300">
            <div>
                <h2 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-3 uppercase">
                    <span class="w-10 h-10 bg-emerald-600 rounded-lg flex items-center justify-center text-white shadow-sm">
                        <i class="fas fa-radar text-[14px]"></i>
                    </span>
                    LIVE_COMMAND_OS
                </h2>
                <div class="flex items-center gap-3 mt-2">
                    <div class="flex items-center gap-2 px-2.5 py-0.5 bg-emerald-50 rounded-md border border-emerald-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-sm font-black text-emerald-600 uppercase tracking-widest">Signal Active</span>
                    </div>
                    <span class="text-slate-400 font-black text-sm uppercase tracking-[0.2em]">{{ formatDate() }}</span>
                </div>
            </div>
            
            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="relative group flex-1 md:flex-none">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm group-focus-within:text-emerald-500 transition-colors"></i>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="IDENTIFY ENTITY..." 
                        class="w-full md:w-64 bg-slate-50 border border-slate-200 rounded-xl py-2 pl-10 pr-4 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all placeholder:text-slate-300 uppercase tracking-widest"
                    />
                </div>
                <button @click="fetchData" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-emerald-600 bg-white rounded-xl border border-slate-200 shadow-sm transition-all active:scale-95 group shrink-0">
                    <i class="fas fa-arrows-rotate text-[14px]" :class="{'animate-spin text-emerald-500': loading}"></i>
                </button>
            </div>
        </div>

        <!-- Tactical Stats Matrix -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-10 h-10 bg-slate-50 border border-slate-100 rounded-lg flex items-center justify-center text-slate-400 group-hover:bg-slate-900 group-hover:text-white transition-all duration-300">
                        <i class="fas fa-users-viewfinder text-[14px]"></i>
                    </div>
                    <span class="text-sm font-black text-slate-300 uppercase tracking-widest">Registry</span>
                </div>
                <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Total Payload</p>
                <div class="text-3xl font-black text-slate-800 tracking-tighter tabular-nums">{{ stats.total }} <span class="text-sm text-slate-300 ml-1 uppercase">Units</span></div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-10 h-10 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center justify-center text-emerald-500 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                        <i class="fas fa-signal-stream text-[14px]"></i>
                    </div>
                    <div class="flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </div>
                </div>
                <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Active Stream</p>
                <div class="text-3xl font-black text-emerald-600 tracking-tighter tabular-nums">{{ stats.active }} <span class="text-sm text-emerald-400 ml-1 uppercase">Live</span></div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-10 h-10 bg-amber-50 border border-amber-100 rounded-lg flex items-center justify-center text-amber-500 group-hover:bg-amber-600 group-hover:text-white transition-all duration-300">
                        <i class="fas fa-bolt-lightning text-[14px]"></i>
                    </div>
                    <span class="text-sm font-black text-amber-500 uppercase tracking-widest">Latency</span>
                </div>
                <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Late Arrivals</p>
                <div class="text-3xl font-black text-amber-500 tracking-tighter tabular-nums">{{ stats.late }} <span class="text-sm text-amber-500 ml-1 uppercase italic font-mono">Lag</span></div>
            </div>
            
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-10 h-10 bg-rose-50 border border-rose-100 rounded-lg flex items-center justify-center text-rose-500 group-hover:bg-rose-600 group-hover:text-white transition-all duration-300">
                        <i class="fas fa-clock-slash text-[14px]"></i>
                    </div>
                    <span class="text-sm font-black text-rose-500 uppercase tracking-widest">Off-Grid</span>
                </div>
                <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Absentee Pool</p>
                <div class="text-3xl font-black text-rose-500 tracking-tighter tabular-nums">{{ stats.total - stats.present }} <span class="text-sm text-rose-400 ml-1 uppercase italic font-mono">Null</span></div>
            </div>
        </div>

        <!-- Neural Entity Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="emp in processedEmployees" :key="emp.id" 
                 class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-emerald-500 group overflow-hidden relative transition-all duration-300">
                
                <!-- Telemetry Progress Header -->
                <div class="h-1 bg-slate-100/50 w-full overflow-hidden flex">
                    <div class="h-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)] transition-all duration-1000 ease-out" :style="{ width: emp.progress + '%' }"></div>
                </div>

                <div class="p-6">
                    <!-- Entity Profile -->
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex items-center gap-4">
                            <div class="relative group/avatar shrink-0">
                                <div class="absolute inset-x-0 inset-y-0 bg-emerald-500 rounded-xl blur-lg opacity-0 group-hover/avatar:opacity-20 transition-opacity"></div>
                                <img v-if="emp.avatar" :src="`/storage/${emp.avatar}`" class="w-12 h-12 rounded-xl object-cover border-2 border-white shadow-md relative z-10" />
                                <div v-else class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white font-black text-sm shadow-md relative z-10">
                                    {{ emp.name.charAt(0) }}
                                </div>
                                <!-- Neural Pulse Indicator -->
                                <div v-if="emp.status.includes('Active')" class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full z-20 shadow-sm">
                                    <span class="absolute inset-0 rounded-full bg-emerald-500 animate-pulse opacity-75"></span>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-black text-slate-800 text-lg tracking-tight group-hover:text-emerald-700 transition-colors uppercase truncate leading-tight">{{ emp.name }}</h4>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-0.5 truncate">{{ emp.designation || emp.department }}</p>
                            </div>
                        </div>
                        <!-- Operational Status -->
                        <div class="flex flex-col items-end gap-1.5 shrink-0 ml-2">
                            <span class="px-2.5 py-0.5 rounded-md text-xs font-black border uppercase tracking-widest transition-all duration-300 shadow-sm" :class="getStatusStyles(emp.status)">
                                {{ emp.status }}
                            </span>
                            <span class="text-xs font-black text-slate-300 uppercase font-mono tracking-tighter tabular-nums">NODE_ID: {{ emp.id.toString().padStart(4, '0') }}</span>
                        </div>
                    </div>
                    
                    <!-- Telemetry Matrix -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-slate-50/50 p-3 rounded-xl border border-slate-100 group-hover:bg-emerald-50/30 group-hover:border-emerald-100 transition-all duration-300">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1.5 flex items-center gap-1.5 leading-none">
                                <i class="fas fa-calendar-day text-sm text-emerald-300"></i>
                                Protocol
                            </p>
                            <p class="text-sm font-black text-slate-700 truncate uppercase tabular-nums leading-none">{{ emp.shift }}</p>
                        </div>
                        <div class="bg-slate-50/50 p-3 rounded-xl border border-slate-100 group-hover:bg-emerald-50/30 group-hover:border-emerald-100 transition-all duration-300">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1.5 flex items-center gap-1.5 leading-none">
                                <i class="fas fa-right-to-bracket text-sm text-emerald-300"></i>
                                Sync_In
                            </p>
                            <p class="text-sm font-black text-slate-700 font-mono tabular-nums leading-none">{{ emp.check_in }}</p>
                        </div>
                        <div class="bg-slate-50/50 p-3 rounded-xl border border-slate-100 group-hover:bg-emerald-50/30 group-hover:border-emerald-100 transition-all duration-300">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1.5 flex items-center gap-1.5 leading-none">
                                <i class="fas fa-hourglass-half text-sm text-emerald-300"></i>
                                Uptime
                            </p>
                            <p class="text-base font-black text-emerald-600 font-mono tracking-tighter leading-none tabular-nums">{{ emp.work_duration_human }}</p>
                        </div>
                         <div class="bg-slate-50/50 p-3 rounded-xl border border-slate-100 group-hover:bg-emerald-50/30 group-hover:border-emerald-100 transition-all duration-300 flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1.5 flex items-center gap-1.5 leading-none">
                                    <i class="fas fa-microchip text-sm text-slate-300"></i>
                                    Module
                                </p>
                                <p class="text-sm font-black text-slate-700 truncate uppercase leading-none">{{ emp.device }}</p>
                            </div>
                            <div class="w-7 h-7 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-300 group-hover:text-emerald-400 transition-colors shrink-0">
                                <i v-if="emp.device === 'Mobile'" class="fas fa-mobile-screen-button text-sm"></i>
                                <i v-else class="fas fa-desktop text-sm"></i>
                            </div>
                        </div>
                    </div>

                     <!-- Hardware Signature -->
                     <div class="mt-6 flex items-center justify-between text-xs font-black text-slate-300 border-t border-slate-100 pt-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5">
                                <span class="w-1 h-1 bg-emerald-500 rounded-full animate-pulse"></span>
                                <span class="uppercase tracking-widest">NET_ADDR: {{ emp.ip }}</span>
                            </div>
                        </div>
                        <div class="px-2 py-0.5 bg-slate-50 rounded-md group-hover:bg-emerald-50 group-hover:text-emerald-500 transition-all tabular-nums">
                            SYNC_LOAD: {{ emp.progress }}%
                        </div>
                     </div>
                </div>
            </div>
        </div>
        
        <!-- Null State -->
         <div v-if="employees.length === 0 && !loading" class="flex flex-col items-center justify-center py-40 bg-white/40 backdrop-blur-xl rounded-[64px] border border-white/50 border-dashed animate-pulse">
            <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-10 text-slate-200">
                <i class="fas fa-user-slash text-4xl"></i>
            </div>
            <p class="text-xl font-black text-slate-300 uppercase tracking-[0.4em]">Signal Flatline</p>
            <p class="text-base font-black text-slate-400 uppercase tracking-widest mt-4">Awaiting entity synchronization events...</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';

const stats = ref({ total: 0, present: 0, active: 0, late: 0 });
const employees = ref([]);
const loading = ref(false);
const search = ref('');
let timer = null;

const fetchData = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/admin/attendance/monitoring/data');
        employees.value = res.data.employees;
        stats.value = res.data.stats;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const processedEmployees = computed(() => {
    if (!search.value) return employees.value;
    const lower = search.value.toLowerCase();
    return employees.value.filter(e => 
        e.name.toLowerCase().includes(lower) || 
        e.department?.toLowerCase().includes(lower)
    );
});

const formatDate = () => {
    return new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }).toUpperCase();
};

onMounted(() => {
    fetchData();
    timer = setInterval(fetchData, 30000); // Live refresh every 30s
});

onUnmounted(() => clearInterval(timer));

const getStatusStyles = (status) => {
    if (status.includes('Active')) return 'text-emerald-700 bg-emerald-50 border-emerald-200 shadow-emerald-500/10';
    if (status.includes('Late')) return 'text-amber-700 bg-amber-50 border-amber-200 shadow-amber-500/10';
    if (status.includes('Absent')) return 'text-slate-500 bg-slate-50 border-slate-200';
    return 'text-emerald-700 bg-emerald-50 border-emerald-200 shadow-emerald-500/10';
};
</script>
