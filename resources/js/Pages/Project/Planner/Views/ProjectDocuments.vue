<template>
    <div class="h-full flex flex-col bg-gray-50">
        <!-- Toolbar -->
        <!-- Toolbar -->
        <div class="bg-white border-b border-gray-200 px-4 md:px-6 py-4 md:py-3 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 shadow-sm z-10">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
               <h2 class="text-lg font-black text-gray-900 tracking-tight">Documents</h2>
               <!-- Category Filter -->
                <div class="flex bg-gray-100 rounded-xl p-1 overflow-x-auto no-scrollbar snap-x border border-gray-200/50">
                    <button 
                        v-for="cat in ['All', 'MOM', 'DFD', 'Contract', 'Design', 'Other']" 
                        :key="cat"
                        @click="activeCategory = cat"
                        class="px-3 py-1.5 text-sm font-black uppercase tracking-widest rounded-lg transition-all whitespace-nowrap snap-center"
                        :class="activeCategory === cat ? 'bg-white shadow text-gray-900 border border-transparent' : 'text-gray-500 hover:text-gray-700'"
                    >
                        {{ cat }}
                    </button>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                 <div class="relative flex-1 md:flex-none">
                    <input 
                        v-model="searchQuery" 
                        type="text" 
                        placeholder="Search..." 
                        class="pl-9 pr-4 py-2 text-sm border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 w-full md:w-64 bg-gray-50/50"
                    >
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                 </div>
                 
                 <button 
                    @click="openUploadModal"
                    class="flex items-center justify-center gap-2 bg-indigo-600 text-white p-2 md:px-4 md:py-2 rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-600/20 font-black uppercase tracking-widest text-sm"
                 >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    <span class="hidden sm:inline">Upload</span>
                 </button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="flex-1 overflow-auto p-6" v-if="!loading">
            <div v-if="filteredDocs.length > 0" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                <!-- File Card -->
                <div 
                    v-for="doc in filteredDocs" 
                    :key="doc.id"
                    class="bg-white rounded-xl border border-gray-200 p-4 relative group hover:shadow-lg transition-all hover:border-indigo-200 cursor-pointer flex flex-col"
                >   
                    <!-- Actions -->
                    <div class="md:absolute top-2 right-2 md:opacity-0 group-hover:opacity-100 transition-opacity flex gap-1 z-10 mt-2 md:mt-0 justify-end">
                        <button 
                            @click.stop="downloadFile(doc)"
                            class="p-2 bg-indigo-50 md:bg-white rounded-lg border border-indigo-100 md:border-gray-200 text-indigo-600 md:text-gray-500 hover:text-indigo-600 hover:border-indigo-200 shadow-sm"
                            title="Download"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        </button>
                        <button 
                            @click.stop="deleteFile(doc)"
                            class="p-2 bg-red-50 md:bg-white rounded-lg border border-red-100 md:border-gray-200 text-red-600 md:text-gray-500 hover:text-red-600 hover:border-red-200 shadow-sm"
                            title="Delete"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>

                    <!-- Icon / Preview -->
                    <div class="h-32 bg-gray-50 rounded-lg flex items-center justify-center mb-3 group-hover:bg-indigo-50 transition-colors">
                        <svg class="w-12 h-12 text-gray-300 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <span class="absolute text-sm uppercase font-bold text-gray-400 mt-8">{{ doc.category }}</span>
                    </div>

                    <!-- Meta -->
                    <div class="flex-1 flex flex-col justify-between">
                         <div>
                            <h3 class="font-medium text-gray-900 text-sm truncate" :title="doc.name">{{ doc.name }}</h3>
                            <p class="text-xs text-gray-500 mt-1 flex items-center justify-between">
                                <span>{{ doc.size }}</span>
                                <span class="px-1.5 py-0.5 rounded text-sm uppercase font-bold" 
                                    :class="{
                                        'bg-emerald-100 text-emerald-700': doc.visibility === 'public',
                                        'bg-blue-100 text-blue-700': doc.visibility === 'team',
                                        'bg-gray-100 text-gray-600': doc.visibility === 'private'
                                    }">
                                    {{ doc.visibility }}
                                </span>
                            </p>
                         </div>
                         
                         <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                             <span class="text-sm text-gray-400">By {{ doc.uploader }}</span>
                             <span class="text-sm text-gray-400">{{ doc.created_at }}</span>
                         </div>
                    </div>
                </div>
            </div>

             <div v-else class="h-full flex flex-col items-center justify-center text-gray-400">
                <div class="p-4 bg-gray-100 rounded-full mb-4">
                     <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="font-medium text-gray-600">No documents found</h3>
                <p class="text-sm">Upload a new file to get started.</p>
            </div>
        </div>
         <div v-else class="flex-1 flex items-center justify-center">
             <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
         </div>

        <!-- Upload Modal -->
        <Modal :show="showUploadModal" @close="closeModal">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Upload Document</h3>
                
                <form @submit.prevent="submitUpload" class="space-y-4">
                    <!-- File Input -->
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 flex flex-col items-center justify-center cursor-pointer hover:bg-gray-50 transition-colors"
                         @click="$refs.fileInput.click()"
                         @dragover.prevent
                         @drop.prevent="handleDrop"
                    >
                        <input type="file" ref="fileInput" class="hidden" @change="handleFileSelect">
                        <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        <p class="text-sm font-medium text-gray-600" v-if="!form.file">Click to upload or drag and drop</p>
                        <p class="text-sm font-medium text-indigo-600" v-else>{{ form.file.name }}</p>
                        <p class="text-xs text-gray-400 mt-1">PDF, DOCX, PNG, JPG up to 10MB</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Category" />
                            <select v-model="form.category" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option v-for="cat in ['MOM', 'DFD', 'Contract', 'Design', 'Other']" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                        </div>
                         <div>
                            <InputLabel value="Visibility" />
                            <select v-model="form.visibility" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option value="team">Team Only</option>
                                <option value="public">Public (Org Wide)</option>
                                <option value="private">Private (Only Me)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Sharing Options -->
                    <div v-if="form.visibility !== 'public'" class="space-y-4 pt-2 border-t border-gray-100">
                        <!-- Teams -->
                        <div>
                            <InputLabel value="Share with Teams" class="mb-2" />
                            <div class="h-32 overflow-y-auto border border-gray-200 rounded-md p-2 space-y-1 bg-gray-50">
                                <label v-for="team in shareableTeams" :key="team.id" class="flex items-center space-x-2 p-1 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" :value="team.id" v-model="form.shared_with_teams" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm text-gray-700">{{ team.name }}</span>
                                </label>
                                <div v-if="shareableTeams.length === 0" class="text-xs text-gray-400 p-2">No teams found</div>
                            </div>
                        </div>

                        <!-- Users -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <InputLabel value="Share with People" />
                                <input v-model="userSearch" type="text" placeholder="Search..." class="text-xs border-gray-200 rounded py-1 px-2 w-32 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="h-40 overflow-y-auto border border-gray-200 rounded-md p-2 space-y-1 bg-gray-50">
                                <label v-for="user in filteredUsers" :key="user.id" class="flex items-center space-x-2 p-1 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" :value="user.id" v-model="form.shared_with" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm text-gray-700">{{ user.name }}</span>
                                </label>
                                <div v-if="filteredUsers.length === 0" class="text-xs text-gray-400 p-2">No users found</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Module Linkage -->
                    <!-- Placeholder: In real app, fetch modules list -->

                    <div class="flex justify-end gap-3 mt-6">
                        <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing || !form.file">
                            {{ form.processing ? 'Uploading...' : 'Upload' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    projects: { type: Array, default: () => [] },
    projectId: { type: [String, Number], default: null }
});

