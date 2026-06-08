<script setup>
import { computed } from 'vue';

const props = defineProps({
    project: Object,
    tasks: Array
});

// Since the full Kanban logic is complex, we will link to the dedicated Board page for now
// or implemented a simplified view.
// In the long run, we would port the entire Board.vue logic here.
// For the purpose of this "Command Center" refactor, we will provide a "Go to Full Board" button 
// and a simplified column view.

const columns = computed(() => {
    // Basic grouping
    const cols = {};
    if (props.project && props.project.stages) {
        props.project.stages.forEach(s => cols[s.id] = []);
        props.tasks.forEach(task => {
            if (cols[task.stage_id]) cols[task.stage_id].push(task);
        });
    }
    return cols;
});
</script>

<template>
    <div class="h-[calc(100vh-200px)] flex flex-col animate-fade-in">
        <div class="flex justify-between items-center mb-4 px-2">
            <h3 class="font-bold text-gray-700">Sprint Board</h3>
             <!-- Link to original board if needed, or if we fully migrate, this shouldn't be here -->
             <!-- We are pretending this IS the board now -->
        </div>

        <div class="flex-1 overflow-x-auto overflow-y-hidden pb-4">
             <div class="flex h-full gap-4 min-w-[1000px]">
                 <div 
                    v-for="stage in project?.stages" 
                    :key="stage.id"
                    class="w-80 flex flex-col bg-gray-100 rounded-xl max-h-full"
                 >
                    <div class="p-3 border-b border-gray-200 font-bold text-gray-700 flex justify-between">
                        {{ stage.name }}
                        <span class="text-xs bg-gray-200 px-2 rounded-full">{{ columns[stage.id]?.length || 0 }}</span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-3 custom-scrollbar">
                        <div 
                            v-for="task in columns[stage.id]" 
                            :key="task.id"
                            class="bg-white p-3 rounded-lg shadow-sm border border-gray-200 hover:shadow-md cursor-pointer transition-all"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-mono text-gray-400">#{{ task.id }}</span>
                                <span class="w-2 h-2 rounded-full bg-red-500" v-if="task.priority === 'High'"></span>
                            </div>
                            <h4 class="text-sm font-medium text-gray-900 mb-2">{{ task.title }}</h4>
                            <div class="flex items-center gap-2">
                                <img v-for="u in task.assignees" :key="u.id" :src="u.avatar || `https://ui-avatars.com/api/?name=${u.name}`" class="h-5 w-5 rounded-full border border-white" />
                            </div>
                        </div>
                    </div>
                 </div>
             </div>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
