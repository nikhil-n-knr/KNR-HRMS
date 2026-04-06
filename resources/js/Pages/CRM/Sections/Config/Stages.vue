<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative font-sans text-left">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group no-print">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <i class="fas fa-info text-xs"></i>
            </button>
            <div class="absolute right-0 top-10 w-80 bg-gray-900 text-white text-sm p-6 rounded-[32px] shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium font-sans">
                <div class="font-black tracking-[0.2em] uppercase mb-3 text-indigo-400 border-b border-indigo-500/20 pb-3 text-left">Pipeline Velocity Architecture</div>
                <p class="text-gray-300 leading-relaxed mb-4 text-left font-sans">Pipeline Stages define the momentum of your revenue funnel. Structure the progression from lead to close with specific win probabilities and duration service level agreements (SLAs).</p>
                <div class="space-y-2 text-left">
                    <div class="flex items-center gap-3 text-left font-sans"><div class="w-2 h-2 rounded-full bg-indigo-500 shadow-lg shadow-indigo-500/50"></div> <span>Sequential Velocity Mapping</span></div>
                    <div class="flex items-center gap-3 text-left font-sans"><div class="w-2 h-2 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/50"></div> <span>Probabilistic Win Calculation</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40 no-print">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 text-left font-sans text-left font-sans">
                    <div class="text-left font-sans text-left font-sans">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight text-left font-sans text-left font-sans">Pipeline Architecture</h2>
                        <div class="flex items-center mt-3 text-left font-sans text-left font-sans">
                            <i class="fas fa-layer-group text-indigo-500 mr-3 text-left font-sans"></i>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">Velocity Engine: Active</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-left font-sans text-left font-sans">
                         <button @click="openCreateModal" class="px-8 py-3.5 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all font-black text-sm tracking-widest uppercase shadow-xl shadow-indigo-100 flex items-center group active:scale-95 text-left font-sans text-left font-sans">
                            <i class="fas fa-plus mr-2 text-xs group-hover:rotate-90 transition-transform text-left font-sans"></i>
                            Initialize New Stage
                        </button>
                        <button class="w-12 h-12 bg-white border border-gray-200 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm group relative text-left font-sans">
                            <i class="fas fa-file-excel text-sm text-left font-sans"></i>
                        </button>
                        <button @click="printView" class="w-12 h-12 bg-white border border-gray-200 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm group relative text-left font-sans">
                            <i class="fas fa-print text-sm text-left font-sans"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stages Surface -->
            <div class="flex-1 overflow-auto p-8 text-left font-sans" id="print-area">
                <div class="max-w-5xl mx-auto space-y-6 text-left font-sans text-left font-sans">
                    <div v-for="(stage, index) in stages" :key="stage.id" 
                         class="bg-white p-8 rounded-[40px] border border-gray-100 shadow-sm flex items-center gap-10 group hover:shadow-2xl hover:shadow-indigo-500/5 transition-all text-left font-sans text-left font-sans border-l-8"
                         :style="`border-left-color: ${stage.color}`">
                        
                        <div class="w-16 h-16 rounded-[24px] flex items-center justify-center font-black text-white shadow-xl text-lg text-left font-sans" :style="`background-color: ${stage.color}`">
                            {{ index + 1 }}
                        </div>
                        
                        <div class="flex-1 text-left font-sans text-left font-sans">
                            <div class="flex items-center gap-4 text-left font-sans">
                                <h4 class="text-xl font-black text-gray-900 tracking-tight text-left font-sans">{{ stage.name }}</h4>
                                <span :class="['px-3 py-1.5 rounded-xl text-sm font-black uppercase tracking-widest border text-left font-sans', getStageTypeClass(stage.type)]">
                                    {{ stage.type.toUpperCase() }} NODE
                                </span>
                            </div>
                            <div class="flex gap-6 mt-3 text-sm font-black text-gray-400 uppercase tracking-widest text-left font-sans">
                                <span class="flex items-center items-center text-left font-sans"><i class="fas fa-bullseye mr-2 text-indigo-500 text-left font-sans"></i> Win Probability: {{ stage.win_probability }}%</span>
                                <span v-if="stage.is_default" class="flex items-center items-center text-left font-sans"><i class="fas fa-star mr-2 text-amber-500 text-left font-sans"></i> Primary Default</span>
                                <span class="flex items-center items-center text-left font-sans"><i class="fas fa-clock mr-2 text-emerald-500 text-left font-sans"></i> SLA: {{ stage.expected_duration_days || 0 }} Days</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 text-left font-sans text-left font-sans">
                            <div class="flex gap-3 text-left font-sans">
                                <button @click="editStage(stage)" class="w-12 h-12 rounded-2xl bg-white border border-gray-100 text-gray-400 hover:text-indigo-600 hover:border-indigo-100 flex items-center justify-center transition-all shadow-sm active:scale-95 text-left font-sans text-left font-sans">
                                    <i class="fas fa-pen text-xs text-left font-sans"></i>
                                </button>
                                <button @click="deleteStage(stage.id)" class="w-12 h-12 rounded-2xl bg-white border border-gray-100 text-gray-400 hover:text-rose-600 hover:border-rose-100 flex items-center justify-center transition-all shadow-sm active:scale-95 text-left font-sans text-left font-sans text-left font-sans">
                                    <i class="fas fa-trash text-xs text-left font-sans"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!stages.length" class="flex flex-col items-center justify-center py-40 text-gray-300 text-left font-sans text-left font-sans">
                        <div class="w-24 h-24 bg-white rounded-[40px] shadow-sm flex items-center justify-center mb-8 border border-gray-100 text-left font-sans text-left font-sans">
                             <i class="fas fa-project-diagram text-3xl opacity-20 text-left font-sans text-left font-sans"></i>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight text-left font-sans text-left font-sans">Pipeline Void</h3>
                        <p class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-2 text-left font-sans text-left font-sans">Initialize stages to define your global sales velocity logic.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- STAGE CONFIGURATION MODAL -->
        <div v-if="showModal" @click.self="closeModal" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4 text-left font-sans">
            <div class="relative bg-white rounded-[45px] shadow-2xl max-w-xl w-full overflow-hidden border border-white text-left font-sans animate-in zoom-in-95 duration-300">
                <div class="bg-gray-50/50 px-12 py-10 border-b border-gray-100 flex justify-between items-center text-left font-sans">
                    <div class="flex items-center text-left font-sans">
                        <div class="w-14 h-14 bg-indigo-600 text-white rounded-2xl flex items-center justify-center text-white mr-5 shadow-xl shadow-indigo-100 text-left font-sans">
                            <i class="fas fa-layer-group text-xl text-left font-sans"></i>
                        </div>
                        <div class="text-left font-sans">
                            <h3 class="text-2xl font-black text-gray-900 tracking-tight text-left font-sans">{{ isEditing ? 'Refine Stage' : 'Define Stage' }}</h3>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-[0.2em] mt-1 text-left font-sans">Pipeline Engine Architecture</p>
                        </div>
                    </div>
                    <button @click="closeModal" class="w-12 h-12 rounded-2xl bg-white border border-gray-100 text-gray-400 hover:text-gray-900 flex items-center justify-center shadow-sm transition-all hover:rotate-90 text-left font-sans">
                        <i class="fas fa-times text-left font-sans"></i>
                    </button>
                </div>
                
                <form @submit.prevent="submit" class="p-12 space-y-8 text-left font-sans">
                    <div class="grid grid-cols-2 gap-8 text-left font-sans">
                        <div class="space-y-3 col-span-2 text-left font-sans">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Stage Designation</label>
                             <input v-model="form.name" type="text" required class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4.5 px-6 text-sm font-semibold shadow-inner focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-left font-sans" placeholder="e.g. Strategic Negotiation">
                        </div>
                        
                        <div class="space-y-3 text-left font-sans">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Operational Type</label>
                             <select v-model="form.type" class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4.5 px-6 text-sm font-bold shadow-inner focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-left font-sans appearance-none cursor-pointer">
                                <option value="open">ACTIVE PIPELINE</option>
                                <option value="won">CLOSED REVENUE (WON)</option>
                                <option value="lost">CLOSED CHURN (LOST)</option>
                             </select>
                        </div>

                        <div class="space-y-3 text-left font-sans">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Win Probality (%)</label>
                             <input v-model="form.win_probability" type="number" min="0" max="100" class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4.5 px-6 text-sm font-black shadow-inner focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-left font-sans" placeholder="75">
                        </div>

                        <div class="space-y-3 text-left font-sans">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Node Core Theme</label>
                             <div class="flex gap-4 text-left font-sans">
                                <div class="relative text-left font-sans">
                                    <input v-model="form.color" type="color" class="h-14 w-14 rounded-2xl border-none p-1 cursor-pointer bg-white shadow-sm ring-1 ring-gray-100 text-left font-sans">
                                </div>
                                <input v-model="form.color" type="text" class="flex-1 bg-gray-50 border-gray-100 rounded-2xl py-4.5 px-6 text-xs font-mono font-bold shadow-inner text-left font-sans uppercase">
                             </div>
                        </div>

                        <div class="space-y-3 text-left font-sans">
                             <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Velocity SLA (Days)</label>
                             <input v-model="form.expected_duration_days" type="number" min="1" class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4.5 px-6 text-sm font-black shadow-inner focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-left font-sans" placeholder="14">
                        </div>
                    </div>

                    <div class="flex items-center gap-8 p-6 bg-indigo-50 rounded-[30px] border border-indigo-100 text-left font-sans">
                         <div class="flex items-center gap-3 text-left font-sans font-sans font-sans">
                            <input v-model="form.is_default" type="checkbox" id="is_default" class="w-6 h-6 text-indigo-600 border-indigo-200 rounded-lg focus:ring-indigo-500/10 text-left font-sans">
                            <label for="is_default" class="text-sm font-black text-indigo-900 uppercase tracking-widest cursor-pointer text-left font-sans">Master Default Node</label>
                         </div>
                         <div class="flex items-center gap-3 text-left font-sans font-sans font-sans">
                            <input v-model="form.is_active" type="checkbox" id="is_active_stage" class="w-6 h-6 text-indigo-600 border-indigo-200 rounded-lg focus:ring-indigo-500/10 text-left font-sans">
                            <label for="is_active_stage" class="text-sm font-black text-indigo-900 uppercase tracking-widest cursor-pointer text-left font-sans">Functional State</label>
                         </div>
                    </div>

                    <div class="pt-8 flex items-center justify-between text-left font-sans">
                        <button type="button" @click="closeModal" class="text-base font-black text-gray-400 hover:text-gray-900 transition-colors uppercase tracking-widest text-left font-sans">Discard Settings</button>
                        <button type="submit" 
                                :disabled="form.processing"
                                class="bg-indigo-600 text-white px-12 py-5 rounded-[28px] font-black text-base tracking-widest uppercase hover:bg-indigo-700 transition-all shadow-2xl shadow-indigo-100 flex items-center justify-center min-w-[240px] active:scale-95 text-left font-sans">
                            <i class="fas fa-save mr-3 text-left font-sans"></i>
                            {{ isEditing ? 'COMMIT ARCHITECTURE' : 'INITIALIZE PIPELINE NODE' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    stages: { type: Array, default: () => [] }
});

