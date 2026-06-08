<template>
  <component :is="embedded ? 'div' : AttendanceLayout" title="Holiday Intel" activeTab="approvals-float" v-bind="$props">
    <Head v-if="!embedded" title="Floating Holiday Matrix" />
    
    <div :class="{'max-w-[1600px] mx-auto': !embedded}" class="space-y-6 pb-12">
      <!-- Specialized Event Header -->
      <div class="h-auto md:h-14 bg-white/90 backdrop-blur-md p-2 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-center mb-4 gap-4 relative overflow-hidden group/header">
          <div class="absolute inset-0 bg-gradient-to-r from-amber-50/50 to-transparent opacity-0 group-hover/header:opacity-100 transition-opacity"></div>
          <div class="flex items-center gap-4 px-2 w-full md:w-auto relative z-10">
              <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-amber-200 shrink-0">
                  <i class="fas fa-globe-americas text-base"></i>
              </div>
              <div>
                  <h2 class="text-xs font-black text-slate-800 uppercase tracking-tight leading-none">Holiday Intel</h2>
                  <p class="text-xs font-black text-amber-500 uppercase tracking-[0.2em] mt-1.5 leading-none px-0.5">Global Exceptions Matrix</p>
              </div>
          </div>
          
          <div class="flex items-center gap-2 w-full md:w-auto px-1 relative z-10">
              <button @click="exportData" class="flex-1 md:flex-none h-11 md:h-10 px-4 bg-white text-slate-400 rounded-xl flex items-center justify-center border border-slate-200 shadow-sm hover:text-amber-500 transition-all active:scale-95" title="Export Matrix">
                  <i class="fas fa-file-export text-sm"></i>
              </button>
              <button @click="openCreateModal" class="flex-[3] md:flex-none h-11 md:h-10 px-6 bg-slate-900 text-white rounded-xl text-sm font-black uppercase tracking-[0.2em] hover:bg-amber-500 transition-all flex items-center justify-center gap-2 shadow-xl shadow-slate-200/50 active:scale-95 shrink-0">
                  <i class="fas fa-calendar-plus text-sm text-amber-400"></i>
                  Initialize Assignment
              </button>
          </div>
      </div>

      <!-- Event Pulse Summary -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-6">
          <div v-for="metric in [
              { label: 'Upcoming Primary', count: (requests?.data || []).filter(r => r.status === 'Approved').length, color: 'amber', icon: 'fa-star' },
              { label: 'Pending Verification', count: (requests?.data || []).filter(r => r.status === 'Pending').length, color: 'slate', icon: 'fa-hourglass-start' }
          ]" :key="metric.label" 
          class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3 group hover:border-amber-500/30 transition-all">
              <div :class="`w-8 h-8 rounded-lg bg-${metric.color}-50 text-${metric.color}-600 border border-${metric.color}-100 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform`">
                  <i :class="`fas ${metric.icon} text-sm`"></i>
              </div>
              <div>
                  <p class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none mb-1">{{ metric.label }}</p>
                  <p :class="`text-base font-black text-${metric.color}-600 tracking-tighter leading-none`">{{ metric.count }} EVENTS</p>
              </div>
          </div>
      </div>

      <!-- Intelligence Filters -->
      <div class="p-1 rounded-lg border border-slate-200 bg-white/50">
          <EmployeeFilterBar 
              :departments="departments" 
              :locations="locations || []"
              @update="handleFilterUpdate"
          >
              <template #extra>
                  <div class="flex gap-2 items-center ml-auto">
                      <div class="relative min-w-[120px]">
                          <i class="fas fa-filter absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                          <select v-model="filterForm.status" class="w-full bg-white border border-slate-200 rounded-lg pl-8 pr-6 py-1.5 text-sm font-black uppercase tracking-widest text-slate-500 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 appearance-none cursor-pointer h-9 shadow-sm">
                              <option value="">ALL STATUS</option>
                              <option value="Approved">APPROVED</option>
                              <option value="Pending">PENDING</option>
                              <option value="Rejected">REJECTED</option>
                          </select>
                      </div>
                  </div>
              </template>
          </EmployeeFilterBar>
      </div>

      <!-- Registry Terminal -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden min-h-[400px]">
        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
          <table class="w-full border-collapse">
              <thead>
                  <tr class="bg-slate-900 border-b border-slate-800">
                      <th class="px-5 py-4 text-left text-sm font-black text-slate-400 uppercase tracking-widest">Active Member</th>
                      <th class="px-5 py-4 text-left text-sm font-black text-slate-400 uppercase tracking-widest">Event Designation</th>
                      <th class="px-5 py-4 text-left text-sm font-black text-slate-400 uppercase tracking-widest">Temporal Anchor</th>
                      <th class="px-5 py-4 text-left text-sm font-black text-slate-400 uppercase tracking-widest">Status Protocol</th>
                      <th class="px-5 py-4 text-right text-sm font-black text-slate-400 uppercase tracking-widest">Actions</th>
                  </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                  <tr v-if="(requests?.data || []).length === 0">
                      <td colspan="5" class="p-20 text-center">
                          <div class="flex flex-col items-center gap-4 opacity-30 grayscale items-center">
                              <i class="fas fa-calendar-circle-exclamation text-3xl"></i>
                              <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">No Exception Records Found</span>
                          </div>
                      </td>
                  </tr>
                  
                  <tr v-for="req in (requests?.data || [])" :key="req.id" class="group hover:bg-emerald-50/20 transition-all duration-150 border-b border-slate-50 last:border-0">
                      <td class="px-5 py-4">
                          <div class="flex items-center gap-3.5">
                              <div class="w-9 h-9 rounded-xl bg-slate-900 flex items-center justify-center text-white font-black text-sm border border-white shadow-sm flex-shrink-0 uppercase group-hover:bg-emerald-600 transition-colors">
                                  {{ req.user?.employee?.first_name[0] }}{{ req.user?.employee?.last_name[0] }}
                              </div>
                              <div class="truncate max-w-[180px]">
                                  <div class="text-sm font-black text-slate-800 uppercase tracking-tighter truncate leading-none group-hover:text-emerald-700 transition-colors">{{ req.user?.employee?.first_name }} {{ req.user?.employee?.last_name }}</div>
                                  <div class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] truncate mt-2 leading-none">{{ req.user?.employee?.department?.name ?? 'EXTERNAL' }}</div>
                              </div>
                          </div>
                      </td>
                      <td class="px-5 py-4">
                          <div class="flex items-center gap-2">
                              <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-sm animate-pulse"></div>
                              <span class="text-sm font-black text-slate-700 uppercase tracking-tighter truncate max-w-[150px] leading-none">{{ req.holiday?.name }}</span>
                          </div>
                      </td>
                       <td class="px-5 py-4">
                          <span class="text-sm font-black text-slate-500 uppercase tracking-tighter leading-none whitespace-nowrap">{{ req.holiday?.date }}</span>
                      </td>
                      <td class="px-5 py-4">
                          <span class="px-2.5 py-1 text-xs font-black rounded-lg border uppercase tracking-widest shadow-sm leading-none" 
                              :class="{
                                  'bg-emerald-50 text-emerald-600 border-emerald-100': req.status === 'Approved', 
                                  'bg-amber-50 text-amber-600 border-amber-100': req.status === 'Pending', 
                                  'bg-rose-50 text-rose-600 border-rose-100': req.status === 'Rejected'
                              }">
                              {{ req.status }}
                          </span>
                      </td>
                      <td class="px-5 py-4 text-right">
                          <button @click="openEditModal(req)" class="w-8 h-8 rounded-lg bg-white border border-slate-200 inline-flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-all shadow-sm opacity-0 group-hover:opacity-100 translate-x-1 group-hover:translate-x-0">
                              <i class="fas fa-sliders-h text-sm"></i>
                          </button>
                      </td>
                  </tr>
              </tbody>
          </table>
        </div>

        <!-- Mobile Card View (MRT Pattern) -->
        <div class="lg:hidden divide-y divide-slate-100">
            <div v-if="(requests?.data || []).length === 0" class="p-12 text-center">
                <span class="text-sm font-black text-slate-300 uppercase tracking-widest">No Exception Records Found</span>
            </div>
            <div v-for="req in (requests?.data || [])" :key="'mb-'+req.id" class="p-4 space-y-4 group relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-amber-50/50 rounded-full -mr-12 -mt-12 blur-2xl group-hover:bg-amber-100/50 transition-colors"></div>
                
                <!-- MRT Header: Event Profile -->
                <div class="flex items-center justify-between relative z-10">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white font-black text-base uppercase shadow-lg border border-white shrink-0 group-hover:bg-amber-500 transition-colors">
                            {{ req.user?.employee?.first_name[0] }}{{ req.user?.employee?.last_name[0] }}
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-base font-black text-slate-800 uppercase tracking-tighter leading-none truncate">{{ req.user?.employee?.first_name }} {{ req.user?.employee?.last_name }}</h4>
                            <div class="flex items-center gap-2 mt-1.5">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100 shrink-0">CODE: #EV-{{ req.id }}</span>
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest truncate">{{ req.user?.employee?.department?.name ?? 'RESOURCES' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="px-2 py-1 text-xs font-black rounded-lg border uppercase tracking-widest shadow-xs" 
                            :class="{
                                'bg-amber-50 text-amber-600 border-amber-100': req.status === 'Approved', 
                                'bg-slate-50 text-slate-500 border-slate-100': req.status === 'Pending', 
                                'bg-rose-50 text-rose-600 border-rose-100': req.status === 'Rejected'
                            }">
                            {{ req.status === 'Approved' ? 'VERIFIED' : req.status === 'Pending' ? 'PENDING' : 'REVOKED' }}
                        </span>
                    </div>
                </div>

                <!-- MRT Terminal: Temporal Matrix -->
                <div class="bg-white/80 backdrop-blur-md p-3 rounded-2xl border border-slate-100 grid grid-cols-2 gap-3 relative z-10 hover:shadow-lg hover:shadow-amber-100/50 transition-all">
                    <div class="min-w-0 border-r border-slate-200/50 pr-2">
                         <span class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-1">Event Logic</span>
                         <span class="text-sm font-black text-slate-800 uppercase tracking-tighter truncate block">{{ req.holiday?.name }}</span>
                    </div>
                    <div class="min-w-0 pl-1">
                         <span class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-1">Temporal Key</span>
                         <span class="text-sm font-black text-amber-600 uppercase tracking-tighter truncate block font-mono">{{ req.holiday?.date }}</span>
                    </div>
                </div>

                <!-- MRT Actions -->
                <div class="flex justify-end pt-1 relative z-10">
                    <button @click="openEditModal(req)" class="h-10 px-6 rounded-xl bg-white border border-slate-200 text-slate-800 flex items-center justify-center gap-3 text-sm font-black uppercase tracking-[0.2em] shadow-sm active:scale-95 hover:bg-slate-50 transition-all group/btn">
                        <i class="fas fa-sliders-h text-amber-500 group-hover/btn:rotate-90 transition-transform"></i>
                        CONFIGURE_EXCEPTION
                    </button>
                </div>
            </div>
        </div>
      </div>
         <!-- Neural Pagination -->
         <div class="px-5 py-3 bg-slate-50/50 flex flex-col md:flex-row justify-between items-center gap-4 border-t border-slate-100" v-if="(requests?.data || []).length > 0">
             <div class="flex items-center gap-2">
                 <button @click="fetchData(requests.current_page - 1)" :disabled="requests.current_page === 1" class="w-7 h-7 bg-white text-slate-400 border border-slate-200 rounded flex items-center justify-center hover:text-emerald-600 hover:bg-slate-50 disabled:opacity-30 disabled:pointer-events-none transition-all shadow-sm">
                     <i class="fas fa-chevron-left text-sm"></i>
                 </button>
                 <div class="bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-inner mt-0.5">
                     <span class="text-sm font-black text-slate-400 uppercase tracking-widest leading-none">SEGMENT {{ requests.current_page }} / {{ requests.last_page }}</span>
                 </div>
                 <button @click="fetchData(requests.current_page + 1)" :disabled="requests.current_page === requests.last_page" class="w-7 h-7 bg-white text-slate-400 border border-slate-200 rounded flex items-center justify-center hover:text-emerald-600 hover:bg-slate-50 disabled:opacity-30 disabled:pointer-events-none transition-all shadow-sm">
                     <i class="fas fa-chevron-right text-sm"></i>
                 </button>
             </div>
             <div class="text-right">
                 <span class="text-xs font-black text-slate-400 uppercase tracking-widest block leading-none mb-1">Matrix Load</span>
                 <span class="text-xs font-black text-slate-800 tracking-tighter leading-none">{{ (requests.total || 0).toLocaleString() }} OBJECTS</span>
             </div>
      </div>
    </div>

    <!-- Initial Assignment Modal -->

    <PremiumModal 
        :show="showCreateModal" 
        @close="showCreateModal = false" 
        title="Temporal Assignment"
        subtitle="Initialize Floating Protocol"
        icon="fa-calendar-plus"
        maxWidth="xl"
    >
            <form @submit.prevent="createRequest" class="space-y-6">
                <div class="space-y-1.5">
                    <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1 leading-none">Target Operative</label>
                    <div class="relative group mt-1">
                        <i class="fas fa-user-tag absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors text-sm"></i>
                        <select v-model="form.employee_id" class="w-full h-10 bg-slate-50 border border-slate-200 rounded-lg pl-10 pr-8 text-sm font-black uppercase text-slate-600 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all appearance-none cursor-pointer tracking-widest" required>
                            <option value="" disabled>IDENTIFY_ENTITY...</option>
                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name.toUpperCase() }}</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-xs"></i>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1 leading-none">Event Selection</label>
                     <div class="relative group mt-1">
                        <i class="fas fa-star absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors text-sm"></i>
                        <select v-model="form.holiday_id" class="w-full h-10 bg-slate-50 border border-slate-200 rounded-lg pl-10 pr-8 text-sm font-black uppercase text-slate-600 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all appearance-none cursor-pointer tracking-widest" required>
                            <option value="" disabled>SELECT_EXCEPTION...</option>
                            <option v-for="h in holidays" :key="h.id" :value="h.id">{{ h.name.toUpperCase() }} ({{ h.date }})</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-xs"></i>
                     </div>
                </div>

                <div class="space-y-1.5">
                     <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1 leading-none">Initial protocol State</label>
                     <div class="relative group mt-1">
                        <i class="fas fa-shield-halved absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors text-sm"></i>
                        <select v-model="form.status" class="w-full h-10 bg-slate-50 border border-slate-200 rounded-lg pl-10 pr-8 text-sm font-black uppercase text-slate-600 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all appearance-none cursor-pointer tracking-widest">
                            <option value="Approved">APPROVED_PROTOCOL</option>
                            <option value="Pending">PENDING_VERIFICATION</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-xs"></i>
                     </div>
                </div>

                <div class="flex justify-end gap-2 pt-6 border-t border-slate-100">
                    <button type="button" @click="showCreateModal = false" class="px-6 py-2.5 rounded-lg text-sm font-black text-slate-400 uppercase tracking-widest hover:bg-slate-50 transition-all active:scale-95">ABORT</button>
                    <button type="submit" :disabled="form.processing" class="h-10 px-8 bg-slate-900 text-white rounded-lg text-sm font-black uppercase tracking-widest shadow-md hover:bg-slate-800 active:scale-95 transition-all">
                         {{ form.processing ? 'SYNCHRONIZING...' : 'EXECUTE_ASSIGNMENT' }}
                    </button>
                </div>
            </form>
    </PremiumModal>

    <!-- Configuration Adjustment Modal -->
    <PremiumModal 
        :show="showEditModal" 
        @close="showEditModal = false" 
        title="Protocol Adjustment"
        subtitle="Refine Assignment State"
        icon="fa-sliders-h"
        maxWidth="xl"
    >
             <form @submit.prevent="updateRequest" class="space-y-6">
                <div class="bg-emerald-900 rounded-xl p-6 text-white shadow-xl shadow-emerald-500/20 relative overflow-hidden">
                    <div class="absolute -top-12 -right-12 w-32 h-32 bg-white/10 blur-[60px] rounded-full"></div>
                    <p class="text-sm font-bold text-emerald-300 uppercase tracking-[0.2em] mb-2">Integrity Warning</p>
                    <p class="text-sm font-medium leading-relaxed opacity-80 uppercase tracking-tight">Primary indices (Entity/Event) are locked for audit integrity. Shift status override is accessible below.</p>
                </div>

                 <div class="space-y-1.5">
                     <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1 leading-none">Target protocol State</label>
                     <div class="relative group mt-1">
                        <i class="fas fa-shield-halved absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors text-sm"></i>
                        <select v-model="form.status" class="w-full h-10 bg-slate-50 border border-slate-200 rounded-lg pl-10 pr-8 text-sm font-black uppercase text-slate-600 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all appearance-none cursor-pointer tracking-widest">
                            <option value="Approved">APPROVED_PROTOCOL</option>
                            <option value="Pending">PENDING_VERIFICATION</option>
                            <option value="Rejected">REJECTED_PROTOCOL</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-xs"></i>
                     </div>
                </div>

                <div class="flex flex-col md:flex-row justify-between gap-6 pt-6 border-t border-slate-100">
                    <button type="button" @click="deleteRequest" class="flex items-center gap-2 text-rose-500 hover:text-rose-700 text-sm font-black uppercase tracking-widest transition-all group active:scale-95">
                        <i class="fas fa-trash-can group-hover:shake"></i>
                        PURGE_ASSIGNMENT
                    </button>
                    <div class="flex gap-2">
                        <button type="button" @click="showEditModal = false" class="px-6 py-2.5 rounded-lg text-sm font-black text-slate-400 uppercase tracking-widest hover:bg-slate-50 transition-all active:scale-95">ABORT</button>
                        <button type="submit" :disabled="form.processing" class="h-10 px-8 bg-emerald-600 text-white rounded-lg text-sm font-black uppercase tracking-widest shadow-md hover:bg-emerald-700 active:scale-95 transition-all">
                            {{ form.processing ? 'SYNCHRONIZING...' : 'APPLY_CHANGES' }}
                        </button>
                    </div>
                </div>
            </form>
    </PremiumModal>
  </component>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useToastStore } from '@/stores/toast';
