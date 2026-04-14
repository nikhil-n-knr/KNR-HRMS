<template>
  <div class="h-screen flex flex-col bg-slate-50 overflow-hidden font-sans">
    <!-- Project Header (Conditional) -->
    <ProjectHeader v-if="currentProjectDetail" :project="currentProjectDetail" />

    <!-- Planner Toolbar -->
    <div class="h-auto md:h-16 bg-white/80 backdrop-blur-xl border-b border-gray-200 flex flex-col md:flex-row items-stretch md:items-center justify-between px-4 md:px-6 z-20 shadow-sm relative">
      <div class="flex flex-col md:flex-row items-start md:items-center gap-4 md:gap-6 py-3 md:py-0">
        <h1 class="text-lg md:text-xl font-black tracking-tight shrink-0">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-700 to-violet-700">Visual Planner</span>
        </h1>
        
        <!-- View Switcher -->
        <div class="bg-gray-100/80 p-1 rounded-2xl flex gap-1 shadow-inner overflow-x-auto no-scrollbar snap-x w-full md:w-auto border border-gray-200/50">
          <button 
            v-for="view in ['Gantt', 'Matrix', 'List', 'Reports', 'Documents']" 
            :key="view"
            @click="currentView = view"
            class="px-4 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all duration-300 flex items-center gap-2 whitespace-nowrap snap-center min-w-[100px] justify-center"
            :class="currentView === view ? 'bg-white shadow-lg shadow-indigo-500/10 text-indigo-600 ring-1 ring-indigo-500/10 scale-[1.02]' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-200/50'"
          >
            {{ view }}
          </button>
        </div>

        <!-- Search Bar -->
        <div class="relative group w-full md:w-64">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input 
                v-model="searchQuery" 
                type="text" 
                placeholder="Search plan..." 
                class="w-full pl-9 pr-4 py-1.5 text-xs border border-gray-200 rounded-lg bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all font-bold"
            >
        </div>
      </div>

      <div class="flex items-center gap-3 py-3 md:py-0 justify-end border-t md:border-t-0 border-gray-100">
        <!-- New Task Button -->
        <button 
            @click="openTaskModal()"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black uppercase tracking-widest rounded-lg shadow-lg shadow-indigo-600/20 transition-all flex items-center gap-2"
        >
            <span class="text-lg leading-none">+</span>
            <span class="hidden sm:inline">New Task</span>
        </button>

        <div class="w-px h-6 bg-gray-200 mx-1 hidden md:block"></div>

        <button 
          @click="showHelpModal = true"
          class="p-2 text-indigo-400 hover:text-indigo-600 transition-colors bg-indigo-50 rounded-lg"
          title="Guide"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
        </button>

        <button 
          @click="loadPlannerData"
          class="p-2 text-gray-400 hover:text-indigo-600 transition-colors"
          title="Refresh"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v3.283a1 1 0 11-2 0V12.1a1 1 0 11-2 2v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 relative overflow-hidden flex">
       <!-- Sidebar (Backlog) -->
       <div 
          class="fixed md:relative inset-y-0 left-0 w-80 bg-white border-r border-gray-200 flex flex-col z-[40] transition-all duration-300 ease-in-out shadow-2xl md:shadow-[4px_0_24px_rgba(0,0,0,0.02)]" 
          :class="showSidebar ? 'translate-x-0' : '-translate-x-full md:-ml-80'"
       >
          <div class="p-6 border-b border-gray-100 bg-gray-50/30 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] flex items-center gap-2">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                       <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                   </svg>
                   Backlog
                </h3>
                <span class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-md text-sm font-black border border-indigo-100">{{ unassignedTasks.length }}</span>
            </div>
            
            <!-- Project Filter -->
            <select v-model="projectFilter" class="w-full text-xs font-black uppercase tracking-widest border-gray-200 rounded-xl bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 p-3 shadow-sm transition-all">
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
               class="p-4 bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-xl cursor-grab active:cursor-grabbing group transition-all duration-300 hover:border-indigo-300 relative overflow-hidden"
             >
                <div class="absolute left-0 top-0 bottom-0 w-1.5" :class="getPriorityColor(task.priority)"></div>
                
                <div class="flex justify-between items-start mb-2 pl-2">
                   <h4 class="text-xs font-black text-gray-800 tracking-tight group-hover:text-indigo-600 transition-colors line-clamp-2 leading-snug uppercase">
                       {{ task.text }}
                   </h4>
                </div>
                
                <div class="mt-4 flex items-center justify-between text-sm pl-2 font-black uppercase tracking-widest text-gray-400">
                   <span class="flex items-center gap-1.5 bg-gray-50 px-2 py-1 rounded-lg border border-gray-100">
                       <ClockIcon class="h-3 w-3" />
                       {{ task.duration }} Days
                   </span>
                   <span class="truncate max-w-[100px] text-gray-300 group-hover:text-gray-500 transition-colors flex items-center gap-1" :title="task.project_name">
                       {{ task.project_name }}
                       <span v-if="task.stage_name" class="text-[10px] opacity-60 bg-gray-100 px-1 rounded">{{ task.stage_name }}</span>
                   </span>
                </div>
             </div>
             
             <!-- Empty State -->
             <div v-if="unassignedTasks.length === 0" class="flex flex-col items-center justify-center py-20 bg-gray-50/50 rounded-3xl border border-dashed border-gray-200">
                <p class="text-sm font-black uppercase tracking-widest text-gray-400">Backlog Clear</p>
             </div>
          </div>

          <!-- Mobile Close Button -->
          <button @click="showSidebar = false" class="md:hidden absolute top-4 right-4 p-2 bg-gray-100 rounded-xl text-gray-400">
            <XMarkIcon class="w-5 h-5" />
          </button>
       </div>

       <!-- Overlay for mobile sidebar -->
       <div v-if="showSidebar" @click="showSidebar = false" class="md:hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[35] animate-in fade-in duration-300"></div>

       <!-- Toggle Sidebar Handle -->
       <button 
         @click="showSidebar = !showSidebar"
         class="fixed md:absolute bottom-6 left-6 z-40 md:z-30 bg-indigo-600 md:bg-white p-3 md:p-2 rounded-full shadow-2xl md:shadow-lg border border-transparent md:border-gray-200 hover:bg-indigo-700 md:hover:bg-gray-50 transition-all hover:scale-110 active:scale-95 text-white md:text-gray-600"
         title="Backlog"
       >
         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
         </svg>
       </button>

       <!-- Charts Area -->
       <div class="flex-1 bg-slate-50/50 relative overflow-hidden">
          <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white/80 z-50 backdrop-blur-md">
             <div class="flex flex-col items-center">
                <div class="relative w-16 h-16">
                     <div class="absolute inset-0 border-4 border-gray-200 rounded-full"></div>
                     <div class="absolute inset-0 border-4 border-indigo-600 rounded-full border-t-transparent animate-spin"></div>
                </div>
                <span class="mt-4 text-sm font-bold text-gray-600 tracking-wide">Initializing Planner...</span>
             </div>
          </div>

          <!-- Dynamic Component View -->
          <Transition name="fade" mode="out-in">
              <component 
                :is="currentViewComponent" 
                :data="filteredData" 
                :resources="resources"
                :availability="availability"
                :holidays="holidays"
                :workDays="workDays"
                :searchQuery="searchQuery"
                :projects="projects"
                @task-update="handleTaskUpdate"
                @task-click="openTaskModal"
                class="h-full w-full"
              />
          </Transition>
       </div>
    </div>

    <!-- Unified Task Modal -->
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

     <!-- Help Modal -->
    <Modal :show="showHelpModal" @close="showHelpModal = false" maxWidth="2xl">
        <div class="p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Planner Guide
            </h2>
            
            <div class="space-y-6">
                <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                    <h3 class="font-bold text-indigo-800 mb-1">Gantt Chart</h3>
                    <p class="text-sm text-indigo-700">Timeline view. Best for seeing project duration and dependencies. Drag bars to reschedule tasks.</p>
                </div>

                <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                    <h3 class="font-bold text-emerald-800 mb-1">Resource Matrix</h3>
                    <p class="text-sm text-emerald-700">Workload heat map. Best for spotting overloaded employees. Grid shows hours assigned per day.</p>
                </div>
                
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-1">List View</h3>
                    <p class="text-sm text-gray-600">Simple tabular view. Best for scanning task statuses and quick assignments without visual clutter.</p>
                </div>

                <div class="text-xs text-gray-500 pt-2 border-t border-gray-100">
                    <strong>Tip:</strong> Drag tasks from the "Unassigned" sidebar on the left to any view to assign them instantly.
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button @click="showHelpModal = false" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">
                    Got it
                </button>
            </div>
        </div>
    </Modal>
  </div>
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
const currentView = ref('Gantt'); // Default to Gantt
const showSidebar = ref(true);
const loading = ref(true);
const showHelpModal = ref(false); 
const showTaskModal = ref(false);
const focusedTaskId = ref(null);
const searchQuery = ref('');
const projectFilter = ref(props.initialProjectId ? parseInt(props.initialProjectId) : '');

