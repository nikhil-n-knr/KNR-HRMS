<script setup>
defineProps({
    show: Boolean,
    title: { type: String, default: 'Are you sure?' },
    message: { type: String, default: 'This action cannot be undone.' },
    confirmText: { type: String, default: 'Confirm' },
    cancelText: { type: String, default: 'Cancel' },
    type: { type: String, default: 'danger' } // danger, warning, info, success
});

const emit = defineEmits(['close', 'confirm']);
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>

        <!-- content -->
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 p-6 transform transition-all scale-100 animate-in fade-in zoom-in-95 duration-200">
             <div class="flex flex-col items-center text-center">
                 <!-- Icon -->
                 <div v-if="type === 'danger'" class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4 text-xl">
                     🗑️
                 </div>
                 <div v-else-if="type === 'warning'" class="w-12 h-12 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mb-4 text-xl">
                     ⚠️
                 </div>
                 <div v-else-if="type === 'success'" class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-4 text-xl">
                     ✅
                 </div>
                 <div v-else class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4 text-xl">
                     ℹ️
                 </div>

                 <h3 class="text-lg font-bold text-gray-900 mb-2">{{ title }}</h3>
                 <p class="text-sm text-gray-500 mb-6">{{ message }}</p>

                 <div class="flex gap-3 w-full">
                     <button @click="$emit('close')" class="flex-1 px-4 py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold rounded-xl transition-colors">
                         {{ cancelText }}
                     </button>
                     
                     <button 
                        @click="$emit('confirm')" 
                        class="flex-1 px-4 py-2.5 text-white font-bold rounded-xl shadow-md transition-all transform active:scale-95"
                        :class="{
                            'bg-red-600 hover:bg-red-700 shadow-red-200': type === 'danger',
                            'bg-amber-500 hover:bg-amber-600 shadow-amber-200': type === 'warning',
                            'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-200': type === 'success',
                            'bg-blue-600 hover:bg-blue-700 shadow-blue-200': type === 'info'
                        }"
                     >
                         {{ confirmText }}
                     </button>
                 </div>
             </div>
        </div>
    </div>
</template>
