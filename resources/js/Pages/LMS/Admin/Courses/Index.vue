<template>
  <MainLayout>
    <Head title="Course Architect | Advanced LMS" />
    
    <div class="min-h-screen bg-slate-50/50 pb-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12">
        <!-- Header -->
        <div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-8">
          <div>
            <h1 class="text-4xl lg:text-5xl font-black text-slate-800 uppercase tracking-tighter italic leading-none mb-4 tracking-tighter">Course Registry</h1>
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] italic">Manage your institutional curriculum and intellectual property</p>
          </div>
          <Link :href="route('lms.admin.courses.create')" 
            class="bg-slate-800 hover:bg-black text-white text-[11px] font-black uppercase tracking-[0.2em] px-10 py-5 rounded-[24px] shadow-2xl shadow-slate-900/10 transition-all flex items-center gap-3 active:scale-95 group">
            <PlusIcon class="h-5 w-5 fill-emerald-500 transition-transform group-hover:rotate-90" />
            Initialize New Vector
          </Link>
        </div>

        <!-- Stats Matrix -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-16">
          <div v-for="(val, key) in stats" :key="key" class="bg-white p-8 rounded-[40px] border border-slate-100 shadow-xl shadow-slate-200/20 group hover:scale-[1.02] transition-all">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">{{ key.replace('_', ' ') }} Index</p>
            <p class="text-3xl font-black text-slate-800 italic">{{ val }}</p>
            <div class="mt-4 h-1 w-12 bg-emerald-500/20 rounded-full overflow-hidden">
               <div class="h-full bg-emerald-500 w-1/2"></div>
            </div>
          </div>
        </div>

        <!-- Filters Hub -->
        <div class="bg-white/80 backdrop-blur-xl p-6 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/10 mb-10 flex flex-wrap items-center gap-6">
           <div class="relative flex-1 min-w-[300px]">
             <input type="text" v-model="filters.search" placeholder="Search course archives..." 
               class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-12 py-4 text-xs font-black text-slate-700 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all placeholder:text-slate-300 italic" />
             <MagnifyingGlassIcon class="absolute left-4 top-4 h-5 w-5 text-slate-300" />
           </div>
           
           <div class="flex items-center gap-3">
             <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Global Taxonomy:</span>
             <select v-model="filters.category_id" class="bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-[10px] font-black uppercase tracking-widest outline-none focus:border-emerald-500 transition-all">
                <option :value="null">All Clusters</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
             </select>
           </div>
        </div>

        <!-- Courses Table -->
        <div class="bg-white rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/20 overflow-hidden">
           <table class="w-full border-collapse">
             <thead>
               <tr class="bg-slate-50/50 border-b border-slate-100">
                 <th class="px-8 py-6 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Identification Vector</th>
                 <th class="px-8 py-6 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Taxonomy</th>
                 <th class="px-8 py-6 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Metrics</th>
                 <th class="px-8 py-6 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Registry Status</th>
                 <th class="px-8 py-6 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Operations</th>
               </tr>
             </thead>
             <tbody class="divide-y divide-slate-50">
               <tr v-for="course in courses.data" :key="course.id" class="hover:bg-slate-50/30 transition-colors group">
                 <td class="px-8 py-6">
                   <div class="flex items-center gap-5">
                     <div class="w-16 h-10 rounded-xl overflow-hidden bg-slate-100 shrink-0 relative shadow-inner border border-slate-100">
                        <img v-if="course.thumbnail_path" :src="'/storage/' + course.thumbnail_path" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-slate-300"><PhotoIcon class="h-6 w-6" /></div>
                     </div>
                     <div>
                       <p class="text-sm font-black text-slate-800 uppercase tracking-tighter italic">{{ course.title }}</p>
                       <p class="text-[9px] text-slate-400 uppercase font-black tracking-widest mt-1">UUID: {{ course.slug }}</p>
                     </div>
                   </div>
                 </td>
                 <td class="px-8 py-6">
                    <span class="text-[9px] font-black text-emerald-600 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-100 uppercase tracking-widest">{{ course.category?.name || 'Uncategorized' }}</span>
                 </td>
                 <td class="px-8 py-6">
                   <div class="flex items-center gap-6">
                     <div class="text-center">
                       <p class="text-[8px] font-black text-slate-400 mb-0.5">NODES</p>
                       <p class="text-xs font-black text-slate-700 italic">{{ course.modules_count }}</p>
                     </div>
                     <div class="text-center">
                       <p class="text-[8px] font-black text-slate-400 mb-0.5">LEARNERS</p>
                       <p class="text-xs font-black text-slate-700 italic">{{ course.enrollments_count }}</p>
                     </div>
                   </div>
                 </td>
                 <td class="px-8 py-6">
                    <div :class="['w-3 h-3 rounded-full mb-1', course.is_published ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]' : 'bg-slate-300 animate-pulse']"></div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 italic">{{ course.is_published ? 'Synchronized' : 'Draft Protocol' }}</span>
                 </td>
                 <td class="px-8 py-6 text-right">
                    <div class="flex items-center justify-end gap-3 translate-x-4 opacity-0 group-hover:translate-x-0 group-hover:opacity-100 transition-all">
                      <Link :href="route('lms.admin.courses.builder', course.id)" class="p-3 bg-white text-slate-400 hover:text-emerald-500 hover:shadow-lg rounded-xl border border-slate-100 transition-all"><PencilSquareIcon class="h-5 w-5" /></Link>
                      <Link :href="route('lms.admin.courses.show', course.id)" class="p-3 bg-white text-slate-400 hover:text-blue-500 hover:shadow-lg rounded-xl border border-slate-100 transition-all"><ChartBarSquareIcon class="h-5 w-5" /></Link>
                      <button class="p-3 bg-white text-slate-400 hover:text-rose-500 hover:shadow-lg rounded-xl border border-slate-100 transition-all"><TrashIcon class="h-5 w-5" /></button>
                    </div>
                 </td>
               </tr>
             </tbody>
           </table>

           <div v-if="!courses.data.length" class="text-center py-32">
             <AcademicCapIcon class="mx-auto h-20 w-20 text-slate-50 mb-6" />
             <h3 class="text-xl font-black text-slate-800 uppercase tracking-tighter italic">Registry Null</h3>
             <p class="text-slate-400 text-sm mt-3 font-medium italic">Your curriculum archive is currently zero-indexed.</p>
           </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '../../../../Layouts/MainLayout.vue';
import { 
  PlusIcon, MagnifyingGlassIcon, PhotoIcon, 
  PencilSquareIcon, ChartBarSquareIcon, TrashIcon,
  AcademicCapIcon
} from '@heroicons/vue/24/outline';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
  courses: Object,
  stats: Object,
  categories: Array,
  institutions: Array,
  filters: Object
});

const filters = ref({ ...props.filters });

watch(filters, debounce(() => {
  router.get(route('lms.admin.builder.index'), filters.value, { preserveState: true, replace: true });
}, 500), { deep: true });
</script>
