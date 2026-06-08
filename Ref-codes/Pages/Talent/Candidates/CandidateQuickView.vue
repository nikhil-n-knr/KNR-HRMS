<template>
    <div v-if="show" class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <!-- Background Overlay -->
            <div class="fixed inset-0 bg-gray-500/20 transition-opacity backdrop-blur-lg" aria-hidden="true" @click="close"></div>

            <!-- Modal Panel -->
            <div class="relative inline-block align-bottom bg-white/90 backdrop-blur-xl border border-white/50 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
                
                <!-- Close Button -->
                <div class="absolute top-0 right-0 pt-4 pr-4 z-10">
                    <button type="button" @click="close" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="flex flex-col h-[85vh]">
                    <!-- Header -->
                    <div class="bg-white px-4 py-5 sm:px-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title">{{ displayCandidate?.name }}</h3>
                                <div class="mt-1 flex items-center space-x-2">
                                    <p class="text-sm text-gray-500">{{ displayCandidate?.job_title }} • {{ displayCandidate?.email }}</p>
                                    <span v-if="displayCandidate?.referrer" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                        Referred by {{ displayCandidate.referrer.name }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 mr-8">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ displayCandidate?.status }}</span>
                                <span v-if="displayCandidate?.score" class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ displayCandidate?.score }}% Match</span>
                                <button v-if="displayCandidate?.status !== 'Interview' && displayCandidate?.status !== 'Rejected' && displayCandidate?.status !== 'Offer'" 
                                        v-can="'manage_recruitment'"
                                        @click="openAddInterview" 
                                        class="ml-2 px-3 py-1 bg-indigo-600 text-white rounded text-xs hover:bg-indigo-700 transition">
                                    Move to Interview
                                </button>
                                <button v-if="displayCandidate?.status === 'Interview'" 
                                        v-can="'manage_recruitment'"
                                        @click="showOfferModal = true" 
                                        class="ml-2 px-3 py-1 bg-green-600 text-white rounded text-xs hover:bg-green-700 transition">
                                    Make Offer
                                </button>
                                <button v-if="displayCandidate?.status !== 'Rejected' && displayCandidate?.status !== 'Offer'" 
                                        v-can="'manage_recruitment'"
                                        @click="showRejectionModal = true" 
                                        class="ml-2 px-3 py-1 bg-white border border-red-300 text-red-700 rounded text-xs hover:bg-red-50 transition">
                                    Reject
                                </button>
                            </div>
                        </div>
                         <!-- Tabs -->
                        <div class="mt-4 -mb-5">
                            <nav class="-mb-px flex space-x-8">
                                <button v-for="tab in tabs" :key="tab"
                                    @click="currentTab = tab"
                                    :class="[currentTab === tab ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm']">
                                    {{ tab }}
                                </button>
                            </nav>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="flex-1 flex overflow-hidden">
                        <!-- Left Pane: Details/Timeline -->
                         <div class="w-1/3 border-r border-gray-200 overflow-y-auto p-6 bg-gray-50">
                            
                            <div v-show="currentTab === 'Overview'" class="space-y-6">
                                <!-- Screening Rating (New Modal Based) -->
                                <div v-if="displayCandidate?.status === 'Screening' || displayCandidate?.status === 'Applied'" class="bg-indigo-50 p-4 rounded-md border border-indigo-100">
                                    <div class="flex justify-between items-start mb-2">
                                         <h4 class="text-sm font-medium text-indigo-900">Screening Assessment</h4>
                                         <button @click="showScreeningFeedbackModal = true" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium underline">
                                             {{ displayCandidate.screening_rating ? 'Edit Assessment' : 'Rate Candidate' }}
                                         </button>
                                    </div>
                                    
                                    <!-- Stars Display -->
                                    <div v-if="displayCandidate.screening_rating" class="flex items-center space-x-1 mb-2">
                                        <div class="flex text-yellow-400">
                                            <span v-for="star in 5" :key="star" :class="star <= displayCandidate.screening_rating ? 'text-yellow-400' : 'text-gray-300'">★</span>
                                        </div>
                                        <span class="text-xs text-indigo-700 font-semibold ml-2">
                                            {{ displayCandidate.screening_rating }}/5
                                        </span>
                                    </div>

                                    <!-- Feedback Text Display -->
                                    <p v-if="displayCandidate.screening_feedback" class="text-sm text-gray-700 italic">
                                        "{{ displayCandidate.screening_feedback }}"
                                    </p>
                                    <p v-else class="text-xs text-gray-500 italic">No feedback provided yet.</p>
                                </div>
                                
                                <!-- Contact Details -->
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 uppercase tracking-wide mb-2">Contact Info</h4>
                                    <div class="bg-white p-3 rounded border border-gray-200 shadow-sm space-y-2">
                                        <div>
                                            <label class="text-xs text-gray-500 block">Email</label>
                                            <a :href="'mailto:' + displayCandidate?.email" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 break-all">{{ displayCandidate?.email }}</a>
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500 block">Phone</label>
                                            <span class="text-sm font-medium text-gray-900">{{ displayCandidate?.phone || 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Screening Answers -->
                                <div v-if="displayCandidate?.answers && Object.keys(displayCandidate.answers).length > 0">
                                    <h4 class="text-sm font-medium text-gray-900 uppercase tracking-wide mb-2">Screening Responses</h4>
                                    <div class="space-y-3">
                                        <div v-for="(answer, question) in displayCandidate.answers" :key="question" class="p-3 bg-white border border-gray-200 rounded-md shadow-sm">
                                            <p class="text-xs text-gray-500 font-semibold mb-1">{{ question }}</p>
                                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ answer }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Activities -->
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 uppercase tracking-wide mb-2">Activities</h4>
                                    <div class="flow-root">
                                        <ul role="list" class="-mb-8">
                                            <li v-for="(event, eventIdx) in timeline" :key="event.id">
                                                <div class="relative pb-8">
                                                    <!-- Vertical Line -->
                                                    <span v-if="eventIdx !== timeline.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                    
                                                    <div class="relative flex space-x-3 group">
                                                        <!-- Icon Wrapper -->
                                                        <div>
                                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-4 ring-white transition-all group-hover:scale-110"
                                                                  :class="{
                                                                      'bg-gray-100': event.color === 'gray',
                                                                      'bg-blue-100': event.color === 'blue',
                                                                      'bg-green-100': event.color === 'green',
                                                                      'bg-red-100': event.color === 'red',
                                                                      'bg-purple-100': event.color === 'purple',
                                                                      'bg-indigo-100': event.color === 'indigo',
                                                                  }">
                                                                <!-- Icon Svg Logic remains same -->
                                                                <svg v-if="event.icon === 'user-add'" class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                                                <svg v-else-if="event.icon === 'calendar'" class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                                <svg v-else-if="event.icon === 'check'" class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                                <svg v-else-if="event.icon === 'x'" class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                                <svg v-else-if="event.icon === 'refresh'" class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                                <svg v-else-if="event.icon === 'chat'" class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                                                <svg v-else class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                            </span>
                                                        </div>
                                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between items-start">
                                                            <div class="text-sm font-medium text-gray-900">{{ event.content }}</div>
                                                            <div class="text-xs text-gray-500 whitespace-nowrap ml-2">{{ event.date }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-show="currentTab === 'Interviews'" class="space-y-4">
                                <!-- Add Button -->
                                <button @click="openAddInterview" class="w-full flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Schedule Interview
                                </button>
                                
                                <div v-for="interview in interviews" :key="interview.id" class="p-4 bg-white rounded-lg border border-gray-100 shadow-sm relative group hover:shadow-md transition-shadow">
                                    <!-- Header -->
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h5 class="text-sm font-bold text-gray-900">{{ interview.round }}</h5>
                                            <p v-if="interview.round_title" class="text-xs font-semibold text-indigo-600">{{ interview.round_title }}</p>
                                            
                                            <!-- Interviewer & Link -->
                                            <div class="mt-1 space-y-0.5">
                                                <div class="flex items-center text-xs text-gray-700">
                                                    <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                    <span class="font-medium">{{ interview.interviewer ? interview.interviewer.name : 'Unassigned' }}</span>
                                                </div>
                                                <div v-if="interview.meeting_link" class="flex items-center text-xs">
                                                     <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                                     <a :href="interview.meeting_link" target="_blank" class="text-indigo-600 hover:underline truncate max-w-[200px]">
                                                        {{ interview.meeting_link }}
                                                     </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                             <button @click="editInterview(interview)" class="p-1 text-gray-400 hover:text-indigo-600 bg-gray-50 rounded hover:bg-gray-100" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                             </button>
                                             
                                             <!-- Mark Completed Button -->
                                             <button v-if="interview.status === 'Scheduled'" @click="markCompleted(interview)" class="p-1 text-gray-400 hover:text-green-600 bg-gray-50 rounded hover:bg-gray-100" title="Mark as Completed">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                             </button>

                                             <button @click="sendReminder(interview)" :disabled="reminderLoading === interview.id" class="p-1 text-gray-400 hover:text-yellow-600 bg-gray-50 rounded hover:bg-gray-100" title="Send Reminder">
                                                <svg class="w-4 h-4" :class="{'animate-spin text-yellow-600': reminderLoading === interview.id}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                             </button>
                                             <button v-if="interview.status === 'Scheduled'" @click="openCancellation(interview)" class="p-1 text-gray-400 hover:text-red-600 bg-gray-50 rounded hover:bg-gray-100" title="Cancel Interview">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                             </button>
                                        </div>
                                    </div>
                                    
                                    <div class="text-xs text-gray-500 space-y-1 mt-2">
                                        <p class="flex items-center">
                                            <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            {{ new Date(interview.scheduled_at).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }) }}  
                                            <span class="ml-1 text-gray-400">({{ interview.duration }}m)</span>
                                        </p>
                                        <p class="flex items-center">
                                             <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            {{ interview.type }} 
                                            <span v-if="interview.location" class="ml-1">- {{ interview.location }}</span>
                                        </p>
                                        <p v-if="interview.status === 'Cancelled' && interview.cancellation_reason" class="text-red-600 italic border-l-2 border-red-200 pl-2">
                                            Cancelled: {{ interview.cancellation_reason }}
                                        </p>
                                        <p v-if="interview.message_body" class="text-gray-400 italic mt-1 border-t border-gray-100 pt-1 truncate" :title="interview.message_body">
                                            Notes: {{ interview.message_body }}
                                        </p>
                                    </div>
                                    
                                     <!-- Feedback & Actions Section -->
                                    <div v-if="interview.status === 'Completed'" class="mt-2 pt-2 border-t border-gray-100">
                                         <!-- Existing Feedback Display -->
                                         <div v-if="interview.feedback" class="flex items-center text-xs mb-3">
                                             <div class="flex text-yellow-400 mr-2">
                                                 <span v-for="i in 5" :key="i" :class="i <= interview.feedback.rating ? 'text-yellow-400' : 'text-gray-300'">★</span>
                                             </div>
                                             <span class="font-medium" :class="{
                                                 'text-green-600': ['Strong Hire', 'Hire'].includes(interview.feedback.recommendation),
                                                 'text-red-600': ['No Hire', 'Strong No'].includes(interview.feedback.recommendation)
                                             }">{{ interview.feedback.recommendation }}</span>
                                             <button @click="openFeedback(interview)" class="ml-auto text-indigo-600 hover:underline">Edit Feedback</button>
                                         </div>
                                         <div v-else class="mb-3">
                                              <button @click="openFeedback(interview)" class="text-xs text-indigo-600 hover:underline flex items-center">
                                                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                  Add Detailed Feedback
                                              </button>
                                         </div>
                                         
                                         <!-- Quick Actions (Always visible if Completed) -->
                                         <div class="flex items-center space-x-2">
                                             <button v-if="!['Rejected', 'Offer', 'Hire', 'Shortlisted'].includes(displayCandidate?.status)" 
                                                     @click="openAddInterview" 
                                                     class="flex-1 bg-green-50 text-green-700 px-2 py-1.5 rounded text-xs border border-green-200 hover:bg-green-100 flex items-center justify-center font-medium transition-colors">
                                                 <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                 Mark Cleared / Shortlist
                                             </button>
                                             <button v-if="!['Rejected', 'Offer'].includes(displayCandidate?.status)" 
                                                     @click="showRejectionModal = true" 
                                                     class="flex-1 bg-red-50 text-red-700 px-2 py-1.5 rounded text-xs border border-red-200 hover:bg-red-100 flex items-center justify-center font-medium transition-colors">
                                                 <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                 Reject Candidate
                                             </button>
                                         </div>
                                    </div>

                                    <div class="mt-3 flex items-center justify-between">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-sm font-medium" 
                                              :class="{
                                                  'bg-yellow-100 text-yellow-800': interview.status === 'Scheduled',
                                                  'bg-green-100 text-green-800': interview.status === 'Completed',
                                                  'bg-red-100 text-red-800': interview.status === 'Cancelled'
                                              }">
                                            {{ interview.status }}
                                        </span>
                                        
                                        <!-- Reminder Stats -->
                                        <div v-if="interview.reminder_count > 0" class="text-sm text-gray-400 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Sent: {{ interview.reminder_count }}
                                        </div>
                                    </div>
                                </div>
                                <p v-if="interviews.length === 0" class="text-sm text-gray-500 italic text-center py-4">No interviews scheduled yet.</p>
                            </div>

                            <!-- Offer Tab -->
                            <div v-show="currentTab === 'Offer'" class="space-y-6">
                                <div v-if="!offer" class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Offer Created</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by creating an offer letter.</p>
                                    <div class="mt-6">
                                        <button @click="showOfferModal = true" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                            </svg>
                                            Create Offer
                                        </button>
                                    </div>
                                </div>

                                <div v-else class="space-y-6">
                                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                      :class="{
                                                          'bg-gray-100 text-gray-800': offer.status === 'Draft',
                                                          'bg-blue-100 text-blue-800': offer.status === 'Sent',
                                                          'bg-yellow-100 text-yellow-800': offer.status === 'Pending_Docs',
                                                          'bg-green-100 text-green-800': offer.status === 'Accepted',
                                                          'bg-red-100 text-red-800': offer.status === 'Rejected'
                                                      }">
                                                    {{ offer.status }}
                                                </span>
                                                <h3 class="mt-2 text-lg font-bold text-gray-900">{{ offer.salary_currency }} {{ Number(offer.salary_amount).toLocaleString() }}</h3>
                                                <p class="text-sm text-gray-500">Joining: {{ new Date(offer.joining_date).toLocaleDateString() }}</p>
                                            </div>
                                            <div class="flex space-x-2">
                                                <div v-if="offer.status !== 'Withdrawn' && offer.status !== 'Draft'" class="relative inline-block text-left">
                                                     <button @click="resendOffer(offer)" class="text-xs bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-2 py-1 rounded" title="Resend Email">
                                                        Resend
                                                     </button>
                                                </div>
                                                <a v-if="offer.status !== 'Withdrawn'" :href="offer.token ? route('portal.offer.show', offer.token) : '#'" target="_blank" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium flex items-center">
                                                    View Portal
                                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                                </a>
                                            </div>
                                        </div>

                                        <div class="mt-4 border-t border-gray-100 pt-4 flex space-x-3">
                                            <button v-if="offer.status === 'Draft'" @click="sendOffer(offer)" :disabled="sendingOffer" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-indigo-700">
                                                {{ sendingOffer ? 'Sending...' : 'Send Offer' }}
                                            </button>
                                            
                                            <template v-if="offer.status === 'Sent' || offer.status === 'Viewed' || offer.status === 'Accepted'">
                                                <button @click="openExtendModal(offer)" class="flex-1 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded text-sm font-medium hover:bg-gray-50">
                                                    Extend Validity
                                                </button>
                                                <button @click="withdrawOffer(offer)" class="flex-1 bg-white border border-red-300 text-red-700 px-4 py-2 rounded text-sm font-medium hover:bg-red-50">
                                                    Withdraw
                                                </button>
                                            </template>

                                            <button v-if="offer.status === 'Pending_Docs'" @click="releaseOffer(offer)" class="flex-1 bg-green-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-green-700 shadow-sm">
                                                Release Offer
                                            </button>

                                             <div v-if="offer.status === 'Withdrawn'" class="w-full bg-red-50 text-red-800 p-2 rounded text-center text-sm">
                                                Offer Withdrawn
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                                            <h4 class="text-sm font-bold text-gray-900">Document Verification</h4>
                                        </div>
                                        <ul role="list" class="divide-y divide-gray-200">
                                            <li v-if="!offer.documents || offer.documents.length === 0" class="px-4 py-4 text-sm text-gray-500 text-center">
                                                No documents requested.
                                            </li>
                                            <li v-for="doc in offer.documents" :key="doc.id" class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center min-w-0">
                                                        <div class="mr-3 flex-shrink-0">
                                                            <svg v-if="doc.status === 'Verified'" class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                            <svg v-else-if="doc.status === 'Submitted'" class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                            <svg v-else class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                        </div>
                                                        <div class="min-w-0 truncate">
                                                            <p class="text-sm font-medium text-indigo-600 truncate">{{ doc.name }}</p>
                                                            <p class="text-xs text-gray-500">{{ doc.status }} <span v-if="doc.is_mandatory" class="text-red-500">*</span></p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <a v-if="doc.file_path" :href="route('documents.stream', doc.id)" target="_blank" class="text-gray-400 hover:text-gray-600">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                        </a>
                                                        <div v-if="doc.status === 'Submitted'" class="flex space-x-1">
                                                            <button @click="verifyDoc(doc, 'Verified')" class="p-1 text-green-600 hover:bg-green-50 rounded" title="Approve"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></button>
                                                            <button @click="verifyDoc(doc, 'Rejected')" class="p-1 text-red-600 hover:bg-red-50 rounded" title="Reject"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div v-if="doc.status === 'Rejected' && doc.rejection_reason" class="mt-2 text-xs text-red-600 bg-red-50 p-2 rounded">
                                                    Reason: {{ doc.rejection_reason }}
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Right Pane: Resume Preview -->
                        <div class="w-2/3 bg-gray-100 flex flex-col">
                            <div v-if="displayCandidate?.resume_url" class="flex-1 relative">
                                <iframe :src="displayCandidate.resume_url + '#view=FitH'" class="w-full h-full" frameborder="0"></iframe>
                            </div>
                            <div v-else class="flex-1 flex flex-col items-center justify-center text-gray-400">
                                <svg class="h-16 w-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p>No Resume Uploaded</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <InterviewModal 
            :show="showInterviewModal"
            :candidate-name="displayCandidate?.name"
            :interview="selectedInterview"
            :users="users"
            :initial-round="nextRound"
            :application-id="details?.application_id || (interviews.length > 0 ? interviews[0].job_application_id : null)"
            @close="closeInterviewModal"
            @success="fetchData"
        />

        <RejectionModal 
            :show="showRejectionModal" 
            :application-id="details?.application_id"
            @close="showRejectionModal = false"
            @success="fetchData"
        />

        <OfferModal 
            :show="showOfferModal" 
            :candidate="displayCandidate"
            :application-id="details?.application_id || displayCandidate?.application_id"
            :templates="offerTemplates"
            :salary-structures="salaryStructures"
            :users="users"
            :offer-summary="offer"
            @close="showOfferModal = false"
            @success="fetchData"
        />

        <FeedbackModal 
            :show="showFeedbackModal" 
            :interview-id="selectedInterviewForFeedback?.id"
            :existing-feedback="selectedInterviewForFeedback?.feedback"
            @close="showFeedbackModal = false"
            @success="fetchData"
            @scheduleNext="openAddInterview" 
        />

        <CancellationModal 
            :show="showCancellationModal"
            :interview-id="selectedInterviewForCancellation?.id"
            @close="showCancellationModal = false"
            @success="fetchData"
        />

        <ScreeningFeedbackModal
            :show="showScreeningFeedbackModal"
            :candidate-id="displayCandidate?.id"
            :existing-rating="displayCandidate?.screening_rating"
            :existing-feedback="displayCandidate?.screening_feedback"
            @close="showScreeningFeedbackModal = false"
            @success="fetchData"
        />
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import InterviewModal from './InterviewModal.vue';
import RejectionModal from './RejectionModal.vue';
import OfferModal from './OfferModal.vue';
import FeedbackModal from './FeedbackModal.vue';
import ScreeningFeedbackModal from './ScreeningFeedbackModal.vue';
import CancellationModal from './CancellationModal.vue';

