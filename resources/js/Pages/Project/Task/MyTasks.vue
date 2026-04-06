<template>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
             <h2 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-700 to-indigo-500">
                My Tasks
            </h2>
        </div>

        <!-- Filters Bar -->
        <div class="p-4 bg-white/50 backdrop-blur-sm rounded-xl border border-white/20 shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-4 relative z-30">
             <!-- Status Filter -->
            <MultiUserSelect
                v-model="filters.status"
                :items="statusOptions"
                labelKey="name"
                valueKey="name"
                placeholder="Filter Status"
                class="w-full"
            />
            
            <!-- Priority Filter -->
            <MultiUserSelect
                v-model="filters.priority"
                :items="priorityOptions"
                labelKey="name"
                valueKey="name"
                placeholder="Filter Priority"
                class="w-full"
            />

            <div class="flex items-end">
                <SecondaryButton @click="applyFilters" class="w-full justify-center h-[42px]">Apply Filters</SecondaryButton>
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
            <template #cell-project="{ value }">
                <span class="font-medium text-gray-700">{{ value?.name || '--' }}</span>
            </template>

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
                             {{ assignee.name ? assignee.name[0] : '?' }}
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">You're all caught up!</h3>
                    <p class="text-gray-500 mt-2 max-w-sm text-center">No tasks assigned to you across any active projects.</p>
                </div>
            </template>
        </BaseDataTable>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import MultiUserSelect from '@/Components/MultiUserSelect.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    tasks: Object,
    filters: Object
});

const filters = ref({
    status: props.filters?.status || [],
    priority: props.filters?.priority || [],
    search: props.filters?.search || ''
});

// Hardcoded Options for Global View
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

const columns = [
    { key: 'project', label: 'Project', class: 'font-medium text-gray-600' },
    { key: 'title', label: 'Task Title', class: 'font-medium text-gray-900' },
    { key: 'status', label: 'Status' },
    { key: 'assignee', label: 'Assignee' },
    { key: 'priority', label: 'Priority' },
    { key: 'scrum_points', label: 'Pts', class: 'text-center w-16' },
    { key: 'due_date', label: 'Due Date' }
];

const applyFilters = () => {
    router.get(route('projects.my-tasks'), filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const onPageChange = (page) => {
    router.visit(route('projects.my-tasks', { page, ...filters.value }), { preserveState: true });
};

// Date Formatter
const formatDate = (dateString) => {
    if (!dateString) return '--';
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric', month: 'short', day: 'numeric'
    });
};

// Helper to get color styles
const getStatusStyle = (statusName, item) => {
    // 1. Use embedded stage info from MyTasks controller
    if (item?.stage?.color) {
        return { backgroundColor: item.stage.color + '20', color: item.stage.color };
    }
    return { backgroundColor: '#f3f4f6', color: '#4b5563' }; 
};

// ... priorities kept simple for now
const getPriorityStyle = (priorityName, item) => {
    const map = {
        'Critical': '#ef4444', 
        'High': '#f97316', 
        'Medium': '#f59e0b', 
        'Low': '#10b981'
    };
    
    const color = map[priorityName] || '#6b7280';
    return { backgroundColor: color + '20', color: color }; 
};
</script>
