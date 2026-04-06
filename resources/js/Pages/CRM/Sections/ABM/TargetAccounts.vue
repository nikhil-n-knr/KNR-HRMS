<template>
    <div class="space-y-6">
        <!-- ABM Target List Container -->
        <div class="bg-gray-50/10 p-12 rounded-[50px] border border-gray-100 shadow-sm min-h-[700px] relative overflow-hidden group">
            <div class="flex justify-between items-start mb-16 relative z-10">
                <div>
                     <p class="text-sm font-black uppercase tracking-widest text-indigo-500 mb-2">High-Velocity Targeting Hub</p>
                    <h2 class="text-4xl font-black text-gray-900 tracking-tight flex items-center gap-6 underline decoration-indigo-500 decoration-8 underline-offset-8">
                        ABM Command Target List
                        <span class="px-5 py-2 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-100 text-xs font-black uppercase tracking-widest shadow-inner">
                            500+ Qualified Accounts
                        </span>
                    </h2>
                    <p class="text-sm text-gray-400 font-medium tracking-wide mt-10 uppercase">Enterprise Revenue Focus</p>
                </div>
                <div class="flex gap-4">
                    <button class="px-8 py-4 bg-white text-gray-900 rounded-3xl text-sm font-black shadow-lg border border-gray-100 hover:-translate-y-1 transition-all uppercase tracking-widest">🔍 FILTER ACCOUNTS</button>
                    <button class="px-8 py-4 bg-indigo-600 text-white rounded-3xl text-sm font-black shadow-2xl shadow-indigo-500/30 hover:scale-105 transition-all uppercase tracking-widest border border-indigo-500">TARGET 50 NEW</button>
                </div>
            </div>

            <!-- Enhanced ABM Table Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 relative z-10">
                 <div v-for="acc in abm_accounts" :key="acc.name" 
                    class="p-10 rounded-[50px] border border-gray-200/50 bg-white/60 backdrop-blur-xl hover:bg-white hover:shadow-2xl hover:-translate-y-4 transition-all duration-700 group/acc relative overflow-hidden"
                 >
                      <div class="flex justify-between items-start mb-10">
                           <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center text-3xl font-black shadow-2xl shadow-indigo-500/20 group-hover/acc:rotate-12 transition-transform duration-700">
                               {{ acc.name.charAt(0) }}
                           </div>
                           <div class="flex flex-col items-end">
                                <span class="px-4 py-1.5 rounded-full text-sm font-black uppercase tracking-widest transition-all shadow-sm"
                                    :class="acc.status === 'Warm' ? 'bg-orange-50 text-orange-600 border border-orange-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100 animate-pulse'"
                                >
                                    {{ acc.status }}
                                </span>
                                <span class="text-3xl font-black text-gray-900 mt-4 tracking-tighter">{{ acc.score }} <span class="text-xs text-gray-300 font-bold uppercase tracking-widest">Score</span></span>
                           </div>
                      </div>

                      <h3 class="text-2xl font-black text-gray-900 tracking-tight leading-none mb-2">{{ acc.name }}</h3>
                      <p class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-10">Enterprise SaaS | Value: {{ acc.value }}</p>

                      <div class="space-y-6 border-t border-gray-100 pt-8 mt-10">
                          <div class="flex justify-between items-center text-sm font-black uppercase tracking-widest text-gray-400">
                              <span>Next Action</span>
                              <span class="text-indigo-600">CEO CALL INTRO</span>
                          </div>
                      </div>

                      <div class="mt-12 flex gap-2 translate-y-20 group-hover/acc:translate-y-0 opacity-0 group-hover/acc:opacity-100 transition-all duration-700">
                           <button class="flex-1 py-4 bg-indigo-600 text-white rounded-2xl text-sm font-black shadow-xl hover:scale-[1.05] transition-all uppercase tracking-widest">ENGAGE NOW</button>
                           <button class="w-16 h-14 bg-gray-900 text-white rounded-2xl flex items-center justify-center text-xl shadow-xl hover:-translate-x-2 transition-all"><i class="fas fa-external-link-alt"></i></button>
                      </div>
                      
                      <div class="absolute inset-0 z-[-1] bg-gradient-to-br from-indigo-500/5 to-transparent opacity-0 group-hover/acc:opacity-100 transition-opacity duration-1000"></div>
                 </div>
            </div>

            <!-- Visualization: ABM Funnel Health -->
             <div class="mt-20 p-12 bg-white rounded-[60px] border border-gray-100 shadow-sm relative overflow-hidden flex flex-col items-center">
                 <h4 class="text-xs font-black text-indigo-300 uppercase tracking-[1em] mb-12">ABM Target Velocity</h4>
                 <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden relative">
                      <div class="h-full bg-gradient-to-r from-red-500 via-yellow-500 to-emerald-500 w-[78%]"></div>
                 </div>
                 <div class="flex justify-between w-full mt-4 text-sm font-black text-gray-400 uppercase tracking-widest">
                     <span>RESEARCH</span>
                     <span>ENGAGEMENT</span>
                     <span class="text-emerald-500">DEAL SIGNED</span>
                 </div>
             </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    abm_accounts: Array
});
</script>
