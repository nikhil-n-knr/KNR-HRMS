<template>
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl h-full flex flex-col group relative overflow-hidden transition-all hover:border-emerald-500/50">
        <!-- Decoration -->
        <div class="absolute -top-12 -right-12 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
        <div class="absolute -bottom-12 -left-12 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl"></div>

        <div class="flex justify-between items-center mb-8 relative z-10">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                Deployment_Status
            </h3>
            <span v-if="currentIP" class="text-[9px] font-mono text-slate-500 bg-slate-800/50 px-2 py-0.5 rounded border border-slate-700/50">
                IP: {{ currentIP }}
            </span>
        </div>

        <div class="flex-grow flex flex-col items-center justify-center text-center space-y-6 relative z-10">
            <div v-if="checking" class="flex flex-col items-center gap-4">
                <div class="w-16 h-16 border-4 border-emerald-500/20 border-t-emerald-500 rounded-full animate-spin"></div>
                <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest animate-pulse">Establishing Link...</p>
            </div>
            <div v-else class="space-y-6 w-full">
                <div v-if="hasSessions" class="space-y-4">
                    <div class="w-20 h-20 bg-emerald-500/10 rounded-full flex items-center justify-center border-2 border-emerald-500/20 mx-auto shadow-2xl shadow-emerald-500/5">
                        <i class="fas fa-check-double text-2xl text-emerald-500"></i>
                    </div>
                    <div>
                        <h4 class="text-xl font-black text-white tracking-tighter">{{ isCheckedIn ? 'Operative Active' : 'Shift Closed' }}</h4>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">
                            IN {{ checkInTime }}
                            <span v-if="checkOutTime"> | OUT {{ checkOutTime }}</span>
                        </p>
                        <p class="text-[10px] font-black text-emerald-400 uppercase tracking-widest mt-2">Worked {{ workedDuration }}</p>
                    </div>
                    <div class="flex items-center justify-center gap-3">
                        <div class="px-3 py-1.5 bg-slate-800 rounded-xl border border-slate-700 text-[10px] font-black text-emerald-400 uppercase tracking-widest">
                            <i class="fas fa-wifi mr-2"></i> Authorized Node
                        </div>
                    </div>
                </div>
                <div v-else class="space-y-6">
                    <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center border border-slate-700 mx-auto group-hover:border-emerald-500/50 transition-colors">
                        <i class="fas fa-fingerprint text-2xl text-slate-500 group-hover:text-emerald-500 transition-colors"></i>
                    </div>
                    <div>
                        <h4 class="text-xl font-black text-white tracking-tighter">Action Required</h4>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Personnel mesh sync failure</p>
                    </div>
                    
                    <button 
                        @click="handleCheckIn"
                        :disabled="processing"
                        class="w-full py-4 bg-emerald-600 text-white text-[11px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-emerald-500 transition-all active:scale-x-95 shadow-xl shadow-emerald-600/20 group/btn flex items-center justify-center gap-3"
                    >
                        <i class="fas fa-bolt text-sm group-hover/btn:rotate-12 transition-transform" :class="{'animate-spin': processing}"></i>
                        {{ processing ? 'SYNCING MATRIX...' : 'AUTO_CHECK_IN' }}
                    </button>
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-tight">Requires authorized office WiFi connection</p>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-800/50 relative z-10 flex justify-between items-center">
             <div class="flex flex-col">
                 <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest">Network_Zone</span>
                 <span class="text-[10px] font-black text-slate-300 uppercase truncate max-w-[100px]">{{ zoneName }}</span>
             </div>
             <div class="text-right">
                 <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest">Current_Shift</span>
                 <span class="text-[10px] font-black text-emerald-400 uppercase truncate max-w-[100px]">{{ shiftName }}</span>
             </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();
const checking = ref(true);
const processing = ref(false);
const todayLog = ref(null);
const currentIP = ref('');
const zoneName = ref('Detecting...');
const shiftName = ref('Syncing...');

const sessions = computed(() => todayLog.value?.sessions || []);
const hasSessions = computed(() => sessions.value.length > 0);
const sortedSessions = computed(() => [...sessions.value].sort((a, b) => new Date(a.in_time) - new Date(b.in_time)));
const firstSession = computed(() => sortedSessions.value[0] || null);
const lastSession = computed(() => sortedSessions.value[sortedSessions.value.length - 1] || null);
const isCheckedIn = computed(() => !!sortedSessions.value.find((s) => !s.out_time));

const checkInTime = computed(() => {
    if (!firstSession.value?.in_time) return '--:--';
    return new Date(firstSession.value.in_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
});

const checkOutTime = computed(() => {
    if (!lastSession.value?.out_time) return null;
    return new Date(lastSession.value.out_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
});

const workedDuration = computed(() => {
    const totalMinutes = Number(todayLog.value?.total_work_minutes || 0);
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;
    return `${hours}h ${minutes}m`;
});

const fetchStatus = async () => {
    checking.value = true;
    try {
        const res = await axios.get('/api/employee/attendance/today-status');
        todayLog.value = res.data.log;
        currentIP.value = res.data.ip;
        zoneName.value = res.data.zone || 'Global Mesh';
        shiftName.value = res.data.shift || 'Alpha Shift';
    } catch (e) {
        console.error('Failed to sync telemetry');
    } finally {
        checking.value = false;
    }
};

const handleCheckIn = async () => {
    processing.value = true;
    try {
        const res = await axios.post('/attendance/auto-check-in');
        if (res.data.success) {
            toast.success('Matrix sync successful. Deployment verified.');
            fetchStatus();
        } else {
            toast.error(res.data.message || 'Node authentication failed');
        }
    } catch (e) {
        toast.error(e.response?.data?.message || 'Protocol failure. Link terminated.');
    } finally {
        processing.value = false;
    }
};

onMounted(fetchStatus);
</script>
