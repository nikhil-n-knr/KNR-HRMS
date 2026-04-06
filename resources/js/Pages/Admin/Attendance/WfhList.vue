<template>
  <component :is="embedded ? 'div' : AttendanceLayout" title="Remote Intelligence" activeTab="approvals-wfh" v-bind="$props">
    <Head v-if="!embedded" title="WFH Nodes" />
    
    <div :class="{'max-w-[1600px] mx-auto': !embedded}" class="space-y-8 pb-12 px-4 md:px-0 font-outfit">
      <!-- Specialized Command Header -->
      <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
          <div class="flex items-center gap-5">
              <div class="p-4 bg-slate-900 border border-slate-800 rounded-2xl text-emerald-400 shadow-xl shadow-slate-200/50 relative overflow-hidden group">
                  <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                  <svg class="w-7 h-7 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
              </div>
              <div>
                  <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                      Remote Hub
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-widest">Distributed Node</span>
                  </h2>
                  <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mt-1">Management of decentralized operational clusters</p>
              </div>
          </div>
          
          <div class="flex items-center gap-3 w-full lg:w-auto">
              <a :href="route('admin.attendance.wfh.export', filters)" class="h-12 w-12 bg-white text-slate-400 rounded-2xl flex items-center justify-center border border-gray-100 shadow-sm hover:text-emerald-600 hover:border-emerald-100 transition-all active:scale-90 shrink-0">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
              </a>
              <button @click="openCreateModal" class="flex-1 lg:flex-none flex items-center justify-center gap-3 bg-slate-900 text-white h-12 px-8 rounded-2xl hover:bg-emerald-600 transition-all text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200/50 group">
                  <svg class="w-4 h-4 text-emerald-400 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  <span>Initialize Remote</span>
              </button>
          </div>
      </div>

      <!-- Standardized Filter Array -->
      <div class="bg-white/40 backdrop-blur-xl border border-white p-1.5 rounded-3xl shadow-sm">
        <EmployeeFilterBar
          :departments="departments"
          :locations="locations"
          @update="handleFilterUpdate"
        >
          <template #extra>
            <div class="flex flex-col lg:flex-row gap-3 items-stretch lg:items-center flex-1 lg:flex-none">
                <div class="relative group flex-1 lg:w-48">
                    <select v-model="filters.status" @change="handleFilterUpdate" class="w-full h-11 bg-white border-none rounded-xl pl-4 pr-10 text-sm font-black uppercase tracking-widest text-slate-700 focus:ring-4 focus:ring-emerald-500/10 appearance-none cursor-pointer shadow-sm">
                        <option value="">ALL_STATUS</option>
                        <option value="Pending">PENDING_AUDIT</option>
                        <option value="Approved">APPROVED</option>
                        <option value="Rejected">REJECTED</option>
                    </select>
                    <svg class="w-3 h-3 absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
                
                <div class="flex items-center gap-3 bg-white rounded-xl px-4 h-11 shadow-sm border border-transparent focus-within:ring-4 focus-within:ring-emerald-500/10 transition-all">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <div class="flex items-center gap-2">
                        <input type="date" v-model="filters.start_date" @change="handleFilterUpdate" class="bg-transparent border-none p-0 text-sm font-black text-slate-700 focus:ring-0 uppercase h-full w-28">
                        <span class="text-slate-200 text-sm font-black mx-1 opacity-50">/</span>
                        <input type="date" v-model="filters.end_date" @change="handleFilterUpdate" class="bg-transparent border-none p-0 text-sm font-black text-slate-700 focus:ring-0 uppercase h-full w-28">
                    </div>
                </div>
            </div>
          </template>
        </EmployeeFilterBar>
      </div>

      <!-- Registry Terminal -->
      <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-2xl shadow-slate-200/50 overflow-hidden min-h-[400px]">
        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-slate-900 border-b border-slate-800">
                <th class="px-6 py-5 text-left">
                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Operative Registry</span>
                </th>
                <th class="px-6 py-5 text-left w-48">
                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Timeline Anchor</span>
                </th>
                <th class="px-6 py-5 text-left">
                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Strategic Context</span>
                </th>
                <th class="px-6 py-5 text-left w-32">
                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Validation</span>
                </th>
                <th class="px-6 py-5 text-right w-40">
                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Command Array</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="request in (requests?.data || [])" :key="request.id" class="group hover:bg-slate-50 transition-all duration-300">
                <td class="px-6 py-6">
                  <div class="flex items-center gap-4">
                    <div class="w-11 h-11 rounded-2xl bg-slate-900 flex items-center justify-center text-white font-black text-xs border-2 border-white shadow-lg flex-shrink-0 overflow-hidden group-hover:bg-emerald-600 transition-all">
                      {{ request.employee?.first_name ? request.employee.first_name[0] : '' }}{{ request.employee?.last_name ? request.employee.last_name[0] : '' }}
                    </div>
                    <div class="truncate max-w-[200px]">
                      <div class="text-base font-black text-slate-900 uppercase tracking-tight truncate leading-none group-hover:text-emerald-700 transition-colors">
                        {{ request.employee?.first_name }} {{ request.employee?.last_name }}
                      </div>
                      <div class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] truncate mt-2.5 leading-none">
                        {{ request.employee?.department?.name || 'OPERATIONS' }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-6 font-black text-base text-slate-600 uppercase tabular-nums tracking-tighter">
                   {{ formatDate(request.date) }}
                </td>
                <td class="px-6 py-6">
                  <div class="max-w-[300px]">
                      <p class="text-base font-black text-slate-600 uppercase tracking-tight truncate leading-relaxed" :title="request.reason">{{ request.reason || 'NO_CONTEXT_PROVIDED' }}</p>
                  </div>
                </td>
                <td class="px-6 py-6">
                  <span class="px-4 py-1.5 text-xs font-black rounded-full border uppercase tracking-widest shadow-sm transition-all" 
                    :class="getStatusColor(request.status)"
                  >
                    {{ request.status }}
                  </span>
                </td>
                <td class="px-6 py-6 text-right">
                  <div v-if="request.status === 'Pending'" class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all">
                    <button @click="updateStatus(request, 'Approved')" class="h-9 w-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm active:scale-95" title="Authorize Node">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </button>
                    <button @click="updateStatus(request, 'Rejected')" class="h-9 w-9 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-sm active:scale-95" title="Terminate Pulse">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                  </div>
                  <div v-else class="text-right flex flex-col items-end">
                      <p class="text-xs font-black text-slate-300 uppercase tracking-widest leading-none mb-2">Validated By</p>
                      <p class="text-sm font-black text-slate-400 uppercase tracking-tighter truncate max-w-[120px] leading-none">{{ request.approver?.name || 'SYSTEM_CORE' }}</p>
                  </div>
                </td>
              </tr>
              <tr v-if="(requests?.data || []).length === 0">
                 <td colspan="5" class="p-32 text-center">
                    <div class="flex flex-col items-center gap-4 opacity-20 grayscale">
                        <svg class="w-16 h-16 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                      <span class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">No Remote Records Found</span>
                    </div>
                 </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile Card View -->
        <div class="lg:hidden divide-y divide-gray-50 bg-slate-50/50">
            <div v-if="(requests?.data || []).length === 0" class="p-20 text-center animate-in fade-in zoom-in duration-700">
                <svg class="w-12 h-12 mx-auto text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <p class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">No Remote Nodes Located</p>
            </div>
            <div v-for="request in (requests?.data || [])" :key="'mb-'+request.id" class="p-5 space-y-5 group relative overflow-hidden bg-white hover:bg-slate-50 transition-all active:scale-[0.98]">
                <div class="flex items-start justify-between relative z-10">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-12 h-12 rounded-2xl bg-slate-900 border-2 border-white flex items-center justify-center text-white font-black text-xs shrink-0 shadow-lg group-hover:bg-emerald-600 transition-all">
                            {{ request.employee?.first_name ? request.employee.first_name[0] : '' }}{{ request.employee?.last_name ? request.employee.last_name[0] : '' }}
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight leading-none truncate">{{ request.employee?.first_name }} {{ request.employee?.last_name }}</h4>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-2 leading-none">#WFH-{{ String(request.id).padStart(3, '0') }} &bull; {{ request.employee?.department?.name || 'CORE_UNIT' }}</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 text-xs font-black rounded-full border uppercase tracking-widest shadow-sm" 
                        :class="getStatusColor(request.status)"
                    >
                        {{ request.status }}
                    </span>
                </div>

                <div class="bg-emerald-50/40 p-4 rounded-2xl border border-emerald-100/50 group-hover:bg-white transition-all relative z-10 shadow-inner">
                    <div class="flex items-center justify-between mb-4">
                         <span class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Deployment Anchor</span>
                         <span class="text-sm font-black text-emerald-700 uppercase tracking-tighter tabular-nums bg-white px-2 py-1 rounded-lg border border-emerald-100 shadow-sm">{{ formatDate(request.date) }}</span>
                    </div>
                    <div v-if="request.reason" class="pt-4 border-t border-emerald-100/50 flex gap-3">
                        <svg class="w-4 h-4 text-emerald-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                        <p class="text-base font-black text-slate-600 uppercase tracking-tight leading-normal">" {{ request.reason }} "</p>
                    </div>
                </div>

                <div v-if="request.status === 'Pending'" class="flex gap-2 relative z-10 pt-1">
                    <button @click="updateStatus(request, 'Rejected')" class="flex-1 h-12 rounded-2xl bg-white border-2 border-rose-100 text-rose-500 flex items-center justify-center gap-2 text-sm font-black uppercase tracking-[0.2em] shadow-sm active:scale-95 hover:bg-rose-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        DENY
                    </button>
                    <button @click="updateStatus(request, 'Approved')" class="flex-[1.5] h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center gap-2 text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 active:scale-95 hover:bg-emerald-600 transition-all">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        AUTHORIZE
                    </button>
                </div>
                <div v-else class="flex justify-between items-center p-4 bg-slate-50 rounded-2xl border border-dashed border-gray-200 relative z-10">
                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">System Validation</span>
                    <span class="text-sm font-black text-slate-600 uppercase tracking-tighter">{{ request.approver?.name || 'ROOT_PROTOCOL' }}</span>
                </div>
            </div>
        </div>
      </div>

      <!-- Neural Pagination -->
      <div v-if="(requests?.data || []).length > 0" class="px-8 py-6 bg-slate-50/80 flex flex-col md:flex-row items-center justify-between border-t border-gray-100 gap-6">
         <span class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] leading-none">
            NODE {{ requests?.from }}-{{ requests?.to }} OF {{ requests?.total }} DEPLOYED
         </span>
         <div class="flex gap-2">
              <Link
                v-for="(link, k) in requests?.links || []"
                :key="k"
                :href="link.url || '#'"
                class="px-5 py-2.5 text-sm font-black uppercase tracking-widest rounded-xl transition-all leading-none shadow-sm"
                 :class="{'bg-slate-900 text-white shadow-xl': link.active, 'bg-white text-slate-400 border border-gray-200 hover:text-emerald-600 hover:border-emerald-100': !link.active, 'opacity-30 pointer-events-none': !link.url}"
                v-html="link.label"
              />
         </div>
      </div>
    </div>
    
    <PremiumModal :show="showCreateModal" @close="closeCreateModal" title="Remote Protocol" subtitle="Initialize WFH Strategic Deployment" icon="fa-house-signal" maxWidth="xl">
        <form @submit.prevent="submitCreate" class="space-y-6 pt-4 px-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2.5">
                    <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1 leading-none">Engagement Alpha</label>
                    <div class="relative group mt-1">
                        <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <input v-model="createForm.start_date" type="date" required class="w-full h-12 bg-slate-50 border-2 border-gray-100 rounded-2xl pl-11 pr-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest shadow-sm">
                    </div>
                </div>
                <div class="space-y-2.5">
                    <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1 leading-none">Engagement Omega</label>
                    <div class="relative group mt-1">
                        <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <input v-model="createForm.end_date" type="date" required class="w-full h-12 bg-slate-50 border-2 border-gray-100 rounded-2xl pl-11 pr-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest shadow-sm">
                    </div>
                </div>
            </div>
            
            <div class="space-y-2.5">
                <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1 leading-none">Strategic Justification</label>
                <textarea v-model="createForm.reason" required rows="4" placeholder="DESCRIBE_REMOTE_OBJECTIVES..." class="w-full bg-slate-50 border-2 border-gray-100 rounded-2xl p-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all leading-relaxed placeholder:text-slate-300 mt-1 uppercase tracking-widest shadow-sm"></textarea>
            </div>

            <div class="flex items-center justify-between pt-8 border-t border-gray-100 mt-8">
                <button type="button" @click="closeCreateModal" class="text-sm font-black uppercase tracking-[0.2em] text-slate-400 hover:text-slate-600 transition-colors">Abort</button>
                <button type="submit" class="h-14 px-10 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-emerald-600 transition-all flex items-center gap-3 shadow-xl shadow-slate-200 active:scale-95 group">
                    <svg class="w-4 h-4 text-emerald-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <span>Deploy Protocol</span>
                </button>
            </div>
        </form>
    </PremiumModal>

  </component>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import EmployeeFilterBar from '@/Components/EmployeeFilterBar.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import PremiumModal from '@/Components/PremiumModal.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
  embedded: Boolean,
  requests: Object,
  filters: Object,
  departments: Array,
  locations: Array,
});

