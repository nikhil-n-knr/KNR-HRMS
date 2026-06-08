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
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 flex-1">
                <TextInput
                    v-model="filters.search"
                    placeholder="Search Employee..."
                    class="w-full sm:w-80 h-11 rounded-lg border-gray-200 bg-white text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
                <select v-model="filters.payout_mode" class="h-11 w-full sm:w-52 rounded-lg border-gray-200 bg-white px-3 text-sm font-medium text-gray-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Modes</option>
                    <option value="payroll">Via Payroll</option>
                    <option value="direct">Direct Transfer</option>
                </select>
            </div>
            
            <div class="flex flex-wrap items-center gap-2" v-if="selectedIds.length > 0">
                <div class="h-10 inline-flex items-center px-4 bg-blue-50 border border-blue-100 rounded-lg text-xs font-black text-blue-900">
                    Selected: {{ selectedIds.length }} ({{ formatCurrency(processedAmount) }})
                </div>
                <PrimaryButton @click="showPayrollModal = true" class="h-10 bg-indigo-600 hover:bg-indigo-700 text-xs font-bold">
                    Map to Payroll
                </PrimaryButton>
                <SecondaryButton @click="showPayModal = true" class="h-10 bg-green-600 text-white hover:bg-green-700 border-none text-xs font-bold">
                    Mark as Paid
                </SecondaryButton>
            </div>
        </div>

        <!-- Table -->
        <div v-if="loading" class="text-center p-8 text-gray-500">Loading Disbursement Queue...</div>
        <div v-else class="overflow-x-auto rounded-xl border border-gray-100 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-blue-50">
                    <tr>
                        <th class="w-[4%] px-4 py-3.5 text-left">
                            <!-- Select All logic omitted for brevity, manageable manually -->
                        </th>
                        <th class="w-[30%] px-6 py-3.5 text-left text-[11px] font-black text-blue-900 uppercase tracking-widest">Employee</th>
                        <th class="w-[22%] px-6 py-3.5 text-left text-[11px] font-black text-blue-900 uppercase tracking-widest">Category</th>
                        <th class="w-[14%] px-6 py-3.5 text-left text-[11px] font-black text-blue-900 uppercase tracking-widest">Method</th>
                        <th class="w-[15%] px-6 py-3.5 text-left text-[11px] font-black text-blue-900 uppercase tracking-widest">Amount</th>
                        <th class="w-[15%] px-6 py-3.5 text-left text-[11px] font-black text-blue-900 uppercase tracking-widest">Approved Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <tr v-for="item in items.data" :key="item.id" class="hover:bg-blue-50/30 transition-colors" :class="{'bg-indigo-50': selectedIds.includes(item.id)}">
                        <td class="px-4 py-4">
                            <input type="checkbox" :checked="selectedIds.includes(item.id)" @change="toggleSelection(item.id)" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">{{ item.employee?.user.name }}</div>
                            <div class="text-xs font-medium text-gray-500">{{ item.title }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-600">
                            {{ item.category?.name }}
                            <div v-if="item.gst_number" class="text-[11px] font-semibold text-blue-600 mt-0.5">GST: {{ item.gst_number }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span v-if="item.payout_method === 'payroll'" class="px-2 py-0.5 rounded-md text-[11px] bg-purple-100 text-purple-800 font-bold">Payroll</span>
                            <span v-else class="px-2 py-0.5 rounded-md text-[11px] bg-green-100 text-green-800 font-bold">Direct</span>
                        </td>
                        <td class="px-6 py-4 text-sm font-black text-gray-900">
                            {{ formatCurrency(item.approved_amount || item.amount) }}
                            <div v-if="item.approved_amount && item.approved_amount < item.amount" class="text-xs text-red-500 line-through">
                                {{ formatCurrency(item.amount) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(item.updated_at) }}</td>
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
