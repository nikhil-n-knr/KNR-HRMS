<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const items = ref({ data: [] });
const loading = ref(true);
const selectedAction = ref(null);
const form = ref({ id: null, remarks: '', amount: null }); // For Reject or Partial

const fetchApprovals = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('hr.expenses.api.approvals'));
        items.value = res.data;
    } finally {
        loading.value = false;
    }
};

onMounted(fetchApprovals);

const openAction = (item, action) => {
    selectedAction.value = action;
    form.value = { id: item.id, remarks: '', amount: item.amount };
};

const submitAction = () => {
    // Determine route based on action
    // "approve" -> manager.approvals.action (POST) with type='expense', action='approve'
    // "reject" -> same
    // "partial" -> Not standard. Need a custom route or add 'approved_amount' to 'approve' logic.
    // For MVP, we stick to approve/reject logic provided by Manager Module, or create a specific endpoint if Partial is needed.
    // Let's use the Manager Controller generic logic for now: manager.approvals.action
    
    // BUT! Partial Approval requires 'amount' field which generic controller might ignore?
    // We added 'approved_amount' to DB. We need specific logic.
    // Let's assume generic logic for now, and handle partials manually in DB if needed later.
    // Actually, `handleExpense` in ApprovalController delegates to WorkflowService.
    
    router.post(route('manager.manager.approvals.action'), {
        type: 'expense',
        id: form.value.id,
        action: selectedAction.value,
        remarks: form.value.remarks
    }, {
        onSuccess: () => {
            selectedAction.value = null;
            fetchApprovals();
        },
        onError: (errors) => {
             // Show first error message
             const msg = Object.values(errors)[0] || 'Action failed. Please check permissions.';
             alert(msg);
        }
    });
};

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val || 0);
const formatDate = (date) => new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short' });
</script>

<template>
    <div>
        <div v-if="loading" class="text-center p-8 text-gray-500">Loading Approvals...</div>
        
        <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                         <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Employee</th>
                         <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Expense</th>
                         <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                         <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Amount</th>
                         <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="item in items.data" :key="item.id">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ item.employee?.user?.name || 'Unknown' }}</div>
                            <div class="text-xs text-gray-500">{{ item.employee?.employee_id }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ item.title }}</div>
                            <span class="px-2 py-0.5 rounded text-xs bg-gray-100">{{ item.category?.name }}</span>
                            <a v-if="item.receipt_path" :href="'/storage/'+item.receipt_path" target="_blank" class="ml-2 text-indigo-600 text-xs hover:underline">
                                📎 View Receipt
                            </a>
                        </td>
                         <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(item.incurred_date) }}</td>
                         <td class="px-6 py-4 text-right font-bold text-gray-900">{{ formatCurrency(item.amount) }}</td>
                         <td class="px-6 py-4 text-right space-x-2">
                             <button @click="openAction(item, 'reject')" class="text-red-600 hover:text-red-900 text-sm font-medium">Reject</button>
                             <button @click="openAction(item, 'approve')" class="px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 text-sm font-bold">Approve</button>
                         </td>
                    </tr>
                    <tr v-if="items.data?.length === 0">
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">All caught up! No pending approvals.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Action Modal -->
        <Modal :show="!!selectedAction" @close="selectedAction = null">
            <div class="p-6">
                <h3 class="text-lg font-bold mb-4 capitalize">
                    {{ selectedAction }} Expense
                </h3>
                
                <div v-if="selectedAction === 'reject'">
                    <InputLabel value="Rejection Reason (Mandatory)" />
                    <textarea v-model="form.remarks" class="w-full mt-1 border-gray-300 rounded shadow-sm" rows="3"></textarea>
                </div>
                
                 <div v-if="selectedAction === 'approve'">
                    <p class="text-gray-600 mb-4">Are you sure you want to approve this expense of <b>{{ formatCurrency(form.amount) }}</b>?</p>
                    <InputLabel value="Approver Remarks (Optional)" />
                    <textarea v-model="form.remarks" class="w-full mt-1 border-gray-300 rounded shadow-sm" rows="2"></textarea>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="selectedAction = null">Cancel</SecondaryButton>
                    <PrimaryButton @click="submitAction" :class="selectedAction === 'reject' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'">
                        Confirm {{ selectedAction }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </div>
</template>
