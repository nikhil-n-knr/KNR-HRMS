<template>
    <Head title="My Expenses" />
    <MainLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Expenses & Claims</h2>
                <div class="flex gap-3">
                    <Link :href="route('employee.expenses.index')" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md font-medium text-sm hover:bg-gray-50 flex items-center shadow-sm">
                        My Personal Claims
                    </Link>
                    <button @click="showCreateModal = true" class="px-4 py-2 bg-indigo-600 text-white rounded-md font-medium text-sm hover:bg-indigo-700">
                        + Record Expense (HR)
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                        <p class="text-sm font-medium text-gray-500">This Month's Claims</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.total_this_month) }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                        <p class="text-sm font-medium text-gray-500">Pending Approval</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ stats.total_pending }}</p>
                    </div>
                </div>

                <!-- Expense List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Claim History</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payout</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="expense in expenses.data" :key="expense.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(expense.incurred_date) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div class="font-medium">{{ expense.title }}</div>
                                            <div class="text-xs text-gray-500 truncate max-w-xs">{{ expense.description }}</div>
                                            <div v-if="expense.project" class="text-xs text-indigo-600 mt-1">
                                                Project: {{ expense.project.name }}
                                                <span v-if="expense.is_billable" class="bg-green-100 text-green-800 text-sm px-1 rounded ml-1">Billable</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ expense.category?.name || 'Uncategorized' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            {{ formatCurrency(expense.amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div v-if="expense.status === 'Pending'" class="flex items-center">
                                                 <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Pending
                                                 </span>
                                                 <!-- Show Workflow Stage -->
                                                 <span v-if="expense.current_stage" class="text-xs text-gray-500 ml-2">
                                                    ({{ expense.current_stage.stage_name }})
                                                 </span>
                                                 <span v-else class="text-xs text-gray-500 ml-2">(Initiated)</span>
                                            </div>
                                            <span v-else-if="expense.status === 'Processing'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Processing ({{ expense.current_stage?.stage_name || 'Workflow' }})
                                            </span>
                                            <span v-else-if="expense.status === 'Approved'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Approved
                                            </span>
                                            <span v-else-if="expense.status === 'Paid'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                                Paid
                                            </span>
                                            <div v-else-if="expense.status === 'Rejected'" class="text-red-600 flex flex-col">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 w-min">Rejected</span>
                                                <span class="text-xs mt-1">{{ expense.rejection_reason }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span v-if="expense.payroll_id" class="text-green-600 font-bold">Via Payroll</span>
                                            <span v-else-if="expense.payout_method === 'direct'" class="text-blue-600">Direct Transfer</span>
                                            <span v-else class="text-gray-400">-</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginator -->
                         <div class="mt-4">
                            <!-- Simple Pagination Links if needed -->
                        </div>
                    </div>
                </div>

                <!-- Create Modal -->
                <Modal :show="showCreateModal" @close="showCreateModal = false">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Details of Expenditure</h2>
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <InputLabel value="Expense Title" />
                                    <TextInput v-model="form.title" class="w-full" required placeholder="e.g. Client Dinner, Flight Tickets" />
                                    <InputError :message="form.errors.title" />
                                </div>
                                
                                <div>
                                    <InputLabel value="Category" />
                                    <select v-model="form.expense_category_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                                        <option :value="null">Select Category</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                    <InputError :message="form.errors.expense_category_id" />
                                </div>

                                <div>
                                    <InputLabel value="Date Incurred" />
                                    <TextInput type="date" v-model="form.incurred_date" class="w-full" required />
                                </div>

                                <div>
                                    <InputLabel value="Amount (INR)" />
                                    <TextInput type="number" step="0.01" v-model="form.amount" class="w-full" required />
                                </div>
                                
                                <div class="col-span-1 border rounded p-3 bg-gray-50">
                                     <div class="flex items-center justify-between mb-2">
                                         <label class="text-sm font-medium text-gray-700">Project / Client</label>
                                         <span class="text-xs text-gray-400">(Optional)</span>
                                     </div>
                                     <select v-model="form.project_id" class="block w-full text-sm border-gray-300 rounded mb-2">
                                         <option :value="null">No Project</option>
                                         <option v-for="proj in projects" :key="proj.id" :value="proj.id">{{ proj.name }} ({{ proj.code }})</option>
                                     </select>
                                     <div class="flex items-center" v-if="form.project_id">
                                         <input type="checkbox" v-model="form.is_billable" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                         <span class="ml-2 text-sm text-gray-600">Billable to Client?</span>
                                     </div>
                                </div>

                                <div class="col-span-2">
                                    <InputLabel value="Receipt / Proof" />
                                    <input type="file" @change="e => form.receipt = e.target.files[0]" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded cursor-pointer bg-gray-50 focus:outline-none" />
                                    <p v-if="selectedCategory?.requires_bill_proof" class="text-xs text-red-500 mt-1">* Receipt mandatory for this category.</p>
                                    <InputError :message="form.errors.receipt" />
                                </div>

                                <div class="col-span-2">
                                    <InputLabel value="Description" />
                                    <textarea v-model="form.description" rows="3" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <SecondaryButton @click="showCreateModal = false">Cancel</SecondaryButton>
                                <PrimaryButton :disabled="form.processing">Submit Claim</PrimaryButton>
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
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    expenses: Object,
    stats: Object,
    categories: Array,
    projects: Array
});

const showCreateModal = ref(false);
const form = useForm({
    title: '',
    amount: '',
    expense_category_id: null,
    incurred_date: new Date().toISOString().substr(0, 10),
    description: '',
    receipt: null,
    project_id: null,
    is_billable: false,
    payout_method: 'payroll'
});

const selectedCategory = computed(() => {
    return props.categories.find(c => c.id === form.expense_category_id);
});

const submit = () => {
    form.post(route('hr.expenses.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        }
    });
};

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val || 0);
const formatDate = (date) => new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
</script>
