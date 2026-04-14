<template>
    <div class="w-full font-inter text-slate-900 bg-white p-2 animate-in fade-in zoom-in duration-500">
        <!-- Compact Header with Chat Toggle -->
        <div class="flex items-center justify-between mb-8 px-4">
            <div>
                <h2 class="text-xl font-black tracking-tighter italic">Signal Broadcast Terminal</h2>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Satellite Intel Transmission Pipeline</p>
            </div>
            <button @click="emit('chat')" class="flex items-center gap-3 px-4 py-2 bg-emerald-500/10 text-emerald-600 rounded-xl hover:bg-emerald-600 hover:text-white transition-all group border border-emerald-500/20">
                <ChatBubbleLeftRightIcon class="w-4 h-4 group-hover:scale-110 transition-transform" />
                <span class="text-[10px] font-black uppercase tracking-widest">Live Support Chat</span>
            </button>
        </div>

        <!-- Progress Stepper (Compact) -->
        <div class="flex items-center gap-4 mb-8 px-4">
            <div v-for="i in 3" :key="i" class="flex-1 h-1.5 rounded-full transition-all duration-700"
                 :class="currentStep >= i ? 'bg-emerald-500 shadow-lg shadow-emerald-500/20' : 'bg-slate-100'"></div>
        </div>

        <!-- Wizard Content -->
        <div class="min-h-[400px] flex flex-col">
            <transition name="pop" mode="out-in">
                <!-- Step 1: Target Selector -->
                <div v-if="currentStep === 1" key="step1" class="space-y-6">
                    <div class="space-y-2 px-2 text-center">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 italic flex items-center justify-center gap-2">
                            Select Targeted Sector
                            <div class="group/info relative inline-block">
                                <span class="h-4 w-4 rounded-full border border-slate-200 flex items-center justify-center text-[8px] italic cursor-help">i</span>
                                <div class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 w-48 p-3 bg-slate-900 text-[9px] text-white rounded-xl opacity-0 group-hover:opacity-100 transition-opacity z-50 shadow-2xl font-inter pointer-events-none tracking-normal normal-case">
                                    Choose the specific project and technical area where you encountered the anomaly. General Intel is for high-level project queries.
                                </div>
                            </div>
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div v-for="project in projects" :key="project.id" class="space-y-3">
                            <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">{{ project.name }}</h4>
                            <div class="flex flex-col gap-2">
                                <!-- General Option -->
                                <button @click="selectModule(project, null)"
                                        :class="['p-4 rounded-2xl border-2 text-left transition-all duration-300 flex items-center gap-4 group', form.project_id === project.id && form.module_id === null ? 'border-emerald-500 bg-emerald-50/50' : 'border-slate-50 bg-white hover:border-slate-200 shadow-sm']">
                                    <div class="h-10 w-10 rounded-xl bg-slate-900 flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform">
                                        <BoltIcon class="w-5 h-5 text-emerald-400" />
                                    </div>
                                    <div class="truncate">
                                        <div class="text-[11px] font-black text-slate-900 truncate">General Intelligence</div>
                                        <div class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none mt-1">Foundational Project Feedback</div>
                                    </div>
                                </button>

                                <button v-for="module in project.modules" :key="module.id" 
                                        @click="selectModule(project, module)"
                                        :class="['p-4 rounded-2xl border-2 text-left transition-all duration-300 flex items-center gap-4 group', form.module_id === module.id ? 'border-emerald-500 bg-emerald-50/50' : 'border-slate-50 bg-white hover:border-slate-200 shadow-sm']">
                                    <div class="h-10 w-10 rounded-xl bg-emerald-500 flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform">
                                        <Squares2X2Icon class="w-5 h-5 text-slate-900" />
                                    </div>
                                    <div class="truncate">
                                        <div class="text-[11px] font-black text-slate-900 truncate">{{ module.name }}</div>
                                        <div class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-1">Specific Functional Unit</div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Evidence Matrix -->
                <div v-else-if="currentStep === 2" key="step2" class="space-y-6 pb-2">
                    <div class="space-y-4">
                        <div class="px-1">
                            <label class="flex items-center gap-2 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 italic">
                                Signal Subject Line
                                <div class="group/info relative inline-block">
                                    <span class="text-slate-300 text-[10px] cursor-help">?</span>
                                    <div class="absolute left-0 bottom-full mb-2 w-48 p-3 bg-slate-900 text-[9px] text-white rounded-xl opacity-0 group-hover:opacity-100 transition-opacity z-50 shadow-2xl font-inter pointer-events-none normal-case tracking-normal">
                                        Briefly describe the anomaly. Use clear terms like "Login Error" or "Display Issue".
                                    </div>
                                </div>
                            </label>
                            <input v-model="form.subject" type="text" placeholder="e.g. BTC.NODE: CRITICAL_OVERLOAD" class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-4 text-xs font-black text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-500 transition-all font-mono" />
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="px-1">
                                <label class="flex items-center gap-2 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 italic">
                                    Complete Behavioral Log
                                    <div class="group/info relative inline-block">
                                        <span class="text-slate-300 text-[10px] cursor-help">?</span>
                                        <div class="absolute left-0 bottom-full mb-2 w-48 p-3 bg-slate-900 text-[9px] text-white rounded-xl opacity-0 group-hover:opacity-100 transition-opacity z-50 shadow-2xl font-inter pointer-events-none normal-case tracking-normal">
                                            What happened? What were you doing? This helps our engineers replicate and fix the issue faster.
                                        </div>
                                    </div>
                                </label>
                                <textarea v-model="form.description" rows="8" placeholder="Provide raw logs or behavioral anomalies..." class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-4 text-[11px] font-bold text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-500 transition-all resize-none"></textarea>
                            </div>
                            
                            <div class="space-y-4 px-1">
                                <label class="flex items-center gap-2 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 italic">
                                    Visual Capture (Media)
                                    <div class="group/info relative inline-block">
                                        <span class="text-slate-300 text-[10px] cursor-help">?</span>
                                        <div class="absolute right-0 bottom-full mb-2 w-48 p-3 bg-slate-900 text-[9px] text-white rounded-xl opacity-0 group-hover:opacity-100 transition-opacity z-50 shadow-2xl font-inter pointer-events-none normal-case tracking-normal">
                                            A screenshot or screen recording is worth a thousand lines of log code.
                                        </div>
                                    </div>
                                </label>
                                <div @click="$refs.fileInput.click()" class="h-44 border-2 border-dashed border-slate-200 rounded-[2rem] flex flex-col items-center justify-center transition-all cursor-pointer group bg-slate-50/50 hover:bg-white hover:border-emerald-400">
                                    <input type="file" ref="fileInput" class="hidden" multiple @change="handleFileSelect" />
                                    <CloudArrowUpIcon class="w-10 h-10 text-slate-300 group-hover:text-emerald-500 group-hover:scale-110 transition-all mb-3" />
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest group-hover:text-emerald-600">Transmit Evidence</p>
                                </div>

                                <div v-if="form.attachments.length > 0" class="flex flex-wrap gap-2 max-h-24 overflow-y-auto">
                                    <div v-for="(file, idx) in form.attachments" :key="idx" class="px-3 py-1.5 bg-slate-900 text-white rounded-xl flex items-center gap-2 text-[9px] font-black uppercase tracking-widest">
                                        <PhotoIcon class="w-3 h-3 text-emerald-400" />
                                        <span class="max-w-[100px] truncate italic">{{ file.original_name }}</span>
                                        <button @click.stop="removeFile(idx)" class="text-white/40 hover:text-white">×</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Deployment Logic -->
                <div v-else-if="currentStep === 3" key="step3" class="space-y-8 pb-4">
                    <div class="space-y-3 px-2">
                        <label class="flex items-center gap-2 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">
                            Operational Threat Level
                            <div class="group/info relative inline-block">
                                <span class="text-slate-300 text-[10px] cursor-help">?</span>
                                <div class="absolute left-0 bottom-full mb-2 w-48 p-3 bg-slate-900 text-[9px] text-white rounded-xl opacity-0 group-hover:opacity-100 transition-opacity z-50 shadow-2xl font-inter pointer-events-none normal-case tracking-normal">
                                    Critical: Blocking operations. Low: Minor cosmetic or non-functional anomaly.
                                </div>
                            </div>
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                             <button v-for="sev in ['low', 'medium', 'high', 'critical']" :key="sev" 
                                    @click="form.severity = sev"
                                    :class="['p-4 rounded-2xl border-2 text-center transition-all relative overflow-hidden group', form.severity === sev ? 'border-emerald-500 bg-emerald-50/50' : 'border-slate-50 hover:border-slate-200 bg-slate-50/20']">
                                <div :class="['h-2.5 w-2.5 rounded-full mx-auto mb-2 shadow-[0_0_8px]', sev === 'critical' ? 'bg-rose-500 shadow-rose-500/50' : sev === 'high' ? 'bg-amber-500 shadow-amber-500/50' : sev === 'medium' ? 'bg-sky-500 shadow-sky-500/50' : 'bg-emerald-500 shadow-emerald-500/50']"></div>
                                <span class="block text-[10px] font-black uppercase tracking-widest text-slate-900">{{ sev }}</span>
                                <div v-if="form.severity === sev" class="absolute top-1 right-1"><CheckIcon class="w-3 h-3 text-emerald-600 stroke-[4]" /></div>
                            </button>
                        </div>
                    </div>

                    <div class="bg-slate-900 rounded-[2.5rem] border border-white/10 p-8 flex flex-col md:flex-row items-center justify-between gap-8 relative overflow-hidden group shadow-2xl">
                         <div class="flex items-center gap-5 relative z-10 text-left">
                            <div class="h-14 w-14 bg-emerald-500 rounded-2xl flex items-center justify-center shadow-xl shadow-emerald-500/20">
                                <CpuChipIcon class="w-7 h-7 text-slate-900" />
                            </div>
                            <div>
                                <h4 class="text-xs font-black uppercase tracking-widest text-white">Encrypted Signature</h4>
                                <p class="text-[9px] text-emerald-400 font-bold uppercase tracking-widest mt-1 italic">Protocol: SSLV4-AES256</p>
                            </div>
                        </div>

                         <button @click="submitTicket" :disabled="isSubmitting" class="w-full md:w-auto px-12 py-5 bg-emerald-500 text-slate-900 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transform transition-all hover:scale-105 active:scale-95 disabled:opacity-50 flex items-center justify-center gap-3 shadow-2xl shadow-emerald-500/40 border-b-4 border-emerald-700">
                             <span v-if="!isSubmitting">Finalize Transmission</span>
                             <span v-else class="animate-pulse">Multiplexing...</span>
                             <PaperAirplaneIcon v-if="!isSubmitting" class="w-4 h-4" />
                         </button>
                    </div>
                </div>
            </transition>

            <!-- Navigation Controls (Sticky Footer) -->
            <div class="mt-auto pt-8 flex items-center justify-between">
                <button v-if="currentStep > 1" @click="currentStep--" class="px-6 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-all flex items-center gap-2">
                    <ArrowLeftIcon class="w-4 h-4" /> REVERSE
                </button>
                <div v-else></div>
                
                <button v-if="currentStep < 3" @click="nextStep" :disabled="!canProceed" class="px-8 py-4 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all flex items-center gap-3 active:scale-95 shadow-xl shadow-slate-900/10 disabled:opacity-30">
                    ANALYZE & FORWARD
                    <ArrowRightIcon class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { 
    Squares2X2Icon, 
    CloudArrowUpIcon, 
    CheckIcon, 
    ArrowRightIcon, 
    ArrowLeftIcon,
    PhotoIcon,
    BoltIcon,
    CpuChipIcon,
    PaperAirplaneIcon,
    XMarkIcon,
    ChatBubbleLeftRightIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';

const props = defineProps(['projects']);
const emit = defineEmits(['close', 'chat']);

const currentStep = ref(1);
const isSubmitting = ref(false);

const form = reactive({
    project_id: null,
    module_id: null,
    subject: '',
    description: '',
    severity: 'medium',
    attachments: [],
    environment: {
        agent: navigator.userAgent,
        width: window.innerWidth,
        height: window.innerHeight,
        platform: navigator.platform
    }
});

const canProceed = computed(() => {
    if (currentStep.value === 1) return form.project_id !== null;
    if (currentStep.value === 2) return form.subject.length > 5 && form.description.length > 5;
    return true;
});

const selectModule = (project, module) => {
    form.project_id = project.id;
    form.module_id = module ? module.id : null;
};

const nextStep = () => {
    if (canProceed.value) currentStep.value++;
};

const handleFileSelect = async (e) => {
    const files = Array.from(e.target.files);
    for (const file of files) {
        const formData = new FormData();
        formData.append('file', file);
        try {
            const { data } = await axios.post(route('portal.upload'), formData);
            form.attachments.push(data);
        } catch (e) {
            console.error("Upload failed", e);
        }
    }
};

const removeFile = (idx) => {
    form.attachments.splice(idx, 1);
};

const submitTicket = async () => {
    isSubmitting.value = true;
    try {
        await axios.post(route('portal.tickets.store'), form);
        emit('close');
    } catch (e) {
        console.error("Submission failed", e);
        alert("Transmission failure: Check network protocols.");
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<style scoped>
.pop-enter-active, .pop-leave-active { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.pop-enter-from { opacity: 0; transform: scale(0.95); }
.pop-leave-to { opacity: 0; transform: scale(0.95); }
</style>