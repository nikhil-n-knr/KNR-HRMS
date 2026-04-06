<template>
  <MainLayout>
    <Head :title="course?.title" />

    <div class="min-h-screen bg-slate-50/50 pb-20">
      <!-- Course Hero -->
      <div class="relative h-[450px] overflow-hidden">
        <!-- Banner Image -->
        <img v-if="course?.banner_image" :src="'/storage/' + course.banner_image" class="w-full h-full object-cover opacity-80" />
        <div v-else class="w-full h-full bg-gradient-to-br from-emerald-600 via-teal-500 to-cyan-500 opacity-90"></div>

        <!-- Hero Content Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-white via-white/40 to-transparent">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col justify-end pb-12">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-10">
              <div class="max-w-3xl">
                <div class="flex items-center gap-3 mb-5">
                  <span class="bg-emerald-600 text-white text-[10px] font-black uppercase tracking-[0.2em] px-3 py-1 rounded-lg shadow-lg shadow-emerald-500/20">{{ course?.level }}</span>
                  <span v-if="course?.category" class="bg-white/70 backdrop-blur-md text-emerald-800 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-lg border border-emerald-100">{{ course.category.name }}</span>
                </div>
                <h1 class="text-4xl lg:text-6xl font-black text-slate-800 mb-6 leading-none tracking-tight uppercase italic">
                  {{ course?.title }}
                </h1>
                <p class="text-slate-500 text-base lg:text-lg mb-8 line-clamp-2 max-w-2xl leading-relaxed font-medium italic">
                  {{ course?.description }}
                </p>
                
                <div class="flex flex-wrap items-center gap-8 text-[10px] text-slate-400 font-black uppercase tracking-widest">
                  <div class="flex items-center gap-2"><ClockIcon class="h-4 w-4 text-emerald-500" /> {{ formatDuration(course?.total_duration_minutes) }}</div>
                  <div class="flex items-center gap-2"><BookOpenIcon class="h-4 w-4 text-emerald-500" /> {{ course?.total_concepts }} topics</div>
                  <div class="flex items-center gap-2"><LanguageIcon class="h-4 w-4 text-emerald-500" /> {{ course?.language === 'en' ? 'English' : course?.language }}</div>
                  <div class="flex items-center gap-2"><UserGroupIcon class="h-4 w-4 text-emerald-500" /> {{ course?.enrolled_count?.toLocaleString() }} learners</div>
                </div>
              </div>

              <div class="shrink-0 mb-2">
                <button v-if="isEnrolled" @click="resumeCourse" 
                  class="bg-slate-800 hover:bg-black text-white text-[11px] font-black uppercase tracking-[0.2em] px-14 py-6 rounded-[32px] shadow-2xl shadow-slate-900/10 transition-all transform hover:scale-105 active:scale-95 flex items-center justify-center gap-3">
                   RESUME {{ progress?.completion_pct > 0 ? 'LEARNING' : 'CURRICULUM' }}
                   <PlayIcon class="h-5 w-5 fill-current" />
                </button>
                <div v-else class="bg-white/70 backdrop-blur-xl px-10 py-8 rounded-[40px] border border-slate-200 shadow-2xl text-center">
                   <p class="text-slate-400 text-[9px] font-black uppercase tracking-widest mb-3">Institutional Access</p>
                   <p class="text-3xl font-black text-slate-800 mb-6 italic">₹ {{ course?.price || 'Free' }}</p>
                   <button class="w-full bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest px-10 py-5 rounded-3xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-500/20 active:scale-95">Enroll into Workspace</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-16">
          <!-- Main Content -->
          <div class="lg:col-span-2 space-y-20">
            
            <!-- What You'll Learn -->
            <section v-if="course?.what_youll_learn" class="bg-white p-10 rounded-[48px] border border-slate-100 shadow-xl shadow-slate-200/20 relative overflow-hidden group">
              <div class="absolute top-0 right-0 p-10 opacity-5 group-hover:opacity-10 transition-opacity"><LightBulbIcon class="h-40 w-40 text-emerald-400" /></div>
              <h3 class="text-xl font-black text-slate-800 uppercase tracking-tighter italic mb-8 flex items-center gap-3">
                <span class="w-8 h-1 bg-emerald-500 inline-block"></span>
                Key Competencies
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-6">
                <div v-for="(item, idx) in formattedWhatYoullLearn" :key="idx" class="flex items-start gap-4">
                  <div class="bg-emerald-50 p-1.5 rounded-xl"><CheckIcon class="h-4 w-4 text-emerald-600 font-black" /></div>
                  <p class="text-sm text-slate-500 font-medium italic leading-relaxed">{{ item }}</p>
                </div>
              </div>
            </section>

            <!-- Curriculum Explorer -->
            <section>
              <div class="flex items-center justify-between mb-10 px-4">
                <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tighter italic">Structural Graph</h3>
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">{{ course?.modules?.length }} Clusters · {{ course?.total_concepts }} Data Points</p>
              </div>

              <div class="space-y-6">
                <div v-for="module in course.modules" :key="module.id" 
                  class="bg-white rounded-[40px] border border-slate-100 overflow-hidden transition-all duration-500 hover:shadow-2xl hover:shadow-emerald-500/5 group">
                  <button @click="toggleModule(module.id)" class="w-full flex items-center justify-between p-8 text-left transition-colors hover:bg-slate-50/50">
                    <div class="flex items-center gap-6">
                      <div class="w-14 h-14 bg-slate-50 rounded-3xl flex items-center justify-center text-slate-400 text-lg font-black group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-inner border border-slate-100">
                        {{ module.sort_order }}
                      </div>
                      <div>
                        <h4 class="text-lg font-black text-slate-800 uppercase tracking-tighter italic leading-tight">{{ module.title }}</h4>
                        <div class="flex items-center gap-5 text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">
                          <span class="flex items-center gap-2"><DocumentIcon class="h-4 w-4 text-emerald-500/50" /> {{ module.chapters?.length }} Chapters</span>
                          <span class="flex items-center gap-2"><ClockIcon class="h-4 w-4 text-emerald-500/50" /> {{ module.estimated_duration_minutes }} mins</span>
                        </div>
                      </div>
                    </div>
                    <div :class="['p-3 rounded-2xl bg-slate-50 text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-all duration-500', expandedModules.includes(module.id) ? 'rotate-180 bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : '']">
                       <ChevronDownIcon class="h-5 w-5" />
                    </div>
                  </button>

                  <div v-show="expandedModules.includes(module.id)" class="px-8 pb-8">
                    <div v-for="chapter in module.chapters" :key="chapter.id" class="mt-6 first:mt-0 pt-6 border-t border-slate-50">
                      <h5 class="text-[9px] font-black text-slate-300 uppercase tracking-[0.3em] mb-4 ml-6">{{ chapter.title }}</h5>
                      <div class="space-y-2">
                        <div v-for="concept in chapter.concepts" :key="concept.id" 
                          class="flex items-center justify-between p-4 ml-6 rounded-2xl hover:bg-slate-50 group/item transition-all">
                          <div class="flex items-center gap-4">
                             <div class="w-2 h-2 rounded-full border-2 border-slate-200 group-hover/item:border-emerald-500 group-hover/item:bg-emerald-500 transition-all"></div>
                             <span class="text-sm font-black text-slate-500 italic uppercase tracking-tight group-hover/item:text-slate-800 group-hover/item:translate-x-1 transition-all">{{ concept.title }}</span>
                          </div>
                          <div class="flex items-center gap-5 opacity-40 group-hover/item:opacity-100 transition-opacity">
                            <component :is="getActivityIcon(concept)" class="h-4 w-4 text-emerald-600" />
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest group-hover/item:text-emerald-600">{{ formatActivityType(concept) }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </section>

          </div>

          <!-- Sidebar: Widgets -->
          <div class="space-y-12">
            <!-- Eligibility Meter -->
            <div v-if="isEnrolled" class="bg-white rounded-[48px] p-10 border border-slate-100 shadow-2xl shadow-slate-200/20 relative overflow-hidden group">
               <div class="absolute -top-12 -right-12 w-40 h-40 bg-emerald-500/5 blur-3xl group-hover:bg-emerald-500/10 transition-all"></div>
               <h4 class="text-slate-800 font-black flex items-center gap-3 mb-10 uppercase tracking-widest text-[11px] italic">
                 <TrophyIcon class="h-6 w-6 text-emerald-500 drop-shadow-[0_0_12px_rgba(16,185,129,0.3)]" />
                 Credential Readiness
               </h4>

               <div v-if="eligibility" class="space-y-10">
                  <div class="relative w-44 h-44 mx-auto">
                    <svg class="w-full h-full transform -rotate-90">
                       <circle cx="88" cy="88" r="78" stroke="currentColor" stroke-width="8" fill="transparent" class="text-slate-50" />
                       <circle cx="88" cy="88" r="78" stroke="currentColor" stroke-width="12" fill="transparent" class="text-emerald-600"
                         :stroke-dasharray="490" :stroke-dashoffset="490 - (490 * eligibility.total_score / 100)"
                         stroke-linecap="round" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                       <span class="text-5xl font-black text-slate-800 italic">{{ eligibility.total_score }}%</span>
                       <span class="text-[10px] font-black text-slate-400 tracking-widest uppercase mt-1">Status Index</span>
                    </div>
                  </div>

                  <div class="space-y-5 py-6">
                    <div v-for="rule in eligibility.rules" :key="rule.label" class="space-y-2">
                      <div class="flex justify-between text-[10px] font-black uppercase tracking-widest">
                        <span class="text-slate-400">{{ rule.label }}</span>
                        <span :class="rule.met ? 'text-emerald-600' : 'text-slate-400'">{{ rule.met ? 'Met' : (rule.percent + '%') }}</span>
                      </div>
                      <div class="w-full bg-slate-50 rounded-full h-2 overflow-hidden border border-slate-100">
                        <div class="h-2 rounded-full transition-all duration-1000" :class="rule.met ? 'bg-emerald-500 shadow-[0_0_12px_rgba(16,185,129,0.4)]' : 'bg-slate-200'" :style="`width: ${rule.percent}%`"></div>
                      </div>
                    </div>
                  </div>

                  <div v-if="eligibility.is_eligible" class="bg-emerald-600 text-white p-6 rounded-3xl text-center shadow-xl shadow-emerald-500/20 animate-bounce-short">
                    <p class="text-[10px] font-black uppercase tracking-widest italic">Credential Eligibility Reached!</p>
                  </div>
               </div>
            </div>

            <!-- Pre-requisites -->
            <div v-if="course?.prerequisites?.length" class="bg-slate-800 text-white p-10 rounded-[48px] shadow-2xl shadow-slate-900/10 relative overflow-hidden">
               <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent"></div>
               <h4 class="font-black mb-8 flex items-center gap-3 uppercase tracking-widest text-[11px] italic text-emerald-400">
                 <ShieldCheckIcon class="h-5 w-5" />
                 System Prerequisites
               </h4>
               <ul class="space-y-5 relative z-10">
                 <li v-for="pre in course.prerequisites" :key="pre" class="flex gap-4">
                   <div class="w-2 h-2 rounded-full bg-emerald-500 mt-2 shrink-0"></div>
                   <span class="text-sm text-slate-300 leading-relaxed font-medium italic italic leading-relaxed">{{ pre.desc || pre }}</span>
                 </li>
               </ul>
            </div>

            <!-- Technical Accreditation -->
            <div class="bg-white border border-slate-100 p-10 rounded-[48px] shadow-xl shadow-slate-200/20 relative overflow-hidden group">
               <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
               <h4 class="text-slate-800 font-black mb-4 uppercase tracking-tighter italic">Accreditation</h4>
               <p class="text-[11px] text-slate-400 font-medium italic leading-relaxed mb-8">This program is strictly aligned with NSDC 4.0 Institutional Standards for data-driven pedagogical excellence.</p>
               <div class="flex items-center gap-6 opacity-40 hover:opacity-100 transition-all">
                  <div class="w-12 h-12 bg-slate-100 rounded-2xl border border-slate-200"></div>
                  <div class="w-24 h-6 bg-slate-100 rounded-xl border border-slate-200"></div>
               </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '../../../Layouts/MainLayout.vue';
