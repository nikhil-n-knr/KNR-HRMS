<template>
  <Head :title="`Employee 360 | ${profile?.name || 'Loading...'}`" />

  <div class="min-h-screen bg-slate-50 p-4 lg:p-8">
    <!-- Selection & Header -->
    <div class="max-w-7xl mx-auto mb-8">
      <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm flex flex-col lg:flex-row items-center gap-6">
        <div class="flex-1 w-full">
          <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-2 block">Select Employee to Analyze</label>
          <select 
            v-model="selectedEmployeeId" 
            @change="fetchData"
            class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 transition-all"
          >
            <option value="" disabled>Choose an employee...</option>
            <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }} ({{ emp.designation }})</option>
          </select>
        </div>
        
        <div class="flex flex-col md:flex-row items-center gap-4 w-full lg:w-auto">
          <div class="flex items-center gap-2 bg-slate-50 p-2 rounded-2xl">
            <input type="date" v-model="filters.start_date" @change="fetchData" class="bg-transparent border-none text-xs font-bold text-slate-600 focus:ring-0" />
            <span class="text-slate-300">→</span>
            <input type="date" v-model="filters.end_date" @change="fetchData" class="bg-transparent border-none text-xs font-bold text-slate-600 focus:ring-0" />
          </div>
          <button @click="exportDossier" class="flex items-center gap-2 px-6 py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">
            <DownloadIcon class="w-4 h-4" /> Export Dossier
          </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="max-w-7xl mx-auto h-[60vh] flex flex-col items-center justify-center">
       <div class="w-16 h-16 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
       <p class="mt-4 font-black text-slate-400 uppercase tracking-widest">Synthesizing Analytical Data...</p>
    </div>

    <div v-else-if="data" class="max-w-7xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
      
      <!-- Profile & Core Metrics -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Profile Card -->
        <div class="lg:col-span-4">
          <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm text-center relative overflow-hidden group">
            <div class="relative z-10">
              <div class="relative inline-block mb-6">
                <img :src="profile.avatar || '/images/default-avatar.png'" class="w-32 h-32 rounded-[2.5rem] object-cover ring-8 ring-slate-50 mx-auto transition-transform group-hover:scale-105" />
                <div class="absolute -bottom-2 -right-2 bg-emerald-500 p-2.5 rounded-2xl border-4 border-white shadow-lg">
                  <CheckIcon class="w-5 h-5 text-white" />
                </div>
              </div>
              <h2 class="text-2xl font-black text-slate-900 leading-tight">{{ profile.name }}</h2>
              <p class="text-xs font-black text-indigo-600 uppercase tracking-widest mt-1">{{ profile.designation }}</p>
              <div class="mt-2 text-sm font-bold text-slate-400">{{ profile.department }}</div>

              <div class="grid grid-cols-2 gap-4 mt-8">
                <div class="p-4 bg-slate-50 rounded-3xl">
                  <div class="text-xl font-black text-slate-900">{{ data.metrics.overview.scrum_velocity }}</div>
                  <div class="text-[10px] font-black text-slate-400 uppercase">Scrum Velocity</div>
                </div>
                <div class="p-4 bg-slate-50 rounded-3xl">
                  <div class="text-xl font-black text-slate-900">{{ data.metrics.overview.hours_burned }}h</div>
                  <div class="text-[10px] font-black text-slate-400 uppercase">Intensity</div>
                </div>
              </div>
            </div>
            <!-- Decorative background -->
            <div class="absolute -top-24 -left-24 w-64 h-64 bg-indigo-50 rounded-full opacity-50 blur-3xl"></div>
          </div>
        </div>

        <!-- Analytical Gauges -->
        <div class="lg:col-span-8">
          <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm h-full">
            <h3 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mb-8">Intelligence Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
               <div class="text-center space-y-4">
                  <div class="relative inline-flex items-center justify-center">
                    <svg class="w-32 h-32 transform -rotate-90">
                      <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="12" fill="transparent" class="text-slate-100" />
                      <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="12" fill="transparent" :stroke-dasharray="364.4" :stroke-dashoffset="364.4 - (364.4 * data.metrics.overview.reliability_score) / 100" class="text-emerald-500 transition-all duration-1000" />
                    </svg>
                    <span class="absolute text-2xl font-black text-slate-900">{{ data.metrics.overview.reliability_score }}%</span>
                  </div>
                  <p class="text-xs font-black text-slate-500 uppercase">Reliability Index</p>
               </div>

               <div class="text-center space-y-4">
                  <div class="relative inline-flex items-center justify-center">
                    <svg class="w-32 h-32 transform -rotate-90">
                      <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="12" fill="transparent" class="text-slate-100" />
                      <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="12" fill="transparent" :stroke-dasharray="364.4" :stroke-dashoffset="364.4 - (364.4 * data.metrics.deviation.efficiency) / 100" class="text-indigo-500 transition-all duration-1000" />
                    </svg>
                    <span class="absolute text-2xl font-black text-slate-900">{{ data.metrics.deviation.efficiency }}%</span>
                  </div>
                  <p class="text-xs font-black text-slate-500 uppercase">Productivity Flow</p>
               </div>

               <div class="text-center space-y-4">
                  <div class="w-32 h-32 mx-auto flex items-center justify-center rounded-full bg-slate-50 border-8 border-white shadow-inner">
                     <span :class="`text-xl font-black ${getBurnoutColor(data.metrics.overview.burnout_risk)}`">{{ data.metrics.overview.burnout_risk }}</span>
                  </div>
                  <p class="text-xs font-black text-slate-500 uppercase">Burnout Risk</p>
               </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Analytical Sections -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left: Workload & Bugs (7 cols) -->
        <div class="lg:col-span-7 space-y-8">
          
          <!-- Projects Table -->
          <div class="bg-white rounded-[3rem] border border-slate-100 shadow-sm overflow-hidden">
             <div class="p-8 border-b border-slate-50 flex items-center justify-between">
                <h3 class="font-black text-slate-900 flex items-center gap-2">
                  <LayoutIcon class="w-5 h-5 text-indigo-500" /> Project Contributions
                </h3>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ data.metrics.project_details.length }} Projects Active</span>
             </div>
             <div class="overflow-x-auto">
                <table class="w-full text-left">
                  <thead>
                    <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                      <th class="px-8 py-4">Project Entity</th>
                      <th class="px-8 py-4 text-center">Plan</th>
                      <th class="px-8 py-4 text-center">Actual</th>
                      <th class="px-8 py-4 text-right">Progress</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-50">
                    <tr v-for="proj in data.metrics.project_details" :key="proj.id" class="group hover:bg-slate-50/50 transition-all">
                      <td class="px-8 py-5">
                        <div class="font-black text-slate-800">{{ proj.name }}</div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Status: {{ proj.status }}</div>
                      </td>
                      <td class="px-8 py-5 text-center font-bold text-slate-600">{{ proj.estimated_hours }}h</td>
                      <td class="px-8 py-5 text-center">
                        <span :class="`font-black ${proj.actual_hours > proj.estimated_hours ? 'text-rose-600' : 'text-emerald-600'}`">
                          {{ proj.actual_hours }}h
                        </span>
                      </td>
                      <td class="px-8 py-5">
                        <div class="flex items-center justify-end gap-3">
                          <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 rounded-full" :style="`width: ${proj.progress}%`"></div>
                          </div>
                          <span class="text-[10px] font-black text-slate-400">{{ proj.progress }}%</span>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
             </div>
          </div>

          <!-- Bug Resolution Pulse -->
          <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm">
             <div class="flex items-center justify-between mb-8">
                <h3 class="font-black text-slate-900 flex items-center gap-2">
                   <BugIcon class="w-5 h-5 text-rose-500" /> Resolution Intelligence
                </h3>
             </div>
             <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="stat in bugSummary" :key="stat.label" class="p-5 bg-slate-50 rounded-[2rem] border border-white shadow-sm">
                   <div :class="`text-2xl font-black ${stat.color}`">{{ stat.value }}</div>
                   <div class="text-[10px] font-black text-slate-400 uppercase mt-1 leading-tight">{{ stat.label }}</div>
                </div>
             </div>
             <!-- Bug Resolution Trends could go here -->
          </div>

        </div>

        <!-- Right: Attendance & Timeline (5 cols) -->
        <div class="lg:col-span-5 space-y-8">
          
          <!-- Attendance Grid -->
          <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm">
             <h3 class="font-black text-slate-900 flex items-center gap-2 mb-6">
                <ClockIcon class="w-5 h-5 text-emerald-500" /> Attendance Precision
             </h3>
             <div class="space-y-4">
                <div v-for="log in data.metrics.attendance_grid.slice(0, 7)" :key="log.date" class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-white group hover:border-indigo-100 transition-all">
                   <div class="flex items-center gap-3">
                      <div :class="`w-2 h-8 rounded-full ${log.status === 'present' ? 'bg-emerald-500' : 'bg-rose-500'}`"></div>
                      <div>
                        <div class="text-xs font-black text-slate-800">{{ log.date }}</div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ log.status }}</div>
                      </div>
                   </div>
                   <div class="text-right">
                      <div class="text-sm font-black text-slate-900">{{ log.duration }}h</div>
                      <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ log.duration > 8.5 ? 'Intensive' : 'Normal' }}</div>
                   </div>
                </div>
             </div>
          </div>

          <!-- Activity Timeline -->
          <div class="bg-slate-900 p-8 rounded-[3rem] shadow-xl text-white overflow-hidden relative">
             <h3 class="font-black text-white/50 text-[10px] uppercase tracking-[0.3em] mb-8 relative z-10">Neural Activity Stream</h3>
             <div class="space-y-8 relative z-10">
                <div v-for="(act, idx) in data.metrics.activity_logs.slice(0, 8)" :key="idx" class="flex gap-4 relative">
                   <!-- Line connector -->
                   <div v-if="idx < 7" class="absolute left-2.5 top-6 bottom-0 w-0.5 bg-white/10"></div>
                   
                   <div :class="`w-5 h-5 rounded-full border-4 border-slate-900 mt-1 relative z-10 ${getActivityColor(act.event_type)}`"></div>
                   <div class="flex-1">
                      <div class="text-xs font-black text-white leading-tight">{{ act.title }}</div>
                      <div class="text-[10px] font-bold text-white/40 mt-1">{{ act.meta }}</div>
                      <div class="text-[9px] font-black text-white/20 uppercase tracking-tighter mt-1">{{ formatTime(act.occurred_at) }}</div>
                   </div>
                </div>
             </div>
             <!-- Decorative background -->
             <div class="absolute -bottom-12 -right-12 w-48 h-48 bg-indigo-500 rounded-full opacity-10 blur-3xl"></div>
          </div>

        </div>

      </div>

    </div>

    <!-- Empty State -->
    <div v-else class="max-w-7xl mx-auto h-[60vh] flex flex-col items-center justify-center text-center">
       <div class="p-8 bg-white rounded-[3rem] border border-slate-100 shadow-sm max-w-md">
          <div class="w-20 h-20 bg-slate-50 rounded-3xl mx-auto flex items-center justify-center mb-6">
             <SearchIcon class="w-10 h-10 text-slate-300" />
          </div>
          <h2 class="text-xl font-black text-slate-900">Awaiting Analysis Parameters</h2>
          <p class="mt-2 text-slate-500 font-medium">Select an employee from the dropdown above to begin the deep-dive intelligence report.</p>
       </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
  DownloadIcon, CheckIcon, LayoutIcon, BugIcon, ClockIcon, 
  SearchIcon, UserIcon, ArrowRightIcon 
} from 'lucide-vue-next';
import dayjs from 'dayjs';

