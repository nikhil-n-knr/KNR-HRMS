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
import { PlusIcon, ClockIcon } from '@heroicons/vue/24/outline';
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();

defineOptions({ layout: MainLayout });

const props = defineProps({
    requests: Object,
});

const showModal = ref(false);
const processing = ref(false);

const form = useForm({
    date: new Date().toISOString().split('T')[0],
    hours: 1,
    reason: ''
});

const columns = {
    date: { label: 'Date', class: 'text-left' },
    hours: { label: 'Hours', class: 'text-center' },
    reason: { label: 'Reason', class: 'text-left w-1/2' },
    status: { label: 'Status', class: 'text-center' },
    approved_at: { label: 'Actioned On', class: 'text-right' }
};

const openCreate = () => {
    form.reset();
    showModal.value = true;
};

const submit = () => {
    processing.value = true;
    form.post(route('attendance.overtime.store'), { // Using the admin route for now if shared controller, check Web.php alias
        onSuccess: () => {
            showModal.value = false;
            toast.success("Requests submitted");
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
                <h2 class="text-xl font-semibold text-gray-800">My Overtime Requests</h2>
                <p class="text-sm text-gray-500">Track your extra hours.</p>
            </div>
            <PrimaryButton @click="openCreate" class="flex items-center gap-2">
                <PlusIcon class="w-4 h-4" /> Request OT
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
             <template #cell-approved_at="{ item }">
                 <span v-if="item.approved_at" class="text-xs text-gray-500">
                     {{ new Date(item.approved_at).toLocaleDateString() }}
                 </span>
                 <span v-else>-</span>
             </template>
        </BaseDataTable>

        <Modal :show="showModal" @close="showModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Request Overtime</h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <BaseInput type="date" label="Date" v-model="form.date" required />
                    <BaseInput type="number" step="0.5" label="Hours" v-model="form.hours" required />
                    <BaseTextarea label="Reason" v-model="form.reason" required rows="3" placeholder="Why was overtime needed?" />
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="form.processing">Submit Request</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>
