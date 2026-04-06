<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const loans = ref([]);
const loading = ref(true);
const selectedLoan = ref(null);
const showRiskModal = ref(false);

const fetchQueue = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('hr.loans.api.queue'));
        loans.value = res.data;
    } finally {
        loading.value = false;
    }
};

onMounted(fetchQueue);

const openRiskAnalysis = (loan) => {
    selectedLoan.value = loan;
    showRiskModal.value = true;
};

const approve = (id) => {
    if(confirm('Approve this loan?')) {
        router.post(route('hr.loans.approve', id), {}, { onSuccess: () => { showRiskModal.value = false; fetchQueue(); }});
    }
};

const reject = (id) => {
    const reason = prompt("Rejection Reason:");
    if(reason) {
        router.post(route('hr.loans.reject', id), { reason }, { onSuccess: () => { showRiskModal.value = false; fetchQueue(); }});
    }
};

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val || 0);
</script>

<template>
    <div>
        <div v-if="loading" class="p-8 text-center text-gray-500">Loading Queue...</div>
        <div v-else>
            <div v-if="loans.length === 0" class="text-center p-12 bg-gray-50 rounded-lg text-gray-500">
                No pending loan applications.
            </div>
            <table v-else class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Risk Score</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="loan in loans" :key="loan.id">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ loan.employee?.user?.name }}</div>
                            <div class="text-xs text-gray-500">{{ loan.employee?.designation }}</div>
                        </td>
                         <td class="px-6 py-4">
                            <div class="text-sm font-bold">{{ loan.loan_type }}</div>
                            <div class="text-sm text-gray-600">{{ formatCurrency(loan.principal_amount) }} for {{ loan.tenure_months }} Mo.</div>
                        </td>
                        <td class="px-6 py-4">
                            <span v-if="loan.risk_analysis" 
                                :class="[
                                    'px-2 py-1 text-xs font-bold rounded',
                                    loan.risk_analysis.score > 50 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'
                                ]"
                            >
                                Score: {{ loan.risk_analysis.score }}/100
                            </span>
                            <span v-else class="text-xs text-gray-400">Not Analyzed</span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                             <button @click="openRiskAnalysis(loan)" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View Analysis</button>
                             <button @click="approve(loan.id)" class="px-3 py-1 bg-green-600 text-white rounded text-xs font-bold">Approve</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Risk Analysis Modal -->
        <Modal :show="showRiskModal" @close="showRiskModal = false">
            <div class="p-6" v-if="selectedLoan">
                <h3 class="text-lg font-bold mb-4">Risk Analysis Report</h3>
                
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 p-3 rounded">
                        <div class="text-xs text-gray-500">Requested Amount</div>
                        <div class="font-bold text-lg">{{ formatCurrency(selectedLoan.principal_amount) }}</div>
                    </div>
                     <div class="bg-gray-50 p-3 rounded">
                        <div class="text-xs text-gray-500">Net Salary</div>
                        <div class="font-bold text-lg">{{ formatCurrency(selectedLoan.risk_analysis?.net_salary) }}</div>
                    </div>
                </div>

                <div class="mb-6">
                    <h4 class="font-medium text-sm text-gray-700 mb-2">Key Risk Indicators</h4>
                    <div class="space-y-2">
                         <div class="flex justify-between text-sm">
                             <span>Debt-to-Income Ratio:</span>
                             <span :class="selectedLoan.risk_analysis?.dti_ratio > 40 ? 'text-red-600 font-bold' : 'text-green-600'">
                                 {{ selectedLoan.risk_analysis?.dti_ratio }}%
                             </span>
                         </div>
                         <div v-for="(flag, i) in selectedLoan.risk_analysis?.flags" :key="i" class="flex items-start text-sm text-red-600 bg-red-50 p-2 rounded">
                             <span class="mr-2">⚠️</span>
                             {{ flag }}
                         </div>
                         <div v-if="!selectedLoan.risk_analysis?.flags?.length" class="text-green-600 text-sm bg-green-50 p-2 rounded">
                             ✅ No critical risk implementation flags detected.
                         </div>
                    </div>
                </div>
                
                <div class="bg-blue-50 p-4 rounded mb-6 border border-blue-100">
                    <span class="font-bold text-blue-800">Recommendation:</span> 
                    {{ selectedLoan.risk_analysis?.recommendation }}
                </div>

                <div class="flex justify-end gap-3 border-t pt-4">
                    <button @click="reject(selectedLoan.id)" class="text-red-600 hover:text-red-800 font-medium text-sm">Reject Application</button>
                    <button @click="approve(selectedLoan.id)" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded font-medium text-sm">Approve Loan</button>
                </div>
            </div>
        </Modal>
    </div>
</template>
