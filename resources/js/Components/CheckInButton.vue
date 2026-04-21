<template>
    <div class="flex items-center gap-2">
        <button 
            v-if="!isCheckedIn"
            @click="performCheckIn" 
            :disabled="loading"
            class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/30 hover:bg-emerald-700 transition-all active:scale-95 disabled:opacity-50"
        >
            <i class="fas fa-sign-in-alt text-[12px]"></i>
            <span v-if="loading">Processing...</span>
            <span v-else>{{ hasSessions ? 'Check In Again' : 'Check In' }}</span>
        </button>

        <div 
            v-if="hasSessions"
            class="flex items-center gap-3 px-4 py-1.5 bg-emerald-50 border border-emerald-100 rounded-xl shadow-sm animate-fade-in"
        >
            <div class="relative">
                <div
                    class="w-2 h-2 rounded-full shadow-[0_0_8px_rgba(16,185,129,0.5)]"
                    :class="isCheckedIn ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"
                ></div>
                <div v-if="isCheckedIn" class="absolute inset-0 w-2 h-2 bg-emerald-500 rounded-full animate-ping opacity-20"></div>
            </div>
            <div class="flex flex-col">
                <span class="text-[9px] font-black text-emerald-800 uppercase tracking-tighter leading-none">
                    {{ isCheckedIn ? 'In Office' : 'Checked Out' }}
                </span>
                <span class="text-[10px] font-bold text-emerald-600 mt-1 leading-none">
                    IN {{ checkInTime }}
                    <span v-if="checkOutTime"> | OUT {{ checkOutTime }}</span>
                </span>
                <span class="text-[10px] font-bold text-slate-600 mt-1 leading-none">
                    Worked {{ workedDuration }}
                </span>
            </div>
            
            <button 
                v-if="isCheckedIn"
                @click="performCheckOut"
                :disabled="loading"
                class="ml-2 w-7 h-7 flex items-center justify-center rounded-lg bg-white border border-emerald-200 text-emerald-600 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 transition-all active:scale-90 shadow-sm"
                title="Clock Out"
            >
                <i class="fas fa-power-off text-[10px]"></i>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';

const loading = ref(false);
const attendanceLog = ref(null);
const now = ref(new Date());
let timer = null;
const toast = useToastStore();

const sessions = computed(() => attendanceLog.value?.sessions || []);
const hasSessions = computed(() => sessions.value.length > 0);

const sortedSessions = computed(() => {
    return [...sessions.value].sort((a, b) => new Date(a.in_time) - new Date(b.in_time));
});

const firstSession = computed(() => sortedSessions.value[0] || null);
const lastSession = computed(() => sortedSessions.value[sortedSessions.value.length - 1] || null);
const openSession = computed(() => [...sortedSessions.value].reverse().find((s) => !s.out_time) || null);
const isCheckedIn = computed(() => !!openSession.value);

const formatTime = (value) => {
    if (!value) return '--:--';
    return new Date(value).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const checkInTime = computed(() => {
    if (!firstSession.value?.in_time) return '--:--';
    return formatTime(firstSession.value.in_time);
});

const checkOutTime = computed(() => {
    if (!lastSession.value?.out_time) return null;
    return formatTime(lastSession.value.out_time);
});

const workedDuration = computed(() => {
    let totalMinutes = Number(attendanceLog.value?.total_work_minutes || 0);

    if (isCheckedIn.value && openSession.value?.in_time) {
        const openMinutes = Math.max(0, Math.floor((now.value - new Date(openSession.value.in_time)) / 60000));
        totalMinutes += openMinutes;
    }

    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;
    return `${hours}h ${minutes}m`;
});

const fetchStatus = async () => {
    try {
        const res = await axios.get(route('api.employee.attendance.today-status'));
        attendanceLog.value = res.data.log;
    } catch (e) {
        console.error('Attendance status check failed', e.response?.data || e.message);
    }
};

const performCheckIn = async () => {
    loading.value = true;
    try {
        const res = await axios.post(route('employee.attendance.auto-check-in'));
        if (res.data.success) {
            toast.success(res.data.message);
            attendanceLog.value = res.data.log;
        } else {
            toast.error(res.data.message);
        }
    } catch (e) {
        if (e.response && e.response.status === 403) {
            toast.warning(e.response.data.message);
        } else {
            toast.error(e.response?.data?.message || "Check-In failed");
        }
    } finally {
        loading.value = false;
        fetchStatus();
    }
};

const performCheckOut = async () => {
    if (!confirm('Are you sure you want to clock out?')) return;
    loading.value = true;
    try {
        const res = await axios.post(route('employee.attendance.clock-out'));
        toast.success(res.data.message);
        fetchStatus();
    } catch (e) {
        toast.error(e.response?.data?.message || "Clock-out failed");
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchStatus();
    timer = setInterval(() => {
        now.value = new Date();
    }, 60000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>
