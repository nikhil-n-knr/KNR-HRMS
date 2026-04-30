<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import BankDetailImportModal from '@/Components/Modals/BankDetailImportModal.vue';
import debounce from 'lodash/debounce';
import {
    MagnifyingGlassIcon,
    FunnelIcon,
    UserPlusIcon,
    ArrowUpTrayIcon,
    TableCellsIcon,
    Squares2X2Icon,
    ChevronRightIcon,
    BriefcaseIcon,
    MapPinIcon,
    CalendarDaysIcon,
    ArrowPathRoundedSquareIcon,
    UserGroupIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    employees: Object,
    filters: Object,
    departments: Array
});

const search      = ref(props.filters.search || '');
const departmentId = ref(props.filters.department_id || '');
const status      = ref(props.filters.status || '');
const viewType    = ref('table');
const showImportModal = ref(false);

const reloadData = () => router.reload({ only: ['employees'] });

watch([search, departmentId, status], debounce(() => {
    router.get(route('admin.employees.index'), {
        search: search.value,
        department_id: departmentId.value,
        status: status.value
    }, { preserveState: true, replace: true });
}, 300));

const statusStyles = {
    active:        'bg-emerald-50 text-emerald-700 border-emerald-100',
    probation:     'bg-blue-50 text-blue-700 border-blue-100',
    notice_period: 'bg-amber-50 text-amber-700 border-amber-100',
    terminated:    'bg-rose-50 text-rose-700 border-rose-100',
    resigned:      'bg-rose-50 text-rose-700 border-rose-100',
    on_leave:      'bg-purple-50 text-purple-700 border-purple-100',
};
const statusDots = {
    active: 'bg-emerald-500', probation: 'bg-blue-500', notice_period: 'bg-amber-500',
    terminated: 'bg-rose-500', resigned: 'bg-rose-500', on_leave: 'bg-purple-500',
};
const getStatusStyles = (s) => statusStyles[s] || 'bg-slate-50 text-slate-500 border-slate-100';
const getStatusDot    = (s) => statusDots[s]   || 'bg-slate-400';
const formatStatus    = (s) => (s || '').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());

const avatarColors = [
    'bg-indigo-100 text-indigo-700','bg-emerald-100 text-emerald-700',
    'bg-amber-100 text-amber-700','bg-rose-100 text-rose-700',
    'bg-purple-100 text-purple-700','bg-teal-100 text-teal-700','bg-sky-100 text-sky-700',
];
const getAvatarColor = (id) => avatarColors[id % avatarColors.length];
</script>

