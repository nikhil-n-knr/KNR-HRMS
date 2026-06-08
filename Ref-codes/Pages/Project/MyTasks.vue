<template>
    <div class="min-h-screen bg-slate-50 py-6">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Page Header -->
            <section class="bg-white border border-slate-200/70 shadow-sm rounded-3xl overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-8 text-white">
                    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                        <div class="space-y-3">
                            <p class="text-sm uppercase tracking-[0.35em] text-indigo-100/80">My Tasks</p>
                            <h1 class="text-3xl lg:text-4xl font-black tracking-tight">Your Assignment Hub</h1>
                            <p class="max-w-2xl text-sm text-indigo-100/85 leading-6">Track all your assigned tasks across projects with real-time status updates and deadline visibility.</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3 w-full lg:w-auto">
                            <div class="rounded-3xl bg-white/10 border border-white/20 p-4">
                                <p class="text-xs uppercase tracking-[0.3em] text-indigo-100/80">Total Tasks</p>
                                <p class="mt-2 text-3xl font-black">{{ tasks.length || 0 }}</p>
                            </div>
                            <div class="rounded-3xl bg-white/10 border border-white/20 p-4">
                                <p class="text-xs uppercase tracking-[0.3em] text-indigo-100/80">In Progress</p>
                                <p class="mt-2 text-3xl font-black">{{ inProgressCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Stats Cards -->
            <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Todo</p>
                            <p class="mt-3 text-3xl font-black text-slate-900">{{ todoCount }}</p>
                        </div>
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-600">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8" /><path d="M21 21l-4.35-4.35" /></svg>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Not yet started.</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm uppercase tracking-[0.3em] text-slate-400">In Progress</p>
                            <p class="mt-3 text-3xl font-black text-slate-900">{{ inProgressCount }}</p>
                        </div>
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1" /><path d="M12 8v-4" /><path d="M12 20v-4" /><path d="M8 12h-4" /><path d="M20 12h-4" /></svg>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Currently being worked on.</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Completed</p>
                            <p class="mt-3 text-3xl font-black text-slate-900">{{ doneCount }}</p>
                        </div>
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7" /></svg>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Successfully finished.</p>
                </div>
            </section>

            <!-- Tasks List -->
            <section class="space-y-6">
                <div class="grid gap-4 lg:grid-cols-[1fr_280px] items-center">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-500">Tasks</p>
                        <h2 class="text-2xl font-black text-slate-900">Your assignments</h2>
                    </div>
                    <div class="flex justify-start lg:justify-end">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-medium text-slate-600 border border-slate-200 shadow-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span>
                            {{ tasks.length }} tasks total
                        </span>
                    </div>
                </div>

                <div v-if="tasks.length > 0" class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="border-b border-slate-200 bg-slate-50/50">
                            <tr>
                                <th class="px-6 py-4 text-xs uppercase tracking-[0.35em] font-semibold text-slate-600">Task</th>
                                <th class="px-6 py-4 text-xs uppercase tracking-[0.35em] font-semibold text-slate-600">Project</th>
                                <th class="px-6 py-4 text-xs uppercase tracking-[0.35em] font-semibold text-slate-600">Status</th>
                                <th class="px-6 py-4 text-xs uppercase tracking-[0.35em] font-semibold text-slate-600">Due Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="task in tasks" :key="task.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">{{ task.title }}</p>
                                    <p class="text-xs text-slate-500 mt-1">{{ task.description || 'No description' }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ task.project?.name || '-' }}</td>
                                <td class="px-6 py-4">
                                    <span :class="getStatusBadge(task.status)" class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.25em]">
                                        {{ formatStatus(task.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ task.deadline ? formatDate(task.deadline) : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="rounded-3xl border border-dashed border-slate-200 bg-white p-10 text-center text-slate-500 shadow-sm">
                    <div class="mx-auto mb-5 inline-flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16" /><path d="M4 12h16" /><path d="M4 18h16" /></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900">No tasks assigned</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">You're all caught up! Check back later for new assignments.</p>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
defineOptions({ layout: MainLayout });

const props = defineProps(['tasks']);

const todoCount = computed(() => props.tasks?.filter(t => t.status === 'todo').length || 0);
const inProgressCount = computed(() => props.tasks?.filter(t => t.status === 'in_progress').length || 0);
const doneCount = computed(() => props.tasks?.filter(t => t.status === 'done').length || 0);

const formatStatus = (status) => {
    if (!status) return 'Unknown';
    return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'todo': return 'bg-slate-100 text-slate-700 border border-slate-200';
        case 'in_progress': return 'bg-blue-100 text-blue-700 border border-blue-200';
        case 'done': return 'bg-emerald-100 text-emerald-700 border border-emerald-200';
        default: return 'bg-slate-100 text-slate-700 border border-slate-200';
    }
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>
