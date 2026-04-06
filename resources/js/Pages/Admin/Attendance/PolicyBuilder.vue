<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import PremiumModal from '@/Components/PremiumModal.vue';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const toast = useToastStore();

const props = defineProps({
    policy: Object,
    shifts: { type: Array, default: () => [] },
    locations: { type: Array, default: () => [] },
    subTab: { type: String, default: 'global' },
    embedded: { type: Boolean, default: false }
});

// State
const loading = ref(false);
const processing = ref(false);
const activeSection = ref(props.subTab || 'global');

const policyForm = ref({
    late_mark_threshold: 3,
    sandwich_rule_enabled: false,
    overtime_policy: { rate: 1.5, min_minutes: 60 },
    deduction_rule: { deduct_leave: 0.5, type: 'CL' }
});

// Shift Management State
const localShifts = ref(props.shifts || []);

// React to Prop Updates 
watch(() => props.shifts, (newShifts) => {
    localShifts.value = newShifts || [];
}, { deep: true });

watch(() => props.policy, (newPolicy) => {
    if (newPolicy) policyForm.value = newPolicy;
}, { deep: true });

const showShiftModal = ref(false);
const showDeleteModal = ref(false);
const shiftEditMode = ref(false);
const itemToDelete = ref(null);
const locationFilter = ref('');

// Shift Form
const shiftForm = useForm({
    id: null,
    name: '',
    code: '',
    start_time: '09:00',
    end_time: '18:00',
    work_days: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
    grace_late_entry: 0,
    grace_early_exit: 0,
    color: '#3B82F6',
    location_ids: [],
    is_default: false,
});

