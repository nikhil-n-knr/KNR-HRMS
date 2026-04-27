<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import { 
    CloudArrowUpIcon, 
    DocumentChartBarIcon, 
    XMarkIcon,
    ArrowLeftIcon,
    InformationCircleIcon,
    TableCellsIcon,
    CheckCircleIcon,
    CpuChipIcon,
    ArrowUpTrayIcon,
    ArrowPathIcon,
    BoltIcon,
    SparklesIcon,
    DocumentTextIcon,
    ArrowDownTrayIcon,
    CloudIcon,
    ArrowDownOnSquareIcon
} from '@heroicons/vue/24/solid';

defineOptions({ layout: MainLayout });

const form = useForm({
    file: null
});

const handleFile = (e) => {
    form.file = e.target.files[0];
};

const handleDrop = (e) => {
    form.file = e.dataTransfer.files[0];
};

const submit = () => {
    if (!form.file) return;
    form.post(route('admin.assets.import.store'));
};
</script>

<template>
    <Head title="Add Many Items" />
    <div class="h-screen flex flex-col bg-slate-50 font-outfit overflow-hidden -m-8 p-12 relative text-left">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-500/5 via-transparent to-transparent pointer-events-none"></div>

        <!-- Strategic Header Terminal -->
        <div class="bg-white px-10 py-8 flex flex-shrink-0 justify-between items-center z-10 relative overflow-hidden rounded-3xl border border-slate-200 mb-10 shadow-sm">
            <div class="absolute -right-32 -top-32 w-80 h-80 bg-indigo-50 rounded-full blur-[100px]"></div>
            
            <div class="relative z-10 flex items-center gap-8">
                <Link :href="route('admin.assets.dashboard', { view: 'list' })" class="w-12 h-12 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm active:scale-90 shrink-0">
                    <ArrowLeftIcon class="w-6 h-6" />
                </Link>
                <div class="text-left">
                    <div class="flex items-center gap-6">
                        <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tight leading-none">Add Many Items</h1>
                        <div class="group/tooltip relative flex items-center">
                            <InformationCircleIcon class="w-6 h-6 text-indigo-400 cursor-help opacity-70 hover:opacity-100 transition-opacity" />
                            <div class="absolute left-full ml-6 top-1/2 -translate-y-1/2 w-80 bg-slate-900 text-white text-[11px] font-bold px-6 py-4 rounded-2xl opacity-0 invisible group-hover/tooltip:opacity-100 group-hover/tooltip:visible transition-all shadow-xl z-50 pointer-events-none border border-white/10 leading-relaxed">
                                Upload a CSV or Excel file to register hundreds of devices in a single click.
                            </div>
                        </div>
                    </div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2.5 leading-none">Bulk Resource Ingestion Terminal</p>
                </div>
            </div>

            <div class="flex items-center gap-6 z-10">
                 <div class="px-5 py-2 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center gap-3 shadow-sm">
                    <BoltIcon class="w-4 h-4 text-emerald-500" />
                    <span class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest">Swift Sync Grid</span>
                 </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto no-scrollbar pb-12 relative z-10">
            <div class="max-w-4xl mx-auto space-y-10">
                <!-- Instruction Matrix -->
                <div class="bg-white rounded-3xl p-10 border border-slate-200 shadow-sm relative overflow-hidden group">
                    <div class="absolute -right-24 -top-24 w-80 h-80 bg-indigo-50 rounded-full blur-[100px] opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    
                    <div class="flex items-center justify-between mb-10 border-b border-slate-100 pb-8 transition-all">
                        <div class="flex items-center gap-8">
                            <div class="w-14 h-14 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-center text-slate-400 shadow-sm shrink-0 group-hover:rotate-6 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-700">
                                <DocumentTextIcon class="w-7 h-7" />
                            </div>
                            <div class="text-left">
                                <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight leading-none">Pre-Sync Setup</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2.5 leading-none font-outfit">Mandatory schema verification protocol.</p>
                            </div>
                        </div>
                        <a href="#" class="h-14 px-8 bg-white border border-slate-200 text-slate-400 rounded-xl text-[9px] font-bold uppercase tracking-widest hover:text-indigo-600 hover:border-indigo-100 transition-all active:scale-95 flex items-center gap-4 shadow-sm group/dl">
                            <ArrowDownOnSquareIcon class="w-5 h-5 text-indigo-400 group-hover/dl:translate-y-1 transition-transform" />
                            Download Schema
                        </a>
                    </div>

                    <div class="bg-slate-50 p-8 rounded-2xl relative overflow-hidden group/code shadow-inner border border-slate-100">
                         <div class="flex flex-wrap gap-4">
                            <span v-for="col in ['Item_Name', 'Serial_No', 'Type_ID', 'Unit_Cost', 'Buy_Date']" :key="col" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-[9px] font-bold text-indigo-600 font-mono tracking-widest uppercase transition-all group-hover/code:-translate-y-1 group-hover/code:shadow-md">
                                {{ col.toUpperCase() }}
                            </span>
                         </div>
                         <p class="mt-8 text-[9px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-3">
                            <SparklesIcon class="w-4 h-4 text-yellow-400" />
                            Ensure column headers match exactly to prevent sync drift.
                         </p>
                    </div>
                </div>

                <!-- Dropzone Terminal -->
                <div 
                    @dragover.prevent 
                    @drop.prevent="handleDrop"
                    class="bg-white rounded-3xl border-4 border-dashed p-16 border-slate-100 hover:border-indigo-400 hover:bg-white transition-all duration-700 flex flex-col items-center justify-center text-center shadow-sm relative min-h-[400px]"
                    :class="form.file ? 'border-emerald-400 bg-emerald-50/10' : ''"
                >
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-indigo-50/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>

                    <div v-if="!form.file" class="flex flex-col items-center animate-in fade-in zoom-in duration-700">
                        <div class="w-24 h-24 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center text-slate-200 shadow-lg mb-10 hover:rotate-6 transition-transform shrink-0 group">
                            <CloudIcon class="w-12 h-12 group-hover:text-indigo-400 transition-colors" />
                        </div>
                        <h4 class="text-3xl font-black text-slate-900 uppercase tracking-tight leading-none mb-3">Feed the Matrix</h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-10 max-w-sm leading-relaxed">Drag your resource list here or use the manual toggle below.</p>
                        
                        <label class="h-16 px-12 bg-slate-900 text-white rounded-2xl text-[10px] font-bold uppercase tracking-widest shadow-lg hover:bg-indigo-600 transition-all flex items-center gap-6 active:scale-95 cursor-pointer border border-slate-800">
                            <PlusIcon class="w-6 h-6 text-indigo-400" />
                            Pick Resource List
                            <input type="file" @change="handleFile" class="hidden" accept=".csv,.xlsx">
                        </label>
                    </div>

                    <div v-else class="flex flex-col items-center animate-in zoom-in duration-500">
                        <div class="w-24 h-24 bg-emerald-600 border-4 border-white rounded-3xl flex items-center justify-center text-white shadow-xl mb-10 group scale-110">
                            <CheckCircleIcon class="w-12 h-12 animate-pulse" />
                        </div>
                        <h4 class="text-3xl font-black text-slate-900 uppercase tracking-tight leading-none mb-3">Read Success</h4>
                        <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-widest mb-12">{{ form.file.name }}</p>

                        <div class="flex items-center gap-6">
                            <button @click="form.file = null" class="h-14 px-10 bg-white border border-slate-200 text-slate-400 rounded-xl text-[9px] font-bold uppercase tracking-widest hover:text-rose-500 hover:border-rose-100 transition-all shadow-sm">Discard</button>
                            <button @click="submit" :disabled="form.processing" class="h-16 px-12 bg-slate-900 text-white rounded-2xl text-[10px] font-bold uppercase tracking-widest shadow-lg hover:bg-emerald-600 transition-all flex items-center gap-6 active:scale-95 disabled:opacity-30 group/save border border-slate-800">
                                <ArrowPathIcon v-if="form.processing" class="w-6 h-6 animate-spin" />
                                <BoltIcon v-else class="w-6 h-6 text-indigo-400 group-hover:rotate-12 transition-transform" />
                                <span>{{ form.processing ? 'Syncing...' : 'Initialize Ingestion' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.shadow-3xl {
    box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.05);
}
.no-scrollbar::-webkit-scrollbar { display: none; }
</style>