const props = defineProps({
    show: Boolean,
    candidate: Object, // Basic info passed from parent
    users: Array, // Need to pass this from parent!
    initialTab: { type: String, default: 'overview' },
    autoOpenFeedback: { type: Boolean, default: false }
});

const emit = defineEmits(['close']);

const tabs = ['Overview', 'Timeline', 'Interviews', 'Offer'];
const currentTab = ref('Overview');
const loading = ref(false);
const details = ref(null); // Stores full data from API
const timeline = ref([]);
const interviews = ref([]);
const offer = ref(null);
const offerTemplates = ref([]);
const salaryStructures = ref([]);

// Smart Defaults
const nextRound = computed(() => {
    return 'Round ' + ((interviews.value?.length || 0) + 1);
});

// Interview Modal State
const showInterviewModal = ref(false);
const selectedInterview = ref(null);
const reminderLoading = ref(null);

// ATS Actions State
const showRejectionModal = ref(false);
const showOfferModal = ref(false);
const showFeedbackModal = ref(false);
const showCancellationModal = ref(false);
const showScreeningFeedbackModal = ref(false);
const selectedInterviewForFeedback = ref(null);
const selectedInterviewForCancellation = ref(null);

const close = () => {
    emit('close');
};

const fetchData = async () => {
    if (!props.candidate?.id) return;
    
    loading.value = true;
    try {
        const response = await axios.get(route('talent.candidates.quick-view', props.candidate.id));
        details.value = response.data.candidate;
        timeline.value = response.data.timeline;
        interviews.value = response.data.interviews;
        offer.value = response.data.offer;
        offer.value = response.data.offer;
        offerTemplates.value = response.data.offerTemplates;
        salaryStructures.value = response.data.salaryStructures;
        
        // Auto Open Feedback if Requested
        if (props.autoOpenFeedback) {
            // Find the pending interview for current user
            // We need current user ID - we can get it from page props or assume we iterate
            // Ideally we check 'pending_feedback' logic similar to backend or just find first Scheduled for this user
            // For now, let's find the first 'Scheduled' one where interviewer is me (if we had ID) or just the first Scheduled one if in 'My Interviews' context
            // Better: Find the one that matches the backend 'pending_feedback' logic if possible.
            // Simplified: Find first Scheduled interview.
             const pending = interviews.value.find(i => i.status === 'Scheduled');
             if (pending) {
                 openFeedback(pending);
             }
        }
    } catch (error) {
        console.error('Failed to fetch candidate details', error);
    } finally {
        loading.value = false;
    }
};

