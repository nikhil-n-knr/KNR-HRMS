<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import BaseTextarea from '@/Components/BaseTextarea.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { useToastStore } from '@/stores/toast';

const props = defineProps({
    data: Object,
    leaveTypes: Array
});

const toast = useToastStore();
const showModal = ref(false);
const showConfirm = ref(false);
const processing = ref(false);
const itemToCancel = ref(null);

const form = useForm({
    leave_type_id: '',
    start_date: new Date().toISOString().split('T')[0],
    end_date: new Date().toISOString().split('T')[0],
    reason: '',
    document: null
});

// Computed to check requirement
const selectedType = computed(() => {
    return props.leaveTypes.find(t => t.id === form.leave_type_id);
});

const requiresDocument = computed(() => {
    return selectedType.value?.requires_document || false;
});

const submit = () => {
    processing.value = true;
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
};

const handleFile = (e) => {
    form.document = e.target.files[0];
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
    form.reset();
    showModal.value = true;
};



const confirmCancelAction = (id) => {
    itemToCancel.value = id;
    showConfirm.value = true;
};

const processCancel = () => {
    cancelForm.id = itemToCancel.value;
    // Assuming a generic cancel route or specific leave cancel route exists
    // Using generic logic if available, otherwise specific
    cancelForm.delete(route('leaves.destroy', itemToCancel.value), {
        onSuccess: () => {
             toast.success('Request cancelled');
             showConfirm.value = false;
        },
        preserveScroll: true
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', { 
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

        <BaseDataTable v-if="data" :columns="columns" :data="data.data" :pagination="data">
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
                        'bg-yellow-100 text-yellow-800': item.status === 'pending',
                        'bg-green-100 text-green-800': item.status === 'approved',
                        'bg-red-100 text-red-800': item.status === 'rejected',
                        'bg-gray-100 text-gray-800': item.status === 'cancelled',
                    }">
                    {{ item.status }}
                </span>
             </template>
             <template #cell-actions="{ item }">
                <div class="flex items-center justify-end gap-2">
                    <button v-if="item.status === 'pending'" 
                        @click="confirmCancelAction(item.id)"
                        class="text-red-400 hover:text-red-600 transition-colors"
                        title="Cancel Request">
                        <TrashIcon class="w-5 h-5" />
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
                <h3 class="text-lg font-bold text-gray-800 mb-4">New Leave Request</h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <BaseSelect label="Leave Type" v-model="form.leave_type_id" required>
                        <option value="" disabled>Select Type...</option>
                        <option v-for="type in leaveTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                    </BaseSelect>

                    <div class="grid grid-cols-2 gap-4">
                        <BaseInput type="date" label="Start Date" v-model="form.start_date" required />
                        <BaseInput type="date" label="End Date" v-model="form.end_date" :min="form.start_date" required />
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
                        <div v-if="form.errors.document" class="text-xs text-red-600 mt-1">{{ form.errors.document }}</div>
                    </div>
                    
                    <BaseTextarea label="Reason" v-model="form.reason" required rows="3" placeholder="Reason for leave..." />
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="form.processing">Submit Request</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>
