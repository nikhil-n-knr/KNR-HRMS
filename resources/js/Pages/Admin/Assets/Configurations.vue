<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Cog8ToothIcon, 
    SquaresPlusIcon, 
    ChartBarSquareIcon, 
    QueueListIcon, 
    PlusIcon,
    TrashIcon,
    CheckCircleIcon,
    SparklesIcon
} from '@heroicons/vue/24/outline';

defineOptions({ layout: MainLayout });

const props = defineProps({
    categories: Array,
    initialConfigs: Object
});

const activeTab = ref('attributes'); 
const selectedCategory = ref(props.categories[0] || null);

// --- Form Initialization ---
const attributeForm = useForm({
    custom_attributes: [],
    useful_life_years: null,
    depreciation_method: 'Straight_Line',
    scrap_value_percent: 0
});

const selectCategoryForEdit = (category) => {
    selectedCategory.value = category;
    attributeForm.custom_attributes = category.custom_attributes || [];
    attributeForm.useful_life_years = category.useful_life_years;
    attributeForm.depreciation_method = category.depreciation_method || 'Straight_Line';
    attributeForm.scrap_value_percent = category.scrap_value_percent || 0;
};

if (selectedCategory.value) {
    selectCategoryForEdit(selectedCategory.value);
}

const addAttribute = () => {
    attributeForm.custom_attributes.push({ name: '', type: 'text', required: false });
};

const removeAttribute = (index) => {
    attributeForm.custom_attributes.splice(index, 1);
};

const saveCategoryConfig = () => {
    attributeForm.put(route('assets.categories.config.update', selectedCategory.value.id), {
        onSuccess: () => {
             // System feedback implied
        }
    });
};

const tabs = [
    { id: 'attributes', label: 'Dynamic Schemas', icon: SquaresPlusIcon },
    { id: 'depreciation', label: 'Financial Rules', icon: ChartBarSquareIcon },
    { id: 'workflows', label: 'Approval Chains', icon: QueueListIcon }
];
</script>