<template>
    <Head title="Workforce Management" />
    <MainLayout>

        <!-- ░░ Outer Page Shell ░░ -->
        <div class="min-h-screen bg-[#f4f5fa]">

            <!-- ▓▓ GRADIENT HERO HEADER ▓▓ -->
            <div class="relative overflow-hidden sm:rounded-2xl mx-0 sm:mx-6 mt-0 sm:mt-6
                        bg-gradient-to-br from-[#3d27b4] via-[#6b3fd4] to-[#a855f7]">
                <!-- Blobs -->
                <div class="absolute -top-20 -right-20 w-80 h-80 bg-white/5 rounded-full pointer-events-none"></div>
                <div class="absolute bottom-0 left-1/3 w-56 h-56 bg-white/5 rounded-full pointer-events-none"></div>

                <div class="relative z-10 px-6 sm:px-10 py-8
                            flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <!-- Left -->
                    <div>
                        <p class="text-xs font-bold text-white/50 uppercase tracking-widest mb-2">Employees</p>
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                            Workforce Registry
                        </h1>
                        <p class="mt-2 text-sm text-white/60 max-w-md leading-relaxed">
                            Manage, monitor, and onboard all employees across your organisation.
                        </p>
                    </div>
                    <!-- Stat Pills -->
                    <div class="flex flex-wrap gap-3 shrink-0">
                        <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[130px]">
                            <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Total Employees</p>
                            <p class="text-4xl font-extrabold text-white leading-none">{{ employees.total }}</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[130px]">
                            <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Current Filter</p>
                            <p class="text-3xl font-extrabold text-white leading-none capitalize">{{ status || 'All' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ▓▓ INNER CONTENT BODY ▓▓ -->
            <div class="mx-0 sm:mx-6 mt-5 pb-12 space-y-5">

                <!-- Toolbar -->
                <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3">

                    <!-- Search -->
                    <div class="relative flex-1 min-w-[200px]">
                        <MagnifyingGlassIcon class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search employees, codes, emails…"
                            class="w-full h-11 pl-10 pr-4 rounded-2xl border border-slate-200 bg-white
                                   text-sm font-medium text-slate-800 placeholder-slate-400 shadow-sm
                                   outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/10 transition-all"
                        />
                    </div>

                    <!-- Status Pills -->
                    <div class="flex items-center gap-1 bg-white border border-slate-200 rounded-2xl p-1 shadow-sm">
                        <button
                            v-for="opt in [{ label: 'Active', val: 'active' }, { label: 'Archived', val: 'resigned' }, { label: 'All', val: '' }]"
                            :key="opt.val"
                            @click="status = opt.val"
                            class="px-4 py-1.5 rounded-xl text-sm font-bold transition-all"
                            :class="status === opt.val
                                ? 'bg-indigo-600 text-white shadow-sm'
                                : 'text-slate-500 hover:text-slate-800'"
                        >{{ opt.label }}</button>
                    </div>

                    <!-- Department -->
                    <div class="relative">
                        <BriefcaseIcon class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                        <select
                            v-model="departmentId"
                            class="h-11 pl-9 pr-4 rounded-2xl border border-slate-200 bg-white
                                   text-sm font-medium text-slate-700 shadow-sm outline-none
                                   focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/10 transition-all
                                   appearance-none cursor-pointer"
                        >
                            <option value="">All Departments</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                        </select>
                    </div>

                    <!-- View Toggle -->
                    <div class="flex items-center gap-0.5 bg-white border border-slate-200 rounded-2xl p-1 shadow-sm">
                        <button
                            v-for="v in [{ id:'table', Icon: TableCellsIcon }, { id:'grid', Icon: Squares2X2Icon }]"
                            :key="v.id"
                            @click="viewType = v.id"
                            class="w-9 h-9 rounded-xl flex items-center justify-center transition-all"
                            :class="viewType === v.id ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-slate-600'"
                        >
                            <component :is="v.Icon" class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Bulk Sync -->
                    <button
                        v-if="$page.props.auth.user.role?.name === 'Admin' || $page.props.auth.user.role?.name === 'Super Admin'"
                        @click="showImportModal = true"
                        class="inline-flex items-center gap-2 h-11 px-4 rounded-2xl border border-slate-200
                               bg-white text-sm font-semibold text-slate-600 hover:bg-slate-50 shadow-sm transition-all"
                    >
                        <ArrowUpTrayIcon class="w-4 h-4" />
                        <span class="hidden sm:inline">Bulk Sync</span>
                    </button>

                    <!-- Export -->
                    <a
                        :href="route('admin.employees.export', { search, department_id: departmentId, status })"
                        class="inline-flex items-center gap-2 h-11 px-4 rounded-2xl border border-slate-200
                               bg-white text-sm font-semibold text-slate-600 hover:bg-slate-50 shadow-sm transition-all"
                    >
                        <TableCellsIcon class="w-4 h-4" />
                        <span class="hidden sm:inline">Export</span>
                    </a>

                    <!-- Create -->
                    <Link
                        :href="route('admin.employees.create')"
                        class="inline-flex items-center gap-2 h-11 px-5 rounded-2xl
                               bg-indigo-600 text-white text-sm font-bold shadow-sm shadow-indigo-200
                               hover:bg-indigo-700 active:scale-[0.98] transition-all whitespace-nowrap"
                    >
                        <UserPlusIcon class="w-4 h-4" />
                        Add Employee
                    </Link>
                </div>

                <!-- Stat Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="card in [
                        { label:'Total',        value: employees.total, desc:'All employees visible to your role.',      icon:'M4 6h16M4 10h16M4 14h16M4 18h16', bg:'bg-indigo-50',  ic:'text-indigo-500' },
                        { label:'Active',       value: employees.data?.filter(e=>e.status==='active').length ?? '—',     desc:'Employees currently in service.',         icon:'M5 13l4 4L19 7',  bg:'bg-emerald-50', ic:'text-emerald-500' },
                        { label:'Probation',    value: employees.data?.filter(e=>e.status==='probation').length ?? '—',  desc:'Employees on probation period.',           icon:'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', bg:'bg-amber-50', ic:'text-amber-500' },
                        { label:'Current View', value: status || 'All', desc:'Filter the dashboard to focus on the right employees.', icon:'M4 6h16M4 12h16M4 18h7', bg:'bg-slate-100', ic:'text-slate-500' },
                    ]" :key="card.label"
                        class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ card.label }}</p>
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center" :class="card.bg">
                                <svg class="w-4 h-4" :class="card.ic" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-extrabold text-slate-900 capitalize truncate">{{ card.value }}</p>
                        <p class="text-xs text-slate-400 mt-2 leading-relaxed">{{ card.desc }}</p>
                    </div>
                </div>

                <!-- TABLE VIEW -->
                <div v-if="viewType === 'table'" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <!-- Desktop -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/80">
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider w-10">#</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Employee</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Department</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Joined</th>
                                    <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="(emp, idx) in employees.data" :key="emp.id"
                                    class="group hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-4 text-xs font-semibold text-slate-300 tabular-nums">
                                        {{ (employees.current_page - 1) * employees.per_page + idx + 1 }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold shrink-0 transition-transform group-hover:scale-105"
                                                :class="getAvatarColor(emp.id)">
                                                {{ emp.first_name[0] }}{{ emp.last_name[0] }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-slate-900 group-hover:text-indigo-600 transition-colors">
                                                    {{ emp.first_name }} {{ emp.last_name }}
                                                </div>
                                                <div class="flex items-center gap-1.5 mt-0.5">
                                                    <span class="text-xs text-slate-400 font-mono">{{ emp.employee_code }}</span>
                                                    <span class="text-slate-200">·</span>
                                                    <span class="text-xs text-slate-400">{{ emp.designation }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-slate-700 font-medium">{{ emp.department?.name || '—' }}</div>
                                        <div class="text-xs text-slate-400 mt-0.5">{{ emp.location?.name || '—' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border"
                                            :class="getStatusStyles(emp.status)">
                                            <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDot(emp.status)"></span>
                                            {{ formatStatus(emp.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1.5">
                                            <CalendarDaysIcon class="w-3.5 h-3.5 text-slate-300" />
                                            <span class="text-sm text-slate-500">
                                                {{ new Date(emp.joining_date).toLocaleDateString('en-GB', { day:'numeric', month:'short', year:'numeric' }) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <Link :href="route('admin.employees.show', emp.id)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition-all">
                                                View Profile <ChevronRightIcon class="w-3 h-3" />
                                            </Link>
                                            <Link v-if="!['terminated','resigned'].includes(emp.status)"
                                                :href="route('hr.settlement.create', emp.id)"
                                                class="w-8 h-8 rounded-xl border border-slate-200 bg-white flex items-center justify-center text-slate-400 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition-all">
                                                <ArrowPathRoundedSquareIcon class="w-4 h-4" />
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="employees.data.length === 0">
                                    <td colspan="6" class="py-20 text-center">
                                        <UserGroupIcon class="w-12 h-12 text-slate-200 mx-auto mb-3" />
                                        <p class="text-sm font-semibold text-slate-400">No employees found</p>
                                        <p class="text-xs text-slate-300 mt-1">Try adjusting your search or filters</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="lg:hidden divide-y divide-slate-100">
                        <div v-for="emp in employees.data" :key="'m-'+emp.id" class="p-4 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold shrink-0"
                                        :class="getAvatarColor(emp.id)">
                                        {{ emp.first_name[0] }}{{ emp.last_name[0] }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-slate-900 truncate">{{ emp.first_name }} {{ emp.last_name }}</div>
                                        <div class="text-xs text-slate-400 font-mono mt-0.5">{{ emp.employee_code }}</div>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border shrink-0"
                                    :class="getStatusStyles(emp.status)">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDot(emp.status)"></span>
                                    {{ formatStatus(emp.status) }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-slate-50 rounded-xl p-3">
                                    <p class="text-xs text-slate-400 font-medium mb-0.5">Department</p>
                                    <p class="text-xs font-semibold text-slate-700 truncate">{{ emp.department?.name || '—' }}</p>
                                </div>
                                <div class="bg-slate-50 rounded-xl p-3">
                                    <p class="text-xs text-slate-400 font-medium mb-0.5">Location</p>
                                    <p class="text-xs font-semibold text-slate-700 truncate">{{ emp.location?.name || '—' }}</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <Link :href="route('admin.employees.show', emp.id)"
                                    class="flex-1 h-9 flex items-center justify-center gap-1.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition-all">
                                    View Profile <ChevronRightIcon class="w-3.5 h-3.5" />
                                </Link>
                                <Link v-if="!['terminated','resigned'].includes(emp.status)"
                                    :href="route('hr.settlement.create', emp.id)"
                                    class="w-9 h-9 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all">
                                    <ArrowPathRoundedSquareIcon class="w-4 h-4" />
                                </Link>
                            </div>
                        </div>
                        <div v-if="employees.data.length === 0" class="py-20 text-center">
                            <UserGroupIcon class="w-12 h-12 text-slate-200 mx-auto mb-3" />
                            <p class="text-sm font-semibold text-slate-400">No employees found</p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="employees.data?.length" class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p class="text-xs text-slate-500 font-medium">
                            Showing {{ (employees.current_page-1)*employees.per_page+1 }}–{{ Math.min(employees.current_page*employees.per_page, employees.total) }} of {{ employees.total }} employees
                        </p>
                        <div class="flex items-center gap-1">
                            <template v-for="(link, k) in employees.links" :key="k">
                                <Link v-if="link.url" :href="link.url"
                                    class="h-8 min-w-[32px] px-2 rounded-xl text-xs font-semibold flex items-center justify-center transition-all"
                                    :class="link.active ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-500 hover:text-indigo-600 hover:border-indigo-300'"
                                    v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>

                <!-- GRID VIEW -->
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <Link v-for="emp in employees.data" :key="'g-'+emp.id"
                        :href="route('admin.employees.show', emp.id)"
                        class="group bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md hover:-translate-y-0.5 hover:border-indigo-200 transition-all block">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-sm font-bold group-hover:scale-105 transition-transform"
                                :class="getAvatarColor(emp.id)">
                                {{ emp.first_name[0] }}{{ emp.last_name[0] }}
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border"
                                :class="getStatusStyles(emp.status)">
                                <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDot(emp.status)"></span>
                                {{ formatStatus(emp.status) }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ emp.first_name }} {{ emp.last_name }}</h4>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">{{ emp.designation }}</p>
                        <div class="space-y-1.5 pt-3 mt-3 border-t border-slate-100">
                            <div class="flex items-center gap-1.5">
                                <BriefcaseIcon class="w-3.5 h-3.5 text-slate-300 shrink-0" />
                                <span class="text-xs text-slate-500 font-medium truncate">{{ emp.department?.name || '—' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <MapPinIcon class="w-3.5 h-3.5 text-slate-300 shrink-0" />
                                <span class="text-xs text-slate-500 font-medium">{{ emp.location?.name || '—' }}</span>
                            </div>
                        </div>
                    </Link>
                    <div v-if="employees.data.length === 0" class="col-span-full py-20 text-center">
                        <UserGroupIcon class="w-12 h-12 text-slate-200 mx-auto mb-3" />
                        <p class="text-sm font-semibold text-slate-400">No employees found</p>
                    </div>
                </div>

            </div><!-- /inner body -->
        </div><!-- /outer shell -->

        <BankDetailImportModal :show="showImportModal" @close="showImportModal = false" @success="reloadData" />
    </MainLayout>
</template>