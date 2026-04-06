<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative font-sans text-left">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group no-print">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <i class="fas fa-info text-xs"></i>
            </button>
            <div class="absolute right-0 top-10 w-80 bg-gray-900 text-white text-sm p-6 rounded-[32px] shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium font-sans">
                <div class="font-black tracking-[0.2em] uppercase mb-3 text-indigo-400 border-b border-indigo-500/20 pb-3 text-left font-sans">Sales Pipeline Command</div>
                <p class="text-gray-300 leading-relaxed mb-4 text-left font-sans">The Kanban Board visualizes deal velocity through your revenue funnel. Drag and drop deals across stages to advance lifecycle status and trigger progression workflows.</p>
                <div class="space-y-2 text-left font-sans">
                    <div class="flex items-center gap-3 text-left font-sans"><div class="w-2 h-2 rounded-full bg-indigo-500 shadow-lg shadow-indigo-500/50"></div> <span>Drag-and-Drop Lifecycle Management</span></div>
                    <div class="flex items-center gap-3 text-left font-sans"><div class="w-2 h-2 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/50"></div> <span>Real-Time Stage Valuation</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40 no-print">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 text-left font-sans">
                    <div class="text-left font-sans">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight text-left font-sans">Pipeline Visualization</h2>
                        <div class="flex items-center mt-3 text-left font-sans">
                             <div class="flex -space-x-2 mr-4">
                                <div class="w-6 h-6 rounded-full bg-indigo-600 border-2 border-white"></div>
                                <div class="w-6 h-6 rounded-full bg-emerald-500 border-2 border-white"></div>
                                <div class="w-6 h-6 rounded-full bg-amber-400 border-2 border-white"></div>
                             </div>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">Revenue Velocity Tracking Active</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 text-left font-sans">
                        <div class="relative w-64 group text-left font-sans">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-500 transition-colors text-left font-sans"></i>
                            <input 
                                v-model="search" 
                                type="text" 
                                class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-xl text-sm font-black uppercase focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder-gray-400 shadow-inner text-left font-sans" 
                                placeholder="Find Active Deal..."
                            >
                        </div>
                        <button @click="printView" class="w-12 h-12 bg-white border border-gray-200 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm group relative">
                            <i class="fas fa-print text-sm text-left font-sans"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Kanban Surface -->
            <div class="flex-1 overflow-x-auto p-8 flex gap-8 select-none no-scrollbar bg-gray-50/50" id="print-area">
                <div 
                    v-for="stage in pipelineStages" 
                    :key="stage.id" 
                    class="flex-shrink-0 w-[340px] flex flex-col h-full text-left font-sans"
                >
                    <!-- Stage Header -->
                    <div class="mb-6 px-2 flex justify-between items-end text-left font-sans">
                        <div class="text-left font-sans">
                            <div class="flex items-center gap-2 mb-2 text-left font-sans">
                                <div class="w-2 h-2 rounded-full" :style="`background-color: ${stage.color || '#6366f1'}`"></div>
                                <span class="text-sm font-black uppercase tracking-[0.2em] text-gray-400 text-left font-sans">{{ stage.name }}</span>
                            </div>
                            <h4 class="text-lg font-black text-gray-900 tracking-tight text-left font-sans">${{ formatNumber(getStageValue(stage.name)) }}</h4>
                        </div>
                        <div class="text-right text-left font-sans">
                            <span class="text-sm font-black text-gray-300 uppercase bg-white px-3 py-1 rounded-full border border-gray-100 shadow-sm text-left font-sans">{{ filteredDealsForStage(stage.name).length }} Cards</span>
                        </div>
                    </div>

                    <!-- Stage Column -->
                    <div class="flex-1 overflow-y-auto pr-2 pb-8 space-y-4 no-scrollbar text-left font-sans">
                        <div 
                            v-for="deal in filteredDealsForStage(stage.name)" 
                            :key="deal.id"
                            @click="openDealModal(deal)"
                            class="bg-white p-6 rounded-[30px] border border-gray-100 shadow-sm hover:shadow-2xl hover:shadow-indigo-500/10 hover:-translate-y-1 transition-all cursor-grab active:cursor-grabbing group relative overflow-hidden border-l-4 text-left font-sans"
                            :style="`border-left-color: ${stage.color || '#6366f1'}`"
                        >
                            <div class="absolute -right-2 -top-2 opacity-0 group-hover:opacity-10 scale-50 group-hover:scale-100 transition-all duration-500 text-left font-sans">
                                <i class="fas fa-handshake text-6xl text-left font-sans"></i>
                            </div>

                            <div class="relative text-left font-sans">
                                <div class="flex justify-between items-start mb-4 text-left font-sans">
                                    <div class="text-sm font-black text-indigo-500 uppercase tracking-widest bg-indigo-50 px-2 py-1 rounded-lg text-left font-sans">#{{ deal.id }}</div>
                                    <div class="flex -space-x-1.5 text-left font-sans">
                                        <div class="w-5 h-5 rounded-full bg-gray-100 border border-white flex items-center justify-center text-xs font-black text-gray-400 text-left font-sans">
                                            <i class="fas fa-user-circle text-left font-sans"></i>
                                        </div>
                                    </div>
                                </div>
                                <h5 class="text-sm font-black text-gray-900 mb-2 leading-tight group-hover:text-indigo-600 transition-colors text-left font-sans">{{ deal.title }}</h5>
                                <p class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 text-left font-sans">{{ deal.account?.name || 'Private Target' }}</p>
                                
                                <div class="flex items-center justify-between text-left font-sans">
                                    <div class="text-left font-sans">
                                        <div class="text-sm font-black text-gray-300 uppercase tracking-widest text-left font-sans">Agreement Value</div>
                                        <div class="text-sm font-black text-gray-900 text-left font-sans">${{ formatNumber(deal.value) }}</div>
                                    </div>
                                    <div :class="['px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-widest shadow-sm', getProbColor(deal.probability)]">
                                        {{ deal.probability }}% Score
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty Placeholder -->
                        <div v-if="filteredDealsForStage(stage.name).length === 0" class="h-32 border-2 border-dashed border-gray-100 rounded-[30px] flex items-center justify-center text-gray-300 transition-colors hover:border-indigo-100 group text-left font-sans">
                            <i class="fas fa-plus text-sm opacity-20 group-hover:opacity-50 transition-opacity text-left font-sans"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DEAL INSPECTION MODAL -->
        <div v-if="selectedDeal" @click.self="selectedDeal = null" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-[45px] shadow-2xl max-w-xl w-full p-12 relative overflow-hidden text-left font-sans">
                 <div class="absolute -right-20 -top-20 w-80 h-80 bg-indigo-50 rounded-full blur-3xl opacity-50"></div>
                 
                 <div class="relative text-left font-sans">
                     <div class="flex justify-between items-start mb-10 text-left font-sans">
                         <div class="text-left font-sans">
                             <div class="flex items-center gap-3 mb-3 text-left font-sans">
                                 <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-indigo-100 text-left font-sans">
                                     <i class="fas fa-briefcase text-left font-sans"></i>
                                 </div>
                                 <div class="text-left font-sans">
                                     <h3 class="text-2xl font-black text-gray-900 tracking-tight text-left font-sans">{{ selectedDeal.title }}</h3>
                                     <p class="text-sm font-black text-indigo-500 uppercase tracking-[0.2em] text-left font-sans">{{ selectedDeal.account?.name || 'Enterprise Partner' }}</p>
                                 </div>
                             </div>
                         </div>
                         <button @click="selectedDeal = null" class="w-10 h-10 rounded-xl hover:bg-gray-50 flex items-center justify-center text-gray-400 transition-all text-left font-sans">
                             <i class="fas fa-times text-left font-sans"></i>
                         </button>
                     </div>

                     <div class="grid grid-cols-2 gap-8 mb-12 text-left font-sans">
                         <div class="p-6 bg-gray-50 rounded-[32px] border border-gray-100 text-left font-sans">
                             <span class="text-sm font-black text-gray-400 uppercase tracking-widest block mb-1 text-left font-sans">Project Valuation</span>
                             <span class="text-2xl font-black text-gray-900 text-left font-sans">${{ formatNumber(selectedDeal.value) }}</span>
                         </div>
                         <div class="p-6 bg-indigo-50 rounded-[32px] border border-indigo-100 text-left font-sans">
                             <span class="text-sm font-black text-indigo-400 uppercase tracking-widest block mb-1 text-left font-sans">Yield Probability</span>
                             <div class="flex items-center justify-between text-left font-sans">
                                 <span class="text-2xl font-black text-indigo-600 text-left font-sans">{{ selectedDeal.probability }}%</span>
                                 <i class="fas fa-chart-line text-indigo-200 text-left font-sans"></i>
                             </div>
                         </div>
                     </div>

                     <div class="space-y-6 mb-12 text-left font-sans">
                         <div class="text-left font-sans">
                             <label class="text-sm font-black text-gray-400 uppercase tracking-widest mb-3 block text-left font-sans">Current Lifecycle Phase</label>
                             <div class="flex items-center gap-2 text-left font-sans">
                                 <div 
                                    v-for="s in pipelineStages" 
                                    :key="s.id" 
                                    class="h-1.5 flex-1 rounded-full transition-all duration-500 text-left font-sans"
                                    :class="s.order <= pipelineStages.find(ps => ps.name === selectedDeal.stage)?.order ? 'bg-indigo-600 shadow-sm shadow-indigo-200' : 'bg-gray-100 text-left font-sans'"
                                 ></div>
                             </div>
                             <div class="flex justify-between mt-2 text-left font-sans">
                                 <span class="text-sm font-black text-indigo-600 uppercase tracking-widest text-left font-sans">{{ selectedDeal.stage }}</span>
                                 <span class="text-sm font-bold text-gray-300 uppercase tracking-widest text-left font-sans">Next Milestone: Negotiation</span>
                             </div>
                         </div>
                         <div class="text-left font-sans text-left font-sans">
                             <label class="text-sm font-black text-gray-400 uppercase tracking-widest mb-2 block text-left font-sans">Strategic Briefing</label>
                             <p class="text-xs text-gray-500 font-medium leading-relaxed italic text-left font-sans">"{{ selectedDeal.description || 'Targeting systemic deployment with focus on scalability and architectural integrity.' }}"</p>
                         </div>
                     </div>

                     <div class="flex gap-4 text-left font-sans">
                        <button class="w-14 h-14 rounded-2xl bg-white border border-gray-200 text-gray-400 hover:text-indigo-600 hover:border-indigo-100 flex items-center justify-center transition-all shadow-sm text-left font-sans">
                            <i class="fas fa-history text-left font-sans"></i>
                        </button>
                        <button class="flex-1 bg-indigo-600 text-white py-4 rounded-[24px] font-black text-base uppercase tracking-[0.2em] hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 active:scale-95 text-left font-sans">
                            ACTIVATE EXECUTION WORKFLOW
                        </button>
                     </div>
                 </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    deals: { type: Array, default: () => [] },
    stages: { type: Array, default: () => [] }
});

