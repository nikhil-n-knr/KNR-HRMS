<template>
    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm flex items-center justify-between group overflow-hidden relative">
            <div class="relative z-10">
                <h3 class="text-xl font-black text-gray-900 tracking-tight mb-1 uppercase italic">Subscription Matrix</h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Monitoring Active Revenue Tiers & Persistence</p>
            </div>
            <div class="flex items-center gap-6 relative z-10">
                <div class="text-right">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Monthly MRR Delta</p>
                    <p class="text-lg font-black text-emerald-500 tabular-nums leading-none tracking-tighter">+₹12.4k (↑8%)</p>
                </div>
                <div class="h-10 w-px bg-gray-100 mx-2"></div>
                <button class="px-8 py-3 bg-gray-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all shadow-xl">
                    Export Financial Audit
                </button>
            </div>
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-gray-50 rounded-full opacity-50 group-hover:scale-125 transition-transform duration-1000"></div>
        </div>

        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden pb-12">
            <div class="overflow-x-auto no-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Subscriber Identity</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Active Protocol</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Billing Cycle</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status / Pulse</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Ops</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="i in 5" :key="i" class="hover:bg-gray-50/50 transition-all">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center font-black text-[10px] text-gray-400 uppercase">
                                         U{{ 120 + i }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-gray-900 uppercase italic tracking-tight mb-1">Subscriber Entity #{{ 800 + i }}</p>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Active since Jan 2026</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-xl border border-indigo-100">PRO ARCHITECT</span>
                            </td>
                            <td class="px-8 py-6 text-xs font-black tabular-nums text-gray-600 uppercase">ANNUAL RECURRING</td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-lg shadow-emerald-200"></span>
                                    <span class="text-[10px] font-black text-gray-800 uppercase tracking-widest">Active Access</span>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <button class="text-gray-400 hover:text-indigo-600 transition-colors uppercase text-[10px] font-black tracking-widest">Manage Access</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
// Placeholder for potential props
</script>
