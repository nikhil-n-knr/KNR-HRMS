<template>
    <Head title="Workflow Architect" />
    <MainLayout>
        <div class="h-full bg-gray-50 p-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Workflow Architect</h1>
                        <p class="mt-2 text-sm text-gray-600">Design and automate your project lifecycle stages with advanced logic.</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                        <h2 class="font-bold text-gray-900">{{ workflow?.name }} Pipeline</h2>
                        <button @click="openAddModal" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-sm hover:bg-indigo-700 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Stage
                        </button>
                    </div>
                    
                    <div class="p-8">
                        <div class="flex gap-4 overflow-x-auto pb-6">
                            <div v-for="stage in workflow?.stages" :key="stage.id" 
                                class="flex-shrink-0 w-72 bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-shadow group relative">
                                <!-- Stage Header with Edit/Delete -->
                                <div class="flex justify-between items-start mb-4">
                                    <span class="px-2 py-1 bg-gray-50 rounded-lg text-sm font-bold text-gray-400 uppercase tracking-widest">{{ stage.stage_order }}</span>
                                    <div class="flex items-center gap-1 opacity-100 group-hover:opacity-100 transition-opacity">
                                        <button @click="openEditModal(stage)" class="p-1 text-gray-400 hover:text-indigo-600 rounded bg-gray-50 hover:bg-indigo-50">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <button @click="deleteStage(stage)" class="p-1 text-gray-400 hover:text-red-600 rounded bg-gray-50 hover:bg-red-50">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Stage Content -->
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: stage.color || '#6366f1' }"></div>
                                    <h4 class="font-bold text-gray-900 truncate">{{ stage.name }}</h4>
                                </div>

                                <div class="space-y-3 mt-4">
                                    <div class="flex items-center gap-2 text-base text-gray-500 font-medium">
                                        <div class="w-1.5 h-1.5 rounded-full" :class="stage.is_final ? 'bg-emerald-500' : 'bg-amber-500'"></div>
                                        {{ stage.is_final ? 'Terminal Stage' : 'Active Stage' }}
                                    </div>

                                    <!-- Mentor Badge -->
                                    <div v-if="stage.mentor_id" class="flex items-center gap-2">
                                        <div class="w-5 h-5 rounded-full bg-indigo-100 flex items-center justify-center text-sm font-bold text-indigo-600">
                                            M
                                        </div>
                                        <div class="text-base text-gray-600">
                                            <span class="font-bold">Mentor:</span> {{ getUserName(stage.mentor_id) }}
                                        </div>
                                    </div>

                                    <!-- Approver Badge -->
                                    <div v-if="stage.requires_verification" class="flex items-center gap-2">
                                        <div class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center text-sm font-bold text-emerald-600">
                                            A
                                        </div>
                                        <div class="text-base text-gray-600">
                                            <span class="font-bold">Approver:</span> {{ stage.approver_type?.replace('_', ' ') }}
                                        </div>
                                    </div>

                                    <div v-if="stage.is_client_visible" class="inline-flex items-center px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 text-sm font-bold uppercase">Client Visible</div>
                                </div>
                            </div>

                            <!-- Empty state -->
                            <div v-if="!workflow?.stages?.length" class="flex-1 flex flex-col items-center justify-center py-16 text-gray-400">
                                <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <p class="font-medium">No stages yet. Add your first stage.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Stage Modal (Add/Edit) -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="showModal = false">
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/30">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ isEditing ? 'Configure' : 'Add' }} Workflow Stage</h2>
                            <p class="text-sm text-gray-500 mt-0.5">{{ workflow?.name }} Workflow</p>
                        </div>
                        <button @click="showModal = false" class="w-8 h-8 flex items-center justify-center rounded-full bg-white border border-gray-100 hover:bg-gray-100 transition text-gray-500 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Tabs -->
                    <div class="flex border-b border-gray-100 px-6">
                        <button v-for="tab in tabs" :key="tab.id" 
                            @click="activeTab = tab.id"
                            class="px-4 py-3 text-sm font-bold transition-all border-b-2"
                            :class="activeTab === tab.id ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-400 hover:text-gray-600'">
                            {{ tab.name }}
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-8 bg-white">
                        <form @submit.prevent="submitForm" id="stageForm">
                            <!-- Tab 1: General Info -->
                            <div v-show="activeTab === 'general'" class="space-y-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Stage Name *</label>
                                    <input v-model="form.name" type="text" placeholder="e.g. Code Review, Testing, Deployment" required
                                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Brand Color</label>
                                    <div class="flex gap-2.5 flex-wrap">
                                        <button type="button" v-for="color in colors" :key="color"
                                            @click="form.color = color"
                                            :style="{ backgroundColor: color }"
                                            class="w-9 h-9 rounded-full border-2 transition-all shadow-sm"
                                            :class="form.color === color ? 'border-gray-800 scale-110 ring-2 ring-gray-100' : 'border-transparent hover:scale-105'">
                                        </button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="flex items-center gap-3 p-4 rounded-2xl border border-gray-100 bg-gray-50/30 cursor-pointer hover:bg-gray-50 transition-colors">
                                        <input type="checkbox" v-model="form.is_final" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                        <div>
                                            <span class="block text-sm font-bold text-gray-900">Final Stage</span>
                                            <span class="block text-xs text-gray-500">Marks ticket as resolved</span>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-3 p-4 rounded-2xl border border-gray-100 bg-gray-50/30 cursor-pointer hover:bg-gray-50 transition-colors">
                                        <input type="checkbox" v-model="form.is_client_visible" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                        <div>
                                            <span class="block text-sm font-bold text-gray-900">Client Portal</span>
                                            <span class="block text-xs text-gray-500">Visible to external clients</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Tab 2: Logic & Assignees -->
                            <div v-show="activeTab === 'logic'" class="space-y-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Stage Mentor</label>
                                    <select v-model="form.mentor_id" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                        <option :value="null">No Mentor Assigned</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                    </select>
                                    <p class="mt-2 text-base text-gray-500 italic">This user will be notified and can guide ticket progress in this stage.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Auto-Assign to Team</label>
                                    <select v-model="form.assigned_team_id" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                        <option :value="null">None</option>
                                        <option v-for="team in teams" :key="team.id" :value="team.id">{{ team.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Auto-Close Policy</label>
                                    <div class="flex items-center gap-3">
                                        <input type="number" v-model="form.auto_close_days" placeholder="0" class="w-24 rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" />
                                        <span class="text-sm text-gray-600 font-medium">days after inactivity</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab 3: Approval settings -->
                            <div v-show="activeTab === 'approval'" class="space-y-6">
                                <label class="flex items-center gap-3 p-4 rounded-2xl border border-indigo-100 bg-indigo-50/30 cursor-pointer">
                                    <input type="checkbox" v-model="form.requires_verification" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                    <div>
                                        <span class="block text-sm font-bold text-indigo-900">Enable Verification Protocol</span>
                                        <span class="block text-xs text-indigo-600">Tickets require sign-off before advancing</span>
                                    </div>
                                </label>

                                <div v-if="form.requires_verification" class="space-y-6 pt-4 animate-in fade-in slide-in-from-top-4 duration-300">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Approver Selection</label>
                                        <select v-model="form.approver_type" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                            <option value="manager">Reporting Manager</option>
                                            <option value="specific_user">Specific User</option>
                                            <option value="role">User with Role</option>
                                            <option value="department_head">Department Head</option>
                                        </select>
                                    </div>

                                    <div v-if="form.approver_type === 'specific_user'">
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Choose User</label>
                                        <select v-model="form.user_id" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm">
                                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                        </select>
                                    </div>

                                    <div v-if="form.approver_type === 'role'">
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Choose Role</label>
                                        <select v-model="form.role_id" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm">
                                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab 4: Flow Control -->
                            <div v-show="activeTab === 'flow'" class="space-y-6">
                                <h3 class="text-sm font-bold text-gray-900 border-l-4 border-indigo-500 pl-3">Transition Rules</h3>
                                <p class="text-xs text-gray-500">Define which stages a ticket can move to from here. Leave empty to allow any stage.</p>
                                
                                <div class="space-y-2 max-h-60 overflow-y-auto">
                                    <div v-for="s in workflow?.stages?.filter(st => st.id !== currentStageId)" :key="s.id" class="flex items-center gap-3 p-3 rounded-xl border border-gray-50 hover:bg-gray-50 transition-colors">
                                        <input type="checkbox" :value="s.id" v-model="form.transition_rules" class="rounded text-indigo-600" />
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: s.color || '#eee' }"></div>
                                            <span class="text-sm font-medium text-gray-700">{{ s.name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="p-6 border-t border-gray-100 flex justify-end gap-3 bg-gray-50/30">
                        <button type="button" @click="showModal = false" class="px-6 py-2.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-500 hover:bg-white hover:shadow-sm transition-all">Discard Changes</button>
                        <button type="submit" form="stageForm" :disabled="form.processing" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all disabled:opacity-60">
                            {{ form.processing ? 'Saving...' : (isEditing ? 'Sync Configuration' : 'Establish Stage') }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </MainLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
    workflow: Object,
    teams: Array,
    roles: Array,
    users: Array
});

const showModal = ref(false);
const isEditing = ref(false);
const currentStageId = ref(null);
const activeTab = ref('general');

const tabs = [
    { id: 'general', name: 'General' },
    { id: 'logic', name: 'Logic & Assignments' },
    { id: 'approval', name: 'Approval Flow' },
    { id: 'flow', name: 'Flow Control' }
];

const colors = [
    '#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', 
    '#f97316', '#34495e', '#2ecc71', '#9b59b6', '#3498db', '#e74c3c'
];

const form = useForm({
    name: '',
    color: '#6366f1',
    is_final: false,
    is_client_visible: false,
    requires_verification: false,
    workflow_id: props.workflow?.id,
    mentor_id: null,
    assigned_team_id: null,
    auto_close_days: 0,
    approver_type: 'manager',
    user_id: null,
    role_id: null,
    transition_rules: []
});

const openAddModal = () => {
    isEditing.value = false;
    currentStageId.value = null;
    activeTab.value = 'general';
    form.reset({
        name: '',
        color: '#6366f1',
        is_final: false,
        is_client_visible: false,
        requires_verification: false,
        workflow_id: props.workflow?.id,
        mentor_id: null,
        assigned_team_id: null,
        auto_close_days: 0,
        approver_type: 'manager',
        user_id: null,
        role_id: null,
        transition_rules: []
    });
    showModal.value = true;
};

const openEditModal = (stage) => {
    isEditing.value = true;
    currentStageId.value = stage.id;
    activeTab.value = 'general';
    
    form.name = stage.name;
    form.color = stage.color || '#6366f1';
    form.is_final = !!stage.is_final;
    form.is_client_visible = !!stage.is_client_visible;
    form.requires_verification = !!stage.requires_verification;
    form.workflow_id = stage.workflow_id;
    form.mentor_id = stage.mentor_id;
    form.assigned_team_id = stage.assigned_team_id;
    form.auto_close_days = stage.auto_close_days || 0;
    form.approver_type = stage.approver_type || 'manager';
    form.user_id = stage.user_id;
    form.role_id = stage.role_id;
    form.transition_rules = Array.isArray(stage.transition_rules) ? stage.transition_rules : [];

    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('workflow-architect.stages.update', currentStageId.value), {
            onSuccess: () => { showModal.value = false; }
        });
    } else {
        form.post(route('workflow-architect.stages.store'), {
            onSuccess: () => { showModal.value = false; }
        });
    }
};

const deleteStage = (stage) => {
    if (confirm(`Are you sure you want to delete the stage "${stage.name}"? This action cannot be undone.`)) {
        router.delete(route('workflow-architect.stages.destroy', stage.id));
    }
};

const getUserName = (id) => {
    return props.users?.find(u => u.id === id)?.name || 'Unknown';
};
</script>

<style scoped>
.animate-in {
    animation: animate-in 0.3s ease-out;
}
@keyframes animate-in {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
