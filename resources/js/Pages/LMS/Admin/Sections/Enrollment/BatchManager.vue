<template>
    <div class="space-y-12 animate-fade-in font-sans pb-24">
        <!-- Strategic Header: Enrollment Lifecycle -->
        <div class="bg-gradient-to-br from-indigo-600 to-blue-700 p-12 rounded-[4rem] text-white shadow-2xl relative overflow-hidden group">
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-12">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 rounded-full border border-white/20">
                        <div class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></div>
                        <span class="text-[9px] font-black text-blue-100 uppercase tracking-widest leading-none">Batch Orchestration Layer v4.0</span>
                    </div>
                    <h3 class="text-4xl md:text-5xl font-black tracking-tighter uppercase italic leading-none border-l-4 border-white pl-8">ENROLLMENTS & BATCHES</h3>
                    <p class="text-[10px] font-bold text-white/50 uppercase tracking-[0.3em] pl-8">Global deployment engine for {{ batches.length }} active student cohorts.</p>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-4">
                    <button class="px-8 py-4 bg-white text-indigo-700 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-50 transition-all flex items-center gap-3">
                        <i class="fas fa-plus"></i> Create New Batch
                    </button>
                    <button class="px-8 py-4 bg-white/10 text-white border border-white/20 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-white/20 transition-all flex items-center gap-3">
                        <i class="fas fa-file-csv"></i> Mass Enroll (CSV)
                    </button>
                    <button class="px-8 py-4 bg-white/10 text-white border border-white/20 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-white/20 transition-all flex items-center gap-3">
                        <i class="fas fa-plug"></i> Link ERP Data
                    </button>
                </div>
            </div>
            <div class="absolute -right-20 -top-20 w-[400px] h-[400px] bg-white/5 rounded-full blur-[120px] pointer-events-none"></div>
        </div>

        <!-- Active Batch Registry -->
        <div class="bg-white rounded-[4rem] border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-10 border-b border-gray-50 flex items-center justify-between">
                <h4 class="text-xl font-black text-gray-900 uppercase italic">Operational Batch Registry</h4>
                <div class="flex items-center gap-4">
                    <button class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-emerald-600 hover:text-white transition-all"><i class="fas fa-search text-xs"></i></button>
                    <button class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-emerald-600 hover:text-white transition-all"><i class="fas fa-filter text-xs"></i></button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-8 py-6 text-left text-[9px] font-black text-gray-400 uppercase tracking-widest">Batch Identity</th>
                            <th class="px-8 py-6 text-left text-[9px] font-black text-gray-400 uppercase tracking-widest">Syllabus Track</th>
                            <th class="px-8 py-6 text-left text-[9px] font-black text-gray-400 uppercase tracking-widest">Census / Cohort</th>
                            <th class="px-8 py-6 text-left text-[9px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-6 text-right text-[9px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="batch in batches" :key="batch.id" class="hover:bg-indigo-50/10 transition-all group">
                            <td class="px-8 py-6">
                                <p class="text-[11px] font-black text-gray-900 uppercase italic">{{ batch.name }}</p>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ batch.college }} • Segment Alpha</p>
                            </td>
                            <td class="px-8 py-6 text-[10px] font-black text-indigo-600 uppercase">{{ batch.course }}</td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-black text-gray-900 tabular-nums italic">{{ batch.learners }}</span>
                                    <div class="flex -space-x-3">
                                        <div v-for="i in 3" :key="i" class="w-7 h-7 rounded-full border-2 border-white bg-gray-100 overflow-hidden shadow-sm"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="px-3 py-1 bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg text-[8px] font-black uppercase tracking-widest">Operational</span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-indigo-600 hover:text-white transition-all shadow-sm transform active:scale-95" title="Move Students">
                                         <i class="fas fa-right-left text-[10px]"></i>
                                    </button>
                                    <button class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-indigo-600 hover:text-white transition-all shadow-sm transform active:scale-95" title="Pause Batch Access">
                                         <i class="fas fa-pause text-[10px]"></i>
                                    </button>
                                    <button class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-indigo-600 hover:text-white transition-all shadow-sm transform active:scale-95" title="Mass Email Batch">
                                         <i class="fas fa-envelope-open text-[10px]"></i>
                                    </button>
                                    <button class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-red-500 hover:text-white transition-all shadow-sm transform active:scale-95" title="Revoke Global Access">
                                         <i class="fas fa-lock text-[10px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    batches: {
        type: Array,
        default: () => [
            { id: 1, name: '2026 EV Batch - College A', course: 'EV Fundamentals', college: 'MIT Engineering', learners: 450 },
            { id: 2, name: 'Quantum Q2 Cohort', course: 'Quantum Logic Certificate', college: 'Logic Faculty', learners: 120 },
            { id: 3, name: 'State-wide Digital Literacy', course: 'Internet Protocols', college: 'Regional Hub Alpha', learners: 1800 },
        ]
    }
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }
</style>
