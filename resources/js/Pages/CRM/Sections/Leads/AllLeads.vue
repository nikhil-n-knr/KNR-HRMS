<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none">
                <i class="fas fa-info text-xs"></i>
            </button>
            <div class="absolute right-0 top-10 w-72 bg-gray-900 text-white text-sm p-5 rounded-2xl shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium font-sans">
                <div class="font-black tracking-widest uppercase mb-2 text-indigo-400 border-b border-indigo-500/20 pb-2">Prospect Intelligence</div>
                <p class="text-gray-300 leading-relaxed mb-3">Your lead pipeline represents the future of your revenue. Track touchpoints, monitor engagement health, and convert prospects into high-value deals using the integrated CRM conversion engine.</p>
                <div class="space-y-1.5 pt-2 text-left">
                    <div class="flex items-center gap-2 text-left"><div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div> <span>Engagement Score Analysis</span></div>
                    <div class="flex items-center gap-2 text-left"><div class="w-1.5 h-1.5 rounded-full bg-amber-500"></div> <span>Pipeline Status Tracking</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="text-left">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight">Leads Pipeline</h2>
                        <div class="flex items-center mt-3">
                            <div class="flex -space-x-2 mr-4">
                                <div v-for="i in 3" :key="i" class="w-6 h-6 rounded-full border-2 border-white bg-indigo-500 shadow-sm"></div>
                            </div>
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-[0.2em]">{{ leads.length }} Active Prospects Found</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button @click="showCreateModal = true" class="px-8 py-3.5 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all font-black text-sm tracking-widest uppercase shadow-xl shadow-indigo-100 flex items-center group active:scale-95">
                            <i class="fas fa-bolt mr-2 text-xs group-hover:scale-125 transition-transform"></i>
                            Capture New Lead
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enhanced Toolbar -->
            <div class="px-8 py-5 border-b border-gray-200 bg-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative z-40">
                <div class="flex items-center gap-4 flex-1 w-full max-w-3xl">
                    <div class="relative w-full max-w-sm group">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-500 transition-colors"></i>
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder-gray-400" 
                            placeholder="Search by name, email or company..."
                        >
                    </div>

                    <div class="flex gap-2">
                        <select v-model="statusFilter" class="px-5 py-3 bg-gray-50 border-none rounded-xl text-sm font-black uppercase tracking-widest text-gray-500 focus:ring-4 focus:ring-indigo-500/10 cursor-pointer outline-none">
                            <option value="">All Statuses</option>
                            <option value="new">New / Uncontacted</option>
                            <option value="contacted">In Contact</option>
                            <option value="qualified">Qualified</option>
                            <option value="junk">Junk / Terminated</option>
                        </select>
                        <select v-model="sourceFilter" class="px-5 py-3 bg-gray-50 border-none rounded-xl text-sm font-black uppercase tracking-widest text-gray-500 focus:ring-4 focus:ring-indigo-500/10 cursor-pointer outline-none">
                            <option value="">All Sources</option>
                            <option v-for="s in sources" :key="s" :value="s">{{ s.charAt(0).toUpperCase() + s.slice(1).replace('_', ' ') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Export/Print Actions -->
                <div class="flex items-center gap-2 pr-8">
                    <button @click="printView" class="w-11 h-11 bg-white border border-gray-200 text-gray-400 rounded-xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm group relative">
                        <i class="fas fa-print text-sm"></i>
                        <div class="absolute -top-10 bg-gray-900 text-white text-sm font-black px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl">Print Pipeline</div>
                    </button>
                    <button class="w-11 h-11 bg-white border border-gray-200 text-gray-400 rounded-xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm group relative">
                        <i class="fas fa-file-excel text-sm"></i>
                        <div class="absolute -top-10 bg-gray-900 text-white text-sm font-black px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl">Export Excel</div>
                    </button>
                </div>
            </div>

            <!-- Prospect Table Grid -->
            <div class="flex-1 overflow-auto p-8" id="print-area">

                <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden min-w-[1000px]">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-8 py-6 text-left text-sm font-black text-gray-400 uppercase tracking-widest">Lead Identity</th>
                                <th class="px-8 py-6 text-left text-sm font-black text-gray-400 uppercase tracking-widest">Company / Org</th>
                                <th class="px-8 py-6 text-center text-sm font-black text-gray-400 uppercase tracking-widest">Pipeline Status</th>
                                <th class="px-8 py-6 text-center text-sm font-black text-gray-400 uppercase tracking-widest">Health & AI</th>
                                <th class="px-8 py-6 text-center text-sm font-black text-gray-400 uppercase tracking-widest">Engagement</th>
                                <th class="px-8 py-6 text-right text-sm font-black text-gray-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50">
                            <tr v-for="lead in filteredLeads" :key="lead.id" class="hover:bg-indigo-50/20 transition-all group/row cursor-pointer text-left" @click="router.visit(route('crm.leads.show', lead.id))">
                                <td class="px-8 py-8 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-14 w-14 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-2xl flex items-center justify-center text-white font-black text-lg shadow-lg shadow-indigo-100 group-hover/row:rotate-3 transition-transform border border-white">
                                            {{ lead.first_name[0] }}{{ lead.last_name[0] }}
                                        </div>
                                        <div class="ml-5">
                                            <div class="text-sm font-black text-gray-900 group-hover/row:text-indigo-600 transition-colors">
                                                {{ lead.first_name }} {{ lead.last_name }}
                                            </div>
                                            <div class="text-sm text-gray-400 font-bold mt-1 uppercase tracking-widest tracking-tighter">{{ lead.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-8 whitespace-nowrap">
                                    <div class="flex flex-col text-left">
                                        <span class="text-xs font-black text-gray-700 underline decoration-indigo-500/20 underline-offset-4">{{ lead.company || 'Private Individual' }}</span>
                                        <span class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-2 flex items-center gap-2">
                                            <i class="fas fa-crosshairs text-xs"></i>
                                            {{ lead.source || 'Direct' }} Focus
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-8 whitespace-nowrap text-center">
                                    <span :class="[getStatusClass(lead.status), 'px-5 py-2 rounded-full text-sm font-black uppercase tracking-widest shadow-sm border']">
                                        {{ lead.status }}
                                    </span>
                                </td>
                                <td class="px-8 py-8 whitespace-nowrap">
                                    <div class="flex items-center justify-center">
                                        <div class="w-24 h-2.5 bg-gray-100 rounded-full overflow-hidden border border-gray-50 flex shadow-inner">
                                            <div 
                                                class="h-full bg-gradient-to-r transition-all duration-1000"
                                                :class="getHealthClass(lead.health_score)"
                                                :style="{ width: lead.health_score + '%' }"
                                            ></div>
                                        </div>
                                        <span class="ml-3 text-sm font-black text-gray-900">{{ lead.health_score }}%</span>
                                    </div>
                                </td>
                                <td class="px-8 py-8 whitespace-nowrap text-center">
                                    <div class="flex flex-col items-center">
                                         <div class="text-xl font-black text-gray-900 tracking-tighter">{{ lead.score || 0 }}</div>
                                         <span class="text-xs font-black text-gray-300 uppercase tracking-widest">Touchpoints</span>
                                    </div>
                                </td>
                                <td class="px-8 py-8 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-3 translate-x-4 opacity-0 group-hover/row:opacity-100 group-hover/row:translate-x-0 transition-all duration-300">
                                        <button @click.stop="voiceCall(lead)" class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm border border-blue-100 group/btn relative">
                                            <i class="fas fa-phone text-xs"></i>
                                            <div class="absolute -top-9 bg-gray-900 text-white text-xs font-black px-2 py-1 rounded-md opacity-0 group-hover/btn:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl">Voice Call</div>
                                        </button>
                                        <button @click.stop="whatsappLead(lead)" class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm border border-emerald-100 group/btn relative">
                                            <i class="fab fa-whatsapp text-xs"></i>
                                            <div class="absolute -top-9 bg-gray-900 text-white text-xs font-black px-2 py-1 rounded-md opacity-0 group-hover/btn:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl">WhatsApp</div>
                                        </button>
                                        <button @click.stop="openConvertModal(lead)" class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shadow-sm border border-indigo-100 group/btn relative">
                                            <i class="fas fa-random text-xs"></i>
                                            <div class="absolute -top-9 bg-gray-900 text-white text-xs font-black px-2 py-1 rounded-md opacity-0 group-hover/btn:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl">Convert</div>
                                        </button>
                                        <button @click.stop="deleteLead(lead.id)" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 hover:bg-rose-600 hover:text-white transition-all shadow-sm border border-transparent group/btn relative">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                            <div class="absolute -top-9 bg-gray-900 text-white text-xs font-black px-2 py-1 rounded-md opacity-0 group-hover/btn:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl">Archive</div>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <div v-if="filteredLeads.length === 0" class="flex flex-col items-center justify-center py-24 bg-gray-50/20">
                        <div class="w-24 h-24 bg-white rounded-[40px] shadow-sm flex items-center justify-center mx-auto mb-8 border border-gray-100 transform -rotate-6">
                            <i class="fas fa-radar text-4xl text-gray-200"></i>
                        </div>
                        <h3 class="text-2xl font-black text-gray-900 mb-3 tracking-tight">Expansion Zone Not Detected</h3>
                        <p class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-2 px-10 text-center">No prospects match your current filtering criteria in this sector.</p>
                        <button @click="resetFilters" class="mt-8 px-8 py-3 bg-white border border-gray-200 text-gray-500 rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm">Reset Strategy</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- PREMIUM CREATE MODAL -->
        <div v-if="showCreateModal" @click.self="showCreateModal = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
            <div class="relative bg-white rounded-[40px] shadow-2xl max-w-2xl w-full overflow-hidden border border-white">
                <div class="bg-gray-50/50 px-10 py-8 border-b border-gray-100 flex justify-between items-center text-left">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white mr-4 shadow-lg shadow-indigo-100">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 tracking-tight">Capture Lead</h3>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mt-1">Direct Entry / Source Import</p>
                        </div>
                    </div>
                    <button @click="showCreateModal = false" class="w-12 h-12 rounded-2xl bg-white border border-gray-100 text-gray-400 hover:text-gray-900 flex items-center justify-center shadow-sm transition-all hover:rotate-90">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form @submit.prevent="submit" class="p-10 space-y-6 text-left">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1">First Name</label>
                             <input v-model="form.first_name" type="text" required class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-5 font-semibold shadow-inner" placeholder="John">
                        </div>
                        <div class="space-y-2">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1">Last Name</label>
                             <input v-model="form.last_name" type="text" required class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-5 font-semibold shadow-inner" placeholder="Doe">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1">Work Email</label>
                             <input v-model="form.email" type="email" required class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-5 font-semibold shadow-inner" placeholder="john.doe@company.com">
                        </div>
                        <div class="space-y-2">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1">Company Name</label>
                             <input v-model="form.company" type="text" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-5 font-semibold shadow-inner" placeholder="Acme Global Inc.">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1">Lead Source</label>
                             <select v-model="form.source" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-5 font-bold shadow-inner">
                                <option v-for="s in sources" :key="s" :value="s">{{ s.charAt(0).toUpperCase() + s.slice(1).replace('_', ' ') }}</option>
                             </select>
                        </div>
                        <div class="space-y-2">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1">Initial Status</label>
                             <select v-model="form.status" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-5 font-bold shadow-inner">
                                <option v-for="st in statuses" :key="st" :value="st">{{ st.charAt(0).toUpperCase() + st.slice(1) }}</option>
                             </select>
                        </div>
                    </div>

                    <div class="pt-6 flex items-center justify-end gap-6">
                        <button type="button" @click="showCreateModal = false" class="text-sm font-black text-gray-400 hover:text-gray-900 transition-colors flex items-center group">
                            <i class="fas fa-ban mr-2 opacity-0 group-hover:opacity-100 transition-opacity"></i> DISCARD
                        </button>
                        <button type="submit" 
                                :disabled="form.processing"
                                class="bg-indigo-600 text-white px-10 py-4 rounded-3xl font-black text-sm hover:bg-indigo-700 transition-all shadow-2xl shadow-indigo-100 disabled:opacity-50 min-w-[180px] flex items-center justify-center">
                            <i class="fas fa-bolt mr-2"></i>
                            {{ form.processing ? 'CAPTURING...' : 'SAVE PROSPECT' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- CONVERT MODAL -->
        <div v-if="showConvertModal" @click.self="showConvertModal = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4 text-left">
            <div class="relative bg-white rounded-[40px] shadow-2xl max-w-md w-full overflow-hidden border border-white">
                <div class="bg-emerald-50/50 px-8 py-6 border-b border-emerald-100 flex justify-between items-center text-left">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white mr-4 shadow-lg shadow-emerald-100">
                            <i class="fas fa-random"></i>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">Convert Lead</h3>
                    </div>
                    <button @click="showConvertModal = false" class="text-gray-400 hover:text-gray-900 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="p-8 space-y-6">
                    <p class="text-sm text-gray-500 font-medium leading-relaxed">
                        Converting <span class="font-black text-gray-900">{{ selectedLead?.first_name }}</span> will create a permanent contact record and link them to an account.
                    </p>

                    <div class="space-y-2">
                        <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1">Link to Account</label>
                        <select v-model="convertForm.account_id" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:bg-white transition-all text-sm py-4 px-5 font-bold shadow-inner">
                            <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
                            <option value="">+ Create New From Company Name</option>
                        </select>
                    </div>

                    <button @click="submitConvert" 
                            :disabled="convertForm.processing"
                            class="w-full bg-emerald-600 text-white py-4 rounded-2xl font-black text-sm hover:bg-emerald-700 transition-all shadow-xl shadow-emerald-100 disabled:opacity-50 flex items-center justify-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ convertForm.processing ? 'CONVERTING...' : 'FINALIZE CONVERSION' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    leads: { type: Array, default: () => [] },
    accounts: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    sources: { type: Array, default: () => [] },
    statuses: { type: Array, default: () => [] }
});

const showCreateModal = ref(false);
const showConvertModal = ref(false);
const selectedLead = ref(null);
const searchQuery = ref('');

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    company: '',
    source: 'Website',
    status: 'new',
});

const convertForm = useForm({
    account_id: ''
});

const openConvertModal = (lead) => {
    selectedLead.value = lead;
    convertForm.account_id = lead.account_id || '';
    showConvertModal.value = true;
};

const submitConvert = () => {
    // If account_id is empty/null and the lead has a company, the backend will auto-create
    // But we can be explicit if we want to force it
    const payload = {
        account_id: convertForm.account_id || null,
        create_account: !convertForm.account_id && !!selectedLead.value.company
    };

    router.post(route('crm.leads.convert', selectedLead.value.id), payload, {
        onSuccess: () => {
            showConvertModal.value = false;
        }
    });
};

const submit = () => {
    form.post(route('crm.leads.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        }
    });
};

const voiceCall = (lead) => {
    if (confirm(`Initiate Voice AI Call to ${lead.first_name}?`)) {
        router.post(route('crm.leads.voice-call', lead.id));
    }
};

const whatsappLead = (lead) => {
    if (confirm(`Send WhatsApp engagement nudge to ${lead.first_name}?`)) {
        router.post(route('crm.leads.whatsapp', lead.id));
    }
};

const editLead = (lead) => {
    alert('Feature coming soon: Advanced lead editor');
};

const statusFilter = ref('');
const sourceFilter = ref('');

const filteredLeads = computed(() => {
    let list = props.leads || [];
    
    if (searchQuery.value) {
        const term = searchQuery.value.toLowerCase();
        list = list.filter(lead =>
            (lead.first_name + ' ' + lead.last_name).toLowerCase().includes(term) ||
            lead.email?.toLowerCase().includes(term) ||
            lead.company?.toLowerCase().includes(term)
        );
    }

    if (statusFilter.value) {
        list = list.filter(l => l.status?.toLowerCase() === statusFilter.value.toLowerCase());
    }

    if (sourceFilter.value) {
        list = list.filter(l => l.source === sourceFilter.value);
    }
    
    return list;
});

const printView = () => window.print();

const resetFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
    sourceFilter.value = '';
};

const getHealthClass = (score) => {
    if (score >= 80) return 'from-emerald-400 to-emerald-600 shadow-emerald-500/20';
    if (score >= 50) return 'from-amber-400 to-amber-600 shadow-amber-500/20';
    return 'from-rose-400 to-rose-600 shadow-rose-500/20';
};


const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const getHealthColor = (score) => {
    if (score >= 80) return 'text-emerald-500';
    if (score >= 50) return 'text-amber-500';
    return 'text-rose-500';
};

const getStatusClass = (status) => {
    const classes = {
        'new': 'bg-blue-50 text-blue-600 border-blue-100',
        'contacted': 'bg-amber-50 text-amber-600 border-amber-100',
        'qualified': 'bg-emerald-50 text-emerald-600 border-emerald-100',
        'lost': 'bg-rose-50 text-rose-600 border-rose-100',
    };
    return classes[status] || 'bg-gray-50 text-gray-500 border-gray-100';
};
</script>
