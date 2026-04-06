<template>
    <component :is="embedded ? 'div' : AttendanceLayout" title="Deployment Matrix" activeTab="roster" v-bind="$props">
        <Head v-if="!embedded" title="Shift Roster" />
        
        <div :class="{'max-w-[1600px] mx-auto': !embedded}" class="space-y-8 pb-12 px-4 md:px-0 font-outfit">
            <!-- Standardized Command Header -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div class="flex items-center gap-5">
                    <div class="p-5 bg-slate-900 border border-slate-800 rounded-[2rem] text-amber-400 shadow-2xl shadow-slate-200">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">Deployment Matrix</h2>
                        <div class="flex items-center gap-2 mt-1.5 opacity-60">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            <p class="text-sm font-black text-slate-500 uppercase tracking-[0.3em]">Sector Rotation Pulse</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                    <!-- Date Navigation Hub -->
                    <div class="flex items-center bg-white p-1 rounded-2xl shadow-sm border border-gray-100 flex-1 md:flex-none">
                        <button @click="changeDate(-7)" class="w-10 h-10 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-slate-900 transition-all active:scale-95">
                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        <div class="px-6 text-center min-w-[160px]">
                            <p class="text-sm font-black text-slate-900 tracking-widest uppercase">
                                {{ new Date(dateRange.start).toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) }} - 
                                {{ new Date(dateRange.end).toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) }}
                            </p>
                        </div>
                        <button @click="changeDate(7)" class="w-10 h-10 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-slate-900 transition-all active:scale-95">
                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <button @click="showImportModal = true" class="h-12 w-12 bg-white text-slate-400 rounded-2xl flex items-center justify-center border border-gray-100 shadow-sm hover:text-indigo-600 transition-all group" title="Import CSV Protocol">
                            <svg class="w-5 h-5 group-hover:-translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        </button>
                        <button @click="openAssignModal" class="flex-1 sm:flex-none flex items-center justify-center gap-3 bg-slate-900 text-white h-12 px-8 rounded-2xl hover:bg-emerald-600 transition-all text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 group">
                            <svg class="w-4 h-4 text-emerald-400 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Deploy Assignment</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Standardized Filter Array -->
            <div class="bg-white/40 backdrop-blur-xl border border-white/60 p-1.5 rounded-3xl shadow-sm">
                <EmployeeFilterBar 
                    :filters="filters"
                    :departments="departments"
                    :locations="locations"
                    @update="f => filters = { ...filters, ...f }"
                    class="!mb-0"
                />
            </div>

            <!-- Matrix Transformation -->
            <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-2xl shadow-slate-200/50 overflow-hidden flex flex-col relative min-h-[600px] group/matrix transition-all">
                <!-- Loading Overlay -->
                <div v-if="loading" class="absolute inset-0 bg-white/60 backdrop-blur-md z-50 flex items-center justify-center">
                    <div class="flex flex-col items-center gap-6">
                        <div class="relative w-16 h-16">
                            <div class="absolute inset-0 border-4 border-emerald-500/10 rounded-full"></div>
                            <div class="absolute inset-0 border-4 border-t-emerald-600 rounded-full animate-spin"></div>
                        </div>
                        <span class="text-sm font-black text-emerald-600 uppercase tracking-[0.3em] animate-pulse">Syncing Matrix...</span>
                    </div>
                </div>

                <!-- Desktop Matrix View (Hidden on Mobile) -->
                <div class="hidden lg:flex flex-col h-full overflow-hidden">
                    <!-- Matrix Header Row -->
                    <div class="flex border-b border-gray-100 bg-slate-900 sticky top-0 z-30 shadow-xl">
                        <div class="w-72 p-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em] border-r border-slate-800 shrink-0 sticky left-0 bg-slate-900 z-20 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                Operative Registry
                            </div>
                            <button @click="selection.clear()" v-if="selection.size > 0" class="px-3 py-1.5 bg-emerald-600 text-white rounded-xl text-sm font-black hover:bg-white hover:text-slate-900 transition-all uppercase tracking-widest shadow-lg">
                                Clear ({{ selection.size }})
                            </button>
                        </div>
                        <div class="flex-1 flex overflow-x-auto hide-scrollbar">
                            <div v-for="day in days" :key="day" class="min-w-[140px] w-full p-4 text-center border-r border-slate-800 last:border-0 hover:bg-slate-800/50 transition-all">
                                <p class="text-sm font-black text-slate-500 uppercase tracking-widest mb-2">{{ day.toLocaleDateString('en-US', { weekday: 'short' }) }}</p>
                                <p class="text-xl font-black text-white tracking-tighter tabular-nums leading-none">{{ day.getDate() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Matrix Body -->
                    <div class="overflow-auto flex-1 custom-scrollbar">
                        <div v-for="emp in (employees?.data || [])" :key="emp.id" class="flex border-b border-gray-50 hover:bg-slate-50/50 transition-all group/row">
                            <!-- Member Sticky Column -->
                            <div 
                                class="w-72 p-5 border-r border-gray-50 shrink-0 sticky left-0 bg-white group-hover/row:bg-slate-50 transition-all z-10 flex items-center gap-4 cursor-pointer" 
                                @click="toggleSelection(emp.id)"
                            >
                                <div class="w-6 h-6 rounded-xl border-2 flex items-center justify-center transition-all shrink-0" 
                                    :class="selection.has(emp.id) ? 'bg-emerald-600 border-emerald-600 shadow-md scale-110' : 'border-gray-200 bg-white'">
                                    <svg v-if="selection.has(emp.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-900 border-2 border-white flex items-center justify-center text-white font-black text-sm uppercase overflow-hidden shrink-0 shadow-lg group-hover/row:bg-emerald-600 transition-all">
                                        <img v-if="emp.avatar" :src="emp.avatar" class="w-full h-full object-cover">
                                        <span v-else>{{ emp.first_name[0] }}</span>
                                    </div>
                                    <div class="truncate">
                                        <p class="text-xs font-black text-slate-900 truncate uppercase tracking-tight group-hover/row:text-emerald-700 transition-colors">{{ emp.first_name }} {{ emp.last_name }}</p>
                                        <p class="text-sm font-black text-slate-400 uppercase tracking-widest truncate mt-2 leading-none">{{ emp.department?.name || 'Operations' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Shift Time Blocks -->
                            <div class="flex-1 flex overflow-x-auto hide-scrollbar">
                                <div v-for="day in days" :key="day" class="min-w-[140px] w-full border-r border-gray-50 p-2.5 flex items-center justify-center">
                                    <div 
                                        class="w-full h-full py-4 rounded-2xl flex flex-col items-center justify-center border-2 transition-all text-center px-2 cursor-pointer shadow-sm active:scale-95 group/shift relative overflow-hidden"
                                        :class="getShiftStyles(getShiftForDay(emp, day))"
                                    >
                                        <div class="absolute inset-y-0 left-0 w-1 opacity-20 bg-current"></div>
                                        <span class="text-sm font-black uppercase tracking-widest truncate w-full leading-none">{{ getShiftForDay(emp, day).name }}</span>
                                        <div v-if="!getShiftForDay(emp, day).isDefault" class="flex items-center gap-1.5 mt-3 opacity-60 group-hover/shift:opacity-100 transition-opacity">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span class="text-sm font-black tracking-tight tabular-nums">
                                                {{ getShiftForDay(emp, day).start_time.slice(0, 5) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Roster Strip View -->
                <div class="lg:hidden flex-1 overflow-y-auto bg-slate-50/50 p-4 space-y-4">
                    <div v-for="emp in (employees?.data || [])" :key="'mb-'+emp.id" class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 space-y-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4" @click="toggleSelection(emp.id)">
                                <div class="w-7 h-7 rounded-xl border-2 flex items-center justify-center transition-all shrink-0" 
                                    :class="selection.has(emp.id) ? 'bg-emerald-600 border-emerald-600 shadow-md shadow-emerald-100' : 'border-gray-200 bg-white'">
                                    <svg v-if="selection.has(emp.id)" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div class="w-12 h-12 rounded-2xl bg-slate-900 border-2 border-white flex items-center justify-center text-white font-black text-xs uppercase overflow-hidden shadow-lg">
                                    {{ emp.first_name[0] }}
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight truncate leading-none">{{ emp.first_name }} {{ emp.last_name }}</h4>
                                    <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mt-2 leading-none">{{ emp.department?.name || 'OPS' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Horizontal Weekly Strip -->
                        <div class="flex overflow-x-auto gap-3 pb-2 hide-scrollbar">
                            <div v-for="day in days" :key="day" class="min-w-[100px] flex flex-col items-center group/mbshift">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-widest mb-3 opacity-60">{{ day.toLocaleDateString('en-US', { weekday: 'short' }) }} {{ day.getDate() }}</span>
                                <div 
                                    class="w-full py-4 px-2 rounded-2xl border-2 flex flex-col items-center justify-center text-center transition-all shadow-sm active:scale-95"
                                    :class="getShiftStyles(getShiftForDay(emp, day))"
                                >
                                    <span class="text-sm font-black uppercase truncate w-full leading-none">{{ getShiftForDay(emp, day).name }}</span>
                                    <span v-if="!getShiftForDay(emp, day).isDefault" class="text-sm font-black mt-2 tabular-nums opacity-70">
                                        {{ getShiftForDay(emp, day).start_time.slice(0, 5) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State (Mobile) -->
                    <div v-if="(employees?.data?.length || 0) === 0 && !loading" class="py-20 text-center animate-in fade-in slide-in-from-bottom-5 duration-700">
                         <div class="w-20 h-20 bg-white rounded-[2rem] flex items-center justify-center text-slate-200 mx-auto border border-gray-100 mb-6 shadow-inner">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em]">No operatives located in sector</h4>
                    </div>
                </div>
            </div>

            <!-- Deployment Protocol Modal -->
            <PremiumModal 
                :show="showAssignModal" 
                @close="showAssignModal = false" 
                title="Shift Assignment Protocol" 
                :subtitle="`${selection.size} Operations Personnel Selected`"
                icon="fa-calendar-check"
                maxWidth="xl"
            >
                <div class="space-y-8 p-2">
                    <div class="space-y-3">
                        <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1">Select Operational Shift Profile</label>
                        <div class="relative group">
                            <select v-model="assignForm.shift_id" class="w-full bg-slate-50 border-2 border-gray-100 rounded-2xl py-4 px-5 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all appearance-none cursor-pointer uppercase tracking-widest shadow-sm">
                                <option value="" disabled>Select Shift Profile...</option>
                                <option v-for="shift in shifts" :key="shift.id" :value="shift.id">
                                    {{ shift.name }} ({{ shift.start_time.slice(0, 5) }} - {{ shift.end_time.slice(0, 5) }})
                                </option>
                            </select>
                            <svg class="w-4 h-4 absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none group-focus-within:text-emerald-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1">Engagement Start</label>
                            <input type="date" v-model="assignForm.start_date" class="w-full bg-slate-50 border-2 border-gray-100 rounded-2xl py-4 px-5 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest shadow-sm">
                        </div>
                        <div class="space-y-3">
                            <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1">Engagement End</label>
                            <input type="date" v-model="assignForm.end_date" class="w-full bg-slate-50 border-2 border-gray-100 rounded-2xl py-4 px-5 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest shadow-sm">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-8 border-t border-gray-100 mt-8">
                        <button @click="showAssignModal = false" class="text-sm font-black uppercase tracking-[0.2em] text-slate-400 hover:text-slate-600 transition-colors">Abort Mission</button>
                        <button @click="submitAssignment" class="h-14 px-10 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-emerald-600 transition-all flex items-center gap-3 shadow-xl shadow-slate-200 active:scale-95 group">
                            <svg class="w-4 h-4 text-emerald-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Confirm Deployment</span>
                        </button>
                    </div>
                </div>
            </PremiumModal>

            <BulkShiftImportModal :show="showImportModal" @close="showImportModal = false" @success="fetchRoster" />
        </div>

    </component>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import EmployeeFilterBar from '@/Components/EmployeeFilterBar.vue';
import BulkShiftImportModal from './Components/BulkShiftImportModal.vue';

defineOptions({ layout: MainLayout });

const toast = useToastStore();
const props = defineProps({
    embedded: Boolean,
    locations: Array,
    departments: Array
});

const loading = ref(false);
const employees = ref({ data: [] });
const shifts = ref([]);
const roster = ref({});
const selection = ref(new Set());

const dateRange = ref({
    start: new Date().toISOString().slice(0, 10),
    end: new Date(new Date().setDate(new Date().getDate() + 6)).toISOString().slice(0, 10)
});

const filters = ref({
    search: '',
    location_id: '',
    department_id: ''
});

const assignForm = ref({
    shift_id: '',
    start_date: '',
    end_date: ''
});

const showAssignModal = ref(false);
const showImportModal = ref(false);

const changeDate = (days) => {
    const s = new Date(dateRange.value.start);
    s.setDate(s.getDate() + days);
    dateRange.value.start = s.toISOString().slice(0, 10);

    const e = new Date(dateRange.value.end);
    e.setDate(e.getDate() + days);
    dateRange.value.end = e.toISOString().slice(0, 10);
};

const days = computed(() => {
    const start = new Date(dateRange.value.start);
    const end = new Date(dateRange.value.end);
    const arr = [];
    for (let dt = new Date(start); dt <= end; dt.setDate(dt.getDate() + 1)) {
        arr.push(new Date(dt));
    }
    return arr;
});

const fetchRoster = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('admin.attendance.roster.data'), {
            params: { 
                start: dateRange.value.start, 
                end: dateRange.value.end,
                ...filters.value
            }
        });
        employees.value = res.data.employees;
        shifts.value = res.data.shifts;
        roster.value = res.data.roster || {};
    } catch (e) {
        toast.error("Failed to load matrix");
    } finally {
        loading.value = false;
    }
};

onMounted(fetchRoster);
watch([dateRange, filters], fetchRoster, { deep: true });

const toggleSelection = (id) => {
    if (selection.value.has(id)) {
        const newSet = new Set(selection.value);
        newSet.delete(id);
        selection.value = newSet;
    } else {
        const newSet = new Set(selection.value);
        newSet.add(id);
        selection.value = newSet;
    }
};

const openAssignModal = () => {
    if (selection.value.size === 0) {
        toast.error("Identify operatives first");
        return;
    }
    assignForm.value.start_date = dateRange.value.start;
    assignForm.value.end_date = dateRange.value.end;
    showAssignModal.value = true;
};

const submitAssignment = async () => {
    try {
        await axios.post(route('admin.attendance.roster.assign'), {
            employee_ids: Array.from(selection.value),
            ...assignForm.value
        });
        toast.success("Deployment successful");
        showAssignModal.value = false;
        fetchRoster();
        selection.value = new Set();
    } catch (e) {
        toast.error("Protocol failed");
    }
};

const getShiftForDay = (employee, date) => {
    const d = date.toISOString().slice(0, 10);
    const shiftId = roster.value[employee.id]?.[d];
    if (shiftId) {
        const shift = shifts.value.find(s => s.id === shiftId);
        return shift || { name: 'Unknown', color: 'gray' };
    }
    return { name: '--', color: 'gray', isDefault: true };
};

const getShiftStyles = (shift) => {
    if (shift.isDefault) return 'bg-slate-50/20 text-slate-300 border-slate-100/50 opacity-60';
    if (shift.name.includes('Morning') || (shift.start_time && shift.start_time < '10:00:00')) 
        return 'bg-amber-50/50 text-amber-600 border-amber-200 shadow-sm shadow-amber-500/5 backdrop-blur-sm';
    if (shift.name.includes('Night') || (shift.start_time && shift.start_time > '18:00:00')) 
        return 'bg-indigo-50/50 text-indigo-700 border-indigo-200 shadow-sm shadow-indigo-500/5 backdrop-blur-sm';
    return 'bg-emerald-50/50 text-emerald-600 border-emerald-200 shadow-sm shadow-emerald-500/5 backdrop-blur-sm';
};
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}

input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(0.4) sepia(1) saturate(5) hue-rotate(200deg);
    cursor: pointer;
}
</style>