const filters = ref({
  search: props.filters?.search || '',
  department_id: props.filters?.department_id || '',
  location_id: props.filters?.location_id || '',
  status: props.filters?.status || '',
  start_date: props.filters?.start_date || '',
  end_date: props.filters?.end_date || '',
});

const handleFilterUpdate = (newFilters) => {
    const merged = { ...filters.value, ...newFilters };
    filters.value = merged;

  router.get(route('admin.attendance.wfh.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

const updateStatus = (request, status) => {
  if (confirm(`Execute protocol shift to ${status.toUpperCase()}?`)) {
      router.patch(route('admin.attendance.wfh.update', request.id), {
          status: status
      });
  }
};

const showCreateModal = ref(false);
const createForm = ref({
    employee_id: '',
    start_date: '',
    end_date: '',
    reason: ''
});

const openCreateModal = () => {
    createForm.value = { 
      employee_id: '', 
      start_date: new Date().toISOString().split('T')[0], 
      end_date: new Date().toISOString().split('T')[0], 
      reason: '' 
    };
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
};

const submitCreate = () => {
    router.post(route('admin.attendance.wfh.store'), createForm.value, {
        onSuccess: () => closeCreateModal()
    });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
  });
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

<style scoped>
input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(0.4) sepia(1) saturate(5) hue-rotate(200deg);
    cursor: pointer;
}
</style>
