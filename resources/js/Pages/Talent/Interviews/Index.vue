<template>
    <TalentLayout>
        <template #actions>
             <!-- Actions from parent if any -->
        </template>
        
        <main class="py-6">
            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-8 -mx-4 sm:mx-0 overflow-x-auto no-scrollbar">
                <nav class="-mb-px flex space-x-8 px-4 sm:px-0" aria-label="Tabs">
                    <button v-for="tab in tabs" :key="tab.name"
                            @click="currentTab = tab.name"
                            :class="[currentTab === tab.name ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-400 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-black text-sm uppercase tracking-widest transition-all flex items-center']">
                        {{ tab.name }}
                        <span v-if="tab.count" class="ml-2 bg-indigo-50 text-indigo-600 py-0.5 px-2.5 rounded-full text-sm font-black">
                            {{ tab.count }}
                        </span>
                    </button>
                </nav>
            </div>
 
            <!-- Content -->
            <div v-show="loading" class="flex justify-center py-20">
                <div class="animate-spin rounded-full h-10 w-10 border-4 border-indigo-600 border-t-transparent"></div>
            </div>
 
            <div v-show="!loading" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="interview in filteredInterviews" :key="interview.id" 
                     class="bg-white/80 backdrop-blur-xl overflow-hidden shadow-sm rounded-2xl border border-gray-100 hover:shadow-xl hover:border-indigo-200 transition-all duration-300 relative group flex flex-col">
                    
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-indigo-500 to-purple-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    
                    <!-- Card Header -->
                    <div class="px-5 py-6 flex-1">
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100 flex items-center justify-center text-indigo-600 font-black text-xs border border-indigo-200/50 shadow-inner">
                                        {{ getInitials(interview.application.candidate.first_name, interview.application.candidate.last_name) }}
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-base font-black text-slate-900 tracking-tight group-hover:text-indigo-600 transition-colors truncate">
                                        {{ interview.application.candidate.first_name }} {{ interview.application.candidate.last_name }}
                                    </h3>
                                    <p class="text-sm font-bold text-gray-400 uppercase tracking-widest truncate">{{ interview.application.job.title }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-black uppercase tracking-widest shadow-sm"
                                  :class="{
                                      'bg-emerald-100 text-emerald-800': interview.status === 'Completed',
                                      'bg-amber-100 text-amber-800': interview.status === 'Scheduled',
                                      'bg-rose-100 text-rose-800': interview.status === 'Cancelled'
                                  }">
                                {{ interview.status }}
                            </span>
                        </div>
 
                        <!-- Details Grid -->
                        <div class="space-y-3 bg-gray-50/50 p-4 rounded-xl border border-gray-100 mb-5">
                            <div class="flex items-center text-xs font-bold text-slate-600 tracking-tight">
                                <svg class="flex-shrink-0 mr-2.5 h-4 w-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>{{ formatDate(interview.scheduled_at) }}</span>
                            </div>
                            <div class="flex items-center text-xs font-bold text-slate-600 tracking-tight">
                                <svg class="flex-shrink-0 mr-2.5 h-4 w-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>{{ interview.duration }}m • {{ interview.type }} • {{ interview.round }}</span>
                            </div>
                            <div v-if="interview.meeting_link" class="flex items-center pt-1">
                                <a :href="interview.meeting_link" target="_blank" class="flex items-center gap-2 bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-sm font-black uppercase tracking-widest shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all w-full justify-center">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    Join Digital Session
                                </a>
                            </div>
                        </div>
 
                        <!-- Feedback Summary (if completed) -->
                        <div v-if="interview.feedbacks && interview.feedbacks.length > 0" class="pt-4 border-t border-gray-100">
                             <p class="text-sm font-black text-gray-400 uppercase tracking-widest mb-3">Panel Verdict</p>
                             <div  v-for="fb in interview.feedbacks" :key="fb.id" class="flex items-center justify-between">
                                 <span class="text-xs font-black text-indigo-700 uppercase tracking-wider">{{ fb.recommendation }}</span>
                                 <div class="flex text-amber-400 text-sm">
                                     <span v-for="i in 5" :key="i" :class="i <= fb.rating ? 'text-amber-400' : 'text-gray-200'">★</span>
                                 </div>
                             </div>
                        </div>
                    </div>
 
                    <!-- Actions -->
                    <div class="bg-gray-50/50 border-t border-gray-100 px-5 py-4 flex justify-between items-center bg-gray-50/30">
                        <button @click="openCandidate(interview.application.candidate)" class="text-sm font-black uppercase tracking-widest text-slate-500 hover:text-indigo-600 transition-colors">
                            Candidate Profile
                        </button>
                        
                        <div class="flex space-x-2">
                             <button v-if="interview.status === 'Scheduled'" 
                                     @click="markCompleted(interview)"
                                     class="inline-flex items-center px-3 py-2 bg-white border border-gray-200 text-indigo-600 text-sm font-black uppercase tracking-widest rounded-xl shadow-sm hover:border-indigo-200 hover:bg-indigo-50 transition-all">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Finish
                            </button>
                            <button v-if="interview.status === 'Completed'" 
                                    @click="openFeedback(interview)"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-indigo-600 text-sm font-black uppercase tracking-widest rounded-xl shadow-sm hover:border-indigo-200 hover:bg-indigo-50 transition-all">
                                {{ interview.feedbacks.length ? 'Edit Score' : 'Add Feedback' }}
                            </button>
                        </div>
                    </div>
                </div>
 
                <div v-if="filteredInterviews.length === 0" class="col-span-full text-center py-24 bg-white/50 backdrop-blur rounded-3xl border-2 border-dashed border-gray-200">
                    <div class="mx-auto h-16 w-16 text-gray-200 mb-4">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">No scheduled events</h3>
                    <p class="mt-1 text-xs text-gray-400 font-medium">Try checking another bucket or schedule a new round.</p>
                </div>
            </div>
        </main>

        <!-- Components -->
        <CandidateQuickView 
            :show="!!selectedCandidate" 
            :candidate="selectedCandidate" 
            @close="selectedCandidate = null"
            :initial-tab="'Interviews'"
        />

        <FeedbackModal 
            :show="showFeedbackModal" 
            :interview-id="selectedInterviewForFeedback?.id"
            :existing-feedback="selectedInterviewForFeedback?.feedbacks?.[0]"
            @close="closeFeedback"
            @success="handleFeedbackSuccess"
            @scheduleNext="handleScheduleNext" 
        />
    </TalentLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import TalentLayout from '@/Layouts/TalentLayout.vue';
