<template>
    <div class="w-full font-inter text-slate-900">
        <!-- Progress Stepper -->
        <div class="flex items-center justify-between mb-16 relative">
            <div class="absolute top-1/2 left-0 w-full h-0.5 bg-slate-100 -translate-y-1/2 z-0"></div>
            <div class="absolute top-1/2 left-0 h-0.5 bg-emerald-500 -translate-y-1/2 z-0 transition-all duration-700" :style="{ width: ((currentStep - 1) / (steps.length - 1)) * 100 + '%' }"></div>
            
            <div v-for="(step, index) in steps" :key="index" class="relative z-10 flex flex-col items-center">
                <div :class="[
                    'w-10 h-10 rounded-full flex items-center justify-center border-4 transition-all duration-500 bg-white',
                    currentStep > index + 1 ? 'border-emerald-500 bg-emerald-500 text-white' : 
                    currentStep === index + 1 ? 'border-emerald-500 text-emerald-600 scale-110 shadow-lg shadow-emerald-500/20' : 
                    'border-slate-100 text-slate-300'
                ]">
                    <CheckIcon v-if="currentStep > index + 1" class="w-6 h-6 stroke-[3]" />
                    <component v-else :is="step.icon" class="w-5 h-5" />
                </div>
                <span class="mt-4 text-sm font-black uppercase tracking-[0.2em]" :class="currentStep === index + 1 ? 'text-emerald-600' : 'text-slate-400'">
                    {{ step.name }}
                </span>
            </div>
        </div>

        <!-- Wizard Content -->
        <div class="bg-white rounded-[3rem] shadow-2xl shadow-slate-200/50 border border-slate-100 p-12 min-h-[500px] flex flex-col items-center text-center">
            
            <transition name="fade-slide" mode="out-in">
                <!-- Step 1: Project & Module -->
                <div v-if="currentStep === 1" key="step1" class="w-full space-y-10">
                    <div class="space-y-4">
                        <h2 class="text-3xl font-black tracking-tight">Where is the issue?</h2>
                        <p class="text-slate-400 font-medium">Select the operational zone that requires attention.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
                        <div v-for="project in projects" :key="project.id" class="space-y-4">
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 ml-4">{{ project.name }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <!-- General Option -->
                                <button 
                                        @click="selectModule(project, null)"
                                        :class="[
                                            'p-6 rounded-2xl border-2 text-left transition-all duration-300 flex items-center gap-4 group',
                                            form.project_id === project.id && form.module_id === null ? 'border-emerald-500 bg-emerald-50/50 ring-4 ring-emerald-500/5' : 'border-slate-50 hover:border-slate-200'
                                        ]">
                                    <div class="h-12 w-12 rounded-xl bg-white border border-slate-100 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                        <BriefcaseIcon class="w-6 h-6 text-emerald-500" />
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">General Report</div>
                                        <div class="text-sm font-black text-slate-400 uppercase tracking-widest mt-0.5">Project Level</div>
                                    </div>
                                </button>

                                <button v-for="module in project.modules" :key="module.id" 
                                        @click="selectModule(project, module)"
                                        :class="[
                                            'p-6 rounded-2xl border-2 text-left transition-all duration-300 flex items-center gap-4 group',
                                            form.module_id === module.id ? 'border-emerald-500 bg-emerald-50/50 ring-4 ring-emerald-500/5' : 'border-slate-50 hover:border-slate-200'
                                        ]">
                                    <div class="h-12 w-12 rounded-xl bg-white border border-slate-100 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                        <Squares2X2Icon class="w-6 h-6 text-emerald-500" />
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ module.name }}</div>
                                        <div class="text-sm font-black text-slate-400 uppercase tracking-widest mt-0.5">Application Zone</div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Details & Evidence -->
                <div v-else-if="currentStep === 2" key="step2" class="w-full space-y-10">
                    <div class="space-y-4">
                        <h2 class="text-3xl font-black tracking-tight">Show us what's broken.</h2>
                        <p class="text-slate-400 font-medium">Describe the anomaly and upload visual evidence.</p>
                    </div>

                    <div class="space-y-6 text-left">
                        <div>
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1 flex justify-between">
                                Subject
                                <span v-if="form.subject.length > 0 && form.subject.length <= 5" class="text-rose-500">Too short</span>
                            </label>
                            <input v-model="form.subject" type="text" placeholder="e.g. Broken checkout button on mobile..." class="w-full bg-slate-50 rounded-2xl border-none focus:ring-4 focus:ring-emerald-500/5 focus:bg-white px-6 py-5 text-lg font-bold transition-all" />
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-sm font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1 flex justify-between">
                                    Detailed Log
                                    <span v-if="form.description.length > 0 && form.description.length <= 10" class="text-rose-500 text-xs">Provide more detail</span>
                                </label>
                                <textarea v-model="form.description" rows="8" placeholder="Tell us exactly what happened step-by-step..." class="w-full bg-slate-50 rounded-3xl border-none focus:ring-4 focus:ring-emerald-500/5 focus:bg-white px-6 py-5 text-sm font-medium transition-all"></textarea>
                            </div>
                            
                            <div class="space-y-4">
                                <label class="block text-sm font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Media Hub</label>
                                <div 
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleDrop"
                                    @click="$refs.fileInput.click()"
                                    :class="[
                                        'h-48 border-2 border-dashed rounded-[2rem] flex flex-col items-center justify-center transition-all cursor-pointer group',
                                        isDragging ? 'border-emerald-500 bg-emerald-50/50' : 'border-slate-200 hover:border-slate-300 bg-slate-50/30'
                                    ]"
                                >
                                    <input type="file" ref="fileInput" class="hidden" multiple @change="handleFileSelect" />
                                    <div class="h-16 w-16 mb-4 rounded-full bg-white flex items-center justify-center shadow-lg transition-transform group-hover:scale-110">
                                        <CloudArrowUpIcon class="w-8 h-8 text-emerald-500" />
                                    </div>
                                    <p class="text-xs font-black text-slate-500 uppercase tracking-widest">Drop Media or Paste (Ctrl+V)</p>
                                    <p class="text-sm text-slate-400 mt-1 font-medium">Screenshots, Video, or PDF (Max 20MB)</p>
                                </div>

                                <!-- Uploading List -->
                                <div v-if="form.attachments.length > 0" class="flex flex-wrap gap-2">
                                    <div v-for="(file, idx) in form.attachments" :key="idx" class="px-3 py-2 bg-slate-100 rounded-xl flex items-center gap-2 text-sm font-bold">
                                        <PhotoIcon v-if="file.file_type === 'image'" class="w-3.5 h-3.5 text-blue-500" />
                                        <VideoCameraIcon v-else-if="file.file_type === 'video'" class="w-3.5 h-3.5 text-rose-500" />
                                        <DocumentIcon v-else class="w-3.5 h-3.5 text-amber-500" />
                                        <span class="max-w-[100px] truncate">{{ file.original_name }}</span>
                                        <button @click.stop="removeFile(idx)" class="hover:text-rose-500">
                                            <XMarkIcon class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Impact & Meta -->
                <div v-else-if="currentStep === 3" key="step3" class="w-full space-y-12">
                     <div class="space-y-4">
                        <h2 class="text-3xl font-black tracking-tight">How badly is this affecting you?</h2>
                        <p class="text-slate-400 font-medium">This helps our team prioritize your operational stability.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <button v-for="sev in severities" :key="sev.id" 
                                @click="form.severity = sev.id"
                                :class="[
                                    'p-8 rounded-[2rem] border-2 text-left transition-all relative overflow-hidden group',
                                    form.severity === sev.id ? 'border-emerald-500 bg-emerald-50/50' : 'border-slate-50 hover:border-slate-200 bg-slate-50/20'
                                ]">
                            <div class="flex items-center gap-4 mb-2">
                                <div :class="[
                                    'h-4 w-4 rounded-full',
                                    sev.id === 'critical' ? 'bg-rose-500' : 
                                    sev.id === 'high' ? 'bg-orange-500' : 
                                    sev.id === 'medium' ? 'bg-amber-500' : 'bg-emerald-500'
                                ]"></div>
                                <span class="font-black text-xl tracking-tight">{{ sev.name }}</span>
                            </div>
                            <p class="text-xs font-medium text-slate-500">{{ sev.desc }}</p>
                            
                            <!-- Checkmark for selected -->
                            <div v-if="form.severity === sev.id" class="absolute -top-3 -right-3 h-12 w-12 bg-emerald-500 text-white rounded-bl-3xl flex items-center justify-center pt-2 pl-2">
                                <CheckIcon class="w-5 h-5 stroke-[4]" />
                            </div>
                        </button>
                    </div>

                    <div class="p-8 bg-emerald-900 border border-emerald-500/30 text-white rounded-[2.5rem] space-y-8 shadow-2xl shadow-emerald-900/40 text-left relative overflow-hidden group">
                        <!-- Abstract glow -->
                        <div class="absolute -right-10 -bottom-10 h-32 w-32 bg-white/5 blur-3xl rounded-full"></div>

                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative z-10">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 bg-white/10 rounded-2xl flex items-center justify-center">
                                    <CpuChipIcon class="w-6 h-6 text-emerald-400" />
                                </div>
                                <div>
                                    <h4 class="text-sm font-black uppercase tracking-widest">Environment Intelligence</h4>
                                    <p class="text-sm text-emerald-200 font-bold uppercase tracking-widest opacity-80 mt-1">Protocol: {{ deviceSummary }}</p>
                                </div>
                            </div>

                            <!-- Preset Quick-Select & Management -->
                            <div v-if="presets.length > 0" class="w-full md:w-auto flex items-center gap-2">
                                <select @change="applyPreset($event.target.value)" class="bg-white/5 border-white/10 rounded-xl text-sm font-black uppercase tracking-widest text-emerald-400 focus:ring-1 focus:ring-emerald-500 py-3 px-4 w-full cursor-pointer">
                                    <option value="" disabled selected>Load Saved Device</option>
                                    <option v-for="p in presets" :key="p.id" :value="p.id" class="bg-emerald-950">{{ p.device_name }}</option>
                                </select>
                                
                                <button v-if="selectedPresetId" @click="deletePreset(selectedPresetId)" class="h-10 w-10 flex shrink-0 items-center justify-center bg-rose-500/10 text-rose-400 rounded-xl hover:bg-rose-500 hover:text-white transition-all" title="Delete Preset">
                                    <XMarkIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-white/5 flex flex-col md:flex-row gap-8 items-end relative z-10">
                            <div class="flex-1 space-y-4">
                                <div class="flex items-center gap-3">
                                    <button 
                                        type="button"
                                        @click="form.save_preset = !form.save_preset"
                                        :class="['h-6 w-11 rounded-full transition-colors relative', form.save_preset ? 'bg-emerald-500' : 'bg-white/10']"
                                    >
                                        <div :class="['absolute top-1 left-1 h-4 w-4 bg-white rounded-full transition-transform', form.save_preset ? 'translate-x-5' : '']"></div>
                                    </button>
                                    <span class="text-sm font-black uppercase tracking-widest text-emerald-200">Save this device signature</span>
                                </div>
                                <input v-if="form.save_preset" v-model="form.preset_name" type="text" placeholder="Device Label (e.g. My MacBook Pro)" class="w-full bg-white/10 border-none rounded-xl text-xs text-white placeholder-emerald-700 p-4 focus:ring-1 focus:ring-white transition-all" />
                            </div>

                            <button @click="submitTicket" :disabled="isSubmitting" class="px-12 py-5 bg-white text-emerald-900 rounded-2xl text-xs font-black uppercase tracking-[0.2em] transform transition-all hover:scale-105 active:scale-95 disabled:opacity-50 flex items-center gap-3 shadow-2xl">
                                <span v-if="!isSubmitting">Broadcast Signal</span>
                                <span v-else>Transmitting...</span>
                                <PaperAirplaneIcon v-if="!isSubmitting" class="w-4 h-4" />
                                <svg v-else class="animate-spin h-4 w-4 text-emerald-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Navigation Buttons -->
            <div class="mt-auto w-full flex justify-between pt-12">
                <button v-if="currentStep > 1" @click="currentStep--" class="px-8 py-4 bg-slate-50 text-slate-500 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-slate-100 transition-all flex items-center gap-2">
                    <ArrowLeftIcon class="w-4 h-4" />
                    Previous Stage
                </button>
                <div v-else></div>
                
                <button v-if="currentStep < 3" @click="nextStep" :disabled="!canProceed" class="px-10 py-4 bg-emerald-600 text-white rounded-xl text-sm font-black uppercase tracking-widest shadow-xl shadow-emerald-500/20 hover:bg-emerald-700 transition-all flex items-center gap-2 active:scale-95">
                    Analyze & Forward
                    <ArrowRightIcon class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import { 
    Squares2X2Icon, 
    CloudArrowUpIcon, 
    CheckIcon, 
    ArrowRightIcon, 
    ArrowLeftIcon,
    PhotoIcon,
    VideoCameraIcon,
    DocumentIcon,
    XMarkIcon,
    CpuChipIcon,
    PaperAirplaneIcon,
    CodeBracketIcon,
    SwatchIcon,
    DevicePhoneMobileIcon,
    BriefcaseIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';

const emit = defineEmits(['ticket-created']);

const currentStep = ref(1);
const steps = [
    { name: 'Identity', icon: SwatchIcon },
    { name: 'Evidence', icon: CodeBracketIcon },
    { name: 'Priority', icon: DevicePhoneMobileIcon }
];

const projects = ref([]);
const severities = ref([]);
const presets = ref([]);
const isDragging = ref(false);
const isSubmitting = ref(false);

const form = reactive({
    project_id: null,
    module_id: null,
    subject: '',
    description: '',
    severity: 'medium',
    attachments: [],
    save_preset: false,
    preset_name: '',
    environment: {
        agent: navigator.userAgent,
        width: window.innerWidth,
        height: window.innerHeight,
        resolution: `${window.screen.width}x${window.screen.height}`,
        platform: navigator.platform
    }
});

const selectedPresetId = ref(null);

const applyPreset = (id) => {
    const preset = presets.value.find(p => p.id === parseInt(id));
    if (preset) {
        form.environment = { ...preset.metadata };
        selectedPresetId.value = preset.id;
    }
};

const deletePreset = async (id) => {
    if(!confirm("Erase this device signature from your profile?")) return;
    try {
        await axios.delete(route('portal.presets.destroy', id));
        presets.value = presets.value.filter(p => p.id !== id);
        selectedPresetId.value = null;
    } catch (e) {
        console.error("Deletion failed", e);
    }
};

const deviceSummary = computed(() => {
    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
    return isMobile ? 'Mobile Intelligence' : 'Desktop Protocol';
});

const canProceed = computed(() => {
    if (currentStep.value === 1) return form.project_id !== null;
    if (currentStep.value === 2) return form.subject.length > 5 && form.description.length > 10;
    return true;
});

const selectModule = (project, module) => {
    form.project_id = project.id;
    form.module_id = module ? module.id : null;
};

const nextStep = () => {
    if (canProceed.value) currentStep.value++;
};

const handleDrop = async (e) => {
    isDragging.value = false;
    const files = Array.from(e.dataTransfer.files);
    await uploadFiles(files);
};

const handleFileSelect = async (e) => {
    const files = Array.from(e.target.files);
    await uploadFiles(files);
};

const handlePaste = async (e) => {
    const items = (e.clipboardData || e.originalEvent.clipboardData).items;
    const files = [];
    for (const item of items) {
        if (item.kind === 'file') {
            files.push(item.getAsFile());
        }
    }
    if (files.length) await uploadFiles(files);
};

const uploadFiles = async (files) => {
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
        if (form.save_preset && form.preset_name) {
            await axios.post(route('portal.presets.store'), {
                device_name: form.preset_name,
                metadata: form.environment
            });
        }
        await axios.post(route('portal.tickets.store'), form);
        emit('ticket-created');
    } catch (e) {
        console.error("Submission failed", e);
        alert("Transmission failure: Check network protocols.");
    } finally {
        isSubmitting.value = false;
    }
};

const loadContext = async () => {
    const { data } = await axios.get(route('portal.context'));
    projects.value = data.projects;
    severities.value = data.severities;

    // Fetch Presets
    const { data: presetData } = await axios.get(route('portal.presets.index'));
    presets.value = presetData;
};

onMounted(() => {
    loadContext();
    window.addEventListener('paste', handlePaste);
});

onUnmounted(() => {
    window.removeEventListener('paste', handlePaste);
});
</script>

<style scoped>
.fade-slide-enter-active, .fade-slide-leave-active {
    transition: all 0.5s ease;
}
.fade-slide-enter-from {
    opacity: 0;
    transform: translateX(40px);
}
.fade-slide-leave-to {
    opacity: 0;
    transform: translateX(-40px);
}
</style>
