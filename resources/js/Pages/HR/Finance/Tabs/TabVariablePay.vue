
<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

defineProps({ employees: Array });

// Ad-hoc Logic
const showAdhocModal = ref(false);
const adhocForm = useForm({
    title: 'Diwali Bonus',
    type: 'Bonus', // Bonus, Incentive, Commission
    amount: '',
    calculation_type: 'flat', // flat, percentage_basic
    percentage: '',
    target_audience: 'all', // all, department (todo), individual
    employee_ids: [], // if individual
    pay_month: new Date().getMonth() + 1,
    pay_year: new Date().getFullYear(),
});

const submitAdhoc = () => {
    adhocForm.post(route('hr.finance.variable.store-adhoc'), {
        onSuccess: () => {
            showAdhocModal.value = false;
            adhocForm.reset();
        }
    });
};

// Matrix Logic (Placeholder for now)
const showMatrixModal = ref(false);
const matrixFile = ref(null);
const uploadMatrix = () => {
    // Implement file upload logic later or simple CSV post
    alert('Matrix Upload Feature Coming Soon');
};
</script>
<template>
    <div class="space-y-6">
        <h2 class="text-lg font-bold text-gray-800">Variable Pay Engine</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h3 class="font-bold text-gray-900">Ad-hoc Bonuses</h3>
                <p class="text-sm text-gray-500 mb-4">Festival gifts, spot bonuses, etc.</p>
                <button @click="showAdhocModal = true" class="px-4 py-2 bg-emerald-600 text-white rounded text-sm hover:bg-emerald-700">Add Bulk Bonus</button>
            </div>
             <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h3 class="font-bold text-gray-900">Performance-Linked Pay</h3>
                <p class="text-sm text-gray-500 mb-4">Calculate payouts based on appraisal ratings.</p>
                <button class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700" @click="uploadMatrix">Upload Matrix</button>
            </div>
        </div>

        <!-- Adhoc Modal -->
        <div v-if="showAdhocModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-bold mb-4">Add Ad-hoc / Bulk Payment</h3>
                <form @submit.prevent="submitAdhoc" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Title</label>
                        <input v-model="adhocForm.title" type="text" placeholder="e.g. Diwali Bonus" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <select v-model="adhocForm.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                                <option>Bonus</option>
                                <option>Incentive</option>
                                <option>Commission</option>
                                <option>Reimbursement</option>
                            </select>
                        </div>
                         <div>
                            <label class="block text-sm font-medium text-gray-700">Target Audience</label>
                            <select v-model="adhocForm.target_audience" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                                <option value="all">All Employees</option>
                                <option value="department">Specific Department (Coming Soon)</option>
                                <option value="individual">Specific Employees</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="adhocForm.target_audience === 'individual'">
                        <label class="block text-sm font-medium text-gray-700">Select Employees</label>
                        <select multiple v-model="adhocForm.employee_ids" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm h-32">
                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                {{ emp.first_name }} {{ emp.last_name }}
                            </option>
                        </select>
                         <p class="text-xs text-gray-500">Hold Ctrl to select multiple.</p>
                    </div>

                    <div class="bg-gray-50 p-3 rounded border border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Calculation Logic</label>
                        <div class="flex items-center gap-4 mb-2">
                             <label class="inline-flex items-center">
                                <input type="radio" v-model="adhocForm.calculation_type" value="flat" class="form-radio text-indigo-600">
                                <span class="ml-2 text-sm">Flat Amount</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" v-model="adhocForm.calculation_type" value="percentage_basic" class="form-radio text-indigo-600">
                                <span class="ml-2 text-sm">% of Basic Salary</span>
                            </label>
                        </div>
                        
                        <div v-if="adhocForm.calculation_type === 'flat'">
                             <input v-model="adhocForm.amount" type="number" placeholder="Enter Amount (e.g. 5000)" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                        </div>
                         <div v-else>
                             <input v-model="adhocForm.percentage" type="number" placeholder="Enter % (e.g. 50)" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" @click="showAdhocModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium" :disabled="adhocForm.processing">Process Payout</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