const selectedDeal = ref(null);
const search = ref('');

const pipelineStages = computed(() => {
    if (props.stages && props.stages.length > 0) {
        return [...props.stages].sort((a, b) => a.order - b.order);
    }
    return [];
});

const filteredDealsForStage = (stageName) => {
    let stageDeals = props.deals?.filter(d => d.stage?.toString().toLowerCase() === stageName?.toString().toLowerCase()) || [];
    if (search.value) {
        const term = search.value.toLowerCase();
        stageDeals = stageDeals.filter(d => 
            d.title.toLowerCase().includes(term) || 
            d.account?.name?.toLowerCase().includes(term)
        );
    }
    return stageDeals;
};

const getStageValue = (stageName) => {
    return props.deals?.filter(d => d.stage?.toString().toLowerCase() === stageName?.toString().toLowerCase())
        .reduce((sum, d) => sum + (parseFloat(d.value) || 0), 0);
};

const formatNumber = (num) => new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(num || 0);

const getProbColor = (prob) => {
    if (prob >= 80) return 'bg-emerald-50 text-emerald-600 border border-emerald-100';
    if (prob >= 50) return 'bg-amber-50 text-amber-600 border border-amber-100';
    return 'bg-rose-50 text-rose-600 border border-rose-100';
};

const openDealModal = (deal) => { selectedDeal.value = deal; };
const printView = () => window.print();
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
@media print { #print-area { padding: 0 !important; } .no-print { display: none !important; } }
</style>
