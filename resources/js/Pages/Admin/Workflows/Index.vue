<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import { ref, computed, watch } from 'vue';
import { 
    MagnifyingGlassIcon, 
    BoltIcon, 
    PlusIcon, 
    TrashIcon, 
    PencilSquareIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    UserGroupIcon,
    UserIcon,
    ClockIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline';

import SystemIntelligenceLayout from '@/Layouts/SystemIntelligenceLayout.vue';

const props = defineProps({
    workflows: { type: Array, default: () => [] },
    roles: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] } // For multiselect
});

// State
const searchQuery = ref('');
const selectedModule = ref('ALL');
const activeWorkflow = ref(null); // The workflow currently being edited in the builder

const filteredWorkflows = computed(() => {
    let filtered = Array.isArray(props.workflows) ? props.workflows : [];

    if (selectedModule.value !== 'ALL') {
        filtered = filtered.filter(w => (w?.entity_type || '').toUpperCase() === selectedModule.value);
    }

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(w => 
            (w?.name || '').toLowerCase().includes(query) || 
            (w?.description || '').toLowerCase().includes(query)
        );
    }

    return filtered;
});

// Select a workflow to build
const selectWorkflow = (wf) => {
    activeWorkflow.value = wf;
};

// Workflow Form (Create/Edit Base Info)
const showWorkflowModal = ref(false);
const workflowForm = useForm({
    id: null,
    name: '',
    module: 'EXPENSE',
    description: ''
});

const openWorkflowModal = (wf = null) => {
    if (wf) {
        workflowForm.id = wf.id;
        workflowForm.name = wf.name;
        workflowForm.module = wf.module;
        workflowForm.description = wf.description;
    } else {
        workflowForm.reset();
        workflowForm.id = null;
    }
    showWorkflowModal.value = true;
};

const submitWorkflow = () => {
    if (workflowForm.id) {
        workflowForm.put(route('admin.workflows.update', workflowForm.id), {
             onSuccess: () => {
                 showWorkflowModal.value = false;
                 // Update active workflow reference if it was edited
                 if (activeWorkflow.value && activeWorkflow.value.id === workflowForm.id) {
                     const updated = props.workflows.find(w => w.id === workflowForm.id);
                     if (updated) activeWorkflow.value = updated;
                 }
             }
        });
    } else {
        workflowForm.post(route('admin.workflows.store'), {
             onSuccess: () => showWorkflowModal.value = false
        });
    }
};

// Stage Form (Add/Edit Stage)
const showStageModal = ref(false);
const editingStage = ref(null);
const stageForm = useForm({
    name: '', // Changed from stage_name
    approver_type: 'ROLE',
    role_id: '',
    dynamic_rule: 'reporting_manager',
    user_ids: [] // Multiselect
});

const openStageModal = (stage = null) => {
    editingStage.value = stage;
    stageForm.reset();
    
    if (stage) {
        stageForm.name = stage.name;
        stageForm.approver_type = stage.approver_type;
        stageForm.role_id = stage.role_id;
        stageForm.dynamic_rule = stage.dynamic_rule;
        stageForm.user_ids = stage.users ? stage.users.map(u => u.id) : [];
    } else {
        // Default name based on count
        const count = activeWorkflow.value ? activeWorkflow.value.stages.length + 1 : 1;
        stageForm.name = `Stage ${count}`;
    }
    
    showStageModal.value = true;
};

const submitStage = () => {
    if (!activeWorkflow.value) return; 
    
    if (editingStage.value) {
        stageForm.put(route('admin.workflows.stages.update', editingStage.value.id), {
            onSuccess: () => {
                showStageModal.value = false;
                refreshActiveWorkflow();
            }
        });
    } else {
        stageForm.post(route('admin.workflows.stages.store', activeWorkflow.value.id), {
            onSuccess: () => {
                showStageModal.value = false;
                refreshActiveWorkflow();
            }
        });
    }
};

const deleteStage = (stage) => {
    if (confirm('Remove this stage?')) {
        router.delete(route('admin.workflows.stages.destroy', stage.id), {
            onSuccess: () => refreshActiveWorkflow()
        });
    }
};

