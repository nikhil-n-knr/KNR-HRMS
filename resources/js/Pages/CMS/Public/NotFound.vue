<template>
    <div class="min-h-screen bg-white flex flex-col items-center justify-center text-center px-6 font-sans">
        <div class="max-w-md">
            <div class="w-24 h-24 rounded-3xl bg-gray-100 flex items-center justify-center mx-auto mb-8">
                <i class="fas fa-compass text-4xl text-gray-300"></i>
            </div>
            <h1 class="text-6xl font-black text-gray-900 mb-2">404</h1>
            <h2 class="text-xl font-bold text-gray-500 mb-6">{{ message || 'Page not found' }}</h2>
            <p class="text-gray-400 text-sm mb-8">The page you're looking for doesn't exist or hasn't been published yet.</p>
            <a href="/" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/20">
                <i class="fas fa-home text-xs"></i> Return Home
            </a>
        </div>
    </div>
</template>
<script setup>
defineProps({ site: Object, message: String });
</script>
