<template>
  <transition name="fade">
    <div v-if="uiStore.isLoading" class="fixed inset-0 z-50 flex items-center justify-center bg-white/50 backdrop-blur-sm">
      <div class="flex flex-col items-center">
        <!-- Modern Spinner -->
        <div class="relative w-16 h-16">
          <div class="absolute inset-0 rounded-full border-4 border-emerald-100"></div>
          <div class="absolute inset-0 rounded-full border-4 border-emerald-500 border-t-transparent animate-spin"></div>
        </div>
        <p class="mt-4 text-emerald-800 font-medium text-sm animate-pulse">Processing...</p>
      </div>
    </div>
  </transition>

  <!-- Global Error Toast (Simple implementation, can be upgraded) -->
  <transition name="slide-up">
    <div v-if="uiStore.globalError" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-red-500 text-white px-6 py-3 rounded-full shadow-lg flex items-center gap-3">
       <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
         <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
       </svg>
       <span>{{ uiStore.globalError }}</span>
    </div>
  </transition>
</template>

<script setup>
import { useUiStore } from '@/stores/ui';
const uiStore = useUiStore();
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
  transform: translate(-50%, 20px);
  opacity: 0;
}
</style>
