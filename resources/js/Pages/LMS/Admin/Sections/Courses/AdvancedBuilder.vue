<template>
    <div class="space-y-12 animate-fade-in font-sans">
        <!-- Sub-Section Conditional Rendering -->
        <div v-if="activeTab === 'registry' || activeTab === 'overview' || !activeTab" class="space-y-12">
            <!-- Strategic Header: Asset Management -->
            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 p-12 rounded-[4rem] text-white shadow-2xl relative overflow-hidden group">
                <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-12">
                    <div class="space-y-8 flex-1 text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 rounded-full border border-white/20">
                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
                            <span class="text-[9px] font-black text-emerald-100 uppercase tracking-widest leading-none">Global Syllabus Registry Active</span>
                        </div>
                        <div class="space-y-4">
                            <h2 class="text-4xl md:text-6xl font-black tracking-tighter italic uppercase border-l-4 border-white pl-8 leading-none">COURSE ENGINEERING</h2>
                            <p class="text-lg text-white/50 max-w-xl font-medium leading-relaxed italic">
                                Orchestrate your institution's intellectual capital. Modular course construction with real-time enterprise replication.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 pt-4">
                            <button @click="router.visit(route('lms.admin.courses.create'))" class="px-8 py-4 bg-white text-emerald-700 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-50 transition-all flex items-center gap-3 shadow-xl transform active:scale-95">
                                <i class="fas fa-plus"></i> Create Course
                            </button>
                            <button @click="$emit('action', { type: 'navigate', id: 'bulk-manager' })" class="px-8 py-4 bg-emerald-500/30 text-white border border-white/20 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-500/50 transition-all flex items-center gap-3 transform active:scale-95">
                                <i class="fas fa-layer-group"></i> Bulk Update Registry
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6 w-full lg:w-96 shrink-0">
                        <div v-for="m in builderStats" :key="m.label" class="bg-white/5 p-8 rounded-[2.5rem] border border-white/10 hover:bg-white/10 transition-all group/stat relative overflow-hidden">
                            <p class="text-[9px] font-black text-white/30 uppercase tracking-[0.3em] mb-4 font-sans italic">{{ m.label }}</p>
                            <p class="text-3xl font-black tabular-nums tracking-tighter italic">{{ m.value }}</p>
                        </div>
                    </div>
                </div>
                <!-- Neural BG Decoration -->
                <div class="absolute -right-20 -top-20 w-[400px] h-[400px] bg-white/5 rounded-full blur-[120px] pointer-events-none"></div>
            </div>

            <!-- Library Matrix with Search -->
            <div class="bg-white rounded-[4rem] border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                <div class="p-10 border-b border-gray-50 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight uppercase italic mb-1">Active Syllabus Matrix</h3>
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Managing {{ stats.global_kpis?.course_count || 0 }} deployed learning vectors</p>
                    </div>
                    <div class="relative group w-full md:w-80">
                        <input 
                            type="text" 
                            v-model="search"
                            placeholder="Filter registry..." 
                            class="w-full pl-12 pr-6 py-4 bg-gray-50 border-none rounded-2xl text-[10px] font-bold text-gray-700 focus:ring-2 focus:ring-emerald-100 transition-all"
                        />
                        <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-emerald-500 transition-colors"></i>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-8 py-5 text-left text-[9px] font-black text-gray-400 uppercase tracking-widest">Protocol ID</th>
                                <th class="px-8 py-5 text-left text-[9px] font-black text-gray-400 uppercase tracking-widest">Syllabus Title</th>
                                <th class="px-8 py-5 text-left text-[9px] font-black text-gray-400 uppercase tracking-widest">Composition</th>
                                <th class="px-8 py-5 text-left text-[9px] font-black text-gray-400 uppercase tracking-widest">Enrolled Minds</th>
                                <th class="px-8 py-5 text-left text-[9px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                                <th class="px-8 py-5 text-right text-[9px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="(course, idx) in filteredCourses" :key="course.id" class="hover:bg-emerald-50/20 transition-all group">
                                <td class="px-8 py-6 text-xs font-black text-gray-300">#{{ 1000 + idx }}</td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-gray-900 flex items-center justify-center text-white text-[10px] font-black italic shadow-xl group-hover:bg-emerald-600 transition-colors">
                                           {{ course.title.charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-gray-900 uppercase italic group-hover:text-emerald-600 transition-colors">{{ course.title }}</p>
                                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest italic">{{ course.category?.name || 'GENERIC' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 bg-gray-50 rounded-lg text-[9px] font-black text-gray-500 uppercase tracking-widest">{{ course.modules_count || 0 }} MODS</span>
                                        <span class="px-2 py-1 bg-gray-50 rounded-lg text-[9px] font-black text-gray-500 uppercase tracking-widest">{{ course.topics_count || 0 }} TOPS</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-sm font-black text-gray-900 tabular-nums italic">
                                    {{ (course.enrolled_count || 0).toLocaleString() }}
                                </td>
                                <td class="px-8 py-6">
                                    <span :class="course.is_published ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-orange-100 text-orange-700 border-orange-200'" class="px-3 py-1 border rounded-lg text-[9px] font-black uppercase tracking-widest">
                                        {{ course.is_published ? 'Live' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="editStructure(course)" class="w-9 h-9 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-emerald-600 hover:text-white transition-all shadow-sm group/btn" title="Edit Structure">
                                            <i class="fas fa-sitemap text-[10px]"></i>
                                        </button>
                                        <button @click="manageContent(course)" class="w-9 h-9 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Manage Content">
                                            <i class="fas fa-photo-video text-[10px]"></i>
                                        </button>
                                        <button @click="togglePublish(course)" class="w-9 h-9 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-emerald-600 hover:text-white transition-all shadow-sm" :title="course.is_published ? 'Unpublish' : 'Publish'">
                                            <i :class="course.is_published ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-[10px]"></i>
                                        </button>
                                        <button @click="viewLearners(course)" class="w-9 h-9 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="View Learners">
                                            <i class="fas fa-users text-[10px]"></i>
                                        </button>
                                        <button @click="cloneCourse(course)" class="w-9 h-9 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Clone Course">
                                            <i class="fas fa-copy text-[10px]"></i>
                                        </button>
                                        <button @click="archiveCourse(course)" class="w-9 h-9 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-red-500 hover:text-white transition-all shadow-sm" title="Archive">
                                            <i class="fas fa-archive text-[10px]"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Advanced Architect View (Wizard) -->
        <div v-else-if="activeTab === 'architect' || activeTab === 'forge'" class="h-[calc(100vh-280px)]">
             <CourseWizard @cancel="$emit('action', { type: 'navigate', id: 'dashboard' })" />
        </div>

        <!-- Bulk Manager Placeholder -->
        <div v-else-if="activeTab === 'bulk'" class="bg-white p-20 rounded-[4rem] border border-gray-100 shadow-sm space-y-12">
            <div class="flex items-center justify-between border-b border-gray-50 pb-10">
                <div>
                    <h3 class="text-2xl font-black text-gray-900 uppercase italic">Bulk Asset Controller</h3>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Update 1000s of topics and lessons in a single execution</p>
                </div>
                <button class="px-8 py-4 bg-gray-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest">Execute Batch Order</button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="action in bulkActions" :key="action.title" class="p-8 bg-gray-50 rounded-[3rem] border border-transparent hover:border-emerald-200 transition-all group cursor-pointer">
                    <div class="w-14 h-14 rounded-2xl bg-white flex items-center justify-center text-xl text-emerald-600 mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                        <i :class="action.icon"></i>
                    </div>
                    <h4 class="text-sm font-black text-gray-900 uppercase italic mb-2">{{ action.title }}</h4>
                    <p class="text-[10px] font-medium text-gray-400 leading-relaxed font-sans mb-6 italic">{{ action.desc }}</p>
                    <div class="w-full h-1 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 w-0 group-hover:w-full transition-all duration-1000"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Structure Forge Placeholder -->
        <div v-else-if="activeTab === 'forge'" class="bg-emerald-50 p-20 rounded-[4rem] border-2 border-dashed border-emerald-100 flex flex-col items-center justify-center gap-8">
            <div class="w-20 h-20 bg-emerald-600 rounded-[2rem] flex items-center justify-center text-white text-3xl shadow-2xl shadow-emerald-200 animate-bounce">
                 <i class="fas fa-fire-flame-curved"></i>
            </div>
            <div class="text-center space-y-2">
                <h3 class="text-2xl font-black text-emerald-900 uppercase italic">STRUCTURAL FORGE ALPHA</h3>
                <p class="text-xs font-black text-emerald-600 uppercase tracking-widest">Generating algorithmic course paths for 100+ new units...</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import CourseWizard from './CourseWizard.vue';

const props = defineProps({
    stats: Object,
    activeTab: String
});

const emit = defineEmits(['action']);

const search = ref('');

const editStructure = (course) => {
    console.log('Editing Structure for:', course.title);
    emit('action', { type: 'navigate', id: 'courses', tab: 'architect' });
};

const manageContent = (course) => console.log('Managing Content for:', course.title);
const togglePublish = (course) => {
    course.is_published = !course.is_published;
    console.log('Toggling Publish for:', course.title);
};
const viewLearners = (course) => console.log('Viewing Learners for:', course.title);
const cloneCourse = (course) => console.log('Cloning Course:', course.title);
const archiveCourse = (course) => console.log('Archiving Course:', course.title);

const builderStats = [
    { label: 'Published Units', value: props.stats?.global_kpis?.course_count || '42' },
    { label: 'Draft Assets', value: '18' },
    { label: 'Module Variance', value: '0.4%' },
    { label: 'Syllabus Health', value: '98%' },
];

const toolbox = [
    { label: 'Logic Shaper', icon: 'fas fa-project-diagram' },
    { label: 'Narrative Engine', icon: 'fas fa-pen-nib' },
    { label: 'Quiz Architect', icon: 'fas fa-tasks' },
    { label: 'Media Injector', icon: 'fas fa-photo-video' },
    { label: 'AI Summarizer', icon: 'fas fa-robot' },
    { label: 'Code Lab Linker', icon: 'fas fa-code' },
];

const bulkActions = [
    { title: 'Global Publish', icon: 'fas fa-bullhorn', desc: 'Move all draft cycles to production ledger.' },
    { title: 'Metadata Sync', icon: 'fas fa-sync-alt', desc: 'Standardize SEO & taxonomies across all assets.' },
    { title: 'Enrolled Purge', icon: 'fas fa-user-minus', desc: 'Archive completed learner matrices globally.' },
    { title: 'Task Migration', icon: 'fas fa-file-export', desc: 'Transfer assignments between syllabus versions.' },
    { title: 'Revenue Re-calc', icon: 'fas fa-calculator', desc: 'Apply new pricing logic to historical ledger.' },
    { title: 'Health Audit', icon: 'fas fa-stethoscope', desc: 'Inspect link integrity for 10,000+ topics.' },
];

// Mock courses for "50+ Courses" demonstration
const mockCourses = Array.from({ length: 12 }, (_, i) => ({
    id: i + 1,
    title: [
        'Advanced Quantum Architecture',
        'Strategic Logic Formulation',
        'Enterprise Asset Protocols',
        'Distributed Intelligence Cycles',
        'Neural Pathway Design',
        'Spectral Data Management'
    ][i % 6] + ` v.${(i%3)+1}.0`,
    category: { name: ['Logic', 'Design', 'Systems', 'Ops', 'Finance'][i % 5] },
    modules_count: 10 + i,
    topics_count: 80 + (i * 5),
    enrolled_count: 100 + (i * 350),
    is_published: i % 4 !== 0,
}));

const filteredCourses = computed(() => {
    if (!search.value) return mockCourses;
    return mockCourses.filter(c => c.title.toLowerCase().includes(search.value.toLowerCase()));
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }

@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    animation: spin-slow 12s linear infinite;
}
</style>

