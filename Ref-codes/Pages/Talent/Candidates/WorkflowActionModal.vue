<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Flex Container for Centering - Works on Mobile & Desktop -->
            <div class="fixed inset-0 flex items-center justify-center p-4">
                
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-900/20 transition-opacity backdrop-blur-md" @click="close"></div>

                <!-- Modal Panel -->
                <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md mx-auto z-10 overflow-hidden transform transition-all">
                    
                    <!-- Header Section -->
                    <div class="text-center pt-8 px-6">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-50 mb-4">
                            <!-- Icon -->
                            <svg v-if="targetStatus === 'Interview'" class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                             <svg v-else-if="targetStatus === 'Screening'" class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                             <svg v-else class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        
                        <h3 class="text-xl font-bold text-gray-900" id="modal-title">
                            {{ isEditMode ? 'Update ' + targetStatus : 'Move to ' + targetStatus }}
                        </h3>
                        <p class="mt-2 text-sm text-gray-500">
                            {{ isEditMode ? 'Update details' : 'Prepare the next steps' }} for <strong class="text-gray-800">{{ candidateName }}</strong>.
                        </p>
                    </div>

                    <!-- Status Summary Banner (New) -->
                    <div v-if="candidateName" class="px-6 pb-2">
                        <div class="bg-white border border-gray-200 rounded-lg p-3 flex items-center justify-between text-xs shadow-sm">
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-500">Current Stage:</span>
                                <span class="px-2 py-0.5 rounded-full font-bold"
                                      :class="{
                                          'bg-blue-100 text-blue-800': currentStatus === 'Screening',
                                          'bg-indigo-100 text-indigo-800': currentStatus === 'Interview',
                                          'bg-purple-100 text-purple-800': currentStatus === 'Offer',
                                          'bg-green-100 text-green-800': currentStatus === 'Hired',
                                          'bg-red-100 text-red-800': currentStatus === 'Rejected',
                                          'bg-gray-100 text-gray-800': currentStatus === 'Applied',
                                      }">
                                    {{ currentStatus }}
                                </span>
                            </div>

                            <div v-if="candidate && candidate.last_interview_round" class="flex items-center space-x-2">
                                <span class="text-gray-400">|</span>
                                <span class="text-gray-500">Last:</span>
                                <span class="font-medium text-gray-700">
                                    {{ candidate.last_interview_round }}
                                    <span class="text-gray-400 text-sm ml-1">({{ candidate.last_interview_date }})</span>
                                </span>
                                <!-- Result Indicator -->
                                <span v-if="['Strong Hire', 'Hire'].includes(candidate.interview_recommendation)" 
                                      class="ml-1 h-2 w-2 rounded-full bg-green-500" title="Passed"></span>
                                <span v-else-if="['No Hire', 'Strong No'].includes(candidate.interview_recommendation)" 
                                      class="ml-1 h-2 w-2 rounded-full bg-red-500" title="Failed"></span>
                            </div>
                        </div>
                    </div>

                     <!-- Toggle Bypass -->
                    <div class="mt-6 flex items-center justify-center space-x-3 px-6 pb-2">
                        <span class="text-sm font-medium" :class="!form.skip_workflow ? 'text-gray-900' : 'text-gray-400'">Workflow</span>
                        <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                            <input type="checkbox" v-model="form.skip_workflow" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer border-gray-300 left-0 transition-all duration-300" :class="{ 'translate-x-6 border-indigo-600': form.skip_workflow }" />
                            <label class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer" :class="{ 'bg-indigo-200': form.skip_workflow }"></label>
                        </div>
                        <span class="text-sm font-medium" :class="form.skip_workflow ? 'text-indigo-600' : 'text-gray-400'">Skip Details</span>
                    </div>

                    <!-- Scrollable Content -->
                    <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
                        
                        <!-- CONTEXT: SCREENING -->
                        <div v-if="targetStatus === 'Screening' && !form.skip_workflow" class="space-y-4">
                            <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-bold text-blue-900">AI Match Score</h4>
                                    <p class="text-xs text-blue-600 mt-1">Based on resume keywords</p>
                                </div>
                                <div class="h-12 w-12 rounded-full bg-white flex items-center justify-center border-2 border-blue-200 shadow-sm">
                                    <span class="text-sm font-bold text-blue-700">85%</span>
                                </div>
                            </div>
                            
                            <!-- Configuration Header -->
                            <div class="flex items-center justify-between border-b pb-2">
                                <h4 class="text-sm font-semibold text-gray-700">Assignments</h4>
                                <button type="button" @click="emit('configure-stages')" class="text-gray-400 hover:text-indigo-600 transition-colors" title="Configure Stage Defaults">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </button>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Assign Reviewers</label>
                                <Combobox 
                                    v-model="form.reviewer_ids" 
                                    :items="users" 
                                    :multiple="true" 
                                    placeholder="Search people..." 
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Assign Teams</label>
                                <Combobox 
                                    v-model="form.team_ids" 
                                    :items="teams" 
                                    :multiple="true" 
                                    placeholder="Search teams..." 
                                />
                            </div>
                        </div>

                        <!-- CONTEXT: INTERVIEW -->
                        <div v-if="targetStatus === 'Interview' && !form.skip_workflow" class="space-y-5">
                            <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                                <label class="text-sm font-medium text-gray-700">Schedule Now?</label>
                                <div class="flex items-center space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" v-model="form.interview.schedule_mode" value="now" class="form-radio text-indigo-600">
                                        <span class="ml-2 text-sm text-gray-700">Yes</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" v-model="form.interview.schedule_mode" value="later" class="form-radio text-indigo-600">
                                        <span class="ml-2 text-sm text-gray-700">Later</span>
                                    </label>
                                </div>
                            </div>

                            <div v-if="form.interview.schedule_mode === 'now'" class="space-y-4 animate-fadeIn">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Round</label>
                                        <select v-model="form.interview.round" class="block w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            <option v-for="round in roundTypes" :key="round">{{ round }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Round Title (Optional)</label>
                                        <input type="text" v-model="form.interview.round_title" placeholder="e.g. System Design" class="block w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Type</label>
                                        <select v-model="form.interview.type" class="block w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            <option>Online</option>
                                            <option>In-Person</option>
                                            <option>Phone</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="col-span-1">
                                         <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Date & Time</label>
                                        <input type="datetime-local" v-model="form.interview.scheduled_at" class="block w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div class="col-span-1">
                                         <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Duration</label>
                                         <div class="relative rounded-md shadow-sm">
                                            <input type="number" v-model="form.interview.duration" class="block w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 pr-12" placeholder="30">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">min</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Interviewers</label>
                                    <Combobox 
                                        v-model="form.interview.interviewer_ids" 
                                        :items="users" 
                                        :multiple="true" 
                                        placeholder="Select interviewers..." 
                                    />
                                </div>

                                <div v-if="form.interview.type === 'Online'">
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Meeting Link</label>
                                    <input type="text" v-model="form.interview.meeting_link" placeholder="Zoom / Teams URL" class="block w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div v-if="form.interview.type === 'In-Person'">
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Location</label>
                                    <input type="text" v-model="form.interview.location" placeholder="Office Address / Room" class="block w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Message to Candidate</label>
                                    <textarea v-model="form.interview.message_body" rows="3" class="block w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Optional custom message..."></textarea>
                                </div>
                                
                                <!-- Email Options -->
                                <div class="pt-2">
                                    <button type="button" @click="showEmailOpts = !showEmailOpts" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                                        {{ showEmailOpts ? '- Hide Email Options' : '+ Show CC/BCC' }}
                                    </button>
                                    <div v-if="showEmailOpts" class="mt-2 grid grid-cols-2 gap-4 bg-gray-50 p-3 rounded-lg">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">CC</label>
                                            <input type="text" v-model="form.interview.cc" placeholder="comma emails..." class="mt-1 block w-full border-gray-200 rounded text-xs bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">BCC</label>
                                            <input type="text" v-model="form.interview.bcc" placeholder="comma emails..." class="mt-1 block w-full border-gray-200 rounded text-xs bg-white">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-sm text-gray-600 bg-indigo-50 p-4 rounded-lg flex items-start">
                                <svg class="w-5 h-5 text-indigo-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>Status will be updated to "Interview". You can schedule the interview later from the candidate profile.</span>
                            </div>
                        </div>

                        <!-- Fallbacks -->
                         <div v-else-if="form.skip_workflow" class="p-4 bg-yellow-50 text-yellow-800 text-sm rounded-lg flex items-center">
                            <svg class="w-5 h-5 mr-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Candidate status will be updated immediately. No details will be recorded.
                        </div>

                        <div v-else-if="!['Screening', 'Interview'].includes(targetStatus)" class="p-4 bg-gray-50 rounded-lg border border-gray-100 text-center">
                            <p class="text-sm text-gray-600 mb-1">
                                Confirm moving <strong>{{ candidateName }}</strong> to <strong>{{ targetStatus }}</strong>?
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row-reverse sm:gap-2 gap-3">
                        <button type="button" 
                                @click="confirm"
                                class="w-full sm:w-auto inline-flex justify-center items-center rounded-lg border border-transparent shadow-sm px-6 py-2.5 bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            {{ form.skip_workflow ? 'Move Immediately' : (isEditMode ? 'Update Details' : 'Schedule & Move') }}
                        </button>
                        <button type="button" 
                                @click="close" 
                                class="w-full sm:w-auto inline-flex justify-center items-center rounded-lg border border-gray-300 shadow-sm px-6 py-2.5 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { reactive, ref, watch, computed } from 'vue';
