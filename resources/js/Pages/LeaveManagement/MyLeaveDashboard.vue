<script setup>
import { ref, computed } from 'vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import { useToastStore } from '@/stores/toast';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import BaseTextarea from '@/Components/BaseTextarea.vue';
import { PlusIcon, ClockIcon, CheckCircleIcon, XCircleIcon, PaperClipIcon, PencilIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    balances: Array,
    leaves: Object,
    leaveTypes: Array,
    filters: Object
});

const showModal = ref(false);
const showCancelModal = ref(false);
const leaveToCancel = ref(null);

const form = useForm({
    id: null,
    leave_type_id: '',
    start_date: '',
    end_date: '',
    reason: '',
    attachment: null
});

const filterForm = ref({
    status: props.filters?.status || '',
    leave_type_id: props.filters?.leave_type_id || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || ''
});

const applyFilters = () => {
    router.get(route('employee.leave.index'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
        only: ['leaves']
    });
};

const resetFilters = () => {
    filterForm.value = { status: '', leave_type_id: '', start_date: '', end_date: '' };
    applyFilters();
};

const exportUrl = computed(() => {
    const params = new URLSearchParams();
    if(filterForm.value.status) params.append('status', filterForm.value.status);
    if(filterForm.value.leave_type_id) params.append('leave_type_id', filterForm.value.leave_type_id);
    if(filterForm.value.start_date) params.append('start_date', filterForm.value.start_date);
    if(filterForm.value.end_date) params.append('end_date', filterForm.value.end_date);
    params.append('mode', 'my');
    return route('leaves.export') + '?' + params.toString();
});

// Use passed Types prop for dropdown
const availableTypes = computed(() => props.leaveTypes || []);

const selectedType = computed(() => availableTypes.value.find(t => t.id === form.leave_type_id));
const requiresDocument = computed(() => selectedType.value?.requires_document);

