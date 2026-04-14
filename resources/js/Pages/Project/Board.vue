<template>
    <ProjectLayout :project="project" title="Project Sprint Board">
        <div class="flex flex-col h-full overflow-hidden bg-gradient-to-br from-indigo-50/50 to-purple-50/50">
            <!-- Header Component -->
            <BoardHeader 
                :project="project"
                :current-sprint="currentSprint"
                :active-sprints="activeSprints"
                :planned-sprints="plannedSprints"
                @startSprint="startSprint"
                @completeSprint="openCompleteSprintModal"
                @openCreateModal="openCreateModal"
                @openSprintManager="openSprintManager"
                @openStageManager="showStageManager = true"
                @openPriorityManager="showPriorityManager = true"
                @openActivityLog="openActivityLog"
                @editSprint="handleEditSprint"
                @deleteSprint="handleDeleteSprint"
                @toggleReports="showReports = !showReports"
            />

            <!-- Reports View -->
            <div v-if="showReports" class="flex-1 overflow-hidden relative z-0">
                <ProjectReports :projects="[project]" />
            </div>

            <!-- Board Canvas -->
            <div v-else class="flex-1 overflow-x-auto overflow-y-hidden p-4 md:p-6 no-scrollbar h-full scroll-smooth snap-x">
                <div class="flex h-full gap-4 md:gap-6 min-w-max pb-2">
                    <!-- Dynamic Columns -->
                    <BoardColumn 
                        v-for="stage in project.stages" 
                        :key="stage.id"
                        :stage="stage"
                        :tasks="columns[stage.id] || []"
                        :dragging-task="draggingTask"
                        :is-drag-over="draggingOverColumn === stage.id"
                        :priorities="priorities"
                        :selected-task-ids="selectedTaskIds"
                        class="snap-center sm:snap-align-none"
                        @dragover="onDragOver"
                        @drop="onDrop"
                        @dragstart="onDragStart"
                        @editTask="openEditModal"
                        @toggle-select="toggleTaskSelection"
                    />
                    
                    <!-- Empty State / Add Stage Guide -->
                    <div class="flex flex-col items-center justify-center min-w-[280px] md:min-w-[200px] border-2 border-dashed border-gray-200 rounded-2xl bg-white/30 backdrop-blur-sm snap-center">
                        <button @click="showStageManager = true" class="text-gray-400 hover:text-indigo-500 text-sm font-bold flex flex-col items-center gap-2 transition-colors group">
                            <span class="p-3 rounded-full bg-gray-50 group-hover:bg-indigo-50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            </span>
                            Edit Stages
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unified Task Modal (Create/Edit/Full) -->
        <TaskFullModal 
            :show="showTaskModal"
            :task-id="focusedTaskId"
            :project-id="project.id"
            :projects="[project]"
            :employees="employees"
            :task-templates="taskTemplates"
            :modules="modules"
            :initial-data="taskForm"
            @close="closeTaskModal"
            @success="handleTaskSuccess"
            @deleted="handleTaskDeleted"
        />


        <!-- Sprint Manager Modal -->
        <SprintManagerModal
            :show="showSprintManager"
            :project="project"
            :sprints="project.sprints"
            :sprint-to-edit="sprintToEdit"
            @close="closeSprintManager"
        />

        <!-- Stage Manager Modal (Extracted) -->
        <StageManagerModal 
            :show="showStageManager" 
            :project="project"
            :employees="employees" 
            @close="showStageManager = false" 
        />

        <!-- Priority Manager Modal -->
        <ModalLarge :show="showPriorityManager" @close="showPriorityManager = false" title="Manage Priorities">
             <div class="space-y-6">
                <!-- Priority Form Content Omitted for brevity, kept structure -->
                <div class="flex items-center justify-between p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                    <p class="text-sm text-indigo-700 font-medium">
                        Define custom priorities for your tasks. Use colors to make them stand out on the board.
                    </p>
                </div>

                <!-- Priority Form -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 mb-6 relative" :class="{'ring-2 ring-indigo-100': priorityForm.id}">
                     <div class="flex justify-between items-center mb-3">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">
                            {{ priorityForm.id ? 'Edit Priority' : 'Add New Priority' }}
                        </h4>
                        <button v-if="priorityForm.id" @click="cancelEditPriority" class="text-xs text-gray-500 hover:text-gray-700 underline">
                            Cancel
                        </button>
                     </div>
                     <form @submit.prevent="createPriority" class="flex gap-3 items-end">
                        <div class="flex-1">
                             <BaseInput v-model="priorityForm.name" placeholder="Priority Name (e.g. Urgent)" class="mb-0" />
                        </div>
                         <div class="w-12">
                             <input type="color" v-model="priorityForm.color" class="h-10 w-full rounded-lg border-gray-300 cursor-pointer text-sm" />
                         </div>
                         <PrimaryButton :disabled="priorityForm.processing" class="h-10 px-4">
                             {{ priorityForm.id ? 'Save' : '+' }}
                         </PrimaryButton>
                     </form>
                </div>

                <!-- Existing Priorities List -->
                <div class="space-y-2 max-h-[300px] overflow-y-auto custom-scrollbar pr-1">
                    <div v-for="(prio, idx) in project.priorities" :key="prio.id" class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-lg shadow-sm group hover:border-indigo-200 transition-all">
                        <div class="flex items-center gap-3">
                             <span class="text-xs font-mono text-gray-400 w-4">{{ idx + 1 }}.</span>
                             <span class="px-2 py-0.5 rounded text-sm font-bold uppercase tracking-wide" :style="{ backgroundColor: prio.color + '20', color: prio.color }">
                                 {{ prio.name }}
                             </span>
                        </div>
                        
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                             <button @click="editPriority(prio)" class="text-gray-300 hover:text-indigo-600 p-2" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" /></svg>
                             </button>
                             <button @click="deletePriority(prio)" class="text-gray-300 hover:text-red-500 p-2" title="Delete">
                                 <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
             </div>
        </ModalLarge>

        <!-- Sprint Completion Modal -->
        <SprintCompletionModal
            :show="showSprintCompletion"
            :sprint="currentSprint"
            :project="project"
            :stats="sprintStats"
            @close="showSprintCompletion = false"
        />



        <!-- Batch Action Bar -->
        <BatchActionBar 
            v-if="selectedTaskIds.length > 0"
            :count="selectedTaskIds.length"
            @cancel="clearSelection"
            @move-sprint="openBatchMoveSprint"
            @bulk-assign="openBatchAssign"
        />

        <!-- Batch Modal -->
        <ModalLarge :show="showBatchModal" @close="showBatchModal = false" :title="batchActionType === 'sprint' ? 'Bulk Move Sprint' : 'Bulk Assign'">
            <form @submit.prevent="submitBatchForm" class="space-y-6">
                <p class="text-sm text-gray-500">
                    Applying changes to <span class="font-bold text-indigo-600">{{ selectedTaskIds.length }}</span> selected tasks.
                </p>

                <div v-if="batchActionType === 'sprint'">
                     <label class="block font-medium text-sm text-gray-700 mb-1">Select Sprint</label>
                     <select v-model="batchForm.sprint_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                         <option :value="null">Backlog</option>
                         <option v-for="s in project.sprints" :key="s.id" :value="s.id">{{ s.name }} ({{ s.status }})</option>
                     </select>
                </div>

                <div v-if="batchActionType === 'assign'">
                    <label class="block font-medium text-sm text-gray-700 mb-1">Assign To</label>
                    <MultiUserSelect
                        v-model="batchForm.assignee_ids"
                        :items="employees"
                        placeholder="Search Members..."
                    />
                    <p class="text-xs text-gray-400 mt-1">This will override existing assignees.</p>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <SecondaryButton @click="showBatchModal = false">Cancel</SecondaryButton>
                    <PrimaryButton :disabled="batchForm.processing">Apply</PrimaryButton>
                </div>
            </form>
        </ModalLarge>
        
        <!-- Activity Log Modal -->
        <ModalLarge :show="showActivityLog" @close="showActivityLog = false" title="Sprint Board Activity Log">
            <div class="space-y-4">
                <!-- Filters -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="md:col-span-2">
                        <InputLabel value="Search Activity" />
                        <TextInput v-model="activityFilters.search" placeholder="Search task, user, action..." @input="debouncedFetchActivity" />
                    </div>
                    <div>
                        <InputLabel value="Start Date" />
                        <TextInput type="date" v-model="activityFilters.start_date" @change="fetchActivity" />
                    </div>
                    <div>
                        <InputLabel value="End Date" />
                        <TextInput type="date" v-model="activityFilters.end_date" @change="fetchActivity" />
                    </div>
                </div>

                <div class="flex justify-between items-center px-2">
                    <span class="text-xs text-gray-500 font-medium">Total: {{ activitiesMeta.total || 0 }} logs found</span>
                    <SecondaryButton @click="exportActivity" size="xs" class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Export CSV
                    </SecondaryButton>
                </div>

                <div v-if="loadingActivity && activities.length === 0" class="flex justify-center py-12">
                     <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                </div>
                <div v-else-if="activities.length === 0" class="text-center py-12 text-gray-500 italic">
                    No activity recorded yet for this board.
                </div>
                <div v-else class="space-y-1 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                    <div v-for="act in activities" :key="act.id" class="p-4 bg-white border border-gray-100 rounded-xl hover:shadow-sm transition-all group">
                         <div class="flex gap-4">
                             <!-- Avatar -->
                             <div class="flex-shrink-0">
                                 <img :src="act.user?.avatar || `https://ui-avatars.com/api/?name=${act.user?.name}&background=6366f1&color=fff`" 
                                      class="h-10 w-10 rounded-full border-2 border-white shadow-sm" alt="">
                             </div>
                             
                             <div class="flex-1 min-w-0">
                                 <div class="flex justify-between items-start">
                                     <p class="text-sm font-bold text-gray-900 truncate">{{ act.user?.name || 'System' }}</p>
                                     <span class="text-[10px] text-gray-400 font-mono">{{ new Date(act.created_at).toLocaleString() }}</span>
                                 </div>
                                 
                                  <!-- Activity Description - Human Readable -->
                                  <div class="mt-1 text-sm text-gray-600">
                                      <!-- Stage Move -->
                                      <template v-if="act.type === 'moved'">
                                          Moved task <span class="font-bold text-indigo-700">#{{ act.task?.id }} {{ act.task?.title }}</span>
                                          <div class="mt-1.5 flex items-center gap-2 flex-wrap">
                                              <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-600 text-[10px] font-bold uppercase">{{ act.details?.from_stage_name || '?' }}</span>
                                              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                              <span class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 text-[10px] font-bold uppercase">{{ act.details?.to_stage_name || '?' }}</span>
                                          </div>
                                      </template>
                                      <!-- Created -->
                                      <template v-else-if="act.type === 'created'">
                                          Created task <span class="font-bold text-gray-900">{{ act.task?.title }}</span>
                                      </template>
                                      <!-- Comment Added -->
                                      <template v-else-if="act.type === 'comment'">
                                          Added a <span class="font-bold text-indigo-600">comment</span> on task <span class="font-bold text-gray-900">#{{ act.task?.id }} {{ act.task?.title }}</span>
                                      </template>
                                      <!-- Task Update -->
                                      <template v-else-if="act.type === 'update'">
                                          Updated <span class="font-bold text-indigo-700">details</span> for task <span class="font-bold">#{{ act.task_id }}</span>
                                          <div v-if="act.details?.fields?.length" class="mt-1 flex gap-1 flex-wrap">
                                              <span v-for="field in act.details.fields" :key="field" class="px-1.5 py-0.5 rounded bg-gray-100 text-[9px] text-gray-500 uppercase font-bold">{{ field.replace(/_/g, ' ') }}</span>
                                          </div>
                                      </template>
                                      <!-- PR Linked -->
                                      <template v-else-if="act.type === 'pr_linked'">
                                          Linked PR <span class="font-bold text-indigo-600">{{ act.details?.title }}</span> to task <span class="font-bold">#{{ act.task_id }}</span>
                                      </template>
                                      <!-- PR Status Updated -->
                                      <template v-else-if="act.type === 'pr_status_updated'">
                                          PR status changed for <span class="font-bold text-gray-900">{{ act.details?.pr_title }}</span>:
                                          <span class="inline-flex items-center gap-1.5 ml-1">
                                              <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-yellow-100 text-yellow-700">{{ act.details?.old_status }}</span>
                                              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                              <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase" :class="act.details?.new_status === 'approved' || act.details?.new_status === 'merged' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">{{ act.details?.new_status }}</span>
                                          </span>
                                      </template>
                                      <!-- Checklist -->
                                      <template v-else-if="act.type === 'checklist_add'">
                                          Added checklist item <span class="italic text-gray-500">"{{ act.details?.content }}"</span> to task <span class="font-bold">#{{ act.task_id }}</span>
                                      </template>
                                      <!-- Cloned Checklist -->
                                      <template v-else-if="act.type === 'checklist_cloned'">
                                          Cloned <span class="font-bold text-indigo-700">{{ act.details?.count }}</span> checklist items from task <span class="font-bold">#{{ act.details?.source_task_id }}</span>
                                      </template>
                                      <!-- Moved to Backlog -->
                                      <template v-else-if="act.type === 'moved_to_backlog'">
                                          Moved task <span class="font-bold text-gray-900">#{{ act.task_id }}</span> to <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 text-[10px] font-black uppercase tracking-wider">Backlog</span>
                                      </template>
                                      <!-- Restored from Backlog -->
                                      <template v-else-if="act.type === 'restored_from_backlog' || act.type === 'restored_from_backlog'">
                                          Restored task <span class="font-bold text-gray-900">#{{ act.task_id }}</span> back to the <span class="px-1.5 py-0.5 rounded bg-green-100 text-green-700 text-[10px] font-black uppercase tracking-wider">Sprint Board</span>
                                      </template>
                                      <!-- Generic Fallback -->
                                      <template v-else>
                                          Performed <span class="px-1.5 py-0.5 rounded bg-gray-100 font-mono text-[10px] text-gray-500">{{ act.type.replace(/_/g, ' ') }}</span> on task <span class="font-bold">#{{ act.task_id }}</span>
                                      </template>
                                  </div>
                             </div>
                         </div>
                    </div>
                </div>
                
                <div v-if="activitiesMeta.current_page < activitiesMeta.last_page" class="flex justify-center mt-4">
                    <SecondaryButton @click="loadMoreActivity" :disabled="loadingActivity">
                         {{ loadingActivity ? 'Loading...' : 'Load More Activities' }}
                    </SecondaryButton>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end p-2 border-t">
                    <SecondaryButton @click="showActivityLog = false">Close</SecondaryButton>
                </div>
            </template>
        </ModalLarge>

    </ProjectLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import ProjectLayout from '@/Layouts/ProjectLayout.vue';
