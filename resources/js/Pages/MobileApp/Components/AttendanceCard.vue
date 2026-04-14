<template>
    <div class="glass-card p-6 mt-4">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Attendance</h3>
                <p class="text-sm text-slate-500">{{ todayStr }}</p>
            </div>
            <div :class="statusBadgeClass" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                {{ statusText }}
            </div>
        </div>

        <div class="space-y-4">
            <div class="flex items-center gap-4 p-4 bg-slate-50/50 rounded-2xl border border-white/50">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase font-bold">Punch In</p>
                    <p class="text-lg font-semibold text-slate-700">{{ punchInTime || '--:--' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4 p-4 bg-slate-50/50 rounded-2xl border border-white/50">
                <div class="w-12 h-12 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase font-bold">Punch Out</p>
                    <p class="text-lg font-semibold text-slate-700">{{ punchOutTime || '--:--' }}</p>
                </div>
            </div>
        </div>

        <button 
            @click="handleAction"
            :disabled="processing"
            class="w-full mt-6 btn-mobile-primary flex items-center justify-center gap-2 py-4"
        >
            <i :class="actionIcon" v-if="!processing"></i>
            <span v-if="processing" class="animate-spin mr-2"><i class="fas fa-spinner"></i></span>
            {{ actionText }}
        </button>

        <p v-if="geoError" class="text-center text-[10px] text-rose-500 mt-2 font-medium">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ geoError }}
        </p>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    initialData: Object
});

const todayStr = new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
const processing = ref(false);
const geoError = ref(null);
const attendance = ref(props.initialData?.log || null);

const punchInTime = computed(() => {
    const session = attendance.value?.sessions?.[0];
    return session ? new Date(session.in_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : null;
});

const punchOutTime = computed(() => {
    const session = attendance.value?.sessions?.find(s => s.out_time);
    return session ? new Date(session.out_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : null;
});

const isClockedIn = computed(() => {
    return attendance.value?.sessions?.some(s => !s.out_time) || false;
});

const statusText = computed(() => {
    if (isClockedIn.value) return 'Active';
    if (punchInTime.value) return 'Clocked Out';
    return 'Not Started';
});

const statusBadgeClass = computed(() => {
    if (isClockedIn.value) return 'bg-emerald-100 text-emerald-600';
    if (punchInTime.value) return 'bg-slate-100 text-slate-600';
    return 'bg-amber-100 text-amber-600';
});

const actionText = computed(() => isClockedIn.value ? 'Clock Out' : 'Clock In Now');
const actionIcon = computed(() => isClockedIn.value ? 'fas fa-stop-circle' : 'fas fa-play-circle');

const handleAction = async () => {
    processing.value = true;
    geoError.value = null;

    if (!navigator.geolocation) {
        geoError.value = "Geolocation is not supported by your device.";
        executePunch(null, null);
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => {
            executePunch(position.coords.latitude, position.coords.longitude);
        },
        (error) => {
            console.warn("Geo error:", error);
            geoError.value = "Location access denied. Please enable GPS for attendance.";
            executePunch(null, null);
        },
        { enableHighAccuracy: true, timeout: 5000 }
    );
};

const executePunch = async (lat, long) => {
    const endpoint = isClockedIn.value ? '/api/mobile/v1/attendance/clock-out' : '/api/mobile/v1/attendance/clock-in';
    
    try {
        const response = await axios.post(endpoint, { lat, long });
        if (response.data.success) {
            attendance.value = response.data.log;
        }
    } catch (err) {
        geoError.value = err.response?.data?.message || "Failed to mark attendance.";
    } finally {
        processing.value = false;
    }
};
</script>
