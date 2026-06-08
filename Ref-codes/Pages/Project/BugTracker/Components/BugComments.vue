<template>
    <div class="space-y-6 flex flex-col h-[600px] border border-slate-100 bg-white rounded-3xl shadow-sm overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
            <h4 class="text-sm font-black uppercase tracking-[0.25em] text-slate-500 flex items-center gap-2">
                <ChatBubbleLeftRightIcon class="w-4 h-4" /> Feedback Room
            </h4>
        </div>

        <!-- Message Feed -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6 custom-scrollbar bg-slate-50/30 flex flex-col-reverse">
            <div v-for="comment in comments" :key="comment.id" 
                 :class="['flex gap-4 max-w-[85%]', comment.user_id === currentUserId ? 'ml-auto flex-row-reverse' : '']">
                
                <div class="h-10 w-10 shrink-0 rounded-2xl bg-white border border-slate-100 flex items-center justify-center font-black text-xs text-emerald-600 shadow-sm overflow-hidden">
                    <img v-if="comment.author?.avatar" :src="comment.author.avatar" class="w-full h-full object-cover" />
                    <span v-else>{{ comment.author?.name?.charAt(0) || '?' }}</span>
                </div>
                
                <div :class="[
                    'p-5 rounded-2xl space-y-2 relative',
                    comment.user_id === currentUserId 
                        ? 'bg-emerald-600 text-white rounded-tr-none shadow-md shadow-emerald-500/10' 
                        : 'bg-white text-slate-700 shadow-sm rounded-tl-none border border-slate-100'
                ]">
                    <!-- Internal/Public Badge -->
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm font-bold opacity-80">{{ comment.author?.name }}</span>
                        <span v-if="!comment.is_public" class="px-1.5 py-0.5 bg-rose-500/20 text-rose-100 text-xs rounded uppercase tracking-widest font-black" :class="comment.user_id !== currentUserId ? 'text-rose-600 bg-rose-50' : ''">
                            Internal
                        </span>
                        <span v-else class="px-1.5 py-0.5 bg-white/20 text-white text-xs rounded uppercase tracking-widest font-black" :class="comment.user_id !== currentUserId ? 'text-emerald-600 bg-emerald-50' : ''">
                            Public
                        </span>
                    </div>

                    <div
                        :class="[
                            'text-sm font-medium leading-relaxed break-words [&_a]:font-semibold [&_a]:underline [&_a]:underline-offset-2 [&_pre]:mt-2 [&_pre]:overflow-x-auto [&_pre]:rounded-lg [&_pre]:p-3 [&_pre]:text-xs [&_pre]:whitespace-pre-wrap [&_strong]:font-black',
                            comment.user_id === currentUserId
                                ? '[&_a]:text-white [&_pre]:bg-emerald-700/70 [&_pre]:text-emerald-50'
                                : '[&_a]:text-emerald-700 [&_pre]:bg-slate-50 [&_pre]:text-slate-700'
                        ]"
                        v-html="comment.body"
                    ></div>
                    
                    <!-- Attachments -->
                    <div v-if="comment.attachments && comment.attachments.length" class="mt-3 flex flex-wrap gap-2">
                        <a v-for="(file, idx) in comment.attachments" :key="idx" 
                           :href="'/storage/' + file.path" 
                           target="_blank"
                           :class="['inline-flex items-center px-2 py-1.5 rounded-lg text-sm font-black uppercase tracking-widest transition-colors', comment.user_id === currentUserId ? 'bg-emerald-700 hover:bg-emerald-800 text-emerald-100' : 'bg-slate-100 hover:bg-slate-200 text-slate-600']">
                            <DocumentIcon class="w-3 h-3 mr-1" />
                            {{ file.name }}
                        </a>
                    </div>
                    
                    <p :class="['text-xs font-black uppercase tracking-widest text-right mt-2 opacity-60']">
                        {{ new Date(comment.created_at).toLocaleString() }}
                    </p>
                </div>
            </div>
            
            <div v-if="comments.length === 0" class="text-center text-slate-400 py-12">
                <ChatBubbleLeftRightIcon class="w-12 h-12 mx-auto mb-4 opacity-20" />
                <p class="text-xs font-bold uppercase tracking-widest">No chatter yet.</p>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-6 bg-white border-t border-slate-100">
            <form @submit.prevent="submitComment">
                <div class="bg-slate-50 rounded-2xl p-3 flex flex-col gap-3 border border-slate-100 focus-within:ring-4 focus-within:ring-emerald-500/5 focus-within:bg-white transition-all">
                    
                    <textarea v-model="form.body" rows="2" 
                        @keydown.enter.ctrl.prevent="submitComment"
                        class="bg-transparent border-none focus:ring-0 text-sm font-medium px-2 py-1 w-full resize-none custom-scrollbar" 
                        placeholder="Type a message... (Ctrl+Enter to send)"></textarea>
                    
                    <!-- Toolbar -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-200/50">
                        <div class="flex items-center gap-4">
                            <!-- Attachments -->
                            <label class="cursor-pointer flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-black uppercase tracking-widest text-slate-500 hover:bg-slate-200/50 transition-colors">
                                <PaperClipIcon class="w-4 h-4" />
                                <span class="hidden sm:inline">Attach</span>
                                <input type="file" multiple @change="handleFileUpload" class="hidden">
                            </label>
                            <span v-if="form.attachments.length" class="text-sm font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded">{{ form.attachments.length }} files</span>

                            <!-- Visibility Toggle -->
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" v-model="form.is_public" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-3.5 h-3.5">
                                <span :class="['text-sm font-black tracking-widest uppercase transition-colors', form.is_public ? 'text-emerald-600' : 'text-slate-400']">
                                    {{ form.is_public ? 'Client Visible' : 'Internal Note' }}
                                </span>
                            </label>
                        </div>
                        
                        <button type="submit" 
                            :disabled="form.processing || !form.body.trim()"
                            class="h-10 w-10 shrink-0 bg-emerald-600 text-white rounded-xl flex items-center justify-center hover:bg-emerald-700 hover:scale-105 active:scale-95 transition-all shadow-md shadow-emerald-500/20 disabled:opacity-30 disabled:scale-100">
                            <PaperAirplaneIcon class="w-5 h-5 ml-0.5" />
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { 
    ChatBubbleLeftRightIcon, 
    PaperAirplaneIcon,
    PaperClipIcon,
    DocumentIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';

const props = defineProps(['bugId', 'initialComments', 'currentUserId']);

// We reverse them so that new comments appear at the bottom!
const comments = ref(props.initialComments ? [...props.initialComments] : []);

watch(() => props.initialComments, (newVals) => {
    comments.value = newVals ? [...newVals] : [];
}, { deep: true });

const form = useForm({
    body: '',
    is_public: true,
    attachments: [] 
});

const handleFileUpload = (e) => {
    form.attachments = Array.from(e.target.files);
};

const submitComment = async () => {
    if (!form.body.trim() && form.attachments.length === 0) return;

    const formData = new FormData();
    formData.append('body', form.body);
    formData.append('is_public', form.is_public ? '1' : '0');
    form.attachments.forEach((file) => {
        formData.append('attachments[]', file);
    });

    try {
        const response = await axios.post(route('bugs.comments.store', props.bugId), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        // The controller now returns the comment directly
        comments.value.unshift(response.data);
        form.reset();
        form.attachments = [];
    } catch (error) {
        console.error("Failed to post comment", error);
    }
};
</script>

<style scoped>
/* Scoped css for smooth scrollbar if needed */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
</style>