watch(() => props.show, (newVal) => {
    if (newVal && props.candidate) {
        // Reset and fetch
        details.value = null;
        fetchData();
        currentTab.value = 'Overview'; // Default tab
    }
});

// Computed to merge props and details (prefer details if available)
const displayCandidate = computed(() => details.value || props.candidate);

// Interview Actions
const openAddInterview = () => {
    selectedInterview.value = null;
    showInterviewModal.value = true;
};

const editInterview = (interview) => {
    selectedInterview.value = interview;
    showInterviewModal.value = true;
};

const closeInterviewModal = () => {
    showInterviewModal.value = false;
    selectedInterview.value = null;
};



const sendReminder = async (interview) => {
    if (!confirm(`Send interview reminder to ${displayCandidate.value.email}?`)) return;
    
    reminderLoading.value = interview.id;
    try {
        await router.post(route('talent.candidates.interviews.remind', interview.id), {}, {
              onFinish: () => reminderLoading.value = null,
              onSuccess: () => fetchData()
        });
    } catch (e) {
        reminderLoading.value = null;
    }
};

const markCompleted = async (interview) => {
    if (!confirm('Mark this interview as Completed?')) return;
    
    try {
        await router.put(route('talent.candidates.interviews.update', interview.id), {
            ...interview, // Keep existing details
            status: 'Completed'
        }, {
            onSuccess: () => {
                fetchData();
                // Optional: Open feedback modal immediately
                setTimeout(() => openFeedback(interview), 500);
            }
        });
    } catch (e) {
        console.error('Failed to mark completed', e);
    }
};

