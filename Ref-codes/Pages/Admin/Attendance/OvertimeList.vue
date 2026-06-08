<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import Pagination from '@/Components/Pagination.vue'; 

defineOptions({ layout: MainLayout });

const props = defineProps({
    embedded: Boolean,
    requests: Object,
    filters: Object,
    departments: Array,
    locations: Array
});

// Filters
const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const dateRange = ref({
    start: props.filters?.start_date || '',
    end: props.filters?.end_date || ''
});

// Watch and Debounce Search
let timer = null;
watch([search, statusFilter, dateRange], () => {
    if (timer) clearTimeout(timer);
    timer = setTimeout(() => {
        router.get(window.location.href, {
            search: search.value,
            status: statusFilter.value,
            start_date: dateRange.value.start,
            end_date: dateRange.value.end
        }, { preserveState: true, preserveScroll: true });
    }, 300);
}, { deep: true });

// Form for Create
const showCreateModal = ref(false);
const createForm = useForm({
    employee_id: '',
    date: '',
    hours: 1,
    reason: ''
});

const openCreateModal = () => {
    createForm.reset();
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
};

const submitCreate = () => {
    createForm.post(route('attendance.overtime.store'), {
        onSuccess: () => closeCreateModal()
    });
};

const handleAction = (id, status) => {
    let reason = null;
    if (status === 'Rejected') {
        reason = prompt('Please provide a reason for rejection:');
        if (!reason) return;
    }
    
    if (confirm(`Execute protocol shift to ${status.toUpperCase()}?`)) {
        router.put(`/attendance/overtime/${id}`, {
            status: status,
            rejection_reason: reason
        });
    }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString('en-US', {
    weekday: 'short', 
    month: 'short',   
    day: 'numeric',   
  });
};

const formatMinutes = (mins) => {
    const h = Math.floor(mins / 60);
    const m = mins % 60;
    return `${h}H ${m}M`;
};

const getStatusColor = (status) => {        
  switch (status) {   
    case 'Approved':  
      return 'bg-emerald-50 text-emerald-600 border-emerald-100';      
    case 'Rejected':  
      return 'bg-rose-50 text-rose-600 border-rose-100';
    default:
      return 'bg-amber-50 text-amber-600 border-amber-100';   
  }
};
</script>

