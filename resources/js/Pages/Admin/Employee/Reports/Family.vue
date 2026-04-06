<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    UsersIcon, 
    MagnifyingGlassIcon,
    FunnelIcon,
    PhoneIcon,
    HeartIcon
} from '@heroicons/vue/24/outline';
import Pagination from '@/Components/Pagination.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    families: Object,
    filters: Object,
    relationships: Array
});

const search = ref(props.filters.search || '');
const relationship = ref(props.filters.relationship || '');

watch([search, relationship], ([s, r]) => {
    router.get(route('admin.employees.reports.family'), { search: s, relationship: r }, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
});

const formatDate = (d) => d ? new Date(d).toLocaleDateString() : 'N/A';
</script>

<template>
    <div class="p-6 space-y-6">
        <Head title="Employee Family Report" />

        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Family & Dependents</h1>
                <p class="text-sm text-gray-500 font-medium">Global report of family members and emergency contacts.</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-wrap gap-4 items-center">
            <div class="flex-1 relative min-w-[300px]">
                <MagnifyingGlassIcon class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" />
                <input 
                    v-model="search"
                    type="text" 
                    placeholder="Search by employee or family member name..."
                    class="w-full pl-12 pr-4 py-2.5 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 transition"
                >
            </div>
            <div class="flex items-center gap-3">
                <FunnelIcon class="w-5 h-5 text-gray-400" />
                <select 
                    v-model="relationship"
                    class="bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm font-medium pr-10"
                >
                    <option value="">All Relationships</option>
                    <option v-for="rel in relationships" :key="rel" :value="rel">{{ rel }}</option>
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
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Family Member</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Relationship</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <tr v-for="member in families.data" :key="member.id" class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ member.employee.first_name }} {{ member.employee.last_name }}</div>
                                <div class="text-xs text-gray-500">{{ member.employee.employee_code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center font-bold text-xs">
                                        {{ member.name[0] }}
                                    </div>
                                    <div class="text-sm font-medium text-gray-800">{{ member.name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-600 capitalize">{{ member.relationship }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div v-if="member.phone" class="flex items-center gap-2 text-sm text-gray-600">
                                    <PhoneIcon class="w-4 h-4 text-gray-400" />
                                    {{ member.phone }}
                                </div>
                                <span v-else class="text-xs text-gray-400">No phone</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex gap-2">
                                    <span v-if="member.is_dependent" class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-sm font-black rounded uppercase">Dependent</span>
                                    <span v-if="member.is_emergency_contact" class="px-2 py-0.5 bg-red-100 text-red-700 text-sm font-black rounded uppercase flex items-center gap-1">
                                        <HeartIcon class="w-3 h-3" />
                                        Emergency
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden divide-y divide-gray-100">
                <div v-for="member in families.data" :key="member.id" class="p-4 space-y-4">
                    <div class="flex items-center justify-between">
                         <div>
                            <div class="text-sm font-bold text-gray-900">{{ member.employee.first_name }} {{ member.employee.last_name }}</div>
                            <div class="text-sm text-gray-500 font-bold uppercase tracking-widest">{{ member.employee.employee_code }}</div>
                        </div>
                        <div class="flex gap-1">
                            <span v-if="member.is_dependent" class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-black rounded uppercase">Dep</span>
                            <span v-if="member.is_emergency_contact" class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-black rounded uppercase flex items-center gap-1">
                                <HeartIcon class="w-3 h-3" />
                                Emg
                            </span>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="h-8 w-8 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ member.name[0] }}
                                </div>
                                <span class="text-xs font-bold text-gray-800">{{ member.name }}</span>
                            </div>
                            <span class="text-sm font-black text-gray-400 uppercase tracking-widest px-2 py-1 bg-white rounded border border-gray-100">
                                {{ member.relationship }}
                            </span>
                        </div>
                        
                        <div v-if="member.phone" class="flex items-center gap-2 text-xs font-bold text-gray-600 pl-1">
                            <PhoneIcon class="w-4 h-4 text-emerald-500" />
                            {{ member.phone }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="families.data.length === 0" class="py-20 text-center">
                <UsersIcon class="w-12 h-12 text-gray-200 mx-auto mb-4" />
                <h3 class="text-lg font-bold text-gray-900">No family records found</h3>
            </div>
        </div>

        <Pagination :links="families.links" />
    </div>
</template>
