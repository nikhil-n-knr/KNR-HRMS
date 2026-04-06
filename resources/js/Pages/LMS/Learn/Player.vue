<template>
  <div class="min-h-screen bg-slate-50 text-slate-800 flex flex-col overflow-hidden">
    <Head :title="course?.title + ' | Learning Player'" />

    <!-- Top Navigation -->
    <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 z-30 shadow-sm relative">
      <div class="flex items-center gap-4">
        <Link href="/learn/hub" class="p-2.5 hover:bg-slate-50 rounded-xl transition-all group border border-transparent hover:border-slate-200">
          <ArrowLeftIcon class="h-5 w-5 text-slate-400 group-hover:text-emerald-600" />
        </Link>
        <div class="hidden sm:block">
          <h1 class="text-sm font-black text-slate-800 truncate max-w-[300px] uppercase tracking-tighter italic">{{ course?.title }}</h1>
          <p class="text-[9px] text-slate-400 uppercase tracking-[0.2em] font-black">{{ activeConcept ? activeConcept.title : 'Orchestrating...' }}</p>
        </div>
      </div>

      <div class="flex items-center gap-3 sm:gap-6">
        <!-- Progress Circular -->
        <div class="flex items-center gap-4 bg-slate-50 px-4 py-2 rounded-2xl border border-slate-100 shadow-inner">
          <div class="relative w-10 h-10 flex items-center justify-center">
            <svg class="w-full h-full transform -rotate-90">
              <circle cx="20" cy="20" r="18" stroke="currentColor" stroke-width="3" fill="transparent" class="text-slate-200" />
              <circle cx="20" cy="20" r="18" stroke="currentColor" stroke-width="4" fill="transparent" class="text-emerald-600"
                :stroke-dasharray="113" :stroke-dashoffset="113 - (113 * (progress?.completion_pct || 0) / 100)"
                stroke-linecap="round" />
            </svg>
            <span class="absolute text-[10px] font-black text-slate-800 italic">{{ Math.round(progress?.completion_pct || 0) }}%</span>
          </div>
          <div class="hidden md:block">
            <div class="text-[9px] text-slate-400 uppercase font-black tracking-widest leading-none mb-1">Vector Progress</div>
            <div class="text-xs font-black text-slate-700 italic">{{ progress?.concepts_completed || 0 }} / {{ progress?.concepts_total || 0 }} Data Points</div>
          </div>
        </div>

        <button @click="toggleSidebar" class="lg:hidden p-2.5 hover:bg-slate-50 rounded-xl border border-transparent hover:border-slate-200">
          <Bars3Icon v-if="!sidebarOpen" class="h-6 w-6 text-slate-600" />
          <XMarkIcon v-else class="h-6 w-6 text-slate-600" />
        </button>
      </div>
    </header>

    <div class="flex-1 flex overflow-hidden relative">
      <!-- Panel 1: Neural Map (Syllabus) -->
      <aside :class="['absolute lg:relative inset-y-0 left-0 w-80 bg-white border-r border-slate-100 z-40 transform transition-transform duration-700 ease-in-out lg:translate-x-0 shadow-2xl lg:shadow-none', sidebarOpen ? 'translate-x-0' : '-translate-x-full']">
        <div class="h-full flex flex-col">
          <div class="p-8 border-b border-slate-50 bg-slate-50/30">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4">Neural Architecture</h3>
            <div class="relative group">
              <input type="text" v-model="searchQuery" placeholder="Search data points..." 
                class="w-full bg-white border border-slate-100 rounded-2xl px-10 py-3 text-[10px] font-black text-slate-700 focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-500 outline-none transition-all placeholder:text-slate-300 shadow-sm" />
              <MagnifyingGlassIcon class="absolute left-3.5 top-3 h-4 w-4 text-slate-300" />
            </div>
          </div>

          <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-4">
            <div v-for="module in course.modules" :key="module.id" class="space-y-2">
              <button @click="toggleModule(module.id)" class="w-full flex items-center justify-between p-4 rounded-3xl hover:bg-slate-50 group transition-all">
                <span class="text-[10px] font-black text-slate-600 uppercase italic">{{ module.title }}</span>
                <ChevronDownIcon :class="['h-3 w-3 text-slate-300 transition-transform duration-500', expandedModules.includes(module.id) ? 'rotate-180' : '']" />
              </button>

              <div v-show="expandedModules.includes(module.id)" class="space-y-2 pl-4 border-l-2 border-slate-50 ml-2">
                <div v-for="ch in module.chapters" :key="ch.id" class="space-y-1">
                   <button v-for="concept in ch.concepts" :key="concept.id"
                    @click="selectConcept(concept)"
                    :disabled="isNodeLocked(concept)"
                    :class="['w-full group rounded-2xl p-4 flex items-start gap-3 transition-all border text-left relative overflow-hidden', 
                             activeConcept?.id === concept.id ? 'bg-white border-emerald-500 shadow-lg' : 'hover:bg-slate-50 border-transparent',
                             isNodeLocked(concept) ? 'opacity-40 grayscale cursor-not-allowed' : '']">
                    
                    <div class="shrink-0 mt-0.5">
                        <i v-if="isNodeLocked(concept)" class="fas fa-lock text-[10px] text-slate-300"></i>
                        <i v-else-if="isCompleted(concept.id)" class="fas fa-check-circle text-[10px] text-emerald-500"></i>
                        <div v-else class="w-2.5 h-2.5 rounded-full bg-slate-200 group-hover:bg-emerald-400"></div>
                    </div>

                    <div class="flex-1">
                      <p :class="['text-[10px] font-black uppercase tracking-tight leading-tight mb-1 italic', activeConcept?.id === concept.id ? 'text-emerald-700' : 'text-slate-400 group-hover:text-slate-600']">
                        {{ concept.title }}
                      </p>
                      <span class="text-[8px] font-black text-slate-300 uppercase italic">{{ getActivityLabel(concept) }}</span>
                    </div>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </aside>

      <!-- Panel 2: Immersive Content Hub -->
      <main class="flex-1 flex flex-col overflow-hidden bg-white">
        <div id="player-container" class="flex-1 overflow-y-auto custom-scrollbar relative px-6 md:px-12 py-12">
          <div class="max-w-4xl mx-auto space-y-12">
              <div class="space-y-4">
                 <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase rounded-lg border border-emerald-100">Sync Active</span>
                    <span class="text-[10px] text-slate-300 font-black uppercase italic">{{ activeChapter?.title }}</span>
                 </div>
                 <h2 class="text-4xl md:text-5xl font-black text-slate-800 uppercase tracking-tighter italic leading-none">{{ activeConcept?.title }}</h2>
              </div>

              <div class="bg-gray-900 rounded-[3rem] overflow-hidden shadow-2xl relative group border-[8px] border-slate-50">
                  <!-- Video View -->
                  <div v-if="activeActivity?.type === 'video'" class="relative aspect-video">
                      <video ref="videoPlayer" class="w-full h-full" controls playsinline
                        @timeupdate="onVideoTimeUpdate" @ended="onVideoEnded">
                        <source :src="activeActivityContent?.video_url" type="video/mp4" />
                      </video>
                      <!-- Progress Lock HUD -->
                      <div v-if="!canAdvance" class="absolute bottom-16 right-8 bg-black/60 backdrop-blur-md px-6 py-3 rounded-2xl border border-white/20 text-white flex items-center gap-4 animate-fade-in">
                          <i class="fas fa-lock text-emerald-400 animate-pulse"></i>
                          <p class="text-[9px] font-black uppercase tracking-widest leading-none">Complete 90% of stream to advance</p>
                      </div>
                  </div>

                  <!-- Reading View -->
                  <div v-else-if="activeActivity?.type === 'reading'" class="bg-white p-16 text-slate-700 min-h-[500px]">
                      <div class="prose prose-slate max-w-none italic font-medium leading-relaxed" v-html="activeActivityContent?.content"></div>
                  </div>
              </div>

              <!-- Footer Actions -->
              <div class="flex items-center justify-between pt-12 border-t border-slate-50">
                  <button @click="prevConcept" :disabled="!canGoPrev" class="px-8 py-4 bg-slate-50 text-slate-400 rounded-2xl text-[9px] font-black uppercase flex items-center gap-3 hover:bg-slate-100 transition-all disabled:opacity-30">
                      <i class="fas fa-chevron-left"></i> Previous Node
                  </button>
                  <button @click="markCompleted" :disabled="!canAdvance" 
                    :class="['px-12 py-5 rounded-[2rem] text-[10px] font-black uppercase tracking-widest transition-all shadow-xl flex items-center gap-4',
                             canAdvance ? 'bg-emerald-600 text-white hover:bg-black' : 'bg-slate-100 text-slate-300 cursor-not-allowed border border-slate-200 shadow-none']">
                      <span>{{ isCompleted(activeConcept?.id) ? 'Node Mastered' : 'Commit & Advance' }}</span>
                      <i :class="isCompleted(activeConcept?.id) ? 'fas fa-check-double' : 'fas fa-chevron-right'"></i>
                  </button>
              </div>
          </div>
        </div>
      </main>

      <!-- Panel 3: Neural Notebook (Insights) -->
      <aside class="hidden xl:flex w-96 bg-slate-50 border-l border-slate-100 flex-col overflow-hidden">
          <div class="p-10 border-b border-slate-200/50 bg-white">
              <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-[0.3em] mb-6 italic">Neural Notebook</h3>
              <div class="space-y-6">
                <div class="p-6 bg-slate-50 rounded-[2rem] border border-slate-100 space-y-3 relative overflow-hidden group">
                    <p class="text-[9px] font-black text-emerald-600 uppercase">Context Optimization</p>
                    <p class="text-[11px] text-slate-500 italic leading-relaxed font-medium">Auto-mapping active concepts to career trajectory. Parity: 98.4%</p>
                    <div class="absolute -right-4 -bottom-4 w-12 h-12 bg-emerald-500 opacity-5 rounded-full group-hover:scale-150 transition-transform"></div>
                </div>
              </div>
          </div>

          <div class="flex-1 overflow-y-auto p-10 space-y-10">
              <div class="space-y-4">
                  <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">Draft Neural Insight</label>
                  <textarea placeholder="Capture data points..." class="w-full h-40 bg-white border border-slate-100 rounded-[2rem] p-6 text-[11px] font-medium italic focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-500 outline-none resize-none transition-all shadow-sm"></textarea>
                  <button class="w-full py-4 bg-gray-900 text-white rounded-2xl text-[9px] font-black uppercase hover:bg-emerald-600 transition-all shadow-lg">Save To Cloud Storage</button>
              </div>

              <div class="space-y-6 pt-10 border-t border-slate-200/50">
                  <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Mastery Requirements</p>
                  <div class="space-y-4">
                      <div v-for="rule in masteryRules" :key="rule.id" class="flex gap-4 items-center">
                          <div :class="['w-2 h-2 rounded-full', rule.met ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]' : 'bg-slate-200']"></div>
                          <span :class="['text-[10px] font-black uppercase italic leading-none', rule.met ? 'text-slate-800' : 'text-slate-400']">{{ rule.label }}</span>
                      </div>
                  </div>
              </div>
          </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
  ArrowLeftIcon, Bars3Icon, XMarkIcon, ChevronDownIcon, CheckIcon,
  VideoCameraIcon, DocumentTextIcon, PencilSquareIcon, CloudArrowUpIcon,
  CheckCircleIcon, MagnifyingGlassIcon, LightBulbIcon, DocumentIcon,
  ChevronLeftIcon, ChevronRightIcon, ArrowDownTrayIcon, PlayCircleIcon,
  PresentationChartBarIcon, ClockIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  course: Object,
  progress: Object,
  active_concept_id: [Number, String],
  completed_concept_ids: Array
});

