<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import { 
    BanknotesIcon, 
    CheckBadgeIcon as CheckSealIcon, 
    ArrowPathIcon, 
    MagnifyingGlassIcon,
    FunnelIcon,
    CurrencyRupeeIcon,
    UserCircleIcon,
    CalendarDaysIcon,
    ClockIcon,
    WalletIcon,
    ArrowUpTrayIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    expenses: Object,
    filters: Object
});

const filters = ref({ ...props.filters });
const selectedIds = ref([]);
const showSettleModal = ref(false);
const showPayrollModal = ref(false);

const payrollForm = useForm({
    ids: [],
    target_month: new Date().toISOString().slice(0, 7) // YYYY-MM
});

const settleForm = useForm({
    ids: [],
    transaction_reference: '',
    settlement_date: new Date().toISOString().substr(0, 10)
});

watch(filters, (val) => {
    router.get(route('expenses.settlement'), val, { preserveState: true, replace: true });
}, { deep: true });

const allSelected = computed(() => {
    return props.expenses.data.length > 0 && selectedIds.value.length === props.expenses.data.length;
});

const toggleAll = () => {
    if (allSelected.value) selectedIds.value = [];
    else selectedIds.value = props.expenses.data.map(e => e.id);
};

const openPayrollModal = () => {
    if (selectedIds.value.length === 0) return;
    showPayrollModal.value = true;
};

const submitPayroll = () => {
    payrollForm.ids = selectedIds.value;
    payrollForm.post(route('expenses.settlement.payroll'), {
        onSuccess: () => {
            showPayrollModal.value = false;
            selectedIds.value = [];
        }
    });
};

const submitSettlement = () => {
    settleForm.ids = selectedIds.value;
    settleForm.post(route('expenses.settlement.pay'), {
        onSuccess: () => {
            showSettleModal.value = false;
            selectedIds.value = [];
            settleForm.reset();
        }
    });
};

const formatDate = (date) => new Date(date).toLocaleDateString();
const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val);
</script>

