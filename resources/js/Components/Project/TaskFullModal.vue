<template>
    <ModalLarge :show="show" @close="close" :title="isEditing ? (loading ? 'Loading...' : (details?.title || 'Edit Task')) : 'New Task'">
        <div v-if="loading && isEditing" class="flex items-center justify-center h-64">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>
        
        <div v-else class="flex flex-col h-full overflow-hidden">
            <!-- Tabs Headers (Only in Edit Mode) -->
            <div v-if="isEditing" class="flex border-b border-gray-200 bg-gray-50/50 px-4 shrink-0">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    class="px-4 py-3 text-sm font-bold border-b-2 transition-all relative uppercase tracking-widest"
                    :class="activeTab === tab.id ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-400 hover:text-gray-600'"
                >
                    {{ tab.name }}
                    <span v-if="tab.count" class="ml-1 px-1.5 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-[10px] items-center justify-center inline-flex h-4 min-w-[16px]">{{ tab.count }}</span>
                </button>
            </div>

            <!-- Tab Content -->
            <div class="flex-1 overflow-hidden bg-white relative">
                <!-- General / Form Tab -->
                <div v-show="activeTab === 'general'" class="h-full overflow-y-auto p-6 scroll-smooth custom-scrollbar">
                    <form @submit.prevent="submit" id="taskFullForm" class="space-y-6">
                        <!-- Template Loader (Create mode only) -->
                        <div v-if="!isEditing && taskTemplates?.length > 0" class="flex items-center gap-3 p-3 bg-indigo-50/50 border border-indigo-100/50 rounded-xl mb-2">
                            <div class="h-8 w-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            </div>
                            <div class="flex-1">
                                <label class="text-[10px] font-black text-indigo-700 uppercase tracking-[0.2em]">Quick Template</label>
                                <select @change="applyTemplate($event.target.value)" class="mt-0.5 block w-full text-sm border-transparent focus:ring-0 bg-transparent p-0 font-bold text-gray-900 border-none cursor-pointer">
                                    <option value="">Choose a preset...</option>
                                    <option v-for="t in taskTemplates" :key="t.id" :value="t.id">{{ t.name }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Title -->
                        <BaseInput
                            v-model="form.title" 
                            label="Task Title"
                            :error="form.errors.title"
                            placeholder="What needs to be done?"
                            color="indigo"
                            required 
                            autofocus 
                        />

                        <!-- Grid: Project, Stage -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <BaseSelect
                                v-model="form.project_id"
                                label="Project"
                                :error="form.errors.project_id"
                                color="indigo"
                                required
                                :disabled="isEditing"
                            >
                                <option v-for="p in availableProjects" :key="p.id" :value="p.id">{{ p.name || p.text }}</option>
                            </BaseSelect>

                            <BaseSelect
                                v-model="form.stage_id"
                                label="Board Stage"
                                :error="form.errors.stage_id"
                                color="indigo"
                            >
                                <option value="">Auto-assign</option>
                                <option v-for="s in currentProjectStages" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </BaseSelect>
                        </div>

                        <!-- Grid: Sprint, Priority -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                             <BaseSelect
                                v-model="form.sprint_id"
                                label="Sprint"
                                :error="form.errors.sprint_id"
                                color="indigo"
                            >
                                <option :value="null">Backlog</option>
                                <option v-for="s in currentProjectSprints" :key="s.id" :value="s.id">{{ s.name }} ({{ s.status }})</option>
                            </BaseSelect>

                             <BaseSelect
                                v-model="form.priority"
                                label="Priority"
                                :error="form.errors.priority"
                                color="indigo"
                            >
                                <option v-for="p in availablePriorities" :key="p" :value="p">{{ p }}</option>
                            </BaseSelect>
                        </div>

                        <!-- Grid: Module, Points -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                             <div class="flex flex-col">
                                <label class="block font-black text-[10px] text-gray-400 uppercase tracking-widest mb-1.5">Module / Feature</label>
                                <Combobox 
                                    v-model="form.module_id" 
                                    :items="flatModules" 
                                    labelKey="breadcrumb_name"
                                    valueKey="id"
                                    placeholder="Select module..."
                                    class="w-full"
                                />
                            </div>

                             <BaseInput 
                                v-model="form.scrum_points" 
                                label="Scrum Points" 
                                placeholder="0" 
                                type="number"
                                :error="form.errors.scrum_points" 
                            />
                        </div>

                        <!-- Grid: Start Date, Due Date -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <BaseInput 
                                v-model="form.start_date" 
                                label="Start Date" 
                                type="date"
                                :error="form.errors.start_date" 
                            />
                            <BaseInput 
                                v-model="form.due_date" 
                                label="Due Date" 
                                type="date"
                                :error="form.errors.due_date" 
                            />
                        </div>

                        <!-- Efforts & Lock -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-indigo-50/30 border border-indigo-100/50 rounded-2xl">
                             <BaseInput 
                                v-model="form.total_efforts" 
                                label="Total Effort (Man-Hours)" 
                                type="number"
                                placeholder="Combined team effort"
                                :error="form.errors.total_efforts" 
                            />
                             <div class="flex flex-col justify-center">
                                <label class="block font-black text-[10px] text-indigo-700 uppercase tracking-widest mb-2">Schedule Governance</label>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="form.is_locked" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                    <span class="ml-3 text-xs font-bold text-gray-700 uppercase tracking-widest">{{ form.is_locked ? 'Schedule Locked' : 'Schedule Open' }}</span>
                                </label>
                             </div>
                        </div>

                        <!-- Assignees -->
                        <div>
                             <label class="block font-black text-[10px] text-gray-400 uppercase tracking-widest mb-1.5">Assignees</label>
                             <MultiUserSelect
                                v-model="form.assignees"
                                :items="employees"
                                placeholder="Type member name..."
                            />
                        </div>

                        <!-- Description -->
                        <div class="space-y-1.5">
                            <label class="block font-black text-[10px] text-gray-400 uppercase tracking-widest">Description</label>
                            <textarea 
                                v-model="form.description"
                                rows="4"
                                class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-0 text-sm bg-gray-50/50 p-4 transition-all"
                                placeholder="Add more details about this task..."
                            ></textarea>
                        </div>

                        <!-- Git Fields -->
                        <div v-show="showGitFields" class="p-4 bg-gray-50 rounded-xl border border-gray-100 space-y-4">
                             <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 flex items-center gap-2">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
                                Git Integration
                             </h4>
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <BaseInput v-model="form.git_branch_url" label="Branch URL" placeholder="https://..." color="indigo" class="mb-0" />
                                <BaseInput v-model="form.git_pr_url" label="Current PR" placeholder="https://..." color="indigo" class="mb-0" />
                             </div>
                        </div>

                         <button type="button" @click="showGitFields = !showGitFields" class="text-xs text-indigo-600 font-bold hover:underline">
                            {{ showGitFields ? '- Hide Git Fields' : '+ Show Git Fields' }}
                         </button>
                    </form>
                </div>

                <!-- Checklist Tab -->
                <TaskChecklists 
                    v-if="isEditing"
                    v-show="activeTab === 'checklists'" 
                    :items="details?.checklists || []"
                    :other-tasks="[]"
                    @add="addChecklist"
                    @toggle="toggleChecklist"
                    @delete="deleteChecklist"
                    @clone="cloneChecklist"
                    @import="importChecklist"
                    class="h-full"
                />

                <!-- Activity Tab -->
                <TaskActivity 
                    v-if="isEditing"
                    v-show="activeTab === 'activity'"
                    :activities="details?.activities || []" 
                    class="h-full"
                />

                <!-- Comments Tab -->
                <TaskComments 
                    v-if="isEditing"
                    v-show="activeTab === 'comments'"
                    :comments="details?.comments || []"
                    @add="addComment"
                    @delete="deleteComment"
                    class="h-full"
                />

                 <!-- Code Tab -->
                 <div v-if="isEditing" v-show="activeTab === 'code'" class="h-full overflow-y-auto p-6 space-y-8 custom-scrollbar">
                     <!-- Link New PR -->
                     <div class="bg-indigo-50/50 p-5 rounded-2xl border border-indigo-100/50 shadow-sm">
                        <h4 class="text-[10px] text-indigo-700 uppercase font-black tracking-widest mb-4">Link Manual Pull Request</h4>
                        <div class="flex flex-col md:flex-row gap-3">
                            <div class="flex-1">
                                <input v-model="prForm.title" placeholder="PR Title" class="w-full text-sm border-gray-200 rounded-xl focus:ring-indigo-500" />
                            </div>
                            <div class="flex-[2]">
                                <input v-model="prForm.url" placeholder="GitHub URL" class="w-full text-sm border-gray-200 rounded-xl focus:ring-indigo-500" />
                            </div>
                            <button @click="addPR" class="px-6 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700">Link</button>
                        </div>
                    </div>

                    <!-- PR List -->
                    <div class="space-y-4">
                        <h4 class="text-[10px] text-gray-400 uppercase font-black tracking-widest pl-1">Pull Requests ({{ (details?.pull_requests?.length || 0) + (details?.related_pull_requests?.length || 0) }})</h4>
                        
                        <!-- Manual PRs -->
                        <div v-for="pr in details?.pull_requests" :key="pr.id" class="p-4 bg-white border border-gray-100 rounded-2xl shadow-sm flex items-center justify-between group hover:border-indigo-200 transition-all">
                             <div>
                                <a :href="pr.url" target="_blank" class="text-sm font-bold text-gray-900 pr-2 hover:text-indigo-600 transition-colors">{{ pr.title }}</a>
                                <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-black uppercase tracking-tighter" 
                                    :class="pr.status === 'pending' ? 'bg-amber-100 text-amber-700' : (pr.status === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700')">
                                    {{ pr.status }}
                                </span>
                             </div>
                             <div class="flex gap-2">
                                 <button v-if="pr.status !== 'approved'" @click="updatePRStatus(pr, 'approved')" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg opacity-0 group-hover:opacity-100 transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></button>
                                 <button @click="deletePR(pr.id)" class="p-2 text-gray-300 hover:text-red-500 rounded-lg transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                             </div>
                        </div>

                         <!-- Auto PRs -->
                          <div v-for="pr in details?.related_pull_requests" :key="pr.id" class="p-4 bg-slate-50/50 border border-slate-200/50 rounded-2xl flex items-center justify-between italic">
                             <div class="text-sm font-medium text-slate-700">{{ pr.title }} [Auto]</div>
                             <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">{{ pr.state }}</span>
                         </div>
                     </div>
                  </div>

                  <!-- Settings Tab (Lock Recipients, Baseline) -->
                  <div v-if="isEditing" v-show="activeTab === 'settings'" class="h-full overflow-y-auto p-6 space-y-8 custom-scrollbar">
                        <section class="space-y-4">
                            <h4 class="text-xs font-black text-gray-900 uppercase tracking-widest border-b pb-2">Planner Governance</h4>
                            <p class="text-xs text-gray-500">Configure who receives immediate alerts if the locked schedule is overwritten.</p>
                            
                            <div>
                                <label class="block font-black text-[10px] text-gray-400 uppercase tracking-widest mb-1.5">Notification Recipients (Alert Stakeholders)</label>
                                <MultiUserSelect
                                    v-model="projectForm.plan_lock_recipients"
                                    :items="employees"
                                    placeholder="Add someone to notify..."
                                />
                            </div>

                            <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-100 rounded-xl">
                                <input type="checkbox" v-model="projectForm.is_locked" class="rounded border-red-300 text-red-600 focus:ring-red-500">
                                <div>
                                    <label class="block text-sm font-bold text-red-700">Lock Entire Project</label>
                                    <span class="text-[10px] text-red-500 uppercase font-black">Strict Mode</span>
                                </div>
                            </div>

                            <button @click="saveProjectSettings" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 transition-all">
                                Update Project Governance
                            </button>
                        </section>

                        <section class="space-y-4 pt-4">
                            <h4 class="text-xs font-black text-gray-900 uppercase tracking-widest border-b pb-2">Audit & Baseline</h4>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-bold text-gray-700">Baseline Snapshot</div>
                                        <div class="text-[10px] text-gray-400 uppercase font-bold">Used for Plan vs. Reality Reports</div>
                                    </div>
                                    <button class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold hover:bg-gray-50">Create Snapshot</button>
                                </div>
                            </div>
                        </section>
                  </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-200 flex justify-between items-center shrink-0 backdrop-blur-sm">
                <div class="flex gap-4 items-center">
                    <button 
                        v-if="isEditing"
                        @click="handleDelete"
                        class="text-xs font-black text-red-400 hover:text-red-600 uppercase tracking-[0.2em] transition-colors"
                    >
                        Delete Task
                    </button>
                    <button 
                        v-if="isEditing"
                        @click="showTemplateSave = true"
                        class="text-xs font-black text-indigo-400 hover:text-indigo-600 uppercase tracking-[0.2em] transition-colors flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                        Save Preset
                    </button>
                    <div v-else></div>
                </div>

                <div class="flex gap-3">
                    <SecondaryButton @click="close">Cancel</SecondaryButton>
                    <PrimaryButton @click="submit" :disabled="form.processing">
                        {{ isEditing ? 'Update Task' : 'Create Task' }}
                    </PrimaryButton>
                </div>
            </div>
        </div>

        <!-- Template Name Modal (Inner) -->
        <Modal :show="showTemplateSave" @close="showTemplateSave = false" maxWidth="sm">
            <div class="p-6">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-[0.2em] mb-4">Save Task Preset</h3>
                <BaseInput v-model="templateName" label="Preset Name" placeholder="e.g. Bug Report" autofocus @keyup.enter="saveAsTemplate" />
                <div class="flex justify-end gap-3 mt-4">
                    <SecondaryButton @click="showTemplateSave = false">Discard</SecondaryButton>
                    <PrimaryButton @click="saveAsTemplate" :disabled="!templateName.trim()">Save Preset</PrimaryButton>
                </div>
            </div>
        </Modal>
    </ModalLarge>
</template>

<script setup>
import { ref, computed, watch, reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import dayjs from 'dayjs';
import { useToastStore } from '@/stores/toast';

// Components
import ModalLarge from '@/Components/ModalLarge.vue';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import Combobox from '@/Components/Combobox.vue';
import MultiUserSelect from '@/Components/MultiUserSelect.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

// Tabs Components
import TaskComments from './TaskComments.vue';
import TaskChecklists from './TaskChecklists.vue';
import TaskActivity from './TaskActivity.vue';

const props = defineProps({
    show: Boolean,
    taskId: { type: Number, default: null }, // If null, mode is Create
    projectId: { type: [Number, String], default: null },
    // Data contexts
    projects: { type: Array, default: () => [] },
    employees: { type: Array, default: () => [] },
    taskTemplates: { type: Array, default: () => [] },
    modules: { type: Array, default: () => [] },
    // Optional presets
    initialData: { type: Object, default: () => ({}) }
});

const emit = defineEmits(['close', 'success', 'deleted']);
const toast = useToastStore();

// --- State ---
const isEditing = computed(() => !!props.taskId);
const activeTab = ref('general');
const loading = ref(false);
const details = ref(null); // Full data for Edit mode
const showGitFields = ref(false);
const showTemplateSave = ref(false);
const templateName = ref('');

// --- Form & Data Management ---
const form = useForm({
    id: null,
    title: '',
    description: '',
    project_id: props.projectId || '',
    stage_id: '',
    sprint_id: null,
    priority: 'Medium',
    module_id: '',
    assignees: [],
    scrum_points: 0,
    start_date: '',
    due_date: '',
    total_efforts: 0,
    is_locked: false,
    git_branch_url: '',
    git_pr_url: ''
});

const projectForm = reactive({
    id: null,
    is_locked: false,
    plan_lock_recipients: []
});

// --- Computed Lookups ---
const availableProjects = computed(() => props.projects);
const currentProject = computed(() => props.projects.find(p => p.id == form.project_id));
const currentProjectStages = computed(() => currentProject.value?.stages || []);
const currentProjectSprints = computed(() => currentProject.value?.sprints || []);
const availablePriorities = ['Low', 'Medium', 'High', 'Critical'];

const tabs = computed(() => {
    if (!isEditing.value) return [];
    return [
        { id: 'general', name: 'General' },
        { id: 'checklists', name: 'Checklist', count: details.value?.checklists?.length || 0 },
        { id: 'comments', name: 'Comments', count: details.value?.comments?.length || 0 },
        { id: 'code', name: 'Code & PRs', count: (details.value?.pull_requests?.length || 0) + (details.value?.related_pull_requests?.length || 0) },
        { id: 'settings', name: 'Settings' },
        { id: 'activity', name: 'Activity' }
    ];
});

// --- Lifecycle & Watchers ---
watch(() => props.show, (shown) => {
    if (shown) {
        if (isEditing.value) {
            fetchTaskDetails();
            activeTab.value = 'general';
        } else {
            resetForm();
            // Apply initialData if provided
            Object.assign(form, { ...form, ...props.initialData });
            activeTab.value = 'general';
        }
    }
});

// Auto-select first project/stage if none
watch(() => props.projects, (pts) => {
    if (!form.project_id && pts.length > 0) {
        form.project_id = pts[0].id;
    }
}, { immediate: true });

watch(() => form.project_id, (pid) => {
    if (!pid || isEditing.value) return;
    const proj = props.projects.find(p => p.id == pid);
    if (proj && proj.stages?.length > 0 && !form.stage_id) {
        form.stage_id = proj.stages[0].id;
    }
});

// --- Methods ---
const resetForm = () => {
    form.reset();
    form.clearErrors();
    details.value = null;
    showGitFields.value = false;
};

const fetchTaskDetails = async () => {
    if (!props.taskId) return;
    loading.value = true;
    try {
        // We need a reliable endpoint for task details. 
        // Using projects.tasks.show which requires {project, task}
        const res = await axios.get(route('projects.tasks.show', { project: form.project_id || props.projectId, task: props.taskId }));
        details.value = res.data;
        
        // Sync Form
        form.id = res.data.id;
        form.title = res.data.title;
        form.description = res.data.description;
        form.project_id = res.data.project_id;
        form.stage_id = res.data.stage_id;
        form.sprint_id = res.data.sprint_id;
        form.priority = res.data.priority;
        form.module_id = res.data.module_id || '';
        // Map assignees: try user_id (User object), fall back to id (User object if direct users, or coerce)
        form.assignees = res.data.assignees?.map(a => Number(a.user_id || a.id)) || [];
        form.scrum_points = res.data.scrum_points;
        form.start_date = res.data.start_date ? dayjs(res.data.start_date).format('YYYY-MM-DD') : '';
        form.due_date = res.data.due_date ? dayjs(res.data.due_date).format('YYYY-MM-DD') : '';
        form.total_efforts = res.data.total_efforts || 0;
        form.is_locked = !!res.data.is_locked;
        form.git_branch_url = res.data.git_branch_url || '';
        form.git_pr_url = res.data.git_pr_url || '';

        // Sync Project Form
        const proj = props.projects.find(p => p.id == res.data.project_id);
        if (proj) {
            projectForm.id = proj.id;
            projectForm.is_locked = !!proj.is_locked;
            projectForm.plan_lock_recipients = proj.plan_lock_recipients || [];
        }
        
        if (form.git_branch_url || form.git_pr_url) showGitFields.value = true;
    } catch (e) {
        console.error(e);
        toast.error('Could not load task details');
        close();
    } finally {
        loading.value = false;
    }
};

const applyTemplate = (tid) => {
    const t = props.taskTemplates.find(x => x.id == tid);
    if (!t) return;
    form.title = t.name;
    form.description = t.description || '';
    form.priority = t.priority || 'Medium';
    form.scrum_points = t.scrum_points || 0;
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('projects.tasks.update', { project: form.project_id, task: form.id }), {
            onSuccess: () => {
                toast.success('Task updated');
                emit('success');
                close();
            }
        });
    } else {
        form.post(route('projects.tasks.store', form.project_id), {
            onSuccess: () => {
                toast.success('Task created');
                emit('success');
                close();
            }
        });
    }
};

