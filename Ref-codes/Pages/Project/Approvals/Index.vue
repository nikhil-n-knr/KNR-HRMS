<template>
  <Head title="Pending Approvals | Requirement Gathering" />

  <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-[3rem] border border-slate-100 shadow-xl p-12 text-center animate-in fade-in zoom-in duration-700">
      <div class="w-24 h-24 bg-amber-50 rounded-[2rem] mx-auto flex items-center justify-center mb-8 relative">
        <ShieldCheckIcon class="w-12 h-12 text-amber-600" />
        <div class="absolute -top-2 -right-2 bg-slate-900 p-2 rounded-xl border-4 border-white animate-bounce">
            <LockIcon class="w-4 h-4 text-white" />
        </div>
      </div>

      <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-4">
        Pending Approvals
      </h1>
      
      <div class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 rounded-full border border-amber-100 mb-8">
        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
        <span class="text-[10px] font-black text-amber-700 uppercase tracking-widest">In Requirement Gathering</span>
      </div>

      <p class="text-slate-500 font-medium leading-relaxed mb-10">
        Management approval protocols are currently being codified. The verification matrix for bug resolutions is in strategy formulation.
      </p>

      <div class="space-y-4">
        <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-amber-500 to-orange-500 w-1/2 rounded-full animate-progress"></div>
        </div>
        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Policy Architecture Phase</p>
      </div>

      <button @click="goBack" class="mt-12 flex items-center justify-center gap-2 w-full py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">
        <ArrowLeftIcon class="w-4 h-4" /> Return to Command Center
      </button>
    </div>
  </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ShieldCheckIcon, LockIcon, ArrowLeftIcon } from 'lucide-vue-next';

defineOptions({ layout: MainLayout });

const goBack = () => {
  if (window.history.length > 1) {
    window.history.back();
  } else {
    router.visit(route('dashboard'));
  }
};
</script>

<style scoped>
@keyframes progress {
  0% { transform: translateX(-100%); }
  50% { transform: translateX(100%); }
  100% { transform: translateX(-100%); }
}
.animate-progress {
  animation: progress 3s ease-in-out infinite;
}
</style>
