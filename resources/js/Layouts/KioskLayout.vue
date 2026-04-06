<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <div class="h-screen w-screen overflow-hidden bg-white select-none touch-manipulation">
        <!-- PWA Status Bar Spacer if needed -->
        <header class="hidden">
           <!-- Meta Tags for PWA handled in Head if passed -->
        </header>

        <main class="h-full w-full">
            <slot />
        </main>
        
        <!-- Optional Kiosk Toolbar (Hidden trigger for admin exit) -->
        <div class="fixed bottom-0 right-0 w-16 h-16 z-50 opacity-0" @dblclick="$inertia.visit(route('dashboard'))"></div>
    </div>
</template>

<style>
/* Disable Text Selection and Zoom for Kiosk Feel */
body {
    -webkit-user-select: none;
    -webkit-touch-callout: none;
    overscroll-behavior: none;
}
</style>
