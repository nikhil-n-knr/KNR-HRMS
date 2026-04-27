<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, computed, watch, nextTick } from 'vue';
import axios from 'axios';
import { 
    QrCodeIcon, 
    CheckCircleIcon, 
    XCircleIcon, 
    ExclamationTriangleIcon,
    ArrowPathIcon,
    InformationCircleIcon,
    ArrowLeftIcon,
    MagnifyingGlassIcon,
    SparklesIcon,
    BoltIcon,
    CursorArrowRaysIcon,
    ArchiveBoxIcon,
    MapPinIcon,
    CubeIcon,
    BellAlertIcon,
    TrashIcon,
    CheckBadgeIcon,
    StopIcon,
    CommandLineIcon
} from '@heroicons/vue/24/solid';

defineOptions({ layout: MainLayout });

const props = defineProps({
    locations: Array,
    categories: Array
});

// --- State ---
const selectedLocation = ref('');
const selectedCategory = ref('');
const isLoading = ref(false);
const scanInput = ref('');
const scanInputRef = ref(null);

// Data Lists
const expectedAssets = ref([]); // From DB
const scannedTags = ref(new Set()); // User Scanned
const unexpectedAssets = ref([]); // Scanned but not in expected list

// Feedback
const message = ref('');
const messageColor = ref('blue'); // blue, green, amber
let msgTimeout = null;

// --- Computed Logic ---
const missingAssets = computed(() => {
    return expectedAssets.value.filter(a => !scannedTags.value.has(a.asset_tag));
});

const foundCount = computed(() => {
    return expectedAssets.value.length - missingAssets.value.length;
});

const progressPercent = computed(() => {
    if (expectedAssets.value.length === 0) return 0;
    return Math.round((foundCount.value / expectedAssets.value.length) * 100);
});

// --- Actions ---
const fetchTargetList = async () => {
    if (!selectedLocation.value) return;
    
    isLoading.value = true;
    try {
        const response = await axios.post(route('admin.assets.audit.fetch'), {
            location_id: selectedLocation.value,
            category_id: selectedCategory.value
        });
        expectedAssets.value = response.data;
        scannedTags.value.clear();
        unexpectedAssets.value = [];
        
        // Auto-focus scanner after load
        nextTick(() => scanInputRef.value?.focus());
    } catch (e) {
        console.error(e);
        flashMessage('Unable to load target assets', 'amber');
    } finally {
        isLoading.value = false;
    }
};

const processScan = () => {
    const rawTag = scanInput.value.trim();
    if (!rawTag) return;

    // 1. Is it already scanned?
    if (scannedTags.value.has(rawTag)) {
        flashMessage('Tag already scanned', 'blue');
        scanInput.value = '';
        return;
    }

    // 2. Is it in expected list?
    const match = expectedAssets.value.find(a => a.asset_tag === rawTag);
    
    if (match) {
        scannedTags.value.add(rawTag);
        flashMessage(`Verified: ${match.name}`, 'green');
    } else {
        // 3. Unexpected (Is it some other tag?)
        unexpectedAssets.value.push(rawTag);
        flashMessage('Tag not in selected location list', 'amber');
    }

    scanInput.value = '';
    nextTick(() => scanInputRef.value?.focus());
};

const flashMessage = (html, color) => {
    if (msgTimeout) clearTimeout(msgTimeout);
    message.value = html;
    messageColor.value = color;
    msgTimeout = setTimeout(() => message.value = '', 2000);
};

const finishAudit = () => {
    if (!confirm('Save this audit log now?')) return;
    
    router.post(route('admin.assets.audit.store'), {
        location_id: selectedLocation.value,
        total_expected: expectedAssets.value.length,
        found_tags: Array.from(scannedTags.value),
        unexpected_tags: unexpectedAssets.value
    }, {
        onSuccess: () => {
            router.get(route('admin.assets.dashboard'));
        }
    });
};
</script>

