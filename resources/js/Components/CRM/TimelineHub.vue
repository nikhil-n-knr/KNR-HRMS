<template>
    <div class="h-full flex flex-col">
        <!-- Filters / Header -->
        <div class="flex items-center justify-between mb-6 px-4">
            <h3 class="text-sm font-black uppercase tracking-widest text-gray-400">360° Interaction Timeline</h3>
            <div class="flex gap-2">
                <button @click="fetchTimeline" class="p-2 hover:bg-gray-100 rounded-lg transition-colors text-gray-400" title="Refresh">
                    <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
                </button>
            </div>
        </div>

        <!-- Timeline Feed -->
        <div v-if="loading" class="flex-1 flex items-center justify-center">
            <div class="animate-pulse flex flex-col items-center">
                <div class="h-10 w-10 bg-gray-100 rounded-full mb-4"></div>
                <div class="h-2 w-24 bg-gray-50 rounded"></div>
            </div>
        </div>

        <div v-else-if="events.length > 0" class="flex-1 overflow-y-auto space-y-8 pl-8 pr-4 relative">
            <!-- Central Line -->
            <div class="absolute left-[19px] top-0 bottom-0 w-px bg-gradient-to-b from-gray-100 via-gray-200 to-gray-100"></div>

            <div v-for="event in events" :key="event.id" class="relative group">
                <!-- Icon Bubble -->
                <div 
                    class="absolute -left-[30px] mt-1 h-6 w-6 rounded-full border-4 border-white shadow-sm z-10 flex items-center justify-center text-[10px] text-white"
                    :class="getEventConfig(event.type).bg"
                >
                    <i :class="getEventConfig(event.type).icon"></i>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 ml-4">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-3">
                            <span :class="`text-[10px] font-black uppercase tracking-tighter px-2 py-0.5 rounded ${getEventConfig(event.type).labelClass}`">
                                {{ event.type }}
                            </span>
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                                {{ formatDate(event.timestamp) }}
                            </span>
                        </div>
                        <div v-if="event.status" class="flex items-center gap-1">
                            <span class="h-1.5 w-1.5 rounded-full" :class="statusColor(event.status)"></span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase">{{ event.status }}</span>
                        </div>
                    </div>

                    <h4 class="text-sm font-bold text-gray-900 mb-2 leading-tight">
                        {{ event.title }}
                    </h4>

                    <div class="text-xs text-gray-500 leading-relaxed font-medium line-clamp-3 mb-4" v-html="event.content"></div>

                    <!-- Meta Data Footer -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-50">
                        <div class="flex -space-x-2">
                            <div v-if="event.meta?.from" class="h-6 w-6 rounded-full bg-blue-50 border border-white flex items-center justify-center text-[8px] font-black text-blue-500 uppercase" :title="`From: ${event.meta.from}`">
                                {{ event.meta.from[0] }}
                            </div>
                            <div class="h-6 w-6 rounded-full bg-gray-50 border border-white flex items-center justify-center text-[8px] font-black text-gray-400">
                                <i class="fas fa-ellipsis-h"></i>
                            </div>
                        </div>
                        <button class="text-[10px] font-black text-blue-500 uppercase tracking-widest hover:text-blue-600 transition-colors">
                            View Details <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="flex-1 flex flex-col items-center justify-center p-12 text-center">
            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                <i class="fas fa-stream text-2xl text-gray-200"></i>
            </div>
            <h3 class="text-sm font-bold text-gray-900 mb-2 uppercase tracking-widest">Silent Horizon</h3>
            <p class="text-xs text-gray-400 font-medium leading-relaxed max-w-[200px]">
                No interactions recorded yet. Time to spark a conversation.
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { formatDistanceToNow, format } from 'date-fns';

const props = defineProps({
    type: { type: String, required: true }, // contact, lead, deal
    id: { type: Number, required: true }
});

const events = ref([]);
const loading = ref(true);

const fetchTimeline = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('crm.timeline.show'), {
            params: { type: props.type, id: props.id }
        });
        events.value = response.data.events;
    } catch (error) {
        console.error('Failed to fetch timeline:', error);
    } finally {
        loading.value = false;
    }
};

const getEventConfig = (type) => {
    const configs = {
        email: { icon: 'fas fa-envelope', bg: 'bg-blue-500 shadow-blue-200', labelClass: 'bg-blue-50 text-blue-500' },
        meeting: { icon: 'fas fa-calendar-alt', bg: 'bg-purple-500 shadow-purple-200', labelClass: 'bg-purple-50 text-purple-500' },
        call: { icon: 'fas fa-phone', bg: 'bg-green-500 shadow-green-200', labelClass: 'bg-green-50 text-green-500' },
        note: { icon: 'fas fa-sticky-note', bg: 'bg-yellow-500 shadow-yellow-200', labelClass: 'bg-yellow-50 text-yellow-500' },
        system: { icon: 'fas fa-robot', bg: 'bg-gray-800 shadow-gray-200', labelClass: 'bg-gray-100 text-gray-800' }
    };
    return configs[type] || configs.note;
};

const statusColor = (status) => {
    const colors = {
        completed: 'bg-green-500',
        scheduled: 'bg-blue-500',
        pending: 'bg-yellow-500',
        cancelled: 'bg-red-500',
        sent: 'bg-green-400',
        opened: 'bg-purple-400',
    };
    return colors[status] || 'bg-gray-300';
};

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const distance = formatDistanceToNow(d, { addSuffix: true });
    return distance;
};

onMounted(() => {
    fetchTimeline();
});
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
</style>