// State
const sidebarOpen = ref(false);
const searchQuery = ref('');
const expandedModules = ref([props.course?.modules[0]?.id]);
const activeConcept = ref(null);
const activeActivity = ref(null);
const activeActivityContent = ref(null);
const activeCheckpoint = ref(null);
const checkpointError = ref('');
const videoLoading = ref(false);
const videoPlayer = ref(null);
const lastHeartbeatTime = ref(0);
const activeInfoTab = ref('outcomes');
const submittingAssignment = ref(false);
const assignmentForm = ref({ text: '', files: [] });
const watchProgress = ref(0); // For video time logic

// Rules Logic
const canAdvance = computed(() => {
    if (isCompleted(activeConcept.value?.id)) return true;
    if (activeActivity.value?.type === 'video') return watchProgress.value >= 90;
    if (activeActivity.value?.type === 'reading') return true; // Could add scroll check here
    return false;
});

const isNodeLocked = (concept) => {
    if (isCompleted(concept.id)) return false;
    const index = currentFlattenedConcepts.value.findIndex(c => c.id === concept.id);
    if (index === 0) return false;
    const prev = currentFlattenedConcepts.value[index - 1];
    return !isCompleted(prev.id);
};

const masteryRules = computed(() => [
    { id: 1, label: 'Stream Synchronization', met: watchProgress.value > 0 },
    { id: 2, label: '90% Retention Threshold', met: watchProgress.value >= 90 },
    { id: 3, label: 'Interaction Buffer', met: true },
]);

