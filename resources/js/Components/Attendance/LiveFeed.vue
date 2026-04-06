<script setup>
import { computed } from 'vue';
import { ClockIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    events: {
        type: Array,
        default: () => []
    }
});

const formatTime = (time) => {
    if (!time) return '';
    return new Date(time).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getEventColor = (type) => {
    switch (type) {
        case 'Check In': return 'text-green-600 bg-green-50';
        case 'Check Out': return 'text-red-600 bg-red-50';
        case 'Break Start': return 'text-yellow-600 bg-yellow-50';
        case 'Break End': return 'text-blue-600 bg-blue-50';
        default: return 'text-gray-600 bg-gray-50';
    }
};
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 h-full flex flex-col">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 rounded-t-xl">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <ClockIcon class="w-5 h-5 text-indigo-500" />
                Live Feed
            </h3>
            <span class="text-xs font-medium text-gray-500 px-2 py-1 bg-white rounded border border-gray-200">Real-time</span>
        </div>
        
        <div class="flex-1 overflow-y-auto p-4 space-y-4 max-h-[400px]">
            <div v-if="events.length === 0" class="text-center py-8 text-gray-400 text-sm italic">
                No activity yet today.
            </div>

            <div v-for="(event, index) in events" :key="index" class="flex gap-3 animate-in fade-in slide-in-from-left-2 duration-300">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                     <div v-if="event.user?.avatar" class="w-10 h-10 rounded-full bg-cover bg-center border border-gray-100" :style="`background-image: url('${event.user.avatar}')`"></div>
                     <div v-else class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100">
                        <span class="font-bold text-sm">{{ event.user?.first_name?.charAt(0) || '?' }}</span>
                     </div>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ event.user?.first_name }} {{ event.user?.last_name }}
                        </p>
                        <span class="text-xs text-gray-400 whitespace-nowrap ml-2">
                            {{ formatTime(event.timestamp) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="getEventColor(event.type)">
                            {{ event.type }}
                        </span>
                        <span class="text-xs text-gray-400 truncate">
                            via {{ event.source || 'Web' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-3 bg-gray-50 border-t border-gray-100 text-center rounded-b-xl">
             <a href="/admin/attendance/monitoring" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors">View All Activity &rarr;</a>
        </div>
    </div>
</template>
