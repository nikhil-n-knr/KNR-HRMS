<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-800 to-teal-700">
          Locations
        </h1>
        <p class="text-emerald-600/80 text-sm mt-1">Manage office locations and branches</p>
      </div>
      <button 
        v-if="authStore.can('org.locations.create')"
        @click="openModal()"
        class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl text-sm font-medium shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all flex items-center gap-2"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
        </svg>
        Add Location
      </button>
    </div>

    <!-- Data Table -->
    <BaseDataTable
        :columns="columns"
        :data="locations || []"
        :meta="{}"
        :loading="false"
        search-placeholder="Search locations..."
        @search="handleSearch"
    >
        <template #cell-name="{ item }">
            <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
        </template>

        <template #cell-city="{ item }">
            <div class="text-sm text-gray-900">{{ item.city }}</div>
            <div class="text-xs text-gray-500">{{ item.address }}</div>
        </template>

        <template #cell-code="{ item }">
            <span class="px-2 py-1 bg-gray-100 rounded text-xs font-mono">{{ item.code || '-' }}</span>
        </template>

        <template #rowActions="{ item }">
              <div class="flex items-center justify-end gap-3">
                <button 
                    v-if="authStore.can('org.locations.update')"
                    @click="openModal(item)" 
                    class="text-emerald-600 hover:text-emerald-900 transition-colors"
                >
                    Edit
                </button>
                <button 
                    v-if="authStore.can('org.locations.delete')"
                    @click="deleteLocation(item)" 
                    class="text-red-400 hover:text-red-600 transition-colors"
                >
                    Delete
                </button>
              </div>
        </template>
    </BaseDataTable>
    
    <!-- Modal -->
    <Modal :show="showModal" :title="isEditing ? 'Edit Location' : 'Create Location'" @close="closeModal">
        <div class="space-y-4">
            <BaseInput
                v-model="form.name"
                label="Location Name"
                placeholder="e.g. Headquarters"
                minlength="2"
                maxlength="100"
                required
                :error="form.errors.name"
            />
            <BaseInput
                v-model="form.city"
                label="City"
                placeholder="e.g. New York"
                minlength="2"
                maxlength="50"
                required
                :error="form.errors.city"
            />
            <BaseInput
                v-model="form.address"
                label="Address"
                placeholder="123 Main St"
                maxlength="500"
                :error="form.errors.address"
            />
            <BaseInput
                v-model="form.code"
                label="Code (Optional)"
                placeholder="e.g. NYC-HQ"
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
    <Modal :show="showDeleteModal" title="Delete Location" @close="closeDeleteModal">
        <div class="space-y-4">
            <p class="text-gray-600">
                Are you sure you want to delete <span class="font-semibold text-gray-800">{{ locToDelete?.name }}</span>? 
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
import BaseInput from '@/Components/BaseInput.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import { useToastStore } from '@/stores/toast';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    locations: Array, // Or Object if paginated
});

const toast = useToastStore();
const authStore = useAuthStore();

const search = ref('');
const showModal = ref(false);
const showDeleteModal = ref(false);
const locToDelete = ref(null);
const isEditing = ref(false);

// Inertia Form
const form = useForm({
    id: null,
    name: '',
    code: '',
    city: '',
    address: ''
});

const columns = {
    name: { label: 'Name', class: 'text-left' },
    city: { label: 'City / Address', class: 'text-left' },
    code: { label: 'Code', class: 'text-left' },
};

// Search (Client side filtering for now as controller returns all)
// Or better: keep as is since it's a small list usually.
const handleSearch = (term) => {
    // If we want server-side search, we should use router.get like in UserList
    // But LocationController::index currently returns all.
    // Let's implement client-side filter for now to avoid controller changes if not needed.
    // Actually, consistency is better. But let's stick to simple props rendering first.
    search.value = term;
};

// Filtered data computed property could be added if needed, 
// but BaseDataTable might handle client side search if we pass all data? 
// BaseDataTable usually expects server side search event.
// Let's assume controller returns all data for now (which it does: Location::all()).

const openModal = (loc = null) => {
    isEditing.value = !!loc;
    form.clearErrors();
    form.reset();
    
    if (loc) {
        form.id = loc.id;
        form.name = loc.name;
        form.code = loc.code;
        form.city = loc.city;
        form.address = loc.address;
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const save = () => {
    if (isEditing.value) {
        form.put(`/admin/locations/${form.id}`, {
            onSuccess: () => {
                toast.success('Location updated successfully');
                closeModal();
            },
            onError: () => toast.error('Failed to update location')
        });
    } else {
        form.post('/admin/locations', {
            onSuccess: () => {
                toast.success('Location created successfully');
                closeModal();
            },
            onError: () => toast.error('Failed to create location')
        });
    }
};

const deleteLocation = (loc) => {
    locToDelete.value = loc;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    locToDelete.value = null;
};

const confirmDelete = () => {
     if (!locToDelete.value) return;

    router.delete(`/admin/locations/${locToDelete.value.id}`, {
        onSuccess: () => {
             toast.success('Location deleted');
             closeDeleteModal();
        },
        onError: (err) => {
             toast.error(err?.response?.data?.message || 'Failed to delete');
        }
    });
};
</script>
