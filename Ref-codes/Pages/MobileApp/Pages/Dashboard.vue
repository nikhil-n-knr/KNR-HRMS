<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/Pages/MobileApp/App.vue';
import axios from 'axios';

const props = defineProps({
    assignedTasksCount: Number,
    openBugsCount: Number,
    attendanceData: Object,
    tactical: Object
});

const syncing = ref(false);
const geoError = ref(null);

const attendanceStatus = computed(() => {
    if (props.attendanceData.status === 'Offline') return { label: 'Out', color: 'text-rose-500', bg: 'bg-rose-50' };
    return { label: 'Active', color: 'text-emerald-500', bg: 'bg-emerald-50' };
});

const handleAttendance = () => {
    syncing.value = true;
    geoError.value = null;

    if (!navigator.geolocation) {
        geoError.value = "Geolocation not supported";
        syncing.value = false;
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async (pos) => {
            try {
                const action = props.attendanceData.log?.check_in ? 'clock_out' : 'clock_in';
                await axios.post('/api/mobile/v1/attendance/sync', {
                    lat: pos.coords.latitude,
                    lng: pos.coords.longitude,
                    action: action
                });
                router.reload(); // Refresh dashboard state
            } catch (err) {
                geoError.value = err.response?.data?.message || "Sync Protocol Failed";
            } finally {
                syncing.value = false;
            }
        },
        (err) => {
            geoError.value = "Location Permission Required";
            syncing.value = false;
        },
        { enableHighAccuracy: true }
    );
};
</script>

