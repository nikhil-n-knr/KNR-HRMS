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
    <div class="space-y-6">

        <!-- Header Card -->
        <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-base font-black text-gray-900">Ready for Disbursement</h3>
                <p class="text-xs text-gray-500 mt-1">
                    Approved loans awaiting transfer to employee accounts.
                </p>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="p-6 text-center text-sm text-gray-500">
                Loading disbursement queue...
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">

                    <thead class="bg-blue-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-widest text-blue-900">
                                Beneficiary
                            </th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-widest text-blue-900">
                                Amount
                            </th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-widest text-blue-900">
                                Bank Details
                            </th>
                            <th class="px-4 py-3 text-right text-[11px] font-black uppercase tracking-widest text-blue-900">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        <tr
                            v-for="item in items"
                            :key="item.id"
                            class="hover:bg-blue-50/30 transition"
                        >
                            <!-- Employee -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 flex items-center justify-center rounded-lg bg-indigo-50 text-xs font-bold text-indigo-700">
                                        {{ (item.employee?.user?.name || 'N').charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900">
                                            {{ item.employee?.user?.name || 'Unknown Employee' }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Loan ID: #{{ item.id }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Amount -->
                            <td class="px-4 py-3">
                                <p class="text-sm font-extrabold text-gray-900">
                                    {{ formatCurrency(item.principal_amount) }}
                                </p>
                                <p class="text-xs text-gray-500">Disbursement amount</p>
                            </td>

                            <!-- Bank -->
                            <td class="px-4 py-3 text-xs text-gray-600">
                                <p class="font-semibold text-gray-800">
                                    {{ item.employee?.current_bank_detail?.bank_name || 'N/A' }}
                                </p>
                                <p class="text-gray-500">
                                    {{ item.employee?.current_bank_detail?.account_number || 'No Account Linked' }}
                                </p>
                            </td>

                            <!-- Action -->
                            <td class="px-4 py-3 text-right">
                                <button
                                    @click="markDisbursed(item.id)"
                                    class="inline-flex items-center gap-1 rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition"
                                >
                                    Confirm
                                </button>
                            </td>
                        </tr>

                        <!-- Empty -->
                        <tr v-if="items.length === 0">
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">
                                No approved loans waiting for disbursement.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payroll Sync Card -->
        <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-base font-black text-gray-900">Monthly Recovery Sync</h3>
                <p class="text-xs text-gray-500 mt-1">
                    Sync EMI deductions with payroll system.
                </p>
            </div>

            <div class="p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                
                <div>
                    <h4 class="text-sm font-bold text-gray-800">Push EMIs to Payroll</h4>
                    <p class="text-xs text-gray-500 mt-1">
                        Marks EMIs as deducted and syncs payroll records.
                    </p>
                </div>

                <button
                    @click="syncPayroll"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m-15.357-2a8.001 8.001 0 0015.357-2m0 0H15" />
                    </svg>
                    Run Recovery Sync
                </button>

            </div>

            <div class="px-5 pb-4 text-right">
                <p class="text-xs text-gray-400">Last Sync: Never</p>
            </div>
        </div>

    </div>
</template>
