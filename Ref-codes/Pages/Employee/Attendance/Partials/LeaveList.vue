<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import BaseTextarea from '@/Components/BaseTextarea.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import { PlusIcon, PencilIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { useToastStore } from '@/stores/toast';

const props = defineProps({
    data: Object,
    leaveTypes: Array
});

const emit = defineEmits(['page-change']);

const toast = useToastStore();
const showModal = ref(false);
const showConfirm = ref(false);
const processing = ref(false);
const itemToCancel = ref(null);

const form = useForm({
    id: null,
    leave_type_id: '',
    start_date: new Date().toISOString().split('T')[0],
    end_date: new Date().toISOString().split('T')[0],
    reason: '',
    attachment: null
});

// Computed to check requirement
const selectedType = computed(() => {
    return props.leaveTypes.find(t => t.id === form.leave_type_id);
});

const requiresDocument = computed(() => {
    return selectedType.value?.requires_document || false;
});

watch(() => form.start_date, (newVal) => {
    if (form.end_date && new Date(form.end_date) < new Date(newVal)) {
        form.end_date = newVal;
    }
});

const submit = () => {
    form.clearErrors();
    if (new Date(form.end_date) < new Date(form.start_date)) {
        form.setError('end_date', 'End Date cannot be before Start Date.');
        return;
    }
    processing.value = true;
    if (form.id) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT'
        })).post(route('leaves.update', form.id), {
            forceFormData: true,
            onSuccess: () => {
                 showModal.value = false;
                 toast.success("Leave request updated");
                 form.reset();
                 processing.value = false;
            },
            onError: () => processing.value = false
        });
    } else {
        form.post(route('leaves.store'), { 
            forceFormData: true,
            onSuccess: () => {
                 showModal.value = false;
                 toast.success("Leave request submitted");
                 form.reset();
                 processing.value = false;
            },
            onError: () => processing.value = false
        });
    }
};

const handleFile = (e) => {
    form.attachment = e.target.files[0];
};

const cancelForm = useForm({ type: 'leave', id: null });

const columns = {
    type: { label: 'Type', class: 'text-left' },
    date_range: { label: 'Date Range', class: 'text-left w-1/3' },
    days_count: { label: 'Days', class: 'text-center' },
    reason: { label: 'Reason', class: 'text-left w-1/3' },
    status: { label: 'Status', class: 'text-center' },
    actions: { label: '', class: 'text-right' }
};

const openCreate = () => {
    form.clearErrors();
    form.reset();
    form.id = null;
    showModal.value = true;
};

const openEdit = (leave) => {
    form.clearErrors();
    form.id = leave.id;
    form.leave_type_id = leave.leave_type_id;
    form.start_date = leave.start_date.split('T')[0];
    form.end_date = leave.end_date.split('T')[0];
    form.reason = leave.reason;
    form.attachment = null;
    showModal.value = true;
};

const confirmCancelAction = (id) => {
    itemToCancel.value = id;
    showConfirm.value = true;
};

const processCancel = () => {
    cancelForm.id = itemToCancel.value;
    cancelForm.delete(route('leaves.destroy', itemToCancel.value), {
        onSuccess: () => {
             toast.success('Request cancelled');
             showConfirm.value = false;
        },
        preserveScroll: true
    });
};

const isFuture = (dateStr) => {
    const start = new Date(dateStr);
    const today = new Date();
    today.setHours(0,0,0,0);
    return start > today;
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const cleanDate = typeof dateString === 'string' ? dateString.split('T')[0].replace(/-/g, '/') : dateString;
    return new Date(cleanDate).toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
    });
};
</script>

