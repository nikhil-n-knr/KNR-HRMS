<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    steps: {
        type: Array,
        default: () => ['Sync Data', 'Variables', 'Calculate', 'Verification', 'Lock']
    },
    currentStep: {
        type: Number,
        default: 0
    }
});
</script>

<template>
    <div class="w-full py-6">
        <div class="flex items-center justify-between relative">
            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-200 -z-10"></div>
            <div 
                class="absolute left-0 top-1/2 transform -translate-y-1/2 h-1 bg-indigo-600 -z-10 transition-all duration-500 ease-in-out"
                :style="{ width: `${(currentStep / (steps.length - 1)) * 100}%` }"
            ></div>

            <div 
                v-for="(step, index) in steps" 
                :key="index"
                class="flex flex-col items-center gap-2 group cursor-default"
            >
                <div 
                    class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all duration-300 relative bg-white"
                    :class="[
                        index < currentStep ? 'border-indigo-600 bg-indigo-600 text-white' : 
                        index === currentStep ? 'border-indigo-600 text-indigo-600 scale-110 shadow-lg' : 
                        'border-gray-300 text-gray-400'
                    ]"
                >
                    <svg v-if="index < currentStep" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span v-else>{{ index + 1 }}</span>
                    
                    <!-- Pulse effect for current step -->
                    <span v-if="index === currentStep" class="absolute inset-0 rounded-full animate-ping bg-indigo-400 opacity-20"></span>
                </div>
                <span 
                    class="text-xs font-semibold whitespace-nowrap transition-colors"
                    :class="index <= currentStep ? 'text-indigo-800' : 'text-gray-400'"
                >
                    {{ step }}
                </span>
            </div>
        </div>
    </div>
</template>
