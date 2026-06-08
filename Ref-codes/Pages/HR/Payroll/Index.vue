<script setup>
import { computed, ref, onMounted, watch } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import SmartTabLayout from '@/Layouts/SmartTabLayout.vue';
import PayrollStepper from './Components/PayrollStepper.vue';
import BaseChart from '@/Components/BaseChart.vue';
import dayjs from 'dayjs';
import axios from 'axios';

const props = defineProps({
    payrolls: Array,
    stats: Object,
    filters: Object
});

const formFilters = ref({
    trashed: props.filters?.trashed === 'true' || props.filters?.trashed === true
});

watch(formFilters, (val) => {
    router.get(route('hr.payroll.index'), { trashed: val.trashed }, { preserveState: true, replace: true });
}, { deep: true });

const tabs = [
    { id: 'runs', label: 'History & Runs' },
    { id: 'wizard', label: 'Run Payroll (Wizard)' },
    { id: 'disbursement', label: 'Disbursement' },
    { id: 'all_payslips', label: 'All Payslips' },
    { id: 'settings', label: 'Settings' },
];

const page = usePage();

const activeTab = computed(() => {
    // Parse the current Inertia page URL
    const urlParams = new URLSearchParams(page.url.split('?')[1]);
    return urlParams.get('tab') || 'runs';
});

const wizardStep = ref(0);
const wizardSteps = ['Data Sync', 'Variable Pay', 'Calculate', 'Verification', 'Publish & Lock'];

const payrollId = computed(() => {
    const params = new URLSearchParams(page.url.split('?')[1]);
    return params.get('payroll_id');
});

// Watch for URL changes to update step (handling Inertia visits to self)
watch(() => page.url, (newUrl) => {
    const params = new URLSearchParams(newUrl.split('?')[1]);
    if (params.get('step')) {
        wizardStep.value = parseInt(params.get('step'));
    }
});

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('step')) {
        wizardStep.value = parseInt(params.get('step'));
    }
});

const formatCurrency = (amount) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amount || 0);
const formatDate = (date) => dayjs(date).format('MMM D, YYYY');

// Wizard Actions
const loadingSync = ref(false);
const syncData = ref(null);

const fetchSyncStats = async () => {
    loadingSync.value = true;
    try {
        const res = await axios.get(route('hr.payroll.sync-stats'));
        syncData.value = res.data;
    } catch (e) {
        console.error(e);
        alert('Failed to sync data. Please try again.');
    } finally {
        loadingSync.value = false;
    }
};

const nextStep = () => {
    if (wizardStep.value < wizardSteps.length - 1) wizardStep.value++;
};
const prevStep = () => {
    if (wizardStep.value > 0) wizardStep.value--;
};

const processing = ref(false);
const ignoreWarnings = ref(false);
const form = ref({
    month: dayjs().month() + 1, // Current Month (1-indexed)
    year: dayjs().year()
});

const months = [
    { value: 1, label: 'January' }, { value: 2, label: 'February' }, { value: 3, label: 'March' },
    { value: 4, label: 'April' }, { value: 5, label: 'May' }, { value: 6, label: 'June' },
    { value: 7, label: 'July' }, { value: 8, label: 'August' }, { value: 9, label: 'September' },
    { value: 10, label: 'October' }, { value: 11, label: 'November' }, { value: 12, label: 'December' }
];

