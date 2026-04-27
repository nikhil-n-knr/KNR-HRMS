<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Html5QrcodeScanner } from 'html5-qrcode';
import { 
    ChevronLeftIcon, 
    QrCodeIcon, 
    CheckCircleIcon, 
    XCircleIcon,
    ArrowPathIcon,
    MagnifyingGlassIcon,
    CpuChipIcon,
    SignalIcon,
    PlusIcon,
    ArchiveBoxIcon
} from '@heroicons/vue/24/solid';

defineOptions({ layout: MainLayout });

const props = defineProps({
    scan_success: Boolean,
    message: String,
    item: Object
});

const form = useForm({
    barcode: ''
});

const scannerRef = ref(null);
let html5QrcodeScanner = null;

const onScanSuccess = (decodedText, decodedResult) => {
    form.barcode = decodedText;
    if (html5QrcodeScanner) {
        html5QrcodeScanner.pause();
    }
    submit();
};

const onScanFailure = (error) => {
    // Silent fail for continuous scan
};

onMounted(() => {
    setTimeout(() => {
        const scannerElement = document.getElementById('reader');
        if (scannerElement) {
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader",
                { 
                    fps: 20, 
                    qrbox: { width: 280, height: 280 },
                    aspectRatio: 1.0
                },
                /* verbose= */ false
            );
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }
    }, 600);
});

onBeforeUnmount(() => {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.clear().catch(error => {
            console.error("Scanner clear failed:", error);
        });
    }
});

const submit = () => {
    form.post(route('admin.inventory.scan'), {
        preserveScroll: true,
        onSuccess: () => {
            // Logic handled by view props
        }
    });
};

const reset = () => {
    form.reset();
    if(html5QrcodeScanner) html5QrcodeScanner.resume();
    router.visit(route('admin.inventory.scanner'), { preserveScroll: true }); 
}
</script>