<template>
    <Head title="Blind Audit" />
    <div class="h-full flex flex-col font-outfit -m-8 p-12 bg-slate-50 min-h-screen relative overflow-hidden text-slate-900 selection:bg-indigo-500/30">
        <!-- Subtle background accents -->
        <div class="absolute -right-32 -top-32 w-128 h-128 bg-indigo-500/5 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute -left-32 bottom-0 w-128 h-128 bg-indigo-500/5 rounded-full blur-[140px] pointer-events-none"></div>

        <!-- Header -->
        <header class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm mb-10 relative overflow-hidden group">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-10 relative z-10">
                <div class="flex items-center gap-6">
                    <Link :href="route('admin.assets.dashboard')" class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-all active:scale-95 border border-slate-100 shadow-sm shrink-0">
                        <ArrowLeftIcon class="w-7 h-7" />
                    </Link>
                    <div class="text-left">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-600">Blind Audit</p>
                        <h1 class="text-3xl font-black text-slate-900 tracking-tight mt-1 uppercase">Field Audit</h1>
                        <p class="text-xs font-semibold text-slate-400 mt-2">Scan and verify physical assets in the selected location.</p>
                    </div>
                </div>

                <div class="flex items-center gap-10 shrink-0">
                    <div class="flex flex-col items-end gap-2.5">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Audit Progress</span>
                        <div class="w-80 h-2.5 bg-slate-100 border border-slate-200 rounded-full overflow-hidden flex shadow-inner">
                            <div class="h-full bg-indigo-600 transition-all duration-1000 shadow-[0_0_15px_rgba(79,70,229,0.3)]" :style="{ width: progressPercent + '%' }"></div>
                        </div>
                    </div>
                    <div class="text-5xl font-black text-slate-900 tabular-nums tracking-tighter">{{ progressPercent }}%</div>
                </div>
            </div>
        </header>

        <div class="flex-1 flex flex-col xl:flex-row gap-10 min-h-0 relative z-10 overflow-hidden pb-4">
            <!-- Sidebar: Control Node -->
            <aside class="w-full xl:w-[480px] flex flex-col gap-10 min-h-0">
                
                <!-- Location Selection -->
                <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm text-left">
                    <div class="flex items-center gap-5 mb-8 border-b border-slate-50 pb-8">
                        <div class="w-12 h-12 bg-indigo-50 border border-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center shadow-sm shrink-0">
                            <MapPinIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight uppercase leading-none">Location Setup</h3>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-2">Select where you are scanning.</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="space-y-3">
                            <label class="px-2 text-[9px] font-bold text-slate-500 uppercase tracking-widest">Location</label>
                            <select v-model="selectedLocation" @change="fetchTargetList" class="w-full h-12 bg-slate-50 border border-slate-200 rounded-xl px-5 text-sm font-bold text-slate-900 uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                <option value="" class="bg-white">Select Location</option>
                                <option v-for="loc in locations" :key="loc.id" :value="loc.id" class="bg-white">{{ loc.name.toUpperCase() }}</option>
                            </select>
                        </div>

                        <div class="space-y-3">
                            <label class="px-2 text-[9px] font-bold text-slate-500 uppercase tracking-widest">Category Filter</label>
                            <select v-model="selectedCategory" @change="fetchTargetList" class="w-full h-12 bg-slate-50 border border-slate-200 rounded-xl px-5 text-sm font-bold text-slate-900 uppercase tracking-widest focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                <option value="" class="bg-white">All Categories</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id" class="bg-white">{{ cat.name.toUpperCase() }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Intelligence Scanner Hub -->
                <div class="flex-1 bg-white rounded-3xl border border-slate-200 p-8 shadow-sm flex flex-col relative overflow-hidden">
                    <div v-if="!expectedAssets.length && !isLoading" class="flex-1 flex flex-col items-center justify-center text-center space-y-8 opacity-20 text-left">
                        <StopIcon class="w-20 h-20 text-slate-400" />
                        <div>
                            <p class="text-xl font-black text-slate-600 uppercase tracking-tight">Scanner Idle</p>
                            <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-3">Select a location to begin.</p>
                        </div>
                    </div>

                    <div v-else-if="isLoading" class="flex-1 flex flex-col items-center justify-center text-center space-y-8">
                         <div class="w-14 h-14 border-4 border-slate-100 border-t-indigo-600 rounded-full animate-spin"></div>
                        <p class="text-[9px] font-bold text-indigo-600 uppercase tracking-widest animate-pulse">Loading target assets...</p>
                    </div>

                    <div v-else class="flex-1 flex flex-col animate-in fade-in zoom-in duration-500 text-left">
                        <div class="flex items-center justify-between mb-8 border-b border-slate-50 pb-6">
                             <div class="flex items-center gap-5">
                                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg">
                                    <QrCodeIcon class="w-6 h-6" />
                                </div>
                                          <h3 class="text-xl font-black text-slate-900 tracking-tight uppercase">Scan Intake</h3>
                             </div>
                                      <div class="px-4 py-1.5 bg-emerald-50 border border-emerald-100 text-emerald-600 text-[9px] font-bold uppercase tracking-widest rounded-lg shadow-sm animate-pulse">Live Scan On</div>
                        </div>

                        <div class="relative group/scan mb-10">
                            <CommandLineIcon class="absolute left-6 top-1/2 -translate-y-1/2 w-6 h-6 text-slate-300 group-focus-within:text-indigo-600 transition-all duration-500" />
                            <input 
                                v-model="scanInput" 
                                @keydown.enter="processScan"
                                ref="scanInputRef"
                                type="text" 
                                placeholder="Scan asset tag..." 
                                class="w-full h-20 bg-slate-50 border border-slate-200 rounded-2xl pl-16 pr-8 text-2xl font-black text-slate-900 text-center focus:bg-white focus:ring-8 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all uppercase tracking-widest placeholder:text-slate-300"
                            >
                        </div>

                        <!-- Real-time Pulse Feedback -->
                        <div class="h-36 flex items-center justify-center">
                            <Transition name="msg" mode="out-in">
                                <div v-if="message" class="text-center p-6 rounded-2xl border transition-all shadow-sm w-full"
                                    :class="{
                                        'bg-emerald-50 border-emerald-200 text-emerald-600': messageColor === 'green',
                                        'bg-amber-50 border-amber-200 text-amber-600': messageColor === 'amber',
                                        'bg-indigo-50 border-indigo-200 text-indigo-600': messageColor === 'blue',
                                    }">
                                    <div class="flex items-center justify-center gap-6">
                                        <CheckBadgeIcon v-if="messageColor === 'green'" class="w-10 h-10 animate-in zoom-in duration-300" />
                                        <BellAlertIcon v-else-if="messageColor === 'amber'" class="w-10 h-10 text-amber-500 animate-pulse" />
                                        <SparklesIcon v-else class="w-10 h-10 text-indigo-400 animate-spin-slow" />
                                        <span class="text-xl font-black uppercase tracking-tight leading-none">{{ message }}</span>
                                    </div>
                                </div>
                                <div v-else class="flex flex-col items-center gap-4 opacity-10">
                                     <div class="flex gap-2">
                                         <div v-for="i in 3" :key="i" class="w-2.5 h-2.5 bg-indigo-600 rounded-full animate-bounce shadow-sm" :style="`animation-delay: ${i * 150}ms`"></div>
                                     </div>
                                     <span class="text-[9px] font-bold uppercase tracking-widest text-indigo-600">waiting for scan</span>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Panel: Data Matrix -->
            <main class="flex-1 bg-white rounded-3xl border border-slate-200 shadow-sm flex flex-col min-h-0 relative overflow-hidden">
                <div class="grid grid-cols-1 xl:grid-cols-2 h-full text-left">
                    
                    <!-- Missing Nodes (The TODO List) -->
                    <section class="flex flex-col border-r border-slate-50 min-h-0 relative group">
                        <div class="p-8 border-b border-slate-50 flex items-center justify-between sticky top-0 bg-white/90 backdrop-blur-md z-20">
                             <div class="flex items-center gap-5">
                                <div class="w-10 h-10 bg-rose-50 text-rose-500 border border-rose-100 rounded-xl flex items-center justify-center shadow-sm shrink-0">
                                    <ExclamationTriangleIcon class="w-6 h-6" />
                                </div>
                                <div>
                                     <h4 class="text-sm font-black text-rose-600 uppercase tracking-tight leading-none">Missing Assets</h4>
                                     <p class="text-[9px] font-bold text-slate-400 mt-2 uppercase tracking-widest">{{ missingAssets.length }} Assets Pending</p>
                                </div>
                             </div>
                                 <div class="px-3 py-1 bg-rose-50 border border-rose-100 text-[8px] font-bold text-rose-500 uppercase tracking-widest rounded-lg shadow-sm">Gap</div>
                        </div>
                        <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar scroll-smooth">
                             <div v-for="asset in missingAssets" :key="asset.id" class="p-6 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-between group/item hover:bg-white hover:border-rose-200 transition-all duration-500 shadow-sm relative overflow-hidden">
                                 <div class="absolute left-0 top-0 w-1 h-full bg-rose-500 opacity-30"></div>
                                 <div class="flex items-center gap-5">
                                    <div class="w-12 h-12 bg-white border border-slate-100 rounded-xl flex items-center justify-center text-slate-400 font-black text-base shrink-0 group-hover/item:bg-rose-50 group-hover/item:text-rose-600 transition-all duration-500 shadow-sm">{{ asset.name.charAt(0) }}</div>
                                     <div>
                                        <h5 class="text-base font-black text-slate-900 uppercase tracking-tight leading-none group-hover/item:text-rose-700 transition-colors">{{ asset.name }}</h5>
                                        <div class="flex items-center gap-3 mt-2.5">
                                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest font-mono">{{ asset.asset_tag }}</p>
                                            <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ asset.category?.name || 'UNASSIGNED' }}</span>
                                        </div>
                                     </div>
                                 </div>
                                 <div class="flex flex-col items-end gap-3 shrink-0">
                                     <div class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse outline outline-4 outline-rose-500/10"></div>
                                 </div>
                             </div>
                             <div v-if="!missingAssets.length && expectedAssets.length" class="h-full flex flex-col items-center justify-center text-center opacity-40 animate-in fade-in duration-1000 py-20">
                                 <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mb-6 shadow-sm">
                                    <CheckBadgeIcon class="w-12 h-12 text-emerald-500" />
                                 </div>
                                 <p class="text-xl font-black text-emerald-600 uppercase tracking-tight">Harmonization Complete</p>
                                 <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-3">All target assets verified.</p>
                             </div>
                        </div>
                    </section>
 
                    <!-- Verified Nodes (The Done List) -->
                    <section class="flex flex-col min-h-0 bg-slate-50/30">
                        <div class="p-8 border-b border-slate-50 flex items-center justify-between sticky top-0 bg-white/90 backdrop-blur-md z-20">
                             <div class="flex items-center gap-5">
                                <div class="w-10 h-10 bg-emerald-50 text-emerald-500 border border-emerald-100 rounded-xl flex items-center justify-center shadow-sm shrink-0">
                                    <CheckBadgeIcon class="w-6 h-6" />
                                </div>
                                <div>
                                     <h4 class="text-sm font-black text-emerald-600 uppercase tracking-tight leading-none">Verified Assets</h4>
                                     <p class="text-[9px] font-bold text-slate-400 mt-2 uppercase tracking-widest">{{ scannedTags.size }} Assets Verified</p>
                                </div>
                             </div>
                                 <button v-if="expectedAssets.length" @click="finishAudit" class="px-6 py-2.5 bg-indigo-600 text-white text-[10px] font-bold uppercase tracking-widest rounded-xl shadow-lg hover:bg-indigo-700 transition-all active:scale-95 group/commit">Save Audit</button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar scroll-smooth">
                             <div v-for="tag in Array.from(scannedTags).reverse()" :key="tag" class="p-6 bg-white border border-emerald-100 rounded-2xl flex items-center justify-between group/vitem hover:border-emerald-300 transition-all duration-500 shadow-sm relative animate-in slide-in-from-right-10 overflow-hidden">
                                 <div class="absolute left-0 top-0 w-1 h-full bg-emerald-500"></div>
                                 <div class="flex items-center gap-5">
                                     <div class="w-12 h-12 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center justify-center text-emerald-500 shadow-sm">
                                         <CheckCircleIcon class="w-7 h-7" />
                                     </div>
                                     <div>
                                        <h5 class="text-base font-black text-emerald-600 uppercase tracking-tight leading-none">{{ expectedAssets.find(a => a.asset_tag === tag)?.name || 'UNKNOWN ASSET' }}</h5>
                                        <div class="flex items-center gap-3 mt-2.5">
                                            <p class="text-[9px] font-bold text-emerald-400 uppercase tracking-widest font-mono">{{ tag }}</p>
                                             <span class="w-1 h-1 rounded-full bg-emerald-200"></span>
                                            <span class="text-[9px] font-bold text-emerald-300 uppercase tracking-widest">VERIFIED_STOCK</span>
                                        </div>
                                     </div>
                                 </div>
                                 <SparklesIcon class="w-5 h-5 text-emerald-200 group-hover/vitem:animate-spin-slow transition-transform" />
                             </div>
                             
                             <!-- Unexpected Block (Ghost Nodes) -->
                             <div v-for="tag in unexpectedAssets" :key="tag" class="p-6 bg-white border border-amber-100 rounded-2xl flex items-center justify-between group hover:border-amber-300 transition-all duration-500 shadow-sm relative animate-in slide-in-from-right-10 overflow-hidden">
                                 <div class="absolute left-0 top-0 w-1 h-full bg-amber-500"></div>
                                 <div class="flex items-center gap-5">
                                     <div class="w-12 h-12 bg-amber-50 border border-amber-100 rounded-xl flex items-center justify-center text-amber-500 shadow-sm">
                                         <BoltIcon class="w-7 h-7" />
                                     </div>
                                     <div>
                                        <h5 class="text-base font-black text-amber-600 uppercase tracking-tight leading-none">Ghost Node (Mismatched)</h5>
                                        <p class="text-[9px] font-bold text-amber-400 uppercase tracking-widest mt-2.5 font-mono">{{ tag }}</p>
                                     </div>
                                 </div>
                                 <button @click="unexpectedAssets = unexpectedAssets.filter(t => t !== tag)" class="w-9 h-9 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white transition-all active:scale-90 flex items-center justify-center border border-rose-100"><TrashIcon class="w-4 h-4" /></button>
                             </div>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.msg-enter-active, .msg-leave-active { transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
.msg-enter-from { opacity: 0; transform: translateY(20px) scale(0.95); }
.msg-leave-to { opacity: 0; transform: translateY(-20px) scale(0.95); }

.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(15, 23, 42, 0.05); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(79, 70, 229, 0.1); }

@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    animation: spin-slow 12s linear infinite;
}
.no-scrollbar::-webkit-scrollbar { display: none; }
</style>
