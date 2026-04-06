<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative font-sans text-left">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group no-print">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <i class="fas fa-info text-xs"></i>
            </button>
            <div class="absolute right-0 top-10 w-80 bg-gray-900 text-white text-sm p-6 rounded-[32px] shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium font-sans">
                <div class="font-black tracking-[0.2em] uppercase mb-3 text-indigo-400 border-b border-indigo-500/20 pb-3 text-left">Achievements Lab</div>
                <p class="text-gray-300 leading-relaxed mb-4 text-left font-sans">The Excellence Matrix tracks high-velocity performance across your global sales cohorts. Earn badges through strategic resonance and milestone penetration.</p>
                <div class="space-y-2 text-left">
                    <div class="flex items-center gap-3 text-left font-sans font-sans"><div class="w-2 h-2 rounded-full bg-indigo-500 shadow-lg shadow-indigo-500/50 text-left font-sans"></div> <span>Performance Milestone Synthesis</span></div>
                    <div class="flex items-center gap-3 text-left font-sans font-sans"><div class="w-2 h-2 rounded-full bg-amber-500 shadow-lg shadow-amber-500/50 text-left font-sans"></div> <span>Reputation Score Propagation</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40 no-print text-left font-sans">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 text-left font-sans text-left font-sans">
                    <div class="text-left font-sans text-left font-sans text-left font-sans">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight text-left font-sans text-left font-sans">Excellence Ledger</h2>
                        <div class="flex items-center mt-3 text-left font-sans text-left font-sans text-left font-sans">
                            <i class="fas fa-trophy text-indigo-500 mr-3 text-left font-sans text-left font-sans text-left font-sans"></i>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans text-left font-sans">Reputation Sync: Synchronized</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-left font-sans text-left font-sans">
                         <button @click="printView" class="w-12 h-12 bg-white border border-gray-200 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm group relative text-left font-sans">
                            <i class="fas fa-print text-sm text-left font-sans"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Achievement Matrix Surface -->
            <div class="flex-1 overflow-auto p-8 space-y-12 text-left font-sans text-left font-sans" id="print-area">
                <!-- User Rank & Points Pulse -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 text-left font-sans text-left font-sans">
                    <div class="lg:col-span-2 bg-indigo-900 p-12 rounded-[50px] text-white shadow-2xl relative overflow-hidden group border-l-8 border-l-indigo-400 text-left font-sans text-left font-sans">
                        <div class="absolute -right-20 -top-20 w-80 h-80 bg-white/10 rounded-full blur-3xl group-hover:bg-white/20 transition-all duration-700 text-left font-sans"></div>
                        <div class="relative flex items-center gap-12 text-left font-sans text-left font-sans">
                             <div class="w-32 h-32 bg-white/10 rounded-[40px] flex items-center justify-center border border-white/20 shadow-inner group-hover:rotate-6 transition-all duration-500 text-left font-sans text-left font-sans">
                                 <i class="fas fa-medal text-5xl text-indigo-300 text-left font-sans text-left font-sans"></i>
                             </div>
                             <div class="text-left font-sans text-left font-sans">
                                 <h3 class="text-3xl font-black mb-2 tracking-tight text-left font-sans text-left font-sans">Master Artisan Rank</h3>
                                 <p class="text-sm text-indigo-400 font-bold uppercase tracking-[0.2em] mb-8 text-left font-sans text-left font-sans text-left font-sans">Current Resonance Score: 12,450 XP</p>
                                 <div class="w-96 text-left font-sans text-left font-sans">
                                     <div class="flex justify-between text-base font-black uppercase tracking-widest mb-3 opacity-70 text-left font-sans text-left font-sans">
                                         <span>Progress to Elite Tier</span>
                                         <span>85%</span>
                                     </div>
                                     <div class="h-2 bg-white/10 rounded-full overflow-hidden shadow-inner text-left font-sans text-left font-sans">
                                         <div class="h-full bg-indigo-400 rounded-full w-[85%] shadow-lg shadow-indigo-500/50 text-left font-sans text-left font-sans"></div>
                                     </div>
                                 </div>
                             </div>
                        </div>
                    </div>

                    <div class="bg-white p-12 rounded-[50px] shadow-sm border border-gray-100 flex flex-col justify-between border-b-8 border-b-emerald-400 text-left font-sans">
                        <div class="text-left font-sans text-left font-sans">
                            <div class="text-sm font-black text-gray-400 uppercase tracking-widest mb-4 text-left font-sans text-left font-sans">Total Achievements</div>
                            <div class="text-5xl font-black text-gray-900 text-left font-sans text-left font-sans">24 <span class="text-emerald-500">/ 50</span></div>
                        </div>
                        <p class="text-base text-gray-500 font-medium leading-relaxed italic border-l-4 border-emerald-100 pl-4 mt-8 text-left font-sans text-left font-sans">
                             "Strategic achievement unlocked: 'The Closer' - won 5 high-velocity deals this cycle."
                        </p>
                    </div>
                </div>

                <!-- Badges Matrix -->
                <div class="space-y-8 text-left font-sans text-left font-sans">
                    <h4 class="text-sm font-black text-gray-900 tracking-[0.2em] uppercase text-left font-sans text-left font-sans flex items-center items-center">
                        <i class="fas fa-shield-halved mr-3 text-indigo-500 text-left font-sans text-left font-sans"></i>
                        Earned Recognition Hub
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 text-left font-sans text-left font-sans">
                        <div v-for="badge in (badges || [])" :key="badge.id" 
                             class="bg-white p-10 rounded-[50px] shadow-sm border border-gray-100 group hover:shadow-2xl hover:-translate-y-2 transition-all relative overflow-hidden text-left font-sans text-left font-sans">
                            <i :class="[badge.icon || 'fas fa-award', 'absolute -right-4 -bottom-4 text-[120px] text-gray-50 opacity-0 group-hover:opacity-100 transition-opacity duration-700 text-left font-sans text-left font-sans']"></i>
                            <div class="relative z-10 text-left font-sans text-left font-sans">
                                <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-3xl flex items-center justify-center mb-6 shadow-inner group-hover:scale-110 transition-transform text-left font-sans text-left font-sans">
                                    <i :class="[badge.icon || 'fas fa-award', 'text-2xl text-left font-sans text-left font-sans']"></i>
                                </div>
                                <h5 class="text-xl font-black text-gray-900 mb-2 tracking-tight text-left font-sans text-left font-sans">{{ badge.name }}</h5>
                                <p class="text-base text-gray-500 font-medium leading-relaxed line-clamp-2 text-left font-sans text-left font-sans text-left font-sans">{{ badge.description || 'System-recognized performance excellence.' }}</p>
                                
                                <div class="mt-8 pt-8 border-t border-gray-50 flex justify-between items-center text-left font-sans text-left font-sans">
                                     <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-sm font-black uppercase tracking-widest border border-emerald-100 text-left font-sans text-left font-sans">EARNED</span>
                                     <span class="text-sm font-black text-gray-400 uppercase tracking-widest text-left font-sans text-left font-sans">2D AGO</span>
                                </div>
                            </div>
                        </div>

                        <!-- Locked Badges -->
                        <div v-for="i in 3" :key="i" 
                             class="bg-gray-50/50 p-10 rounded-[50px] border border-dashed border-gray-200 group relative opacity-60 hover:opacity-100 transition-all text-left font-sans text-left font-sans">
                             <div class="relative z-10 text-left font-sans text-left font-sans">
                                <div class="w-16 h-16 bg-white text-gray-300 rounded-3xl flex items-center justify-center mb-6 shadow-sm border border-gray-100 text-left font-sans text-left font-sans">
                                    <i class="fas fa-lock text-xl text-left font-sans"></i>
                                </div>
                                <h5 class="text-xl font-black text-gray-300 mb-2 tracking-tight text-left font-sans text-left font-sans">Protocol Locked</h5>
                                <p class="text-base text-gray-400 font-medium leading-relaxed text-left font-sans text-left font-sans text-left font-sans text-left font-sans">Synthesize 50 leads to unlock this operational recognition node.</p>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- Global Leaderboard -->
                <div class="bg-white rounded-[50px] shadow-sm border border-gray-100 overflow-hidden text-left font-sans">
                     <div class="px-12 py-10 border-b border-gray-50 bg-gray-50/20 flex justify-between items-center text-left font-sans">
                         <div class="text-left font-sans text-left font-sans">
                             <h3 class="text-2xl font-black text-gray-900 tracking-tight text-left font-sans text-left font-sans">Leaderboard Matrix</h3>
                             <p class="text-sm font-black text-gray-400 uppercase tracking-widest mt-2 text-left font-sans text-left font-sans">Global Performance Distribution</p>
                         </div>
                         <div class="flex gap-4 text-left font-sans text-left font-sans">
                              <span class="px-5 py-2.5 bg-indigo-50 text-indigo-600 rounded-2xl text-sm font-black uppercase tracking-widest text-left font-sans text-left font-sans">MONTHLY CYCLE</span>
                         </div>
                     </div>
                     <div class="p-8 text-left font-sans text-left font-sans">
                         <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left font-sans text-left font-sans">
                             <div v-for="(user, index) in leaderboard" :key="user.id" 
                                  class="flex items-center gap-6 p-6 rounded-[32px] border transition-all hover:bg-gray-50 text-left font-sans"
                                  :class="index === 0 ? 'bg-indigo-50/50 border-indigo-200' : 'bg-white border-gray-100'">
                                 <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-lg font-black shrink-0 shadow-sm text-left font-sans"
                                      :class="index === 0 ? 'bg-indigo-600 text-white shadow-indigo-200' : 'bg-gray-100 text-gray-400'">
                                     {{ index + 1 }}
                                 </div>
                                 <div class="flex-1 overflow-hidden text-left font-sans">
                                     <div class="text-base font-black text-gray-900 truncate text-left font-sans text-left font-sans">{{ user.user?.name || 'Candidate ' + (index+1) }}</div>
                                     <div class="text-sm font-bold text-gray-400 uppercase tracking-widest mt-1 text-left font-sans text-left font-sans">{{ user.total_points }} REPUTATION</div>
                                 </div>
                                 <div v-if="index === 0" class="text-indigo-500 text-left font-sans">
                                     <i class="fas fa-crown text-left font-sans"></i>
                                 </div>
                             </div>
                         </div>
                     </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    badges: { type: Array, default: () => [] },
    leaderboard: { type: Array, default: () => [] },
    monthly_best: { type: [String, Number], default: null },
    top_daily: { type: String, default: '' },
});

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
