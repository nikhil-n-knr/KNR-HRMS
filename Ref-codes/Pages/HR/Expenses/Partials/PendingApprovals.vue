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
        
        <div v-else class="overflow-x-auto rounded-xl border border-gray-100 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-blue-50">
                    <tr>
                         <th class="w-[22%] px-6 py-3.5 text-left text-[13px] font-black text-blue-900 uppercase tracking-widest">Employee</th>
                         <th class="w-[34%] px-6 py-3.5 text-left text-[13px] font-black text-blue-900 uppercase tracking-widest">Expense</th>
                         <th class="w-[14%] px-6 py-3.5 text-left text-[13px] font-black text-blue-900 uppercase tracking-widest">Date</th>
                         <th class="w-[15%] px-6 py-3.5 text-left text-[13px] font-black text-blue-900 uppercase tracking-widest">Amount</th>
                         <th class="w-[15%] px-6 py-3.5 text-left text-[13px] font-black text-blue-900 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <tr v-for="item in items.data" :key="item.id" class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">{{ item.employee?.user?.name || 'Unknown' }}</div>
                            <div class="text-xs font-medium text-gray-500">{{ item.employee?.employee_id }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-gray-900">{{ item.title }}</div>
                            <span class="mt-1 inline-flex px-2 py-0.5 rounded-md text-[11px] font-bold bg-gray-100 text-gray-600">{{ item.category?.name }}</span>
                            <a v-if="item.receipt_path" :href="'/storage/'+item.receipt_path" target="_blank" class="ml-2 text-indigo-600 text-[11px] font-semibold hover:underline">
                                📎 View Receipt
                            </a>
                        </td>
                         <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(item.incurred_date) }}</td>
                         <td class="px-6 py-4 text-sm font-black text-gray-900">{{ formatCurrency(item.amount) }}</td>
                         <td class="px-6 py-4">
                             <div class="flex items-center gap-2">
                                 <button @click="openAction(item, 'reject')" class="px-2.5 py-1 text-[11px] font-bold text-red-700 bg-red-50 rounded-md hover:bg-red-100 transition-colors">Reject</button>
                                 <button @click="openAction(item, 'approve')" class="px-2.5 py-1 bg-green-100 text-green-700 rounded-md hover:bg-green-200 text-[11px] font-bold transition-colors">Approve</button>
                             </div>
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
