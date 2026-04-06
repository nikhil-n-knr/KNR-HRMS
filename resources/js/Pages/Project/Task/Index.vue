<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
             <div class="space-y-1">
                 <h2 class="text-2xl md:text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-indigo-700 to-violet-600 tracking-tight">
                    Task Backlog
                </h2>
                <p class="text-xs text-gray-500 font-medium">Manage and refine project requirements.</p>
             </div>
            <div class="flex w-full md:w-auto gap-3">
                 <button 
                    @click="exportTasks"
                    class="flex-1 md:flex-none px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-black uppercase tracking-widest text-gray-600 hover:bg-gray-50 flex items-center justify-center gap-2 shadow-sm transition-all active:scale-95"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export
                </button>
            </div>
        </div>

        <!-- Filters Bar -->
        <div class="p-6 bg-white/80 backdrop-blur-md rounded-2xl border border-gray-200 shadow-xl shadow-indigo-500/5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 relative z-30">
            <MultiUserSelect
                v-model="filters.status"
                :items="statusOptions"
                labelKey="name"
                valueKey="id"
                placeholder="Status"
                class="w-full"
            />
            
            <MultiUserSelect
                v-model="filters.priority"
                :items="priorityOptions"
                labelKey="name"
                valueKey="name"
                placeholder="Priority"
                class="w-full"
            />

            <div class="sm:col-span-2">
                <MultiUserSelect
                    v-model="filters.assignees"
                    :items="memberOptions"
                    placeholder="Filter by Assignee"
                    class="w-full"
                />
            </div>

            <div class="flex items-end">
                <button @click="applyFilters" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-indigo-600/20 transition-all active:scale-95">Apply</button>
            </div>
        </div>

        <BaseDataTable
            :data="tasks.data"
            :columns="columns"
            :meta="
                {
                    current_page: tasks.current_page,
                    last_page: tasks.last_page,
                    total: tasks.total,
                    from: tasks.from,
                    to: tasks.to,
                    per_page: tasks.per_page
                }
            "
            @page-change="onPageChange"
        >
            <template #cell-status="{ value, item }">
                 <span class="px-2 py-1 rounded-full text-xs font-bold whitespace-nowrap" 
                    :style="getStatusStyle(value, item)"
                 >
                    {{ value }}
                 </span>
            </template>

            <template #cell-priority="{ value, item }">
                 <span class="px-2 py-0.5 rounded text-sm font-bold uppercase tracking-wide"
                    :style="getPriorityStyle(value, item)"
                 >
                    {{ value }}
                 </span>
            </template>

            <template #cell-assignee="{ item }">
                <div class="flex items-center gap-2">
                    <div v-if="item.assignees && item.assignees.length" class="flex -space-x-2">
                         <div v-for="assignee in item.assignees" :key="assignee.id" class="h-6 w-6 rounded-full bg-indigo-100 border border-white flex items-center justify-center text-sm font-bold text-indigo-700" :title="assignee.name">
                             {{ assignee.first_name[0] }}
                         </div>
                    </div>
                    <span v-else class="text-gray-400 text-xs italic">Unassigned</span>
                </div>
            </template>

            <template #cell-due_date="{ value }">
                 <span class="text-sm text-gray-500">{{ formatDate(value) }}</span>
            </template>

            <template #empty>
                <div class="flex flex-col items-center justify-center py-12">
                    <div class="bg-indigo-50 p-6 rounded-full mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">No tasks pending</h3>
                    <p class="text-gray-500 mt-2 max-w-sm text-center">Great job! You've cleared the backlog. Or maybe it's time to add some work?</p>
                </div>
            </template>
        </BaseDataTable>
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import ProjectLayout from '@/Layouts/ProjectLayout.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import Combobox from '@/Components/Combobox.vue'; // Keep for other uses if needed or remove
import BaseSelect from '@/Components/BaseSelect.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import MultiUserSelect from '@/Components/MultiUserSelect.vue';

defineOptions({ layout: ProjectLayout });

const props = defineProps({
    project: Object,
    tasks: Object,
    filters: Object
});

const columns = [
    { key: 'title', label: 'Task Title', class: 'font-medium text-gray-900' },
    { key: 'status', label: 'Status' },
    { key: 'assignee', label: 'Assignee' },
    { key: 'priority', label: 'Priority' },
    { key: 'scrum_points', label: 'Pts', class: 'text-center w-16' },
    { key: 'due_date', label: 'Due Date' }
];

// Reactive Filter State
const filters = ref({
    status: props.filters?.status || [],
    priority: props.filters?.priority || [],
    assignees: props.filters?.assignees || [], // User IDs
    search: props.filters?.search || ''
});

// Dropdown Options
const statusOptions = computed(() => props.project?.stages || []);
const priorityOptions = computed(() => props.project?.priorities || []);
// Members from assignments (need to see how load works, likely assignments[].assignee)
const memberOptions = computed(() => {
    if (!props.project?.assignments) return [];
    // Extract assignees from assignments
    return props.project.assignments
        .filter(a => a.assignee)
        .map(a => a.assignee) // User objects
        .filter((v,i,a) => a.findIndex(t=>(t.id === v.id))===i); // Unique by ID
});

const applyFilters = () => {
    router.get(route('projects.tasks.index', props.project.id), filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const exportTasks = () => {
    // Navigate to same route but with export=1 and current filters
    const params = { ...filters.value, export: 1 };
    window.location.href = route('projects.tasks.index', { project: props.project.id, ...params });
};

// Date Formatter
const formatDate = (dateString) => {
    if (!dateString) return '--';
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric', month: 'short', day: 'numeric'
    });
};

const onPageChange = (page) => {
    router.visit(route('projects.tasks.index', { project: props.project.id, page, ...filters.value }), { preserveState: true });
};

// ... status/priority style helpers (kept same)
const getStatusStyle = (statusName, item) => {
    if (item?.stage?.color) {
        return { backgroundColor: item.stage.color + '20', color: item.stage.color };
    }
    if (props.project?.stages) {
        const stage = props.project.stages.find(s => s.name === statusName);
        if (stage) return { backgroundColor: stage.color + '20', color: stage.color };
    }
    return { backgroundColor: '#f3f4f6', color: '#4b5563' }; 
};

const getPriorityStyle = (priorityName, item) => {
    if (item?.priority_color) {
         return { backgroundColor: item.priority_color + '20', color: item.priority_color };     
    }
    if (props.project?.priorities) {
        const prio = props.project.priorities.find(p => p.name === priorityName);
        if (prio) return { backgroundColor: prio.color + '20', color: prio.color };
    }
    return { backgroundColor: '#f3f4f6', color: '#6b7280' }; 
};
</script>
