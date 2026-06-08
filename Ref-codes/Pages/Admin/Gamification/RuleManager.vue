<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import InertiaLayout from '@/Layouts/InertiaLayout.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue'; // Corrected Import
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseTextarea from '@/Components/BaseTextarea.vue'; // Assuming existence or use native
import { TrophyIcon, StarIcon, PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    rules: Object
});

const showModal = ref(false);
const isEditing = ref(false);
const editId = ref(null);

const form = useForm({
    name: '',
    event_key: '',
    points: 0,
    description: '',
    is_active: true
});

const columns = {
    name: { label: 'Rule Name', class: 'text-left font-semibold' },
    event_key: { label: 'Event Key', class: 'text-left font-mono text-xs text-gray-500' },
    points: { label: 'Points', class: 'text-center' },
    description: { label: 'Description', class: 'text-left w-1/3' },
    is_active: { label: 'Status', class: 'text-center' },
};

const openCreateModal = () => {
    isEditing.value = false;
    editId.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    editId.value = item.id;
    form.name = item.name;
    form.event_key = item.event_key;
    form.points = item.points;
    form.description = item.description;
    form.is_active = !!item.is_active;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.gamification.update', editId.value), {
            onSuccess: () => closeModal(),
            preserveScroll: true
        });
    } else {
        form.post(route('admin.gamification.store'), {
            onSuccess: () => closeModal(),
            preserveScroll: true
        });
    }
};

const deleteRule = (item) => {
    if(!confirm('Delete this rule?')) return;
    router.delete(route('admin.gamification.destroy', item.id)); // Using router directly
};

</script>

<template>
    <Head title="Gamification Rules" />

    <InertiaLayout>
        <div class="space-y-6 animate-fade-in-up">
            <!-- Header -->
            <div class="flex justify-between items-center bg-white/60 backdrop-blur-md p-6 rounded-2xl shadow-sm border border-white/50">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl text-white shadow-lg shadow-orange-500/30">
                        <TrophyIcon class="w-8 h-8" />
                    </div>
                    <div>
                         <h2 class="text-2xl font-bold text-gray-800">
                            Gamification Rules
                        </h2>
                        <p class="text-sm text-gray-500">Configure how employees earn points and badges.</p>
                    </div>
                </div>
                <div>
                     <PrimaryButton @click="openCreateModal" class="flex items-center gap-2">
                        <PlusIcon class="w-5 h-5" />
                        Add Rule
                    </PrimaryButton>
                </div>
            </div>

            <!-- Rules Table -->
             <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/50 overflow-hidden">
                <BaseDataTable 
                    :columns="columns" 
                    :data="rules.data" 
                    :pagination="rules"
                >
                    <template #cell-points="{ item }">
                        <span :class="['font-bold px-3 py-1 rounded-full text-sm', item.points >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">
                            {{ item.points > 0 ? '+' : '' }}{{ item.points }} pts
                        </span>
                    </template>
                    
                    <template #cell-is_active="{ item }">
                         <span :class="['w-2 h-2 rounded-full inline-block mr-2', item.is_active ? 'bg-green-500' : 'bg-gray-300']"></span>
                         {{ item.is_active ? 'Active' : 'Inactive' }}
                    </template>

                    <template #rowActions="{ item }">
                         <div class="flex space-x-2">
                            <button @click="openEditModal(item)" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-1.5 rounded-lg transition-colors">
                                <PencilIcon class="w-4 h-4" />
                            </button>
                            <button @click="deleteRule(item)" class="text-red-500 hover:text-red-800 bg-red-50 p-1.5 rounded-lg transition-colors">
                                <TrashIcon class="w-4 h-4" />
                            </button>
                         </div>
                    </template>
                </BaseDataTable>
            </div>

            <!-- Modal -->
             <Modal :show="showModal" @close="closeModal">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <StarIcon class="w-5 h-5 text-yellow-500" />
                        {{ isEditing ? 'Edit Rule' : 'Create Point Rule' }}
                    </h3>

                    <form @submit.prevent="submit" class="space-y-4">
                        <BaseInput 
                            v-model="form.name" 
                            label="Rule Name" 
                            placeholder="e.g. Perfect Attendance Bonus" 
                            required 
                        />

                        <BaseInput 
                            v-model="form.event_key" 
                            label="Event Key (Dev Reference)" 
                            placeholder="e.g. attendance_streak_7" 
                            :disabled="isEditing" 
                            required
                            class="font-mono"
                        />
                        
                         <BaseInput 
                            type="number"
                            v-model="form.points" 
                            label="Points (Positive or Negative)" 
                            required 
                        />
                        
                        <div>
                             <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                             <textarea 
                                v-model="form.description"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows="3"
                             ></textarea>
                        </div>

                         <div class="flex items-center mt-4">
                            <input 
                                type="checkbox" 
                                id="is_active" 
                                v-model="form.is_active" 
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            >
                            <label for="is_active" class="ml-2 text-sm text-gray-600">Rule is Active</label>
                        </div>

                        <div class="flex justify-end gap-3 mt-8">
                            <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                            <PrimaryButton :disabled="form.processing">
                                {{ isEditing ? 'Update Rule' : 'Create Rule' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>
        </div>
    </InertiaLayout>
</template>
