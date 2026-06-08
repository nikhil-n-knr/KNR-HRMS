<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 text-gray-700">
        <h1 class="text-6xl font-bold mb-4">404</h1>
        <p class="text-xl mb-8">Page Not Found</p>
        <Link href="/" class="text-emerald-600 hover:underline">Go Home</Link>
    </div>
</template>
