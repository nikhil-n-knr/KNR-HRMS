<template>
    <div class="h-[calc(100vh-160px)] flex flex-col space-y-8">
        <!-- Campaigns Header -->
        <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-8 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 shadow-sm border border-purple-100">
                    <i class="fas fa-route text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Journey Maps</h2>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Multi-Stage Customer Experience Design</p>
                </div>
            </div>
            <button @click="showLaunchModal = true" class="px-8 py-3.5 bg-indigo-600 text-white rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl flex items-center gap-2">
                <i class="fas fa-magic"></i> Launch Campaign
            </button>
        </div>

        <!-- Launch Modal -->
        <div v-if="showLaunchModal" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-[40px] shadow-2xl max-w-lg w-full overflow-hidden border border-white">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight">Initiate Journey</h3>
                    <button @click="showLaunchModal = false" class="text-gray-400 hover:text-gray-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-8 space-y-6 text-left">
                    <div class="space-y-4">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block ml-1">Journey Name</label>
                        <input v-model="journeyForm.name" type="text" placeholder="e.g. Q1 Education Drip" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
                    </div>
                    <div class="space-y-4">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block ml-1">Campaign Type</label>
                        <select v-model="journeyForm.type" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/10">
                            <option value="broadcast">Instant Broadcast</option>
                            <option value="drip">Automated Drip Series</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 pt-6">
                        <button @click="showLaunchModal = false" class="px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest text-gray-400 hover:bg-gray-50">Cancel</button>
                        <button @click="submitJourney" class="px-8 py-3 bg-indigo-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-700 shadow-xl shadow-indigo-100">
                            Create Journey
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 sticky top-0 z-10">
             <div class="bg-indigo-600 rounded-[40px] p-8 text-white relative overflow-hidden shadow-2xl shadow-indigo-200">
                 <div class="relative z-10">
                    <p class="text-indigo-200 text-[10px] font-black uppercase tracking-[0.3em] mb-4">Total Engagement</p>
                    <h3 class="text-5xl font-black tracking-tighter mb-2">94.2<span class="text-indigo-300 text-2xl">%</span></h3>
                    <p class="text-xs font-bold text-indigo-100/60 uppercase tracking-widest">Across all active funnels</p>
                 </div>
                 <div class="absolute -right-10 -bottom-10 opacity-10 text-[200px]">
                     <i class="fas fa-chart-line"></i>
                 </div>
             </div>
             <div class="bg-white rounded-[40px] p-8 border border-gray-100 shadow-sm flex items-center justify-between">
                 <div>
                    <p class="text-gray-400 text-[10px] font-black uppercase tracking-[0.3em] mb-4">Active Journeys</p>
                    <h3 class="text-5xl font-black text-gray-900 tracking-tighter mb-2">{{ journeys.length }}</h3>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <p class="text-xs font-bold text-emerald-500 uppercase tracking-widest">Real-time Sync Active</p>
                    </div>
                 </div>
                 <div class="flex -space-x-3">
                     <div v-for="i in 4" :key="i" class="w-12 h-12 rounded-2xl border-4 border-white bg-gray-100 flex items-center justify-center text-xs font-black text-gray-400">
                        {{ i }}
                     </div>
                 </div>
             </div>
        </div>

        <!-- Journeys List -->
        <div class="flex-1 overflow-y-auto pb-12">
            <div v-if="journeys.length > 0" class="space-y-6">
                <div v-for="journey in journeys" :key="journey.id" class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-8 hover:shadow-xl transition-all group flex items-center gap-8 text-left">
                    <div class="w-20 h-20 bg-gray-50 rounded-[28px] flex items-center justify-center text-gray-400 text-2xl border border-gray-100">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-1 text-left">
                            <h4 class="text-lg font-black text-gray-900 leading-tight text-left">{{ journey.name }}</h4>
                            <span class="px-3 py-0.5 bg-purple-50 text-purple-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-purple-100">
                                {{ journey.steps_count }} STEPS
                            </span>
                        </div>
                        <p class="text-sm text-gray-400 font-bold uppercase tracking-widest text-left">Last updated {{ journey.updated_at || 'just now' }}</p>
                    </div>
                    <div class="flex gap-4 items-center">
                        <div class="text-right mr-4">
                            <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest mb-1">Conversion</p>
                            <p class="text-xl font-black text-gray-900">12.5%</p>
                        </div>
                        <button class="h-14 px-8 bg-gray-50 text-gray-400 font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-gray-100 hover:text-gray-900 transition-all">
                            Analyze
                        </button>
                    </div>
                </div>
            </div>
            <div v-else class="h-64 bg-white rounded-[40px] border border-gray-100 border-dashed flex flex-col items-center justify-center text-center p-12">
                <p class="text-xs font-black text-gray-300 uppercase tracking-widest">No strategic campaigns active</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    journeys: { type: Array, default: () => [] }
});

const showLaunchModal = ref(false);
const journeyForm = ref({
    name: '',
    type: 'drip'
});

const submitJourney = () => {
    router.post(route('crm.campaign-journeys.store'), journeyForm.value, {
        onSuccess: () => {
            showLaunchModal.value = false;
            journeyForm.value = { name: '', type: 'drip' };
        }
    });
};
</script>
