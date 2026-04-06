<template>
    <div class="flex h-screen bg-gray-50">
        <!-- Left Sidebar: Queue List -->
        <div class="w-1/3 border-r border-gray-200 bg-white flex flex-col">
             <!-- Header & Filter -->
            <div class="p-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-800">Verification Queue</h2>
                <div class="flex gap-2 mt-2">
                    <button 
                        v-for="s in ['Submitted', 'Verified', 'Rejected']" 
                        :key="s"
                        @click="router.get(route('hr.tax.proofs.index', { status: s }))"
                        :class="[
                            filters.status === s ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50',
                            'px-3 py-1 rounded-lg text-xs font-bold uppercase border transition'
                        ]"
                    >
                        {{ s }}
                    </button>
                </div>
            </div>

            <!-- List -->
            <div class="flex-1 overflow-y-auto">
                <div v-if="queue.length === 0" class="p-8 text-center text-gray-400 text-sm">
                    No declarations found.
                </div>
                <div 
                    v-for="item in queue" 
                    :key="item.type + item.id"
                    @click="selectItem(item)"
                    :class="[
                        selectedItem?.id === item.id && selectedItem?.type === item.type ? 'bg-indigo-50 border-l-4 border-l-indigo-500' : 'border-l-4 border-l-transparent hover:bg-gray-50',
                        'p-4 border-b border-gray-100 cursor-pointer transition'
                    ]"
                >
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-bold text-gray-800 text-sm flex items-center gap-2">
                             {{ item.employee_name }}
                             <span v-if="item.is_disputed" class="bg-orange-100 text-orange-600 px-1.5 py-0.5 rounded text-sm uppercase font-bold tracking-wider">Disputed</span>
                        </span>
                        <span class="text-sm text-gray-400">{{ formatDate(item.created_at) }}</span>
                    </div>
                    <div class="text-xs text-gray-500 mb-2">{{ item.employee_code }}</div>
                    
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 rounded text-sm font-bold uppercase tracking-wider bg-gray-100 text-gray-600">
                            {{ item.type === 'HRA' ? 'HRA' : item.section_code }}
                        </span>
                        <span v-if="item.proofs.length > 0" class="flex items-center text-sm text-indigo-600">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" /></svg>
                            {{ item.proofs.length }} File(s)
                        </span>
                    </div>

                    <div class="flex justify-between items-end">
                         <div>
                             <span class="text-sm text-gray-400 uppercase">Claimed</span>
                             <div class="font-bold text-gray-900">{{ item.claimed_display || formatCurrency(item.claimed) }}</div>
                         </div>
                         <div v-if="item.verified_amount" class="text-right">
                             <span class="text-sm text-emerald-500 uppercase">Verified</span>
                             <div class="font-bold text-emerald-700">{{ formatCurrency(item.verified) }}</div>
                         </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Detail & Action -->
        <div class="w-2/3 bg-gray-100 border-l border-gray-200 flex flex-col h-full">
            
            <!-- Top Navigation Tabs -->
            <div class="bg-white px-6 py-3 border-b border-gray-200 flex items-center justify-between">
                <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg">
                    <Link :href="route('hr.tax.proofs.index')" 
                          :class="['px-4 py-1.5 rounded-md text-sm font-bold shadow-sm transition', 
                                   $page.url.startsWith('/hr/tax/verification-queue') ? 'bg-white text-gray-900' : 'text-gray-500 hover:text-gray-900 hover:bg-white/50']">
                        Queue
                    </Link>
                    <Link :href="route('hr.tax.reports.dashboard')" 
                           :class="['px-4 py-1.5 rounded-md text-sm font-medium transition', 
                                   $page.url.startsWith('/hr/tax/reports/dashboard') ? 'bg-white text-gray-900 shadow-sm font-bold' : 'text-gray-500 hover:text-gray-900 hover:bg-white/50']">
                        Analytics
                    </Link>
                     <Link :href="route('hr.tax.reports.index')" 
                           :class="['px-4 py-1.5 rounded-md text-sm font-medium transition', 
                                   $page.url === '/hr/tax/reports' ? 'bg-white text-gray-900 shadow-sm font-bold' : 'text-gray-500 hover:text-gray-900 hover:bg-white/50']">
                        Reports
                    </Link>
                </div>
            </div>
            <div v-if="!selectedItem" class="flex-1 flex flex-col items-center justify-center text-gray-400">
                <svg class="w-16 h-16 opacity-20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" /></svg>
                <p class="mt-4 font-medium">Select a declaration to verify</p>
            </div>

            <div v-else class="flex flex-col h-full">
                <!-- Top Bar -->
                <div class="bg-white border-b border-gray-200 p-4 shadow-sm flex justify-between items-center z-10">
                    <div>
                        <h1 class="font-bold text-xl text-gray-800">{{ selectedItem.section }} <span class="text-gray-400 font-normal">({{ selectedItem.section_code }})</span></h1>
                        <p class="text-sm text-gray-500">Employee: {{ selectedItem.employee_name }}</p>
                    </div>
                    <div class="text-right">
                        <span class="block text-xs text-gray-500 uppercase">Submission Date</span>
                        <span class="font-medium text-gray-800">{{ formatDate(selectedItem.created_at) }}</span>
                    </div>
                </div>

                <!-- Split Content -->
                <div class="flex-1 flex overflow-hidden">
                    <!-- PDF / Document Preview -->
                    <div class="flex-1 bg-gray-800 relative group p-4 flex items-center justify-center overflow-auto">
                        <iframe 
                            v-if="selectedProofUrl" 
                            :src="selectedProofUrl" 
                            class="w-full h-full rounded shadow-lg bg-white"
                        ></iframe>
                         <div v-else class="text-white/50 text-center">
                            <p v-if="selectedItem.proofs.length === 0">No documents uploaded.</p>
                            <p v-else>Select a document below to preview.</p>
                        </div>
                        
                        <!-- File Switcher Overlay -->
                        <div v-if="selectedItem.proofs.length > 0" class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-black/70 backdrop-blur rounded-full p-1 flex gap-1">
                            <button 
                                v-for="(proof, i) in selectedItem.proofs" 
                                :key="proof.id"
                                @click="previewProof(proof)"
                                :class="[
                                    activeProofId === proof.id ? 'bg-white text-gray-900' : 'text-gray-300 hover:bg-white/20',
                                    'px-3 py-1 rounded-full text-xs font-bold transition'
                                ]"
                            >
                                File {{ i+1 }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Action Panel -->
                    <div class="w-80 bg-white border-l border-gray-200 p-6 flex flex-col shadow-xl z-20">
                        <h3 class="font-bold text-gray-800 mb-6">Verification Action</h3>

                        <div class="space-y-6 flex-1">
                             <!-- Dispute Banner -->
                             <div v-if="selectedItem.is_disputed" class="bg-orange-50 border-l-4 border-orange-400 p-3">
                                 <p class="text-xs font-bold text-orange-700 uppercase">Employee Query</p>
                                 <p class="text-sm text-gray-700 mt-1">"{{ selectedItem.dispute_reason }}"</p>
                             </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Claimed Amount</label>
                                <div class="text-2xl font-black text-gray-900">{{ selectedItem.claimed_display || formatCurrency(selectedItem.claimed) }}</div>
                            </div>

                            <div v-if="selectedItem.status === 'Submitted'">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Verified Amount</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500 text-sm">₹</span>
                                    <input 
                                        type="number" 
                                        v-model="form.verified_amount" 
                                        class="w-full pl-7 rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="Enter approved amount"
                                    >
                                </div>
                                <p class="text-sm text-gray-400 mt-1">Override if receipt amount is lower.</p>
                            </div>

                            <div v-if="selectedItem.status === 'Submitted'">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Remarks / Rejection Reason</label>
                                <textarea 
                                    v-model="form.remarks" 
                                    rows="3" 
                                    id="remarksField"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Optional for approval, required for rejection."
                                ></textarea>
                            </div>

                             <!-- Status Display for Processed Items -->
                            <div v-if="selectedItem.status !== 'Submitted'" class="p-4 rounded-lg bg-gray-50 border border-gray-200">
                                <span class="block text-xs font-bold text-gray-500 uppercase">Current Status</span>
                                <span 
                                    class="text-lg font-bold"
                                    :class="selectedItem.status === 'Verified' ? 'text-emerald-600' : 'text-red-600'"
                                >
                                    {{ selectedItem.status }}
                                </span>
                                <p class="text-xs text-gray-600 mt-2" v-if="selectedItem.remarks">
                                    "{{ selectedItem.remarks }}"
                                </p>
                            </div>
                        </div>

                        <!-- Buttons -->
                         <div v-if="selectedItem.status === 'Submitted'" class="grid grid-cols-2 gap-3 mt-auto">
                            <button 
                                @click="reject" 
                                class="px-4 py-2 bg-white border border-red-200 text-red-600 rounded-lg text-sm font-bold hover:bg-red-50 transition"
                                :disabled="processing"
                            >
                                Reject
                            </button>
                            <button 
                                @click="approve" 
                                class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition shadow-lg shadow-emerald-200"
                                :disabled="processing"
                            >
                                Approve
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useToastStore } from '@/stores/toast';