const openFeedback = (interview) => {
    selectedInterviewForFeedback.value = interview;
    showFeedbackModal.value = true;
};

const openCancellation = (interview) => {
    selectedInterviewForCancellation.value = interview;
    showCancellationModal.value = true;
};

// Screening Actions
const updateScreeningRating = (rating) => {
    if (!details.value && !props.candidate) return;
    const candidateId = details.value?.id || props.candidate.id;
    
    // Optimistic Update
    if (details.value) details.value.screening_rating = rating;

    axios.post(route('talent.candidates.rate-screening', candidateId), {
        rating: rating
    }).then(() => {
        // Silent success or toast
    }).catch(err => {
        console.error(err);
        // Revert on fail
        if (details.value) details.value.screening_rating = 0; // or previous
    });
};

const updateScreeningFeedback = (feedback) => {
    if (!details.value && !props.candidate) return;
    const candidateId = details.value?.id || props.candidate.id;

    if (details.value) details.value.screening_feedback = feedback;

    axios.post(route('talent.candidates.rate-screening', candidateId), {
        feedback: feedback
    });
};

const sendingOffer = ref(false);

const sendOffer = (offerItem) => {
    if (!confirm('This will change status to "Sent". Continue?')) return;
    sendingOffer.value = true;
    router.post(route('talent.offers.send', offerItem.id), {}, {
        onFinish: () => sendingOffer.value = false,
        onSuccess: () => fetchData()
    });
};

