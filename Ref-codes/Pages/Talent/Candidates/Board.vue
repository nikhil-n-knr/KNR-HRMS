<template>
    <div class="h-full flex flex-col">
        <!-- Kanban Board -->
        <div class="flex-1 overflow-x-auto overflow-y-hidden snap-x snap-mandatory scroll-smooth lg:snap-none no-scrollbar">
            <div class="h-full flex space-x-4 pb-4 px-4 min-w-max">
                
                <div v-for="status in statuses" :key="status" 
                     class="w-[85vw] sm:w-80 flex flex-col bg-gray-100/50 rounded-2xl p-4 transition-all duration-200 hover:bg-gray-100 border border-gray-200/50 shadow-sm snap-center md:snap-align-none"
                     @dragover.prevent="onDragOver"
                     @drop="onDrop($event, status)">
                    
                    <!-- Column Header -->
                    <div class="flex justify-between items-center mb-4 sticky top-0 bg-inherit z-20 pb-2 border-b border-gray-200">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] leading-none">{{ status }}</h3>
                            <button v-if="['Screening', 'Interview'].includes(status)" 
                                    @click="selectedJob && emit('configure-stages', status)"
                                    :disabled="!selectedJob"
                                    :class="[selectedJob ? 'text-gray-400 hover:text-indigo-600' : 'text-gray-200 cursor-not-allowed', 'transition-colors', 'ml-2']"
                                    :title="selectedJob ? 'Configure Defaults' : 'Select a Job first'">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </button>
                        </div>
                        <span class="bg-white/80 backdrop-blur shadow-sm text-indigo-600 text-sm font-black px-2.5 py-1 rounded-full border border-indigo-100">
                            {{ getCount(status) }}
                        </span>
                    </div>

                    <!-- Draggable Area -->
                    <div class="flex-1 overflow-y-auto space-y-3 min-h-[100px] custom-scrollbar pr-1">
                        <div v-for="candidate in getCandidates(status)" 
                             :key="candidate.id"
                             draggable="true"
                             @dragstart="onDragStart($event, candidate)"
                             @click="emit('open-quick-view', candidate)"
                             class="group p-4 rounded-xl shadow-sm border hover:shadow-lg cursor-grab active:cursor-grabbing transition-all duration-200 relative overflow-hidden bg-white"
                             :class="{
                                'ring-2 ring-emerald-500/20 shadow-emerald-100': status === 'Interview' && candidate.last_interview_status === 'Completed',
                                'ring-2 ring-amber-500/20 shadow-amber-100': status === 'Interview' && candidate.last_interview_status === 'Scheduled',
                                'ring-2 ring-indigo-500/10 shadow-indigo-100': status === 'Screening' && candidate.screening_rating > 0
                             }">
                            
                            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r"
                                 :class="{
                                    'from-emerald-400 to-emerald-600': status === 'Interview' && candidate.last_interview_status === 'Completed',
                                    'from-amber-400 to-amber-600': status === 'Interview' && candidate.last_interview_status === 'Scheduled',
                                    'from-indigo-400 to-indigo-600': status === 'Screening' && candidate.screening_rating > 0,
                                    'from-slate-200 to-slate-300': !status.match(/Interview|Screening/)
                                 }"></div>
                            
                            <div class="flex justify-between items-start">
                                <!-- Referral Badge -->
                                <div v-if="candidate.referrer" class="absolute -top-2 -right-2 bg-yellow-100 text-yellow-800 text-sm px-2 py-0.5 rounded-full border border-yellow-200 shadow-sm z-10 flex items-center" title="Referred Candidate">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    Referred by {{ candidate.referrer.name.split(' ')[0] }}
                                </div>

                                <div>
                                    <h4 class="text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors tracking-tight">{{ candidate.name }}</h4>
                                    <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mt-0.5">{{ candidate.job_title }}</p>
                                    
                                    <!-- Feedback Ratings (New) -->
                                    <div v-if="candidate.interview_rating" class="flex items-center mt-1 space-x-2">
                                         <div class="flex text-yellow-400 text-xs">
                                             <span v-for="i in 5" :key="i" :class="i <= candidate.interview_rating ? 'text-yellow-400' : 'text-gray-300'">★</span>
                                         </div>
                                         <span class="text-sm uppercase font-bold tracking-wider" 
                                               :class="{
                                                   'text-green-600': ['Strong Hire', 'Hire'].includes(candidate.interview_recommendation),
                                                   'text-red-500': ['No Hire', 'Strong No'].includes(candidate.interview_recommendation),
                                                   'text-yellow-600': candidate.interview_recommendation === 'On Hold'
                                               }">
                                            {{ candidate.interview_recommendation }}
                                         </span>
                                    </div>
                                    <p v-if="candidate.rejection_reason" class="text-sm text-red-500 mt-1 font-medium">Reason: {{ candidate.rejection_reason }}</p>
                                    
                                    <!-- Last Interview Info -->
                                    <div v-if="candidate.last_interview_round && status === 'Interview'" class="mt-2 text-sm text-gray-500 flex items-center justify-between bg-gray-50 p-1.5 rounded">
                                        <span>{{ candidate.last_interview_round }}</span>
                                        <span class="font-medium" :class="['Strong Hire', 'Hire'].includes(candidate.interview_recommendation) ? 'text-green-700 bg-green-100 px-1 rounded' : ''">
                                            {{ candidate.last_interview_date }}
                                        </span>
                                    </div>

                                    <!-- Offer Status Details -->
                                    <div v-if="status === 'Offer' && candidate.latest_offer" class="mt-2 p-2 bg-indigo-50 rounded border border-indigo-100">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-sm font-bold text-indigo-800 uppercase">Offer Extended</span>
                                            <span class="text-sm text-indigo-600">{{ candidate.latest_offer.salary_currency }} {{ Number(candidate.latest_offer.salary_amount).toLocaleString() }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm text-indigo-500">
                                            <span>Sent: {{ candidate.latest_offer.sent_at }}</span>
                                            <span class="font-medium" :class="{'text-green-600': candidate.latest_offer.status === 'Accepted', 'text-amber-600': candidate.latest_offer.status === 'Pending_Approval'}">
                                                {{ candidate.latest_offer.status }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Hired Status Details -->
                                    <div v-if="status === 'Hired' && candidate.latest_offer" class="mt-2 p-2 bg-green-50 rounded border border-green-100">
                                        <div class="flex items-center gap-2 mb-1">
                                            <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            <span class="text-xs font-bold text-green-800 uppercase">Offer Accepted</span>
                                        </div>
                                        <p v-if="candidate.latest_offer.accepted_at" class="text-sm text-green-600 pl-6">
                                            Signed on {{ candidate.latest_offer.accepted_at }}
                                        </p>
                                    </div>

                                    <!-- Pipeline Enhancements -->
                                    <div v-if="['Strong Hire', 'Hire'].includes(candidate.interview_recommendation) && status === 'Interview'" class="mt-2">
                                        <button @click.stop="emit('open-quick-view', candidate, 'Interviews', false)" 
                                                class="w-full text-center px-2 py-1 bg-green-50 text-green-700 text-xs font-medium rounded border border-green-200 hover:bg-green-100 transition-colors flex items-center justify-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            Schedule Next
                                        </button>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <!-- Edit Trigger (Screening/Interview) -->
                                    <button v-if="['Screening', 'Interview'].includes(status)"
                                            @click.stop="editWorkflow(candidate)" 
                                            class="p-1 text-gray-400 hover:text-indigo-600 rounded-full hover:bg-indigo-50 transition-colors"
                                            title="Update Details">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>

                                     <!-- Quick View Trigger -->
                                    <button @click.stop="emit('open-quick-view', candidate)" class="p-1 text-gray-400 hover:text-indigo-600 rounded-full hover:bg-indigo-50 transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                    <div class="h-2 w-2 rounded-full ml-1" :class="getScoreColor(candidate.score)" title="Match Score"></div>
                                </div>
                            </div>
                            
                            <div class="mt-3 flex justify-between items-center text-xs text-gray-500">
                                <span>{{ candidate.created_at }}</span>
                                <!-- Background Check Status (Spec Feature) -->
                                <span v-if="candidate.background_status !== 'Pending'" 
                                      class="px-2 py-0.5 rounded text-sm font-semibold"
                                      :class="getBgCheckColor(candidate.background_status)">
                                    BG: {{ candidate.background_status }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>
</template>

<script setup>
import { ref } from 'vue'; // Ensure ref is imported
import { router } from '@inertiajs/vue3';
// import CandidateQuickView from './CandidateQuickView.vue';

const props = defineProps({
    candidates: [Array, Object],
    jobs: Array,
    users: Array,
    teams: Array,
    selectedJob: Object
});

const emit = defineEmits(['trigger-workflow', 'configure-stages', 'open-rejection', 'open-offer', 'open-quick-view']);

const statuses = ['Applied', 'Screening', 'Interview', 'Offer', 'Hired', 'Rejected'];

// Removed local Quick View State


const editWorkflow = (candidate) => {
    emit('trigger-workflow', { candidate, status: candidate.status });
};

const getCandidates = (status) => (props.candidates.data || props.candidates).filter(c => c.status === status);
const getCount = (status) => getCandidates(status).length;
// Drag & Drop Logic (Native HTML5)
const onDragStart = (event, candidate) => {
    event.dataTransfer.dropEffect = 'move';
    event.dataTransfer.effectAllowed = 'move';
    // Use text/plain for broad browser compatibility
    event.dataTransfer.setData('text/plain', candidate.id.toString());
};

const onDrop = (event, newStatus) => {
    // Try standard text
    const candidateId = event.dataTransfer.getData('text/plain');

    if (!candidateId) {
        console.error('Drop failed: No valid ID found');
        return;
    }

    // Match by String (UUID compatible)
    const candidate = (props.candidates.data || props.candidates).find(c => String(c.id) === String(candidateId));
    
    if (!candidate) {
        alert('Candidate not found in local data!');
        return;
    }
    
    // 1. Check for Workflow Triggers (Delegated to Parent)
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

    // 2. Standard Move (No Workflow)
    performMove(candidateId, newStatus);
};

// Central Move Logic
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

// Helpers
const getScoreColor = (score) => {
    if (!score) return 'bg-gray-300';
    if (score >= 80) return 'bg-green-500';
    if (score >= 50) return 'bg-yellow-500';
    return 'bg-red-500';
};

const getBgCheckColor = (status) => {
    if (status === 'Clear') return 'bg-green-100 text-green-800';
    if (status === 'Flagged') return 'bg-red-100 text-red-800';
    return 'bg-blue-100 text-blue-800';
};
</script>

