<template>
    <div class="h-full overflow-y-auto p-4 custom-scrollbar">
        <div class="relative pl-6 space-y-6 before:absolute before:left-[19px] before:top-2 before:bottom-0 before:w-px before:bg-gray-200">
            <div v-if="activities.length === 0" class="text-sm text-gray-400 pl-2">No activity recorded.</div>
            
            <div v-for="activity in activities" :key="activity.id" class="relative group">
                <!-- Dot -->
                <div class="absolute -left-[29px] top-1 bg-white border-2 border-indigo-100 w-5 h-5 rounded-full flex items-center justify-center z-10 group-hover:border-indigo-400 transition-colors">
                    <div class="w-2 h-2 rounded-full bg-indigo-400"></div>
                </div>

                <div class="flex flex-col">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-bold text-xs text-gray-800">{{ activity.user?.name || 'System' }}</span>
                        <span class="text-[10px] text-gray-400">{{ formatDate(activity.created_at) }}</span>
                    </div>
                    
                    <p class="text-sm text-gray-600 leading-relaxed">
                        <span v-if="activity.type === 'comment'">Commented</span>
                        <span v-else-if="activity.type === 'move' || activity.type === 'moved'">
                            Moved from <span class="font-bold text-gray-800">{{ activity.details?.from_stage_name || activity.details?.from }}</span> to <span class="font-bold text-gray-800">{{ activity.details?.to_stage_name || activity.details?.to }}</span>
                        </span>
                        <span v-else-if="activity.type === 'create'">Created this task</span>
                        <span v-else-if="activity.type === 'update'">Updated: <span class="font-medium text-gray-700">{{ formatChanges(activity.details) }}</span></span>
                        <span v-else-if="activity.type === 'pr_linked'">Linked PR: <span class="font-bold text-indigo-600">{{ activity.details?.title }}</span></span>
                        <span v-else-if="activity.type === 'pr_status_updated'">{{ activity.details?.new_status.toUpperCase() }} PR: <span class="font-bold text-gray-800">{{ activity.details?.pr_title }}</span></span>
                        <span v-else-if="activity.type === 'checklist_add'">Added checklist: <span class="italic">"{{ activity.details?.content }}"</span></span>
                         <span v-else-if="activity.type === 'checklist_toggle'">Toggled checklist item</span>
                        <span v-else>{{ (activity.type || '').replace('_', ' ').toUpperCase() }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const props = defineProps({
    activities: Array
});

const formatDate = (date) => {
    return dayjs(date).fromNow();
};

const formatChanges = (details) => {
    if (!details) return 'details';
    if (details.fields) return details.fields.join(', ');
    if (details.action) return details.action;
    
    // Fallback simple parsing for common fields if details is just key-value
    const keys = Object.keys(details).filter(k => !['project_id', 'task_id', 'user_id', 'id'].includes(k));
    if (keys.length > 0) return keys.join(', ');
    
    return 'details updated';
};
</script>
