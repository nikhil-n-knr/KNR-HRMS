<script setup>
import { Link, Head } from '@inertiajs/vue3';
import { 
    PrinterIcon, 
    ArrowLeftIcon,
    ShieldCheckIcon,
    CpuChipIcon,
    ArchiveBoxIcon,
    TagIcon,
    InformationCircleIcon,
    SparklesIcon,
    BoltIcon,
    MapPinIcon,
    ArchiveBoxArrowDownIcon,
    CheckBadgeIcon,
    QrCodeIcon
} from '@heroicons/vue/24/solid';

defineProps({
    asset: Object,
    qrCode: String
});

const print = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Item Sticker: ${asset.serial_number || asset.asset_code}`" />
    
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-12 font-outfit print:bg-white print:p-0 relative overflow-hidden text-center">
        <!-- Digital Artifact Background (Non-Print) -->
        <div class="absolute inset-0 z-0 print:hidden overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-0 w-[60rem] h-[60rem] bg-indigo-500/5 rounded-full blur-[140px]"></div>
            <div class="absolute bottom-0 left-0 w-[50rem] h-[50rem] bg-emerald-500/5 rounded-full blur-[100px]"></div>
        </div>

        <div class="max-w-lg w-full relative z-10 print:w-full print:max-w-none italic">
            <!-- Strategic Helper (Non-Print) -->
            <div class="mb-10 print:hidden animate-in fade-in slide-in-from-top-10 duration-700">
                <div class="inline-flex items-center gap-6 bg-white border border-slate-200 rounded-2xl px-8 py-5 shadow-sm">
                    <InformationCircleIcon class="w-6 h-6 text-indigo-500" />
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Print this tag and affix it to the physical resource.</span>
                </div>
            </div>

            <!-- Registry Label Card -->
            <div class="bg-white p-12 rounded-3xl shadow-sm border border-slate-200 w-full relative print:shadow-none print:border-none print:w-full print:max-w-none print:rounded-none group/card animate-in zoom-in-95 duration-500 overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-50/20 via-transparent to-transparent pointer-events-none print:hidden"></div>
                
                <div class="mb-10 flex justify-center transform group-hover/card:scale-105 transition-transform duration-700 print:hidden">
                    <div class="w-18 h-18 bg-white border border-slate-100 rounded-2xl flex items-center justify-center text-slate-300 shadow-lg group-hover/card:rotate-12 transition-transform duration-700 shrink-0">
                       <ArchiveBoxIcon class="w-10 h-10 text-indigo-500/50" />
                    </div>
                </div>
                
                <!-- Print Header -->
                <div class="hidden print:flex justify-between items-center mb-10 pb-8 border-b-8 border-black text-left">
                     <div class="flex items-center gap-6 italic shrink-0">
                        <TagIcon class="w-12 h-12 text-black shrink-0" />
                        <span class="text-4xl font-extrabold text-black uppercase tracking-tighter italic leading-none">KNR PROPERTY</span>
                     </div>
                     <span class="text-xl font-black text-black italic">2024_REG</span>
                </div>

                <div class="print:hidden">
                    <h1 class="text-4xl font-black text-slate-900 uppercase tracking-tight mb-4 leading-none">KNR STORAGE</h1>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-12 relative leading-none">
                        GLOBAL RESOURCE INVENTORY
                        <span class="absolute -bottom-6 left-1/2 -translate-x-1/2 w-12 h-1.5 bg-indigo-500 rounded-full print:hidden"></span>
                    </p>
                </div>

                <div class="hidden print:block mb-10 border-4 border-black p-6 rounded-2xl text-left">
                     <p class="text-lg font-black text-black uppercase tracking-widest mb-0 leading-none">DO NOT REMOVE. RETURN TO ADMIN OFFICE IF FOUND.</p>
                </div>

                <!-- Holographic QR Container -->
                <div class="flex justify-center mb-12 relative group/qr">
                     <div class="absolute inset-0 bg-indigo-500/10 blur-2xl rounded-full scale-125 print:hidden opacity-0 group-hover/card:opacity-100 transition-opacity duration-700"></div>
                     <div v-html="qrCode" class="border border-slate-100 rounded-3xl p-8 bg-slate-50 shadow-inner relative z-10 print:border-8 print:border-black print:rounded-[2rem] print:p-6 [&>svg]:w-64 [&>svg]:h-64 print:[&>svg]:w-56 print:[&>svg]:h-56 transition-transform duration-700"></div>
                </div>

                <!-- Telemetry Details -->
                <div class="text-left border-t border-slate-100 pt-10 space-y-10 print:border-black print:pt-10 print:border-t-8">
                    <div class="flex flex-col gap-4 text-center print:text-left">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center justify-center print:justify-start gap-3 print:text-black leading-none">
                            <BoltIcon class="w-4 h-4 text-indigo-400 print:hidden" />
                            RESOURCE IDENTIFIER
                        </span>
                        <div class="flex flex-col gap-2">
                            <span class="font-mono text-4xl font-black tracking-tight text-slate-900 uppercase leading-none print:text-black italic">{{ asset.serial_number || asset.asset_code }}</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-10 border-t border-slate-50 pt-8 print:border-black print:border-t-4">
                        <div class="flex flex-col gap-3">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest print:text-black leading-none">Type Matrix</span>
                            <span class="text-sm font-bold uppercase tracking-widest text-slate-950 bg-slate-50 px-4 py-2 rounded-xl border border-slate-100 print:bg-transparent print:p-0 print:border-none print:text-2xl leading-none">{{ asset.category?.name || 'GENERIC_RESOURCE' }}</span>
                        </div>
                        <div class="flex flex-col gap-3 items-end text-right">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest print:text-black leading-none">Status Code</span>
                            <div class="flex items-center gap-3 shrink-0 print:gap-2">
                                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse print:hidden"></div>
                                <span class="text-sm font-bold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-4 py-2 rounded-xl border border-emerald-100 print:text-black print:bg-transparent print:p-0 print:border-none print:text-2xl leading-none">VERIFIED</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Print Trigger Hub -->
            <div class="mt-12 flex items-center justify-center gap-8 print:hidden animate-in fade-in slide-in-from-bottom-10 duration-1000">
                <Link :href="route('admin.assets.dashboard', { view: 'list' })" class="h-16 px-10 bg-white border border-slate-200 text-slate-400 rounded-2xl text-[10px] font-bold uppercase tracking-widest hover:text-rose-500 hover:border-rose-100 transition-all active:scale-95 flex items-center gap-4 shadow-sm">
                    <ArrowLeftIcon class="w-5 h-5" />
                    Discard
                </Link>
                <button @click="print" class="h-18 px-12 bg-slate-900 text-white rounded-2xl text-[11px] font-bold uppercase tracking-widest shadow-lg hover:bg-indigo-600 transition-all flex items-center gap-6 active:scale-95 group border border-slate-800">
                    <PrinterIcon class="w-6 h-6 text-indigo-400 group-hover:scale-110 transition-all" />
                    Print Sticker
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    body * { visibility: hidden; }
    .print\:block, .print\:block * { visibility: visible; }
    .print\:flex, .print\:flex * { visibility: visible; }
    div[class*="min-h-screen"] { min-height: 0 !important; height: auto !important; padding: 0 !important; margin: 0 !important; }
    div[class*="max-w-lg"], .print\:w-full { 
        visibility: visible; 
        position: absolute; 
        left: 0; 
        top: 0; 
        width: 100% !important; 
        max-width: none !important;
    }
    .print\:shadow-none { box-shadow: none !important; }
    .print\:border-none { border: none !important; }
}

.shadow-3xl {
    box-shadow: 0 50px 120px -20px rgba(0, 0, 0, 0.12);
}
</style>
