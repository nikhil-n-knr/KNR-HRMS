<template>
    <AppLayout>
        <div class="h-[calc(100vh-160px)] flex flex-col">
            <!-- Header with Toggle -->
            <header class="p-4 space-y-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-slate-800">{{ activeTab === 'inbox' ? 'Conversations' : 'Sharing Room' }}</h2>
                    <button class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                <!-- Toggle Segment -->
                <div class="p-1.5 bg-slate-100 rounded-2xl flex relative h-12 shadow-inner border border-white">
                    <button 
                        @click="activeTab = 'inbox'"
                        class="flex-1 rounded-xl text-[10px] uppercase font-black transition-all z-10 tracking-[0.2em]"
                        :class="activeTab === 'inbox' ? 'text-emerald-700 bg-white shadow-sm' : 'text-slate-400'"
                    >
                        Inbox Matrix
                    </button>
                    <button 
                        @click="activeTab = 'team'"
                        class="flex-1 rounded-xl text-[10px] uppercase font-black transition-all z-10 tracking-[0.2em]"
                        :class="activeTab === 'team' ? 'text-emerald-700 bg-white shadow-sm' : 'text-slate-400'"
                    >
                        Sharing Room
                    </button>
                </div>
            </header>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto px-4 space-y-4 pb-24 custom-scrollbar">
                <div v-if="loading" class="text-center py-10 opacity-50">
                    <i class="fas fa-satellite-dish animate-bounce text-emerald-500 text-2xl"></i>
                </div>

                <!-- Inbox List -->
                <template v-if="activeTab === 'inbox'">
                    <div 
                        v-for="thread in threads" 
                        :key="thread.id" 
                        class="nature-card p-4 flex gap-4 active:scale-[0.98] transition-all bg-white"
                        @click="openThread(thread)"
                    >
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black shadow-inner border border-white">
                            {{ thread.account?.email_address?.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="text-xs font-black text-slate-900 truncate uppercase tracking-tight">{{ thread.subject || 'No Subject' }}</h4>
                                <span class="text-[9px] font-black text-slate-300 uppercase italic">{{ formatDate(thread.updated_at) }}</span>
                            </div>
                            <p class="text-[10px] text-slate-500 line-clamp-1 italic font-bold">
                                {{ thread.messages?.[0]?.body_text || 'Secure transmission pending...' }}
                            </p>
                        </div>
                    </div>
                    <div v-if="threads.length === 0 && !loading" class="text-center py-24 px-8 opacity-30">
                        <i class="fas fa-inbox text-4xl mb-4 block"></i>
                        <p class="text-[10px] font-black uppercase tracking-widest">No Active Conversations</p>
                    </div>
                </template>

                <!-- Sharing Room Feed -->
                <template v-else>
                    <div 
                        v-for="signal in signals" 
                        :key="signal.id" 
                        class="nature-card p-5 space-y-3 border-l-4 bg-white" 
                        :class="getSignalBorder(signal.type)"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg bg-slate-50 border border-white text-[10px] shadow-sm" :class="getSignalText(signal.type)">
                                    <i :class="signal.icon"></i>
                                </div>
                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 tracking-[0.2em]">{{ signal.type }} Signal</span>
                            </div>
                            <span class="text-[8px] font-black text-slate-300 tabular-nums">{{ signal.time }}</span>
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-slate-900 leading-snug uppercase tracking-tight italic">{{ signal.title }}</h3>
                            <p class="text-[10px] text-slate-500 mt-2 leading-relaxed font-bold">{{ signal.content }}</p>
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t border-slate-50">
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest italic opacity-60">Source: {{ signal.user }}</span>
                            <button class="text-[9px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-3 py-1.5 rounded-lg border border-white">Sync Link</button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Floating Action Button: Compose -->
            <div class="fixed bottom-32 right-6">
                <button 
                    @click="showCompose = true"
                    class="w-14 h-14 bg-slate-900 text-emerald-400 rounded-2xl shadow-2xl flex items-center justify-center active:scale-95 transition-all border-b-4 border-slate-950"
                >
                    <i class="fas fa-edit text-lg"></i>
                </button>
            </div>

            <!-- Compose Modal -->
            <transition name="slide-up">
                <div v-if="showCompose" class="fixed inset-0 z-[3000] flex flex-col justify-end">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-[2px]" @click="showCompose = false"></div>
                    <div class="nature-card !rounded-t-[3rem] !rounded-b-none p-8 max-h-[95vh] overflow-y-auto relative z-[3001] pb-12 shadow-[0_-20px_50px_rgba(0,0,0,0.1)]">
                        <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-8"></div>
                        
                        <div class="flex justify-between items-center mb-8">
                             <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight italic">Compose Signal</h2>
                             <button @click="showCompose = false" class="w-10 h-10 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 border border-white">
                                <i class="fas fa-times text-xs"></i>
                             </button>
                        </div>

                        <form @submit.prevent="sendMail" class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest px-1 italic">Source Axis</label>
                                <select v-model="composeForm.account_id" class="w-full bg-slate-50 border border-slate-100 rounded-2xl p-4 text-[10px] font-black uppercase tracking-widest outline-none focus:ring-2 focus:ring-emerald-500/10">
                                    <option v-for="acc in emailAccounts" :key="acc.id" :value="acc.id">{{ acc.email_address }}</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest px-1 italic">Target Recipient</label>
                                <input v-model="composeForm.to" type="email" placeholder="TARGET@EXTERNAL.COM" class="w-full bg-slate-50 border border-slate-100 rounded-2xl p-4 text-[10px] font-black uppercase tracking-widest outline-none focus:ring-2 focus:ring-emerald-500/10" />
                            </div>

                            <div class="space-y-2">
                                <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest px-1 italic">Protocol Subject</label>
                                <input v-model="composeForm.subject" type="text" placeholder="URGENT: INTEL SYNC" class="w-full bg-slate-50 border border-slate-100 rounded-2xl p-4 text-[10px] font-black uppercase tracking-widest outline-none focus:ring-2 focus:ring-emerald-500/10" />
                            </div>

                            <div class="space-y-2">
                                <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest px-1 italic">Handshake Content</label>
                                <textarea v-model="composeForm.body" rows="6" placeholder="Define the payload..." class="w-full bg-slate-50 border border-slate-100 rounded-2xl p-4 text-[11px] font-bold italic outline-none focus:ring-2 focus:ring-emerald-500/10"></textarea>
                            </div>

                            <button 
                                :disabled="sending"
                                class="w-full py-5 bg-slate-900 text-emerald-400 rounded-2xl font-black text-[10px] uppercase tracking-[0.3em] shadow-2xl active:scale-95 transition-all border-b-4 border-slate-950 disabled:opacity-50"
                            >
                                {{ sending ? 'Transmitting...' : 'Initiate Transmission' }}
                            </button>
                        </form>
                    </div>
                </div>
            </transition>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import AppLayout from '../App.vue';
import axios from 'axios';

const activeTab = ref('inbox');
const threads = ref([]);
const signals = ref([]);
const emailAccounts = ref([]);
const loading = ref(true);
const sending = ref(false);
const showCompose = ref(false);

const composeForm = ref({
    account_id: null,
    to: '',
    subject: '',
    body: ''
});

const fetchThreads = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/mobile/v1/emails');
        threads.value = response.data.data;
    } catch (err) {
        console.error("Failed to fetch threads:", err);
    } finally {
        loading.value = false;
    }
};

