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
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                        My Tasks
                    </h1>
                    <p class="mt-2 text-sm text-white/60 max-w-md leading-relaxed">
                        All tasks assigned to you across active projects, filtered by status and priority.
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
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-5
                        grid grid-cols-1 sm:grid-cols-3 gap-4 relative z-30">

                <!-- Status Filter -->
                <div class="space-y-1.5">
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
                <div class="space-y-1.5">
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

                <!-- Apply -->
                <div class="flex items-end">
                    <button
                        @click="applyFilters"
                        class="w-full h-[42px] inline-flex items-center justify-center gap-2
                               px-5 rounded-xl bg-indigo-600 text-white text-sm font-bold
                               hover:bg-indigo-700 active:scale-[0.98] transition-all shadow-sm shadow-indigo-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                        Apply Filters
                    </button>
                </div>
            </div>

            <!-- Data Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <BaseDataTable
                    :data="tasks.data"
                    :columns="columns"
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
                    <template #cell-status="{ value, item }">
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border"
                            :style="getStatusStyle(value, item)"
                        >
                            <span class="w-1.5 h-1.5 rounded-full" :style="{ background: item?.stage?.color || '#6b7280' }"></span>
                            {{ value }}
                        </span>
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

                    <!-- Due Date -->
                    <template #cell-due_date="{ value }">
                        <span class="text-xs text-slate-500 font-medium">{{ formatDate(value) }}</span>
                    </template>

                    <!-- Empty State -->
                    <template #empty>
                        <div class="flex flex-col items-center justify-center py-20">
                            <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-extrabold text-slate-900">You're all caught up!</h3>
                            <p class="text-sm text-slate-400 mt-1 max-w-sm text-center">No tasks assigned to you across any active projects.</p>
                        </div>
                    </template>
                </BaseDataTable>
            </div>

        </div><!-- /inner body -->
    </div><!-- /outer shell -->
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
    tasks:   Object,
    filters: Object
});

const filters = ref({
    status:   props.filters?.status   || [],
    priority: props.filters?.priority || [],
    search:   props.filters?.search   || ''
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

const columns = [
    { key: 'project',      label: 'Project',    class: 'font-medium text-slate-600' },
    { key: 'title',        label: 'Task Title', class: 'font-semibold text-slate-900' },
    { key: 'status',       label: 'Status' },
    { key: 'assignee',     label: 'Assignee' },
    { key: 'priority',     label: 'Priority' },
    { key: 'scrum_points', label: 'Pts',        class: 'text-center w-16' },
    { key: 'due_date',     label: 'Due Date' }
];

const applyFilters = () => {
    router.get(route('projects.my-tasks'), filters.value, {
        preserveState:  true,
        preserveScroll: true
    });
};

const onPageChange = (page) => {
    router.visit(route('projects.my-tasks', { page, ...filters.value }), { preserveState: true });
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