import Modal from '@/Components/Modal.vue';
import ModalLarge from '@/Components/ModalLarge.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import Combobox from '@/Components/Combobox.vue';
import BaseInput from '@/Components/BaseInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

// Components
import BoardHeader from '@/Components/Project/BoardHeader.vue';
import BoardColumn from '@/Components/Project/BoardColumn.vue';
import StageManagerModal from '@/Components/Project/StageManagerModal.vue';
import SprintCompletionModal from '@/Components/Project/SprintCompletionModal.vue';
import SprintManagerModal from '@/Components/Project/SprintManagerModal.vue'; // Imported
import ProjectReports from '@/Pages/Project/Planner/Views/ProjectReports.vue'; // Imported

import MultiUserSelect from '@/Components/MultiUserSelect.vue';
import TaskFullModal from '@/Components/Project/TaskFullModal.vue';
import BatchActionBar from '@/Components/Project/BatchActionBar.vue';

const props = defineProps({
    project: Object,
    tasks: Array,
    employees: Array,
    currentSprint: [Object, String], // Updated validation
    activeSprints: Array, // Updated Prop
    priorities: Array,
    modules: Array,
    taskTemplates: Array // New Prop
});

// State
const localTasks = ref([...props.tasks]);
const draggingTask = ref(null);
const draggingOverColumn = ref(null);
const showTaskModal = ref(false);
const showSprintManager = ref(false);
const showStageManager = ref(false);
const showPriorityManager = ref(false);
const showSprintCompletion = ref(false);
const showReports = ref(false); // Reports Toggle
const showActivityLog = ref(false); // Activity Log
const activities = ref([]);
const activitiesMeta = ref({});
const activityFilters = ref({
    search: '',
    start_date: '',
    end_date: ''
});
const loadingActivity = ref(false);
const sprintToEdit = ref(null); // Sprint Edit State
const isEditing = ref(false); // Task Edit

