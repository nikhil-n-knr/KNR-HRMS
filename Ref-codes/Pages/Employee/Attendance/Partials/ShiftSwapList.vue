<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import Combobox from '@/Components/Combobox.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { PlusIcon, ArrowsRightLeftIcon } from '@heroicons/vue/24/outline';
import { useToastStore } from '@/stores/toast';
import BaseDataTable from '@/Components/BaseDataTable.vue';

const props = defineProps({
    data: Object, // Paginated
    options: Object // Colleagues, Shifts, MyShiftId
});

const emit = defineEmits(['page-change']);

const toast = useToastStore();
const showModal = ref(false);
const processing = ref(false);

// Map Options
const colleagues = computed(() => props.options?.colleagues || []);
const shifts = computed(() => props.options?.shifts || []);
const myShiftId = computed(() => props.options?.my_shift_id);
const authEmployeeId = computed(() => props.options?.employee_id);

const form = useForm({
    recipient_id: '',
    shift_id_from: myShiftId.value,
    shift_id_to: '',
    date: new Date().toISOString().split('T')[0]
});

// Auto-fill Logic (Same as before)
const handleColleagueSelect = (member) => {
    form.recipient_id = member.id;
    if (member.current_shift_id) {
        form.shift_id_to = member.current_shift_id;
    }
};

const openCreate = () => {
    form.reset();
    form.shift_id_from = myShiftId.value; 
    showModal.value = true;
};

const submit = () => {
    processing.value = true;
    form.post(route('attendance.swaps.store'), { // Keep existing store route
        onSuccess: () => {
            showModal.value = false;
            toast.success("Swap requested successfully");
            processing.value = false;
        },
        onError: () => processing.value = false
    });
};

const columns = {
    requester: { label: 'Requester', class: 'text-left' }, // Actually usually me, but good for context
    permission: { label: 'Swap With', class: 'text-left' },
    shift_from: { label: 'My Shift', class: 'text-left' }, 
    shift_to: { label: 'Target Shift', class: 'text-left' },
    date: { label: 'Date', class: 'text-center' },
    status: { label: 'Status', class: 'text-center' },
    actions: { label: '', class: 'text-right' }
};

const isIncoming = (item) => {
    return item.recipient_id === authEmployeeId.value;
};

const isOutgoing = (item) => {
    return item.requester_id === authEmployeeId.value;
};

const actionForm = useForm({
    action: ''
});

const handleAccept = (item) => {
    if (!confirm('Are you sure you want to accept this shift swap request?')) return;
    actionForm.action = 'accept';
    actionForm.put(route('attendance.swaps.update', item.id), {
        onSuccess: () => toast.success('Shift swap accepted'),
        preserveScroll: true
    });
};

const handleReject = (item) => {
    if (!confirm('Are you sure you want to reject this shift swap request?')) return;
    actionForm.action = 'reject';
    actionForm.put(route('attendance.swaps.update', item.id), {
        onSuccess: () => toast.success('Shift swap rejected'),
        preserveScroll: true
    });
};

const cancelForm = useForm({
    type: 'swaps',
    id: null
});

const handleCancel = (item) => {
    if (!confirm('Are you sure you want to cancel this shift swap request?')) return;
    cancelForm.id = item.id;
    cancelForm.post(route('attendance.requests.cancel'), {
        onSuccess: () => toast.success('Shift swap request cancelled'),
        preserveScroll: true
    });
};

