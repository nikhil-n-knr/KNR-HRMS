<template>
  <Transition name="modal">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-emerald-900/40 backdrop-blur-sm transition-opacity" @click="close"></div>

      <!-- Modal Content (Large) -->
      <div class="relative w-full max-w-4xl bg-white/80 backdrop-blur-2xl border border-white/60 rounded-3xl shadow-2xl overflow-hidden transform transition-all flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="px-8 py-5 border-b border-gray-100/50 bg-white/40 flex justify-between items-center flex-shrink-0">
            <h3 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-800 to-gray-600">{{ title }}</h3>
            <button @click="close" class="text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg p-1 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <!-- Body -->
        <div class="p-8 overflow-y-auto custom-scrollbar flex-1">
            <slot></slot>
        </div>

        <!-- Footer -->
        <div class="px-8 py-5 bg-gray-50/50 border-t border-gray-100/50 flex justify-end gap-3 flex-shrink-0">
            <slot name="footer">
                <button @click="close" class="px-5 py-2.5 text-gray-600 hover:bg-gray-100/80 rounded-xl text-sm font-semibold transition-colors border border-transparent hover:border-gray-200">Cancel</button>
            </slot>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
const props = defineProps({
    show: Boolean,
    title: {
        type: String,
        default: 'Dialog'
    }
});

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};
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
   transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); /* Ease Out Expoish */
}
.modal-enter-from .transform,
.modal-leave-to .transform {
   opacity: 0;
   transform: scale(0.96) translateY(10px);
}
</style>
