<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import DocumentUploadModal from '@/Components/Modals/DocumentUploadModal.vue';
import { 
    FolderIcon, 
    FolderOpenIcon, 
    DocumentChartBarIcon, 
    DocumentTextIcon, 
    ShieldCheckIcon,
    IdentificationIcon,
    AcademicCapIcon,
    InboxStackIcon,
    PlusIcon,
    ArrowUpTrayIcon,
    TrashIcon,
    EyeIcon,
    LockClosedIcon,
    CommandLineIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    employee: { type: Object, required: true }
});

const toast = useToastStore();
const showUploadModal = ref(false);
const documents = ref([]);
const loading = ref(true);
const activeCategory = ref('All');

const categories = ref([
    { id: 'All', name: 'Global Vault', icon: InboxStackIcon, count: 0 },
    { id: 'Official', name: 'Official Records', icon: ShieldCheckIcon, count: 0 },
    { id: 'Financial', name: 'Financial Ledger', icon: DocumentChartBarIcon, count: 0 },
    { id: 'Medical', name: 'Medical Intel', icon: LockClosedIcon, count: 0 },
    { id: 'Identity', name: 'Identity Proofs', icon: IdentificationIcon, count: 0 },
    { id: 'Education', name: 'Academic Bio', icon: AcademicCapIcon, count: 0 },
    { id: 'Other', name: 'Misc Data', icon: FolderIcon, count: 0 },
]);

const currentCategoryName = computed(() => {
    return categories.value.find(c => c.id === activeCategory.value)?.name || 'Data Segment';
});

const filteredDocuments = computed(() => {
    const docs = documents.value || [];
    if (activeCategory.value === 'All') return docs;
    if (activeCategory.value === 'Medical') {
        return docs.filter(d => d.category && d.category.startsWith('Medical'));
    }
    return docs.filter(d => d.category === activeCategory.value);
});

