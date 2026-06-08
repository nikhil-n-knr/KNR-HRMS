<template>
    <div class="h-full flex flex-col bg-white overflow-hidden font-inter">
        <!-- Timeline Controls -->
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <div class="flex items-center gap-4">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-700">Chronological Intelligence</h3>
                <div class="flex items-center gap-2 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg border border-emerald-100">
                    <CalendarIcon class="w-4 h-4" />
                    <span class="text-sm font-black uppercase tracking-wider">{{ dateRangeLabel }}</span>
                </div>
            </div>
            <div class="flex gap-2">
                 <button @click="zoomLevel = 'days'" :class="[zoomLevel === 'days' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200' : 'bg-white text-slate-500 border-gray-200', 'px-3 py-1 text-sm font-black uppercase tracking-widest rounded-lg border transition-all']">Days</button>
                 <button @click="zoomLevel = 'weeks'" :class="[zoomLevel === 'weeks' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200' : 'bg-white text-slate-500 border-gray-200', 'px-3 py-1 text-sm font-black uppercase tracking-widest rounded-lg border transition-all']">Weeks</button>
            </div>
        </div>

        <!-- Timeline Grid -->
        <div class="flex-1 overflow-auto custom-scrollbar relative">
            <div class="min-w-[1200px] h-full flex flex-col">
                <!-- Header: Dates -->
                <div class="flex sticky top-0 z-20 bg-white border-b border-gray-100 shadow-sm">
                    <div class="w-64 shrink-0 border-r border-gray-100 bg-gray-50 flex items-center px-6">
                        <span class="text-sm font-black uppercase tracking-[0.2em] text-slate-400">Issue Context</span>
                    </div>
                    <div class="flex-1 flex">
                        <div v-for="date in timelineDates" :key="date.iso" 
                             class="flex-1 border-r border-gray-50/50 py-3 flex flex-col items-center justify-center min-w-[80px]">
                            <span class="text-sm font-black text-slate-400 uppercase tracking-tighter">{{ date.day }}</span>
                            <span class="text-xs font-black text-slate-900">{{ date.date }}</span>
                            <span class="text-xs font-bold text-slate-300 uppercase tracking-widest">{{ date.month }}</span>
                        </div>
                    </div>
                </div>

                <!-- Body: Rows -->
                <div class="flex-1 overflow-y-auto">
                    <div v-for="bug in sortedBugs" :key="bug.id" 
                         class="flex border-b border-gray-50 hover:bg-slate-50/50 transition-colors group relative"
                         @click="$emit('open', bug)">
                        
                        <!-- Fixed Sidebar Info -->
                        <div class="w-64 shrink-0 border-r border-gray-100 p-4 sticky left-0 z-10 bg-white group-hover:bg-slate-50 transition-colors shadow-[4px_0_10px_-4px_rgba(0,0,0,0.05)]">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-sm font-black font-mono text-slate-300">#{{ bug.id }}</span>
                                <span :class="[getSeverityClass(bug.severity), 'h-1.5 w-1.5 rounded-full ring-2 ring-white shadow-sm']"></span>
                            </div>
                            <h4 class="text-xs font-bold text-slate-800 truncate leading-tight transition-colors group-hover:text-emerald-700">{{ bug.subject }}</h4>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-xs font-black uppercase tracking-widest text-slate-400">{{ bug.project.name }}</span>
                                <span v-if="bug.stage" class="text-xs font-black uppercase tracking-widest text-emerald-500">{{ bug.stage.name }}</span>
                            </div>
                        </div>

                        <!-- Timeline Bar Area -->
                        <div class="flex-1 relative h-16 bg-white group-hover:bg-slate-50/30 transition-colors">
                            <!-- Background Vertical Lines -->
                            <div class="absolute inset-0 flex">
                                <div v-for="n in timelineDates.length" :key="n" class="flex-1 border-r border-slate-100/30"></div>
                            </div>
                            
                            <!-- Legend/Bar -->
                            <div class="absolute top-1/2 -translate-y-1/2 h-8 rounded-xl shadow-lg shadow-slate-200/50 group/bar transition-all hover:scale-[1.02] cursor-pointer"
                                 :style="getBarStyle(bug)"
                                 :class="[getSeverityBg(bug.severity), 'hover:z-30 border border-white/20']">
                                
                                <div class="px-4 h-full flex items-center justify-between overflow-hidden">
                                     <span class="text-sm font-black text-white uppercase tracking-widest opacity-0 group-hover/bar:opacity-100 transition-opacity truncate">{{ bug.severity }} PRIORITY</span>
                                     <div class="flex items-center gap-2">
                                         <span class="text-xs font-black text-white/50">{{ getBugDuration(bug) }}</span>
                                         <div class="h-5 w-5 rounded-full border-2 border-white/20 bg-white/10 flex items-center justify-center">
                                              <img v-if="bug.assignee" :src="bug.assignee.avatar" class="h-3 w-3 rounded-full">
                                              <UserIcon v-else class="w-2.5 h-2.5 text-white/40" />
                                         </div>
                                     </div>
                                </div>

                                <!-- Tooltip -->
                                <div class="absolute -top-12 left-1/2 -translate-x-1/2 bg-slate-900 text-white p-2 rounded-lg opacity-0 group-hover/bar:opacity-100 transition-all pointer-events-none z-50 shadow-2xl scale-50 group-hover/bar:scale-100 origin-bottom">
                                    <div class="text-xs font-black uppercase tracking-widest text-slate-400 mb-1">Timeline Analytics</div>
                                    <div class="text-sm font-bold whitespace-nowrap">Opened: {{ formatDate(bug.created_at) }}</div>
                                    <div class="text-sm font-bold whitespace-nowrap">Status: {{ bug.stage?.name }}</div>
                                    <div class="absolute bottom-[-4px] left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
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
import { ref, computed } from 'vue';
import { CalendarIcon, UserIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    bugs: Array
});

