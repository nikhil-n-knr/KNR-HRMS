<template>
  <MainLayout>
    <Head :title="'Builder: ' + course?.title" />

    <div class="h-screen bg-slate-50 flex flex-col overflow-hidden">
      <!-- Builder Top Navbar -->
      <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 z-30 shrink-0 shadow-sm">
        <div class="flex items-center gap-4">
          <Link href="/hr/lms" class="p-2.5 hover:bg-slate-100 rounded-xl transition-all group border border-transparent hover:border-slate-200">
            <ArrowLeftIcon class="h-5 w-5 text-slate-400 group-hover:text-emerald-600" />
          </Link>
          <div class="h-6 w-px bg-slate-100 mx-2"></div>
          <div>
            <h1 class="text-sm font-black text-slate-800 truncate max-w-[400px] uppercase tracking-tighter italic">{{ course?.title }}</h1>
            <div class="flex items-center gap-3 mt-0.5">
               <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Architectural Depth: 5 Layers</span>
               <div :class="['w-1.5 h-1.5 rounded-full', course?.is_published ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.3)]' : 'bg-slate-300']"></div>
               <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">{{ course?.is_published ? 'Production' : 'Blueprint' }}</span>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-5">
           <button @click="openReorderModal" class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-emerald-600 transition-colors">
              <ArrowsUpDownIcon class="h-4 w-4" />
              Reorder Graph
           </button>
           <button @click="publishCourse" 
             :class="['px-6 py-2.5 rounded-2xl text-[10px] font-black transition-all shadow-xl active:scale-95 uppercase tracking-widest', course?.is_published ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-slate-800 text-white hover:bg-black shadow-slate-900/10']">
             {{ course?.is_published ? 'Decommission' : 'Deploy Course' }}
           </button>
        </div>
      </header>

      <div class="flex-1 flex overflow-hidden">
        <!-- Structure Index Left Panel -->
        <aside class="w-80 border-r border-slate-200 bg-white/50 backdrop-blur-sm flex flex-col shrink-0">
          <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
             <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Architectural Graph</h3>
             <div class="flex items-center gap-2">
               <button @click="expandedModules = course.modules.map(m => m.id)" class="text-[9px] text-emerald-600 hover:text-emerald-700 font-black uppercase tracking-widest transition-colors">Expand</button>
               <button @click="expandedModules = []" class="text-[9px] text-slate-400 hover:text-slate-600 font-black uppercase tracking-widest transition-colors">Collapse</button>
             </div>
          </div>

          <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-4">
             <div v-for="module in course.modules" :key="module.id" class="group/module">
                <button @click="selectItem('module', module)"
                  :class="['w-full p-4 rounded-3xl transition-all flex items-center justify-between group', 
                    selectedItem?.id === module.id && selectedType === 'module' ? 'bg-emerald-50 border border-emerald-200 shadow-sm' : 'hover:bg-slate-50 border border-transparent']">
                   <div class="flex items-center gap-4">
                     <span class="text-[10px] font-black text-slate-300 group-hover:text-emerald-500 transition-colors">{{ module.sort_order }}</span>
                     <span :class="['text-xs font-black uppercase tracking-tighter italic truncate max-w-[150px]', selectedItem?.id === module.id && selectedType === 'module' ? 'text-emerald-800' : 'text-slate-600']">{{ module.title }}</span>
                   </div>
                   <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                     <button @click.stop="addChapter(module.id)" class="p-1 hover:text-emerald-600"><PlusCircleIcon class="h-4 w-4" /></button>
                     <ChevronRightIcon :class="['h-3 w-3 transition-transform text-slate-400', expandedModules.includes(module.id) ? 'rotate-90 text-emerald-500' : '']" />
                   </div>
                </button>

                <!-- Chapter / Concept Tree Level 2-3 -->
                <div v-show="expandedModules.includes(module.id)" class="ml-4 pl-4 border-l border-slate-100 mt-2 space-y-2 py-1">
                   <div v-for="chapter in module.chapters" :key="chapter.id" class="group/chapter">
                      <button @click="selectItem('chapter', chapter)"
                        :class="['w-full p-2.5 rounded-xl text-left transition-all flex items-center justify-between', 
                          selectedItem?.id === chapter.id && selectedType === 'chapter' ? 'bg-slate-100 text-emerald-700 font-black italic' : 'text-slate-400 hover:text-slate-600']">
                        <span class="text-[11px] uppercase tracking-tighter truncate">{{ chapter.title }}</span>
                        <div class="flex items-center gap-1 opacity-0 group-hover/chapter:opacity-100 transition-opacity">
                           <button @click.stop="addConcept(chapter.id, module.id)" class="p-1 hover:text-emerald-500"><PlusIcon class="h-3 w-3" /></button>
                        </div>
                      </button>

                      <div class="ml-3 pl-4 border-l border-slate-100 space-y-1 mt-1.5">
                        <button v-for="concept in chapter.concepts" :key="concept.id"
                          @click="selectItem('concept', concept)"
                          :class="['w-full p-2 rounded-xl text-[10px] transition-all flex items-center gap-2 group/concept', 
                            selectedItem?.id === concept.id && selectedType === 'concept' ? 'bg-emerald-600 text-white font-black shadow-lg shadow-emerald-500/20' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-50']">
                           <div :class="['w-1.5 h-1.5 rounded-full transition-colors', selectedItem?.id === concept.id && selectedType === 'concept' ? 'bg-white' : 'bg-slate-300']"></div>
                           <span class="truncate uppercase tracking-tight">{{ concept.title }}</span>
                           <component v-if="concept.activities?.[0]" :is="getActivityIcon(concept)" :class="['h-3 w-3 ml-auto opacity-40', selectedItem?.id === concept.id && selectedType === 'concept' ? 'text-white opacity-100' : '']" />
                        </button>
                      </div>
                   </div>
                </div>
             </div>

             <!-- Global Add Module -->
             <button @click="addModule" class="w-full mt-6 p-5 border-2 border-dashed border-slate-200 rounded-[32px] text-[10px] font-black text-slate-400 hover:border-emerald-500/50 hover:text-emerald-600 hover:bg-emerald-50 transition-all flex items-center justify-center gap-2 group italic">
                <PlusIcon class="h-4 w-4 group-hover:scale-110 transition-transform" />
                ORCHESTRATE NEW MODULE
             </button>
          </div>
        </aside>

        <!-- Main Workspace (Editor Area) -->
        <main class="flex-1 overflow-y-auto bg-white custom-scrollbar p-6 lg:p-16">
            <div v-if="!selectedItem" class="h-full flex flex-col items-center justify-center text-center max-w-lg mx-auto">
               <div class="w-24 h-24 bg-emerald-500/5 rounded-[40px] border border-emerald-100 flex items-center justify-center mb-8 text-emerald-500 shadow-2xl shadow-emerald-500/10">
                  <CubeIcon class="h-10 w-10 animate-pulse" />
               </div>
               <h2 class="text-3xl font-black text-slate-800 mb-4 uppercase tracking-tighter italic">Architecture Node</h2>
               <p class="text-slate-500 text-sm leading-relaxed mb-10 font-medium italic">Select an element from the left graph or spawn a new module to begin mapping the learning experience.</p>
               <div class="flex gap-3">
                  <div v-for="i in 3" :key="i" class="w-12 h-1 bg-slate-100 rounded-full"></div>
               </div>
            </div>

            <!-- Item Editor View -->
            <div v-else class="max-w-4xl mx-auto animate-fade-in">
                <!-- Editor Header -->
                <div class="flex items-center justify-between mb-12">
                   <div class="flex items-center gap-5">
                      <div class="p-4 bg-slate-800 text-white rounded-3xl shadow-2xl shadow-slate-900/10">
                        <component :is="getEditorIcon()" class="h-6 w-6" />
                      </div>
                      <div>
                        <h2 class="text-2xl font-black text-slate-800 capitalize leading-none tracking-tighter italic">{{ selectedType }} Settings</h2>
                        <p class="text-[9px] text-slate-400 uppercase font-black tracking-widest mt-2 italic">Vector: {{ selectedItem.id }} · Last Synced: {{ formatDate(selectedItem.updated_at) }}</p>
                      </div>
                   </div>

                   <div class="flex items-center gap-3">
                      <button @click="deleteItem" class="p-3 text-slate-300 hover:text-rose-500 hover:bg-rose-50 rounded-2xl transition-all"><TrashIcon class="h-5 w-5" /></button>
                      <button @click="saveItem" :disabled="saving" class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-[10px] font-black uppercase tracking-widest px-10 py-4 rounded-2xl shadow-xl shadow-emerald-500/20 transition-all active:scale-95">
                        {{ saving ? 'Transmitting...' : 'Commit Updates' }}
                      </button>
                   </div>
                </div>

                <!-- Form Fields based on Type -->
                <div class="space-y-16 pb-32">
                  <!-- Common Meta (Title/Desc) -->
                  <section class="space-y-8">
                    <div class="grid grid-cols-1 gap-8">
                       <div class="group/field">
                          <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 group-hover/field:text-emerald-600 transition-colors">Vector Title</label>
                          <input type="text" v-model="selectedItem.title" placeholder="Define the core value..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-8 py-5 text-slate-800 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all shadow-sm text-xl font-black tracking-tight" />
                       </div>
                       <div class="group/field">
                          <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 group-hover/field:text-emerald-600 transition-colors">Semantic Payload / Objectives</label>
                          <textarea v-model="selectedItem.description" placeholder="A brief overview or clear learning outcomes..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-3xl px-8 py-6 text-slate-700 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none h-48 resize-none transition-all shadow-sm leading-relaxed font-medium"></textarea>
                       </div>
                    </div>
                  </section>

                  <!-- ────────────────────────────────────────────────────────
                       CONCEPT SPECIFIC: ACTIVITY BUILDER
                       ──────────────────────────────────────────────────────── -->
                  <section v-if="selectedType === 'concept'" class="space-y-8 animate-fade-in-up">
                      <div class="flex items-center justify-between">
                         <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest flex items-center gap-3 italic">
                           <CpuChipIcon class="h-5 w-5 text-emerald-600" />
                           Activity Orchestrator
                         </h4>
                         <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Vector Configuration</span>
                      </div>

                      <!-- Concept Meta Config (Mandatory, Estimated Time, etc) -->
                      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                         <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/20">
                           <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Temporal Weight (Min)</label>
                           <input type="number" v-model="selectedItem.estimated_duration_minutes" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-3 text-slate-800 font-black" />
                         </div>
                         <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/20 flex items-center justify-between">
                           <div>
                             <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Mandatory Logic</label>
                             <p class="text-[9px] text-slate-400 mt-1 italic font-medium">Blocking dependency</p>
                           </div>
                           <Switch v-model="selectedItem.is_mandatory" />
                         </div>
                      </div>

                      <!-- Primary Activity Selector -->
                      <div v-if="!selectedItem.activities?.length" class="border-2 border-dashed border-slate-200 rounded-[48px] p-20 text-center group cursor-pointer hover:bg-emerald-50 hover:border-emerald-200 transition-all">
                         <div class="w-24 h-24 bg-emerald-600/5 rounded-[40px] flex items-center justify-center mx-auto mb-8 group-hover:scale-110 transition-transform">
                            <PlusIcon class="h-10 w-10 text-emerald-500" />
                         </div>
                         <h5 class="text-slate-800 font-black text-xl mb-3 uppercase tracking-tighter italic">Initialize Payload</h5>
                         <p class="text-slate-500 text-sm mb-12 max-w-sm mx-auto leading-relaxed font-medium italic">Assign a video node, cognitive evaluation, or textual archive to this topic.</p>
                         
                         <div class="flex flex-wrap items-center justify-center gap-4">
                           <button v-for="type in activityTypes" :key="type.id" @click="createActivity(type.id)"
                             class="flex items-center gap-3 bg-white hover:bg-slate-800 text-slate-500 hover:text-white px-6 py-4 rounded-3xl transition-all border border-slate-200 text-[10px] font-black uppercase tracking-widest shadow-lg shadow-slate-200/20">
                              <component :is="type.icon" class="h-4 w-4" />
                              {{ type.label }}
                           </button>
                         </div>
                      </div>

                      <!-- Active Activity Editor -->
                      <div v-else class="space-y-8">
                         <div v-for="act in selectedItem.activities" :key="act.id" 
                           class="bg-white rounded-[48px] p-10 border border-slate-100 shadow-2xl shadow-slate-200/40 relative group overflow-hidden">
                           <!-- Dynamic Icon based on type -->
                           <div class="absolute -top-12 -right-12 w-56 h-56 bg-emerald-500/5 rounded-full flex flex-col items-center justify-center">
                              <component :is="getActivityIconByType(act.type)" class="h-20 w-20 text-emerald-500/10" />
                           </div>

                           <div class="relative z-10">
                             <div class="flex items-center justify-between mb-10">
                                <div class="flex items-center gap-4">
                                   <div class="w-12 h-12 bg-emerald-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/20"><component :is="getActivityIconByType(act.type)" class="h-6 w-6" /></div>
                                   <h5 class="text-xl font-black text-slate-800 uppercase tracking-tighter italic">{{ act.type }} Component</h5>
                                </div>
                                <button class="text-slate-300 hover:text-rose-500 transition-colors"><TrashIcon class="h-6 w-6" /></button>
                             </div>

                             <!-- Content Specific Editors (Simplified for this demo) -->
                             <div v-if="act.type === 'video'" class="space-y-8">
                               <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                  <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Transmission Protocol</label>
                                    <select v-model="act.activityable.source_type" class="w-full bg-slate-50 border border-slate-100 rounded-2xl p-4 text-xs font-black text-slate-700">
                                      <option value="upload">Internal CDN (Lossless MP4)</option>
                                      <option value="vimeo">Vimeo Enterprise</option>
                                      <option value="youtube">Global YouTube Node</option>
                                    </select>
                                  </div>
                                  <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">URI / Pointer</label>
                                    <input type="text" v-model="act.activityable.video_url" class="w-full bg-slate-50 border border-slate-100 rounded-2xl p-4 text-xs font-black text-slate-800" placeholder="https://..." />
                                  </div>
                               </div>

                               <div class="p-8 bg-slate-50 rounded-[32px] border border-slate-100">
                                 <h6 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">Integrity & Telemetry Config</h6>
                                 <div class="flex flex-wrap gap-10 items-center">
                                    <div class="flex items-center gap-4">
                                      <Switch v-model="act.activityable.disable_seeking" />
                                      <span class="text-xs font-black text-slate-600 uppercase tracking-tighter">Timeline Lock</span>
                                    </div>
                                    <div class="flex items-center gap-4">
                                      <Switch v-model="act.activityable.random_check_popup" />
                                      <span class="text-xs font-black text-slate-600 uppercase tracking-tighter">Engagement Pulse</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                       <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Min Pct:</span>
                                       <input type="number" v-model="act.activityable.min_watch_pct" class="w-20 bg-white border border-slate-200 p-2 text-center text-xs font-black rounded-lg" />
                                       <span class="text-[11px] font-black text-slate-800">%</span>
                                    </div>
                                 </div>
                               </div>
                             </div>

                             <!-- READ CONTENT EDITOR -->
                             <div v-else-if="act.type === 'reading'" class="space-y-6">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Rich Metadata / Archive Content</label>
                                <div class="bg-slate-50 border border-slate-200 rounded-3xl min-h-[400px] overflow-hidden p-8 text-sm text-slate-700 font-medium italic leading-relaxed">
                                   <!-- This would be a WYSIWYG editor like TipTap -->
                                   <div class="opacity-40">Load semantic engine...</div>
                                </div>
                             </div>

                             <!-- ADDITIONAL ACTIVITY ADD -->
                             <button class="mt-10 flex items-center gap-2 text-emerald-600 text-[10px] font-black uppercase tracking-widest hover:text-emerald-700 transition-colors group">
                               <PlusCircleIcon class="h-5 w-5 group-hover:rotate-90 transition-transform" /> 
                               Attach Sequential Vector
                             </button>
                           </div>
                         </div>
                      </div>
                   </section>
                </div>
            </div>
        </main>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '../../../../Layouts/MainLayout.vue';
