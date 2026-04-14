<template>
  <div class="mb-4">
    <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1">{{ label }}</label>
    <div class="relative">
      <select
        v-bind="$attrs"
        :value="modelValue"
        @change="$emit('update:modelValue', $event.target.value)"
        class="block w-full rounded-xl shadow-sm text-sm py-2.5 pl-4 pr-10 appearance-none transition-colors bg-white/50 backdrop-blur-sm hover:bg-white"
        :class="[
          colorClasses,
          inputClass
        ]"
      >
        <option v-if="placeholder" value="">{{ placeholder }}</option>
        <slot v-if="hasDefaultSlot"></slot>
        <template v-else>
          <option
            v-for="option in normalizedOptions"
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </template>
      </select>
      
      <!-- Custom Chevron (Optional, relying on browser default or appearance-none + bg-icon usually best) -->
      <!-- Using standard appearance-none with a background icon would be ideal, but for now simple structure -->
      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
        <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
      </div>

      <!-- Error Icon -->
      <div v-if="error" class="absolute inset-y-0 right-8 pr-3 flex items-center pointer-events-none">
        <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
      </div>
    </div>
    <p v-if="error" class="mt-1 text-xs text-red-600 animate-pulse">{{ error }}</p>
  </div>
</template>

<script setup>
import { computed, useSlots } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean],
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
  options: {
    type: Array,
    default: () => []
  },
  placeholder: {
    type: String,
    default: ''
  },
  valueField: {
    type: String,
    default: 'value'
  },
  labelField: {
    type: String,
    default: 'label'
  },
  color: {
    type: String,
    default: 'emerald',
    validator: (value) => ['emerald', 'indigo'].includes(value)
  }
});

defineEmits(['update:modelValue']);

const slots = useSlots();

const hasDefaultSlot = computed(() => Boolean(slots.default));

const normalizedOptions = computed(() => {
  return (props.options || []).map((option) => {
    if (option === null || option === undefined) {
      return { value: '', label: '' };
    }

    if (typeof option !== 'object') {
      return { value: option, label: String(option) };
    }

    const value = option[props.valueField] ?? option.value ?? option.id ?? option.key ?? '';
    const label = option[props.labelField] ?? option.label ?? option.name ?? option.title ?? String(value);

    return { value, label };
  });
});

const colorClasses = computed(() => {
    if (props.error) return 'border-red-300 text-red-900 focus:ring-red-500 focus:border-red-500';
    
    switch (props.color) {
        case 'indigo':
            return 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500';
        case 'emerald':
        default:
            return 'border-gray-300 focus:border-emerald-500 focus:ring-emerald-500';
    }
});
</script>
