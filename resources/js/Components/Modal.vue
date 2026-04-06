<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-emerald-900/30 backdrop-blur-sm transition-opacity" @click="close"></div>

        <!-- Modal Content -->
        <div class="relative w-full bg-white/70 backdrop-blur-xl border border-white/50 rounded-2xl shadow-2xl overflow-hidden transform transition-all flex flex-col max-h-[90vh]" :class="maxWidthClass">
          <!-- Header -->
          <div v-if="title || $slots.header" class="px-6 py-4 border-b border-gray-100/50 bg-white/30 flex justify-between items-center flex-shrink-0">
              <h3 v-if="title" class="text-lg font-bold text-gray-800">{{ title }}</h3>
              <slot name="header"></slot>
              <button @click="close" class="text-gray-400 hover:text-gray-600 transition ml-auto">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
              </button>
          </div>
          
          <!-- Body -->
          <div class="p-6 overflow-y-auto custom-scrollbar">
              <slot></slot>
          </div>

          <!-- Footer -->
          <div v-if="$slots.footer" class="px-6 py-4 bg-gray-50/50 border-t border-gray-100/50 flex justify-end gap-3 flex-shrink-0">
              <slot name="footer"></slot>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: Boolean,
    title: {
        type: String,
        default: 'Dialog'
    },
    maxWidth: {
        type: String,
        default: 'md'
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
