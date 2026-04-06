<template>
    <Modal :show="show" @close="$emit('close')" max-width="lg">
        <div class="p-6">
             <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                Manage Board Stages
            </h3>

            <!-- Stage Form -->
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 mb-6 relative" :class="{'ring-2 ring-indigo-100': form.id}">
                 <div class="flex justify-between items-center mb-3">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">
                        {{ form.id ? 'Edit Stage' : 'Add New Stage' }}
                    </h4>
                    <button v-if="form.id" @click="cancelEdit" class="text-xs text-gray-500 hover:text-gray-700 underline">
                        Cancel
                    </button>
                 </div>
                 <form @submit.prevent="submit" class="space-y-3">
                    <div class="flex gap-3 items-end">
                        <div class="flex-1">
                             <BaseInput v-model="form.name" placeholder="Stage Name" class="mb-0" />
                        </div>
                         <div class="w-32">
                             <BaseSelect v-model="form.type" class="mb-0">
                                 <option value="backlog">Backlog</option>
                                 <option value="todo">To Do</option>
                                 <option value="doing">In Progress</option>
                                 <option value="review">Review / QA</option>
                                 <option value="done">Done</option>
                             </BaseSelect>
                         </div>
                         <div class="w-12">
                             <input type="color" v-model="form.color" class="h-10 w-full rounded-lg border-gray-300 cursor-pointer text-sm" />
                         </div>
                    </div>
                    
                    <!-- Owners / Assignees -->
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1 block">Stage Owners (Alerts)</label>
                        <MultiUserSelect 
                            v-model="form.assignees" 
                            :items="employees" 
                            placeholder="Search by Name or ID..."
                        />
                         <p class="text-[10px] text-gray-400 mt-1">Select users to receive notifications for this stage.</p>
                    </div>

                     <div class="flex justify-end">
                        <PrimaryButton :disabled="form.processing" class="h-8 px-4 text-xs">
                             {{ form.id ? 'Save Changes' : 'Add Stage' }}
                         </PrimaryButton>
                     </div>
                 </form>
            </div>

            <!-- Existing Stages List -->
            <div class="space-y-2 max-h-[300px] overflow-y-auto custom-scrollbar pr-1">
                <div v-for="(stage, idx) in project.stages" :key="stage.id" class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-lg shadow-sm group hover:border-indigo-200 transition-all">
                    <div class="flex items-center gap-3">
                         <span class="text-xs font-mono text-gray-400 w-4">{{ idx + 1 }}.</span>
                         <span class="h-4 w-4 rounded-full border border-gray-200" :style="{ backgroundColor: stage.color }"></span>
                         <div>
                             <div class="font-bold text-gray-800 text-sm">{{ stage.name }}</div>
                             <div class="text-[10px] text-gray-400 uppercase flex gap-2">
                                {{ stage.type }}
                                <span v-if="stage.assignees?.length" class="text-indigo-500 font-semibold" :title="stage.assignees.map(u => u.name).join(', ')">
                                    • {{ stage.assignees.length }} Owners
                                </span>
                             </div>
                         </div>
                    </div>
                    
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <!-- Up/Down Reorder can be added here -->
                         <button @click="edit(stage)" class="text-gray-300 hover:text-indigo-600 p-2" title="Edit">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" /></svg>
                         </button>
                         <button @click="remove(stage)" class="text-gray-300 hover:text-red-500 p-2" title="Delete">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="$emit('close')">Close</SecondaryButton>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import MultiUserSelect from '@/Components/MultiUserSelect.vue';

const props = defineProps({
    show: Boolean,
    project: Object,
    employees: Array // For selecting owners
});

const emit = defineEmits(['close']);

const form = useForm({
    id: null,
    name: '',
    type: 'todo',
    color: '#6366f1',
    order: 0,
    assignees: [] // Array of user_ids
});

const submit = () => {
    if (form.id) {
        form.put(route('projects.stages.update', { project: props.project.id, stage: form.id }), {
            onSuccess: () => cancelEdit()
        });
    } else {
        form.order = props.project.stages.length;
        form.post(route('projects.stages.store', props.project.id), {
            onSuccess: () => {
                form.reset();
                form.assignees = [];
            }
        });
    }
};

const edit = (stage) => {
    form.id = stage.id;
    form.name = stage.name;
    form.type = stage.type;
    form.color = stage.color;
    form.order = stage.order;
    // Map existing assignees if available
    form.assignees = stage.assignees?.map(u => u.id) || [];
};

const cancelEdit = () => {
    form.reset();
    form.id = null;
    form.assignees = [];
};

const remove = (stage) => {
    if (confirm('Delete this stage? Tasks may need reassigning.')) { 
        router.delete(route('projects.stages.destroy', { project: props.project.id, stage: stage.id }));
    }
};
</script>
