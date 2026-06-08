<template>
  <Head title="Timesheets Management" />

  <div class="p-8 space-y-8 animate-in fade-in duration-700">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
            <ClockIcon class="w-8 h-8 text-indigo-600" />
            Timesheet Command Center
        </h1>
        <p class="text-slate-500 font-medium mt-1">Audit and manage employee work allocations across projects.</p>
      </div>
      
      <div class="flex items-center gap-3">
        <button @click="exportData" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 rounded-2xl font-bold text-xs uppercase tracking-widest text-slate-700 hover:bg-slate-50 transition-all shadow-sm">
          <DownloadIcon class="w-4 h-4" /> Export CSV
        </button>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm flex flex-wrap items-center gap-4">
      <div class="relative flex-1 min-w-[200px]">
        <SearchIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
        <input 
          v-model="filters.search"
          type="text" 
          placeholder="Search by name or code..." 
          class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-2xl text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 transition-all"
        />
      </div>
      
      <select 
        v-model="filters.status"
        class="px-6 py-3 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 transition-all"
      >
        <option value="all">All Statuses</option>
        <option value="Submitted">Submitted</option>
        <option value="Approved">Approved</option>
        <option value="Rejected">Rejected</option>
      </select>

      <button @click="applyFilters" class="px-8 py-3 bg-slate-900 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-slate-800 transition-all">
        Filter
      </button>
    </div>

    <!-- Timesheets Table -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50/50">
            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Employee</th>
            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Project</th>
            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Hours</th>
            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="ts in timesheets.data" :key="ts.id" class="group hover:bg-slate-50/50 transition-colors">
            <td class="px-8 py-5">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-xs">
                  {{ ts.employee.first_name[0] }}{{ ts.employee.last_name[0] }}
                </div>
                <div>
                  <div class="text-sm font-bold text-slate-900">{{ ts.employee.first_name }} {{ ts.employee.last_name }}</div>
                  <div class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">{{ ts.employee.department?.name || 'No Dept' }}</div>
                </div>
              </div>
            </td>
            <td class="px-8 py-5">
              <div class="text-sm font-semibold text-slate-700">{{ formatDate(ts.date) }}</div>
            </td>
            <td class="px-8 py-5">
              <div class="text-sm font-bold text-slate-900">{{ ts.project_name }}</div>
              <div class="text-[10px] font-medium text-slate-400 truncate max-w-[150px]">{{ ts.task_description }}</div>
            </td>
            <td class="px-8 py-5">
              <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 rounded-lg text-xs font-black text-slate-700">
                <ClockIcon class="w-3.5 h-3.5" /> {{ ts.hours_spent }}h
              </div>
            </td>
            <td class="px-8 py-5">
              <span 
                class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border"
                :class="{
                  'bg-amber-50 text-amber-600 border-amber-100': ts.status === 'Submitted',
                  'bg-emerald-50 text-emerald-600 border-emerald-100': ts.status === 'Approved',
                  'bg-rose-50 text-rose-600 border-rose-100': ts.status === 'Rejected'
                }"
              >
                {{ ts.status }}
              </span>
            </td>
            <td class="px-8 py-5 text-right">
              <div class="flex items-center justify-end gap-2" v-if="ts.status === 'Submitted'">
                <button 
                  @click="updateStatus(ts.id, 'Approved')"
                  class="p-2 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-100 transition-colors"
                >
                  <CheckIcon class="w-4 h-4" />
                </button>
                <button 
                  @click="updateStatus(ts.id, 'Rejected')"
                  class="p-2 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition-colors"
                >
                  <XIcon class="w-4 h-4" />
                </button>
              </div>
              <div v-else class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Processed</div>
            </td>
          </tr>
          <tr v-if="timesheets.data.length === 0">
            <td colspan="6" class="px-8 py-20 text-center">
              <div class="w-16 h-16 bg-slate-50 rounded-3xl mx-auto flex items-center justify-center mb-4">
                <InboxIcon class="w-8 h-8 text-slate-300" />
              </div>
              <div class="text-slate-400 font-bold">No timesheets found</div>
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Pagination -->
      <div v-if="timesheets.links.length > 3" class="px-8 py-6 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">
            Showing {{ timesheets.from }} to {{ timesheets.to }} of {{ timesheets.total }} entries
        </div>
        <div class="flex items-center gap-1">
            <Link 
                v-for="(link, i) in timesheets.links" 
                :key="i"
                :href="link.url || '#'"
                v-html="link.label"
                class="px-4 py-2 rounded-xl text-xs font-black transition-all"
                :class="{
                    'bg-indigo-600 text-white shadow-lg shadow-indigo-200': link.active,
                    'text-slate-400 hover:bg-slate-100': !link.active && link.url,
                    'opacity-30 cursor-not-allowed': !link.url
                }"
            />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
  ClockIcon, DownloadIcon, SearchIcon, CheckIcon, 
  XIcon, InboxIcon, ArrowRightIcon 
} from 'lucide-vue-next';
import dayjs from 'dayjs';

defineOptions({ layout: MainLayout });

const props = defineProps({
  timesheets: Object,
  filters: Object
});

const filters = reactive({
  search: props.filters.search || '',
  status: props.filters.status || 'all'
});

const formatDate = (date) => dayjs(date).format('DD MMM, YYYY');

const applyFilters = () => {
  router.get(route('admin.attendance.timesheets.standalone'), filters, {
    preserveState: true,
    replace: true
  });
};

const updateStatus = (id, status) => {
  router.put(route('admin.attendance.timesheets.update', id), { status }, {
    preserveScroll: true
  });
};

const exportData = () => {
  window.location.href = route('admin.attendance.timesheets.export', filters);
};
</script>
