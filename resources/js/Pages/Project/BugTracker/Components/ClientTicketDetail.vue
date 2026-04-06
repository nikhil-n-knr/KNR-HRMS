<template>
    <div class="space-y-8 animate-in fade-in duration-700">
        <!-- Ticket Header Card -->
        <div class="bg-white rounded-[3rem] p-12 shadow-2xl shadow-slate-200/50 border border-slate-100 relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex flex-col md:flex-row justify-between items-start gap-8 mb-12">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 bg-slate-100 rounded-full text-sm font-black uppercase tracking-widest text-slate-500">
                                Ticket #BT-{{ ticket.id.toString().padStart(4, '0') }}
                            </span>
                            <div :class="[
                                'h-2 w-2 rounded-full',
                                ticket.severity === 'critical' ? 'bg-rose-500' : 'bg-amber-500'
                            ]"></div>
                            <span class="text-sm font-black uppercase tracking-widest text-slate-400">
                                {{ ticket.project?.name }} / {{ ticket.module?.name }}
                            </span>
                        </div>
                        <h2 class="text-4xl font-black tracking-tight text-slate-900">{{ ticket.subject }}</h2>
                        <p class="text-slate-500 max-w-2xl font-medium leading-relaxed">{{ ticket.description }}</p>
                    </div>

                    <!-- Action Block (Verify Fix) -->
                    <transition name="scale-fade">
                        <div v-if="needsVerification" class="p-8 bg-emerald-50 rounded-[2.5rem] border border-emerald-100 flex flex-col items-center text-center gap-4">
                            <div class="h-14 w-14 bg-emerald-500 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/30">
                                <SparklesIcon class="w-8 h-8 text-white" />
                            </div>
                            <div>
                                <h4 class="font-black text-emerald-900 uppercase tracking-widest text-xs">Verify Resolution</h4>
                                <p class="text-sm text-emerald-600 font-bold mt-1">Our team has deployed a fix.</p>
                            </div>
                            <div class="flex gap-2">
                                <button @click="verifyFix(true)" class="px-6 py-3 bg-emerald-600 text-white rounded-xl text-sm font-black uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-500/20 active:scale-95">Fix Successful</button>
                                <button @click="verifyFix(false)" class="px-6 py-3 bg-white text-rose-600 border border-rose-100 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-rose-50 transition-all active:scale-95">Still Broken</button>
                            </div>
                        </div>
                    </transition>
                </div>

                <!-- Animated Pizza Tracker -->
                <TicketJourneyTimeline 
                    :transitions="timelineData.transitions" 
                    :all-stages="timelineData.all_stages"
                    :current-stage-id="ticket.workflow_stage_id"
                />

                <!-- Satisfaction Rating Gate -->
                <transition name="scale-fade">
                    <div v-if="ratedSuccessfully" class="mt-20">
                         <SatisfactionRating :ticket-id="ticket.id" @rated="onRated" />
                    </div>
                </transition>
            </div>
            
            <!-- Decor -->
            <div class="absolute top-0 right-0 p-8 opacity-5 pointer-events-none">
                <Squares2X2Icon class="w-64 h-64" />
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Media & Evidence -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-xl shadow-slate-200/30">
                    <h4 class="text-sm font-black uppercase tracking-[0.25em] text-slate-400 mb-6 flex items-center gap-2">
                        <PhotoIcon class="w-4 h-4" /> Evidence Hub
                    </h4>
                    <div v-if="ticket.media?.length" class="grid grid-cols-2 gap-3">
                        <div v-for="item in ticket.media" :key="item.id" class="group relative rounded-2xl overflow-hidden border border-slate-100 aspect-square bg-slate-50 cursor-zoom-in">
                            <img v-if="item.file_type === 'image'" :src="'/storage/' + item.file_path" class="w-full h-full object-cover transition-transform group-hover:scale-110" />
                            <div v-else class="w-full h-full flex flex-col items-center justify-center gap-2">
                                <VideoCameraIcon v-if="item.file_type === 'video'" class="w-8 h-8 text-rose-500" />
                                <DocumentIcon v-else class="w-8 h-8 text-amber-500" />
                                <span class="text-xs font-bold text-slate-400 px-2 truncate w-full text-center">{{ item.original_name }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-12 text-center text-slate-300">
                        <p class="text-sm font-black uppercase tracking-widest">No Media Attached</p>
                    </div>
                </div>

                <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white relative overflow-hidden">
                    <h4 class="text-sm font-black uppercase tracking-[0.25em] text-white/40 mb-6 flex items-center gap-2">
                        <CpuChipIcon class="w-4 h-4" /> Environment Context
                    </h4>
                    <div v-if="ticket.environment_metadata" class="space-y-4">
                        <div v-for="(val, key) in ticket.environment_metadata" :key="key" class="flex justify-between items-center border-b border-white/5 pb-3">
                            <span class="text-sm font-black uppercase tracking-widest text-white/40">{{ key }}</span>
                            <span class="text-sm font-bold text-emerald-400 truncate max-w-[150px]">{{ val }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Communication Channel -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/30 flex flex-col h-[700px]">
                    <div class="p-8 border-b border-slate-50">
                        <h4 class="text-sm font-black uppercase tracking-[0.25em] text-slate-400 flex items-center gap-2">
                            <ChatBubbleLeftRightIcon class="w-4 h-4" /> Feedback Room
                        </h4>
                    </div>

                    <!-- Message Feed -->
                    <div class="flex-1 overflow-y-auto p-8 space-y-6 bg-slate-50/30 custom-scrollbar">
                        <div v-for="comment in ticket.comments" :key="comment.id" 
                             :class="['flex gap-4 max-w-[85%]', comment.user_id === $page.props.auth.user.id ? 'ml-auto flex-row-reverse' : '']">
                            <div class="h-10 w-10 shrink-0 rounded-2xl bg-white border border-slate-100 flex items-center justify-center font-black text-xs text-emerald-600 shadow-sm">
                                {{ comment.author?.name?.charAt(0) }}
                            </div>
                            <div :class="[
                                'p-5 rounded-2xl space-y-1',
                                comment.user_id === $page.props.auth.user.id ? 'bg-emerald-600 text-white rounded-tr-none' : 'bg-white text-slate-700 shadow-sm rounded-tl-none border border-slate-100'
                            ]">
                                <p class="text-sm font-medium leading-relaxed" v-html="comment.body"></p>
                                <p :class="['text-xs font-black uppercase tracking-widest', comment.user_id === $page.props.auth.user.id ? 'text-emerald-200' : 'text-slate-300']">
                                    {{ formatTime(comment.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="p-8 bg-white rounded-b-[2.5rem] border-t border-slate-50">
                        <div class="flex gap-4 items-center bg-slate-50 rounded-2xl p-2 pr-4 border border-slate-100 focus-within:ring-4 focus-within:ring-emerald-500/5 focus-within:bg-white transition-all">
                            <input v-model="newComment" @keyup.enter="postComment" type="text" placeholder="Type a message to the engineering team..." class="flex-1 bg-transparent border-none focus:ring-0 text-sm font-medium px-4 py-3" />
                            <button @click="postComment" :disabled="!newComment.trim()" class="h-10 w-10 bg-emerald-600 text-white rounded-xl flex items-center justify-center hover:bg-emerald-700 transition-all shadow-md shadow-emerald-500/20 disabled:opacity-30">
                                <PaperAirplaneIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { 
    Squares2X2Icon,
    PhotoIcon,
    VideoCameraIcon,
    DocumentIcon,
    CpuChipIcon,
    ChatBubbleLeftRightIcon,
    PaperAirplaneIcon,
    SparklesIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import TicketJourneyTimeline from './TicketJourneyTimeline.vue';
import SatisfactionRating from './SatisfactionRating.vue';
import axios from 'axios';
import dayjs from 'dayjs';

const props = defineProps({
    ticket: Object,
    stages: Array
});

const ticket = ref(props.ticket);
const timelineData = ref({ transitions: [], all_stages: [] });
const newComment = ref('');
const ratedSuccessfully = ref(false);

const needsVerification = computed(() => {
    return ticket.value.stage?.requires_verification && !ticket.value.stage?.is_final && !ratedSuccessfully.value;
});

const loadTimeline = async () => {
    const { data } = await axios.get(route('portal.tickets.timeline', ticket.value.id));
    timelineData.value = data;
};

const postComment = async () => {
    if (!newComment.value.trim()) return;
    try {
        const { data } = await axios.post(route('bugs.comments.store', ticket.value.id), {
            body: newComment.value
        });
        ticket.value.comments.unshift(data);
        newComment.value = '';
    } catch (e) {
        console.error("Comment failed", e);
    }
};

const verifyFix = async (success) => {
    try {
        await axios.post(route('portal.tickets.verify', ticket.value.id), { action: success ? 'approve' : 'reject' });
        if (success) {
            ratedSuccessfully.value = true;
            // Reload timeline to show final stage
            loadTimeline();
        } else {
            // Reload to reflect stage change
            location.reload();
        }
    } catch (e) {
        console.error("Verification failed", e);
    }
};

const onRated = () => {
    ratedSuccessfully.value = false;
    // Potentially close the detail view or show a "Thank you" state.
    alert("Feedback received. Operational log closed.");
};

const formatTime = (time) => dayjs(time).format('h:mm A, MMM D');

onMounted(() => {
    loadTimeline();
});
</script>

<style scoped>
.scale-fade-enter-active, .scale-fade-leave-active { transition: all 0.4s ease; }
.scale-fade-enter-from, .scale-fade-leave-to { opacity: 0; transform: scale(0.9); }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
