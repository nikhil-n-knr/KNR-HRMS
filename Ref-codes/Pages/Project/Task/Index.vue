<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="space-y-1">
                <h2 class="text-2xl md:text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-indigo-700 to-violet-600 tracking-tight">
                    Task Backlog
                </h2>
                <p class="text-xs text-gray-500 font-medium">All project tasks — move items to backlog to hide them from the Sprint Board.</p>
            </div>
            <div class="flex gap-3">
                <button
                    @click="openCreate"
                    class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-indigo-600/20 flex items-center gap-2 hover:bg-indigo-700 transition-all active:scale-95"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Task
                </button>
                <button @click="exportTasks" class="px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-black uppercase tracking-widest text-gray-600 hover:bg-gray-50 flex items-center gap-2 shadow-sm transition-all active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="p-4 bg-white/80 backdrop-blur-md rounded-2xl border border-gray-200 shadow-xl shadow-indigo-500/5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <input v-model="filters.search" placeholder="Search tasks..." @input="debouncedApply" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2"/>

            <select v-model="filters.is_backlog" @change="applyFilters" class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500">
                <option value="">All Tasks</option>
                <option value="0">Board Tasks</option>
                <option value="1">Backlog Only</option>
            </select>

            <select v-model="filters.priority" @change="applyFilters" class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500">
                <option value="">All Priorities</option>
                <option v-for="p in project.priorities" :key="p.id" :value="p.name">{{ p.name }}</option>
            </select>

            <select v-model="filters.status" @change="applyFilters" class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500">
                <option value="">All Stages</option>
                <option v-for="s in project.stages" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>

            <button @click="clearFilters" class="w-full py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs font-bold uppercase tracking-widest transition-all">Clear</button>
        </div>

        <!-- Table -->
        <BaseDataTable
            :data="tasks.data"
            :columns="columns"
            :meta="{ current_page: tasks.current_page, last_page: tasks.last_page, total: tasks.total, from: tasks.from, to: tasks.to }"
            @page-change="onPageChange"
        >
            <template #cell-title="{ item }">
                <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-900 truncate max-w-xs">{{ item.title }}</span>
                    <span v-if="item.is_backlog" class="px-1.5 py-0.5 bg-amber-100 text-amber-700 text-[9px] font-black uppercase rounded tracking-wider">Backlog</span>
                </div>
            </template>

            <template #cell-stage="{ item }">
                <span class="px-2 py-1 rounded-full text-xs font-bold whitespace-nowrap"
                    :style="item.stage ? { backgroundColor: item.stage.color + '20', color: item.stage.color } : {}">
                    {{ item.stage?.name || '--' }}
                </span>
            </template>

            <template #cell-priority="{ item }">
                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase">{{ item.priority || '--' }}</span>
            </template>

            <template #cell-assignees="{ item }">
                <div class="flex -space-x-1">
                    <div v-for="a in item.assignees" :key="a.id"
                        class="w-6 h-6 rounded-full bg-indigo-100 border border-white flex items-center justify-center text-[10px] font-bold text-indigo-700"
                        :title="`${a.first_name} ${a.last_name}`">
                        {{ a.first_name?.[0] }}{{ a.last_name?.[0] }}
                    </div>
                    <span v-if="!item.assignees?.length" class="text-gray-400 text-xs italic">—</span>
                </div>
            </template>

            <template #cell-due_date="{ value }">
                <span class="text-xs text-gray-500">{{ value ? new Date(value).toLocaleDateString() : '--' }}</span>
            </template>

            <template #rowActions="{ item }">
                <div class="flex items-center gap-1 justify-end">
                    <!-- Analytics -->
                    <Link :href="route('projects.tasks.analytics', { project: project.id, task: item.id })" title="Analytics"
                        class="p-1.5 text-emerald-500 hover:bg-emerald-50 rounded transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </Link>
                    <!-- Move to/from Backlog -->
                    <button v-if="!item.is_backlog" @click="moveToBacklog(item)" title="Move to Backlog"
                        class="p-1.5 text-amber-500 hover:bg-amber-50 rounded transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12"/></svg>
                    </button>
                    <button v-else @click="restoreToBoard(item)" title="Restore to Board"
                        class="p-1.5 text-green-500 hover:bg-green-50 rounded transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11l3-3m0 0l3 3m-3-3v8m0-13a9 9 0 110 18 9 9 0 010-18z"/></svg>
                    </button>
                    <!-- Edit -->
                    <button @click="openEdit(item)" title="Edit"
                        class="p-1.5 text-indigo-500 hover:bg-indigo-50 rounded transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                    </button>
                    <!-- Delete -->
                    <button @click="deleteTask(item)" :title="canDelete ? 'Delete Task' : 'You do not have permission to delete tasks.'"
                        class="p-1.5 rounded transition-colors"
                        :class="canDelete ? 'text-red-400 hover:bg-red-50' : 'text-gray-300 cursor-not-allowed'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    </button>
                </div>
            </template>

            <template #empty>
                <div class="flex flex-col items-center justify-center py-12">
                    <div class="bg-indigo-50 p-6 rounded-full mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">No tasks found</h3>
                    <p class="text-gray-500 mt-1 text-sm">Try adjusting your filters or add a new task.</p>
                </div>
            </template>
        </BaseDataTable>
    </div>

    <!-- Unified Task Modal -->
    <TaskFullModal 
        :show="showTaskModal"
        :task-id="focusedTaskId"
        :project-id="project.id"
        :projects="[project]"
        :employees="employees"
        :modules="modules"
        :task-templates="[]"
        @close="closeTaskModal"
        @success="handleTaskSuccess"
        @deleted="handleTaskDeleted"
    />
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import ProjectLayout from '@/Layouts/ProjectLayout.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TaskFullModal from '@/Components/Project/TaskFullModal.vue';
import { useToastStore } from '@/stores/toast';

