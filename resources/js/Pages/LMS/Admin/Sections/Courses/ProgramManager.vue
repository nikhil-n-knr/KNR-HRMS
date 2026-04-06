<template>
    <div class="space-y-12 animate-fade-in font-sans pb-24">
        <!-- Strategic Header: Program Orchestration -->
        <div class="bg-white p-12 rounded-[4rem] border border-gray-100 shadow-sm flex flex-col md:flex-row items-center justify-between group overflow-hidden relative">
            <div class="relative z-10 space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-teal-50 rounded-full border border-teal-100">
                    <div class="w-1.5 h-1.5 rounded-full bg-teal-500 animate-pulse"></div>
                    <span class="text-[9px] font-black text-teal-600 uppercase tracking-widest leading-none">Master Track Logic Active</span>
                </div>
                <h3 class="text-4xl font-black text-gray-900 tracking-tighter uppercase italic leading-none">PROGRAMS & TRACKS</h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.3em]">Orchestrating multi-syllabus credentials for state-wide mastery.</p>
            </div>
            <button class="px-10 py-5 bg-gray-900 text-white rounded-3xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all shadow-2xl relative z-10 transform active:scale-95 flex items-center gap-3">
                <i class="fas fa-layer-group"></i> Create Master Track
            </button>
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-teal-50 rounded-full opacity-50 group-hover:scale-125 transition-transform duration-1000"></div>
        </div>

        <!-- Program Matrix -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <div v-for="program in programs" :key="program.id" class="group bg-white rounded-[3.5rem] border border-gray-100 shadow-sm hover:shadow-2xl hover:border-teal-200 transition-all duration-700 p-10 space-y-8 relative overflow-hidden">
                <div class="flex items-start justify-between relative z-10">
                    <div class="w-16 h-16 rounded-[2rem] bg-gray-50 border border-gray-100 flex items-center justify-center text-teal-600 shadow-sm group-hover:bg-teal-600 group-hover:text-white transition-all duration-500">
                         <i class="fas fa-microchip text-2xl"></i>
                    </div>
                    <span class="text-[9px] font-black text-teal-500 uppercase tracking-widest bg-teal-50 px-3 py-1 rounded-xl border border-teal-100">Master Level</span>
                </div>

                <div class="relative z-10 space-y-1">
                    <h4 class="text-xl font-black text-gray-900 tracking-tight uppercase italic group-hover:text-teal-600 transition-colors">{{ program.title }}</h4>
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest italic">Includes: {{ program.course_count }} Syllabus Units</p>
                </div>

                <!-- Master Rules Summary -->
                <div class="bg-gray-50/50 p-6 rounded-3xl space-y-4 border border-transparent group-hover:border-teal-100 transition-all">
                     <div class="flex items-center justify-between">
                         <span class="text-[9px] font-black text-gray-400 uppercase italic">Completion Rule</span>
                         <span class="text-[10px] font-black text-teal-600 uppercase">{{ program.completion_rule }}%</span>
                     </div>
                     <div class="h-1 bg-gray-100 rounded-full overflow-hidden">
                         <div class="h-full bg-teal-500" :style="{ width: program.completion_rule + '%' }"></div>
                     </div>
                </div>

                <!-- Actions -->
                <div class="grid grid-cols-2 gap-4 relative z-10 pt-4">
                    <button class="py-4 bg-gray-50 text-gray-400 rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-teal-600 hover:text-white transition-all shadow-sm transform active:scale-95" title="Add/Remove Courses">
                         <i class="fas fa-plus-circle mr-2"></i> Edit Units
                    </button>
                    <button class="py-4 bg-gray-50 text-gray-400 rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-teal-600 hover:text-white transition-all shadow-sm transform active:scale-95" title="Set Master Rules">
                         <i class="fas fa-gavel mr-2"></i> Logic
                    </button>
                    <button class="py-4 bg-gray-50 text-gray-400 rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-teal-600 hover:text-white transition-all shadow-sm transform active:scale-95" title="Assign Institutions">
                         <i class="fas fa-university mr-2"></i> Colleges
                    </button>
                    <button class="py-4 bg-gray-900 text-white rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-black transition-all shadow-sm transform active:scale-95" title="View Progress">
                         <i class="fas fa-chart-pie mr-2"></i> Analytics
                    </button>
                </div>
                <!-- Decoration -->
                <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-teal-50 rounded-full opacity-0 group-hover:opacity-40 transition-opacity blur-3xl -z-1"></div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    programs: {
        type: Array,
        default: () => [
            { id: 1, title: 'EV Engineering Master Track', course_count: 8, completion_rule: 90 },
            { id: 2, title: 'Quantum Logic Certificate', course_count: 5, completion_rule: 85 },
            { id: 3, title: 'Strategic HR Protocol', course_count: 12, completion_rule: 95 },
            { id: 4, title: 'Distributed Systems Mastery', course_count: 6, completion_rule: 80 },
        ]
    }
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }
</style>