<template>
    <Head title="Scanner Mode" />

    <div class="h-screen bg-slate-50 flex flex-col items-center justify-center p-12 relative overflow-hidden font-outfit -m-8">
        <!-- Execution Desk Background Decor -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-500/5 via-transparent to-transparent pointer-events-none"></div>
        <div class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>
        
        <!-- Back Navigation Link -->
        <Link :href="route('admin.inventory.index')" class="absolute top-12 left-12 flex items-center gap-4 text-slate-400 hover:text-indigo-600 transition-all group z-50">
            <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center shadow-sm group-hover:border-indigo-200 transition-all">
                <ChevronLeftIcon class="w-6 h-6 group-hover:-translate-x-1 transition-transform" />
            </div>
            <span class="text-[10px] font-bold uppercase tracking-widest">Back To Store</span>
        </Link>

        <!-- Command Controller Header -->
        <div class="relative z-10 mb-12 text-center text-left">
            <div class="flex items-center justify-center gap-6 mb-4">
                <div class="p-3 bg-indigo-50 border border-indigo-100 rounded-xl text-indigo-600 shadow-sm shrink-0">
                    <SignalIcon class="w-6 h-6" />
                </div>
                <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tight leading-none">Scanner <span class="text-indigo-600">Mode</span></h1>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none px-1">Scan item barcodes and open actions instantly</p>
        </div>

        <!-- Tactical Scanner Interface -->
        <div class="z-10 w-full max-w-lg relative group">
            <div class="bg-white p-4 rounded-[2.5rem] shadow-xl border border-slate-200 relative aspect-square backdrop-blur-xl">
                <!-- Live Feed Holder -->
                <div id="reader" class="w-full h-full rounded-[2rem] overflow-hidden opacity-90 transition-all border border-slate-100"></div>
                
                <!-- Scanning Line -->
                <div class="absolute top-4 left-4 right-4 h-1 bg-gradient-to-r from-transparent via-indigo-500 to-transparent shadow-[0_0_15px_rgba(79,70,229,0.4)] z-20 animate-[scan_3s_linear_infinite] opacity-50 pointer-events-none"></div>

                <!-- Response Overlay -->
                <Transition enter-active-class="transition duration-500 ease-out" enter-from-class="opacity-0 translate-y-10" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-300 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                    <div v-if="props.message" class="absolute inset-4 bg-white/95 backdrop-blur-xl rounded-[2rem] flex flex-col items-center justify-center p-10 text-center z-30">
                        <div class="relative mb-8">
                            <div v-if="props.scan_success" class="h-20 w-20 bg-emerald-50 border border-emerald-100 rounded-3xl flex items-center justify-center shadow-inner">
                                <CheckCircleIcon class="h-10 w-10 text-emerald-500" />
                            </div>
                            <div v-else class="h-20 w-20 bg-rose-50 border border-rose-100 rounded-3xl flex items-center justify-center shadow-inner">
                                <XCircleIcon class="h-10 w-10 text-rose-500" />
                            </div>
                        </div>

                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-3">
                            {{ props.scan_success ? 'Item Found' : 'Scan Failed' }}
                        </h3>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-10 leading-relaxed px-4">{{ props.message }}</p>

                        <div v-if="!props.scan_success" class="flex flex-col gap-4 w-full mb-10">
                            <Link :href="route('admin.inventory.create')" class="w-full h-14 bg-indigo-50 border border-indigo-100 text-indigo-600 rounded-xl text-[10px] font-bold uppercase tracking-widest flex items-center justify-center gap-3 hover:bg-indigo-100 transition-all shadow-sm">
                                <PlusIcon class="w-4 h-4" />
                                Register New Item
                            </Link>
                        </div>

                        <!-- Object Data Node -->
                        <div v-if="props.item" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-6 text-left mb-10 shadow-sm relative group/card overflow-hidden">
                            <div class="absolute -right-12 -top-12 w-32 h-32 bg-indigo-500/5 rounded-full blur-2xl group-hover/card:scale-150 transition-transform duration-700"></div>
                            
                            <div class="flex items-center justify-between mb-4 relative z-10">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Item Details</span>
                                <div class="px-2 py-0.5 bg-indigo-50 text-indigo-600 text-[9px] font-bold rounded uppercase tracking-widest border border-indigo-100">
                                        {{ props.item.sku ? 'Active SKU' : 'Asset Record' }}
                                </div>
                            </div>
                            <p class="text-lg font-black text-slate-900 uppercase tracking-tight mb-2 truncate leading-none relative z-10">{{ props.item.name }}</p>
                            <div class="flex items-center gap-3 relative z-10 mb-6">
                                <CpuChipIcon class="w-4 h-4 text-slate-300" />
                                <p class="text-xs font-mono text-indigo-500 font-bold tracking-tight">{{ props.item.sku || props.item.serial_number || 'REF-' + props.item.id }}</p>
                            </div>

                            <!-- Tactical Actions -->
                            <div class="flex items-center gap-3 relative z-10 pt-4 border-t border-slate-200/60">
                                    <Link 
                                    v-if="props.item.sku"
                                    :href="route('admin.inventory.dashboard', { view: 'list' })" 
                                    class="flex-1 h-10 bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 rounded-lg text-[9px] font-bold uppercase tracking-widest flex items-center justify-center gap-2 transition-all shadow-sm"
                                >
                                    <ArchiveBoxIcon class="w-3.5 h-3.5" />
                                    Open Item
                                </Link>
                                <Link 
                                    v-else
                                    :href="route('admin.assets.show', props.item.id)" 
                                    class="flex-1 h-10 bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 rounded-lg text-[9px] font-bold uppercase tracking-widest flex items-center justify-center gap-2 transition-all shadow-sm"
                                >
                                    <SignalIcon class="w-3.5 h-3.5" />
                                    Open Asset
                                </Link>

                                <Link 
                                    v-if="props.item.sku"
                                    :href="route('admin.inventory.dashboard', { view: 'procurement' })" 
                                    class="h-10 px-5 bg-indigo-600 text-white rounded-lg text-[9px] font-bold uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-indigo-700 transition-all shadow-sm"
                                >
                                    <PlusIcon class="w-3.5 h-3.5 text-white" />
                                    Restock
                                </Link>
                            </div>
                        </div>

                        <button @click="reset" class="w-full h-14 bg-slate-100 text-slate-400 text-[10px] font-bold uppercase tracking-widest rounded-xl hover:text-indigo-600 transition-all active:scale-95 flex items-center justify-center gap-4">
                            <ArrowPathIcon class="w-4 h-4" />
                            Resume Scan
                        </button>
                    </div>
                </Transition>
            </div>
        </div>

        <!-- Manual Sequence Entry -->
        <div class="mt-12 w-full max-w-lg relative z-10 animate-in fade-in slide-in-from-bottom-10 delay-300">
            <form @submit.prevent="submit" class="relative group/form">
                <div class="absolute inset-y-0 left-6 flex items-center pointer-events-none text-slate-300 group-focus-within/form:text-indigo-500 transition-colors">
                    <MagnifyingGlassIcon class="w-5 h-5" />
                </div>
                <input 
                    v-model="form.barcode"
                    type="text" 
                    placeholder="Enter barcode manually..." 
                    class="w-full h-16 bg-white border border-slate-200 rounded-2xl pl-16 pr-24 text-[13px] font-bold text-slate-900 placeholder-slate-300 focus:bg-white focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-400 transition-all backdrop-blur-sm uppercase tracking-widest shadow-sm"
                >
                <button type="submit" class="absolute right-3 top-3 bottom-3 px-6 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all active:scale-90 shadow-lg">
                    Verify
                </button>
            </form>
            <p class="mt-6 text-center text-[9px] font-bold text-slate-400 uppercase tracking-[0.4em]">Scanner Ready</p>
        </div>
    </div>
</template>

<style>
#reader {
    border: none !important;
}
#reader video {
    object-fit: cover !important;
}
#reader__dashboard_section_csr span, 
#reader__dashboard_section_swaplink,
#reader__camera_selection,
#reader__dashboard_section_header {
    display: none !important;
}
#reader__scan_region {
    background: transparent !important;
    border: none !important;
}

@keyframes scan {
    0% { top: 0%; opacity: 0; }
    50% { opacity: 1; }
    100% { top: 100%; opacity: 0; }
}
</style>
