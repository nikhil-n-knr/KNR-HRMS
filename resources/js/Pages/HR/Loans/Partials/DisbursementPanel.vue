<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const items = ref([]);
const loading = ref(true);

const fetchDisbursement = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('hr.loans.api.disbursement'));
        items.value = res.data.to_disburse;
    } finally {
        loading.value = false;
    }
};

onMounted(fetchDisbursement);

const markDisbursed = (id) => {
    const date = prompt("Enter Disbursement Date (YYYY-MM-DD):", new Date().toISOString().slice(0, 10));
    if(date) {
        router.post(route('hr.loans.disburse', id), { date }, { onSuccess: fetchDisbursement });
    }
};

const syncPayroll = () => {
    if(confirm('Start Payroll Sync for this month?')) {
        router.post(route('hr.loans.sync'));
    }
};

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val || 0);
</script>

<template>
    <div>
        <h3 class="font-bold text-gray-700 mb-4 px-4">Ready for Disbursement</h3>
        <div v-if="loading" class="p-8 text-center text-gray-500">Loading...</div>
        <table v-else class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Beneficiary</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bank Details</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in items" :key="item.id">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ item.employee?.user?.name || 'Unknown Employee' }}</td>
                    <td class="px-6 py-4 font-bold">{{ formatCurrency(item.principal_amount) }}</td>
                    <td class="px-6 py-4 text-xs text-gray-500">
                        {{ item.employee?.current_bank_detail?.bank_name || 'N/A' }} <br/>
                        {{ item.employee?.current_bank_detail?.account_number || 'No Account Linked' }}
                    </td>
                    <td class="px-6 py-4 text-right">
                         <button @click="markDisbursed(item.id)" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-xs font-bold shadow-sm hover:bg-indigo-700">
                             Confirm Transfer
                         </button>
                    </td>
                </tr>
                 <tr v-if="items.length === 0">
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">No approved loans waiting for disbursement.</td>
                </tr>
            </tbody>
        </table>
        
        <div class="mt-8 px-4 border-t pt-8">
            <h3 class="font-bold text-gray-700 mb-4">Monthly Recovery Sync</h3>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 flex justify-between items-center">
                <div>
                    <h4 class="font-bold text-gray-800">Push EMIs to Payroll</h4>
                    <p class="text-sm text-gray-500 mt-1">This will mark current month's EMIs as "Deducted" and sync with Payroll.</p>
                </div>
                <button @click="syncPayroll" class="bg-green-600 text-white px-4 py-2 rounded-lg font-bold shadow-sm hover:bg-green-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m-15.357-2a8.001 8.001 0 0015.357-2m0 0H15"></path></svg>
                    Run Recovery Sync
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-2 text-right">Last Sync: Never</p>
        </div>
    </div>
</template>
