<template>
    <Modal :show="show" @close="close">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900">Configure Stage Defaults</h3>
            <button @click="close" class="text-gray-400 hover:text-gray-500">
                <span class="sr-only">Close</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="px-6 py-4">
            <div v-if="job" class="mb-4">
               <p class="text-sm text-gray-500">
                   Setting defaults for <strong>{{ job.title }}</strong>. These assignments will be pre-filled when moving candidates.
               </p>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 mb-4">
                <nav class="-mb-px flex space-x-6">
                    <button v-for="tab in tabs" :key="tab"
                            @click="activeTab = tab"
                            :class="[activeTab === tab ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors']">
                        {{ tab }}
                    </button>
                </nav>
            </div>

            <!-- Content Area -->
            <div class="min-h-[200px]">
                
                <!-- Screening Defaults -->
                <div v-show="activeTab === 'Screening'" class="space-y-4 animate-fadeIn">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Default Reviewers</label>
                        <Combobox 
                            v-model="form.screening.reviewer_ids" 
                            :items="users" 
                            :multiple="true" 
                            placeholder="Search users..." 
                        />
                        <p class="text-xs text-gray-500 mt-1">These users will be auto-selected when moving to Screening.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Default Teams</label>
                        <Combobox 
                            v-model="form.screening.team_ids" 
                            :items="teams" 
                            :multiple="true" 
                            placeholder="Search teams..." 
                        />
                    </div>
                </div>

                <!-- Interview Defaults -->
                <div v-show="activeTab === 'Interview'" class="space-y-4 animate-fadeIn">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Default Interviewers</label>
                         <Combobox 
                            v-model="form.interview.interviewer_ids" 
                            :items="users" 
                            :multiple="true" 
                            placeholder="Search interviewers..." 
                        />
                        <p class="text-xs text-gray-500 mt-1">Common interviewers for this job role.</p>
                    </div>
                </div>

            <!-- Apply Scope -->
            <div class="mt-6 pt-4 border-t border-gray-100">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Apply Configuration To:</h4>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <input id="scope-current" name="scope" type="radio" value="current" v-model="applyScope" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                        <label for="scope-current" class="ml-2 block text-sm text-gray-700">
                            Only <strong>{{ job?.title }}</strong>
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input id="scope-bulk" name="scope" type="radio" value="bulk" v-model="applyScope" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                        <label for="scope-bulk" class="ml-2 block text-sm text-gray-700">
                            Multiple Jobs (Bulk Update)
                        </label>
                    </div>
                </div>

                <!-- Bulk Job Selector -->
                <div v-show="applyScope === 'bulk'" class="mt-3 pl-6 animate-fadeIn">
                     <label class="block text-xs font-medium text-gray-700 mb-1">Select other jobs to apply this config to:</label>
                     <Combobox 
                        v-model="targetJobIds" 
                        :items="availableJobsForBulk" 
                        :multiple="true" 
                        placeholder="Search jobs..." 
                    />
                    <p class="text-xs text-gray-500 mt-1">
                        Current job will also be updated. Selected jobs will have their defaults overwritten.
                    </p>
                </div>
            </div>

            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex justify-end space-x-3 bg-gray-50 rounded-b-lg">
            <button @click="close" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
            <button @click="save" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                {{ applyScope === 'bulk' ? `Apply to ${targetJobIds.length + 1} Jobs` : 'Save Configuration' }}
            </button>
        </div>
    </Modal>
</template>

<script setup>
import Modal from '@/Components/Modal.vue';
import Combobox from '@/Components/Combobox.vue';
import { reactive, watch, ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    job: Object,
    jobs: Array, // All available jobs for bulk select
    users: Array,
    teams: Array
});

const emit = defineEmits(['close']);

const activeTab = ref('Screening');
const tabs = ['Screening', 'Interview'];

const applyScope = ref('current'); // 'current' or 'bulk'
const targetJobIds = ref([]);

const form = reactive({
    screening: {
        reviewer_ids: [],
        team_ids: []
    },
    interview: {
        interviewer_ids: []
    }
});

// Load existing config
watch(() => props.show, (newVal) => {
    if (newVal && props.job) {
        const config = props.job.stage_config || {};
        
        // Screening
        const screening = config.Screening || {};
        form.screening.reviewer_ids = screening.reviewer_ids || [];
        form.screening.team_ids = screening.team_ids || [];

        // Interview
        const interview = config.Interview || {};
        form.interview.interviewer_ids = interview.interviewer_ids || [];

        // Reset scope
        applyScope.value = 'current';
        targetJobIds.value = [];
    }
});

const availableJobsForBulk = computed(() => {
    // Exclude current job from list if desired, or keep all.
    // Generally beneficial to exclude current job to avoid redundancy or just keep it simple.
    // Let's filter out current job to make "Bulk" mean "Other jobs + this one potentially"
    return props.jobs?.filter(j => j.id !== props.job?.id).map(j => ({
        id: j.id,
        name: j.title
    })) || [];
});

const close = () => emit('close');

const save = () => {
    if (!props.job) return;

    const configPayload = {
        Screening: form.screening,
        Interview: form.interview
    };

    if (applyScope.value === 'current') {
        // Single Update
        router.put(route('talent.jobs.stage_config', props.job.id), { stage_config: configPayload }, {
            onSuccess: () => close(),
            preserveScroll: true
        });
    } else {
        // Bulk Update
        // Include current job + selected targets
        const jobIds = [props.job.id, ...targetJobIds.value];
        
        router.put(route('jobs.stage_config.bulk'), {
            job_ids: jobIds,
            stage_config: configPayload
        }, {
            onSuccess: () => {
                close();
                // Can show toast here if needed
            },
            preserveScroll: true
        });
    }
};
</script>
