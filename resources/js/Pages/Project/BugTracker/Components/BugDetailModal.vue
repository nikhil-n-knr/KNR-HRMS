<template>
    <Modal :show="show" @close="close" maxWidth="4xl">
        <div v-if="loading" class="p-12 text-center text-gray-500">
            <svg class="animate-spin h-8 w-8 mx-auto text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-2">Loading ticket details...</p>
        </div>

        <div v-else-if="bug" class="flex flex-col h-[85vh]">
            <!-- Header -->
            <div class="px-6 py-4 border-b flex justify-between items-start bg-gray-50">
                <div>
                     <div class="flex items-center gap-3 mb-1">
                        <span class="text-xs font-mono text-gray-500">#{{ bug.id }}</span>
                        <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getSeverityClass(bug.severity)]">
                            {{ bug.severity }}
                        </span>
                        <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getPriorityClass(bug.priority)]">
                            {{ bug.priority }}
                        </span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">{{ bug.subject }}</h2>
                </div>
                <button @click="close" class="text-gray-400 hover:text-gray-500">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-hidden flex flex-col md:flex-row">
                <!-- Main Content (Left) -->
                <div class="flex-1 overflow-y-auto p-6 border-r">
                    <!-- Workflow State Stepper (Simplified) -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Stage</label>
                        <select v-model="selectedStage" @change="updateStage" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md">
                            <option v-for="stage in stages" :key="stage.id" :value="stage.id">
                                {{ stage.name }}
                            </option>
                        </select>
                    </div>

                    <div class="prose max-w-none mb-8">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Description</h3>
                        <p class="text-gray-800 whitespace-pre-wrap">{{ bug.description }}</p>
                    </div>
                    
                    <div v-if="bug.steps_to_reproduce" class="mb-8">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Steps to Reproduce</h3>
                         <p class="text-gray-800 whitespace-pre-wrap">{{ bug.steps_to_reproduce }}</p>
                    </div>

                    <!-- Attachments -->
                    <div v-if="bug.attachments && bug.attachments.length" class="mb-8">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Attachments</h3>
                        <div class="flex flex-wrap gap-2">
                            <a v-for="(file, idx) in bug.attachments" :key="idx" 
                               :href="'/storage/' + file.path" 
                               target="_blank"
                               class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                                <svg class="-ml-0.5 mr-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" /></svg>
                                {{ file.name }}
                            </a>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button @click="activeTab = 'comments'" :class="[activeTab === 'comments' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                                Comments
                            </button>
                            <button @click="activeTab = 'history'" :class="[activeTab === 'history' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                                History
                            </button>
                        </nav>
                    </div>

                    <div class="mt-6">
                        <BugComments v-if="activeTab === 'comments'" :bug-id="bug.id" :initial-comments="bug.comments" />
                        <BugHistory v-if="activeTab === 'history'" :activities="bug.activities" />
                    </div>

                </div>

                <!-- Meta Sidebar (Right) -->
                <div class="w-full md:w-80 bg-gray-50 p-6 overflow-y-auto">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Details</h3>
                    
                    <dl class="space-y-4 text-sm">
                        <!-- Project -->
                        <div>
                            <dt class="text-gray-500">Project</dt>
                            <dd class="font-medium text-gray-900">{{ bug.project.name }}</dd>
                        </div>
                        
                        <!-- Module -->
                        <div v-if="bug.module">
                            <dt class="text-gray-500">Module</dt>
                            <dd class="font-medium text-gray-900">{{ bug.module.name }}</dd>
                        </div>

                         <!-- Reporter -->
                        <div>
                            <dt class="text-gray-500">Reporter</dt>
                            <dd class="font-medium text-gray-900 flex items-center gap-2">
                                <img v-if="bug.reporter.avatar" :src="bug.reporter.avatar" class="h-6 w-6 rounded-full">
                                {{ bug.reporter.name || bug.reporter.first_name }}
                            </dd>
                        </div>

                        <!-- Assignee -->
                        <div>
                            <dt class="text-gray-500">Assignee</dt>
                            <dd class="font-medium text-gray-900 flex items-center gap-2">
                                <template v-if="bug.assignee">
                                    <img v-if="bug.assignee.avatar" :src="bug.assignee.avatar" class="h-6 w-6 rounded-full">
                                    {{ bug.assignee.name || bug.assignee.first_name + ' ' + bug.assignee.last_name }}
                                </template>
                                <span v-else class="text-gray-400 italic">Unassigned</span>
                            </dd>
                        </div>

                        <!-- Dates -->
                        <div>
                            <dt class="text-gray-500">Created</dt>
                            <dd class="text-gray-900">{{ new Date(bug.created_at).toLocaleDateString() }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Updated</dt>
                            <dd class="text-gray-900">{{ new Date(bug.updated_at).toLocaleDateString() }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import BugComments from './BugComments.vue';
import BugHistory from './BugHistory.vue';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    bugId: Number,
    stages: Array // Pass available stages for editing
});

const emit = defineEmits(['close', 'updated']);

const bug = ref(null);
const loading = ref(false);
const activeTab = ref('comments');
const selectedStage = ref(null);

const fetchBugDetails = async () => {
    if (!props.bugId) return;
    loading.value = true;
    try {
        const { data } = await axios.get(route('bugs.show', props.bugId));
        bug.value = data;
        selectedStage.value = data.workflow_stage_id;
    } catch (e) {
        console.error("Failed to fetch bug details", e);
    } finally {
        loading.value = false;
    }
};

watch(() => props.show, (val) => {
    if (val) {
        fetchBugDetails();
        activeTab.value = 'comments';
    } else {
        bug.value = null; // Clear on close
    }
});

const updateStage = async () => {
    try {
        await axios.put(route('bugs.stage.update', bug.value.id), {
            stage_id: selectedStage.value
        });
        emit('updated'); // Signal parent to refresh list
        // Refresh local details to get new history log
        fetchBugDetails();
    } catch (e) {
        console.error("Failed to update stage", e);
    }
};

const close = () => {
    emit('close');
};

const getSeverityClass = (severity) => {
    const map = {
        critical: 'bg-red-100 text-red-800',
        high: 'bg-orange-100 text-orange-800',
        medium: 'bg-blue-100 text-blue-800',
        low: 'bg-gray-100 text-gray-800'
    };
    return map[severity] || 'bg-gray-100 text-gray-800';
};

const getPriorityClass = (priority) => {
    const map = {
        urgent: 'bg-red-100 text-red-800',
        high: 'bg-orange-100 text-orange-800',
        normal: 'bg-blue-100 text-blue-800',
        low: 'bg-green-100 text-green-800'
    };
    return map[priority] || 'bg-gray-100 text-gray-800';
};

</script>