import EmployeeFilterBar from '@/Components/EmployeeFilterBar.vue';
import PremiumModal from '@/Components/PremiumModal.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    embedded: Boolean,
    requests: Object,
    filters: Object,
    employees: Array,
    holidays: Array,
    departments: Array,
    locations: Array
});

const toast = useToastStore();
const showCreateModal = ref(false);
const showEditModal = ref(false);

const filterForm = ref({
    status: props.filters?.status || '',
    search: props.filters?.search || '',
    department_id: props.filters?.department_id || '',
    location_id: props.filters?.location_id || '',
});

const form = useForm({
    id: null,
    employee_id: '',
    holiday_id: '',
    status: 'Pending'
});

const handleFilterUpdate = (newFilters) => {
    reload({
        ...filterForm.value,
        ...newFilters
    });
};

watch(() => filterForm.value.status, () => {
    reload(filterForm.value);
});

const reload = (query) => {
    router.visit(route('attendance.floating-holidays'), {
        method: 'get',
        data: query,
        preserveState: true,
        preserveScroll: true,
        only: ['requests', 'filters']
    });
};

const fetchData = (page) => {
     router.visit(route('attendance.floating-holidays'), {
         method: 'get',
        data: { 
            page, 
            ...filterForm.value 
        },
        preserveState: true,
        preserveScroll: true,
        only: ['requests']
     });
};