import CandidateQuickView from '../Candidates/CandidateQuickView.vue';
import FeedbackModal from '../Candidates/FeedbackModal.vue';

const props = defineProps({
    myPending: Array,
    allUpcoming: Array,
    completed: Array
});

const currentTab = ref('My Pending');
const loading = ref(false);

const tabs = computed(() => [
    { name: 'My Pending', count: props.myPending.length },
    { name: 'Upcoming', count: props.allUpcoming.length },
    { name: 'Completed', count: props.completed.length },
]);

const filteredInterviews = computed(() => {
    if (currentTab.value === 'My Pending') return props.myPending;
    if (currentTab.value === 'Upcoming') return props.allUpcoming;
    return props.completed;
});

// Modals
const selectedCandidate = ref(null);
const showFeedbackModal = ref(false);
const selectedInterviewForFeedback = ref(null);

const openCandidate = (candidate) => {
    selectedCandidate.value = candidate;
};

const openFeedback = (interview) => {
    selectedInterviewForFeedback.value = interview;
    showFeedbackModal.value = true;
};

const closeFeedback = () => {
    showFeedbackModal.value = false;
    selectedInterviewForFeedback.value = null;
};

const handleFeedbackSuccess = () => {
    closeFeedback();
    // Refresh Page to move items between tabs
    router.reload({ only: ['myPending', 'allUpcoming', 'completed'] });
};

const handleScheduleNext = () => {
    // Logic to open schedule modal for next round if needed
    // For now, reload serves purpose as candidate status updates
    router.reload({ only: ['myPending', 'allUpcoming', 'completed'] });
};

const markCompleted = (interview) => {
    if (!confirm('Mark this interview as completed?')) return;

    router.put(route('talent.candidates.interviews.update', interview.id), {
        ...interview,
        status: 'Completed'
    }, {
        onSuccess: () => {
             // Optional: Open Feedback Modal
             openFeedback(interview);
        }
    });
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('en-US', {
        weekday: 'short', month: 'short', day: 'numeric',
        hour: 'numeric', minute: '2-digit'
    });
};

const getInitials = (first, last) => {
    return (first?.[0] || '') + (last?.[0] || '');
};

</script>
