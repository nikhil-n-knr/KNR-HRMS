<template>
    <div class="space-y-8 text-left">
        <!-- Header Actions -->
        <div class="flex justify-between items-end pb-4 border-b border-gray-100">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Leads Pipeline</h2>
                <div class="flex items-center mt-2">
                    <span class="w-8 h-1 bg-indigo-500 rounded-full mr-3"></span>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-widest">Prospect Management</p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="relative group">
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Search prospects..."
                        class="pl-12 pr-6 py-3 bg-gray-50 border-transparent rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white focus:border-indigo-500 transition-all text-sm font-semibold w-64 shadow-inner"
                    />
                    <i class="fas fa-search absolute left-5 top-4 text-gray-300 group-focus-within:text-indigo-500 transition-colors"></i>
                </div>
                <button @click="showCreateModal = true" class="px-8 py-3 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all font-black text-sm shadow-xl shadow-indigo-100 flex items-center group">
                    <i class="fas fa-bolt mr-2 text-xs group-hover:scale-125 transition-transform"></i>
                    CAPTURE LEAD
                </button>
            </div>
        </div>

        <!-- Leads Table -->
        <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-8 py-5 text-left text-sm font-black text-gray-400 uppercase tracking-widest">Lead Identity</th>
                        <th class="px-8 py-5 text-left text-sm font-black text-gray-400 uppercase tracking-widest">Company / Org</th>
                        <th class="px-8 py-5 text-center text-sm font-black text-gray-400 uppercase tracking-widest">Pipeline Status</th>
                        <th class="px-8 py-5 text-center text-sm font-black text-gray-400 uppercase tracking-widest">Engagement Score</th>
                        <th class="px-8 py-5 text-right text-sm font-black text-gray-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    <tr v-for="lead in filteredLeads" :key="lead.id" class="hover:bg-indigo-50/30 transition-colors group">
                        <td class="px-8 py-6 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-14 w-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white font-black text-lg shadow-lg shadow-indigo-100 group-hover:rotate-3 transition-transform">
                                    {{ getInitials(lead.first_name + ' ' + lead.last_name) }}
                                </div>
                                <div class="ml-5">
                                    <div class="text-sm font-black text-gray-900 group-hover:text-indigo-600 transition-colors">
                                        {{ lead.first_name }} {{ lead.last_name }}
                                    </div>
                                    <div class="text-xs text-gray-400 font-bold mt-0.5">{{ lead.email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-xs font-black text-gray-700">{{ lead.company || 'Private Individual' }}</span>
                                <span class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-1">{{ lead.source || 'Direct' }} Source</span>
                            </div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap text-center">
                            <span :class="[getStatusClass(lead.status), 'px-4 py-1.5 rounded-xl text-sm font-black uppercase tracking-widest border']">
                                {{ lead.status }}
                            </span>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-xs font-black text-gray-900 mb-2">{{ lead.score || 0 }} pts</span>
                                <div class="w-24 bg-gray-100 rounded-full h-1.5 overflow-hidden border border-gray-50 shadow-inner">
                                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-full rounded-full" :style="{ width: (lead.score || 0) + '%' }"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap text-right">
                            <div class="flex justify-end gap-2">
                                <button @click="openConvertModal(lead)" class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm border border-emerald-100" title="Link / Convert">
                                    <i class="fas fa-random text-xs"></i>
                                </button>
                                <button @click="editLead(lead)" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 hover:bg-indigo-600 hover:text-white transition-all shadow-sm border border-gray-100">
                                    <i class="fas fa-pen text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Empty State -->
            <div v-if="!filteredLeads || filteredLeads.length === 0" class="text-center py-24 bg-gray-50/20">
                <div class="w-24 h-24 bg-white rounded-[32px] shadow-sm flex items-center justify-center mx-auto mb-8 border border-gray-100 transform -rotate-6">
                    <i class="fas fa-radar text-4xl text-gray-200"></i>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-3 tracking-tight">No Active Prospects</h3>
                <p class="text-gray-500 max-w-sm mx-auto font-medium leading-relaxed mb-10">Start your sales engine by capturing your first lead or importing from your list.</p>
                <button @click="showCreateModal = true" class="px-10 py-4 bg-indigo-600 text-white rounded-2xl font-black shadow-2xl shadow-indigo-100 hover:bg-indigo-700 transition-all">
                    CAPTURE NEW LEAD
                </button>
            </div>
        </div>

        <!-- PREMIUM CREATE MODAL -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
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
                                <option value="Website">Website</option>
                                <option value="Referral">Referral</option>
                                <option value="LinkedIn">LinkedIn</option>
                                <option value="Ads">Ads / Paid Search</option>
                                <option value="External">External Partner</option>
                             </select>
                        </div>
                        <div class="space-y-2">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1">Initial Status</label>
                             <select v-model="form.status" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4 px-5 font-bold shadow-inner">
                                <option value="new">New Prospect</option>
                                <option value="contacted">Contacted</option>
                                <option value="qualified">Qualified</option>
                             </select>
                        </div>
                    </div>

                    <div class="pt-6 flex items-center justify-end gap-6">
                        <button type="button" @click="showCreateModal = false" class="text-sm font-black text-gray-400 hover:text-gray-900 transition-colors">DISCARD</button>
                        <button type="submit" 
                                :disabled="form.processing"
                                class="bg-indigo-600 text-white px-10 py-4 rounded-3xl font-black text-sm hover:bg-indigo-700 transition-all shadow-2xl shadow-indigo-100 disabled:opacity-50 min-w-[180px]">
                            {{ form.processing ? 'CAPTURING...' : 'SAVE PROSPECT' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- CONVERT MODAL -->
        <div v-if="showConvertModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4 text-left">
            <div class="relative bg-white rounded-[40px] shadow-2xl max-w-md w-full overflow-hidden border border-white">
                <div class="bg-emerald-50/50 px-8 py-6 border-b border-emerald-100 flex justify-between items-center">
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
                            class="w-full bg-emerald-600 text-white py-4 rounded-2xl font-black text-sm hover:bg-emerald-700 transition-all shadow-xl shadow-emerald-100 disabled:opacity-50">
                        {{ convertForm.processing ? 'CONVERTING...' : 'FINALIZE CONVERSION' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    leads: { type: Array, default: () => [] },
    accounts: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] }
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
    title: '',
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
    convertForm.post(route('crm.leads.convert', selectedLead.value.id), {
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

const editLead = (lead) => {
    // Lead edit logic or redirect
    alert('Feature coming soon: Advanced lead editor');
};

const filteredLeads = computed(() => {
    let list = props.leads || [];
    if (!searchQuery.value) return list;
    
    return list.filter(lead =>
        (lead.first_name + ' ' + lead.last_name).toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        lead.email?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        lead.company?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
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
