<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    goals: Array,
    cycle: Object
});

const showModal = ref(false);
const editingGoal = ref(null);

const form = useForm({
    title: '',
    description: '',
    weightage: 20,
    appraisal_cycle_id: props.cycle?.id
});

const submit = () => {
    if (editingGoal.value) {
        form.put(route('performance.goals.update', editingGoal.value.id), {
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('performance.goals.store'), {
            onSuccess: () => closeModal()
        });
    }
};

const openCreate = () => {
    editingGoal.value = null;
    form.reset();
    form.appraisal_cycle_id = props.cycle?.id;
    showModal.value = true;
};

const openEdit = (goal) => {
    if (goal.status !== 'Draft' && goal.status !== 'Rejected') return;
    editingGoal.value = goal;
    form.title = goal.title;
    form.description = goal.description;
    form.weightage = goal.weightage;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};
</script>

<template>
    <Head title="My Goals" />

    <MainLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Goals ({{ cycle?.name || 'No Active Cycle' }})</h2>
                <PrimaryButton v-if="cycle" @click="openCreate">
                    New Goal
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Goals Grid -->
                <div v-if="goals.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-for="goal in goals" :key="goal.id" class="bg-white p-6 rounded-lg shadow border border-gray-100 relative">
                        <!-- Status Badge -->
                        <span class="absolute top-4 right-4 px-2 py-1 text-xs rounded-full"
                            :class="{
                                'bg-yellow-100 text-yellow-800': goal.status === 'Draft',
                                'bg-blue-100 text-blue-800': goal.status === 'Pending Approval',
                                'bg-green-100 text-green-800': goal.status === 'Approved',
                                'bg-red-100 text-red-800': goal.status === 'Rejected'
                            }">
                            {{ goal.status }}
                        </span>

                        <h3 class="text-lg font-bold text-gray-800">{{ goal.title }}</h3>
                        <p class="text-sm text-gray-500 mt-1">Weightage: {{ goal.weightage }}%</p>
                        <p class="mt-4 text-gray-600">{{ goal.description }}</p>

                        <!-- Progress Bar -->
                        <div class="mt-4">
                            <div class="flex justify-between text-xs mb-1">
                                <span>Progress</span>
                                <span>{{ goal.progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full" :style="{ width: goal.progress + '%' }"></div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-6 flex gap-2" v-if="goal.status === 'Draft' || goal.status === 'Rejected'">
                            <button @click="openEdit(goal)" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">Edit</button>
                        </div>

                        <!-- Manager Remarks -->
                        <div v-if="goal.manager_remarks" class="mt-4 bg-gray-50 p-3 rounded text-sm text-gray-600">
                            <strong>Manager:</strong> {{ goal.manager_remarks }}
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white p-12 text-center rounded-lg shadow">
                    <p class="text-gray-500">No goals set for this cycle yet.</p>
                    <button v-if="cycle" @click="openCreate" class="mt-4 text-indigo-600 font-medium">Create your first goal</button>
                </div>

            </div>
        </div>

        <!-- Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ editingGoal ? 'Edit Goal' : 'New Goal' }}
                </h3>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <InputLabel value="Goal Title" />
                        <TextInput v-model="form.title" class="w-full mt-1" required placeholder="e.g. Deliver Project X" />
                    </div>

                    <div>
                        <InputLabel value="Description" />
                        <textarea v-model="form.description" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="3" required></textarea>
                    </div>

                    <div>
                        <InputLabel value="Weightage (%)" />
                        <TextInput type="number" v-model="form.weightage" class="w-full mt-1" required min="1" max="100" />
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="form.processing">Save Draft</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </MainLayout>
</template>