const withdrawOffer = (offerItem) => {
    if (!confirm('Are you sure you want to WITHDRAW this offer? The candidate link will stop working immediately.')) return;
    router.post(route('talent.offers.withdraw', offerItem.id), {}, {
        onSuccess: () => fetchData()
    });
};

const resendOffer = (offerItem) => {
    if (!confirm('Resend the offer email to the candidate?')) return;
    router.post(route('talent.offers.resend', offerItem.id), {}, {
         onSuccess: () => fetchData()
    });
};

const openExtendModal = (offerItem) => {
    // Simple Prompt for speed, better UX would be a small modal with DatePicker
    const newDate = prompt("Enter new expiry date (YYYY-MM-DD):", offerItem.expiry_date);
    if (!newDate) return;
    
    router.post(route('talent.offers.extend', offerItem.id), { expiry_date: newDate }, {
        onSuccess: () => fetchData()
    });
};

const releaseOffer = (offerItem) => {
    if (!confirm('This will release the offer to the candidate. They will now be able to view the offer letter. Continue?')) return;
    router.post(route('talent.offers.release', offerItem.id), {}, {
        onSuccess: () => fetchData()
    });
};

const verifyDoc = (doc, status) => {
    let reason = null;
    if (status === 'Rejected') {
        reason = prompt('Reason for rejection:');
        if (!reason && reason !== '') return; // Allow empty reason? No.
    }
    
    router.post(route('talent.offers.documents.verify', doc.id), { status, reason }, {
        onSuccess: () => fetchData()
    });
};

</script>
