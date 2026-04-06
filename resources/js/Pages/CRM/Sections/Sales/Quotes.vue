<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group no-print text-left">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none">
                <i class="fas fa-info text-xs"></i>
            </button>
            <div class="absolute right-0 top-10 w-72 bg-gray-900 text-white text-sm p-5 rounded-2xl shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium font-sans">
                <div class="font-black tracking-widest uppercase mb-2 text-indigo-400 border-b border-indigo-500/20 pb-2 text-left">Financial Architecture</div>
                <p class="text-gray-300 leading-relaxed mb-3 text-left">Proposals are the bridge between engagement and revenue. Track quote lifecycles, validity periods, and multi-product configurations in a standardized financial ledger.</p>
                <div class="space-y-1.5 pt-2 text-left">
                    <div class="flex items-center gap-2 text-left"><div class="w-1.5 h-1.5 rounded-full bg-amber-500"></div> <span>Price Optimization</span></div>
                    <div class="flex items-center gap-2 text-left"><div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div> <span>Revenue Forecasting</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0 font-sans">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40 no-print">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="text-left">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight">Financial Ledger</h2>
                        <div class="flex items-center mt-3">
                            <i class="fas fa-file-invoice-dollar text-indigo-500 mr-3"></i>
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-[0.2em]">{{ quotes.length }} active proposals in cycle</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button @click="showModal = true" class="px-8 py-3.5 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all font-black text-sm tracking-widest uppercase shadow-xl shadow-indigo-100 flex items-center group active:scale-95">
                            <i class="fas fa-plus mr-2 text-xs group-hover:scale-125 transition-transform"></i>
                            Generate Proposal
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enhanced Toolbar -->
            <div class="px-8 py-5 border-b border-gray-200 bg-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative z-40 no-print text-left">
                <div class="flex items-center gap-4 flex-1 w-full max-w-3xl">
                    <div class="relative w-full max-w-sm group">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-500 transition-colors"></i>
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder-gray-400 shadow-inner" 
                            placeholder="Search by quote #, title or account..."
                        >
                    </div>

                    <div class="flex gap-2">
                        <select v-model="statusFilter" class="px-5 py-3 bg-gray-50 border-none rounded-xl text-sm font-black uppercase tracking-widest text-gray-500 focus:ring-4 focus:ring-indigo-500/10 cursor-pointer outline-none w-48 shadow-inner">
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="sent">Sent</option>
                            <option value="accepted">Accepted</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <button @click="resetFilters" class="text-sm font-black text-gray-400 hover:text-indigo-600 tracking-widest uppercase transition-colors px-4">Reset</button>
                </div>

                <!-- Export/Print Actions -->
                <div class="flex items-center gap-2 pr-8">
                    <button @click="printView" class="w-11 h-11 bg-white border border-gray-200 text-gray-400 rounded-xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm group relative">
                        <i class="fas fa-print text-sm"></i>
                        <div class="absolute -top-10 bg-gray-900 text-white text-sm font-black px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl">Print Ledger</div>
                    </button>
                    <button class="w-11 h-11 bg-white border border-gray-200 text-gray-400 rounded-xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm group relative">
                        <i class="fas fa-file-excel text-sm"></i>
                        <div class="absolute -top-10 bg-gray-900 text-white text-sm font-black px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl">Export Ledger</div>
                    </button>
                </div>
            </div>

            <!-- Table Container -->
            <div class="flex-1 overflow-auto p-8" id="print-area">
                <div class="bg-white rounded-[32px] shadow-sm border border-gray-100 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-100 text-left">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-8 py-5 text-sm font-black text-gray-400 uppercase tracking-widest text-left">Identifier</th>
                                <th class="px-8 py-5 text-sm font-black text-gray-400 uppercase tracking-widest text-left">Proposal Artifact</th>
                                <th class="px-8 py-5 text-sm font-black text-gray-400 uppercase tracking-widest text-left">Stakeholder / Enterprise</th>
                                <th class="px-8 py-5 text-center text-sm font-black text-gray-400 uppercase tracking-widest">Financial Matrix</th>
                                <th class="px-8 py-5 text-center text-sm font-black text-gray-400 uppercase tracking-widest">Visibility Status</th>
                                <th class="px-8 py-5 text-right text-sm font-black text-gray-400 uppercase tracking-widest no-print">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50">
                            <tr v-for="quote in filteredQuotes" :key="quote.id" class="hover:bg-indigo-50/20 transition-all group border-l-4 border-l-transparent hover:border-l-indigo-500">
                                <td class="px-8 py-6 whitespace-nowrap text-left">
                                    <span class="text-base font-black text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100 shadow-sm">{{ quote.quote_number }}</span>
                                </td>
                                <td class="px-8 py-6 text-left">
                                    <div class="text-sm font-black text-gray-900 leading-tight mb-1 text-left">{{ quote.title }}</div>
                                    <div class="flex items-center gap-2 text-left">
                                         <i class="far fa-calendar-alt text-sm text-gray-300"></i>
                                         <span class="text-sm font-bold text-gray-400 uppercase tracking-widest text-left">Expiry: {{ quote.valid_until }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-left">
                                    <div class="flex items-center text-left">
                                        <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center mr-3 border border-gray-100 text-left">
                                            <i class="fas fa-building text-gray-300 text-sm text-left"></i>
                                        </div>
                                        <div class="text-xs font-black text-gray-700 text-left">{{ quote.account?.name || quote.contact?.first_name || 'Legacy Entity' }}</div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <div class="text-sm font-black text-gray-900">{{ formatCurrency(quote.total) }}</div>
                                    <div class="text-sm font-bold text-gray-400 uppercase mt-1">Total Valuation</div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span :class="[getStatusClass(quote.status), 'px-4 py-2 rounded-xl text-sm font-black uppercase tracking-[0.1em] border shadow-sm transition-all']">
                                        {{ quote.status }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right no-print">
                                    <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button class="w-9 h-9 rounded-xl bg-white text-gray-400 hover:bg-gray-900 hover:text-white flex items-center justify-center shadow-sm border border-gray-100 transition-all group/btn relative">
                                            <i class="fas fa-eye text-xs"></i>
                                            <div class="absolute -top-10 bg-gray-900 text-white text-xs font-black px-2 py-1 rounded-md opacity-0 group-hover/btn:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl z-50">View Artifact</div>
                                        </button>
                                        <button class="w-9 h-9 rounded-xl bg-white text-gray-400 hover:bg-indigo-600 hover:text-white flex items-center justify-center shadow-sm border border-gray-100 transition-all group/btn relative">
                                            <i class="fas fa-download text-xs"></i>
                                            <div class="absolute -top-10 bg-gray-900 text-white text-xs font-black px-2 py-1 rounded-md opacity-0 group-hover/btn:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl z-50">Download PDF</div>
                                        </button>
                                        <button class="w-9 h-9 rounded-xl bg-white text-gray-400 hover:bg-rose-600 hover:text-white flex items-center justify-center shadow-sm border border-gray-100 transition-all group/btn relative">
                                            <i class="fas fa-archive text-xs"></i>
                                            <div class="absolute -top-10 bg-gray-900 text-white text-xs font-black px-2 py-1 rounded-md opacity-0 group-hover/btn:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl z-50">Archive</div>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <div v-if="filteredQuotes.length === 0" class="flex flex-col items-center justify-center py-32 text-gray-300 text-left">
                        <div class="w-24 h-24 bg-white rounded-[40px] shadow-sm flex items-center justify-center mb-8 border border-gray-100 text-left">
                             <i class="fas fa-file-invoice text-3xl opacity-20 text-left"></i>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight text-left">Matrix Empty</h3>
                        <p class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-2 text-left">No financial proposals match your current filters.</p>
                        <button v-if="searchQuery || statusFilter" @click="resetFilters" class="mt-6 text-sm font-black text-indigo-500 uppercase tracking-widest hover:underline text-left">Clear Active Filters</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- GENERATE QUOTE MODAL -->
        <div v-if="showModal" @click.self="showModal = false" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
             <div class="relative bg-white rounded-[40px] shadow-2xl max-w-4xl w-full overflow-hidden border border-white text-left font-sans animate-in zoom-in-95 duration-300">
                <div class="bg-gray-50/50 px-10 py-8 border-b border-gray-100 flex justify-between items-center text-left">
                    <div class="flex items-center text-left">
                        <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white mr-5 shadow-xl shadow-indigo-100 text-left">
                            <i class="fas fa-file-invoice-dollar text-xl text-left"></i>
                        </div>
                        <div class="text-left">
                            <h3 class="text-2xl font-black text-gray-900 tracking-tight text-left">Generate Proposal</h3>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-[0.2em] mt-1 text-left">Financial Architecture Console</p>
                        </div>
                    </div>
                    <button @click="showModal = false" class="w-12 h-12 rounded-2xl bg-white border border-gray-100 text-gray-400 hover:text-gray-900 flex items-center justify-center shadow-sm transition-all hover:rotate-90">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="grid grid-cols-12 h-[600px] text-left">
                    <!-- Left Column: Form -->
                    <div class="col-span-8 overflow-y-auto p-10 space-y-8 border-r border-gray-100 text-left">
                        <div class="grid grid-cols-2 gap-8 text-left">
                             <div class="col-span-2 space-y-2 text-left">
                                <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left">Artifact Title</label>
                                <input v-model="form.title" type="text" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-6 font-semibold shadow-inner text-left" placeholder="e.g. Q1 Infrastructure Upgrade Proposal">
                            </div>
                            
                            <div class="space-y-2 text-left">
                                <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left">Target Enterprise</label>
                                <select v-model="form.account_id" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-6 font-bold shadow-inner text-left cursor-pointer">
                                    <option :value="null">Unlinked Entity</option>
                                    <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
                                </select>
                            </div>

                            <div class="space-y-2 text-left">
                                <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left">Validity Horizon</label>
                                <input v-model="form.valid_until" type="date" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-6 font-semibold shadow-inner text-left">
                            </div>
                        </div>

                        <!-- Line Items -->
                        <div class="space-y-4 text-left">
                            <div class="flex justify-between items-center text-left">
                                <h4 class="text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left">Configuration Units</h4>
                                <button @click="addItem" class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                    <i class="fas fa-plus mr-1"></i> Add Unit
                                </button>
                            </div>

                            <div class="space-y-4 text-left">
                                <div v-for="(item, index) in form.items" :key="index" class="bg-gray-50/50 p-6 rounded-[24px] border border-gray-100 group transition-all hover:bg-white hover:shadow-xl hover:shadow-indigo-500/5 text-left relative overflow-hidden">
                                     <div class="absolute top-0 right-0 p-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                         <button @click="removeItem(index)" class="w-7 h-7 bg-rose-50 text-rose-500 rounded-lg hover:bg-rose-500 hover:text-white transition-all">
                                             <i class="fas fa-times text-sm"></i>
                                         </button>
                                     </div>

                                     <div class="grid grid-cols-12 gap-6 text-left">
                                         <div class="col-span-12 text-left">
                                             <select v-model="item.product_id" @change="onProductSelect(item)" class="w-full bg-white border-gray-100 rounded-xl text-xs font-black text-indigo-600 uppercase tracking-widest py-2 px-3 focus:ring-4 focus:ring-indigo-500/10 text-left cursor-pointer">
                                                <option :value="null">PULL FROM CATALOG...</option>
                                                <option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }} - {{ formatCurrency(product.base_price) }}</option>
                                             </select>
                                         </div>
                                         <div class="col-span-7 space-y-2 text-left">
                                             <input v-model="item.name" type="text" placeholder="Unit Designation" class="w-full bg-transparent border-none text-sm font-black text-gray-900 focus:ring-0 p-0 text-left" />
                                             <input v-model="item.description" type="text" placeholder="Technical Specifications" class="w-full bg-transparent border-none text-xs text-gray-400 font-bold focus:ring-0 p-0 text-left" />
                                         </div>
                                         <div class="col-span-2 text-left">
                                             <label class="block text-xs font-black text-gray-300 uppercase tracking-widest mb-1 text-left">Qty</label>
                                             <input v-model="item.quantity" type="number" class="w-full bg-white border-gray-100 rounded-xl text-center text-xs py-2 font-black text-left shadow-sm" />
                                         </div>
                                         <div class="col-span-3 text-left">
                                             <label class="block text-xs font-black text-gray-300 uppercase tracking-widest mb-1 text-left">Unit Price</label>
                                             <input v-model="item.unit_price" type="number" class="w-full bg-white border-gray-100 rounded-xl text-right text-xs py-2 pr-3 font-black text-left shadow-sm" />
                                         </div>
                                     </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Summary & Publish -->
                    <div class="col-span-4 bg-gray-50/50 p-10 flex flex-col text-left">
                        <div class="flex-1 space-y-10 text-left">
                             <div class="text-left">
                                <h4 class="text-base font-black text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-200 pb-3 text-left">Valuation Summary</h4>
                                <div class="space-y-4 text-left">
                                    <div class="flex justify-between items-center text-left">
                                        <span class="text-sm font-black text-gray-400 uppercase tracking-widest text-left">Gross Value</span>
                                        <span class="text-sm font-black text-gray-900 text-left">{{ formatCurrency(subtotal) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-left">
                                        <span class="text-sm font-black text-gray-400 uppercase tracking-widest text-left">Tax Protocol (0%)</span>
                                        <span class="text-sm font-black text-gray-900 text-left">$0.00</span>
                                    </div>
                                    <div class="pt-6 border-t border-gray-200 text-left">
                                         <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mb-2 text-left">Total Artifact Value</p>
                                         <p class="text-4xl font-black text-gray-900 text-left">{{ formatCurrency(total) }}</p>
                                    </div>
                                </div>
                             </div>

                             <div class="p-6 bg-indigo-900 rounded-[32px] text-white relative overflow-hidden text-left">
                                <div class="absolute -right-4 -bottom-4 opacity-10 text-left">
                                    <i class="fas fa-shield-alt text-6xl text-left"></i>
                                </div>
                                <p class="text-sm font-black text-indigo-300 uppercase tracking-[0.3em] mb-2 text-left">Compliance Check</p>
                                <p class="text-sm font-medium leading-relaxed text-indigo-100 text-left">This proposal artifact adheres to enterprise financial standards and active price-list configurations.</p>
                             </div>
                        </div>

                        <button @click="submitQuote" :disabled="form.processing" class="w-full bg-indigo-600 text-white py-5 rounded-[24px] font-black text-sm hover:bg-indigo-700 transition-all shadow-2xl shadow-indigo-100 mt-10 flex items-center justify-center gap-3 active:scale-[0.98] disabled:opacity-50 text-left">
                            <i class="fas fa-paper-plane text-xs text-left animate-pulse"></i>
                            {{ form.processing ? 'DEPLOING...' : 'PUBLISH PROPOSAL' }}
                        </button>
                    </div>
                </div>
             </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    quotes: { type: Array, default: () => [] },
    accounts: { type: Array, default: () => [] },
    products: { type: Array, default: () => [] },
    contacts: { type: Array, default: () => [] }
});

