<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import {
    MagnifyingGlassIcon, ArrowDownTrayIcon, ChevronRightIcon,
    CalendarDaysIcon, UsersIcon, CheckCircleIcon, XCircleIcon,
    ClockIcon, HomeModernIcon, ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    embedded: Boolean,
    matrix_employees: Object,
    matrix_stats: Object,
    departments: Array,
    locations: Array,
    filters: Object,
});

const getLocalDate = () => new Date().toISOString().split('T')[0];

const form = ref({
    date_from: props.filters?.date_from || getLocalDate(),
    date_to:   props.filters?.date_to   || getLocalDate(),
    department_id: props.filters?.department_id || '',
    location_id:   props.filters?.location_id   || '',
    status:   props.filters?.status || '',
    search:   props.filters?.search || '',
});

const loading = ref(false);
const hoveredRow = ref(null);
const tooltipStyle = ref({});

const quickRange = (type) => {
    const today = new Date();
    if (type === 'today') {
        form.value.date_from = form.value.date_to = today.toISOString().split('T')[0];
    } else if (type === 'week') {
        const mon = new Date(today); mon.setDate(today.getDate() - today.getDay() + 1);
        form.value.date_from = mon.toISOString().split('T')[0];
        form.value.date_to   = today.toISOString().split('T')[0];
    } else if (type === 'month') {
        form.value.date_from = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
        form.value.date_to   = today.toISOString().split('T')[0];
    }
    reload();
};

const reload = (extra = {}) => {
    loading.value = true;
    router.visit(route('admin.attendance.monitoring'), {
        method: 'get',
        data: { tab: 'daily_log', ...form.value, ...extra },
        preserveState: true,
        preserveScroll: true,
        only: ['matrix_employees', 'matrix_stats', 'filters'],
        onFinish: () => { loading.value = false; },
    });
};

watch(() => [form.value.department_id, form.value.location_id, form.value.status], () => reload(), { deep: true });

let searchTimer = null;
watch(() => form.value.search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => reload(), 500);
});

const applyDates = () => reload();

const goPage = (p) => reload({ page: p });

const exportMatrix = () => {
    const params = new URLSearchParams({ ...form.value }).toString();
    window.location.href = route('admin.attendance.monitoring.matrix-export') + '?' + params;
};

const showTooltip = (e, row) => {
    hoveredRow.value = row;
    const rect = e.currentTarget.getBoundingClientRect();
    tooltipStyle.value = {
        top: (rect.bottom + window.scrollY + 8) + 'px',
        left: Math.min(rect.left + window.scrollX, window.innerWidth - 360) + 'px',
    };
};
const hideTooltip = () => { hoveredRow.value = null; };

const statusCls = (s) => ({
    'Present':      'bg-emerald-50 text-emerald-700 border-emerald-200',
    'Late':         'bg-amber-50  text-amber-700  border-amber-200',
    'On Leave':     'bg-purple-50 text-purple-700 border-purple-200',
    'Leave Pending':'bg-purple-50 text-purple-500 border-purple-200 border-dashed',
    'WFH':          'bg-orange-50 text-orange-700 border-orange-200',
    'WFH Pending':  'bg-orange-50 text-orange-500 border-orange-200 border-dashed',
    'Absent':       'bg-red-50    text-red-700    border-red-200',
    'Not Marked':   'bg-red-50    text-red-500    border-red-200    border-dashed',
    'Weekend Off':  'bg-slate-50  text-slate-400  border-slate-200',
}[s] || 'bg-slate-50 text-slate-400 border-slate-200');

const stats = computed(() => props.matrix_stats || {});
const employees = computed(() => props.matrix_employees?.data || []);
const pagination = computed(() => props.matrix_employees || {});

const kpis = computed(() => [
    { label: 'Present',   value: stats.value.total_present  ?? 0, cls: 'text-emerald-600', bg: 'bg-emerald-50 border-emerald-100' },
    { label: 'Absent',    value: stats.value.total_absent   ?? 0, cls: 'text-red-600',     bg: 'bg-red-50    border-red-100' },
    { label: 'On Leave',  value: stats.value.total_on_leave ?? 0, cls: 'text-purple-600',  bg: 'bg-purple-50 border-purple-100' },
    { label: 'WFH',       value: stats.value.total_wfh      ?? 0, cls: 'text-orange-600',  bg: 'bg-orange-50 border-orange-100' },
    { label: 'Pending',   value: stats.value.total_pending  ?? 0, cls: 'text-amber-600',   bg: 'bg-amber-50  border-amber-100' },
    { label: 'Total',     value: stats.value.total_employees ?? 0, cls: 'text-slate-900',  bg: 'bg-white border-slate-200' },
]);
</script>

