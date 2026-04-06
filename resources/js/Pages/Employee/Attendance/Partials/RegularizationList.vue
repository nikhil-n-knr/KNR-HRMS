<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseTextarea from '@/Components/BaseTextarea.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';
import { useToastStore } from '@/stores/toast';

const props = defineProps({
    data: Object // Paginator
});

const toast = useToastStore();
const showModal = ref(false);
const processing = ref(false);

const form = useForm({
    date: '',
    regularized_in_time: '',
    regularized_out_time: '',
    reason: ''
});

const columns = {
    date: { label: 'Date', class: 'text-left' },
    timings: { label: 'Regularized Time', class: 'text-left' },
    reason: { label: 'Reason', class: 'text-left w-1/3' },
    status: { label: 'Status', class: 'text-center' },
    approver: { label: 'Approver', class: 'text-left' },
    created_at: { label: 'Requested On', class: 'text-right' }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
    });
};

const formatTime = (isoString) => {
    if (!isoString) return '-';
    // Handle both "10:00:00" and ISO format
    if (isoString.includes('T')) {
        return new Date(isoString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    return isoString.substring(0, 5);
};

const openCreate = () => {
    form.reset();
    showModal.value = true;
};

const submit = () => {
    processing.value = true;
    form.post(route('employee.attendance.regularization.store'), { 
        onSuccess: () => {
            showModal.value = false;
            toast.success("Regularization request submitted");
            processing.value = false;
        },
        onError: () => processing.value = false
    });
};
</script>

<template>
    <div class="space-y-4">
         <div class="flex justify-end">
            <PrimaryButton @click="openCreate" class="flex items-center gap-2">
                <PlusIcon class="w-4 h-4" /> New Regularization
            </PrimaryButton>
        </div>

        <BaseDataTable v-if="data" :columns="columns" :data="data.data" :pagination="data">
             <template #cell-date="{ item }">
                 <span class="font-medium text-gray-800">{{ formatDate(item.date) }}</span>
             </template>
             <template #cell-timings="{ item }">
                 <div class="text-sm">
                     <span class="text-emerald-600 font-medium">{{ formatTime(item.regularized_in_time) }}</span>
                     <span class="text-gray-400 mx-1">-</span>
                     <span class="text-red-500 font-medium">{{ formatTime(item.regularized_out_time) }}</span>
                 </div>
             </template>
             <template #cell-status="{ item }">
                <span class="px-2 py-0.5 rounded text-xs font-bold" 
                    :class="{
                        'bg-yellow-100 text-yellow-800': item.status === 'Pending',
                        'bg-green-100 text-green-800': item.status === 'Approved',
                        'bg-red-100 text-red-800': item.status === 'Rejected'
                    }">
                    {{ item.status }}
                </span>
             </template>
             <template #cell-approver="{ item }">
                 <span v-if="item.approver_remarks" class="text-xs italic text-gray-500 flex flex-col">
                     <span>{{ item.approver_remarks }}</span>
                 </span>
                 <span v-else>-</span>
             </template>
             <template #cell-created_at="{ item }">
                 <span class="text-xs text-gray-500">{{ formatDate(item.created_at) }}</span>
             </template>
        </BaseDataTable>

        <Modal :show="showModal" @close="showModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Regularize Attendance</h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <BaseInput type="date" label="Date to Regularize" v-model="form.date" required :error="form.errors.date" />

                    <div class="grid grid-cols-2 gap-4">
                        <BaseInput type="time" label="Check In Time" v-model="form.regularized_in_time" required :error="form.errors.regularized_in_time" />
                        <BaseInput type="time" label="Check Out Time" v-model="form.regularized_out_time" required :error="form.errors.regularized_out_time" />
                    </div>

                    <BaseTextarea label="Reason" v-model="form.reason" required rows="3" placeholder="Reason for missing/incorrect punch..." :error="form.errors.reason" />
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="form.processing">Submit Request</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>
