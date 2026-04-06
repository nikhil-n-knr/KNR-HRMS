<template>
    <div class="w-full min-h-[160px] flex flex-col items-center justify-center bg-gray-50 border-2 border-dashed border-gray-200 py-12 text-center">
        <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
            <i class="fas fa-cube text-2xl text-gray-300"></i>
        </div>
        <p class="font-black text-sm text-gray-400">Unregistered Block</p>
        <p class="text-xs text-gray-300 mt-1 font-mono">type: {{ block?.type }}</p>
        <p class="text-xs text-gray-300 mt-0.5">Add this block type to CanvasEngine's block registry.</p>
    </div>
</template>
<script setup>
defineProps({ block: Object });
</script>
