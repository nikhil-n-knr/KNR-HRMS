<template>
    <header class="h-20 bg-white border-b border-gray-100 flex items-center justify-between px-8 z-20">
        <div class="flex items-center gap-4">
            <div class="flex flex-col">
                <h1 class="text-xl font-black text-gray-900 tracking-tight capitalize">{{ title }}</h1>
                <p class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] leading-none mt-1">{{ subtitle }}</p>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <div class="flex items-center gap-8 hidden lg:flex">
                <div v-for="stat in stats" :key="stat.label" class="flex flex-col text-right">
                    <span class="text-[10px] font-black text-gray-300 uppercase tracking-widest leading-none mb-1">{{ stat.label }}</span>
                    <span class="text-base font-black text-gray-900 leading-none">{{ stat.value }}</span>
                </div>
            </div>
            
            <div class="h-10 w-px bg-gray-100 hidden lg:block"></div>

            <div class="flex items-center gap-4">
                <button class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:text-indigo-600 transition-all border border-gray-100 shadow-sm">
                    <i class="fas fa-search"></i>
                </button>
                <button class="w-10 h-10 rounded-xl bg-gray-900 flex items-center justify-center text-white shadow-xl hover:bg-black transition-all">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
    </header>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    section: String,
    tab: String,
    title: String
});

const subtitle = computed(() => {
    return `${props.section} / ${props.tab}`.toUpperCase();
});

const stats = [
    { label: 'Unread', value: '12' },
    { label: 'Today', value: '4' },
    { label: 'Active', value: '8' }
];
</script>
