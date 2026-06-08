<template>
    <!-- ░░ Outer Page Shell ░░ -->
    <div class="min-h-screen bg-[#f4f5fa]">

        <!-- ▓▓ GRADIENT HERO HEADER ▓▓ -->
        <div class="relative overflow-hidden sm:rounded-2xl mx-0 sm:mx-6 mt-0 sm:mt-6
                    bg-gradient-to-br from-[#3d27b4] via-[#6b3fd4] to-[#a855f7]">
            <!-- Decorative blobs -->
            <div class="absolute -top-20 -right-20 w-80 h-80 bg-white/5 rounded-full pointer-events-none"></div>
            <div class="absolute bottom-0 left-1/3 w-56 h-56 bg-white/5 rounded-full pointer-events-none"></div>

            <div class="relative z-10 px-6 sm:px-10 py-8
                        flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <!-- Left: title + desc -->
                <div>
                    <p class="text-xs font-bold text-white/50 uppercase tracking-widest mb-2">Projects</p>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                            {{ filters.view === 'all' ? 'All Tasks' : 'My Tasks' }}
                        </h1>
                        <!-- Tab Switch (Only for Admins/Super Admins/Managers) -->
                        <div v-if="canViewAll" class="flex bg-black/20 backdrop-blur-sm p-1 rounded-xl border border-white/10 sm:ml-2 w-fit">
                            <button 
                                @click="switchView('my')" 
                                class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all"
                                :class="filters.view !== 'all' ? 'bg-white text-indigo-700 shadow-sm' : 'text-white/80 hover:text-white'"
                            >
                                My Tasks
                            </button>
                            <button 
                                @click="switchView('all')" 
                                class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all"
                                :class="filters.view === 'all' ? 'bg-white text-indigo-700 shadow-sm' : 'text-white/80 hover:text-white'"
                            >
                                All Tasks
                            </button>
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-white/60 max-w-md leading-relaxed">
                        {{ filters.view === 'all' 
                            ? 'All tasks across active projects, available for administrative oversight and editing.' 
                            : 'All tasks assigned to you across active projects, filtered by status and priority.' }}
                    </p>
                </div>

                <!-- Right: stat pills -->
                <div class="flex flex-wrap gap-3 shrink-0">
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[130px]">
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Total Tasks</p>
                        <p class="text-4xl font-extrabold text-white leading-none">{{ tasks.total }}</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[130px]">
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Current Page</p>
                        <p class="text-3xl font-extrabold text-white leading-none">{{ tasks.current_page }} / {{ tasks.last_page }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ▓▓ INNER CONTENT BODY ▓▓ -->
        <div class="mx-0 sm:mx-6 mt-5 pb-12 space-y-5">

            <!-- Filters Bar -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-5 space-y-4 relative z-30">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Search Input -->
                    <div class="md:col-span-4 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Search Tasks</label>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Search by title..."
                            class="block w-full px-4 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 placeholder-slate-400 h-[42px]"
                            @keyup.enter="applyFilters"
                        />
                    </div>

                    <!-- Project Filter -->
                    <div class="md:col-span-4 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Project</label>
                        <select
                            v-model="filters.project_id"
                            class="block w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 h-[42px] bg-white"
                        >
                            <option value="">All Projects</option>
                            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>

                    <!-- Person Filter -->
                    <div class="md:col-span-4 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Assignee</label>
                        <select
                            v-model="filters.person_id"
                            class="block w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 h-[42px] bg-white"
                        >
                            <option value="">All Assignees</option>
                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Status Filter -->
                    <div class="md:col-span-3 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Status</label>
                        <MultiUserSelect
                            v-model="filters.status"
                            :items="statusOptions"
                            labelKey="name"
                            valueKey="name"
                            placeholder="Filter by status…"
                            class="w-full"
                        />
                    </div>

                    <!-- Priority Filter -->
                    <div class="md:col-span-2 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Priority</label>
                        <MultiUserSelect
                            v-model="filters.priority"
                            :items="priorityOptions"
                            labelKey="name"
                            valueKey="name"
                            placeholder="Filter by priority…"
                            class="w-full"
                        />
                    </div>

                    <!-- Start Date -->
                    <div class="md:col-span-2 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Start Date From</label>
                        <input
                            v-model="filters.start_date"
                            type="date"
                            class="block w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 h-[42px] bg-white"
                        />
                    </div>

                    <!-- End Date -->
                    <div class="md:col-span-2 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Due Date To</label>
                        <input
                            v-model="filters.end_date"
                            type="date"
                            class="block w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 h-[42px] bg-white"
                        />
                    </div>

                    <!-- Archive Filter -->
                    <div class="md:col-span-3 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Archive Status</label>
                        <select
                            v-model="filters.archive"
                            class="block w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 h-[42px] bg-white"
                        >
                            <option value="active">Active Tasks</option>
                            <option value="archived">Archived Tasks</option>
                            <option value="all">All (Active & Archived)</option>
                        </select>
                    </div>
                </div>

                <!-- Action Row -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100">
                    <div class="flex gap-2">
                        <button
                            @click="applyFilters"
                            class="h-[42px] inline-flex items-center justify-center gap-2
                                   px-6 rounded-xl bg-indigo-600 text-white text-sm font-bold
                                   hover:bg-indigo-700 active:scale-[0.98] transition-all shadow-sm shadow-indigo-200"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                            </svg>
                            Apply Filters
                        </button>
                        <button
                            @click="exportTasks"
                            class="h-[42px] inline-flex items-center justify-center gap-2
                                   px-6 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold
                                   hover:bg-slate-200 active:scale-[0.98] transition-all"
                        >
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export CSV
                        </button>
                    </div>

                    <button
                        v-if="filters.view === 'all' && canViewAll"
                        @click="openCreateModal"
                        class="h-[42px] inline-flex items-center justify-center gap-2
                               px-6 rounded-xl bg-emerald-600 text-white text-sm font-bold
                               hover:bg-emerald-700 active:scale-[0.98] transition-all shadow-sm shadow-emerald-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Task
                    </button>
                </div>
            </div>

            <!-- Data Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <BaseDataTable
                    :data="tasks.data"
                    :columns="columns"
                    :row-clickable="true"
                    @row-click="onRowClick"
                    :meta="{
                        current_page: tasks.current_page,
                        last_page:    tasks.last_page,
                        total:        tasks.total,
                        from:         tasks.from,
                        to:           tasks.to,
                        per_page:     tasks.per_page
                    }"
                    @page-change="onPageChange"
                >
                    <!-- Project -->
                    <template #cell-project="{ value }">
                        <span class="text-sm font-semibold text-slate-700">{{ value?.name || '—' }}</span>
                    </template>

                    <!-- Status -->
                    <template #cell-status="{ item }">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border"
                                :style="getStatusStyle(item.stage?.name, item)"
                            >
                                <span class="w-1.5 h-1.5 rounded-full" :style="{ background: item?.stage?.color || '#6b7280' }"></span>
                                {{ item.stage?.name || 'Not Assigned' }}
                            </span>
                            <span
                                v-if="item.is_backlog"
                                class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 uppercase tracking-wider"
                            >
                                Archived
                            </span>
                        </div>
                    </template>

                    <!-- Priority -->
                    <template #cell-priority="{ value, item }">
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold uppercase tracking-wide"
                            :style="getPriorityStyle(value, item)"
                        >
                            {{ value }}
                        </span>
                    </template>

                    <!-- Assignee -->
                    <template #cell-assignee="{ item }">
                        <div class="flex items-center gap-2">
                            <div v-if="item.assignees && item.assignees.length" class="flex -space-x-2">
                                <div
                                    v-for="assignee in item.assignees"
                                    :key="assignee.id"
                                    :title="assignee.name"
                                    class="h-7 w-7 rounded-xl bg-indigo-100 border-2 border-white
                                           flex items-center justify-center text-xs font-extrabold text-indigo-700
                                           shadow-sm"
                                >
                                    {{ assignee.name ? assignee.name[0] : '?' }}
                                </div>
                            </div>
                            <span v-else class="text-slate-300 text-xs italic font-medium">Unassigned</span>
                        </div>
                    </template>

                    <!-- Start Date -->
                    <template #cell-start_date="{ value }">
                        <span class="text-xs text-slate-500 font-medium">{{ formatDate(value) }}</span>
                    </template>

                    <!-- Due Date -->
                    <template #cell-due_date="{ value }">
                        <span class="text-xs text-slate-500 font-medium">{{ formatDate(value) }}</span>
                    </template>

                    <!-- Row Actions -->
                    <template #rowActions="{ item }">
                        <div class="flex items-center gap-1 justify-end">
                            <!-- Analytics -->
                            <Link :href="route('projects.tasks.analytics', { project: item.project.id, task: item.id })" title="Analytics"
                                class="p-1.5 text-emerald-500 hover:bg-emerald-50 rounded transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </Link>
                            <!-- Move to/from Backlog (Only Admins/Managers) -->
                            <template v-if="canDelete">
                                <button v-if="!item.is_backlog" @click.stop="moveToBacklog(item)" title="Move to Backlog"
                                    class="p-1.5 text-amber-500 hover:bg-amber-50 rounded transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12"/></svg>
                                </button>
                                <button v-else @click.stop="restoreToBoard(item)" title="Restore to Board"
                                    class="p-1.5 text-green-500 hover:bg-green-50 rounded transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11l3-3m0 0l3 3m-3-3v8m0-13a9 9 0 110 18 9 9 0 010-18z"/></svg>
                                </button>
                            </template>
                            <!-- Edit -->
                            <button @click.stop="openEdit(item)" title="Edit"
                                class="p-1.5 text-indigo-500 hover:bg-indigo-50 rounded transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                            </button>
                            <!-- Delete -->
                            <button @click.stop="deleteTask(item)" :title="canDelete ? 'Delete Task' : 'You do not have permission to delete tasks.'"
                                class="p-1.5 rounded transition-colors"
                                :class="canDelete ? 'text-red-400 hover:bg-red-50' : 'text-gray-300 cursor-not-allowed'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            </button>
                        </div>
                    </template>

                    <!-- Empty State -->
                    <template #empty>
                        <div class="flex flex-col items-center justify-center py-20">
                            <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-extrabold text-slate-900">No tasks found!</h3>
                            <p class="text-sm text-slate-400 mt-1 max-w-sm text-center">Try adjusting your filters or status checks.</p>
                        </div>
                    </template>
                </BaseDataTable>
            </div>

        </div><!-- /inner body -->

        <!-- Unified Task Modal (Create/Edit/Full) -->
        <TaskFullModal 
            :show="showTaskModal"
            :task-id="focusedTaskId"
            :projects="projects"
            :employees="employees"
            @close="closeTaskModal"
            @success="handleTaskSuccess"
            @deleted="handleTaskDeleted"
        />

    </div><!-- /outer shell -->
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import MainLayout from '@/Layouts/MainLayout.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import MultiUserSelect from '@/Components/MultiUserSelect.vue';
import TaskFullModal from '@/Components/Project/TaskFullModal.vue';
import { useToastStore } from '@/stores/toast';

defineOptions({ layout: MainLayout });

const props = defineProps({
    tasks:      Object,
    filters:    Object,
    canViewAll: Boolean,
    projects:   { type: Array, default: () => [] },
    employees:  { type: Array, default: () => [] }
});

const filters = ref({
    status:     props.filters?.status     || [],
    priority:   props.filters?.priority   || [],
    search:     props.filters?.search     || '',
    view:       props.filters?.view       || 'my',
    archive:    props.filters?.archive    || 'active',
    project_id: props.filters?.project_id || '',
    person_id:  props.filters?.person_id  || '',
    start_date: props.filters?.start_date || '',
    end_date:   props.filters?.end_date   || ''
});

const statusOptions = [
    { name: 'To Do' },
    { name: 'In Progress' },
    { name: 'In Review' },
    { name: 'Done' }
];

const priorityOptions = [
    { name: 'Critical' },
    { name: 'High' },
    { name: 'Medium' },
    { name: 'Low' }
];

const toast = useToastStore();
const page = usePage();

const canDelete = computed(() => {
    const roles = page.props.auth?.user?.roles || [];
    return roles.some(r => ['Super Admin', 'Admin', 'Manager'].includes(r.name));
});

const columns = [
    { key: 'project',      label: 'Project',    class: 'font-medium text-slate-600' },
    { key: 'title',        label: 'Task Title', class: 'font-semibold text-slate-900' },
    { key: 'status',       label: 'Status' },
    { key: 'assignee',     label: 'Assignee' },
    { key: 'priority',     label: 'Priority' },
    { key: 'scrum_points', label: 'Pts',        class: 'text-center w-16' },
    { key: 'start_date',   label: 'Start Date' },
    { key: 'due_date',     label: 'Due Date' }
];

// Task modal state
const showTaskModal = ref(false);
const focusedTaskId = ref(null);

const openEdit = (task) => {
    focusedTaskId.value = task.id;
    showTaskModal.value = true;
};

const deleteTask = (task) => {
    if (!canDelete.value) {
        toast.error('You do not have permission to delete tasks.');
        return;
    }
    if (confirm(`Are you sure you want to delete "${task.title}"?`)) {
        router.delete(route('projects.tasks.destroy', { project: task.project.id, task: task.id }), {
            preserveScroll: true,
            onSuccess: () => toast.success('Task deleted successfully'),
        });
    }
};

const moveToBacklog = async (task) => {
    try {
        await axios.post(route('projects.tasks.backlog', { project: task.project.id, task: task.id }));
        toast.success(`"${task.title}" moved to backlog — hidden from the Sprint Board`);
        router.reload({ preserveScroll: true });
    } catch (e) {
        toast.error('Failed to move to backlog');
    }
};

const restoreToBoard = async (task) => {
    try {
        await axios.post(route('projects.tasks.restore', { project: task.project.id, task: task.id }));
        toast.success(`"${task.title}" restored to Sprint Board`);
        router.reload({ preserveScroll: true });
    } catch (e) {
        toast.error('Failed to restore');
    }
};

const onRowClick = (item) => {
    focusedTaskId.value = item.id;
    showTaskModal.value = true;
};

const openCreateModal = () => {
    focusedTaskId.value = null;
    showTaskModal.value = true;
};

const closeTaskModal = () => {
    showTaskModal.value = false;
    focusedTaskId.value = null;
};

const handleTaskSuccess = () => {
    closeTaskModal();
    router.reload({ only: ['tasks'] });
};

const handleTaskDeleted = () => {
    closeTaskModal();
    router.reload({ only: ['tasks'] });
};

const applyFilters = () => {
    router.get(route('projects.my-tasks'), filters.value, {
        preserveState:  true,
        preserveScroll: true
    });
};

const exportTasks = () => {
    const params = new URLSearchParams();
    Object.keys(filters.value).forEach(key => {
        const val = filters.value[key];
        if (val !== null && val !== undefined && val !== '') {
            if (Array.isArray(val)) {
                if (val.length > 0) {
                    params.append(key, val.join(','));
                }
            } else {
                params.append(key, val);
            }
        }
    });
    window.location.href = route('projects.my-tasks.export') + '?' + params.toString();
};

const onPageChange = (page) => {
    router.visit(route('projects.my-tasks', { page, ...filters.value }), { preserveState: true });
};

const switchView = (newView) => {
    filters.value.view = newView;
    applyFilters();
};

const formatDate = (dateString) => {
    if (!dateString) return '—';
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric', month: 'short', day: 'numeric'
    });
};

const getStatusStyle = (statusName, item) => {
    if (item?.stage?.color) {
        return {
            backgroundColor: item.stage.color + '20',
            color:           item.stage.color,
            borderColor:     item.stage.color + '40'
        };
    }
    return { backgroundColor: '#f1f5f9', color: '#64748b', borderColor: '#e2e8f0' };
};

const getPriorityStyle = (priorityName) => {
    const map = {
        Critical: '#ef4444',
        High:     '#f97316',
        Medium:   '#f59e0b',
        Low:      '#10b981'
    };
    const color = map[priorityName] || '#6b7280';
    return { backgroundColor: color + '18', color };
};
</script>