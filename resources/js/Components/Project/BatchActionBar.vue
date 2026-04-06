<template>
    <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-white rounded-full shadow-2xl border border-indigo-100 p-2 px-6 flex items-center gap-6 animate-in slide-in-from-bottom-4 duration-200">
        <div class="flex items-center gap-2 border-r border-gray-200 pr-4">
            <span class="bg-indigo-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ count }}</span>
            <span class="text-sm font-medium text-gray-700">Selected</span>
        </div>
        
        <div class="flex items-center gap-2">
            <button @click="$emit('move-sprint')" class="flex items-center gap-1.5 px-3 py-1.5 hover:bg-gray-100 rounded-lg text-sm font-medium text-gray-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                Move to Sprint
            </button>
            <button @click="$emit('bulk-assign')" class="flex items-center gap-1.5 px-3 py-1.5 hover:bg-gray-100 rounded-lg text-sm font-medium text-gray-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                Assign
            </button>
        </div>

        <button @click="$emit('cancel')" class="ml-2 text-gray-400 hover:text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
        </button>
    </div>
</template>

<script setup>
defineProps({
    count: Number
});

defineEmits(['cancel', 'move-sprint', 'bulk-assign']);
</script>