const handleDelete = () => {
    if (!confirm('Permanently delete this task?')) return;
    form.delete(route('projects.tasks.destroy', { project: form.project_id, task: form.id }), {
        onSuccess: () => {
            toast.success('Task deleted');
            emit('deleted');
            close();
        }
    });
};

const close = () => {
    emit('close');
    resetForm();
};

// --- Sub-component Actions (Copied/Adapted from TaskDetailModal) ---
const addComment = async (payload) => {
    const config = payload instanceof FormData ? { headers: { 'Content-Type': 'multipart/form-data' } } : {};
    try {
        const res = await axios.post(route('tasks.comments.store', props.taskId), payload, config);
        details.value.comments.unshift(res.data);
    } catch (e) { toast.error('Failed to comment'); }
};

const deleteComment = async (id) => {
    try {
        await axios.delete(route('tasks.comments.destroy', id));
        details.value.comments = details.value.comments.filter(c => c.id !== id);
    } catch (e) { toast.error('Failed to delete'); }
};

const addChecklist = async (content) => {
    try {
        const res = await axios.post(route('tasks.checklists.store', props.taskId), { content });
        details.value.checklists.push(res.data);
    } catch (e) { toast.error('Checklist error'); }
};

const toggleChecklist = async (item) => {
     try { await axios.post(route('tasks.checklists.toggle', item.id)); } 
     catch (e) { toast.error('Sync failed'); }
};

