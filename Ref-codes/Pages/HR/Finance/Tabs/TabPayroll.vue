<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';

const props = defineProps({ 
    payrolls: Array,
    selectedPayroll: Object,
    payslips: Array
});

const editingPayslip = ref(null);
const showEditModal = ref(false);
const editForm = useForm({
    type: 'deduction',
    adjustment_amount: '',
    reason: ''
});

const openEditModal = (slip) => {
    if (props.selectedPayroll.status !== 'Draft') return;
    editingPayslip.value = slip;
    editForm.reset();
    showEditModal.value = true;
};

const submitAdjustment = () => {
    editForm.post(route('finance.payslip.update', editingPayslip.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        }
    });
};

const viewPayroll = (id) => {
    router.get(route('finance.hub'), { tab: 'payroll', payroll_id: id }, { preserveState: true });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amount);
};
</script>
<template>
    <div class="space-y-6">
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6 mb-6 flex justify-between items-center">
            <div>
                 <h2 class="text-lg font-bold text-indigo-900">Payroll Processor</h2>
                 <p class="text-sm text-indigo-700 mt-1">Manage monthly payroll runs, generate payslips, and handle disbursements.</p>
            </div>
            <a href="/hr/payroll" class="px-6 py-3 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 font-bold flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                Open Full Payroll Suite
            </a>
        </div>

        <div class="flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Recent Runs (Drill-down)</h3>
            <div class="flex space-x-2">
                 <button v-if="selectedPayroll" @click="viewPayroll(null)" class="text-indigo-600 text-sm hover:underline font-bold px-4 py-2">
                    &larr; Back to List
                </button>
            </div>
        </div>

        <!-- Payroll Runs List -->
        <div v-if="!selectedPayroll" class="bg-white rounded-lg shadow overflow-hidden border border-gray-100">
             <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                         <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payout</th>
                         <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="payroll in payrolls" :key="payroll.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ payroll.batch_name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ payroll.month }}/{{ payroll.year }}</td>
                         <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-bold rounded-full"
                                  :class="{'bg-yellow-100 text-yellow-800': payroll.status === 'Draft', 'bg-green-100 text-green-800': payroll.status === 'Paid'}">
                                {{ payroll.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ formatCurrency(payroll.total_payout) }}</td>
                        <td class="px-6 py-4 text-sm">
                             <button @click="viewPayroll(payroll.id)" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                {{ payroll.status === 'Draft' ? 'Process / Edit' : 'View' }}
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Selected Payroll (Payslip Grid) -->
        <div v-else class="bg-white rounded-lg shadow border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <div>
                     <h3 class="font-bold text-gray-900">{{ selectedPayroll.batch_name }}</h3>
                     <span class="text-xs text-gray-500 uppercase tracking-wider">{{ selectedPayroll.status }} Mode</span>
                </div>
                <div class="text-sm">
                    <span class="text-gray-500">Total Payout: </span>
                    <span class="font-bold text-gray-900">{{ formatCurrency(selectedPayroll.total_payout || 0) }}</span>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                 <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gross</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase cursor-pointer text-indigo-600" title="Click to edit">Deductions ✎</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase cursor-pointer text-indigo-600" title="Click to edit">Net Pay ✎</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="slip in payslips" :key="slip.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ slip.employee?.user?.name }}
                                <div class="text-xs text-gray-400">{{ slip.employee?.employee_id }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-right text-gray-700">{{ formatCurrency(slip.gross_earnings) }}</td>
                            
                             <!-- Editable Deduction -->
                            <td class="px-6 py-4 text-sm text-right text-red-600 cursor-pointer hover:bg-red-50 border-gray-100 border-l" @click="openEditModal(slip)">
                                - {{ formatCurrency(slip.gross_deductions) }}
                            </td>
                            
                            <!-- Editable Net -->
                            <td class="px-6 py-4 text-sm text-right text-green-700 font-bold cursor-pointer hover:bg-green-50 border-gray-100 border-l" @click="openEditModal(slip)">
                                {{ formatCurrency(slip.net_pay) }}
                            </td>
                        </tr>
                    </tbody>
                 </table>
            </div>
        </div>

        <!-- Inline Edit Modal -->
        <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
             <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-bold mb-2">Manual Override</h3>
                <p class="text-sm text-gray-500 mb-4">Adjusting payslip for {{ editingPayslip?.employee?.user?.name }}</p>
                
                <form @submit.prevent="submitAdjustment" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <select v-model="editForm.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                            <option value="deduction">One-time Deduction</option>
                            <option value="allowance">One-time Allowance</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                        <input type="number" step="0.01" v-model="editForm.adjustment_amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="e.g. 500">
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700">Reason / Remarks</label>
                        <textarea v-model="editForm.reason" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="e.g. Penalty for damage"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium" :disabled="editForm.processing">Save Adjustment</button>
                    </div>
                </form>
             </div>
        </div>
    </div>
</template>
