<template>
    <div class="py-12 flex flex-col items-center justify-center text-center px-4">
        <div class="relative mb-8">
            <!-- Animated circles background -->
            <div class="absolute inset-0 bg-blue-100 rounded-full scale-150 blur-3xl opacity-30 animate-pulse"></div>
            <div class="relative w-24 h-24 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl shadow-xl flex items-center justify-center transform rotate-3 flex-shrink-0">
                <i :class="['fas', `fa-${icon}`, 'text-4xl text-white']"></i>
            </div>
            <!-- Smaller floating icon -->
            <div class="absolute -top-4 -right-4 w-10 h-10 bg-white rounded-xl shadow-lg flex items-center justify-center animate-bounce">
                <i class="fas fa-sparkles text-yellow-500"></i>
            </div>
        </div>

        <h3 class="text-3xl font-extrabold text-gray-900 mb-4 tracking-tight">
            {{ formatTitle(title) }}
        </h3>
        
        <p class="text-lg text-gray-600 max-w-lg mx-auto mb-8 leading-relaxed">
            {{ description || "We're building something amazing here. This section will feature advanced CRM tools powered by AI to help you scale your operations." }}
        </p>

        <div class="inline-flex items-center space-x-4 p-1 bg-white rounded-2xl shadow-sm border border-gray-100 mb-8">
            <div class="flex -space-x-2">
                <div v-for="i in 3" :key="i" class="w-8 h-8 rounded-full border-2 border-white bg-gray-200 overflow-hidden">
                    <img :src="`https://ui-avatars.com/api/?name=Agent+${i}&background=random`" alt="dev">
                </div>
            </div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-widest pl-2">Scheduled for Q2 2026</span>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <button class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-gray-800 transition shadow-lg shadow-gray-200">
                Request Early Access
            </button>
            <button class="px-6 py-3 bg-white text-gray-700 font-bold rounded-xl border border-gray-200 hover:bg-gray-50 transition">
                View Roadmap
            </button>
        </div>

        <div v-if="componentPath" class="mt-12 pt-8 border-t border-gray-100 w-full max-w-md">
            <p class="text-xs text-gray-400 font-mono italic">
                Development Path: {{ componentPath }}
            </p>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    title: String,
    description: String,
    icon: { type: String, default: 'layer-group' },
    componentPath: String,
});

const formatTitle = (str) => {
    if (!str) return 'Coming Soon';
    return str.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};
</script>

<style scoped>
@keyframes sparkles {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.2); opacity: 0.8; }
}
.fa-sparkles {
    animation: sparkles 2s infinite ease-in-out;
}
</style>
