<template>
    <Head title="Pending Approvals" />
    <MainLayout>
        <div class="h-full bg-gray-50 p-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Pending Approvals</h1>
                        <p class="mt-2 text-sm text-gray-600">Review and verify bug fixes before they are marked as resolved.</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-sm font-bold text-gray-400 uppercase tracking-widest">Ticket</th>
                                <th class="px-6 py-4 text-sm font-bold text-gray-400 uppercase tracking-widest">Project</th>
                                <th class="px-6 py-4 text-sm font-bold text-gray-400 uppercase tracking-widest">Reporter</th>
                                <th class="px-6 py-4 text-sm font-bold text-gray-400 uppercase tracking-widest">Current Stage</th>
                                <th class="px-6 py-4 text-sm font-bold text-gray-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 text-sm">#{{ ticket.id }} {{ ticket.subject }}</div>
                                    <div class="text-sm text-gray-400 font-medium uppercase mt-0.5 tracking-tighter">{{ ticket.module?.name || 'General' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-medium text-gray-600">{{ ticket.project.name }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-sm font-bold text-gray-500">
                                            {{ ticket.reporter?.name?.charAt(0) || 'U' }}
                                        </div>
                                        <span class="text-sm text-gray-600">{{ ticket.reporter?.name || 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-lg bg-amber-50 text-amber-600 text-sm font-bold border border-amber-100">{{ ticket.stage.name }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl text-xs font-bold hover:bg-indigo-100 transition-colors">Verify Fix</button>
                                </td>
                            </tr>
                            <tr v-if="tickets.data.length === 0">
                                <td colspan="5" class="px-6 py-20 text-center text-gray-400 font-medium italic">
                                    No tickets currently awaiting management approval.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

defineProps({
    tickets: Object,
    projects: Array,
    filters: Object
});
</script>
