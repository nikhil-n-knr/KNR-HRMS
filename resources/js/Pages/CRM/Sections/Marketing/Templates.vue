<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative font-sans text-left">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group no-print">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <i class="fas fa-info text-xs text-left font-sans"></i>
            </button>
            <div class="absolute right-0 top-10 w-80 bg-gray-900 text-white text-sm p-6 rounded-[32px] shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium font-sans">
                <div class="font-black tracking-[0.2em] uppercase mb-3 text-indigo-400 border-b border-indigo-500/20 pb-3 text-left font-sans">Visual Asset Repository</div>
                <p class="text-gray-300 leading-relaxed mb-4 text-left font-sans">The Creative Matrix stores standardized communication frameworks. Architect high-impact narratives that maintain brand integrity across all growth channels.</p>
                <div class="space-y-2 text-left font-sans">
                    <div class="flex items-center gap-3 text-left font-sans font-sans"><div class="w-2 h-2 rounded-full bg-indigo-500 shadow-lg shadow-indigo-500/50"></div> <span>Standardized Blueprint Frameworks</span></div>
                    <div class="flex items-center gap-3 text-left font-sans font-sans"><div class="w-2 h-2 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/50"></div> <span>Contextual Narrative Categories</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40 no-print">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 text-left font-sans">
                    <div class="text-left font-sans">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight text-left font-sans">Communication Hub</h2>
                        <div class="flex items-center mt-3 text-left font-sans">
                            <i class="fas fa-layer-group text-indigo-500 mr-3 text-left font-sans"></i>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">Creative Engine: Synchronized</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-left font-sans">
                        <div class="relative w-64 group text-left font-sans">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-500 transition-colors text-left font-sans"></i>
                            <input 
                                v-model="search" 
                                type="text" 
                                class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border-none rounded-xl text-sm font-black uppercase focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder-gray-400 shadow-inner text-left font-sans" 
                                placeholder="Find Blueprint..."
                            >
                        </div>
                        <button @click="openCreateModal" class="px-8 py-3.5 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all font-black text-sm tracking-widest uppercase shadow-xl shadow-indigo-100 flex items-center group active:scale-95 text-left font-sans">
                            <i class="fas fa-plus mr-2 text-xs group-hover:scale-125 transition-transform text-left font-sans"></i>
                            Architect New Blueprint
                        </button>
                    </div>
                </div>
            </div>

            <!-- Templates Surface -->
            <div class="flex-1 overflow-auto p-8 text-left font-sans" id="print-area">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 text-left font-sans">
                    <div v-for="template in filteredTemplates" :key="template.id" class="group relative bg-white border border-gray-100 rounded-[40px] overflow-hidden hover:shadow-2xl transition-all h-[380px] flex flex-col shadow-sm border-l-4 border-l-transparent hover:border-l-indigo-600 text-left font-sans">
                        <!-- Preview Engine Visualization -->
                        <div class="flex-1 bg-gray-50/50 p-8 text-sm text-gray-400 overflow-hidden relative font-mono select-none text-left font-sans line-clamp-10">
                            <div class="absolute inset-x-8 top-8 leading-relaxed tracking-tight break-words text-left font-sans opacity-40">{{ template.content.substring(0, 1000) }}...</div>
                            
                            <!-- Interaction Overlay -->
                            <div class="absolute inset-0 bg-indigo-900/0 group-hover:bg-indigo-900/80 transition-all flex items-center justify-center gap-4 opacity-0 group-hover:opacity-100 backdrop-blur-[2px] text-left font-sans">
                                 <button @click="editTemplate(template)" class="w-14 h-14 bg-white rounded-2xl text-indigo-600 hover:scale-110 transition-all shadow-xl flex items-center justify-center active:scale-95 text-left font-sans" title="Refine Structure">
                                    <i class="fas fa-pen text-sm text-left font-sans"></i>
                                </button>
                                 <button @click="deleteTemplate(template.id)" class="w-14 h-14 bg-white rounded-2xl text-rose-500 hover:scale-110 transition-all shadow-xl flex items-center justify-center active:scale-95 text-left font-sans" title="Decommission Blueprint">
                                    <i class="fas fa-trash text-sm text-left font-sans"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="p-8 bg-white flex flex-col justify-between border-t border-gray-50 text-left font-sans">
                            <div class="text-left font-sans">
                                <h3 class="text-sm font-black text-gray-900 group-hover:text-indigo-600 transition-colors truncate text-left font-sans mb-3">{{ template.name }}</h3>
                                <div class="flex justify-between items-center text-left font-sans">
                                     <span class="text-sm font-black text-indigo-500 bg-indigo-50 px-3 py-1 rounded-lg border border-indigo-100 uppercase tracking-widest text-left font-sans shadow-inner">{{ template.category }}</span>
                                     <span class="text-sm font-black text-gray-300 uppercase tracking-widest text-left font-sans">REV: {{ new Date(template.updated_at).toLocaleDateString() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                     <!-- Empty Blueprint Injection -->
                    <button @click="openCreateModal" class="bg-gray-50/50 border-2 border-dashed border-gray-200 rounded-[40px] flex flex-col items-center justify-center text-gray-300 hover:border-indigo-500 hover:text-indigo-600 hover:bg-white transition-all min-h-[380px] group/new text-left font-sans">
                        <div class="w-20 h-20 rounded-[30px] bg-white shadow-sm border border-gray-100 flex items-center justify-center mb-6 group-hover/new:rotate-12 transition-transform text-left font-sans">
                            <i class="fas fa-pencil-ruler text-3xl group-hover/new:scale-110 transition-transform text-left font-sans"></i>
                        </div>
                        <span class="text-lg font-black tracking-tight text-left font-sans">Genesis Block</span>
                        <span class="text-sm font-bold uppercase tracking-widest mt-2 opacity-60 text-left font-sans">Architect New Blueprint</span>
                    </button>
                </div>

                <!-- Empty State Fallback -->
                <div v-if="filteredTemplates.length === 0 && search" class="flex flex-col items-center justify-center py-40 text-gray-300 text-left font-sans">
                    <div class="w-20 h-20 bg-gray-50 rounded-[30px] flex items-center justify-center mx-auto mb-6 text-left font-sans">
                         <i class="fas fa-search text-3xl text-gray-200 text-left font-sans"></i>
                    </div>
                    <p class="text-sm font-black text-gray-300 uppercase tracking-widest text-left font-sans">No blueprints matching "{{ search }}" in current index.</p>
                </div>
            </div>
        </div>

        <!-- BLUEPRINT ARCHITECT MODAL -->
        <div v-if="showModal" @click.self="closeModal" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="relative bg-white rounded-[45px] shadow-2xl max-w-5xl w-full overflow-hidden border border-white text-left animate-in zoom-in-95 duration-300">
                <div class="bg-gray-50/50 px-12 py-10 border-b border-gray-100 flex justify-between items-center text-left font-sans">
                    <div class="flex items-center text-left font-sans">
                        <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white mr-5 shadow-xl shadow-indigo-100 text-left font-sans">
                            <i class="fas fa-code text-xl text-left font-sans"></i>
                        </div>
                        <div class="text-left font-sans">
                            <h3 class="text-2xl font-black text-gray-900 tracking-tight text-left font-sans">{{ isEditing ? 'Refine Logic Blueprint' : 'Architect New Logic Blueprint' }}</h3>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-[0.2em] mt-1 text-left font-sans">Creative Asset Construction Engine</p>
                        </div>
                    </div>
                    <button @click="closeModal" class="w-12 h-12 rounded-2xl bg-white border border-gray-100 text-gray-400 hover:text-gray-900 flex items-center justify-center shadow-sm transition-all hover:rotate-90 text-left font-sans">
                        <i class="fas fa-times text-left font-sans"></i>
                    </button>
                </div>
                
                <form @submit.prevent="submit" class="p-12 space-y-10 text-left font-sans">
                    <div class="grid grid-cols-2 gap-10 text-left font-sans">
                        <div class="space-y-3 text-left font-sans">
                             <label class="block text-sm font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Designation Identity</label>
                             <input v-model="form.name" type="text" required class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4.5 px-6 font-semibold shadow-inner text-left font-sans" placeholder="e.g. Enterprise Onboarding Framework">
                        </div>
                        <div class="space-y-3 text-left font-sans">
                             <label class="block text-sm font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Contextual Category</label>
                             <select v-model="form.category" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4.5 px-6 font-bold shadow-inner cursor-pointer appearance-none text-left font-sans">
                                <option value="Newsletter">Intelligence Digest</option>
                                <option value="Promo">Growth Catalyst</option>
                                <option value="Transactional">Lifecycle Engine</option>
                                <option value="General">General Sequence</option>
                             </select>
                        </div>
                    </div>

                    <div class="space-y-3 text-left font-sans">
                         <label class="block text-sm font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Primary Narrative Hook (Subject Line)</label>
                         <input v-model="form.subject" type="text" required class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-sm py-4.5 px-6 font-semibold shadow-inner text-left font-sans" placeholder="Unlocking the power of systemic scalability...">
                    </div>

                    <div class="space-y-3 text-left font-sans">
                         <div class="flex justify-between items-center mb-3 text-left font-sans px-1">
                             <label class="block text-sm font-black text-indigo-500 uppercase tracking-widest text-left font-sans">Core Asset Syntax (HTML/Text)</label>
                             <span class="text-sm font-bold text-gray-400 uppercase text-left font-sans">Standardized Syntax Protocol Active</span>
                         </div>
                         <textarea v-model="form.content" rows="12" class="w-full bg-gray-900 border-gray-800 text-indigo-300 rounded-[35px] focus:ring-8 focus:ring-indigo-500/5 transition-all text-xs py-10 px-10 font-mono shadow-2xl leading-relaxed text-left font-sans" placeholder="<html>..."></textarea>
                    </div>

                    <div class="pt-8 flex items-center justify-between text-left font-sans">
                        <button type="button" @click="closeModal" class="text-base font-black text-gray-400 hover:text-gray-900 transition-colors uppercase tracking-widest text-left font-sans">Discard Draft</button>
                        <button type="submit" 
                                :disabled="form.processing"
                                class="bg-indigo-600 text-white px-12 py-5 rounded-[28px] font-black text-base tracking-widest uppercase hover:bg-indigo-700 transition-all shadow-2xl shadow-indigo-100 active:scale-95 disabled:opacity-50 min-w-[260px] flex items-center justify-center text-left font-sans">
                            <i class="fas fa-save mr-3 text-left font-sans"></i>
                            {{ form.processing ? 'SYNTHESIZING ASSET...' : (isEditing ? 'UPDATE MASTER BLUEPRINT' : 'COMMIT MASTER BLUEPRINT') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    templates: { type: Array, default: () => [] }
});

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const search = ref('');

const filteredTemplates = computed(() => {
    let list = props.templates || [];
    if (search.value) {
        const term = search.value.toLowerCase();
        list = list.filter(t => t.name?.toLowerCase().includes(term) || t.category?.toLowerCase().includes(term));
    }
    return list;
});

const form = useForm({
    name: '',
    subject: '',
    category: 'General',
    content: '<html>\n<body style="font-family: sans-serif; color: #333; background: #f9fafb; padding: 40px;">\n  <div style="max-width: 600px; margin: 0 auto; padding: 60px; background: #ffffff; border-radius: 40px; box-shadow: 0 4px 60px rgba(0,0,0,0.05); border: 1px solid #f1f5f9;">\n    <div style="margin-bottom: 40px; text-align: left;">\n       <h1 style="color: #4f46e5; text-transform: uppercase; font-weight: 900; font-size: 24px; letter-spacing: 0.1em;">RESONANCE CORE</h1>\n    </div>\n    <p style="line-height: 1.8; font-size: 16px; color: #475569;">Hello Partner,</p>\n    <p style="line-height: 1.8; font-size: 16px; color: #475569;">This is a premium communication framework initialized via the HRMS Global Marketing Engine. Our goal is ecosystem synergy.</p>\n    <div style="margin-top: 60px; padding-top: 30px; border-top: 1px solid #f1f5f9;">\n       <p style="font-size: 12px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;">Proprietary Strategic Blueprint</p>\n    </div>\n  </div>\n</body>\n</html>'
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    showModal.value = true;
};

const editTemplate = (template) => {
    isEditing.value = true;
    editingId.value = template.id;
    form.name = template.name;
    form.subject = template.subject;
    form.category = template.category;
    form.content = template.content;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('crm.marketing.templates.update', editingId.value), {
            onSuccess: closeModal
        });
    } else {
        form.post(route('crm.marketing.templates.store'), {
            onSuccess: closeModal
        });
    }
};

const deleteTemplate = (id) => {
    if (confirm('Decommission this creative asset blueprint?')) {
        router.delete(route('crm.marketing.templates.destroy', id));
    }
};

const printView = () => window.print();
</script>

<style scoped>
/* Custom Hide Scrollbar */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.05); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.1); }

@media print {
    #print-area { padding: 0 !important; }
    .no-print { display: none !important; }
}

.line-clamp-10 {
    display: -webkit-box;
    -webkit-line-clamp: 10;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
</style>
