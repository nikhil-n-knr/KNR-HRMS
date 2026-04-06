<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseTextarea from '@/Components/BaseTextarea.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import { HomeIcon, PlusIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { useToastStore } from '@/stores/toast';

const props = defineProps({
    data: Object,
    options: Object 
});

const toast = useToastStore();
const showModal = ref(false);
const showConfirm = ref(false);
const processing = ref(false);
const itemToCancel = ref(null);

const form = useForm({
    start_date: new Date().toISOString().split('T')[0],
    end_date: new Date().toISOString().split('T')[0],
    reason: ''
});

const cancelForm = useForm({ type: 'wfh', id: null });

const columns = {
    date_range: { label: 'Date Range', class: 'text-left w-1/3' },
    days_count: { label: 'Days', class: 'text-center' },
    reason: { label: 'Reason', class: 'text-left w-1/3' },
    status: { label: 'Status', class: 'text-center' },
    approved_by: { label: 'Updated By', class: 'text-right' },
    actions: { label: '', class: 'text-right' }
};

const openCreate = () => {
    form.reset();
    showModal.value = true;
};

const submit = () => {
    processing.value = true;
    form.post(route('attendance.wfh.store'), { 
        onSuccess: () => {
             showModal.value = false;
             toast.success("Request submitted");
             processing.value = false;
        },
        onError: () => processing.value = false
    });
};

const confirmCancelAction = (id) => {
    itemToCancel.value = id;
    showConfirm.value = true;
};

const processCancel = () => {
    cancelForm.id = itemToCancel.value;
    cancelForm.post(route('attendance.requests.cancel'), {
        onSuccess: () => {
             toast.success('Action processed');
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

const daysCount = computed(() => {
    if (!form.start_date || !form.end_date) return 0;
    const start = new Date(form.start_date);
    const end = new Date(form.end_date);
    if (isNaN(start) || isNaN(end)) return 0;
    
    let count = 0;
    let current = new Date(start);
    
    while (current <= end) {
        // Exclude Sundays (0)
        if (current.getDay() !== 0) {
            count++;
        }
        current.setDate(current.getDate() + 1);
    }
    return count;
});
</script>

<template>
    <div class="space-y-4">
         <div class="flex justify-between items-center">
             <div class="text-sm text-gray-600">
                 Total Requests: <span class="font-bold">{{ data?.total || 0 }}</span>
             </div>
             <div>
                <PrimaryButton @click="openCreate" class="flex items-center gap-2">
                    <PlusIcon class="w-4 h-4" /> Request WFH
                </PrimaryButton>
            </div>
        </div>

        <BaseDataTable v-if="data" :columns="columns" :data="data.data" :pagination="data">
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
                     {{ item.days_count }}
                 </span>
             </template>
             <template #cell-status="{ item }">
                <span class="px-2 py-0.5 rounded text-xs font-bold" 
                    :class="{
                        'bg-yellow-100 text-yellow-800': item.status === 'Pending',
                        'bg-green-100 text-green-800': item.status === 'Approved',
                        'bg-red-100 text-red-800': item.status === 'Rejected',
                        'bg-gray-100 text-gray-800': item.status === 'Cancellation Requested',
                        'bg-blue-100 text-blue-800': item.status === 'Mixed'
                    }">
                    {{ item.status }}
                </span>
             </template>
             <template #cell-approved_by="{ item }">
                 <span v-if="item.approved_by" class="text-xs text-gray-500">
                     Admin #{{ item.approved_by }}
                 </span>
                 <span v-else>-</span>
             </template>
             <template #cell-actions="{ item }">
                <div class="flex items-center justify-end gap-2">
                    <button v-if="item.status === 'Pending'" class="text-gray-400 hover:text-blue-600 transition-colors" title="Edit">
                        <PencilSquareIcon class="w-5 h-5" />
                    </button>

                    <button v-if="['Pending', 'Approved', 'Mixed'].includes(item.status)" 
                        @click="confirmCancelAction(item.id)"
                        class="text-red-400 hover:text-red-600 transition-colors"
                        :title="item.status === 'Approved' ? 'Withdraw Batch' : 'Cancel Batch'">
                        <TrashIcon class="w-5 h-5" />
                    </button>
                </div>
             </template>
        </BaseDataTable>
        
        <Modal :show="showConfirm" @close="showConfirm = false">
             <div class="p-6">
                 <h3 class="text-lg font-bold text-red-600 mb-4">Confirm Action</h3>
                 <p class="text-gray-700 mb-6">Are you sure you want to cancel/withdraw this entire batch of requests?</p>
                 <div class="flex justify-end gap-3">
                     <SecondaryButton @click="showConfirm = false">Back</SecondaryButton>
                     <PrimaryButton class="bg-red-600 hover:bg-red-700" @click="processCancel" :disabled="cancelForm.processing">
                         Yes, Cancel Batch
                     </PrimaryButton>
                 </div>
             </div>
        </Modal>

        <Modal :show="showModal" @close="showModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Request Work From Home</h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <BaseInput type="date" label="Start Date" v-model="form.start_date" required />
                        <BaseInput type="date" label="End Date" v-model="form.end_date" :min="form.start_date" required />
                    </div>
                    
                    <p class="text-xs text-gray-500 italic pb-2">
                        Est. Days: {{ daysCount }} (Excluding Sundays)
                    </p>

                    <BaseTextarea label="Reason" v-model="form.reason" required rows="3" placeholder="Why WFH?" />
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="form.processing">Submit Request</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>