const fetchSignals = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/mobile/v1/emails/signals');
        signals.value = response.data;
    } catch (err) {
        console.error("Failed to fetch signals:", err);
    } finally {
        loading.value = false;
    }
};

const fetchAccounts = async () => {
    try {
        const response = await axios.get('/api/mobile/v1/email-accounts');
        emailAccounts.value = response.data;
        if (emailAccounts.value.length > 0) {
            composeForm.value.account_id = emailAccounts.value[0].id;
        }
    } catch (err) {
        console.error("Failed to fetch accounts:", err);
    }
};

const sendMail = async () => {
    sending.value = true;
    try {
        await axios.post('/api/mobile/v1/emails/compose', composeForm.value);
        showCompose.value = false;
        composeForm.value.to = '';
        composeForm.value.subject = '';
        composeForm.value.body = '';
        alert("Signal Transmitted Successfully.");
        fetchThreads();
    } catch (err) {
        alert("Transmission Failed: Check Parameters.");
    } finally {
        sending.value = false;
    }
};

const formatDate = (date) => new Date(date).toLocaleDateString([], { month: 'short', day: 'numeric' });

const getSignalBorder = (type) => {
    const borders = {
        'task': 'border-emerald-500',
        'communication': 'border-teal-500',
        'system': 'border-slate-800'
    };
    return borders[type] || 'border-emerald-500';
};

const getSignalText = (type) => {
    const texts = {
        'task': 'text-emerald-600',
        'communication': 'text-teal-600',
        'system': 'text-slate-800'
    };
    return texts[type] || 'text-emerald-600';
};

const openThread = (thread) => {
    // Logic for detail view or modal
    alert('Opening thread: ' + thread.subject);
};

onMounted(() => {
    fetchThreads();
    fetchSignals();
    fetchAccounts();
});

watch(activeTab, (newTab) => {
    if (newTab === 'inbox') fetchThreads();
    else fetchSignals();
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
