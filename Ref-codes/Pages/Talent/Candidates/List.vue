<template>
    <div class="h-full flex flex-col">
        <BaseDataTable
            :columns="columns"
            :data="candidates.data || candidates"
            :meta="candidates"
            :loading="loading"
            :selectable="true"
            :model-value="selectedIds"
            @update:model-value="emit('update:selectedIds', $event)"
            @search="handleSearch"
            @page-change="handlePageChange"
        >
            <template #actions>
               <button class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md font-medium text-sm hover:bg-gray-50 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export CSV
                </button>
            </template>

            <!-- Custom Cell: Name -->
            <template #cell-name="{ item }">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
                        {{ item.name.charAt(0) }}
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
                        <div class="text-sm text-gray-500">{{ item.email }}</div>
                    </div>
                </div>
            </template>

            <!-- Custom Cell: Score -->
            <template #cell-score="{ value }">
                 <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                      :class="getScoreColor(value)">
                    {{ value ? value + '%' : 'N/A' }}
                </span>
            </template>

            <!-- Custom Cell: Latest Status -->
            <template #cell-last_interview_status="{ item, value }">
                 <span v-if="item.status === 'Interview'" class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full capitalize" 
                      :class="{
                          'bg-green-100 text-green-800': value === 'Completed',
                          'bg-yellow-100 text-yellow-800': value === 'Scheduled',
                          'bg-orange-100 text-orange-800': value === 'None',
                          'bg-gray-100 text-gray-800': !['Completed', 'Scheduled', 'None'].includes(value)
                      }">
                    {{ value === 'None' ? 'Pending Action' : value }}
                </span>
                <span v-else-if="item.status === 'Screening'" class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
                      :class="item.screening_rating > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'">
                    {{ item.screening_rating > 0 ? 'Assessed' : 'Pending' }}
                </span>
                <span v-else class="text-xs text-gray-400">-</span>
            </template>

            <!-- Custom Cell: Interview Rating -->
             <template #cell-interview_rating="{ item }">
                <div v-if="item.interview_rating" class="flex flex-col">
                    <div class="flex text-yellow-400 text-xs">
                         <span v-for="i in 5" :key="i" :class="i <= item.interview_rating ? 'text-yellow-400' : 'text-gray-300'">★</span>
                    </div>
                    <span class="text-sm font-medium" :class="{
                           'text-green-600': ['Strong Hire', 'Hire'].includes(item.interview_recommendation),
                           'text-red-500': ['No Hire', 'Strong No'].includes(item.interview_recommendation)
                       }">{{ item.interview_recommendation }}</span>
                </div>
                <span v-else class="text-xs text-gray-400">-</span>
            </template>

            <!-- Custom Cell: Status (Dropdown) -->
            <template #cell-status="{ item, value }">
                 <select :value="value" 
                         @change="updateStatus(item, $event.target.value)"
                         class="text-xs border-gray-200 rounded-md focus:ring-indigo-500 focus:border-indigo-500 py-1 pl-2 pr-6">
                    <option>Applied</option>
                    <option>Screening</option>
                    <option>Interview</option>
                    <option>Offer</option>
                    <option>Hired</option>
                    <option>Rejected</option>
                </select>
            </template>
            
            <!-- Row Actions -->
            <template #rowActions="{ item }">
                <div class="flex items-center space-x-2">
                    <button v-if="['Strong Hire', 'Hire'].includes(item.interview_recommendation) && item.status === 'Interview'"
                             @click="emit('open-quick-view', item, 'Interviews', false)" 
                             class="text-xs bg-green-50 text-green-700 px-2 py-1 rounded border border-green-200 hover:bg-green-100 font-medium whitespace-nowrap">
                        Schedule Next
                    </button>
                    <button v-if="item.pending_feedback" 
                            @click="emit('open-quick-view', item, 'Interviews', true)" 
                            class="text-xs bg-indigo-50 text-indigo-700 px-2 py-1 rounded border border-indigo-200 hover:bg-indigo-100 font-medium">
                        Rate Now
                    </button>
                    <button @click="emit('open-quick-view', item)" class="text-indigo-600 hover:text-indigo-900 font-medium">View</button>
                </div>
            </template>
        </BaseDataTable>
        
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import BaseDataTable from '@/Components/BaseDataTable.vue';
// import CandidateQuickView from './CandidateQuickView.vue'; 

const props = defineProps({
    candidates: [Array, Object],
    jobs: Array,
    users: Array,
    filters: Object,
    selectedIds: { type: Array, default: () => [] } // Added
});

const emit = defineEmits(['trigger-workflow', 'open-rejection', 'open-offer', 'open-quick-view', 'update:selectedIds']); // Added emit

const loading = ref(false);

const columns = [
    { key: 'name', label: 'Candidate' },
    { key: 'job_title', label: 'Job Role' },
    { key: 'score', label: 'Match Score' },
    { key: 'last_interview_status', label: 'Latest Status' },
    { key: 'interview_rating', label: 'Interview Rating' },
    { key: 'status', label: 'Stage' },
    { key: 'created_at', label: 'Applied' },
];

// Quick View State
const showQuickView = ref(false);
const selectedCandidate = ref(null);

const handleSearch = (query) => {
    router.get(route('talent.candidates.index'), { search: query }, { preserveState: true, preserveScroll: true });
};

const getScoreColor = (score) => {
    if (!score) return 'bg-gray-100 text-gray-800';
    if (score >= 80) return 'bg-green-100 text-green-800';
    if (score >= 50) return 'bg-yellow-100 text-yellow-800';
    return 'bg-red-100 text-red-800';
};

const updateStatus = (candidate, newStatus) => {
    if (newStatus === candidate.status) return;

    // Workflow Check
    if (newStatus === 'Interview' || newStatus === 'Screening') {
         emit('trigger-workflow', { candidate, status: newStatus });
        return;
    }

    if (newStatus === 'Rejected') {
        emit('open-rejection', candidate);
        return;
    }

    if (newStatus === 'Offer') {
        emit('open-offer', candidate);
        return;
    }
    
    performMove(candidate.id, newStatus);
};

const performMove = (candidateId, newStatus, extraData = {}) => {
    router.post(route('talent.candidates.move', candidateId), {
        status: newStatus,
        ...extraData
    }, {
        preserveScroll: true,
        onError: (errors) => {
            console.error('Move failed', errors);
            alert('Failed to move candidate. Check inputs.');
        }
    });
};
// Removed local Quick View Logic
</script>
