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
    ArrowLeftIcon
} from '@heroicons/vue/24/outline';

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
        const response = await axios.post(route('assets.import.inspect'), formData);
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
        alert('Failed to analyze Source Registry');
    } finally {
        isProcessing.value = false;
    }
}

const processImport = async () => {
    isProcessing.value = true;
    try {
        const response = await axios.post(route('assets.import.process'), {
            path: uploadedPath.value,
            mapping: mapping.value
        });
        results.value = response.data;
        step.value = 3;
    } catch (error) {
        console.error(error);
        alert('Migration Protocol Failed');
    } finally {
        isProcessing.value = false;
    }
};
</script>

<template>
    <Head title="Strategic Uplink" />
    <MainLayout>
        <div class="max-w-[1200px] mx-auto space-y-10 pb-20 font-outfit animate-in fade-in slide-in-from-bottom-5 duration-700">
            <!-- Header Terminal -->
            <div class="bg-slate-900 rounded-[3rem] p-10 md:p-14 border border-slate-800 shadow-2xl relative overflow-hidden group">
                <div class="absolute -right-32 -top-32 w-96 h-96 bg-indigo-500/10 rounded-full blur-[100px] group-hover:bg-indigo-500/20 transition-all duration-1000"></div>
                <div class="absolute -left-16 bottom-0 w-64 h-64 bg-emerald-500/10 rounded-full blur-[80px] group-hover:scale-125 transition-transform duration-1000"></div>

                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl flex items-center justify-center text-indigo-400 shadow-xl group-hover:-translate-y-1 transition-transform">
                            <CloudArrowUpIcon class="w-8 h-8" />
                        </div>
                        <div>
                            <h1 class="text-3xl font-black text-white uppercase tracking-tight">Strategic Uplink</h1>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] mt-2">Migrate legacy matrices into the network</p>
                        </div>
                    </div>
                </div>

                <!-- Telemetry Progress Bar -->
                <div class="mt-12 relative z-10 pt-4 border-t border-white/10">
                    <div class="flex justify-between relative max-w-2xl mx-auto">
                        <div class="absolute top-1/2 left-0 w-full h-1 bg-white/5 -translate-y-1/2 rounded-full"></div>
                        <div class="absolute top-1/2 left-0 h-1 bg-emerald-500 -translate-y-1/2 rounded-full transition-all duration-1000 shadow-[0_0_10px_rgba(16,185,129,0.5)]" :style="{ width: step === 1 ? '0%' : step === 2 ? '50%' : '100%' }"></div>
                        
                        <div v-for="(s, i) in [1, 2, 3]" :key="i" class="w-12 h-12 rounded-2xl flex items-center justify-center text-base font-black relative z-10 transition-all duration-500 border-2"
                             :class="step > i ? 'bg-emerald-500 text-white border-emerald-400 scale-110 shadow-lg' : step === s ? 'bg-indigo-600 text-white border-indigo-400 scale-110 shadow-[0_0_20px_rgba(79,70,229,0.5)]' : 'bg-slate-900 border-slate-700 text-slate-500'">
                             {{ s }}
                        </div>
                    </div>
                    <div class="flex justify-between max-w-2xl mx-auto mt-4 px-2">
                        <span class="text-xs font-black uppercase tracking-[0.2em]" :class="step >= 1 ? 'text-emerald-400' : 'text-slate-500'">1. Prepare Vector</span>
                        <span class="text-xs font-black uppercase tracking-[0.2em]" :class="step >= 2 ? 'text-indigo-400' : 'text-slate-500'">2. Map Schema</span>
                        <span class="text-xs font-black uppercase tracking-[0.2em]" :class="step === 3 ? 'text-emerald-400' : 'text-slate-500'">3. Resolve</span>
                    </div>
                </div>
            </div>

            <div class="min-h-[400px]">
                <!-- Step 1: Upload Vector -->
                <div v-if="step === 1" class="bg-white rounded-[3rem] p-12 md:p-16 border border-slate-100 shadow-xl shadow-slate-200/50 text-center animate-in zoom-in-95 duration-500 relative overflow-hidden group">
                    <div class="w-24 h-24 bg-slate-50 border-2 border-slate-100 rounded-[2rem] flex items-center justify-center text-slate-400 mx-auto mb-8 group-hover:bg-indigo-50 group-hover:text-indigo-500 group-hover:border-indigo-100 transition-all duration-500 shadow-inner group-hover:-translate-y-2">
                         <TableCellsIcon class="w-10 h-10" />
                    </div>
                    
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-4">Mount Data Matrix (.CSV)</h2>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] mb-10 max-w-sm mx-auto">Select a valid comma-separated vector payload for decryption.</p>
                    
                    <div class="w-full max-w-md mx-auto relative group/file">
                        <input type="file" @change="handleFileUpload" accept=".csv" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                        <div class="w-full h-20 bg-slate-50 border-2 border-slate-200 border-dashed rounded-[2rem] flex flex-col items-center justify-center group-hover/file:border-indigo-400 group-hover/file:bg-indigo-50/30 transition-all">
                            <span class="text-base font-black text-slate-600 uppercase tracking-widest group-hover/file:text-indigo-600 transition-colors">
                                {{ file ? file.name : 'BROWSE SYSTEM DIRECTORY' }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-12">
                        <button @click="inspectFileWrapper" :disabled="!file || isProcessing" class="h-16 px-14 mx-auto bg-slate-900 text-white rounded-[2rem] text-sm font-black uppercase tracking-[0.3em] hover:bg-indigo-600 transition-all flex items-center justify-center gap-4 shadow-xl shadow-slate-900/20 active:scale-95 group/btn disabled:opacity-50 disabled:cursor-not-allowed">
                            <ArrowPathIcon v-if="isProcessing" class="w-5 h-5 text-indigo-400 animate-spin" />
                            <CloudArrowUpIcon v-else class="w-5 h-5 text-indigo-400 group-hover/btn:-translate-y-1 transition-transform" />
                            <span>{{ isProcessing ? 'DECRYPTING...' : 'INITIATE ANALYSIS' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Map Schema -->
                <div v-if="step === 2" class="bg-white rounded-[3rem] p-10 md:p-14 border border-slate-100 shadow-xl shadow-slate-200/50 animate-in slide-in-from-right-10 duration-500">
                     <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-8 pb-6 border-b border-slate-100 flex items-center justify-between">
                         <span class="flex items-center gap-3">
                             <TableCellsIcon class="w-6 h-6 text-indigo-500" /> Transform Schema Array
                         </span>
                         <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-xs uppercase tracking-widest rounded-lg border border-emerald-100">Auto-Map Enabled</span>
                     </h2>
                     
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative">
                         <div v-for="(label, field) in dbFields" :key="field" class="flex flex-col bg-slate-50 p-6 rounded-[2rem] border border-slate-100 shadow-sm relative group/map hover:border-indigo-200 hover:shadow-md transition-all">
                             
                             <label class="text-sm font-black text-slate-500 uppercase tracking-[0.3em] mb-3 px-1">Target Element: <span class="text-slate-900">{{ label }}</span></label>
                             
                             <div class="relative w-full">
                                 <select v-model="mapping[field]" class="w-full h-14 bg-white border border-slate-200 rounded-2xl px-5 text-sm font-black text-slate-700 uppercase tracking-widest focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                     <option value="" disabled>OMIT VECTOR</option>
                                     <option v-for="(header, index) in headers" :key="index" :value="index" class="py-2">
                                         COL {{ index + 1 }}: {{ header }}
                                     </option>
                                 </select>
                                 <div class="absolute right-4 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full" :class="mapping[field] !== '' ? 'bg-emerald-400' : 'bg-rose-300'"></div>
                             </div>
                         </div>
                     </div>
                     
                     <div class="mt-12 pt-8 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-6">
                         <button @click="step = 1" class="h-14 px-8 bg-white border-2 border-slate-200 text-slate-500 rounded-2xl text-sm font-black uppercase tracking-[0.3em] hover:text-slate-700 hover:bg-slate-50 active:scale-95 transition-all w-full sm:w-auto flex items-center justify-center gap-2">
                             <ArrowLeftIcon class="w-4 h-4" /> Go Back
                         </button>
                         <button @click="processImport" :disabled="isProcessing" class="h-14 px-10 bg-indigo-600 text-white rounded-2xl text-sm font-black uppercase tracking-[0.3em] hover:bg-emerald-500 shadow-xl shadow-indigo-500/20 active:scale-95 transition-all w-full sm:w-auto flex items-center justify-center gap-3 group/run">
                             <ArrowPathIcon v-if="isProcessing" class="w-4 h-4 animate-spin text-white" />
                             <CloudArrowUpIcon v-else class="w-4 h-4 text-indigo-200 group-hover/run:-translate-y-0.5 transition-transform" />
                              <span>{{ isProcessing ? 'Executing...' : 'Commit Protocol' }}</span>
                         </button>
                     </div>
                </div>

                <!-- Step 3: Resolution -->
                <div v-if="step === 3" class="bg-white p-12 md:p-16 rounded-[3rem] border border-slate-100 shadow-2xl shadow-indigo-500/10 text-center animate-in zoom-in-95 duration-700">
                    <div class="w-24 h-24 bg-emerald-50 text-emerald-500 rounded-[2rem] flex items-center justify-center border-4 border-emerald-100 mx-auto mb-8 shadow-inner animate-bounce-slow">
                        <CheckBadgeIcon class="w-12 h-12" />
                    </div>
                    
                    <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight mb-2">Protocol Complete</h2>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] mb-10">
                        Synthesized <span class="text-emerald-500">{{ results.success_count }}</span> active nodes from payload.
                    </p>
                    
                    <div v-if="results.failed_count > 0" class="mb-10 max-w-sm mx-auto p-6 bg-rose-50 border border-rose-100 rounded-3xl text-left shadow-sm">
                        <div class="flex items-center gap-3 text-base font-black text-rose-800 uppercase tracking-widest mb-3">
                            <ExclamationTriangleIcon class="w-5 h-5 text-rose-500" />
                            {{ results.failed_count }} Deviations Logged
                        </div>
                        <p class="text-sm font-medium leading-relaxed text-rose-600/80 mb-4 italic">
                            Anomalies detected in payload logic causing rejection of certain nodes.
                        </p>
                        <a :href="results.error_url" class="inline-flex items-center gap-2 h-10 px-5 bg-white border border-rose-200 text-rose-600 rounded-xl text-sm font-black uppercase tracking-widest hover:border-rose-400 hover:bg-rose-50 transition-all shadow-sm">
                            Extract Error Matrix
                        </a>
                    </div>
                    <div v-else class="mb-10 inline-block px-5 py-2 bg-slate-50 border border-slate-100 rounded-xl text-sm font-black uppercase tracking-[0.3em] text-slate-400 italic">
                        No deviations detected. Perfect execution.
                    </div>

                     <div class="flex flex-col sm:flex-row justify-center gap-4">
                         <button @click="step = 1; file = null; results = null;" class="h-14 px-8 bg-white border-2 border-slate-200 text-slate-500 hover:text-indigo-600 hover:border-indigo-200 rounded-2xl text-sm font-black uppercase tracking-[0.3em] transition-all shadow-sm active:scale-95 shrink-0">
                             Mount New Vector
                         </button>
                         <Link :href="route('admin.assets.dashboard', { view: 'list' })" class="h-14 px-10 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.3em] hover:bg-emerald-600 shadow-xl shadow-slate-900/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                             Access Registry <ArrowRightIcon class="w-4 h-4" />
                         </Link>
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
.animate-bounce-slow {
    animation: bounce 3s infinite;
}
</style>
