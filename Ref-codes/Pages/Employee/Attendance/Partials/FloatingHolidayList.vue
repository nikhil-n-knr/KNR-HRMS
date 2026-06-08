<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import { XCircleIcon, ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/outline';
import { useToastStore } from '@/Stores/toast';

const props = defineProps({
    data: Object,
});

const toast = useToastStore();
const showConfirm = ref(false);
const itemToCancel = ref(null);
const cancelForm = useForm({ type: 'floating', id: null });

const columns = {
    holiday: { label: 'Holiday', class: 'text-left w-1/3' },
    date: { label: 'Date', class: 'text-left' },
    status: { label: 'Status', class: 'text-center' },
    created_at: { label: 'Requested On', class: 'text-right' },
    actions: { label: '', class: 'text-right' }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
    });
};

const confirmCancel = (id) => {
    itemToCancel.value = id;
    showConfirm.value = true;
};

const processCancel = () => {
    cancelForm.id = itemToCancel.value;
    cancelForm.post(route('attendance.requests.cancel'), {
        onSuccess: () => {
             showConfirm.value = false;
             toast.success('Action processed');
        },
        preserveScroll: true
    });
};
</script>

<template>
    <div class="space-y-4">
         <div class="flex justify-end">
            <a href="/leave-management?tab=restricted" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150 gap-2">
                <ArrowTopRightOnSquareIcon class="w-4 h-4" /> Request Optional Holiday
            </a>
        </div>

        <BaseDataTable v-if="data" :columns="columns" :data="data.data" :pagination="data">
            <template #cell-holiday="{ item }">
                <span class="font-medium text-gray-800">{{ item.holiday?.name }}</span>
            </template>
            <template #cell-date="{ item }">
                <span class="text-sm font-medium text-gray-700">{{ formatDate(item.holiday?.date) }}</span>
            </template>
             <template #cell-status="{ item }">
                <span class="px-2 py-0.5 rounded text-xs font-bold" 
                    :class="{
                        'bg-yellow-100 text-yellow-800': item.status === 'Requested' || item.status === 'Pending',
                        'bg-green-100 text-green-800': item.status === 'Approved',
                        'bg-red-100 text-red-800': item.status === 'Rejected',
                        'bg-gray-100 text-gray-800': item.status === 'Cancellation Requested'
                    }">
                    {{ item.status }}
                </span>
             </template>
             <template #cell-created_at="{ item }">
                  <span class="text-xs text-gray-500">
                      {{ formatDate(item.created_at) }}
                  </span>
             </template>
             <template #cell-actions="{ item }">
                  <button v-if="['Requested', 'Pending', 'Approved'].includes(item.status)"
                      @click="confirmCancel(item.id)"
                      class="text-red-400 hover:text-red-600 transition-colors"
                      title="Cancel/Withdraw">
                      <XCircleIcon class="w-5 h-5" />
                  </button>
             </template>
        </BaseDataTable>

        <Modal :show="showConfirm" @close="showConfirm = false">
             <div class="p-6">
                 <h3 class="text-lg font-bold text-red-600 mb-4">Confirm Action</h3>
                 <p class="text-gray-700 mb-6">Are you sure you want to cancel/withdraw this request?</p>
                 <div class="flex justify-end gap-3">
                     <SecondaryButton @click="showConfirm = false">Back</SecondaryButton>
                     <PrimaryButton class="bg-red-600 hover:bg-red-700" @click="processCancel" :disabled="cancelForm.processing">
                         Yes, Cancel
                     </PrimaryButton>
                 </div>
             </div>
        </Modal>
    </div>
</template>
