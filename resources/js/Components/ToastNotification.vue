<template>
  <div class="fixed top-5 right-5 z-[100] flex flex-col gap-3 pointer-events-none" style="width: 28rem;">
    <transition-group 
      tag="div"
      class="flex flex-col gap-3 items-end"
      enter-active-class="transform ease-out duration-300 transition" 
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" 
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" 
      leave-active-class="transition ease-in duration-200" 
      leave-from-class="opacity-100" 
      leave-to-class="opacity-0 scale-95"
    >
      <div 
        v-for="toast in nonSuccessToasts" 
        :key="toast.id" 
        class="w-full bg-white/90 backdrop-blur-xl shadow-xl rounded-xl pointer-events-auto border border-white/50 overflow-hidden transform transition-all hover:scale-[1.02]"
        :class="{
            'border-l-4 border-emerald-500': toast.type === 'success',
            'border-l-4 border-red-500': toast.type === 'error',
            'border-l-4 border-blue-500': toast.type === 'info',
        }"
      >
        <div class="p-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
               <!-- Success Icon -->
              <div v-if="toast.type === 'success'" class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center">
                  <svg class="h-5 w-5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
              </div>
              <!-- Error Icon -->
              <div v-if="toast.type === 'error'" class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center">
                  <svg class="h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
              </div>
              <!-- Info Icon -->
              <div v-if="toast.type === 'info'" class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                  <svg class="h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
              </div>
            </div>
            <div class="ml-3 w-0 flex-1 pt-1">
              <p class="text-sm font-semibold text-gray-800">{{ toast.type.charAt(0).toUpperCase() + toast.type.slice(1) }}</p>
              <p class="text-xs text-gray-600 mt-0.5 leading-relaxed">{{ toast.message }}</p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
              <button @click="toastStore.remove(toast.id)" class="bg-transparent rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none transition-colors">
                <span class="sr-only">Close</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition-group>
  </div>

  <div class="fixed bottom-5 right-5 z-[100] flex flex-col gap-3 pointer-events-none" style="width: 28rem;">
    <transition-group 
      tag="div"
      class="flex flex-col gap-3 items-end"
      enter-active-class="transform ease-out duration-300 transition" 
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" 
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" 
      leave-active-class="transition ease-in duration-200" 
      leave-from-class="opacity-100" 
      leave-to-class="opacity-0 scale-95"
    >
      <div 
        v-for="toast in successToasts" 
        :key="toast.id" 
        class="w-full bg-white/90 backdrop-blur-xl shadow-xl rounded-xl pointer-events-auto border border-white/50 overflow-hidden transform transition-all hover:scale-[1.02]"
        :class="{
            'border-l-4 border-emerald-500': toast.type === 'success',
            'border-l-4 border-red-500': toast.type === 'error',
            'border-l-4 border-blue-500': toast.type === 'info',
        }"
      >
        <div class="p-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
               <!-- Success Icon -->
              <div v-if="toast.type === 'success'" class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center">
                  <svg class="h-5 w-5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
              </div>
              <!-- Error Icon -->
              <div v-if="toast.type === 'error'" class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center">
                  <svg class="h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
              </div>
              <!-- Info Icon -->
              <div v-if="toast.type === 'info'" class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                  <svg class="h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
              </div>
            </div>
            <div class="ml-3 w-0 flex-1 pt-1">
              <p class="text-sm font-semibold text-gray-800">{{ toast.type.charAt(0).toUpperCase() + toast.type.slice(1) }}</p>
              <p class="text-xs text-gray-600 mt-0.5 leading-relaxed">{{ toast.message }}</p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
              <button @click="toastStore.remove(toast.id)" class="bg-transparent rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none transition-colors">
                <span class="sr-only">Close</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useToastStore } from '@/stores/toast';

const toastStore = useToastStore();
const successToasts = computed(() => toastStore.toasts.filter(t => t.type === 'success'));
const nonSuccessToasts = computed(() => toastStore.toasts.filter(t => t.type !== 'success'));
</script>
