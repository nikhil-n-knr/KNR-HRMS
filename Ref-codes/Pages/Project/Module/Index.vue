<template>
    <ProjectLayout :project="project" title="Modules">
        <div class="flex flex-col h-full bg-slate-50 relative overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-6 md:px-8 bg-white border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 z-10">
                <div>
                     <h2 class="text-2xl md:text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-indigo-700 to-indigo-500 tracking-tight">
                        Project Modules
                    </h2>
                    <p class="text-sm md:text-sm text-gray-400 font-black uppercase tracking-widest mt-1">Functional Architecture & Hierarchy</p>
                </div>
                <div class="flex flex-col sm:flex-row w-full md:w-auto gap-3">
                    <button 
                        v-if="selectedIds.length > 0"
                        @click="confirmBulkDelete"
                        class="px-5 py-2.5 bg-red-50 text-red-600 hover:bg-red-100 border border-red-100 rounded-xl text-sm font-black uppercase tracking-widest shadow-sm transition-all active:scale-95 flex items-center justify-center gap-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Delete ({{ selectedIds.length }})
                    </button>
                    <button 
                        @click="showBulkModal = true"
                        class="px-5 py-2.5 bg-white text-indigo-600 hover:bg-indigo-50 border border-indigo-100 rounded-xl text-sm font-black uppercase tracking-widest shadow-sm transition-all active:scale-95 flex items-center justify-center gap-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                        Bulk Import
                    </button>
                    <button 
                        @click="openCreateModal(null)"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-black uppercase tracking-widest shadow-xl shadow-indigo-600/20 transition-all active:scale-95 flex items-center justify-center gap-2"
                    >
                        <span class="text-lg leading-none">+</span>
                        Record Root
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <div v-if="loading" class="flex justify-center items-center h-64">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
                </div>

                <div v-else-if="modules.length === 0" class="flex flex-col items-center justify-center py-20 bg-white/50 rounded-3xl border border-dashed border-gray-300">
                    <div class="bg-indigo-50 p-6 rounded-full mb-6 relative">
                        <!-- Abstract Blocks Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <div class="absolute -bottom-2 -right-2 bg-white rounded-full p-1.5 shadow-sm border border-gray-100">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No Modules Defined</h3>
                    <p class="text-gray-500 max-w-md text-center mb-8">
                        Modules help organize your project into functional components (e.g., "Authentication", "Billing").
                        Start by creating your first root module.
                    </p>
                     <div class="flex justify-center gap-4">
                        <button 
                            @click="showBulkModal = true"
                            class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold rounded-xl transition-colors shadow-sm"
                        >
                            Import from Template
                        </button>
                        <button 
                            @click="openCreateModal(null)"
                            class="px-5 py-2.5 bg-indigo-600 text-white hover:bg-indigo-700 font-semibold rounded-xl transition-colors shadow-lg shadow-indigo-500/30"
                        >
                            Create First Module
                        </button>
                     </div>
                </div>

                <div v-else class="max-w-4xl mx-auto space-y-4">
                    <!-- Master Select All -->
                    <div class="flex items-center px-6 py-2 bg-white/50 backdrop-blur-sm border border-gray-200 rounded-2xl shadow-sm mb-6">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative flex items-center">
                                <input 
                                    type="checkbox" 
                                    :checked="isAllSelected"
                                    :indeterminate="isSomeSelected"
                                    @change="toggleSelectAll"
                                    class="w-5 h-5 text-indigo-600 border-gray-300 rounded-lg focus:ring-indigo-500 transition-all cursor-pointer"
                                />
                            </div>
                            <span class="text-sm font-black text-gray-700 uppercase tracking-widest group-hover:text-indigo-600 transition-colors">
                                {{ isAllSelected ? 'Deselect All' : 'Select All Architecture' }}
                            </span>
                        </label>
                    </div>

                    <ModuleTreeItem 
                        v-for="module in modules" 
                        :key="module.id" 
                        :module="module"
                        :selected-ids="selectedIds"
                        @toggle-select="handleToggleSelect"
                        @add-submodule="openCreateModal"
                        @edit="openEditModal"
                        @delete="confirmDelete"
                    />
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-6">
                    {{ isEditing ? 'Edit Module' : (parentModule ? `Add Sub-module to "${parentModule.name}"` : 'New Root Module') }}
                </h3>

                <form @submit.prevent="submitForm">
                    <div class="space-y-6">
                        <BaseInput 
                            v-model="form.name" 
                            label="Module Name"
                            :error="form.errors.name"
                            color="indigo"
                            placeholder="e.g. User Management"
                            required 
                            autofocus
                        />

                        <BaseInput
                            v-model="form.description" 
                            label="Description (Optional)"
                            :error="form.errors.description"
                            color="indigo"
                            placeholder="Briefly describe this module's scope..."
                        />
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <button type="button" @click="closeModal" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg font-medium">Cancel</button>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ isEditing ? 'Update Module' : 'Create Module' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation -->
         <Modal :show="showDeleteModal" @close="showDeleteModal = false" maxWidth="sm">
            <div class="p-6 text-center">
                 <div class="bg-red-50 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">
                    {{ isBulkDelete ? `Delete ${selectedIds.length} Modules?` : 'Delete Module?' }}
                </h3>
                <p class="text-gray-500 mt-2 text-sm">
                    <span v-if="isBulkDelete">
                        Are you sure you want to delete the selected modules? This action cannot be undone.
                    </span>
                    <span v-else>
                        Are you sure you want to delete <strong>{{ moduleToDelete?.name }}</strong>? 
                        <br>
                        <span class="text-red-500 font-bold" v-if="moduleToDelete?.children?.length > 0">Warning: All sub-modules will also be deleted.</span>
                    </span>
                </p>
                <div class="mt-6 flex justify-center gap-3">
                    <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 font-medium">Cancel</button>
                    <button @click="isBulkDelete ? bulkDelete() : deleteModule()" class="px-4 py-2 bg-red-600 text-white rounded-lg font-bold hover:bg-red-700">Yes, Delete</button>
                </div>
            </div>
         </Modal>

        <BulkCreateModal 
            :show="showBulkModal" 
            :project="project" 
            @close="showBulkModal = false" 
            @success="handleBulkSuccess"
        />

    </ProjectLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import ProjectLayout from '@/Layouts/ProjectLayout.vue';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import ModuleTreeItem from './ModuleTreeItem.vue';