// JSON headers for axios
const jsonHeaders = { headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' } };

const rawData = ref([]); 
const resources = ref([]); 
const availability = ref({});
const holidays = ref([]);
const workDays = ref({});

// Validations
// (Already imported above)

const errors = ref({}); // To store validation errors 

// Async View Components
const GanttChart = defineAsyncComponent(() => import('./Views/GanttChart.vue'));
const ResourceMatrix = defineAsyncComponent(() => import('./Views/ResourceMatrix.vue'));
const ListView = defineAsyncComponent(() => import('./Views/ListView.vue'));
const ProjectReports = defineAsyncComponent(() => import('./Views/ProjectReports.vue'));
const ProjectDocuments = defineAsyncComponent(() => import('./Views/ProjectDocuments.vue'));

// Task Form
const taskForm = reactive({
    id: null,
    title: '',
    project_id: '',
    stage_id: '',
    start_date: '',
    end_date: '',
    assignees: [], // Changed from assignee_id
    priority: 'Medium'
});

const availableStages = computed(() => {
    if (!taskForm.project_id) return [];
    const proj = projects.value.find(p => p.id === taskForm.project_id);
    return proj ? proj.stages : [];
});

const filters = ref({
   myTasks: false,
   critical: false
});

const currentViewComponent = computed(() => {
    switch(currentView.value) {
        case 'Gantt': return GanttChart;
        case 'Matrix': return ResourceMatrix;
        case 'List': return ListView;
        case 'Reports': return ProjectReports;
        case 'Documents': return ProjectDocuments;
        default: return GanttChart; 
    }
});

const projects = computed(() => {
    return rawData.value.filter(i => i.type === 'project');
});

const currentProjectDetail = computed(() => {
    if (!projectFilter.value) return null;
    return projects.value.find(p => p.id === projectFilter.value);
});

const unassignedTasks = computed(() => {
    return rawData.value.filter(item => 
       item.type !== 'project' && 
       (!item.assignments || item.assignments.length === 0) &&
       (!projectFilter.value || item.parent === projectFilter.value)
    );
});

const filteredData = computed(() => {
    let dataset = rawData.value;
    // Basic View Filters could go here if global
    return dataset;
});

const loadPlannerData = async () => {
    loading.value = true;
    try {
        // Use prop or URL param
        let pid = projectFilter.value || new URLSearchParams(window.location.search).get('project');
        
        if (pid && !projectFilter.value) {
            projectFilter.value = parseInt(pid);
        }

        const res = await axios.get(route('planner.data', { project: pid }), jsonHeaders);
        rawData.value = res.data.data; // Tasks + Projects
        resources.value = res.data.resources;
        availability.value = res.data.availability || {}; // Store Availability
        holidays.value = res.data.holidays || [];
        workDays.value = (res.data.workDays && Object.keys(res.data.workDays).length > 0) ? res.data.workDays : {
            'mon': true, 'tue': true, 'wed': true, 'thu': true, 'fri': true, 'sat': false, 'sun': false
        };
    } catch (e) {
        toast.error('Failed to load project data.');
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const handleTaskUpdate = (updatedTask) => {
    // Optimistic update handler from children
    const idx = rawData.value.findIndex(t => t.id === updatedTask.id);
    if (idx !== -1) {
        rawData.value[idx] = { ...rawData.value[idx], ...updatedTask };
    }
    // Reload full data in background to stay synced
    loadPlannerData();
};

const onDragStart = (evt, task) => {
    evt.dataTransfer.dropEffect = 'move';
    evt.dataTransfer.effectAllowed = 'move';
    evt.dataTransfer.setData('task_id', task.id);
    evt.dataTransfer.setData('source', 'backlog');
};

const getPriorityColor = (p) => {
    switch(p) {
        case 'Critical': return 'bg-red-500';
        case 'High': return 'bg-orange-500';
        default: return 'bg-indigo-500';
    }
};

// --- CRUD Actions ---

const openTaskModal = (task = null) => {
    focusedTaskId.value = task ? task.id : null;
    showTaskModal.value = true;
};

onMounted(() => {
    loadPlannerData();
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 4px;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
