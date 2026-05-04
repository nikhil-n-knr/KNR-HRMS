<template>
  <!-- ░░ Outer Page Shell ░░ -->
  <div class="min-h-screen bg-[#f4f5fa] flex flex-col font-sans overflow-hidden" style="height:200vh;">

    <!-- ▓▓ GRADIENT HERO HEADER ▓▓ -->
    <div class="relative overflow-hidden sm:rounded-2xl mx-0 sm:mx-6 mt-0 sm:mt-4 shrink-0
                bg-gradient-to-br from-[#3d27b4] via-[#6b3fd4] to-[#a855f7]">
      <!-- Decorative blobs -->
      <div class="absolute -top-16 -right-16 w-72 h-72 bg-white/5 rounded-full pointer-events-none"></div>
      <div class="absolute bottom-0 left-1/3 w-48 h-48 bg-white/5 rounded-full pointer-events-none"></div>

      <div class="relative z-10 px-5 sm:px-8 py-5
                  flex flex-col md:flex-row md:items-center justify-between gap-4">

        <!-- Left: title + view switcher + search -->
        <div class="flex flex-col md:flex-row md:items-center gap-4 min-w-0">

          <!-- Title -->
          <div class="shrink-0">
            <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-0.5">Projects</p>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-tight">
              Visual Planner
            </h1>
          </div>

          <!-- View Switcher -->
          <div class="flex items-center gap-1 bg-white/10 backdrop-blur-sm border border-white/20
                      rounded-2xl p-1 overflow-x-auto no-scrollbar shrink-0">
            <button
              v-for="view in ['Gantt', 'Matrix', 'List', 'Reports', 'Documents']"
              :key="view"
              @click="currentView = view"
              class="px-3 py-2 rounded-xl text-xs font-extrabold uppercase tracking-widest
                     transition-all duration-200 whitespace-nowrap"
              :class="currentView === view
                ? 'bg-white text-indigo-700 shadow-md'
                : 'text-white/60 hover:text-white hover:bg-white/10'"
            >
              {{ view }}
            </button>
          </div>

          <!-- Search -->
          <div class="relative group w-full md:w-56">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-white/40 group-hover:text-white/70 transition-colors pointer-events-none"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search plan…"
              class="w-full pl-9 pr-4 py-2 text-xs font-semibold rounded-xl
                     bg-white/10 border border-white/20 text-white placeholder-white/40
                     focus:bg-white/20 focus:border-white/40 focus:outline-none transition-all"
            />
          </div>
        </div>

        <!-- Right: stat pills + actions -->
        <div class="flex flex-wrap items-center gap-3 shrink-0">

          <!-- New Task -->
          <button
            @click="openTaskModal()"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl
                   bg-white text-indigo-700 text-sm font-extrabold
                   hover:bg-indigo-50 active:scale-[0.97] transition-all shadow-lg shadow-black/10"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span class="hidden sm:inline">New Task</span>
          </button>

          <!-- Help -->
          <button
            @click="showHelpModal = true"
            class="w-10 h-10 flex items-center justify-center rounded-2xl
                   bg-white/10 border border-white/20 text-white/70
                   hover:bg-white/20 hover:text-white transition-all"
            title="Guide"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
          </button>

          <!-- Refresh -->
          <button
            @click="loadPlannerData"
            class="w-10 h-10 flex items-center justify-center rounded-2xl
                   bg-white/10 border border-white/20 text-white/70
                   hover:bg-white/20 hover:text-white transition-all"
            title="Refresh"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v3.283a1 1 0 11-2 0V12.1a1 1 0 11-2 2v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Project Header strip (conditional) — inside hero, below toolbar -->
      <div v-if="currentProjectDetail" class="relative z-10 border-t border-white/10">
        <ProjectHeader :project="currentProjectDetail" />
      </div>
    </div>

    <!-- ▓▓ INNER CONTENT BODY — fills remaining height ▓▓ -->
    <div class="flex-1 flex overflow-hidden mx-0 sm:mx-6 mt-4 mb-4 sm:mb-6 rounded-none sm:rounded-2xl border border-slate-200 bg-white shadow-sm relative">

      <!-- ── Sidebar (Backlog) ── -->
      <div
        class="fixed md:relative inset-y-0 left-0 w-80 bg-white border-r border-slate-100
               flex flex-col z-[40] transition-all duration-300 ease-in-out
               shadow-2xl md:shadow-none"
        :class="showSidebar ? 'translate-x-0' : '-translate-x-full md:-ml-80'"
      >
        <div class="p-5 border-b border-slate-100 bg-slate-50/50 space-y-3">
          <div class="flex justify-between items-center">
            <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-[0.18em] flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
              </svg>
              Backlog
            </h3>
            <span class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-lg text-xs font-extrabold border border-indigo-100">
              {{ unassignedTasks.length }}
            </span>
          </div>

          <!-- Project Filter -->
          <select
            v-model="projectFilter"
            class="w-full text-xs font-bold uppercase tracking-wide border border-slate-200 rounded-xl
                   bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400
                   p-2.5 transition-all outline-none"
          >
            <option value="">Global Backlog</option>
            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.text }}</option>
          </select>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3 no-scrollbar pb-24">
          <div
            v-for="task in unassignedTasks"
            :key="task.id"
            draggable="true"
            @dragstart="onDragStart($event, task)"
            @click="openTaskModal(task)"
            class="p-4 bg-white border border-slate-100 rounded-2xl shadow-sm
                   hover:shadow-lg cursor-grab active:cursor-grabbing group
                   transition-all duration-200 hover:border-indigo-200 relative overflow-hidden"
          >
            <!-- Priority stripe -->
            <div class="absolute left-0 top-0 bottom-0 w-1.5 rounded-l-2xl" :class="getPriorityColor(task.priority)"></div>

            <div class="flex justify-between items-start mb-2 pl-2">
              <h4 class="text-xs font-extrabold text-slate-800 tracking-tight group-hover:text-indigo-600
                         transition-colors line-clamp-2 leading-snug uppercase">
                {{ task.text }}
              </h4>
            </div>

            <div class="mt-3 flex items-center justify-between text-[11px] pl-2 font-bold uppercase tracking-widest text-slate-400">
              <span class="flex items-center gap-1.5 bg-slate-50 px-2 py-1 rounded-lg border border-slate-100">
                <ClockIcon class="h-3 w-3" />
                {{ task.duration }} Days
              </span>
              <span class="truncate max-w-[100px] text-slate-300 group-hover:text-slate-500 transition-colors flex items-center gap-1"
                    :title="task.project_name">
                {{ task.project_name }}
                <span v-if="task.stage_name" class="text-[9px] opacity-60 bg-slate-100 px-1 rounded">{{ task.stage_name }}</span>
              </span>
            </div>
          </div>

          <!-- Empty State -->
          <div
            v-if="unassignedTasks.length === 0"
            class="flex flex-col items-center justify-center py-16 bg-slate-50/50
                   rounded-2xl border border-dashed border-slate-200"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-200 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-xs font-extrabold uppercase tracking-widest text-slate-400">Backlog Clear</p>
          </div>
        </div>

        <!-- Mobile Close -->
        <button @click="showSidebar = false"
                class="md:hidden absolute top-4 right-4 p-2 bg-slate-100 rounded-xl text-slate-400 hover:bg-slate-200 transition-colors">
          <XMarkIcon class="w-5 h-5" />
        </button>
      </div>

      <!-- Mobile overlay -->
      <div
        v-if="showSidebar"
        @click="showSidebar = false"
        class="md:hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[35]"
      ></div>

      <!-- Toggle Sidebar Handle -->
      <button
        @click="showSidebar = !showSidebar"
        class="fixed md:absolute bottom-6 left-6 z-40 md:z-30
               bg-indigo-600 md:bg-white
               p-3 md:p-2 rounded-full
               shadow-2xl md:shadow-md
               border border-transparent md:border-slate-200
               hover:bg-indigo-700 md:hover:bg-slate-50
               transition-all hover:scale-110 active:scale-95
               text-white md:text-slate-600"
        title="Toggle Backlog"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-5 md:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <!-- ── Charts / Main View Area ── -->
      <div class="flex-1 bg-slate-50/30 relative overflow-hidden">

        <!-- Loading overlay -->
        <div
          v-if="loading"
          class="absolute inset-0 flex items-center justify-center bg-white/80 z-50 backdrop-blur-md"
        >
          <div class="flex flex-col items-center">
            <div class="relative w-14 h-14">
              <div class="absolute inset-0 border-4 border-slate-100 rounded-full"></div>
              <div class="absolute inset-0 border-4 border-indigo-600 rounded-full border-t-transparent animate-spin"></div>
            </div>
            <span class="mt-4 text-sm font-bold text-slate-600 tracking-wide">Initializing Planner…</span>
          </div>
        </div>

        <!-- Dynamic View Component -->
        <Transition name="fade" mode="out-in">
          <component
            :is="currentViewComponent"
            :data="filteredData"
            :resources="resources"
            :availability="availability"
            :holidays="holidays"
            :workDays="workDays"
            :weekOffRules="weekOffRules"
            :searchQuery="searchQuery"
            :projects="projects"
            :initial-project-id="projectFilter || props.initialProjectId"
            :initial-report-view="initialReportView"
            @task-update="handleTaskUpdate"
            @task-click="openTaskModal"
            class="h-full w-full"
          />
        </Transition>
      </div>

    </div><!-- /inner content body -->

    <!-- ── Unified Task Modal ── -->
    <TaskFullModal
      :show="showTaskModal"
      :task-id="focusedTaskId"
      :projects="projects"
      :employees="resources"
      :task-templates="[]"
      :initial-data="{}"
      @close="showTaskModal = false"
      @success="loadPlannerData"
      @deleted="loadPlannerData"
    />

    <!-- ── Help Modal ── -->
    <Modal :show="showHelpModal" @close="showHelpModal = false" maxWidth="2xl">
      <div class="p-6 sm:p-8">
        <h2 class="text-xl font-extrabold text-slate-900 mb-6 flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          Planner Guide
        </h2>

        <div class="space-y-3">
          <div class="p-4 bg-indigo-50 rounded-2xl border border-indigo-100">
            <h3 class="font-extrabold text-indigo-800 text-sm mb-1">Gantt Chart</h3>
            <p class="text-xs text-indigo-700 leading-relaxed">Timeline view. Best for seeing project duration and dependencies. Drag bars to reschedule tasks.</p>
          </div>
          <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
            <h3 class="font-extrabold text-emerald-800 text-sm mb-1">Resource Matrix</h3>
            <p class="text-xs text-emerald-700 leading-relaxed">Workload heat map. Best for spotting overloaded employees. Grid shows hours assigned per day.</p>
          </div>
          <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200">
            <h3 class="font-extrabold text-slate-800 text-sm mb-1">List View</h3>
            <p class="text-xs text-slate-600 leading-relaxed">Simple tabular view. Best for scanning task statuses and quick assignments without visual clutter.</p>
          </div>
          <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100">
            <h3 class="font-extrabold text-amber-800 text-sm mb-1">Reports</h3>
            <p class="text-xs text-amber-700 leading-relaxed">Project analytics, performance, and operations metrics at a glance.</p>
          </div>
          <div class="p-4 bg-purple-50 rounded-2xl border border-purple-100">
            <h3 class="font-extrabold text-purple-800 text-sm mb-1">Documents</h3>
            <p class="text-xs text-purple-700 leading-relaxed">Manage all project-related files and documentation in one place.</p>
          </div>
          <div class="text-xs text-slate-500 pt-2 border-t border-slate-100 leading-relaxed">
            <strong>Tip:</strong> Drag tasks from the Backlog sidebar on the left to any view to assign them instantly.
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <button
            @click="showHelpModal = false"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition-all"
          >
            Got it
          </button>
        </div>
      </div>
    </Modal>

  </div><!-- /outer shell -->
