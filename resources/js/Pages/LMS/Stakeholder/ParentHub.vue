<template>
  <MainLayout>
    <Head title="LMS Parent Hub" />

    <div class="min-h-[calc(100vh-80px)] bg-slate-50 p-8 lg:p-12 font-sans overflow-y-auto">
        <div class="max-w-6xl mx-auto space-y-12">
            <!-- Parent Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Parent Hub</h1>
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-1 italic">Monitoring Ward: {{ ward.name }}</p>
                </div>
                <div class="flex items-center gap-4 bg-white p-4 rounded-3xl border border-slate-200 shadow-sm">
                    <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white text-xl">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest leading-none">Overall Rank</p>
                        <p class="text-lg font-black text-slate-800">{{ ward.grade }}</p>
                    </div>
                </div>
            </div>

            <!-- Progress Snapshot -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="md:col-span-2 bg-white p-8 rounded-[3rem] border border-slate-200 shadow-sm space-y-8">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] mb-4">Course Progress Matrix</h3>
                    <div class="space-y-6">
                        <div v-for="course in courses" :key="course.id" class="space-y-3">
                            <div class="flex justify-between items-end">
                                <p class="text-sm font-black text-slate-800">{{ course.title }}</p>
                                <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">{{ course.status }}</p>
                            </div>
                            <div class="h-3 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 rounded-full transition-all duration-1000" :style="{ width: course.progress + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-indigo-600 to-slate-900 p-8 rounded-[3rem] text-white shadow-xl shadow-indigo-100 flex flex-col justify-between">
                    <div>
                        <i class="fas fa-chart-line text-3xl opacity-20 mb-6"></i>
                        <h3 class="text-xl font-black italic mb-2 tracking-tight">Growth Insight</h3>
                        <p class="text-xs opacity-70 leading-relaxed font-bold">Your ward's engagement is 15% higher than the class average this week. Keep encouraging the self-paced tracks.</p>
                    </div>
                    <button class="w-full py-4 bg-white/10 hover:bg-white/20 border border-white/20 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all">View Full Log</button>
                </div>
            </div>

            <!-- Activity Stream -->
            <div class="space-y-6">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em]">Latest Performance Events</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="i in 4" :key="i" class="bg-white p-6 rounded-3xl border border-slate-100 flex items-center gap-5 hover:border-indigo-200 transition-colors">
                        <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400">
                            <i class="fas fa-calendar-check text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-slate-800 leading-tight">Quiz Completed: Hydrogen Storage Matrix</p>
                            <p class="text-[9px] text-indigo-500 font-bold uppercase mt-1">Score: 94/100 • 2h ago</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '../../../Layouts/MainLayout.vue';

const props = defineProps({
    ward: Object,
    courses: Array
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }
</style>
