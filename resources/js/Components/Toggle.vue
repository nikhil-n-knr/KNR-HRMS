<template>
  <button
    type="button"
    @click="$emit('update:modelValue', !modelValue)"
    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
    :class="modelValue ? 'bg-emerald-600' : 'bg-slate-200'"
  >
    <span
      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
      :class="modelValue ? 'translate-x-5' : 'translate-x-0'"
    ></span>
  </button>
</template>

<script setup>
defineProps({
  modelValue: Boolean
});

defineEmits(['update:modelValue']);
</script>