const docs = ref([]);
const loading = ref(false);
const activeCategory = ref('All');
const searchQuery = ref('');
const showUploadModal = ref(false);
const fileInput = ref(null);

// Shareable Resources
const shareableTeams = ref([]);
const shareableUsers = ref([]);
const userSearch = ref('');

const form = useForm({
    project_id: null,
    file: null,
    category: 'MOM',
    visibility: 'team',
    shared_with: [],       // User IDs
    shared_with_teams: []  // Team IDs
});

// Context
const currentProjectId = ref(null); 

const filteredDocs = computed(() => {
    let d = docs.value;
    if (activeCategory.value !== 'All') {
        d = d.filter(x => x.category === activeCategory.value);
    }
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        d = d.filter(x => x.name.toLowerCase().includes(q) || x.uploader.toLowerCase().includes(q));
    }
    return d;
});

const filteredUsers = computed(() => {
    if (!userSearch.value) return shareableUsers.value;
    const q = userSearch.value.toLowerCase();
    return shareableUsers.value.filter(u => u.name.toLowerCase().includes(q));
});

const fetchDocs = async () => {
    const pid = props.projectId || new URLSearchParams(window.location.search).get('project');
    if (!pid) return; 

    currentProjectId.value = pid;
    loading.value = true;
    try {
        const res = await axios.get(route('planner.documents.index'), { params: { project_id: pid } });
        docs.value = res.data.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const fetchShareables = async () => {
    if (shareableTeams.value.length > 0) return; // Already loaded
    try {
        const res = await axios.get(route('planner.shareables'));
        shareableTeams.value = res.data.teams;
        shareableUsers.value = res.data.users;
    } catch (e) {
        console.error("Failed to load shareables", e);
    }
};

const openUploadModal = () => {
    showUploadModal.value = true;
    fetchShareables();
};

const handleFileSelect = (e) => {
    form.file = e.target.files[0];
};

const handleDrop = (e) => {
    form.file = e.dataTransfer.files[0];
};

const submitUpload = () => {
    form.project_id = currentProjectId.value;
    form.post(route('planner.documents.store'), {
        onSuccess: () => {
            closeModal();
            fetchDocs();
        }
    });
};

const closeModal = () => {
    showUploadModal.value = false;
    form.reset();
    form.shared_with = [];
    form.shared_with_teams = [];
};

const deleteFile = async (doc) => {
    if (!confirm('Are you sure you want to delete this file?')) return;
    try {
        await axios.delete(route('planner.documents.delete', doc.id));
        fetchDocs(); 
    } catch (e) {
        alert('Failed to delete file');
    }
};

const downloadFile = (doc) => {
    window.open(doc.url, '_blank');
};

onMounted(() => {
    fetchDocs();
});
</script>