defineOptions({ layout: MainLayout });

const props = defineProps({
    queue: Array,
    filters: Object
});

const toast = useToastStore();
const selectedItem = ref(null);
const activeProofId = ref(null);
const selectedProofUrl = ref(null);
const processing = ref(false);

const form = reactive({
    verified_amount: '',
    remarks: ''
});

const selectItem = (item) => {
    selectedItem.value = item;
    // Reset Form
    form.verified_amount = item.verified || item.claimed;
    form.remarks = '';
    
    // Auto-select first proof
    if (item.proofs.length > 0) {
        previewProof(item.proofs[0]);
    } else {
        selectedProofUrl.value = null;
        activeProofId.value = null;
    }
};

const previewProof = (proof) => {
    activeProofId.value = proof.id;
    // Assuming public storage link or route to view
    selectedProofUrl.value = `/storage/${proof.file_path}`; 
};

const approve = () => {
    if (!selectedItem.value) return;
    processing.value = true;
    router.post(route('hr.tax.proofs.verify'), {
        id: selectedItem.value.id,
        type: selectedItem.value.type,
        verified_amount: form.verified_amount,
        remarks: form.remarks
    }, {
        onSuccess: () => {
            toast.success("Approved successfully");
            selectedItem.value = null;
            processing.value = false;
        },
        onError: () => processing.value = false
    });
};