const infoTabs = [
  { id: 'outcomes', label: 'Learning Meta' },
  { id: 'resources', label: 'Nodes & Assets' },
  { id: 'notes', label: 'Neural Logs' },
];

// Computed
const currentFlattenedConcepts = computed(() => {
  let list = [];
  props.course.modules.forEach(m => {
    m.chapters.forEach(ch => {
      ch.concepts.forEach(c => {
        list.push({ ...c, moduleId: m.id, chapterId: ch.id });
      });
    });
  });
  return list;
});

const currentConceptIndex = computed(() => 
  currentFlattenedConcepts.value.findIndex(c => c.id == activeConcept.value?.id)
);

const canGoPrev = computed(() => currentConceptIndex.value > 0);
const canGoNext = computed(() => currentConceptIndex.value < currentFlattenedConcepts.value.length - 1 && isCompleted(activeConcept.value?.id));
const prevConceptData = computed(() => canGoPrev.value ? currentFlattenedConcepts.value[currentConceptIndex.value - 1] : null);
const nextConceptData = computed(() => canGoNext.value ? currentFlattenedConcepts.value[currentConceptIndex.value + 1] : null);

const activeModule = computed(() => props.course.modules.find(m => m.id === activeConcept.value?.moduleId));
const activeChapter = computed(() => activeModule.value?.chapters.find(ch => ch.id === activeConcept.value?.chapterId));