import { Switch } from '@headlessui/vue';
import { 
  ArrowLeftIcon, PlusCircleIcon, ChevronRightIcon, PlusIcon,
  DocumentIcon, ArrowsUpDownIcon, CubeIcon, TrashIcon,
  CpuChipIcon, VideoCameraIcon, PencilSquareIcon, DocumentTextIcon,
  CloudArrowUpIcon, CheckIcon, SquaresPlusIcon
} from '@heroicons/vue/24/outline';
import { 
  PlusIcon as PlusSolid, FolderIcon, AcademicCapIcon
} from '@heroicons/vue/24/solid';

const props = defineProps({
  course: Object
});

// Sidebar State
const expandedModules = ref(props.course.modules.map(m => m.id));
const searchQuery = ref('');
const selectedItem = ref(null);
const selectedType = ref(null);
const saving = ref(false);

const activityTypes = [
  { id: 'video',      label: 'Video',      icon: VideoCameraIcon },
  { id: 'quiz',       label: 'Quiz',       icon: PencilSquareIcon },
  { id: 'reading',    label: 'Article',    icon: DocumentTextIcon },
  { id: 'assignment', label: 'Assign',     icon: CloudArrowUpIcon },
  { id: 'live',       label: 'Live Class', icon: SquaresPlusIcon },
];