const deleteChecklist = async (id) => {
    try {
        await axios.delete(route('tasks.checklists.destroy', id));
        details.value.checklists = details.value.checklists.filter(i => i.id !== id);
    } catch (e) { toast.error('Failed to delete'); }
};

const cloneChecklist = async (sourceTaskId) => {
    try {
        const res = await axios.post(route('tasks.checklists.clone', props.taskId), { source_task_id: sourceTaskId });
        details.value.checklists = res.data;
    } catch (e) { toast.error('Clone failed'); }
};

const importChecklist = async (file) => {
    const formData = new FormData();
    formData.append('file', file);
    try {
        const res = await axios.post(route('tasks.checklists.import', props.taskId), formData, { headers: { 'Content-Type': 'multipart/form-data' } });
        details.value.checklists = res.data;
    } catch (e) { toast.error('Import failed'); }
};

// PRs
const prForm = ref({ title: '', url: '' });
const addPR = async () => {
    if (!prForm.value.title || !prForm.value.url) return toast.error('Missing info');
    try {
        const res = await axios.post(route('tasks.pull-requests.store', props.taskId), prForm.value);
        if (!details.value.pull_requests) details.value.pull_requests = [];
        details.value.pull_requests.unshift(res.data);
        prForm.value = { title: '', url: '' };
    } catch (e) { toast.error('Link failed'); }
};

