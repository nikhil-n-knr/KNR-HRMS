<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none">
                <i class="fas fa-info text-xs"></i>
            </button>
            <div class="absolute right-0 top-10 w-72 bg-gray-900 text-white text-sm p-5 rounded-2xl shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium font-sans">
                <div class="font-black tracking-widest uppercase mb-2 text-indigo-400 border-b border-indigo-500/20 pb-2 text-left">Relationship Intelligence</div>
                <p class="text-gray-300 leading-relaxed mb-3 text-left">Your contacts are the heartbeat of your ecosystem. Manage key stakeholders, track engagement across accounts, and leverage AI-driven insights to nurture high-value relationships.</p>
                <div class="space-y-1.5 pt-2 text-left">
                    <div class="flex items-center gap-2 text-left"><div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div> <span>Stakeholder Mapping</span></div>
                    <div class="flex items-center gap-2 text-left"><div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div> <span>Cross-Account Activity</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0 font-sans">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="text-left">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight">Active Contacts</h2>
                        <div class="flex items-center mt-3">
                            <div class="flex -space-x-2 mr-4 text-left">
                                <div v-for="i in 3" :key="i" class="w-6 h-6 rounded-full border-2 border-white bg-indigo-500 shadow-sm flex items-center justify-center text-xs text-white font-black">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-[0.2em]">{{ contacts.length }} Stakeholders Identified</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button @click="showCreateModal = true" class="px-8 py-3.5 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all font-black text-sm tracking-widest uppercase shadow-xl shadow-indigo-100 flex items-center group active:scale-95">
                            <i class="fas fa-plus mr-2 text-xs group-hover:scale-125 transition-transform"></i>
                            New Stakeholder
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
                            placeholder="Search stakeholders by name, email or account..."
                        >
                    </div>

                    <div class="flex gap-2">
                        <select v-model="accountFilter" class="px-5 py-3 bg-gray-50 border-none rounded-xl text-sm font-black uppercase tracking-widest text-gray-500 focus:ring-4 focus:ring-indigo-500/10 cursor-pointer outline-none w-48">
                            <option value="">All Accounts</option>
                            <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                        </select>
                    </div>
                </div>

                <!-- Export/Print Actions -->
                <div class="flex items-center gap-2 pr-8">
                    <button @click="printView" class="w-11 h-11 bg-white border border-gray-200 text-gray-400 rounded-xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm group relative">
                        <i class="fas fa-print text-sm"></i>
                        <div class="absolute -top-10 bg-gray-900 text-white text-sm font-black px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl">Print Index</div>
                    </button>
                    <button class="w-11 h-11 bg-white border border-gray-200 text-gray-400 rounded-xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm group relative">
                        <i class="fas fa-file-excel text-sm"></i>
                        <div class="absolute -top-10 bg-gray-900 text-white text-sm font-black px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl">Export CSV</div>
                    </button>
                </div>
            </div>

            <!-- Contacts Grid Container -->
            <div class="flex-1 overflow-auto p-8" id="print-area">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
                    <div
                        v-for="contact in filteredContacts"
                        :key="contact.id"
                        @click="selectContact(contact)"
                        class="bg-white rounded-[32px] shadow-sm border border-gray-100 p-8 hover:shadow-2xl hover:shadow-indigo-500/10 transition-all cursor-pointer group/card relative overflow-hidden flex flex-col text-left"
                    >
                        <!-- Background Pattern -->
                        <div class="absolute -right-6 -top-6 opacity-[0.02] group-hover/card:opacity-[0.05] transition-opacity pointer-events-none">
                            <i class="fas fa-user-tie text-[160px]"></i>
                        </div>

                        <div class="flex items-start justify-between mb-6 text-left">
                            <div class="flex items-center text-left">
                                <div class="flex-shrink-0 h-16 w-16 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-[22px] flex items-center justify-center text-white font-black text-2xl shadow-lg shadow-indigo-100 border-2 border-white group-hover/card:scale-110 transition-all text-left">
                                    {{ getInitials(contact.first_name + ' ' + contact.last_name) }}
                                </div>
                                <div class="ml-5 text-left">
                                    <h3 class="text-xl font-black text-gray-900 group-hover/card:text-indigo-600 transition-colors tracking-tight leading-none text-left">{{ contact.first_name }} {{ contact.last_name }}</h3>
                                    <span class="inline-block text-sm font-bold text-indigo-500 uppercase tracking-widest mt-2 px-3 py-1 bg-indigo-50 rounded-lg text-left">{{ contact.title || 'SME Contact' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Info Stack -->
                        <div class="space-y-4 mb-8 flex-1 text-left">
                            <div v-if="contact.account" class="p-4 bg-gray-50 rounded-2xl border border-gray-100 flex items-center group/acc text-left">
                                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-sm mr-4 border border-gray-100 text-left">
                                    <i class="fas fa-building text-indigo-500 text-xs text-left"></i>
                                </div>
                                <div class="flex flex-col text-left">
                                    <span class="text-sm font-black text-gray-400 uppercase tracking-widest text-left">Enterprise</span>
                                    <span class="text-sm font-black text-gray-800 text-left">{{ contact.account.name }}</span>
                                </div>
                            </div>

                            <div class="flex items-center text-sm text-gray-600 px-4 py-2 hover:bg-indigo-50/50 rounded-xl transition-colors text-left">
                                <div class="w-6 mr-3 flex justify-center text-left"><i class="fas fa-envelope text-gray-300 text-xs text-left"></i></div>
                                <span class="font-bold flex-1 truncate text-left">{{ contact.email }}</span>
                            </div>

                            <div v-if="contact.phone" class="flex items-center text-sm text-gray-600 px-4 py-2 hover:bg-indigo-50/50 rounded-xl transition-colors text-left">
                                <div class="w-6 mr-3 flex justify-center text-left"><i class="fas fa-phone text-gray-300 text-xs text-left"></i></div>
                                <span class="font-bold whitespace-nowrap text-left">{{ contact.phone }}</span>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="pt-6 border-t border-gray-50 flex items-center justify-between text-left">
                            <div class="flex -space-x-1 text-left">
                                <div v-for="i in 2" :key="i" class="w-6 h-6 rounded-full border border-white bg-gray-100 flex items-center justify-center text-[6px] text-gray-300 text-left">
                                    <i class="fas fa-user text-xs text-left"></i>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-left">
                                <span class="text-sm font-black text-gray-400 uppercase tracking-widest mr-2 text-left">Last Sync: 2h ago</span>
                                <div class="w-2 h-2 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/20 text-left"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredContacts.length === 0" class="flex flex-col items-center justify-center py-32 text-gray-300 text-left">
                    <div class="w-24 h-24 bg-white rounded-[40px] shadow-sm flex items-center justify-center mb-8 border border-gray-100 text-left">
                         <i class="fas fa-search text-3xl opacity-20 text-left"></i>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 tracking-tight text-left">Intelligence Vacuum Detected</h3>
                    <p class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-2 text-left">No stakeholders match your current strategic filters.</p>
                </div>
            </div>
        </div>

        <!-- PROFILE DRAWER -->
        <div v-if="showProfileModal" @click.self="closeProfile" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex justify-end">
            <div class="w-full max-w-2xl bg-white h-full shadow-2xl animate-in slide-in-from-right duration-500 p-10 overflow-y-auto border-l border-white/20 text-left font-sans">
                <div class="flex justify-between items-start mb-10 text-left">
                    <div class="flex items-center gap-5 text-left">
                       <div class="w-20 h-20 bg-indigo-600 rounded-[28px] flex items-center justify-center text-white font-black text-3xl shadow-xl shadow-indigo-100 text-left">
                           {{ getInitials(selected_contact.first_name + ' ' + selected_contact.last_name) }}
                       </div>
                       <div class="text-left">
                           <h2 class="text-3xl font-black text-gray-900 tracking-tight text-left">{{ selected_contact.first_name }} {{ selected_contact.last_name }}</h2>
                           <p class="text-sm font-bold text-indigo-500 uppercase tracking-[0.2em] mt-1 text-left">{{ selected_contact.title || 'Stakeholder' }}</p>
                       </div>
                    </div>
                    <button @click="closeProfile" class="h-10 w-10 bg-gray-50 rounded-xl flex items-center justify-center hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-all">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- AI Recommendations -->
                <div class="bg-gray-900 text-white rounded-[32px] p-8 mb-10 relative overflow-hidden text-left">
                    <div class="absolute top-0 right-0 p-8 opacity-10 text-left">
                        <i class="fas fa-sparkles text-8xl text-left"></i>
                    </div>
                    <div class="relative z-10 text-left">
                        <div class="flex items-center gap-3 mb-6 text-left">
                            <i class="fas fa-bolt text-indigo-400 text-left"></i>
                            <h4 class="text-sm font-black uppercase tracking-[0.3em] text-gray-400 text-left">Propensity Engines</h4>
                        </div>
                        <div v-if="recommendations && recommendations.length > 0" class="space-y-4 text-left">
                            <div v-for="rec in recommendations" :key="rec.id" class="flex items-center justify-between bg-white/5 p-4 rounded-2xl border border-white/5 hover:bg-white/10 transition-all text-left">
                                <div class="text-left">
                                    <p class="font-black text-sm text-left">{{ rec.name }}</p>
                                    <p class="text-sm text-gray-400 font-bold mt-1 uppercase tracking-widest text-left">{{ formatCurrency(rec.base_price) }} Potential</p>
                                </div>
                                <button class="h-10 w-10 bg-indigo-500 rounded-xl flex items-center justify-center text-white hover:bg-indigo-400 shadow-lg shadow-indigo-900/50">
                                    <i class="fas fa-plus text-xs"></i>
                                </button>
                            </div>
                        </div>
                        <p v-else class="text-xs text-gray-500 italic font-medium px-2 text-left">Neutral intelligence cycle ongoing...</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-8 mb-10 text-left">
                    <div class="space-y-6 text-left">
                        <h4 class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] pb-3 border-b border-gray-100 flex items-center gap-2 text-left">
                            <i class="fas fa-id-card text-indigo-500 text-left"></i> Dossier Alpha
                        </h4>
                        <div class="space-y-4 text-left">
                            <div class="flex items-center text-sm font-black text-gray-700 text-left">
                                <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center mr-3 text-left"><i class="fas fa-envelope text-gray-300 text-sm text-left"></i></div>
                                {{ selected_contact.email }}
                            </div>
                            <div v-if="selected_contact.phone" class="flex items-center text-sm font-black text-gray-700 text-left">
                                <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center mr-3 text-left"><i class="fas fa-phone text-gray-300 text-sm text-left"></i></div>
                                {{ selected_contact.phone }}
                            </div>
                            <div v-if="selected_contact.account" class="flex items-center text-sm font-black text-gray-700 text-left">
                                <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center mr-3 text-left"><i class="fas fa-building text-gray-300 text-sm text-left"></i></div>
                                {{ selected_contact.account.name }}
                            </div>
                        </div>
                    </div>
                    <RelationshipGraph :graph-data="graph_data" class="bg-gray-50 rounded-[32px] border border-gray-100" />
                </div>

                <div class="min-h-96 text-left">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-6">
                        <h4 class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] flex items-center gap-2">
                            <i class="fas fa-history text-indigo-500"></i> Event Stream
                        </h4>
                        <Link :href="route('crm.contacts.show', selected_contact.id)" class="text-xs font-black text-indigo-500 uppercase tracking-widest hover:underline">
                            Open Full Profile <i class="fas fa-external-link-alt ml-1"></i>
                        </Link>
                    </div>
                    <TimelineHub 
                        type="contact"
                        :id="selected_contact.id"
                    />
                </div>
            </div>
        </div>

        <!-- CREATE MODAL -->
        <div v-if="showCreateModal" @click.self="showCreateModal = false" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="relative bg-white rounded-[40px] shadow-2xl max-w-xl w-full overflow-hidden border border-white text-left font-sans">
                <div class="bg-gray-50/50 px-10 py-8 border-b border-gray-100 flex justify-between items-center text-left">
                    <div class="text-left">
                        <h3 class="text-2xl font-black text-gray-900 tracking-tight text-left">Capture Stakeholder</h3>
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mt-1 text-left">Direct Entry / Enterprise Protocol</p>
                    </div>
                    <button @click="showCreateModal = false" class="w-12 h-12 rounded-2xl bg-white border border-gray-100 text-gray-400 hover:text-gray-900 flex items-center justify-center shadow-sm transition-all hover:rotate-90">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form @submit.prevent="submit" class="p-10 space-y-6 text-left">
                    <div class="grid grid-cols-2 gap-6 text-left">
                        <div class="space-y-2 text-left">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left">First Name</label>
                             <input v-model="form.first_name" type="text" required class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-5 font-semibold shadow-inner text-left" placeholder="John">
                        </div>
                        <div class="space-y-2 text-left">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left">Last Name</label>
                             <input v-model="form.last_name" type="text" required class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-5 font-semibold shadow-inner text-left" placeholder="Doe">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6 text-left">
                        <div class="space-y-2 text-left">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left">Work Email</label>
                             <input v-model="form.email" type="email" required class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-5 font-semibold shadow-inner text-left" placeholder="john.doe@company.com">
                        </div>
                        <div class="space-y-2 text-left">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left">Direct Line</label>
                             <input v-model="form.phone" type="text" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-5 font-semibold shadow-inner text-left" placeholder="+1 (555) 000-0000">
                        </div>
                    </div>

                    <div class="space-y-2 text-left">
                        <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left">Enterprise Linkage</label>
                        <select v-model="form.account_id" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-5 font-bold shadow-inner text-left">
                            <option value="">No Active Account</option>
                            <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
                        </select>
                    </div>

                    <div class="pt-6 flex items-center justify-end gap-6 text-left">
                        <button type="button" @click="showCreateModal = false" class="text-sm font-black text-gray-400 hover:text-gray-900 transition-colors flex items-center group text-left">
                             DISCARD
                        </button>
                        <button type="submit" 
                                :disabled="form.processing"
                                class="bg-indigo-600 text-white px-10 py-4 rounded-3xl font-black text-sm hover:bg-indigo-700 transition-all shadow-2xl shadow-indigo-100 disabled:opacity-50 min-w-[180px] flex items-center justify-center text-left">
                            <i class="fas fa-bolt mr-2 text-left"></i>
                            {{ form.processing ? 'DEPLOING...' : 'SAVE STAKEHOLDER' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import ActivityTimeline from './ActivityTimeline.vue';
import RelationshipGraph from './Components/RelationshipGraph.vue';
import TimelineHub from '@/Components/CRM/TimelineHub.vue';

const props = defineProps({
    contacts: { type: Array, default: () => [] },
    accounts: { type: Array, default: () => [] },
    recommendations: { type: Array, default: () => [] },
    graph_data: { type: Object, default: () => ({ nodes: [], links: [] }) },
    selected_contact: { type: Object, default: null }
});

const showCreateModal = ref(false);
const showProfileModal = ref(!!props.selected_contact);
const searchQuery = ref('');

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    account_id: '',
    title: ''
});

const selectContact = (contact) => {
    // Visit same route with contact_id to fetch recommendations
    router.get(route('crm.hub'), { 
        section: 'contacts', 
        tab: 'all_contacts', 
        contact_id: contact.id 
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            showProfileModal.value = true;
        }
    });
};

const closeProfile = () => {
    showProfileModal.value = false;
    // Optional: clear query param
    router.get(route('crm.hub'), { section: 'contacts', tab: 'all_contacts' }, { preserveState: true, preserveScroll: true });
};

const submit = () => {
    form.post(route('crm.contacts.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        }
    });
};

const accountFilter = ref('');

const filteredContacts = computed(() => {
    let list = props.contacts || [];
    
    if (searchQuery.value) {
        const term = searchQuery.value.toLowerCase();
        list = list.filter(contact =>
            (contact.first_name + ' ' + contact.last_name).toLowerCase().includes(term) ||
            contact.email?.toLowerCase().includes(term) ||
            contact.account?.name?.toLowerCase().includes(term)
        );
    }

    if (accountFilter.value) {
        list = list.filter(c => String(c.account_id) === String(accountFilter.value));
    }
    
    return list;
});

const printView = () => window.print();


const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
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
