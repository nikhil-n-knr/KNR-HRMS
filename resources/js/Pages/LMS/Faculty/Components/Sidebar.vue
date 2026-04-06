<template>
    <div class="w-20 md:w-24 bg-white border-r border-gray-100 flex flex-col h-full z-10 transition-all duration-500">
        <!-- Logo -->
        <div class="h-20 flex items-center justify-center border-b border-gray-100">
            <div class="w-10 h-10 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-indigo-200 shadow-lg transform hover:rotate-12 transition-transform">
                <i class="fas fa-graduation-cap text-xl"></i>
            </div>
        </div>

        <!-- Navigation Icons -->
        <nav class="flex-1 py-10 flex flex-col items-center gap-8 overflow-y-auto no-scrollbar">
            <button 
                v-for="item in navItems" 
                :key="item.id"
                @click="$emit('navigate', item.id)"
                class="group relative w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-300"
                :class="currentSection === item.id ? 'bg-indigo-50 text-indigo-600 shadow-sm' : 'text-gray-400 hover:bg-gray-50 hover:text-gray-600'"
            >
                <i :class="item.icon" class="text-xl"></i>
                <span class="absolute left-full ml-4 px-3 py-1 bg-gray-900 text-white text-[10px] font-bold rounded-lg opacity-0 invisible group-hover:visible group-hover:opacity-100 transition-all z-50 whitespace-nowrap uppercase tracking-widest shadow-xl">
                    {{ item.label }}
                </span>
                <!-- Active Indicator -->
                <div v-if="currentSection === item.id" class="absolute left-0 w-1 h-6 bg-indigo-600 rounded-r-full"></div>
            </button>
        </nav>

        <!-- Bottom Actions -->
        <div class="p-6 border-t border-gray-100 flex flex-col items-center gap-6">
            <button class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all">
                <i class="fas fa-cog"></i>
            </button>
            <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-black shadow-lg shadow-indigo-100 overflow-hidden border-2 border-white">
                <img v-if="$page.props.auth.user.avatar" :src="$page.props.auth.user.avatar" class="w-full h-full object-cover" />
                <span v-else>{{ $page.props.auth.user.name.charAt(0) }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    currentSection: String
});

defineEmits(['navigate']);

const navItems = [
    { id: 'dashboard', label: 'Faculty Hub', icon: 'fas fa-chalkboard-teacher' },
    { id: 'courses', label: 'My Courses', icon: 'fas fa-book-reader' },
    { id: 'grading', label: 'Review Queue', icon: 'fas fa-marker' },
    { id: 'messages', label: 'Batch Comms', icon: 'fas fa-paper-plane' },
    { id: 'sessions', label: 'Live Tracks', icon: 'fas fa-satellite' },
];
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
