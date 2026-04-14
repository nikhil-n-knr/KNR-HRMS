<template>
  <component :is="embedded ? 'div' : AttendanceLayout" title="Shift Master List" activeTab="shift_config" v-bind="$props">
    <Head v-if="!embedded" title="Workforce Shift Logic" />
    
    <div class="space-y-6">
        <!-- Compact Intelligence Command Bar -->
        <div class="h-auto xl:h-16 bg-white/90 backdrop-blur-md rounded-2xl border border-slate-200 p-3 md:p-2 shadow-sm flex flex-col xl:flex-row justify-between items-center mb-6 gap-4 font-outfit">
            <div class="flex items-center gap-3 px-2 w-full xl:w-auto">
                <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg shrink-0">
                    <i class="fas fa-business-time text-base"></i>
                </div>
                <div>
                    <h2 class="text-xs font-black text-slate-800 uppercase tracking-tight leading-none">Shift Architect</h2>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-1.5 leading-none">Global Patterns</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto px-1">
                <div class="relative group w-full sm:w-64">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm group-focus-within:text-emerald-500 transition-colors"></i>
                    <input v-model="searchQuery" type="text" placeholder="Search Filter..." 
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl h-11 pl-10 pr-4 text-sm font-black text-slate-600 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all shadow-inner uppercase tracking-widest">
                </div>

                <div class="flex gap-2 w-full sm:w-auto">
                    <button @click="exportShifts" class="h-11 w-11 bg-white text-slate-400 rounded-xl flex items-center justify-center border border-slate-200 shadow-sm hover:text-emerald-500 transition-all active:scale-95 shrink-0">
                        <i class="fas fa-file-export text-base"></i>
                    </button>
                    <button @click="openCreateModal" class="h-11 flex-1 sm:flex-none px-6 bg-slate-900 text-white rounded-xl text-sm font-black uppercase tracking-[0.3em] hover:bg-emerald-600 transition-all flex items-center justify-center gap-2 shadow-lg active:scale-95 whitespace-nowrap">
                        <i class="fas fa-plus text-sm text-emerald-400"></i>
                        New Profile
                    </button>
                </div>
            </div>
        </div>

        <!-- Shift Matrix Table -->
        <!-- Shift Matrix Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col relative hover:border-emerald-500/30 transition-colors">
            <!-- Desktop Table -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-slate-900 border-b border-slate-800">
                            <th class="px-4 py-3 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Identity</span>
                            </th>
                            <th class="px-4 py-3 text-center">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Timeline</span>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Cycle</span>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Grace</span>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Status</span>
                            </th>
                            <th class="px-4 py-3 text-right">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="loading" class="animate-pulse">
                            <td colspan="6" class="px-6 py-20 text-center text-slate-300">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <div class="text-sm font-bold uppercase tracking-widest">Syncing Matrix...</div>
                            </td>
                        </tr>
                        <tr v-else-if="tableData.length === 0" class="text-center">
                            <td colspan="6" class="p-20">
                                 <div class="flex flex-col items-center gap-4 text-slate-300">
                                    <i class="fas fa-clock text-4xl"></i>
                                    <span class="text-base font-bold uppercase tracking-widest">No shift protocols found</span>
                                </div>
                            </td>
                        </tr>
                        
                        <tr v-for="item in tableData" :key="item.id" class="hover:bg-emerald-50/20 transition-all group border-b border-slate-50 last:border-0 border-l border-l-transparent hover:border-l-emerald-500">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded bg-emerald-600 flex items-center justify-center text-white text-sm font-black border border-white shadow-sm shrink-0" :style="{ backgroundColor: item.color || '#059669' }">
                                        {{ (item.name || 'S').substring(0,2).toUpperCase() }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-slate-800 leading-none mb-1 uppercase tracking-tighter group-hover:text-emerald-700 transition-colors">{{ item.name }}</div>
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest font-mono">{{ item.code || 'SYS_LOG' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2 bg-slate-50 px-2.5 py-1 rounded border border-slate-100 w-fit mx-auto shadow-inner">
                                    <span class="text-sm font-black text-slate-600 tracking-tighter leading-none tabular-nums uppercase">{{ item.start_time }} - {{ item.end_time }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="day in item.work_days" :key="day" class="text-xs px-1 py-0.5 bg-slate-100 text-slate-500 font-black uppercase rounded border border-slate-200">
                                        {{ day.substring(0,2) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    <div v-if="item.grace_late_entry > 0" class="flex items-center gap-1.5">
                                        <div class="w-1 h-1 rounded-full bg-emerald-400 shadow-sm shadow-emerald-500/50"></div>
                                        <span class="text-xs font-black text-emerald-600 uppercase tracking-widest">LATE: {{ item.grace_late_entry }}M</span>
                                    </div>
                                    <div v-if="item.grace_early_exit > 0" class="flex items-center gap-1.5">
                                        <div class="w-1 h-1 rounded-full bg-rose-400 shadow-sm shadow-rose-500/50"></div>
                                        <span class="text-xs font-black text-rose-600 uppercase tracking-widest">EXIT: {{ item.grace_early_exit }}M</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span v-if="item.is_default" class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-600 text-xs font-black border border-emerald-100 uppercase tracking-widest block w-fit shadow-sm">
                                    PRIMARY_NODE
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                 <div class="flex justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-all translate-x-1 group-hover:translate-x-0">
                                    <button @click.prevent="openEditModal(item)" class="w-7 h-7 rounded-md bg-white text-slate-400 flex items-center justify-center hover:bg-emerald-50 hover:text-emerald-600 transition-all border border-slate-200 shadow-sm">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button @click.prevent="confirmDelete(item.id)" class="w-7 h-7 rounded-md bg-white text-slate-400 flex items-center justify-center hover:bg-rose-50 hover:text-rose-600 transition-all border border-slate-200 shadow-sm">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden divide-y divide-slate-100">
                <div v-if="loading" class="p-10 text-center animate-pulse">
                    <i class="fas fa-spinner fa-spin text-emerald-500 text-xl"></i>
                </div>
                <div v-else-if="tableData.length === 0" class="p-10 text-center text-slate-400 text-sm uppercase font-black tracking-widest">
                    No protocols found
                </div>
                <div v-for="item in tableData" :key="item.id" class="p-4 space-y-4 group relative overflow-hidden">
                    <!-- MRT Header: Shift Identity -->
                    <div class="flex justify-between items-center relative z-10">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-base font-black border-2 border-white shadow-lg shrink-0 group-hover:rotate-12 transition-transform" :style="{ backgroundColor: item.color || '#000000' }">
                                {{ (item.name || 'S').substring(0,1).toUpperCase() }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-black text-slate-800 uppercase tracking-tighter leading-none truncate">{{ item.name }}</h3>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100 shrink-0">CODE: {{ item.code }}</span>
                                    <span v-if="item.is_default" class="text-xs font-black text-emerald-600 uppercase tracking-widest leading-none">PRIMARY_NODE</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2 shrink-0">
                             <button @click.prevent="openEditModal(item)" class="w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 flex items-center justify-center hover:bg-emerald-50 hover:text-emerald-600 active:scale-95 transition-all shadow-sm">
                                <i class="fas fa-sliders-h text-sm"></i>
                             </button>
                             <button @click.prevent="confirmDelete(item.id)" class="w-9 h-9 rounded-xl bg-white border border-rose-200 text-rose-400 flex items-center justify-center hover:bg-rose-50 hover:text-rose-600 active:scale-95 transition-all shadow-sm">
                                <i class="fas fa-trash-can text-sm"></i>
                             </button>
                        </div>
                    </div>

                    <!-- MRT Terminal: Temporal Metrics -->
                    <div class="bg-slate-50/80 p-3 rounded-2xl border border-slate-100 group-hover:border-slate-900/10 transition-all relative z-10 hover:bg-white grid grid-cols-2 gap-3">
                        <div class="border-r border-slate-200/50 pr-2">
                             <span class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-1">Shift Hours</span>
                             <div class="flex items-center gap-2">
                                 <span class="text-sm font-black text-slate-800 tabular-nums">{{ item.start_time.slice(0,5) }}</span>
                                 <i class="fas fa-arrow-right text-xs text-slate-300"></i>
                                 <span class="text-sm font-black text-slate-800 tabular-nums">{{ item.end_time.slice(0,5) }}</span>
                             </div>
                        </div>
                        <div class="pl-1 min-w-0">
                             <span class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-1">Grace Thresholds</span>
                             <div class="flex items-center gap-1.5">
                                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-sm animate-pulse" v-if="item.grace_late_entry > 0"></div>
                                <span class="text-xs font-black text-slate-600 uppercase tracking-tighter truncate">L:{{ item.grace_late_entry }}M / E:{{ item.grace_early_exit }}M</span>
                             </div>
                        </div>
                    </div>

                    <!-- Cycle Ribbon -->
                    <div class="flex flex-wrap gap-1 relative z-10">
                        <span v-for="day in item.work_days" :key="day" class="px-2 py-0.5 bg-white border border-slate-200 rounded text-xs font-black text-slate-400 uppercase tracking-widest group-hover:border-slate-300 transition-colors">
                            {{ day.substring(0,3) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compact Temporal Configuration Modal -->
        <PremiumModal 
            :show="showModal" 
            @close="showModal = false" 
            :title="isEditing ? 'Modify Shift' : 'New Shift Protocol'" 
            subtitle="Configure operational hours"
            icon="fa-clock"
            maxWidth="xl"
            @confirm="submit"
        >
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Shift Name</label>
                        <input v-model="form.name" type="text" placeholder="e.g. STANDARD_MORNING" class="w-full bg-slate-50 border border-slate-200 rounded-lg h-10 px-3 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest">
                        <p v-if="errors.name" class="text-xs font-black text-rose-500 uppercase tracking-widest mt-0.5">{{ errors.name }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Color Marker</label>
                        <div class="flex items-center gap-2 bg-slate-50 p-1.5 rounded-lg border border-slate-200 h-10">
                            <input type="color" v-model="form.color" class="h-6 w-10 border-none rounded cursor-pointer bg-white p-0.5" />
                            <input v-model="form.color" type="text" class="flex-1 bg-transparent border-none py-1 text-sm font-black text-slate-500 font-mono tracking-widest focus:ring-0 uppercase">
                        </div>
                    </div>
                </div>

                <div class="bg-emerald-50/20 p-4 rounded-xl border border-emerald-100 grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-sm font-black text-emerald-600 uppercase tracking-widest px-1 leading-none">Start Pulse</label>
                        <div class="relative group mt-1">
                            <i class="fas fa-clock absolute left-3 top-1/2 -translate-y-1/2 text-emerald-300 pointer-events-none group-focus-within:text-emerald-500 transition-colors text-sm"></i>
                            <input type="time" v-model="form.start_time" class="w-full bg-white border border-emerald-100 rounded-lg h-10 pl-9 pr-3 text-sm font-black text-slate-700 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all appearance-none cursor-pointer">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-black text-emerald-600 uppercase tracking-widest px-1 leading-none">End Pulse</label>
                        <div class="relative group mt-1">
                            <i class="fas fa-clock absolute left-3 top-1/2 -translate-y-1/2 text-emerald-300 pointer-events-none group-focus-within:text-emerald-500 transition-colors text-sm"></i>
                            <input type="time" v-model="form.end_time" class="w-full bg-white border border-emerald-100 rounded-lg h-10 pl-9 pr-3 text-sm font-black text-slate-700 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all appearance-none cursor-pointer">
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Weekly Cycle</label>
                    <div class="flex flex-wrap gap-1.5">
                        <button 
                            v-for="day in weekDays" 
                            :key="day"
                            type="button"
                            @click="form.work_days.includes(day) ? form.work_days = form.work_days.filter(d => d !== day) : form.work_days.push(day)"
                            class="h-9 rounded-lg text-sm font-black uppercase tracking-widest border transition-all duration-200 flex-1 min-w-[65px] shadow-sm"
                            :class="form.work_days.includes(day) ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-400 border-slate-200 hover:border-emerald-300 hover:text-emerald-600'"
                        >
                            {{ day.substring(0,3) }}
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Late Tolerance (Min)</label>
                        <div class="relative">
                            <input type="number" v-model="form.grace_late_entry" class="w-full bg-slate-50 border border-slate-200 rounded-lg h-10 px-3 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Exit Tolerance (Min)</label>
                        <div class="relative">
                            <input type="number" v-model="form.grace_early_exit" class="w-full bg-slate-50 border border-slate-200 rounded-lg h-10 px-3 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest">
                        </div>
                    </div>
                </div>

                <div class="bg-emerald-600 p-2.5 rounded-lg border border-emerald-500 flex items-center justify-between group transition-all shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded bg-white/20 flex items-center justify-center text-white backdrop-blur-md">
                            <i class="fas fa-fingerprint text-sm"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-white uppercase tracking-widest leading-none">Global Default</h4>
                            <p class="text-xs font-bold text-emerald-100 uppercase tracking-widest mt-1">Primary operational node</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="form.is_default" class="sr-only peer">
                        <div class="w-8 h-4 bg-white/20 rounded-full peer peer-checked:bg-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-emerald-600 after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:after:translate-x-4 shadow-inner"></div>
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-6 border-t border-slate-100">
                    <button @click="showModal = false" type="button" class="px-4 py-2 rounded-lg text-sm font-black text-slate-400 uppercase tracking-widest hover:bg-slate-50 transition-all">Abort</button>
                    <button @click="submit" class="h-10 px-6 bg-slate-900 text-white rounded-lg text-sm font-black uppercase tracking-widest hover:bg-slate-800 transition-all flex items-center gap-2 shadow-sm active:scale-95">
                        <i class="fas fa-shield-check text-sm text-emerald-400"></i>
                        <span>{{ isEditing ? 'Commit Changes' : 'Deploy Protocol' }}</span>
                    </button>
                </div>
            </div>
        </PremiumModal>

        <!-- Compact Confirmation Modal -->
        <PremiumModal 
            :show="showConfirmModal" 
            @close="showConfirmModal = false" 
            title="Confirm Deletion" 
            subtitle="Irreversible system action"
            icon="fa-trash-can"
            maxWidth="md"
        >
            <div class="p-4 text-center">
                 <p class="text-sm font-black text-slate-500 uppercase tracking-widest mb-10 leading-relaxed max-w-xs mx-auto">Decommissioning this shift protocol will permanently remove it from the matrix. This action is irreversible.</p>
                 <div class="flex flex-col gap-2">
                    <button @click="confirmCallback" class="w-full h-11 bg-rose-600 text-white rounded-lg text-sm font-black uppercase tracking-[0.2em] shadow-lg shadow-rose-500/20 hover:bg-rose-700 transition-all active:scale-95">
                        Execute Purge
                    </button>
                    <button @click="showConfirmModal = false" class="w-full h-11 text-sm font-black uppercase tracking-widest text-slate-400 hover:bg-slate-50 rounded-lg transition-all">
                        Abort Sequence
                    </button>
                 </div>
            </div>
        </PremiumModal>
    </div>

  </component>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PremiumModal from '@/Components/PremiumModal.vue';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useToastStore } from '@/stores/toast';

defineOptions({ layout: MainLayout });

const props = defineProps({
    embedded: Boolean,
    shifts: { type: Array, default: () => [] },
    filters: Object,
    departments: Array,
    locations: Array
});

const toast = useToastStore();
const loading = ref(false);
const showModal = ref(false);
const isEditing = ref(false);

const shifts = ref(props.shifts || []);
const tableData = ref([]);
const searchQuery = ref('');

const form = ref({
    id: null,
    name: '',
    code: '',
    start_time: '09:00',
    end_time: '18:00',
    work_days: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
    grace_late_entry: 15,
    grace_early_exit: 15,
    color: '#3b82f6',
    is_default: false
});
const errors = ref({});

const weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

const fetchShifts = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/admin/attendance/shifts-list'); 
        const data = Array.isArray(res.data) ? res.data : (res.data.shifts || []);
        shifts.value = data;
        filterData();
    } catch (e) {
        toast.error("Failed to load shifts");
    } finally {
        loading.value = false;
    }
};

const filterData = () => {
    let data = Array.isArray(shifts.value) ? shifts.value : [];
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        data = data.filter(s => 
            (s.name && s.name.toLowerCase().includes(q)) || 
            (s.code && s.code.toLowerCase().includes(q))
        );
    }
    tableData.value = data;
};

watch(searchQuery, filterData);

watch(() => props.shifts, (newShifts) => {
    if (newShifts) {
        shifts.value = Array.isArray(newShifts) ? newShifts : [];
        filterData();
    }
}, { immediate: true });

const openCreateModal = () => {
    isEditing.value = false;
    form.value = {
        id: null, name: '', code: '', start_time: '09:00', end_time: '18:00',
        work_days: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'], grace_late_entry: 15,
        grace_early_exit: 15, color: '#3b82f6', is_default: false
    };
    errors.value = {};
    showModal.value = true;
};

const openEditModal = (shift) => {
    isEditing.value = true;
    form.value = { 
        ...shift, 
        work_days: Array.isArray(shift.work_days) ? shift.work_days : JSON.parse(shift.work_days || '[]')
    }; 
    errors.value = {};
    showModal.value = true;
};

const submit = () => {
    if (isEditing.value) {
        router.put(`/admin/attendance/shifts/${form.value.id}`, form.value, {
            onSuccess: () => {
                toast.success("Protocol updated");
                showModal.value = false;
            },
            onError: (err) => {
                errors.value = err;
                toast.error("Validation lock engaged");
            }
        });
    } else {
        router.post('/admin/attendance/shifts', form.value, {
            onSuccess: () => {
                toast.success("Protocol deployed");
                showModal.value = false;
            },
            onError: (err) => {
                errors.value = err;
                toast.error("Validation lock engaged");
            }
        });
    }
};

const showConfirmModal = ref(false);
const confirmCallback = ref(null);

const confirmDelete = (id) => {
    confirmCallback.value = () => {
        router.delete(`/admin/attendance/shifts/${id}`, {
            onSuccess: () => {
                toast.success("Protocol deconstructed");
                showConfirmModal.value = false;
            },
            onError: () => {
                toast.error("Process termination failed");
            }
        });
    };
    showConfirmModal.value = true;
};

const exportShifts = () => {
    window.location.href = '/admin/attendance/shifts/export';
    toast.success("Temporal matrix exported");
};

onMounted(() => {
    if (!props.shifts || !props.shifts.length) {
        fetchShifts();
    } else {
        filterData();
    }
});
</script>

<style scoped>
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

.animate-fade-in {
    animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

input[type="time"]::-webkit-calendar-picker-indicator {
    filter: invert(0.4) sepia(1) saturate(5) hue-rotate(200deg);
    cursor: pointer;
}
</style>
