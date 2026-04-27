<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref } from 'vue';
import axios from 'axios';
import { 
    CloudArrowUpIcon, 
    TableCellsIcon, 
    CheckBadgeIcon, 
    ExclamationTriangleIcon,
    ArrowPathIcon,
    ArrowLeftIcon,
    InformationCircleIcon,
    SparklesIcon,
    BoltIcon,
    DocumentChartBarIcon,
    ArrowRightIcon,
    ArrowUpOnSquareIcon,
    CheckCircleIcon,
    AdjustmentsVerticalIcon,
    GlobeAltIcon
} from '@heroicons/vue/24/solid';

defineOptions({ layout: MainLayout });

const step = ref(1);
const file = ref(null);
const headers = ref([]);
const dbFields = ref({});
const mapping = ref({});
const results = ref(null);
const isProcessing = ref(false);
const uploadedPath = ref('');

const handleFileUpload = (event) => {
    file.value = event.target.files[0];
};

const inspectFileWrapper = async () => {
    if (!file.value) return;
    const formData = new FormData();
    formData.append('file', file.value);
    isProcessing.value = true;
    try {
        const response = await axios.post(route('admin.assets.import.inspect'), formData);
        headers.value = response.data.headers;
        dbFields.value = response.data.db_fields;
        uploadedPath.value = response.data.path; 
        
         // Auto-Map
        const map = {};
        for (const [field, label] of Object.entries(dbFields.value)) {
             const matchedIndex = headers.value.findIndex(h => 
                h.toLowerCase() === field.replace(/_/g, '').toLowerCase() || 
                h.toLowerCase().includes(field.replace(/_/g, ' '))
             );
            map[field] = matchedIndex !== -1 ? matchedIndex : "";
        }
        mapping.value = map;
        step.value = 2;
    } catch (error) {
        alert('Failed to analyze the file');
    } finally {
        isProcessing.value = false;
    }
}

const processImport = async () => {
    isProcessing.value = true;
    try {
        const response = await axios.post(route('admin.assets.import.process'), {
            path: uploadedPath.value,
            mapping: mapping.value
        });
        results.value = response.data;
        step.value = 3;
    } catch (error) {
        console.error(error);
        alert('Import process failed');
    } finally {
        isProcessing.value = false;
    }
};
</script>

