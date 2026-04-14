<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseTextarea from '@/Components/BaseTextarea.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import { PlusIcon, ClockIcon } from '@heroicons/vue/24/outline';
import { useToastStore } from '@/stores/toast';

const props = defineProps({
    data: Object,
    options: Object // { projects: [] }
});

const toast = useToastStore();
const showModal = ref(false);
const processing = ref(false);

const form = useForm({
    date: new Date().toISOString().split('T')[0],
    hours: 1,
    reason: '',
    project_id: null,
    task_id: null
});

const projects = computed(() => props.options?.projects || []);
const tasks = computed(() => {
    if (!form.project_id) return [];
    const p = projects.value.find(p => p.id === form.project_id);
    return p?.tasks || [];
});

watch(() => form.project_id, () => form.task_id = null);

const cancelForm = useForm({ type: 'overtime', id: null });

const columns = {
    date: { label: 'Date', class: 'text-left' },
    hours: { label: 'Hours', class: 'text-center' },
    reason: { label: 'Reason', class: 'text-left w-1/3' }, 
    project: { label: 'Project', class: 'text-left' }, 
    status: { label: 'Status', class: 'text-center' },
    approved_at: { label: 'Actioned On', class: 'text-right' },
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

const openCreate = () => {
    form.reset();
    showModal.value = true;
};

const submit = () => {
    processing.value = true;
    form.post(route('attendance.overtime.store'), { 
        onSuccess: () => {
            showModal.value = false;
            toast.success("Request submitted");
            processing.value = false;
        },
        onError: () => processing.value = false
    });
};

const cancelRequest = (id) => {
    if (!confirm('Are you sure you want to cancel/withdraw this request?')) return;
    cancelForm.id = id;
    cancelForm.post(route('attendance.requests.cancel'), {
        onSuccess: () => toast.success('Action processed'),
        preserveScroll: true
    });
};
</script>

<template>
    <div class="space-y-4">
         <div class="flex justify-end">
            <PrimaryButton @click="openCreate" class="flex items-center gap-2">
                <PlusIcon class="w-4 h-4" /> Request OT
            </PrimaryButton>
        </div>

        <BaseDataTable v-if="data" :columns="columns" :data="data.data" :pagination="data">
             <template #cell-date="{ item }">
                 <span class="text-sm font-medium text-gray-700">{{ formatDate(item.date) }}</span>
             </template>
             <template #cell-hours="{ item }">
                 <span class="text-sm font-bold text-emerald-700">
                     {{ (item.minutes / 60).toFixed(1) }} hrs
                 </span>
             </template>
             <template #cell-project="{ item }">
                 <span class="text-sm text-gray-600">{{ item.project?.name || '-' }}</span>
             </template>
             <template #cell-status="{ item }">
                <span class="px-2 py-0.5 rounded text-xs font-bold" 
                    :class="{
                        'bg-yellow-100 text-yellow-800': item.status === 'Pending',
                        'bg-green-100 text-green-800': item.status === 'Approved',
                        'bg-red-100 text-red-800': item.status === 'Rejected',
                        'bg-gray-100 text-gray-800': item.status === 'Cancellation Requested'
                    }">
                    {{ item.status }}
                </span>
             </template>
             <template #cell-approved_at="{ item }">
                 <span v-if="item.status !== 'Pending'" class="text-xs text-gray-500">
                     {{ formatDate(item.updated_at) }}
                 </span>
                 <span v-else>-</span>
             </template>
             <template #cell-actions="{ item }">
                <button v-if="['Pending', 'Approved'].includes(item.status)" 
                    @click="cancelRequest(item.id)"
                    class="text-red-600 hover:text-red-800 text-xs font-semibold underline">
                    {{ item.status === 'Approved' ? 'Withdraw' : 'Cancel' }}
                </button>
             </template>
        </BaseDataTable>

        <Modal :show="showModal" @close="showModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Request Overtime</h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <BaseInput type="date" label="Date" v-model="form.date" required />

                    <div class="grid grid-cols-2 gap-4">
                        <BaseSelect 
                            label="Project (Optional)" 
                            v-model="form.project_id" 
                            :options="projects.map(p => ({value: p.id, label: p.name}))" 
                        />
                        <BaseSelect 
                            label="Task (Optional)" 
                            v-model="form.task_id" 
                            :options="tasks.map(t => ({value: t.id, label: t.title}))"
                            :disabled="!tasks.length"
                        />
                    </div>

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
