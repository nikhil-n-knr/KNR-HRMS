<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
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
    CheckCircleIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/outline';

import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    workflows: { type: Array, default: () => [] },
    roles: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] }
});

// State
const searchQuery = ref('');
const activeWorkflow = ref(null);

const filteredWorkflows = computed(() => {
    let filtered = Array.isArray(props.workflows) ? props.workflows : [];
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(w => 
            (w?.name || '').toLowerCase().includes(query) || 
            (w?.description || '').toLowerCase().includes(query)
        );
    }
    return filtered;
});

const selectWorkflow = (wf) => {
    activeWorkflow.value = wf;
};

// Workflow Form
const showWorkflowModal = ref(false);
const workflowForm = useForm({
    id: null,
    name: '',
    description: '',
    entity_type: 'Lead',
    trigger_event: 'created'
});

const openWorkflowModal = (wf = null) => {
    if (wf) {
        workflowForm.id = wf.id;
        workflowForm.name = wf.name;
        workflowForm.description = wf.description;
        workflowForm.entity_type = wf.entity_type;
        workflowForm.trigger_event = wf.trigger_event;
    } else {
        workflowForm.reset();
        workflowForm.id = null;
    }
    showWorkflowModal.value = true;
};

const submitWorkflow = () => {
    if (workflowForm.id) {
        workflowForm.put(route('crm.workflows.update', workflowForm.id), {
            onSuccess: () => {
                showWorkflowModal.value = false;
                refreshActive();
            }
        });
    } else {
        workflowForm.post(route('crm.workflows.store'), {
            onSuccess: () => showWorkflowModal.value = false
        });
    }
};

// Stage Form
const showStageModal = ref(false);
const editingStage = ref(null);
const stageForm = useForm({
    name: '',
    action_type: 'approval',
    config: {}
});

const openStageModal = (stage = null) => {
    editingStage.value = stage;
    stageForm.reset();
    if (stage) {
        stageForm.name = stage.name;
        stageForm.action_type = stage.action_type;
        stageForm.config = stage.config || {};
    }
    showStageModal.value = true;
};

const submitStage = () => {
    if (!activeWorkflow.value) return;
    if (editingStage.value) {
        stageForm.put(route('crm.workflows.stages.update', editingStage.value.id), {
            onSuccess: () => {
                showStageModal.value = false;
                refreshActive();
            }
        });
    } else {
        stageForm.post(route('crm.workflows.stages.store', activeWorkflow.value.id), {
            onSuccess: () => {
                showStageModal.value = false;
                refreshActive();
            }
        });
    }
};

const deleteStage = (stage) => {
    if (confirm('Remove this action step?')) {
        router.delete(route('crm.workflows.stages.destroy', stage.id), {
            onSuccess: () => refreshActive()
        });
    }
};

const deleteWorkflow = (wf) => {
    if (confirm('Delete this CRM Workflow?')) {
        router.delete(route('crm.workflows.destroy', wf.id), {
            onSuccess: () => {
                if (activeWorkflow.value?.id === wf.id) activeWorkflow.value = null;
            }
        });
    }
};

const refreshActive = () => {
    if (activeWorkflow.value) {
        const found = props.workflows.find(w => w.id === activeWorkflow.value.id);
        if (found) activeWorkflow.value = found;
    }
};

</script>

