<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import axios from 'axios';

defineOptions({ layout: MainLayout });

const props = defineProps({
    instances: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
    moduleTypes: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

// ── State ─────────────────────────────────────────────────────────────────────
const loading = ref(false);
const localInstances = ref(props.instances || []);
const drawer = ref({ open: false, item: null });

// Filter State (local-first — no reload for basic status/type filters)
const filterStatus = ref(props.filters?.status || 'all');
const filterModule = ref(props.filters?.entity_type || 'all');
const filterDateFrom = ref(props.filters?.date_from || '');
const filterDateTo = ref(props.filters?.date_to || '');
const search = ref(props.filters?.search || '');

// ── Sync props ────────────────────────────────────────────────────────────────
watch(() => props.instances, v => { localInstances.value = v || []; }, { deep: true });

// ── Local Frontend Filtering (instant, no API call) ──────────────────────────
const filtered = computed(() => {
    let list = localInstances.value;
    if (filterStatus.value !== 'all') {
        list = list.filter(i => i.status === filterStatus.value);
    }
    if (filterModule.value !== 'all') {
        list = list.filter(i => i.entity_type === filterModule.value);
    }
    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        list = list.filter(i =>
            (i.entity_summary ?? '').toLowerCase().includes(q) ||
            (i.entity_type ?? '').toLowerCase().includes(q)
        );
    }
    return list;
});

// ── Server-Side Reload (for date filters which need DB filtering) ─────────────
const applyDateFilter = () => {
    router.visit(route('employee.my-approvals.index'), {
        data: {
            date_from: filterDateFrom.value,
            date_to: filterDateTo.value,
        },
        preserveState: true,
        preserveScroll: true,
        only: ['instances', 'stats'],
    });
};

const clearFilters = () => {
    filterStatus.value = 'all';
    filterModule.value = 'all';
    filterDateFrom.value = '';
    filterDateTo.value = '';
    search.value = '';
    router.visit(route('employee.my-approvals.index'), { preserveScroll: true });
};

// ── Drawer ────────────────────────────────────────────────────────────────────
const openDrawer = (item) => {
    drawer.value = { open: true, item };
};
const closeDrawer = () => {
    drawer.value = { open: false, item: null };
};

// ── Helpers ───────────────────────────────────────────────────────────────────
const statusConfig = {
    pending:  { label: 'Pending',  bg: 'bg-amber-100',   text: 'text-amber-700',  dot: 'bg-amber-400'  },
    approved: { label: 'Approved', bg: 'bg-emerald-100', text: 'text-emerald-700', dot: 'bg-emerald-500' },
    rejected: { label: 'Rejected', bg: 'bg-red-100',     text: 'text-red-700',    dot: 'bg-red-500'    },
    cancelled:{ label: 'Cancelled',bg: 'bg-gray-100',    text: 'text-gray-600',   dot: 'bg-gray-400'   },
};

const moduleConfig = {
    leave_request:               { label: 'Leave',         icon: '📅', color: 'bg-indigo-100 text-indigo-700' },
    attendance_regularization:   { label: 'Regularization',icon: '⏱️', color: 'bg-cyan-100 text-cyan-700'    },
    shift_swap:                  { label: 'Shift Swap',    icon: '🔄', color: 'bg-violet-100 text-violet-700' },
    expense:                     { label: 'Expense',       icon: '💰', color: 'bg-amber-100 text-amber-700'  },
    timesheet:                   { label: 'Timesheet',     icon: '📝', color: 'bg-teal-100 text-teal-700'    },
    overtime_request:            { label: 'Overtime',      icon: '⏰', color: 'bg-orange-100 text-orange-700' },
    wfh_request:                 { label: 'WFH',           icon: '🏠', color: 'bg-blue-100 text-blue-700'   },
    floating_holiday_request:    { label: 'Holiday',       icon: '🎈', color: 'bg-pink-100 text-pink-700'   },
    payroll:                     { label: 'Payroll',       icon: '💸', color: 'bg-emerald-100 text-emerald-700' },
};

const getStatus    = (s) => statusConfig[s]  || { label: s, bg: 'bg-gray-100', text: 'text-gray-700', dot: 'bg-gray-400' };
const getModule    = (t) => moduleConfig[t]  || { label: t, icon: '📌', color: 'bg-gray-100 text-gray-700' };
const relativeTime = (d) => {
    if (!d) return '—';
    const diff = Math.floor((Date.now() - new Date(d)) / 86400000);
    if (diff === 0) return 'Today';
    if (diff === 1) return 'Yesterday';
    return `${diff} days ago`;
};

// Stage progress helpers
const stageProgress = (item) => {
    const approvals = item.approvals || [];
    const done = approvals.filter(a => a.status === 'approved' || a.status === 'skipped').length;
    const total = approvals.length || 1;
    return { done, total, pct: Math.round((done / total) * 100) };
};
</script>

<template>
    <Head title="My Approvals" />

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-emerald-50/30 px-4 py-8">

        <!-- ── Page Header ─────────────────────────────────────────────────── -->
        <div class="mb-8">
            <h1 class="text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-emerald-700 to-teal-600">
                My Approvals
            </h1>
            <p class="text-slate-500 mt-1 text-sm">Track every request you've submitted and its current stage.</p>
        </div>

        <!-- ── Stat Cards ──────────────────────────────────────────────────── -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="col-span-2 md:col-span-1 bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col gap-1">
                <span class="text-base font-bold uppercase tracking-widest text-slate-400">Total</span>
                <span class="text-4xl font-black text-slate-800">{{ stats.total ?? 0 }}</span>
                <span class="text-xs text-slate-400">All requests</span>
            </div>
            <div class="bg-amber-50 rounded-2xl p-5 shadow-sm border border-amber-100 flex flex-col gap-1">
                <span class="text-base font-bold uppercase tracking-widest text-amber-500">Pending</span>
                <span class="text-4xl font-black text-amber-600">{{ stats.total_pending ?? 0 }}</span>
                <span class="text-xs text-amber-400">Awaiting review</span>
            </div>
            <div class="bg-emerald-50 rounded-2xl p-5 shadow-sm border border-emerald-100 flex flex-col gap-1">
                <span class="text-base font-bold uppercase tracking-widest text-emerald-600">Approved</span>
                <span class="text-4xl font-black text-emerald-700">{{ stats.total_approved ?? 0 }}</span>
                <span class="text-xs text-emerald-400">All time</span>
            </div>
            <div class="bg-red-50 rounded-2xl p-5 shadow-sm border border-red-100 flex flex-col gap-1">
                <span class="text-base font-bold uppercase tracking-widest text-red-500">Rejected</span>
                <span class="text-4xl font-black text-red-600">{{ stats.total_rejected ?? 0 }}</span>
                <span class="text-xs text-red-300">Declined</span>
            </div>
            <div class="bg-slate-50 rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col gap-1">
                <span class="text-base font-bold uppercase tracking-widest text-slate-400">Avg. Time</span>
                <span class="text-4xl font-black text-slate-700">{{ stats.avg_turnaround_days ? `${stats.avg_turnaround_days}d` : '—' }}</span>
                <span class="text-xs text-slate-400">Turnaround</span>
            </div>
        </div>

        <!-- ── Filter Bar ──────────────────────────────────────────────────── -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6 flex flex-wrap gap-3 items-center">

            <!-- Status Pills -->
            <div class="flex gap-2 flex-wrap">
                <button
                    v-for="s in ['all','pending','approved','rejected']"
                    :key="s"
                    @click="filterStatus = s"
                    :class="[
                        'px-3 py-1.5 rounded-full text-xs font-bold capitalize transition-all',
                        filterStatus === s
                            ? 'bg-emerald-600 text-white shadow-md'
                            : 'bg-gray-100 text-gray-500 hover:bg-gray-200'
                    ]"
                >{{ s === 'all' ? 'All Status' : s }}</button>
            </div>

            <div class="w-px h-6 bg-gray-200 hidden md:block"></div>

            <!-- Module Dropdown -->
            <select
                v-model="filterModule"
                class="rounded-xl border-gray-200 text-sm py-1.5 px-3 focus:ring-emerald-400 focus:border-emerald-400 bg-gray-50 text-gray-700"
            >
                <option v-for="m in moduleTypes" :key="m.value" :value="m.value">{{ m.label }}</option>
            </select>

            <!-- Date Range -->
            <input type="date" v-model="filterDateFrom" class="rounded-xl border-gray-200 text-sm py-1.5 px-3 bg-gray-50 text-gray-700" />
            <span class="text-gray-400 text-xs">to</span>
            <input type="date" v-model="filterDateTo"   class="rounded-xl border-gray-200 text-sm py-1.5 px-3 bg-gray-50 text-gray-700" />
            <button @click="applyDateFilter" class="px-3 py-1.5 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition-colors">Apply</button>

            <!-- Clear -->
            <button @click="clearFilters" class="text-xs text-slate-400 hover:text-red-500 transition-colors font-medium">Clear</button>
        </div>

        <div class="flex flex-col md:flex-row gap-8">
            <!-- ── Module Sidebar ───────────────────────────────────────────── -->
            <aside class="w-full md:w-64 shrink-0 space-y-1">
                <p class="px-4 text-base font-black uppercase tracking-widest text-slate-400 mb-2">Modules</p>
                <button
                    v-for="m in moduleTypes"
                    :key="m.value"
                    @click="filterModule = m.value"
                    :class="[
                        'w-full flex items-center justify-between px-4 py-3 rounded-2xl transition-all duration-200 group',
                        filterModule === m.value
                            ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20'
                            : 'bg-white text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 border border-transparent hover:border-emerald-100'
                    ]"
                >
                    <div class="flex items-center gap-3">
                        <span class="text-lg group-hover:scale-110 transition-transform">{{ getModule(m.value).icon }}</span>
                        <span class="text-sm font-bold capitalize">{{ m.label }}</span>
                    </div>
                    <span :class="['text-base font-black px-2 py-0.5 rounded-lg ml-2', filterModule === m.value ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-400']">
                        {{ m.count ?? 0 }}
                    </span>
                </button>
            </aside>

            <!-- ── Approval Feed ────────────────────────────────────────────────── -->
            <div class="flex-1 space-y-4">
                
                <!-- Search & Results Count -->
                <div class="flex items-center justify-between gap-4">
                    <div class="relative flex-1 max-w-sm group">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400 group-focus-within:text-emerald-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search these requests…"
                            class="w-full pl-9 pr-3 py-2 rounded-xl border-gray-100 bg-white text-sm focus:ring-emerald-400 focus:border-emerald-400 shadow-sm"
                        />
                    </div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                        Showing {{ filtered.length }} results
                    </span>
                </div>

                <div class="space-y-3">

            <!-- Skeleton -->
            <template v-if="loading">
                <div
                    v-for="n in 5" :key="n"
                    class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm animate-pulse flex gap-4"
                >
                    <div class="w-12 h-12 bg-gray-200 rounded-xl shrink-0"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                        <div class="h-3 bg-gray-100 rounded w-2/3"></div>
                        <div class="h-2 bg-gray-100 rounded w-full mt-2"></div>
                    </div>
                    <div class="w-20 h-6 bg-gray-200 rounded-full self-start shrink-0"></div>
                </div>
            </template>

            <!-- Empty State -->
            <div v-else-if="filtered.length === 0" class="bg-white rounded-2xl border border-dashed border-gray-200 p-16 text-center">
                <div class="text-5xl mb-4">📭</div>
                <h3 class="text-lg font-bold text-gray-600">No requests found</h3>
                <p class="text-sm text-gray-400 mt-1">Try adjusting your filters or submit a new request.</p>
            </div>

            <!-- Approval Cards -->
            <template v-else>
                <div
                    v-for="item in filtered"
                    :key="item.id"
                    @click="openDrawer(item)"
                    class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all cursor-pointer group flex gap-4 items-start"
                >
                    <!-- Module Icon -->
                    <div :class="['w-12 h-12 rounded-xl flex items-center justify-center text-2xl shrink-0 transition-transform group-hover:scale-110', getModule(item.entity_type).color]">
                        {{ getModule(item.entity_type).icon }}
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-base font-black uppercase tracking-widest text-slate-400">
                                {{ getModule(item.entity_type).label }}
                            </span>
                            <span
                                :class="['inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-base font-bold', getStatus(item.status).bg, getStatus(item.status).text]"
                            >
                                <span :class="['w-1.5 h-1.5 rounded-full', getStatus(item.status).dot]"></span>
                                {{ getStatus(item.status).label }}
                            </span>
                        </div>

                        <p class="text-sm font-semibold text-gray-800 mt-1 truncate">{{ item.entity_summary }}</p>

                        <div class="text-xs text-gray-400 mt-0.5 flex items-center gap-3">
                            <span>Submitted {{ relativeTime(item.started_at) }}</span>
                            <span v-if="item.current_stage">• Stage: <span class="font-medium text-gray-600">{{ item.current_stage?.name }}</span></span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mt-3 flex items-center gap-3">
                            <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-gradient-to-r from-emerald-400 to-teal-500 rounded-full transition-all duration-700"
                                    :style="{ width: stageProgress(item).pct + '%' }"
                                ></div>
                            </div>
                            <span class="text-sm text-gray-400 shrink-0">{{ stageProgress(item).done }}/{{ stageProgress(item).total }} stages</span>
                        </div>
                    </div>

                    <!-- Arrow -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-300 group-hover:text-emerald-500 shrink-0 transition-colors mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                </template>
            </div> <!-- End space-y-3 -->
        </div> <!-- End flex-1 space-y-4 -->
    </div> <!-- End flex flex-col md:flex-row gap-8 -->
