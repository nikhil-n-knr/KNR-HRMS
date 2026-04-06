<template>
    <div class="h-full overflow-y-auto custom-scrollbar p-4 md:p-6 bg-slate-50/50">
        <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-indigo-500/5 border border-white overflow-hidden overflow-x-auto no-scrollbar scroll-smooth">
            <table class="min-w-full divide-y divide-gray-100 table-fixed md:table-auto">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Task / Project</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Assigned To</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Timeline</th>
                         <th class="px-6 py-3 text-left text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Stage / Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr 
                        v-for="item in tasks" 
                        :key="item.id" 
                        class="hover:bg-indigo-50/30 transition-colors group"
                    >
                        <!-- Task Info -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <span class="h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                     {{ item.text.charAt(0) }}
                                </span>
                                <div>
                                    <div class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ item.text }}</div>
                                    <div class="text-xs text-gray-500">{{ item.project_name || 'No Project' }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Assignees -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex -space-x-2 overflow-hidden" v-if="item.assignments && item.assignments.length > 0">
                                <img 
                                    v-for="assignee in item.assignments" 
                                    :key="assignee.id"
                                    :src="assignee.avatar || `https://ui-avatars.com/api/?name=${assignee.name}&background=random`" 
                                    class="inline-block h-8 w-8 rounded-full ring-2 ring-white"
                                    :title="assignee.name"
                                />
                            </div>
                            <span v-else class="text-xs text-gray-400 italic">Unassigned</span>
                        </td>

                        <!-- Timeline -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-sm text-gray-700 font-medium">
                                    {{ item.start_date ? formatDate(item.start_date) : 'TBD' }} 
                                    <span v-if="item.duration" class="text-gray-400 mx-1">→</span>
                                    {{ item.duration ? addDays(item.start_date, item.duration) : '' }}
                                </span>
                                <span class="text-xs text-gray-400" v-if="item.duration">{{ Math.round(item.duration) }} days</span>
                            </div>
                        </td>

                         <!-- Status -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span 
                                class="px-2 py-1 inline-flex text-xs leading-5 font-black uppercase tracking-widest rounded-lg shadow-sm border"
                                :class="getStatusClass(item)"
                            >
                                {{ item.stage_name || item.status || 'Backlog' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    data: { type: Array, default: () => [] }
});

const tasks = computed(() => {
    return props.data.filter(i => i.type !== 'project'); // Filter out project headers
});

const getStatusClass = (task) => {
    const status = (task.stage_name || task.status || '').toLowerCase();
    
    if (status.includes('done') || status.includes('completed') || status.includes('finish')) {
        return 'bg-emerald-50 text-emerald-700 border-emerald-100';
    }
    if (status.includes('prog') || status.includes('active') || status.includes('dev')) {
        return 'bg-indigo-50 text-indigo-700 border-indigo-100';
    }
    if (status.includes('blocked') || status.includes('stuck') || status.includes('hold')) {
        return 'bg-red-50 text-red-700 border-red-100';
    }
    if (status.includes('review') || status.includes('test') || status.includes('qa')) {
        return 'bg-amber-50 text-amber-700 border-amber-100';
    }
    
    return 'bg-gray-50 text-gray-600 border-gray-100';
};

const formatDate = (dateStr) => {
    if(!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
};

const addDays = (dateStr, days) => {
    if(!dateStr) return '';
    const d = new Date(dateStr);
    d.setDate(d.getDate() + parseInt(days));
    return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
};
</script>
