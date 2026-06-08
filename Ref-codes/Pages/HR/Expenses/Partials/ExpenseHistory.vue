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
        <div class="bg-white p-4  -xl border border-gray-100 shadow-sm mb-6">
            <h4 class="text-sm font-black text-blue-900 mb-4 uppercase tracking-widest">Advanced Filters</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4 items-end">
                <div>
                     <InputLabel value="Search Employee" class="text-xs mb-1" />
                     <TextInput v-model="filters.search" placeholder="Name..." class="w-full h-10 rounded-lg border-gray-200 px-3 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div class="xl:col-span-2">
                     <InputLabel value="Date Range" class="text-xs mb-1" />
                     <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                         <TextInput type="date" v-model="filters.date_from" class="w-full h-10 rounded-lg border-gray-200 px-3 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                         <TextInput type="date" v-model="filters.date_to" class="w-full h-10 rounded-lg border-gray-200 px-3 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                     </div>
                </div>
                 <div class="xl:col-span-2">
                     <InputLabel value="Amount Range" class="text-xs mb-1" />
                     <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                         <TextInput type="number" v-model="filters.min_amount" placeholder="Min" class="w-full h-10 rounded-lg border-gray-200 px-3 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                         <TextInput type="number" v-model="filters.max_amount" placeholder="Max" class="w-full h-10 rounded-lg border-gray-200 px-3 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                     </div>
                </div>
                <div class="flex items-end">
                    <!-- Export button typically calls a separate route -->
                    <a :href="route('hr.expenses.api.history', { ...filters, export: 1 })" class="w-full h-10 flex justify-center items-center px-4 py-2 bg-blue-50 border border-blue-100 rounded-lg shadow-sm text-sm font-bold text-blue-700 hover:bg-blue-100 hover:text-blue-800 transition-colors">
                        ⬇ Export CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- History Table -->
        <div v-if="loading" class="text-center p-8 text-gray-500">Loading History...</div>
         <div v-else class="overflow-x-auto rounded-xl border border-gray-100 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-blue-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-[11px] font-black text-blue-900 uppercase tracking-widest">Ref #</th>
                        <th class="px-6 py-3.5 text-left text-[11px] font-black text-blue-900 uppercase tracking-widest">Employee</th>
                        <th class="px-6 py-3.5 text-left text-[11px] font-black text-blue-900 uppercase tracking-widest">Details</th>
                        <th class="px-6 py-3.5 text-left text-[11px] font-black text-blue-900 uppercase tracking-widest">Amount</th>
                        <th class="px-6 py-3.5 text-left text-[11px] font-black text-blue-900 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-3.5 text-left text-[11px] font-black text-blue-900 uppercase tracking-widest">Reimbursed On</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <tr v-for="item in items.data" :key="item.id" class="hover:bg-blue-50/30 transition">
                        <td class="px-6 py-4 text-xs font-mono text-gray-500">#{{ item.id }}</td>
                        <td class="px-6 py-4">
                             <div class="text-sm font-medium text-gray-900">{{ item.employee?.user?.name || 'N/A' }}</div>
                             <div class="text-xs text-gray-500">{{ item.employee?.employee_id }}</div>
                        </td>
                        <td class="px-6 py-4">
                             <div class="text-sm text-gray-900">{{ item.title }}</div>
                             <div class="text-xs text-indigo-600">{{ item.category?.name }}</div>
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-900">
                            {{ formatCurrency(item.approved_amount || item.amount) }}
                        </td>
                        <td class="px-6 py-4">
                            <span v-if="item.status === 'Paid'" class="px-2 py-0.5 rounded text-xs bg-green-100 text-green-800 font-bold">Paid</span>
                            <span v-if="item.status === 'Rejected'" class="px-2 py-0.5 rounded text-xs bg-red-100 text-red-800 font-bold">Rejected</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
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
