<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-6">
            <div>
                <h1 class="text-2xl md:text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-indigo-800 to-purple-700">
                    Project Command Center
                </h1>
                <p class="text-indigo-600/60 text-sm font-medium mt-1">Manage, Track, and Deliver with Precision</p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 items-center w-full md:w-auto">
                <!-- Search -->
                <div class="relative group w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-indigo-400 group-focus-within:text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input 
                        v-model="searchQuery" 
                        @input="handleSearch"
                        type="text" 
                        placeholder="Search Projects..." 
                        class="pl-9 pr-4 py-2.5 rounded-xl border-none bg-white focus:bg-white shadow-sm ring-1 ring-indigo-100 focus:ring-2 focus:ring-indigo-500 text-sm w-full transition-all"
                    >
                </div>

                <!-- Tabs -->
                <div class="bg-gray-100/50 p-1 rounded-xl flex text-xs font-bold w-full sm:w-auto overflow-hidden">
                    <button 
                        @click="toggleView('active')"
                        :class="{'bg-white text-indigo-600 shadow-sm': currentView === 'active' || !currentView, 'text-gray-500 hover:text-gray-700': currentView !== 'active' && currentView}"
                        class="flex-1 sm:flex-none px-4 py-2 rounded-lg transition-all"
                    >
                        Active
                    </button>
                    <button 
                        @click="toggleView('archived')"
                        :class="{'bg-white text-indigo-600 shadow-sm': currentView === 'archived', 'text-gray-500 hover:text-gray-700': currentView !== 'archived'}"
                        class="flex-1 sm:flex-none px-4 py-2 rounded-lg transition-all"
                    >
                        Archived
                    </button>
                     <button 
                        @click="toggleView('all')"
                        :class="{'bg-white text-indigo-600 shadow-sm': currentView === 'all', 'text-gray-500 hover:text-gray-700': currentView !== 'all'}"
                        class="flex-1 sm:flex-none px-4 py-2 rounded-lg transition-all"
                    >
                        All
                    </button>
                </div>

                <Link 
                    v-can="'create-project'"
                    :href="route('projects.create')"
                    class="w-full sm:w-auto px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                    </svg>
                    Project
                </Link>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4" v-if="currentView !== 'archived'">
            <!-- Stats Content Same as Before -->
        </div>

        <!-- Project Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="project in (projects.data || projects)" :key="project?.id" 
                class="group relative bg-white/80 backdrop-blur-xl border border-white/60 rounded-2xl p-6 shadow-sm hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-300 hover:-translate-y-1 block"
            >
                <!-- Status & Actions -->
                <div class="absolute top-4 right-4 flex items-center gap-2 z-20">
                     <span v-if="project.status !== 'archived'" :class="[
                        'px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border',
                        project.status === 'active' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 
                        project.status === 'planning' ? 'bg-blue-50 text-blue-600 border-blue-100' :
                        'bg-gray-50 text-gray-600 border-gray-100'
                    ]">
                        {{ project.status }}
                    </span>
                    <span v-else class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border bg-amber-50 text-amber-600 border-amber-100">
                        Archived
                    </span>

                    <!-- Action Menu -->
                    <div class="relative group/menu">
                        <button class="p-1 rounded hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                            </svg>
                        </button>
                        <!-- Dropdown -->
                        <div class="absolute right-0 mt-1 w-36 bg-white rounded-lg shadow-lg border border-gray-100 py-1 hidden group-hover/menu:block">
                            
                            <!-- Edit inline -->
                            <button 
                                @click.prevent="openEditModal(project)"
                                class="w-full text-left px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 hover:text-indigo-600"
                            >
                                Edit Project
                            </button>
                            
                            <!-- Archive/Unarchive -->
                            <button 
                                v-if="project.status === 'archived'"
                                @click.prevent="unarchiveProject(project)"
                                class="w-full text-left px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 hover:text-emerald-600"
                            >
                                Restore
                            </button>
                            <button 
                                v-else
                                @click.prevent="archiveProject(project)"
                                class="w-full text-left px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 hover:text-amber-600"
                            >
                                Archive
                            </button>
                            
                            <!-- Delete -->
                             <button 
                                @click.prevent="deleteProject(project)"
                                class="w-full text-left px-4 py-2 text-xs font-medium text-red-600 hover:bg-red-50"
                             >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ project.code }}</span>
                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-indigo-600 transition-colors mt-1">{{ project?.name }}</h3>
                    <p class="text-sm text-gray-500 mt-2 line-clamp-2 h-10">{{ project.description || 'No description provided.' }}</p>
                </div>
                
                <!-- Client Info -->
                <div class="flex items-center gap-3 mb-6">
                     <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 border-2 border-white shadow-sm flex items-center justify-center text-xs font-bold text-indigo-700">
                        {{ project.client?.name?.charAt(0) || 'I' }}
                    </div>
                    <div>
                         <span class="text-sm uppercase text-gray-400 font-bold tracking-wider block">Client</span>
                         <span class="text-sm text-gray-600 font-medium">{{ project.client?.name || 'Internal' }}</span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-100/50">
                    <div class="flex items-center gap-1 text-xs font-medium text-gray-500">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        {{ project.tasks_count || 0 }} Tasks
                    </div>
                    
                    <Link :href="route('projects.show', { project: project.id })" 
                         class="px-4 py-2 rounded-xl bg-indigo-50 text-indigo-700 text-sm font-bold hover:bg-indigo-600 hover:text-white transition-all shadow-sm hover:shadow-indigo-500/25 flex items-center gap-2 group/btn"
                    >
                        {{ project.status === 'archived' ? 'View' : 'Open' }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover/btn:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </Link>
                </div>
            </div>
            
            <!-- Empty State -->
            <div v-if="(projects.data || projects).length === 0" class="col-span-full py-12 flex flex-col items-center justify-center text-gray-500">
               <!-- ... existing empty state ... -->
               <div class="bg-gray-50 rounded-full p-6 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 01-2-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
               </div>
               <h3 class="text-lg font-medium text-gray-900">
                   {{ currentView === 'archived' ? 'No archived projects' : 'No projects found' }}
               </h3>
               <p class="mt-1 text-sm text-gray-500">
                   {{ currentView === 'archived' ? 'Archive projects to clear your view.' : 'Get started by creating a new project.' }}
               </p>
               <div class="mt-6" v-if="currentView !== 'archived'">
                    <Link :href="route('projects.create')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Project
                    </Link>
               </div>
            </div>
        </div>

        <!-- Edit Project Modal inline on Dashboard -->
        <Modal :show="showEditModal" @close="closeEditModal" title="Edit Project Details">
             <form @submit.prevent="submitEditForm" id="editProjectFormDashboard">
                 <div class="space-y-4">
                     <BaseInput
                        v-model="editForm.name"
                        label="Project Name"
                        color="indigo"
                        :error="editForm.errors.name"
                     />
                     <BaseInput
                        v-model="editForm.code"
                        label="Project Code"
                        color="indigo"
                        class="uppercase"
                        :error="editForm.errors.code"
                     />
                     <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Project Status</label>
                        <select v-model="editForm.status" class="w-full rounded-xl shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 px-4 bg-white/50 backdrop-blur-sm">
                            <option value="planning">Planning (Draft)</option>
                            <option value="active">Active (In Progress)</option>
                            <option value="on_hold">On Hold</option>
                            <option value="completed">Completed</option>
                        </select>
                        <p v-if="editForm.errors.status" class="text-xs text-red-600 animate-pulse mt-1">{{ editForm.errors.status }}</p>
                     </div>
                     <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Project Description</label>
                        <textarea
                            v-model="editForm.description"
                            class="w-full rounded-xl shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 px-4 bg-white/50 backdrop-blur-sm"
                            rows="4"
                            placeholder="Brief description of the project goals..."
                        ></textarea>
                        <p v-if="editForm.errors.description" class="text-xs text-red-600 animate-pulse mt-1">{{ editForm.errors.description }}</p>
                     </div>
                 </div>
             </form>
             <template #footer>
                 <div class="flex justify-end gap-3 w-full">
                     <SecondaryButton @click="closeEditModal">Cancel</SecondaryButton>
                     <PrimaryButton type="submit" form="editProjectFormDashboard" :disabled="editForm.processing">Save Changes</PrimaryButton>
                 </div>
             </template>
        </Modal>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage, Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    projects: [Array, Object], // Can be array or Paginated Object
    stats: Object,
    filters: Object // search, view
});

