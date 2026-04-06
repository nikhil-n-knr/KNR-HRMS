<template>
    <Head title="Variable Pay" />
    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Variable Pay & Bonuses
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Payouts / Bonuses</h3>
                        <p class="text-sm text-gray-500">Add ad-hoc payments like Bonuses, Commissions, or Incentives.</p>
                    </div>
                    <div class="space-x-2">
                        <button @click="showImportModal = true" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                            Import CSV
                        </button>
                        <button @click="showGenerateModal = true" class="px-4 py-2 bg-indigo-100 border border-transparent rounded-md font-semibold text-xs text-indigo-700 uppercase tracking-widest hover:bg-indigo-200">
                            Bulk Generate
                        </button>
                        <button @click="showModal = true" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Add Payout
                        </button>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pay Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="payout in payouts.data" :key="payout.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ payout.employee?.user?.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ payout.type }}
                                    <p class="text-xs text-gray-400">{{ payout.remarks }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    {{ formatCurrency(payout.amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ getMonthName(payout.pay_month) }} {{ payout.pay_year }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                          :class="{
                                              'bg-yellow-100 text-yellow-800': payout.status === 'Pending',
                                              'bg-green-100 text-green-800': payout.status === 'Approved' || payout.status === 'Paid',
                                              'bg-red-100 text-red-800': payout.status === 'Rejected'
                                          }">
                                        {{ payout.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div v-if="payout.status === 'Pending'" class="flex justify-end gap-2">
                                        <button @click="updateStatus(payout, 'approve')" class="text-green-600 hover:text-green-900">Approve</button>
                                        <button @click="updateStatus(payout, 'reject')" class="text-red-600 hover:text-red-900">Reject</button>
                                        <button @click="deletePayout(payout)" class="text-gray-400 hover:text-gray-600">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Modal -->
                <Modal :show="showModal" @close="showModal = false">
                    <div class="p-6">
                         <h2 class="text-lg font-medium text-gray-900 mb-4">Add Variable Payout</h2>
                         <form @submit.prevent="submit">
                             <div class="space-y-4">
                                <div>
                                    <InputLabel value="Employee" />
                                    <select v-model="form.employee_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                        <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel value="Type" />
                                        <select v-model="form.type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            <option>Performance Bonus</option>
                                            <option>Sales Commission</option>
                                            <option>Incentive</option>
                                            <option>Joining Bonus</option>
                                            <option>Referral Bonus</option>
                                        </select>
                                    </div>
                                    <div>
                                        <InputLabel value="Amount" />
                                        <TextInput type="number" v-model="form.amount" class="w-full mt-1" step="0.01" required />
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                     <div>
                                        <InputLabel value="Pay Month" />
                                        <select v-model="form.pay_month" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            <option v-for="m in 12" :key="m" :value="m">{{ getMonthName(m) }}</option>
                                        </select>
                                     </div>
                                     <div>
                                        <InputLabel value="Pay Year" />
                                        <TextInput type="number" v-model="form.pay_year" class="w-full mt-1" required />
                                     </div>
                                </div>
                                <div>
                                    <InputLabel value="Remarks" />
                                    <TextInput type="text" v-model="form.remarks" class="w-full mt-1" />
                                </div>
                             </div>
                             <div class="mt-6 flex justify-end gap-3">
                                <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                                <PrimaryButton :disabled="form.processing">Save Draft</PrimaryButton>
                             </div>
                         </form>
                    </div>
                </Modal>

                <!-- Import Modal -->
                <Modal :show="showImportModal" @close="showImportModal = false">
                    <div class="p-6">
                         <h2 class="text-lg font-medium text-gray-900 mb-4">Import Variable Pay (CSV)</h2>
                         <p class="text-sm text-gray-500 mb-4">
                             Expected Format: Employee Code, Amount, Type, Remarks.<br>
                             First row is skipped if expected header.
                         </p>
                         <form @submit.prevent="submitImport">
                             <div class="space-y-4">
                                <div>
                                    <InputLabel value="Upload CSV File" />
                                    <input type="file" @change="e => importForm.csv_file = e.target.files[0]"  class="mt-1 block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-full file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-indigo-50 file:text-indigo-700
                                        hover:file:bg-indigo-100
                                    " accept=".csv">
                                    <InputError :message="importForm.errors.csv_file" />
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                     <div>
                                        <InputLabel value="Pay Month" />
                                        <select v-model="importForm.pay_month" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            <option v-for="m in 12" :key="m" :value="m">{{ getMonthName(m) }}</option>
                                        </select>
                                     </div>
                                     <div>
                                        <InputLabel value="Pay Year" />
                                        <TextInput type="number" v-model="importForm.pay_year" class="w-full mt-1" required />
                                     </div>
                                </div>
                             </div>
                             <div class="mt-6 flex justify-end gap-3">
                                <SecondaryButton @click="showImportModal = false">Cancel</SecondaryButton>
                                <PrimaryButton :disabled="importForm.processing">Import Data</PrimaryButton>
                             </div>
                         </form>
                    </div>
                </Modal>

                <!-- Bulk Generate Modal -->
                <Modal :show="showGenerateModal" @close="showGenerateModal = false">
                    <div class="p-6">
                         <h2 class="text-lg font-medium text-gray-900 mb-4">Bulk Generate Bonuses</h2>
                         <form @submit.prevent="submitGenerate">
                             <div class="space-y-4">
                                <!-- Filters -->
                                <div class="bg-gray-50 p-3 rounded-md">
                                    <h4 class="text-xs font-bold uppercase text-gray-500 mb-2">Target Employees</h4>
                                    <div>
                                        <InputLabel value="Department (Optional)" />
                                        <!-- Replace with real departments if available in props, else simple input or load via API -->
                                        <select v-model="generateForm.filters.department_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            <option value="">All Departments</option>
                                            <option value="1">Engineering</option>
                                            <option value="2">Sales</option>
                                            <option value="3">HR</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Rule -->
                                <div class="bg-indigo-50 p-3 rounded-md">
                                    <h4 class="text-xs font-bold uppercase text-indigo-500 mb-2">Payout Rule</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel value="Calculation Type" />
                                            <select v-model="generateForm.rule.type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                                <option value="fixed">Fixed Amount (₹)</option>
                                                <option value="percent_ctc">% of Monthly CTC</option>
                                            </select>
                                        </div>
                                        <div>
                                            <InputLabel value="Value" />
                                            <TextInput type="number" v-model="generateForm.rule.value" class="w-full mt-1" required />
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                         <InputLabel value="Payout Type" />
                                         <select v-model="generateForm.rule.payout_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            <option>Performance Bonus</option>
                                            <option>Incentive</option>
                                            <option>Festival Bonus</option>
                                         </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                     <div>
                                        <InputLabel value="Pay Month" />
                                        <select v-model="generateForm.pay_month" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            <option v-for="m in 12" :key="m" :value="m">{{ getMonthName(m) }}</option>
                                        </select>
                                     </div>
                                     <div>
                                        <InputLabel value="Pay Year" />
                                        <TextInput type="number" v-model="generateForm.pay_year" class="w-full mt-1" required />
                                     </div>
                                </div>
                             </div>
                             <div class="mt-6 flex justify-end gap-3">
                                <SecondaryButton @click="showGenerateModal = false">Cancel</SecondaryButton>
                                <PrimaryButton :disabled="generateForm.processing">Generate Drafts</PrimaryButton>
                             </div>
                         </form>
                    </div>
                </Modal>

            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    payouts: Object,
    employees: Array
});

const showModal = ref(false);
const form = useForm({
    employee_id: '',
    type: 'Performance Bonus',
    amount: '',
    pay_month: new Date().getMonth() + 1,
    pay_year: new Date().getFullYear(),
    remarks: ''
});

const submit = () => {
    form.post(route('hr.payroll.variable.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};

// Bulk Import
const showImportModal = ref(false);
const importForm = useForm({
    csv_file: null,
    pay_month: new Date().getMonth() + 1,
    pay_year: new Date().getFullYear(),
});

const submitImport = () => {
    importForm.post(route('hr.payroll.variable.import'), {
         onSuccess: () => {
            showImportModal.value = false;
            importForm.reset();
        }
    });
};

// Bulk Generate
const showGenerateModal = ref(false);
const generateForm = useForm({
    filters: { department_id: '' },
    rule: { type: 'fixed', value: '', payout_type: 'Performance Bonus' },
    pay_month: new Date().getMonth() + 1,
    pay_year: new Date().getFullYear(),
});

const submitGenerate = () => {
    if(!confirm('This will generate draft payouts for all matching employees. Continue?')) return;
    
    generateForm.post(route('hr.payroll.variable.bulk'), {
         onSuccess: () => {
            showGenerateModal.value = false;
        }
    });
};

const updateStatus = (payout, action) => {
    if(confirm(`Are you sure you want to ${action} this payout?`)) {
        router.put(route('hr.payroll.variable.update', payout.id), { action });
    }
};

const deletePayout = (payout) => {
    if(confirm('Delete this record?')) {
        router.delete(route('hr.payroll.variable.destroy', payout.id));
    }
};

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val);
const getMonthName = (m) => new Date(0, m - 1).toLocaleString('default', { month: 'long' });
</script>
