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
    SignalIcon
} from '@heroicons/vue/24/outline';

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
            console.error("Scanner clearing protocol failed: ", error);
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
    <Head title="Optical Matrix Uplink" />

    <div class="min-h-screen bg-[#050505] flex flex-col items-center justify-center p-6 relative overflow-hidden font-outfit selection:bg-emerald-500/30">
        <!-- Digital Horizon Decor -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-emerald-500/5 via-transparent to-transparent opacity-50"></div>
        <div class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-emerald-500/20 to-transparent"></div>
        
        <!-- Back Navigation Link -->
        <Link :href="route('admin.inventory.index')" class="absolute top-8 left-8 flex items-center gap-3 text-slate-500 hover:text-emerald-400 transition-all group z-[50]">
            <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center group-hover:border-emerald-500/30 transition-all">
                <ChevronLeftIcon class="w-5 h-5 group-hover:-translate-x-1 transition-transform" />
            </div>
            <span class="text-sm font-black uppercase tracking-[0.3em] font-outfit">Terminate Uplink</span>
        </Link>

        <!-- Command Controller Header -->
        <div class="relative z-10 mb-12 text-center">
            <div class="flex items-center justify-center gap-4 mb-4">
                <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-400 animate-pulse shadow-2xl shadow-emerald-500/20">
                    <SignalIcon class="w-6 h-6" />
                </div>
                <h1 class="text-3xl font-black text-white uppercase tracking-tighter italic">Optical <span class="text-emerald-500">Matrix</span></h1>
            </div>
            <p class="text-sm font-black text-slate-500 uppercase tracking-[0.5em] ml-2">Real-time object vector extraction</p>
        </div>

        <!-- Tactical Scanner Interface -->
        <div class="z-10 w-full max-w-lg relative group">
            <!-- Decorative Corners -->
            <div class="absolute -top-2 -left-2 w-8 h-8 border-t-2 border-l-2 border-emerald-500 rounded-tl-lg z-20 group-hover:scale-110 transition-transform"></div>
            <div class="absolute -top-2 -right-2 w-8 h-8 border-t-2 border-r-2 border-emerald-500 rounded-tr-lg z-20 group-hover:scale-110 transition-transform"></div>
            <div class="absolute -bottom-2 -left-2 w-8 h-8 border-b-2 border-l-2 border-emerald-500 rounded-bl-lg z-20 group-hover:scale-110 transition-transform"></div>
            <div class="absolute -bottom-2 -right-2 w-8 h-8 border-b-2 border-r-2 border-emerald-500 rounded-br-lg z-20 group-hover:scale-110 transition-transform"></div>

            <div class="bg-black/80 rounded-[2.5rem] overflow-hidden shadow-[0_0_100px_rgba(16,185,129,0.1)] border border-white/5 relative aspect-square backdrop-blur-xl">
                <!-- Live Feed Holder -->
                <div id="reader" class="w-full h-full opacity-90 grayscale-[0.5] hover:grayscale-0 transition-all"></div>
                
                <!-- Scanning Scanning Line -->
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-emerald-500 to-transparent shadow-[0_0_15px_rgba(16,185,129,0.5)] z-20 animate-[scan_3s_linear_infinite] opacity-50 pointer-events-none"></div>

                <!-- Response Overlay -->
                <Transition enter-active-class="transition duration-500 ease-out" enter-from-class="opacity-0 translate-y-10" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-300 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                    <div v-if="props.message" class="absolute inset-0 bg-[#050505]/95 backdrop-blur-3xl flex flex-col items-center justify-center p-10 text-center z-[30]">
                        <div class="relative mb-8">
                            <div v-if="props.scan_success" class="h-24 w-24 bg-emerald-500/10 border-2 border-emerald-500/30 rounded-[2.5rem] flex items-center justify-center shadow-[0_0_50px_rgba(16,185,129,0.2)] animate-in zoom-in duration-500">
                                <CheckCircleIcon class="h-12 w-12 text-emerald-400" />
                            </div>
                            <div v-else class="h-24 w-24 bg-rose-500/10 border-2 border-rose-500/30 rounded-[2.5rem] flex items-center justify-center shadow-[0_0_50px_rgba(244,63,94,0.2)] animate-in zoom-in duration-500">
                                <XCircleIcon class="h-12 w-12 text-rose-400" />
                            </div>
                        </div>

                        <h3 class="text-2xl font-black text-white uppercase tracking-tight mb-3">
                            {{ props.scan_success ? 'Vector_Locked' : 'Uplink_Failed' }}
                        </h3>
                        <p class="text-base font-black text-slate-500 uppercase tracking-widest mb-10 leading-relaxed">{{ props.message }}</p>

                        <!-- Object Data Node -->
                        <div v-if="props.item" class="w-full bg-white/5 border border-white/10 rounded-3xl p-6 text-left mb-10 shadow-inner group/data hover:border-emerald-500/30 transition-all">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-xs font-black text-slate-500 uppercase tracking-[0.3em]">Resource_Identity</span>
                                <div class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 text-xs font-black rounded uppercase tracking-widest border border-emerald-500/20">Active_SKU</div>
                            </div>
                            <p class="text-xl font-black text-white uppercase tracking-tight mb-2 group-hover/data:text-emerald-400 transition-colors">{{ props.item.name }}</p>
                            <div class="flex items-center gap-3">
                                <CpuChipIcon class="w-4 h-4 text-slate-600" />
                                <p class="text-sm font-mono text-emerald-500/70 tracking-tighter">{{ props.item.serial_number || 'NODE_REF_' + props.item.id }}</p>
                            </div>
                        </div>

                        <button @click="reset" class="w-full h-16 bg-white text-[#050505] text-base font-black uppercase tracking-[0.4em] rounded-2xl hover:bg-emerald-500 hover:text-white transition-all active:scale-95 shadow-[0_20px_40px_rgba(255,255,255,0.05)] flex items-center justify-center gap-4 group/btn">
                            <ArrowPathIcon class="w-5 h-5 group-hover/btn:rotate-180 transition-transform duration-700" />
                            Resume Matrix Scan
                        </button>
                    </div>
                </Transition>
            </div>
        </div>

        <!-- Manual Sequence Entry -->
        <div class="mt-12 w-full max-w-lg relative z-10 animate-in fade-in slide-in-from-bottom-10 delay-300">
            <form @submit.prevent="submit" class="relative group/form">
                <div class="absolute inset-y-0 left-6 flex items-center pointer-events-none text-slate-500 group-focus-within/form:text-emerald-500 transition-colors">
                    <MagnifyingGlassIcon class="w-5 h-5" />
                </div>
                <input 
                    v-model="form.barcode"
                    type="text" 
                    placeholder="ENTER_SEQUENCE_MANUALLY..." 
                    class="w-full h-16 bg-white/5 border border-white/10 rounded-2xl pl-16 pr-24 text-base font-black text-white placeholder-slate-600 focus:bg-white/10 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500/50 transition-all backdrop-blur-sm uppercase tracking-widest"
                >
                <button type="submit" class="absolute right-3 top-3 bottom-3 px-6 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-emerald-500 hover:text-white transition-all active:scale-90">
                    Verify
                </button>
            </form>
            <p class="mt-4 text-center text-sm font-black text-slate-600 uppercase tracking-[0.4em]">Protocol Version 2.4.0 &bull; Secure Uplink</p>
        </div>

        <!-- Floating Cosmic Dust Decor -->
        <div class="absolute top-[15%] right-[10%] w-64 h-64 bg-indigo-500/10 blur-[100px] rounded-full animate-pulse"></div>
        <div class="absolute bottom-[15%] left-[10%] w-64 h-64 bg-emerald-500/10 blur-[100px] rounded-full animate-pulse"></div>
    </div>
</template>

<style>
/* Override HTML5 Scanner Styles */
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

.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
</style>