// View Toggle Logic
const currentView = computed(() => {
    const params = new URLSearchParams(window.location.search);
    return params.get('view') || 'active';
});

const toggleView = (mode) => {
    if (currentView.value === mode) return;
    
    // Route moved to root level: 'projects.index'
    router.visit(route('projects.index'), { 
        data: { 
            view: mode,
            search: props.filters?.search 
        },
        preserveState: true,
        replace: true
    });
};

// Search Logic
const searchQuery = ref(props.filters?.search || '');
let searchTimeout = null;

const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('projects.index'), { 
            search: searchQuery.value, 
            view: currentView.value 
        }, { 
            preserveState: true, 
            replace: true 
        });
    }, 300);
};

// Actions
const archiveProject = (project) => {
    if (confirm(`Are you sure you want to archive "${project.name}"?`)) {
        router.post(route('projects.archive', project.id));
    }
};

const unarchiveProject = (project) => {
    router.post(route('projects.unarchive', project.id));
};

const deleteProject = (project) => {
    if (confirm(`PERMANENT DELETE WARNING: Are you sure you want to delete "${project.name}"? \n\nThis will move the project to trash.`)) {
        router.delete(route('projects.destroy', project.id));
    }
};

const showCreateModal = ref(false);

const showEditModal = ref(false);

const editForm = useForm({
    id: '',
    name: '',
    code: '',
    description: '',
    status: '',
    client_id: ''
});

const openEditModal = (project) => {
    editForm.id = project.id;
    editForm.name = project.name || '';
    editForm.code = project.code || '';
    editForm.description = project.description || '';
    editForm.status = project.status || 'planning';
    editForm.client_id = project.client_id;
    editForm.clearErrors();
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
};

const submitEditForm = () => {
    if (editForm.id) {
        editForm.defaults({ id: editForm.id });
        editForm.put(route('projects.update', { project: editForm.id }), {
            preserveScroll: true,
            onSuccess: () => closeEditModal()
        });
    }
};
</script>