// Transform data for table if needed
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
        <!-- Actions -->
        <div class="flex justify-end">
             <PrimaryButton @click="openCreate" class="flex items-center gap-2">
                <ArrowsRightLeftIcon class="w-4 h-4" /> Request Swap
            </PrimaryButton>
        </div>

        <BaseDataTable v-if="data" :columns="columns" :data="data.data" :pagination="data" @page-change="page => $emit('page-change', page)">
            <template #cell-requester="{ item }">
                <span class="text-sm font-medium">
                    {{ isOutgoing(item) ? 'Me' : (item.requester?.user?.name || '-') }}
                </span>
            </template>
            <template #cell-permission="{ item }">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-700">
                        {{ isIncoming(item) ? 'M' : (item.recipient?.user?.name?.charAt(0) || '?') }}
                    </div>
                    <span class="text-sm font-medium">
                        {{ isIncoming(item) ? 'Me' : (item.recipient?.user?.name || '-') }}
                    </span>
                </div>
            </template>
            <template #cell-shift_from="{ item }">
                <span class="text-xs font-medium">{{ item.shift_from?.name }}</span>
            </template>
             <template #cell-shift_to="{ item }">
                <span class="text-xs font-medium">{{ item.shift_to?.name }}</span>
            </template>
            <template #cell-date="{ item }">
                <span class="text-sm text-gray-600">{{ formatDate(item.date) }}</span>
            </template>
            <template #cell-status="{ item }">
                <span class="px-2 py-0.5 rounded text-xs font-bold" 
                    :class="{
                        'bg-yellow-100 text-yellow-800': ['Pending', 'pending'].includes(item.status),
                        'bg-blue-100 text-blue-800': ['Accepted', 'accepted', 'Requested', 'requested'].includes(item.status),
                        'bg-green-100 text-green-800': ['Approved', 'approved'].includes(item.status),
                        'bg-red-100 text-red-800': ['Rejected', 'rejected'].includes(item.status),
                        'bg-gray-100 text-gray-800': ['cancelled', 'Cancelled', 'Cancellation Requested'].includes(item.status)
                    }">
                    {{ item.status }}
                </span>
            </template>
            <template #cell-actions="{ item }">
                <div class="flex items-center justify-end gap-2">
                    <!-- Incoming Pending Actions -->
                    <template v-if="isIncoming(item) && ['Pending', 'pending'].includes(item.status)">
                        <button 
                            @click="handleAccept(item)" 
                            class="px-2.5 py-1.5 text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg transition"
                        >
                            Accept
                        </button>
                        <button 
                            @click="handleReject(item)" 
                            class="px-2.5 py-1.5 text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-lg transition"
                        >
                            Reject
                        </button>
                    </template>
                    
                    <!-- Outgoing Pending Actions -->
                    <template v-if="isOutgoing(item) && ['Pending', 'pending'].includes(item.status)">
                        <button 
                            @click="handleCancel(item)" 
                            class="px-2.5 py-1.5 text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-lg transition"
                        >
                            Cancel
                        </button>
                    </template>
                </div>
            </template>
        </BaseDataTable>

        <!-- Modal -->
        <Modal :show="showModal" @close="showModal = false">
            <div class="p-6">
                 <h3 class="text-lg font-bold text-gray-800 mb-4">Request Shift Swap</h3>
                 <form @submit.prevent="submit" class="space-y-4">
                     <!-- Colleague Select -->
                     <div>
                        <InputLabel value="Swap With (Colleague)" />
                        <Combobox 
                            :items="colleagues" 
                            v-model="form.recipient_id" 
                            placeholder="Search colleague..." 
                            @update:modelValue="id => { const m = colleagues.find(c => c.id === id); if(m) handleColleagueSelect(m); }"
                        />
                         <InputError :message="form.errors.recipient_id" />
                    </div>

                    <!-- Date -->
                     <div>
                        <InputLabel value="Date" />
                        <TextInput type="date" v-model="form.date" class="w-full" required />
                         <InputError :message="form.errors.date" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                             <InputLabel value="My Shift" />
                             <BaseSelect v-model="form.shift_id_from" :options="shifts.map(s => ({value: s.id, label: s.name}))" class="w-full" disabled />
                        </div>
                        <div>
                             <InputLabel value="Target Shift" />
                             <BaseSelect v-model="form.shift_id_to" :options="shifts.map(s => ({value: s.id, label: s.name}))" class="w-full" disabled />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="form.processing">Send Request</PrimaryButton>
                    </div>
                 </form>
            </div>
        </Modal>
    </div>
</template>
