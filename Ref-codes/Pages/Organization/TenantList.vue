<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-800 to-teal-700">
          Tenants (SaaS Mode)
        </h1>
        <p class="text-emerald-600/80 text-sm mt-1">Manage global system tenants and instances</p>
      </div>
      <button 
        @click="openModal()"
        class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl text-sm font-medium shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all flex items-center gap-2"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
        </svg>
        Register Tenant
      </button>
    </div>

    <!-- Data Table -->
    <BaseDataTable
        :columns="columns"
        :data="tenants || []"
        :meta="{}"
        :loading="false"
        search-placeholder="Search tenants..."
        @search="handleSearch"
    >
        <template #cell-name="{ item }">
            <div class="font-bold text-gray-900">{{ item.name }}</div>
            <div class="text-xs text-gray-500 font-mono">{{ item.domain || item.slug }}</div>
        </template>

        <template #cell-status="{ item }">
            <span 
              class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
              :class="item.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
            >
              {{ item.is_active ? 'Active' : 'Suspended' }}
            </span>
        </template>

        <template #cell-users_count="{ item }">
            <span class="text-sm text-gray-800 font-medium bg-gray-100 px-2 py-1 rounded">{{ item.users_count || 0 }} Users</span>
        </template>

        <template #rowActions="{ item }">
              <div class="flex items-center justify-end gap-3">
                <button 
                    @click="openModal(item)" 
                    class="text-emerald-600 hover:text-emerald-900 transition-colors text-sm"
                >
                    Edit
                </button>
                <button 
                    v-if="!item.is_active"
                    @click="deleteTenant(item)" 
                    class="text-red-400 hover:text-red-600 transition-colors text-sm"
                >
                    Delete
                </button>
              </div>
        </template>
    </BaseDataTable>
    
    <!-- Modal -->
    <Modal :show="showModal" :title="isEditing ? 'Edit Tenant' : 'Register New Tenant'" @close="closeModal">
        <div class="space-y-4">
            <BaseInput
                v-model="form.name"
                label="Tenant Name"
                placeholder="e.g. Acme Corp"
                minlength="2"
                maxlength="100"
                required
                :error="form.errors.name"
            />
             <BaseInput
                v-model="form.slug"
                label="Slug (URL identifier)"
                placeholder="e.g. acme-corp"
                minlength="2"
                maxlength="50"
                :error="form.errors.slug"
            />
            <BaseInput
                v-model="form.domain"
                label="Custom Domain (Optional)"
                placeholder="e.g. hr.acme.com"
                maxlength="100"
                :error="form.errors.domain"
            />
            <div class="flex items-center mt-4">
                <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded" />
                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                  Tenant Active
                </label>
            </div>
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
    <Modal :show="showDeleteModal" title="Delete Tenant" @close="closeDeleteModal">
        <div class="space-y-4">
            <p class="text-gray-600">
                Are you sure you want to delete <span class="font-semibold text-gray-800">{{ tenantToDelete?.name }}</span>? 
                This action is destructive and might orphan data if not correctly handled.
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
                Confirm Delete
            </button>
        </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import { useToastStore } from '@/stores/toast';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import BaseInput from '@/Components/BaseInput.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    tenants: Array,
});

const toast = useToastStore();
const search = ref('');
const showModal = ref(false);
const showDeleteModal = ref(false);
const tenantToDelete = ref(null);
const isEditing = ref(false);

const form = useForm({
    id: null,
    name: '',
    slug: '',
    domain: '',
    is_active: true
});

const columns = {
    name: { label: 'Tenant Name & Domain', class: 'text-left' },
    users_count: { label: 'Active Users', class: 'text-left' },
    status: { label: 'Status', class: 'text-left' },
};

const handleSearch = (term) => {
    search.value = term;
};

const openModal = (tenant = null) => {
    isEditing.value = !!tenant;
    form.clearErrors();
    form.reset();
    
    if (tenant) {
        form.id = tenant.id;
        form.name = tenant.name;
        form.slug = tenant.slug || '';
        form.domain = tenant.domain || '';
        form.is_active = tenant.is_active !== null ? tenant.is_active : true;
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const save = () => {
    if (isEditing.value) {
        form.put(`/admin/tenants/${form.id}`, {
            onSuccess: () => {
                toast.success('Tenant updated successfully');
                closeModal();
            },
            onError: () => toast.error('Failed to update tenant')
        });
    } else {
        form.post('/admin/tenants', {
            onSuccess: () => {
                toast.success('Tenant registered successfully');
                closeModal();
            },
            onError: () => toast.error('Failed to register tenant')
        });
    }
};

const deleteTenant = (tenant) => {
    tenantToDelete.value = tenant;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    tenantToDelete.value = null;
};

const confirmDelete = () => {
    if (!tenantToDelete.value) return;
    
    router.delete(`/admin/tenants/${tenantToDelete.value.id}`, {
        onSuccess: () => {
            toast.success('Tenant deleted');
            closeDeleteModal();
        },
        onError: () => {
            toast.error('Failed to delete tenant');
        }
    });
};
</script>