const showModal = ref(false);
const searchQuery = ref('');
const statusFilter = ref('');

const form = useForm({
    title: '',
    account_id: null,
    contact_id: null,
    valid_until: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    items: [
        { product_id: null, name: '', description: '', quantity: 1, unit_price: 0 }
    ],
    tax_rate: 0
});

const subtotal = computed(() => {
    return form.items.reduce((acc, item) => acc + (item.quantity * item.unit_price), 0);
});

const total = computed(() => {
    return subtotal.value * (1 + (form.tax_rate || 0) / 100);
});

const filteredQuotes = computed(() => {
    let list = props.quotes || [];
    
    if (searchQuery.value) {
        const term = searchQuery.value.toLowerCase();
        list = list.filter(q => 
            q.title?.toLowerCase().includes(term) ||
            q.quote_number?.toLowerCase().includes(term) ||
            q.account?.name?.toLowerCase().includes(term)
        );
    }
    
    if (statusFilter.value) {
        list = list.filter(q => q.status?.toLowerCase() === statusFilter.value.toLowerCase());
    }
    
    return list;
});

const resetFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
};

const printView = () => window.print();

const addItem = () => {
    form.items.push({ product_id: null, name: '', description: '', quantity: 1, unit_price: 0 });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const onProductSelect = (item) => {
    if (!item.product_id) return;
    const product = props.products.find(p => p.id === item.product_id);
    if (product) {
        item.name = product.name;
        item.description = product.description;
        item.unit_price = product.base_price;
    }
};

const submitQuote = () => {
    form.post(route('crm.quotes.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount || 0);
};

const getStatusClass = (status) => {
    const s = status?.toLowerCase();
    const classes = {
        'sent': 'bg-indigo-50 text-indigo-600 border-indigo-100',
        'accepted': 'bg-emerald-50 text-emerald-600 border-emerald-100',
        'draft': 'bg-gray-50 text-gray-400 border-gray-100',
        'expired': 'bg-rose-50 text-rose-600 border-rose-100',
    };
    return classes[s] || 'bg-gray-50 text-gray-500 border-gray-100';
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
