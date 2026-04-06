<template>
    <ModalLarge :show="show" @close="close" :title="loading ? 'Loading...' : (details?.title || 'Task Details')">
        <div v-if="loading" class="flex items-center justify-center h-64">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>
        
        <div v-else-if="details" class="flex flex-col h-full overflow-hidden">
            <!-- Tabs Headers -->
            <div class="flex border-b border-gray-200 bg-gray-50/50 px-4">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition-colors relative"
                    :class="activeTab === tab.id ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
                >
                    {{ tab.name }}
                    <span v-if="tab.count" class="ml-1 px-1.5 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-[10px] items-center justify-center inline-flex h-4 min-w-[16px]">{{ tab.count }}</span>
                </button>
            </div>

            <!-- Tab Content -->
            <div class="flex-1 overflow-hidden bg-white">
                <!-- Details Tab -->
                <div v-show="activeTab === 'details'" class="h-full overflow-y-auto p-6">
                    <!-- Basic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <span class="text-xs text-gray-400 uppercase font-bold tracking-wider">Status</span>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="px-2 py-1 rounded text-xs font-bold uppercase" :style="{ backgroundColor: details.stage?.color + '20', color: details.stage?.color }">
                                    {{ details.stage?.name }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 uppercase font-bold tracking-wider">Priority</span>
                            <div class="mt-1 font-medium text-sm text-gray-700">{{ details.priority }}</div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <span class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-2 block">Description</span>
                        <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap bg-gray-50 p-4 rounded-lg border border-gray-100">{{ details.description || 'No description provided.' }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Module:</span>
                            <span class="ml-2 font-medium">{{ details.module ? details.module.name : '--' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 uppercase font-bold tracking-wider">Assignees</span>
                            <div class="flex items-center gap-1 mt-1">
                                <template v-if="details.assignees && details.assignees.length">
                                    <div v-for="user in details.assignees" :key="user.id" class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-[10px] font-bold text-indigo-700 shadow-sm border border-white" :title="user.name">
                                        {{ getInitials(user.name) }}
                                    </div>
                                </template>
                                <span v-else class="text-gray-400 italic">None</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button @click="$emit('edit', details)" class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 font-bold transition-colors">
                            Edit Task Details
                        </button>
                    </div>
                </div>

                <!-- Checklists Tab -->
                <TaskChecklists 
                    v-show="activeTab === 'checklists'" 
                    :items="details.checklists || []"
                    :other-tasks="tasks?.filter(t => t.id !== taskId) || []"
                    @add="addChecklist"
                    @toggle="toggleChecklist"
                    @delete="deleteChecklist"
                    @clone="cloneChecklist"
                    @import="importChecklist"
                />

                <!-- Activity Tab -->
                <TaskActivity 
                    v-show="activeTab === 'activity'"
                    :activities="details.activities || []" 
                />

                <!-- Comments Tab -->
                <TaskComments 
                    v-show="activeTab === 'comments'"
                    :comments="details.comments || []"
                    @add="addComment"
                    @delete="deleteComment"
                />

                <!-- Code Tab -->
                <div v-show="activeTab === 'code'" class="h-full overflow-y-auto p-6 space-y-8">
                    <!-- Manual Add PR -->
                    <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100 shadow-sm">
                        <h4 class="text-xs text-indigo-700 uppercase font-bold tracking-wider mb-3">Link New Pull Request</h4>
                        <div class="flex-col space-y-3">
                            <div class="flex flex-col md:flex-row gap-3">
                                <div class="flex-1">
                                    <input 
                                        v-model="prForm.title" 
                                        placeholder="PR Title (e.g., Auth Fix)" 
                                        class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                        :class="{'border-red-500 ring-1 ring-red-100 bg-red-50': prErrors.title}"
                                    />
                                    <span v-if="prErrors.title" class="text-[10px] text-red-500 font-bold mt-1 block px-1">Mandatory Title</span>
                                </div>
                                <div class="flex-[2]">
                                    <input 
                                        v-model="prForm.url" 
                                        placeholder="PR URL (https://github.com/...)" 
                                        class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                        :class="{'border-red-500 ring-1 ring-red-100 bg-red-50': prErrors.url}"
                                    />
                                    <span v-if="prErrors.url" class="text-[10px] text-red-500 font-bold mt-1 block px-1">{{ prErrors.url }}</span>
                                </div>
                                <button @click="addPR" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition-all shadow-md h-[42px]">
                                    Link PR
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Linked PRs List -->
                    <div>
                        <h4 class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-3">Linked Pull Requests ({{ details.pull_requests?.length || 0 }})</h4>
                        <div v-if="details.pull_requests?.length > 0" class="space-y-3">
                            <div v-for="pr in details.pull_requests" :key="pr.id" class="p-4 bg-white border border-gray-100 rounded-xl shadow-sm hover:border-indigo-200 transition-all flex items-center justify-between group">
                                <div class="flex-1 min-w-0 pr-4">
                                    <div class="flex items-center gap-2 mb-1">
                                        <a :href="pr.url" target="_blank" class="text-sm font-bold text-gray-900 hover:text-indigo-600 truncate underline decoration-indigo-200 decoration-2 underline-offset-4">
                                            {{ pr.title }}
                                        </a>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" 
                                            :class="{
                                                'bg-yellow-100 text-yellow-700': pr.status === 'pending',
                                                'bg-green-100 text-green-700': pr.status === 'approved' || pr.status === 'merged',
                                                'bg-red-100 text-red-700': pr.status === 'rejected'
                                            }">
                                            {{ pr.status }}
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-gray-400 flex items-center gap-2">
                                        <span>Added by {{ pr.user?.name }}</span>
                                        <span>•</span>
                                        <span>{{ new Date(pr.created_at).toLocaleDateString() }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div v-if="pr.status === 'pending' || pr.status === 'rejected'" class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="updatePRStatus(pr, 'approved')" class="p-1.5 text-xs font-bold text-green-600 hover:bg-green-50 rounded" title="Approve">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        </button>
                                        <button @click="updatePRStatus(pr, 'rejected')" class="p-1.5 text-xs font-bold text-red-600 hover:bg-red-50 rounded" title="Reject">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                    <button @click="deletePR(pr.id)" class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded transition-colors" title="Remove Link">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-400 italic bg-gray-50 p-8 rounded-xl text-center border-2 border-dashed border-gray-200">
                             No manual links provided. Add a Pull Request to track its status.
                        </div>
                    </div>

                    <!-- Auto-Detected PRs (Existing) -->
                    <div class="pt-6 border-t border-gray-100">
                        <h4 class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-2">
                            Auto-Detected PRs ({{ details.related_pull_requests?.length || 0 }})
                        </h4>
                        <div v-if="details.related_pull_requests && details.related_pull_requests.length > 0" class="space-y-3 opacity-70 scale-95 origin-top">
                            <div v-for="pr in details.related_pull_requests" :key="pr.id" class="p-3 bg-white border border-gray-200 rounded-lg flex items-center justify-between hover:shadow-sm transition-shadow italic">
                                <div>
                                    <div class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                        {{ pr.title }}
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" 
                                            :class="{
                                                'bg-green-100 text-green-700': pr.state === 'merged' || pr.state === 'closed',
                                                'bg-blue-100 text-blue-700': pr.state === 'open'
                                            }">
                                            {{ pr.state }}
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">
                                        by {{ pr.author_name }} • {{ new Date(pr.created_at_provider).toLocaleDateString() }}
                                    </div>
                                </div>
                                <div class="text-right text-xs text-gray-400">
                                    <div>+{{ pr.additions }} / -{{ pr.deletions }}</div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-400 italic">
                             No automatic matches found for "Fixes #{{ details.id }}".
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer for Save as Template -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                 <button 
                    @click="showTemplateModal = true"
                    class="text-xs text-indigo-600 font-medium hover:text-indigo-800 flex items-center gap-1"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    Save as Template
                </button>
            </div>
        </div>
    </ModalLarge>

    <!-- Template Name Modal -->
    <Modal :show="showTemplateModal" @close="showTemplateModal = false" :maxWidth="'sm'">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Save Task as Template</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
                <input 
                    v-model="templateName"
                    type="text" 
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="e.g. Standard Bug Report"
                    @keyup.enter="saveAsTemplate"
                >
            </div>
             <div class="flex justify-end gap-2">
                <button @click="showTemplateModal = false" class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 text-sm font-medium">
                    Cancel
                </button>
                <button 
                    @click="saveAsTemplate" 
                    :disabled="!templateName.trim()"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium disabled:opacity-50"
                >
                    Save Template
                </button>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import ModalLarge from '@/Components/ModalLarge.vue';
import TaskComments from './TaskComments.vue';
import TaskChecklists from './TaskChecklists.vue';
import TaskActivity from './TaskActivity.vue';

const props = defineProps({
    show: Boolean,
    taskId: Number,
    project: Object, // Need project ID for route construction
    tasks: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'edit']);
const toast = useToastStore();
const activeTab = ref('details');
const loading = ref(false);
const details = ref(null);

const tabs = computed(() => [
    { id: 'details', name: 'Details' },
    { id: 'checklists', name: 'Checklist', count: details.value?.checklists?.length || 0 },
    { id: 'comments', name: 'Comments', count: details.value?.comments?.length || 0 },
    { id: 'code', name: 'Code & PRs', count: (details.value?.pull_requests?.length || 0) + (details.value?.related_pull_requests?.length || 0) },
    { id: 'activity', name: 'Activity' }
]);

// Fetch Data
const fetchDetails = async () => {
    if (!props.taskId) return;
    console.log('Fetching details for task', props.taskId);
    loading.value = true;
    try {
        const res = await axios.get(route('projects.tasks.show', { project: props.project.id, task: props.taskId }));
        console.log('Response received', res.data);
        details.value = res.data;
    } catch (e) {
        console.error('Fetch error', e);
        toast.error('Failed to load task details');
    } finally {
        console.log('Setting loading to false');
        loading.value = false;
    }
};

watch(() => props.taskId, (newVal) => {
    if (newVal && props.show) {
        fetchDetails();
        activeTab.value = 'details';
    }
});

watch(() => props.show, (newVal) => {
    if (newVal && props.taskId) fetchDetails();
});

const close = () => {
    emit('close');
    details.value = null;
    prErrors.value = {};
};

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

// Actions
const addComment = async (payload) => {
    if (!props.taskId) return;
    
    // Check if payload is FormData
    const isFormData = payload instanceof FormData;
    const config = isFormData ? { headers: { 'Content-Type': 'multipart/form-data' } } : {};

    try {
        const res = await axios.post(route('tasks.comments.store', props.taskId), payload, config);
        details.value.comments.unshift(res.data);
    } catch (e) {
        if (e.response && e.response.status === 422) {
            toast.error(Object.values(e.response.data.errors).flat()[0]);
        } else {
            toast.error('Failed to send comment');
        }
    }
};

const deleteComment = async (id) => {
     try {
        await axios.delete(route('tasks.comments.destroy', id));
        details.value.comments = details.value.comments.filter(c => c.id !== id);
        toast.success('Comment deleted');
    } catch (e) {
        toast.error('Failed to delete');
    }
};

const addChecklist = async (content) => {
    try {
        const res = await axios.post(route('tasks.checklists.store', props.taskId), { content });
        details.value.checklists.push(res.data);
        // Sort by position? Backend handles order match? Usually append is fine.
    } catch (e) {
        if (e.response && e.response.status === 422) {
            toast.error(Object.values(e.response.data.errors).flat()[0]);
        } else {
            toast.error('Failed to add item');
        }
    }
};

const toggleChecklist = async (item) => {
     // Optimistic
     const idx = details.value.checklists.findIndex(i => i.id === item.id);
     if (idx !== -1) details.value.checklists[idx].is_completed = !details.value.checklists[idx].is_completed;

     try {
        await axios.post(route('tasks.checklists.toggle', item.id));
    } catch (e) {
        // Revert
        if (idx !== -1) details.value.checklists[idx].is_completed = !details.value.checklists[idx].is_completed;
        toast.error('Sync failed');
    }
};

const cloneChecklist = async (sourceTaskId) => {
    try {
        const res = await axios.post(route('tasks.checklists.clone', props.taskId), { source_task_id: sourceTaskId });
        details.value.checklists = res.data;
        toast.success('Checklist cloned');
    } catch (e) {
        toast.error('Failed to clone checklist');
    }
};

const importChecklist = async (file) => {
    const formData = new FormData();
    formData.append('file', file);
    try {
        const res = await axios.post(route('tasks.checklists.import', props.taskId), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        details.value.checklists = res.data;
        toast.success('File imported successfully');
    } catch (e) {
        toast.error(e.response?.data?.message || 'Import failed');
    }
};

const deleteChecklist = async (id) => {
    try {
        await axios.delete(route('tasks.checklists.destroy', id));
        details.value.checklists = details.value.checklists.filter(i => i.id !== id);
    } catch (e) {
        toast.error('Failed to delete');
    }
}

// PR Management
const prForm = ref({ title: '', url: '' });
const prErrors = ref({});

const addPR = async () => {
    // Front-end Validation
    prErrors.value = {};
    if (!prForm.value.title.trim()) prErrors.value.title = true;
    if (!prForm.value.url.trim()) prErrors.value.url = 'Mandatory URL';
    else if (!prForm.value.url.startsWith('http')) prErrors.value.url = 'Invalid URL';

    if (Object.keys(prErrors.value).length > 0) return;

    try {
        const res = await axios.post(route('tasks.pull-requests.store', props.taskId), prForm.value);
        if (!details.value.pull_requests) details.value.pull_requests = [];
        details.value.pull_requests.unshift(res.data);
        prForm.value = { title: '', url: '' };
        prErrors.value = {};
        toast.success('Pull request linked');
    } catch (e) {
        toast.error('Failed to link PR');
    }
};

const updatePRStatus = async (pr, status) => {
    try {
        const res = await axios.patch(route('tasks.pull-requests.update', pr.id), { status });
        const idx = details.value.pull_requests.findIndex(i => i.id === pr.id);
        if (idx !== -1) details.value.pull_requests[idx] = res.data;
        toast.success(`PR ${status}`);
    } catch (e) {
        toast.error('Failed to update status');
    }
};

const deletePR = async (id) => {
    try {
        await axios.delete(route('tasks.pull-requests.destroy', id));
        details.value.pull_requests = details.value.pull_requests.filter(i => i.id !== id);
        toast.success('PR link removed');
    } catch (e) {
        toast.error('Failed to remove link');
    }
};

// Template Logic
import Modal from '@/Components/Modal.vue';
const showTemplateModal = ref(false);
const templateName = ref('');

const saveAsTemplate = async () => {
    if (!templateName.value.trim()) return;
    
    try {
        await axios.post(route('projects.templates.store', props.project.id), {
            name: templateName.value,
            description: details.value.description,
            priority: details.value.priority,
            scrum_points: details.value.scrum_points,
            checklists: details.value.checklists?.map(c => c.content) || []
        });
        
        toast.success('Template saved successfully');
        showTemplateModal.value = false;
        templateName.value = '';
    } catch (e) {
        toast.error('Failed to save template');
    }
};
</script>
