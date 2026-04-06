<template>
    <div class="h-full flex flex-col bg-white rounded-[4rem] border border-gray-100 shadow-xl overflow-hidden animate-fade-in font-sans">
        <!-- Wizard Header -->
        <div class="px-12 py-10 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-black text-gray-900 uppercase italic mb-1">COURSE ARCHITECT</h3>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Protocol Version 4.2 • Structural Integrity Check Active</p>
            </div>
            <div class="flex items-center gap-4">
                <button @click="$emit('cancel')" class="px-6 py-3 bg-white text-gray-400 border border-gray-100 rounded-2xl text-[9px] font-black uppercase hover:bg-red-50 hover:text-red-500 transition-all">Cancel Forge</button>
                <button @click="handleSave" class="px-8 py-3 bg-emerald-600 text-white rounded-2xl text-[9px] font-black uppercase shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all">Save Protocol</button>
            </div>
        </div>

        <!-- Wizard Navigation -->
        <div class="px-12 py-4 bg-white border-b border-gray-50 flex items-center gap-12 overflow-x-auto no-scrollbar">
            <button 
                v-for="(step, idx) in steps" 
                :key="step.id"
                @click="currentStep = step.id"
                class="flex items-center gap-3 group transition-all shrink-0"
                :class="currentStep === step.id ? 'opacity-100' : 'opacity-40 hover:opacity-100'"
            >
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-[10px] font-black transition-all"
                    :class="currentStep === step.id ? 'bg-emerald-600 text-white shadow-lg' : 'bg-gray-100 text-gray-400'"
                >
                    {{ idx + 1 }}
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest">{{ step.label }}</span>
                <div v-if="idx < steps.length - 1" class="h-px w-8 bg-gray-100 mx-2"></div>
            </button>
        </div>

        <!-- Wizard Content -->
        <div class="flex-1 overflow-y-auto p-12 custom-scrollbar bg-gray-50/20">
            <transition name="fade-slide" mode="out-in">
                <div :key="currentStep" class="max-w-4xl mx-auto">
                    <!-- Step 1: Basic Info -->
                    <div v-if="currentStep === 'basic'" class="space-y-10">
                        <h4 class="text-lg font-black text-gray-900 uppercase italic border-l-4 border-emerald-500 pl-6">Primary Metadata</h4>
                        <div class="grid grid-cols-2 gap-8">
                            <div class="col-span-2 space-y-3">
                                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Syllabus Title</label>
                                <input v-model="course.title" type="text" placeholder="e.g. Advanced EV Propulsion Systems" class="w-full px-6 py-4 bg-white border border-gray-100 rounded-2xl text-xs font-bold focus:ring-2 focus:ring-emerald-100 transition-all" />
                            </div>
                            <div class="space-y-3">
                                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Category</label>
                                <select v-model="course.category" class="w-full px-6 py-4 bg-white border border-gray-100 rounded-2xl text-xs font-bold focus:ring-2 focus:ring-emerald-100 transition-all">
                                    <option value="engineering">Engineering</option>
                                    <option value="systems">Systems Logic</option>
                                    <option value="governance">Digital Governance</option>
                                </select>
                            </div>
                            <div class="space-y-3">
                                <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Knowledge Level</label>
                                <select v-model="course.level" class="w-full px-6 py-4 bg-white border border-gray-100 rounded-2xl text-xs font-bold focus:ring-2 focus:ring-emerald-100 transition-all">
                                    <option value="foundational">Foundational</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="god-mode">Elite Hub (Advanced)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Structure -->
                    <div v-else-if="currentStep === 'structure'" class="space-y-10">
                        <div class="flex items-center justify-between border-l-4 border-emerald-500 pl-6">
                            <h4 class="text-lg font-black text-gray-900 uppercase italic">Syllabus Matrix</h4>
                            <button @click="addModule" class="px-6 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-[9px] font-black uppercase hover:bg-emerald-600 hover:text-white transition-all">Add Module</button>
                        </div>
                        
                        <div class="space-y-6">
                            <!-- Visual Tree Mockup -->
                            <div v-for="(mod, mIdx) in course.structure" :key="mIdx" class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-6 relative group overflow-hidden">
                                <div class="flex items-center justify-between relative z-10">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-gray-900 flex items-center justify-center text-white text-xs font-black italic">M.{{ mIdx + 1 }}</div>
                                        <input v-model="mod.title" type="text" class="bg-transparent border-none text-md font-black uppercase italic p-0 focus:ring-0 text-gray-900" placeholder="Module Title" />
                                    </div>
                                    <button @click="removeModule(mIdx)" class="opacity-0 group-hover:opacity-100 text-red-300 hover:text-red-500 transition-all"><i class="fas fa-trash-alt"></i></button>
                                </div>
                                
                                <div class="pl-14 space-y-4">
                                    <div v-for="(chap, cIdx) in mod.chapters" :key="cIdx" class="flex items-center gap-4 bg-gray-50 px-6 py-3 rounded-2xl border border-transparent hover:border-emerald-100 transition-all">
                                        <span class="text-[10px] font-black text-gray-300 italic">C.{{ cIdx + 1 }}</span>
                                        <input v-model="chap.title" type="text" class="flex-1 bg-transparent border-none text-[11px] font-black uppercase p-0 focus:ring-0 text-gray-600" placeholder="Chapter Title" />
                                        <button @click="addConcept(mIdx, cIdx)" class="text-emerald-500 hover:scale-110 transition-transform"><i class="fas fa-plus-circle"></i></button>
                                    </div>
                                    <button @click="addChapter(mIdx)" class="text-[9px] font-black text-emerald-600 uppercase tracking-widest italic hover:ml-2 transition-all">+ Inject New Chapter</button>
                                </div>
                                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-emerald-50 rounded-full opacity-20 pointer-events-none"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Activities & Content -->
                    <div v-else-if="currentStep === 'content'" class="space-y-12">
                         <h4 class="text-lg font-black text-gray-900 uppercase italic border-l-4 border-emerald-500 pl-6">Deep Content Injection</h4>
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                             <div v-for="type in activityTypes" :key="type.id" class="p-10 bg-white rounded-[3.5rem] border border-gray-100 shadow-sm hover:shadow-2xl hover:border-emerald-200 transition-all group relative overflow-hidden text-center cursor-pointer">
                                 <div class="w-16 h-16 rounded-[2rem] bg-emerald-50 mx-auto flex items-center justify-center text-emerald-600 text-2xl mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                                     <i :class="type.icon"></i>
                                 </div>
                                 <h5 class="text-sm font-black text-gray-900 uppercase italic mb-2">{{ type.label }}</h5>
                                 <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">{{ type.desc }}</p>
                                 <div class="absolute -right-10 -top-10 w-24 h-24 bg-emerald-50 rounded-full opacity-40 group-hover:scale-150 transition-transform duration-1000"></div>
                             </div>
                         </div>
                    </div>

                    <!-- Step 4: Rules -->
                    <div v-else-if="currentStep === 'rules'" class="space-y-12">
                        <h4 class="text-lg font-black text-gray-900 uppercase italic border-l-4 border-emerald-500 pl-6">Governance & Logic Thresholds</h4>
                        <div class="space-y-8">
                            <div v-for="rule in rules" :key="rule.id" class="flex items-center justify-between p-8 bg-white rounded-[3rem] border border-gray-100 shadow-sm group">
                                <div class="flex items-center gap-6">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                                        <i :class="rule.icon"></i>
                                    </div>
                                    <div class="max-w-[14rem]">
                                        <p class="text-[10px] font-black text-gray-900 uppercase italic leading-none mb-1">{{ rule.label }}</p>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest leading-tight">{{ rule.desc }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div v-if="rule.type === 'number'" class="flex items-center gap-3">
                                        <input type="number" v-model="rule.value" class="w-20 px-4 py-2 bg-gray-50 border-none rounded-xl text-xs font-black text-center text-emerald-600" />
                                        <span class="text-[8px] font-black text-gray-300 uppercase tracking-widest">{{ rule.unit }}</span>
                                    </div>
                                    <button @click="rule.active = !rule.active" :class="rule.active ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-400'" class="w-12 h-6 rounded-full relative transition-all">
                                        <div class="w-4 h-4 bg-white rounded-full absolute top-1 transition-all" :class="rule.active ? 'left-7' : 'left-1'"></div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Publishing -->
                    <div v-else-if="currentStep === 'publish'" class="space-y-12 text-center pb-20">
                         <div class="w-24 h-24 bg-emerald-600 rounded-[2.5rem] mx-auto flex items-center justify-center text-white text-4xl shadow-2xl shadow-emerald-200 animate-bounce">
                             <i class="fas fa-rocket"></i>
                         </div>
                         <div class="space-y-4">
                             <h4 class="text-3xl font-black text-gray-900 uppercase italic tracking-tighter leading-none">Ready for Global Deployment</h4>
                             <p class="text-xs font-bold text-gray-400 uppercase tracking-widest max-w-sm mx-auto leading-relaxed italic">Your structural integrity audit is 100% complete. Choose your deployment tier.</p>
                         </div>
                         <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-10">
                             <button v-for="tier in tiers" :key="tier.id" class="p-10 bg-white border border-gray-100 rounded-[4rem] shadow-sm hover:shadow-2xl hover:border-emerald-300 transition-all group relative overflow-hidden">
                                 <h5 class="text-xs font-black text-gray-900 uppercase italic mb-4">{{ tier.label }}</h5>
                                 <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest mb-8">{{ tier.desc }}</p>
                                 <div class="text-[8px] font-black text-gray-300 uppercase italic border-t border-gray-50 pt-4 group-hover:text-emerald-500">Deploy Protocol</div>
                             </button>
                         </div>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Wizard Footer -->
        <div class="px-12 py-8 bg-white border-t border-gray-100 flex items-center justify-between">
            <button 
                @click="prevStep" 
                :disabled="currentStep === 'basic'"
                class="px-8 py-4 bg-gray-50 text-gray-400 rounded-2xl text-[9px] font-black uppercase tracking-widest disabled:opacity-20 hover:bg-gray-100 transition-all flex items-center gap-2"
            >
                <i class="fas fa-arrow-left"></i> Previous Matrix
            </button>
            <button 
                @click="nextStep"
                class="px-10 py-4 bg-gray-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl hover:bg-black transition-all flex items-center gap-2"
            >
                {{ currentStep === 'publish' ? 'Publish Course' : 'Save & Next' }}
                <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const currentStep = ref('basic');