defineOptions({ layout: ProjectLayout });

const props = defineProps({
    project: Object,
    tasks: Object,
    filters: Object,
    employees: Array,
    sprints: Array,
    modules: Array,
});

const toast = useToastStore();
const page = usePage();

const canDelete = computed(() => {
    const roles = page.props.auth?.user?.roles || [];
    return roles.some(r => ['Super Admin', 'Admin', 'Manager'].includes(r.name));
});

const columns = [
    { key: 'title',     label: 'Task Title' },
    { key: 'stage',     label: 'Stage' },
    { key: 'priority',  label: 'Priority' },
    { key: 'assignees', label: 'Assignees' },
    { key: 'due_date',  label: 'Due Date' },
    { key: 'scrum_points', label: 'Pts', class: 'text-center w-16' },
];

const filters = ref({
    status:     props.filters?.status     || '',
    priority:   props.filters?.priority   || '',
    assignees:  props.filters?.assignees  || [],
    search:     props.filters?.search     || '',
    is_backlog: props.filters?.is_backlog ?? '',
});

const applyFilters = () => {
    router.get(route('projects.tasks.index', props.project.id), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

let debounceTimer = null;
const debouncedApply = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(applyFilters, 400);
};

const clearFilters = () => {
    filters.value = { status: '', priority: '', assignees: [], search: '', is_backlog: '' };
    applyFilters();
};

const exportTasks = () => {
    window.location.href = route('projects.tasks.index', { project: props.project.id, ...filters.value, export: 1 });
};

const onPageChange = (page) => {
    router.visit(route('projects.tasks.index', { project: props.project.id, page, ...filters.value }), { preserveState: true });
};

// ---- Unified Modal State ----
const showTaskModal = ref(false);
const focusedTaskId = ref(null);
const submitting = ref(false);
const formErrors = ref({});

const defaultForm = () => ({
    title: '', description: '', stage_id: props.project.stages?.[0]?.id || null,
    priority: 'Medium', sprint_id: null, scrum_points: 0, due_date: '', is_backlog: false,
});

const form = ref(defaultForm());

const openCreate = () => {
    focusedTaskId.value = null;
    showTaskModal.value = true;
};

const openEdit = (task) => {
    focusedTaskId.value = task.id;
    showTaskModal.value = true;
};

const closeTaskModal = () => {
    showTaskModal.value = false;
    focusedTaskId.value = null;
};

const handleTaskSuccess = () => {
    router.reload({ preserveScroll: true });
};

const handleTaskDeleted = () => {
    router.reload({ preserveScroll: true });
};

const deleteTask = (task) => {
    if (!canDelete.value) {
        toast.error('You do not have permission to delete tasks.');
        return;
    }
    if (confirm(`Are you sure you want to delete "${task.title}"?`)) {
        router.delete(route('projects.tasks.destroy', { project: props.project.id, task: task.id }), {
            preserveScroll: true,
            onSuccess: () => toast.success('Task deleted successfully'),
        });
    }
};

// ---- Backlog Actions ----
const moveToBacklog = async (task) => {
    try {
        await axios.post(route('projects.tasks.backlog', { project: props.project.id, task: task.id }));
        toast.success(`"${task.title}" moved to backlog — hidden from the Sprint Board`);
        router.reload({ preserveScroll: true });
    } catch (e) {
        toast.error('Failed to move to backlog');
    }
};

const restoreToBoard = async (task) => {
    try {
        await axios.post(route('projects.tasks.restore', { project: props.project.id, task: task.id }));
        toast.success(`"${task.title}" restored to Sprint Board`);
        router.reload({ preserveScroll: true });
    } catch (e) {
        toast.error('Failed to restore');
    }
};
</script>
