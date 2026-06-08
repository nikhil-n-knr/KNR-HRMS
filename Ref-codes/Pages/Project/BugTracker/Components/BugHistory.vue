<template>
    <div class="flow-root">
        <ul role="list" class="-mb-8">
            <li v-for="(activity, activityIdx) in activities" :key="activity.id">
                <div class="relative pb-8">
                    <span v-if="activityIdx !== activities.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true" />
                    <div class="relative flex space-x-3">
                        <div>
                            <span :class="[
                                getTypeColor(activity.activity_type),
                                'h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white'
                            ]">
                                <component :is="getTypeIcon(activity.activity_type)" class="h-5 w-5 text-white" aria-hidden="true" />
                            </span>
                        </div>
                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                            <div>
                                <p class="text-sm text-gray-500">
                                    {{ activity.description }} 
                                    <span v-if="activity.user" class="font-medium text-gray-900">by {{ activity.user.name }}</span>
                                </p>
                            </div>
                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                <time :datetime="activity.created_at">{{ new Date(activity.created_at).toLocaleString() }}</time>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            <li v-if="activities.length === 0" class="text-center text-gray-500 py-4">
                No history available.
            </li>
        </ul>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
    CheckCircleIcon, 
    ChatBubbleLeftEllipsisIcon, 
    PencilSquareIcon, 
    PlusIcon 
} from '@heroicons/vue/20/solid';

const props = defineProps(['activities']);

const getTypeColor = (type) => {
    switch (type) {
        case 'created': return 'bg-emerald-500';
        case 'commented': return 'bg-teal-500';
        case 'p_change': return 'bg-amber-500'; // Stage/Priority change
        case 'closed': return 'bg-slate-500';
        default: return 'bg-slate-400';
    }
};

const getTypeIcon = (type) => {
    switch (type) {
        case 'created': return PlusIcon;
        case 'commented': return ChatBubbleLeftEllipsisIcon;
        case 'p_change': return PencilSquareIcon;
        case 'closed': return CheckCircleIcon;
        default: return PencilSquareIcon;
    }
};
</script>
