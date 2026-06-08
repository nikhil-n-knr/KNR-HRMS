<script setup>
import KioskLayout from '@/Layouts/KioskLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    pass: Object,
    visitor: Object,
    host: Object
});

const printing = ref(true);
const progress = ref(0);

onMounted(() => {
    // Simulate printing delay
    const interval = setInterval(() => {
        progress.value += 5;
        if (progress.value >= 100) {
            clearInterval(interval);
            printing.value = false;
            
            // Auto redirect to standby
            setTimeout(() => {
                router.visit(route('kiosk.standby'));
            }, 5000);
        }
    }, 100);
});
</script>

<template>
    <Head title="Welcome" />
    <KioskLayout>
        <div class="h-full flex flex-col items-center justify-center p-8 bg-white relative overflow-hidden">
            
            <!-- Confetti / Success Animation Background (CSS only for MVP) -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                 <div class="absolute top-1/4 left-1/4 w-4 h-4 bg-red-400 rounded-full animate-ping"></div>
                 <div class="absolute top-1/3 right-1/4 w-3 h-3 bg-blue-400 transform rotate-45 animate-bounce"></div>
                 <div class="absolute bottom-1/4 left-1/3 w-6 h-6 bg-yellow-400 rounded-sm animate-pulse"></div>
            </div>

            <div class="text-center relative z-10 max-w-2xl">
                 <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-green-100 mb-8 animate-fade-in-up">
                    <svg class="w-16 h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                 </div>

                 <h1 class="text-5xl font-black text-gray-900 mb-4 animate-fade-in-up delay-100">Welcome, {{ visitor.name.split(' ')[0] }}!</h1>
                 <p class="text-xl text-gray-500 mb-12 animate-fade-in-up delay-200">
                     Your host, <span class="font-bold text-gray-800">{{ host.name }}</span>, has been notified.
                 </p>

                 <!-- Printing Status -->
                 <div class="bg-gray-50 rounded-2xl p-8 border border-gray-100 w-full animate-fade-in-up delay-300">
                      <div v-if="printing">
                          <div class="text-lg font-bold text-gray-800 mb-4 flex items-center justify-center">
                              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                              </svg>
                              Printing Badge...
                          </div>
                          <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-100 ease-out" :style="{ width: progress + '%' }"></div>
                          </div>
                      </div>
                      <div v-else class="text-center">
                           <div class="text-indigo-600 font-bold text-lg mb-2">Badge Printed!</div>
                           <p class="text-gray-500 text-sm">Please collect your badge from the printer below.</p>
                           <p class="text-xs text-gray-400 mt-4">Redirecting in 5s...</p>
                      </div>
                 </div>
            </div>

            <!-- Footer -->
            <div class="absolute bottom-8 text-gray-400 text-sm">
                Need help? Ask Reception.
            </div>
        </div>
    </KioskLayout>
</template>
