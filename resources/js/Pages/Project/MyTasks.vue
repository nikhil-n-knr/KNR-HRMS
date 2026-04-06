<template>
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-800">My Tasks</h1>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
             <table class="w-full text-left">
                <thead class="bg-gray-50/50 text-gray-500 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-4">Task</th>
                        <th class="px-6 py-4">Project</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Due</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="task in tasks" :key="task.id" class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-800">{{ task.title }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ task.project?.name }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-bold rounded-full uppercase"
                                :class="{
                                    'bg-gray-100 text-gray-600': task.status === 'todo',
                                    'bg-blue-100 text-blue-600': task.status === 'in_progress',
                                    'bg-emerald-100 text-emerald-600': task.status === 'done'
                                }"
                            >
                                {{ task.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ task.deadline || '-' }}</td>
                    </tr>
                    <tr v-if="tasks.length === 0">
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                            You have no assigned tasks.
                        </td>
                    </tr>
                </tbody>
             </table>
        </div>
    </div>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
defineOptions({ layout: MainLayout });
defineProps(['tasks']);
</script>