// Reordering logic
const moveStage = (index, direction) => {
    if (!activeWorkflow.value) return;
    
    const stages = [...activeWorkflow.value.stages];
    const newIndex = index + direction;
    
    if (newIndex < 0 || newIndex >= stages.length) return;
    
    // Swap
    [stages[index], stages[newIndex]] = [stages[newIndex], stages[index]];
    
    // Update local validation immediately for UI
    activeWorkflow.value.stages = stages;

    // Prepare payload
    const payload = stages.map((s, i) => ({ id: s.id, order: i + 1 }));
    
    router.put(route('admin.workflows.reorder', activeWorkflow.value.id), { stages: payload }, {
        preserveScroll: true,
        onSuccess: () => refreshActiveWorkflow() // Reload fresh from DB
    });
};

const refreshActiveWorkflow = () => {
    // Re-find the active workflow from props after Inertia reload
    if (activeWorkflow.value) {
        const updated = props.workflows.find(w => w.id === activeWorkflow.value.id);
        if (updated) activeWorkflow.value = updated;
    }
};

// Helper: Get user names for preview
const getUserNames = (userIds) => {
    if (!userIds || userIds.length === 0) return '';
    const names = props.users.filter(u => userIds.includes(u.id)).map(u => u.name);
    if (names.length <= 3) return names.join(', ');
    return `${names.slice(0, 3).join(', ')} +${names.length - 3} more`;
};
</script>