// Hub Modal State
// Unified Modal State
// showTaskModal already declared at line 370
const focusedTaskId = ref(null);

// Batch Actions State
const selectedTaskIds = ref([]);
const toggleTaskSelection = (task) => {
    if (selectedTaskIds.value.includes(task.id)) {
        selectedTaskIds.value = selectedTaskIds.value.filter(id => id !== task.id);
    } else {
        selectedTaskIds.value.push(task.id);
    }
};
const clearSelection = () => selectedTaskIds.value = [];
const taskForm = useForm({
    id: null,
    title: '',
    description: '',
    stage_id: props.project.stages[0]?.id,
    module_id: '',
    sprint_id: props.currentSprint?.id || null, 
    priority: 'Medium',
    assignees: [],
    scrum_points: 0,
    due_date: '',
    git_branch_url: '',
    git_pr_url: ''
});
// sprintForm removed (using SprintManagerModal)

const priorityForm = useForm({
    id: null,
    name: '',
    color: '#f59e0b',
    order: 0
});

// Batch
const showBatchModal = ref(false);
const batchActionType = ref(null); // 'sprint' | 'assign'
const batchForm = useForm({
    task_ids: [],
    sprint_id: null,
    assignee_ids: []
});

const openBatchMoveSprint = () => {
    batchActionType.value = 'sprint';
    batchForm.task_ids = [...selectedTaskIds.value];
    batchForm.sprint_id = props.currentSprint?.id || null;
    showBatchModal.value = true;
};

