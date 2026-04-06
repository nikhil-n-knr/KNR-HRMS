<template>
  <component :is="embedded ? 'div' : AttendanceLayout" title="Shift Exchange Intelligence" activeTab="swap_requests" v-bind="$props">
    <Head v-if="!embedded" title="Shift Exchange Pulse" />
    
    <div :class="{'max-w-[1600px] mx-auto': !embedded}" class="space-y-12 animate-fade-in relative z-10">
        <!-- Standardized Command Header -->
        <div class="h-auto md:h-14 bg-white/90 backdrop-blur-md rounded-2xl border border-slate-200 p-2 shadow-sm flex flex-col md:flex-row justify-between items-center mb-6 gap-4 font-outfit">
            <div class="flex items-center gap-4 px-2 w-full md:w-auto">
                <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg shrink-0">
                    <i class="fas fa-arrows-left-right text-base"></i>
                </div>
                <div>
                    <h2 class="text-xs font-black text-slate-800 uppercase tracking-tight leading-none">Shift Swaps</h2>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-1.5 leading-none">Exchange Protocols</p>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto px-1">
                <div class="relative flex-1 md:w-48 group">
                    <i class="fas fa-filter absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm group-focus-within:text-emerald-500 transition-colors"></i>
                    <select v-model="filterForm.status" class="w-full h-11 md:h-10 bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-6 text-sm font-black uppercase tracking-widest text-slate-600 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 shadow-inner appearance-none cursor-pointer">
                        <option value="">ALL STATUS</option>
                        <option v-for="s in ['Approved', 'Requested', 'Rejected']" :key="s" :value="s">{{ s.toUpperCase() }}</option>
                    </select>
                </div>
                
                <button @click="exportData" class="h-11 md:h-10 px-4 bg-white text-slate-400 rounded-xl flex items-center justify-center border border-slate-200 shadow-sm hover:text-emerald-500 transition-all active:scale-95 shrink-0">
                    <i class="fas fa-file-export text-sm"></i>
                </button>
                <button @click="openCreateModal" class="flex-[3] md:flex-none h-11 md:h-10 flex items-center justify-center gap-2 bg-slate-900 text-white px-6 rounded-xl hover:bg-emerald-600 transition-all text-sm font-black uppercase tracking-[0.2em] active:scale-95 shadow-xl shadow-slate-200/50">
                    <i class="fas fa-plus text-sm text-emerald-400"></i>
                    <span>New Swap</span>
                </button>
            </div>
        </div>

        <!-- Telemetry Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col relative min-h-[400px]">
            <!-- Desktop View -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-5 py-3 text-left text-sm font-black text-slate-400 uppercase tracking-widest">Temporal Anchor</th>
                            <th class="px-5 py-3 text-left text-sm font-black text-slate-400 uppercase tracking-widest">Requested By</th>
                            <th class="px-5 py-3 text-left text-sm font-black text-slate-400 uppercase tracking-widest">Swap With</th>
                            <th class="px-5 py-3 text-left text-sm font-black text-slate-400 uppercase tracking-widest">Shift Change</th>
                            <th class="px-5 py-3 text-right text-sm font-black text-slate-400 uppercase tracking-widest">Status Protocol</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-if="loading" class="animate-pulse">
                            <td colspan="5" class="py-20 text-center">
                                <div class="w-8 h-8 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                                <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">Loading swaps...</span>
                            </td>
                        </tr>
                        <tr v-else-if="!(swaps?.data || []) || (swaps?.data || []).length === 0" class="text-center">
                            <td colspan="5" class="py-20">
                                <div class="flex flex-col items-center gap-4 opacity-40">
                                    <div class="w-12 h-12 bg-slate-50 rounded-xl flex items-center justify-center text-slate-200">
                                        <i class="fas fa-shuffle text-xl"></i>
                                    </div>
                                    <span class="text-sm font-bold text-slate-300 uppercase tracking-widest">No Swaps Found</span>
                                </div>
                            </td>
                        </tr>
                        
                        <tr v-for="req in (swaps?.data || [])" :key="req.id" class="group hover:bg-emerald-50/20 transition-all duration-150 border-b border-slate-50 last:border-0">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded bg-white border border-slate-100 flex items-center justify-center text-slate-300 group-hover:text-emerald-500 group-hover:border-emerald-100 transition-all shadow-sm">
                                        <i class="fas fa-calendar-alt text-sm"></i>
                                    </div>
                                    <span class="text-sm font-black text-slate-500 uppercase tracking-tighter whitespace-nowrap">{{ new Date(req.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded bg-emerald-600 flex items-center justify-center text-white font-black text-sm border border-white shadow-sm transition-all group-hover:scale-110">
                                        {{ req.requester?.first_name ? req.requester.first_name[0] : '' }}{{ req.requester?.last_name ? req.requester.last_name[0] : '' }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-slate-800 tracking-tighter group-hover:text-emerald-700 transition-all uppercase leading-none">{{ req.requester?.first_name || '' }} {{ req.requester?.last_name || '' }}</div>
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1 leading-none">{{ req.requester?.employee_code || 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <div v-if="req.recipient" class="flex items-center gap-3">
                                     <div class="w-8 h-8 rounded bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 font-black text-sm shadow-sm transition-all group-hover:scale-110 group-hover:bg-emerald-50 group-hover:text-emerald-600">
                                        {{ req.recipient?.first_name ? req.recipient.first_name[0] : '' }}{{ req.recipient?.last_name ? req.recipient.last_name[0] : '' }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-slate-700 tracking-tighter uppercase leading-none">{{ req.recipient?.first_name || '' }} {{ req.recipient?.last_name || '' }}</div>
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1 leading-none">{{ req.recipient?.employee_code || 'N/A' }}</div>
                                    </div>
                                </div>
                                <span v-else class="text-xs font-black text-slate-300 uppercase tracking-widest italic">OPEN_MATRIX</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="px-2 py-0.5 bg-slate-50 rounded text-sm font-black text-slate-500 uppercase tracking-widest border border-slate-100 group-hover:bg-white transition-all shadow-sm">
                                        {{ req.shift_from?.name }}
                                    </div>
                                    <div class="w-6 h-6 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-400 shadow-inner group-hover:scale-110 transition-transform border border-emerald-100">
                                        <i class="fas fa-shuffle text-xs"></i>
                                    </div>
                                    <div class="px-2 py-0.5 bg-emerald-600 rounded text-sm font-black text-white uppercase tracking-widest shadow-sm group-hover:scale-105 transition-all">
                                        {{ req.shift_to?.name }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-right">
                                 <span class="px-2 py-0.5 text-xs font-black rounded border uppercase tracking-widest shadow-sm" 
                                    :class="{
                                        'bg-emerald-50 text-emerald-600 border-emerald-100': req.status === 'Approved', 
                                        'bg-amber-50 text-amber-600 border-amber-100': req.status === 'Requested' || req.status === 'Pending', 
                                        'bg-rose-50 text-rose-600 border-rose-100': req.status === 'Rejected'
                                    }">
                                    {{ req.status }}
                                 </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile View -->
            <div class="lg:hidden divide-y divide-slate-100">
                <div v-if="loading" class="p-10 text-center animate-pulse">
                    <i class="fas fa-spinner fa-spin text-emerald-500 text-xl"></i>
                </div>
                <div v-else-if="!(swaps?.data || []) || (swaps?.data || []).length === 0" class="p-10 text-center text-slate-400 text-sm uppercase font-black tracking-widest">
                    No swap logs detected
                </div>
                <div v-for="req in (swaps?.data || [])" :key="'mb-'+req.id" class="p-4 space-y-4 group relative overflow-hidden">
                    <!-- MRT Header: Transfer Status -->
                    <div class="flex justify-between items-center relative z-10">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 bg-slate-900 text-white rounded-lg text-xs font-black uppercase tracking-[0.2em] shadow-lg">{{ new Date(req.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</span>
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-1.5 py-1 rounded border border-slate-100">LOG: #EX-{{ String(req.id).padStart(3, '0') }}</span>
                        </div>
                        <span class="px-2 py-1 text-xs font-black rounded-lg border uppercase tracking-widest shadow-xs" 
                            :class="{
                                'bg-emerald-50 text-emerald-600 border-emerald-100': req.status === 'Approved', 
                                'bg-amber-50 text-amber-600 border-amber-100': req.status === 'Requested' || req.status === 'Pending', 
                                'bg-rose-50 text-rose-600 border-rose-100': req.status === 'Rejected'
                            }">
                            {{ req.status }}
                        </span>
                    </div>

                    <!-- MRT Exchange Terminal -->
                    <div class="bg-slate-50/80 p-4 rounded-3xl border border-slate-100 relative z-10 group-hover:bg-white transition-all group-hover:border-emerald-500/30">
                        <div class="flex items-center justify-between gap-4">
                            <!-- Requester Node -->
                            <div class="flex flex-col items-center gap-2 text-center flex-1 min-w-0">
                                <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center text-white font-black text-base border-2 border-white shadow-xl group-hover:bg-emerald-600 transition-colors">
                                    {{ req.requester?.first_name[0] }}{{ req.requester?.last_name[0] }}
                                </div>
                                <div class="min-w-0 w-full px-1">
                                    <p class="text-sm font-black text-slate-800 uppercase tracking-tighter truncate leading-none mb-1">{{ req.requester?.first_name }}</p>
                                    <div class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-xs font-black text-slate-500 uppercase tracking-widest truncate">{{ req.shift_from?.name }}</div>
                                </div>
                            </div>

                            <!-- Vector Icon -->
                            <div class="shrink-0 relative">
                                <div class="w-10 h-10 rounded-full bg-white shadow-lg border border-slate-200 flex items-center justify-center text-emerald-500 z-10 relative group-hover:rotate-180 transition-transform duration-500">
                                    <i class="fas fa-arrows-spin text-[14px]"></i>
                                </div>
                                <div class="absolute inset-0 bg-emerald-500/20 rounded-full blur-xl scale-150 animate-pulse"></div>
                            </div>

                            <!-- Target Node -->
                            <div class="flex flex-col items-center gap-2 text-center flex-1 min-w-0">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-600 flex items-center justify-center text-white font-black text-base border-2 border-white shadow-xl">
                                    {{ req.requested_with?.first_name[0] }}{{ req.requested_with?.last_name[0] }}
                                </div>
                                <div class="min-w-0 w-full px-1">
                                    <p class="text-sm font-black text-slate-800 uppercase tracking-tighter truncate leading-none mb-1">{{ req.requested_with?.first_name }}</p>
                                    <div class="px-1.5 py-0.5 bg-emerald-50 border border-emerald-100 rounded text-xs font-black text-emerald-600 uppercase tracking-widest truncate">{{ req.shift_to?.name }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Neural Pagination Terminal -->
            <div class="px-5 py-3 bg-slate-50/50 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4" v-if="(swaps?.data || []) && (swaps?.data || []).length > 0">
                <div class="flex items-center gap-2">
                    <button @click="fetchData(swaps.current_page - 1)" :disabled="swaps.current_page === 1" class="w-7 h-7 rounded bg-white border border-slate-200 text-slate-400 flex items-center justify-center hover:bg-slate-50 hover:text-emerald-600 active:scale-95 disabled:opacity-20 transition-all shadow-sm">
                        <i class="fas fa-chevron-left text-sm"></i>
                    </button>
                    <div class="bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-inner">
                        <span class="text-sm font-black text-slate-400 uppercase tracking-widest whitespace-nowrap mt-0.5 leading-none">SEGMENT <span class="text-emerald-600">{{ swaps.current_page }}</span> / {{ swaps.last_page }}</span>
                    </div>
                    <button @click="fetchData(swaps.current_page + 1)" :disabled="swaps.current_page === swaps.last_page" class="w-7 h-7 rounded bg-white border border-slate-200 text-slate-400 flex items-center justify-center hover:bg-slate-50 hover:text-emerald-600 active:scale-95 disabled:opacity-20 transition-all shadow-sm">
                        <i class="fas fa-chevron-right text-sm"></i>
                    </button>
                </div>
                <div class="text-right">
                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest block leading-none mb-1">Matrix Load</span>
                    <span class="text-xs font-black text-slate-800 tracking-tighter leading-none">{{ swaps.total || 0 }} OBJECTS</span>
                </div>
            </div>
        </div>

        <!-- SYNERGY DEPLOYMENT MODAL -->
        <PremiumModal 
            :show="showCreateModal" 
            @close="closeCreateModal" 
            title="Synergy Protocol" 
            subtitle="Exchange Parameter Configuration"
            icon="fa-arrows-spin"
            maxWidth="3xl"
        >
                <div class="space-y-4 pt-2">
                    <!-- Target Operatives -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                         <div class="space-y-1.5">
                            <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1 leading-none">Primary Operative</label>
                            <div class="relative group mt-1">
                                <Combobox 
                                    v-model="createForm.requester_id" 
                                    :items="employees" 
                                    :display-format="employeeDisplay"
                                    placeholder="Search Operative ID..."
                                    :error="errors.requester_id"
                                    variant="modern"
                                />
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1 leading-none">Impact Timeline</label>
                            <div class="relative group mt-1">
                                <i class="fas fa-calendar-check absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:text-emerald-500 transition-colors text-sm"></i>
                                <input type="date" v-model="createForm.date" class="w-full h-10 bg-slate-50 border border-slate-200 rounded-lg pl-10 pr-4 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest">
                                <p v-if="errors.date" class="text-xs font-black text-rose-500 uppercase tracking-widest mt-1 px-1">{{ errors.date }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Synergy Exchange Logic -->
                    <div class="p-6 bg-emerald-50/50 rounded-xl border border-emerald-100/50 space-y-6 relative mt-4">
                         <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-10 h-10 bg-white rounded-full flex items-center justify-center text-emerald-400 shadow-md border border-emerald-50 z-10 transition-transform hover:rotate-180 duration-500">
                            <i class="fas fa-shuffle text-base"></i>
                         </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 relative">
                            <div class="space-y-2">
                                <label class="text-xs font-black text-emerald-600 uppercase tracking-widest px-1 block text-center">SOURCE_ALLOCATION</label>
                                <select v-model="createForm.shift_id_from" class="w-full h-10 bg-white border border-emerald-200 rounded-lg px-4 text-sm font-black uppercase text-slate-600 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 shadow-sm transition-all appearance-none cursor-pointer text-center tracking-widest">
                                    <option value="" disabled>SELECT_SEGMENT</option>
                                    <option v-for="shift in shifts" :key="shift.id" :value="shift.id">{{ shift.name.toUpperCase() }}</option>
                                </select>
                            </div>
                             <div class="space-y-2">
                                <label class="text-xs font-black text-emerald-600 uppercase tracking-widest px-1 block text-center">TARGET_ALIGNMENT</label>
                                <select v-model="createForm.shift_id_to" class="w-full h-10 bg-white border border-emerald-200 rounded-lg px-4 text-sm font-black uppercase text-slate-600 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 shadow-sm transition-all appearance-none cursor-pointer text-center tracking-widest">
                                    <option value="" disabled>SELECT_TARGET</option>
                                    <option v-for="shift in shifts" :key="shift.id" :value="shift.id">{{ shift.name.toUpperCase() }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Recipient Alignment -->
                    <div class="space-y-3 pt-2">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1 leading-none">Counterpart Operative</label>
                        <Combobox 
                            v-model="createForm.recipient_id" 
                            :items="employees" 
                            :display-format="employeeDisplay"
                            placeholder="Select Exchange Partner Profile..."
                            :error="errors.recipient_id"
                        />
                        <div class="bg-emerald-50/50 p-2.5 rounded-lg flex items-center gap-3 border border-emerald-100/30">
                            <i class="fas fa-info-circle text-emerald-500 text-sm"></i>
                            <p class="text-xs font-black text-emerald-600 uppercase tracking-widest leading-none">LOGIC_SYNCHRONIZED_WITH_COUNTERPART_TERMINAL</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-8 mt-4 border-t border-slate-100">
                        <button @click="closeCreateModal" class="px-4 py-2.5 rounded-lg text-sm font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all active:scale-95">
                            ABORT_PROTOCOL
                        </button>
                        <button @click="submitCreate" class="h-10 px-6 bg-slate-900 text-white rounded-lg text-sm font-black uppercase tracking-widest shadow-md hover:bg-slate-800 active:scale-95 transition-all flex items-center gap-2 group">
                            <i class="fas fa-check-double text-sm text-emerald-400 group-hover:scale-110 transition-transform"></i>
                            DEPLOY_PROTOCOL
                        </button>
                    </div>
                </div>
        </PremiumModal>
    </div>
  </component>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useToastStore } from '@/stores/toast';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import { Head } from '@inertiajs/vue3';
import PremiumModal from '@/Components/PremiumModal.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import Combobox from '@/Components/Combobox.vue';
import axios from 'axios';

defineOptions({ layout: MainLayout });

const props = defineProps({
    embedded: Boolean,
    swaps: { type: Object, default: () => ({ data: [], total: 0, current_page: 1, last_page: 1 }) },
    employees: { type: Array, default: () => [] },
    shifts: { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
    locations: { type: Array, default: () => [] }
});

const toast = useToastStore();
const swaps = ref(props.swaps || { data: [], total: 0, current_page: 1, last_page: 1 });
const shifts = ref(props.shifts || []);
const employees = ref(props.employees || []);
const loading = ref(false);

watch(() => props.swaps, (newVal) => { if (newVal) swaps.value = newVal; }, { immediate: true });
watch(() => props.employees, (newVal) => { if (newVal) employees.value = newVal; }, { immediate: true });
watch(() => props.shifts, (newVal) => { if (newVal) shifts.value = newVal; }, { immediate: true });

const filterForm = ref({
    status: '',
    search: '',
    department_id: '',
    location_id: '',
});

const fetchData = async (page = 1) => {
    loading.value = true;
    try {
        const res = await axios.get('/admin/attendance/swaps', {
            params: { page, ...filterForm.value },
            headers: { Accept: 'application/json' }
        });
        swaps.value = res.data.swaps || res.data;
    } catch(e) {
        toast.error("SYNERGY_SYNC_FAILED");
    } finally {
        loading.value = false;
    }
};

watch(() => filterForm.value.status, () => fetchData(1));

const exportData = () => {
    const params = new URLSearchParams(filterForm.value).toString();
    window.location.href = `/admin/attendance/swaps/export?${params}`;
    toast.success("Excel export initiated");
};

// Create Logic
const showCreateModal = ref(false);
const createForm = ref({
    requester_id: '',
    date: '',
    shift_id_from: '',
    shift_id_to: '',
    recipient_id: ''
});

const errors = ref({});

const openCreateModal = () => {
    createForm.value = { requester_id: '', date: new Date().toISOString().split('T')[0], shift_id_from: '', shift_id_to: '', recipient_id: '' };
    errors.value = {};
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    errors.value = {};
};

const submitCreate = async () => {
    errors.value = {};
    
    if (!createForm.value.requester_id) errors.value.requester_id = "Operative required";
    if (!createForm.value.recipient_id) errors.value.recipient_id = "Counterpart required";
    if (!createForm.value.date) errors.value.date = "Impact timeline required";
    if (!createForm.value.shift_id_from) errors.value.shift_id_from = "Source shift required";
    if (!createForm.value.shift_id_to) errors.value.shift_id_to = "Target shift required";

    if (Object.keys(errors.value).length > 0) {
        toast.error("Please fix the validation errors");
        return;
    }

    try {
        await axios.post('/admin/attendance/swaps/store', createForm.value);
        toast.success("Swap request submitted successfully");
        closeCreateModal();
        fetchData();
    } catch(e) {
        if (e.response?.status === 422) {
             errors.value = e.response.data.errors;
             toast.error("Validation failed");
        } else {
             toast.error(e.response?.data?.message || "Failed to submit request");
        }
    }
};

const employeeDisplay = (item) => `${item.first_name} ${item.last_name} [${item.employee_code || item.id}]`;

watch(() => createForm.value.requester_id, (newId) => {
    if (newId) {
        const emp = employees.value.find(e => e.id === newId);
        if (emp && emp.current_shift_id) createForm.value.shift_id_from = emp.current_shift_id;
    }
});

watch(() => createForm.value.recipient_id, (newId) => {
    if (newId) {
        const emp = employees.value.find(e => e.id === newId);
        if (emp && emp.current_shift_id) createForm.value.shift_id_to = emp.current_shift_id;
    }
});

onMounted(() => {
    if (props.swaps && props.swaps.data && props.swaps.data.length > 0) {
        // Data already exists, no immediate fetch needed if Inertia loaded it
    } else {
        fetchData();
    }
});
</script>