</template>

<script setup>
import { ref, onMounted, computed, defineAsyncComponent, reactive } from 'vue';
import axios from 'axios';
import MainLayout from '@/Layouts/MainLayout.vue';
import ProjectHeader from '@/Components/Project/ProjectHeader.vue';
import dayjs from 'dayjs';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import MultiUserSelect from '@/Components/MultiUserSelect.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import TaskFullModal from '@/Components/Project/TaskFullModal.vue';
import { useToastStore } from '@/stores/toast';

defineOptions({ layout: MainLayout });

const props = defineProps({
    initialProjectId: { type: [String, Number], default: null }
});

const toast = useToastStore();

const reportTabFromUrl = new URLSearchParams(window.location.search).get('tab');
const initialReportView = ['extensions', 'performance', 'operations', 'standard'].includes(
    String(reportTabFromUrl || '').toLowerCase()
) ? String(reportTabFromUrl).toLowerCase() : 'standard';

const currentView    = ref(initialReportView !== 'standard' ? 'Reports' : 'Gantt');
const showSidebar    = ref(true);
const loading        = ref(true);
const showHelpModal  = ref(false);
const showTaskModal  = ref(false);
const focusedTaskId  = ref(null);
const searchQuery    = ref('');
const projectFilter  = ref(props.initialProjectId ? parseInt(props.initialProjectId) : '');

