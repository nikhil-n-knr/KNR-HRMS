<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseTextarea from '@/Components/BaseTextarea.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import { PlusIcon, HomeIcon } from '@heroicons/vue/24/outline';
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();

defineOptions({ layout: MainLayout });

const props = defineProps({
    requests: Object,
});

const showModal = ref(false);
const processing = ref(false);

const form = useForm({
    start_date: new Date().toISOString().split('T')[0],
    end_date: new Date().toISOString().split('T')[0],
    reason: ''
});

const columns = {
    date: { label: 'Date', class: 'text-left' },
    reason: { label: 'Reason', class: 'text-left w-1/2' },
    status: { label: 'Status', class: 'text-center' },
    updated_at: { label: 'Last Update', class: 'text-right' }
};

const openCreate = () => {
    form.reset();
    showModal.value = true;
};

const submit = () => {
    processing.value = true;
    form.post(route('attendance.wfh.store'), { // Using admin controller alias if routed
        onSuccess: () => {
            showModal.value = false;
            toast.success("WFH Request submitted");
            processing.value = false;
        },
        onError: () => processing.value = false
    });
};
</script>

<template>
    <div class="space-y-6">
         <div class="flex justify-between items-center bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">My Work From Home Requests</h2>
                <p class="text-sm text-gray-500">Request and track WFH status.</p>
            </div>
            <PrimaryButton @click="openCreate" class="flex items-center gap-2">
                <PlusIcon class="w-4 h-4" /> Request WFH
            </PrimaryButton>
        </div>

        <BaseDataTable :columns="columns" :data="requests.data" :pagination="requests">
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
              <template #cell-updated_at="{ item }">
                 <span class="text-xs text-gray-500">
                     {{ new Date(item.updated_at).toLocaleDateString() }}
                 </span>
             </template>
        </BaseDataTable>

        <Modal :show="showModal" @close="showModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Request Work From Home</h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                         <BaseInput type="date" label="Start Date" v-model="form.start_date" required />
                         <BaseInput type="date" label="End Date" v-model="form.end_date" required />
                    </div>
                    <BaseTextarea label="Reason" v-model="form.reason" required rows="3" placeholder="Reason for WFH..." />
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="form.processing">Submit Request</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>