const steps = [
    { id: 'basic', label: 'Course Info' },
    { id: 'structure', label: 'Structure' },
    { id: 'rules', label: 'Rule Engine' },
    { id: 'publish', label: 'Publishing' },
];

const course = ref({
    title: '',
    category: 'engineering',
    level: 'foundational',
    structure: [
        { title: 'Foundational Theory', chapters: [{ title: 'Introduction' }] }
    ]
});

const activityTypes = [
    { id: 'video', label: 'Neural Streaming', icon: 'fas fa-play-circle', desc: 'Secure high-res video concepts' },
    { id: 'reading', label: 'Syllabus Whitepaper', icon: 'fas fa-file-alt', desc: 'Deep-dive technical documentation' },
    { id: 'quiz', label: 'Cognitive Audit', icon: 'fas fa-tasks', desc: 'Real-time assessment matrix' },
    { id: 'assignment', label: 'Structural Forge', icon: 'fas fa-hammer', desc: 'Practical lab & project engine' },
];

const rules = ref([
    { id: 'watch-time', label: 'Attention Threshold', icon: 'fas fa-eye', desc: 'Minimum video watch percentage', type: 'number', unit: '%', value: 90, active: true },
    { id: 'quiz-score', label: 'Mastery Score', icon: 'fas fa-award', desc: 'Minimum quiz passing percentage', type: 'number', unit: '%', value: 80, active: true },
    { id: 'max-attempts', label: 'Assessment Retries', icon: 'fas fa-redo', desc: 'Maximum number of exam attempts', type: 'number', unit: 'tries', value: 3, active: true },
    { id: 'cool-down', label: 'Cool-down Logic', icon: 'fas fa-hourglass-half', desc: 'Wait time between retries', type: 'number', unit: 'mins', value: 60, active: false },
    { id: 'no-skip', label: 'Linear Logic', icon: 'fas fa-lock', desc: 'Prevent concept skipping protocols', type: 'toggle', active: true },
    { id: 'exam-mode', label: 'High-Integrity Mode', icon: 'fas fa-user-shield', desc: 'Lock navigation during assessments', type: 'toggle', active: false },
]);

const tiers = [
    { id: 'draft', label: 'Save Archive', desc: 'Internal Hub Only' },
    { id: 'college', label: 'Regional Mesh', desc: 'Selected Institutions' },
    { id: 'public', label: 'Global Network', desc: 'State-wide Open' },
];

const addModule = () => course.value.structure.push({ title: 'New Matrix Layer', chapters: [] });
const addChapter = (mIdx) => course.value.structure[mIdx].chapters.push({ title: 'New Logic Segment' });
const removeModule = (mIdx) => course.value.structure.splice(mIdx, 1);

const nextStep = () => {
    const idx = steps.findIndex(s => s.id === currentStep.value);
    if (idx < steps.length - 1) currentStep.value = steps[idx + 1].id;
};
const prevStep = () => {
    const idx = steps.findIndex(s => s.id === currentStep.value);
    if (idx > 0) currentStep.value = steps[idx - 1].id;
};

const handleSave = () => console.log('Saving Course Matrix...', course.value);
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.4s ease; }
.fade-slide-enter-from { opacity: 0; transform: translateX(30px); }
.fade-slide-leave-to { opacity: 0; transform: translateX(-30px); }
</style>
