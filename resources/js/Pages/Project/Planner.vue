<template>
    <div class="h-full flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <div>
                 <h1 class="text-2xl font-bold text-gray-800">Resource Planner</h1>
                 <p class="text-gray-500 text-sm">Drag and drop to assign resources</p>
            </div>
            <div class="flex gap-2">
                <button class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50">Day</button>
                <button class="px-3 py-1.5 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded-lg text-sm font-medium">Week</button>
                <button class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50">Month</button>
            </div>
        </div>

        <div class="flex-1 bg-white border border-gray-200 rounded-xl flex items-center justify-center">
            <div class="text-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900">Gantt Chart Loading...</h3>
                <p class="max-w-md mx-auto mt-2">The "God Mode" Resource Planner with drag-and-drop assignment is explicitly scheduled for Phase 6.2.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
defineOptions({ layout: MainLayout });
</script>
