<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    ClockIcon, 
    MagnifyingGlassIcon,
    UserCircleIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline';
import Pagination from '@/Components/Pagination.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    history: Object,
    filters: Object
});

const search = ref(props.filters.search || '');

watch(search, (s) => {
    router.get(route('admin.employees.reports.history'), { search: s }, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
});

const formatDate = (d) => d ? new Date(d).toLocaleString() : 'N/A';
</script>

<template>
    <div class="p-6 space-y-6">
        <Head title="Employee history" />

        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Audit History</h1>
                <p class="text-sm text-gray-500 font-medium">Timeline of all changes to employee profiles.</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-wrap gap-4 items-center">
            <div class="flex-1 relative min-w-[300px]">
                <MagnifyingGlassIcon class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" />
                <input 
                    v-model="search"
                    type="text" 
                    placeholder="Search history descriptions..."
                    class="w-full pl-12 pr-4 py-2.5 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 transition"
                >
            </div>
        </div>

        <!-- Table (Desktop) / Card View (Mobile) -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Timestamp</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Action / Description</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Done By</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <tr v-for="log in history.data" :key="log.id" class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(log.created_at) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-2">
                                    <ArrowPathIcon class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" />
                                    <span class="text-sm font-medium text-gray-800">{{ log.description }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <UserCircleIcon class="w-5 h-5 text-gray-400" />
                                    <span class="text-sm font-medium text-gray-700">{{ log.causer_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-sm font-black rounded uppercase tracking-widest border border-blue-100">
                                    Logged
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden divide-y divide-gray-100">
                <div v-for="log in history.data" :key="log.id" class="p-4 space-y-3">
                    <div class="flex justify-between items-center text-sm font-black text-gray-400 uppercase tracking-widest">
                        <span>{{ formatDate(log.created_at) }}</span>
                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded border border-blue-100 italic">Audit Signal</span>
                    </div>

                    <div class="flex items-start gap-3 bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <ArrowPathIcon class="w-5 h-5 text-indigo-500 shrink-0 mt-0.5" />
                        <span class="text-xs font-bold text-gray-700 leading-relaxed">{{ log.description }}</span>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center gap-2">
                            <UserCircleIcon class="w-5 h-5 text-gray-400" />
                            <span class="text-sm font-black text-gray-500 uppercase tracking-widest">{{ log.causer_name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="history.data.length === 0" class="py-20 text-center">
                <ClockIcon class="w-12 h-12 text-gray-200 mx-auto mb-4" />
                <h3 class="text-lg font-bold text-gray-900">No history found</h3>
            </div>
        </div>

        <Pagination :links="history.links" />
    </div>
</template>