import Combobox from '@/Components/Combobox.vue';

const props = defineProps({
    show: Boolean,
    targetStatus: String,
    currentStatus: String,
    candidateName: String,
    candidate: Object, // Added
    stageConfig: Object,
    users: Array,
    teams: { type: Array, default: () => [] } // Add teams logic later if not present in parent
});

const emit = defineEmits(['close', 'confirm', 'configure-stages']);
const showEmailOpts = ref(false);

const isEditMode = computed(() => {
    return props.targetStatus === props.currentStatus;
});

const form = reactive({
    skip_workflow: false,
    reviewer_ids: [],
    team_ids: [],
    interview: {
        schedule_mode: 'now',
        type: 'Online',
        round: 'Round 1',
        round_title: '',
        scheduled_at: '',
        duration: 30,
        interviewer_ids: [],
        meeting_link: '',
        location: '',
        cc: '',
        cc: '',
        bcc: '',
        message_body: ''
    }
});

const roundTypes = [
    'Round 1', 'Round 2', 'Round 3', 
    'Technical', 'Managerial', 'HR', 'Final',
    'Screening', 'Behavioral'
];

// Reset form & Apply Defaults
watch(() => props.show, (val) => {
    if (val) {
        form.skip_workflow = false;
        
        // Reset Interview form state
        form.interview = {
            schedule_mode: 'now',
            type: 'Online',
            round: 'Round 1',
            round_title: '',
            scheduled_at: '',
            duration: 30,
            interviewer_ids: [],
            meeting_link: '',
            location: '',
            cc: '',
            bcc: '',
            message_body: ''
        };
        
        // Reset Assignments
        form.reviewer_ids = [];
        form.team_ids = [];

        // Apply Defaults if Moving to Screening
        if (props.targetStatus === 'Screening' && props.stageConfig?.Screening && !isEditMode.value) {
            form.reviewer_ids = props.stageConfig.Screening.reviewer_ids || [];
            form.team_ids = props.stageConfig.Screening.team_ids || [];
        }
    }
});

const close = () => {
    emit('close');
};

const confirm = () => {
    // Basic frontend validation
    if (!form.skip_workflow) {
        if (props.targetStatus === 'Interview' && form.interview.schedule_mode === 'now') {
            if (!form.interview.scheduled_at) return alert('Date is required');
            if (form.interview.interviewer_ids.length === 0) return alert('Select at least one interviewer');
        }
        if (props.targetStatus === 'Screening') {
            // Optional val rule?
            if (form.reviewer_ids.length === 0 && form.team_ids.length === 0) {
                 // return alert('Assign at least one reviewer or team'); // Enforce? Maybe loose for now
            }
        }
    }
    
    emit('confirm', form);
};
</script>

<style scoped>
/* Custom toggle styling if needed, otherwise rely on utility classes */
</style>
