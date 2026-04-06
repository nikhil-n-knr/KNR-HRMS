<template>
    <div class="relative pl-6">
        <!-- Connector Lines -->
        <div v-if="depth > 0" class="absolute left-0 top-0 bottom-0 w-px bg-gray-200"></div>
        <div v-if="depth > 0" class="absolute left-0 top-6 w-6 h-px bg-gray-200"></div>

        <!-- Card -->
        <div 
            class="relative mb-3 bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all group"
            :class="{'ring-2 ring-indigo-50 border-indigo-200': isOpen}"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <!-- Toggle Button -->
                    <button 
                        v-if="module.children && module.children.length > 0"
                        @click="isOpen = !isOpen"
                        class="p-1 rounded-md hover:bg-gray-100 text-gray-400 hover:text-indigo-600 transition-colors"
                    >
                        <svg 
                            xmlns="http://www.w3.org/2000/svg" 
                            class="h-4 w-4 transition-transform duration-200"
                            :class="{'rotate-90': isOpen}" 
                            viewBox="0 0 20 20" 
                            fill="currentColor"
                        >
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div v-else class="w-6"></div> <!-- Spacer -->

                    <!-- Module Info -->
                    <div>
                        <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                             {{ module.name }}
                             <span v-if="module.children?.length" class="bg-gray-100 text-gray-500 text-sm px-1.5 py-0.5 rounded-full">{{ module.children.length }}</span>
                        </h4>
                        <p class="text-xs text-gray-500 line-clamp-1" v-if="module.description">{{ module.description }}</p>
                    </div>
                </div>

                <!-- Actions (Hover) -->
                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button 
                        @click="$emit('add-submodule', module)"
                        class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg tooltip-trigger" 
                        title="Add Sub-module"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <button 
                        @click="$emit('edit', module)"
                        class="p-1.5 text-gray-500 hover:bg-gray-100 rounded-lg" 
                        title="Edit"
                    >
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                    </button>
                    <button 
                        @click="$emit('delete', module)"
                        class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg"
                        title="Delete"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Children Recursion (Safe Limit) -->
        <div v-if="isOpen && module.children && depth < 10" class="space-y-2">
            <ModuleTreeItem 
                v-for="child in module.children" 
                :key="child.id" 
                :module="child"
                :depth="depth + 1"
                @add-submodule="$emit('add-submodule', $event)"
                @edit="$emit('edit', $event)"
                @delete="$emit('delete', $event)"
            />
        </div>
        <div v-if="depth >= 10 && module.children && module.children.length" class="pl-4 text-xs text-gray-400 italic">
            Max depth reached.
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    module: Object,
    depth: {
        type: Number,
        default: 0
    }
});

const isOpen = ref(true); // Default open
</script>
