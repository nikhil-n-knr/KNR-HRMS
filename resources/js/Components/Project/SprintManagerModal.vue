<template>
    <Modal :show="show" @close="$emit('close')" max-width="md">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">
                    {{ isEditing ? 'Edit Sprint' : (tab === 'create' ? 'Create New Sprint' : 'Manage Sprints') }}
                </h3>
                
                <!-- Tabs (Only show if not editing specific sprint passed from outside) -->
                <div v-if="!forcedEditMode" class="flex bg-gray-100 p-1 rounded-lg">
                    <button 
                        @click="tab = 'create'" 
                        class="px-3 py-1 text-xs font-bold rounded-md transition-all"
                        :class="tab === 'create' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    >
                        Create
                    </button>
                    <button 
                        @click="tab = 'manage'" 
                        class="px-3 py-1 text-xs font-bold rounded-md transition-all"
                        :class="tab === 'manage' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    >
                        Manage
                    </button>
                </div>
            </div>

            <!-- Create / Edit Form -->
            <form v-if="tab === 'create' || isEditing" @submit.prevent="submitForm">
                <div class="space-y-4">
                    <BaseInput v-model="form.name" label="Sprint Name" required autofocus placeholder="Sprint 1" />
                    
                    <div class="grid grid-cols-2 gap-4">
                        <BaseInput v-model="form.start_date" type="date" label="Start Date" required />
                        <BaseInput v-model="form.end_date" type="date" label="End Date" required />
                    </div>
                    
                    <BaseInput v-model="form.goal" label="Sprint Goal" placeholder="What are we achieving?" />
                    
                    <div v-if="isEditing">
                        <label class="block font-medium text-sm text-gray-700 mb-1">Status</label>
                        <select v-model="form.status" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <option value="planned">Planned</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button 
                        v-if="isEditing && !forcedEditMode" 
                        type="button" 
                        @click="cancelEdit" 
                        class="mr-auto text-sm text-gray-500 hover:text-gray-700 underline"
                    >
                        Back to List
                    </button>
                    <SecondaryButton @click="$emit('close')">Cancel</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">
                        {{ isEditing ? 'Save Changes' : 'Create Sprint' }}
                    </PrimaryButton>
                </div>
            </form>

            <!-- Manage List -->
            <div v-else class="space-y-4">
                <div v-if="sprints.length === 0" class="text-center py-8 text-gray-400 text-sm">
                    No sprints found. Create one!
                </div>
                
                <div v-else class="space-y-2 max-h-[400px] overflow-y-auto custom-scrollbar pr-1">
                    <div 
                        v-for="sprint in sprints" 
                        :key="sprint.id" 
                        class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-lg shadow-sm group hover:border-indigo-200 transition-all"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-700 text-sm">{{ sprint.name }}</span>
                                <span 
                                    class="text-[10px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wide"
                                    :class="{
                                        'bg-emerald-100 text-emerald-600': sprint.status === 'active',
                                        'bg-gray-100 text-gray-500': sprint.status === 'planned',
                                        'bg-blue-100 text-blue-600': sprint.status === 'completed'
                                    }"
                                >{{ sprint.status }}</span>
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">
                                {{ sprint.start_date }} - {{ sprint.end_date }}
                            </div>
                        </div>

                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="editSprint(sprint)" class="text-gray-300 hover:text-indigo-600 p-2" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" /></svg>
                            </button>
                            <button @click="deleteSprint(sprint)" class="text-gray-300 hover:text-red-500 p-2" title="Delete">
                                 <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: Boolean,
    project: Object,
    sprints: { type: Array, default: () => [] },
    sprintToEdit: { type: Object, default: null } // Optional: Pass a sprint to edit immediately
});

const emit = defineEmits(['close']);

const tab = ref('create');
const isEditing = ref(false);
const forcedEditMode = ref(false); // If opened via "Edit Sprint" directly

const form = useForm({
    id: null,
    name: '',
    goal: '',
    start_date: new Date().toISOString().split('T')[0],
    end_date: new Date(Date.now() + 14 * 86400000).toISOString().split('T')[0],
    status: 'planned'
});

// Watchters
watch(() => props.show, (val) => {
    if (val) {
        if (props.sprintToEdit) {
            editSprint(props.sprintToEdit);
            forcedEditMode.value = true;
        } else {
            resetForm();
            tab.value = 'create';
            forcedEditMode.value = false;
        }
    }
});

// Methods
const resetForm = () => {
    isEditing.value = false;
    form.reset();
    form.id = null;
    form.name = 'Sprint ' + ((props.sprints.length || 0) + 1);
    form.status = 'planned';
    // Reset dates default
    form.start_date = new Date().toISOString().split('T')[0];
    form.end_date = new Date(Date.now() + 14 * 86400000).toISOString().split('T')[0];
};

const editSprint = (sprint) => {
    isEditing.value = true;
    form.id = sprint.id;
    form.name = sprint.name;
    form.goal = sprint.goal;
    form.start_date = sprint.start_date;
    form.end_date = sprint.end_date;
    form.status = sprint.status;
};

const cancelEdit = () => {
    isEditing.value = false;
    form.reset();
    tab.value = 'manage'; // Go back to list
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('projects.sprints.update', { project: props.project.id, sprint: form.id }), {
            onSuccess: () => {
                if(forcedEditMode.value) emit('close');
                else cancelEdit(); // Reload list basically (Inertia handles it)
            }
        });
    } else {
        form.post(route('projects.sprints.store', props.project.id), {
            onSuccess: () => {
                emit('close');
                resetForm();
            }
        });
    }
};

const deleteSprint = (sprint) => {
    if (confirm('Are you sure? This will unassign all tasks in this sprint.')) {
        router.delete(route('projects.sprints.destroy', { project: props.project.id, sprint: sprint.id }));
    }
};
</script>
