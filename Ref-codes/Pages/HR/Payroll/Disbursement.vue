<template>
    <Head title="Disbursement Hub" />
    <MainLayout>
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Hub Header -->
            <div class="mb-8 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div class="h-14 w-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-xl">
                        <CurrencyRupeeIcon class="h-8 w-8" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Disbursement Hub</h1>
                        <p class="text-sm font-medium text-gray-500">Host-to-Host (H2H) Payout Management</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-black uppercase tracking-widest border border-green-200">API: Connected</span>
                </div>
            </div>

            <!-- Smart Tabs -->
            <div class="bg-white/80 backdrop-blur-xl border border-white/20 shadow-2xl rounded-3xl overflow-hidden">
                <div class="border-b border-gray-100 px-8 py-2 bg-gray-50/50 flex space-x-8">
                    <button 
                        v-for="tab in tabs" 
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            activeTab === tab.id 
                            ? 'border-indigo-500 text-indigo-600' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'py-4 px-1 border-b-2 font-bold text-sm transition-all duration-200 uppercase tracking-widest'
                        ]"
                    >
                        {{ tab.name }}
                    </button>
                </div>

                <div class="p-8">
                    <!-- Tab 1: Batch Files (Legacy) -->
                    <div v-if="activeTab === 'batch'" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <h3 class="text-lg font-bold text-gray-800">Export Payout Batches</h3>
                                <div class="p-6 bg-gray-50 rounded-3xl border border-gray-100 space-y-4">
                                    <label class="block text-xs font-bold text-gray-500 uppercase">Select Payroll Cycle</label>
                                    <select v-model="selectedPayrollId" class="block w-full border-gray-200 rounded-xl font-bold focus:ring-indigo-500">
                                        <option v-for="p in payrolls" :key="p.id" :value="p.id">
                                            {{ p.month_name }} {{ p.year }} ({{ p.status }})
                                        </option>
                                    </select>
                                    <button class="w-full py-3 bg-white border border-gray-200 rounded-2xl text-sm font-bold text-gray-700 hover:bg-gray-100 shadow-sm transition-all flex items-center justify-center">
                                        <ArrowDownTrayIcon class="h-4 w-4 mr-2" /> Download HDFC CSV File
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <h3 class="text-lg font-bold text-gray-800">Import Bank Response</h3>
                                <div class="p-6 bg-indigo-50/30 rounded-3xl border border-indigo-100 space-y-4">
                                    <p class="text-xs text-indigo-700">Sync payment status by uploading the bank's confirmation file.</p>
                                    <input type="file" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-indigo-600 file:text-white hover:file:bg-indigo-700" />
                                    <button class="w-full py-3 bg-indigo-600 rounded-2xl text-sm font-bold text-white shadow-xl shadow-indigo-100 hover:bg-indigo-700">Process & Sync Status</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: H2H API Payouts -->
                    <div v-if="activeTab === 'api'" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                         <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-800">Direct H2H Transfers</h3>
                            <div class="flex space-x-2">
                                <button class="px-4 py-2 bg-indigo-600 rounded-xl text-xs font-bold text-white shadow-lg hover:bg-indigo-700">Initiate Bulk API Payout</button>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-gray-100">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-500 uppercase tracking-widest">Employee</th>
                                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-500 uppercase tracking-widest">Bank Details</th>
                                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-500 uppercase tracking-widest">Net Amount</th>
                                        <th class="px-6 py-3 text-right text-sm font-bold text-gray-500 uppercase tracking-widest">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-50">
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900">Demo Employee</div>
                                            <div class="text-sm text-gray-500">ID: EMP001</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-xs font-bold text-gray-700 italic">HDFC • 501002XXX345</div>
                                            <div class="text-sm text-indigo-500 font-medium">HDFC0001234</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-gray-900">₹45,000.00</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded text-sm font-black tracking-widest uppercase">Awaiting Action</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    CurrencyRupeeIcon, 
    ArrowDownTrayIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    payrolls: Array
});

const activeTab = ref('batch');
const tabs = [
    { id: 'batch', name: 'File Exports' },
    { id: 'api', name: 'API Transfers' },
    { id: 'monitoring', name: 'Real-time Monitor' }
];

const selectedPayrollId = ref(props.payrolls.length ? props.payrolls[0].id : null);
</script>
