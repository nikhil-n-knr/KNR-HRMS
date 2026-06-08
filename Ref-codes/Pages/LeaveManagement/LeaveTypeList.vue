<template>
  <div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Leave Types</h1>
      <button 
        @click="openCreateModal"
        class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-lg text-sm font-medium shadow-md hover:shadow-lg transition-all flex items-center gap-2"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        Add Leave Type
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days/Year</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="type in (types?.data || [])" :key="type.id" class="hover:bg-gray-50 transition">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ type.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ type.code }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ type.days_allowed_per_year }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <span :class="type.is_paid ? 'text-green-600 bg-green-100' : 'text-gray-600 bg-gray-100'" class="px-2 py-1 rounded-full text-xs font-bold">
                    {{ type.is_paid ? 'Yes' : 'No' }}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded-full border border-gray-200" :style="{ backgroundColor: type.color || '#10b981' }"></span>
                    <span class="text-xs text-gray-400 font-mono">{{ type.color }}</span>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button @click="openEditModal(type)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
              <button @click="confirmDelete(type)" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
          </tr>
          <tr v-if="!types?.data?.length">
             <td colspan="6" class="px-6 py-10 text-center text-gray-400">No leave types found.</td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Modal -->
    <Modal :show="showModal" @close="closeModal">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ form.id ? 'Edit' : 'Create' }} Leave Type</h3>
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <BaseInput 
                        label="Name" 
                        v-model="form.name" 
                        required 
                        placeholder="e.g. Annual Leave" 
                        :error="form.errors.name"
                    />
                    <BaseInput 
                        label="Code" 
                        v-model="form.code" 
                        required 
                        placeholder="e.g. AL" 
                        :error="form.errors.code"
                    />
                    <BaseInput 
                        label="Days Per Year" 
                        v-model="form.days_allowed_per_year" 
                        type="number" 
                        required 
                        :error="form.errors.days_allowed_per_year"
                    />
                    
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-700">Color</label>
                        <input type="color" v-model="form.color" class="h-10 w-full rounded border-gray-300 cursor-pointer" />
                    </div>

                    <div class="col-span-2 flex items-center gap-6 mt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.is_paid" class="w-5 h-5 rounded text-emerald-600 focus:ring-emerald-500 border-gray-300" />
                            <span class="text-sm text-gray-700">Paid Leave</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.requires_approval" class="w-5 h-5 rounded text-emerald-600 focus:ring-emerald-500 border-gray-300" />
                            <span class="text-sm text-gray-700">Requires Approval</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.requires_document" class="w-5 h-5 rounded text-emerald-600 focus:ring-emerald-500 border-gray-300" />
                            <span class="text-sm text-gray-700">Requires Document (e.g. Medical)</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50">
                        {{ form.id ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal :show="showDeleteModal" @close="closeModal">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Confirm Delete</h3>
            <p class="text-gray-600 mb-6">Are you sure you want to delete this leave type? This action cannot be undone.</p>
            <div class="flex justify-end gap-3">
                <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                <button @click="deleteType" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
            </div>
        </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import BaseInput from '@/Components/BaseInput.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();
const props = defineProps({}); // No props needed

const types = ref({ data: [] });
const loading = ref(false);
const showModal = ref(false);

const form = useForm({
    id: null,
    name: '',
    code: '',
    days_allowed_per_year: 12,
    is_paid: true,
    requires_approval: true,
    requires_document: false,
    color: '#10b981'
});

const fetchData = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/admin/leave-types', {
            headers: { 'Accept': 'application/json' }
        });
        console.log("Fetched types:", res.data); // Debug
        types.value = res.data;
    } catch (e) {
        console.error("Fetch error:", e);
        toast.error("Failed to load leave types");
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchData();
});

const openCreateModal = () => {
    form.reset();
    form.id = null;
    showModal.value = true;
};

const openEditModal = (type) => {
    form.id = type.id;
    form.name = type.name;
    form.code = type.code;
    form.days_allowed_per_year = type.days_allowed_per_year;
    form.is_paid = !!type.is_paid;
    form.requires_approval = !!type.requires_approval;
    form.requires_document = !!type.requires_document;
    form.color = type.color || '#10b981';
    showModal.value = true;
};

const showDeleteModal = ref(false);
const deleteId = ref(null);

const closeModal = () => {
    showModal.value = false;
    showDeleteModal.value = false; // Close delete modal too
    form.reset();
    form.clearErrors();
};

const confirmDelete = (type) => {
    deleteId.value = type.id;
    showDeleteModal.value = true;
};

const submit = () => {
    const url = form.id ? `/admin/leave-types/${form.id}` : '/admin/leave-types';
    const method = form.id ? 'put' : 'post';

    form.submit(method, url, {
        onSuccess: () => {
            toast.success(form.id ? 'Updated successfully' : 'Created successfully');
            closeModal();
            fetchData();
        },
        onError: () => {
            toast.error('Operation failed');
        }
    });
};

const deleteType = async () => {
    if (!deleteId.value) return;
    try {
        await axios.delete(`/admin/leave-types/${deleteId.value}`);
        toast.success('Deleted successfully');
        fetchData();
        closeModal();
    } catch (error) {
         toast.error('Failed to delete');
    }
};
</script>
