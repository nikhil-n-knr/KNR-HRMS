<template>
  <StudentLayout>
    <Head title="Learning Hub" />

    <div class="min-h-screen bg-transparent font-sans text-gray-900 selection:bg-emerald-100">
      <!-- Premium Identity Header -->
      <div class="relative overflow-hidden bg-white/40 backdrop-blur-3xl rounded-[4rem] border border-emerald-100/30 shadow-2xl mb-12 group">
        <div class="absolute inset-0 bg-[radial-gradient(#10b981_1px,transparent_1px)] [background-size:32px_32px] opacity-[0.03]"></div>

        <div class="relative max-w-[1600px] mx-auto px-12 py-16 flex flex-col lg:flex-row items-center justify-between gap-12">
          <div class="space-y-6 flex-1">
             <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 rounded-lg border border-emerald-100">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-[9px] font-black text-emerald-700 uppercase tracking-widest leading-none italic">Journey Active</span>
             </div>
             <h1 class="text-4xl md:text-6xl font-black text-gray-900 tracking-tighter uppercase italic leading-none">
                RESUME MISSION: <span class="text-emerald-600 underline underline-offset-8 decoration-[12px] decoration-emerald-100/50">ASCENT</span>
             </h1>
             <p class="text-lg text-gray-400 font-medium italic max-w-xl">Welcome back, {{ $page.props.auth.user.name.split(' ')[0] }}. You are 3 modules away from your next certification.</p>
             
             <button v-if="enrollments.length > 0" @click="openCourse(enrollments[0])" class="px-10 py-5 bg-gray-900 text-white rounded-[2rem] text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-2xl flex items-center gap-4 transform active:scale-95 group/btn">
                <span>Resume: {{ enrollments[0].course?.title }}</span>
                <i class="fas fa-arrow-right group-hover/btn:translate-x-2 transition-transform"></i>
             </button>
          </div>
          
          <div class="grid grid-cols-3 gap-6 shrink-0 w-full lg:w-auto">
             <div v-for="stat in hubMainStats" :key="stat.label" class="bg-white/80 p-8 rounded-[3.5rem] border border-emerald-50 shadow-sm flex flex-col items-center justify-center min-w-[140px] hover:shadow-2xl transition-all group overflow-hidden relative">
                <span class="text-3xl font-black text-gray-900 tracking-tighter italic group-hover:text-emerald-600 transition-colors relative z-10">{{ stat.value }}</span>
                <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest relative z-10">{{ stat.label }}</span>
                <div class="absolute -right-8 -bottom-8 w-20 h-20 bg-emerald-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity blur-2xl"></div>
             </div>
          </div>
        </div>
      </div>

      <div class="max-w-[1600px] mx-auto px-6 md:px-12 pb-24">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-16">
          
          <!-- Left Wing: Achievements & Milestones -->
          <div class="lg:col-span-1 space-y-12">
             <!-- Achievements Timeline -->
             <div class="bg-white p-10 rounded-[3.5rem] border border-gray-100 shadow-sm space-y-8">
                 <h3 class="text-[10px] font-black text-gray-900 uppercase tracking-widest border-b border-gray-50 pb-6 italic">Achievements Timeline</h3>
                 <div class="space-y-8">
                     <div v-for="i in 3" :key="i" class="flex gap-4 relative group cursor-alias">
                         <div v-if="i<3" class="absolute left-4 top-10 w-px h-10 bg-gray-100"></div>
                         <div class="w-8 h-8 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-xs shadow-sm group-hover:bg-emerald-600 group-hover:text-white transition-all">
                             <i class="fas fa-check-circle"></i>
                         </div>
                         <div>
                             <p class="text-[10px] font-black text-gray-900 uppercase italic leading-none mb-1">Milestone Alpha {{ i }}</p>
                             <p class="text-[8px] font-bold text-gray-400 uppercase tracking-widest italic">Unlocked 2 days ago</p>
                         </div>
                     </div>
                 </div>
                 <button class="w-full py-4 bg-gray-50 text-[9px] font-black text-gray-400 uppercase rounded-2xl hover:bg-emerald-50 hover:text-emerald-600 transition-all">View Career Ledger</button>
             </div>

             <!-- Wallet Glass -->
             <div class="bg-gray-900 p-10 rounded-[4rem] text-white shadow-2xl space-y-8 relative overflow-hidden group">
                 <div class="relative z-10 space-y-6">
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] font-black uppercase tracking-[0.3em] text-emerald-400">Credentials Wallet</span>
                        <i class="fas fa-award text-emerald-500 animate-pulse"></i>
                    </div>
                    <div class="space-y-1">
                        <p class="text-4xl font-black italic tracking-tighter uppercase leading-none">{{ stats.certificates || 0 }}</p>
                        <p class="text-[9px] font-bold text-white/30 uppercase tracking-widest italic leading-none">Verified Industry Assets</p>
                    </div>
                    <Link href="/lms/learn/certificates" class="w-full block text-center py-4 bg-white/5 border border-white/10 text-white rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-white hover:text-gray-900 transition-all backdrop-blur-md">Uplink to Ledger</Link>
                 </div>
                 <div class="absolute -right-16 -top-16 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
             </div>

             <!-- Navigation Quick Actions -->
             <div class="space-y-4">
                 <Link :href="route('lms.store.catalog')" class="w-full flex items-center justify-between p-6 bg-white rounded-3xl border border-gray-100 hover:border-emerald-500 transition-all group">
                     <span class="text-[10px] font-black uppercase italic">Catalog</span>
                     <i class="fas fa-search text-emerald-500 group-hover:translate-x-1 transition-transform"></i>
                 </Link>
                 <button @click="activeTab = 'completed'" class="w-full flex items-center justify-between p-6 bg-white rounded-3xl border border-gray-100 hover:border-emerald-500 transition-all group">
                     <span class="text-[10px] font-black uppercase italic">Completed Tracks</span>
                     <i class="fas fa-archive text-emerald-500 group-hover:translate-x-1 transition-transform"></i>
                 </button>
             </div>
          </div>

          <!-- Central Hub: Enrolled Units -->
          <div class="lg:col-span-3 space-y-12">
            <div class="flex items-end justify-between border-b border-gray-100 pb-10">
                <div class="space-y-4">
                    <h3 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight uppercase italic leading-none">ACTIVE INTELLIGENCE TRACKS</h3>
                    <div class="flex gap-4">
                        <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                            :class="['text-[9px] font-black uppercase tracking-widest transition-all pb-2 border-b-2', 
                            activeTab === tab.id ? 'border-emerald-500 text-gray-900' : 'border-transparent text-gray-400 hover:text-gray-600']">
                            {{ tab.label }} ({{ tab.count }})
                        </button>
                    </div>
                </div>
                <Link :href="route('lms.store.catalog')" class="px-8 py-3 bg-emerald-50 text-emerald-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all border border-emerald-100/50 shadow-sm flex items-center gap-2">
                    <i class="fas fa-search"></i> Advance Repository
                </Link>
            </div>

            <!-- Unit Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div v-for="enrollment in filteredEnrollments" :key="enrollment.id"
                  class="group bg-white rounded-[4rem] border border-gray-100 shadow-sm hover:shadow-2xl hover:border-emerald-200 transition-all duration-700 overflow-hidden cursor-pointer flex flex-col"
                  @click="openCourse(enrollment)">
                  
                  <!-- Card Header -->
                  <div class="h-48 bg-gray-900 relative">
                      <img v-if="enrollment.course?.thumbnail" :src="enrollment.course.thumbnail" class="w-full h-full object-cover opacity-60 group-hover:scale-110 transition-transform duration-1000" />
                      <div v-else class="w-full h-full bg-gradient-to-tr from-gray-900 to-gray-800 opacity-60"></div>
                      
                      <div class="absolute top-8 left-8">
                           <span class="text-[9px] font-black uppercase tracking-widest px-4 py-2 rounded-xl backdrop-blur-xl border border-white/20 text-white bg-black/40">
                            {{ enrollment.status }} TRACK
                          </span>
                      </div>
                      <!-- Linear Progress Line -->
                      <div class="absolute bottom-0 left-0 w-full h-1.5 bg-white/10">
                          <div class="h-full bg-emerald-500 shadow-[0_0_12px_rgba(16,185,129,0.5)] transition-all duration-1000" :style="`width: ${enrollment.course_progress?.completion_pct ?? 0}%` "></div>
                      </div>
                  </div>

                  <div class="p-12 flex-1 flex flex-col space-y-8">
                      <div class="space-y-3">
                        <h3 class="text-2xl font-black text-gray-900 tracking-tighter uppercase italic leading-none group-hover:text-emerald-600 transition-colors">
                            {{ enrollment.course?.title }}
                        </h3>
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest italic line-clamp-1">{{ enrollment.course?.category?.name || 'Scientific' }} Domain • System v4.0</p>
                      </div>

                      <!-- Sub-Stats Grid -->
                      <div class="grid grid-cols-2 gap-6 py-6 border-y border-gray-50">
                          <div class="space-y-1">
                              <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest italic">Mastery Engine</p>
                              <div class="flex items-center gap-2">
                                  <i class="fas fa-brain text-[10px] text-emerald-500"></i>
                                  <span class="text-xs font-black text-gray-800 italic">{{ Math.round(enrollment.course_progress?.completion_pct ?? 0) }}% Complete</span>
                              </div>
                          </div>
                          <div class="space-y-1 text-right">
                              <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest italic">Neural XP Gained</p>
                              <div class="flex items-center gap-2 justify-end">
                                  <span class="text-xs font-black text-gray-800 italic">+{{ (enrollment.course_progress?.concepts_completed ?? 0) * 100 }} XP</span>
                                  <i class="fas fa-bolt text-[10px] text-emerald-500"></i>
                              </div>
                          </div>
                      </div>

                      <div class="flex items-center justify-between pt-2">
                          <div class="flex -space-x-3">
                              <div v-for="i in 3" :key="i" class="w-8 h-8 rounded-full border-4 border-white bg-emerald-50 flex items-center justify-center text-[8px] font-black text-emerald-600 shadow-sm">MB</div>
                          </div>
                          <button class="px-8 py-4 bg-gray-50 text-gray-400 rounded-2xl text-[9px] font-black uppercase tracking-widest group-hover:bg-emerald-600 group-hover:text-white transition-all transform group-hover:scale-105">
                              Jump To Next Node
                          </button>
                      </div>
                  </div>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </StudentLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import StudentLayout from '../../../Layouts/StudentLayout.vue';

