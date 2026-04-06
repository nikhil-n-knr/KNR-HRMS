<template>
    <div class="border-b border-gray-200 bg-white px-4 sm:px-6 mb-6">
        <div class="-mb-px flex space-x-8 overflow-x-auto">
            <Link 
                v-for="tab in tabs" 
                :key="tab.name"
                :href="tab.route"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200"
                :class="[
                    isCurrent(tab)
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                ]"
            >
                {{ tab.name }}
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const tabs = [
    { name: 'Payroll Dashboard', route: route('hr.payroll.index'), active: 'hr.payroll.index' },
    { name: 'Appraisal & Corrections', route: route('hr.payroll.bulk'), active: 'hr.payroll.bulk' }, 
    { name: 'Bulk Bank Update', route: route('hr.payroll.bulk-bank'), active: 'hr.payroll.bulk-bank' },
    { name: 'Compliance Hub', route: route('hr.compliance.index'), active: 'hr.compliance.*' },
    { name: 'Loans & Advances', route: route('hr.loans.index'), active: 'hr.loans.*' },
    { name: 'Command Center', route: route('hr.analytics.command-center'), active: 'hr.analytics.command-center' },
    { name: 'Strategic Intelligence', route: route('hr.analytics.strategic'), active: 'hr.analytics.strategic' }
];

const isCurrent = (tab) => {
    return route().current(tab.active);
};
</script>