<template>
    <Head title="Expense Settlement Console" />
    <MainLayout>
        <div class="max-w-7xl mx-auto space-y-8 font-outfit px-4 md:px-8 pb-20">
            <!-- Strategic Header -->
            <div class="py-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8 border-b border-slate-100">
                <div class="flex items-center gap-6">
                    <div class="p-5 bg-slate-900 border border-slate-800 rounded-[2rem] text-emerald-400 shadow-2xl shadow-slate-200/50 relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <BanknotesIcon class="w-10 h-10 relative z-10" />
                    </div>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-4">
                            Settlement Matrix
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-black bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-widest shadow-sm">Liquidation Phase</span>
                        </h1>
                        <p class="text-base font-black text-slate-400 uppercase tracking-[0.3em] mt-2 block">Enterprise financial reconciliation & disbursement logic</p>
                    </div>
                </div>

                <!-- Strategic Intelligence Bar -->
                <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto items-center">
                    <div class="bg-white/80 backdrop-blur-md p-2 rounded-2xl flex items-center border border-slate-100 shadow-xl shadow-slate-200/30 w-full sm:w-auto">
                        <div class="px-4 text-slate-400">
                            <MagnifyingGlassIcon class="w-5 h-5" />
                        </div>
                        <input v-model="filters.search" placeholder="SEARCH_OPERATIVE_NODE..." class="bg-transparent border-none text-base font-black text-slate-700 focus:ring-0 px-2 py-2.5 uppercase tracking-widest w-full min-w-[200px]">
                    </div>

                    <div class="bg-white/80 backdrop-blur-md p-2 rounded-2xl flex items-center border border-slate-100 shadow-xl shadow-slate-200/30 w-full sm:w-auto">
                        <div class="px-3 text-slate-400">
                            <FunnelIcon class="w-5 h-5" />
                        </div>
                        <select v-model="filters.payout_method" class="bg-transparent border-none text-sm font-black text-slate-600 focus:ring-0 px-4 py-2 uppercase tracking-widest cursor-pointer hover:bg-slate-50 transition-all appearance-none rounded-xl min-w-[140px]">
                            <option value="">ALL_METHODS</option>
                            <option value="payroll">PAYROLL_INJECTION</option>
                            <option value="direct">DIRECT_SETTLEMENT</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Registry Module -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/50 overflow-hidden min-h-[600px] flex flex-col relative group">
                <!-- Selection Toolbar Overlay -->
                <Transition
                    enter-active-class="transition duration-500 ease-out"
                    enter-from-class="transform -translate-y-full opacity-0"
                    enter-to-class="transform translate-y-0 opacity-100"
                    leave-active-class="transition duration-300 ease-in"
                    leave-from-class="transform translate-y-0 opacity-100"
                    leave-to-class="transform -translate-y-full opacity-0"
                >
                    <div v-if="selectedIds.length > 0" class="absolute top-0 inset-x-0 z-20 bg-slate-900 border-b border-slate-800 p-5 px-10 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                                <WalletIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-white uppercase tracking-[0.2em]">{{ selectedIds.length }} NODES_LOCKED_FOR_SETTLEMENT</h4>
                                <p class="text-sm font-black text-slate-500 uppercase tracking-widest mt-1 italic">Authorized financial operations pending protocol select</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button @click="openPayrollModal" class="h-11 px-8 bg-indigo-600 text-white text-sm font-black uppercase tracking-[0.3em] rounded-xl hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-500/20 active:scale-95 flex items-center gap-2">
                                <ArrowPathIcon class="w-4 h-4" />
                                Queue for Payroll
                            </button>
                            <button @click="showSettleModal = true" class="h-11 px-8 bg-emerald-600 text-white text-sm font-black uppercase tracking-[0.3em] rounded-xl hover:bg-emerald-700 transition-all shadow-xl shadow-emerald-500/20 active:scale-95 flex items-center gap-2">
                                <CheckSealIcon class="w-4 h-4 text-emerald-400" />
                                Finalize Payment
                            </button>
                        </div>
                    </div>
                </Transition>

                <!-- Table Content -->
                <div class="flex-1 overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-slate-900 border-b border-slate-800 text-left">
                                <th class="px-8 py-6 w-12 bg-slate-900">
                                    <input type="checkbox" :checked="allSelected" @change="toggleAll" class="w-5 h-5 rounded-lg border-slate-700 bg-slate-800 text-emerald-500 focus:ring-emerald-500/20 cursor-pointer">
                                </th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Operative Node</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Temporal Node</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Claim Intel</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Settlement Logic</th>
                                <th class="px-8 py-6 text-right text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Liquidation Value</th>
                                <th class="px-8 py-6 text-center text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Protocol State</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="expense in expenses.data" :key="expense.id" 
                                class="group hover:bg-slate-50/80 transition-all duration-300"
                                :class="{'bg-emerald-50/30': selectedIds.includes(expense.id)}"
                            >
                                <td class="px-8 py-6">
                                    <input type="checkbox" :value="expense.id" v-model="selectedIds" class="w-5 h-5 rounded-lg border-slate-200 text-emerald-600 focus:ring-emerald-500 transition-transform active:scale-125 cursor-pointer">
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-emerald-400 shadow-lg group-hover:rotate-6 transition-transform">
                                            <span class="text-xs font-black uppercase">{{ expense.employee?.first_name?.[0] }}{{ expense.employee?.last_name?.[0] }}</span>
                                        </div>
                                        <div>
                                            <div class="text-base font-black text-slate-900 uppercase tracking-tight">{{ expense.employee?.first_name }} {{ expense.employee?.last_name }}</div>
                                            <div class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1 flex items-center gap-1.5 opacity-70">
                                                <UserCircleIcon class="w-3 h-3" />
                                                {{ expense.employee?.employee_code }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-2.5 text-slate-400 group-hover:text-slate-600 transition-colors">
                                        <CalendarDaysIcon class="w-4 h-4 opacity-50" />
                                        <span class="text-sm font-black tracking-widest uppercase">{{ formatDate(expense.incurred_date) }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="space-y-1">
                                        <div class="text-base font-black text-slate-900 uppercase tracking-tighter">{{ expense.title }}</div>
                                        <div class="text-sm font-black text-indigo-500 uppercase tracking-[0.2em] italic opacity-80">{{ expense.category?.name }}</div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                     <span class="inline-flex items-center gap-2 text-sm font-black uppercase tracking-widest text-slate-500">
                                        <div class="w-1.5 h-1.5 rounded-full" :class="expense.payout_method === 'payroll' ? 'bg-indigo-500 shadow-lg shadow-indigo-500/20' : 'bg-amber-500 shadow-lg shadow-amber-500/20'"></div>
                                        {{ expense.payout_method || 'MANUAL_DEBIT' }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="text-[14px] font-black text-slate-900 tracking-tighter group-hover:scale-105 transition-transform flex items-center justify-end gap-1.5">
                                        <CurrencyRupeeIcon class="w-4 h-4 text-emerald-500" />
                                        {{ formatCurrency(expense.amount).replace('₹', '') }}
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="px-4 py-1.5 rounded-full text-xs font-black border uppercase tracking-[0.2em] shadow-sm transform group-hover:scale-105 transition-all inline-block" 
                                        :class="expense.status === 'Approved_Payroll' ? 'bg-indigo-50 text-indigo-600 border-indigo-100 shadow-indigo-500/5' : 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-emerald-500/5'"
                                    >
                                        {{ expense.status === 'Approved_Payroll' ? 'PAYROLL_QUEUED' : 'READY_FOR_DEBIT' }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="expenses.data.length === 0">
                                <td colspan="7" class="px-8 py-32 text-center grayscale opacity-20 animate-pulse">
                                    <BanknotesIcon class="h-20 w-20 mx-auto mb-6" />
                                    <p class="text-sm font-black uppercase tracking-[0.4em]">Zero financial settlements pending in sector</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Summary Strip -->
                <div class="px-8 py-5 bg-slate-900 border-t border-slate-800 flex justify-between items-center relative z-10 shadow-2xl overflow-hidden rounded-b-[2.5rem]">
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/5 to-transparent"></div>
                    <div class="text-sm font-black text-slate-500 uppercase tracking-[0.3em] relative z-10 flex items-center gap-4">
                        <ClockIcon class="w-4 h-4 text-emerald-500" />
                        Live Registry Cycle Tracking Active
                    </div>
                    <div v-if="expenses.total > 0" class="text-sm font-black text-white uppercase tracking-[0.2em] relative z-10 px-4 py-1.5 bg-slate-800/50 rounded-xl border border-slate-700">
                        {{ expenses.total }} PENDING_LIQUIDATIONS
                    </div>
                </div>
            </div>
        </div>

        <!-- Payroll Injection Portal -->
        <PremiumModal 
            :show="showPayrollModal" 
            @close="showPayrollModal = false" 
            title="Payroll Integrator" 
            subtitle="Financial Cycle Projection"
            icon="fa-calendar-check"
            maxWidth="lg"
        >
            <div class="space-y-10 py-4">
                <div class="p-6 bg-slate-50 rounded-[2rem] border border-slate-100 flex items-center gap-5">
                    <div class="w-12 h-12 bg-indigo-500 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-indigo-500/30">
                        <ArrowPathIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight">Injection Protocol Authorization</h4>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1">Queueing {{ selectedIds.length }} settlements into ledger</p>
                    </div>
                </div>
                
                <div class="space-y-4 px-2">
                    <label class="text-sm font-black text-slate-400 uppercase tracking-widest ml-2">Target Disbursement Month</label>
                    <input type="month" v-model="payrollForm.target_month" class="w-full bg-slate-50 border-transparent rounded-[1.75rem] py-6 px-10 text-lg font-black text-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all uppercase tracking-[0.2em] shadow-sm">
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-slate-50">
                    <button @click="showPayrollModal = false" class="px-8 py-5 text-sm font-black uppercase tracking-[0.3em] text-slate-400 hover:bg-slate-50 rounded-2xl transition-all">Abort</button>
                    <button @click="submitPayroll" :disabled="payrollForm.processing" class="px-12 py-5 bg-slate-900 text-white rounded-[2rem] text-sm font-black uppercase tracking-[0.4em] shadow-2xl shadow-slate-900/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-4">
                        <ArrowPathIcon v-if="payrollForm.processing" class="w-4 h-4 animate-spin" />
                        <span>Authorize Injection</span>
                    </button>
                </div>
            </div>
        </PremiumModal>

        <!-- Final Settlement Portal -->
        <PremiumModal 
            :show="showSettleModal" 
            @close="showSettleModal = false" 
            title="Direct Liquidator" 
            subtitle="Final Financial Settlement"
            icon="fa-shield-check"
            maxWidth="xl"
        >
            <div class="space-y-12 py-4">
                <div class="p-8 bg-emerald-50 rounded-[2.5rem] border border-emerald-100/50 flex items-center gap-6 relative overflow-hidden group">
                     <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/5 rounded-full group-hover:scale-150 transition-transform"></div>
                     <div class="w-14 h-14 bg-emerald-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-emerald-500/30 shrink-0">
                        <CheckSealIcon class="w-8 h-8" />
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-emerald-900 uppercase tracking-tight">Final Debit Authorization</h4>
                        <p class="text-sm font-black text-emerald-600 uppercase tracking-widest mt-1 italic">Marking {{ selectedIds.length }} transactions as SETTLED via External Bridge</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 px-2">
                    <div class="space-y-4">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest ml-2">Settlement Node Date</label>
                        <input type="date" v-model="settleForm.settlement_date" class="w-full bg-slate-50 border-transparent rounded-[1.75rem] py-5 px-8 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all uppercase tracking-widest">
                    </div>
                    <div class="space-y-4">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest ml-2">Transaction Signature / ID</label>
                        <div class="relative">
                            <input v-model="settleForm.transaction_reference" placeholder="REF_HASH_NODE..." class="w-full bg-slate-50 border-transparent rounded-[1.75rem] py-5 px-8 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all uppercase tracking-widest shadow-sm">
                            <div class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-300">
                                <ArrowUpTrayIcon class="w-4 h-4" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-slate-50">
                    <button @click="showSettleModal = false" class="px-8 py-5 text-sm font-black uppercase tracking-[0.3em] text-slate-400 hover:bg-slate-50 rounded-2xl transition-all">Cancel Operation</button>
                    <button @click="submitSettlement" :disabled="settleForm.processing" class="px-14 py-5 bg-emerald-600 text-white rounded-[2rem] text-sm font-black uppercase tracking-[0.4em] shadow-2xl shadow-emerald-500/30 hover:scale-105 active:scale-95 transition-all flex items-center gap-4">
                        <CheckSealIcon v-if="!settleForm.processing" class="w-4 h-4" />
                        <ArrowPathIcon v-else class="w-4 h-4 animate-spin" />
                        <span>Confirm Liquidation</span>
                    </button>
                </div>
            </div>
        </PremiumModal>
    </MainLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