<template>
    <Head title="Ingestion Core Terminal" />
    
    <div class="h-screen flex flex-col bg-slate-50 font-outfit overflow-hidden -m-8 p-12 relative animate-in fade-in duration-1000 text-left">
        <!-- AI Grid Background -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808008_1px,transparent_1px),linear-gradient(to_bottom,#80808008_1px,transparent_1px)] bg-[size:40px_40px] pointer-events-none"></div>
        <div class="absolute -right-32 -top-32 w-[800px] h-[800px] bg-indigo-500/5 rounded-full blur-[140px] pointer-events-none"></div>

        <!-- Tactical Command Header -->
        <header class="flex items-center justify-between mb-10 bg-white rounded-3xl p-8 shadow-sm relative z-20 group border border-slate-200">
            <div class="flex items-center gap-8">
                <Link :href="route('admin.assets.hub')" 
                    class="w-12 h-12 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all active:scale-90 shadow-sm shrink-0">
                    <ArrowLeftIcon class="w-6 h-6" />
                </Link>
                <div class="text-left">
                    <div class="flex items-center gap-6">
                        <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tight leading-none">Ingestion Core</h1>
                        <div class="group/tooltip relative">
                             <InformationCircleIcon class="w-6 h-6 text-indigo-400 cursor-help opacity-70 hover:opacity-100 transition-opacity" />
                        </div>
                    </div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2.5 leading-none">MASS_DATA_INGESTION_v2.0</p>
                </div>
            </div>

            <!-- Ingestion Telemetry (Progress) -->
            <div class="flex items-center gap-8 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                <div v-for="(s, i) in [
                    { id: 1, label: 'UPLOAD', icon: CloudArrowUpIcon },
                    { id: 2, label: 'MAP', icon: AdjustmentsVerticalIcon },
                    { id: 3, label: 'COMMIT', icon: CheckCircleIcon }
                ]" :key="i" class="flex items-center gap-3 group/step">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-700 border"
                        :class="step >= s.id ? 'bg-indigo-600 border-indigo-400 text-white shadow-lg shadow-indigo-500/20' : 'bg-white border-slate-200 text-slate-400'">
                        <component :is="s.icon" class="w-5 h-5" />
                    </div>
                    <span class="text-[9px] font-bold uppercase tracking-widest hidden xl:block transition-colors" :class="step >= s.id ? 'text-slate-900' : 'text-slate-400'">{{ s.label }}</span>
                    <div v-if="i < 2" class="w-8 h-1 bg-slate-200 rounded-full overflow-hidden hidden xl:block">
                         <div class="h-full bg-emerald-500 transition-all duration-1000" :style="{ width: step > s.id ? '100%' : '0%' }"></div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="flex-1 max-w-5xl mx-auto w-full min-h-0 relative z-10 overflow-hidden pb-10">
            
            <Transition name="step-fade" mode="out-in">
                <!-- Step 1: Upload Vector -->
                <div v-if="step === 1" class="h-full flex flex-col items-center justify-center py-10 animate-in zoom-in-95 duration-700">
                    <div class="w-full max-w-2xl bg-white rounded-3xl border border-slate-200 p-16 text-center shadow-sm relative overflow-hidden group/box">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-50/10 via-transparent to-transparent pointer-events-none"></div>
                        
                        <div class="w-20 h-20 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center text-slate-300 mx-auto mb-8 shadow-sm group-hover/box:bg-slate-900 group-hover/box:text-indigo-400 transition-all duration-700">
                             <DocumentChartBarIcon class="w-10 h-10" />
                        </div>
                        
                        <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight mb-3">Source Acquisition</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-10">Target acquisition for CSV / XLSX resource arrays.</p>
                        
                        <div class="w-full max-w-md mx-auto relative group/file">
                            <input type="file" @change="handleFileUpload" accept=".csv" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" />
                            <div class="w-full h-20 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col items-center justify-center group-hover/file:border-indigo-400 group-hover/file:bg-white transition-all shadow-sm overflow-hidden">
                                <div class="flex items-center gap-4">
                                     <ArrowUpOnSquareIcon class="w-6 h-6 text-indigo-400 group-hover/file:scale-110 transition-transform" />
                                     <span class="text-lg font-black text-slate-600 uppercase tracking-tight group-hover/file:text-indigo-600 transition-colors">
                                        {{ file ? Math.min(file.name.length, 20) < file.name.length ? file.name.substring(0, 17) + '...' : file.name : 'BROWSE_DATA_CORE' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-12">
                            <button @click="inspectFileWrapper" :disabled="!file || isProcessing" 
                                class="h-16 px-12 bg-slate-900 text-white rounded-2xl text-[10px] font-bold uppercase tracking-widest hover:bg-indigo-600 transition-all flex items-center justify-center gap-4 shadow-lg active:scale-95 disabled:opacity-30 group/btn border border-slate-800">
                                <ArrowPathIcon v-if="isProcessing" class="w-5 h-5 animate-spin text-indigo-400" />
                                <GlobeAltIcon v-else class="w-5 h-5 text-indigo-400 group-hover/btn:rotate-90 transition-transform" />
                                <span>{{ isProcessing ? 'ANALYZING...' : 'INSPECT_RESOURCE' }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Protocol Mapping -->
                <div v-else-if="step === 2" class="h-full flex flex-col bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm animate-in slide-in-from-bottom-5 duration-1000">
                     <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between text-left">
                         <div>
                             <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Schema Alignment</h2>
                             <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5">Map source columns to matrix identity nodes</p>
                         </div>
                         <div class="px-4 py-2 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center gap-3">
                              <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                              <span class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest">AI_AUTO_MAPPING_ACTIVE</span>
                         </div>
                     </div>
                     
                     <div class="flex-1 overflow-y-auto p-10 no-scrollbar">
                         <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-left">
                             <div v-for="(label, field) in dbFields" :key="field" 
                                 class="bg-white border border-slate-100 p-6 rounded-2xl shadow-sm hover:shadow-lg hover:border-indigo-400 hover:-translate-y-1 transition-all duration-500 group/field">
                                  <div class="flex items-center gap-3 mb-4">
                                      <div class="w-8 h-8 bg-slate-50 border border-slate-100 rounded-lg flex items-center justify-center text-slate-300 group-hover/field:bg-slate-900 group-hover/field:text-indigo-400 transition-colors">
                                          <AdjustmentsVerticalIcon class="w-5 h-5" />
                                      </div>
                                      <label class="text-[9px] font-bold text-slate-500 uppercase tracking-widest group-hover/field:text-slate-900 transition-colors">{{ label }}</label>
                                  </div>
                                  
                                  <div class="relative">
                                     <select v-model="mapping[field]" class="w-full h-12 bg-slate-50 border border-slate-200 rounded-xl px-4 text-xs font-bold text-slate-900 uppercase tracking-widest focus:bg-white focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-400 transition-all appearance-none cursor-pointer font-mono">
                                         <option value="">IGNORE_NODE</option>
                                         <option v-for="(header, index) in headers" :key="index" :value="index">
                                             #{{ index + 1 }} {{ header.toUpperCase() }}
                                         </option>
                                     </select>
                                     <div class="absolute right-3 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full transition-all" :class="mapping[field] !== '' ? 'bg-emerald-500 scale-125' : 'bg-slate-300 opacity-20'"></div>
                                  </div>
                             </div>
                         </div>
                     </div>
                     
                     <div class="p-8 border-t border-slate-100 bg-slate-50 flex flex-col sm:flex-row justify-between items-center gap-6">
                         <button @click="step = 1" class="h-14 px-8 bg-white border border-slate-200 text-slate-400 rounded-xl text-[9px] font-bold uppercase tracking-widest hover:text-rose-500 hover:border-rose-100 transition-all active:scale-95 flex items-center gap-3">
                             <ArrowLeftIcon class="w-5 h-5" /> ABORT
                         </button>
                         <button @click="processImport" :disabled="isProcessing" 
                            class="h-16 px-10 bg-slate-900 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-emerald-600 shadow-lg active:scale-95 transition-all flex items-center justify-center gap-4 group/run border border-slate-800">
                             <ArrowPathIcon v-if="isProcessing" class="w-5 h-5 animate-spin text-indigo-400" />
                             <CloudArrowUpIcon v-else class="w-5 h-5 text-indigo-400 group-hover/run:-translate-y-1 transition-transform" />
                             <span>{{ isProcessing ? 'COMMITTING...' : 'START_INGESTION' }}</span>
                         </button>
                     </div>
                </div>

                <!-- Step 3: Success Terminal -->
                <div v-else-if="step === 3" class="h-full flex flex-col items-center justify-center py-10 animate-in zoom-in-95 duration-700">
                    <div class="w-full max-w-3xl bg-white rounded-3xl border border-slate-200 p-16 text-center shadow-sm relative overflow-hidden group/success">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,_var(--tw-gradient-stops))] from-emerald-50/10 via-transparent to-transparent pointer-events-none"></div>
                        
                        <div class="w-24 h-24 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center border border-emerald-100 mx-auto mb-8 shadow-sm">
                            <CheckBadgeIcon class="w-12 h-12" />
                        </div>
                        
                        <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight mb-3 leading-tight">Sync Completed</h2>
                        <div class="inline-flex items-center gap-6 px-8 py-3 bg-slate-900 text-white rounded-2xl shadow-xl mb-10 transition-transform group-hover/success:scale-105">
                             <span class="text-3xl font-black leading-none tabular-nums">{{ results.success_count }}</span>
                             <div class="h-8 w-px bg-white/20"></div>
                             <span class="text-[9px] font-bold uppercase tracking-widest text-left">Nodes Successfully<br>Migrated to Matrix</span>
                        </div>
                        
                        <div v-if="results.failed_count > 0" class="mb-10 max-w-md mx-auto p-8 bg-rose-50 rounded-2xl text-left border border-rose-100 relative group/error overflow-hidden shadow-sm">
                             <div class="relative z-10">
                                <div class="flex items-center gap-3 text-rose-600 mb-3">
                                    <ExclamationTriangleIcon class="w-6 h-6" />
                                    <h5 class="text-lg font-black uppercase tracking-tight">Collision Log Active</h5>
                                </div>
                                <p class="text-[10px] font-bold text-rose-500/80 uppercase tracking-widest mb-6 leading-relaxed">
                                    {{ results.failed_count }} resources failed ingestion protocols. Review collision hash below.
                                </p>
                                <a :href="results.error_url" class="w-full h-14 bg-white border border-rose-200 text-rose-600 rounded-xl flex items-center justify-center gap-3 text-[9px] font-bold uppercase tracking-widest hover:bg-rose-50 transition-all shadow-sm">
                                    <CloudArrowUpIcon class="w-4 h-4" /> DOWNLOAD_COLLISION_HASH
                                </a>
                             </div>
                        </div>

                         <div class="flex flex-col sm:flex-row justify-center gap-6 pt-8 border-t border-slate-100">
                             <button @click="step = 1; file = null; results = null;" class="h-14 px-10 bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 rounded-xl text-[9px] font-bold uppercase tracking-widest transition-all shadow-sm active:scale-95">
                                 ACQUIRE_NEW_VECTOR
                             </button>
                             <Link :href="route('admin.assets.index')" class="h-16 px-12 bg-slate-900 text-white rounded-2xl text-[10px] font-bold uppercase tracking-widest hover:bg-emerald-600 shadow-lg active:scale-95 transition-all flex items-center justify-center gap-4 group/fin border border-slate-800">
                                 <span>SEE_MATRIX_LIST</span> <ArrowRightIcon class="w-5 h-5 text-indigo-400 group-hover/fin:translate-x-1 transition-transform" />
                             </Link>
                         </div>
                    </div>
                </div>
            </Transition>
        </main>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(15, 23, 42, 0.05);
    border-radius: 20px;
}

.step-fade-enter-active, .step-fade-leave-active {
    transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.step-fade-enter-from { opacity: 0; transform: translateY(20px); }
.step-fade-leave-to { opacity: 0; transform: translateY(-20px); }

.animate-bounce-slow {
    animation: bounce 3s infinite;
}

.shadow-3xl {
    box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.05);
}
</style>