const openBatchAssign = () => {
    batchActionType.value = 'assign';
    batchForm.task_ids = [...selectedTaskIds.value];
    // batchForm.assignee_ids = []; 
    showBatchModal.value = true;
};

const submitBatchForm = () => {
    batchForm.post(route('projects.tasks.bulk_update', props.project.id), {
        onSuccess: () => {
            showBatchModal.value = false;
            clearSelection();
            batchForm.reset();
        }
    });
};

// Computed
const flatModules = computed(() => {
    const flatten = (items, parentNames = []) => {
        let result = [];
        items.forEach(item => {
            const currentNames = [...parentNames, item.name];
            
            // Graceful depth truncation for 3+ nested modules
            let breadcrumb = '';
            if (currentNames.length <= 3) {
                breadcrumb = currentNames.join(' > ');
            } else {
                // E.g: "Core App > ... > Sub Module > Deep Task"
                breadcrumb = `${currentNames[0]} > ... > ${currentNames[currentNames.length - 2]} > ${currentNames[currentNames.length - 1]}`;
            }

            result.push({ 
                ...item, 
                breadcrumb_name: breadcrumb,
                full_path: currentNames.join(' > ') 
            });
            
            // Support both old and new recursive relationships
            const childList = item.children_recursive || item.children;
            if (childList && childList.length) {
                result = result.concat(flatten(childList, currentNames));
            }
        });
        return result;
    };
    return flatten(props.modules || []);
});

