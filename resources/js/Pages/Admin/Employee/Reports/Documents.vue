<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    DocumentIcon, 
    MagnifyingGlassIcon,
    ArrowDownTrayIcon,
    FunnelIcon,
    EyeIcon
} from '@heroicons/vue/24/outline';
import Pagination from '@/Components/Pagination.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    documents: Object,
    filters: Object,
    categories: Array
});

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');

watch([search, category], ([s, c]) => {
    router.get(route('admin.employees.reports.documents'), { search: s, category: c }, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
});

const formatDate = (d) => d ? new Date(d).toLocaleDateString() : 'N/A';
const getFileIcon = (type) => {
    if (type?.includes('pdf')) return 'text-red-500';
    if (type?.includes('image')) return 'text-emerald-500';
    return 'text-blue-500';
};
</script>

<template>
    <div class="p-6 space-y-6">
        <Head title="Employee Documents Report" />

        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Employee Documents</h1>
                <p class="text-sm text-gray-500 font-medium">Global repository of all uploaded employee records.</p>
            </div>
            <div class="flex gap-3">
                <button class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition flex items-center gap-2">
                    <ArrowDownTrayIcon class="w-4 h-4" />
                    Export CSV
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-wrap gap-4 items-center">
            <div class="flex-1 relative min-w-[300px]">
                <MagnifyingGlassIcon class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" />
                <input 
                    v-model="search"
                    type="text" 
                    placeholder="Search by employee name, code or document title..."
                    class="w-full pl-12 pr-4 py-2.5 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 transition"
                >
            </div>
            <div class="flex items-center gap-3">
                <FunnelIcon class="w-5 h-5 text-gray-400" />
                <select 
                    v-model="category"
                    class="bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm font-medium pr-10"
                >
                    <option value="">All Categories</option>
                    <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                </select>
            </div>
        </div>

        <!-- Table (Desktop) / Card View (Mobile) -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Document Title</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Upload Date</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <tr v-for="doc in documents.data" :key="doc.id" class="hover:bg-gray-50/50 transition group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                                        {{ doc.employee.first_name[0] }}{{ doc.employee.last_name[0] }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">{{ doc.employee.first_name }} {{ doc.employee.last_name }}</div>
                                        <div class="text-xs text-gray-500">{{ doc.employee.employee_code }} | {{ doc.employee.department?.name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <DocumentIcon class="w-5 h-5" :class="getFileIcon(doc.file_type)" />
                                    <span class="text-sm font-medium text-gray-700">{{ doc.title }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg text-sm font-black uppercase tracking-wider bg-gray-100 text-gray-600 border border-gray-200">
                                    {{ doc.category }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(doc.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition">
                                    <a 
                                        :href="route('documents.stream', { id: doc.id })" 
                                        target="_blank"
                                        class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                                    >
                                        <EyeIcon class="w-5 h-5" />
                                    </a>
                                    <button class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition">
                                        <ArrowDownTrayIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden divide-y divide-gray-100">
                <div v-for="doc in documents.data" :key="doc.id" class="p-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                                {{ doc.employee.first_name[0] }}{{ doc.employee.last_name[0] }}
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">{{ doc.employee.first_name }} {{ doc.employee.last_name }}</div>
                                <div class="text-sm text-gray-500 font-bold uppercase tracking-widest">{{ doc.employee.employee_code }}</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-lg text-sm font-black uppercase tracking-wider bg-gray-100 text-gray-600 border border-gray-200">
                            {{ doc.category }}
                        </span>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <DocumentIcon class="w-5 h-5 shrink-0" :class="getFileIcon(doc.file_type)" />
                            <span class="text-xs font-bold text-gray-700 truncate">{{ doc.title }}</span>
                        </div>
                        <div class="flex gap-1 ml-2">
                             <a 
                                :href="route('documents.stream', { id: doc.id })" 
                                target="_blank"
                                class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                            >
                                <EyeIcon class="w-4 h-4" />
                            </a>
                            <button class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition">
                                <ArrowDownTrayIcon class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-sm font-bold text-gray-400 uppercase tracking-widest">
                        <span>Uploaded On</span>
                        <span>{{ formatDate(doc.created_at) }}</span>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="documents.data.length === 0" class="py-20 text-center">
                <DocumentIcon class="w-12 h-12 text-gray-200 mx-auto mb-4" />
                <h3 class="text-lg font-bold text-gray-900">No documents found</h3>
                <p class="text-sm text-gray-500">Try adjusting your search or filters.</p>
            </div>
        </div>

        <Pagination :links="documents.links" />
    </div>
</template>
