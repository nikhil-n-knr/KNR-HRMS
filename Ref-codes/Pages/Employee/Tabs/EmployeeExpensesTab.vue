
<template>
    <div class="space-y-6">
        <!-- Controls -->
        <div class="flex flex-col sm:flex-row justify-between gap-4">
             <div class="relative w-full sm:w-64">
                 <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                 <input v-model="search" type="text" placeholder="Search expenses..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs">
             </div>
             
             <div class="flex gap-2">
                 <select v-model="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                     <option value="">All Statuses</option>
                     <option value="Pending">Pending</option>
                     <option value="Processing">Processing</option>
                     <option value="Approved">Approved</option>
                     <option value="Paid">Paid</option>
                     <option value="Rejected">Rejected</option>
                 </select>

                 <button v-if="canApplyLoan" @click="openLoanModal" class="px-4 py-2 bg-emerald-600 text-white rounded-lg font-medium text-xs hover:bg-emerald-700 transition flex items-center shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Apply Loan
                 </button>

                 <button @click="openCreateModal" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium text-xs hover:bg-indigo-700 transition flex items-center shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    New Claim
                 </button>
             </div>
        </div>

        <!-- Table -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
             <!-- Loading State -->
             <div v-if="loading" class="p-12 flex justify-center">
                 <svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
             </div>

             <!-- Empty State -->
             <div v-else-if="filteredExpenses.length === 0" class="p-12 text-center">
                 <div class="bg-gray-50 p-4 rounded-full inline-block mb-3">
                     <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                 </div>
                 <h3 class="text-gray-900 font-medium">No expenses found</h3>
                 <p class="text-gray-500 text-xs mt-1">Try adjusting your filters or search.</p>
             </div>

             <table v-else class="min-w-full divide-y divide-gray-200">
                 <thead class="bg-gray-50">
                     <tr>
                         <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                         <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Expense Details</th>
                         <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Amount</th>
                         <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                         <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tracker</th>
                         <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                     </tr>
                 </thead>
                 <tbody class="bg-white divide-y divide-gray-200">
                     <tr v-for="expense in filteredExpenses" :key="expense.id" class="hover:bg-gray-50 transition">
                         <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600">
                             {{ formatDate(expense.incurred_date) }}
                         </td>
                         <td class="px-6 py-4">
                             <div class="text-xs font-bold text-gray-900">{{ expense.title }}</div>
                             <div class="text-xs text-gray-500">{{ expense.category?.name }}</div>
                             <div class="text-xs text-indigo-600 mt-1" v-if="expense.project">{{ expense.project?.name }}</div>
                         </td>
                         <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-900">
                             {{ formatCurrency(expense.amount) }}
                         </td>
                         <td class="px-6 py-4 whitespace-nowrap">
                             <span :class="getStatusClasses(expense.status)" class="px-2 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                                 {{ expense.status }}
                             </span>
                             <div v-if="expense.current_stage" class="text-xs text-gray-400 mt-1 font-medium pl-1">
                                 {{ expense.current_stage.stage_name }}
                             </div>
                         </td>
                         <td class="px-6 py-4">
                             <!-- Mini Tracker / ProgressBar -->
                             <div class="w-24 h-1.5 bg-gray-100 rounded-full overflow-hidden flex">
                                 <div 
                                    class="h-full bg-indigo-500" 
                                    :style="{ width: getProgress(expense) + '%' }"
                                    :class="{
                                        'bg-green-500': expense.status === 'Paid' || expense.status === 'Approved',
                                        'bg-red-500': expense.status === 'Rejected',
                                        'bg-yellow-400': expense.status === 'Pending',
                                        'bg-blue-500': expense.status === 'Processing'
                                    }"
                                 ></div>
                             </div>
                             <p class="text-xs text-gray-400 mt-1">{{ getProgressLabel(expense) }}</p>
                         </td>
                         <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                             <button @click="viewDetails(expense)" class="text-indigo-600 hover:text-indigo-900 font-medium hover:underline">View</button>
                         </td>
                     </tr>
                 </tbody>
             </table>
        </div>

        <!-- Create Expense Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false" maxWidth="2xl">
            <div class="p-6">
                <h3 class="text-base font-bold text-gray-900 mb-6">Create Expense Claim (Admin)</h3>
                <form @submit.prevent="submitExpense" class="space-y-4">
                     <div>
                        <InputLabel value="Expense Title" />
                        <TextInput v-model="form.title" class="w-full mt-1" required placeholder="e.g. Client Lunch" />
                        <InputError :message="errors.title" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Category" />
                            <select v-model="form.expense_category_id" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="" disabled>Select Category</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                             <InputError :message="errors.expense_category_id" />
                        </div>
                         <div>
                            <InputLabel value="Amount" />
                            <TextInput type="number" step="0.01" v-model="form.amount" class="w-full mt-1" required />
                             <InputError :message="errors.amount" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                             <InputLabel value="Date Incurred" />
                             <TextInput type="date" v-model="form.incurred_date" class="w-full mt-1" required />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Project (Optional)" />
                         <select v-model="form.project_id" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option :value="null">None</option>
                                <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }} ({{ p.code }})</option>
                        </select>
                        <div class="mt-2 flex items-center" v-if="form.project_id">
                             <input type="checkbox" v-model="form.is_billable" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                             <span class="ml-2 text-xs text-gray-700">Billable to Client?</span>
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Description" />
                        <textarea v-model="form.description" rows="3" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    
                    <div>
                         <InputLabel value="Receipt (Optional)" />
                         <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-gray-50 transition cursor-pointer relative">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-xs text-gray-600 justify-center">
                                    <label class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                        <span>Upload a file</span>
                                        <input type="file" class="sr-only" @change="e => form.receipt = e.target.files[0]">
                                    </label>
                                </div>
                                <p v-if="form.receipt" class="text-xs text-green-600 font-bold">{{ form.receipt.name }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium text-xs">Cancel</button>
                        <button type="submit" :disabled="submitting" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium text-xs disabled:opacity-75">
                            {{ submitting ? 'Creating...' : 'Create Claim' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Loan Application Modal -->
        <Modal :show="showLoanModal" @close="showLoanModal = false" maxWidth="2xl">
            <div class="p-6">
                 <h3 class="text-base font-bold text-gray-900 mb-6">Apply for Loan / Advance</h3>
                 <div class="space-y-4">
                     <div>
                         <InputLabel value="Select Plan" />
                         <select v-model="loanForm.loan_product_id" @change="simulation = null" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                             <option value="" disabled>Choose a Loan Policy...</option>
                             <option v-for="plan in loanProducts" :key="plan.id" :value="plan.id">
                                 {{ plan.name }} - {{ plan.interest_rate }}% (Max: {{ formatCurrency(plan.max_amount_limit) }})
                             </option>
                         </select>
                         <p class="text-xs text-gray-500 mt-1" v-if="loanForm.loan_product_id">
                             {{ loanProducts.find(p => p.id === loanForm.loan_product_id)?.interest_type }} Interest
                         </p>
                     </div>

                     <div class="grid grid-cols-2 gap-4">
                         <div>
                             <InputLabel value="Loan Amount (INR)" />
                             <TextInput type="number" v-model="loanForm.amount" class="w-full mt-1" @blur="simulateLoan" />
                         </div>
                         <div>
                             <InputLabel value="Tenure (Months)" />
                             <TextInput type="number" v-model="loanForm.tenure" class="w-full mt-1" @blur="simulateLoan" />
                         </div>
                     </div>

                     <!-- Simulation Result -->
                     <div v-if="simulating" class="text-center py-4 text-emerald-600 font-medium animate-pulse">
                         Calculating Plan...
                     </div>
                     <div v-else-if="simulation" class="bg-emerald-50 rounded-lg p-4 border border-emerald-100">
                         <div class="grid grid-cols-2 gap-4 text-xs">
                             <div>
                                 <span class="text-gray-500 block">Monthly EMI</span>
                                 <span class="font-bold text-gray-900 text-base">{{ formatCurrency(simulation.emi) }}</span>
                             </div>
                             <div>
                                 <span class="text-gray-500 block">Total Interest</span>
                                 <span class="font-bold text-gray-900">{{ formatCurrency(simulation.total_interest) }}</span>
                             </div>
                         </div>
                        <div v-if="simulation.risk_analysis?.risk_level === 'High'" class="mt-2 text-xs text-red-600 font-bold bg-red-50 p-2 rounded">
                            ⚠️ High Risk Application: Might be rejected due to eligibility.
                        </div>
                     </div>

                     <div>
                         <InputLabel value="Reason for Loan" />
                         <textarea v-model="loanForm.reason" rows="2" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="e.g. Medical Emergency"></textarea>
                     </div>
                     
                     <div class="flex items-center mt-2">
                         <input type="checkbox" v-model="loanForm.agreed_to_terms" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                         <span class="ml-2 text-xs text-gray-600">I agree to the repayment terms and direct payroll deduction.</span>
                     </div>
                 </div>

                 <div class="flex justify-end gap-3 pt-6 mt-2 border-t">
                     <button @click="showLoanModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium text-xs">Cancel</button>
                     <button @click="submitLoan" :disabled="loanSubmitting || !loanForm.agreed_to_terms" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium text-xs disabled:opacity-50 flex items-center">
                         <svg v-if="loanSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                         Submit Application
                     </button>
                 </div>
            </div>
        </Modal>

        <!-- Details & Tracker Modal -->
        <Modal :show="!!selectedExpense" @close="selectedExpense = null" maxWidth="3xl">
            <div v-if="selectedExpense" class="p-6">
                
                <div class="flex justify-between items-start border-b border-gray-100 pb-4 mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ selectedExpense.title }}</h2>
                        <p class="text-xs text-gray-500 mt-1">
                             {{ selectedExpense.employee?.first_name }} {{ selectedExpense.employee?.last_name }} • {{ formatCurrency(selectedExpense.amount) }}
                        </p>
                    </div>
                    <span :class="getStatusClasses(selectedExpense.status)" class="px-3 py-1 rounded-full text-xs font-bold uppercase">{{ selectedExpense.status }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Left: Details -->
                    <div class="md:col-span-2 space-y-4">
                         <div class="grid grid-cols-2 gap-4">
                             <div class="p-3 bg-gray-50 rounded-lg">
                                 <span class="text-xs text-gray-500 font-bold uppercase">Date Incurred</span>
                                 <p class="font-medium text-gray-900">{{ formatDate(selectedExpense.incurred_date) }}</p>
                             </div>
                             <div class="p-3 bg-gray-50 rounded-lg">
                                 <span class="text-xs text-gray-500 font-bold uppercase">Category</span>
                                 <p class="font-medium text-gray-900">{{ selectedExpense.category?.name }}</p>
                             </div>
                             <div class="p-3 bg-gray-50 rounded-lg">
                                 <span class="text-xs text-gray-500 font-bold uppercase">Payout Method</span>
                                 <p class="font-medium text-gray-900 capitalize">{{ selectedExpense.payout_method }}</p>
                             </div>
                             <div class="p-3 bg-gray-50 rounded-lg">
                                 <span class="text-xs text-gray-500 font-bold uppercase">Project</span>
                                 <p class="font-medium text-gray-900">{{ selectedExpense.project?.name || 'N/A' }}</p>
                             </div>
                         </div>

                         <div>
                             <h4 class="text-xs font-bold text-gray-800 mb-2">Description</h4>
                             <p class="text-xs text-gray-600 bg-gray-50 p-4 rounded-lg border border-gray-100">
                                 {{ selectedExpense.description || 'No description provided.' }}
                             </p>
                         </div>
                         
                         <div v-if="selectedExpense.receipt_path">
                             <h4 class="text-xs font-bold text-gray-800 mb-2">Attachments</h4>
                             <a :href="`/storage/${selectedExpense.receipt_path}`" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                                 <svg class="h-5 w-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                 Download Receipt
                             </a>
                         </div>
                    </div>

                    <!-- Right: Tracker -->
                    <div class="border-l border-gray-100 pl-8">
                        <h4 class="text-xs font-bold text-gray-800 mb-6">Workflow Tracker</h4>
                         <div class="relative pl-4 border-l-2 border-indigo-100 space-y-8">
                             <!-- Steps -->
                             <div class="relative">
                                 <div class="absolute -left-[21px] top-1 w-3 h-3 rounded-full bg-indigo-500 ring-4 ring-white"></div>
                                 <p class="text-xs font-bold text-gray-500 uppercase">Submitted</p>
                                 <p class="text-xs font-medium text-gray-900">{{ formatDate(selectedExpense.created_at) }}</p>
                             </div>

                             <div class="relative" :class="{'opacity-50 grayscale': selectedExpense.status === 'Pending'}">
                                 <div class="absolute -left-[21px] top-1 w-3 h-3 rounded-full bg-blue-500 ring-4 ring-white"></div>
                                 <p class="text-xs font-bold text-gray-500 uppercase">Processing</p>
                                 <p class="text-xs font-medium text-gray-900">{{ selectedExpense.current_stage?.stage_name || 'Workflow' }}</p>
                             </div>

                             <!-- Dynamic Final Step -->
                             <div v-if="selectedExpense.status === 'Rejected'" class="relative">
                                 <div class="absolute -left-[21px] top-1 w-3 h-3 rounded-full bg-red-500 ring-4 ring-white"></div>
                                 <p class="text-xs font-bold text-red-500 uppercase">Rejected</p>
                                 <p class="text-xs text-red-600 mt-1 max-w-[150px]">{{ selectedExpense.rejection_reason }}</p>
                             </div>
                             <div v-else class="relative" :class="{'opacity-50 grayscale': !['Approved', 'Paid'].includes(selectedExpense.status)}">
                                 <div class="absolute -left-[21px] top-1 w-3 h-3 rounded-full bg-green-500 ring-4 ring-white"></div>
                                 <p class="text-xs font-bold text-gray-500 uppercase">Approved</p>
                                 <p class="text-xs font-medium text-gray-900" v-if="['Approved', 'Paid'].includes(selectedExpense.status)">Active</p>
                             </div>

                             <div class="relative" :class="{'opacity-50 grayscale': selectedExpense.status !== 'Paid'}">
                                 <div class="absolute -left-[21px] top-1 w-3 h-3 rounded-full bg-emerald-500 ring-4 ring-white"></div>
                                 <p class="text-xs font-bold text-gray-500 uppercase">Paid / Settled</p>
                                 <p class="text-xs font-medium text-gray-900" v-if="selectedExpense.status === 'Paid'">Completed</p>
                             </div>
                         </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button @click="selectedExpense = null" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium text-xs transition">Close Details</button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { useToastStore } from '@/stores/toast';

const props = defineProps({
    employee: { type: Object, required: true }
});

const toast = useToastStore();
const expenses = ref([]);
const categories = ref([]);
const projects = ref([]);
const loading = ref(true);
const search = ref('');
const statusFilter = ref('');
const selectedExpense = ref(null);
const showCreateModal = ref(false);
const submitting = ref(false);
const errors = ref({});
const canApplyLoan = ref(false);

const checkLoanStatus = async () => {
    try {
        const res = await axios.get(route('employee.loans.status'));
        canApplyLoan.value = res.data.enabled;
    } catch (e) {
        canApplyLoan.value = false;
    }
};

const form = reactive({
    title: '',
    amount: '',
    expense_category_id: '',
    incurred_date: new Date().toISOString().substr(0, 10),
    project_id: null,
    is_billable: false,
    payout_method: 'payroll',
    description: '',
    receipt: null
});

const filteredExpenses = computed(() => {
    return expenses.value.filter(e => {
        const matchesSearch = e.title.toLowerCase().includes(search.value.toLowerCase()) || 
                              e.amount.toString().includes(search.value);
        const matchesStatus = !statusFilter.value || e.status === statusFilter.value;
        return matchesSearch && matchesStatus;
    });
});

const fetchExpenses = async () => {
    loading.value = true;
    try {
        const res = await axios.get(`/api/admin/employees/${props.employee.id}/expenses`);
        expenses.value = res.data;
    } catch (e) {
        toast.error("Failed to load expenses");
    } finally {
        loading.value = false;
    }
};

const fetchOptions = async () => {
    try {
        const res = await axios.get('/api/admin/employees/expense-options');
        categories.value = res.data.categories;
        projects.value = res.data.projects;
    } catch (e) {
        console.error("Failed to fetch options");
    }
};

const openCreateModal = () => {
    showCreateModal.value = true;
    fetchOptions();
};

const submitExpense = async () => {
    submitting.value = true;
    errors.value = {};
    
    try {
        const formData = new FormData();
        Object.keys(form).forEach(key => {
            if (form[key] !== null) formData.append(key, form[key]);
        });
        // boolean handling
        formData.set('is_billable', form.is_billable ? 1 : 0);

        await axios.post(`/api/admin/employees/${props.employee.id}/expenses`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        toast.success("Expense created successfully");
        showCreateModal.value = false;
        // Reset form
        Object.assign(form, {
             title: '', amount: '', expense_category_id: '', 
             incurred_date: new Date().toISOString().substr(0, 10),
             project_id: null, is_billable: false, payout_method: 'payroll',
             description: '', receipt: null
        });
        fetchExpenses();
        
    } catch (e) {
        if (e.response && e.response.status === 422) {
            errors.value = e.response.data.errors;
        } else {
            toast.error("Failed to create expense");
        }
    } finally {
        submitting.value = false;
    }
};

onMounted(() => {
    fetchExpenses();
    checkLoanStatus();
});

const getStatusClasses = (status) => {
    const map = {
        'Pending': 'bg-yellow-100 text-yellow-800',
        'Processing': 'bg-blue-100 text-blue-800',
        'Approved': 'bg-green-100 text-green-800',
        'Paid': 'bg-emerald-100 text-emerald-800',
        'Rejected': 'bg-red-100 text-red-800'
    };
    return map[status] || 'bg-gray-100 text-gray-600';
};

const getProgress = (expense) => {
    if (expense.status === 'Pending') return 25;
    if (expense.status === 'Processing') return 50;
    if (expense.status === 'Approved') return 75;
    if (expense.status === 'Paid') return 100;
    if (expense.status === 'Rejected') return 100;
    return 0;
};

const getProgressLabel = (expense) => {
    if (expense.status === 'Pending') return 'Submitted';
    if (expense.status === 'Processing') return 'In Progress';
    if (expense.status === 'Approved') return 'Pending Payout';
    if (expense.status === 'Paid') return 'Completed';
    if (expense.status === 'Rejected') return 'Closed';
    return '';
};

const viewDetails = (expense) => {
    selectedExpense.value = expense;
};

// --- Loan Application Logic ---
const showLoanModal = ref(false);
const loanProducts = ref([]);
const loanSubmitting = ref(false);
const loanForm = reactive({
    loan_product_id: '',
    amount: '',
    tenure: '',
    reason: '',
    agreed_to_terms: false,
    signature_hash: 'signed_digitally_' + Date.now()
});

const openLoanModal = async () => {
    showLoanModal.value = true;
    try {
        const res = await axios.get(route('employee.loans.products'));
        loanProducts.value = res.data;
    } catch(e) {
        toast.error('Failed to load active loan plans.');
    }
};

const submitLoan = async () => {
    loanSubmitting.value = true;
    try {
        await axios.post(route('employee.loans.store'), loanForm);
        toast.success("Loan Application Submitted Successfully!");
        showLoanModal.value = false;
        // Optionally fetch loans if we were displaying them
    } catch (e) {
        if(e.response?.data?.errors) {
            toast.error(Object.values(e.response.data.errors)[0]);
        } else {
            toast.error("Failed to submit loan application.");
        }
    } finally {
        loanSubmitting.value = false;
    }
};

// Simulation
const simulation = ref(null);
const simulating = ref(false);
const simulateLoan = async () => {
    if(!loanForm.loan_product_id || !loanForm.amount || !loanForm.tenure) return;
    simulating.value = true;
    try {
        const res = await axios.post(route('employee.loans.simulate'), {
            loan_product_id: loanForm.loan_product_id,
            amount: loanForm.amount,
            tenure: loanForm.tenure
        });
        simulation.value = res.data;
    } catch(e) {
       console.error(e);
    } finally {
        simulating.value = false;
    }
};

const formatDate = (date) => new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val || 0);

</script>