const showModal = ref(false);
const isEditing = ref(false);
const editId = ref(null);

const form = useForm({
    name: '',
    color: '#6366F1', // Indigo-500 default
    order: 0,
    type: 'open',
    win_probability: 0,
    is_active: true,
    is_default: false,
    expected_duration_days: 7
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.order = props.stages?.length || 0;
    showModal.value = true;
};

const editStage = (stage) => {
    isEditing.value = true;
    editId.value = stage.id;
    form.name = stage.name;
    form.color = stage.color;
    form.type = stage.type;
    form.win_probability = stage.win_probability;
    form.is_active = stage.is_active;
    form.is_default = stage.is_default;
    form.expected_duration_days = stage.expected_duration_days;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('crm.pipeline-stages.update', editId.value), {
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('crm.pipeline-stages.store'), {
            onSuccess: () => closeModal()
        });
    }
};

const deleteStage = (id) => {
    if (confirm('Critical Protocol: existing deals and intelligence assets in this node may need reallocation. Proceed with decommissioning?')) {
        router.delete(route('crm.pipeline-stages.destroy', id));
    }
};

const getStageTypeClass = (type) => {
    switch(type) {
        case 'won': return 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-emerald-50/50 shadow-sm';
        case 'lost': return 'bg-rose-50 text-rose-600 border-rose-100 shadow-rose-50/50 shadow-sm';
        default: return 'bg-indigo-50 text-indigo-600 border-indigo-100 shadow-indigo-50/50 shadow-sm';
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
</style>
