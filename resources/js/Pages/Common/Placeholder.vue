<template>
  <Head :title="`${title} | In Progress`" />

  <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-[3rem] border border-slate-100 shadow-xl p-12 text-center animate-in fade-in zoom-in duration-700">
      <div class="w-24 h-24 bg-indigo-50 rounded-[2rem] mx-auto flex items-center justify-center mb-8 relative">
        <LayoutIcon class="w-12 h-12 text-indigo-600" />
        <div class="absolute -top-2 -right-2 bg-amber-400 p-2 rounded-xl border-4 border-white animate-bounce">
            <ConstructionIcon class="w-4 h-4 text-white" />
        </div>
      </div>

      <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-4">
        {{ title }}
      </h1>
      
      <div class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 rounded-full border border-amber-100 mb-8">
        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
        <span class="text-[10px] font-black text-amber-700 uppercase tracking-widest">In Requirement Gathering</span>
      </div>

      <p class="text-slate-500 font-medium leading-relaxed mb-10">
        Our architecture team is currently synthesizing the functional protocols for this module. Standby for deployment updates.
      </p>

      <div class="space-y-4">
        <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 w-1/3 rounded-full animate-progress"></div>
        </div>
        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Strategy Formulation Phase</p>
      </div>

      <button @click="goBack" class="mt-12 flex items-center justify-center gap-2 w-full py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">
        <ArrowLeftIcon class="w-4 h-4" /> Return to Command Center
      </button>
    </div>
    
    <!-- Decorative background elements -->
    <div class="fixed top-0 left-0 w-96 h-96 bg-indigo-100 rounded-full blur-3xl opacity-20 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="fixed bottom-0 right-0 w-96 h-96 bg-purple-100 rounded-full blur-3xl opacity-20 translate-x-1/2 translate-y-1/2"></div>
  </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { LayoutIcon, ConstructionIcon, ArrowLeftIcon } from 'lucide-vue-next';

defineOptions({ layout: MainLayout });

defineProps({
  title: {
    type: String,
    default: 'Module'
  }
});

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
