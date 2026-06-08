<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    CalendarIcon, 
    ClockIcon, 
    ChatBubbleLeftRightIcon, 
    BanknotesIcon, 
    DocumentTextIcon, 
    SparklesIcon, 
    MapIcon, 
    CurrencyDollarIcon,
    BriefcaseIcon,
    ScaleIcon,
    CheckCircleIcon, 
    XCircleIcon,
    InboxIcon,
    UserCircleIcon,
    BoltIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: MainLayout });

const props = defineProps({
    instances: { type: Array, default: () => [] },
    incoming: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
    moduleTypes: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

// ── State ─────────────────────────────────────────────────────────────────────
const loading = ref(false);
const activeTab = ref('incoming'); // 'incoming' or 'requests'
const localInstances = ref(props.instances || []);
const localIncoming = ref(props.incoming || []);
const drawer = ref({ open: false, item: null });
const actionLoading = ref(null);
const bulkLoading = ref(false);
const selectedIncomingIds = ref([]);
const drawerRemarks = ref('');
const copiedField = ref('');

const copyToClipboard = (text, field) => {
    navigator.clipboard.writeText(text);
    copiedField.value = field;
    setTimeout(() => {
        copiedField.value = '';
    }, 2000);
};

// Filter State
const filterStatus = ref(props.filters?.status || 'all');
const filterModule = ref(props.filters?.entity_type || 'all');
const filterDateFrom = ref(props.filters?.date_from || '');
const filterDateTo = ref(props.filters?.date_to || '');
const search = ref(props.filters?.search || '');

// ── Sync props ────────────────────────────────────────────────────────────────
watch(() => props.instances, v => { localInstances.value = v || []; }, { deep: true });
watch(() => props.incoming, v => { localIncoming.value = v || []; }, { deep: true });

onMounted(() => {
    if (localIncoming.value.length === 0 && localInstances.value.length > 0) {
        activeTab.value = 'requests';
    }
});

// ── Local Frontend Filtering ──────────────────────────
const filtered = computed(() => {
    let list = activeTab.value === 'incoming' ? localIncoming.value : localInstances.value;
    
    if (filterStatus.value !== 'all' && activeTab.value === 'requests') {
        list = list.filter(i => i.status === filterStatus.value);
    }
    if (filterModule.value !== 'all') {
        list = list.filter(i => i.entity_type === filterModule.value);
    }
    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        list = list.filter(i =>
            ((i.entity_summary || i.summary) ?? '').toLowerCase().includes(q) ||
            (i.entity_type ?? '').toLowerCase().includes(q)
        );
    }
    return list;
});

const visibleIncomingIds = computed(() => {
    if (activeTab.value !== 'incoming') return [];
    return filtered.value.map((item) => Number(item.id)).filter((id) => Number.isFinite(id));
});

const allIncomingSelected = computed({
    get() {
        const ids = visibleIncomingIds.value;
        if (!ids.length) return false;
        return ids.every((id) => selectedIncomingIds.value.includes(id));
    },
    set(value) {
        if (value) {
            selectedIncomingIds.value = [...visibleIncomingIds.value];
        } else {
            selectedIncomingIds.value = [];
        }
    },
});

// ── Server-Side Reload ─────────────────────────────
const applyDateFilter = () => {
    router.visit(route('employee.my-approvals.index'), {
        data: { date_from: filterDateFrom.value, date_to: filterDateTo.value },
        preserveState: true,
        preserveScroll: true,
        only: ['instances', 'incoming', 'stats'],
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

// ── Actions ───────────────────────────────────────────────────────────────────
const processAction = (id, action, remarks = '') => {
    if (actionLoading.value) return;
    
    if (remarks === undefined || remarks === null) {
        remarks = '';
    }
    
    if (!remarks.trim() && (action === 'reject' || action === 'correction')) {
        remarks = prompt(`Please enter comments/remarks for ${action === 'correction' ? 'requesting correction' : 'rejection'}:`);
        if (remarks === null) return;
    }

    actionLoading.value = id;
    router.post(route('employee.my-approvals.action'), { id, action, remarks }, {
        onSuccess: () => { 
            actionLoading.value = null; 
            closeDrawer();
        },
        onError: () => { 
            actionLoading.value = null; 
        }
    });
};

const toggleIncomingSelection = (id) => {
    const numericId = Number(id);
    if (!Number.isFinite(numericId)) return;

    if (selectedIncomingIds.value.includes(numericId)) {
        selectedIncomingIds.value = selectedIncomingIds.value.filter((itemId) => itemId !== numericId);
        return;
    }

    selectedIncomingIds.value = [...selectedIncomingIds.value, numericId];
};

const bulkApproveSelected = () => {
    if (bulkLoading.value || !selectedIncomingIds.value.length) return;

    bulkLoading.value = true;
    router.post(route('employee.my-approvals.bulk'), {
        ids: selectedIncomingIds.value,
        action: 'approve',
        remarks: null,
    }, {
        preserveScroll: true,
        onFinish: () => {
            bulkLoading.value = false;
            selectedIncomingIds.value = [];
        },
    });
};

// ── Drawer ────────────────────────────────────────────────────────────────────
const openDrawer = (item) => { 
    drawer.value = { open: true, item }; 
    drawerRemarks.value = '';
};
const closeDrawer = () => { 
    drawer.value = { open: false, item: null }; 
    drawerRemarks.value = '';
};

// ── Helpers ───────────────────────────────────────────────────────────────────
const statusConfig = {
    pending:  { label: 'Pending',  bg: 'bg-amber-100',   text: 'text-amber-700',  dot: 'bg-amber-400'  },
    approved: { label: 'Approved', bg: 'bg-emerald-100', text: 'text-emerald-700', dot: 'bg-emerald-500' },
    rejected: { label: 'Rejected', bg: 'bg-red-100',     text: 'text-red-700',    dot: 'bg-red-500'    },
    cancelled:{ label: 'Cancelled',bg: 'bg-gray-100',    text: 'text-gray-600',   dot: 'bg-gray-400'   },
    needs_correction: { label: 'Needs Correction', bg: 'bg-amber-50 border border-amber-200', text: 'text-amber-800', dot: 'bg-amber-500' },
};

const moduleConfig = {
    leave_request:               { label: 'Leave',         icon: CalendarIcon, color: 'bg-indigo-100 text-indigo-700' },
    attendance_regularization:   { label: 'Regularization',icon: ClockIcon,    color: 'bg-cyan-100 text-cyan-700'    },
    shift_swap:                  { label: 'Shift Swap',    icon: ChatBubbleLeftRightIcon, color: 'bg-violet-100 text-violet-700' },
    expense:                     { label: 'Expense',       icon: BanknotesIcon, color: 'bg-amber-100 text-amber-700'  },
    timesheet:                   { label: 'Timesheet',     icon: DocumentTextIcon, color: 'bg-teal-100 text-teal-700'    },
    overtime_request:            { label: 'Overtime',      icon: ClockIcon,    color: 'bg-orange-100 text-orange-700' },
    wfh_request:                 { label: 'WFH',           icon: MapIcon,      color: 'bg-blue-100 text-blue-700'   },
    floating_holiday_request:    { label: 'Holiday',       icon: SparklesIcon, color: 'bg-pink-100 text-pink-700'   },
    payroll:                     { label: 'Payroll',       icon: CurrencyDollarIcon, color: 'bg-emerald-100 text-emerald-700' },
    task_checklist:              { label: 'Sub-task',      icon: CheckCircleIcon, color: 'bg-indigo-100 text-indigo-700' },
};

const getStatus    = (s) => statusConfig[s]  || { label: s, bg: 'bg-gray-100', text: 'text-gray-700', dot: 'bg-gray-400' };
const getModule    = (t) => moduleConfig[t]  || { label: t, icon: BriefcaseIcon, color: 'bg-gray-100 text-gray-700' };
const relativeTime = (d) => {
    if (!d) return '—';
    const date = new Date(d);
    const diff = Math.floor((Date.now() - date) / 86400000);
    if (diff === 0) return 'Today';
    if (diff === 1) return 'Yesterday';
    return date.toLocaleDateString('en-IN', { day: 'numeric', month: 'short' });
};

const stageProgress = (item) => {
    const approvals = item.approvals || [];
    const done = approvals.filter(a => a.status === 'approved' || a.status === 'skipped').length;
    const total = approvals.length || 1;
    return { done, total, pct: Math.round((done / total) * 100) };
};

const itemDetails = (item) => {
    if (Array.isArray(item?.details)) return item.details;
    if (Array.isArray(item?.entity_details)) return item.entity_details;
    return [];
};

const displayPeople = (people) => {
    if (!Array.isArray(people) || !people.length) return '—';
    return people.join(', ');
};

watch(activeTab, (tab) => {
    if (tab !== 'incoming') {
        selectedIncomingIds.value = [];
    }
});

watch(visibleIncomingIds, (ids) => {
    const allowed = new Set(ids);
    selectedIncomingIds.value = selectedIncomingIds.value.filter((id) => allowed.has(id));
});
</script>

<template>
    <Head title="Approval Center" />

    <div class="min-h-screen bg-neutral-50/50 px-4 py-8">

        <!-- ── Page Header ─────────────────────────────────────────────────── -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">
                    Approval <span class="text-emerald-600">Center</span>
                </h1>
                <p class="text-slate-500 mt-2 text-base font-medium">Manage your pending tasks and track your submitted requests in one place.</p>
            </div>
            
            <!-- Tab Switcher -->
            <div class="bg-white/80 backdrop-blur-xl p-1.5 rounded-2xl shadow-xl shadow-emerald-900/10 border border-white/40 flex gap-2 self-start ring-1 ring-emerald-500/10">
                <button 
                    @click="activeTab = 'incoming'"
                    :class="['px-6 py-2.5 rounded-xl text-sm font-black transition-all duration-300 flex items-center gap-3', 
                        activeTab === 'incoming' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30 -translate-y-0.5' : 'text-slate-500 hover:text-emerald-700 hover:bg-emerald-50/50']"
                >
                    <InboxIcon class="w-5 h-5" />
                    <span>To Approve</span>
                    <span v-if="stats.incoming_pending > 0" class="bg-white/20 text-white px-2 py-0.5 rounded-lg text-[10px]">{{ stats.incoming_pending }}</span>
                </button>
                <button 
                    @click="activeTab = 'requests'"
                    :class="['px-6 py-2.5 rounded-xl text-sm font-black transition-all duration-300 flex items-center gap-3', 
                        activeTab === 'requests' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30 -translate-y-0.5' : 'text-slate-500 hover:text-emerald-700 hover:bg-emerald-50/50']"
                >
                    <UserCircleIcon class="w-5 h-5" />
                    <span>My Requests</span>
                </button>
            </div>
        </div>

        <!-- ── Analysis Quick Stats ────────────────────────────────────────── -->
        <div v-if="activeTab === 'requests'" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/60">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Pending</p>
                <p class="text-3xl font-black text-amber-500">{{ stats.total_pending ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/60">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Approved</p>
                <p class="text-3xl font-black text-emerald-600">{{ stats.total_approved ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/60">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Rejected</p>
                <p class="text-3xl font-black text-rose-500">{{ stats.total_rejected ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/60">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Turnaround</p>
                <p class="text-3xl font-black text-slate-800">{{ stats.avg_turnaround_days ? `${stats.avg_turnaround_days}d` : '—' }}</p>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- ── Sidebar Filters ───────────────────────────────────────────── -->
            <aside class="w-full lg:w-72 shrink-0 glassmorphism rounded-3xl p-6 h-fit border border-white/40 sticky top-8">
                <div class="space-y-8">
                    <!-- Search -->
                    <div class="relative group">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Quick search..."
                            class="w-full pl-4 pr-10 py-3 rounded-2xl border-slate-100 bg-slate-50/50 text-sm focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                        />
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                    </div>

                    <!-- Module Filter -->
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-400 mb-4 px-1">Filter by Module</p>
                        <div class="space-y-1">
                            <button
                                v-for="m in moduleTypes"
                                :key="m.value"
                                @click="filterModule = m.value"
                                :class="['w-full flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-200 group', 
                                    filterModule === m.value ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-600 hover:bg-emerald-50 hover:text-emerald-700']"
                            >
                                <div class="flex items-center gap-3">
                                    <component :is="getModule(m.value).icon" class="w-5 h-5 opacity-70 group-hover:scale-110 transition-transform" />
                                    <span class="text-sm font-bold capitalize">{{ m.label }}</span>
                                </div>
                                <span v-if="m.count" :class="['text-[10px] font-bold px-2 py-0.5 rounded-md', filterModule === m.value ? 'bg-white/20' : 'bg-slate-100 group-hover:bg-emerald-100']">{{ m.count }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Date Filter -->
                    <div class="pt-4 border-t border-slate-100">
                         <div class="flex flex-col gap-2">
                             <input type="date" v-model="filterDateFrom" class="w-full rounded-xl border-slate-100 text-xs py-2 bg-slate-50/50" />
                             <input type="date" v-model="filterDateTo"   class="w-full rounded-xl border-slate-100 text-xs py-2 bg-slate-50/50" />
                             <button @click="applyDateFilter" class="w-full mt-2 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition-all">Apply Dates</button>
                             <button @click="clearFilters" class="text-xs text-slate-400 hover:text-rose-500 mt-2 font-bold transition-colors">Reset All</button>
                         </div>
                    </div>
                </div>
            </aside>

            <!-- ── List View ────────────────────────────────────────────────── -->
            <div class="flex-1 min-w-0 space-y-6">
                
                <!-- Results Header -->
                <div class="flex items-center justify-between">
                    <p class="text-xs font-black uppercase tracking-widest text-slate-400">
                         Showing {{ filtered.length }} {{ activeTab === 'incoming' ? 'pending approvals' : 'requests' }}
                    </p>
                    <div v-if="activeTab === 'incoming' && filtered.length" class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-600">
                            <input v-model="allIncomingSelected" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                            Check all
                        </label>
                        <button
                            type="button"
                            class="px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-black uppercase tracking-wider disabled:opacity-50"
                            :disabled="bulkLoading || !selectedIncomingIds.length"
                            @click="bulkApproveSelected"
                        >
                            <span v-if="bulkLoading">Approving...</span>
                            <span v-else>Approve Selected ({{ selectedIncomingIds.length }})</span>
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filtered.length === 0" class="bg-white rounded-[2.5rem] border-2 border-dashed border-slate-100 p-20 text-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <component :is="activeTab === 'incoming' ? CheckCircleIcon : ClockIcon" class="w-10 h-10 text-slate-200" />
                    </div>
                    <p class="text-xl font-black text-slate-800">Coast is clear!</p>
                    <p class="text-slate-400 text-sm mt-2 font-medium">No results found for your current filters.</p>
                </div>

                <!-- Items Grid -->
                <div v-else class="grid grid-cols-1 gap-4">
                    <div
                        v-for="item in filtered"
                        :key="item.id"
                        @click="openDrawer(item)"
                        class="group relative bg-white rounded-[2rem] border border-slate-100/80 p-6 shadow-sm hover:shadow-xl hover:shadow-slate-200/40 hover:-translate-y-1 transition-all duration-300 overflow-hidden cursor-pointer"
                    >
                        <div v-if="activeTab === 'incoming'" class="absolute top-4 right-4 z-10">
                            <input
                                :checked="selectedIncomingIds.includes(Number(item.id))"
                                type="checkbox"
                                class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                @click.stop
                                @change="toggleIncomingSelection(item.id)"
                            />
                        </div>
                        <div class="flex flex-col md:flex-row md:items-center gap-6">
                            <!-- Left: Icon & Module -->
                            <div class="flex items-center gap-4 shrink-0">
                                <div :class="['w-16 h-16 rounded-[1.25rem] flex items-center justify-center shadow-inner transition-transform group-hover:scale-105', getModule(item.entity_type).color]">
                                    <component :is="getModule(item.entity_type).icon" class="w-8 h-8" />
                                </div>
                                <div class="md:hidden">
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 leading-none mb-1">{{ getModule(item.entity_type).label }}</p>
                                    <p class="text-lg font-black text-slate-900 leading-tight">{{ item.summary || item.entity_summary }}</p>
                                </div>
                            </div>

                            <!-- Middle: Summary & Context -->
                            <div class="flex-1 min-w-0">
                                <div class="hidden md:block">
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 leading-none mb-1.5">{{ getModule(item.entity_type).label }}</p>
                                    <h4 class="text-xl font-black text-slate-900 leading-tight truncate mb-1">
                                         {{ item.summary || item.entity_summary }}
                                    </h4>
                                </div>
                                
                                <div class="flex items-center gap-4 text-xs font-bold text-slate-400">
                                     <div v-if="activeTab === 'incoming'" class="flex items-center gap-1.5 bg-slate-50 px-2 py-0.5 rounded-lg text-slate-600">
                                         <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                                         Requester: <span class="text-slate-900">{{ item.initiator }}</span>
                                     </div>
                                     <span class="flex items-center gap-1">
                                         <ClockIcon class="w-3.5 h-3.5" />
                                         {{ relativeTime(item.started_at || item.created_at) }}
                                     </span>
                                     <span v-if="item.current_stage" class="hidden sm:inline">• Stage: <span class="text-slate-600">{{ item.current_stage?.name }}</span></span>
                                </div>

                                <div class="mt-2 flex flex-col gap-1 text-[11px] font-semibold text-slate-500">
                                    <p>Raised By: <span class="text-slate-800">{{ item.raised_by || item.initiator || 'System' }}</span></p>
                                    <p>Approved By: <span class="text-slate-800">{{ displayPeople(item.approved_by) }}</span></p>
                                    <p>Pending With: <span class="text-slate-800">{{ displayPeople(item.pending_with) }}</span></p>
                                </div>

                                <div v-if="itemDetails(item).length" class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <div
                                        v-for="detail in itemDetails(item)"
                                        :key="`${item.id}-${detail.label}-${detail.value}`"
                                        class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs"
                                    >
                                        <span class="font-black uppercase tracking-wide text-slate-500">{{ detail.label }}:</span>
                                        <span class="ml-1 font-bold text-slate-800">{{ detail.value }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Actions/Status -->
                            <div class="shrink-0 flex items-center justify-end">
                                
                                <div v-if="activeTab === 'incoming'" class="flex items-center gap-2">
                                     <button 
                                        @click.stop="openDrawer(item)"
                                        class="bg-indigo-600 text-white px-5 py-2.5 rounded-2xl font-bold text-xs shadow-md shadow-indigo-600/10 hover:bg-indigo-700 hover:shadow-lg transition-all flex items-center gap-1.5"
                                     >
                                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                         Review & Action
                                     </button>
                                </div>

                                <!-- My Request Status -->
                                <div v-else class="text-right">
                                     <div :class="['inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest leading-none mb-3', getStatus(item.status).bg, getStatus(item.status).text]">
                                         {{ getStatus(item.status).label }}
                                     </div>
                                     <!-- Progress Dots -->
                                     <div class="flex gap-1 justify-end">
                                         <div v-for="n in 3" :key="n" :class="['w-1.5 h-1.5 rounded-full', n <= stageProgress(item).done ? 'bg-emerald-500' : 'bg-slate-100']"></div>
                                     </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Detail Drawer ───────────────────────────────────────────────────── -->
    <Teleport to="body">
        <div v-if="drawer.open" class="fixed inset-0 z-[60] flex justify-end">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="closeDrawer"></div>
            <div class="relative w-full max-w-xl bg-white shadow-2xl h-full flex flex-col animate-slide-left border-l border-slate-100">
                
                <!-- Drawer Header -->
                <div class="px-8 py-10 border-b border-slate-50 flex items-start justify-between">
                    <div class="flex gap-4">
                        <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center', getModule(drawer.item?.entity_type).color]">
                            <component :is="getModule(drawer.item?.entity_type).icon" class="w-8 h-8" />
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-900">{{ getModule(drawer.item?.entity_type).label }} Details</h3>
                            <p class="text-slate-400 font-bold text-sm tracking-wide mt-0.5">Submitted on {{ new Date(drawer.item?.started_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                        </div>
                    </div>
                    <button @click="closeDrawer" class="p-2 hover:bg-slate-50 rounded-xl transition-colors">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Drawer Body -->
                <div class="flex-1 overflow-y-auto px-8 py-8 space-y-10">
                    
                    <!-- Summary Card -->
                    <div class="bg-slate-50/80 rounded-3xl p-8 border border-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-5">
                             <component :is="getModule(drawer.item?.entity_type).icon" class="w-20 h-20" />
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 mb-2">Detailed Summary</p>
                        <p class="text-xl font-extrabold text-slate-800 leading-relaxed">{{ drawer.item?.entity_summary }}</p>
                        <div v-if="itemDetails(drawer.item).length" class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div
                                v-for="detail in itemDetails(drawer.item)"
                                :key="`drawer-${detail.label}-${detail.value}`"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs"
                            >
                                <span class="font-black uppercase tracking-wide text-slate-500">{{ detail.label }}:</span>
                                <span class="ml-1 font-bold text-slate-800">{{ detail.value }}</span>
                            </div>
                        </div>
                    </div>
 
                    <!-- Sub-task Verification Submission Metadata -->
                    <div v-if="drawer.item?.entity_type === 'task_checklist' && drawer.item?.decoded_payload" class="bg-indigo-50/20 border border-indigo-100/50 rounded-3xl p-6 space-y-6">
                        <div class="flex items-center justify-between border-b border-indigo-100/30 pb-3">
                            <h4 class="text-xs font-black text-indigo-900 uppercase tracking-widest flex items-center gap-2">
                                <SparklesIcon class="w-4 h-4 text-indigo-500" />
                                Sub-task Submission Details
                            </h4>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="bg-white p-3 rounded-xl border border-indigo-100/30">
                                <span class="block text-[10px] font-black uppercase text-indigo-400">Git Branch</span>
                                <span class="text-sm font-bold text-slate-800 font-mono">{{ drawer.item?.decoded_payload.git_branch || '—' }}</span>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-indigo-100/30 flex items-center justify-between">
                                <div>
                                    <span class="block text-[10px] font-black uppercase text-indigo-400">Git Commit</span>
                                    <span class="text-sm font-bold text-slate-800 font-mono">{{ drawer.item?.decoded_payload.git_commit ? drawer.item?.decoded_payload.git_commit.substring(0, 10) : '—' }}</span>
                                </div>
                                <button 
                                    v-if="drawer.item?.decoded_payload.git_commit" 
                                    @click="copyToClipboard(drawer.item?.decoded_payload.git_commit, 'commit')"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-bold bg-indigo-50 px-2 py-1 rounded-md"
                                >
                                    {{ copiedField === 'commit' ? 'Copied!' : 'Copy' }}
                                </button>
                            </div>
                        </div>

                        <div v-if="drawer.item?.decoded_payload.git_pr_url" class="bg-white p-3 rounded-xl border border-indigo-100/30">
                            <span class="block text-[10px] font-black uppercase text-indigo-400 mb-1">Git Pull Request URL</span>
                            <a :href="drawer.item?.decoded_payload.git_pr_url" target="_blank" class="text-sm font-bold text-indigo-600 hover:underline flex items-center gap-1.5 break-all">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                {{ drawer.item?.decoded_payload.git_pr_url }}
                            </a>
                        </div>

                        <div v-if="drawer.item?.decoded_payload.frameworks" class="bg-white p-3 rounded-xl border border-indigo-100/30">
                            <span class="block text-[10px] font-black uppercase text-indigo-400">Frameworks / Technologies</span>
                            <span class="text-sm font-bold text-slate-800">{{ drawer.item?.decoded_payload.frameworks }}</span>
                        </div>

                        <div v-if="drawer.item?.decoded_payload.code_references" class="bg-white p-4 rounded-xl border border-indigo-100/30">
                            <span class="block text-[10px] font-black uppercase text-indigo-400 mb-1.5">Code / File References</span>
                            <pre class="text-xs font-mono text-slate-700 bg-slate-50 p-2.5 rounded-lg overflow-x-auto whitespace-pre-wrap">{{ drawer.item?.decoded_payload.code_references }}</pre>
                        </div>

                        <div v-if="drawer.item?.decoded_payload.feedback" class="bg-indigo-900/5 p-4 rounded-xl border border-indigo-100/50">
                            <span class="block text-[10px] font-black uppercase text-indigo-900 mb-1.5">Submission Description / Notes</span>
                            <p class="text-sm text-indigo-950 font-medium whitespace-pre-wrap leading-relaxed">{{ drawer.item?.decoded_payload.feedback }}</p>
                        </div>
                    </div>
 
                     <!-- Workflow Progress -->
                     <div>
                         <div class="flex justify-between items-end mb-6">
                             <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Workflow Timeline</p>
                             <span class="text-xs font-black text-emerald-600">{{ stageProgress(drawer.item).done }} of {{ stageProgress(drawer.item).total }} Stages Complete</span>
                         </div>
                         
                         <div class="space-y-4">
                             <div v-for="(approval, idx) in drawer.item?.approvals" :key="approval.id" class="flex gap-4 group">
                                 <div class="flex flex-col items-center">
                                     <div :class="['w-10 h-10 rounded-2xl flex items-center justify-center border-2 z-10 transition-all', 
                                         approval.status === 'approved' ? 'bg-emerald-500 border-emerald-500 text-white' : 
                                         approval.status === 'rejected' ? 'bg-rose-500 border-rose-500 text-white' : 
                                         approval.status === 'pending' ? 'bg-amber-400 border-amber-400 text-white animate-pulse' : 'bg-white border-slate-100 text-slate-300']">
                                         <template v-if="approval.status === 'approved'"><CheckCircleIcon class="w-6 h-6" /></template>
                                         <template v-else-if="approval.status === 'rejected'"><XCircleIcon class="w-6 h-6" /></template>
                                         <template v-else><span class="text-xs font-black">{{ idx + 1 }}</span></template>
                                     </div>
                                     <div v-if="idx < (drawer.item?.approvals?.length - 1)" class="w-0.5 h-10 bg-slate-100 my-1"></div>
                                 </div>
                                 <div class="flex-1 pb-8">
                                     <div class="flex justify-between items-center mb-1">
                                         <h5 class="text-base font-black text-slate-800">{{ approval.stage?.name }}</h5>
                                         <span :class="['text-[10px] font-black px-2 py-0.5 rounded-md uppercase tracking-wider', getStatus(approval.status).bg, getStatus(approval.status).text]">
                                             {{ approval.status }}
                                         </span>
                                     </div>
                                     <p class="text-xs font-bold text-slate-400 mb-2">Approver: <span class="text-slate-600">{{ approval.approver?.name || 'Pool Approver' }}</span></p>
                                     <div v-if="approval.comments" class="bg-indigo-50/30 border border-indigo-100 rounded-2xl p-4 italic text-sm text-indigo-900/70">
                                         "{{ approval.comments }}"
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>

                 <!-- Drawer Action Form for Incoming Requests -->
                 <div v-if="activeTab === 'incoming' && drawer.item?.status === 'pending'" class="bg-slate-50 border-t border-slate-100 p-8 space-y-5">
                     <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest">Process Approval Action</h4>
                     
                     <div>
                         <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Remarks / Feedback Comments</label>
                         <textarea 
                             v-model="drawerRemarks" 
                             placeholder="Provide details about your decision or correction requests..." 
                             class="w-full text-sm rounded-2xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" 
                             rows="3"
                         ></textarea>
                     </div>
                     
                     <div class="flex flex-col sm:flex-row gap-3">
                         <button 
                             @click="processAction(drawer.item.id, 'approve', drawerRemarks)"
                             :disabled="actionLoading"
                             class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3.5 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-1.5 shadow-lg shadow-emerald-600/10 disabled:opacity-50"
                         >
                             <CheckCircleIcon class="w-5 h-5" />
                             Approve
                         </button>
                         <button 
                             v-if="drawer.item?.entity_type === 'task_checklist'"
                             @click="processAction(drawer.item.id, 'correction', drawerRemarks)"
                             :disabled="actionLoading"
                             class="flex-1 bg-amber-500 hover:bg-amber-600 text-white px-4 py-3.5 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-1.5 shadow-lg shadow-amber-500/10 disabled:opacity-50"
                         >
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                             Correction
                         </button>
                         <button 
                             @click="processAction(drawer.item.id, 'reject', drawerRemarks)"
                             :disabled="actionLoading"
                             class="flex-1 bg-rose-600 hover:bg-rose-700 text-white px-4 py-3.5 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-1.5 shadow-lg shadow-rose-600/10 disabled:opacity-50"
                         >
                             <XCircleIcon class="w-5 h-5" />
                             Reject
                         </button>
                     </div>
                 </div>
 
                 <!-- Footer -->
                 <div class="p-8 border-t border-slate-50 bg-slate-50/50">
                     <button @click="closeDrawer" class="w-full py-4 bg-white border border-slate-200 rounded-2xl text-slate-600 font-black text-sm hover:bg-slate-50 transition-all">Close Details</button>
                 </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.glassmorphism {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
}
.animate-slide-left {
    animation: slideLeft 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes slideLeft {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}
</style>
