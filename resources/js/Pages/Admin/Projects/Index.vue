<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import BaseInput from '@/Components/BaseInput.vue';
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';
import { PlusIcon, PencilSquareIcon, TrashIcon, CalendarIcon, ViewColumnsIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: MainLayout });

const props = defineProps({
    projects: Object, // Paginated
    managers: Array
});

const showModal = ref(false);
const editingProject = ref(null);
const confirmState = ref({ show: false, title: '', message: '', onConfirm: null });

const form = useForm({
    name: '',
    code: '',
    description: '',
    start_date: '',
    end_date: '',
    status: 'Active',
    manager_id: ''
});

const openCreate = () => {
    editingProject.value = null;
    form.reset();
    showModal.value = true;
};

const openEdit = (project) => {
    editingProject.value = project;
    form.name = project.name;
    form.code = project.code;
    form.description = project.description;
    form.start_date = project.start_date;
    form.end_date = project.end_date;
    form.status = project.status;
    form.manager_id = project.manager_id;
    showModal.value = true;
};

const submit = () => {
    if (editingProject.value) {
        form.put(route('admin.projects.update', editingProject.value.id), {
            onSuccess: () => showModal.value = false
        });
    } else {
        form.post(route('admin.projects.store'), {
            onSuccess: () => showModal.value = false
        });
    }
};

const deleteProject = (id) => {
    confirmState.value = {
        show: true,
        title: 'Delete Project?',
        message: 'Are you sure? This will delete all associated tasks and cannot be undone.',
        onConfirm: () => {
            router.delete(route('admin.projects.destroy', id), {
                onSuccess: () => confirmState.value.show = false
            });
        }
    };
};

const statusColors = {
    'Active': 'bg-green-100 text-green-800',
    'On Hold': 'bg-yellow-100 text-yellow-800',
    'Completed': 'bg-blue-100 text-blue-800',
    'Archived': 'bg-gray-100 text-gray-800'
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-emerald-100/50">
            <div>
                <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-800 to-teal-700">Projects</h1>
                <p class="text-slate-500 mt-1">Manage client projects and development sprints.</p>
            </div>
            <PrimaryButton @click="openCreate" class="flex items-center gap-2">
                <PlusIcon class="w-5 h-5" /> New Project
            </PrimaryButton>
        </div>

        <!-- Project Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="project in projects.data" :key="project.id" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow group">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                             <h3 class="font-bold text-gray-900 text-lg">{{ project.name }}</h3>
                             <span v-if="project.code" class="text-xs font-mono bg-gray-100 px-2 py-0.5 rounded text-gray-500">{{ project.code }}</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ project.description || 'No description provided.' }}</p>
                    </div>
                    <span :class="['text-xs px-2 py-1 rounded-full font-medium', statusColors[project.status]]">
                        {{ project.status }}
                    </span>
                </div>

                <div class="space-y-2 text-sm text-gray-600 mb-4">
                    <div class="flex items-center gap-2">
                        <CalendarIcon class="w-4 h-4 text-gray-400" />
                        <span>{{ project.start_date || 'TBD' }} &rarr; {{ project.end_date || 'Ongoing' }}</span>
                    </div>
                    <div v-if="project.manager" class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">
                            {{ project.manager.name.charAt(0) }}
                        </div>
                        <span>Manager: {{ project.manager.name }}</span>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-gray-50 mt-4">
                     <Link :href="route('admin.projects.board', project.id)" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                        <ViewColumnsIcon class="w-4 h-4" />
                        Scrum Board
                    </Link>

                    <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="openEdit(project)" class="p-2 hover:bg-gray-100 rounded-lg text-gray-600 transition-colors">
                            <PencilSquareIcon class="w-4 h-4" />
                        </button>
                        <button @click="deleteProject(project.id)" class="p-2 hover:bg-red-50 rounded-lg text-red-500 transition-colors">
                            <TrashIcon class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Empty State -->
            <div v-if="projects.data.length === 0" class="col-span-full text-center py-12 text-gray-400 bg-gray-50 rounded-xl border-dashed border-2 border-gray-200">
                No active projects found. Create one to get started.
            </div>
        </div>

        <!-- Modal -->
        <Modal :show="showModal" @close="showModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ editingProject ? 'Edit Project' : 'Create Project' }}</h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <BaseInput label="Project Name" v-model="form.name" required placeholder="e.g. Website Redesign" />
                    
                    <div class="grid grid-cols-2 gap-4">
                        <BaseInput label="Project Code" v-model="form.code" placeholder="e.g. WEB-01" />
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select v-model="form.status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option>Active</option>
                                <option>On Hold</option>
                                <option>Completed</option>
                                <option>Archived</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea v-model="form.description" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <BaseInput type="date" label="Start Date" v-model="form.start_date" />
                        <BaseInput type="date" label="End Date" v-model="form.end_date" />
                    </div>

                    <div>
                         <label class="block text-sm font-medium text-gray-700 mb-1">Project Manager</label>
                         <select v-model="form.manager_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                             <option value="">None</option>
                             <option v-for="mgr in managers" :key="mgr.id" :value="mgr.id">{{ mgr.name }}</option>
                         </select>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="form.processing">{{ editingProject ? 'Update' : 'Create' }}</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
        <ConfirmationModal 
            :show="confirmState.show" 
            :title="confirmState.title" 
            :message="confirmState.message" 
            @close="confirmState.show = false" 
            @confirm="confirmState.onConfirm"
        />
    </div>
</template>