<template>
    <Head title="CRM Workflow Builder" />
    <MainLayout>
        <div class="max-w-[1600px] mx-auto h-[calc(100vh-120px)] flex flex-col gap-6">
            <!-- Header -->
            <div class="flex justify-between items-center bg-white/40 backdrop-blur-md p-6 rounded-[2rem] border border-white/60 shadow-xl shadow-emerald-900/5">
                <div>
                     <nav class="flex text-xs font-black uppercase tracking-widest text-emerald-600/50 mb-2 gap-2 items-center">
                        <Link :href="route('crm.hub', {section: 'config'})">CONFIG</Link>
                        <ChevronRightIcon class="h-3 w-3" />
                        <span class="text-emerald-900">WORKFLOW ENGINE</span>
                    </nav>
                    <h1 class="text-3xl font-black text-gray-900 tracking-tighter">CRM Automation <span class="text-emerald-600">Architect</span></h1>
                </div>
                <button @click="openWorkflowModal()" class="flex items-center gap-2 px-6 py-3 bg-gray-900 text-white rounded-2xl font-black text-sm hover:bg-emerald-600 transition-all shadow-lg shadow-gray-900/10 active:scale-95">
                    <PlusIcon class="h-5 w-5" />
                    CREATE NEW SCHEMA
                </button>
            </div>

            <div class="flex-1 flex gap-6 min-h-0">
                <!-- Left: List -->
                <div class="w-80 flex flex-col bg-white/60 backdrop-blur-md rounded-[2.5rem] border border-white/60 shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <div class="relative">
                            <input v-model="searchQuery" type="text" placeholder="Search schemas..." 
                                class="w-full bg-gray-50/50 border-none rounded-2xl py-3 pl-10 text-sm font-bold placeholder-gray-400 focus:ring-2 focus:ring-emerald-500/20" />
                            <MagnifyingGlassIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4 space-y-2">
                        <div v-for="wf in filteredWorkflows" :key="wf.id" 
                            @click="selectWorkflow(wf)"
                            class="p-4 rounded-[1.5rem] cursor-pointer transition-all duration-300 group border"
                            :class="activeWorkflow?.id === wf.id 
                                ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20 border-emerald-500' 
                                : 'bg-white/50 text-gray-600 hover:bg-white border-transparent hover:border-emerald-100 hover:shadow-sm'"
                        >
                            <div class="flex justify-between items-start mb-1">
                                <h3 class="font-black text-sm tracking-tight truncate">{{ wf.name }}</h3>
                                <div class="w-2 h-2 rounded-full" :class="wf.is_active ? 'bg-emerald-400' : 'bg-gray-300'"></div>
                            </div>
                            <div class="flex items-center gap-2 text-sm font-black uppercase tracking-widest"
                                :class="activeWorkflow?.id === wf.id ? 'text-emerald-100' : 'text-gray-400'">
                                {{ wf.entity_type }} • {{ (wf.stages || []).length }} STAGES
                            </div>
                        </div>

                        <div v-if="filteredWorkflows.length === 0" class="flex flex-col items-center justify-center py-12 text-gray-400">
                            <BoltIcon class="h-10 w-10 mb-2 opacity-20" />
                            <p class="text-sm font-black uppercase tracking-widest">No active schemas</p>
                        </div>
                    </div>
                </div>

                <!-- Right: Builder Area -->
                <div class="flex-1 bg-white/60 backdrop-blur-md rounded-[2.5rem] border border-white/60 shadow-lg flex flex-col overflow-hidden relative">
                    <div v-if="activeWorkflow" class="flex flex-col h-full">
                        <!-- Toolbar -->
                        <div class="p-8 border-b border-gray-100 flex justify-between items-start bg-gray-50/30">
                            <div>
                                <h2 class="text-2xl font-black text-gray-900 tracking-tighter">{{ activeWorkflow.name }}</h2>
                                <p class="text-sm text-gray-500 font-bold mt-1">{{ activeWorkflow.description || 'Dedicated CRM automation logic' }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <button @click="openWorkflowModal(activeWorkflow)" class="p-2.5 rounded-xl bg-white text-gray-600 hover:text-emerald-600 border border-gray-100 shadow-sm transition-all hover:shadow-md">
                                    <PencilSquareIcon class="h-5 w-5" />
                                </button>
                                <button @click="deleteWorkflow(activeWorkflow)" class="p-2.5 rounded-xl bg-white text-gray-600 hover:text-red-600 border border-gray-100 shadow-sm transition-all hover:shadow-md">
                                    <TrashIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </div>

                        <!-- Canvas -->
                        <div class="flex-1 overflow-y-auto p-12 bg-gray-50/20">
                            <div class="max-w-2xl mx-auto flex flex-col items-center gap-8">
                                <!-- Trigger Node -->
                                <div class="w-full bg-gray-900 text-white p-6 rounded-[2rem] shadow-2xl relative">
                                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-emerald-500 text-xs font-black uppercase tracking-widest rounded-full">ENTRY TRIGGER</div>
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center">
                                            <BoltIcon class="h-6 w-6 text-emerald-400" />
                                        </div>
                                        <div>
                                            <div class="text-sm font-black text-emerald-400/80 uppercase tracking-widest mb-0.5">{{ activeWorkflow.entity_type }} Event</div>
                                            <div class="text-base font-black tracking-tight">On {{ activeWorkflow.trigger_event }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stages -->
                                <template v-for="(stage, idx) in activeWorkflow.stages" :key="stage.id">
                                     <div class="w-0.5 h-8 bg-gray-200"></div>
                                     <div class="w-full bg-white p-6 rounded-[2rem] border border-gray-100 shadow-xl shadow-gray-900/5 group relative">
                                         <div class="absolute -left-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-white border-2 border-emerald-500 flex items-center justify-center text-sm font-black text-emerald-600 shadow-sm">
                                             {{ idx + 1 }}
                                         </div>
                                         <div class="flex items-center justify-between">
                                             <div class="flex items-center gap-4">
                                                 <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black">
                                                     <PlusIcon v-if="stage.action_type === 'approval'" class="h-5 w-5" />
                                                     <BoltIcon v-else class="h-5 w-5" />
                                                 </div>
                                                 <div>
                                                     <h4 class="font-black text-gray-800 tracking-tight">{{ stage.name }}</h4>
                                                     <div class="text-sm font-black text-gray-400 uppercase tracking-widest">{{ stage.action_type }}</div>
                                                 </div>
                                             </div>
                                             <div class="opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                                                <button @click="openStageModal(stage)" class="p-1.5 hover:bg-gray-50 rounded-lg text-gray-400 hover:text-emerald-600 transition-colors">
                                                    <PencilSquareIcon class="h-4 w-4" />
                                                </button>
                                                <button @click="deleteStage(stage)" class="p-1.5 hover:bg-red-50 rounded-lg text-gray-400 hover:text-red-600 transition-colors">
                                                    <TrashIcon class="h-4 w-4" />
                                                </button>
                                             </div>
                                         </div>
                                     </div>
                                </template>

                                <!-- Add Base Node -->
                                <div class="w-0.5 h-8 bg-gray-200 border-dashed"></div>
                                <button @click="openStageModal()" class="w-full py-6 border-2 border-dashed border-gray-200 rounded-[2rem] text-gray-400 hover:border-emerald-300 hover:text-emerald-600 hover:bg-emerald-50/50 transition-all font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2 group">
                                    <PlusIcon class="h-5 w-5 group-hover:scale-110 transition-transform" />
                                    INSERT ACTION STEP
                                </button>

                                <div class="w-0.5 h-8 bg-gray-200"></div>
                                <div class="px-6 py-2 bg-gray-100 text-gray-400 text-sm font-black uppercase tracking-widest rounded-full">EXIT SCHEMA</div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="flex-1 flex flex-col items-center justify-center p-12 text-center">
                        <div class="w-24 h-24 bg-emerald-50 text-emerald-200 rounded-[2.5rem] flex items-center justify-center mb-6">
                            <BoltIcon class="h-12 w-12" />
                        </div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight mb-2">Initialize Your Automation Flow</h3>
                        <p class="text-sm text-gray-500 font-bold max-w-sm mb-8">Select a schema from the sidebar or architect a new one to begin optimizing your CRM operations.</p>
                        <button @click="openWorkflowModal()" class="px-8 py-3 bg-emerald-600 text-white rounded-2xl font-black text-sm hover:shadow-lg shadow-emerald-500/20 transition-all">
                            CREATE MY FIRST SCHEMA
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workflow Lifecycle Modal -->
        <Modal :show="showWorkflowModal" @close="showWorkflowModal = false">
            <div class="p-8">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-black">
                        <BoltIcon class="h-6 w-6" />
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-900 tracking-tight">{{ workflowForm.id ? 'Refine Schema' : 'Architect New Schema' }}</h2>
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Blueprint your automation logic</p>
                    </div>
                </div>

                <form @submit.prevent="submitWorkflow" class="space-y-6">
                    <div>
                        <InputLabel value="SCHEMA NAME" class="text-sm font-black text-gray-400 tracking-widest mb-1.5" />
                        <TextInput v-model="workflowForm.name" class="w-full bg-gray-50 border-none rounded-2xl shadow-inner font-bold" required placeholder="e.g. VIP Lead Prioritization" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="TARGET ENTITY" class="text-sm font-black text-gray-400 tracking-widest mb-1.5" />
                            <select v-model="workflowForm.entity_type" class="w-full bg-gray-50 border-none rounded-2xl shadow-inner font-bold text-sm focus:ring-emerald-500 py-3">
                                <option value="Lead">Lead</option>
                                <option value="Deal">Deal</option>
                                <option value="Contact">Contact</option>
                                <option value="Quote">Quote</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="ENTRY EVENT" class="text-sm font-black text-gray-400 tracking-widest mb-1.5" />
                            <select v-model="workflowForm.trigger_event" class="w-full bg-gray-50 border-none rounded-2xl shadow-inner font-bold text-sm focus:ring-emerald-500 py-3">
                                <option value="created">Created</option>
                                <option value="updated">Updated</option>
                                <option value="status_changed">Status Changed</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <InputLabel value="BLUEPRINT DESCRIPTION" class="text-sm font-black text-gray-400 tracking-widest mb-1.5" />
                        <textarea v-model="workflowForm.description" rows="3" class="w-full bg-gray-50 border-none rounded-2xl shadow-inner font-bold text-sm focus:ring-emerald-500 p-4" placeholder="Briefly describe the automation logic..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                        <button type="button" @click="showWorkflowModal = false" class="px-6 py-3 text-xs font-black uppercase tracking-widest text-gray-400 hover:text-gray-600">ABORT</button>
                        <button :disabled="workflowForm.processing" class="px-8 py-3 bg-gray-900 text-white rounded-2xl font-black text-sm hover:bg-emerald-600 transition-all shadow-xl shadow-gray-900/10">
                            {{ workflowForm.id ? 'UPDATE SCHEMA' : 'DEPLOY SCHEMA' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Stage Sculptor Modal -->
        <Modal :show="showStageModal" @close="showStageModal = false">
             <div class="p-8">
                 <h3 class="text-xl font-black text-gray-900 mb-8 uppercase tracking-widest">Insertion Sculptor</h3>
                 <form @submit.prevent="submitStage" class="space-y-6">
                     <div>
                        <InputLabel value="ACTION NAME" class="text-sm font-black text-gray-400 tracking-widest mb-1.5" />
                        <TextInput v-model="stageForm.name" class="w-full bg-gray-50 border-none rounded-2xl shadow-inner font-bold" required placeholder="e.g. Finance Approval" />
                    </div>

                    <div>
                        <InputLabel value="ACTION TYPE" class="text-sm font-black text-gray-400 tracking-widest mb-1.5" />
                        <select v-model="stageForm.action_type" class="w-full bg-gray-50 border-none rounded-2xl shadow-inner font-bold text-sm focus:ring-emerald-500 py-3">
                            <option value="approval">Approval Chain</option>
                            <option value="notification">Instant Alert</option>
                            <option value="auto_update">Property Update</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                        <button type="button" @click="showStageModal = false" class="px-6 py-3 text-xs font-black uppercase tracking-widest text-gray-400 hover:text-gray-600">DISCARD</button>
                        <button :disabled="stageForm.processing" class="px-8 py-3 bg-emerald-600 text-white rounded-2xl font-black text-sm hover:shadow-lg transition-all">
                            ATTACH STEP
                        </button>
                    </div>
                 </form>
             </div>
        </Modal>
    </MainLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