<template>
    <div class="space-y-4">
         <div class="flex justify-between items-center">
             <div class="text-sm text-gray-600">
                 Total Requests: <span class="font-bold">{{ data?.total || 0 }}</span>
             </div>
             <div>
                <PrimaryButton @click="openCreate" class="flex items-center gap-2">
                    <PlusIcon class="w-4 h-4" /> Request Leave
                </PrimaryButton>
            </div>
        </div>

        <BaseDataTable v-if="data" :columns="columns" :data="data.data" :pagination="data" @page-change="page => $emit('page-change', page)">
             <template #cell-type="{ item }">
                 <span class="font-medium text-gray-800">{{ item.leave_type?.name || 'Leave' }}</span>
             </template>
             <template #cell-date_range="{ item }">
                 <div class="flex flex-col">
                     <span class="text-sm font-medium text-gray-800">
                         {{ formatDate(item.start_date) }}
                     </span>
                     <span v-if="item.start_date !== item.end_date" class="text-xs text-gray-500">
                         to {{ formatDate(item.end_date) }}
                     </span>
                 </div>
             </template>
             <template #cell-days_count="{ item }">
                 <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-xs font-bold text-gray-700">
                     {{ item.total_days || '-' }}
                 </span>
             </template>
             <template #cell-status="{ item }">
                <span class="px-2 py-0.5 rounded text-xs font-bold" 
                    :class="{
                        'bg-yellow-100 text-yellow-800': ['pending', 'Pending'].includes(item.status),
                        'bg-green-100 text-green-800': ['approved', 'Approved'].includes(item.status),
                        'bg-red-100 text-red-800': ['rejected', 'Rejected'].includes(item.status),
                        'bg-gray-100 text-gray-800': ['cancelled', 'Cancelled', 'Cancellation Requested'].includes(item.status),
                    }">
                    {{ item.status }}
                </span>
             </template>
             <template #cell-actions="{ item }">
                <div class="flex items-center justify-end gap-2">
                    <button v-if="['pending', 'Pending'].includes(item.status)" 
                        @click="openEdit(item)"
                        class="px-2.5 py-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg flex items-center gap-1 transition-all"
                        title="Edit Request">
                        <PencilIcon class="w-3.5 h-3.5" /> Edit
                    </button>
                    <button v-if="['pending', 'Pending'].includes(item.status) || (['approved', 'Approved'].includes(item.status) && isFuture(item.start_date))" 
                        @click="confirmCancelAction(item.id)"
                        class="px-2.5 py-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg flex items-center gap-1 transition-all"
                        :title="['pending', 'Pending'].includes(item.status) ? 'Cancel Request' : 'Withdraw Request'">
                        <XMarkIcon class="w-3.5 h-3.5" /> {{ ['pending', 'Pending'].includes(item.status) ? 'Cancel' : 'Withdraw' }}
                    </button>
                </div>
             </template>
        </BaseDataTable>
        
        <Modal :show="showConfirm" @close="showConfirm = false">
             <div class="p-6">
                  <h3 class="text-lg font-bold text-red-600 mb-4">Confirm Cancellation</h3>
                  <p class="text-gray-700 mb-6">Are you sure you want to cancel this leave request?</p>
                  <div class="flex justify-end gap-3">
                      <SecondaryButton @click="showConfirm = false">Back</SecondaryButton>
                      <PrimaryButton class="bg-red-600 hover:bg-red-700" @click="processCancel" :disabled="cancelForm.processing">
                          Yes, Cancel
                      </PrimaryButton>
                  </div>
              </div>
        </Modal>

        <Modal :show="showModal" @close="showModal = false">
             <div class="p-6">
                 <h3 class="text-lg font-bold text-gray-800 mb-4">{{ form.id ? 'Edit Leave Request' : 'New Leave Request' }}</h3>
                 <form @submit.prevent="submit" class="space-y-4">
                     <BaseSelect label="Leave Type" v-model="form.leave_type_id" required>
                         <option value="" disabled>Select Type...</option>
                         <option v-for="type in leaveTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                     </BaseSelect>
 
                     <div class="grid grid-cols-2 gap-4">
                         <BaseInput type="date" label="Start Date" v-model="form.start_date" required :error="form.errors.start_date" />
                         <BaseInput type="date" label="End Date" v-model="form.end_date" :min="form.start_date" required :error="form.errors.end_date" />
                     </div>
 
                     <div v-if="requiresDocument" class="bg-blue-50 p-3 rounded-lg border border-blue-100 animate-fade-in-down">
                         <label class="block text-sm font-medium text-blue-800 mb-1">
                             Supporting Document (Required)
                         </label>
                         <p class="text-xs text-blue-600 mb-2">Please upload a medical certificate or relevant document.</p>
                         <input 
                             type="file" 
                             @change="handleFile"
                             accept=".pdf,.jpg,.jpeg,.png"
                             class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 transition"
                         />
                         <div v-if="form.errors.attachment" class="text-xs text-red-600 mt-1">{{ form.errors.attachment }}</div>
                     </div>
                     
                     <BaseTextarea label="Reason" v-model="form.reason" required rows="3" placeholder="Reason for leave..." />
                     
                     <div class="flex justify-end gap-3 mt-6">
                         <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                         <PrimaryButton :disabled="form.processing">{{ form.id ? 'Update Request' : 'Submit Request' }}</PrimaryButton>
                     </div>
                 </form>
             </div>
         </Modal>
     </div>
 </template>