<template>
    <Head title="Architectural configuration" />
    <MainLayout>
        <div class="max-w-[1600px] mx-auto space-y-10 pb-20 font-outfit animate-in fade-in slide-in-from-bottom-5 duration-700">
            <!-- Strategic Header Terminal -->
            <div class="bg-slate-900 rounded-[3rem] p-10 md:p-14 border border-slate-800 shadow-2xl shadow-indigo-500/20 relative overflow-hidden group">
                <div class="absolute -right-32 -top-32 w-96 h-96 bg-indigo-500/10 rounded-full blur-[100px] group-hover:bg-indigo-500/20 transition-all duration-1000"></div>
                <div class="absolute -left-16 bottom-0 w-64 h-64 bg-emerald-500/10 rounded-full blur-[80px] group-hover:scale-125 transition-transform duration-1000"></div>

                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl flex items-center justify-center text-indigo-400 shadow-xl group-hover:rotate-45 transition-transform duration-700">
                            <Cog8ToothIcon class="w-8 h-8" />
                        </div>
                        <div>
                            <h1 class="text-3xl font-black text-white uppercase tracking-tight">Configuration Hub</h1>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] mt-2">Manage dynamic schemas, depreciation & matrix workflows</p>
                        </div>
                    </div>
                </div>

                <!-- Strategic Navigation Matrix -->
                <div class="mt-12 flex gap-4 overflow-x-auto no-scrollbar relative z-10">
                    <button 
                        v-for="t in tabs" 
                        :key="t.id"
                        @click="activeTab = t.id"
                        class="h-14 px-8 rounded-2xl text-sm font-black uppercase tracking-[0.3em] transition-all flex items-center gap-3 shrink-0"
                        :class="activeTab === t.id ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-500/30 scale-105' : 'bg-white/5 text-slate-400 border border-white/10 hover:bg-white/10 hover:text-white'"
                    >
                        <component :is="t.icon" class="w-5 h-5 flex-shrink-0" :class="activeTab === t.id ? 'text-indigo-200' : ''" />
                        {{ t.label }}
                    </button>
                </div>
            </div>

            <!-- Configuration Workbench Array -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Sidebar: Node Class Selector -->
                <div class="lg:col-span-3 bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 p-6 flex flex-col h-[600px] sticky top-8 z-10">
                    <h3 class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] mb-6 px-4">Class Node Array</h3>
                    <div class="flex-1 overflow-y-auto pr-2 pb-4 space-y-3 custom-scrollbar">
                        <button 
                            v-for="cat in categories" 
                            :key="cat.id"
                            @click="selectCategoryForEdit(cat)"
                            class="w-full text-left p-5 rounded-2xl text-base font-black uppercase tracking-widest transition-all shadow-sm border"
                            :class="selectedCategory?.id === cat.id ? 'bg-slate-900 border-slate-900 text-white scale-[1.02]' : 'bg-slate-50 border-slate-100 text-slate-600 hover:bg-white hover:border-slate-200'"
                        >
                            {{ cat.name }}
                            <div v-if="selectedCategory?.id === cat.id" class="text-xs text-emerald-400 mt-2 tracking-[0.4em]">ACTIVE_TARGET</div>
                        </button>
                    </div>
                </div>

                <!-- Operations Terminal -->
                <div class="lg:col-span-9 space-y-8">
                    
                    <!-- Dynamic Attributes Schema -->
                    <div v-if="activeTab === 'attributes'" class="bg-white rounded-[3rem] border border-slate-100 shadow-2xl shadow-slate-200/40 p-10 md:p-12 relative overflow-hidden group/form animate-in slide-in-from-right-10 duration-500">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
                            <div>
                                <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                                    Entity Properties
                                    <span class="px-3 py-1 bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-lg text-sm font-black uppercase tracking-widest shadow-sm">
                                        {{ selectedCategory?.name }}
                                    </span>
                                </h2>
                                <p class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] mt-2">Design custom data matrices for this class</p>
                            </div>
                            <button @click="saveCategoryConfig" :disabled="attributeForm.processing" class="h-14 px-8 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.3em] shadow-xl hover:bg-indigo-600 transition-all active:scale-95 flex items-center justify-center gap-3 w-full md:w-auto">
                                <CheckCircleIcon class="w-5 h-5 text-indigo-400" />
                                Commit Schema
                            </button>
                        </div>

                        <div class="space-y-6">
                            <!-- Empty State -->
                            <div v-if="attributeForm.custom_attributes.length === 0" class="p-12 border-2 border-dashed border-slate-200 rounded-[2rem] bg-slate-50 text-center relative group/empty overflow-hidden">
                                <SquaresPlusIcon class="w-16 h-16 text-slate-300 mx-auto mb-6 group-hover/empty:scale-110 group-hover/empty:text-indigo-400 transition-all duration-500" />
                                <h3 class="text-sm font-black text-slate-600 uppercase tracking-widest mb-2">Virgin Schema Detected</h3>
                                <p class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] mb-8">Inject custom metadata fields into the registry architecture</p>
                                <button @click="addAttribute" class="h-12 px-8 bg-white border-2 border-slate-200 text-slate-600 rounded-xl text-sm font-black uppercase tracking-widest hover:border-indigo-400 hover:text-indigo-600 shadow-sm active:scale-95 transition-all inline-flex items-center gap-2">
                                    <PlusIcon class="w-4 h-4" /> Synthesize Field
                                </button>
                            </div>

                            <!-- Schema Builder Rows -->
                            <div v-for="(attr, index) in attributeForm.custom_attributes" :key="index" class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100 flex flex-col md:flex-row items-end gap-6 shadow-sm relative group/row">
                                <div class="absolute -left-2 top-1/2 -translate-y-1/2 w-1 h-12 bg-indigo-500 rounded-r opacity-0 group-hover/row:opacity-100 transition-opacity"></div>
                                
                                <div class="flex-1 w-full space-y-3">
                                    <label class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] px-2">Data Label</label>
                                    <input v-model="attr.name" type="text" class="w-full h-14 bg-white border border-slate-200 rounded-2xl px-6 text-base font-black text-slate-900 uppercase tracking-widest focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm" placeholder="E.G. RAM CAPACITY">
                                </div>
                                <div class="w-full md:w-56 space-y-3">
                                    <label class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] px-2">Data Type</label>
                                    <select v-model="attr.type" class="w-full h-14 bg-white border border-slate-200 rounded-2xl px-6 text-base font-black text-slate-900 uppercase tracking-widest focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm appearance-none cursor-pointer">
                                        <option value="text">String / Text</option>
                                        <option value="number">Numeric Float</option>
                                        <option value="date">Temporal Date</option>
                                        <option value="boolean">Binary (Yes/No)</option>
                                    </select>
                                </div>
                                <div class="w-full md:w-auto shrink-0 pb-1">
                                    <button @click="removeAttribute(index)" class="w-full md:w-12 h-14 flex items-center justify-center bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white rounded-2xl border border-rose-100 transition-all shadow-sm group/del active:scale-95">
                                        <TrashIcon class="w-5 h-5 group-hover/del:scale-110 transition-transform" />
                                    </button>
                                </div>
                            </div>

                            <button v-if="attributeForm.custom_attributes.length > 0" @click="addAttribute" class="w-full h-16 bg-slate-50 border-2 border-dashed border-slate-200 text-slate-400 rounded-[2rem] text-sm font-black uppercase tracking-[0.3em] hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-600 transition-all flex items-center justify-center gap-3">
                                <PlusIcon class="w-5 h-5" /> Append Vector
                            </button>
                        </div>
                    </div>

                    <!-- Financial Simulation & Depreciation Rules -->
                    <div v-if="activeTab === 'depreciation'" class="bg-white rounded-[3rem] border border-slate-100 shadow-2xl shadow-slate-200/40 p-10 md:p-12 relative overflow-hidden group/fin animate-in slide-in-from-right-10 duration-500">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
                            <div>
                                <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                                    Asset Depreciation Logistics
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg text-sm font-black uppercase tracking-[0.2em] shadow-sm">
                                        {{ selectedCategory?.name }}
                                    </span>
                                </h2>
                                <p class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] mt-2">Adjust corporate valuation descent variables</p>
                            </div>
                            <button @click="saveCategoryConfig" :disabled="attributeForm.processing" class="h-14 px-8 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.3em] shadow-xl hover:bg-emerald-600 transition-all active:scale-95 flex items-center justify-center gap-3 w-full md:w-auto">
                                <CheckCircleIcon class="w-5 h-5 text-emerald-400" />
                                Save Algorithm
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                            <div class="space-y-3 md:col-span-2">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-widest px-2">Math Model</label>
                                <select v-model="attributeForm.depreciation_method" class="w-full h-16 bg-slate-50 border-2 border-slate-100 rounded-[2rem] px-6 text-base font-black text-slate-900 uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all shadow-sm appearance-none cursor-pointer">
                                    <option value="Straight_Line">Straight Line Depreciation (SLM)</option>
                                    <option value="Double_Declining_Balance">Double Declining Balance Matrix</option>
                                    <option value="Sum_of_Years_Digits">Sum of Years Digits Simulation</option>
                                </select>
                            </div>

                            <div class="space-y-3">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-widest px-2">Expected Life Span (Years)</label>
                                <input v-model="attributeForm.useful_life_years" type="number" step="0.5" class="w-full h-16 bg-slate-50 border-2 border-slate-100 rounded-[2rem] px-6 text-[14px] font-black text-slate-900 font-mono tracking-tighter focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all shadow-inner">
                            </div>

                            <div class="space-y-3">
                                <label class="text-sm font-black text-slate-500 uppercase tracking-widest px-2">Scrap Threshold (%)</label>
                                <div class="relative">
                                    <span class="absolute right-6 top-1/2 -translate-y-1/2 text-base font-black text-slate-400">%</span>
                                    <input v-model="attributeForm.scrap_value_percent" type="number" step="0.1" class="w-full h-16 bg-slate-50 border-2 border-slate-100 rounded-[2rem] pl-6 pr-12 text-[14px] font-black text-slate-900 font-mono tracking-tighter focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all shadow-inner border-opacity-60">
                                </div>
                            </div>
                        </div>

                        <!-- Impact Simulator Card -->
                        <div class="bg-slate-900 rounded-[2.5rem] p-8 md:p-10 text-white shadow-2xl relative overflow-hidden group/sim">
                            <div class="absolute -right-20 -top-20 w-80 h-80 bg-emerald-500/10 rounded-full blur-[80px] group-hover/sim:bg-emerald-500/20 transition-all duration-1000"></div>
                            
                            <div class="relative z-10 flex gap-6">
                                <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center border border-white/20 shrink-0 shadow-xl group-hover/sim:rotate-12 transition-transform">
                                    <SparklesIcon class="w-8 h-8 text-emerald-400" />
                                </div>
                                <div class="space-y-4 pt-1">
                                    <h4 class="text-sm font-black uppercase tracking-[0.4em] text-emerald-400 flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
                                        Live Trajectory Simulation
                                    </h4>
                                    <p class="text-sm font-medium leading-relaxed text-slate-300">
                                        Assuming a baseline <span class="text-white font-mono font-bold tracking-tight bg-white/10 px-2 py-0.5 rounded">₹1,000,000</span> procurement load injected into a 
                                        <span class="text-white font-mono font-bold tracking-tight bg-white/10 px-2 py-0.5 rounded">{{ attributeForm.useful_life_years || 'X' }}</span> 
                                        standard cycles matrix:
                                    </p>
                                    <div class="mt-4 p-5 bg-black/40 rounded-2xl border border-white/10 flex items-center justify-between">
                                        <span class="text-sm font-black text-slate-400 uppercase tracking-widest">Calculated Structural Degradation</span>
                                        <span class="text-2xl font-black font-mono text-emerald-400 tracking-tighter shadow-md">
                                            ₹{{ ((1000000 * (1 - (attributeForm.scrap_value_percent/100))) / (attributeForm.useful_life_years || 5)).toFixed(2).toLocaleString() }} <span class="text-xs text-slate-500">/ Cycle</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Workflows Placeholder -->
                    <div v-if="activeTab === 'workflows'" class="bg-white rounded-[3rem] border border-slate-100 shadow-2xl shadow-slate-200/40 p-20 flex flex-col items-center justify-center text-center animate-in zoom-in-95 duration-500 relative overflow-hidden">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-slate-50 via-transparent to-transparent"></div>
                        <div class="w-24 h-24 bg-slate-900 rounded-[2rem] flex items-center justify-center text-white shadow-2xl mb-8 relative z-10">
                            <QueueListIcon class="w-10 h-10 text-indigo-400 animate-pulse" />
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-4 relative z-10">Approval Protocol Matrix</h3>
                        <div class="h-1.5 w-20 bg-gradient-to-r from-slate-300 to-slate-400 rounded-full mb-6 shadow-sm relative z-10"></div>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] max-w-sm leading-relaxed relative z-10">
                            The visual rules engine for acquisition and assignment authorizations is currently compiling. Future iteration deployment confirmed.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
