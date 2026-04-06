<template>
  <Transition name="modal">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-emerald-900/30 backdrop-blur-sm transition-opacity" @click="close"></div>

      <!-- Modal Content (No default header, generic container) -->
      <div class="relative w-full bg-white shadow-2xl overflow-hidden transform transition-all flex flex-col max-h-[95vh] rounded-2xl" :class="maxWidthClass">
        
        <!-- Body (Full slot control) -->
        <div class="flex-1 overflow-hidden flex flex-col">
            <slot></slot>
        </div>

      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: Boolean,
    maxWidth: {
        type: String,
        default: '2xl'
    }
});

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};

const maxWidthClass = computed(() => {
    return {
        'sm': 'max-w-sm',
        'md': 'max-w-md',
        'lg': 'max-w-lg',
        'xl': 'max-w-xl',
        '2xl': 'max-w-2xl',
        '3xl': 'max-w-3xl',
        '4xl': 'max-w-4xl',
        '5xl': 'max-w-5xl',
        '6xl': 'max-w-6xl',
        '7xl': 'max-w-7xl',
    }[props.maxWidth];
});
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .transform,
.modal-leave-active .transform {
   transition: all 0.3s ease;
}
.modal-enter-from .transform,
.modal-leave-to .transform {
   opacity: 0;
   transform: scale(0.95) translateY(10px);
}
</style>