const exportData = () => {
    const params = new URLSearchParams(filterForm.value).toString();
    window.location.href = `/attendance/floating-holidays/export?${params}`;
    toast.success("Temporal Exception Matrix Exported");
};

const openCreateModal = () => {
    form.reset();
    form.status = 'Approved'; 
    showCreateModal.value = true;
};

const createRequest = () => {
    form.post(route('attendance.floating-holidays.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            toast.success("Floating Protocol Initialized");
        },
        onError: () => toast.error("Deployment Failure")
    });
};

const openEditModal = (req) => {
    form.id = req.id;
    form.employee_id = req.employee_id; 
    form.holiday_id = req.holiday_id;
    form.status = req.status;
    showEditModal.value = true;
};

const updateRequest = () => {
    form.put(route('attendance.floating-holidays.update', form.id), {
        onSuccess: () => {
            showEditModal.value = false;
            toast.success("Protocol Synchronization Complete");
        }
    });
};

const deleteRequest = () => {
    if(!confirm("Execute deconstruction of this assignment?")) return;
    router.delete(route('attendance.floating-holidays.destroy', form.id), {
        onSuccess: () => {
            showEditModal.value = false;
            toast.success("Protocol Dismantled");
        }
    });
};

const getStatusStyles = (status) => {
    switch(status) {
        case 'Approved': return 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-emerald-500/5';
        case 'Pending': return 'bg-amber-50 text-amber-600 border-amber-100 shadow-amber-500/5 animate-pulse';
        case 'Rejected': return 'bg-rose-50 text-rose-600 border-rose-100 shadow-rose-500/5';
        default: return 'bg-slate-50 text-slate-400 border-slate-100';
    }
};
</script>
