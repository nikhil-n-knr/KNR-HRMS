<template>
    <Head title="Manage Salary" />
    <MainLayout>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="md:flex md:gap-6">
                <!-- Sidebar Info -->
                <div class="md:w-1/3 space-y-6">
                    <!-- Employee Card -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-lg">
                                {{ employee.user.name.charAt(0) }}
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">{{ employee.user.name }}</h2>
                                <p class="text-sm text-gray-500">{{ employee.employee_id }}</p>
                            </div>
                        </div>
                        <div class="border-t border-gray-100 pt-4">
                             <p class="text-xs text-gray-400 uppercase tracking-widest font-semibold">Active Structure</p>
                             <div v-if="currentSalary">
                                 <p class="text-2xl font-bold text-gray-800 mt-1">{{ formatCurrency(currentSalary.annual_ctc) }} <span class="text-sm font-normal text-gray-500">/ Year</span></p>
                                 <p class="text-sm text-indigo-600 mt-1">{{ currentSalary.structure?.name }}</p>
                                 <p class="text-xs text-gray-400 mt-2">Effective: {{ formatDate(currentSalary.effective_date) }}</p>
                             </div>
                             <div v-else class="py-4 text-sm text-gray-400 italic">
                                 No salary assigned yet.
                             </div>
                        </div>
                    </div>

                    <!-- Revision Form -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Revise Salary / Appraisal</h3>
                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Salary Structure</label>
                                <select v-model="form.salary_structure_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option v-for="struct in structures" :key="struct.id" :value="struct.id">{{ struct.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">New Annual CTC</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">₹</span>
                                    </div>
                                    <input type="number" v-model="form.annual_ctc" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 sm:text-sm border-gray-300 rounded-md" placeholder="0.00">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Effective Date</label>
                                <input type="date" v-model="form.effective_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Remarks</label>
                                <textarea v-model="form.remarks" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" rows="2" placeholder="e.g. Annual Appraisal"></textarea>
                            </div>
                            <button type="submit" :disabled="form.processing" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                Update Salary
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Main Breakdown -->
                <div class="md:w-2/3 space-y-6">
                    <!-- Current Breakdown -->
                    <div v-if="currentSalary" class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800">Monthly Compensation Breakdown</h3>
                        </div>
                        <div class="p-6">
                             <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                                 <!-- Earnings -->
                                 <div>
                                     <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Earnings</h4>
                                     <ul class="space-y-2">
                                         <li v-for="(amount, name) in (currentSalary.breakdown?.earnings_breakdown || {})" :key="name" class="flex justify-between text-sm">
                                             <span class="text-gray-600">{{ name }}</span>
                                             <span class="font-medium text-gray-900">{{ formatCurrency(amount) }}</span>
                                         </li>
                                         <!-- Fallback logic if breakdown structure differs -->
                                          <template v-if="!currentSalary.breakdown?.earnings_breakdown">
                                             <li v-for="(amount, name) in (currentSalary.breakdown?.components || {})" :key="name" class="flex justify-between text-sm">
                                                 <span class="text-gray-600">{{ name }}</span>
                                                 <span class="font-medium text-gray-900">{{ formatCurrency(amount) }}</span>
                                             </li>
                                          </template>
                                     </ul>
                                     <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between font-bold text-gray-900">
                                         <span>Total Earnings</span>
                                         <span>{{ formatCurrency(currentSalary.breakdown?.gross_earnings || 0) }}</span>
                                     </div>
                                 </div>

                                 <!-- Deductions & Net -->
                                 <div>
                                     <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Net Pay</h4>
                                     <div class="bg-indigo-50 rounded-lg p-4 text-center border border-indigo-100 mb-4">
                                         <span class="block text-indigo-600 text-xs font-bold uppercase">Monthly Net Salary</span>
                                         <span class="block text-2xl font-extrabold text-indigo-900 mt-1">{{ formatCurrency(currentSalary.breakdown?.net_pay || 0) }}</span>
                                     </div>
                                      <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Deductions</h4>
                                     <ul class="space-y-2">
                                          <li v-for="(amount, name) in (currentSalary.breakdown?.deductions_breakdown || {})" :key="name" class="flex justify-between text-sm">
                                             <span class="text-gray-600">{{ name }}</span>
                                             <span class="text-red-500 font-medium">- {{ formatCurrency(amount) }}</span>
                                         </li>
                                     </ul>
                                     <div v-if="currentSalary.breakdown?.total_deductions > 0" class="mt-3 pt-3 border-t border-gray-100 flex justify-between font-bold text-gray-500">
                                         <span>Total Deductions</span>
                                         <span class="text-red-500">- {{ formatCurrency(currentSalary.breakdown?.total_deductions || 0) }}</span>
                                     </div>
                                 </div>
                             </div>
                        </div>
                    </div>

                    <!-- History Table -->
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800">Salary History</h3>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Effective Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CTC</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Structure</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="rec in history" :key="rec.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(rec.effective_date) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ formatCurrency(rec.annual_ctc) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ rec.structure?.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 italic">{{ rec.remarks || '-' }}</td>
                                </tr>
                                <tr v-if="history.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-400 text-sm">No revision history found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import dayjs from 'dayjs';

const props = defineProps({
    employee: Object,
    currentSalary: Object,
    history: Array,
    structures: Array
});

const form = useForm({
    salary_structure_id: props.currentSalary?.salary_structure_id || (props.structures.length > 0 ? props.structures[0].id : ''),
    annual_ctc: props.currentSalary?.annual_ctc || '',
    effective_date: dayjs().format('YYYY-MM-DD'),
    remarks: ''
});

const submit = () => {
    form.post(route('hr.employees.salary.store', props.employee.id), {
        onSuccess: () => form.reset(),
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amount);
};

const formatDate = (date) => dayjs(date).format('MMM D, YYYY');
</script>
