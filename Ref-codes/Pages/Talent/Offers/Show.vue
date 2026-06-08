<template>
    <TalentLayout>
         <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                 <h2 class="text-2xl font-bold text-gray-900">Offer Preview</h2>
                 <div>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold" :class="statusClass">
                        {{ offer.status }}
                    </span>
                 </div>
            </div>

            <!-- Paper View -->
            <div class="bg-white shadow-lg sm:rounded-lg p-10 min-h-[600px] border border-gray-200">
                <div class="prose max-w-none" v-html="preview_html"></div>
                
                <!-- Signature Block -->
                <div class="mt-12 flex justify-between border-t border-gray-100 pt-8">
                    <div>
                        <p class="font-bold text-gray-900">Authorized Signatory</p>
                        <p class="text-gray-500 text-sm">HR Manager</p>
                    </div>
                    <div>
                        <div v-if="offer.esigned_at" class="text-green-600 font-mono border-2 border-green-600 px-4 py-2 rounded -rotate-6">
                            DIGITALLY SIGNED<br>
                            {{ new Date(offer.esigned_at).toLocaleString() }}
                        </div>
                         <div v-else class="h-16 w-48 border-b border-gray-400">
                            <p class="text-gray-400 text-xs mt-16">Candidate Signature</p>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <!-- Actions Toolbar -->
            <div class="mt-6 flex flex-wrap items-center justify-between gap-4 bg-gray-50 px-4 py-3 rounded-lg border border-gray-200">
                <div class="flex items-center space-x-3">
                     <a :href="route('talent.offers.index')" class="text-sm text-gray-600 hover:text-gray-900 font-medium">
                        &larr; Back to List
                     </a>
                     <div class="h-4 w-px bg-gray-300"></div>
                     <button @click="copyLink" v-if="offer.token" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                        Copy Portal Link
                     </button>
                     <a v-if="offer.token" :href="route('portal.offer.show', offer.token)" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium flex items-center">
                         <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                         Preview as Candidate
                     </a>
                </div>

                <div class="flex space-x-3">
                    <button v-if="offer.status !== 'Withdrawn' && offer.status !== 'Accepted'" @click="withdrawOffer" class="bg-white border border-red-300 text-red-700 px-4 py-2 rounded-md hover:bg-red-50 text-sm font-medium">
                        Withdraw Offer
                    </button>
                    
                    <button class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50 text-sm font-medium hidden">
                        Download PDF
                    </button>

                    <button v-if="offer.status === 'Draft' || offer.status === 'Pending_Docs'" @click="sendOffer" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm font-medium shadow-sm flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        {{ offer.status === 'Draft' ? 'Send Offer Email' : 'Resend Email' }}
                    </button>
                </div>
            </div>

            <!-- Version History Drawer -->
            <div class="fixed inset-y-0 right-0 w-80 bg-white shadow-xl transform transition-transform duration-300 z-40 border-l border-gray-200" :class="showHistory ? 'translate-x-0' : 'translate-x-full'">
                <div class="h-full flex flex-col">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900">Version History</h3>
                        <button @click="showHistory = false" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4 space-y-4">
                        <!-- Current Info -->
                        <div class="p-3 bg-emerald-50 rounded-lg border border-emerald-100">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Current (v{{ (offer.versions?.length || 0) + 1 }})</span>
                                <span class="text-xs text-emerald-600">Now</span>
                            </div>
                            <p class="text-sm font-medium text-gray-900 mt-1">CTC: {{ offer.salary_currency }} {{ Number(offer.salary_amount).toLocaleString() }}</p>
                            <p class="text-xs text-emerald-700 mt-0.5">Active Draft</p>
                        </div>

                        <!-- History List -->
                        <template v-if="offer.versions && offer.versions.length > 0">
                            <div v-for="v in offer.versions" :key="v.id" class="p-3 bg-white rounded-lg border border-gray-200 hover:border-indigo-300 transition group cursor-pointer">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-gray-500">v{{ v.version_number }}</span>
                                    <span class="text-xs text-gray-400">{{ new Date(v.created_at).toLocaleDateString() }}</span>
                                </div>
                                <div class="mt-2 text-sm text-gray-800">
                                    CTC: {{ v.payload.salary_currency }} {{ Number(v.payload.salary_amount).toLocaleString() }}
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ v.payload.designation }}
                                </div>
                                <button class="mt-2 text-xs text-indigo-600 font-medium hover:underline opacity-0 group-hover:opacity-100 transition">View Snapshot</button>
                            </div>
                        </template>
                        <div v-else class="text-center py-8 text-gray-400 text-sm italic">
                            No previous versions found.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toggle Button (Floating) -->
            <button @click="showHistory = !showHistory" class="fixed bottom-8 right-8 bg-white text-gray-700 p-3 rounded-full shadow-lg border border-gray-200 hover:bg-gray-50 z-30 flex items-center gap-2 pr-4 transition-all hover:scale-105">
                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="font-bold text-sm">History</span>
                <span v-if="offer.versions?.length" class="bg-indigo-600 text-white text-sm px-1.5 py-0.5 rounded-full">{{ offer.versions.length }}</span>
            </button>

         </div>
    </TalentLayout>
</template>

<script setup>
import TalentLayout from '@/Layouts/TalentLayout.vue';
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    offer: Object,
    preview_html: String
});

const showHistory = ref(false);

const statusClass = computed(() => {
    const s = props.offer.status;
    if (s === 'Draft') return 'bg-gray-100 text-gray-800';
    if (s === 'Accepted') return 'bg-green-100 text-green-800';
    if (s === 'Sent') return 'bg-blue-100 text-blue-800';
    if (s === 'Withdrawn') return 'bg-red-100 text-red-800';
    return 'bg-gray-100 text-gray-800';
});

const sendOffer = () => {
    if (confirm('Are you sure you want to email this offer to the candidate?')) {
        router.post(route('talent.offers.send', props.offer.id));
    }
};

const withdrawOffer = () => {
    if (confirm('This will invalidate the offer link. Continue?')) {
        router.post(route('talent.offers.withdraw', props.offer.id));
    }
};

const copyLink = () => {
    const url = route('portal.offer.show', props.offer.token);
    navigator.clipboard.writeText(url);
    alert('Offer link copied to clipboard!');
};
</script>
