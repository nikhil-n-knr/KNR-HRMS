<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import BaseInput from '@/Components/BaseInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useToastStore } from '@/stores/toast';
import { ClockIcon, CalendarIcon, BriefcaseIcon, ArrowsRightLeftIcon } from '@heroicons/vue/24/outline'; // Re-verify icons if needed

const toast = useToastStore();
defineOptions({ layout: MainLayout });

const props = defineProps({
    todayLog: Object, // Can be null
    history: Array,   // Collection
    currentShift: Object,
    error: String,
    flash: Object
});

const currentTime = ref(new Date());
const timer = ref(null);
const showModal = ref(false);

const modalForm = ref({
    date: '',
    regularized_in_time: '09:00',
    regularized_out_time: '18:00',
    reason: ''
});

const processing = ref(false);

onMounted(() => {
    timer.value = setInterval(() => {
        currentTime.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    clearInterval(timer.value);
});

const formattedTime = computed(() => {
    return currentTime.value.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
});

const formattedDate = computed(() => {
    return currentTime.value.toLocaleDateString([], { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
});

const workDuration = computed(() => {
    if (props.todayLog && props.todayLog.total_work_minutes) {
        const hours = Math.floor(props.todayLog.total_work_minutes / 60);
        const minutes = props.todayLog.total_work_minutes % 60;
        return `${hours}h ${minutes}m`;
    }
    return '0h 0m';
});

const isClockedIn = computed(() => {
    if (!props.todayLog) return false;
    const sessions = [...(props.todayLog.sessions || [])].sort((a, b) => new Date(a.in_time) - new Date(b.in_time));
    if (sessions.length === 0) return false;
    const lastSession = sessions[sessions.length - 1];
    return lastSession.out_time === null;
});

const columns = {
    date_formatted: { label: 'Date', class: 'text-left' },
    status: { label: 'Status', class: 'text-left' },
    punch_in: { label: 'Punch In', class: 'text-left' },
    punch_out: { label: 'Punch Out', class: 'text-left' },
    total_work: { label: 'Total Work', class: 'text-left' },
};

const historyData = computed(() => {
    if (!props.history) return [];
    return props.history.map(log => {
        const sessions = [...(log.sessions || [])].sort((a, b) => new Date(a.in_time) - new Date(b.in_time));
        const first = sessions[0] || null;
        const last = sessions[sessions.length - 1] || null;
        const totalMinutes = Number(log.total_work_minutes || 0);
        const hours = Math.floor(totalMinutes / 60);
        const minutes = totalMinutes % 60;

        return {
            ...log,
            date_formatted: new Date(log.date).toLocaleDateString(),
            punch_in: first?.in_time ? new Date(first.in_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '-',
            punch_out: last?.out_time ? new Date(last.out_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '-',
            total_work: `${hours}h ${minutes}m`
        };
    });
});

const todaySessions = computed(() => {
    return [...(props.todayLog?.sessions || [])].sort((a, b) => new Date(a.in_time) - new Date(b.in_time));
});

const punch = () => {
    if (processing.value) return;
    processing.value = true;
    
    const url = isClockedIn.value 
        ? route('employee.attendance.clock-out') // Named routes needed
        : route('employee.attendance.clock-in'); // Named routes needed

    router.post(url, {}, {
        preserveScroll: true,
        onFinish: () => processing.value = false,
        onSuccess: () => toast.success(isClockedIn.value ? "Clocked Out" : "Clocked In"),
        onError: (err) => toast.error(err.message || 'Failed')
    });
};

const getSessionDuration = (session) => {
    if (!session?.in_time || !session?.out_time) return 'Open Session';
    const start = new Date(session.in_time);
    const end = new Date(session.out_time);
    const totalMinutes = Math.max(0, Math.floor((end - start) / 60000));
    const h = Math.floor(totalMinutes / 60);
    const m = totalMinutes % 60;
    return `${h}h ${m}m`;
};

const openRegularizeModal = (log) => {
    modalForm.value.date = log.date;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const submitRegularization = () => {
    processing.value = true;
    router.post(route('employee.attendance.regularize'), modalForm.value, {
        onSuccess: () => {
            closeModal();
            toast.success("Requests submitted");
        },
        onFinish: () => processing.value = false
    });
};
</script>

<template>
    <!-- Use standard SPA title updater if desired, Head is Inertia -->
    <!-- <Head title="Attendance Dashboard" /> -->

    <div class="space-y-6">
        <!-- Error Alert -->
        <div v-if="error" class="bg-red-50 border-l-4 border-red-500 p-4 rounded shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        {{ error }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-4 md:p-6 rounded-2xl shadow-sm border border-gray-100 gap-4">
            <div class="w-full md:w-auto">
                <h2 class="text-lg md:text-xl font-black text-slate-800 uppercase tracking-tight">Attendance Center</h2>
                <div class="flex flex-wrap gap-3 md:gap-4 mt-2">
                    <p class="text-sm md:text-sm text-gray-500 font-medium whitespace-nowrap">Operational Dashboard</p>
                    <div class="flex gap-3">
                        <Link href="/attendance/floating-holidays" class="text-sm font-black text-emerald-600 hover:text-emerald-800 flex items-center gap-1 uppercase tracking-widest">
                            <CalendarIcon class="w-3.5 h-3.5" /> Holidays
                        </Link>
                        <Link href="/attendance/swaps" class="text-sm font-black text-emerald-600 hover:text-emerald-800 flex items-center gap-1 uppercase tracking-widest">
                            <ArrowsRightLeftIcon class="w-3.5 h-3.5" /> Swaps
                        </Link>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between w-full md:w-auto md:text-right bg-slate-50 md:bg-transparent p-3 md:p-0 rounded-xl md:rounded-none">
                <div class="md:hidden">
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Global Pulse</p>
                    <p class="text-xs font-bold text-slate-600">{{ formattedDate }}</p>
                </div>
                <div>
                    <p class="text-2xl md:text-3xl font-black text-emerald-600 font-mono leading-none tracking-tighter">{{ formattedTime }}</p>
                    <p class="hidden md:block text-sm font-black text-slate-400 uppercase tracking-widest mt-1.5">{{ formattedDate }}</p>
                </div>
            </div>
        </div>

        <!-- Main Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Status Card -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center">
                <div class="p-3 rounded-full bg-emerald-50 text-emerald-600 mb-3">
                    <BriefcaseIcon class="w-8 h-8" />
                </div>
                <h3 class="text-gray-500 text-sm font-medium">Current Status</h3>
                <p class="text-lg font-bold mt-1" 
                    :class="{'text-green-600': todayLog?.status === 'Present', 'text-red-600': todayLog?.status === 'Absent' || todayLog?.status === 'Late'}">
                    {{ todayLog ? todayLog.status : 'Not Started' }}
                </p>
                <p v-if="todayLog?.is_late" class="text-xs text-red-500 mt-1">Marked Late</p>
            </div>

            <!-- Duration Card -->
             <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center">
                <div class="p-3 rounded-full bg-teal-50 text-teal-600 mb-3">
                    <ClockIcon class="w-8 h-8" />
                </div>
                <h3 class="text-gray-500 text-sm font-medium">Work Duration</h3>
                <p class="text-lg font-bold mt-1 text-gray-800">{{ workDuration }}</p>
                <p v-if="currentShift" class="text-xs text-gray-400 mt-1">Shift: {{ currentShift.start_time }} - {{ currentShift.end_time }}</p>
            </div>

            <!-- Punch Action Card -->
             <div class="bg-gradient-to-br from-emerald-500 to-teal-600 p-6 rounded-lg shadow-lg text-white flex flex-col items-center justify-center text-center">
                <h3 class="text-emerald-100 text-sm font-medium mb-4">Action Required</h3>
                
                <button 
                    @click="punch"
                    :disabled="processing"
                    class="w-32 h-32 rounded-full border-4 border-white/30 flex items-center justify-center shadow-inner transition-all transform hover:scale-105 active:scale-95 bg-white/10 backdrop-blur-sm"
                    :class="{'hover:bg-red-500/20': isClockedIn, 'hover:bg-green-500/20': !isClockedIn}"
                >
                    <div class="text-center">
                        <p class="text-2xl font-bold">{{ isClockedIn ? 'OUT' : 'IN' }}</p>
                        <p class="text-xs uppercase tracking-wider opacity-75 mt-1">Punch</p>
                    </div>
                </button>
                <p class="mt-4 text-xs opacity-75">Click to record your attendance</p>
            </div>
        </div>

        <!-- Timeline & Sessions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-medium text-gray-800">Today's Timeline</h3>
            </div>
            <div class="p-6" v-if="todaySessions.length > 0">
                <div class="relative pl-4 border-l-2 border-gray-200 space-y-8">
                    <div v-for="session in todaySessions" :key="session.id" class="relative">
                        <!-- In Punch -->
                        <div class="mb-4">
                            <span class="absolute -left-[21px] flex h-4 w-4 items-center justify-center rounded-full bg-green-500 ring-4 ring-white"></span>
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Clock In</p>
                                    <p class="text-xs text-gray-500">{{ session.source }} • {{ session.in_ip }}</p>
                                </div>
                                <p class="text-sm font-mono text-gray-700">
                                    {{ new Date(session.in_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Out Punch (if exists) -->
                        <div v-if="session.out_time">
                            <span class="absolute -left-[21px] flex h-4 w-4 items-center justify-center rounded-full bg-red-500 ring-4 ring-white"></span>
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Clock Out</p>
                                    <p class="text-xs text-gray-500">{{ session.source }} • {{ session.out_ip }}</p>
                                </div>
                                <p class="text-sm font-mono text-gray-700">
                                    {{ new Date(session.out_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-2 ml-1 text-xs text-slate-500 font-semibold uppercase tracking-wide">
                            Session Duration: {{ getSessionDuration(session) }}
                        </div>
                    </div>
                </div>
            </div>
             <div v-else class="p-12 text-center text-gray-400">
                <p>No activity recorded for today.</p>
            </div>
        </div>

         <!-- History Table -->
        <BaseDataTable 
            :columns="columns"
            :data="historyData"
        >
            <template #cell-status="{ item }">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                    :class="{
                        'bg-green-100 text-green-800': item.status === 'Present',
                        'bg-red-100 text-red-800': item.status === 'Absent',
                        'bg-yellow-100 text-yellow-800': item.status === 'Late',
                        'bg-blue-100 text-blue-800': item.status === 'Holiday'
                    }">
                    {{ item.status }}
                </span>
            </template>
            
            <template #rowActions="{ item }">
               
               <button v-if="['Absent', 'Late'].includes(item.status) && !item.is_regularized" 
                    @click="openRegularizeModal(item)"
                    class="text-emerald-600 hover:text-emerald-900 text-xs font-medium">
                    Regularize
                </button>
                <span v-else-if="item.is_regularized" class="text-green-600 text-xs">Regularized</span>
            </template>
        </BaseDataTable>

         <!-- Regularization Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Request Regularization</h3>
                <p class="text-sm text-gray-500 mb-4">Correcting attendance for: <b>{{ new Date(modalForm.date).toLocaleDateString() }}</b></p>
                
                <form @submit.prevent="submitRegularization" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <BaseInput
                            type="time"
                            v-model="modalForm.regularized_in_time"
                            label="In Time"
                            required
                        />
                        <BaseInput
                            type="time"
                            v-model="modalForm.regularized_out_time"
                            label="Out Time"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                        <textarea
                            v-model="modalForm.reason" 
                            rows="2" 
                            required 
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-all text-sm py-2.5 px-4 bg-white/50 backdrop-blur-sm"
                            placeholder="Forgot to punch out..."
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 mt-4">
                        <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                        <button 
                            :disabled="processing"
                            class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-lg text-sm font-medium shadow-md hover:shadow-lg disabled:opacity-50 transition-all"
                        >
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>
