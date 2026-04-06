<template>
  <MainLayout>
    <Head title="Certificates Wallet" />

    <div class="min-h-screen bg-slate-50/50 p-4 sm:p-8">
      <!-- Header -->
      <div class="max-w-7xl mx-auto mb-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <div class="flex items-center gap-3 mb-2">
              <span class="bg-emerald-600/10 text-emerald-600 text-[10px] font-black uppercase tracking-[0.2em] px-2 py-0.5 rounded border border-emerald-500/30">Verified Credentials</span>
              <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">Secure Wallet</span>
            </div>
            <h1 class="text-4xl font-black text-slate-800 tracking-tight uppercase italic">Certificates <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-500 whitespace-nowrap">Wallet</span></h1>
            <p class="text-slate-500 text-sm mt-1 font-medium italic">Your collection of earned credentials and academic achievements.</p>
          </div>

          <div class="flex items-center gap-4">
             <div class="bg-white border border-slate-200 rounded-3xl px-8 py-4 shadow-sm relative overflow-hidden group">
               <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest mb-1 text-right">Total Assets</p>
               <div class="flex items-center gap-2 justify-end relative z-10">
                 <TrophyIcon class="h-5 w-5 text-emerald-500" />
                 <span class="text-2xl font-black text-slate-800 tracking-tighter">{{ certificates.total || 0 }}</span>
               </div>
             </div>
          </div>
        </div>
      </div>

      <!-- Grid -->
      <div class="max-w-7xl mx-auto">
        <div v-if="certificates.data.length === 0" class="text-center py-20 bg-white rounded-[40px] border border-slate-200 shadow-sm">
           <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
              <TrophyIcon class="h-10 w-10 text-slate-300" />
           </div>
           <h3 class="text-lg font-black text-slate-800 uppercase tracking-tighter">Vault is Empty</h3>
           <p class="text-slate-500 text-sm mt-1 font-medium italic">Earn your first certificate by completing a course or program.</p>
           <Link href="/lms/learn/hub" class="mt-8 inline-flex items-center gap-2 bg-slate-800 text-white text-[10px] font-black uppercase tracking-widest px-8 py-4 rounded-2xl hover:bg-black transition-all active:scale-95">
             Explore Curriculum
           </Link>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
           <div v-for="cert in certificates.data" :key="cert.id" 
             class="bg-white rounded-[40px] border border-slate-200 overflow-hidden group hover:border-emerald-500/30 hover:shadow-2xl hover:shadow-emerald-500/5 transition-all duration-500 flex flex-col">
             
             <!-- Certificate Preview Mock -->
             <div class="relative aspect-[1.4/1] bg-slate-100 flex items-center justify-center p-8 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-teal-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                <!-- Mock Certificate Card -->
                <div class="w-full h-full bg-white border border-emerald-100/50 shadow-2xl rounded-lg p-6 flex flex-col items-center justify-center text-center relative overflow-hidden">
                   <div class="absolute top-0 right-0 w-16 h-16 bg-emerald-500/5 -rotate-45 translate-x-8 -translate-y-8"></div>
                   <TrophyIcon class="h-8 w-8 text-emerald-500 mb-4" />
                   <p class="text-[8px] font-black uppercase text-slate-400 tracking-widest mb-1">Certificate of Achievement</p>
                   <p class="text-xs font-black text-slate-800 leading-tight uppercase tracking-tighter">{{ cert.course?.title || cert.program?.name }}</p>
                   <div class="mt-4 pt-4 border-t border-slate-100 w-full flex justify-between items-center px-4">
                      <div class="text-[6px] text-slate-400 uppercase font-bold text-left">Ref: {{ cert.unique_code }}</div>
                      <div class="text-[6px] text-slate-400 uppercase font-bold text-right">{{ formatDate(cert.issued_at) }}</div>
                   </div>
                </div>

                <!-- Hover Overlay -->
                <div class="absolute inset-0 flex items-center justify-center gap-3 opacity-0 group-hover:opacity-100 transition-all translate-y-4 group-hover:translate-y-0">
                   <a :href="`/storage/${cert.pdf_path}`" target="_blank" class="bg-slate-800 text-white p-3 rounded-xl hover:bg-black transition-all shadow-xl">
                      <ArrowDownTrayIcon class="h-5 w-5" />
                   </a>
                   <button class="bg-white text-slate-800 p-3 rounded-xl hover:bg-emerald-50 border border-slate-200 transition-all shadow-xl">
                      <ShareIcon class="h-5 w-5" />
                   </button>
                </div>
             </div>

             <div class="p-8 flex-1 flex flex-col">
                <div class="flex items-center gap-2 mb-4">
                   <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 bg-emerald-50 text-emerald-600 rounded-lg border border-emerald-100">{{ cert.type }}</span>
                   <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 bg-slate-50 text-slate-400 rounded-lg border border-slate-100 underline decoration-emerald-500/50">Verified</span>
                </div>
                <h3 class="text-lg font-black text-slate-800 tracking-tighter uppercase italic leading-tight mb-2 group-hover:text-emerald-700 transition-colors">
                  {{ cert.course?.title || cert.program?.name }}
                </h3>
                <p class="text-xs text-slate-400 font-medium italic mb-6">Issued on {{ formatDate(cert.issued_at) }} by Matrix LMS Governance.</p>

                <div class="mt-auto flex items-center justify-between">
                   <a :href="`/certificates/verify/${cert.unique_code}`" target="_blank" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-emerald-600 transition-all flex items-center gap-1.5">
                      <ShieldCheckIcon class="h-4 w-4" />
                      Public Record
                   </a>
                   <div class="h-8 w-px bg-slate-100 mx-4"></div>
                   <button class="text-[10px] font-black uppercase tracking-widest text-emerald-600 hover:text-emerald-700">Digital Asset</button>
                </div>
             </div>
           </div>
        </div>

        <!-- Pagination -->
        <div v-if="certificates.links && certificates.links.length > 3" class="mt-12 flex justify-center gap-2">
           <Link v-for="link in certificates.links" :key="link.label"
             :href="link.url"
             v-html="link.label"
             :class="['px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all', 
                     link.active ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-white text-slate-500 border border-slate-200 hover:bg-emerald-50']"
           />
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '../../../Layouts/MainLayout.vue';
import { 
  TrophyIcon, ArrowDownTrayIcon, ShareIcon, 
  ShieldCheckIcon, AcademicCapIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  certificates: Object
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-IN', {
    day: 'numeric', month: 'short', year: 'numeric'
  });
};
</script>
