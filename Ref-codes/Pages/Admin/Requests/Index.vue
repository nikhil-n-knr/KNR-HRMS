<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    requests: Object
});

const getStatusColor = (status) => {
    switch(status) {
        case 'approved': return 'bg-emerald-100 text-emerald-700';
        case 'rejected': return 'bg-red-100 text-red-700';
        case 'pending': return 'bg-amber-100 text-amber-700';
        case 'cancelled': return 'bg-gray-100 text-gray-500';
        default: return 'bg-gray-50 text-gray-500';
    }
};

const getTypeColor = (type) => {
     switch(type) {
        case 'Leave': return 'text-purple-600 bg-purple-50 border-purple-100';
        case 'WFH': return 'text-emerald-600 bg-emerald-50 border-emerald-100';
        case 'Overtime': return 'text-amber-600 bg-amber-50 border-amber-100';
        case 'Swap': return 'text-blue-600 bg-blue-50 border-blue-100';
        default: return 'text-gray-600';
    }
};
</script>

<template>
    <MainLayout>
        <Head title="Request Hub" />
        <div class="p-8 bg-gray-50/50 min-h-screen">
             <div class="flex justify-between items-center mb-6">
                 <div>
                     <h1 class="text-2xl font-black text-gray-900">Request Hub</h1>
                     <p class="text-sm text-gray-500">Centralized audit log of all employee requests.</p>
                 </div>
                 <a :href="route('admin.requests.export')" class="px-4 py-2 border bg-white rounded-lg shadow-sm hover:bg-gray-50 font-bold text-gray-700 flex items-center gap-2">
                     Updated Export
                 </a>
             </div>

             <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                 <table class="w-full text-left">
                     <thead class="bg-gray-50 border-b border-gray-100">
                         <tr>
                             <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Request Type</th>
                             <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Employee</th>
                             <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Dates</th>
                             <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Status</th>
                             <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Reason</th>
                             <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Submitted</th>
                         </tr>
                     </thead>
                     <tbody class="divide-y divide-gray-100">
                         <tr v-for="req in requests.data" :key="req.uuid" class="hover:bg-gray-50 transition-colors">
                             <td class="px-6 py-4">
                                 <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg border text-xs font-bold" :class="getTypeColor(req.type)">
                                     {{ req.type }}
                                     <span v-if="req.sub_type" class="text-sm opacity-75 border-l border-current pl-2 ml-1">{{ req.sub_type }}</span>
                                 </div>
                             </td>
                             <td class="px-6 py-4">
                                 <div class="font-bold text-gray-900">{{ req.employee.name }}</div>
                                 <div class="text-xs text-gray-400">{{ req.employee.code }}</div>
                             </td>
                             <td class="px-6 py-4 text-sm">
                                 <div v-if="req.start_date === req.end_date">{{ req.start_date }}</div>
                                 <div v-else class="flex flex-col">
                                     <span>{{ req.start_date }}</span>
                                     <span class="text-xs text-gray-400">to {{ req.end_date }}</span>
                                 </div>
                                 <div v-if="req.metadata" class="text-sm text-gray-500 mt-1 font-mono bg-gray-100 inline-block px-1 rounded">{{ req.metadata }}</div>
                             </td>
                             <td class="px-6 py-4">
                                 <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase" :class="getStatusColor(req.status)">
                                     {{ req.status }}
                                 </span>
                             </td>
                             <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" :title="req.reason">
                                 {{ req.reason || '-' }}
                             </td>
                             <td class="px-6 py-4 text-xs text-gray-500">
                                 {{ new Date(req.created_at).toLocaleString() }}
                             </td>
                         </tr>
                     </tbody>
                 </table>
                 
                 <!-- Pagination -->
                 <div class="p-4 border-t border-gray-100 flex justify-center" v-if="requests.links">
                      <component 
                        :is="link.url ? Link : 'span'" 
                        v-for="(link, i) in requests.links" 
                        :key="i"
                        :href="link.url"
                        v-html="link.label"
                        class="px-3 py-1 rounded-lg text-sm font-bold mx-1"
                        :class="link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                    />
                 </div>
             </div>
        </div>
    </MainLayout>
</template>
