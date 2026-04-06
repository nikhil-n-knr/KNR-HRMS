<template>
    <Modal :show="show" @close="$emit('close')" max-width="lg">
        <div class="p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-2 flex items-center gap-2">
                <span class="p-2 bg-indigo-100 text-indigo-600 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </span>
                Complete Sprint: {{ sprint?.name }}
            </h3>

            <!-- Stats -->
             <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex flex-col items-center">
                    <span class="text-2xl font-bold text-gray-800">{{ stats.completedPoints }} / {{ stats.totalPoints }}</span>
                    <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Points Completed</span>
                </div>
                 <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex flex-col items-center">
                    <span class="text-2xl font-bold text-gray-800">{{ stats.incompleteCount }}</span>
                    <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Open Tasks</span>
                </div>
             </div>

             <!-- Early Completion Warning -->
             <div v-if="isEarly" class="mb-6 bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-amber-700 font-bold">Sprint is ending early!</p>
                        <p class="text-xs text-amber-600 mt-1">
                            Scheduled End Date: <span class="font-mono bg-amber-100 px-1 rounded">{{ sprint?.end_date }}</span>
                        </p>
                    </div>
                </div>
             </div>

            <form @submit.prevent="submit">
                <div class="space-y-4 mb-6" v-if="stats.incompleteCount > 0">
                    <p class="text-sm font-medium text-gray-700">What should we do with open tasks?</p>
                    
                    <div class="space-y-2">
                        <label class="flex items-center p-3 border rounded-xl cursor-pointer hover:bg-gray-50 transition-colors" :class="{'border-indigo-500 bg-indigo-50': form.move_action === 'backlog'}">
                            <input type="radio" v-model="form.move_action" value="backlog" class="text-indigo-600 focus:ring-indigo-500 h-4 w-4 border-gray-300">
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900">Move to Backlog</span>
                                <span class="block text-xs text-gray-500">Tasks will be unassigned from any sprint.</span>
                            </div>
                        </label>

                        <label class="flex items-center p-3 border rounded-xl cursor-pointer hover:bg-gray-50 transition-colors" :class="{'border-indigo-500 bg-indigo-50': form.move_action === 'next'}">
                             <input type="radio" v-model="form.move_action" value="next" class="text-indigo-600 focus:ring-indigo-500 h-4 w-4 border-gray-300">
                             <div class="ml-3 w-full">
                                <span class="block text-sm font-medium text-gray-900">Move to New Sprint</span>
                                <select v-if="form.move_action === 'next'" v-model="form.target_sprint_id" class="mt-2 block w-full pl-3 pr-10 py-1.5 text-sm border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" @click.stop>
                                    <option :value="null" disabled>Select Sprint...</option>
                                    <option v-for="s in futureSprints" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="$emit('close')">Cancel</SecondaryButton>
                    <PrimaryButton :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-700">
                        Complete Sprint
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: Boolean,
    sprint: Object,
    stats: Object, // { totalPoints, completedPoints, incompleteCount }
    project: Object
});

const emit = defineEmits(['close']);

const form = useForm({
    move_action: 'backlog', // 'backlog' or 'next'
    target_sprint_id: null,
    move_incomplete_to_backlog: true,
    move_incomplete_to_sprint_id: null
});

// Computed
const futureSprints = computed(() => {
    return props.project?.sprints?.filter(s => s.status === 'planned' && s.id !== props.sprint?.id) || [];
});

const isEarly = computed(() => {
    if (!props.sprint?.end_date) return false;
    return new Date(props.sprint.end_date) > new Date();
});

// Logic
const submit = () => {
    // Transform helper selections to backend fields
    form.move_incomplete_to_backlog = form.move_action === 'backlog';
    form.move_incomplete_to_sprint_id = form.move_action === 'next' ? form.target_sprint_id : null;

    form.post(route('projects.sprints.complete', { project: props.project.id, sprint: props.sprint.id }), {
        onSuccess: () => emit('close')
    });
};

watch(() => props.show, (val) => {
    if (val) form.reset();
});
</script>
