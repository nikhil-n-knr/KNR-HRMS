<template>
    <div class="space-y-6">
        <!-- ML Analytics Overview Section -->
        <div class="bg-violet-900 p-12 rounded-[60px] shadow-2xl shadow-violet-500/20 text-white min-h-[700px] relative overflow-hidden group">
            <div class="flex justify-between items-start mb-20 relative z-10">
                <div>
                     <p class="text-sm font-black uppercase tracking-widest text-violet-300 mb-2">Advanced Machine Learning Engine</p>
                    <h2 class="text-4xl font-black text-white tracking-tight flex items-center gap-6">
                        ML Insights & Forecasting
                        <span class="px-5 py-2 rounded-2xl bg-white/10 text-emerald-400 border border-white/10 text-xs font-black uppercase tracking-widest animate-pulse shadow-inner">
                            Model Confidence: 94%
                        </span>
                    </h2>
                    <p class="text-sm text-violet-200/60 font-medium tracking-wide mt-2">Real-time predictive analytics based on 500+ data variables</p>
                </div>
                <div class="flex gap-2">
                    <button class="px-6 py-3 bg-white/10 text-white rounded-2xl text-sm font-black border border-white/10 hover:bg-white/20 transition-all uppercase tracking-widest shadow-xl backdrop-blur-md">🔍 RETRAIN MODEL</button>
                    <button class="px-6 py-3 bg-indigo-600 text-white rounded-2xl text-sm font-black shadow-lg shadow-indigo-500/30 hover:scale-105 transition-all uppercase tracking-widest border border-indigo-500 overflow-hidden group/btn">
                         <span class="relative z-10">EXPORT ML REPORT</span>
                         <div class="absolute inset-0 bg-white/20 translate-x-[-100%] group-hover/btn:translate-x-[100%] transition-transform duration-700"></div>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 relative z-10 mb-20">
                 <!-- LTV Prediction Widget -->
                 <div class="p-10 rounded-[50px] border border-white/10 bg-white/5 backdrop-blur-3xl hover:bg-white/10 transition-all group/item overflow-hidden relative">
                     <h3 class="text-lg font-black tracking-widest uppercase text-violet-300 mb-8"><i class="fas fa-layer-group text-emerald-400 mr-4"></i> LTV FORECASTING</h3>
                     <div class="flex flex-col gap-6">
                         <div class="flex justify-between items-end border-b border-white/10 pb-6">
                             <div>
                                 <p class="text-sm font-black text-violet-400 uppercase tracking-widest mb-1">Current MTD Revenue</p>
                                 <p class="text-4xl font-black text-white tracking-tighter">{{ ltv_prediction.current_total }}</p>
                             </div>
                             <div class="text-right">
                                 <p class="text-sm font-black text-emerald-400 uppercase tracking-widest mb-1">Predicted +90d</p>
                                 <p class="text-2xl font-black text-white tracking-tighter">{{ ltv_prediction.predicted_90d }}</p>
                             </div>
                         </div>
                         <div class="flex items-center gap-6">
                              <div class="w-16 h-16 rounded-3xl bg-emerald-500/20 flex items-center justify-center text-4xl text-emerald-400 border border-emerald-500/30">
                                  <i class="fas fa-chart-line"></i>
                              </div>
                              <div>
                                  <p class="text-lg font-black text-white leading-none tracking-tight">34% Projected Increase</p>
                                  <p class="text-xs font-bold text-violet-300/60 mt-2 uppercase tracking-widest">Based on current conversion velocity</p>
                              </div>
                         </div>
                     </div>
                      <i class="fas fa-brain absolute -right-8 -top-8 text-[120px] text-white/5 opacity-50 group-hover/item:scale-125 transition-transform duration-1000"></i>
                 </div>

                 <!-- Churn Risk Widget -->
                 <div class="p-10 rounded-[50px] border border-white/10 bg-white/5 backdrop-blur-3xl hover:bg-white/10 transition-all group/item overflow-hidden relative flex flex-col justify-between">
                      <h3 class="text-lg font-black tracking-widest uppercase text-violet-300 mb-8"><i class="fas fa-exclamation-triangle text-rose-400 mr-4"></i> CHURN PREVENTION AI</h3>
                      <div class="space-y-6">
                          <div v-for="risk in churn_risk" :key="risk.account" class="p-5 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all flex items-center justify-between">
                              <div class="flex gap-4 items-center">
                                  <div class="w-10 h-10 rounded-xl bg-violet-800/50 flex items-center justify-center text-white font-black">{{ risk.account.charAt(0) }}</div>
                                  <div>
                                      <p class="text-sm font-black text-white">{{ risk.account }}</p>
                                      <p class="text-sm font-bold text-violet-300/60 mt-1 uppercase leading-none">{{ risk.reason }}</p>
                                  </div>
                              </div>
                              <span class="px-4 py-1 rounded-full text-sm font-black uppercase tracking-widest"
                                :class="risk.risk === 'High' ? 'bg-rose-500 text-white animate-pulse' : 'bg-emerald-500 text-white' "
                              >
                                  {{ risk.risk }} RISK
                              </span>
                          </div>
                      </div>
                      <button class="mt-8 w-full py-4 bg-white text-gray-900 rounded-2xl text-xs font-black uppercase tracking-widest shadow-2xl shadow-rose-500/20 hover:scale-105 transition-all">FIX AT-RISK ACCOUNTS</button>
                      <i class="fas fa-shield-alt absolute -right-8 -bottom-8 text-[120px] text-white/5 opacity-50 group-hover/item:rotate-12 transition-transform duration-1000"></i>
                 </div>
            </div>

            <!-- Visualization: LTV Prediction Curve -->
             <div class="bg-white/5 border border-white/10 rounded-[50px] p-12 min-h-[300px] flex flex-col items-center justify-center relative">
                 <h4 class="text-sm font-black text-violet-300 uppercase tracking-[1em] mb-12 op-80">Predictive Revenue Curve</h4>
                 <div class="w-full flex justify-center items-end gap-2 h-40">
                     <div v-for="h in [20, 35, 30, 45, 60, 55, 75, 90]" :key="h" 
                        class="w-12 bg-gradient-to-t from-violet-600 to-indigo-400 rounded-t-xl transition-all duration-1000 group-hover:opacity-100 opacity-60"
                        :style="{ height: h + '%' }"
                     ></div>
                 </div>
                 <div class="flex gap-10 mt-8">
                     <div class="flex items-center gap-3">
                         <div class="w-3 h-3 rounded-full bg-violet-600"></div>
                         <span class="text-sm font-black uppercase text-violet-300">ACTUAL REVENUE</span>
                     </div>
                     <div class="flex items-center gap-3">
                         <div class="w-3 h-3 rounded-full bg-indigo-400"></div>
                         <span class="text-sm font-black uppercase text-violet-300">AI PREDICTED</span>
                     </div>
                 </div>
             </div>
             
             <!-- Animated Particles Layer -->
             <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-20">
                 <div class="particle w-2 h-2 rounded-full bg-emerald-400 absolute top-1/4 left-1/4 blur-sm animate-pulse"></div>
                 <div class="particle w-3 h-3 rounded-full bg-indigo-400 absolute top-1/2 left-3/4 blur-sm animate-pulse delay-500"></div>
                 <div class="particle w-1 h-1 rounded-full bg-violet-400 absolute top-3/4 left-1/2 blur-sm animate-pulse delay-1000"></div>
             </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    ltv_prediction: Object,
    churn_risk: Array
});
</script>