const emit = defineEmits(['open']);

const zoomLevel = ref('days'); // days, weeks

const timelineDates = computed(() => {
    const dates = [];
    const now = new Date();
    const count = 15; // Number of days/units to show
    
    for (let i = count - 1; i >= 0; i--) {
        const d = new Date();
        d.setDate(now.getDate() - i);
        
        dates.push({
            iso: d.toISOString().split('T')[0],
            day: d.toLocaleDateString(undefined, { weekday: 'short' }).toUpperCase(),
            date: d.getDate(),
            month: d.toLocaleDateString(undefined, { month: 'short' }).toUpperCase(),
            timestamp: d.getTime()
        });
    }
    return dates;
});

const dateRangeLabel = computed(() => {
    if (!timelineDates.value.length) return '';
    const start = timelineDates.value[0];
    const end = timelineDates.value[timelineDates.value.length - 1];
    return `${start.month} ${start.date} - ${end.month} ${end.date}`;
});

const sortedBugs = computed(() => {
    return [...props.bugs].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString(undefined, { 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getBugDuration = (bug) => {
    const start = new Date(bug.created_at);
    const end = bug.stage?.is_final ? new Date(bug.updated_at) : new Date();
    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays <= 1 ? 'Live' : `${diffDays}d`;
};

const getBarStyle = (bug) => {
    const start = new Date(bug.created_at).getTime();
    const end = bug.stage?.is_final ? new Date(bug.updated_at).getTime() : new Date().getTime();
    
    const timelineStart = timelineDates.value[0].timestamp;
    const timelineEnd = timelineDates.value[timelineDates.value.length - 1].timestamp + (1000 * 60 * 60 * 24);
    const totalDuration = timelineEnd - timelineStart;
    
    let left = ((start - timelineStart) / totalDuration) * 100;
    let width = ((end - start) / totalDuration) * 100;
    
    // Clamp values
    if (left < 0) {
        width += left;
        left = 0;
    }
    if (left + width > 100) {
        width = 100 - left;
    }
    if (width < 2) width = 2; // Minimum visible bar

    return {
        left: `${left}%`,
        width: `${width}%`
    };
};

const getSeverityClass = (sev) => {
    const map = {
        critical: 'bg-rose-500',
        high: 'bg-orange-500',
        medium: 'bg-teal-500',
        low: 'bg-slate-400'
    };
    return map[sev] || 'bg-gray-400';
};

const getSeverityBg = (sev) => {
    const map = {
        critical: 'bg-gradient-to-r from-rose-500 to-rose-600',
        high: 'bg-gradient-to-r from-orange-500 to-orange-600',
        medium: 'bg-gradient-to-r from-teal-500 to-teal-600',
        low: 'bg-gradient-to-r from-slate-400 to-slate-500'
    };
    return map[sev] || 'bg-gray-500';
};

</script>

<style scoped>
.font-inter { font-family: 'Inter', sans-serif; }
.custom-scrollbar::-webkit-scrollbar {
    height: 8px;
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f8fafc;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}

@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slide-in-from-bottom-2 {
    from { opacity: 0; transform: translateY(0.5rem); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-in {
    animation-duration: 0.3s;
    animation-fill-mode: both;
}

.fade-in { animation-name: fade-in; }
.slide-in-from-bottom-2 { animation-name: slide-in-from-bottom-2; }
</style>
