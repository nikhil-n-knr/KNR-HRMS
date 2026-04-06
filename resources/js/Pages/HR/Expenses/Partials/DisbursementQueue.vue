<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const items = ref({ data: [] });
const loading = ref(true);
const selectedIds = ref([]);
const filters = ref({ search: '', payout_mode: '' });

// Transaction Modal
const showPayModal = ref(false);
const payForm = ref({
    transaction_reference: '',
    settlement_date: new Date().toISOString().substr(0, 10)
});

// Payroll Modal
const showPayrollModal = ref(false);
const payrollForm = ref({
    target_month: new Date().toISOString().slice(0, 7) // YYYY-MM
});

const fetchItems = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('hr.expenses.api.disbursement'), { params: filters.value });
        items.value = res.data;
    } finally {
        loading.value = false;
    }
};

onMounted(fetchItems);
watch(filters, fetchItems, { deep: true });

const toggleSelection = (id) => {
    if (selectedIds.value.includes(id)) {
        selectedIds.value = selectedIds.value.filter(i => i !== id);
    } else {
        selectedIds.value.push(id);
    }
};

const processedAmount = computed(() => {
    const selected = items.value.data.filter(i => selectedIds.value.includes(i.id));
    return selected.reduce((sum, i) => sum + Number(i.approved_amount || i.amount), 0);
});

const submitPayroll = () => {
    router.post(route('expenses.settlement.payroll'), {
        ids: selectedIds.value,
        target_month: payrollForm.value.target_month
    }, {
        onSuccess: () => {
            showPayrollModal.value = false;
            selectedIds.value = [];
            fetchItems();
        }
    });
};

const submitPayment = () => {
    router.post(route('expenses.settlement.pay'), {
        ids: selectedIds.value,
        transaction_reference: payForm.value.transaction_reference,
        settlement_date: payForm.value.settlement_date
    }, {
        onSuccess: () => {
            showPayModal.value = false;
            selectedIds.value = [];
            fetchItems();
        }
    });
};

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val || 0);
const formatDate = (date) => new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short' });
</script>

<template>
    <div>
        <!-- Toolbar -->
        <div class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
            <div class="flex gap-4 flex-1">
                <TextInput v-model="filters.search" placeholder="Search Employee..." class="w-full sm:w-64" />
                <select v-model="filters.payout_mode" class="border-gray-300 rounded-md shadow-sm h-10 w-40">
                    <option value="">All Modes</option>
                    <option value="payroll">Via Payroll</option>
                    <option value="direct">Direct Transfer</option>
                </select>
            </div>
            
            <div class="flex gap-2" v-if="selectedIds.length > 0">
                <div class="px-4 py-2 bg-gray-100 rounded text-sm font-bold text-gray-700">
                    Selected: {{ selectedIds.length }} ({{ formatCurrency(processedAmount) }})
                </div>
                <PrimaryButton @click="showPayrollModal = true" class="bg-indigo-600 hover:bg-indigo-700">
                    Map to Payroll
                </PrimaryButton>
                <SecondaryButton @click="showPayModal = true" class="bg-green-600 text-white hover:bg-green-700 border-none">
                    Mark as Paid
                </SecondaryButton>
            </div>
        </div>

        <!-- Table -->
        <div v-if="loading" class="text-center p-8 text-gray-500">Loading Disbursement Queue...</div>
        <div v-else class="overflow-x-auto bg-white rounded shadow-sm border border-gray-100">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 w-4">
                            <!-- Select All logic omitted for brevity, manageable manually -->
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Method</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Approved Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="item in items.data" :key="item.id" :class="{'bg-indigo-50': selectedIds.includes(item.id)}">
                        <td class="px-4 py-4">
                            <input type="checkbox" :checked="selectedIds.includes(item.id)" @change="toggleSelection(item.id)" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ item.employee?.user.name }}</div>
                            <div class="text-xs text-gray-500">{{ item.title }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ item.category?.name }}
                            <div v-if="item.gst_number" class="text-xs text-blue-600 mt-0.5">GST: {{ item.gst_number }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span v-if="item.payout_method === 'payroll'" class="px-2 py-0.5 rounded text-xs bg-purple-100 text-purple-800 font-bold">Payroll</span>
                            <span v-else class="px-2 py-0.5 rounded text-xs bg-green-100 text-green-800 font-bold">Direct</span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-gray-900">
                            {{ formatCurrency(item.approved_amount || item.amount) }}
                            <div v-if="item.approved_amount && item.approved_amount < item.amount" class="text-xs text-red-500 line-through">
                                {{ formatCurrency(item.amount) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right text-sm text-gray-500">{{ formatDate(item.updated_at) }}</td>
                    </tr>
                    <tr v-if="items.data?.length === 0">
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">Queue empty. All expenses settled!</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Payroll Modal -->
        <Modal :show="showPayrollModal" @close="showPayrollModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold mb-4">Map to Payroll</h3>
                <p class="text-sm text-gray-600 mb-4">
                    You are scheduling <b>{{ selectedIds.length }}</b> claim(s) totaling <b>{{ formatCurrency(processedAmount) }}</b> to be processed with salary.
                </p>
                <div>
                    <InputLabel value="Select Target Payroll Month" />
                    <TextInput type="month" v-model="payrollForm.target_month" class="w-full mt-1" />
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showPayrollModal = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="submitPayroll">Confirm Schedule</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Direct Pay Modal -->
        <Modal :show="showPayModal" @close="showPayModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold mb-4">Mark as Paid (Direct Settlement)</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Record payment details for <b>{{ selectedIds.length }}</b> claim(s) totaling <b>{{ formatCurrency(processedAmount) }}</b>.
                </p>
                <div class="space-y-4">
                    <div>
                        <InputLabel value="Payment Date" />
                        <TextInput type="date" v-model="payForm.settlement_date" class="w-full mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Reference / Cheque No / UTN" />
                        <TextInput v-model="payForm.transaction_reference" class="w-full mt-1" placeholder="e.g. IMPS-1234567890" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showPayModal = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="submitPayment" class="bg-green-600 hover:bg-green-700">Confirm Payment</PrimaryButton>
                </div>
            </div>
        </Modal>
    </div>
</template>
