<template>
    <div class="h-full flex flex-col space-y-4">
        <!-- Kanban Board -->
        <div class="flex-1 overflow-x-auto overflow-y-hidden">
            <div class="h-full flex space-x-4 pb-4 px-4 sm:px-0 min-w-max">
                
                <div v-for="stage in stages" :key="stage.id" 
                     class="w-80 flex flex-col bg-gray-100 rounded-lg p-3 transition-colors duration-200 hover:bg-gray-200"
                     @dragover.prevent
                     @drop="onDrop($event, stage)">
                    
                    <!-- Column Header -->
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase">{{ stage.name }}</h3>
                        <span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full">
                            {{ getBugs(stage.id).length }}
                        </span>
                    </div>

                    <!-- Draggable Area -->
                    <div class="flex-1 overflow-y-auto space-y-3 min-h-[100px] custom-scrollbar">
                        <div v-for="bug in getBugs(stage.id)" 
                             :key="bug.id"
                             draggable="true"
                             @dragstart="onDragStart($event, bug)"
                             @click="$emit('open', bug)"
                             class="bg-white p-3 rounded-md shadow-sm border border-gray-200 hover:shadow-md cursor-grab active:cursor-grabbing transition-all border-l-4"
                             :class="getSeverityBorderColor(bug.severity)">
                            
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-mono text-gray-500">#{{ bug.id }}</span>
                                <span v-if="bug.priority === 'urgent'" class="text-sm bg-red-100 text-red-800 px-1 rounded font-bold">URGENT</span>
                            </div>

                            <h4 class="text-sm font-medium text-gray-900 mb-2 line-clamp-2">{{ bug.subject }}</h4>
                            
                            <div class="flex justify-between items-center mt-3">
                                <div class="flex items-center space-x-2">
                                     <img v-if="bug.assignee" 
                                          :src="bug.assignee.avatar || `https://ui-avatars.com/api/?name=${bug.assignee.first_name || bug.assignee.name}&background=random`" 
                                          class="h-6 w-6 rounded-full border border-white" 
                                          :title="bug.assignee.name || bug.assignee.first_name" />
                                     <span v-else class="text-xs text-gray-400 italic">Unassigned</span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ new Date(bug.updated_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) }}
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    bugs: Array,
    stages: Array
});

const emit = defineEmits(['update-stage', 'open']);

const onDragStart = (event, bug) => {
    event.dataTransfer.dropEffect = 'move';
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', JSON.stringify(bug));
};

const onDrop = (event, stage) => {
    const data = event.dataTransfer.getData('text/plain');
    if (!data) return;
    
    try {
        const bug = JSON.parse(data);
        if (bug.workflow_stage_id !== stage.id) {
            // Store original stage for revert?
            // Actually, parent handles the API call and refresh.
            // But for a smoother experience, we should probably handle it here
            // or let the parent revert.
            // Let's stick to emitting deeply.
            emit('update-stage', { bug, stage });
        }
    } catch (e) {
        console.error("Drag drop parse error", e);
    }
};

const getBugs = (stageId) => {
    return props.bugs.filter(b => b.workflow_stage_id === stageId);
};

const getSeverityBorderColor = (severity) => {
    switch (severity) {
        case 'critical': return 'border-l-4 border-l-red-500';
        case 'high': return 'border-l-4 border-l-orange-500';
        case 'medium': return 'border-l-4 border-l-yellow-500';
        case 'low': return 'border-l-4 border-l-green-500';
        default: return 'border-l-4 border-l-gray-200';
    }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #888; 
    border-radius: 2px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #555; 
}
</style>
