<script setup>
import { Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import AppFooter from '@/Components/UI/AppFooter.vue';

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    tabs: {
        type: Array,
        required: true,
        // Expected format: [{ id: 'overview', label: 'Overview' }, ...]
    },
    activeTab: {
        type: String,
        required: true
    }
});
</script>

<template>
  <MainLayout>
    <div class="glass-container flex flex-col h-[calc(100vh-4rem)] bg-slate-50">
        <!-- Glass Header -->
        <header class="flex justify-between items-center px-6 py-4 bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-20">
      <div>
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">{{ title }}</h1>
        <div class="text-xs text-gray-500 mt-1 font-medium">
          <slot name="meta"></slot> 
        </div>
      </div>
      <div class="flex gap-3 items-center">
         <slot name="actions"></slot>
      </div>
    </header>

    <!-- Floating Tab Bar -->
    <nav class="flex px-6 border-b border-gray-200/60 bg-white/60 backdrop-blur-sm sticky top-[73px] z-10 overflow-x-auto hide-scrollbar">
      <Link 
        v-for="tab in tabs" 
        :key="tab.id"
        :href="`?tab=${tab.id}`"
        preserve-state
        replace
        class="px-4 py-3 text-sm font-medium transition-all relative whitespace-nowrap"
        :class="activeTab === tab.id ? 'text-blue-600' : 'text-gray-500 hover:text-gray-800'"
      >
        {{ tab.label }}
        <!-- Active Indicator -->
        <div v-if="activeTab === tab.id" class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600 rounded-t-full layout-id='active-tab'" />
      </Link>
    </nav>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto p-6 scroll-smooth">
      <div class="max-w-7xl mx-auto w-full">
        <!-- Fade-Slide Transition for Tab Content -->
        <Transition 
            enter-active-class="transition ease-out duration-200" 
            enter-from-class="opacity-0 translate-y-2" 
            enter-to-class="opacity-100 translate-y-0" 
            leave-active-class="transition ease-in duration-150" 
            leave-from-class="opacity-100 translate-y-0" 
            leave-to-class="opacity-0 translate-y-2"
            mode="out-in"
        >
            <div :key="activeTab">
                <slot />
            </div>
         </Transition>
         <AppFooter />
      </div>
    </main>
  </div>
  </MainLayout>
</template>

<style scoped>
.glass-container {
    background: radial-gradient(circle at top left, #f1f5f9, #eff6ff);
}
/* Hide scrollbar for tabs implies horizontal scroll */
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
