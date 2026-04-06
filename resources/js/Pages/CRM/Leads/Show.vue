<template>
    <Head :title="`${lead.first_name} ${lead.last_name} | Lead Profile`" />

    <div class="min-h-screen bg-gray-50 flex overflow-hidden">
        <!-- Sidebar -->
        <Sidebar section="leads" />

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Header/Breadcrumbs -->
            <div class="bg-white border-b border-gray-100 px-8 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('crm.hub', { section: 'leads' })" class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:text-indigo-600 transition-all border border-gray-100">
                        <i class="fas fa-arrow-left"></i>
                    </Link>
                    <div>
                        <h2 class="text-lg font-black text-gray-900 tracking-tight">Lead Profile</h2>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Leads</span>
                            <i class="fas fa-chevron-right text-[8px] text-gray-300"></i>
                            <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">{{ lead.first_name }} {{ lead.last_name }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button @click="voiceCall" class="px-5 py-2.5 rounded-xl bg-emerald-50 text-emerald-600 font-black text-xs uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all flex items-center gap-2 border border-emerald-100">
                        <i class="fas fa-phone"></i> Voice Call
                    </button>
                    <button @click="sendEmail" class="px-5 py-2.5 rounded-xl bg-blue-50 text-blue-600 font-black text-xs uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all flex items-center gap-2 border border-blue-100">
                        <i class="fas fa-envelope"></i> Send Email
                    </button>
                    <button @click="showTransferModal = true" class="px-5 py-2.5 rounded-xl bg-indigo-50 text-indigo-600 font-black text-xs uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all flex items-center gap-2 border border-indigo-100">
                        <i class="fas fa-exchange-alt"></i> Transfer
                    </button>
                    <button @click="showConvertModal = true" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all flex items-center gap-2 shadow-lg shadow-indigo-100">
                        <i class="fas fa-random"></i> Convert
                    </button>
                </div>
            </div>

            <!-- Main Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-8">
                <div class="max-w-7xl mx-auto grid grid-cols-12 gap-8">
                    
                    <!-- Left Column: Identity & Details -->
                    <div class="col-span-12 lg:col-span-4 space-y-8">
                        <!-- Identity Card -->
                        <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-8 text-center relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
                            
                            <div class="w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-[32px] mx-auto mb-6 flex items-center justify-center text-white text-3xl font-black shadow-xl shadow-indigo-100 border-4 border-white">
                                {{ lead.first_name[0] }}{{ lead.last_name[0] }}
                            </div>

                            <h1 class="text-2xl font-black text-gray-900 tracking-tight mb-1">{{ lead.first_name }} {{ lead.last_name }}</h1>
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">{{ lead.company || 'Private Prospect' }}</p>

                            <div class="grid grid-cols-2 gap-4 py-6 border-t border-gray-50">
                                <div>
                                    <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em] mb-1">Status</p>
                                    <span :class="[getStatusClass(lead.status), 'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border']">
                                        {{ lead.status }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em] mb-1">Score</p>
                                    <span class="text-lg font-black text-gray-900">{{ lead.score || 0 }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Details -->
                        <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-8">
                            <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-6">Discovery Details</h3>
                            <div class="space-y-6">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest mb-1">Email Address</p>
                                        <p class="text-sm font-bold text-gray-900">{{ lead.email }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest mb-1">Phone Number</p>
                                        <p class="text-sm font-bold text-gray-900">{{ lead.phone || 'Not provided' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400">
                                        <i class="fas fa-location-arrow"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest mb-1">Source</p>
                                        <p class="text-sm font-bold text-gray-900">{{ lead.source }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Timeline -->
                    <div class="col-span-12 lg:col-span-8 space-y-8">
                        <!-- Dashboard Tabs -->
                        <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-2 flex gap-2">
                            <button 
                                @click="activeTab = 'timeline'"
                                :class="[
                                    'flex-1 py-4 rounded-[32px] text-xs font-black uppercase tracking-widest transition-all',
                                    activeTab === 'timeline' ? 'bg-gray-900 text-white shadow-xl' : 'text-gray-400 hover:text-gray-900'
                                ]"
                            >
                                360° Timeline
                            </button>
                            <button 
                                @click="activeTab = 'insights'"
                                :class="[
                                    'flex-1 py-4 rounded-[32px] text-xs font-black uppercase tracking-widest transition-all',
                                    activeTab === 'insights' ? 'bg-gray-900 text-white shadow-xl' : 'text-gray-400 hover:text-gray-900'
                                ]"
                            >
                                AI Insights
                            </button>
                        </div>

                        <!-- Content Render -->
                        <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-8 min-h-[500px]">
                            <TimelineHub v-if="activeTab === 'timeline'" type="lead" :id="lead.id" />
                            <div v-else class="flex flex-col items-center justify-center h-full text-center p-12">
                                <i class="fas fa-brain text-4xl text-gray-100 mb-6"></i>
                                <h4 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-2">Neural Analysis Pending</h4>
                                <p class="text-xs text-gray-400 font-medium leading-relaxed max-w-xs">
                                    Our AI engine is currently synthesizing interactions to provide predictive scoring and conversion recommendations.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Transfer Modal -->
        <div v-if="showTransferModal" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-[40px] shadow-2xl max-w-lg w-full overflow-hidden border border-white">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight">Ownership Handover</h3>
                    <button @click="showTransferModal = false" class="text-gray-400 hover:text-gray-900 transition-transform hover:rotate-90">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-8 space-y-6">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 text-left">Target Recipient (New Owner)</label>
                        <select v-model="transferForm.user_id" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
                            <option value="">Select individual...</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 text-left">Handover Rationale</label>
                        <textarea v-model="transferForm.reason" placeholder="Explain the context for this ownership transfer..." class="w-full bg-gray-50 border-none rounded-2xl p-4 text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 shadow-inner h-32"></textarea>
                    </div>
                    <button @click="executeTransfer" :disabled="transferForm.processing" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-bolt"></i> {{ transferForm.processing ? 'DEPLOING...' : 'Confirm Handover' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Sidebar from '../Components/Sidebar.vue';
import TimelineHub from '@/Components/CRM/TimelineHub.vue';

const props = defineProps({
    lead: { type: Object, required: true },
    users: { type: Array, default: () => [] }
});

const activeTab = ref('timeline');
const showConvertModal = ref(false);
const showTransferModal = ref(false);

const transferForm = useForm({
    user_id: '',
    reason: ''
});

const executeTransfer = () => {
    transferForm.post(route('crm.leads.transfer', props.lead.id), {
        onSuccess: () => {
            showTransferModal.value = false;
            transferForm.reset();
        }
    });
};

const getStatusClass = (status) => {
    const classes = {
        new: 'bg-blue-50 text-blue-600 border-blue-100',
        contacted: 'bg-amber-50 text-amber-600 border-amber-100',
        qualified: 'bg-emerald-50 text-emerald-600 border-emerald-100',
        lost: 'bg-rose-50 text-rose-600 border-rose-100',
    };
    return classes[status] || 'bg-gray-50 text-gray-500 border-gray-100';
};

const voiceCall = () => {
    if (confirm(`Initiate Voice AI Call to ${props.lead.first_name}?`)) {
        router.post(route('crm.leads.voice-call', props.lead.id));
    }
};

const sendEmail = () => {
    // Navigate to email hub or open side panel
    alert('Email Compose side-panel coming up...');
};

</script>
