<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Sidebar from '../Components/Sidebar.vue';
import TimelineHub from '@/Components/CRM/TimelineHub.vue';

const props = defineProps({
    contact: { type: Object, required: true },
    users: { type: Array, default: () => [] }
});

const showTransferModal = ref(false);
const transferForm = useForm({
    user_id: '',
    reason: ''
});

const executeTransfer = () => {
    transferForm.post(route('crm.contacts.transfer', props.contact.id), {
        onSuccess: () => {
            showTransferModal.value = false;
            transferForm.reset();
        }
    });
};

const voiceCall = () => alert('Initiating VOIP session...');
const sendEmail = () => alert('Opening Email Composer...');
const scheduleMeeting = () => alert('Opening Calendar Scheduler...');
</script>

<template>
    <Head :title="`${contact.first_name} ${contact.last_name} | Contact Profile`" />

    <div class="min-h-screen bg-gray-50 flex overflow-hidden">
        <!-- Sidebar -->
        <Sidebar section="contacts" />

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Header -->
            <div class="bg-white border-b border-gray-100 px-8 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('crm.hub', { section: 'contacts' })" class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:text-indigo-600 transition-all border border-gray-100">
                        <i class="fas fa-arrow-left"></i>
                    </Link>
                    <div>
                        <h2 class="text-lg font-black text-gray-900 tracking-tight">Contact Profile</h2>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Contacts</span>
                            <i class="fas fa-chevron-right text-[8px] text-gray-300"></i>
                            <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">{{ contact.first_name }} {{ contact.last_name }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button @click="voiceCall" class="px-5 py-2.5 rounded-xl bg-emerald-50 text-emerald-600 font-black text-xs uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all flex items-center gap-2 border border-emerald-100">
                        <i class="fas fa-phone"></i> Call
                    </button>
                    <button @click="sendEmail" class="px-5 py-2.5 rounded-xl bg-blue-50 text-blue-600 font-black text-xs uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all flex items-center gap-2 border border-blue-100">
                        <i class="fas fa-envelope"></i> Email
                    </button>
                    <button @click="showTransferModal = true" class="px-5 py-2.5 rounded-xl bg-indigo-50 text-indigo-600 font-black text-xs uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all flex items-center gap-2 border border-indigo-100">
                        <i class="fas fa-exchange-alt"></i> Transfer
                    </button>
                    <button @click="scheduleMeeting" class="px-5 py-2.5 rounded-xl bg-purple-600 text-white font-black text-xs uppercase tracking-widest hover:bg-purple-700 transition-all flex items-center gap-2 shadow-lg shadow-purple-100">
                        <i class="fas fa-calendar-plus"></i> Meeting
                    </button>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-8">
                <div class="max-w-7xl mx-auto grid grid-cols-12 gap-8">
                    
                    <!-- Sidebar: Information -->
                    <div class="col-span-12 lg:col-span-4 space-y-8">
                        <!-- Identity Card -->
                        <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-8 text-center relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-emerald-500 to-teal-600"></div>
                            
                            <div class="w-24 h-24 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-[32px] mx-auto mb-6 flex items-center justify-center text-white text-3xl font-black shadow-xl shadow-emerald-100 border-4 border-white">
                                {{ contact.first_name[0] }}{{ contact.last_name[0] }}
                            </div>

                            <h1 class="text-2xl font-black text-gray-900 tracking-tight mb-1">{{ contact.first_name }} {{ contact.last_name }}</h1>
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">{{ contact.account?.name || 'Independent' }}</p>

                            <div class="flex items-center justify-center gap-2 py-4 border-t border-gray-50">
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-emerald-100">Verified Contact</span>
                            </div>
                        </div>

                        <!-- Info Grid -->
                        <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-8 space-y-6">
                            <div>
                                <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest mb-2">Primary Email</p>
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-envelope text-gray-400 text-xs"></i>
                                    <span class="text-sm font-bold text-gray-900">{{ contact.email }}</span>
                                </div>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest mb-2">Phone</p>
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-phone text-gray-400 text-xs"></i>
                                    <span class="text-sm font-bold text-gray-900">{{ contact.phone || 'N/A' }}</span>
                                </div>
                            </div>
                            <div v-if="contact.account">
                                <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest mb-2">Company</p>
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-building text-gray-400 text-xs"></i>
                                    <span class="text-sm font-bold text-indigo-600 underline decoration-indigo-200 underline-offset-4 cursor-pointer">
                                        {{ contact.account.name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main: Feed & Tabs -->
                    <div class="col-span-12 lg:col-span-8 space-y-8">
                        <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-8 min-h-[600px]">
                            <TimelineHub type="contact" :id="contact.id" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transfer Modal -->
        <div v-if="showTransferModal" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-[40px] shadow-2xl max-w-lg w-full overflow-hidden border border-white">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight">Handover Ownership</h3>
                    <button @click="showTransferModal = false" class="text-gray-400 hover:text-gray-900 transition-transform hover:rotate-90">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-8 space-y-6">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 text-left">New Account Manager</label>
                        <select v-model="transferForm.user_id" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
                            <option value="">Select colleague...</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 text-left">Internal Handover Notes</label>
                        <textarea v-model="transferForm.reason" placeholder="Provide context for the next account owner..." class="w-full bg-gray-50 border-none rounded-2xl p-4 text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 shadow-inner h-32"></textarea>
                    </div>
                    <button @click="executeTransfer" :disabled="transferForm.processing" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-exchange-alt"></i> {{ transferForm.processing ? 'TRANSFERRING...' : 'Execute Handover' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