// Methods
const toggleSidebar = () => sidebarOpen.value = !sidebarOpen.value;
const toggleModule = (id) => {
  if (expandedModules.value.includes(id)) {
    expandedModules.value = expandedModules.value.filter(mid => mid !== id);
  } else {
    expandedModules.value.push(id);
  }
};

const selectConcept = (concept) => {
  if (isNodeLocked(concept)) return;
  
  activeConcept.value = concept;
  activeActivity.value = concept.activities?.[0] || null;
  activeActivityContent.value = activeActivity.value?.activityable || null;
  watchProgress.value = 0; // Reset for new node
  
  if (window.innerWidth < 1024) sidebarOpen.value = false;
  
  if (activeActivity.value?.type === 'video') {
    videoLoading.value = true;
    setTimeout(() => videoLoading.value = false, 500);
  }

  const container = document.getElementById('player-container');
  if (container) container.scrollTo({ top: 0, behavior: 'smooth' });
};

const isCompleted = (id) => props.completed_concept_ids?.includes(id);

const onVideoTimeUpdate = () => {
  if (!videoPlayer.value) return;
  const currentTime = Math.floor(videoPlayer.value.currentTime);
  const duration = Math.floor(videoPlayer.value.duration);
  
  if (duration > 0) {
      watchProgress.value = (currentTime / duration) * 100;
  }

  if (currentTime - lastHeartbeatTime.value >= 10) {
    sendHeartbeat(currentTime);
    lastHeartbeatTime.value = currentTime;
  }
};

