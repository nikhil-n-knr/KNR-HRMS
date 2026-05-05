<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import BaseInput from '@/Components/BaseInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useToastStore } from '@/stores/toast';
import { ClockIcon, CalendarIcon, BriefcaseIcon, ArrowsRightLeftIcon } from '@heroicons/vue/24/outline';

const toast = useToastStore();
defineOptions({ layout: MainLayout });

const props = defineProps({
    todayLog:     Object,
    history:      Array,
    currentShift: Object,
    error:        String,
    flash:        Object
});

// ── Live clock ──
const currentTime = ref(new Date());
const timer       = ref(null);
const showModal   = ref(false);
const processing  = ref(false);

const modalForm = ref({
    date:                    '',
    regularized_in_time:  '09:00',
    regularized_out_time: '18:00',
    reason:                  ''
});

onMounted(() => {
    timer.value = setInterval(() => { currentTime.value = new Date(); }, 1000);
});
onUnmounted(() => { clearInterval(timer.value); });

const formattedTime = computed(() =>
    currentTime.value.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
);
const formattedDate = computed(() =>
    currentTime.value.toLocaleDateString([], { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
);

const workDuration = computed(() => {
    if (props.todayLog?.total_work_minutes) {
        const h = Math.floor(props.todayLog.total_work_minutes / 60);
        const m = props.todayLog.total_work_minutes % 60;
        return `${h}h ${m}m`;
    }
    return '0h 0m';
});

const isClockedIn = computed(() => {
    if (!props.todayLog) return false;
    const sessions = [...(props.todayLog.sessions || [])].sort((a, b) => new Date(a.in_time) - new Date(b.in_time));
    if (!sessions.length) return false;
    return sessions[sessions.length - 1].out_time === null;
});

const columns = {
    date_formatted: { label: 'Date',       class: 'text-left' },
    status:         { label: 'Status',     class: 'text-left' },
    punch_in:       { label: 'Punch In',   class: 'text-left' },
    punch_out:      { label: 'Punch Out',  class: 'text-left' },
    total_work:     { label: 'Total Work', class: 'text-left' },
};

const historyData = computed(() => {
    if (!props.history) return [];
    return props.history.map(log => {
        const sessions    = [...(log.sessions || [])].sort((a, b) => new Date(a.in_time) - new Date(b.in_time));
        const first       = sessions[0] || null;
        const last        = sessions[sessions.length - 1] || null;
        const totalMinutes = Number(log.total_work_minutes || 0);
        return {
            ...log,
            date_formatted: new Date(log.date).toLocaleDateString(),
            punch_in:  first?.in_time   ? new Date(first.in_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '—',
            punch_out: last?.out_time   ? new Date(last.out_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '—',
            total_work: `${Math.floor(totalMinutes / 60)}h ${totalMinutes % 60}m`
        };
    });
});

const todaySessions = computed(() =>
    [...(props.todayLog?.sessions || [])].sort((a, b) => new Date(a.in_time) - new Date(b.in_time))
);

const punch = () => {
    if (processing.value) return;
    processing.value = true;
    const url = isClockedIn.value
        ? route('employee.attendance.clock-out')
        : route('employee.attendance.clock-in');
    router.post(url, {}, {
        preserveScroll: true,
        onFinish:  () => { processing.value = false; },
        onSuccess: () => toast.success(isClockedIn.value ? 'Clocked Out' : 'Clocked In'),
        onError:   (err) => toast.error(err.message || 'Failed')
    });
};

const getSessionDuration = (session) => {
    if (!session?.in_time || !session?.out_time) return 'Open Session';
    const totalMinutes = Math.max(0, Math.floor((new Date(session.out_time) - new Date(session.in_time)) / 60000));
    return `${Math.floor(totalMinutes / 60)}h ${totalMinutes % 60}m`;
};

const openRegularizeModal = (log) => {
    modalForm.value.date = log.date;
    showModal.value = true;
};
const closeModal = () => { showModal.value = false; };

const submitRegularization = () => {
    processing.value = true;
    router.post(route('employee.attendance.regularize'), modalForm.value, {
        onSuccess: () => { closeModal(); toast.success('Request submitted'); },
        onFinish:  () => { processing.value = false; }
    });
};
</script>

<template>
    <!-- ░░ Outer Page Shell ░░ -->
    <div class="min-h-screen bg-[#f4f5fa]">

        <!-- ▓▓ GRADIENT HERO HEADER ▓▓ -->
        <div class="relative overflow-hidden sm:rounded-2xl bg-gradient-to-br from-[#3d27b4] via-[#6b3fd4] to-[#a855f7]">
            <!-- Blobs -->
            <div class="absolute -top-20 -right-20 w-80 h-80 bg-white/5 rounded-full pointer-events-none"></div>
            <div class="absolute bottom-0 left-1/3 w-56 h-56 bg-white/5 rounded-full pointer-events-none"></div>

            <div class="relative z-10 px-6 sm:px-10 py-8
                        flex flex-col lg:flex-row lg:items-center justify-between gap-6">

                <!-- Left: title + quick links -->
                <div>
                    <p class="text-xs font-bold text-white/50 uppercase tracking-widest mb-2">Attendance</p>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                        Attendance Center
                    </h1>
                    <p class="mt-2 text-sm text-white/60 leading-relaxed">Operational Dashboard</p>
                    <div class="flex flex-wrap gap-3 mt-3">
                        <Link href="/attendance/floating-holidays"
                            class="inline-flex items-center gap-1.5 text-xs font-extrabold text-white/70
                                   hover:text-white uppercase tracking-widest transition-colors">
                            <CalendarIcon class="w-3.5 h-3.5" /> Holidays
                        </Link>
                        <span class="text-white/20">·</span>
                        <Link href="/attendance/swaps"
                            class="inline-flex items-center gap-1.5 text-xs font-extrabold text-white/70
                                   hover:text-white uppercase tracking-widest transition-colors">
                            <ArrowsRightLeftIcon class="w-3.5 h-3.5" /> Swaps
                        </Link>
                    </div>
                </div>

                <!-- Right: live clock + stat pills -->
                <div class="flex flex-wrap items-center gap-3 shrink-0">

                    <!-- Live clock pill -->
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[160px]">
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Live Time</p>
                        <p class="text-3xl font-extrabold text-white font-mono leading-none tracking-tighter">
                            {{ formattedTime }}
                        </p>
                        <p class="text-xs text-white/50 font-medium mt-1 truncate max-w-[200px]">{{ formattedDate }}</p>
                    </div>

                    <!-- Work duration pill -->
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[130px]">
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Today's Work</p>
                        <p class="text-3xl font-extrabold text-white leading-none">{{ workDuration }}</p>
                        <p v-if="currentShift" class="text-xs text-white/50 font-medium mt-1">
                            {{ currentShift.start_time }} – {{ currentShift.end_time }}
                        </p>
                    </div>

                    <!-- Status pill -->
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[130px]">
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Status</p>
                        <p class="text-2xl font-extrabold leading-none"
                            :class="{
                                'text-emerald-300': props.todayLog?.status === 'Present',
                                'text-rose-300':    props.todayLog?.status === 'Absent' || props.todayLog?.status === 'Late',
                                'text-white':       !props.todayLog
                            }">
                            {{ props.todayLog?.status || 'Not Started' }}
                        </p>
                        <p v-if="props.todayLog?.is_late" class="text-xs text-rose-300 font-bold mt-1">Marked Late</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ▓▓ INNER CONTENT BODY ▓▓ -->
        <div class="mx-0 sm:mx-6 mt-5 pb-12 space-y-5">

            <!-- Error Alert -->
            <div v-if="error"
                class="flex items-start gap-3 bg-rose-50 border border-rose-200 text-rose-700
                       rounded-2xl p-4 shadow-sm">
                <svg class="h-5 w-5 text-rose-500 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm font-semibold">{{ error }}</p>
            </div>

            <!-- Top Row: Status + Duration + Punch -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                <!-- Current Status -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col items-center justify-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center mb-4">
                        <BriefcaseIcon class="w-7 h-7 text-indigo-500" />
                    </div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Current Status</p>
                    <p class="text-2xl font-extrabold"
                        :class="{
                            'text-emerald-600': props.todayLog?.status === 'Present',
                            'text-rose-600':    props.todayLog?.status === 'Absent' || props.todayLog?.status === 'Late',
                            'text-slate-700':   !props.todayLog
                        }">
                        {{ props.todayLog?.status || 'Not Started' }}
                    </p>
                    <p v-if="props.todayLog?.is_late" class="text-xs text-rose-500 font-bold mt-1">Marked Late</p>
                </div>

                <!-- Work Duration -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col items-center justify-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-teal-50 flex items-center justify-center mb-4">
                        <ClockIcon class="w-7 h-7 text-teal-500" />
                    </div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Work Duration</p>
                    <p class="text-2xl font-extrabold text-slate-900">{{ workDuration }}</p>
                    <p v-if="currentShift" class="text-xs text-slate-400 font-medium mt-1">
                        Shift: {{ currentShift.start_time }} – {{ currentShift.end_time }}
                    </p>
                </div>

                <!-- Punch Action -->
                <div class="relative overflow-hidden bg-gradient-to-br from-[#3d27b4] via-[#6b3fd4] to-[#a855f7]
                            rounded-2xl shadow-lg p-6 flex flex-col items-center justify-center text-center">
                    <!-- blob -->
                    <div class="absolute -top-8 -right-8 w-32 h-32 bg-white/10 rounded-full pointer-events-none"></div>

                    <p class="text-xs font-bold text-white/60 uppercase tracking-widest mb-5">Action Required</p>

                    <button
                        @click="punch"
                        :disabled="processing"
                        class="relative z-10 w-32 h-32 rounded-full border-4 border-white/30
                               flex items-center justify-center
                               bg-white/10 backdrop-blur-sm shadow-inner
                               transition-all transform hover:scale-105 active:scale-95 disabled:opacity-60"
                        :class="isClockedIn ? 'hover:bg-red-500/20' : 'hover:bg-emerald-500/20'"
                    >
                        <div class="text-center">
                            <p class="text-3xl font-extrabold text-white">{{ isClockedIn ? 'OUT' : 'IN' }}</p>
                            <p class="text-xs uppercase tracking-widest text-white/60 mt-1">Punch</p>
                        </div>
                    </button>
                    <p class="mt-4 text-xs text-white/50 font-medium">Click to record your attendance</p>
                </div>
            </div>

            <!-- Today's Timeline -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center">
                        <ClockIcon class="w-4 h-4 text-indigo-500" />
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900">Today's Timeline</h3>
                </div>

                <div v-if="todaySessions.length > 0" class="p-6">
                    <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-px before:bg-slate-100">
                        <div v-for="session in todaySessions" :key="session.id" class="space-y-4">

                            <!-- Clock In -->
                            <div class="relative group">
                                <div class="absolute -left-[22px] top-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow-sm"></div>
                                <div class="bg-emerald-50 hover:bg-emerald-100/70 rounded-xl border border-emerald-100 p-4 transition-colors">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <p class="text-xs font-extrabold text-emerald-800 uppercase tracking-wide">Clock In</p>
                                            <p class="text-xs text-emerald-600 mt-0.5 font-medium">{{ session.source }} · {{ session.in_ip }}</p>
                                        </div>
                                        <p class="text-sm font-mono font-extrabold text-emerald-700 shrink-0">
                                            {{ new Date(session.in_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Clock Out (if exists) -->
                            <div v-if="session.out_time" class="relative group">
                                <div class="absolute -left-[22px] top-1 w-4 h-4 rounded-full bg-rose-500 border-2 border-white shadow-sm"></div>
                                <div class="bg-rose-50 hover:bg-rose-100/70 rounded-xl border border-rose-100 p-4 transition-colors">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <p class="text-xs font-extrabold text-rose-800 uppercase tracking-wide">Clock Out</p>
                                            <p class="text-xs text-rose-600 mt-0.5 font-medium">{{ session.source }} · {{ session.out_ip }}</p>
                                        </div>
                                        <p class="text-sm font-mono font-extrabold text-rose-700 shrink-0">
                                            {{ new Date(session.out_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Duration badge -->
                            <div class="flex items-center gap-2 pl-1">
                                <div class="h-px flex-1 bg-slate-100"></div>
                                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest bg-white border border-slate-200 px-3 py-1 rounded-full">
                                    {{ getSessionDuration(session) }}
                                </span>
                                <div class="h-px flex-1 bg-slate-100"></div>
                            </div>

                        </div>
                    </div>
                </div>

                <div v-else class="py-16 text-center">
                    <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <ClockIcon class="w-6 h-6 text-slate-300" />
                    </div>
                    <p class="text-sm font-semibold text-slate-400">No activity recorded for today.</p>
                </div>
            </div>

            <!-- History Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center">
                        <CalendarIcon class="w-4 h-4 text-slate-500" />
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900">Attendance History</h3>
                </div>

                <BaseDataTable
                    :columns="columns"
                    :data="historyData"
                >
                    <!-- Status Badge -->
                    <template #cell-status="{ item }">
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border"
                            :class="{
                                'bg-emerald-50 text-emerald-700 border-emerald-100': item.status === 'Present',
                                'bg-rose-50    text-rose-700    border-rose-100':    item.status === 'Absent',
                                'bg-amber-50   text-amber-700   border-amber-100':   item.status === 'Late',
                                'bg-blue-50    text-blue-700    border-blue-100':     item.status === 'Holiday',
                            }"
                        >
                            <span
                                class="w-1.5 h-1.5 rounded-full"
                                :class="{
                                    'bg-emerald-500': item.status === 'Present',
                                    'bg-rose-500':    item.status === 'Absent',
                                    'bg-amber-500':   item.status === 'Late',
                                    'bg-blue-500':    item.status === 'Holiday',
                                }"
                            ></span>
                            {{ item.status }}
                        </span>
                    </template>

                    <!-- Row Actions -->
                    <template #rowActions="{ item }">
                        <button
                            v-if="['Absent', 'Late'].includes(item.status) && !item.is_regularized"
                            @click="openRegularizeModal(item)"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl
                                   bg-indigo-50 text-indigo-600 text-xs font-bold
                                   border border-indigo-100
                                   hover:bg-indigo-600 hover:text-white hover:border-indigo-600
                                   transition-all"
                        >
                            Regularize
                        </button>
                        <span
                            v-else-if="item.is_regularized"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Regularized
                        </span>
                    </template>
                </BaseDataTable>
            </div>

        </div><!-- /inner body -->
    </div><!-- /outer shell -->

    <!-- ── Regularization Modal ── -->
    <Modal :show="showModal" @close="closeModal">
        <div class="p-6 sm:p-8">

            <!-- Modal Header -->
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                    <ClockIcon class="w-5 h-5 text-indigo-600" />
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Request Regularization</h3>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">
                        Correcting attendance for:
                        <strong class="text-slate-700">{{ new Date(modalForm.date).toLocaleDateString() }}</strong>
                    </p>
                </div>
            </div>

            <form @submit.prevent="submitRegularization" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-600">In Time <span class="text-rose-500">*</span></label>
                        <input
                            type="time"
                            v-model="modalForm.regularized_in_time"
                            required
                            class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-medium text-slate-800
                                   bg-slate-50 outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/10 transition-all"
                        />
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-600">Out Time <span class="text-rose-500">*</span></label>
                        <input
                            type="time"
                            v-model="modalForm.regularized_out_time"
                            required
                            class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-medium text-slate-800
                                   bg-slate-50 outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/10 transition-all"
                        />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-600">Reason <span class="text-rose-500">*</span></label>
                    <textarea
                        v-model="modalForm.reason"
                        rows="3"
                        required
                        placeholder="Forgot to punch out…"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-medium text-slate-800
                               bg-slate-50 outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/10 transition-all resize-none"
                    ></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-all"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl
                               bg-indigo-600 text-white text-sm font-bold
                               hover:bg-indigo-700 active:scale-[0.98] transition-all
                               shadow-sm shadow-indigo-200 disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <div v-if="processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>