<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    loans: Array
});

const showingLoan = ref(null);

const viewLoan = (loan) => {
    showingLoan.value = loan;
};

const formatCurrency = (amt) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amt);

const approveLoan = () => {
    if(!confirm('Confirm Approval? Schedule will be generated.')) return;
    
    router.post(route('hr.loans.approve', showingLoan.value.id), {}, {
        onSuccess: () => showingLoan.value = null
    });
};

const rejectLoan = () => {
    const reason = prompt('Reason for rejection:');
    if(!reason) return;
    
    router.post(route('hr.loans.reject', showingLoan.value.id), { reason }, {
        onSuccess: () => showingLoan.value = null
    });
};
</script>

<template>
    <MainLayout title="Loan Management">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Loan Requests</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="loan in loans" :key="loan.id" class="hover:bg-gray-50 cursor-pointer" @click="viewLoan(loan)">
                                <td class="px-6 py-4">
                                     <div class="font-medium text-gray-900">{{ loan.employee?.user?.name || 'Unknown' }}</div>
                                     <div class="text-xs text-gray-500">{{ loan.employee?.employee_id }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ loan.loan_type }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900">{{ formatCurrency(loan.principal_amount) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ new Date(loan.created_at).toLocaleDateString() }}</td>
                                <td class="px-6 py-4">
                                    <span :class="{'bg-yellow-100 text-yellow-800': loan.status==='Pending', 'bg-green-100 text-green-800': loan.status==='Active'}" class="px-2 py-1 rounded text-xs font-bold">{{ loan.status }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Modal :show="!!showingLoan" @close="showingLoan = null">
            <div class="p-6" v-if="showingLoan">
                <h3 class="text-lg font-bold mb-4">Loan Details</h3>
                
                <div class="grid grid-cols-2 gap-4 text-sm mb-6">
                    <div>
                        <span class="text-gray-500 block">Applicant</span>
                        <span class="font-medium">{{ showingLoan.employee?.user?.name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Product</span>
                        <span class="font-medium">{{ showingLoan.loan_type }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Principal</span>
                        <span class="font-bold text-indigo-700 text-lg">{{ formatCurrency(showingLoan.principal_amount) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Proposed Tenure</span>
                        <span class="font-medium">{{ showingLoan.tenure_months }} Months</span>
                    </div>
                     <div>
                        <span class="text-gray-500 block">Interest Rate</span>
                        <span class="font-medium">{{ showingLoan.interest_rate_applied }}% ({{ showingLoan.interest_type_applied }})</span>
                    </div>
                     <div>
                        <span class="text-gray-500 block">Est. EMI</span>
                        <span class="font-medium">{{ formatCurrency(showingLoan.monthly_installment) }}</span>
                    </div>
                    <div class="col-span-2 bg-gray-50 p-3 rounded">
                        <span class="text-gray-500 block text-xs">Reason</span>
                        <p>{{ showingLoan.reason }}</p>
                    </div>
                    <div class="col-span-2 border-t border-gray-100 pt-3 flex items-center gap-2">
                        <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center text-green-600">✓</div>
                        <span class="text-xs text-gray-500">Digitally Signed by {{ showingLoan.employee?.user?.name }} on {{ new Date(showingLoan.signed_at).toLocaleString() }}</span>
                    </div>
                </div>

                <div class="flex justify-end gap-3" v-if="showingLoan.status === 'Pending'">
                    <SecondaryButton @click="rejectLoan" class="!bg-red-50 !text-red-600 hover:!bg-red-100 border-red-200">Reject</SecondaryButton>
                    <PrimaryButton @click="approveLoan">Approve & Disburse</PrimaryButton>
                </div>
                <div class="flex justify-end gap-3" v-else>
                     <SecondaryButton @click="showingLoan = null">Close</SecondaryButton>
                </div>
            </div>
        </Modal>
    </MainLayout>
</template>
