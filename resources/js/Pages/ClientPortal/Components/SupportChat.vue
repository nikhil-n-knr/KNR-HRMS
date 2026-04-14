<template>
    <div class="flex flex-col h-[700px] animate-in fade-in slide-in-from-right-10 duration-700 bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-900 text-white shrink-0">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 bg-emerald-500 rounded-2xl flex items-center justify-center text-slate-900 shadow-lg shadow-emerald-500/20">
                    <ChatBubbleLeftRightIcon class="h-6 w-6" />
                </div>
                <div>
                    <h2 class="text-sm font-black uppercase tracking-widest leading-none">Stakeholder Support Terminal</h2>
                    <p class="text-[9px] font-black text-emerald-400 uppercase tracking-widest mt-1.5 underline decoration-emerald-400/30 italic">Priority Verification Channel</p>
                </div>
            </div>
            <div class="flex flex-col items-end">
                <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest">SLA Window</span>
                <span class="text-[10px] font-black text-white italic">Active [2hr Target]</span>
            </div>
        </div>

        <!-- Middle: Discussions List & Chat View -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Sidebar: Active Tickets with chatter -->
            <div class="w-80 border-r border-slate-50 overflow-y-auto custom-scrollbar flex flex-col bg-slate-50/10">
                <div class="p-4 bg-slate-50/50 border-b border-slate-50">
                    <input type="text" placeholder="Filter transmissions..." class="w-full bg-white border-none rounded-xl text-[10px] font-black uppercase tracking-widest placeholder:text-slate-300 focus:ring-0">
                </div>
                <div v-for="ticket in bugsWithComments" :key="ticket.id" 
                     @click="selectedTicketId = ticket.id"
                     :class="['p-5 border-b border-slate-50 cursor-pointer transition-all group relative', selectedTicketId === ticket.id ? 'bg-white shadow-inner' : 'hover:bg-white']">
                    <div v-if="selectedTicketId === ticket.id" class="absolute left-0 inset-y-0 w-1 bg-emerald-500"></div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-[10px] font-black px-2 py-0.5 bg-slate-100 rounded text-slate-500 uppercase tracking-tighter italic">SIG-{{ ticket.id }}</span>
                        <span class="text-[8px] font-black text-slate-400 lowercase">{{ dayjs(ticket.updated_at).fromNow() }}</span>
                    </div>
                    <p class="text-[11px] font-black text-slate-900 truncate leading-tight group-hover:text-emerald-600 transition-colors">{{ ticket.subject }}</p>
                    <div class="flex items-center gap-2 mt-2">
                        <div class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">{{ ticket.comments?.length || 0 }} Signals</span>
                    </div>
                </div>
                <div v-if="bugsWithComments.length === 0" class="flex-1 flex flex-col items-center justify-center p-10 text-center opacity-30">
                    <InboxIcon class="h-10 w-10 mb-4" />
                    <p class="text-[10px] font-black uppercase tracking-widest italic">No Signal Chatter Detected</p>
                </div>
            </div>

            <!-- Chat Room -->
            <div class="flex-1 flex flex-col bg-slate-50/5 relative">
                <template v-if="selectedTicket">
                    <!-- Feed -->
                    <div class="flex-1 overflow-y-auto p-8 space-y-6 custom-scrollbar flex flex-col-reverse relative z-10">
                         <div v-for="comment in selectedTicket.comments" :key="comment.id" 
                             :class="['flex gap-4 max-w-[85%] animate-in fade-in slide-in-from-bottom-2 duration-300', comment.user_id === $page.props.auth.user.id ? 'ml-auto flex-row-reverse' : '']">
                            <div class="h-10 w-10 shrink-0 rounded-2xl bg-white border border-slate-100 flex items-center justify-center font-black text-[10px] text-emerald-600 shadow-sm shadow-slate-200/50">
                                {{ comment.author?.name?.charAt(0) }}
                            </div>
                            <div :class="[
                                'p-5 rounded-3xl space-y-2 relative shadow-lg',
                                comment.user_id === $page.props.auth.user.id ? 'bg-slate-900 text-white rounded-tr-none shadow-slate-900/10' : 'bg-white text-slate-700 shadow-slate-200/50 rounded-tl-none border border-slate-50'
                            ]">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[10px] font-black uppercase tracking-widest italic" :class="comment.user_id === $page.props.auth.user.id ? 'text-emerald-400' : 'text-slate-400'">{{ comment.author?.name }}</span>
                                </div>
                                <p class="text-xs font-bold leading-relaxed whitespace-pre-wrap">{{ comment.body }}</p>
                                <p :class="['text-[8px] font-black uppercase tracking-widest text-right mt-2 opacity-50 px-1']">
                                    {{ dayjs(comment.created_at).format('h:mm A') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="p-8 bg-white border-t border-slate-50 shrink-0 relative z-20">
                        <div class="flex flex-col gap-4 bg-slate-50 rounded-[1.5rem] p-4 border border-slate-100 focus-within:ring-8 focus-within:ring-emerald-500/5 focus-within:bg-white transition-all group">
                            
                            <!-- Attachment Preview -->
                            <div v-if="form.attachments.length" class="flex flex-wrap gap-2 px-2">
                                <div v-for="(file, idx) in form.attachments" :key="idx" class="flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-[9px] font-black uppercase tracking-widest text-slate-500">
                                    <DocumentIcon class="h-3 w-3" />
                                    <span>{{ file.name }}</span>
                                    <button @click="removeFile(idx)" class="text-rose-500 hover:text-rose-700">×</button>
                                </div>
                            </div>

                            <div class="flex gap-4 items-center">
                                <label class="h-12 w-12 bg-white rounded-2xl flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:shadow-lg transition-all cursor-pointer border border-slate-100">
                                    <PaperClipIcon class="h-5 w-5" />
                                    <input type="file" multiple @change="handleFileUpload" class="hidden">
                                </label>
                                <input v-model="form.body" @keyup.enter="postComment" type="text" placeholder="Broadcast a secure signal to engineering hub..." class="flex-1 bg-transparent border-none focus:ring-0 text-xs font-black px-2 py-4 placeholder:text-slate-300" />
                                <button @click="postComment" :disabled="!form.body.trim() || processing" class="h-12 w-12 bg-slate-900 text-white rounded-2xl flex items-center justify-center hover:bg-emerald-600 hover:scale-105 active:scale-95 transition-all shadow-xl shadow-slate-900/10 disabled:opacity-20">
                                    <PaperAirplaneIcon class="w-5 h-5 ml-0.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
                <div v-else class="flex-1 flex flex-col items-center justify-center p-20 text-center space-y-4 opacity-50 relative z-10">
                    <CloudIcon class="h-16 w-16 text-slate-200 animate-bounce duration-[3s]" />
                    <div>
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-400">Hub Logic Standby</h4>
                        <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest mt-1 italic">Select a signal stream from the tactical sidebar to initiate intel exchange.</p>
                    </div>
                </div>
                <!-- Decor -->
                <div class="absolute inset-0 pointer-events-none opacity-[0.03] grayscale">
                   <CpuChipIcon class="h-full w-full object-cover p-20" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { 
    ChatBubbleLeftRightIcon, 
    PaperAirplaneIcon, 
    InboxIcon,
    CloudIcon,
    CpuChipIcon,
    PaperClipIcon,
    DocumentIcon
} from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';

dayjs.extend(relativeTime);

const props = defineProps(['recent_bugs']);

const selectedTicketId = ref(null);
const processing = ref(false);

const form = useForm({
    body: '',
    attachments: [],
    is_public: true
});

const handleFileUpload = (e) => {
    form.attachments = [...form.attachments, ...Array.from(e.target.files)];
};

const removeFile = (idx) => {
    form.attachments.splice(idx, 1);
};

const bugsWithComments = computed(() => {
    return props.recent_bugs || [];
});

const selectedTicket = computed(() => {
    return bugsWithComments.value.find(b => b.id === selectedTicketId.value);
});

const postComment = async () => {
    if (!form.body.trim() || !selectedTicketId.value) return;
    
    processing.value = true;
    
    const formData = new FormData();
    formData.append('body', form.body);
    formData.append('is_public', '1');
    form.attachments.forEach((file, index) => {
        formData.append(`attachments[${index}]`, file);
    });

    try {
        const { data } = await axios.post(route('portal.tickets.comments.store', { bug: selectedTicketId.value }), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        // Find the ticket in our local array and update it
        const ticket = bugsWithComments.value.find(b => b.id === selectedTicketId.value);
        if (ticket) {
            if (!ticket.comments) ticket.comments = [];
            ticket.comments.unshift(data);
        }
        
        form.reset();
        form.attachments = [];
    } catch (e) {
        console.error("Transmission failed", e);
    } finally {
        processing.value = false;
    }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
