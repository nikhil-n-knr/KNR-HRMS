<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import { 
    BanknotesIcon, 
    ArrowPathIcon, 
    ChevronRightIcon, 
    PlusIcon, 
    DocumentTextIcon, 
    ReceiptPercentIcon,
    CalendarIcon,
    WalletIcon,
    SparklesIcon,
    ArrowUpTrayIcon,
    ExclamationCircleIcon,
    BriefcaseIcon
} from '@heroicons/vue/24/outline';

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

watch(() => form.expense_category_id, (newId) => {
    const category = props.categories.find(c => c.id === newId);
    if (category && category.default_payout_method) {
        form.payout_method = category.default_payout_method;
    }
});

const submit = () => {
    form.post(route('employee.expenses.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        }
    });
};

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val || 0);
const formatDate = (date) => new Date(date).toLocaleDateString();

const getStatusStyles = (status) => {
    switch (status) {
        case 'approved': return 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-emerald-500/5';
        case 'pending': return 'bg-amber-50 text-amber-600 border-amber-100 shadow-amber-500/5';
        case 'rejected': return 'bg-rose-50 text-rose-600 border-rose-100 shadow-rose-500/5';
        default: return 'bg-slate-50 text-slate-400 border-slate-100';
    }
};
</script>

<template>
    <Head title="Claims & Reimbursement Hub" />
    <MainLayout>
        <div class="max-w-7xl mx-auto space-y-8 font-outfit px-4 md:px-8 pb-20">
            <!-- Strategic Header -->
            <div class="py-8 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div class="flex items-center gap-5">
                    <div class="p-4 bg-slate-900 border border-slate-800 rounded-3xl text-indigo-400 shadow-2xl shadow-slate-200/50 relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <BanknotesIcon class="w-8 h-8 relative z-10" />
                    </div>
                    <div>
                        <h1 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                            Claims Terminal
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-indigo-50 text-indigo-600 border border-indigo-100 uppercase tracking-widest">Financial Ops</span>
                        </h1>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Expense lifecycle management & reimbursement protocols</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                    <button class="h-12 px-6 bg-white border-2 border-slate-100 text-slate-500 rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-sm active:scale-95 flex items-center gap-3">
                        <ArrowPathIcon class="w-4 h-4" />
                        Sync Data
                    </button>
                    <button @click="showCreateModal = true" class="flex-1 lg:flex-none h-12 px-8 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] shadow-2xl shadow-slate-200 hover:bg-indigo-600 transition-all active:scale-95 flex items-center justify-center gap-3 group">
                        <PlusIcon class="w-4 h-4 text-indigo-400 group-hover:rotate-12 transition-transform" />
                        <span>Log New Claim</span>
                    </button>
                </div>
            </div>

            <!-- Dashboard Pulse -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="stat in [
                    { label: 'Cycle Velocity', val: formatCurrency(stats.total_this_month), sub: 'Current Month Volume', icon: WalletIcon, color: 'indigo' },
                    { label: 'Pending Processing', val: stats.total_pending, sub: 'In-flight Protocols', icon: ArrowPathIcon, color: 'amber' },
                    { label: 'Yearly Liquidation', val: formatCurrency(stats.total_claimed_ytd), sub: 'YTD Settled Volume', icon: ReceiptPercentIcon, color: 'emerald' }
                ]" :key="stat.label" class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/40 group hover:scale-[1.02] transition-all">
                    <div class="flex items-center justify-between mb-6">
                        <div :class="`w-12 h-12 rounded-2xl bg-${stat.color}-50 text-${stat.color}-500 flex items-center justify-center group-hover:rotate-12 transition-transform`">
                            <component :is="stat.icon" class="w-6 h-6" />
                        </div>
                        <span class="text-xs font-black text-slate-400 uppercase tracking-[0.3em]">{{ stat.label }}</span>
                    </div>
                    <div class="text-3xl font-black text-slate-900 tracking-tighter mb-1">{{ stat.val }}</div>
                    <div class="text-sm font-black text-slate-400 uppercase tracking-widest">{{ stat.sub }}</div>
                </div>
            </div>

            <!-- Claim Registry Terminal -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/50 overflow-hidden min-h-[500px]">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center">
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-3">
                        <DocumentTextIcon class="w-5 h-5 text-indigo-500" />
                        Absence Manifest
                    </h3>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-black text-slate-400 uppercase tracking-widest">Sort by:</span>
                        <select class="bg-slate-50 border-none rounded-xl text-sm font-black uppercase tracking-widest text-slate-600 focus:ring-0 cursor-pointer">
                            <option>Temporal_Sequence</option>
                            <option>Valuation_Desc</option>
                        </select>
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-slate-900 border-b border-slate-800 text-left">
                                <th class="px-8 py-5 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Temporal Node</th>
                                <th class="px-8 py-5 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Claim Intel</th>
                                <th class="px-8 py-5 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Classification</th>
                                <th class="px-8 py-5 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Payout Strategy</th>
                                <th class="px-8 py-5 text-right text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Valuation</th>
                                <th class="px-8 py-5 text-center text-sm font-black text-slate-400 uppercase tracking-[0.2em]">State Protocol</th>
                                <th class="px-8 py-5 text-right text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Audit Access</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="expense in expenses.data" :key="expense.id" class="group hover:bg-slate-50/80 transition-all duration-300">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-3">
                                        <CalendarIcon class="w-4 h-4 text-slate-300" />
                                        <span class="text-base font-black text-slate-900 uppercase tracking-tighter">{{ formatDate(expense.incurred_date) }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="space-y-1">
                                        <div class="text-base font-black text-slate-900 uppercase tracking-tight group-hover:text-indigo-600 transition-colors">{{ expense.title }}</div>
                                        <div class="text-sm font-black text-slate-400 uppercase tracking-widest truncate max-w-xs italic opacity-75">" {{ expense.description || 'NO_LEGAL_JUSTIFICATION' }} "</div>
                                        <div v-if="expense.project" class="flex items-center gap-1.5 pt-1">
                                            <BriefcaseIcon class="w-3 h-3 text-indigo-400" />
                                            <span class="text-xs font-black text-indigo-500 uppercase tracking-[0.2em] bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100 uppercase">{{ expense.project.name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-700 text-sm font-black uppercase tracking-widest rounded-lg border border-slate-200">{{ expense.category?.name }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="text-sm font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full" :class="expense.payout_method === 'payroll' ? 'bg-indigo-500' : 'bg-amber-500'"></div>
                                        {{ expense.payout_method }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="text-lg font-black text-slate-900 tracking-tighter">{{ formatCurrency(expense.amount) }}</div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="px-4 py-1.5 rounded-full text-xs font-black border uppercase tracking-[0.2em] shadow-sm transform group-hover:scale-105 transition-all inline-block" :class="getStatusStyles(expense.status)">
                                        {{ expense.status }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <Link :href="route('employee.expenses.show', expense.id)" class="w-10 h-10 rounded-xl bg-slate-900 text-indigo-400 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all shadow-xl shadow-slate-200 active:scale-95 inline-flex translate-x-3 opacity-0 group-hover:translate-x-0 group-hover:opacity-100">
                                        <ChevronRightIcon class="w-5 h-5" />
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="expenses.data.length === 0">
                                <td colspan="7" class="px-8 py-32 text-center grayscale opacity-20 animate-pulse">
                                    <DocumentTextIcon class="h-20 w-20 mx-auto mb-6" />
                                    <p class="text-sm font-black uppercase tracking-[0.4em]">Zero financial claims detected in current sector</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Adaptive Mobile View -->
                <div class="lg:hidden divide-y divide-slate-50">
                    <div v-for="expense in expenses.data" :key="'mb-'+expense.id" class="p-6 space-y-6 group bg-white hover:bg-slate-50 relative overflow-hidden transition-all">
                        <div class="absolute -right-4 top-0 w-1.5 h-full opacity-0 group-hover:opacity-100 bg-indigo-500 transition-all"></div>
                        
                        <div class="flex justify-between items-start">
                            <div class="space-y-3">
                                <span class="bg-indigo-50 text-indigo-600 text-xs font-black px-2 py-0.5 rounded border border-indigo-100 uppercase tracking-widest">{{ expense.category?.name }}</span>
                                <h4 class="text-lg font-black text-slate-900 uppercase tracking-tight">{{ expense.title }}</h4>
                                <div class="flex items-center gap-3 text-slate-400 text-sm font-black uppercase tracking-widest">
                                    <CalendarIcon class="w-4 h-4" />
                                    {{ formatDate(expense.incurred_date) }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-[16px] font-black text-slate-900 tracking-tighter">{{ formatCurrency(expense.amount) }}</div>
                                <span class="px-3 py-1 rounded-full text-xs font-black border uppercase tracking-widest shadow-sm mt-3 inline-block" :class="getStatusStyles(expense.status)">
                                    {{ expense.status }}
                                </span>
                            </div>
                        </div>

                        <Link :href="route('employee.expenses.show', expense.id)" class="h-12 w-full bg-slate-900 text-white rounded-2xl flex items-center justify-center gap-3 text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 group-hover:bg-indigo-600 transition-all">
                            AUDIT_CLAIM_NODE
                            <ChevronRightIcon class="h-4 w-4" />
                        </Link>
                    </div>
                </div>

                <!-- Pagination Engine -->
                 <div class="px-8 py-6 bg-slate-50/80 flex flex-col md:flex-row justify-between items-center gap-6 border-t border-slate-100" v-if="expenses.total > 0">
                    <div class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">
                        Finance Segment {{ (expenses.current_page - 1) * expenses.per_page + 1 }} - {{ Math.min(expenses.current_page * expenses.per_page, expenses.total) }} of {{ expenses.total }} Records
                    </div>
                    <div class="flex gap-2">
                         <template v-for="(link, k) in expenses.links" :key="k">
                            <Link 
                                v-if="link.url"
                                :href="link.url" 
                                class="h-10 px-4 text-sm font-black uppercase tracking-widest rounded-xl transition-all shadow-sm flex items-center justify-center active:scale-95" 
                                :class="link.active ? 'bg-slate-900 text-white shadow-xl shadow-slate-400' : 'bg-white text-slate-400 border border-slate-200 hover:text-indigo-600 shadow-sm'"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Claim Initiation Portal -->
        <PremiumModal 
            :show="showCreateModal" 
            @close="showCreateModal = false" 
            title="Claim Logger" 
            subtitle="Protocol Financial Injection"
            icon="fa-money-bill-transfer"
            maxWidth="2xl"
        >
            <form @submit.prevent="submit" class="space-y-8 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="md:col-span-2 space-y-4">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest ml-2">Claim Signature (Title)</label>
                        <input v-model="form.title" required placeholder="PROTOCOL_EVENT_ALIAS..." class="w-full bg-slate-50 border-transparent rounded-[1.5rem] py-5 px-8 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all uppercase tracking-widest">
                    </div>
                    
                    <div class="space-y-4">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest ml-2">Absence Category</label>
                        <select v-model="form.expense_category_id" class="w-full bg-slate-50 border-transparent rounded-[1.5rem] py-5 px-8 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer uppercase tracking-widest" required>
                            <option :value="null" disabled>SELECT_MODULE</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name.toUpperCase() }}</option>
                        </select>
                    </div>

                    <div class="space-y-4 text-left">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest ml-2">Valuation (INR)</label>
                        <div class="relative group">
                            <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 font-black text-base">₹</div>
                            <input type="number" step="0.01" v-model="form.amount" class="w-full bg-slate-50 border-transparent rounded-[1.5rem] py-5 pl-12 pr-8 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all uppercase tracking-widest" required placeholder="0.00">
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest ml-2">Temporal Node</label>
                        <input type="date" v-model="form.incurred_date" class="w-full bg-slate-50 border-transparent rounded-[1.5rem] py-5 px-8 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all uppercase tracking-widest" required>
                    </div>

                    <div class="space-y-4">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest ml-2">Settlement Logic</label>
                        <select v-model="form.payout_method" class="w-full bg-slate-50 border-transparent rounded-[1.5rem] py-5 px-8 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer uppercase tracking-widest">
                            <option value="payroll">PAYROLL_INJECTION</option>
                            <option value="direct">DIRECT_LIQUIDATION</option>
                        </select>
                    </div>
                </div>

                <!-- Evidentiary Interface -->
                <div class="space-y-4">
                    <label class="text-sm font-black text-slate-400 uppercase tracking-widest ml-2">Proof of Liquidation</label>
                    <div class="border-2 border-dashed border-slate-100 rounded-[2rem] p-10 flex flex-col items-center justify-center group hover:border-indigo-500 hover:bg-slate-50/50 transition-all cursor-pointer relative overflow-hidden">
                        <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-indigo-50 rounded-full blur-2xl opacity-0 group-hover:opacity-100 transition-all"></div>
                        <ArrowUpTrayIcon class="w-12 h-12 text-slate-200 group-hover:text-indigo-500 group-hover:-translate-y-2 transition-all mb-4" />
                        <div class="text-sm font-black text-slate-400 group-hover:text-indigo-600 transition-colors uppercase tracking-[0.2em] relative z-10">
                            {{ form.receipt ? form.receipt.name : 'UPLOAD_MANIFEST_EVIDENCE' }}
                        </div>
                        <p class="text-xs font-black text-slate-300 uppercase tracking-widest mt-2">Maximum Payload: 5MB (JPG, PNG, PDF)</p>
                        <input type="file" @change="e => form.receipt = e.target.files[0]" class="absolute inset-0 opacity-0 cursor-pointer">
                    </div>
                    <div v-if="selectedCategory?.requires_bill_proof && !form.receipt" class="flex items-center gap-2 p-3 bg-rose-50 rounded-xl border border-rose-100 text-xs font-black text-rose-500 uppercase tracking-widest animate-pulse">
                        <ExclamationCircleIcon class="w-4 h-4" />
                        MANDATORY_EVIDENCE_PROTOCOL_ACTIVE
                    </div>
                </div>

                <div class="space-y-4 px-2">
                    <label class="text-sm font-black text-slate-400 uppercase tracking-widest ml-2">Justification Manifest</label>
                    <textarea v-model="form.description" rows="3" class="w-full bg-slate-50 border-transparent rounded-[1.5rem] p-6 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all uppercase tracking-widest resize-none" placeholder="LOG_EVENT_CONTEXT..."></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-8 border-t border-slate-50">
                    <button @click="showCreateModal = false" type="button" class="px-8 py-4 text-sm font-black uppercase tracking-[0.3em] text-slate-400 hover:bg-slate-50 rounded-2xl transition-all">Abort</button>
                    <button 
                        @click="submit" 
                        :disabled="form.processing"
                        class="px-12 py-5 bg-slate-900 text-white rounded-[1.75rem] text-sm font-black uppercase tracking-[0.4em] shadow-2xl shadow-slate-900/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-4"
                    >
                        <SparklesIcon class="w-4 h-4 text-indigo-400" />
                        <span>Inject Claim</span>
                    </button>
                </div>
            </form>
        </PremiumModal>
</MainLayout>
</template>