const reject = () => {
    if (!selectedItem.value) return;
    // If no remarks, focus remark field
    if (!form.remarks) {
        toast.error("Please provide a rejection reason.");
        document.getElementById('remarksField')?.focus();
        return;
    }
    processing.value = true;
    router.post(route('hr.tax.proofs.reject'), {
        id: selectedItem.value.id,
        type: selectedItem.value.type,
        reason: form.remarks
    }, {
        onSuccess: () => {
            toast.success("Rejected successfully");
            selectedItem.value = null;
            processing.value = false;
        },
        onError: () => processing.value = false
    });
};

// Keyboard Shortcuts
onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

const handleKeydown = (e) => {
    // Ignore if input is focused
    if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;
    
    if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
        e.preventDefault();
        selectNext();
    }
    if (e.key.toLowerCase() === 'a') {
        e.preventDefault();
        if (selectedItem.value && selectedItem.value.status === 'Submitted') approve();
    }
    if (e.key.toLowerCase() === 'r') {
        e.preventDefault();
        if (selectedItem.value && selectedItem.value.status === 'Submitted') {
            document.getElementById('remarksField')?.focus();
        }
    }
};

const selectNext = () => {
    if (!props.queue.length) return;
    if (!selectedItem.value) {
        selectItem(props.queue[0]);
        return;
    }
    const idx = props.queue.findIndex(i => i.id === selectedItem.value.id && i.type === selectedItem.value.type);
    if (idx < props.queue.length - 1) {
        selectItem(props.queue[idx+1]);
    }
};

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(val || 0);
const formatDate = (d) => new Date(d).toLocaleDateString();
</script>