import {
  ClockIcon, BookOpenIcon, UserGroupIcon, LanguageIcon,
  PlayIcon, LightBulbIcon, CheckIcon, ChevronDownIcon,
  DocumentIcon, TrophyIcon, ShieldCheckIcon, VideoCameraIcon,
  PencilSquareIcon, DocumentTextIcon, PresentationChartBarIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  course:      Object,
  progress:    Object,
  eligibility: Object,
  isEnrolled:  Boolean
});

const expandedModules = ref([props.course?.modules[0]?.id]);

const toggleModule = (id) => {
  if (expandedModules.value.includes(id)) {
    expandedModules.value = expandedModules.value.filter(mid => mid !== id);
  } else {
    expandedModules.value.push(id);
  }
};

const formattedWhatYoullLearn = computed(() => {
  if (Array.isArray(props.course?.what_youll_learn)) return props.course.what_youll_learn;
  if (!props.course?.what_youll_learn) return [];
  // Split by newline if it's text
  return props.course.what_youll_learn.split('\n').filter(l => l.trim().length > 0);
});

const formatDuration = (mins) => {
  if (!mins) return '0h';
  const h = Math.floor(mins / 60);
  const m = mins % 60;
  return h > 0 ? `${h}h ${m}m` : `${m}m`;
};

const getActivityIcon = (concept) => {
  const type = concept.activities?.[0]?.type;
  return {
    video:      VideoCameraIcon,
    reading:    DocumentTextIcon,
    quiz:       PencilSquareIcon,
    assignment: DocumentIcon,
    live_session: PresentationChartBarIcon,
  }[type] || PlayIcon;
};

const formatActivityType = (concept) => {
  return concept.activities?.[0]?.type?.replace('_', ' ') || 'Topic';
};

const resumeCourse = () => {
  router.visit(`/learn/courses/${props.course.id}/play/0`);
};
</script>

<style scoped>
@keyframes fade-in-down {
  0% { opacity: 0; transform: translateY(-10px); }
  100% { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-down { animation: fade-in-down 0.4s ease-out; }
.animate-bounce-short { animation: bounce 2s infinite ease-in-out; }
@keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
</style>
