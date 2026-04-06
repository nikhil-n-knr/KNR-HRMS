<template>
    <div class="space-y-6">
        <!-- Account Journey Mind Map Section -->
        <div class="bg-white p-12 rounded-[50px] border border-gray-100 shadow-md min-h-[700px] relative overflow-hidden group">
            <div class="flex justify-between items-start mb-16 relative z-10">
                <div>
                     <p class="text-sm font-black uppercase tracking-widest text-emerald-500 mb-2">Customer Lifecycle Visualizer</p>
                    <h2 class="text-4xl font-black text-gray-900 tracking-tight flex items-center gap-6">
                        Account Journey Map
                        <span class="px-5 py-1.5 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 text-xs font-black uppercase tracking-widest shadow-inner">
                            LTV: {{ account_journey.ltv }}
                        </span>
                    </h2>
                    <p class="text-lg font-black text-gray-400 mt-2 uppercase tracking-widest">{{ account_journey.name }}</p>
                </div>
                <div class="flex gap-2">
                    <button class="px-8 py-4 bg-gray-900 text-white rounded-2xl text-sm font-black shadow-xl shadow-gray-900/10 hover:-translate-y-1 transition-all uppercase tracking-widest border border-gray-800"><i class="fas fa-play"></i> REPLAY JOURNEY</button>
                    <button class="px-8 py-4 bg-emerald-600 text-white rounded-2xl text-sm font-black shadow-2xl shadow-emerald-500/30 hover:scale-105 transition-all uppercase tracking-widest border border-emerald-500"><i class="fas fa-magic"></i> OPTIMIZE PATH</button>
                </div>
            </div>

            <!-- Zoomable Mind Map Mock -->
            <div class="relative py-20 flex justify-center items-center h-[500px]">
                <div class="absolute inset-x-0 bottom-0 top-0 bg-[radial-gradient(#f1f5f9_1px,transparent_1px)] [background-size:32px_32px] opacity-60"></div>
                
                <div class="relative z-10 flex flex-col items-center gap-20 scale-110">
                    <!-- Root: Account -->
                    <div class="w-64 p-8 bg-gray-900 text-white rounded-[40px] shadow-2xl relative group/root hover:scale-110 transition-all cursor-crosshair border-8 border-gray-800 ring-8 ring-gray-50 ring-offset-4 ring-offset-white">
                        <div class="flex flex-col items-center text-center gap-4">
                            <div class="w-20 h-20 rounded-3xl bg-emerald-500 text-white flex items-center justify-center text-4xl shadow-inner border-4 border-emerald-600 overflow-hidden rotate-3 group-hover/root:rotate-12 transition-transform duration-500">
                                <i class="fas fa-building drop-shadow-lg"></i>
                            </div>
                            <span class="text-sm font-black uppercase tracking-widest">{{ account_journey.name }}</span>
                        </div>
                         <!-- Animated Connections -->
                        <div class="absolute inset-0 z-[-1] animate-pulse blur-3xl bg-emerald-500/10"></div>
                    </div>

                    <div class="flex justify-center gap-16 relative">
                         <!-- Milestone Nodes -->
                        <div v-for="(milestone, idx) in account_journey.milestones" :key="milestone.type" class="relative">
                            <!-- Visual Connection Line -->
                            <div v-if="idx < account_journey.milestones.length" class="absolute h-20 w-1 bg-gradient-to-b from-gray-200 to-transparent top-[-80px] left-1/2 -translate-x-1/2 opacity-40"></div>
                            
                            <div class="w-48 p-6 bg-white border border-gray-100 rounded-3xl shadow-lg hover:shadow-2xl hover:-translate-y-2 transition-all relative group/node cursor-pointer">
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-sm font-black shadow-inner border border-emerald-100 uppercase tracking-widest leading-none">{{ milestone.date }}</div>
                                <div class="flex flex-col items-center text-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-50 text-gray-400 group-hover/node:bg-emerald-50 group-hover/node:text-emerald-600 flex items-center justify-center text-xl transition-all shadow-inner border border-white">
                                        <i :class="getIcon(milestone.type)"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-gray-900 uppercase tracking-widest leading-none mb-1">{{ milestone.type }}</p>
                                        <p class="text-sm font-bold text-gray-400 uppercase tracking-tighter">{{ milestone.action }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="absolute bottom-10 right-10 flex gap-4">
                 <div class="p-6 bg-indigo-50 border border-indigo-100 rounded-3xl shadow-xl max-w-xs transition-all hover:scale-105">
                     <p class="text-sm font-black text-indigo-500 uppercase tracking-widest mb-2 leading-none">PREDICTIVE ANALYTICS</p>
                     <p class="text-xs font-bold text-gray-900 leading-relaxed uppercase">Likely path to ₹2.5Cr renewal: Next 90 days - <span class="text-indigo-600 font-black">Upsell LMS Pro+</span></p>
                 </div>
                 <div class="p-6 bg-white border border-gray-100 rounded-3xl shadow-xl flex items-center gap-6">
                     <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-400 hover:text-emerald-500 transition-all cursor-pointer">
                         <i class="fas fa-search-plus"></i>
                     </div>
                     <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-400 hover:text-emerald-500 transition-all cursor-pointer">
                         <i class="fas fa-expand"></i>
                     </div>
                     <div class="h-4 w-px bg-gray-100"></div>
                     <span class="text-sm font-black text-gray-400 uppercase tracking-widest pr-4">Mind Map Canvas</span>
                 </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    account_journey: Object
});

const getIcon = (type) => {
    if (type.includes('WhatsApp')) return 'fab fa-whatsapp';
    if (type.includes('Voice')) return 'fas fa-phone-volume';
    if (type.includes('AR')) return 'fas fa-vr-cardboard';
    return 'fas fa-flag-checkered';
}
</script>