<template>
    <Head title="Workflow Builder" />
    <SystemIntelligenceLayout>
        <div class="h-[calc(100vh-100px)] flex flex-col sm:flex-row gap-6">
            
            <!-- Left Sidebar: List -->
            <div class="w-full sm:w-1/3 flex flex-col bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-gray-800">Workflows</h2>
                        <button @click="openWorkflowModal()" class="p-1.5 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 shadow-sm transition-colors">
                            <PlusIcon class="h-5 w-5" />
                        </button>
                    </div>
                    
                    <div class="relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <input v-model="searchQuery" type="text" placeholder="Search..." class="w-full pl-9 pr-3 py-2 text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 bg-white" />
                    </div>

                    <div class="flex gap-2 mt-3 overflow-x-auto pb-1 no-scrollbar">
                        <button 
                            v-for="mod in ['ALL', 'EXPENSE', 'LEAVE', 'ATTENDANCE']" 
                            :key="mod" @click="selectedModule = mod"
                            class="px-2 py-1 text-xs font-medium rounded-md whitespace-nowrap transition-colors"
                            :class="selectedModule === mod ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:bg-gray-100'"
                        >
                            {{ mod }}
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-2 space-y-2">
                    <div 
                        v-for="wf in filteredWorkflows" 
                        :key="wf.id" 
                        @click="selectWorkflow(wf)"
                        class="p-3 rounded-lg border cursor-pointer hover:shadow-sm transition-all duration-200 group"
                        :class="activeWorkflow?.id === wf.id ? 'border-indigo-500 bg-indigo-50/50 ring-1 ring-indigo-500' : 'border-gray-100 bg-white hover:border-indigo-200'"
                    >
                        <div class="flex justify-between items-start">
                             <div>
                                <h3 class="text-sm font-semibold text-gray-900">{{ wf.name }}</h3>
                                <p class="text-xs text-gray-400 mt-0.5">{{ wf.entity_type }}</p>
                            </div>
                            <span class="w-2 h-2 rounded-full" :class="wf.is_active ? 'bg-green-400' : 'bg-gray-300'"></span>
                        </div>
                    </div>
                     <div v-if="filteredWorkflows.length === 0" class="text-center py-8 text-xs text-gray-400">
                        No workflows found
                    </div>
                </div>
            </div>

            <!-- Right Panel: Builder -->
            <div class="flex-1 bg-white border border-gray-200 rounded-lg shadow-sm flex flex-col overflow-hidden relative">
                
                <div v-if="activeWorkflow" class="flex flex-col h-full">
                    <!-- Workflow Header -->
                    <div class="p-6 border-b border-gray-100 flex justify-between items-start bg-gray-50/30">
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">{{ activeWorkflow.name }}</h2>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 uppercase">{{ activeWorkflow.entity_type }}</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1 max-w-xl">{{ activeWorkflow.description }}</p>
                        </div>
                        <button @click="openWorkflowModal(activeWorkflow)" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium underline">
                            Edit Settings
                        </button>
                    </div>

                    <!-- Builder Area -->
                    <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50">
                        <div class="max-w-3xl mx-auto">
                            <!-- Start Node -->
                            <div class="flex justify-center mb-8">
                                <div class="px-4 py-2 bg-gray-800 text-white text-sm font-bold rounded-full shadow-lg flex items-center gap-2">
                                    <BoltIcon class="h-4 w-4" />
                                    <span>Trigger: {{ activeWorkflow.trigger_event || 'Init' }}</span>
                                </div>
                            </div>
                            
                            <!-- Stages Timeline -->
                            <div class="relative pl-8 border-l-2 border-indigo-100 space-y-8 pb-12">
                                <div 
                                    v-for="(stage, index) in activeWorkflow.stages" 
                                    :key="stage.id" 
                                    class="relative pl-8 transition-all duration-300"
                                >
                                    <!-- Connector Dot -->
                                    <div class="absolute -left-[9px] top-6 w-4 h-4 rounded-full border-2 border-indigo-500 bg-white ring-4 ring-white">
                                        <div class="w-full h-full rounded-full bg-indigo-500 transform scale-50"></div>
                                    </div>

                                    <!-- Connector Line -->
                                    <div class="absolute left-0 top-10 bottom-[-32px] w-0.5 -ml-[1px] bg-indigo-100 last:hidden"></div>

                                    <!-- Card -->
                                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow group relative pr-12">
                                        <!-- Actions -->
                                        <div class="absolute top-4 right-4 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="moveStage(index, -1)" :disabled="index === 0" class="p-1 hover:bg-gray-100 rounded text-gray-400 hover:text-gray-600 disabled:opacity-30">
                                                <ArrowUpIcon class="h-4 w-4" />
                                            </button>
                                            <button @click="moveStage(index, 1)" :disabled="index === activeWorkflow.stages.length - 1" class="p-1 hover:bg-gray-100 rounded text-gray-400 hover:text-gray-600 disabled:opacity-30">
                                                <ArrowDownIcon class="h-4 w-4" />
                                            </button>
                                            <div class="h-px bg-gray-100 my-1"></div>
                                            <button @click="openStageModal(stage)" class="p-1 hover:bg-blue-50 rounded text-gray-400 hover:text-blue-600">
                                                <PencilSquareIcon class="h-4 w-4" />
                                            </button>
                                            <button @click="deleteStage(stage)" class="p-1 hover:bg-red-50 rounded text-gray-400 hover:text-red-500">
                                                <TrashIcon class="h-4 w-4" />
                                            </button>
                                        </div>

                                        <!-- Header -->
                                        <div class="flex items-center gap-3 mb-3">
                                            <span class="flex items-center justify-center h-8 w-8 rounded-lg bg-indigo-50 text-indigo-700 font-bold font-mono text-sm border border-indigo-100">
                                                {{ index + 1 }}
                                            </span>
                                            <h4 class="text-base font-bold text-gray-900">{{ stage.name }}</h4>
                                        </div>

                                        <!-- Details -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mt-4 bg-gray-50/50 p-3 rounded-lg border border-gray-100">
                                            <div>
                                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1">Approver Definition</span>
                                                <div class="flex items-center gap-2 text-gray-800">
                                                    <UserIcon v-if="stage.approver_type === 'USER'" class="h-4 w-4 text-purple-500" />
                                                    <UserGroupIcon v-else-if="stage.approver_type === 'ROLE'" class="h-4 w-4 text-blue-500" />
                                                    <BoltIcon v-else class="h-4 w-4 text-orange-500" />
                                                    
                                                    <span class="font-medium">
                                                        {{ stage.approver_type === 'ROLE' ? (stage.role?.name || 'Unknown Role') : 
                                                           stage.approver_type === 'DYNAMIC' ? stage.dynamic_rule : 'Specific Users' }}
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div v-if="stage.users && stage.users.length > 0">
                                                 <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1">Who can approve?</span>
                                                 <div class="flex items-center gap-1.5 flex-wrap">
                                                     <img 
                                                        v-for="user in stage.users.slice(0, 5)" 
                                                        :key="user.id"
                                                        :src="`https://ui-avatars.com/api/?name=${user.name}&background=random`" 
                                                        class="h-6 w-6 rounded-full border border-white shadow-sm"
                                                        :title="user.name"
                                                     />
                                                     <span v-if="stage.users.length > 5" class="text-xs text-gray-500 font-medium">+{{ stage.users.length - 5 }}</span>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="relative pl-8">
                                    <div class="absolute -left-[9px] top-6 w-4 h-4 rounded-full border-2 border-dashed border-gray-300 bg-white"></div>
                                    <button @click="openStageModal()" class="w-full py-4 border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center text-sm font-medium text-gray-500 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50/30 transition-all group">
                                         <PlusIcon class="h-5 w-5 mr-2 group-hover:scale-110 transition-transform" />
                                         Add Approval Stage
                                    </button>
                                </div>

                                <!-- End Node -->
                                <div class="flex justify-center mt-8 pl-8 opacity-50">
                                    <div class="px-4 py-1.5 bg-gray-100 text-gray-500 text-xs font-bold rounded-full border border-gray-200 flex items-center gap-2">
                                        <CheckCircleIcon class="h-3 w-3" />
                                        <span>Workflow Complete</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center justify-center h-full text-center p-8 bg-gray-50/30">
                    <div class="bg-white p-4 rounded-full shadow-sm mb-4">
                        <BoltIcon class="h-12 w-12 text-gray-300" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Select a workflow to build</h3>
                    <p class="text-gray-500 mt-2 max-w-sm">Choose a workflow from the left sidebar to configure its stages, approvers, and logic.</p>
                </div>
            </div>

        </div>

        <!-- Modals -->
        <Modal :show="showWorkflowModal" @close="showWorkflowModal = false">
             <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6">{{ workflowForm.id ? 'Edit Workflow' : 'Create New Workflow' }}</h3>
                <form @submit.prevent="submitWorkflow">
                    <div class="space-y-5">
                        <div>
                            <InputLabel value="Workflow Name" />
                            <TextInput v-model="workflowForm.name" class="w-full mt-1" required placeholder="e.g. Senior Manager Expense" />
                        </div>
                        <div>
                            <InputLabel value="Module" />
                            <select v-model="workflowForm.module" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="EXPENSE">Expense Management</option>
                                <option value="LEAVE">Leave Management</option>
                                <option value="ATTENDANCE">Attendance Correction</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Description" />
                            <TextInput v-model="workflowForm.description" class="w-full mt-1" placeholder="Brief description of when this triggers..." />
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end gap-3">
                        <SecondaryButton @click="showWorkflowModal = false">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="workflowForm.processing">Save Workflow</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="showStageModal" @close="showStageModal = false">
             <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6">{{ editingStage ? 'Edit Stage' : 'Add Approval Stage' }}</h3>
                <form @submit.prevent="submitStage">
                    <div class="space-y-6">
                         <div>
                            <InputLabel value="Stage Name" />
                            <TextInput v-model="stageForm.name" class="w-full mt-1" required placeholder="e.g. Finance Review" />
                        </div>
                        
                         <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel value="Approver Type" />
                                <select v-model="stageForm.approver_type" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="ROLE">Role Based</option>
                                    <option value="DYNAMIC">Dynamic Rule</option>
                                    <option value="USER">Specific Users</option>
                                </select>
                            </div>
                            
                            <!-- Dynamic Content Based on Type -->
                            <div v-if="stageForm.approver_type === 'ROLE'">
                                <InputLabel value="Select Role" />
                                <select v-model="stageForm.role_id" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                </select>
                            </div>

                            <div v-if="stageForm.approver_type === 'DYNAMIC'">
                                <InputLabel value="Select Rule" />
                                <select v-model="stageForm.dynamic_rule" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="reporting_manager">Reporting Manager</option>
                                    <option value="department_head">Department Head</option>
                                    <option value="project_manager">Project Manager</option>
                                </select>
                            </div>
                         </div>
                         
                         <!-- Multiselect for USERS -->
                         <div v-if="stageForm.approver_type === 'USER'">
                             <InputLabel value="Select Approvers" />
                             <div class="mt-2 text-xs text-gray-500 mb-2">Hold Ctrl/Cmd to select multiple.</div>
                             <select multiple v-model="stageForm.user_ids" class="w-full h-32 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                             </select>
                         </div>

                    </div>
                    <div class="mt-8 flex justify-end gap-3">
                        <SecondaryButton @click="showStageModal = false">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="stageForm.processing">
                            {{ editingStage ? 'Update Stage' : 'Add Stage' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </SystemIntelligenceLayout>
</template>
