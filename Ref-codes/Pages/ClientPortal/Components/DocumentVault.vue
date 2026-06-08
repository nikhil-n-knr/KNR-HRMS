<template>
    <div class="space-y-10 animate-in fade-in slide-in-from-bottom-8 duration-1000">
        <!-- Filter Bar -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm relative overflow-hidden">
             <!-- Mesh Gradient Subtle -->
             <div class="absolute -right-16 -top-16 w-32 h-32 bg-emerald-50 rounded-full blur-3xl opacity-50"></div>

            <div class="flex items-center gap-4 relative z-10">
                <div class="h-10 w-10 bg-slate-900 rounded-xl flex items-center justify-center text-white">
                    <ShieldCheckIcon class="h-5 w-5" />
                </div>
                <div>
                     <h2 class="text-sm font-black uppercase tracking-widest text-slate-900 flex items-center gap-2">
                        Governance Workspace
                        <div class="group/info relative">
                            <i class="fas fa-info-circle text-slate-300 hover:text-emerald-500 cursor-help transition-colors text-[10px]"></i>
                            <div class="absolute left-0 bottom-full mb-2 w-64 p-3 bg-slate-900 text-[10px] text-white font-medium rounded-xl opacity-0 translate-y-2 group-hover/info:opacity-100 group-hover/info:translate-y-0 transition-all z-50 pointer-events-none shadow-2xl">
                                This is your secure vault for all project documents. You can upload requirements, view records, and sign off on project plans here.
                                <div class="absolute left-4 top-full border-4 border-transparent border-t-slate-900"></div>
                            </div>
                        </div>
                    </h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5 italic">Permanent Project Records & Approvals</p>
                </div>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto relative z-10">
                <select v-model="filterCategory" class="bg-slate-50 border-none text-[10px] font-black uppercase tracking-widest rounded-xl focus:ring-0 cursor-pointer hover:bg-slate-100 transition-colors">
                    <option value="all">All Documents</option>
                    <option value="requirement">Requirements</option>
                    <option value="sign_off">Approvals</option>
                    <option value="financial">Financial Files</option>
                </select>
                <button @click="showUploadModal = true" class="px-6 py-2.5 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-700 transition-all shadow-lg active:scale-95 flex items-center gap-2">
                    <PlusIcon class="h-3 w-3" /> Upload Document
                </button>
            </div>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <!-- Main Document Feed -->
            <div class="lg:col-span-8 space-y-6">
                <div v-if="filteredDocs.length === 0" class="bg-white rounded-[3rem] p-20 text-center border-2 border-dashed border-slate-100 opacity-40 grayscale flex flex-col items-center">
                    <InboxStackIcon class="h-16 w-16 text-slate-300 mb-6" />
                    <p class="text-xs font-black uppercase tracking-widest text-slate-400 italic">No project files found in this section</p>
                </div>

                <div v-for="doc in filteredDocs" :key="doc.id" 
                     class="bg-white rounded-[3.5rem] p-8 border border-slate-100 hover:border-emerald-200 transition-all hover:shadow-2xl hover:shadow-emerald-500/5 group relative overflow-hidden flex flex-col md:flex-row gap-8 items-center">
                    <div class="h-20 w-20 bg-slate-50 rounded-[2rem] flex items-center justify-center text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-all duration-500 shadow-inner shrink-0">
                        <component :is="getDocIcon(doc.category)" class="h-10 w-10 p-1" />
                    </div>
                    
                    <div class="flex-1 space-y-2 text-center md:text-left">
                        <div class="flex items-center justify-center md:justify-start gap-3">
                            <h3 class="text-xl font-black text-slate-900 tracking-tighter italic">{{ doc.name }}</h3>
                            <span v-if="doc.is_signed" class="px-2 py-0.5 bg-emerald-500 text-white text-[8px] font-black uppercase tracking-widest rounded shadow-lg shadow-emerald-500/20">Signed-off</span>
                        </div>
                        <p v-if="doc.description" class="text-xs font-bold text-slate-500 line-clamp-2 leading-relaxed">{{ doc.description }}</p>
                        <div class="flex items-center justify-center md:justify-start gap-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                            <span class="flex items-center gap-1"><FolderIcon class="h-3 w-3" /> {{ doc.category }}</span>
                            <span class="h-1 w-1 bg-slate-200 rounded-full"></span>
                            <span>{{ (doc.file_size / 1024 / 1024).toFixed(2) }} MB</span>
                            <span class="h-1 w-1 bg-slate-200 rounded-full"></span>
                            <span>Uploaded: {{ dayjs(doc.created_at).format('MMM D, YYYY') }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <a :href="doc.file_url" target="_blank" class="h-12 w-12 bg-slate-50 hover:bg-slate-900 hover:text-white rounded-2xl flex items-center justify-center transition-all border border-slate-100 group shadow-sm">
                            <ArrowDownTrayIcon class="h-5 w-5 group-hover:-translate-y-1 transition-transform" />
                        </a>
                        <button v-if="doc.category === 'requirement' && !doc.is_signed" 
                                @click="initiateSignOff(doc)"
                                class="px-8 py-3 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:scale-105 active:scale-95 transition-all shadow-xl shadow-emerald-500/20">
                            Perform Sign-off
                        </button>
                    </div>

                    <!-- Decorative Hover BG -->
                     <div class="absolute -right-10 -bottom-10 h-32 w-32 bg-emerald-50/50 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-1000"></div>
                </div>
            </div>

            <!-- Stats/Context (Right) -->
            <div class="lg:col-span-4 space-y-8">
                <div class="bg-slate-900 rounded-[4rem] p-10 text-white space-y-8 relative overflow-hidden group shadow-2xl shadow-slate-900/30">
                    <div class="relative z-10 space-y-8">
                        <div>
                             <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-6 italic">Project Compliance Score</h3>
                             <div class="flex items-end gap-3 mb-2">
                                <span class="text-5xl font-black tracking-tighter">{{ complianceRate }}<span class="text-2xl opacity-30">%</span></span>
                                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-400 mb-1 underline decoration-emerald-400/30 italic">Target Achieved</span>
                             </div>
                             <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest leading-relaxed">This percentage shows how many required documents have been officially accepted.</p>
                        </div>

                        <div class="space-y-4">
                             <div v-for="stat in categoryStats" :key="stat.label" class="space-y-2">
                                <div class="flex justify-between items-center text-[9px] font-black uppercase tracking-widest">
                                    <span class="text-slate-400">{{ stat.label }}</span>
                                    <span>{{ stat.count }} Documents</span>
                                </div>
                                <div class="h-1.5 bg-white/5 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-500 transition-all duration-1000" :style="{ width: (stat.count / (documents.length || 1) * 100) + '%' }"></div>
                                </div>
                             </div>
                        </div>
                    </div>
                    <!-- Abstract Wave -->
                    <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:opacity-20 transition-opacity duration-1000">
                        <CpuChipIcon class="h-32 w-32" />
                    </div>
                </div>

                <div class="bg-white rounded-[3.5rem] p-10 border border-slate-100 space-y-6 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500/20"></div>
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400 italic">Governance Notice</h3>
                    <div class="p-6 bg-slate-50 rounded-[2rem] border border-slate-100">
                         <p class="text-[11px] font-bold text-slate-500 leading-relaxed italic">All project records are securely stored. Digital sign-offs are permanent and serve as official approval for the project items.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <Modal :show="showUploadModal" @close="showUploadModal = false">
            <div class="p-8 space-y-8">
                <div>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tighter italic uppercase">Upload Requirements</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-2">Send official project files to the engineering hub</p>
                </div>

                <form @submit.prevent="submitUpload" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <InputLabel value="Select Project" />
                            <BaseSelect v-model="form.project_id" :options="projectOptions" placeholder="Target Project" />
                            <InputError :message="form.errors.project_id" />
                        </div>
                        <div class="space-y-2">
                            <InputLabel value="File Category" />
                            <BaseSelect v-model="form.category" :options="[
                                { id: 'requirement', label: 'Requirement (BRD/SRS)' },
                                { id: 'sign_off', label: 'Official Sign-off' },
                                { id: 'financial', label: 'Financial Document' },
                                { id: 'general', label: 'General Correspondence' }
                            ]" placeholder="Classification" />
                            <InputError :message="form.errors.category" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <InputLabel value="Document Title" />
                        <TextInput v-model="form.name" placeholder="e.g. Revised Requirements V2" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="space-y-2">
                        <InputLabel value="Detailed Description" />
                        <textarea v-model="form.description" rows="4" 
                            class="w-full bg-slate-50 border-slate-200 rounded-3xl text-[10px] font-black uppercase tracking-widest placeholder:text-slate-300 focus:ring-emerald-500 focus:border-emerald-500 transition-all resize-none"
                            placeholder="Provide detailed context or instructions for this document..."></textarea>
                        <InputError :message="form.errors.description" />
                    </div>

                    <div class="space-y-2">
                         <InputLabel value="Attachment" />
                         <div class="border-2 border-dashed border-slate-200 rounded-[2rem] p-10 flex flex-col items-center justify-center gap-4 hover:border-emerald-400 transition-all bg-slate-50/50 group cursor-pointer relative overflow-hidden"
                              @click="$refs.fileInput.click()">
                            <input type="file" ref="fileInput" class="hidden" @change="handleFileSelect">
                            <div class="h-16 w-16 rounded-3xl bg-white shadow-xl shadow-slate-200/50 flex items-center justify-center text-slate-400 group-hover:text-emerald-500 transition-all duration-500">
                                <PlusIcon class="h-8 w-8" />
                            </div>
                            <div class="text-center relative z-10">
                                <p class="text-xs font-black text-slate-900 uppercase tracking-widest italic">{{ selectedFile ? selectedFile.name : 'Select file to upload' }}</p>
                                <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-tighter">PDF, DOCX, XLSX (Max 20MB)</p>
                            </div>
                         </div>
                         <InputError :message="form.errors.file" />
                    </div>

                    <div class="flex justify-end gap-4 pt-6">
                        <button type="button" @click="showUploadModal = false" class="px-8 py-3 bg-slate-100 text-slate-500 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Cancel</button>
                        <button type="submit" 
                            :disabled="form.processing"
                            class="px-10 py-3 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-xl shadow-slate-900/20 disabled:opacity-50">
                            {{ form.processing ? 'Transmitting...' : 'Upload to Hub' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { 
    ShieldCheckIcon, 
    ArrowDownTrayIcon, 
    DocumentTextIcon, 
    FolderIcon,
    InboxStackIcon,
    ScaleIcon,
    CurrencyDollarIcon,
    CpuChipIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';
import { router, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import BaseSelect from '@/Components/BaseSelect.vue';

const props = defineProps(['documents', 'projects']);
const filterCategory = ref('all');
const showUploadModal = ref(false);
const selectedFile = ref(null);

const form = useForm({
    project_id: '',
    name: '',
    description: '',
    category: 'requirement',
    file: null,
    visibility: 'client_shared'
});

const projectOptions = computed(() => {
    return props.projects?.map(p => ({ id: p.id, label: p.name })) || [];
});

const filteredDocs = computed(() => {
    if (filterCategory.value === 'all') return props.documents || [];
    return props.documents.filter(d => d.category === filterCategory.value);
});

const complianceRate = computed(() => {
    const total = props.documents?.filter(d => d.category === 'requirement').length || 0;
    if (total === 0) return 100;
    const signed = props.documents?.filter(d => d.category === 'requirement' && d.is_signed).length || 0;
    return Math.round((signed / total) * 100);
});

const categoryStats = computed(() => [
    { label: 'Core Requirements', count: props.documents?.filter(d => d.category === 'requirement').length || 0 },
    { label: 'Legal Approvals', count: props.documents?.filter(d => d.category === 'sign_off').length || 0 },
    { label: 'Financial Records', count: props.documents?.filter(d => d.category === 'financial').length || 0 }
]);

const getDocIcon = (cat) => {
    if (cat === 'requirement') return DocumentTextIcon;
    if (cat === 'sign_off') return ScaleIcon;
    if (cat === 'financial') return CurrencyDollarIcon;
    return FolderIcon;
};

const handleFileSelect = (e) => {
    const file = e.target.files[0];
    if (file) {
        selectedFile.value = file;
        form.file = file;
        if (!form.name) form.name = file.name.split('.').slice(0, -1).join('.');
    }
};

const submitUpload = () => {
    form.post(route('portal.projects.document.upload', { project: form.project_id }), {
        onSuccess: () => {
            showUploadModal.value = false;
            form.reset();
            selectedFile.value = null;
        }
    });
};

const initiateSignOff = (doc) => {
    if (confirm(`Officially accept and sign-off on "${doc.name}"? This action creates an official project record.`)) {
        router.post(route('portal.projects.document.sign-off', { document: doc.id }));
    }
};
</script>