// Helper View Icons
const getEditorIcon = () => {
  if (selectedType.value === 'module') return FolderIcon;
  if (selectedType.value === 'chapter') return DocumentIcon;
  if (selectedType.value === 'concept') return CpuChipIcon;
  return CubeIcon;
};

const getActivityIcon = (concept) => {
  const type = concept.activities?.[0]?.type;
  return getActivityIconByType(type);
};

const getActivityIconByType = (type) => {
  return {
    video: VideoCameraIcon, reading: DocumentTextIcon, quiz: PencilSquareIcon,
    assignment: CloudArrowUpIcon, live_session: SquaresPlusIcon
  }[type] || DocumentIcon;
};

// CRUD
const selectItem = (type, item) => {
  selectedType.value = type;
  selectedItem.value = JSON.parse(JSON.stringify(item)); // Deep clone for editing
};

const saveItem = async () => {
  saving.value = true;
  const endpoint = `/lms/admin/courses/${props.course.id}/${selectedType.value}s/${selectedItem.value.id}`;
  try {
    await router.put(endpoint, selectedItem.value, { 
      preserveState: true,
      onSuccess: () => {
        // Refresh local structure if needed
      }
    });
  } catch (err) {
    console.error(err);
  } finally {
    saving.value = false;
  }
};

const addModule = () => {
  router.post(`/lms/admin/courses/${props.course.id}/modules`, { title: 'New Module' });
};

const addChapter = (moduleId) => {
  router.post(`/lms/admin/courses/${props.course.id}/modules/${moduleId}/chapters`, { title: 'New Chapter' });
};

const addConcept = (chapterId, moduleId) => {
  router.post(`/lms/admin/courses/${props.course.id}/modules/${moduleId}/chapters/${chapterId}/concepts`, { title: 'New Topic' });
};

const createActivity = (type) => {
  if (!selectedItem.value) return;
  router.post(`/lms/admin/courses/${props.course.id}/concepts/${selectedItem.value.id}/activities`, { type: type });
};

const formatDate = (date) => date ? new Date(date).toLocaleString() : 'Never';
const publishCourse = () => router.post(`/lms/admin/courses/${props.course.id}/publish`);
const deleteItem = () => {
   if (confirm('Are you sure? This will delete all child elements permanently.')) {
     // Implement delete logic
   }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
.animate-fade-in { animation: fadeIn 0.4s ease-out; }
.animate-fade-in-up { animation: fadeInUp 0.5s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.shadow-3xl { box-shadow: 0 40px 80px -20px rgba(79, 70, 229, 0.2); }
</style>