const plannedSprints = computed(() => props.project.sprints.filter(s => s.status === 'planned'));

const columns = computed(() => {
    const cols = {};
    props.project.stages.forEach(s => cols[s.id] = []);
    localTasks.value.forEach(task => {
        if (cols[task.stage_id]) cols[task.stage_id].push(task);
        else if (props.project.stages.length > 0) cols[props.project.stages[0].id].push(task); // Fallback
    });
    return cols;
});

const sprintStats = computed(() => {
    if (!props.currentSprint || typeof props.currentSprint === 'string') return { completedPoints: 0, totalPoints: 0, incompleteCount: 0 };
    
    // We rely on localTasks which contains tasks for the CURRENT view (currentSprint)
    const totalPoints = localTasks.value.reduce((acc, t) => acc + (parseInt(t.scrum_points) || 0), 0);
    
    // Find 'done' stages
    const doneStageIds = props.project.stages.filter(s => s.type === 'done').map(s => s.id);
    
    const completedPoints = localTasks.value
        .filter(t => doneStageIds.includes(t.stage_id))
        .reduce((acc, t) => acc + (parseInt(t.scrum_points) || 0), 0);
        
    const incompleteCount = localTasks.value.filter(t => !doneStageIds.includes(t.stage_id)).length;
    
    return { totalPoints, completedPoints, incompleteCount };
});

// Sync
watch(() => props.tasks, (newTasks) => localTasks.value = [...newTasks]);
watch(() => props.currentSprint, (newVal) => {
    // If we switch sprints, ensuring new tasks default to that sprint
    if (newVal && newVal !== 'backlog') taskForm.sprint_id = newVal.id;
    else taskForm.sprint_id = null;
});

// Actions
const openCreateModal = () => {
    focusedTaskId.value = null; // Use null for "Create" mode
    
    // Default form presets
    taskForm.stage_id = props.project.stages[0]?.id;
    taskForm.sprint_id = (props.currentSprint && props.currentSprint !== 'backlog') ? props.currentSprint.id : null;
    
    showTaskModal.value = true;
};

const applyTemplate = (templateId) => {
    if (!templateId) return;
    const template = props.taskTemplates.find(t => t.id == templateId);
    if (template) {
        taskForm.title = template.name;
        taskForm.description = template.description || '';
        taskForm.priority = template.priority || 'Medium';
        taskForm.scrum_points = template.scrum_points || 0;
    }
};

