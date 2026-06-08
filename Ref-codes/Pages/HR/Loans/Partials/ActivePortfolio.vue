<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const loans = ref([]);
const loading = ref(true);

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val || 0);

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

const portfolioStats = computed(() => {
    const activeLoans = loans.value || [];
    const totalBalance = activeLoans.reduce((sum, loan) => sum + Number(loan.foreclosure_details?.amount || 0), 0);
    const monthlyEmi = activeLoans.reduce((sum, loan) => sum + Number(loan.monthly_installment || 0), 0);
    const paused = activeLoans.filter((loan) => loan.is_paused).length;

    return [
        {
            label: 'Active Loans',
            value: activeLoans.length,
            helper: `${paused} paused`,
            accent: 'bg-blue-600',
            helperClass: 'text-blue-600',
        },
        {
            label: 'Outstanding Balance',
            value: formatCurrency(totalBalance),
            helper: 'remaining principal',
            accent: 'bg-yellow-500',
            helperClass: 'text-gray-400',
        },
        {
            label: 'Paused Loans',
            value: paused,
            helper: 'EMI currently on hold',
            accent: 'bg-rose-500',
            helperClass: 'text-rose-500',
        },
        {
            label: 'Monthly EMI Flow',
            value: formatCurrency(monthlyEmi),
            helper: 'expected deduction',
            accent: 'bg-emerald-500',
            helperClass: 'text-emerald-600',
        },
    ];
});

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

const repaymentPercent = (loan) => {
    const tenure = Number(loan.tenure_months || 0);
    const paid = Number(loan.paid_installments || 0);

    if (!tenure) {
        return 0;
    }

    return Math.min(100, Math.max(0, Math.round((paid / tenure) * 100)));
};
</script>

<template>
   <div class="space-y-6">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div
            v-for="stat in portfolioStats"
            :key="stat.label"
            class="relative min-h-[120px] overflow-hidden rounded-xl border border-gray-100 bg-white px-4 py-4 shadow-[0_1px_6px_rgba(15,23,42,0.10)]"
        >
            <div>
                <p class="text-[14px] font-bold uppercase tracking-[0.12em] text-gray-500">
                    {{ stat.label }}
                </p>

                <p class="mt-3 text-[22px] font-extrabold leading-none text-gray-900">
                    {{ stat.value }}
                </p>

                <p :class="[stat.helperClass, 'mt-2 text-sm font-semibold']">
                    {{ stat.helper }}
                </p>
            </div>

            <span :class="[stat.accent, 'absolute inset-y-0 right-0 w-1']"></span>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-100 bg-white px-5 py-4">
            <div>
                <h3 class="text-base font-black text-gray-900">Active Loan Portfolio</h3>
                <p class="text-xs font-medium text-gray-500">
                    Monitor balances, EMI progress, pauses, and settlement actions.
                </p>
            </div>
        </div>

        <div v-if="loading" class="space-y-3 p-5">
            <div v-for="i in 4" :key="i" class="h-16 animate-pulse rounded-lg bg-gray-100"></div>
        </div>

        <div v-else-if="loans.length === 0" class="px-6 py-14 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2Z" />
                </svg>
            </div>
            <h4 class="mt-4 text-sm font-black text-gray-900">No active loans</h4>
            <p class="mt-1 text-sm text-gray-500">
                Approved and disbursed employee loans will appear here.
            </p>
        </div>

        <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-blue-50">
                    <tr>
                        <th class="w-[26%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-widest text-blue-900">Employee</th>
                        <th class="w-[20%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-widest text-blue-900">Balance</th>
                        <th class="w-[28%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-widest text-blue-900">Repayment</th>
                        <th class="w-[12%] px-4 py-3 text-left text-[11px] font-black uppercase tracking-widest text-blue-900">Status</th>
                        <th class="w-[14%] px-4 py-3 text-right text-[11px] font-black uppercase tracking-widest text-blue-900">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    <tr v-for="loan in loans" :key="loan.id" class="transition-colors hover:bg-blue-50/30">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-xs font-bold text-indigo-700">
                                    {{ (loan.employee?.user?.name || 'N').charAt(0).toUpperCase() }}
                                </div>
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-bold text-gray-900">
                                        {{ loan.employee?.user?.name || 'Unknown Employee' }}
                                    </div>
                                    <div class="mt-0.5 text-xs text-gray-500">
                                        Loan ID: #{{ loan.id }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-3">
                            <div class="text-sm font-bold text-gray-900">
                                {{ formatCurrency(loan.foreclosure_details?.amount) }}
                            </div>
                            <div class="mt-0.5 text-xs text-gray-500">
                                Remaining principal
                            </div>
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-28 overflow-hidden rounded-full bg-gray-100">
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-blue-500 to-emerald-500"
                                        :style="{ width: repaymentPercent(loan) + '%' }"
                                    ></div>
                                </div>
                                <span class="text-xs font-bold text-gray-700">
                                    {{ loan.paid_installments || 0 }}/{{ loan.tenure_months || 0 }}
                                </span>
                            </div>
                            <div class="mt-1 text-xs text-gray-500">
                                Next EMI: {{ formatCurrency(loan.monthly_installment) }}
                            </div>
                        </td>

                        <td class="px-4 py-3">
                            <span
                                v-if="loan.is_paused"
                                class="inline-flex rounded-md bg-orange-100 px-2 py-0.5 text-[10px] font-bold uppercase text-orange-700"
                            >
                                Paused
                            </span>
                            <span
                                v-else
                                class="inline-flex rounded-md bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-700"
                            >
                                Active
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <button
                                    v-if="!loan.is_paused"
                                    @click="pause(loan.id)"
                                    class="inline-flex h-7 items-center rounded-lg border border-orange-100 bg-orange-50 px-2 text-[10px] font-bold text-orange-700 hover:bg-orange-100"
                                >
                                    Pause
                                </button>

                                <button
                                    @click="foreclose(loan.id)"
                                    class="inline-flex h-7 items-center rounded-lg border border-indigo-100 bg-indigo-50 px-2 text-[10px] font-bold text-indigo-700 hover:bg-indigo-100"
                                >
                                    Close
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</template>