import BulkCreateModal from './BulkCreateModal.vue';

const props = defineProps({
    project: Object
});

const modules = ref([]);
const loading = ref(true);

// Forms
const showModal = ref(false);
const showDeleteModal = ref(false);
const showBulkModal = ref(false);
const isEditing = ref(false);
const parentModule = ref(null);
const moduleToDelete = ref(null);
const selectedIds = ref([]);
const isBulkDelete = ref(false);

// Selection Helpers
const getAllIds = (items) => {
    let ids = [];
    items.forEach(item => {
        ids.push(item.id);
        if (item.children && item.children.length > 0) {
            ids = [...ids, ...getAllIds(item.children)];
        }
    });
    return ids;
};

const allModuleIds = computed(() => getAllIds(modules.value));
const isAllSelected = computed(() => allModuleIds.value.length > 0 && selectedIds.value.length === allModuleIds.value.length);
const isSomeSelected = computed(() => selectedIds.value.length > 0 && selectedIds.value.length < allModuleIds.value.length);

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedIds.value = [];
    } else {
        selectedIds.value = [...allModuleIds.value];
    }
};

const form = useForm({
    id: null,
    name: '',
    description: '',
    parent_id: null
});

// Data Fetching
const fetchModules = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('projects.modules.tree', props.project.id));
        modules.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

// Actions
const openCreateModal = (parent = null) => {
    isEditing.value = false;
    parentModule.value = parent;
    form.reset();
    form.parent_id = parent ? parent.id : null;
    showModal.value = true;
};

const openEditModal = (module) => {
    isEditing.value = true;
    parentModule.value = null; // Can't move parent in simple edit
    form.id = module.id;
    form.name = module.name;
    form.description = module.description;
    form.parent_id = module.parent_id;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('projects.modules.update', { project: props.project.id, module: form.id }), {
            onSuccess: () => {
                closeModal();
                fetchModules(); // Reload tree
            }
        });
    } else {
        form.post(route('projects.modules.store', props.project.id), {
            onSuccess: () => {
                closeModal();
                fetchModules();
            }
        });
    }
};

const confirmDelete = (module) => {
    isBulkDelete.value = false;
    moduleToDelete.value = module;
    showDeleteModal.value = true;
};

const handleToggleSelect = (id) => {
    const index = selectedIds.value.indexOf(id);
    if (index > -1) {
        selectedIds.value.splice(index, 1);
    } else {
        selectedIds.value.push(id);
    }
};

const confirmBulkDelete = () => {
    isBulkDelete.value = true;
    showDeleteModal.value = true;
};

const deleteModule = () => {
    if (!moduleToDelete.value) return;
    
    router.delete(route('projects.modules.destroy', { project: props.project.id, module: moduleToDelete.value.id }), {
        onSuccess: () => {
            showDeleteModal.value = false;
            fetchModules();
        }
    });
};

const bulkDelete = () => {
    router.post(route('projects.modules.bulk-destroy', { project: props.project.id }), { 
        ids: selectedIds.value 
    }, {
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedIds.value = []; // Clear selection
            fetchModules();
        }
    });
};

const handleBulkSuccess = () => {
    fetchModules();
};

onMounted(() => {
    fetchModules();
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
</style>
