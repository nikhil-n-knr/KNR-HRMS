<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div><h2 class="text-xl font-black text-gray-900">{{ title }}</h2><p class="text-xs text-gray-500 mt-0.5">{{ description }}</p></div>
        </div>
        <div class="flex-1 overflow-y-auto p-8 custom-scrollbar flex flex-col">
            <slot />
            <div v-if="!$slots.default" class="flex-1 flex flex-col items-center justify-center text-gray-400">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" :class="`bg-${color}-50`">
                    <i :class="[icon, `text-${color}-300`, 'text-3xl']"></i>
                </div>
                <h3 class="text-lg font-black text-gray-600 mb-2">{{ title }}</h3>
                <p class="text-sm text-gray-400 text-center max-w-sm">{{ description }}</p>
                <p class="text-sm font-bold text-gray-300 uppercase tracking-widest mt-4">Coming in next sprint</p>
            </div>
        </div>
    </div>
</template>
<script setup>
defineProps({
    title: { type: String, required: true },
    icon: { type: String, default: 'fas fa-cube' },
    color: { type: String, default: 'gray' },
    description: { type: String, default: '' },
});
</script>
<style scoped>.custom-scrollbar::-webkit-scrollbar{width:6px}.custom-scrollbar::-webkit-scrollbar-track{background:transparent}.custom-scrollbar::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:10px}</style>