defineOptions({ layout: MainLayout });

const props = defineProps({
    employees: Array,
    default_date_start: String,
    default_date_end: String
});

const selectedEmployeeId = ref('');
const filters = ref({
    start_date: props.default_date_start,
    end_date: props.default_date_end,
});

const data = ref(null);
const loading = ref(false);

const profile = computed(() => data.value?.profile || {});

const bugSummary = computed(() => {
  if (!data.value?.metrics?.quality) return [];
  const q = data.value.metrics.quality;
  return [
    { label: 'Assigned', value: q.assigned, color: 'text-slate-900' },
    { label: 'Resolved', value: q.resolved, color: 'text-emerald-600' },
    { label: 'Reopened', value: q.reopened, color: 'text-amber-600' },
    { label: 'Critical Open', value: q.critical_open, color: 'text-rose-600' }
  ];
});

const fetchData = async () => {
    if (!selectedEmployeeId.value) return;
    loading.value = true;
    try {
        const res = await axios.get(route('hr.employee-360.metrics', selectedEmployeeId.value), { params: filters.value });
        data.value = res.data;
    } catch (e) {
        console.error(e);
        alert("System error during data synthesis.");
    } finally {
        loading.value = false;
    }
};

const exportDossier = () => {
    if (!selectedEmployeeId.value) return;
    const params = new URLSearchParams(filters.value).toString();
    window.location.href = route('hr.employee-360.export', selectedEmployeeId.value) + '?' + params;
};

const getBurnoutColor = (risk) => {
  if (risk === 'High') return 'text-rose-600';
  if (risk === 'Medium') return 'text-amber-600';
  return 'text-emerald-600';
};

const getActivityColor = (type) => {
  switch (type) {
    case 'attendance': return 'bg-emerald-500';
    case 'timesheet':  return 'bg-indigo-500';
    case 'leave':      return 'bg-amber-500';
    case 'bug':        return 'bg-rose-500';
    default:           return 'bg-slate-400';
  }
};

const formatTime = (date) => dayjs(date).format('MMM D, h:mm A');

// Support deep linking via URL params
const urlParams = new URLSearchParams(window.location.search);
const empFromUrl = urlParams.get('employee');
if (empFromUrl) {
  selectedEmployeeId.value = empFromUrl;
  fetchData();
}

</script>

<style scoped>
.animate-in {
  animation: animate-in 0.7s ease-out;
}
@keyframes animate-in {
  from { opacity: 0; transform: translateY(1rem); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