const fetchDocuments = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/api/admin/employees/${props.employee.id}/documents`);
        documents.value = response.data.data || [];
        updateCounts();
    } catch (e) {
        toast.error("Failed to load documents");
        documents.value = [];
    } finally {
        loading.value = false;
    }
};

const updateCounts = () => {
    const counts = {};
    const docs = documents.value || [];
    docs.forEach(d => {
        const key = d.category?.startsWith('Medical') ? 'Medical' : d.category;
        counts[key] = (counts[key] || 0) + 1;
    });
    categories.value.forEach(c => {
        if(c.id === 'All') c.count = docs.length;
        else c.count = counts[c.id] || 0;
    });
};

const deleteDoc = async (doc) => {
    if (!confirm(`Confirm permanent deletion of protocol: ${doc.title}?`)) return;
    try {
        await axios.delete(`/api/admin/employees/${props.employee.id}/documents/${doc.id}`);
        toast.success("Protocol Purged");
        fetchDocuments(); 
    } catch (e) {
        toast.error(e.response?.data?.message || "Purge Failed");
    }
};

const formatDate = (date) => new Date(date).toLocaleDateString();

onMounted(() => {
    fetchDocuments();
});

watch(() => props.employee, (newVal) => {
    if (newVal && newVal.id) {
        fetchDocuments();
    }
}, { deep: true });
</script>

<template>
    <div class="flex flex-col lg:flex-row gap-8 h-full min-h-[600px] font-outfit">
        <!-- Strategic Library Sidebar -->
        <div class="w-full lg:w-72 flex-shrink-0">
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-100 p-6 shadow-2xl shadow-slate-200/40 h-full flex flex-col">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] mb-8 px-4 flex items-center gap-3">
                    <FolderOpenIcon class="w-4 h-4" />
                    Archive Sectors
                </h3>
                
                <nav class="flex-1 space-y-2 overflow-y-auto no-scrollbar">
                    <button 
                        v-for="cat in categories" 
                        :key="cat.id"
                        @click="activeCategory = cat.id"
                        class="w-full flex items-center justify-between px-5 py-4 rounded-2xl transition-all duration-300 group relative overflow-hidden"
                        :class="[
                            activeCategory === cat.id 
                            ? 'bg-slate-900 text-white shadow-xl shadow-slate-400 scale-[1.03] z-10' 
                            : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'
                        ]"
                    >
                        <div class="flex items-center gap-4 relative z-10">
                            <component :is="cat.icon" class="w-5 h-5 transition-transform group-hover:scale-110" :class="activeCategory === cat.id ? 'text-indigo-400' : 'text-slate-400 group-hover:text-indigo-500'" />
                            <span class="text-sm font-black uppercase tracking-widest">{{ cat.name }}</span>
                        </div>
                        <span v-if="cat.count > 0" class="relative z-10 px-2.5 py-1 rounded-lg text-xs font-black transition-colors" :class="activeCategory === cat.id ? 'bg-white/10 text-white' : 'bg-slate-100 text-slate-400'">{{ cat.count }}</span>
                    </button>
                </nav>

                <div class="mt-8 pt-6 border-t border-slate-50">
                     <button @click="showUploadModal = true" class="w-full h-14 bg-slate-900 text-white rounded-[1.75rem] text-xs font-black uppercase tracking-[0.2em] shadow-2xl shadow-slate-200 hover:bg-indigo-600 transition-all active:scale-95 flex items-center justify-center gap-4 group">
                        <ArrowUpTrayIcon class="w-5 h-5 text-indigo-400 group-hover:-translate-y-1 transition-transform" />
                        <span>Inject Protocol</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Document Grid Matrix -->
        <div class="flex-1">
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-100 p-10 shadow-2xl shadow-slate-200/40 min-h-full flex flex-col">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 gap-4">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tighter uppercase flex items-center gap-4">
                            {{ currentCategoryName }}
                            <span class="inline-flex px-3 py-1 bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-full text-xs font-black tracking-widest shadow-sm">{{ filteredDocuments.length }} NODES</span>
                        </h2>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-2">Authenticated data segment transmission monitor</p>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="flex-1 flex flex-col items-center justify-center py-20 grayscale opacity-40">
                    <CommandLineIcon class="w-12 h-12 text-slate-400 animate-pulse mb-6" />
                    <p class="text-xs font-black uppercase tracking-[0.4em] italic animate-pulse">Synchronizing vault sectors...</p>
                </div>

                <!-- Document Grid -->
                <div v-else-if="filteredDocuments.length > 0" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    <div v-for="doc in filteredDocuments" :key="doc.id" class="group relative bg-white border border-slate-100 rounded-[2rem] p-6 hover:border-indigo-200 hover:shadow-2xl hover:shadow-slate-200 transition-all duration-500 overflow-hidden">
                        <!-- Holographic Background Element -->
                        <div class="absolute -right-8 -top-8 w-24 h-24 bg-slate-50 rounded-full blur-2xl group-hover:bg-indigo-50 transition-colors"></div>
                        
                        <div class="flex items-start justify-between mb-6 relative z-10">
                            <div class="h-14 w-14 bg-slate-900 rounded-2xl flex items-center justify-center text-indigo-400 shadow-xl group-hover:rotate-6 transition-transform">
                                <DocumentTextIcon v-if="doc.file_type?.includes('pdf')" class="w-7 h-7" />
                                <FolderIcon v-else class="w-7 h-7" />
                                
                                <!-- Privacy Lock Status -->
                                <div v-if="doc.category?.startsWith('Medical')" class="absolute -top-2 -right-2 bg-rose-500 text-white rounded-lg p-1 shadow-lg border-2 border-white" title="High Privacy Lockdown">
                                    <LockClosedIcon class="w-3 h-3" />
                                </div>
                            </div>
                            
                            <div class="flex gap-2">
                                <a :href="doc.url" target="_blank" class="w-9 h-9 bg-slate-50 text-slate-400 rounded-xl flex items-center justify-center hover:bg-slate-900 hover:text-indigo-400 transition-all active:scale-90" title="Access Node">
                                    <EyeIcon class="w-4 h-4" />
                                </a>
                                <button v-if="!doc.is_system_generated" @click="deleteDoc(doc)" class="w-9 h-9 bg-rose-50 text-rose-400 rounded-xl flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all active:scale-90" title="Purge Record">
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        
                        <!-- Metadata -->
                        <div class="relative z-10 space-y-3">
                            <div>
                                <h4 class="text-base font-black text-slate-900 uppercase tracking-tight truncate group-hover:text-indigo-600 transition-colors" :title="doc.title">{{ doc.title }}</h4>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">{{ doc.category }}</span>
                                    <div class="w-1 h-1 rounded-full bg-slate-200"></div>
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">{{ doc.file_size }}</span>
                                </div>
                            </div>

                            <div v-if="doc.metadata && doc.metadata.event_date" class="bg-indigo-50/50 p-2.5 rounded-xl border border-indigo-100/50 flex items-center gap-3">
                                 <PlusIcon class="w-3 h-3 text-indigo-500" />
                                 <span class="text-xs font-black text-indigo-600 uppercase tracking-[0.2em]">Anchor: {{ formatDate(doc.metadata.event_date) }}</span>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-slate-50 mt-4">
                                <span v-if="doc.is_system_generated" class="inline-flex px-2 py-0.5 bg-emerald-50 text-emerald-600 text-xs font-black tracking-widest rounded border border-emerald-100 shadow-sm">SYSTEM_GEN</span>
                                <span v-else class="text-xs font-black text-slate-300 uppercase tracking-widest italic">{{ formatDate(doc.created_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="flex-1 flex flex-col items-center justify-center py-20 text-center grayscale opacity-30">
                    <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-8 border border-slate-100">
                        <FolderOpenIcon class="w-10 h-10 text-slate-300" />
                    </div>
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.3em]">Zero data nodes detected</h3>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-2 max-w-xs leading-relaxed italic">" No {{ currentCategoryName.toLowerCase() }} packets have been transmitted to the employee vault sector "</p>
                </div>
            </div>
        </div>

        <DocumentUploadModal
            :show="showUploadModal"
            :employee="employee"
            @close="showUploadModal = false"
            @saved="fetchDocuments"
        />
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
