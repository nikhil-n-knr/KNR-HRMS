<template>
    <Head title="My Referrals" />

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between mb-6">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Referral Program
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Refer talent to open positions and track their progress.
                    </p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <button @click="showModal = true" type="button" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Refer Candidate
                    </button>
                </div>
            </div>

            <!-- Referral List -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul v-if="referrals.data.length > 0" role="list" class="divide-y divide-gray-200">
                    <li v-for="referral in referrals.data" :key="referral.id">
                        <div class="px-4 py-4 sm:px-6 hover:bg-gray-50 transition duration-150 ease-in-out">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                            {{ getInitials(referral.candidate.first_name, referral.candidate.last_name) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-indigo-600 truncate">
                                            {{ referral.candidate.first_name }} {{ referral.candidate.last_name }}
                                        </div>
                                        <div class="flex items-center text-sm text-gray-500 mt-1">
                                            <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            {{ referral.job.title }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="{
                                            'bg-green-100 text-green-800': referral.status === 'Hired' || referral.status === 'Offer',
                                            'bg-yellow-100 text-yellow-800': referral.status === 'Interview',
                                            'bg-blue-100 text-blue-800': referral.status === 'Screening',
                                            'bg-gray-100 text-gray-800': referral.status === 'Applied',
                                            'bg-red-100 text-red-800': referral.status === 'Rejected'
                                        }">
                                        {{ referral.status }}
                                    </span>
                                    <div class="mt-2 text-xs text-gray-500">
                                        Referred on {{ new Date(referral.updated_at).toLocaleDateString() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
                <div v-else class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No referrals yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Start by referring known candidates for open roles.</p>
                </div>
            </div>

             <!-- Search Modal -->
            <Modal :show="showModal" title="Refer Existing Application" @close="showModal = false" maxWidth="lg">
                <div class="mt-2">
                    <p class="text-sm text-gray-500 mb-4">
                        Search for candidates who have already applied to link yourself as the referrer.
                    </p>
                    
                    <input type="text" v-model="searchQuery" @input="debouncedSearch" 
                        class="shadow-sm focus:ring-emerald-500 focus:border-emerald-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                        placeholder="Search by name or email (min 3 chars)...">

                    <div v-if="loading" class="mt-4 text-center text-sm text-gray-500">Searching...</div>
                    
                    <ul v-else-if="results.length > 0" class="mt-4 divide-y divide-gray-200 border rounded-md border-gray-200 max-h-60 overflow-y-auto custom-scrollbar">
                        <li v-for="res in results" :key="res.id" class="p-3 hover:bg-gray-50 flex justify-between items-center transition-colors">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ res.candidate_name }}</p>
                                <p class="text-xs text-gray-500">{{ res.job_title }} • {{ res.candidate_email }}</p>
                            </div>
                            <button @click="confirmReferral(res)" class="text-xs bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full hover:bg-emerald-200 font-medium transition-colors">
                                Link Me
                            </button>
                        </li>
                    </ul>
                    <div v-else-if="hasSearched && !loading" class="mt-4 text-center text-sm text-gray-500 italic">
                        No unreferred candidates found matching "{{ searchQuery }}".
                    </div>
                </div>

                <template #footer>
                    <button @click="showModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </template>
            </Modal>

        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import axios from 'axios';
import { debounce } from 'lodash';

defineOptions({ layout: MainLayout });

const props = defineProps({
    referrals: Object
});

const showModal = ref(false);
const searchQuery = ref('');
const results = ref([]);
const loading = ref(false);
const hasSearched = ref(false);

const getInitials = (first, last) => {
    return (first[0] + (last ? last[0] : '')).toUpperCase();
};

const performSearch = async () => {
    if (searchQuery.value.length < 3) return;
    loading.value = true;
    try {
        const response = await axios.get(route('employee.referrals.search'), { params: { query: searchQuery.value } });
        results.value = response.data;
        hasSearched.value = true;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const debouncedSearch = debounce(performSearch, 500);

const confirmReferral = (application) => {
    if (confirm(`Confirm you verified ${application.candidate_name} for this role?`)) {
        router.post(route('employee.referrals.store'), { application_id: application.id }, {
            onSuccess: () => {
                showModal.value = false;
                searchQuery.value = '';
                results.value = [];
            }
        });
    }
};
</script>
