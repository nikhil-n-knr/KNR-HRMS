<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Leave Types</h1>
            <p class="text-sm text-gray-500 mt-1">Configure leave policies and entitlements.</p>
        </div>
        <button 
            @click="openModal()"
            class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl text-sm font-medium shadow-lg shadow-emerald-500/30 hover:shadow-xl hover:scale-[1.02] transition-all duration-200 flex items-center gap-2"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Add Type
        </button>
    </div>

    <!-- Data Table -->
    <BaseDataTable
        :columns="columns"
        :data="types"
        :meta="meta"
        :loading="loading"
        :per-page="perPage"
        search-placeholder="Search by name or code..."
        @search="handleSearch"
        @page-change="fetchTypes"
        @limit-change="handleLimitChange"
    >
        <template #cell-status="{ item }">
             <span 
                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border"
                :class="item.is_active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-gray-50 text-gray-500 border-gray-200'"
            >
                {{ item.is_active ? 'Active' : 'Inactive' }}
            </span>
        </template>
        
        <template #cell-color="{ item }">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg shadow-sm border border-gray-200" :style="{ backgroundColor: item.color }"></div>
                <span class="text-xs text-gray-500 uppercase">{{ item.color }}</span>
            </div>
        </template>

        <template #rowActions="{ item }">
            <div class="flex justify-end gap-2">
                <button @click="openModal(item)" class="p-1.5 bg-white text-indigo-600 rounded-lg border border-indigo-100 shadow-sm hover:bg-indigo-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                </button>
                <button @click="deleteType(item)" class="p-1.5 bg-white text-red-600 rounded-lg border border-red-100 shadow-sm hover:bg-red-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </div>
        </template>
    </BaseDataTable>

    <!-- Create/Edit Modal -->
    <Modal :show="showModal" @close="closeModal" :title="editingType ? 'Edit Leave Type' : 'New Leave Type'">
        <div class="space-y-4">
            <BaseInput 
                v-model="form.name" 
                label="Name" 
                placeholder="e.g. Annual Leave" 
                required 
                minlength="2"
                :error="errors.name"
            />
            
            <div class="grid grid-cols-2 gap-4">
                    <BaseInput 
                    v-model="form.code" 
                    label="Code" 
                    placeholder="AL" 
                    restrict="alphanumeric"
                    maxlength="5"
                    required
                    :error="errors.code"
                />
                <BaseInput 
                    v-model="form.days_allowed_per_year" 
                    label="Days / Year" 
                    placeholder="e.g. 12" 
                    restrict="numbers"
                    required
                    :error="errors.days_allowed_per_year"
                />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700">Badge Color</label>
                    <input type="color" v-model="form.color" class="h-10 w-full rounded-lg cursor-pointer">
                </div>
            </div>

            <!-- Toggles -->
            <div class="flex gap-6 pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="form.is_paid" class="rounded text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm font-medium text-gray-700">Paid Leave</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" v-model="form.requires_approval" class="rounded text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm font-medium text-gray-700">Requires Approval</span>
                </label>
            </div>
        </div>
        <template #footer>
            <div class="flex justify-end gap-3">
                <button @click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">Cancel</button>
                <button 
                    @click="saveType" 
                    :disabled="saving"
                    class="px-5 py-2 bg-emerald-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-500/30 hover:bg-emerald-700 disabled:opacity-50 flex items-center gap-2"
                >
                    <svg v-if="saving" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    {{ editingType ? 'Update Type' : 'Create Type' }}
                </button>
            </div>
        </template>
    </Modal>

    <!-- Confirmation Modal -->
     <ConfirmationModal 
        :show="showConfirm" 
        title="Delete Leave Type"
        message="Are you sure? This will remove the leave type configuration. Previous records will remain (soft deleted)."
        type="danger"
        @close="showConfirm = false"
        @confirm="confirmDelete"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import BaseInput from '@/Components/BaseInput.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmationModal from '@/Components/Modals/ConfirmationModal.vue';

const props = defineProps({
    leave_types: { type: Array, default: () => [] }
});

const toast = useToastStore();
const types = ref(props.leave_types || []);
const meta = ref({});
const loading = ref(false);
const saving = ref(false);
const search = ref('');
const perPage = ref(15);

// Sync with prop updates
watch(() => props.leave_types, (newVal) => {
    if (newVal) types.value = newVal;
}, { immediate: true });

// Modal States
const showModal = ref(false);
const showConfirm = ref(false);
const editingType = ref(null);
const typeToDelete = ref(null);

const form = ref({
    name: '',
    code: '',
    days_allowed_per_year: 0,
    color: '#10b981',
    is_paid: true,
    requires_approval: true
});

const errors = ref({});

const columns = {
    name: { label: 'Name' },
    code: { label: 'Code' },
    days_allowed_per_year: { label: 'Allowance (Days)' },
    color: { label: 'Badge' },
    status: { label: 'Status' }
};

const fetchTypes = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/api/admin/leave-types', {
            params: { page, per_page: perPage.value, search: search.value }
        });
        
        // Handle various response structures
        if (response.data.data && Array.isArray(response.data.data)) {
            // Paginated response
            types.value = response.data.data;
            meta.value = {
                current_page: response.data.current_page,
                last_page: response.data.last_page,
                total: response.data.total
            };
        } else if (Array.isArray(response.data)) {
            // Simple array
            types.value = response.data;
            meta.value = { current_page: 1, last_page: 1, total: response.data.length };
        } else if (response.data.leave_types) {
            // Wrapped in leave_types key
            types.value = response.data.leave_types;
            meta.value = { current_page: 1, last_page: 1 };
        }
    } catch (e) {
        toast.error("Failed to load leave types");
    } finally {
        loading.value = false;
    }
};

const handleSearch = (val) => {
    search.value = val;
    fetchTypes(1);
};

const handleLimitChange = (val) => {
    perPage.value = val;
    fetchTypes(1);
};

// Form Logic
const openModal = (item = null) => {
    errors.value = {};
    if (item) {
        editingType.value = item;
        form.value = { ...item };
    } else {
        editingType.value = null;
        form.value = {
            name: '',
            code: '',
            days_allowed_per_year: 0,
            color: '#10b981',
            is_paid: true,
            requires_approval: true
        };
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingType.value = null;
};

const saveType = async () => {
    saving.value = true;
    errors.value = {};
    try {
        if (editingType.value) {
            await axios.put(`/api/admin/leave-types/${editingType.value.id}`, form.value);
            toast.success("Leave type updated");
        } else {
            await axios.post('/api/admin/leave-types', form.value);
            toast.success("Leave type created");
        }
        closeModal();
        fetchTypes(meta.value.current_page);
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
            toast.error("Please check the form for errors");
        } else {
            toast.error("Failed to save");
        }
    } finally {
        saving.value = false;
    }
};

// Delete Logic
const deleteType = (item) => {
    typeToDelete.value = item;
    showConfirm.value = true;
};

const confirmDelete = async () => {
    if (!typeToDelete.value) return;
    try {
        await axios.delete(`/api/admin/leave-types/${typeToDelete.value.id}`);
        toast.success("Type deleted");
        fetchTypes(meta.value.current_page);
    } catch (e) {
        toast.error("Failed to delete");
    } finally {
        showConfirm.value = false;
    }
};

onMounted(() => {
    fetchTypes();
});
</script>
