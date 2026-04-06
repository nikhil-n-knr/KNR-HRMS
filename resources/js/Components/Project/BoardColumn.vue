<template>
    <div 
        class="flex-1 flex flex-col rounded-2xl max-w-sm min-w-[300px] transition-colors duration-300"
        :class="[
            isDragOver ? 'bg-indigo-50/80 ring-2 ring-indigo-400' : 'bg-gray-100/50 hover:bg-gray-100/80'
        ]"
        @dragover.prevent="$emit('dragover', stage.id)"
        @drop="$emit('drop', stage.id)"
    >
        <!-- Column Header -->
        <div class="p-4 flex items-center justify-between border-b border-gray-200/50">
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full shadow-sm" :style="{ backgroundColor: stage.color }"></span>
                <h3 class="font-bold text-gray-700">{{ stage.name }}</h3>
            </div>
            <span class="bg-white/50 px-2 py-0.5 rounded-md text-xs font-semibold text-gray-500 shadow-sm border border-white/50">
                {{ tasks.length || 0 }}
            </span>
        </div>

        <!-- Tasks Container -->
        <div class="flex-1 overflow-y-auto px-4 pb-4 pt-4 space-y-3 custom-scrollbar">
            <TaskCard 
                v-for="task in tasks" 
                :key="task.id"
                :task="task"
                :is-dragging="draggingTask?.id === task.id"
                :stage-color="stage.color"
                :priorities="priorities"
                :is-selected="selectedTaskIds.includes(task.id)"
                @dragstart="(t, e) => $emit('dragstart', t, e)"
                @edit="(t) => $emit('editTask', t)"
                @toggle-select="(t) => $emit('toggle-select', t)"
            />
            
            <!-- Empty State hint if needed -->
            <div v-if="tasks.length === 0" class="h-full flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                <span class="text-xs text-gray-400">Drop tasks here</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import TaskCard from './TaskCard.vue';

defineProps({
    stage: Object,
    tasks: Array,
    draggingTask: Object,
    isDragOver: Boolean,
    isDragOver: Boolean,
    priorities: Array,
    selectedTaskIds: {
        type: Array,
        default: () => []
    }
});

defineEmits(['dragover', 'drop', 'dragstart', 'editTask', 'toggle-select']);
</script>
