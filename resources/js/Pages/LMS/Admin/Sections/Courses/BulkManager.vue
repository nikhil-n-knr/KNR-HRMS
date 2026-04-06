<template>
    <div class="space-y-12 animate-fade-in font-sans">
        <!-- Strategic Header: Bulk Command Hub -->
        <div class="flex items-end justify-between border-b border-emerald-100 pb-8">
            <div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight uppercase italic mb-1">BULK UPDATE MATRIX</h2>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">High-velocity operational control over global syllabus structures</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="px-6 py-3 bg-gray-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-gray-200 hover:bg-black transition-all flex items-center gap-3">
                    <i class="fas fa-check-double"></i> Commit Batch Change
                </button>
            </div>
        </div>

        <!-- Filter & Search Strategy -->
        <div class="flex flex-wrap items-center gap-6 bg-white p-8 rounded-[3rem] border border-gray-100 shadow-sm">
            <div class="flex-1 relative group">
                <input type="text" placeholder="Filter Entity Cluster..." class="w-full pl-12 pr-6 py-4 bg-gray-50 border-none rounded-2xl text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-emerald-100 transition-all font-sans">
                <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 text-xs"></i>
            </div>
            <div class="flex items-center gap-4">
                <div v-for="f in ['Category', 'Author', 'Status', 'Sync Parity']" :key="f" class="px-6 py-4 bg-gray-50 rounded-2xl text-[9px] font-black uppercase tracking-widest text-gray-400 cursor-pointer hover:bg-emerald-50 hover:text-emerald-600 transition-all border border-transparent hover:border-emerald-100">
                    {{ f }}
                </div>
            </div>
        </div>

        <!-- Mass Update Grid -->
        <div class="bg-white rounded-[4rem] border border-gray-100 shadow-sm overflow-hidden overflow-x-auto custom-scrollbar pb-12">
            <table class="w-full text-left">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-10 py-8"><div class="w-5 h-5 border-2 border-emerald-500 rounded-md"></div></th>
                        <th v-for="h in ['Entity (Syllabus)', 'Global Price Matrix', 'Live Status', 'Visibility Logic', 'Revenue Delta']" :key="h" class="px-10 py-8 text-[9px] font-black text-gray-400 uppercase tracking-widest">{{ h }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="i in 8" :key="i" class="group hover:bg-emerald-50/20 transition-all">
                        <td class="px-10 py-8"><div class="w-5 h-5 border-2 border-gray-200 rounded-md hover:border-emerald-500 cursor-pointer transition-colors"></div></td>
                        <td class="px-10 py-8">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-gray-900 flex items-center justify-center text-white text-[10px] font-black italic">B{{i}}</div>
                                <div class="min-w-0">
                                    <p class="text-xs font-black text-gray-900 uppercase italic truncate leading-none mb-1.5 group-hover:text-emerald-600 transition-colors">Cognitive Module 0{{i}}</p>
                                    <p class="text-[8px] font-bold text-gray-300 uppercase tracking-widest">Master Syllabus Registry #8022{{i}}X</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-10 py-8">
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] font-black text-gray-800 tabular-nums">₹{{ 499 + (i * 100) }}</span>
                                <input type="checkbox" checked class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 border-gray-100 rounded">
                            </div>
                        </td>
                        <td class="px-10 py-8">
                             <span :class="['px-3 py-1 rounded-lg text-[8px] font-black uppercase italic tracking-widest', i < 6 ? 'bg-emerald-100 text-emerald-600 border border-emerald-200' : 'bg-orange-100 text-orange-600 border border-orange-200']">
                                {{ i < 6 ? 'Operational' : 'Drafting Phase' }}
                             </span>
                        </td>
                        <td class="px-10 py-8">
                            <div class="flex items-center gap-6">
                                <span class="text-[9px] font-black text-gray-400">Public Hub</span>
                                <div class="w-10 h-5 bg-emerald-100 rounded-full relative p-1 cursor-pointer">
                                    <div class="w-3 h-3 bg-emerald-600 rounded-full shadow-lg shadow-emerald-500 translate-x-5"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-10 py-8">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-black text-emerald-600 tabular-nums">+{{ (i * 2.4).toFixed(1) }}%</span>
                                <i class="fas fa-caret-up text-emerald-400"></i>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }

.custom-scrollbar::-webkit-scrollbar {
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(16, 185, 129, 0.1);
    border-radius: 20px;
}
</style>
