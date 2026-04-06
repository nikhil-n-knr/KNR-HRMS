<template>
    <div class="space-y-6">
        <!-- ABM Overview Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 p-6 rounded-3xl shadow-xl shadow-indigo-500/20 text-white relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-sm font-black uppercase tracking-widest text-indigo-100">Target Accounts</p>
                    <h3 class="text-4xl font-black mt-2 tracking-tight">500+</h3>
                    <div class="mt-4 flex gap-1 items-center bg-white/10 px-3 py-1 rounded-full text-sm font-black border border-white/20 w-fit">
                        <i class="fas fa-arrow-up"></i>
                        <span>NEW THIS MONTH</span>
                    </div>
                </div>
                <div class="absolute -right-8 -bottom-8 text-8xl text-white/10 group-hover:scale-125 transition-transform duration-700">
                    <i class="fas fa-building"></i>
                </div>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-sm font-black uppercase tracking-widest text-gray-400">ABM Engagement</p>
                    <h3 class="text-4xl font-black mt-2 tracking-tight text-emerald-600">94 pts</h3>
                    <p class="text-xs font-bold text-gray-500 mt-2">Avg. Score for High-Value targets</p>
                </div>
                <div class="absolute -right-4 -bottom-4 text-6xl text-emerald-500/5 group-hover:rotate-12 transition-transform duration-700">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>

            <div v-for="tag in ['CEO outreach', 'AR Demo Ready']" :key="tag" class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex flex-col justify-between">
                <div>
                   <p class="text-sm font-black uppercase tracking-widest text-rose-500">ABM PRIORITY ACTION</p>
                   <h3 class="text-lg font-black mt-2 tracking-tight uppercase">{{ tag }}</h3>
                </div>
                <button class="mt-4 w-full py-3 bg-gray-900 text-white text-xs font-black rounded-2xl shadow-lg shadow-gray-900/10 hover:scale-[1.02] active:scale-95 transition-all uppercase tracking-widest">
                    EXECUTE NOW
                </button>
            </div>
        </div>

        <!-- Target Account List Table -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-md overflow-hidden relative min-h-[500px]">
            <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50 backdrop-blur-xl sticky top-0 z-10">
                <div>
                    <h2 class="text-xl font-black text-gray-900 tracking-tight">ABM Target Command</h2>
                    <p class="text-base font-black text-gray-400 uppercase tracking-widest mt-1">High Intensity outreach tracking</p>
                </div>
                <div class="flex gap-2">
                    <button class="px-5 py-2.5 bg-rose-50 rounded-xl text-xs font-black text-rose-600 border border-rose-100 uppercase tracking-widest hover:animate-pulse">🎯 TARGET 50 NEW</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-white">
                            <th class="px-8 py-5 text-sm font-black text-gray-400 uppercase tracking-widest">Target Account</th>
                            <th class="px-8 py-5 text-sm font-black text-gray-400 uppercase tracking-widest">ABM Score</th>
                            <th class="px-8 py-5 text-sm font-black text-gray-400 uppercase tracking-widest">Engagement</th>
                            <th class="px-8 py-5 text-sm font-black text-gray-400 uppercase tracking-widest">Next Action</th>
                            <th class="px-8 py-5 text-sm font-black text-gray-400 uppercase tracking-widest">Account Value</th>
                            <th class="px-8 py-5 text-sm font-black text-right pr-12 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="acc in target_accounts" :key="acc.company" class="hover:bg-gray-50/80 transition-all group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100 flex items-center justify-center font-black text-indigo-700 shadow-inner group-hover:scale-110 transition-all">
                                        {{ acc.company.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-gray-900">{{ acc.company }}</p>
                                        <p class="text-sm font-black text-gray-400 uppercase leading-none mt-1">SaaS Enterprise</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500" :style="{ width: acc.score + '%' }"></div>
                                    </div>
                                    <span class="text-xs font-black text-gray-900">{{ acc.score }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="px-4 py-1.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all shadow-sm"
                                    :class="acc.status === 'Warm' ? 'bg-orange-50 text-orange-600 border border-orange-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100 animate-pulse'"
                                >
                                    {{ acc.status }}
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-phone-alt text-indigo-400 text-sm"></i>
                                    <p class="text-xs font-bold text-gray-700">{{ acc.next }}</p>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-sm font-black text-gray-900">
                                {{ acc.value }}
                            </td>
                            <td class="px-8 py-6 text-right pr-12">
                                <div class="flex justify-end gap-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all">
                                    <button class="w-9 h-9 flex items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm"><i class="fas fa-phone-alt"></i></button>
                                    <button class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm"><i class="fas fa-comments"></i></button>
                                    <button class="w-9 h-9 flex items-center justify-center rounded-xl bg-violet-50 text-violet-600 hover:bg-violet-600 hover:text-white transition-all shadow-sm"><i class="fas fa-vr-cardboard"></i></button>
                                    <button class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-900 text-white shadow-xl shadow-gray-900/20 hover:-translate-y-1 transition-all"><i class="fas fa-external-link-alt"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Account Journey Map (Embedded Mock) -->
            <div class="p-10 border-t border-gray-50 bg-gray-50/30">
                <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-10 text-center">Active Account Journey: EdTechX</h4>
                <div class="flex justify-center items-center gap-12 relative max-w-4xl mx-auto py-10">
                    <div class="absolute w-[90%] h-0.5 bg-gradient-to-r from-emerald-500 via-indigo-500 to-gray-200"></div>
                    <div v-for="(step, idx) in ['Research', 'Personalized Outreach', 'AR Demo', 'Contract']" :key="step" class="z-10 group cursor-pointer">
                        <div class="flex flex-col items-center gap-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center border-4 border-white shadow-xl transition-all group-hover:scale-125"
                                :class="idx <= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-400' "
                            >
                                <i :class="idx <= 2 ? 'fas fa-check' : 'fas fa-lock'"></i>
                            </div>
                            <span class="text-sm font-black uppercase text-center w-20 leading-none" :class="idx <= 2 ? 'text-gray-900' : 'text-gray-400'">{{ step }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    target_accounts: Array
});
</script>
