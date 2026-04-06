<template>
  <div class="mb-4">
    <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1">{{ label }}</label>
    <div class="relative">
      <textarea
        v-bind="$attrs"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-all text-sm py-2.5 px-4 bg-white/50 backdrop-blur-sm hover:bg-white"
        :class="[
          error ? 'border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500' : '',
          inputClass
        ]"
      ></textarea>
      
      <!-- Error Icon -->
      <div v-if="error" class="absolute top-2 right-2 flex items-center pointer-events-none">
        <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
      </div>
    </div>
    <p v-if="error" class="mt-1 text-xs text-red-600 animate-pulse">{{ error }}</p>
  </div>
</template>

<script setup>
defineProps({
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
  }
});

defineEmits(['update:modelValue']);
</script>
