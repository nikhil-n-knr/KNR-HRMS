<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    pending_visits: Array
});

const form = useForm({
    action: ''
});

const submitAction = (id, action) => {
    form.action = action;
    form.post(route('host.approvals.action', id), {
        onSuccess: () => {
             // Toast or refresh
        }
    });
};
</script>

<template>
    <Head title="Visitor Approvals" />
    <MainLayout>
        <div class="max-w-4xl mx-auto py-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Visitor Requests</h1>
            <p class="text-gray-500 mb-8">Manage access for visitors waiting at the reception.</p>

            <div v-if="pending_visits.length === 0" class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-100">
                 <div class="bg-indigo-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                     <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                 </div>
                 <h3 class="text-lg font-medium text-gray-900">All Caught Up</h3>
                 <p class="text-gray-500">No visitors currently waiting for your approval.</p>
            </div>

            <div v-else class="space-y-4">
                <div v-for="visit in pending_visits" :key="visit.id" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col md:flex-row justify-between items-center gap-6 animate-fade-in-up">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-gray-200 flex-shrink-0 overflow-hidden">
                             <img v-if="visit.visitor.photo_path" :src="visit.visitor.photo_path" class="w-full h-full object-cover">
                             <div v-else class="w-full h-full flex items-center justify-center text-gray-400 font-bold text-xl">{{ visit.visitor.name.charAt(0) }}</div>
                        </div>
                        <div>
                            <h3 class="font-bold text-xl text-gray-900">{{ visit.visitor.name }}</h3>
                            <p class="text-sm text-gray-500">{{ visit.visitor.company }}</p>
                            <p class="text-xs font-mono mt-1 bg-gray-100 inline-block px-2 py-1 rounded">Wait Time: {{ new Date(visit.created_at).toLocaleTimeString() }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <button 
                            @click="submitAction(visit.id, 'reject')"
                            class="flex-1 md:flex-none px-4 py-2 border border-red-200 text-red-600 font-bold rounded-lg hover:bg-red-50 transition-colors"
                        >
                            Deny
                        </button>
                        <button 
                            @click="submitAction(visit.id, 'wait')"
                            class="flex-1 md:flex-none px-4 py-2 border border-yellow-200 text-yellow-700 font-bold rounded-lg hover:bg-yellow-50 transition-colors"
                        >
                            Wait 5m
                        </button>
                        <button 
                            @click="submitAction(visit.id, 'approve')"
                            class="flex-1 md:flex-none px-6 py-2 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 shadow-md shadow-indigo-200 transition-all transform hover:scale-105"
                        >
                            Allow Entry
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