<template>
  <component :is="embedded ? 'div' : AttendanceLayout" title="Attendance Matrix" activeTab="daily_log" v-bind="$props">
    <Head v-if="!embedded" title="Attendance Matrix" />

    <div class="space-y-6 pb-16 font-outfit">

      <!-- Header -->
      <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-slate-900 text-white rounded-[2rem] p-6 shadow-xl">
        <div>
          <h2 class="text-lg font-black uppercase tracking-wider text-white flex items-center gap-3">
            Attendance Matrix
            <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 uppercase tracking-widest">Live</span>
          </h2>
          <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">
            {{ stats.date_from }} → {{ stats.date_to }} &bull; {{ stats.total_employees ?? 0 }} Employees
          </p>
        </div>
        <button @click="exportMatrix"
          class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all active:scale-95 shadow-lg">
          <ArrowDownTrayIcon class="w-4 h-4" />
          Export Excel
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white border border-slate-100 rounded-[1.5rem] p-5 shadow-sm space-y-4">
        <!-- Quick Range -->
        <div class="flex flex-wrap gap-2">
          <button v-for="q in [{k:'today',l:'Today'},{k:'week',l:'This Week'},{k:'month',l:'This Month'}]"
            :key="q.k" @click="quickRange(q.k)"
            class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider border transition-all"
            :class="(form.date_from === form.date_to && q.k==='today' && form.date_from===getLocalDate())
              ? 'bg-slate-900 text-white border-slate-900'
              : 'bg-slate-50 hover:bg-slate-100 text-slate-600 border-slate-200'">
            {{ q.l }}
          </button>
        </div>

        <div class="flex flex-wrap gap-3">
          <!-- Date range -->
          <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
            <CalendarDaysIcon class="w-4 h-4 text-slate-400" />
            <input v-model="form.date_from" type="date" class="bg-transparent text-xs font-black text-slate-700 border-none focus:ring-0 w-28" />
            <span class="text-slate-300 text-xs">–</span>
            <input v-model="form.date_to" type="date" class="bg-transparent text-xs font-black text-slate-700 border-none focus:ring-0 w-28" />
            <button @click="applyDates" class="bg-slate-900 text-white text-[10px] font-black px-3 py-1 rounded-lg uppercase tracking-wider hover:bg-indigo-600 transition-all">Apply</button>
          </div>

          <!-- Search -->
          <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 flex-1 min-w-[180px]">
            <MagnifyingGlassIcon class="w-4 h-4 text-slate-400 shrink-0" />
            <input v-model="form.search" type="text" placeholder="Search employee..." class="bg-transparent text-xs font-black text-slate-700 border-none focus:ring-0 w-full" />
          </div>

          <!-- Department -->
          <select v-model="form.department_id" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-black text-slate-700 focus:ring-0">
            <option value="">All Departments</option>
            <option v-for="d in (departments || [])" :key="d.id" :value="d.id">{{ d.name }}</option>
          </select>

          <!-- Status -->
          <select v-model="form.status" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-black text-slate-700 focus:ring-0">
            <option value="">All Status</option>
            <option v-for="s in ['Present','Late','Absent','On Leave','Leave Pending','WFH','WFH Pending','Not Marked']" :key="s" :value="s">{{ s }}</option>
          </select>
        </div>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
        <div v-for="kpi in kpis" :key="kpi.label"
          class="rounded-2xl border p-4 text-center"
          :class="kpi.bg">
          <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ kpi.label }}</p>
          <p class="text-2xl font-black mt-1" :class="kpi.cls">{{ kpi.value }}</p>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white border border-slate-100 rounded-[2rem] shadow-xl overflow-hidden relative">
        <!-- Loading -->
        <div v-if="loading" class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-50">
          <div class="flex flex-col items-center gap-3">
            <div class="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
            <span class="text-xs font-black uppercase tracking-widest text-slate-500">Syncing Matrix...</span>
          </div>
        </div>

        <!-- Desktop -->
        <div class="hidden lg:block overflow-x-auto">
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-slate-900 text-white">
                <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest">#</th>
                <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest">Employee</th>
                <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest">Dept / Shift</th>
                <th class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest">Today Status</th>
                <th class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest">Check In/Out</th>
                <th class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest">WD</th>
                <th class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest">PRE</th>
                <th class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest">LV</th>
                <th class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest">WFH</th>
                <th class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest">NM</th>
                <th class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-widest">View</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-if="!employees.length">
                <td colspan="11" class="p-20 text-center">
                  <p class="text-sm font-black text-slate-300 uppercase tracking-widest">No employee records found</p>
                </td>
              </tr>
              <tr v-for="(emp, idx) in employees" :key="emp.id"
                class="group hover:bg-slate-50 transition-colors relative cursor-default"
                @mouseenter="showTooltip($event, emp)"
                @mouseleave="hideTooltip">
                <td class="px-6 py-4 text-xs font-black text-slate-400">{{ ((pagination.current_page - 1) * 25) + idx + 1 }}</td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-900 flex items-center justify-center text-white text-xs font-black shrink-0 group-hover:bg-indigo-600 transition-colors">
                      {{ emp.name.split(' ').map(n=>n[0]).slice(0,2).join('') }}
                    </div>
                    <div>
                      <p class="text-sm font-black text-slate-900 uppercase leading-none">{{ emp.name }}</p>
                      <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ emp.code }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <p class="text-xs font-black text-slate-700 uppercase">{{ emp.department }}</p>
                  <p class="text-[10px] font-bold text-slate-400 uppercase mt-1">{{ emp.today_shift }}</p>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-black border uppercase tracking-widest" :class="statusCls(emp.today_status)">
                    {{ emp.today_status }}
                  </span>
                  <span v-if="emp.pending_approvals?.length" class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-amber-400 text-white text-[8px] font-black">
                    {{ emp.pending_approvals.length }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <p v-if="emp.today_check_in" class="text-xs font-black text-slate-800">{{ emp.today_check_in }}</p>
                  <p v-if="emp.today_check_out" class="text-[10px] font-bold text-slate-400">{{ emp.today_check_out }}</p>
                  <p v-if="!emp.today_check_in" class="text-xs font-black text-slate-200">--:--</p>
                </td>
                <td class="px-6 py-4 text-center text-xs font-black text-slate-700">{{ emp.working_days }}</td>
                <td class="px-6 py-4 text-center text-xs font-black text-emerald-700">{{ emp.present_days }}</td>
                <td class="px-6 py-4 text-center text-xs font-black text-purple-700">{{ emp.leave_days }}</td>
                <td class="px-6 py-4 text-center text-xs font-black text-orange-700">{{ emp.wfh_days }}</td>
                <td class="px-6 py-4 text-center text-xs font-black text-red-500">{{ emp.not_marked_days }}</td>
                <td class="px-6 py-4 text-right">
                  <Link :href="route('admin.attendance.monitoring.employee-details-page', { employee: emp.id })"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-500 transition-all">
                    <ChevronRightIcon class="w-4 h-4" />
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile Cards -->
        <div class="lg:hidden divide-y divide-slate-100">
          <div v-if="!employees.length" class="p-12 text-center">
            <p class="text-sm font-black text-slate-300 uppercase tracking-widest">No records</p>
          </div>
          <div v-for="emp in employees" :key="emp.id" class="p-5 space-y-4 bg-white hover:bg-slate-50 transition-colors">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-slate-900 flex items-center justify-center text-white text-xs font-black">
                  {{ emp.name.split(' ').map(n=>n[0]).slice(0,2).join('') }}
                </div>
                <div>
                  <p class="text-sm font-black text-slate-900 uppercase">{{ emp.name }}</p>
                  <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ emp.code }} · {{ emp.department }}</p>
                </div>
              </div>
              <span class="px-2.5 py-1 rounded-full text-[9px] font-black border uppercase tracking-widest" :class="statusCls(emp.today_status)">
                {{ emp.today_status }}
              </span>
            </div>
            <div class="grid grid-cols-5 gap-2 text-center">
              <div v-for="(v,l) in {WD:emp.working_days, PRE:emp.present_days, LV:emp.leave_days, WFH:emp.wfh_days, NM:emp.not_marked_days}" :key="l"
                class="bg-slate-50 rounded-xl p-2 border border-slate-100">
                <p class="text-[8px] font-black text-slate-400 uppercase">{{ l }}</p>
                <p class="text-sm font-black text-slate-800 mt-0.5">{{ v }}</p>
              </div>
            </div>
            <div class="flex items-center justify-between">
              <div v-if="emp.today_check_in" class="text-xs font-black text-slate-600">
                IN: {{ emp.today_check_in }}
                <span v-if="emp.today_check_out" class="text-slate-400"> · OUT: {{ emp.today_check_out }}</span>
              </div>
              <Link :href="route('admin.attendance.monitoring.employee-details-page', { employee: emp.id })"
                class="flex items-center gap-1 text-[10px] font-black uppercase text-indigo-600 hover:text-indigo-800">
                View Detail <ChevronRightIcon class="w-3 h-3" />
              </Link>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
          <p class="text-xs font-black text-slate-400 uppercase tracking-widest">
            Page {{ pagination.current_page }} / {{ pagination.last_page }} · {{ pagination.total }} employees
          </p>
          <div class="flex gap-2">
            <button v-if="pagination.current_page > 1"
              @click="goPage(pagination.current_page - 1)"
              class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-black text-slate-600 hover:bg-slate-900 hover:text-white transition-all">
              ← Prev
            </button>
            <button v-for="link in (pagination.links || [])" :key="link.page"
              @click="goPage(link.page)"
              class="px-3 py-1.5 rounded-lg text-xs font-black transition-all border"
              :class="link.active ? 'bg-slate-900 text-white border-slate-900' : 'bg-white border-slate-200 text-slate-500 hover:bg-slate-100'">
              {{ link.label }}
            </button>
            <button v-if="pagination.current_page < pagination.last_page"
              @click="goPage(pagination.current_page + 1)"
              class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-black text-slate-600 hover:bg-slate-900 hover:text-white transition-all">
              Next →
            </button>
          </div>
        </div>
      </div>

      <!-- Hover Tooltip (Portal style, fixed position) -->
      <Teleport to="body">
        <Transition name="tooltip">
          <div v-if="hoveredRow"
            class="fixed z-[9999] w-80 bg-white border border-slate-200 rounded-2xl shadow-2xl shadow-slate-300/50 p-5 pointer-events-none"
            :style="tooltipStyle">
            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
              <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white text-xs font-black shrink-0">
                {{ hoveredRow.name.split(' ').map(n=>n[0]).slice(0,2).join('') }}
              </div>
              <div>
                <p class="text-sm font-black text-slate-900 uppercase leading-none">{{ hoveredRow.name }}</p>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ hoveredRow.designation }}</p>
              </div>
              <span class="ml-auto px-2 py-0.5 rounded-full text-[9px] font-black border uppercase" :class="statusCls(hoveredRow.today_status)">
                {{ hoveredRow.today_status }}
              </span>
            </div>

            <!-- Punch times -->
            <div v-if="hoveredRow.today_check_in" class="mb-3 grid grid-cols-2 gap-2">
              <div class="bg-emerald-50 rounded-xl p-2.5 text-center border border-emerald-100">
                <p class="text-[8px] font-black text-emerald-500 uppercase tracking-widest">Check In</p>
                <p class="text-sm font-black text-emerald-700 mt-1">{{ hoveredRow.today_check_in }}</p>
              </div>
              <div class="bg-slate-50 rounded-xl p-2.5 text-center border border-slate-100">
                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Check Out</p>
                <p class="text-sm font-black text-slate-700 mt-1">{{ hoveredRow.today_check_out || 'Active' }}</p>
              </div>
            </div>

            <!-- Period Stats -->
            <div class="grid grid-cols-5 gap-1.5 mb-3">
              <div v-for="(v,l) in {WD:hoveredRow.working_days,PRE:hoveredRow.present_days,LV:hoveredRow.leave_days,WFH:hoveredRow.wfh_days,NM:hoveredRow.not_marked_days}" :key="l"
                class="bg-slate-50 rounded-lg p-1.5 text-center border border-slate-100">
                <p class="text-[7px] font-black text-slate-400 uppercase">{{ l }}</p>
                <p class="text-xs font-black text-slate-800 mt-0.5">{{ v }}</p>
              </div>
            </div>

            <!-- Pending Approvals -->
            <div v-if="hoveredRow.pending_approvals?.length" class="border-t border-slate-100 pt-3 space-y-2">
              <p class="text-[9px] font-black text-amber-600 uppercase tracking-widest flex items-center gap-1">
                <ExclamationTriangleIcon class="w-3 h-3" />
                {{ hoveredRow.pending_approvals.length }} Pending Approval(s)
              </p>
              <div v-for="(ap, i) in hoveredRow.pending_approvals" :key="i"
                class="bg-amber-50 border border-amber-100 rounded-xl p-2.5">
                <div class="flex items-center justify-between">
                  <p class="text-[9px] font-black text-amber-700 uppercase">{{ ap.type }}: {{ ap.label }}</p>
                  <span class="text-[8px] font-black text-amber-500 bg-amber-100 px-1.5 py-0.5 rounded">{{ ap.days }} day(s)</span>
                </div>
                <p class="text-[9px] font-bold text-slate-500 mt-1">{{ ap.from }}{{ ap.from !== ap.to ? ' → ' + ap.to : '' }}</p>
              </div>
            </div>

            <p v-else class="text-[9px] font-bold text-slate-300 uppercase tracking-widest text-center border-t border-slate-100 pt-3">
              No pending approvals
            </p>
          </div>
        </Transition>
      </Teleport>

    </div>
  </component>
</template>

<style scoped>
.tooltip-enter-active { transition: all 0.15s ease-out; }
.tooltip-leave-active { transition: all 0.1s ease-in; }
.tooltip-enter-from, .tooltip-leave-to { opacity: 0; transform: translateY(-6px) scale(0.97); }
</style>
