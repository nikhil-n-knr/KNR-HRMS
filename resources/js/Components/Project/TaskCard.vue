<template>
    <div 
        draggable="true"
        @dragstart="$emit('dragstart', task, $event)"
        @click="$emit('edit', task)"
        class="bg-white rounded-xl p-4 shadow-sm border border-transparent hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-500/10 cursor-pointer transition-all duration-200 group active:cursor-grabbing border-l-4"
        :class="{'opacity-50 dashed-border': isDragging}"
        :style="{ borderLeftColor: stageColor }"
    >
         <!-- Selection Checkbox (Hover/Selected) -->
        <div class="absolute top-2 right-2 z-20" :class="isSelected ? 'block' : 'hidden group-hover:block'">
             <input 
                type="checkbox" 
                :checked="isSelected" 
                @click.stop="$emit('toggle-select', task)"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 h-5 w-5 cursor-pointer"
            />
        </div>

        <!-- Hierarchy Breadcrumb -->
        <div v-if="task.module" class="flex items-center gap-1 text-[10px] text-gray-400 font-medium mb-1.5 uppercase tracking-wide truncate">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 13a1 1 0 011-1h2a1 1 0 011 1v3a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3z" /></svg>
            <span v-if="task.module.parent" class="text-gray-300">{{ task.module.parent.name }} <span class="text-gray-300">/</span></span>
            {{ task.module.name }}
        </div>

        <!-- Tags / Meta -->
        <div class="flex justify-between items-start mb-2">
             <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide" 
                :style="priorityStyle"
             >
                {{ task.priority }}
             </span>
             <div v-if="task.assignees && task.assignees.length" class="flex items-center -space-x-2">
                 <div v-for="user in task.assignees.slice(0, 3)" :key="user.id" class="h-6 w-6 rounded-full bg-indigo-100 border border-white flex items-center justify-center text-[10px] font-bold text-indigo-700 relative z-10" :title="user.name">
                     {{ getInitials(user.name || user.first_name) }}
                 </div>
                 <div v-if="task.assignees.length > 3" class="h-6 w-6 rounded-full bg-gray-100 border border-white flex items-center justify-center text-[10px] font-bold text-gray-600 relative z-0">
                     +{{ task.assignees.length - 3 }}
                 </div>
             </div>
        </div>

        <h4 class="text-sm font-semibold text-gray-800 mb-1 group-hover:text-indigo-600 leading-snug">{{ task.title }}</h4>
        
        <div class="flex items-center justify-between mt-3 text-xs text-gray-400">
            <div class="flex items-center gap-3">
                <span class="flex items-center gap-1 font-medium text-gray-500" v-if="task.scrum_points">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" /></svg>
                    {{ task.scrum_points }}
                </span>

                <!-- Checklist -->
                <span v-if="task.checklists_count" class="flex items-center gap-1 text-gray-500" :title="`Checklist: ${task.completed_checklists_count || 0}/${task.checklists_count}`">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" /></svg>
                    {{ task.completed_checklists_count || 0 }}/{{ task.checklists_count }}
                </span>

                <!-- Comments -->
                <span v-if="task.comments_count" class="flex items-center gap-1 text-gray-500" :title="`${task.comments_count} Comments`">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" /></svg>
                    {{ task.comments_count }}
                </span>
            </div>

            <!-- Git Icon if linked -->
            <span v-if="task.git_pr_url || task.git_branch_url" class="text-gray-400" title="Git Linked">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
            </span>
        </div>

        <!-- Task Progress Bar (Minified) -->
        <div v-if="task.checklists_count" class="mt-3">
             <div class="h-1 w-full bg-gray-100 rounded-full overflow-hidden">
                 <div 
                    class="h-full bg-emerald-500 transition-all duration-500" 
                    :style="{ width: Math.round(((task.completed_checklists_count || 0) / task.checklists_count) * 100) + '%' }"
                 ></div>
             </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    task: Object,
    isDragging: Boolean,
    stageColor: String,
    isDragging: Boolean,
    stageColor: String,
    priorities: Array,
    isSelected: Boolean
});

defineEmits(['dragstart', 'edit', 'toggle-select']);

const getInitials = (name) => {
    if (!name) return '';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const priorityStyle = computed(() => {
    const p = props.priorities?.find(item => item.name === props.task.priority);
    if (!p) return { backgroundColor: '#f3f4f6', color: '#6b7280' };
    return { backgroundColor: p.color + '20', color: p.color };
});
</script>
