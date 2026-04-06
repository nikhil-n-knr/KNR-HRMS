<template>
    <div class="space-y-8 text-left">
        <!-- Header -->
        <div class="flex justify-between items-end pb-6 border-b border-gray-100">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Customer Care</h2>
                <div class="flex items-center mt-2">
                    <span class="w-8 h-1 bg-amber-500 rounded-full mr-3"></span>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-widest">Support Volume & SLA Performance</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="stat in ticket_stats" :key="stat.status" 
                 class="bg-white p-6 rounded-[30px] border border-gray-100 shadow-sm flex items-center gap-6">
                <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-400 group-hover:text-amber-500 transition-colors border border-gray-100">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div>
                    <div class="text-sm font-black text-gray-400 uppercase tracking-widest">{{ stat.status }}</div>
                    <div class="text-2xl font-black text-gray-900 tracking-tight">{{ stat.count }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Tickets -->
            <div class="lg:col-span-2">
                <div class="bg-white border border-gray-100 rounded-[40px] shadow-sm overflow-hidden h-full">
                    <div class="p-8 border-b border-gray-50 bg-gray-50/20">
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Active Tickets</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-8 py-4 text-sm font-black text-gray-400 uppercase tracking-widest">Ticket</th>
                                    <th class="px-8 py-4 text-sm font-black text-gray-400 uppercase tracking-widest">Contact</th>
                                    <th class="px-8 py-4 text-sm font-black text-gray-400 uppercase tracking-widest">Status</th>
                                    <th class="px-8 py-4 text-sm font-black text-gray-400 uppercase tracking-widest">Agent</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="ticket in recent_tickets" :key="ticket.id" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-8 py-4">
                                        <div class="text-sm font-black text-gray-800 tracking-tight">{{ ticket.subject }}</div>
                                        <div class="text-sm font-bold text-amber-600 uppercase tracking-widest mt-0.5">#{{ ticket.id.toString().padStart(4, '0') }}</div>
                                    </td>
                                    <td class="px-8 py-4">
                                        <div class="text-xs font-bold text-gray-600">{{ ticket.contact?.name || 'Guest' }}</div>
                                    </td>
                                    <td class="px-8 py-4">
                                        <span :class="['px-2 py-1 rounded-md text-sm font-black uppercase tracking-widest border', 
                                                       ticket.status === 'open' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-gray-50 text-gray-400 border-gray-100']">
                                            {{ ticket.status }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-sm font-black text-gray-400 uppercase">
                                                {{ ticket.assignee?.name.charAt(0) || '?' }}
                                            </div>
                                            <span class="text-sm font-bold text-gray-500">{{ ticket.assignee?.name || 'Unassigned' }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="recent_tickets.length === 0" class="p-20 text-center text-gray-300">
                        <div class="text-4xl mb-4 opacity-20"><i class="fas fa-check-double"></i></div>
                        <div class="text-base font-black uppercase tracking-widest">No active tickets. All clear!</div>
                    </div>
                </div>
            </div>

            <!-- Priority & SLAs -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-gray-900 border border-gray-100 rounded-[40px] p-8 shadow-sm text-white">
                    <h3 class="text-sm font-black uppercase tracking-widest text-amber-400 mb-8">Priority Distribution</h3>
                    <div class="space-y-6">
                        <div v-for="stat in priority_stats" :key="stat.priority" class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div :class="['w-2 h-2 rounded-full', 
                                            stat.priority === 'urgent' ? 'bg-rose-500' : 
                                            stat.priority === 'high' ? 'bg-amber-500' : 'bg-blue-500']"></div>
                                <span class="text-xs font-black uppercase tracking-widest text-gray-300">{{ stat.priority }}</span>
                            </div>
                            <span class="text-lg font-black">{{ stat.count }}</span>
                        </div>
                    </div>

                    <div class="mt-12 pt-8 border-t border-white/5">
                        <div class="text-sm font-black text-gray-500 uppercase tracking-widest mb-4">AVG RESOLUTION TIME</div>
                        <div class="text-3xl font-black text-white tracking-tight">2.4 <span class="text-xs text-amber-400">HOURS</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    ticket_stats: Array,
    priority_stats: Array,
    recent_tickets: Array
});
</script>
