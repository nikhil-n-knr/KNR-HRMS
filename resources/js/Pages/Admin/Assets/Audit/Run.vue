<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, computed, watch, nextTick } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { 
    QrCodeIcon, 
    CheckCircleIcon, 
    XCircleIcon, 
    ExclamationTriangleIcon,
    ArrowPathIcon
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
        alert('Failed to load asset list');
    } finally {
        isLoading.value = false;
    }
};

const processScan = () => {
    const rawTag = scanInput.value.trim();
    if (!rawTag) return;

    // 1. Is it already scanned?
    if (scannedTags.value.has(rawTag)) {
        // Play "Already Scanned" sound or visual cue
        flashMessage('Already Scanned', 'blue');
        scanInput.value = '';
        return;
    }

    // 2. Is it in expected list?
    const match = expectedAssets.value.find(a => a.asset_tag === rawTag);
    
    if (match) {
        scannedTags.value.add(rawTag);
        flashMessage('Verified', 'green');
    } else {
        // Unexpected Item
        unexpectedAssets.value.unshift(rawTag); // Add to top
        flashMessage('Unexpected Item!', 'amber');
    }

    scanInput.value = '';
};

// --- Utilities ---
const message = ref('');
const messageColor = ref('gray');

const flashMessage = (msg, color) => {
    message.value = msg;
    messageColor.value = color;
    setTimeout(() => message.value = '', 2000);
};

const submitAudit = () => {
    if (!confirm('Finalize this audit? Missing items will be marked in the system.')) return;

    const scannedIds = expectedAssets.value
        .filter(a => scannedTags.value.has(a.asset_tag))
        .map(a => a.id);
    
    const missingIds = missingAssets.value.map(a => a.id);

    router.post(route('admin.assets.audit.submit'), {
        location_id: selectedLocation.value,
        scanned_ids: scannedIds,
        missing_ids: missingIds
    });
};
</script>

<template>
    <Head title="Audit Scanner" />

    <div class="h-screen flex flex-col bg-gray-900 text-white overflow-hidden mobile-safe-area">
        
        <!-- Top Bar: Config -->
        <div class="p-4 bg-gray-800 border-b border-gray-700 flex flex-col gap-3">
            <div class="flex justify-between items-center">
                <h1 class="font-bold text-lg flex items-center gap-2 text-emerald-400">
                    <QrCodeIcon class="h-6 w-6" /> Blind Audit
                </h1>
                <Link :href="route('admin.assets.audit.history')" class="text-xs font-bold bg-gray-700 hover:bg-emerald-600 px-3 py-1 rounded-full transition-colors border border-gray-600">
                    History
                </Link>
            </div>

            <div class="flex gap-2">
                <select v-model="selectedLocation" @change="fetchTargetList" class="flex-1 bg-gray-700 border-gray-600 text-white rounded-lg text-sm focus:ring-emerald-500">
                    <option value="" disabled>Select Location</option>
                    <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                </select>
                 <select v-model="selectedCategory" @change="fetchTargetList" class="flex-1 bg-gray-700 border-gray-600 text-white rounded-lg text-sm focus:ring-emerald-500">
                    <option value="">All Categories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
            </div>
        </div>

        <!-- Scanner Input (Sticky) -->
        <div class="p-4 bg-gray-800 shadow-lg z-10">
            <input 
                ref="scanInputRef"
                v-model="scanInput"
                @keyup.enter="processScan"
                type="text" 
                placeholder="Scan / Type Asset Tag..." 
                class="w-full bg-black border-2 border-emerald-500 text-emerald-400 font-mono text-xl p-3 rounded-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/30 placeholder-gray-600 text-center"
                :disabled="!selectedLocation"
            >
            <p class="text-center text-xs text-gray-500 mt-2">Press Enter to process</p>
        </div>

        <!-- Progress Bar -->
        <div class="h-2 bg-gray-700 w-full">
            <div class="h-full bg-emerald-500 transition-all duration-300" :style="`width: ${progressPercent}%`"></div>
        </div>

        <!-- Lists Container -->
        <div class="flex-1 overflow-y-auto p-4 space-y-6">
            
            <div v-if="!selectedLocation" class="text-center text-gray-500 mt-10">
                <ArrowPathIcon class="h-12 w-12 mx-auto mb-2 opacity-50" />
                <p>Select a location to start scanning</p>
            </div>

            <div v-else>
                <!-- Stats -->
                <div class="flex justify-between text-sm font-mono mb-4 text-gray-400 border-b border-gray-700 pb-2">
                    <span>Target: {{ expectedAssets.length }}</span>
                    <span class="text-emerald-400">Found: {{ foundCount }}</span>
                    <span class="text-red-400">Missing: {{ missingAssets.length }}</span>
                </div>

                <!-- Missing List (High Priority) -->
                 <div v-if="missingAssets.length" class="mb-6">
                    <h3 class="text-xs font-bold text-red-500 uppercase mb-2">Still Missing</h3>
                    <div class="space-y-2">
                        <div v-for="asset in missingAssets" :key="asset.id" class="flex justify-between items-center p-3 bg-red-900/20 border border-red-500/30 rounded-lg">
                            <div>
                                <div class="font-bold text-red-200">{{ asset.name }}</div>
                                <div class="text-xs text-red-400 font-mono">{{ asset.asset_tag }}</div>
                            </div>
                            <XCircleIcon class="h-5 w-5 text-red-500 opacity-50" />
                        </div>
                    </div>
                </div>

                <!-- Unexpected List -->
                <div v-if="unexpectedAssets.length" class="mb-6">
                    <h3 class="text-xs font-bold text-amber-500 uppercase mb-2">Unexpected Items (Wrong Location)</h3>
                     <div class="space-y-2">
                        <div v-for="tag in unexpectedAssets" :key="tag" class="flex justify-between items-center p-3 bg-amber-900/20 border border-amber-500/30 rounded-lg">
                            <span class="font-mono text-amber-200">{{ tag }}</span>
                            <ExclamationTriangleIcon class="h-5 w-5 text-amber-500" />
                        </div>
                    </div>
                </div>

                 <!-- Found List (Collapsed or Bottom) -->
                 <div v-if="foundCount > 0">
                    <h3 class="text-xs font-bold text-emerald-600 uppercase mb-2">Verified</h3>
                     <!-- Limit items shown to simplify UI -->
                     <div class="text-xs text-gray-500 italic">
                        {{ foundCount }} items verified successfully.
                     </div>
                </div>

            </div>
        </div>

        <!-- Footer Action -->
        <div class="p-4 bg-gray-800 border-t border-gray-700">
            <button 
                @click="submitAudit"
                :disabled="!selectedLocation || foundCount === 0"
                class="w-full py-4 text-center font-bold rounded-xl text-lg transition-colors"
                :class="foundCount > 0 ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-900/50' : 'bg-gray-700 text-gray-500 cursor-not-allowed'"
            >
                Submit Audit Manifest
            </button>
        </div>

    </div>
</template>

<style scoped>
.mobile-safe-area {
    padding-bottom: env(safe-area-inset-bottom);
}
</style>
