<template>
    <TalentLayout>
        <div class="h-full flex flex-col">
            <!-- Header & Filters -->
            <div class="flex flex-col space-y-4 mb-4 px-4 sm:px-0 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <h2 class="text-xl md:text-2xl font-black text-gray-900 tracking-tight">Candidate Pipeline</h2>
                    <div class="flex items-center gap-2 w-full md:w-auto">
                         <!-- Stage Defaults Config -->
                         <button @click="openConfigModal"
                                 :disabled="!filters.job_id"
                                 :class="[filters.job_id ? 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 border-gray-200' : 'text-gray-300 cursor-not-allowed border-gray-100']"
                                 class="p-2 rounded-xl transition-all border shadow-sm"
                                 :title="filters.job_id ? 'Configure Stage Defaults' : 'Select a Job to Configure Defaults'">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                         </button>
 
                         <!-- View Toggle -->
                        <div class="flex bg-gray-100 p-1 rounded-xl flex-1 md:flex-none">
                            <button @click="viewMode = 'board'" 
                                    :class="{'bg-white shadow-sm text-indigo-600': viewMode === 'board', 'text-gray-500': viewMode !== 'board'}"
                                    class="flex-1 md:flex-none px-4 py-2 rounded-lg text-sm font-black uppercase tracking-widest transition-all flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                <span class="hidden sm:inline">Board</span>
                            </button>
                            <button @click="viewMode = 'list'" 
                                    :class="{'bg-white shadow-sm text-indigo-600': viewMode === 'list', 'text-gray-500': viewMode !== 'list'}"
                                    class="flex-1 md:flex-none px-4 py-2 rounded-lg text-sm font-black uppercase tracking-widest transition-all flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                                <span class="hidden sm:inline">List</span>
                            </button>
                        </div>
                        
                        <button class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl font-black text-sm uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                            + <span class="hidden sm:inline">Add Candidate</span>
                            <span class="sm:hidden">Add</span>
                        </button>
                    </div>
                </div>

                <!-- Filters Bar -->
                <div class="flex space-x-4 items-center">
                    <!-- Scope Filter (My Interviews) -->
                     <div class="w-48">
                         <select v-model="filters.scope" @change="updateFilters" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md hover:bg-gray-50">
                            <option value="all">All Candidates</option>
                            <option value="assigned">My Assignments</option>
                            <option value="interviews">My Interviews</option>
                        </select>
                    </div>

                    <div class="w-64">
                         <select v-model="filters.job_id" @change="updateFilters" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">All Jobs</option>
                            <option v-for="job in jobs" :key="job.id" :value="job.id">{{ job.title }}</option>
                        </select>
                    </div>
                    <div class="flex-1 relative">
                        <input type="text" v-model="filters.search" @input="debouncedSearch" placeholder="Search candidates..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>
                    <button class="text-gray-600 hover:text-gray-900 text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        Filters
                    </button>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 overflow-hidden">
                <component 
                    :is="viewMode === 'board' ? BoardView : ListView" 
                    :candidates="candidates" 
                    :jobs="jobs"
                    :users="users"
                    :teams="teams"
                    :selected-job="selectedJob"
                    v-model:selectedIds="selectedIds"
                    @trigger-workflow="onTriggerWorkflow"
                    @open-rejection="onOpenRejection"
                    @open-offer="onOpenOffer"
                    @configure-stages="openConfigModal"
                    @open-quick-view="openQuickView"
                />
            </div>
        </div>

        <WorkflowActionModal 
            :show="showWorkflowModal"
            :target-status="workflowTargetStatus"
            :current-status="workflowCandidate?.status"
            :candidate-name="workflowCandidate?.name"
            :stage-config="workflowCandidate?.stage_config"
            :candidate="workflowCandidate" 
            :users="users"
            :teams="teams"
            @close="closeWorkflowModal"
            @confirm="handleWorkflowConfirm"
            @configure-stages="openConfigModal"
        />

        <StageConfigModal
            :show="showConfigModal"
            :job="selectedJob"
            :jobs="jobs"
            :users="users"
            :teams="teams"
            @close="closeConfigModal"
        />

        <RejectionModal 
            :show="showRejectionModal" 
            :application-id="selectedApplicationId"
            @close="closeRejectionModal"
            @success="handleModalSuccess"
        />

        <OfferModal 
            :show="showOfferModal" 
            :application-id="selectedApplicationId"
            :candidate="selectedCandidate"
            :offer-summary="selectedCandidate?.latest_offer"
            :templates="offerTemplates"
            :salary-structures="salaryStructures"
            :users="users"
            @close="closeOfferModal"
            @success="handleModalSuccess"
        />

        <CandidateQuickView 
            :show="showQuickView" 
            :candidate="selectedCandidate" 
            :users="users"
            :initial-tab="quickViewTab"
            :auto-open-feedback="autoTriggerFeedback"
            @close="closeQuickView" 
        />

        <!-- Bulk Action Floating Bar -->
        <transition enter-active-class="transform transition duration-300 ease-out" enter-from-class="translate-y-20 opacity-0" enter-to-class="translate-y-0 opacity-100" leave-active-class="transform transition duration-200 ease-in" leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-20 opacity-0">
            <div v-if="selectedIds.length > 0" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-gray-900 text-white px-6 py-3 rounded-full shadow-2xl flex items-center gap-6 border border-gray-700">
                <div class="flex items-center gap-3">
                    <span class="bg-indigo-600 px-2 py-0.5 rounded text-xs font-bold">{{ selectedIds.length }}</span>
                    <span class="text-sm font-medium">Selected</span>
                </div>
                <div class="h-4 w-px bg-gray-700"></div>
                <div class="flex items-center gap-2">
                     <button @click="performBulkAction('offer')" class="px-3 py-1.5 rounded-lg hover:bg-gray-800 text-sm font-bold text-indigo-300 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.586l5.414 5.414a1 1 0 01.586 1.414V19a2 2 0 01-2 2z" /></svg>
                        Draft Offers
                    </button>
                    <button @click="performBulkAction('reject')" class="px-3 py-1.5 rounded-lg hover:bg-gray-800 text-sm font-bold text-red-300 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Reject
                    </button>
                </div>
                 <div class="h-4 w-px bg-gray-700"></div>
                 <button @click="selectedIds = []" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </transition>
    </TalentLayout>
