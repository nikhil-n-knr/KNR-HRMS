<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-800 to-teal-700">
          Departments
        </h1>
        <p class="text-emerald-600/80 text-sm mt-1">Manage organization departments</p>
      </div>
      <button 
        v-if="authStore.can('org.departments.create')"
        @click="openModal()"
        class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl text-sm font-medium shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all flex items-center gap-2"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
        </svg>
        Add Department
      </button>
    </div>

    <!-- Data Table -->
    <BaseDataTable
        :columns="columns"
        :data="departments || []"
        :meta="{}"
        :loading="false"
        search-placeholder="Search departments..."
        @search="handleSearch"
    >
        <template #cell-name="{ item }">
            <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
        </template>

        <template #cell-code="{ item }">
            <span class="px-2 py-1 bg-gray-100 rounded text-xs font-mono">{{ item.code || '-' }}</span>
        </template>

        <template #rowActions="{ item }">
              <div class="flex items-center justify-end gap-3">
                <button 
                    v-if="authStore.can('org.departments.update')"
                    @click="openModal(item)" 
                    class="text-emerald-600 hover:text-emerald-900 transition-colors"
                >
                    Edit
                </button>
                <button 
                    v-if="authStore.can('org.departments.delete')"
                    @click="deleteDepartment(item)" 
                    class="text-red-400 hover:text-red-600 transition-colors"
                >
                    Delete
                </button>
              </div>
        </template>
    </BaseDataTable>
    
    <!-- Modal -->
    <Modal :show="showModal" :title="isEditing ? 'Edit Department' : 'Create Department'" @close="closeModal">
        <div class="space-y-4">
            <BaseInput
                v-model="form.name"
                label="Department Name"
                placeholder="e.g. Human Resources"
                minlength="2"
                maxlength="100"
                required
                :error="form.errors.name"
            />
             <BaseInput
                v-model="form.code"
                label="Code (Optional)"
                placeholder="e.g. HR001"
                minlength="2"
                maxlength="20"
                :error="form.errors.code"
            />
        </div>
        <template #footer>
            <button @click="closeModal" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition">Cancel</button>
            <button 
                @click="save" 
                :disabled="form.processing"
                class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-lg text-sm font-medium shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            >
                {{ form.processing ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
            </button>
        </template>
    </Modal>
    <!-- Delete Confirmation Modal -->
    <Modal :show="showDeleteModal" title="Delete Department" @close="closeDeleteModal">
        <div class="space-y-4">
            <p class="text-gray-600">
                Are you sure you want to delete <span class="font-semibold text-gray-800">{{ deptToDelete?.name }}</span>? 
                This action cannot be undone.
            </p>
        </div>
        <template #footer>
            <button @click="closeDeleteModal" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition">
                Cancel
            </button>
            <button 
                @click="confirmDelete" 
                class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium shadow-md hover:bg-red-700 transition-all"
            >
                Delete
            </button>
        </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { useToastStore } from '@/stores/toast';
import { useAuthStore } from '@/stores/auth';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    departments: Array, // Or Object if paginated
});

const toast = useToastStore();
const authStore = useAuthStore();

const search = ref('');
const showModal = ref(false);
const showDeleteModal = ref(false);
const deptToDelete = ref(null);
const isEditing = ref(false);

const form = useForm({
    id: null,
    name: '',
    code: ''
});

const columns = {
    name: { label: 'Name', class: 'text-left' },
    code: { label: 'Code', class: 'text-left' },
};

const handleSearch = (term) => {
    // Client side filtering or just update search ref call router.get if supported
    search.value = term;
};

const openModal = (dept = null) => {
    isEditing.value = !!dept;
    form.clearErrors();
    form.reset();
    
    if (dept) {
        form.id = dept.id;
        form.name = dept.name;
        form.code = dept.code;
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const save = () => {
    if (isEditing.value) {
        form.put(`/admin/departments/${form.id}`, {
            onSuccess: () => {
                toast.success('Department updated successfully');
                closeModal();
            },
            onError: () => toast.error('Failed to update department')
        });
    } else {
        form.post('/admin/departments', {
            onSuccess: () => {
                toast.success('Department created successfully');
                closeModal();
            },
            onError: () => toast.error('Failed to create department')
        });
    }
};

const deleteDepartment = (dept) => {
    deptToDelete.value = dept;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deptToDelete.value = null;
};

const confirmDelete = () => {
    if (!deptToDelete.value) return;
    
    router.delete(`/admin/departments/${deptToDelete.value.id}`, {
        onSuccess: () => {
            toast.success('Department deleted');
            closeDeleteModal();
        },
        onError: () => {
            toast.error('Failed to delete department');
        }
    });
};
</script>
