<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    ClockIcon, 
    ExclamationTriangleIcon,
    ShieldCheckIcon,
    UserIcon
} from '@heroicons/vue/24/outline'; // v2
import { computed } from 'vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    custody_items: Array
});

// Computed Groups
const highRiskItems = computed(() => props.custody_items.filter(i => i.is_high_risk));
const normalItems = computed(() => props.custody_items.filter(i => !i.is_high_risk));

const formatDuration = (hours) => {
    if (hours < 1) return '< 1 Hour';
    if (hours < 24) return `${hours} Hours`;
    const days = Math.floor(hours / 24);
    return `${days} Days ${hours % 24} Hours`;
};
</script>

<template>
    <Head title="Chain of Custody" />

    <div class="p-8 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <ShieldCheckIcon class="h-8 w-8 text-emerald-600" />
                Chain of Custody
            </h1>
            <p class="text-sm text-gray-500 mt-1">Real-time tracking of physical documents "In the Wild".</p>
        </div>

        <!-- HIGH RISK SECTION -->
        <div v-if="highRiskItems.length > 0" class="mb-10 animate-pulse-slow">
            <div class="flex items-center gap-2 mb-4 text-red-600">
                <ExclamationTriangleIcon class="h-6 w-6" />
                <h2 class="text-lg font-bold uppercase tracking-wider">High Risk Alerts (> 24 Hours)</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="item in highRiskItems" :key="item.id" class="bg-red-50 border border-red-200 rounded-xl p-5 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-2 opacity-10">
                        <ClockIcon class="h-24 w-24 text-red-900" />
                    </div>
                    
                    <div class="relative z-10">
                        <span class="inline-block px-2 py-1 bg-red-200 text-red-800 text-xs font-bold rounded mb-2">
                            OUT {{ formatDuration(item.duration_hours) }}
                        </span>
                        <h3 class="font-bold text-gray-900 text-lg mb-1">{{ item.document_type }}</h3>
                        
                        <div class="flex items-center gap-2 text-sm text-gray-600 mb-4">
                            <UserIcon class="h-4 w-4" />
                            <span class="font-semibold">{{ item.user_name }}</span>
                        </div>

                        <div class="text-xs text-gray-500 mb-4">
                            Origin: {{ item.location_name }} ({{ item.container_ref }})
                        </div>

                        <Link :href="route('admin.physical-documents.index')" class="block w-full text-center py-2 bg-white border border-red-300 text-red-700 font-bold rounded-lg hover:bg-red-100 text-sm">
                            Investigate / Return
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- NORMAL CUSTODY SECTION -->
         <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="font-bold text-gray-700">Active Custody Log</h3>
                <span class="text-xs font-bold text-gray-400 uppercase">{{ normalItems.length }} Items Checked Out</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-white text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Document</th>
                            <th class="px-6 py-3">Custrodian</th>
                            <th class="px-6 py-3">Time Out</th>
                            <th class="px-6 py-3">Home Location</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in normalItems" :key="item.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ item.document_type }}</td>
                            <td class="px-6 py-4 flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-xs text-indigo-700 font-bold">
                                    {{ item.user_name.charAt(0) }}
                                </div>
                                {{ item.user_name }}
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ formatDuration(item.duration_hours) }}
                            </td>
                             <td class="px-6 py-4 text-gray-500">
                                {{ item.location_name }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="route('admin.physical-documents.index')" class="text-emerald-600 hover:text-emerald-800 font-bold text-xs">Return</Link>
                            </td>
                        </tr>
                        <tr v-if="normalItems.length === 0 && highRiskItems.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                <ShieldCheckIcon class="h-12 w-12 mx-auto text-emerald-100 mb-2" />
                                All documents are secure in storage. No active checkouts.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>

<style scoped>
@keyframes pulse-slow {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.95; }
}
.animate-pulse-slow {
    animation: pulse-slow 3s infinite;
}
</style>
