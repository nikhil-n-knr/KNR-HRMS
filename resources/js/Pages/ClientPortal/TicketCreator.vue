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
                <div v-if="currentStep === 1" key="step1" class="space-y-6 px-2">
                    <div class="space-y-2 text-center">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 italic flex items-center justify-center gap-2">
                            Select Targeted Sector
                            <div class="group/info relative inline-block">
                                <span class="h-4 w-4 rounded-full border border-slate-200 flex items-center justify-center text-[8px] italic cursor-help">i</span>
                                <div class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 w-56 p-3 bg-slate-900 text-[9px] text-white rounded-xl opacity-0 group-hover:opacity-100 transition-opacity z-50 shadow-2xl font-inter pointer-events-none tracking-normal normal-case">
                                    Choose the project and the affected modules. You can select multiple modules if the same issue spans several areas.
                                </div>
                            </div>
                        </h3>
                    </div>

                    <!-- Project Dropdown (searchable) -->
                    <div class="space-y-2" ref="projectDropdownRef">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Project</label>
                        <div class="relative">
                            <button type="button" @click="projectOpen = !projectOpen"
                                    class="w-full flex items-center justify-between gap-3 px-4 py-3.5 bg-slate-50 border-2 rounded-2xl text-left transition-all focus:outline-none"
                                    :class="projectOpen ? 'border-emerald-500 bg-white' : 'border-slate-100 hover:border-slate-200'">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="h-7 w-7 rounded-lg flex-shrink-0 flex items-center justify-center"
                                         :class="selectedProject ? 'bg-slate-900' : 'bg-slate-100'">
                                        <BoltIcon class="w-3.5 h-3.5" :class="selectedProject ? 'text-emerald-400' : 'text-slate-400'" />
                                    </div>
                                    <span class="text-xs font-black truncate" :class="selectedProject ? 'text-slate-900' : 'text-slate-400'">
                                        {{ selectedProject ? selectedProject.name : 'Choose a project...' }}
                                    </span>
                                </div>
                                <ChevronDownIcon class="w-4 h-4 text-slate-400 flex-shrink-0 transition-transform" :class="projectOpen ? 'rotate-180' : ''" />
                            </button>

                            <transition name="pop">
                                <div v-if="projectOpen" class="absolute z-50 mt-2 w-full bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden">
                                    <div class="p-3 border-b border-slate-50">
                                        <div class="flex items-center gap-2 px-3 py-2 bg-slate-50 rounded-xl">
                                            <MagnifyingGlassIcon class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" />
                                            <input v-model="projectSearch" type="text" placeholder="Search projects..."
                                                   class="bg-transparent border-none focus:ring-0 text-xs font-medium w-full placeholder:text-slate-300"
                                                   @click.stop />
                                        </div>
                                    </div>
                                    <ul class="max-h-52 overflow-y-auto py-2 custom-scrollbar">
                                        <li v-for="project in filteredProjects" :key="project.id">
                                            <button type="button" @click="selectProject(project)"
                                                    class="w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition-colors text-left group">
                                                <div class="h-7 w-7 rounded-lg bg-slate-900 flex items-center justify-center flex-shrink-0">
                                                    <BoltIcon class="w-3.5 h-3.5 text-emerald-400" />
                                                </div>
                                                <span class="text-xs font-black text-slate-900 truncate group-hover:text-emerald-600 transition-colors">{{ project.name }}</span>
                                                <CheckIcon v-if="form.project_id === project.id" class="w-4 h-4 text-emerald-500 ml-auto flex-shrink-0 stroke-[3]" />
                                            </button>
                                        </li>
                                        <li v-if="filteredProjects.length === 0" class="px-4 py-6 text-center text-[10px] font-black text-slate-300 uppercase tracking-widest">No projects found</li>
                                    </ul>
                                </div>
                            </transition>
                        </div>
                    </div>

                    <!-- Module Multi-Select (searchable, lazy-loaded) -->
                    <div class="space-y-2" ref="moduleDropdownRef">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-2">
                            Affected Modules
                            <span class="text-slate-300 font-bold normal-case tracking-normal">(optional — select all that apply)</span>
                        </label>

                        <!-- Selected module chips -->
                        <div v-if="form.module_ids.length > 0" class="flex flex-wrap gap-2 mb-2">
                            <span v-for="mod in form.module_ids" :key="mod.id"
                                  class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 rounded-xl text-[10px] font-black uppercase tracking-wider">
                                <Squares2X2Icon class="w-3 h-3" />
                                <span class="max-w-[120px] truncate">{{ mod.name }}</span>
                                <button type="button" @click="removeModule(mod.id)" class="text-emerald-500 hover:text-rose-500 transition-colors ml-0.5">
                                    <XMarkIcon class="w-3 h-3 stroke-[3]" />
                                </button>
                            </span>
                            <button type="button" @click="form.module_ids = []" class="text-[9px] font-black text-slate-300 hover:text-rose-500 uppercase tracking-widest self-center transition-colors">
                                Clear all
                            </button>
                        </div>

                        <div class="relative">
                            <button type="button" @click="toggleModuleDropdown"
                                    :disabled="!form.project_id"
                                    class="w-full flex items-center justify-between gap-3 px-4 py-3.5 border-2 rounded-2xl text-left transition-all focus:outline-none disabled:opacity-40 disabled:cursor-not-allowed"
                                    :class="moduleOpen ? 'border-emerald-500 bg-white' : 'border-slate-100 bg-slate-50 hover:border-slate-200'">
                                <div class="flex items-center gap-3">
                                    <Squares2X2Icon class="w-4 h-4 text-slate-400 flex-shrink-0" />
                                    <span class="text-xs font-black text-slate-400">
                                        {{ form.project_id ? (form.module_ids.length > 0 ? `${form.module_ids.length} module(s) selected` : 'Search and select modules...') : 'Select a project first' }}
                                    </span>
                                </div>
                                <ChevronDownIcon class="w-4 h-4 text-slate-400 flex-shrink-0 transition-transform" :class="moduleOpen ? 'rotate-180' : ''" />
                            </button>

                            <transition name="pop">
                                <div v-if="moduleOpen" class="absolute z-50 mt-2 w-full bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden">
                                    <!-- Search input -->
                                    <div class="p-3 border-b border-slate-50">
                                        <div class="flex items-center gap-2 px-3 py-2 bg-slate-50 rounded-xl">
                                            <MagnifyingGlassIcon class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" />
                                            <input v-model="moduleSearch" type="text" placeholder="Search modules..."
                                                   class="bg-transparent border-none focus:ring-0 text-xs font-medium w-full placeholder:text-slate-300"
                                                   @click.stop @input="onModuleSearchInput" ref="moduleSearchInput" />
                                            <span v-if="modulesLoading" class="w-3.5 h-3.5 border-2 border-emerald-500/30 border-t-emerald-500 rounded-full animate-spin flex-shrink-0"></span>
                                        </div>
                                    </div>

                                    <!-- General / No module option -->
                                    <div class="border-b border-slate-50">
                                        <button type="button" @click="toggleGeneralModule"
                                                class="w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition-colors text-left group">
                                            <div class="h-7 w-7 rounded-lg bg-slate-900 flex items-center justify-center flex-shrink-0">
                                                <BoltIcon class="w-3.5 h-3.5 text-emerald-400" />
                                            </div>
                                            <div>
                                                <div class="text-xs font-black text-slate-900 group-hover:text-emerald-600 transition-colors">General Intelligence</div>
                                                <div class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Foundational Project Feedback</div>
                                            </div>
                                            <CheckIcon v-if="form.general_module" class="w-4 h-4 text-emerald-500 ml-auto flex-shrink-0 stroke-[3]" />
                                        </button>
                                    </div>

                                    <!-- Module list -->
                                    <ul class="max-h-56 overflow-y-auto py-2 custom-scrollbar" @scroll="onModuleScroll" ref="moduleListRef">
                                        <li v-if="moduleLoadError" class="px-4 py-3 border-b border-slate-50">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-[9px] font-black text-rose-500 uppercase tracking-widest">{{ moduleLoadError }}</span>
                                                <button type="button" @click="loadModules(moduleSearch)" class="text-[9px] font-black text-emerald-600 uppercase tracking-widest hover:underline">Retry</button>
                                            </div>
                                        </li>
                                        <li v-for="mod in moduleOptions" :key="mod.id">
                                            <button type="button" @click="toggleModule(mod)"
                                                    class="w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition-colors text-left group"
                                                    :class="isModuleSelected(mod.id) ? 'bg-emerald-50/50' : ''">
                                                <div class="h-7 w-7 rounded-lg flex-shrink-0 flex items-center justify-center"
                                                     :class="isModuleSelected(mod.id) ? 'bg-emerald-500' : 'bg-slate-100'">
                                                    <Squares2X2Icon class="w-3.5 h-3.5" :class="isModuleSelected(mod.id) ? 'text-slate-900' : 'text-slate-400'" />
                                                </div>
                                                <span class="text-xs font-black truncate" :class="isModuleSelected(mod.id) ? 'text-emerald-700' : 'text-slate-900 group-hover:text-emerald-600 transition-colors'">
                                                    {{ mod.name }}
                                                </span>
                                                <CheckIcon v-if="isModuleSelected(mod.id)" class="w-4 h-4 text-emerald-500 ml-auto flex-shrink-0 stroke-[3]" />
                                            </button>
                                        </li>
                                        <!-- Loading more indicator -->
                                        <li v-if="modulesLoadingMore" class="flex justify-center py-3">
                                            <span class="w-4 h-4 border-2 border-emerald-500/30 border-t-emerald-500 rounded-full animate-spin"></span>
                                        </li>
                                        <li v-if="!modulesLoading && !modulesLoadingMore && moduleOptions.length === 0" class="px-4 py-6 text-center text-[10px] font-black text-slate-300 uppercase tracking-widest">
                                            No modules found
                                        </li>
                                        <li v-if="!modulesLoadingMore && modulesNextPageUrl" class="px-4 py-2 text-center">
                                            <button type="button" @click="loadMoreModules" class="text-[9px] font-black text-emerald-600 uppercase tracking-widest hover:underline">Load more</button>
                                        </li>
                                    </ul>

                                    <div class="p-3 border-t border-slate-50 flex items-center justify-between">
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                            {{ form.module_ids.length }} selected
                                        </span>
                                        <button type="button" @click="moduleOpen = false" class="text-[9px] font-black text-slate-900 uppercase tracking-widest hover:text-emerald-600 transition-colors">Done</button>
                                    </div>
                                </div>
                            </transition>
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
import { ref, reactive, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
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
    ChatBubbleLeftRightIcon,
    ChevronDownIcon,
    MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline';
import axios from 'axios';

const props = defineProps(['projects']);
const emit = defineEmits(['close', 'chat']);

const currentStep = ref(1);
const isSubmitting = ref(false);

// ─── Form ──────────────────────────────────────────────────────────────────────
const form = reactive({
    project_id: null,
    module_ids: [],      // array of { id, name } objects for selected modules
    general_module: false,
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

// ─── Project dropdown ───────────────────────────────────────────────────────────
const projectOpen  = ref(false);
const projectSearch = ref('');
const projectDropdownRef = ref(null);

const selectedProject = computed(() =>
    props.projects?.find(p => p.id === form.project_id) ?? null
);

const filteredProjects = computed(() => {
    const q = projectSearch.value.toLowerCase();
    if (!q) return props.projects ?? [];
    return (props.projects ?? []).filter(p => p.name.toLowerCase().includes(q));
});

const selectProject = (project) => {
    if (form.project_id !== project.id) {
        form.project_id = project.id;
        form.module_ids = [];
        form.general_module = false;
        moduleOptions.value = [];
        modulesNextPageUrl.value = null;
        moduleSearch.value = '';
        loadModules();
    }
    projectOpen.value = false;
    projectSearch.value = '';
};

// ─── Module multi-select ────────────────────────────────────────────────────────
const moduleOpen        = ref(false);
const moduleSearch      = ref('');
const moduleOptions     = ref([]);
const modulesLoading    = ref(false);
const modulesLoadingMore = ref(false);
const modulesNextPageUrl = ref(null);
const moduleLoadError = ref('');
const moduleSearchInput = ref(null);
const moduleListRef     = ref(null);
const moduleDropdownRef = ref(null);
let   moduleDebounceTimer = null;

const toggleModuleDropdown = () => {
    if (!form.project_id) return;
    moduleOpen.value = !moduleOpen.value;
    if (moduleOpen.value) {
        if (moduleOptions.value.length === 0) loadModules();
        nextTick(() => moduleSearchInput.value?.focus());
    }
};

const initDefaultProject = () => {
    const projects = props.projects ?? [];
    if (!projects.length || form.project_id !== null) return;

    // UX rule: if one project, select it; if multiple, select the first project.
    const defaultProject = projects[0];
    form.project_id = defaultProject.id;
    loadModules();
};

const loadModules = async (search = '', append = false) => {
    if (!form.project_id) return;
    if (append) { modulesLoadingMore.value = true; }
    else        { modulesLoading.value = true; }
    moduleLoadError.value = '';

    try {
        const url = append && modulesNextPageUrl.value
            ? modulesNextPageUrl.value
            : `/portal/projects/${form.project_id}/modules`;

        const { data } = await axios.get(url, { params: append ? {} : { search, per_page: 50 } });

        if (append) {
            moduleOptions.value.push(...data.data);
        } else {
            moduleOptions.value = data.data;
        }
        modulesNextPageUrl.value = data.next_page_url;
    } catch (e) {
        console.error('Module load failed', e);
        if (!append) {
            moduleOptions.value = [];
        }
        moduleLoadError.value = e?.response?.status === 403
            ? 'Access denied for this project modules'
            : 'Unable to load modules';
    } finally {
        modulesLoading.value    = false;
        modulesLoadingMore.value = false;
    }
};

const onModuleSearchInput = () => {
    clearTimeout(moduleDebounceTimer);
    modulesNextPageUrl.value = null;
    moduleDebounceTimer = setTimeout(() => {
        loadModules(moduleSearch.value);
    }, 300);
};

watch(
    () => form.project_id,
    (projectId) => {
        if (!projectId) return;
        if (!moduleOpen.value) {
            // Open once the project is selected so user immediately sees modules.
            moduleOpen.value = true;
            nextTick(() => moduleSearchInput.value?.focus());
        }
    }
);

const loadMoreModules = () => {
    if (modulesNextPageUrl.value && !modulesLoadingMore.value) {
        loadModules(moduleSearch.value, true);
    }
};

// Infinite scroll on the list ul
const onModuleScroll = (e) => {
    const el = e.target;
    if (el.scrollHeight - el.scrollTop - el.clientHeight < 60 && !modulesLoadingMore.value && modulesNextPageUrl.value) {
        loadMoreModules();
    }
};

const isModuleSelected = (id) => form.module_ids.some(m => m.id === id);

const toggleModule = (mod) => {
    const idx = form.module_ids.findIndex(m => m.id === mod.id);
    if (idx === -1) {
        form.module_ids.push({ id: mod.id, name: mod.name });
        form.general_module = false;
    } else {
        form.module_ids.splice(idx, 1);
    }
};

const toggleGeneralModule = () => {
    form.general_module = !form.general_module;
    if (form.general_module) form.module_ids = [];
};

const removeModule = (id) => {
    const idx = form.module_ids.findIndex(m => m.id === id);
    if (idx !== -1) form.module_ids.splice(idx, 1);
};

// Close dropdowns on outside click
const handleOutsideClick = (e) => {
    if (projectDropdownRef.value && !projectDropdownRef.value.contains(e.target)) {
        projectOpen.value = false;
    }
    if (moduleDropdownRef.value && !moduleDropdownRef.value.contains(e.target)) {
        moduleOpen.value = false;
    }
};
onMounted(() => {
    document.addEventListener('mousedown', handleOutsideClick);
    initDefaultProject();
});
onUnmounted(() => {
    document.removeEventListener('mousedown', handleOutsideClick);
    if (moduleDebounceTimer) {
        clearTimeout(moduleDebounceTimer);
    }
});

// ─── Wizard navigation ──────────────────────────────────────────────────────────
const canProceed = computed(() => {
    if (currentStep.value === 1) return form.project_id !== null;
    if (currentStep.value === 2) return form.subject.length > 5 && form.description.length > 5;
    return true;
});

const nextStep = () => {
    if (canProceed.value) currentStep.value++;
};

// ─── File upload ────────────────────────────────────────────────────────────────
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

// ─── Submit ─────────────────────────────────────────────────────────────────────
const submitTicket = async () => {
    isSubmitting.value = true;
    try {
        const payload = {
            ...form,
            module_ids: form.module_ids.map(m => m.id),
        };
        await axios.post(route('portal.tickets.store'), payload);
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
