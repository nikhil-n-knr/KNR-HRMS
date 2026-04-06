<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';

const items = ref({ data: [] });
const loading = ref(true);
const filters = ref({
    search: '',
    date_from: '',
    date_to: '',
    min_amount: '',
    max_amount: ''
});

const fetchHistory = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('hr.expenses.api.history'), { params: filters.value });
        items.value = res.data;
    } finally {
        loading.value = false;
    }
};

onMounted(fetchHistory);
let timeout = null;
watch(filters, () => {
    clearTimeout(timeout);
    timeout = setTimeout(fetchHistory, 500);
}, { deep: true });

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val || 0);
const formatDate = (date) => new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
</script>

<template>
    <div>
        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg border border-gray-100 shadow-sm mb-6">
            <h4 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">Advanced Filters</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                     <InputLabel value="Search Employee" class="text-xs mb-1" />
                     <TextInput v-model="filters.search" placeholder="Name..." class="w-full h-9 text-sm" />
                </div>
                <div>
                     <InputLabel value="Date Range" class="text-xs mb-1" />
                     <div class="flex gap-2">
                         <TextInput type="date" v-model="filters.date_from" class="w-full h-9 text-xs" />
                         <TextInput type="date" v-model="filters.date_to" class="w-full h-9 text-xs" />
                     </div>
                </div>
                 <div>
                     <InputLabel value="Amount Range" class="text-xs mb-1" />
                     <div class="flex gap-2">
                         <TextInput type="number" v-model="filters.min_amount" placeholder="Min" class="w-full h-9 text-xs" />
                         <TextInput type="number" v-model="filters.max_amount" placeholder="Max" class="w-full h-9 text-xs" />
                     </div>
                </div>
                <div class="flex items-end">
                    <!-- Export button typically calls a separate route -->
                    <a :href="route('hr.expenses.api.history', { ...filters, export: 1 })" class="w-full flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                        ⬇ Export CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- History Table -->
        <div v-if="loading" class="text-center p-8 text-gray-500">Loading History...</div>
         <div v-else class="overflow-x-auto bg-white rounded shadow-sm border border-gray-100">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Ref #</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Details</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase">Amount</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase">Reimbursed On</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="item in items.data" :key="item.id" class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-xs font-mono text-gray-500">#{{ item.id }}</td>
                        <td class="px-6 py-4">
                             <div class="text-sm font-medium text-gray-900">{{ item.employee?.user?.name || 'N/A' }}</div>
                             <div class="text-xs text-gray-500">{{ item.employee?.employee_id }}</div>
                        </td>
                        <td class="px-6 py-4">
                             <div class="text-sm text-gray-900">{{ item.title }}</div>
                             <div class="text-xs text-indigo-600">{{ item.category?.name }}</div>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-gray-900">
                            {{ formatCurrency(item.approved_amount || item.amount) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span v-if="item.status === 'Paid'" class="px-2 py-0.5 rounded text-xs bg-green-100 text-green-800 font-bold">Paid</span>
                            <span v-if="item.status === 'Rejected'" class="px-2 py-0.5 rounded text-xs bg-red-100 text-red-800 font-bold">Rejected</span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm text-gray-500">
                            {{ item.reimbursed_on ? formatDate(item.reimbursed_on) : '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>
         </div>
         
         <div class="mt-4 flex justify-between items-center" v-if="items.data.length > 0">
             <span class="text-xs text-gray-500">Showing {{ items.from }} to {{ items.to }} of {{ items.total }} entries</span>
             <!-- Pagination logic omitted for brevity -->
         </div>
    </div>
</template>
