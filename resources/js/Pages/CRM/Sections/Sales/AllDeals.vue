<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative font-sans">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group no-print">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <i class="fas fa-info text-xs"></i>
            </button>
            <div class="absolute right-0 top-10 w-80 bg-gray-900 text-white text-sm p-6 rounded-[32px] shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium">
                <div class="font-black tracking-[0.2em] uppercase mb-3 text-indigo-400 border-b border-indigo-500/20 pb-3 text-left">Revenue Command Console</div>
                <p class="text-gray-300 leading-relaxed mb-4 text-left">The Deal Ledger provides a high-fidelity view of your active sales pipeline. Track stage velocity, valuation weighting, and stakeholder engagement in real-time.</p>
                <div class="space-y-2 text-left">
                    <div class="flex items-center gap-3 text-left"><div class="w-2 h-2 rounded-full bg-indigo-500 shadow-lg shadow-indigo-500/50"></div> <span>Pipeline Velocity Tracking</span></div>
                    <div class="flex items-center gap-3 text-left"><div class="w-2 h-2 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/50"></div> <span>Weighted Revenue Analytics</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40 no-print">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 text-left">
                    <div class="text-left">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight text-left">Revenue Ledger</h2>
                        <div class="flex items-center mt-3 text-left">
                            <i class="fas fa-handshake text-indigo-500 mr-3 text-left"></i>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left">{{ deals.length }} active opportunities in funnel</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-left">
                        <button @click="showCreateModal = true" class="px-8 py-3.5 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all font-black text-sm tracking-widest uppercase shadow-xl shadow-indigo-100 flex items-center group active:scale-95 text-left">
                            <i class="fas fa-plus mr-2 text-xs group-hover:rotate-90 transition-transform text-left"></i>
                            Initiate New Deal
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enhanced Toolbar -->
            <div class="px-8 py-5 border-b border-gray-200 bg-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative z-40 no-print text-left">
                <div class="flex items-center gap-4 flex-1 w-full max-w-4xl text-left">
                    <div class="relative w-full max-w-md group text-left">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-500 transition-colors"></i>
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder-gray-400 shadow-inner text-left" 
                            placeholder="Find a deal, company or stakeholder..."
                        >
                    </div>

                    <div class="flex gap-2">
                        <select v-model="stageFilter" class="px-5 py-3 bg-gray-50 border-none rounded-xl text-sm font-black uppercase tracking-widest text-gray-500 focus:ring-4 focus:ring-indigo-500/10 cursor-pointer w-48 shadow-inner">
                            <option value="">All Lifecycle Stages</option>
                            <option v-for="stage in stages" :key="stage.id" :value="stage.name">{{ stage.name }}</option>
                        </select>
                    </div>
                    <button @click="resetFilters" class="text-sm font-black text-gray-400 hover:text-indigo-600 tracking-widest uppercase transition-colors px-4 text-left">Reset</button>
                </div>

                <!-- Export/Print Actions -->
                <div class="flex items-center gap-2 pr-8 text-left">
                    <button @click="printView" class="w-11 h-11 bg-white border border-gray-200 text-gray-400 rounded-xl flex items-center justify-center hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm group relative">
                        <i class="fas fa-print text-sm"></i>
                        <div class="absolute -top-10 bg-gray-900 text-white text-sm font-black px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl z-50">Print Snapshot</div>
                    </button>
                    <button class="w-11 h-11 bg-white border border-gray-200 text-gray-400 rounded-xl flex items-center justify-center hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm group relative">
                        <i class="fas fa-file-excel text-sm"></i>
                        <div class="absolute -top-10 bg-gray-900 text-white text-sm font-black px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl z-50">Export Ledger</div>
                    </button>
                </div>
            </div>

            <!-- Table Container -->
            <div class="flex-1 overflow-auto p-8 text-left" id="print-area">
                <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden text-left">
                    <table class="min-w-full divide-y divide-gray-100 text-left">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-8 py-5 text-left text-sm font-black text-gray-400 uppercase tracking-widest">Opportunity Matrix</th>
                                <th class="px-8 py-5 text-left text-sm font-black text-gray-400 uppercase tracking-widest">Financial Valuation</th>
                                <th class="px-8 py-5 text-center text-sm font-black text-gray-400 uppercase tracking-widest">Pipeline Layer</th>
                                <th class="px-8 py-5 text-left text-sm font-black text-gray-400 uppercase tracking-widest">Strategic Stakeholder</th>
                                <th class="px-8 py-5 text-right text-sm font-black text-gray-400 uppercase tracking-widest no-print">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50">
                            <tr v-for="deal in filteredDeals" :key="deal.id" class="hover:bg-indigo-50/20 transition-all group border-l-4 border-l-transparent hover:border-l-indigo-600">
                                <td class="px-8 py-6 whitespace-nowrap text-left">
                                    <div class="flex items-center text-left">
                                        <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 font-black shadow-sm group-hover:bg-indigo-600 group-hover:text-white transition-all text-left">
                                            <i class="fas fa-briefcase text-left"></i>
                                        </div>
                                        <div class="ml-5 text-left">
                                            <div class="text-sm font-black text-gray-900 group-hover:text-indigo-600 transition-colors text-left">{{ deal.title }}</div>
                                            <div class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-1 text-left">ID: #{{ deal.id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-left">
                                    <div class="text-sm font-black text-gray-900 text-left">{{ formatCurrency(deal.value) }}</div>
                                    <div class="text-sm text-indigo-500 font-black uppercase mt-1 text-left tracking-widest">Baseline Yield</div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-center">
                                    <span :class="[getStageClass(deal.stage), 'px-4 py-2 rounded-xl text-sm font-black uppercase tracking-widest border shadow-sm transition-all']">
                                        {{ deal.stage }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-left">
                                    <div class="flex flex-col text-left">
                                        <span class="text-xs font-black text-gray-700 text-left">{{ deal.account?.name || 'Private Individual' }}</span>
                                        <div class="flex items-center gap-2 mt-1 text-left">
                                             <div class="w-1.5 h-1.5 rounded-full bg-indigo-400 text-left"></div>
                                             <span class="text-sm text-gray-400 font-bold uppercase tracking-widest text-left">{{ deal.contact?.first_name ? (deal.contact.first_name + ' ' + (deal.contact.last_name || '')) : 'Primary Stakeholder' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-right no-print">
                                    <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button class="w-10 h-10 rounded-xl bg-white text-indigo-400 hover:bg-gray-900 hover:text-white transition-all shadow-sm border border-gray-100 flex items-center justify-center">
                                            <i class="fas fa-expand-alt text-xs"></i>
                                        </button>
                                        <button class="w-10 h-10 rounded-xl bg-white text-rose-400 hover:bg-rose-600 hover:text-white transition-all shadow-sm border border-gray-100 flex items-center justify-center">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <div v-if="filteredDeals.length === 0" class="flex flex-col items-center justify-center py-32 text-gray-300 text-left">
                        <div class="w-24 h-24 bg-white rounded-[40px] shadow-sm flex items-center justify-center mb-8 border border-gray-100 text-left">
                             <i class="fas fa-handshake text-3xl opacity-20 text-left"></i>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight text-left">Revenue Void</h3>
                        <p class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-2 text-left">No opportunities match your current filter criteria.</p>
                        <button v-if="searchQuery || stageFilter" @click="resetFilters" class="mt-6 text-sm font-black text-indigo-500 uppercase tracking-widest hover:underline text-left">Clear Execution Filters</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- CREATE MODAL -->
        <div v-if="showCreateModal" @click.self="showCreateModal = false" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="relative bg-white rounded-[40px] shadow-2xl max-w-2xl w-full overflow-hidden border border-white text-left font-sans animate-in zoom-in-95 duration-300">
                <div class="bg-gray-50/50 px-10 py-8 border-b border-gray-100 flex justify-between items-center text-left">
                    <div class="flex items-center text-left">
                        <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white mr-5 shadow-xl shadow-indigo-100 text-left">
                            <i class="fas fa-rocket text-xl text-left"></i>
                        </div>
                        <div class="text-left">
                            <h3 class="text-2xl font-black text-gray-900 tracking-tight text-left">Initiate Opportunity</h3>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-[0.2em] mt-1 text-left">Revenue Entry Protocol</p>
                        </div>
                    </div>
                    <button @click="showCreateModal = false" class="w-12 h-12 rounded-2xl bg-white border border-gray-100 text-gray-400 hover:text-gray-900 flex items-center justify-center shadow-sm transition-all hover:rotate-90">
                        <i class="fas fa-times text-left"></i>
                    </button>
                </div>
                
                <form @submit.prevent="submit" class="p-10 space-y-10 text-left">
                    <div class="space-y-3 text-left">
                        <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left">Opportunity Designation</label>
                        <input v-model="form.title" type="text" required class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-6 font-semibold shadow-inner text-left" placeholder="e.g. Q4 Infrastructure Transformation">
                    </div>

                    <div class="grid grid-cols-2 gap-8 text-left">
                        <div class="space-y-3 text-left">
                            <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left">Baseline Valuation</label>
                            <input v-model="form.value" type="number" required class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-6 font-black shadow-inner text-left" placeholder="0.00">
                        </div>
                        <div class="space-y-3 text-left">
                            <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left">Initial Lifecycle Phase</label>
                            <select v-model="form.stage" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-6 font-black shadow-inner text-left cursor-pointer appearance-none">
                                <option v-for="stage in stages" :key="stage.id" :value="stage.name">{{ stage.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-3 text-left">
                        <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left">Strategic Stakeholder</label>
                        <select v-model="form.account_id" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-6 font-black shadow-inner text-left cursor-pointer appearance-none">
                            <option value="">PRIVATE INDIVIDUAL / UNLINKED</option>
                            <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
                        </select>
                    </div>

                    <div class="pt-10 flex items-center justify-between text-left">
                        <button type="button" @click="showCreateModal = false" class="text-base font-black text-gray-400 hover:text-gray-900 transition-colors uppercase tracking-widest text-left">Abort Transmission</button>
                        <button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white px-12 py-5 rounded-[24px] font-black text-sm hover:bg-indigo-700 transition-all shadow-2xl shadow-indigo-100 min-w-[220px] active:scale-95 disabled:opacity-50 text-left">
                            {{ form.processing ? 'DEPLOING...' : 'LAUNCH OPPORTUNITY' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    deals: { type: Array, default: () => [] },
    accounts: { type: Array, default: () => [] },
    stages: { type: Array, default: () => [] }
});

const searchQuery = ref('');
const stageFilter = ref('');
const showCreateModal = ref(false);

const form = useForm({
    title: '',
    value: '',
    stage: props.stages.length ? props.stages[0].name : 'Lead In',
    account_id: '',
    description: ''
});

const filteredDeals = computed(() => {
    let list = props.deals || [];
    if (searchQuery.value) {
        const term = searchQuery.value.toLowerCase();
        list = list.filter(deal => 
            deal.title?.toLowerCase().includes(term) ||
            deal.account?.name?.toLowerCase().includes(term)
        );
    }
    if (stageFilter.value) {
        list = list.filter(deal => deal.stage?.toLowerCase() === stageFilter.value.toLowerCase());
    }
    return list;
});

const resetFilters = () => {
    searchQuery.value = '';
    stageFilter.value = '';
};

const printView = () => window.print();

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val || 0);
};

const getStageClass = (stage) => {
    const s = stage?.toString().toLowerCase();
    const classes = {
        'closed won': 'bg-emerald-50 text-emerald-600 border-emerald-100',
        'won': 'bg-emerald-50 text-emerald-600 border-emerald-100',
        'closed lost': 'bg-rose-50 text-rose-600 border-rose-100',
        'lost': 'bg-rose-50 text-rose-600 border-rose-100',
        'negotiation': 'bg-amber-50 text-amber-600 border-amber-100',
        'proposal': 'bg-indigo-50 text-indigo-600 border-indigo-100',
        'lead in': 'bg-purple-50 text-purple-600 border-purple-100',
    };
    return classes[s] || 'bg-gray-50 text-gray-400 border-gray-100';
};

const submit = () => {
    form.post(route('crm.deals.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        }
    });
};
</script>

<style scoped>
/* Custom Hide Scrollbar */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.05); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.1); }

@media print {
    #print-area { padding: 0 !important; }
    .no-print { display: none !important; }
}
</style>