const openEditModal = (task) => {
    focusedTaskId.value = task.id;
    showTaskModal.value = true;
};

const closeTaskModal = () => {
    showTaskModal.value = false;
    focusedTaskId.value = null;
    taskForm.reset();
    isEditing.value = false;
};

const handleTaskSuccess = () => {
    // Optionally refresh or sync local state if needed
    // The router will handle general page refresh via onSuccess in the modal
};

const handleTaskDeleted = () => {
    // Redirect or refresh
    router.reload({ only: ['tasks'] });
};

// Toast Store
import { useToastStore } from '@/stores/toast';



const openActivityLog = async () => {
    showActivityLog.value = true;
    fetchActivity();
};

const fetchActivity = async (page = 1) => {
    loadingActivity.value = true;
    try {
        const query = new URLSearchParams({
            ...activityFilters.value,
            page: page
        }).toString();
        
        const response = await fetch(route('projects.board.activities', props.project.id) + '?' + query);
        const data = await response.json();
        
        if (page === 1) {
            activities.value = data.data || [];
        } else {
            activities.value = [...activities.value, ...(data.data || [])];
        }
        activitiesMeta.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total
        };
    } catch (e) {
        useToastStore().error('Failed to load activity log');
    } finally {
        loadingActivity.value = false;
    }
};

const loadMoreActivity = () => {
    if (activitiesMeta.value.current_page < activitiesMeta.value.last_page) {
        fetchActivity(activitiesMeta.value.current_page + 1);
    }
};

let debounceTimer = null;
const debouncedFetchActivity = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchActivity();
    }, 500);
};

const exportActivity = () => {
    const query = new URLSearchParams(activityFilters.value).toString();
    window.location.href = route('projects.board.activities.export', props.project.id) + '?' + query;
};

// Sprint Actions
const openSprintManager = () => {
    sprintToEdit.value = null; // Default to create/manage
    showSprintManager.value = true;
};

const closeSprintManager = () => {
    showSprintManager.value = false;
    sprintToEdit.value = null;
};

const handleEditSprint = (sprint) => {
    sprintToEdit.value = sprint;
    showSprintManager.value = true;
};

const handleDeleteSprint = (sprint) => {
    if (confirm('Delete this sprint? All tasks will move to backlog.')) {
        router.delete(route('projects.sprints.destroy', { project: props.project.id, sprint: sprint.id }));
    }
};

const startSprint = () => {
    if (!props.currentSprint || props.currentSprint === 'backlog') return;
    router.post(route('projects.sprints.start', { project: props.project.id, sprint: props.currentSprint.id }));
};

const openCompleteSprintModal = () => {
    showSprintCompletion.value = true;
};

// --- Priority Actions ---
const createPriority = () => {
    if (priorityForm.id) {
        priorityForm.put(route('projects.priorities.update', { project: props.project.id, priority: priorityForm.id }), {
            onSuccess: () => cancelEditPriority()
        });
    } else {
        priorityForm.post(route('projects.priorities.store', props.project.id), {
            onSuccess: () => cancelEditPriority()
        });
    }
};

const editPriority = (prio) => {
    priorityForm.id = prio.id;
    priorityForm.name = prio.name;
    priorityForm.color = prio.color;
    priorityForm.order = prio.order;
};

const cancelEditPriority = () => {
    priorityForm.reset();
    priorityForm.id = null;
};

const deletePriority = (prio) => {
    if (confirm('Delete this priority?')) {
        router.delete(route('projects.priorities.destroy', { project: props.project.id, priority: prio.id }));
    }
};


// --- DnD Logic (Optimistic) ---
const onDragStart = (task, event) => {
    draggingTask.value = task;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', task.id);
};

const onDragOver = (stageId) => {
    draggingOverColumn.value = stageId;
};

const onDrop = (stageId) => {
    draggingOverColumn.value = null;
    const task = draggingTask.value;
    if (!task || task.stage_id === stageId) return;

    // Optimistic Update
    const originalStageId = task.stage_id;
    const taskIndex = localTasks.value.findIndex(t => t.id === task.id);
    if (taskIndex !== -1) {
        localTasks.value[taskIndex].stage_id = stageId;
    }

    // Backend Sync
    router.post(route('projects.tasks.move', { project: props.project.id, task: task.id }), {
        stage_id: stageId
    }, {
        preserveScroll: true,
        onError: () => {
            // Revert on error
            if (taskIndex !== -1) localTasks.value[taskIndex].stage_id = originalStageId;
        }
    });
    
    draggingTask.value = null;
};
</script>
