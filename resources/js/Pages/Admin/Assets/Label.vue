<script setup>
import { Link, Head } from '@inertiajs/vue3';
import { 
    PrinterIcon, 
    ArrowLeftIcon,
    ShieldCheckIcon,
    CpuChipIcon
} from '@heroicons/vue/24/outline';

defineProps({
    asset: Object,
    qrCode: String
});

const print = () => {
    window.print();
};
</script>

<template>
    <Head :title="`NODE LABEL: ${asset.serial_number || asset.asset_code}`" />
    
    <div class="min-h-screen bg-slate-900 flex items-center justify-center p-6 font-outfit print:bg-white print:p-0 relative overflow-hidden">
        <!-- Digital Artifact Background (Non-Print) -->
        <div class="absolute inset-0 z-0 print:hidden overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500/10 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-emerald-500/10 rounded-full blur-[80px]"></div>
        </div>

        <!-- Registry Label Card -->
        <div class="bg-white p-10 rounded-[3rem] shadow-2xl shadow-indigo-500/20 border-8 border-slate-800 w-full max-w-[450px] text-center relative z-10 print:shadow-none print:border-none print:w-full print:max-w-none print:rounded-none group/card animate-in zoom-in-95 duration-700">
            
            <div class="mb-8 flex justify-center transform group-hover/card:scale-110 transition-transform duration-500 print:hidden">
                <div class="w-16 h-16 bg-slate-900 rounded-2xl flex items-center justify-center text-white shadow-xl rotate-12">
                   <ShieldCheckIcon class="w-8 h-8" />
                </div>
            </div>
            
            <!-- Print Header -->
            <div class="hidden print:flex justify-center mb-6">
                 <!-- Simple Print Logo -->
                 <ShieldCheckIcon class="w-12 h-12 text-black" />
            </div>

            <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tight mb-2">Corporate Node</h1>
            <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] mb-8 relative">
                If intercepted, surrender to central ops
                <span class="absolute -bottom-4 left-1/2 -translate-x-1/2 w-8 h-1 bg-indigo-500 rounded-full print:bg-black"></span>
            </p>

            <!-- Holographic QR Container -->
            <div class="flex justify-center mb-10 relative">
                 <div class="absolute inset-0 bg-indigo-500/5 blur-2xl rounded-full scale-150 print:hidden"></div>
                 <div v-html="qrCode" class="border-4 border-slate-900 rounded-2xl p-4 bg-white shadow-2xl relative z-10 print:border-2 print:border-black print:rounded-lg print:p-2 [&>svg]:w-48 [&>svg]:h-48 group-hover/card:shadow-indigo-500/20 transition-all"></div>
            </div>

            <!-- Telemetry Details -->
            <div class="text-left border-t-2 border-slate-100 pt-6 space-y-4 print:border-black print:pt-4 print:border-t">
                <div class="flex flex-col sm:flex-row sm:justify-between items-start sm:items-center gap-1">
                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] flex items-center gap-2">
                        <CpuChipIcon class="w-4 h-4 print:hidden" /> Vector ID
                    </span>
                    <span class="font-mono text-lg font-black tracking-tighter text-slate-900 uppercase">{{ asset.serial_number || asset.asset_code }}</span>
                </div>
                 <div class="flex flex-col sm:flex-row sm:justify-between items-start sm:items-center gap-1">
                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">Class</span>
                    <span class="text-xs font-black uppercase tracking-widest text-slate-900 bg-slate-100 px-3 py-1 rounded-lg print:bg-transparent print:p-0">{{ asset.category?.name || 'GENERIC' }}</span>
                </div>
                 <div class="flex flex-col justify-between items-start gap-1">
                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">Designation</span>
                    <span class="text-lg font-black uppercase tracking-tight text-slate-900 leading-none">{{ asset.name }}</span>
                </div>
            </div>

             <!-- Action Interface (Non-Print) -->
             <div class="mt-10 pt-8 border-t border-slate-100 print:hidden space-y-4 relative z-10">
                <button @click="print" class="w-full h-16 bg-slate-900 text-white rounded-[2rem] text-sm font-black uppercase tracking-[0.3em] flex items-center justify-center gap-4 hover:bg-indigo-600 transition-all shadow-2xl shadow-slate-900/30 active:scale-95 group/btn">
                    <PrinterIcon class="w-5 h-5 text-indigo-400 group-hover/btn:-translate-y-1 transition-transform" />
                    Commence Render
                </button>
                <Link :href="route('admin.assets.dashboard', { view: 'list' })" class="inline-flex w-full items-center justify-center gap-2 h-16 rounded-[2rem] border-2 border-slate-100 text-sm font-black uppercase tracking-[0.3em] text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition-all active:scale-95">
                    <ArrowLeftIcon class="w-4 h-4" /> Cancel Process
                </Link>
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    body {
        margin: 0;
        padding: 0;
        background: white !important;
    }
    .print\:hidden {
        display: none !important;
    }
    .print\:flex {
        display: flex !important;
    }
    .print\:border-none {
        border: none !important;
    }
    .print\:shadow-none {
        box-shadow: none !important;
    }
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
</style>