<template>
    <AppLayout>
        <Head title="Matrix Dashboard" />
        
        <div class="px-6 space-y-7 pb-32 pt-4">
            <!-- Strategic Welcome -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Intelligence Hub</h2>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] mt-1 italic">Real-time Operative Overview</p>
                </div>
                <div class="flex flex-col items-end">
                    <span class="text-[10px] font-black text-slate-900 tabular-nums">{{ new Date().toLocaleDateString('en-US', { weekday: 'short', day: 'numeric', month: 'short' }) }}</span>
                    <span class="text-[8px] font-bold text-emerald-600 uppercase tracking-widest mt-0.5">Live Matrix</span>
                </div>
            </div>

            <!-- Strategic Metrics Strip (Tactical Insights) -->
            <div class="grid grid-cols-3 gap-3">
                <div v-for="s in [
                    { label: 'Effic.', val: props.tactical.velocity + '%', color: 'text-emerald-600' },
                    { label: 'Health', val: props.tactical.health + '%', color: 'text-teal-600' },
                    { label: 'Urgency', val: props.tactical.urgency, color: props.tactical.urgency === 'High' ? 'text-rose-600' : 'text-slate-600' }
                ]" :key="s.label" class="nature-card p-3 text-center !rounded-[1.2rem]">
                    <span class="text-[7px] font-black text-slate-400 uppercase tracking-widest block mb-1 leading-none">{{ s.label }}</span>
                    <span :class="s.color" class="text-xs font-black tracking-tighter uppercase tabular-nums">{{ s.val }}</span>
                </div>
            </div>

            <!-- Primary Directives Matrix -->
            <div class="grid grid-cols-2 gap-4">
                <Link :href="route('mobile.tasks')" class="nature-card p-5 relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 w-12 h-12 bg-emerald-500/5 rounded-full group-hover:scale-[3] transition-transform duration-700"></div>
                    <div class="relative z-10 flex flex-col justify-between h-full">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 mb-3 group-hover:rotate-12 transition-transform">
                            <i class="fas fa-list-check text-xs"></i>
                        </div>
                        <span class="stat-label">Active Tasks</span>
                        <div class="flex items-baseline gap-1.5 mt-1">
                            <span class="stat-value">{{ assignedTasksCount }}</span>
                            <span class="text-[8px] font-black text-emerald-400 uppercase">Live</span>
                        </div>
                    </div>
                </Link>

                <div class="nature-card p-5 relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 w-12 h-12 bg-rose-500/5 rounded-full group-hover:scale-[3] transition-transform duration-700"></div>
                    <div class="relative z-10 flex flex-col justify-between h-full">
                        <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center text-rose-600 mb-3 group-hover:rotate-12 transition-transform">
                            <i class="fas fa-bug-slash text-xs"></i>
                        </div>
                        <span class="stat-label">Bug Matrix</span>
                        <div class="flex items-baseline gap-1.5 mt-1">
                            <span class="stat-value text-rose-600">{{ openBugsCount }}</span>
                            <span class="text-[8px] font-black text-rose-400 uppercase">Block</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Presence Awareness System (Attendance) -->
            <div class="nature-card p-6 relative overflow-hidden group bg-gradient-to-br from-white to-slate-50">
                <div class="flex items-center justify-between mb-8 relative z-10">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-emerald-600 rounded-[1rem] flex items-center justify-center text-white shadow-xl shadow-emerald-600/20 group-hover:rotate-6 transition-transform">
                            <i class="fas fa-fingerprint text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Tactical Presence</h3>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Biometric Sync Matrix</p>
                        </div>
                    </div>
                    
                    <div :class="[attendanceStatus.bg, attendanceStatus.color]" class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] border border-white shadow-sm">
                        {{ attendanceStatus.label }}
                    </div>
                </div>

                <!-- Live Progress Bar -->
                <div class="relative w-full h-2 bg-slate-100 rounded-full overflow-hidden mb-8">
                    <div class="absolute top-0 left-0 h-full bg-emerald-500 rounded-full transition-all duration-1000" :style="{ width: props.attendanceData.progress + '%' }"></div>
                </div>

                <!-- Dual Action Control -->
                <div class="space-y-4 mb-8">
                    <button 
                        @click="handleAttendance"
                        :disabled="syncing"
                        class="w-full py-4 rounded-2xl flex items-center justify-center gap-3 transition-all active:scale-95 shadow-xl disabled:opacity-50"
                        :class="props.attendanceData.status === 'Offline' ? 'bg-slate-900 text-emerald-400' : 'bg-rose-500 text-white'"
                    >
                        <i :class="[syncing ? 'fa-spinner fa-spin' : 'fa-bolt', 'fas']"></i>
                        <span class="text-xs font-black uppercase tracking-[0.2em]">
                            {{ syncing ? 'Syncing Matrix...' : (props.attendanceData.status === 'Offline' ? 'Initialize Presence' : 'Secure Extraction/Out') }}
                        </span>
                    </button>
                    <p v-if="geoError" class="text-[9px] text-rose-500 font-black uppercase text-center tracking-widest">{{ geoError }}</p>
                </div>

                <!-- Temporal Nodes -->
                <div class="grid grid-cols-2 gap-4 relative z-10">
                    <div class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block mb-1">Check In</span>
                        <p class="text-sm font-black text-emerald-800 font-mono tracking-tight italic">{{ props.attendanceData.log?.check_in ? new Date('2024-01-01 ' + props.attendanceData.log.check_in).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '--:--' }}</p>
                    </div>
                    <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-100">
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block mb-1">Shift Uptime</span>
                        <p class="text-sm font-black text-slate-800 font-mono tracking-tight italic tabular-nums">{{ props.attendanceData.progress }}% Cycle</p>
                    </div>
                </div>
            </div>

            <!-- Action Channels -->
            <div class="space-y-3">
                <div class="flex items-center justify-between px-2">
                    <h3 class="text-stat-label italic">Decision Hubs</h3>
                    <span class="text-[9px] font-bold text-slate-400 italic">Live Pulse</span>
                </div>
                
                <div class="space-y-3">
                    <Link :href="route('mobile.chat')" class="flex items-center justify-between p-4 nature-card group active:scale-95 transition-all">
                        <div class="flex items-center gap-4">
                            <div class="bg-emerald-50 w-10 h-10 rounded-xl flex items-center justify-center border border-white/50 transition-colors group-hover:bg-white shadow-sm">
                                <i class="fas fa-satellite-dish text-emerald-600 text-xs"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight">Strategic Communication</h4>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mt-0.5 italic">Hub Pulse & Intel</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-slate-300 text-[10px] group-hover:translate-x-1 transition-transform"></i>
                    </Link>

                    <Link :href="route('mobile.requests')" class="flex items-center justify-between p-4 nature-card group active:scale-95 transition-all">
                        <div class="flex items-center gap-4">
                            <div class="bg-teal-50 w-10 h-10 rounded-xl flex items-center justify-center border border-white/50 transition-colors group-hover:bg-white shadow-sm">
                                <i class="fas fa-paper-plane text-teal-600 text-xs"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight">Operational Requests</h4>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mt-0.5 italic">Leave & WFH Initialization</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-slate-300 text-[10px] group-hover:translate-x-1 transition-transform"></i>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
</style>
