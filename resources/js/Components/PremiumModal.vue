<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 md:p-10">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-md transition-opacity" @click="close"></div>

                <!-- Modal Container -->
                <div 
                    class="relative w-full bg-white/90 backdrop-blur-2xl border border-slate-200/50 shadow-[0_20px_50px_rgba(0,0,0,0.1)] rounded-2xl overflow-hidden transform transition-all flex flex-col max-h-[90vh]"
                    :class="maxWidthClass"
                >
                    <!-- Premium Header -->
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-slate-50/50 to-transparent relative shrink-0">
                        <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-500 opacity-20"></div>
                        
                        <div class="flex items-center gap-4">
                            <div v-if="icon" class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center text-white shadow-md shadow-emerald-100 rotate-2 shrink-0">
                                <i :class="['fas', icon, 'text-base']"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-800 tracking-tight uppercase leading-none">{{ title }}</h3>
                                <p v-if="subtitle" class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1.5 block leading-none ml-0.5">{{ subtitle }}</p>
                            </div>
                        </div>

                        <button @click="close" class="w-8 h-8 rounded-lg bg-slate-100/50 text-slate-400 flex items-center justify-center hover:bg-rose-50 hover:text-rose-500 transition-all active:scale-90 shrink-0">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                        <slot></slot>
                    </div>

                    <!-- Footer (Optional) -->
                    <div v-if="$slots.footer" class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex justify-end gap-3 shrink-0">
                        <slot name="footer"></slot>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    title: { type: String, default: 'Protocol_Dialog' },
    subtitle: String,
    icon: { type: String, default: 'fa-shield-halved' },
    maxWidth: { type: String, default: '2xl' }
});

const emit = defineEmits(['close']);

const close = () => emit('close');

// Close on Escape
const handleEscape = (e) => {
    if (e.key === 'Escape' && props.show) close();
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        document.addEventListener('keydown', handleEscape);
        document.body.style.overflow = 'hidden';
    } else {
        document.removeEventListener('keydown', handleEscape);
        document.body.style.overflow = '';
    }
}, { immediate: true });

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
    }[props.maxWidth];
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
