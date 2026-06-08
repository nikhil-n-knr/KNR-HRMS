<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    payslips: Object,
    filters: Object
});

const search = ref(props.filters.search);

watch(search, debounce((value) => {
    router.get(route('hr.payroll.all-payslips'), { search: value }, { preserveState: true, replace: true });
}, 500));

const formatDate = (date) => new Date(date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
const formatCurrency = (amount) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amount);
</script>

<template>
    <Head title="All Payslips" />

    <MainLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-semibold">Global Payslip Search</h2>
                            <Link :href="route('hr.payroll.index')" class="text-indigo-600 hover:text-indigo-900">Back to Payroll Hub</Link>
                        </div>

                        <div class="mb-6">
                            <input 
                                v-model="search"
                                type="text" 
                                placeholder="Search by employee name or payslip number..." 
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Pay</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="slip in payslips.data" :key="slip.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ slip.payslip_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ slip.employee?.user?.name }}</div>
                                            <div class="text-xs text-gray-500">{{ slip.employee?.employee_id }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ slip.payroll?.month }}/{{ slip.payroll?.year }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-700">
                                            {{ formatCurrency(slip.net_pay) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a :href="route('hr.payslip.download', slip.id)" target="_blank" class="text-indigo-600 hover:text-indigo-900 mr-3">Download</a>
                                        </td>
                                    </tr>
                                    <tr v-if="payslips.data.length === 0">
                                        <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">
                                            No payslips found matching your search.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            <!-- Simple Pagination component here or just basic links -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
