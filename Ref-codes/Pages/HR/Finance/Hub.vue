<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, computed } from 'vue';

// Tabs
import TabCompensation from './Tabs/TabCompensation.vue';
import TabVariablePay from './Tabs/TabVariablePay.vue';
import TabPayroll from './Tabs/TabPayroll.vue';
import TabAppraisals from './Tabs/TabAppraisals.vue';
import TabExit from './Tabs/TabExit.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    tab: String,
    structures: Array,
    employees: Array,
    payrolls: Array,
    exits: Array
});

const switchTab = (id) => {
    router.get(route('hr.finance.hub'), { tab: id }, { 
        preserveState: true, 
        preserveScroll: true,
        only: ['tab', id === 'compensation' ? 'structures' : (id === 'payroll' ? 'payrolls' : '...')] // Simple optimization
    });
};

const tabs = [
    { id: 'compensation', label: 'Compensation & Offers', icon: 'CurrencyDollarIcon' },
    { id: 'variable', label: 'Variable Pay Engine', icon: 'GiftIcon' },
    { id: 'payroll', label: 'Payroll Processor', icon: 'CalculatorIcon' },
    { id: 'appraisals', label: 'Appraisals', icon: 'TrendingUpIcon' },
    { id: 'exit', label: 'Exit & FNF', icon: 'LogoutIcon' }
];

</script>

<template>
    <Head title="HR Finance Hub" />

    <div class="flex flex-col h-screen overflow-hidden bg-gray-50">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 px-6 py-4 flex-shrink-0 z-10">
            <div class="flex justify-between items-center mb-4">
                 <h1 class="text-2xl font-bold text-gray-900">HR Finance Hub</h1>
                 <div class="flex gap-2">
                     <button class="px-3 py-1 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50">Settings</button>
                     <button class="px-3 py-1 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700">Analytics</button>
                 </div>
            </div>
            
            <!-- Tab Navigation -->
            <div class="flex gap-6 border-b border-gray-100 -mb-4">
                <button 
                    v-for="t in tabs" 
                    :key="t.id"
                    @click="switchTab(t.id)"
                    class="pb-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2"
                    :class="tab === t.id ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
                >
                    {{ t.label }}
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6">
            <TabCompensation v-if="tab === 'compensation'" :structures="structures" />
            <TabVariablePay v-if="tab === 'variable'" :employees="employees" />
            <TabPayroll v-if="tab === 'payroll'" :payrolls="payrolls" />
            <TabAppraisals v-if="tab === 'appraisals'" :employees="employees" />
            <TabExit v-if="tab === 'exit'" :exits="exits" />
        </div>
    </div>
</template>
