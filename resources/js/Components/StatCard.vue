<template>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-6 flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500 truncate">{{ title }}</p>
            <p class="mt-1 text-3xl font-semibold text-gray-900">{{ value }}</p>
        </div>
        <div v-if="change" class="flex flex-col items-end">
             <div class="flex items-center text-sm font-medium" :class="isPositive ? 'text-green-600' : 'text-red-600'">
                <svg v-if="isPositive" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                <svg v-else class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                {{ change }}
             </div>
             <span class="text-xs text-gray-400 mt-1">vs last month</span>
        </div>
        <div v-else class="p-3 bg-indigo-50 rounded-full text-indigo-600">
             <slot name="icon"></slot>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: String,
    value: String,
    change: String // e.g., "+5.2%"
});

const isPositive = computed(() => {
    return props.change && !props.change.startsWith('-');
});
</script>
