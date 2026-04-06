<template>
    <div class="space-y-10 pb-20">
        <!-- Strategic Intelligence Header -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-8 mb-4">
            <div class="flex items-center gap-6">
                <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-[22px] flex items-center justify-center text-white shadow-xl shadow-indigo-200 rotate-6 transition-transform hover:rotate-0">
                    <i class="fas fa-file-contract text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight leading-none">Intelligence Registry</h2>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mt-3 block leading-none">Unified Compliance Extraction Terminal</p>
                </div>
            </div>

            <div class="flex items-center gap-4 bg-white/60 backdrop-blur-xl p-2 rounded-[24px] border border-white shadow-xl shadow-indigo-500/5">
                <div class="relative group">
                    <i class="fas fa-magnifying-glass absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" placeholder="QUERY REGISTRY..." class="bg-slate-100/50 border-transparent rounded-[18px] py-3 pl-12 pr-6 text-sm font-black uppercase tracking-widest text-slate-600 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all w-48 xl:w-64">
                </div>
                <button class="px-6 py-3 bg-slate-900 text-white rounded-[18px] text-sm font-black uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-lg flex items-center gap-3">
                    <i class="fas fa-file-export text-xs"></i>
                    GENERATE AUDIT
                </button>
            </div>
        </div>

        <!-- Compliance Data Matrix -->
        <div class="grid grid-cols-1 gap-6">
            <div 
                v-for="i in 5" 
                :key="i" 
                class="group bg-white/70 backdrop-blur-xl p-8 rounded-[32px] border border-white shadow-2xl shadow-indigo-500/5 hover:shadow-indigo-500/10 transition-all duration-500 hover:scale-[1.01] flex flex-col xl:flex-row xl:items-center justify-between gap-8"
            >
                <div class="flex items-center gap-6 flex-1">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-50 to-indigo-100/50 border border-white flex items-center justify-center text-indigo-600 font-black text-xs shadow-sm group-hover:rotate-6 transition-transform">
                        JD
                    </div>
                    <div>
                        <div class="text-lg font-black text-slate-800 tracking-tight group-hover:text-indigo-600 transition-colors">Operative JD-00{{ i }}</div>
                        <div class="flex items-center gap-3 mt-2">
                            <span class="text-sm font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-id-badge text-indigo-400"></i>
                                DEPLOYMENT ID: 00{{ i }}
                            </span>
                            <span class="w-1 h-1 bg-slate-200 rounded-full"></span>
                            <span class="text-sm font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-building text-violet-400"></i>
                                SECTOR: ALPHA-7
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-10 flex-1 justify-between xl:justify-end">
                    <div class="space-y-2">
                        <p class="text-sm font-black text-slate-300 uppercase tracking-widest text-right xl:text-left">Protocol Type</p>
                        <div class="flex items-center gap-3 px-4 py-2 bg-slate-100/50 rounded-xl border border-slate-100">
                            <i class="fas fa-calendar-check text-indigo-400 text-xs"></i>
                            <span class="text-sm font-black text-slate-600 uppercase tracking-widest">Attendance Regularization</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <p class="text-sm font-black text-slate-300 uppercase tracking-widest text-right">Audit Timestamp</p>
                        <p class="text-base font-black text-slate-600 uppercase tracking-widest text-right">24 FEB 2026</p>
                    </div>

                    <div class="space-y-2 flex flex-col items-end">
                        <p class="text-sm font-black text-slate-300 uppercase tracking-widest text-right">Verification Status</p>
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-black uppercase tracking-widest transition-all duration-500" 
                            :class="i % 2 === 0 ? 'bg-emerald-50 text-emerald-600 shadow-sm ring-1 ring-emerald-200' : 'bg-rose-50 text-rose-600 shadow-sm ring-1 ring-rose-200'">
                            <i class="fas fa-circle text-[6px] mr-2 animate-pulse"></i>
                            {{ i % 2 === 0 ? 'Verified' : 'In Revision' }}
                        </span>
                    </div>

                    <button class="w-12 h-12 rounded-2xl bg-white border border-slate-100 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-lg transition-all group-hover:scale-110">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// Intelligence Registry Logic Ingested
</script>