<template>
  <component :is="embedded ? 'div' : AttendanceLayout" title="Overtime Intelligence" activeTab="approvals-overtime" v-bind="$props">
    <Head v-if="!embedded" title="Overtime Hub" />
    
    <div :class="{'max-w-[1600px] mx-auto': !embedded}" class="space-y-8 pb-12 px-4 md:px-0 font-outfit">
        <!-- Compact Tactical Command Header -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="flex items-center gap-5">
                <div class="p-4 bg-slate-900 border border-slate-800 rounded-2xl text-amber-400 shadow-xl shadow-slate-200/50 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <svg class="w-7 h-7 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div>
                    <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                        Overtime Hub
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-amber-50 text-amber-600 border border-amber-100 uppercase tracking-widest">Temporal Logic</span>
                    </h2>
                    <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mt-1">Strategic temporal accumulation protocols</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3 w-full lg:w-auto">
                <button 
                    v-if="route().has('admin.attendance.overtime.export')"
                    @click="router.get(route('admin.attendance.overtime.export', filters))"
                    class="h-12 w-12 bg-white text-slate-400 rounded-2xl flex items-center justify-center border border-gray-100 shadow-sm hover:text-amber-600 hover:border-amber-100 transition-all active:scale-90 shrink-0"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                </button>
                <button @click="openCreateModal" class="flex-1 lg:flex-none flex items-center justify-center gap-3 bg-slate-900 text-white h-12 px-8 rounded-2xl hover:bg-emerald-600 transition-all text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 active:scale-95 group">
                    <svg class="w-4 h-4 text-emerald-400 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Initialize Request</span>
                </button>
            </div>
        </div>

            <!-- Intelligence Filters -->
            <div class="bg-white/40 backdrop-blur-xl border border-white p-1.5 rounded-3xl shadow-sm grid grid-cols-1 md:grid-cols-4 gap-2">
                <div class="relative group">
                    <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-amber-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input v-model="search" type="text" placeholder="FILTER MEMBER..." class="w-full bg-white border-none rounded-2xl py-3.5 pl-11 pr-4 text-base font-black text-slate-700 focus:ring-4 focus:ring-amber-500/10 transition-all uppercase tracking-widest placeholder:text-slate-300 shadow-sm">
                </div>
                
                <div class="relative group">
                    <select v-model="statusFilter" class="w-full h-12 bg-white border-none rounded-2xl pl-4 pr-10 text-sm font-black uppercase tracking-widest text-slate-700 focus:ring-4 focus:ring-amber-500/10 appearance-none cursor-pointer shadow-sm">
                        <option value="">ALL STATUS</option>
                        <option value="Pending">PENDING AUDIT</option>
                        <option value="Approved">APPROVED</option>
                        <option value="Rejected">REJECTED</option>
                    </select>
                    <svg class="w-3 h-3 absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
                
                <div class="md:col-span-2 flex items-center gap-3 bg-white rounded-2xl px-4 h-12 shadow-sm border border-transparent focus-within:ring-4 focus-within:ring-amber-500/10 transition-all">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <div class="flex items-center gap-2 flex-1">
                        <input v-model="dateRange.start" type="date" class="bg-transparent border-none p-0 text-sm font-black text-slate-700 focus:ring-0 w-full uppercase h-full cursor-pointer">
                        <span class="text-slate-200 text-sm font-black mx-1 opacity-50">/</span>
                        <input v-model="dateRange.end" type="date" class="bg-transparent border-none p-0 text-sm font-black text-slate-700 focus:ring-0 w-full uppercase h-full cursor-pointer">
                    </div>
                </div>
            </div>

            <!-- Registry Terminal -->
            <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-2xl shadow-slate-200/50 overflow-hidden min-h-[400px]">
                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-slate-900 border-b border-slate-800">
                                <th class="px-6 py-5 text-left">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Operative Member</span>
                                </th>
                                <th class="px-6 py-5 text-left w-32">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Anchor</span>
                                </th>
                                <th class="px-6 py-5 text-left">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Accumulation Load</span>
                                </th>
                                <th class="px-6 py-5 text-left">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Strategic Detail</span>
                                </th>
                                <th class="px-6 py-5 text-left w-32">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Validation</span>
                                </th>
                                <th class="px-6 py-5 text-right w-40">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Command</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-if="(requests?.data || []).length === 0" class="text-center">
                                <td colspan="6" class="p-32">
                                    <div class="flex flex-col items-center gap-4 opacity-20 grayscale">
                                        <svg class="w-16 h-16 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">No Temporal Nodes Found</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="req in (requests?.data || [])" :key="req.id" class="group hover:bg-slate-50 transition-all duration-300">
                                <td class="px-6 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-11 h-11 rounded-2xl bg-slate-900 flex items-center justify-center text-white font-black text-xs border-2 border-white shadow-lg flex-shrink-0 group-hover:bg-amber-600 transition-all overflow-hidden">
                                            {{ req.employee?.first_name ? req.employee.first_name[0] : '' }}{{ req.employee?.last_name ? req.employee.last_name[0] : '' }}
                                        </div>
                                        <div class="truncate max-w-[200px]">
                                            <div class="text-base font-black text-slate-900 uppercase tracking-tight truncate leading-none group-hover:text-amber-700 transition-colors">{{ req.employee?.first_name || '' }} {{ req.employee?.last_name || '' }}</div>
                                            <div class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] truncate mt-2.5 leading-none">{{ req.employee?.department?.name || 'CORE_UNIT' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <span class="text-base font-black text-slate-600 uppercase tracking-tighter tabular-nums">{{ formatDate(req.date) }}</span>
                                </td>
                                <td class="px-6 py-6">
                                    <span class="bg-amber-50 text-amber-700 px-3 py-1.5 rounded-xl border border-amber-200 text-sm font-black uppercase tracking-widest shadow-sm tabular-nums">
                                        {{ formatMinutes(req.minutes) }}
                                    </span>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="max-w-[300px]">
                                        <p class="text-base font-black text-slate-600 uppercase tracking-tight truncate leading-relaxed" :title="req.reason">{{ req.reason || 'NO_CONTEXT_PROVIDED' }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <span class="px-4 py-1.5 text-xs font-black rounded-full border uppercase tracking-widest shadow-sm transition-all" 
                                        :class="getStatusColor(req.status)">
                                        {{ req.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-6 text-right">
                                    <div v-if="req.status === 'Pending'" class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                        <button 
                                            @click="handleAction(req.id, 'Approved')" 
                                            class="h-9 w-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm active:scale-95"
                                            title="Authorize"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </button>
                                        <button 
                                            @click="handleAction(req.id, 'Rejected')" 
                                            class="h-9 w-9 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-sm active:scale-95"
                                            title="Reject"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                    <div v-else class="flex flex-col items-end">
                                        <span class="text-xs font-black text-slate-300 uppercase tracking-[0.2em] mb-1">Authenticated</span>
                                        <svg class="w-4 h-4 text-emerald-500/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden divide-y divide-gray-50 bg-slate-50/50">
                    <div v-if="(requests?.data || []).length === 0" class="p-20 text-center animate-in fade-in zoom-in duration-700">
                        <svg class="w-12 h-12 mx-auto text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">No Temporal Nodes Located</p>
                    </div>
                    <div v-for="req in (requests?.data || [])" :key="'mb-'+req.id" class="p-5 space-y-5 group relative overflow-hidden bg-white hover:bg-slate-50 transition-all active:scale-[0.98]">
                        <div class="flex items-start justify-between relative z-10">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-12 h-12 rounded-2xl bg-slate-900 border-2 border-white flex items-center justify-center text-white font-black text-xs shrink-0 shadow-lg group-hover:bg-amber-600 transition-all overflow-hidden">
                                    {{ req.employee?.first_name ? req.employee.first_name[0] : '' }}{{ req.employee?.last_name ? req.employee.last_name[0] : '' }}
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight leading-none truncate">{{ req.employee?.first_name || '' }} {{ req.employee?.last_name || '' }}</h4>
                                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-2 leading-none">CORE-{{ String(req.id).padStart(3, '0') }} &bull; {{ req.employee?.department?.name || 'CORE_UNIT' }}</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-xs font-black rounded-full border uppercase tracking-widest shadow-sm" 
                                :class="getStatusColor(req.status)">
                                {{ req.status }}
                            </span>
                        </div>

                        <div class="bg-amber-50/40 p-4 rounded-2xl border border-amber-100/50 group-hover:bg-white transition-all relative z-10 shadow-inner">
                            <div class="flex items-center justify-between mb-4">
                                 <span class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Temporal Load</span>
                                 <span class="text-sm font-black text-amber-700 uppercase tracking-tighter tabular-nums bg-white px-3 py-1.5 rounded-xl border border-amber-100 shadow-sm leading-none">{{ formatMinutes(req.minutes) }}</span>
                            </div>
                            <div class="flex items-center justify-between mb-4">
                                 <span class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Deployment Anchor</span>
                                 <span class="text-sm font-black text-slate-600 uppercase tracking-tighter tabular-nums">{{ formatDate(req.date) }}</span>
                            </div>
                            <div v-if="req.reason" class="pt-4 border-t border-amber-100/50 flex gap-3">
                                <svg class="w-4 h-4 text-amber-400/50 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                                <p class="text-base font-black text-slate-600 uppercase tracking-tight leading-normal">" {{ req.reason }} "</p>
                            </div>
                        </div>

                        <div v-if="req.status === 'Pending'" class="flex gap-2 relative z-10 pt-1">
                            <button 
                                @click="handleAction(req.id, 'Rejected')" 
                                class="flex-1 h-12 rounded-2xl bg-white border-2 border-rose-100 text-rose-500 flex items-center justify-center gap-2 text-sm font-black uppercase tracking-[0.2em] shadow-sm active:scale-95 hover:bg-rose-50 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                DENY
                            </button>
                            <button 
                                @click="handleAction(req.id, 'Approved')" 
                                class="flex-[1.5] h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center gap-2 text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 active:scale-95 hover:bg-emerald-600 transition-all"
                            >
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                AUTHORIZE
                            </button>
                        </div>
                        <div v-else class="flex justify-between items-center p-4 bg-slate-50 rounded-2xl border border-dashed border-gray-200 relative z-10">
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest">System Validation</span>
                            <span class="text-sm font-black text-slate-600 uppercase tracking-tighter">{{ req.approver?.name || 'CORE_AUTH' }}</span>
                        </div>
                    </div>
                </div>
            </div>
          <!-- Neural Pagination -->
                      <div class="px-8 py-6 bg-slate-50/80 flex flex-col md:flex-row items-center justify-between border-t border-gray-100 gap-6" v-if="requests?.links?.length > 3">
                          <span class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] leading-none px-1">Global Matrix Data Range</span>
                          <Pagination :links="requests?.links" />
                      </div>

            <!-- Accumulation Initialization Modal -->
            <PremiumModal 
                :show="showCreateModal" 
                @close="closeCreateModal" 
                title="Overtime Injection"
                subtitle="Initialize Strategic Temporal Claim"
                icon="fa-clock-rotate-left"
                maxWidth="xl"
            >
                    <div class="space-y-6 pt-4 px-2">
                        <div v-if="$page.props.auth.user.roles.some(r => r.name === 'Admin')" class="space-y-2.5">
                            <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1 leading-none">Target Operative Index</label>
                            <div class="relative group mt-1">
                                <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-amber-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m10 5l10 10m-5-5l5 5"></path></svg>
                                <input v-model="createForm.employee_id" type="text" placeholder="ID_ALPHA_XXXX" class="w-full h-12 bg-slate-50 border-2 border-gray-100 rounded-2xl pl-11 pr-4 text-base font-black uppercase text-slate-700 focus:bg-white focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all tracking-widest shadow-sm">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2.5">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1 leading-none">Temporal Anchor</label>
                                <div class="relative group mt-1">
                                    <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-amber-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <input v-model="createForm.date" type="date" class="w-full h-12 bg-slate-50 border-2 border-gray-100 rounded-2xl pl-11 pr-4 text-base font-black uppercase text-slate-700 focus:bg-white focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all tracking-widest shadow-sm" required>
                                </div>
                            </div>
                            <div class="space-y-2.5">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1 leading-none">Accumulation Load (HRS)</label>
                                <div class="relative group mt-1">
                                    <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-amber-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <input v-model="createForm.hours" type="number" step="0.5" class="w-full h-12 bg-slate-50 border-2 border-gray-100 rounded-2xl pl-11 pr-4 text-base font-black uppercase text-slate-700 focus:bg-white focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all tracking-widest shadow-sm" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-2.5">
                            <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1 leading-none">Operation Detail Brief</label>
                            <textarea v-model="createForm.reason" rows="4" placeholder="DESCRIBE_DEPLOYMENT_GOALS..." class="w-full bg-slate-50 border-2 border-gray-100 rounded-2xl p-4 text-base font-black uppercase text-slate-700 focus:bg-white focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all leading-relaxed shadow-sm"></textarea>
                        </div>

                        <div class="flex items-center justify-between pt-8 border-t border-gray-100 mt-8">
                            <button @click="closeCreateModal" class="text-sm font-black uppercase tracking-[0.2em] text-slate-400 hover:text-slate-600 transition-colors">Abort</button>
                            <button @click="submitCreate" :disabled="createForm.processing" class="h-14 px-10 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-amber-600 transition-all flex items-center gap-3 shadow-xl shadow-amber-100 active:scale-95 group">
                                <svg class="w-4 h-4 text-emerald-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <span>{{ createForm.processing ? 'Injecting...' : 'Execute Claim' }}</span>
                            </button>
                        </div>
                    </div>
            </PremiumModal>
        </div>
    </component>
</template>

<style scoped>
input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(0.4) sepia(1) saturate(5) hue-rotate(200deg);
    cursor: pointer;
}
</style>