</div> <!-- End root div -->

    <!-- ── Detail Drawer ───────────────────────────────────────────────────── -->
    <Teleport to="body">
        <!-- Backdrop -->
        <div
            v-if="drawer.open"
            class="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm"
            @click="closeDrawer"
        ></div>

        <!-- Panel -->
        <div
            :class="[
                'fixed inset-y-0 right-0 z-50 w-full max-w-md bg-white shadow-2xl flex flex-col transition-transform duration-300',
                drawer.open ? 'translate-x-0' : 'translate-x-full'
            ]"
        >
            <!-- Drawer Header -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-teal-50">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">{{ getModule(drawer.item?.entity_type).icon }}</span>
                    <div>
                        <h3 class="font-black text-gray-800">{{ getModule(drawer.item?.entity_type).label }} Request</h3>
                        <p class="text-xs text-gray-400">Submitted {{ relativeTime(drawer.item?.started_at) }}</p>
                    </div>
                </div>
                <button @click="closeDrawer" class="p-2 hover:bg-gray-100 rounded-xl text-gray-400 hover:text-gray-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Drawer Body -->
            <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6" v-if="drawer.item">

                <!-- Status Banner -->
                <div :class="['rounded-xl p-4 flex items-center gap-3', getStatus(drawer.item.status).bg]">
                    <span :class="['w-3 h-3 rounded-full', getStatus(drawer.item.status).dot]"></span>
                    <div>
                        <p :class="['font-black', getStatus(drawer.item.status).text]">{{ getStatus(drawer.item.status).label }}</p>
                        <p class="text-xs opacity-70" :class="getStatus(drawer.item.status).text">
                            {{ drawer.item.status === 'pending' ? 'Awaiting action from approver' : 'Final decision reached' }}
                        </p>
                    </div>
                </div>

                <!-- Summary -->
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-base font-black uppercase tracking-widest text-gray-400 mb-1">Request Summary</p>
                    <p class="text-sm font-semibold text-gray-800">{{ drawer.item.entity_summary }}</p>
                </div>

                <!-- Approval Timeline -->
                <div>
                    <p class="text-base font-black uppercase tracking-widest text-gray-400 mb-4">Approval Timeline</p>
                    <div class="relative space-y-4">
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-100"></div>

                        <div v-for="(approval, idx) in (drawer.item.approvals || [])" :key="approval.id" class="relative pl-10 flex items-start gap-3">
                            <!-- Stage Dot -->
                            <div :class="[
                                'absolute left-2.5 top-1 w-3 h-3 rounded-full border-2 border-white z-10 shadow-sm',
                                approval.status === 'approved' ? 'bg-emerald-500' :
                                approval.status === 'rejected' ? 'bg-red-500' :
                                approval.status === 'skipped'  ? 'bg-gray-300' : 'bg-amber-400 animate-pulse'
                            ]"></div>

                            <!-- Stage Info -->
                            <div class="flex-1 bg-white border border-gray-100 rounded-xl p-3 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-bold text-gray-700">{{ approval.stage?.name || `Stage ${idx + 1}` }}</p>
                                    <span :class="[
                                        'text-sm font-bold uppercase px-2 py-0.5 rounded-full',
                                        approval.status === 'approved' ? 'bg-emerald-100 text-emerald-700' :
                                        approval.status === 'rejected' ? 'bg-red-100 text-red-700' :
                                        approval.status === 'skipped'  ? 'bg-gray-100 text-gray-500' :
                                        'bg-amber-100 text-amber-700'
                                    ]">{{ approval.status }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    Approver: <span class="font-semibold text-gray-700">{{ approval.approver?.name || 'Role-based Pool' }}</span>
                                </p>
                                <p v-if="approval.comments" class="text-xs text-gray-400 mt-2 bg-gray-50 px-2 py-1.5 rounded-lg italic">
                                    "{{ approval.comments }}"
                                </p>
                                <p v-if="approval.acted_at" class="text-sm text-gray-300 mt-1">
                                    Actioned: {{ new Date(approval.acted_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                </p>
                            </div>
                        </div>

                        <!-- Pending placeholder if no approvals -->
                        <div v-if="!drawer.item.approvals?.length" class="pl-10 text-sm text-gray-400 italic">
                            No stage data available yet.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Drawer Footer -->
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                <button @click="closeDrawer" class="w-full py-2.5 rounded-xl border border-gray-200 text-sm font-bold text-gray-600 hover:bg-gray-100 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </Teleport>
</template>