const props = defineProps({
  enrollments:       Array,
  certificates:      Array,
  upcoming_sessions: Array,
  stats:             Object,
});

const $page = usePage();
const activeTab = ref('all');

const hubMainStats = computed(() => [
    { label: 'Neural XP', value: (props.stats.points || 0).toLocaleString() },
    { label: 'Active Paths', value: props.enrollments.filter(e => e.status === 'active').length },
    { label: 'Study Hours', value: formatHours(props.stats.total_watch_hrs) },
]);

const tabs = computed(() => [
  { id: 'all',       label: 'Full Identity', count: props.enrollments.length },
  { id: 'active',    label: 'In Progress',  count: props.enrollments.filter(e => e.course_progress?.completion_pct > 0 && !e.course_progress?.is_completed).length },
  { id: 'completed', label: 'Mastered',      count: props.enrollments.filter(e => e.course_progress?.is_completed).length },
  { id: 'new',       label: 'Uninitiated',   count: props.enrollments.filter(e => !e.course_progress || e.course_progress?.completion_pct === 0).length },
]);

const filteredEnrollments = computed(() => {
  if (activeTab.value === 'all') return props.enrollments;
  if (activeTab.value === 'active') return props.enrollments.filter(e => e.course_progress?.completion_pct > 0 && !e.course_progress?.is_completed);
  if (activeTab.value === 'completed') return props.enrollments.filter(e => e.course_progress?.is_completed);
  if (activeTab.value === 'new') return props.enrollments.filter(e => !e.course_progress || e.course_progress?.completion_pct === 0);
  return props.enrollments;
});

const openCourse = (enrollment) => router.visit(route('lms.learn.course.detail', { course: enrollment.course_id }));

const formatHours = (hrs) => {
  if (!hrs) return '0h';
  return `${Math.round(hrs)}h`;
};

const formatWatchTime = (seconds) => {
  if (!seconds) return '0m';
  const hrs = Math.floor(seconds / 3600);
  const mins = Math.floor((seconds % 3600) / 60);
  if (hrs > 0) return `${hrs}h ${mins}m`;
  return `${mins}m`;
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }
</style>
