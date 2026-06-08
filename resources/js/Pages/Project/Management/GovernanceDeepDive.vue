<template>
    <MainLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 py-4">
                <div class="flex items-center gap-4">
                    <button @click="goBack" class="h-10 w-10 flex items-center justify-center rounded-2xl bg-white border border-slate-100 text-slate-400 hover:text-emerald-600 hover:border-emerald-100 transition-all shadow-sm">
                        <ArrowLeftIcon class="h-5 w-5" />
                    </button>
                    <div>
                        <div class="flex items-center gap-2">
                             <h2 class="font-black text-2xl text-slate-800 tracking-tight leading-none group">
                                Governance <span class="text-emerald-600">Vault</span>
                            </h2>
                            <span class="px-2 py-0.5 bg-slate-100 text-[8px] font-black uppercase tracking-widest text-slate-500 rounded-md border border-slate-200">
                                {{ project.code }}
                            </span>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-2">
                             {{ project.name }} • Executive Oversight hub
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button @click="openUploadModal" class="px-6 py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-emerald-600 transition-all shadow-xl flex items-center gap-2 group">
                        <PlusIcon class="h-4 w-4 text-emerald-400 group-hover:text-white transition-colors" /> Secure New Artifact
                    </button>
                </div>
            </div>
        </template>

        <div class="bg-slate-50/50 min-h-screen py-8 pb-32">
            <div class="max-w-7xl mx-auto px-4 lg:px-8 space-y-8">
                
                <!-- Executive Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                            <DocumentCheckIcon class="h-24 w-24 text-emerald-600" />
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Compliance Health</p>
                        <h3 class="mt-4 text-4xl font-black text-slate-800">{{ complianceRate }}%</h3>
                        <p class="mt-2 text-[10px] font-bold text-emerald-600 uppercase tracking-tight">Requirement Protocol Verification</p>
                    </div>

                    <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                            <ExclamationTriangleIcon class="h-24 w-24 text-rose-600" />
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Signals</p>
                        <h3 class="mt-4 text-4xl font-black text-slate-800">{{ signals.length }}</h3>
                        <p class="mt-2 text-[10px] font-bold text-rose-500 uppercase tracking-tight">Critical/High Risks Detected</p>
                    </div>

                    <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                            <UserGroupIcon class="h-24 w-24 text-indigo-600" />
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Client Portal Status</p>
                        <h3 class="mt-4 text-4xl font-black text-slate-800">Connected</h3>
                        <p class="mt-2 text-[10px] font-bold text-indigo-500 uppercase tracking-tight">Bidirectional Sync Active</p>
                    </div>
                </div>

                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Left: Artifact Registry -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-[40px] border border-slate-100 shadow-sm overflow-hidden">
                            <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                                <div>
                                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Artifact Registry</h3>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">Official Governance Documentation</p>
                                </div>
                            </div>

                            <div class="divide-y divide-slate-50">
                                <div v-for="(catName, catKey) in categories" :key="catKey" class="p-8">
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="h-8 w-8 rounded-xl bg-slate-50 flex items-center justify-center border border-slate-100">
                                            <component :is="getCategoryIcon(catKey)" class="h-4 w-4 text-slate-400" />
                                        </div>
                                        <h4 class="text-[11px] font-black text-slate-800 uppercase tracking-[0.2em]">{{ catName }}</h4>
                                    </div>

                                    <div v-if="documents[catKey]" class="grid gap-4">
                                        <div v-for="doc in documents[catKey]" :key="doc.id" 
                                            class="flex items-center justify-between p-4 rounded-2xl bg-slate-50/50 border border-slate-100 hover:border-emerald-200 hover:bg-white transition-all group">
                                            <div class="flex items-center gap-4">
                                                <div class="h-10 w-10 rounded-xl bg-white flex items-center justify-center border border-slate-100 text-slate-400 group-hover:text-emerald-500 transition-colors">
                                                    <DocumentIcon class="h-5 w-5" />
                                                </div>
                                                <div>
                                                    <p class="text-xs font-black text-slate-800">{{ doc.name }}</p>
                                                    <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">
                                                        V{{ doc.version_number }} • Uploaded by {{ doc.uploader.name }} • {{ formatSize(doc.file_size) }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span v-if="doc.is_signed" class="px-2 py-1 rounded-lg bg-emerald-100 text-emerald-700 text-[8px] font-black uppercase tracking-widest">
                                                    Signed
                                                </span>
                                                <button @click="downloadDocument(doc)" class="h-8 w-8 rounded-lg bg-white border border-slate-100 text-slate-400 hover:text-emerald-600 transition-all flex items-center justify-center shadow-sm">
                                                    <ArrowDownTrayIcon class="h-4 w-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-center py-6 border-2 border-dashed border-slate-100 rounded-2xl">
                                        <p class="text-[9px] font-bold text-slate-300 uppercase tracking-widest">No artifacts filed under this protocol</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Signal Monitor & Audit Trail -->
                    <div class="space-y-8">
                        <!-- Signal Monitor -->
                        <div class="bg-slate-900 rounded-[40px] shadow-2xl overflow-hidden p-8 text-white relative">
                             <div class="absolute top-0 right-0 p-8 opacity-10">
                                <SignalIcon class="h-20 w-20 text-emerald-400 animate-pulse" />
                            </div>
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-emerald-400">Signal Monitor</h3>
                            <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">Critical Risks & Client Feed</p>

                            <div class="mt-8 space-y-4 relative z-10">
                                <div v-for="signal in signals" :key="signal.id" class="p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all cursor-pointer group">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="px-2 py-0.5 rounded bg-rose-500/20 text-rose-400 text-[8px] font-black uppercase tracking-widest border border-rose-500/30">
                                            {{ signal.severity }}
                                        </span>
                                        <span class="text-[8px] font-bold text-slate-500 uppercase">{{ formatDate(signal.created_at) }}</span>
                                    </div>
                                    <h4 class="text-xs font-bold text-slate-100 line-clamp-1 group-hover:text-emerald-400 transition-colors">{{ signal.subject }}</h4>
                                    <div class="mt-3 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="h-5 w-5 rounded-full bg-slate-800 flex items-center justify-center text-[8px] font-black border border-white/10">
                                                {{ signal.reporter?.name?.charAt(0) }}
                                            </div>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ signal.reporter?.name }}</span>
                                        </div>
                                        <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">{{ signal.stage?.name }}</span>
                                    </div>
                                </div>
                                <div v-if="signals.length === 0" class="text-center py-8">
                                     <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Protocol Nominal • No Signals</p>
                                </div>
                            </div>
                        </div>

                        <!-- Audit History -->
                        <div class="bg-white rounded-[40px] border border-slate-100 shadow-sm p-8">
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-800">Governance Timeline</h3>
                            <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">Latest Audit Artifacts</p>

                            <div class="mt-8 space-y-6">
                                <div v-for="log in auditTrail" :key="log.id" class="flex gap-4 relative">
                                    <div class="h-full w-px bg-slate-100 absolute left-[9px] top-4 bottom-[-1.5rem]"></div>
                                    <div class="h-5 w-5 rounded-full bg-white border-2 border-slate-100 flex items-center justify-center shrink-0 relative z-10">
                                        <div class="h-1.5 w-1.5 rounded-full bg-slate-400"></div>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-slate-800 leading-none">{{ log.user.name }}</p>
                                        <p class="text-[9px] font-bold text-slate-500 mt-1 italic leading-relaxed">"{{ log.reason }}"</p>
                                        <p class="text-[8px] font-black text-slate-300 uppercase tracking-widest mt-2">{{ formatDateTime(log.created_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <Modal :show="showUploadModal" @close="showUploadModal = false" maxWidth="lg">
            <div class="p-8">
                <div class="flex items-center gap-4 mb-8">
                    <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100 shadow-sm">
                        <CloudArrowUpIcon class="h-6 w-6" />
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-800 tracking-tight uppercase">Secure Artifact</h3>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Upload to Governance Vault</p>
                    </div>
                </div>

                <form @submit.prevent="submitUpload" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Artifact Name *</label>
                        <input v-model="uploadForm.name" type="text" class="w-full bg-slate-50 border-slate-100 rounded-2xl px-4 py-3 text-sm font-bold focus:border-emerald-500 focus:ring-emerald-500" placeholder="e.g. Final Project Charter V1.2">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Protocol Category *</label>
                        <select v-model="uploadForm.category" class="w-full bg-slate-50 border-slate-100 rounded-2xl px-4 py-3 text-sm font-bold focus:border-emerald-500 focus:ring-emerald-500">
                            <option v-for="(name, key) in categories" :key="key" :value="key">{{ name }}</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                         <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Visibility Scope</label>
                            <select v-model="uploadForm.visibility" class="w-full bg-slate-50 border-slate-100 rounded-2xl px-4 py-3 text-sm font-bold focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="management_only">Management Only</option>
                                <option value="internal">Internal Team</option>
                                <option value="client_shared">Client Shared</option>
                            </select>
                        </div>
                    </div>

                    <div class="p-8 border-2 border-dashed border-slate-100 rounded-3xl bg-slate-50/50 text-center group hover:border-emerald-300 hover:bg-emerald-50/30 transition-all cursor-pointer relative overflow-hidden">
                        <input type="file" @input="uploadForm.file = $event.target.files[0]" class="absolute inset-0 opacity-0 cursor-pointer z-10">
                        <div class="relative z-0">
                            <DocumentIcon class="h-10 w-10 text-slate-300 mx-auto group-hover:text-emerald-500 transition-colors" />
                            <p class="mt-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                {{ uploadForm.file ? uploadForm.file.name : 'Drop Artifact or Click to Browse' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="showUploadModal = false" class="flex-1 px-6 py-4 bg-slate-100 text-slate-500 text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-slate-200 transition-all">
                            Cancel
                        </button>
                        <button type="submit" :disabled="uploadForm.processing" class="flex-2 px-10 py-4 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-emerald-700 transition-all shadow-xl disabled:opacity-50">
                            Secure Artifact
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import { 
    ArrowLeftIcon, 
    PlusIcon, 
    ArrowDownTrayIcon, 
    DocumentIcon,
    DocumentCheckIcon,
    ExclamationTriangleIcon,
    UserGroupIcon,
    SignalIcon,
    CloudArrowUpIcon,
    BriefcaseIcon,
    ShieldCheckIcon,
    BanknotesIcon,
    ClipboardDocumentCheckIcon,
    InboxStackIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    project: Object,
    documents: Object,
    signals: Array,
    auditTrail: Array,
    categories: Object
});

const showUploadModal = ref(false);

const uploadForm = useForm({
    name: '',
    category: 'requirement',
    file: null,
    visibility: 'management_only'
});

const complianceRate = computed(() => {
    const totalRequired = 3; // Theoretical required docs for 100% compliance
    const docs = Object.values(props.documents).flat();
    const uniqueCats = new Set(docs.map(d => d.category)).size;
    return Math.min(Math.round((uniqueCats / totalRequired) * 100), 100);
});

const getCategoryIcon = (key) => {
    const map = {
        requirement: BriefcaseIcon,
        sign_off: ShieldCheckIcon,
        finalized_plan: ClipboardDocumentCheckIcon,
        financial: BanknotesIcon,
        compliance_cert: ShieldCheckIcon,
        general: InboxStackIcon
    };
    return map[key] || DocumentIcon;
};

const goBack = () => {
    router.get(route('projects.portal.management.dealing-hub'));
};

const openUploadModal = () => {
    uploadForm.reset();
    showUploadModal.value = true;
};

const submitUpload = () => {
    uploadForm.post(route('projects.portal.management.governance.document.store', props.project.id), {
        onSuccess: () => {
            showUploadModal.value = false;
            uploadForm.reset();
        }
    });
};

const downloadDocument = (doc) => {
    window.open(route('documents.stream', doc.id), '_blank');
};

const formatSize = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', { day: '2-digit', month: 'short' });
};

const formatDateTime = (date) => {
    return new Date(date).toLocaleString('en-US', { 
        day: '2-digit', 
        month: 'short', 
        hour: '2-digit', 
        minute: '2-digit' 
    });
};
</script>
