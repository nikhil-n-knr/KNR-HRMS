<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    loans: Array,
    products: Array
});

const getStatusColor = (status) => {
    switch(status) {
        case 'Active': return 'bg-green-100 text-green-800';
        case 'Approved': return 'bg-blue-100 text-blue-800';
        case 'Pending': return 'bg-yellow-100 text-yellow-800';
        case 'Closed': return 'bg-gray-100 text-gray-800';
        case 'Rejected': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const formatCurrency = (amt) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amt);
</script>

<template>
    <MainLayout title="My Loans">
        <div class="px-8 py-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Loans</h2>
                <Link :href="route('employee.loans.create')">
                    <PrimaryButton>Apply for Loan</PrimaryButton>
                </Link>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Active Loans Cards -->
                <div v-if="loans.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-for="loan in loans" :key="loan.id" class="bg-white p-6 rounded-lg shadow border border-gray-100">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">{{ loan.loan_type }}</h3>
                                <div class="text-xs text-gray-500">Total Loan Amount</div>
                                <div class="text-xl font-bold text-indigo-600">{{ formatCurrency(loan.principal_amount) }}</div>
                            </div>
                            <span :class="['px-2 py-1 text-xs font-bold rounded', getStatusColor(loan.status)]">
                                {{ loan.status }}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm mt-4 border-t border-gray-100 pt-4">
                             <div>
                                 <span class="text-gray-500 block">EMI</span>
                                 <span class="font-medium">{{ formatCurrency(loan.monthly_installment) }}</span>
                             </div>
                             <div>
                                 <span class="text-gray-500 block">Balance</span>
                                 <!-- Naive balance calc for display -->
                                 <span class="font-medium text-gray-800">
                                     {{ loan.repayments.find(r => r.status === 'Pending') ? formatCurrency(loan.repayments.filter(r => r.status === 'Pending').reduce((sum, r) => sum + parseFloat(r.principal_component), 0) ) : '0' }}
                                 </span>
                             </div>
                             <div>
                                 <span class="text-gray-500 block">Tenure</span>
                                 <span class="font-medium">{{ loan.tenure_months }} Months</span>
                             </div>
                             <div>
                                 <span class="text-gray-500 block">Interest Rate</span>
                                 <span class="font-medium">{{ loan.interest_rate_applied }}%</span>
                             </div>
                        </div>

                        <!-- Progress Bar (Approx) -->
                        <div class="mt-6">
                             <div class="text-xs text-gray-500 flex justify-between mb-1">
                                 <span>Repayment Progress</span>
                                 <span>{{ ((loan.tenure_months - loan.repayments.filter(r => r.status === 'Pending').length) / loan.tenure_months * 100).toFixed(0) }}%</span>
                             </div>
                             <div class="w-full bg-gray-200 rounded-full h-2">
                                  <div class="bg-indigo-600 h-2 rounded-full" :style="{ width: ((loan.tenure_months - loan.repayments.filter(r => r.status === 'Pending').length) / loan.tenure_months * 100) + '%' }"></div>
                             </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12 bg-white rounded-lg border border-dashed text-gray-500">
                    You have no active loans.
                </div>

            </div>
        </div>
    </MainLayout>
</template>
