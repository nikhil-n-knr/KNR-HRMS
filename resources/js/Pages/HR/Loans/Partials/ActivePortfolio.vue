<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const loans = ref([]);
const loading = ref(true);

const fetchPortfolio = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('hr.loans.api.portfolio'));
        loans.value = res.data;
    } finally {
        loading.value = false;
    }
};

onMounted(fetchPortfolio);

const foreclose = (id) => {
    if(confirm('Are you certain? This will calculate the final settlement amount and close the loan.')) {
        router.post(route('hr.loans.foreclose', id), {}, { onSuccess: fetchPortfolio });
    }
};

const pause = (id) => {
    if(confirm('Pause this loan for 1 month? Tenure will be extended.')) {
        router.post(route('hr.loans.pause', id), {}, { onSuccess: fetchPortfolio });
    }
};

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val || 0);
</script>

<template>
    <div>
        <div v-if="loading" class="p-8 text-center text-gray-500">Loading Portfolio...</div>
        <table v-else class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Repayment</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="loan in loans" :key="loan.id">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ loan.employee?.user?.name }}</div>
                        <div class="text-xs text-gray-500">Loan ID: #{{ loan.id }}  <span v-if="loan.is_paused" class="text-orange-600 bg-orange-100 px-1 rounded">PAUSED</span></div>
                    </td>
                     <td class="px-6 py-4">
                        <div class="text-sm font-bold">{{ formatCurrency(loan.foreclosure_details?.amount) }}</div>
                        <div class="text-xs text-gray-500">Remaining Principal</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                             <div class="w-24 bg-gray-200 rounded-full h-2">
                                 <div class="bg-indigo-600 h-2 rounded-full" :style="'width: ' + ((loan.paid_installments / loan.tenure_months)*100) + '%'"></div>
                             </div>
                             <span class="text-xs text-gray-600">{{ loan.paid_installments }}/{{ loan.tenure_months }}</span>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">Next EMI: {{ formatCurrency(loan.monthly_installment) }}</div>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                         <button @click="pause(loan.id)" v-if="!loan.is_paused" class="text-orange-600 hover:text-orange-800 text-xs font-medium border border-orange-200 px-2 py-1 rounded">⏸ Pause EMI</button>
                         <button @click="foreclose(loan.id)" class="text-indigo-600 hover:text-indigo-900 text-xs font-medium border border-indigo-200 px-2 py-1 rounded">⚡ Foreclose</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
