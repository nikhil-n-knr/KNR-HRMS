<template>
    <div class="space-y-6">
        <!-- Dashboard Summary & Tab Badges (Embedded logic) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 p-6 rounded-3xl text-white shadow-xl relative overflow-hidden group">
                <!-- Abstract Vector Background -->
                <div class="absolute -right-12 -top-12 w-48 h-48 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
                <div class="absolute -left-12 -bottom-12 w-48 h-48 bg-emerald-400/20 rounded-full blur-2xl"></div>

                <div class="relative z-10">
                    <h3 class="text-xs font-black uppercase tracking-widest opacity-80 mb-2">Platform Compliance</h3>
                    <p class="text-2xl font-black mb-4 tracking-tight leading-none">Security & Signing Engine</p>
                    <div class="flex items-center gap-6">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-2xl bg-white/20 border border-white/30 flex items-center justify-center backdrop-blur-md">
                                <i class="fas fa-signature text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest opacity-80 leading-none mb-1">Awaiting Intel</p>
                                <h4 class="text-xl font-black">{{ pendingSignoffs.length }} Sign-offs</h4>
                            </div>
                        </div>
                        <div class="h-8 w-px bg-white/10"></div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest opacity-80 leading-none mb-1">Vault Health</p>
                            <h4 class="text-xl font-black text-emerald-300">100% Solid</h4>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-xl p-6 rounded-3xl border border-white/50 shadow-sm flex flex-col justify-center overflow-hidden group relative">
                 <div class="flex items-center justify-between mb-4 relative z-10">
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest">Active Governance Flow</h3>
                    <div class="flex items-center gap-1 text-emerald-600">
                        <i class="fas fa-shield-alt text-[10px] animate-pulse"></i>
                        <span class="text-[9px] font-black uppercase tracking-tighter">Verified Audit Trail</span>
                    </div>
                 </div>
                 <div class="flex items-center gap-2 relative z-10">
                    <div v-for="i in 8" :key="i" 
                        :class="i % 3 === 0 ? 'bg-emerald-500 w-8' : 'flex-1 bg-emerald-500/20 w-4'"
                        class="h-1.5 rounded-full transition-all duration-700 hover:w-12"></div>
                 </div>
                 <p class="text-[10px] font-bold text-slate-400 mt-4 uppercase tracking-widest leading-relaxed relative z-10">
                    All Requirement Artifacts & Digital Signatures are cryptographically bound to your organization for absolute non-repudiation.
                 </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
            <!-- Sidebar: Recent Activity & Filters -->
            <div class="lg:col-span-1 space-y-6 hidden lg:block sticky top-8">
                 <div class="bg-white/80 backdrop-blur-xl p-6 rounded-3xl border border-white/50 shadow-sm">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6 border-b pb-4 border-slate-100 flex items-center justify-between">
                         Recent History
                         <i class="fas fa-clock text-[8px]"></i>
                    </h3>
                    <div class="space-y-4">
                        <div v-for="i in 3" :key="i" class="flex gap-3 items-start group cursor-pointer">
                            <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center shrink-0 border border-slate-100 text-slate-400 group-hover:text-emerald-500 transition-colors">
                                <i class="fas fa-upload text-[10px]"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-[10px] font-black text-slate-800 leading-tight group-hover:text-emerald-600 transition-colors">BRD_V2.1 Uploaded</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter mt-1">{{ i }} hour ago • PM System</p>
                            </div>
                        </div>
                    </div>
                 </div>

                 <!-- Preview Overlay Trigger -->
                 <div v-if="previewDoc" class="bg-slate-900 p-6 rounded-3xl text-white shadow-2xl relative overflow-hidden group border border-white/10 animate-fade-in">
                    <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-emerald-500/30 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-[9px] font-black uppercase tracking-widest text-emerald-400">Contextual Insight</span>
                        <button @click="previewDoc = null" class="text-white/40 hover:text-white transition-colors">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <p class="text-xs font-black mb-1 line-clamp-1">{{ previewDoc.name }}</p>
                    <p class="text-[9px] text-white/50 font-bold uppercase tracking-tighter mb-4">{{ previewDoc.version_number }} • Intel Vault</p>
                    <button class="w-full py-2 bg-white text-slate-900 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-400 transition-all shadow-lg active:scale-95">
                        Deep Inspect
                    </button>
                 </div>
            </div>

            <!-- Repository Content -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Document List & Actions -->
                <div class="bg-white/80 backdrop-blur-xl p-6 rounded-3xl border border-white/50 shadow-sm relative overflow-hidden">
                    <!-- Subtle Mesh Gradient -->
                    <div class="absolute -right-24 -top-24 w-64 h-64 bg-slate-50 rounded-full blur-[120px] pointer-events-none"></div>

                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10 relative z-10">
                        <div class="flex flex-col">
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-tighter flex items-center gap-2">
                                Governance Document Vault
                                <div class="group/info relative">
                                    <i class="fas fa-info-circle text-slate-300 hover:text-emerald-500 cursor-help transition-colors text-[10px]"></i>
                                    <div class="absolute left-0 bottom-full mb-2 w-64 p-3 bg-slate-900 text-[10px] text-white font-medium rounded-xl opacity-0 translate-y-2 group-hover/info:opacity-100 group-hover/info:translate-y-0 transition-all z-50 pointer-events-none shadow-2xl">
                                        This vault stores all official project documents. Files here are used for tracking requirements and formal sign-offs.
                                        <div class="absolute left-4 top-full border-4 border-transparent border-t-slate-900"></div>
                                    </div>
                                </div>
                                <div v-if="pendingSignoffs.length" class="px-1.5 py-0.5 bg-rose-500 text-white text-[8px] rounded-md animate-bounce">
                                    {{ pendingSignoffs.length }} Sign-off Needed
                                </div>
                            </h3>
                            <span class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest">Official Project Records & Sign-offs</span>
                        </div>
                        <div class="flex items-center gap-2 w-full md:w-auto">
                            <div class="flex-1 md:w-64 bg-slate-50 border border-slate-100 rounded-xl px-3 flex items-center gap-2 group focus-within:border-emerald-200 transition-all">
                                <i class="fas fa-search text-[10px] text-slate-400 group-focus-within:text-emerald-500"></i>
                                <input type="text" placeholder="Search Documents..." class="bg-transparent border-none focus:ring-0 text-xs py-2 w-full font-bold placeholder:text-slate-300">
                            </div>
                            <button @click="showUploadModal = true" 
                                class="px-5 py-2.5 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-700 transition-all shadow-lg active:scale-95 flex items-center gap-2 group">
                                <i class="fas fa-plus tracking-tight group-hover:rotate-90 transition-transform duration-300"></i> Upload Document
                            </button>
                        </div>
                    </div>

                    <BaseDataTable :rows="currentDocuments" :columns="columns" selectable>
                        <template #cell-name="{ row }">
                            <div class="flex items-center gap-4 group/item cursor-pointer" @click="previewDoc = row">
                                <div class="h-10 w-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover/item:text-emerald-500 group-hover/item:bg-white border border-slate-100 group-hover/item:border-emerald-100 transition-all">
                                    <i :class="getFileIcon(row.mime_type)" class="text-lg"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-black text-slate-800 tracking-tight group-hover/item:text-emerald-600 transition-colors">{{ row.name }}</span>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ row.version_number ? 'V' + row.version_number : 'V1' }}</span>
                                        <span class="w-1 h-1 bg-slate-200 rounded-full"></span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ row.mime_type }}</span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template #cell-category="{ row }">
                            <div class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest inline-flex"
                                :class="getCategoryClass(row.category)">
                                {{ row.category }}
                            </div>
                        </template>

                        <template #cell-uploader="{ row }">
                            <div class="flex items-center gap-2">
                                <div class="h-7 w-7 rounded-xl bg-gradient-to-br from-slate-50 to-slate-100 border border-slate-200 flex items-center justify-center text-[10px] font-black text-slate-600">
                                     {{ row.uploader?.name?.[0] }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black text-slate-800 leading-none">{{ row.uploader?.name }}</span>
                                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter mt-1">Stakeholder</span>
                                </div>
                            </div>
                        </template>

                        <template #cell-actions="{ row }">
                            <div class="flex items-center gap-2 justify-end">
                                 <!-- Version History Dropdown Placeholder -->
                                 <button v-if="row.history?.length" class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 hover:text-emerald-600 hover:bg-white transition-all flex items-center justify-center border border-slate-100 shadow-sm" title="Version History">
                                    <i class="fas fa-history text-[10px]"></i>
                                 </button>

                                 <button @click="handleDownload(row)" class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 hover:text-emerald-600 hover:bg-white transition-all flex items-center justify-center border border-slate-100 shadow-sm active:scale-90" title="Secure Fetch">
                                    <i class="fas fa-fingerprint text-[10px]"></i>
                                 </button>
                                 
                                 <button v-if="row.category === 'requirement' && !row.is_signed" 
                                    @click="handleSignOff(row)"
                                    class="px-4 py-2 bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-500 hover:text-white transition-all border border-emerald-100 shadow-sm">
                                     Final Sign-off
                                 </button>
                                 <div v-else-if="row.is_signed" class="flex items-center gap-2 px-4 py-2 bg-white rounded-xl border border-emerald-100 shadow-sm group/signed">
                                     <i class="fas fa-check-circle text-emerald-500 text-[10px] group-hover/signed:scale-125 transition-transform duration-500"></i>
                                     <div class="flex flex-col">
                                        <span class="text-[8px] font-black text-emerald-600 uppercase tracking-tighter">Accepted & bound</span>
                                        <span class="text-[7px] text-slate-400 block -mt-1 uppercase tracking-tighter">{{ formatDate(row.signed_at) }}</span>
                                     </div>
                                 </div>
                            </div>
                        </template>
                    </BaseDataTable>
                </div>

                <!-- PDF Intelligence Preview (Conditional Overlay) -->
                <div v-if="previewDoc && previewDoc.mime_type.includes('pdf')" class="bg-white/80 backdrop-blur-xl p-1 rounded-[40px] border border-white/50 shadow-2xl overflow-hidden animate-slide-up h-[600px] flex flex-col group/preview">
                    <div class="px-8 py-4 flex justify-between items-center bg-white rounded-t-[40px] border-b border-slate-50 shrink-0">
                         <div class="flex items-center gap-4">
                            <div class="h-10 w-10 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center shadow-sm">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="flex flex-col">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest">{{ previewDoc.name }}</h4>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">Secure Preview Mode • End-to-End Encrypted Access</p>
                            </div>
                         </div>
                         <button @click="previewDoc = null" class="w-10 h-10 rounded-full bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all flex items-center justify-center border border-slate-100 active:scale-90">
                            <i class="fas fa-times"></i>
                         </button>
                    </div>
                    <!-- Frame Container -->
                    <div class="flex-1 bg-slate-50 relative">
                        <!-- Loading State -->
                        <div class="absolute inset-0 flex items-center justify-center flex-col gap-4 bg-white/60 backdrop-blur-sm pointer-events-none opacity-0 group-hover/preview:opacity-100 md:opacity-0 transition-opacity">
                             <div class="w-12 h-12 border-4 border-emerald-500/20 border-t-emerald-500 rounded-full animate-spin"></div>
                             <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Initialising Document Context...</p>
                        </div>
                        <iframe :src="'/storage/project-documents/preview.pdf'" class="w-full h-full border-none pointer-events-auto"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <!-- Global Broadcast Overlay (Mobile Friendly toast logic) -->
        <transition name="toast">
            <div v-if="activeToast" class="fixed bottom-12 right-12 z-50 animate-bounce">
                <!-- Toast Component Placeholder -->
            </div>
        </transition>

        <!-- Submit Intel Modal -->
        <Modal :show="showUploadModal" @close="showUploadModal = false">
            <div class="p-8 space-y-8">
                <div>
                    <h3 class="text-xl font-black text-slate-900 uppercase tracking-tighter">Upload Project Document</h3>
                    <p class="text-sm font-medium text-slate-400">Add documents for project review and formal approval. These files are saved in the permanent vault for records.</p>
                </div>

                <form @submit.prevent="submitIntel" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <InputLabel value="Associate Project" />
                            <BaseSelect v-model="form.project_id" :options="projectOptions" placeholder="Select Project" />
                            <InputError :message="form.errors.project_id" />
                        </div>
                        <div class="space-y-2">
                            <InputLabel value="Artifact Classification" />
                            <BaseSelect v-model="form.category" :options="[
                                { id: 'requirement', label: 'Requirement (BRD/SRS)' },
                                { id: 'sign_off', label: 'Project Sign-off' },
                                { id: 'financial', label: 'Financial / Invoice' },
                                { id: 'other', label: 'Other Correspondence' }
                            ]" placeholder="Select Category" />
                            <InputError :message="form.errors.category" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <InputLabel value="Document Name" />
                        <TextInput v-model="form.name" placeholder="e.g. Project Plan V1" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="space-y-2">
                        <InputLabel value="Detailed Explanation" />
                        <textarea v-model="form.description" rows="3" 
                            class="w-full bg-slate-50 border-slate-200 rounded-2xl text-[10px] font-black uppercase tracking-widest placeholder:text-slate-300 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                            placeholder="Provide a long description or context for this document..."></textarea>
                        <InputError :message="form.errors.description" />
                    </div>

                    <div class="space-y-2">
                        <InputLabel value="Secure Upload" />
                        <div class="border-2 border-dashed border-slate-200 rounded-2xl p-8 flex flex-col items-center justify-center gap-4 hover:border-emerald-400 transition-all bg-slate-50/50 group cursor-pointer relative"
                             @click="$refs.fileInput.click()">
                            <input type="file" ref="fileInput" class="hidden" @change="handleFileSelect">
                            <div class="h-12 w-12 rounded-full bg-white shadow-sm flex items-center justify-center text-slate-400 group-hover:text-emerald-500 transition-all">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="text-center">
                                <p class="text-xs font-black text-slate-900 uppercase tracking-widest">{{ selectedFile ? selectedFile.name : 'Click to select artifact' }}</p>
                                <p class="text-[10px] font-bold text-slate-400 mt-1">PDF, DOCX, XLSX (Max 20MB)</p>
                            </div>
                        </div>
                        <InputError :message="form.errors.file" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showUploadModal = false" class="px-6 py-3 bg-slate-100 text-slate-500 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Cancel</button>
                        <button type="submit" 
                            :disabled="form.processing"
                            class="px-8 py-3 bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-lg disabled:opacity-50">
                            {{ form.processing ? 'Initialising Secure Stream...' : 'Broadcast to Vault' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, computed, inject } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import BaseSelect from '@/Components/BaseSelect.vue';

const props = defineProps(['documents', 'projects']);

const showUploadModal = ref(false);
const previewDoc = ref(null);
const activeToast = ref(null);

const columns = [
    { key: 'name', label: 'Document Name', sortable: true },
    { key: 'category', label: 'Category' },
    { key: 'uploader', label: 'Uploaded By' },
    { key: 'actions', label: 'Actions', class: 'text-right' }
];

const currentDocuments = computed(() => {
    // Only show current versions in the main table
    return props.documents.filter(d => d.is_current !== false);
});

const pendingSignoffs = computed(() => {
    return props.documents.filter(d => d.category === 'requirement' && !d.is_signed);
});

const form = useForm({
    project_id: '',
    name: '',
    description: '',
    category: 'requirement',
    file: null
});

const selectedFile = ref(null);
const fileInput = ref(null);

const projectOptions = computed(() => {
    return props.projects?.map(p => ({ id: p.id, label: p.name })) || [];
});

const handleFileSelect = (e) => {
    const file = e.target.files[0];
    if (file) {
        selectedFile.value = file;
        form.file = file;
        if (!form.name) form.name = file.name.split('.').slice(0, -1).join('.');
    }
};

const submitIntel = () => {
    form.post(route('projects.portal.document.upload', { project: form.project_id }), {
        onSuccess: () => {
            showUploadModal.value = false;
            form.reset();
            selectedFile.value = null;
        }
    });
};

const handleSignOff = (doc) => {
    if (confirm(`Do you officially sign-off and accept the requirements in "${doc.name}"? This action creates an immutable audit artifact.`)) {
        router.post(route('projects.portal.document.sign-off', { document: doc.id }), {}, {
            preserveScroll: true
        });
    }
};

const handleDownload = (doc) => {
    // Secure fetch logic
    window.open(doc.file_url, '_blank');
};

const getFileIcon = (mime) => {
    if (mime?.includes('pdf')) return 'fas fa-file-pdf';
    if (mime?.includes('image')) return 'fas fa-file-image';
    if (mime?.includes('word') || mime?.includes('text')) return 'fas fa-file-alt';
    if (mime?.includes('sheet') || mime?.includes('excel')) return 'fas fa-file-excel';
    return 'fas fa-file';
};

const getCategoryClass = (cat) => {
    switch (cat) {
        case 'requirement': return 'bg-cyan-50 text-cyan-600 border border-cyan-100';
        case 'sign_off': return 'bg-emerald-50 text-emerald-600 border border-emerald-100 shadow-sm shadow-emerald-100/50';
        case 'financial': return 'bg-rose-50 text-rose-600 border border-rose-100';
        default: return 'bg-slate-50 text-slate-600 border border-slate-100';
    }
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.5s ease-out; }
.animate-slide-up { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1); }

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
</style>