const updatePRStatus = async (pr, status) => {
    try {
        const res = await axios.patch(route('tasks.pull-requests.update', pr.id), { status });
        const idx = details.value.pull_requests.findIndex(i => i.id === pr.id);
        if (idx !== -1) details.value.pull_requests[idx] = res.data;
    } catch (e) { toast.error('Status fail'); }
};

const deletePR = async (id) => {
    try {
        await axios.delete(route('tasks.pull-requests.destroy', id));
        details.value.pull_requests = details.value.pull_requests.filter(i => i.id !== id);
    } catch (e) { toast.error('Remove failed'); }
};

const saveAsTemplate = async () => {
    if (!templateName.value.trim()) return;
    try {
        await axios.post(route('projects.templates.store', form.project_id), {
            name: templateName.value,
            description: form.description,
            priority: form.priority,
            scrum_points: form.scrum_points,
            checklists: details.value?.checklists?.map(c => c.content) || []
        });
        toast.success('Preset saved');
        showTemplateSave.value = false;
        templateName.value = '';
    } catch (e) { toast.error('Failed to save preset'); }
};

const saveProjectSettings = async () => {
    try {
        await axios.put(route('projects.update', { project: projectForm.id }), {
            is_locked: projectForm.is_locked,
            plan_lock_recipients: projectForm.plan_lock_recipients,
            _method: 'PUT' // For standard Inertia update if needed, but using axios here.
        });
        toast.success('Project governance updated');
        // Refresh local projects list if possible, or just emit success to trigger reload
        emit('success');
    } catch (e) {
        toast.error('Failed to update project settings');
    }
};

// --- Module Flattening ---
const flatModules = computed(() => {
    const flatten = (items, parentNames = []) => {
        let result = [];
        items.forEach(item => {
            const currentNames = [...parentNames, item.name];
            let breadcrumb = currentNames.length <= 2 ? currentNames.join(' > ') : `${currentNames[0]} > ... > ${currentNames[currentNames.length - 1]}`;
            result.push({ ...item, breadcrumb_name: breadcrumb });
            const children = item.children_recursive || item.children;
            if (children?.length) result = result.concat(flatten(children, currentNames));
        });
        return result;
    };
    // Extract modules from projects or props if available
    // For now we rely on external prop, but we can also find it from currentProject
    // Try relationship-loaded modules first, then prop-passed modules
    const sourceModules = currentProject.value?.modules || props.modules || [];
    return flatten(sourceModules);
});

</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>