const jsonHeaders = { headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' } };

const rawData      = ref([]);
const resources    = ref([]);
const availability = ref({});
const holidays     = ref([]);
const workDays     = ref({});
const weekOffRules = ref([]);
const errors       = ref({});

// ── Async View Components ──
const GanttChart      = defineAsyncComponent(() => import('./Views/GanttChart.vue'));
const ResourceMatrix  = defineAsyncComponent(() => import('./Views/ResourceMatrix.vue'));
const ListView        = defineAsyncComponent(() => import('./Views/ListView.vue'));
const ProjectReports  = defineAsyncComponent(() => import('./Views/ProjectReports.vue'));
const ProjectDocuments = defineAsyncComponent(() => import('./Views/ProjectDocuments.vue'));

// ── Task Form ──
const taskForm = reactive({
    id: null,
    title: '',
    project_id: '',
    stage_id: '',
    start_date: '',
    end_date: '',
    assignees: [],
    priority: 'Medium'
});

const availableStages = computed(() => {
    if (!taskForm.project_id) return [];
    const proj = projects.value.find(p => p.id === taskForm.project_id);
    return proj ? proj.stages : [];
});

const filters = ref({ myTasks: false, critical: false });

const currentViewComponent = computed(() => {
    switch (currentView.value) {
        case 'Gantt':     return GanttChart;
        case 'Matrix':    return ResourceMatrix;
        case 'List':      return ListView;
        case 'Reports':   return ProjectReports;
        case 'Documents': return ProjectDocuments;
        default:          return GanttChart;
    }
});

const projects = computed(() => rawData.value.filter(i => i.type === 'project'));

const currentProjectDetail = computed(() => {
    if (!projectFilter.value) return null;
    return projects.value.find(p => p.id === projectFilter.value);
});

const unassignedTasks = computed(() => rawData.value.filter(item =>
    item.type !== 'project' &&
    (!item.assignments || item.assignments.length === 0) &&
    (!projectFilter.value || item.parent === projectFilter.value)
));

const filteredData = computed(() => rawData.value);

// ── Data Loading ──
const loadPlannerData = async () => {
    loading.value = true;
    try {
        let pid = projectFilter.value || new URLSearchParams(window.location.search).get('project');
        if (pid && !projectFilter.value) projectFilter.value = parseInt(pid);

        const res = await axios.get(route('planner.data', { project: pid }), jsonHeaders);
        rawData.value      = res.data.data;
        resources.value    = res.data.resources;
        availability.value = res.data.availability || {};
        holidays.value     = res.data.holidays || [];
        weekOffRules.value = res.data.weekOffRules || [];
        workDays.value     = (res.data.workDays && Object.keys(res.data.workDays).length > 0)
            ? res.data.workDays
            : { mon: true, tue: true, wed: true, thu: true, fri: true, sat: false, sun: false };
    } catch (e) {
        toast.error('Failed to load project data.');
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const handleTaskUpdate = (updatedTask) => {
    const idx = rawData.value.findIndex(t => t.id === updatedTask.id);
    if (idx !== -1) rawData.value[idx] = { ...rawData.value[idx], ...updatedTask };
    loadPlannerData();
};

const onDragStart = (evt, task) => {
    evt.dataTransfer.dropEffect = 'move';
    evt.dataTransfer.effectAllowed = 'move';
    evt.dataTransfer.setData('task_id', task.id);
    evt.dataTransfer.setData('source', 'backlog');
};

const getPriorityColor = (p) => {
    switch (p) {
        case 'Critical': return 'bg-rose-500';
        case 'High':     return 'bg-orange-500';
        default:         return 'bg-indigo-500';
    }
};

const openTaskModal = (task = null) => {
    focusedTaskId.value = task ? task.id : null;
    showTaskModal.value = true;
};

onMounted(() => { loadPlannerData(); });
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.fade-enter-active,
.fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from,
.fade-leave-to { opacity: 0; }
</style>