const submit = () => {
    if (form.id) {
        // Update (handling file upload via POST + _method: PUT)
        form.transform((data) => ({
            ...data,
            _method: 'PUT'
        })).post(route('leaves.update', form.id), {
            forceFormData: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    } else {
        // Create
        form.post(route('leaves.store'), {
            forceFormData: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    }
};

const openApplyModal = () => {
    form.reset();
    form.id = null;
    showModal.value = true;
};

const openEditModal = (leave) => {
    form.clearErrors(); // clear old errors
    form.id = leave.id;
    form.leave_type_id = leave.leave_type_id;
    // Ensure dates are formatted YYYY-MM-DD for input
    form.start_date = leave.start_date.split('T')[0];
    form.end_date = leave.end_date.split('T')[0];
    form.reason = leave.reason;
    form.attachment = null; // Don't preload file input
    showModal.value = true;
};

const confirmCancel = (leave) => {
    leaveToCancel.value = leave;
    showCancelModal.value = true;
};

const cancelLeave = () => {
    if (!leaveToCancel.value) return;
    router.delete(route('leaves.destroy', leaveToCancel.value.id), {
        onSuccess: () => showCancelModal.value = false,
        onFinish: () => {
            showCancelModal.value = false;
            leaveToCancel.value = null;
        }
    });
};

const isPast = (dateStr) => {
    const end = new Date(dateStr);
    const today = new Date();
    today.setHours(0,0,0,0);
    return end < today;
};

const isFuture = (dateStr) => {
    const start = new Date(dateStr);
    const today = new Date();
    today.setHours(0,0,0,0); // Reset time to start of day
    return start > today;
};
</script>

<template>
    <Head title="Leave Management" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">My Leave</h2>
                <p class="text-sm text-gray-500 mt-1">Check balances and apply for leave.</p>
            </div>
            <PrimaryButton @click="openApplyModal" class="flex items-center gap-2">
                <PlusIcon class="w-5 h-5" /> Apply for Leave
            </PrimaryButton>
        </div>

        <!-- Balances Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-for="balance in balances" :key="balance.id" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-sm font-medium text-gray-500">{{ balance.leave_type?.name || 'Unknown Type' }}</p>
                    <div class="flex items-baseline mt-2">
                         <span class="text-2xl font-bold text-gray-800">{{ balance.total_days - balance.used_days }}</span>
                         <span class="ml-1 text-xs text-gray-400">/ {{ balance.total_days }} days</span>
                    </div>
                </div>
                <!-- Progress Bar -->
                <div class="absolute bottom-0 left-0 h-1 bg-emerald-500 transition-all" :style="{ width: ((balance.used_days / balance.total_days) * 100) + '%' }"></div>
            </div>
        </div>

        <!-- History List -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="text-md font-medium text-gray-800">Request History</h3>
                
                <div class="flex flex-wrap gap-2 items-center">
                    <select v-model="filterForm.status" @change="applyFilters" class="text-xs border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1.5 cursor-pointer">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <select v-model="filterForm.leave_type_id" @change="applyFilters" class="text-xs border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1.5 cursor-pointer">
                         <option value="">All Types</option>
                         <option v-for="type in availableTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                    </select>
                    <input type="date" v-model="filterForm.start_date" @change="applyFilters" class="text-xs border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1" placeholder="From" />
                    <input type="date" v-model="filterForm.end_date" @change="applyFilters" class="text-xs border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1" placeholder="To" />
                    
                    <button @click="resetFilters" class="px-3 py-1.5 bg-gray-200 text-gray-700 text-xs rounded hover:bg-gray-300 transition">Reset</button>
                    
                    <a :href="exportUrl" target="_blank" class="px-3 py-1.5 bg-emerald-600 text-white text-xs rounded hover:bg-emerald-700 transition flex items-center gap-1">
                        Export
                    </a>
                </div>
            </div>
            <div class="divide-y divide-gray-100">
                <div v-for="leave in leaves.data" :key="leave.id" 
                    class="p-4 flex items-center justify-between transition-colors"
                    :class="{ 'opacity-60 bg-gray-50': isPast(leave.end_date) }"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-900">{{ leave.leave_type?.name || 'Unknown Type' }}</span>
                            <span class="text-xs text-gray-500">({{ leave.total_days }} days)</span>
                            <span v-if="isPast(leave.end_date)" class="text-sm uppercase font-bold text-gray-400 border border-gray-200 px-1 rounded">Past</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-0.5">{{ new Date(leave.start_date).toLocaleDateString() }} - {{ new Date(leave.end_date).toLocaleDateString() }}</p>
                        <p class="text-xs text-gray-400 mt-1 italic">"{{ leave.reason }}"</p>
                    </div>
                    <div class="flex items-center gap-3">
                         <span class="px-3 py-1 text-xs rounded-full flex items-center gap-1"
                            :class="{
                                'bg-yellow-100 text-yellow-800': leave.status === 'pending',
                                'bg-green-100 text-green-800': leave.status === 'approved',
                                'bg-red-100 text-red-800': leave.status === 'rejected',
                                'bg-gray-100 text-gray-800': leave.status === 'cancelled',
                            }">
                            <ClockIcon v-if="leave.status==='pending'" class="w-3 h-3"/>
                            <CheckCircleIcon v-if="leave.status==='approved'" class="w-3 h-3"/>
                            <XCircleIcon v-if="leave.status==='rejected'" class="w-3 h-3"/>
                            {{ leave.status }}
                        </span>
                        
                        <button 
                            v-if="leave.status === 'pending'" 
                            @click="openEditModal(leave)" 
                            class="text-xs text-indigo-600 font-medium px-2 py-1 rounded border border-indigo-200 hover:bg-indigo-50 flex items-center gap-1"
                        >
                            <PencilIcon class="w-3 h-3" /> Edit
                        </button>
                        
                        <button 
                            v-if="leave.status === 'pending' || (leave.status === 'approved' && isFuture(leave.start_date))" 
                            @click="confirmCancel(leave)" 
                            class="text-xs font-medium px-2 py-1 rounded border transition-colors"
                            :class="leave.status === 'pending' 
                                ? 'text-red-600 border-red-200 hover:bg-red-50' 
                                : 'text-orange-600 border-orange-200 hover:bg-orange-50'"
                        >
                            {{ leave.status === 'pending' ? 'Cancel' : 'Withdraw' }}
                        </button>
                    </div>
                </div>
                 <div v-if="leaves.data.length === 0" class="p-8 text-center text-gray-400">
                    No leave history found.
                </div>
            </div>
             <!-- Pagination if needed -->
        </div>

        <!-- Apply Modal -->
        <Modal :show="showModal" @close="showModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ form.id ? 'Edit Request' : 'Apply for Leave' }}</h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                         <BaseSelect 
                            label="Leave Type" 
                            v-model="form.leave_type_id" 
                            required 
                            :error="form.errors.leave_type_id"
                        >
                             <option value="" disabled>Select Type</option>
                             <option v-for="type in availableTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                         </BaseSelect>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <BaseInput type="date" label="Start Date" v-model="form.start_date" required :error="form.errors.start_date" />
                        <BaseInput type="date" label="End Date" v-model="form.end_date" required :error="form.errors.end_date" />
                    </div>

                    <div>
                        <BaseTextarea 
                            label="Reason" 
                            v-model="form.reason" 
                            rows="3" 
                            placeholder="Medical, Vacation, etc..." 
                            required 
                            :error="form.errors.reason"
                        />
                    </div>

                    <div v-if="requiresDocument || form.leave_type_id" class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                         <label class="block text-sm font-medium text-gray-700 mb-1">
                            Supporting Document 
                            <span v-if="requiresDocument" class="text-red-500">* Required</span>
                            <span v-else class="text-gray-400 font-normal">(Optional)</span>
                         </label>
                         <div class="flex items-center gap-2">
                            <PaperClipIcon class="w-5 h-5 text-gray-400" />
                            <input 
                                type="file" 
                                @input="form.attachment = $event.target.files[0]"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
                                :required="requiresDocument"
                            />
                         </div>
                         <p v-if="requiresDocument" class="text-xs text-amber-600 mt-1">Please attach medical certificate or relevant document.</p>
                         <p v-if="form.errors.attachment" class="text-xs text-red-600 mt-1">{{ form.errors.attachment }}</p>
                    </div>

                    <div class="flex justify-end gap-3 mt-4">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="form.processing">{{ form.id ? 'Update Request' : 'Submit Application' }}</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Cancel Confirmation Modal -->
        <Modal :show="showCancelModal" @close="showCancelModal = false">
            <div class="p-6">
                 <h3 class="text-lg font-bold text-gray-900 mb-2">
                    {{ leaveToCancel?.status === 'pending' ? 'Cancel Request?' : 'Withdraw Leave?' }}
                 </h3>
                 <p class="text-gray-600 mb-6">
                    {{ leaveToCancel?.status === 'pending' 
                        ? 'Are you sure you want to cancel this pending request?' 
                        : 'Are you sure you want to withdraw this approved leave?' 
                    }}
                    Your leave balance will be refunded.
                 </p>
                 <div class="flex justify-end gap-3">
                     <SecondaryButton @click="showCancelModal = false">No, Keep it</SecondaryButton>
                     <button @click="cancelLeave" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        {{ leaveToCancel?.status === 'pending' ? 'Yes, Cancel Request' : 'Yes, Withdraw Leave' }}
                     </button>
                 </div>
            </div>
        </Modal>
    </div>
</template>
