<template>
    <div class="h-full flex flex-col bg-white">
        <!-- System Auto-Closed Banner -->
        <div v-if="bug.system_closed_at" class="bg-amber-50 border-b border-amber-200 px-8 py-3 flex items-center gap-3">
            <ClockIcon class="w-5 h-5 text-amber-600" />
            <p class="text-xs font-bold text-amber-800 uppercase tracking-wider">
                This ticket was verified automatically by System Policy on {{ new Date(bug.system_closed_at).toLocaleDateString() }}
            </p>
        </div>

        <!-- Header / Navigation -->
        <div class="bg-white border-b border-gray-100 flex items-center justify-between px-8 py-4">
            <Link :href="route('client.dashboard')" class="flex items-center gap-2 text-emerald-600 hover:text-emerald-700 transition-colors group">
                <ArrowLeftIcon class="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
                <span class="text-xs font-black uppercase tracking-widest">Back to Dashboard</span>
            </Link>
            <div class="flex items-center gap-4">
                 <div v-if="bug.stage?.requires_verification && !bug.stage?.is_final" class="flex gap-2">
                    <button @click="showActionModal('reject')" class="px-3 py-1.5 border border-red-100 text-red-600 rounded-lg text-xs font-bold hover:bg-red-50 transition-all">
                        Reject Fix
                    </button>
                    <button @click="showActionModal('verify')" class="px-4 py-1.5 bg-green-500 text-white rounded-lg text-xs font-black shadow-lg shadow-green-100 hover:bg-green-600 transition-all">
                        Verify & Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Verification Hook Banner (Visible only for verification stages) -->
        <div v-if="bug.stage?.requires_verification && !bug.stage?.is_final" class="bg-emerald-600 text-white px-8 py-4 flex justify-between items-center shadow-lg">
            <div>
                <h3 class="font-bold text-lg">Action Required: Verification</h3>
                <p class="text-xs text-emerald-100 italic">Please test the fix and confirm if this issue is resolved.</p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-8">
            <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Info -->
                <div class="lg:col-span-2 space-y-8">
                    <div>
                         <div class="flex items-center gap-2 mb-2 text-xs font-bold text-gray-400">
                            <span>ISSUE #{{ bug.id }}</span>
                            <span>•</span>
                            <span class="uppercase">{{ bug.project.name }}</span>
                        </div>
                        <h1 class="text-3xl font-black text-gray-900 tracking-tight leading-tight">{{ bug.subject }}</h1>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2">Description</h3>
                        <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-wrap">{{ bug.description }}</p>
                    </div>

                    <div v-if="bug.steps_to_reproduce" class="space-y-4">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2">Steps to Reproduce</h3>
                        <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-wrap bg-gray-50 p-4 rounded-2xl">{{ bug.steps_to_reproduce }}</p>
                    </div>

                    <div v-if="bug.attachments?.length" class="space-y-4">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2">Attachments</h3>
                        <div class="grid grid-cols-3 gap-4">
                            <a v-for="file in bug.attachments" :key="file.id" :href="'/storage/' + file.path" target="_blank" class="block group">
                                <div class="aspect-video bg-gray-100 rounded-xl overflow-hidden border border-gray-200 relative">
                                    <img v-if="isImage(file.mime)" :src="'/storage/' + file.path" class="w-full h-full object-cover group-hover:scale-110 transition-transform" />
                                    <div v-else class="w-full h-full flex flex-col items-center justify-center p-4 text-center">
                                        <DocumentIcon class="w-8 h-8 text-gray-400 mb-2" />
                                        <span class="text-sm font-bold text-gray-500 truncate w-full">{{ file.name }}</span>
                                    </div>
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Communication History (High Control) -->
                    <div class="pt-8 space-y-6">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                             <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">Communication History</h3>
                             <span class="text-sm font-bold text-emerald-500 uppercase">Public Feed Only</span>
                        </div>

                        <!-- Add Comment -->
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 mb-8">
                            <textarea 
                                v-model="commentForm.body" 
                                rows="3" 
                                class="w-full border-gray-200 rounded-xl text-sm focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-sm"
                                placeholder="Write a reply to the development team..."
                            ></textarea>
                            <div class="mt-3 flex justify-end">
                                <button 
                                    @click="postComment"
                                    :disabled="commentForm.processing || !commentForm.body"
                                    class="bg-emerald-600 text-white px-4 py-1.5 rounded-lg text-xs font-black flex items-center gap-2 hover:bg-emerald-700 transition-all disabled:bg-gray-300 shadow-lg shadow-emerald-100"
                                >
                                    <PaperAirplaneIcon class="w-3 h-3" />
                                    Post Reply
                                </button>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div v-for="comment in bug.comments" :key="comment.id" class="flex gap-4">
                                <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-emerald-600 font-black border border-emerald-100 shrink-0">
                                    {{ (comment.author?.name || 'C').charAt(0) }}
                                </div>
                                <div class="flex-1 bg-white p-4 rounded-2xl rounded-tl-none border border-gray-100 shadow-sm relative">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-xs font-black text-gray-900">{{ comment.author?.name || 'Client User' }}</span>
                                        <span class="text-sm text-gray-400 font-bold">{{ new Date(comment.created_at).toLocaleString() }}</span>
                                    </div>
                                    <div class="text-sm text-gray-700 leading-relaxed" v-html="comment.body"></div>
                                </div>
                            </div>
                            <div v-if="!bug.comments?.length" class="text-center py-12 text-gray-400 italic text-sm">
                                No public comments yet. Write a reply above to start the conversation.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="space-y-6">
                    <div class="bg-gray-50 p-6 rounded-2xl space-y-4">
                        <div>
                            <label class="text-sm font-black text-gray-400 uppercase tracking-widest block mb-1">Current Status</label>
                            <span :class="[
                                'px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider',
                                bug.stage.is_final ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'
                            ]">
                                {{ bug.stage.name }}
                            </span>
                        </div>
                        <div>
                            <label class="text-sm font-black text-gray-400 uppercase tracking-widest block mb-1">Severity / Priority</label>
                            <div class="flex gap-2">
                                <span class="bg-red-50 text-red-700 px-2 py-0.5 rounded text-sm font-black uppercase">{{ bug.severity }}</span>
                                <span class="bg-orange-50 text-orange-700 px-2 py-0.5 rounded text-sm font-black uppercase">{{ bug.priority }}</span>
                            </div>
                        </div>
                        <div>
                             <label class="text-sm font-black text-gray-400 uppercase tracking-widest block mb-1">Last Update</label>
                             <div class="text-xs font-bold text-gray-700">{{ new Date(bug.updated_at).toLocaleString() }}</div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                        <h4 class="text-xs font-black text-gray-900 uppercase tracking-widest mb-4">Verification Policy</h4>
                        <p class="text-xs text-gray-500 leading-relaxed">
                            Bugs marked for review will be automatically closed and verified by our system after <span class="font-bold text-gray-900">{{ bug.stage.auto_close_days || 3 }} days</span> of inactivity if not manually verified.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="!!actionType" @close="actionType = null">
            <div class="p-6 text-center">
                 <div :class="['h-16 w-16 mx-auto mb-6 rounded-2xl flex items-center justify-center', actionType === 'verify' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600']">
                    <CheckCircleIcon v-if="actionType === 'verify'" class="w-10 h-10" />
                    <XCircleIcon v-else class="w-10 h-10" />
                </div>
                <h3 class="text-xl font-black text-gray-900 tracking-tight">{{ actionType === 'verify' ? 'Confirm Resolution' : 'Reject Resolution' }}</h3>
                <p class="text-sm text-gray-500 mt-2">Add a final note to complete this ticket.</p>
                
                <textarea 
                    v-model="actionNote" 
                    rows="4" 
                    class="w-full mt-6 rounded-2xl border-gray-200 text-sm focus:ring-emerald-500 focus:border-emerald-500 shadow-inner bg-gray-50/50"
                    placeholder="Your feedback..."
                ></textarea>

                <div class="mt-8 grid grid-cols-2 gap-4">
                    <button @click="actionType = null" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-3 rounded-2xl transition-all">Cancel</button>
                    <button @click="submitAction" :class="['text-white font-bold py-3 rounded-2xl transition-all shadow-lg', actionType === 'verify' ? 'bg-green-600 hover:bg-green-700 shadow-green-100' : 'bg-red-600 hover:bg-red-700 shadow-red-100']">
                        Submit Decision
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    ClockIcon, 
    DocumentIcon, 
    CheckCircleIcon, 
    XCircleIcon,
    ArrowLeftIcon,
    PaperAirplaneIcon
} from '@heroicons/vue/24/outline';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    bug: Object
});

const actionType = ref(null); // 'verify' or 'reject'
const actionNote = ref('');

const commentForm = useForm({
    body: ''
});

const postComment = () => {
    commentForm.post(route('client.bugs.comment', props.bug.id), {
        onSuccess: () => {
            commentForm.reset();
        }
    });
};

const showActionModal = (type) => {
    actionType.value = type;
    actionNote.value = '';
};

const submitAction = () => {
    router.post(route('client.bugs.verify', props.bug.id), {
        status: actionType.value,
        note: actionNote.value
    }, {
        onSuccess: () => {
            actionType.value = null;
            actionNote.value = '';
        }
    });
};

const isImage = (mime) => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(mime);
</script>
