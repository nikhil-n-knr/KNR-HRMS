<template>
    <div class="h-screen flex flex-col md:flex-row overflow-hidden bg-gray-100 font-sans text-gray-900">
        <!-- Confetti Canvas -->
        <canvas id="confetti-canvas" class="fixed inset-0 pointer-events-none z-50"></canvas>

        <!-- LEFT SIDEBAR: Context & Actions (Fixed width, scrollable) -->
        <aside class="w-full md:w-[400px] flex-shrink-0 flex flex-col bg-white border-b md:border-b-0 md:border-r border-gray-200 shadow-xl z-20 max-h-[40vh] md:max-h-full">
            <!-- Brand Header -->
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 bg-gradient-to-br from-indigo-600 to-violet-600 rounded-xl flex items-center justify-center text-white font-bold shadow-sm">
                        N
                    </div>
                    <div>
                         <h1 class="text-lg font-bold text-gray-900 leading-none">Nikhil Infotec</h1>
                         <p class="text-xs text-gray-500 mt-1 font-medium">Official Offer Portal</p>
                    </div>
                </div>
            </div>

            <!-- Scrollable Content Area -->
            <div class="flex-1 overflow-y-auto p-6 space-y-8">
                
                <!-- Welcome -->
                <div>
                     <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-bold text-indigo-700 mb-3 border border-indigo-100">
                        {{ offer.job_application?.job?.title || 'Job Offer' }}
                     </span>
                     <h2 class="text-2xl font-bold text-gray-900">Hello, {{ offer.job_application?.candidate?.first_name }}! 👋</h2>
                     <p class="text-gray-500 mt-2 text-sm leading-relaxed">
                        We are excited to invite you to the team. Please review your offer letter on the right.
                     </p>
                </div>

                <!-- Status Card -->
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</span>
                        <span class="text-xs font-medium text-gray-400">Expires: {{ new Date(offer.expiry_date).toLocaleDateString() }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                         <div class="h-10 w-10 rounded-full flex items-center justify-center text-white font-bold shadow-sm" :class="statusColor.bg">
                           <component :is="statusInfo.icon" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">{{ statusInfo.text }}</p>
                            <p class="text-xs text-gray-500">{{ statusInfo.subtext }}</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons (Main) -->
                 <div v-if="canRespond" class="grid grid-cols-2 gap-3">
                    <button @click="reject" class="flex justify-center items-center px-4 py-3 border border-gray-300 shadow-sm text-sm font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-all hover:-translate-y-0.5">
                        Decline
                    </button>
                    <button @click="openAcceptModal" :disabled="isLocked" class="flex justify-center items-center px-4 py-3 border border-transparent shadow-md text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all hover:-translate-y-0.5 hover:shadow-lg">
                        Accept Offer
                    </button>
                </div>
                <div v-if="isLocked && canRespond" class="p-3 bg-amber-50 rounded-lg text-xs text-amber-800 border border-amber-100 flex gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span>Please complete the <strong>Mandatory Documents</strong> below to unlock acceptance.</span>
                </div>

                <!-- Document Checklist -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Onboarding Checklist</h3>
                        <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-0.5 rounded-full">{{ completedDocs }}/{{ offer.documents.length }}</span>
                    </div>
                    
                    <div class="space-y-3">
                         <div v-if="offer.documents.length === 0" class="text-center py-4 text-sm text-gray-400 border border-dashed border-gray-200 rounded-lg">No documents required.</div>

                        <div v-for="doc in offer.documents" :key="doc.id" class="group bg-white rounded-lg border border-gray-200 p-3 hover:border-indigo-400 transition-all shadow-sm">
                             <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <p class="text-sm font-semibold text-gray-900 truncate" :title="doc.name">{{ doc.name }}</p>
                                        <span v-if="doc.is_mandatory" class="h-1.5 w-1.5 rounded-full bg-red-500" title="Mandatory"></span>
                                    </div>
                                    <p class="text-xs font-medium mt-0.5" :class="getStatusColor(doc.status)">{{ doc.status }}</p>
                                </div>

                                <!-- Action -->
                                <div class="flex-shrink-0">
                                     <svg v-if="doc.status === 'Verified'" class="w-6 h-6 text-green-500 bg-green-50 rounded-full p-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    
                                     <label v-else-if="['Pending', 'Rejected'].includes(doc.status)" class="cursor-pointer inline-flex items-center justify-center h-8 w-8 rounded-full bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all">
                                        <svg v-if="uploading === doc.id" class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                        <input type="file" class="hidden" @change="e => uploadFile(e, doc)" :disabled="uploading === doc.id" accept=".pdf,.png,.jpg,.jpeg">
                                    </label>

                                     <svg v-else class="w-6 h-6 text-blue-500 bg-blue-50 rounded-full p-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                             </div>
                             <!-- Rejection Reason -->
                            <div v-if="doc.rejection_reason" class="mt-2 text-sm leading-tight text-red-600 bg-red-50 p-1.5 rounded">
                                <strong>Rejection:</strong> {{ doc.rejection_reason }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div v-if="timeline?.length">
                     <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4">Journey</h3>
                     <div class="relative pl-2 border-l border-gray-200 space-y-6">
                        <div v-for="(event, idx) in timeline" :key="idx" class="pl-4 relative">
                            <div class="absolute -left-[5px] top-1.5 h-2.5 w-2.5 rounded-full border-2 border-white" :class="event.color === 'green' ? 'bg-green-500' : 'bg-gray-300'"></div>
                            <p class="text-sm font-semibold text-gray-900">{{ event.title }}</p>
                            <p class="text-xs text-gray-500">{{ event.date }}</p>
                        </div>
                     </div>
                </div>

            </div>
            
            <!-- Sticky Footer (User Profile) -->
            <div class="p-4 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                     <div class="h-8 w-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-bold text-xs">
                        {{ offer.job_application?.candidate?.first_name?.charAt(0) || 'U' }}
                    </div>
                    <div class="min-w-0">
                         <p class="text-sm font-bold text-gray-900 truncate">{{ offer.job_application?.candidate?.first_name }} {{ offer.job_application?.candidate?.last_name }}</p>
                         <p class="text-xs text-gray-500 truncate">{{ offer.job_application?.candidate?.email }}</p>
                    </div>
                </div>
                <a :href="route('portal.offer.download', offer.token)" class="text-gray-400 hover:text-indigo-600 transition-colors" title="Download PDF">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                </a>
            </div>
        </aside>

        <!-- RIGHT MAIN: Document Viewer -->
        <main class="flex-1 relative bg-[#F3F4F6] overflow-y-auto items-start justify-center p-4 md:p-8 lg:p-12 cursor-grab active:cursor-grabbing" ref="scrollContainer">
            
            <!-- Shadow for depth -->
            <div class="relative transition-transform duration-300 ease-in-out transform origin-top" :style="{ transform: `scale(${zoom})` }">
                
                <!-- LOCKED OVERLAY -->
                <div v-if="isLocked" class="absolute inset-0 z-50 bg-white/60 backdrop-blur-[2px] rounded flex items-center justify-center">
                    <div class="bg-white p-8 rounded-2xl shadow-2xl max-w-sm text-center border border-gray-100">
                         <div class="mx-auto h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4 text-gray-400">
                             <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                         </div>
                         <h3 class="text-xl font-bold text-gray-900">Document Locked</h3>
                         <p class="text-gray-500 mt-2 text-sm">Please complete the mandatory items in the checklist to view your full offer.</p>
                    </div>
                </div>

                <!-- PDF / CONTENT Render -->
                <div class="bg-white shadow-xl md:shadow-2xl min-h-[600px] md:min-h-[1123px] w-full md:w-[794px] mx-auto relative overflow-hidden flex flex-col rounded-lg md:rounded-none">
                    <!-- Manual PDF Warning -->
                    <!-- Manual PDF Warning / Embedded View -->
                    <div v-if="offer.manual_path && !isLocked" class="flex-1 flex flex-col items-center justify-center bg-gray-50 h-full w-full relative">
                         <iframe :src="'/storage/' + offer.manual_path + '#toolbar=0&navpanes=0&scrollbar=0'" class="w-full h-full absolute inset-0 border-none" title="Offer Letter PDF"></iframe>
                    </div>

                    <!-- Dynamic HTML Content -->
                    <div v-else-if="!isLocked" class="flex-1 flex flex-col relative">
                         <!-- Watermark -->
                         <div v-if="templateConfig?.watermark_text || templateConfig?.watermark_image" class="absolute inset-0 z-0 flex items-center justify-center pointer-events-none opacity-[0.06] overflow-hidden">
                             <img v-if="templateConfig.watermark_image" :src="'/storage/' + templateConfig.watermark_image" class="w-2/3 object-contain transform -rotate-12" alt="Watermark">
                             <h1 v-else class="text-8xl font-black text-gray-900 transform -rotate-45 uppercase tracking-widest border-8 border-gray-900 p-8 rounded-3xl">
                                {{ templateConfig.watermark_text }}
                             </h1>
                        </div>

                        <!-- Header -->
                        <header v-if="templateConfig?.header_html || templateConfig?.header_image" class="px-6 py-6 md:px-12 md:py-10 z-10">
                            <img v-if="templateConfig.header_image" :src="'/storage/' + templateConfig.header_image" class="max-h-24 object-contain" />
                            <div v-if="templateConfig.header_html" class="prose prose-sm max-w-none text-gray-600" v-html="templateConfig.header_html"></div>
                        </header>

                        <!-- Body -->
                        <div class="flex-1 px-6 py-4 md:px-12 z-10 prose prose-indigo max-w-none text-gray-900 leading-relaxed font-serif text-justify text-sm md:text-base" v-html="content"></div>

                        <!-- Footer -->
                        <footer v-if="templateConfig?.footer_html || templateConfig?.footer_image" class="px-12 py-8 mt-auto z-10 text-center">
                            <img v-if="templateConfig.footer_image" :src="'/storage/' + templateConfig.footer_image" class="max-h-16 mx-auto object-contain mb-2" />
                            <div v-if="templateConfig.footer_html" class="prose prose-xs max-w-none text-gray-400 mx-auto" v-html="templateConfig.footer_html"></div>
                        </footer>

                        <!-- Integrated Action Button (Bottom of Paper) -->
                        <div v-if="canRespond" class="px-12 pb-12 pt-4 text-center z-10 print:hidden">
                            <button @click="openAcceptModal" class="inline-flex items-center gap-2 px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white text-base font-bold rounded-full shadow-lg hover:shadow-xl transition-all hover:-translate-y-1 transform">
                                <span>Accept & Sign Offer</span>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                            </button>
                            <p class="text-xs text-gray-400 mt-2">By clicking, you will be prompted to sign digitally.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Zooom Controls -->
            <div class="hidden md:flex fixed bottom-8 right-8 flex-col gap-2 bg-white shadow-lg rounded-lg border border-gray-200 p-1 z-30">
                <button @click="zoomIn" class="p-2 hover:bg-gray-100 rounded text-gray-600"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg></button>
                <div class="text-center text-xs font-bold text-gray-400 py-1 border-t border-b border-gray-100">{{ Math.round(zoom * 100) }}%</div>
                <button @click="zoomOut" class="p-2 hover:bg-gray-100 rounded text-gray-600"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg></button>
            </div>

        </main>

        <!-- Modals -->
        <div v-if="showAcceptModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
             <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-70 backdrop-blur-sm transition-opacity" @click="showAcceptModal = false"></div>
                <!-- Modal Panel -->
                <div class="inline-block align-bottom bg-white rounded-2xl px-4 pt-5 pb-4 text-left shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-8 relative z-50">
                     <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-indigo-50 mb-4">
                            <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                         </div>
                         <h3 class="text-2xl font-bold text-gray-900">Accept Job Offer</h3>
                         <p class="text-sm text-gray-500 mt-2 mb-8">Great choice! Let's get you set up.</p>
                     </div>

                     <!-- Form -->
                     <div class="space-y-6">
                        <div>
                             <label class="font-bold text-sm text-gray-700">Select Workstation</label>
                             <select v-model="preferences.laptop" class="mt-1 block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-lg bg-gray-50">
                                <option v-for="opt in laptopOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                            </select>
                        </div>
                        
                         <div>
                             <label class="font-bold text-sm text-gray-700 block mb-2">T-Shirt Size</label>
                             <div class="flex gap-2">
                                <button v-for="size in sizeOptions" :key="size" @click="preferences.tshirt = size" type="button" :class="preferences.tshirt === size ? 'bg-gray-900 text-white border-gray-900 ring-2 ring-gray-900 ring-offset-2' : 'bg-white text-gray-600 border-gray-300 hover:border-gray-400'" class="flex-1 h-10 rounded-lg border text-sm font-bold transition-all">{{ size }}</button>
                             </div>
                        </div>

                        <!-- Signature Board -->
                        <div>
                             <label class="font-bold text-sm text-gray-700 block mb-2">Digital Signature</label>
                             <div class="border-2 border-gray-200 rounded-xl bg-white shadow-inner overflow-hidden relative touch-none" style="height: 150px;">
                                 <canvas ref="signaturePad" class="absolute inset-0 cursor-crosshair w-full h-full"
                                    @mousedown="startDrawing" @mousemove="draw" @mouseup="stopDrawing" @mouseleave="stopDrawing"
                                    @touchstart.prevent="startDrawing" @touchmove.prevent="draw" @touchend.prevent="stopDrawing"></canvas>
                                 
                                 <div v-if="!isDrawing && !signatureData" class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-gray-300">
                                     <svg class="w-8 h-8 mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                     <span class="text-sm font-medium">Draw your signature here</span>
                                 </div>
                             </div>
                             <div class="flex justify-between mt-2">
                                 <p class="text-xs text-gray-400">Use mouse or finger to sign</p>
                                 <button @click="clearSignature" class="text-xs font-bold text-red-600 hover:text-red-800 uppercase tracking-wide">Clear Board</button>
                             </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-2">
                             <button type="button" class="w-full inline-flex justify-center rounded-xl border border-gray-300 px-4 py-3 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 sm:text-sm" @click="showAcceptModal = false">Cancel</button>
                             <button type="button" class="w-full inline-flex justify-center rounded-xl border border-transparent px-4 py-3 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 sm:text-sm shadow-lg hover:shadow-xl transition-all" @click="confirmAccept">Confirm & Join 🚀</button>
                        </div>
                     </div>
                </div>
             </div>
        </div>

        <div v-if="showCelebrationModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-90 backdrop-blur-sm transition-opacity" @click="showCelebrationModal = false"></div>
            <div class="bg-white rounded-3xl p-10 max-w-2xl w-full text-center relative z-10 shadow-2xl transform transition-all scale-100">
                 <div class="mx-auto h-24 w-24 bg-gradient-to-r from-yellow-200 to-yellow-500 rounded-full flex items-center justify-center mb-6 shadow-lg animate-bounce">
                     <span class="text-5xl">🎉</span>
                 </div>
                 <h2 class="text-4xl font-extrabold text-gray-900 tracking-tight">Welcome Aboard!</h2>
                 <p class="text-xl text-gray-600 mt-4 max-w-lg mx-auto">We are absolutely thrilled to have you join the team, {{ offer.job_application?.candidate?.first_name }}!</p>
                 
                 <div class="mt-10 p-6 bg-gray-50 rounded-2xl border border-gray-100">
                     <h4 class="font-bold text-gray-900 uppercase tracking-wide text-sm mb-4">Your Next Steps</h4>
                     <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
                         <div class="flex flex-col"><span class="text-2xl font-black text-indigo-200 mb-1">01</span><span class="font-bold text-gray-900">HR Review</span><span class="text-xs text-gray-500">Docs verification</span></div>
                         <div class="flex flex-col"><span class="text-2xl font-black text-indigo-200 mb-1">02</span><span class="font-bold text-gray-900">IT Setup</span><span class="text-xs text-gray-500">Assets dispatch</span></div>
                         <div class="flex flex-col"><span class="text-2xl font-black text-indigo-200 mb-1">03</span><span class="font-bold text-gray-900">Join Day</span><span class="text-xs text-gray-500">{{ offer.joining_date_formatted }}</span></div>
                     </div>
                 </div>

                 <button @click="showCelebrationModal = false" class="mt-8 px-8 py-3 bg-gray-900 text-white font-bold rounded-full hover:bg-gray-800 transition-all hover:scale-105 shadow-xl">Detailed Onboarding Plan &rarr;</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import confetti from 'canvas-confetti';
import { CheckCircleIcon, XCircleIcon, ClockIcon, PaperClipIcon } from '@heroicons/vue/24/solid'; // Assumes heroicons installed or use SVGs

const props = defineProps({
    offer: Object,
    content: String,
    team_members: Array,
    timeline: Array,
    hasPendingMandatory: Boolean,
    templateConfig: Object
});

const uploading = ref(null);
const showAcceptModal = ref(false);
const showCelebrationModal = ref(false);
const signaturePad = ref(null);
const isDrawing = ref(false);
const signatureData = ref(null);
const zoom = ref(1);

const zoomIn = () => zoom.value = Math.min(zoom.value + 0.1, 1.5);
const zoomOut = () => zoom.value = Math.max(zoom.value - 0.1, 0.5);

// Preferences
const preferences = ref({ laptop: 'MacBook Pro 14"', tshirt: 'L' });
const laptopOptions = [
    { label: 'MacBook Pro 14" (M3 Pro)', value: 'MacBook Pro 14"' },
    { label: 'MacBook Air 15" (M3)', value: 'MacBook Air 15"' },
    { label: 'Dell XPS 15 (Windows)', value: 'Dell XPS 15' },
    { label: 'Lenovo ThinkPad X1', value: 'Lenovo ThinkPad X1' }
];
const sizeOptions = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

// Computed
const isLocked = computed(() => props.offer.status === 'Pending_Docs' || props.hasPendingMandatory);
const canRespond = computed(() => (props.offer.status === 'Sent' || props.offer.status === 'Viewed') && !isLocked.value);
const completedDocs = computed(() => props.offer.documents ? props.offer.documents.filter(d => ['Submitted', 'Verified'].includes(d.status)).length : 0);

const statusInfo = computed(() => {
    switch(props.offer.status) {
        case 'Accepted': return { text: 'Accepted', subtext: 'Welcome aboard!', icon: 'CheckCircleIcon' };
        case 'Rejected': return { text: 'Declined', subtext: 'Offer declined', icon: 'XCircleIcon' };
        case 'Sent': 
        case 'Viewed': return { text: 'Active', subtext: 'Action required', icon: 'ClockIcon' };
        default: return { text: props.offer.status, subtext: 'Pending', icon: 'ClockIcon' };
    }
});

const statusColor = computed(() => {
    switch(props.offer.status) {
        case 'Accepted': return { bg: 'bg-green-500 text-white' };
        case 'Rejected': return { bg: 'bg-red-500 text-white' };
        default: return { bg: 'bg-indigo-500 text-white' };
    }
});

const getStatusColor = (status) => {
     switch(status) {
        case 'Pending': return 'text-amber-600';
        case 'Submitted': return 'text-blue-600';
        case 'Verified': return 'text-green-600';
        case 'Rejected': return 'text-red-600';
        default: return 'text-gray-500';
    }
}

// Signature Logic
const startDrawing = (e) => { isDrawing.value = true; draw(e); };
const stopDrawing = () => { isDrawing.value = false; if(signaturePad.value) signatureData.value = signaturePad.value.toDataURL();  };
const draw = (e) => {
    if (!isDrawing.value) return;
    const canvas = signaturePad.value;
    const ctx = canvas.getContext('2d');
    
    // Correct coordinates for canvas vs client
    const rect = canvas.getBoundingClientRect();
    const x = (e.clientX || e.touches[0].clientX) - rect.left;
    const y = (e.clientY || e.touches[0].clientY) - rect.top;
    
    // Scale coordinates if canvas resolution differs from CSS size
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;

    ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.strokeStyle = '#000';
    ctx.lineTo(x * scaleX, y * scaleY); 
    ctx.stroke(); 
    ctx.beginPath(); 
    ctx.moveTo(x * scaleX, y * scaleY);
};

const clearSignature = () => {
    if(!signaturePad.value) return;
    const canvas = signaturePad.value; 
    canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height); 
    signatureData.value = null; 
    canvas.getContext('2d').beginPath();
};

const openAcceptModal = () => { 
    showAcceptModal.value = true; 
    setTimeout(() => { 
        if(signaturePad.value) {
            // Resize canvas to match display - CRITICALLY IMPORTANT for drawing acccuracy
            signaturePad.value.width = signaturePad.value.offsetWidth;
            signaturePad.value.height = signaturePad.value.offsetHeight;
        } 
    }, 100); 
};
const confirmAccept = () => {
    if (!signatureData.value) return alert('Please sign to proceed.');
    if (confirm('Confirm acceptance?')) {
        router.post(route('portal.offer.update', props.offer.token), {
            action: 'accept', signature: signatureData.value, preferences: preferences.value
        }, { onSuccess: () => { showAcceptModal.value = false; triggerCelebration(); } });
    }
};

const triggerCelebration = () => {
    showCelebrationModal.value = true;
    const end = Date.now() + 5000;
    const frame = () => {
        confetti({ particleCount: 5, angle: 60, spread: 55, origin: { x: 0 } });
        confetti({ particleCount: 5, angle: 120, spread: 55, origin: { x: 1 } });
        if (Date.now() < end) requestAnimationFrame(frame);
    };
    frame();
};

const reject = () => {
    const reason = prompt('Reason for declining:');
    if (reason) router.post(route('portal.offer.update', props.offer.token), { action: 'reject', reason });
};

const uploadFile = (event, doc) => {
    const file = event.target.files[0];
    if (!file) return;
    uploading.value = doc.id;
    const fd = new FormData(); fd.append('file', file);
    router.post(route('portal.offer.documents.upload', { token: props.offer.token, documentRequest: doc.id }), fd, {
        onFinish: () => uploading.value = null, forceFormData: true
    });
};
</script>

<style scoped>
/* Optional Custom Scrollbar for sidebars */
aside::-webkit-scrollbar { width: 4px; }
aside::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 4px; }
main::-webkit-scrollbar { width: 8px; }
main::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
</style>
