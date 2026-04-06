<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { DocumentTextIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: MainLayout });

const props = defineProps({
    sessions: Object
});
</script>

<template>
    <Head title="Audit History" />

    <div class="p-8 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold flex items-center gap-2">
                <CheckBadgeIcon class="h-8 w-8 text-emerald-600" />
                Audit History
            </h1>
            <Link :href="route('admin.assets.audit.run')" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded hover:bg-gray-200">
                &larr; Back to Scanner
            </Link>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase">
                    <tr>
                        <th class="px-6 py-3">Audit ID</th>
                        <th class="px-6 py-3">Location</th>
                        <th class="px-6 py-3">Auditor</th>
                        <th class="px-6 py-3">Accuracy</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3 text-right">Report</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="session in sessions.data" :key="session.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-mono font-bold">#{{ session.id }}</td>
                        <td class="px-6 py-4">{{ session.location?.name }}</td>
                        <td class="px-6 py-4">{{ session.auditor?.name }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 rounded text-xs font-bold" 
                                :class="session.stats.accuracy >= 100 ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'">
                                {{ session.stats.accuracy }}%
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ new Date(session.created_at).toLocaleDateString() }}</td>
                        <td class="px-6 py-4 text-right">
                            <a :href="route('admin.assets.audit.report', session.id)" target="_blank" class="text-blue-600 hover:text-blue-800 font-bold flex items-center justify-end gap-1">
                                <DocumentTextIcon class="h-4 w-4" />
                                PDF
                            </a>
                        </td>
                    </tr>
                    <tr v-if="sessions.data.length === 0">
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            No audit history found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
