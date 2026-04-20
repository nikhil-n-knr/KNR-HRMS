<template>
  <div class="mb-4">
    <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1">{{ label }}</label>
    <div class="relative">
      <input
        v-bind="$attrs"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        @change="$emit('update:modelValue', $event.target.value)"
        class="w-full rounded-xl shadow-sm transition-all text-sm py-2.5 px-4 bg-white/50 backdrop-blur-sm hover:bg-white"
        :class="[
          colorClasses,
          inputClass
        ]"
      />
      
      <!-- Error Icon -->
      <div v-if="error" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
        <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
      </div>
    </div>
    <p v-if="error" class="mt-1 text-xs text-red-600 animate-pulse">{{ error }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  inputClass: {
    type: String,
    default: ''
  },
  color: {
    type: String,
    default: 'emerald', // emerald | indigo
    validator: (value) => ['emerald', 'indigo'].includes(value)
  }
});

defineEmits(['update:modelValue']);



const colorClasses = computed(() => {
    if (props.error) return 'border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500';
    
    switch (props.color) {
        case 'indigo':
            return 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500';
        case 'emerald':
        default:
            return 'border-gray-300 focus:border-emerald-500 focus:ring-emerald-500';
    }
});

</script>