const onVideoEnded = () => {
    watchProgress.value = 100;
};

const sendHeartbeat = (position) => {
  if (!activeActivityContent.value?.id) return;
  axios.post(route('lms.learn.video.heartbeat', activeActivityContent.value.id), {
    current_position: Math.floor(position),
    segment_start: Math.floor(lastHeartbeatTime.value),
    segment_end: Math.floor(position),
    enrollment_id: props.enrollment.id,
    total_time: Math.floor(videoPlayer.value.duration),
  });
};

const submitCheckpointAnswer = (optId) => {
  axios.post(route('lms.learn.video.checkpoint', activeActivityContent.value.id), {
    checkpoint_id: activeCheckpoint.value.id,
    answer: optId
  }).then(res => {
    if (res.data.is_correct) {
      activeCheckpoint.value = null;
      videoPlayer.value.play();
    } else {
      checkpointError.value = res.data.explanation || 'Verification failed. Re-index current segment.';
    }
  });
};

const markCompleted = async () => {
  if (!canAdvance.value) return;
  
  try {
      await axios.post(route('lms.learn.concept.complete', activeConcept.value.id));
      router.reload();
  } catch (err) {
      console.error(err);
  }
};

const prevConcept = () => canGoPrev.value && selectConcept(prevConceptData.value);
const nextConcept = () => canGoNext.value && selectConcept(nextConceptData.value);

const getActivityIcon = (concept) => {
  const type = concept.activities?.[0]?.type;
  return {
    video:      VideoCameraIcon,
    reading:    DocumentTextIcon,
    quiz:       PencilSquareIcon,
    assignment: DocumentIcon,
    live_session: VideoCameraIcon,
  }[type] || PlayCircleIcon;
};

const getActivityLabel = (concept) => {
  const type = concept.activities?.[0]?.type;
  return {
    video: 'Video Stream', reading: 'Archive', quiz: 'Evaluation', assignment: 'Delta Project', live_session: 'Direct Sync'
  }[type] || 'Node';
};

const onAssignFileSelect = (e) => {
  const selected = Array.from(e.target.files);
  assignmentForm.value.files.push(...selected);
};

const submitAssignment = async () => {
  if (!activeActivity.value?.id) return;
  submittingAssignment.value = true;
  const formData = new FormData();
  formData.append('text_content', assignmentForm.value.text);
  assignmentForm.value.files.forEach((file, idx) => {
    formData.append(`files[${idx}]`, file);
  });
  try {
    await axios.post(route('lms.learn.assignment.submit', activeActivity.value.id), formData);
    router.reload();
  } catch (err) {
    console.error(err);
  } finally {
    submittingAssignment.value = false;
  }
};

onMounted(() => {
  const initial = currentFlattenedConcepts.value.find(c => c.id == props.active_concept_id) || currentFlattenedConcepts.value[0];
  if (initial) selectConcept(initial);
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, 0.05); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(0, 0, 0, 0.1); }
.animate-fade-in-up { animation: fadeInUp 0.5s ease-out; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
