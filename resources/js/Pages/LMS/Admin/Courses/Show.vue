<template>
  <MainLayout>
    <Head :title="course.title + ' | Performance Intelligence'" />
    
    <div class="min-h-screen bg-slate-50/50 pb-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12">
        <!-- Header -->
        <div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-10">
          <div class="flex-1">
             <div class="flex items-center gap-3 mb-4">
                <Link :href="route('lms.admin.builder.index')" class="p-2.5 bg-white rounded-xl border border-slate-100 hover:text-emerald-500 hover:shadow-lg transition-all"><ArrowLeftIcon class="h-5 w-5" /></Link>
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ course.category?.name }}</span>
             </div>
             <h1 class="text-4xl lg:text-5xl font-black text-slate-800 uppercase tracking-tighter italic leading-none mb-4 tracking-tighter">{{ course.title }}</h1>
             <p class="text-slate-500 text-sm max-w-3xl font-medium italic leading-relaxed">{{ course.description }}</p>
          </div>
          <div class="flex items-center gap-4">
             <Link :href="route('lms.admin.courses.builder', course.id)" 
               class="bg-white hover:bg-slate-50 text-slate-800 text-[10px] font-black uppercase tracking-[0.2em] px-10 py-5 rounded-[24px] border border-slate-100 shadow-xl shadow-slate-200/20 transition-all flex items-center gap-3 active:scale-95 group">
               <BeakerIcon class="h-5 w-5 transition-transform group-hover:scale-110" />
               Modify Vector
             </Link>
             <button class="bg-slate-800 hover:bg-black text-white text-[10px] font-black uppercase tracking-[0.2em] px-10 py-5 rounded-[24px] shadow-2xl shadow-slate-900/10 transition-all flex items-center gap-3 active:scale-95 group">
                <ShareIcon class="h-5 w-5 transition-transform group-hover:translate-x-1" />
                Registry Export
             </button>
          </div>
        </div>

        <!-- Intelligence Hub (Stats Grid) -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-16">
           <div v-for="(val, label) in stats" :key="label" class="bg-white p-8 rounded-[40px] border border-slate-100 shadow-xl shadow-slate-200/20 group hover:translate-y-[-4px] transition-all">
              <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-2">{{ label.replace('_', ' ') }}</p>
              <p :class="['text-2xl font-black italic', label.includes('avg') ? 'text-emerald-600' : 'text-slate-800']">
                 {{ typeof val === 'number' && val < 1 ? Math.round(val * 100) : Math.round(val) }}{{ label.includes('pct') || label.includes('score') ? '%' : '' }}
              </p>
              <div :class="['mt-3 h-0.5 w-8 rounded-full bg-slate-100', label.includes('avg') ? 'bg-emerald-500' : 'bg-slate-800']"></div>
           </div>
        </div>

        <!-- Detailed View: Structure vs Metrics -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
           <!-- Curriculum Graph Visualization -->
           <div class="lg:col-span-2 space-y-10">
              <div class="bg-white p-10 rounded-[60px] border border-slate-100 shadow-2xl shadow-slate-200/20">
                 <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tighter italic mb-10 flex items-center gap-4">
                    <QueueListIcon class="h-8 w-8 text-emerald-500" />
                    Logical Payload Architecture
                 </h3>
                 
                 <div class="space-y-6">
                    <div v-for="module in course.modules" :key="module.id" class="p-8 rounded-[40px] bg-slate-50 border border-slate-100 group hover:shadow-xl transition-all">
                       <div class="flex items-center justify-between gap-6 mb-6">
                          <div class="flex items-center gap-5">
                             <div class="w-12 h-12 rounded-2xl bg-white border border-slate-100 flex items-center justify-center text-sm font-black text-slate-300 italic group-hover:text-emerald-600 group-hover:border-emerald-500 transition-all shadow-inner">{{ module.sort_order }}</div>
                             <p class="text-lg font-black text-slate-700 uppercase tracking-tight italic">{{ module.title }}</p>
                          </div>
                          <button class="p-3 bg-white text-slate-300 hover:text-emerald-500 rounded-xl border border-slate-100 transition-all"><ChevronDownIcon class="h-5 w-5" /></button>
                       </div>
                       
                       <div class="pl-16 space-y-4">
                          <div v-for="chapter in module.chapters" :key="chapter.id" class="flex items-center justify-between p-4 bg-white/50 rounded-2xl border border-transparent hover:border-emerald-100 hover:bg-white transition-all">
                             <span class="text-xs font-black text-slate-500 uppercase tracking-tighter italic">{{ chapter.title }}</span>
                             <div class="flex items-center gap-4">
                               <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">{{ chapter.concepts?.length }} Concepts Vectorized</span>
                               <CheckBadgeIcon class="h-4 w-4 text-emerald-500" />
                             </div>
                          </div>
                       </div>
                    </div>
                 </div>
              </div>
           </div>

           <!-- Sidebar Meta Protocols -->
           <div class="space-y-12">
              <div class="bg-slate-800 p-10 rounded-[60px] shadow-2xl relative overflow-hidden group">
                 <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent"></div>
                 <h4 class="text-[11px] font-black text-emerald-400 uppercase tracking-widest mb-8 flex items-center gap-3 italic">
                    <BriefcaseIcon class="h-6 w-6" />
                    Registry Protocols
                 </h4>
                 
                 <div class="space-y-8 relative z-10">
                    <div>
                       <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Institutional Handshake</p>
                       <p class="text-sm font-black text-white italic">{{ course.institution?.name || 'Global Unconstrained' }}</p>
                    </div>
                    <div>
                       <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Program Vector</p>
                       <p class="text-sm font-black text-white italic">{{ course.program?.name || 'Standalone Delta' }}</p>
                    </div>
                    <div>
                       <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Sync Horizon</p>
                       <p class="text-xs font-black text-emerald-400 uppercase tracking-widest">{{ course.is_published ? 'Synchronized and Live' : 'Delta Archive Mode' }}</p>
                    </div>
                 </div>
                 
                 <div class="mt-12 pt-10 border-t border-white/5">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Archive Creator</p>
                    <div class="flex items-center gap-4">
                       <div class="w-10 h-10 rounded-full bg-emerald-500/20 border border-emerald-500/20 flex items-center justify-center text-emerald-400 font-black italic">{{ course.creator?.name?.[0] }}</div>
                       <div>
                          <p class="text-sm font-black text-white italic">{{ course.creator?.name }}</p>
                          <p class="text-[9px] text-slate-400 uppercase font-black tracking-widest">Protocol Lead</p>
                       </div>
                    </div>
                 </div>
              </div>

              <div class="bg-emerald-50 p-10 rounded-[60px] border border-emerald-100 shadow-xl shadow-emerald-500/5 group hover:border-emerald-300 transition-all">
                 <h4 class="text-[10px] font-black text-emerald-700 uppercase tracking-widest mb-6 flex items-center gap-3 italic">
                    <Square3Stack3DIcon class="h-6 w-6" />
                    Cognitive Assets
                 </h4>
                 <div class="space-y-4">
                    <div class="bg-white p-5 rounded-3xl border border-emerald-100 flex items-center justify-between group-hover:shadow-lg transition-all">
                       <div class="flex items-center gap-4">
                          <PhotoIcon class="h-5 w-5 text-emerald-600" />
                          <span class="text-[10px] font-black text-slate-700 uppercase tracking-widest italic">Asset_Thumbnail.jpg</span>
                       </div>
                       <DownloadIcon class="h-4 w-4 text-emerald-300" />
                    </div>
                    <!-- Mock assets -->
                    <div class="bg-white/50 p-5 rounded-3xl border border-emerald-50 flex items-center justify-between">
                       <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Course_Manifest.json</span>
                    </div>
                 </div>
              </div>
           </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '../../../../Layouts/MainLayout.vue';
import { 
  ArrowLeftIcon, BeakerIcon, ShareIcon, ArrowDownTrayIcon as DownloadIcon,
  QueueListIcon, CheckBadgeIcon, BriefcaseIcon, Square3Stack3DIcon, PhotoIcon,
  ChevronDownIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  course: Object,
  stats: Object,
  categories: Array
});
</script>