const fetchData = async () => {
    loading.value = true;
    try {
        const jsonRes = await axios.get('/admin/attendance/policies', {
             headers: { 'Accept': 'application/json' }
        });
        
        if (jsonRes.data.policy) {
            policyForm.value = jsonRes.data.policy;
        }
        localShifts.value = jsonRes.data.shifts;
    } catch (e) {
        console.error("Policy refresh failed", e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    if (props.policy) {
        policyForm.value = props.policy;
    }
});

// Global Policy Actions
const submitPolicy = async () => {
    processing.value = true;
    try {
        await axios.post('/admin/attendance/policies/update', policyForm.value);
        toast.success("Global rules updated successfully");
    } catch (e) {
        toast.error("Failed to update rules");
    } finally {
        processing.value = false;
    }
};

// Shift Actions
const openCreateShiftModal = () => {
    shiftEditMode.value = false;
    shiftForm.reset();
    shiftForm.clearErrors();
    shiftForm.work_days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
    shiftForm.color = '#3B82F6';
    showShiftModal.value = true;
};

const openEditShiftModal = (shift) => {
    shiftEditMode.value = true;
    shiftForm.clearErrors();
    
    shiftForm.id = shift.id;
    shiftForm.name = shift.name;
    shiftForm.code = shift.code;
    shiftForm.start_time = shift.start_time.slice(0, 5); 
    shiftForm.end_time = shift.end_time.slice(0, 5);
    shiftForm.work_days = shift.work_days || [];
    shiftForm.grace_late_entry = shift.grace_late_entry;
    shiftForm.grace_early_exit = shift.grace_early_exit;
    shiftForm.color = shift.color || '#3B82F6';
    shiftForm.location_ids = (shift.location_ids || []).map(String); 
    shiftForm.is_default = !!shift.is_default;

    showShiftModal.value = true;
};

const submitShiftForm = () => {
    const url = shiftEditMode.value ? `/admin/attendance/shifts/${shiftForm.id}` : '/admin/attendance/shifts';
    const method = shiftEditMode.value ? 'put' : 'post';

    shiftForm[method](url, {
        onSuccess: () => {
            showShiftModal.value = false;
            toast.success(shiftEditMode.value ? 'Shift updated successfully' : 'Shift created successfully');
            shiftForm.reset();
            fetchData();
        },
        onError: () => {
            toast.error('Please fix the validation errors.');
        }
    });
};

const confirmDeleteShift = (shift) => {
    itemToDelete.value = shift;
    showDeleteModal.value = true;
};

const deleteShift = () => {
    shiftForm.delete(`/admin/attendance/shifts/${itemToDelete.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            itemToDelete.value = null;
            toast.success('Shift deleted successfully');
            fetchData();
        },
        onError: () => {
             showDeleteModal.value = false;
             toast.error('Failed to delete shift.');
        }
    });
};

const weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

const openAccordion = ref('punctuality');
</script>

<template>
  <component :is="embedded ? 'div' : AttendanceLayout" title="Policy Matrix" activeTab="policy" v-bind="$props">
    <Head v-if="!embedded" title="Policy Builder" />
    
    <div :class="{'max-w-[1600px] mx-auto': !embedded}" class="space-y-8 pb-12 px-4 md:px-0 font-outfit">
        <!-- Specialized Logic Header -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="flex items-center gap-5">
                <div class="p-4 bg-slate-900 border border-slate-800 rounded-2xl text-violet-400 shadow-xl shadow-slate-200/50 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-violet-500/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <svg class="w-7 h-7 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <div>
                    <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                        Policy Matrix
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-violet-50 text-violet-600 border border-violet-100 uppercase tracking-widest">Logic Core</span>
                    </h2>
                    <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mt-1">Operational constraints & temporal rulesets</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2 p-1 bg-white/40 backdrop-blur-xl rounded-2xl border border-white shadow-sm w-full lg:w-auto">
                <button v-for="tab in ['global', 'shifts']" :key="tab"
                    @click="activeSection = tab"
                    class="flex-1 lg:flex-none px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all"
                    :class="activeSection === tab ? 'bg-slate-900 text-white shadow-xl' : 'text-slate-400 hover:text-slate-600'"
                >
                    {{ tab === 'global' ? 'Core Logic' : 'Temporal Nodes' }}
                </button>
            </div>
        </div>

        <!-- Global Rules Tab (Single Column Accordion) -->
        <div v-if="activeSection === 'global'" class="animate-in fade-in slide-in-from-bottom-5 duration-700 max-w-4xl mx-auto space-y-4">
            <form @submit.prevent="submitPolicy" class="space-y-6">
                <!-- Punctuality Accordion -->
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden group transition-all" :class="{'ring-4 ring-violet-500/5 border-violet-200': openAccordion === 'punctuality'}">
                    <button type="button" @click="openAccordion = openAccordion === 'punctuality' ? null : 'punctuality'" class="w-full text-left p-6 md:p-8 flex items-center justify-between group-hover:bg-slate-50/50 transition-all">
                        <div class="flex items-center gap-5">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all bg-violet-50 text-violet-600 border border-violet-100 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight leading-none mb-2">Punctuality Nodes</h4>
                                <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Late marks & threshold logic</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-slate-300 transition-transform duration-500" :class="{'rotate-180 text-violet-500': openAccordion === 'punctuality'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    
                    <div v-show="openAccordion === 'punctuality'" class="px-8 pb-8 space-y-8 animate-in slide-in-from-top-4 duration-500">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1">Penalty Trigger Threshold</label>
                                <div class="relative flex items-center gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100 group/input">
                                    <input type="number" v-model="policyForm.late_mark_threshold" class="w-24 bg-white border-2 border-gray-100 rounded-xl py-3 text-center text-lg font-black text-slate-700 focus:ring-4 focus:ring-violet-500/10 focus:border-violet-500 transition-all shadow-inner">
                                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest leading-relaxed">INCIDENTS PER MONTH BEFORE AUTO-DEDUCTION ACTIVATES</p>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1">Sandwich Protocol</label>
                                <div @click="policyForm.sandwich_rule_enabled = !policyForm.sandwich_rule_enabled" 
                                     class="flex items-center justify-between p-4 bg-white border-2 rounded-2xl cursor-pointer transition-all h-[5.5rem]"
                                     :class="policyForm.sandwich_rule_enabled ? 'border-emerald-500 bg-emerald-50/20' : 'border-gray-100 hover:border-violet-200'">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all bg-emerald-50 text-emerald-600 border border-emerald-100" v-if="policyForm.sandwich_rule_enabled">
                                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all bg-slate-50 text-slate-300 border border-slate-100" v-else>
                                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        </div>
                                        <div>
                                            <span class="text-base font-black text-slate-800 uppercase tracking-tight block">Sandwich Logic</span>
                                            <span class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1 block">Weekend Overlap Deductions</span>
                                        </div>
                                    </div>
                                    <div class="relative w-10 h-6">
                                        <input type="checkbox" v-model="policyForm.sandwich_rule_enabled" class="sr-only">
                                        <div class="w-full h-full bg-slate-200 rounded-full transition-all peer-checked:bg-emerald-500" :class="{'bg-emerald-500': policyForm.sandwich_rule_enabled}"></div>
                                        <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-all" :class="{'translate-x-4': policyForm.sandwich_rule_enabled}"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deduction Accordion -->
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden group transition-all" :class="{'ring-4 ring-rose-500/5 border-rose-200': openAccordion === 'deduction'}">
                    <button type="button" @click="openAccordion = openAccordion === 'deduction' ? null : 'deduction'" class="w-full text-left p-6 md:p-8 flex items-center justify-between group-hover:bg-slate-50/50 transition-all">
                        <div class="flex items-center gap-5">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all bg-rose-50 text-rose-600 border border-rose-100 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight leading-none mb-2">Deduction Architect</h4>
                                <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Leave balance erosion logic</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-slate-300 transition-transform duration-500" :class="{'rotate-180 text-rose-500': openAccordion === 'deduction'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    
                    <div v-show="openAccordion === 'deduction'" class="px-8 pb-8 space-y-8 animate-in slide-in-from-top-4 duration-500">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1">Deduction Weight</label>
                                <div class="relative flex items-center gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                    <input type="number" step="0.5" v-model="policyForm.deduction_rule.deduct_leave" class="w-24 bg-white border-2 border-gray-100 rounded-xl py-3 text-center text-lg font-black text-slate-700 focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 transition-all shadow-inner">
                                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest leading-relaxed">LEAVE DAYS TO STRIP PER POLICY VIOLATION TRIGGER</p>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1">Target Repository</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <button v-for="type in [{v:'CL', l:'CASUAL'}, {v:'SL', l:'SICK'}, {v:'LWP', l:'UNPAID'}]" 
                                        :key="type.v" type="button" @click="policyForm.deduction_rule.type = type.v"
                                        :class="policyForm.deduction_rule.type === type.v ? 'bg-slate-900 text-white shadow-xl shadow-slate-200 border-slate-900' : 'bg-white text-slate-400 border-gray-100 hover:border-rose-200 hover:text-rose-600'"
                                        class="py-4 rounded-2xl text-sm font-black uppercase tracking-widest border-2 transition-all active:scale-95"
                                    >
                                        {{ type.l }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overtime Accordion -->
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden group transition-all" :class="{'ring-4 ring-emerald-500/5 border-emerald-200': openAccordion === 'overtime'}">
                    <button type="button" @click="openAccordion = openAccordion === 'overtime' ? null : 'overtime'" class="w-full text-left p-6 md:p-8 flex items-center justify-between group-hover:bg-slate-50/50 transition-all">
                        <div class="flex items-center gap-5">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all bg-emerald-50 text-emerald-600 border border-emerald-100 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight leading-none mb-2">Overtime Intelligence</h4>
                                <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Extended engagement calibration</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-slate-300 transition-transform duration-500" :class="{'rotate-180 text-emerald-500': openAccordion === 'overtime'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    
                    <div v-show="openAccordion === 'overtime'" class="px-8 pb-8 space-y-8 animate-in slide-in-from-top-4 duration-500">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1">Activation Threshold</label>
                                <div class="relative flex items-center gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                    <input type="number" v-model="policyForm.overtime_policy.min_minutes" class="w-24 bg-white border-2 border-gray-100 rounded-xl py-3 text-center text-lg font-black text-slate-700 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all shadow-inner">
                                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest leading-relaxed">MINIMUM MINUTES BEFORE OT CALCULATION_SEQ INITIATES</p>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1">Temporal Multiplier</label>
                                <div class="relative flex items-center gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                    <input type="number" step="0.1" v-model="policyForm.overtime_policy.rate" class="w-24 bg-white border-2 border-gray-100 rounded-xl py-3 text-center text-lg font-black text-slate-700 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all shadow-inner">
                                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest leading-relaxed">HOURLY COEFFICIENT MULTIPLIER FOR OT PAYLOADS</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-8">
                     <button :disabled="processing" type="submit" class="w-full md:w-auto px-12 h-16 bg-slate-900 text-white rounded-[2rem] font-black text-base uppercase tracking-[0.2em] shadow-2xl shadow-slate-200 hover:bg-emerald-600 active:scale-95 transition-all flex items-center justify-center gap-4 group">
                        <svg v-if="processing" class="w-5 h-5 animate-spin text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <svg v-else class="w-5 h-5 text-emerald-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        <span>Commit Matrix Protocol</span>
                     </button>
                </div>
            </form>
        </div>

        <!-- Temporal Shifts Tab -->
        <div v-else-if="activeSection === 'shifts'" class="animate-in fade-in slide-in-from-bottom-5 duration-700 space-y-8">
            <!-- Command Bar -->
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white/40 backdrop-blur-xl p-2 rounded-3xl border border-white shadow-sm">
                <div class="flex items-center gap-3 w-full md:w-auto px-1">
                    <div class="relative w-full md:w-72 group">
                        <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-violet-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <select v-model="locationFilter" class="w-full bg-white border-none rounded-2xl py-3.5 pl-11 pr-10 text-sm font-black uppercase tracking-widest text-slate-700 focus:ring-4 focus:ring-violet-500/10 transition-all appearance-none cursor-pointer shadow-sm">
                            <option value="">All Deployment Zones</option>
                            <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                        </select>
                        <svg class="w-3 h-3 absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <button @click="openCreateShiftModal" class="flex items-center justify-center gap-3 bg-slate-900 text-white h-12 px-8 rounded-2xl font-black text-sm uppercase tracking-[0.2em] shadow-xl shadow-slate-200/50 hover:bg-violet-600 active:scale-95 transition-all w-full md:w-auto group">
                    <svg class="w-4 h-4 text-violet-400 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Initialize Node</span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div v-for="shift in localShifts.filter(s => !locationFilter || (s.location_ids && s.location_ids.includes(String(locationFilter))))" :key="shift.id" 
                     class="group bg-white rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-2xl hover:shadow-violet-500/5 transition-all relative overflow-hidden flex flex-col hover:border-violet-100">
                    <div class="h-1.5 w-full absolute top-0 left-0" :style="{ backgroundColor: shift.color || '#8b5cf6' }"></div>
                    
                    <div class="p-6 pt-10">
                        <div class="flex justify-between items-start mb-8 gap-4">
                            <div class="space-y-2 min-w-0">
                                <h3 class="font-black text-slate-900 text-sm tracking-tight leading-tight group-hover:text-violet-600 transition-colors uppercase truncate">
                                    {{ shift.name }}
                                </h3>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-2 py-0.5 rounded-lg border border-slate-100 shrink-0">
                                        {{ shift.code || 'SYS_NODE' }}
                                    </span>
                                    <div v-if="shift.is_default" class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-600 text-xs font-black uppercase tracking-widest border border-emerald-100 shrink-0">
                                        Primary
                                    </div>
                                </div>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-violet-50 group-hover:text-violet-500 transition-all shrink-0 shadow-inner">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <!-- Temporal Range -->
                            <div class="bg-slate-900 text-white p-5 rounded-2xl shadow-xl shadow-slate-200 relative overflow-hidden group/range active:scale-95 transition-all">
                                <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent"></div>
                                <div class="relative flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-2 px-1">Timeline Allocation</p>
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-black text-white tracking-widest tabular-nums">{{ shift.start_time.slice(0,5) }}</span>
                                            <svg class="w-3 h-3 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                            <span class="text-sm font-black text-white tracking-widest tabular-nums">{{ shift.end_time.slice(0,5) }}</span>
                                        </div>
                                    </div>
                                    <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-white/40">
                                        <i class="fas fa-moon text-indigo-400 text-xs" v-if="parseInt(shift.start_time) > 18 || parseInt(shift.start_time) < 6"></i>
                                        <i class="fas fa-sun text-amber-400 text-xs" v-else></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Operational Days -->
                            <div class="flex justify-between items-center px-1">
                                <div v-for="day in weekDays" :key="day" 
                                    class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-black transition-all border-2"
                                    :class="(shift.work_days || []).includes(day) 
                                        ? 'bg-slate-900 text-white border-slate-900 shadow-lg scale-110' 
                                        : 'text-slate-200 border-gray-50 bg-white'"
                                >
                                    {{ day.slice(0,1) }}
                                </div>
                            </div>

                            <!-- Deployment Scopes -->
                            <div class="pt-6 border-t border-gray-50 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-violet-50 flex items-center justify-center text-sm font-black text-violet-600 border border-violet-100">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                    </div>
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-widest">
                                        {{ (shift.location_ids && shift.location_ids.length) ? `${shift.location_ids.length} ZONES` : 'GLOBAL SCOPE' }}
                                    </span>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 translate-x-4 group-hover:translate-x-0 transition-all">
                                    <button @click="openEditShiftModal(shift)" class="w-9 h-9 bg-white text-slate-400 border border-gray-100 hover:text-violet-600 hover:border-violet-100 rounded-xl transition-all shadow-sm flex items-center justify-center active:scale-90">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button @click="confirmDeleteShift(shift)" class="w-9 h-9 bg-white text-slate-400 border border-gray-100 hover:text-rose-600 hover:border-rose-100 rounded-xl transition-all shadow-sm flex items-center justify-center active:scale-90">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Node Card -->
                <button @click="openCreateShiftModal" class="min-h-[300px] border-4 border-dashed border-gray-100 rounded-[2rem] flex flex-col items-center justify-center gap-5 text-slate-300 hover:border-violet-200 hover:text-violet-500 hover:bg-violet-50/20 transition-all group relative overflow-hidden">
                    <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-10 transition-opacity"></div>
                    <div class="w-16 h-16 bg-white rounded-2xl shadow-xl flex items-center justify-center text-2xl group-hover:scale-110 group-active:scale-90 transition-all border border-gray-100">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    <span class="font-black uppercase tracking-[0.3em] text-sm">Initialize Node</span>
                </button>
            </div>
        </div>

        <!-- Shift Configuration Modal -->
        <PremiumModal 
            :show="showShiftModal" 
            @close="showShiftModal = false" 
            :title="shiftEditMode ? 'Modify Shift Node' : 'Initialize Shift Node'" 
            subtitle="Configure Strategic Temporal Parameters"
            icon="fa-stopwatch-20"
            maxWidth="3xl"
        >
            <form @submit.prevent="submitShiftForm" class="space-y-8 pt-4 px-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-black text-slate-500 uppercase tracking-[0.2em] mb-3 px-1">Shift Designation</label>
                            <input v-model="shiftForm.name" type="text" placeholder="e.g. ALPHA_CORE_SYNC" class="w-full h-12 bg-slate-50 border-2 border-gray-100 rounded-2xl px-5 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-violet-500/10 focus:border-violet-500 transition-all uppercase tracking-widest shadow-sm" required />
                            <p v-if="shiftForm.errors.name" class="text-sm text-rose-500 mt-2 font-black uppercase px-1 leading-none">{{ shiftForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-black text-slate-500 uppercase tracking-[0.2em] mb-3 px-1">Tactical Code</label>
                            <input v-model="shiftForm.code" type="text" placeholder="SEQ-001" class="w-full h-12 bg-slate-50 border-2 border-gray-100 rounded-2xl px-5 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-violet-500/10 focus:border-violet-500 transition-all uppercase tracking-widest shadow-sm" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 md:col-span-2">
                        <div class="space-y-3 p-5 bg-violet-50/50 rounded-2xl border border-violet-100 shadow-inner">
                            <label class="block text-sm font-black text-violet-600 uppercase tracking-[0.2em] leading-none mb-1 px-1 text-center">Activation Pulse</label>
                            <input type="time" v-model="shiftForm.start_time" class="w-full h-10 bg-white border-none rounded-xl text-center text-lg font-black text-slate-700 focus:ring-4 focus:ring-violet-500/10 transition-all shadow-sm" required />
                        </div>

                        <div class="space-y-3 p-5 bg-indigo-50/50 rounded-2xl border border-indigo-100 shadow-inner">
                            <label class="block text-sm font-black text-indigo-600 uppercase tracking-[0.2em] leading-none mb-1 px-1 text-center">Deactivation Pulse</label>
                            <input type="time" v-model="shiftForm.end_time" class="w-full h-10 bg-white border-none rounded-xl text-center text-lg font-black text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all shadow-sm" required />
                        </div>
                    </div>

                    <div class="space-y-3 p-5 bg-amber-50/50 rounded-2xl border border-amber-100 shadow-inner">
                        <label class="block text-sm font-black text-amber-600 uppercase tracking-[0.2em] mb-4 text-center">Grace Tolerance (Mins)</label>
                        <div class="flex items-center gap-4">
                            <input type="number" v-model="shiftForm.grace_late_entry" class="flex-1 h-10 bg-white border-none rounded-xl text-center font-black text-slate-700 focus:ring-4 focus:ring-amber-500/10 shadow-sm" placeholder="ENTRY" />
                            <span class="text-slate-300 font-black">/</span>
                            <input type="number" v-model="shiftForm.grace_early_exit" class="flex-1 h-10 bg-white border-none rounded-xl text-center font-black text-slate-700 focus:ring-4 focus:ring-amber-500/10 shadow-sm" placeholder="EXIT" />
                        </div>
                    </div>

                    <div class="flex flex-col justify-center space-y-4">
                         <label class="flex items-center gap-4 p-5 bg-emerald-50/50 border-2 border-emerald-100/50 rounded-[2rem] cursor-pointer hover:bg-white hover:border-emerald-500 transition-all group">
                            <div class="relative w-12 h-7 bg-slate-200 rounded-full transition-all group-hover:bg-slate-300" :class="{'!bg-emerald-500 shadow-[0_0_12px_rgba(16,185,129,0.5)]': shiftForm.is_default}">
                                <div class="absolute top-1 left-1 w-5 h-5 bg-white rounded-full transition-all shadow-sm" :class="{'translate-x-5': shiftForm.is_default}"></div>
                            </div>
                            <input type="checkbox" v-model="shiftForm.is_default" class="sr-only" />
                            <div class="min-w-0">
                                <span class="block text-base font-black text-slate-800 uppercase leading-none">Global Default Hub</span>
                                <span class="block text-xs text-emerald-600 font-black uppercase tracking-widest mt-1.5 opacity-60">Auto-deployment primary</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="block text-sm font-black text-slate-400 uppercase tracking-[0.2em] px-1">Activation Schedule</label>
                    <div class="flex flex-wrap gap-3">
                        <button v-for="day in weekDays" :key="day" type="button" @click="shiftForm.work_days.includes(day) ? shiftForm.work_days = shiftForm.work_days.filter(d => d !== day) : shiftForm.work_days.push(day)"
                            :class="shiftForm.work_days.includes(day) ? 'bg-slate-900 text-white border-slate-900 shadow-xl' : 'bg-white text-slate-400 border-gray-100 hover:border-violet-200 hover:text-violet-600'"
                            class="w-12 h-12 rounded-2xl text-base font-black border-2 transition-all active:scale-90"
                        >
                            {{ day.slice(0,1) }}
                        </button>
                    </div>
                </div>

                <div class="p-6 bg-slate-50/50 rounded-[2.5rem] border border-gray-100 space-y-5 shadow-inner">
                    <label class="block text-sm font-black text-slate-400 uppercase tracking-[0.2em] px-2">Zone Deployment Scopes</label>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 max-h-48 overflow-y-auto pr-3 custom-scrollbar">
                        <button v-for="loc in locations" :key="loc.id" type="button" @click="shiftForm.location_ids.includes(String(loc.id)) ? shiftForm.location_ids = shiftForm.location_ids.filter(i => i !== String(loc.id)) : shiftForm.location_ids.push(String(loc.id))"
                            :class="shiftForm.location_ids.includes(String(loc.id)) ? 'bg-violet-600 text-white border-violet-600 shadow-lg shadow-violet-200' : 'bg-white text-slate-500 border-gray-50 hover:border-violet-200'"
                            class="p-4 rounded-2xl text-sm font-black uppercase tracking-widest border transition-all text-left truncate flex items-center gap-3 active:scale-95"
                        >
                            <svg class="w-3 h-3 shrink-0" v-if="shiftForm.location_ids.includes(String(loc.id))" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                            <span class="truncate">{{ loc.name }}</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-8 border-t border-gray-100 mt-8">
                    <button type="button" @click="showShiftModal = false" class="text-sm font-black uppercase tracking-[0.2em] text-slate-400 hover:text-slate-600 transition-colors">Terminate Protocol</button>
                    <button type="submit" :disabled="shiftForm.processing" class="h-14 px-12 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-violet-600 transition-all flex items-center gap-3 shadow-xl shadow-slate-200 active:scale-95 group">
                        <svg v-if="shiftForm.processing" class="w-4 h-4 animate-spin text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <svg v-else class="w-4 h-4 text-violet-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>{{ shiftEditMode ? 'Commit Node Updates' : 'Initialize Command' }}</span>
                    </button>
                </div>
            </form>
        </PremiumModal>

        <!-- Deployment Termination Modal -->
        <PremiumModal 
            :show="showDeleteModal" 
            @close="showDeleteModal = false" 
            title="Purge Command?" 
            subtitle="Permanent Node Erasure Protocol"
            icon="fa-virus-slash"
            maxWidth="md"
        >
            <div class="text-center p-4">
                <div class="w-20 h-20 bg-rose-50 rounded-[2rem] flex items-center justify-center text-rose-500 mx-auto mb-8 animate-pulse border border-rose-100 shadow-inner">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </div>
                <p class="text-base text-slate-500 mb-10 font-black uppercase leading-relaxed max-w-[280px] mx-auto tracking-tight">
                    Confirm erasure of <span class="text-rose-600 underline underline-offset-4 decoration-rose-300">"{{ itemToDelete?.name }}"</span> from the temporal grid. This action is irreversible.
                </p>
                <div class="flex items-center justify-between border-t border-gray-100 pt-8 mt-4">
                    <button @click="showDeleteModal = false" class="text-sm font-black uppercase tracking-[0.2em] text-slate-400 hover:text-slate-600 transition-colors">Abort</button>
                    <button @click="deleteShift" :disabled="shiftForm.processing" class="h-14 px-10 bg-rose-600 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-rose-700 shadow-xl shadow-rose-100 active:scale-95 transition-all flex items-center gap-3">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        <span>Execute Termination</span>
                    </button>
                </div>
            </div>
        </PremiumModal>
    </div>
  </component>
</template>

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

input[type="time"]::-webkit-calendar-picker-indicator {
    filter: invert(0.4) sepia(1) saturate(5) hue-rotate(200deg);
    cursor: pointer;
}
</style>