const downloadSampleCsv = () => {
    // Quick CSV generation on client side or link to static asset
    const headers = ['Employee ID', 'Amount', 'Type (Bonus/Deduction/Commission)', 'Remarks'];
    const rows = [['EMP001', '5000', 'Bonus', 'Performance Reward']];
    const csvContent = "data:text/csv;charset=utf-8," 
        + headers.join(",") + "\n" + rows.map(e => e.join(",")).join("\n");
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "variable_pay_sample.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const showRecalculateOption = ref(false);

const runPayroll = (options = {}) => {
    try {
        const recalculate = options.recalculate || false;
        
        // Skip confirm if recalculating (we show a different confirm below)
        if (!recalculate) {
             const monthObj = months.find(m => m.value == form.value.month);
             const monthLabel = monthObj ? monthObj.label : 'Current Month';
             if (!confirm(`Are you sure you want to start payroll processing for ${monthLabel} ${form.value.year}?`)) return;
        } else {
             if (!confirm(`Warning: This will DELETE the existing draft and all manual changes. Continue?`)) return;
        }
        
        processing.value = true;
        showRecalculateOption.value = false; // Reset

        router.post(route('hr.payroll.store'), {
            month: form.value.month,
            year: form.value.year,
            ignore_warnings: ignoreWarnings.value,
            recalculate: recalculate
        }, {
            onSuccess: (page) => {
                processing.value = false; // Stop spinner
                // URL watcher will pick up the new step
            },
            onError: (errors) => {
                console.error(errors);
                processing.value = false;
                let msg = errors.message || Object.values(errors).join('\n') || 'Unknown Error';
                
                // Check if specific error
                if (msg.includes('already exists') || msg.includes('Already exists')) {
                    showRecalculateOption.value = true;
                }
                
                // Don't alert if we are showing the custom UI for recalculate
                if (!showRecalculateOption.value) {
                     alert('Failed to start payroll:\n' + msg);
                }
            }
        });
    } catch (e) {
        console.error(e);
        alert('Client-side Error: ' + e.message);
        processing.value = false;
    }
};

const deletePayroll = (payroll) => {
    if (confirm(`Are you sure you want to DELETE the payroll run: "${payroll.batch_name}"? This will remove all associated payslips.`)) {
        router.delete(route('hr.payroll.destroy', payroll.id));
    }
};

const restorePayroll = (payroll) => {
    if (confirm(`Restore payroll run: "${payroll.batch_name}"?`)) {
        router.post(route('hr.payroll.restore', payroll.id));
    }
};

const unpublishPayroll = (payroll) => {
    if (confirm(`Unpublish payroll run: "${payroll.batch_name}"?\nThis will hide it from employees but keep it as Approved.`)) {
        router.post(route('hr.payroll.unpublish', payroll.id));
    }
};
</script>

<template>
    <SmartTabLayout 
        title="Payroll Management" 
        :tabs="tabs" 
        :activeTab="activeTab"
    >
        <template #meta>
             Cycle: Monthly • Next Run: Feb 2026
        </template>

        <template #actions>
             <Link :href="route('hr.finance.hub')" class="text-sm font-semibold text-white bg-white/10 hover:bg-white/20 px-3 py-2 rounded-xl border border-white/20 transition-all shadow-sm">
                &larr; Back to Hub
             </Link>
             <Link :href="route('hr.payroll.bulk')" class="text-sm font-semibold text-slate-900 bg-white px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-100 transition-all shadow-sm">
                Bulk Salary Update
             </Link>
             <button v-if="activeTab === 'runs'" @click="router.visit('?tab=wizard')" class="px-3 py-2 bg-white text-slate-900 rounded-xl hover:bg-slate-50 shadow-lg font-semibold transition-all">
                + New Run
             </button>
        </template>

        <!-- Tab: Runs (History) -->
<div v-if="activeTab === 'runs'" class="space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                <div class="bg-white/60 backdrop-blur-xl rounded-2xl p-5 md:p-6 border border-gray-200 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="absolute right-0 top-0 h-full w-1.5 bg-gradient-to-b from-blue-400 to-blue-600"></div>
                    <p class="text-sm md:text-xs font-black text-gray-500 uppercase tracking-widest">Total Payout (YTD)</p>
                    <p class="text-xl md:text-3xl font-black text-slate-900 mt-2 truncate">{{ formatCurrency(stats?.total_payout_ytd) }}</p>
                    <div class="mt-4 flex items-center text-sm font-bold text-green-600 bg-green-50 w-fit px-2 py-0.5 rounded-full">
                        ↑ 12% vs LY
                    </div>
                </div>
                 <div class="bg-white/60 backdrop-blur-xl rounded-2xl p-5 md:p-6 border border-gray-200 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="absolute right-0 top-0 h-full w-1.5 bg-gradient-to-b from-emerald-400 to-emerald-600"></div>
                    <p class="text-sm md:text-xs font-black text-gray-500 uppercase tracking-widest">Active Employees</p>
                    <p class="text-xl md:text-3xl font-black text-slate-900 mt-2">{{ stats?.active_salaries || 0 }}</p>
                    <div class="mt-4 flex items-center text-sm font-bold text-blue-600 bg-blue-50 w-fit px-2 py-0.5 rounded-full">
                        Total Staff
                    </div>
                </div>
                 <div class="bg-white/60 backdrop-blur-xl rounded-2xl p-5 md:p-6 border border-gray-200 shadow-sm relative overflow-hidden group hover:shadow-md transition-all col-span-2 lg:col-span-1">
                    <div class="absolute right-0 top-0 h-full w-1.5 bg-gradient-to-b from-purple-400 to-purple-600"></div>
                    <p class="text-sm md:text-xs font-black text-gray-500 uppercase tracking-widest">Pending Approvals</p>
                    <p class="text-xl md:text-3xl font-black text-slate-900 mt-2">0</p>
                    <div class="mt-4 flex items-center text-sm font-bold text-gray-500 bg-gray-50 w-fit px-2 py-0.5 rounded-full">
                        All Verified
                    </div>
                </div>
            </div>

            <!-- List -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-blue-50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">
                            {{ formFilters.trashed ? 'Deleted Payroll Runs' : 'Payroll Batches' }}
                        </h3>
                        <p class="text-xs text-gray-500 font-medium">History of all executed and draft payroll cycles</p>
                    </div>
                    <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="formFilters.trashed" class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ms-3 text-xs font-black uppercase tracking-widest text-slate-600">Deleted</span>
                        </label>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="block md:hidden">
                    <div v-for="payroll in payrolls" :key="payroll.id" class="p-4 border-b border-gray-100 last:border-0 hover:bg-indigo-50/30 transition-all">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h4 class="text-sm font-black text-slate-900">{{ payroll.batch_name }}</h4>
                                <p class="text-sm text-gray-500 font-bold uppercase tracking-wider">{{ formatDate(payroll.start_date) }} - {{ formatDate(payroll.end_date) }}</p>
                            </div>
                            <span class="px-2.5 py-1 text-sm rounded-full font-black uppercase tracking-widest shadow-sm bg-green-100 text-green-800">{{ payroll.status }}</span>
                        </div>
                        <div class="flex justify-between items-center bg-gray-50 p-2.5 rounded-xl border border-gray-100 mb-4">
                            <span class="text-sm font-black text-gray-500 uppercase tracking-widest">Payout</span>
                            <span class="text-sm font-black text-slate-900">{{ formatCurrency(payroll.total_payout) }}</span>
                        </div>
                        <div class="flex gap-2">
                             <Link v-if="!formFilters.trashed" :href="route('hr.payroll.show', payroll.id)" class="flex-1 bg-white border border-gray-200 text-indigo-600 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest text-center shadow-sm hover:border-indigo-200 hover:bg-indigo-50/50 transition-all">
                                Open Run
                             </Link>
                             
                             <button v-if="!formFilters.trashed && payroll.status === 'Published'" @click="unpublishPayroll(payroll)" class="p-2.5 bg-white border border-gray-200 text-amber-600 rounded-xl hover:bg-amber-50 transition-all shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                             </button>

                             <button v-if="!formFilters.trashed" @click="deletePayroll(payroll)" class="p-2.5 bg-white border border-gray-200 text-red-600 rounded-xl hover:bg-red-50 transition-all shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                             </button>

                             <button v-if="formFilters.trashed" @click="restorePayroll(payroll)" class="flex-1 bg-emerald-600 text-white py-2.5 rounded-xl text-xs font-black uppercase tracking-widest text-center shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                Restore
                             </button>
                        </div>
                    </div>
                    <div v-if="!payrolls || payrolls.length === 0" class="p-12 text-center text-gray-400 italic text-sm">No payroll records found.</div>
                </div>

                <!-- Desktop View --><!-- Desktop View -->
<div class="hidden md:block bg-white rounded-b-2xl border-t border-gray-100">

    <!-- Scroll Container -->
    <div class="max-h-[500px] overflow-y-auto">

        <table class="w-full min-w-full divide-y divide-gray-100">

            <!-- Sticky Header -->
            <thead class="bg-blue-50 sticky top-0 z-10 shadow-sm">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-black text-blue-900 uppercase tracking-widest">
                        Batch details
                    </th>

                    <th class="px-6 py-4 text-left text-xs font-black text-blue-900 uppercase tracking-widest">
                        Processing Period
                    </th>

                    <th class="px-6 py-4 text-left text-xs font-black text-blue-900 uppercase tracking-widest">
                        Total Net Payout
                    </th>

                    <th class="px-6 py-4 text-left text-xs font-black text-blue-900 uppercase tracking-widest">
                        Status
                    </th>

                    <th class="relative px-6 py-4">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-50">
                <tr
                    v-for="payroll in payrolls"
                    :key="payroll.id"
                    class="hover:bg-indigo-50/30 transition-colors group"
                >
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-black text-slate-800">
                            {{ payroll.batch_name }}
                        </div>

                        <div class="text-sm text-gray-400 font-bold uppercase tracking-tighter">
                            BCH-{{ payroll.id.toString().padStart(4, '0') }}
                        </div>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium tracking-tight">
                        {{ formatDate(payroll.start_date) }} -
                        {{ formatDate(payroll.end_date) }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-slate-900 tracking-tight">
                        {{ formatCurrency(payroll.total_payout) }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="px-3 py-1 text-sm rounded-full font-black uppercase tracking-widest shadow-sm"
                            :class="{
                                'bg-emerald-100 text-emerald-800': payroll.status === 'Published',
                                'bg-amber-100 text-amber-800': payroll.status === 'Draft' || payroll.status === 'Approved',
                                'bg-indigo-100 text-indigo-800': payroll.status === 'Processing'
                            }"
                        >
                            {{ payroll.status }}
                        </span>
                    </td>

                <td class="px-6 py-4 text-right text-sm">
    <div class="flex items-center justify-end gap-2">

        <!-- View / Eye Button -->
        <Link
            v-if="!formFilters.trashed"
            :href="route('hr.payroll.show', payroll.id)"
            class="p-2.5 bg-white border border-gray-200 text-indigo-600 rounded-xl hover:bg-indigo-50 transition-all shadow-sm"
            title="View Payroll"
        >
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5
                         c4.478 0 8.268 2.943 9.542 7
                         -1.274 4.057-5.064 7-9.542 7
                         -4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        </Link>

        <!-- Unpublish -->
        <button
            v-if="!formFilters.trashed && payroll.status === 'Published'"
            @click="unpublishPayroll(payroll)"
            class="p-2.5 bg-white border border-gray-200 text-amber-600 rounded-xl hover:bg-amber-50 transition-all shadow-sm"
            title="Unpublish"
        >
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13.875 18.825A10.05 10.05 0 0112 19
                         c-4.478 0-8.268-2.943-9.543-7
                         a9.97 9.97 0 011.563-3.029
                         m5.858.908a3 3 0 114.243 4.243
                         M9.878 9.878l4.242 4.242
                         M9.88 9.88l-3.29-3.29
                         m7.532 7.532l3.29 3.29
                         M3 3l3.59 3.59
                         m0 0A9.953 9.953 0 0112 5
                         c4.478 0 8.268 2.943 9.543 7
                         a10.025 10.025 0 01-4.132 5.411
                         m0 0L21 21" />
            </svg>
        </button>

        <!-- Delete -->
        <button
            v-if="!formFilters.trashed"
            @click="deletePayroll(payroll)"
            class="p-2.5 bg-white border border-gray-200 text-red-600 rounded-xl hover:bg-red-50 transition-all shadow-sm"
            title="Delete"
        >
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 7l-.867 12.142
                         A2 2 0 0116.138 21H7.862
                         a2 2 0 01-1.995-1.858L5 7
                         m5 4v6m4-6v6
                         m1-10V4a1 1 0 00-1-1h-4
                         a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </button>

        <!-- Restore -->
        <button
            v-if="formFilters.trashed"
            @click="restorePayroll(payroll)"
            class="p-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-all shadow-sm"
            title="Restore"
        >
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M4 4v5h.582
                         m15.356 2A8.001 8.001 0 004.582 9
                         m0 0H9m11 11v-5h-.581
                         m0 0a8.003 8.003 0 01-15.357-2
                         m15.357 2H15" />
            </svg>
        </button>

    </div>
</td>
                </tr>

                <tr v-if="!payrolls || payrolls.length === 0">
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic text-sm">
                        No history found. Start a new run.
                    </td>
                </tr>
            </tbody>

        </table>

    </div>
</div>
        </div>
        </div>  

        <!-- Tab: Wizard -->
        <div v-if="activeTab === 'wizard'" class="space-y-8 animate-fade-in">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <PayrollStepper :steps="wizardSteps" :currentStep="wizardStep" />
                
                <div class="mt-8 border-t border-gray-100 pt-8 min-h-[300px]">
                    <!-- Step 1: Sync & Verify -->
                    <div v-if="wizardStep === 0" class="text-center space-y-6">
                        <div class="h-16 w-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto text-blue-500">
                             <svg v-if="!loadingSync" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                             <div v-else class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
                        </div>
                        
                        <div v-if="!syncData">
                            <h3 class="text-lg font-bold text-gray-800">Synchronize Data</h3>
                            <p class="text-gray-500 max-w-md mx-auto mb-6">Pulling latest Attendance records, Leave balances, and new Joiner details to ensure accuracy.</p>
                            
                            <div class="max-w-xs mx-auto bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4 text-left">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Select Payroll Period</label>
                                <div class="flex gap-2">
                                    <select v-model="form.month" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                                    </select>
                                    <input type="number" v-model="form.year" class="block w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Year">
                                </div>
                            </div>
                            
                            <button @click="fetchSyncStats" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-sm transition-transform hover:scale-105" :disabled="loadingSync">
                                {{ loadingSync ? 'Syncing...' : 'Start Sync' }}
                            </button>
                        </div>

                        <div v-else class="space-y-4 animate-fade-in">
                            <div class="flex justify-between items-center max-w-4xl mx-auto px-2">
                                <h3 class="text-lg font-bold text-gray-800">
                                    {{ months.find(m => m.value == form.month).label }} {{ form.year }} Readiness Check
                                </h3>
                                <button @click="syncData = null" class="text-xs text-indigo-600 hover:underline">Change Period</button>
                            </div>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <p class="text-xs text-gray-500 uppercase">Employees</p>
                                    <p class="text-2xl font-bold">{{ syncData.total_employees }}</p>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                                    <p class="text-xs text-green-700 uppercase">New Joiners</p>
                                    <p class="text-2xl font-bold text-green-800">{{ syncData.new_joiners }}</p>
                                </div>
                                <div class="bg-red-50 p-4 rounded-lg border border-red-100">
                                    <p class="text-xs text-red-700 uppercase">Exits</p>
                                    <p class="text-2xl font-bold text-red-800">{{ syncData.exits }}</p>
                                </div>
                                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-100">
                                    <p class="text-xs text-yellow-700 uppercase">LWP Days</p>
                                    <p class="text-2xl font-bold text-yellow-800">{{ syncData.lwp_days || 0 }}</p>
                                </div>
                            </div>

                            <div v-if="syncData.pending_leaves > 0" class="bg-amber-50 border border-amber-200 p-4 rounded-md text-sm text-amber-800 max-w-2xl mx-auto flex items-start">
                                 <span class="mr-2 text-xl">⚠️</span>
                                 <div>
                                    <p class="font-bold">Warning: {{ syncData.pending_leaves }} Pending Leave Requests found.</p>
                                    <p>Processing payroll now may result in incorrect LOP deduction. Please approve/reject pending leaves first.</p>
                                 </div>
                            </div>

                            <button @click="nextStep" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium shadow-sm transition-transform hover:scale-105">
                                Contine to Variable Pay &rarr;
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Variable Pay -->
                    <div v-if="wizardStep === 1" class="text-center space-y-4">
                        <h3 class="text-lg font-bold text-gray-800">Variable Pay & Deductions</h3>
                        <p class="text-gray-500">Import bonuses, sales commissions, or one-time deductions.</p>
                        
                        <div class="flex justify-center gap-4">
                            <button @click="downloadSampleCsv" class="text-sm text-indigo-600 hover:underline flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                Download Sample CSV
                            </button>
                        </div>

                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 bg-gray-50 transition-colors hover:bg-gray-100 hover:border-indigo-400 cursor-pointer">
                            <p class="text-gray-400">Drag & Drop Excel File or <span class="text-indigo-600 underline">Browse</span></p>
                        </div>
                        
                        <div class="pt-4">
                            <button @click="nextStep" class="text-gray-500 hover:text-gray-800 text-sm">Skip this step</button>
                        </div>
                    </div>

                     <!-- Step 3: Calculate -->
                    <div v-if="wizardStep === 2" class="text-center space-y-6">
                         <div class="h-16 w-16 bg-purple-50 rounded-full flex items-center justify-center mx-auto text-purple-500" :class="{'animate-pulse': processing}">
                            <svg v-if="!processing" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                            <div v-else class="animate-spin h-8 w-8 border-4 border-purple-500 border-t-transparent rounded-full"></div>
                        </div>
                        
                        <div v-if="!processing">
                            <h3 class="text-lg font-bold text-gray-800">Ready to Process</h3>
                            <p class="text-gray-500">
                                This will run the payroll engine for <span class="font-bold text-gray-800">{{ syncData?.total_employees || 'all' }} employees</span>.
                                <br>Updates tax slabs, PF, ESI, and integrates variable pay.
                            </p>
                            
                            <div class="flex items-center justify-center gap-2 mt-4" v-if="syncData?.pending_leaves > 0">
                                <input type="checkbox" id="ignore" v-model="ignoreWarnings" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="ignore" class="text-sm text-gray-600">Ignore {{ syncData.pending_leaves }} pending leave warnings and proceed</label>
                            </div>

                            <button @click="runPayroll" class="mt-6 px-8 py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 font-bold shadow-lg transition-transform hover:scale-105">
                                Start Calculation
                            </button>
                            
                            <!-- Recalculate Option -->
                            <div v-if="showRecalculateOption" class="mt-6 bg-amber-50 border-l-4 border-amber-500 p-4 text-left max-w-lg mx-auto animate-fade-in">
                                <p class="font-bold text-amber-800">Payroll Draft Already Exists!</p>
                                <p class="text-sm text-amber-700 mt-1">A draft for this month was found. Do you want to wipe it and start fresh?</p>
                                <div class="mt-3">
                                    <button @click="runPayroll({ recalculate: true })" class="px-4 py-2 bg-amber-600 text-white text-sm font-bold rounded shadow hover:bg-amber-700">
                                        Yes, Recalculate
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else>
                            <h3 class="text-lg font-bold text-gray-800">Processing in Background...</h3>
                            <p class="text-gray-500 mb-4">Calculations are in progress...</p>
                             <div class="w-64 mx-auto bg-gray-200 rounded-full h-2 mt-4 overflow-hidden">
                                <div class="bg-purple-600 h-2 rounded-full animate-progress" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>

                     <!-- Step 3: Verification (Success) -->
                    <div v-if="wizardStep === 3" class="text-center py-10 animate-fade-in">
                        <div class="h-20 w-20 bg-green-50 rounded-full flex items-center justify-center mx-auto text-green-500 mb-6">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        
                        <h3 class="text-2xl font-bold text-gray-800">Payroll Draft Generated!</h3>
                        <p class="text-gray-500 max-w-md mx-auto mt-2 mb-8">
                            Calculations are complete. You can now verify individual payslips, make manual adjustments, or publish the batch.
                        </p>
                        
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a :href="payrollId ? route('hr.payroll.show', payrollId) : route('hr.payroll.index')" 
                               class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm"
                            >
                                Verify & Publish Details &rarr;
                            </a>
                            
                            <button @click="wizardStep = 0; form.month++" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Start Another Run
                            </button>
                        </div>
                    </div>

                    <!-- Step 4 Placeholder -->
                    <div v-if="wizardStep > 3" class="text-center py-10 text-gray-400">
                        Publish & Lock Module Placeholder
                    </div>
                </div>

                <!-- Footer Nav -->
                <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-100">
                    <button 
                        @click="prevStep" 
                        :disabled="wizardStep === 0"
                        class="px-4 py-2 text-gray-600 hover:text-gray-900 disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                    >
                        Back
                    </button>
                    <button 
                        @click="nextStep"
                        v-if="wizardStep !== 0"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium shadow-sm"
                    >
                        {{ wizardStep === wizardSteps.length - 1 ? 'Finish' : 'Next Step' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="activeTab === 'disbursement'" class="space-y-6">
             <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 flex items-start gap-4">
                <div class="p-3 bg-blue-100 rounded-xl text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                     <h3 class="text-sm font-black text-blue-900 uppercase tracking-widest">Bank Transfer Files</h3>
                     <p class="text-xs text-blue-700 mt-1 font-medium">Download payment instruction files (HDFC/ICICI compatible) for "Released" payrolls.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Mobile Card View -->
                <div class="block md:hidden">
                    <div v-for="payroll in payrolls.filter(p => ['Paid', 'Published'].includes(p.status))" :key="payroll.id" class="p-4 border-b border-gray-100 last:border-0">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h4 class="text-sm font-black text-slate-900">{{ payroll.batch_name }}</h4>
                                <p class="text-sm text-gray-500 font-bold uppercase tracking-widest">{{ formatDate(payroll.created_at) }}</p>
                            </div>
                            <span class="text-sm font-black text-emerald-600">{{ formatCurrency(payroll.total_payout) }}</span>
                        </div>
                        <a :href="route('hr.payroll.bank-transfer', payroll.id)" target="_blank" class="w-full flex items-center justify-center gap-2 bg-indigo-600 text-white py-2.5 rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                             Download Bank File
                        </a>
                    </div>
                </div>

                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto bg-white rounded-b-2xl border-t border-gray-100">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-blue-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-black text-blue-900 uppercase tracking-widest">Batch name</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-blue-900 uppercase tracking-widest">Generation Date</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-blue-900 uppercase tracking-widest">Released Amount</th>
                                <th class="px-6 py-4 text-right text-xs font-black text-blue-900 uppercase tracking-widest">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                             <tr v-for="payroll in payrolls.filter(p => ['Paid', 'Published'].includes(p.status))" :key="payroll.id" class="hover:bg-indigo-50/30 transition-all">
                                 <td class="px-6 py-4 text-sm font-black text-gray-900">{{ payroll.batch_name }}</td>
                                 <td class="px-6 py-4 text-sm text-gray-500 font-medium tracking-tight">{{ formatDate(payroll.created_at) }}</td>
                                 <td class="px-6 py-4 text-sm font-black text-emerald-700 tracking-tight">{{ formatCurrency(payroll.total_payout) }}</td>
                                 <td class="px-6 py-4 text-right">
                                     <a :href="route('hr.payroll.bank-transfer', payroll.id)" target="_blank" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-900 text-xs font-black uppercase tracking-widest bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100 transition-all">
                                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                         Download HDFC
                                     </a>
                                 </td>
                             </tr>
                        </tbody>
                    </table>
                </div>
                 <div v-if="payrolls.filter(p => ['Paid', 'Published'].includes(p.status)).length === 0" class="p-12 text-center text-gray-400 italic text-sm">
                    No finalized payrolls available for disbursement.
                 </div>
            </div>
        </div>

        <div v-if="activeTab === 'all_payslips'" class="space-y-6">
             <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <div>
                     <h3 class="text-sm font-bold text-indigo-800">Global Search</h3>
                     <p class="text-xs text-indigo-600 mt-1">Search for payslips across all monthly cycles and batches.</p>
                </div>
            </div>

            <div class="bg-white p-12 rounded-xl shadow-sm border border-gray-200 text-center">
                <h3 class="text-lg font-bold text-gray-800 mb-2">Access Global Records</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">Open the dedicated global search portal to find specific records using employee names or payslip numbers.</p>
                <Link :href="route('hr.payroll.all-payslips')" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium shadow-sm">
                    Open Search Portal &rarr;
                </Link>
            </div>
        </div>

       <div v-if="activeTab === 'settings'" class="max-w-4xl mx-auto space-y-6">

    <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
        
        <div class="px-6 py-5 border-b border-gray-100 bg-blue-50">
            <h3 class="text-lg font-black text-slate-900 tracking-tight">
                Payroll Configuration
            </h3>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="bg-gray-50 border border-gray-100 rounded-2xl p-5 hover:shadow-sm transition-all">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-3">
                        Pay Schedule
                    </label>

                    <select class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option>Monthly (Default)</option>
                        <option>Bi-Weekly</option>
                    </select>

                    <p class="mt-3 text-xs text-gray-500">
                        Only Monthly is supported in Phase 1.
                    </p>
                </div>

                <div class="bg-gray-50 border border-gray-100 rounded-2xl p-5 hover:shadow-sm transition-all">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-3">
                        Payout Date
                    </label>

                    <select class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option>Last Working Day</option>
                        <option>1st of Next Month</option>
                        <option>5th of Next Month</option>
                        <option>10th of Next Month</option>
                    </select>
                </div>

            </div>
        </div>
    </div>

    <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-100 bg-emerald-50">
            <h3 class="text-lg font-black text-slate-900 tracking-tight">
                Statutory & Compliance
            </h3>
        </div>

        <div class="p-6 space-y-4">

            <div class="flex items-center justify-between bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 hover:bg-indigo-50/40 transition-all">
                <div>
                    <p class="text-sm font-black text-slate-900">
                        Provident Fund (PF)
                    </p>

                    <p class="text-xs text-gray-500 mt-1">
                        12% Employee Contribution (Capped at 15k)
                    </p>
                </div>

                <button class="px-4 py-2 rounded-xl bg-white border border-indigo-200 text-indigo-600 hover:bg-indigo-600 hover:text-white text-xs font-black uppercase tracking-widest transition-all shadow-sm">
                    Edit Rules
                </button>
            </div>

            <div class="flex items-center justify-between bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 hover:bg-emerald-50/40 transition-all">
                <div>
                    <p class="text-sm font-black text-slate-900">
                        ESI (Employee State Insurance)
                    </p>

                    <p class="text-xs text-gray-500 mt-1">
                        0.75% Employee Contribution (Gross ≤ 21k)
                    </p>
                </div>

                <button class="px-4 py-2 rounded-xl bg-white border border-emerald-200 text-emerald-600 hover:bg-emerald-600 hover:text-white text-xs font-black uppercase tracking-widest transition-all shadow-sm">
                    Edit Rules
                </button>
            </div>

            <div class="flex items-center justify-between bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 hover:bg-amber-50/40 transition-all">
                <div>
                    <p class="text-sm font-black text-slate-900">
                        Professional Tax (PT)
                    </p>

                    <p class="text-xs text-gray-500 mt-1">
                        State-wise Slab Logic (Karnataka, Maharashtra, etc.)
                    </p>
                </div>

                <button class="px-4 py-2 rounded-xl bg-white border border-amber-200 text-amber-600 hover:bg-amber-600 hover:text-white text-xs font-black uppercase tracking-widest transition-all shadow-sm">
                    Edit Rules
                </button>
            </div>

        </div>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5 shadow-sm">
        <p class="text-sm text-yellow-800">
            <strong>Note:</strong> Advanced tax slab configuration is handled via the separate "Tax Engine" module.
        </p>
    </div>

</div>

    </SmartTabLayout>
</template>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes progress {
    0% { width: 0% }
    100% { width: 100% }
}
.animate-progress {
    animation: progress 2s ease-in-out infinite;
}
</style>
