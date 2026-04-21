<template>
    <Head title="Manual Project Progress" />

    <MainLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-3">
                    <div class="p-2 bg-emerald-100 rounded-lg">
                        <ChartBarIcon class="h-6 w-6 text-emerald-600" />
                    </div>
                    Manual Project Progress Tracking
                </h2>
            </div>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <BaseDataTable
                    :columns="columns"
                    :data="filteredProjects"
                    search-placeholder="Search projects or clients..."
                >
                    <template #cell-name="{ item }">
                        <div class="flex flex-col">
                            <span class="font-bold text-gray-900">{{ item.name }}</span>
                            <span class="text-xs text-gray-500 font-mono uppercase">{{ item.code }}</span>
                        </div>
                    </template>

                    <template #cell-manual_progress_percentage="{ value }">
                        <div class="flex items-center gap-3 w-48">
                            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div 
                                    class="h-full bg-emerald-500 transition-all duration-1000"
                                    :style="{ width: `${value}%` }"
                                ></div>
                            </div>
                            <span class="text-sm font-bold text-gray-700 min-w-[40px] text-right">{{ value }}%</span>
                        </div>
                    </template>

                    <template #cell-manual_status_label="{ value }">
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-bold border border-blue-100 uppercase tracking-wider">
                            {{ value }}
                        </span>
                    </template>

                    <template #rowActions="{ item }">
                        <button 
                            v-can="'edit_project'"
                            @click="openEditModal(item)"
                            class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all duration-200"
                            title="Update Progress"
                        >
                            <PencilSquareIcon class="h-5 w-5" />
                        </button>
                    </template>
                </BaseDataTable>
            </div>
        </div>

        <!-- Update Progress Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <ChartBarIcon class="h-6 w-6 text-emerald-500" />
                    Manual Progress Override: {{ selectedProject?.name }}
                </h2>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="manual_progress_percentage" value="Completion Percentage (%)" />
                            <TextInput
                                id="manual_progress_percentage"
                                type="number"
                                class="mt-1 block w-full"
                                v-model="form.manual_progress_percentage"
                                required
                                min="0"
                                max="100"
                                autofocus
                            />
                            <div class="mt-2 text-xs text-gray-500">
                                This overrides the automated effort-based calculation for dashboard reporting.
                            </div>
                            <InputError class="mt-2" :message="form.errors.manual_progress_percentage" />
                        </div>

                        <div>
                            <InputLabel for="manual_status_label" value="Current Status Label" />
                            <TextInput
                                id="manual_status_label"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.manual_status_label"
                                required
                                placeholder="e.g. Design Phase, UAT, Blocked"
                            />
                            <div class="mt-2 text-xs text-gray-500">
                                Visible to clients and internal stakeholders in health reports.
                            </div>
                            <InputError class="mt-2" :message="form.errors.manual_status_label" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 text-sm font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all duration-200"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-lg shadow-emerald-500/30 transition-all duration-200 transform hover:-translate-y-0.5 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : 'Update Progress' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { ChartBarIcon, PencilSquareIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    projects: Array
});

const columns = {
    name: { label: 'Project' },
    client: { label: 'Client' },
    manual_progress_percentage: { label: 'Progress (%)' },
    manual_status_label: { label: 'Working Status' },
};

const showModal = ref(false);
const selectedProject = ref(null);

const form = useForm({
    id: null,
    manual_progress_percentage: 0,
    manual_status_label: '',
});

const filteredProjects = computed(() => props.projects);

const openEditModal = (project) => {
    selectedProject.value = project;
    form.id = project.id;
    form.manual_progress_percentage = project.manual_progress_percentage;
    form.manual_status_label = project.manual_status_label;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (form.id) {
        form.defaults({ id: form.id });
        form.put(route('admin.projects.progress.update', { project: form.id }), {
            onSuccess: () => closeModal(),
            preserveScroll: true
        });
    }
};
</script>
