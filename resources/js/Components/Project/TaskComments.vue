<template>
    <div class="flex flex-col h-full bg-gray-50/50">
        <!-- Input Area -->
        <div class="p-4 bg-white border-b border-gray-100">
            <div class="relative group">
                <textarea 
                    v-model="form.body"
                    rows="3" 
                    placeholder="Write a comment..." 
                    class="w-full text-sm border-gray-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 p-3 pr-10 resize-none transition-shadow"
                    @keydown.ctrl.enter="submit"
                ></textarea>
                
                <!-- Helper text -->
                <div class="absolute bottom-2 right-2 text-[10px] text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">
                    Ctrl + Enter
                </div>
            </div>

            <!-- File Preview -->
            <div v-if="files.length" class="mt-3 flex flex-wrap gap-2">
                <div v-for="(file, i) in files" :key="i" class="flex items-center gap-2 px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-medium border border-indigo-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" /></svg>
                    <span class="truncate max-w-[150px]">{{ file.name }}</span>
                    <button @click="removeFile(i)" class="text-indigo-400 hover:text-indigo-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between mt-3">
                <div class="flex items-center gap-2">
                     <button @click="$refs.fileInput.click()" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-gray-100 rounded-full transition-colors" title="Attach Files">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                         </svg>
                     </button>
                     <input type="file" ref="fileInput" multiple class="hidden" @change="handleFileSelect">
                </div>
                <button 
                    @click="submit" 
                    :disabled="!form.body.trim() && !files.length"
                    class="px-4 py-1.5 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    Comment
                </button>
            </div>
        </div>

        <!-- Comments List -->
        <div class="flex-1 overflow-y-auto p-4 space-y-6">
            <div v-if="comments.length === 0" class="text-center py-10">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <p class="text-gray-500 text-sm">No comments yet. Be the first to start the discussion.</p>
            </div>

            <div v-for="comment in comments" :key="comment.id" class="flex gap-4 group">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-sm font-bold text-indigo-600">
                        {{ getInitials(comment.author?.name) }}
                    </div>
                </div>

                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-bold text-gray-900 text-sm">{{ comment.author?.name || 'Unknown' }}</span>
                        <span class="text-xs text-gray-400">{{ formatDate(comment.created_at) }}</span>
                    </div>
                    
                    <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap bg-white p-3 rounded-lg border border-gray-100 shadow-sm relative group-hover:bg-gray-50/50 transition-colors">
                        {{ comment.body }}
                        
                        <!-- Attachments Display -->
                        <div v-if="comment.attachments && comment.attachments.length" class="mt-3 pt-3 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-2">
                             <a 
                                v-for="(file, idx) in comment.attachments" 
                                :key="idx" 
                                :href="file.path" 
                                target="_blank"
                                class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 border border-gray-200 hover:bg-indigo-50 hover:border-indigo-200 transition-colors text-xs"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" /></svg>
                                <div class="flex-1 truncate">
                                    <div class="font-medium text-gray-700 truncate">{{ file.name }}</div>
                                    <div class="text-[10px] text-gray-400">{{ formatSize(file.size) }}</div>
                                </div>
                             </a>
                        </div>
                    </div>
                </div>

                <button 
                    v-if="comment.user_id === $page.props.auth.user.id"
                    @click="$emit('delete', comment.id)"
                    class="opacity-0 group-hover:opacity-100 text-gray-300 hover:text-red-500 transition-opacity self-start mt-1"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const props = defineProps({
    comments: Array
});

const emit = defineEmits(['add', 'delete']);

const form = useForm({
    body: ''
});

const files = ref([]);

const handleFileSelect = (e) => {
    files.value.push(...Array.from(e.target.files));
};

const removeFile = (index) => {
    files.value.splice(index, 1);
};

const submit = () => {
    if (!form.body.trim() && files.value.length === 0) return;
    
    // Create FormData
    const formData = new FormData();
    formData.append('body', form.body);
    files.value.forEach(file => {
        formData.append('files[]', file);
    });

    emit('add', formData);
    form.reset();
    files.value = [];
};

const formatDate = (date) => {
    return dayjs(date).fromNow();
};

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const formatSize = (bytes) => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};
</script>
