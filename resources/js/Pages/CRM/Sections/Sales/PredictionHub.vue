<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative font-sans text-left">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group no-print">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <i class="fas fa-info text-xs"></i>
            </button>
            <div class="absolute right-0 top-10 w-80 bg-gray-900 text-white text-sm p-6 rounded-[32px] shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium font-sans">
                <div class="font-black tracking-[0.2em] uppercase mb-3 text-indigo-400 border-b border-indigo-500/20 pb-3 text-left">AI Prediction Hub</div>
                <p class="text-gray-300 leading-relaxed mb-4 text-left font-sans">The Prediction Engine synthesizes historical patterns and real-time behavioral signals to project enterprise-level outcomes. Identify churn risks and high-velocity expansion opportunities before they materialize.</p>
                <div class="space-y-2 text-left">
                    <div class="flex items-center gap-3 text-left font-sans"><div class="w-2 h-2 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/50"></div> <span>Behavioral Pattern Resonance</span></div>
                    <div class="flex items-center gap-3 text-left font-sans"><div class="w-2 h-2 rounded-full bg-indigo-500 shadow-lg shadow-indigo-500/50"></div> <span>Predictive Risk Neutralization</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40 no-print text-left font-sans">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 text-left font-sans">
                    <div class="text-left font-sans">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight text-left font-sans">Intelligence Engine</h2>
                        <div class="flex items-center mt-3 text-left font-sans">
                            <i class="fas fa-brain text-indigo-500 mr-3 text-left font-sans"></i>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">Signed AI Prediction: Operational</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-left font-sans">
                         <button @click="printView" class="w-12 h-12 bg-white border border-gray-200 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm group relative text-left font-sans">
                            <i class="fas fa-print text-sm text-left font-sans"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Prediction Matrix Surface -->
            <div class="flex-1 overflow-auto p-8 space-y-12 text-left font-sans" id="print-area">
                <!-- Predictive Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-10 text-left font-sans">
                    <div v-for="stat in predictionStats" :key="stat.label" 
                         class="bg-white p-12 rounded-[50px] shadow-sm border border-gray-100 group hover:shadow-2xl transition-all relative overflow-hidden text-left border-l-8"
                         :style="`border-left-color: ${stat.hexColor}`">
                        <div class="absolute -right-4 -top-4 w-32 h-32 rounded-full opacity-0 group-hover:opacity-10 group-hover:scale-150 transition-transform duration-700" :style="`background-color: ${stat.hexColor}`"></div>
                        <div class="relative text-left font-sans">
                            <div class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mb-4 text-left font-sans font-sans">{{ stat.label }}</div>
                            <div class="text-5xl font-black text-gray-900 tracking-tighter text-left font-sans font-sans">{{ stat.value }}</div>
                            <div class="mt-8 flex items-center justify-between text-left font-sans font-sans">
                                <div :class="['text-sm font-black uppercase tracking-widest text-left font-sans', stat.trendUp ? 'text-emerald-500' : 'text-rose-400']">
                                    <i :class="['fas mr-2 text-left font-sans', stat.trendUp ? 'fa-chart-line' : 'fa-chart-bar']"></i>
                                    {{ stat.trend }} Confidence
                                </div>
                                <div class="text-sm font-bold text-gray-300 uppercase tracking-[0.2em] text-left font-sans font-sans">AI Sourced</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deep Intelligence Multi-Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 text-left font-sans">
                    <!-- Behavioral Pattern Matrix -->
                    <div class="bg-indigo-900 rounded-[60px] p-12 text-white shadow-2xl relative overflow-hidden group border-l-8 border-l-indigo-400 text-left font-sans text-left font-sans">
                         <div class="absolute -right-40 -top-40 w-[500px] h-[500px] bg-white/5 rounded-full blur-3xl group-hover:bg-white/10 transition-all duration-1000 text-left font-sans"></div>
                         <div class="relative space-y-12 text-left font-sans">
                            <div class="flex justify-between items-center text-left font-sans text-left font-sans text-left font-sans">
                                <h3 class="text-3xl font-black tracking-tight text-left font-sans">Pattern Resonance</h3>
                                <div class="px-5 py-2.5 bg-white/10 rounded-2xl border border-white/10 text-sm font-black uppercase tracking-widest text-left font-sans">Signal Persistence: High</div>
                            </div>

                            <div class="space-y-10 text-left font-sans">
                                <div v-for="i in 4" :key="i" class="space-y-4 text-left font-sans group/bar">
                                    <div class="flex justify-between text-base font-black uppercase tracking-widest opacity-70 group-hover/bar:opacity-100 transition-opacity text-left font-sans text-left font-sans">
                                        <span class="text-left font-sans text-left font-sans">Signal Flow Cluster {{ i }}</span>
                                        <span class="text-left font-sans text-left font-sans">{{ 100 - i * 12 }}% Probability</span>
                                    </div>
                                    <div class="h-3 bg-white/10 rounded-full overflow-hidden shadow-inner text-left font-sans">
                                        <div class="h-full bg-gradient-to-r from-emerald-400 to-indigo-400 rounded-full transition-all duration-1000 group-hover/bar:shadow-lg group-hover/bar:shadow-emerald-500/20 text-left font-sans font-sans" :style="`width: ${100 - i * 12}%`"></div>
                                    </div>
                                </div>
                            </div>
                         </div>
                    </div>

                    <!-- Risk Analysis & Recommendations -->
                    <div class="space-y-10 text-left font-sans text-left font-sans">
                         <div class="bg-white p-12 rounded-[50px] shadow-sm border border-gray-100 relative group overflow-hidden border-r-8 border-r-rose-400 text-left font-sans">
                             <div class="relative text-left font-sans">
                                 <h4 class="text-sm font-black text-gray-900 mb-8 tracking-[0.2em] uppercase text-left font-sans">CHURN NEUTRALIZATION PULSE</h4>
                                 <div class="space-y-6 text-left font-sans">
                                     <div v-for="risk in risks" :key="risk.entity" class="flex items-center justify-between p-6 bg-gray-50 rounded-3xl border border-gray-100 hover:border-rose-200 transition-all text-left font-sans">
                                         <div class="flex items-center gap-5 text-left font-sans">
                                             <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-rose-500 shadow-sm text-left font-sans">
                                                 <i class="fas fa-exclamation-triangle text-left font-sans"></i>
                                             </div>
                                             <div class="text-left font-sans text-left font-sans text-left font-sans">
                                                 <div class="text-sm font-black text-gray-900 text-left font-sans">{{ risk.entity }}</div>
                                                 <div class="text-sm font-bold text-gray-400 uppercase tracking-widest mt-1 text-left font-sans">{{ risk.reason }}</div>
                                             </div>
                                         </div>
                                         <span class="text-xs font-black text-rose-600 text-left font-sans">{{ risk.score }}% RISK</span>
                                     </div>
                                 </div>
                             </div>
                         </div>

                         <div class="bg-indigo-50 p-12 rounded-[50px] border border-indigo-100 shadow-sm relative overflow-hidden group text-left font-sans">
                             <i class="fas fa-magic absolute -right-6 -bottom-6 text-[120px] text-indigo-100 opacity-40 group-hover:rotate-12 transition-transform text-left font-sans"></i>
                             <div class="relative text-left font-sans">
                                 <h4 class="text-sm font-black text-indigo-900 mb-6 tracking-[0.2em] uppercase text-left font-sans">AI ACTION GENESIS</h4>
                                 <p class="text-base text-indigo-700 font-medium leading-relaxed italic border-l-4 border-indigo-300 pl-4 text-left font-sans">
                                     "Detected expansion signals in the 'Global Enterprise' segment. Recommend dynamic quote synthesis for Tier-A stakeholder."
                                 </p>
                                 <button class="mt-8 px-10 py-5 bg-indigo-600 text-white rounded-[28px] text-sm font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 active:scale-95 text-left font-sans">DEPLOY AI STRATEGY</button>
                             </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const predictionStats = [
    { label: 'Forecast Realization', value: '₹14.2Cr', trend: '+12.4%', trendUp: true, hexColor: '#6366f1' },
    { label: 'Expansion Velocity', value: 'High', trend: '+5.2%', trendUp: true, hexColor: '#10b981' },
    { label: 'Active Signal Points', value: '1.2M', trend: 'Optimum', trendUp: true, hexColor: '#3b82f6' }
];

const risks = [
    { entity: 'Global Tech Corp', reason: 'Declining Interaction Frequency', score: 84 },
    { entity: 'Summit Industries', reason: 'Unresolved Support Tickets (High)', score: 72 },
    { entity: 'Horizon Logistics', reason: 'Delayed Payment Cycle Detected', score: 65 }
];

const printView = () => window.print();
</script>

<style scoped>
/* Custom Hide Scrollbar */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.05); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.1); }

@media print {
    #print-area { padding: 0 !important; }
    .no-print { display: none !important; }
}
</style>