</template>

<script setup>
import TalentLayout from '@/Layouts/TalentLayout.vue';
import BoardView from './Board.vue';
import ListView from './List.vue'; 
import WorkflowActionModal from './WorkflowActionModal.vue';
import StageConfigModal from './StageConfigModal.vue';
import RejectionModal from './RejectionModal.vue';
import OfferModal from './OfferModal.vue';
import CandidateQuickView from './CandidateQuickView.vue';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';

const props = defineProps({
    candidates: [Array, Object], // Handle Paginator or Array
    jobs: Array,
    users: Array,
    teams: Array,
    filters: Object,
    selectedJob: Object,
    offerTemplates: Array,
    salaryStructures: Array
});

const viewMode = ref('board');
const filters = ref({
    job_id: props.filters?.job_id || '',
    search: props.filters?.search || '',
    scope: props.filters?.scope || 'all'
});

const updateFilters = () => {
    router.get(route('talent.candidates.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const debouncedSearch = debounce(() => {
    updateFilters();
}, 300);

// Quick View State
const showQuickView = ref(false);
const selectedCandidate = ref(null);
const quickViewTab = ref('overview');
const autoTriggerFeedback = ref(false);

const openQuickView = (candidateIdOrObj, tab = 'overview', autoTrigger = false) => {
    // If object passed (clicked from child)
    if (typeof candidateIdOrObj === 'object') {
        selectedCandidate.value = candidateIdOrObj;
        quickViewTab.value = tab;
        autoTriggerFeedback.value = autoTrigger;
        showQuickView.value = true;
    } else {
        const items = props.candidates.data || props.candidates;
        const found = Array.isArray(items) ? items.find(c => c.id === parseInt(candidateIdOrObj)) : null;
        if (found) {
             selectedCandidate.value = found;
             showQuickView.value = true;
        }
    }
};

const closeQuickView = () => {
    showQuickView.value = false;
    selectedCandidate.value = null;
    
    // Clear URL param without reload
    const url = new URL(window.location);
    url.searchParams.delete('open_id');
    window.history.replaceState({}, '', url);
};

// Check for open_id param on mount
if (typeof window !== 'undefined') {
    const params = new URLSearchParams(window.location.search);
    const openId = params.get('open_id');
    if (openId) {
        // Ideally wait for candidates to load? They are passed as props.
        // Just call logic
        setTimeout(() => openQuickView(openId), 500); // Small delay to ensure render
    }
}

// Workflow State (One source of truth)
const showWorkflowModal = ref(false);
const workflowTargetStatus = ref('');
const workflowCandidate = ref(null);

const onTriggerWorkflow = ({ candidate, status }) => {
    workflowCandidate.value = candidate;
    workflowTargetStatus.value = status;
    showWorkflowModal.value = true;
};

const handleWorkflowConfirm = (formData) => {
    performMove(workflowCandidate.value.id, workflowTargetStatus.value, formData);
    closeWorkflowModal();
};

// Config State
const showConfigModal = ref(false);

const openConfigModal = () => {
    showConfigModal.value = true;
};

const closeConfigModal = () => {
    showConfigModal.value = false;
};

const closeWorkflowModal = () => {
    showWorkflowModal.value = false;
    workflowCandidate.value = null;
    workflowTargetStatus.value = '';
};

// Rejection & Offer State
const showRejectionModal = ref(false);
const showOfferModal = ref(false);
const selectedApplicationId = ref(null);

const onOpenRejection = (candidate) => {
    selectedApplicationId.value = candidate.application_id || candidate.id; // Fallback if API hasn't mapped it yet, but best to rely on explicit mapped ID
    showRejectionModal.value = true;
};

const onOpenOffer = (candidate) => {
    selectedCandidate.value = candidate; 
    selectedApplicationId.value = candidate.application_id;
    showOfferModal.value = true;
};

const closeRejectionModal = () => {
    showRejectionModal.value = false;
    selectedApplicationId.value = null;
};

const closeOfferModal = () => {
    showOfferModal.value = false;
    selectedApplicationId.value = null;
};

const handleModalSuccess = () => {
    // Refresh data
    updateFilters();
    closeRejectionModal();
    closeOfferModal();
    // User requested explicit success message
    // In a real app we'd use a Toast, but for now strict alert ensures visibility as requested
    alert('Action completed successfully!');
};

// Common Move Logic
const performMove = (candidateId, newStatus, extraData = {}) => {
    router.post(route('talent.candidates.move', candidateId), {
        status: newStatus,
        ...extraData
    }, {
        preserveScroll: true,
        onError: (errors) => {
            console.error('Move failed', errors);
            alert('Failed to move candidate. Check inputs.');
        },
        onSuccess: () => {
             // Optional: Toast message
        }
    });
};


// Bulk Selection State
const selectedIds = ref([]);

const performBulkAction = (action) => {
    if (!confirm(`Are you sure you want to ${action} ${selectedIds.value.length} candidates?`)) return;

    router.post(route('talent.candidates.bulk'), {
        ids: selectedIds.value,
        action: action
    }, {
        onSuccess: () => {
            selectedIds.value = [];
            alert('Bulk action completed successfully.');
        },
        onError: () => alert('Bulk action failed.')
    });
};
</script>
