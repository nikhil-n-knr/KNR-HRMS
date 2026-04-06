<template>
    <div class="space-y-6">
        <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-50 flex items-center justify-between bg-gray-50/30">
                <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest">All Scheduled Sessions</h3>
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" placeholder="Search meetings..." class="pl-10 pr-4 py-2 bg-white border border-gray-100 rounded-xl text-xs font-bold focus:ring-2 focus:ring-indigo-500/20 outline-none w-64 shadow-sm" />
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-50">
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Meeting Title</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Date & Time</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Participants</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Platform</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="meeting in sampleMeetings" :key="meeting.id" class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-8 py-6">
                                <p class="text-sm font-black text-gray-900 group-hover:text-indigo-600 transition-colors">{{ meeting.title }}</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ meeting.type }}</p>
                            </td>
                            <td class="px-8 py-6 text-sm font-bold text-gray-600">
                                {{ meeting.date }}<br/>
                                <span class="text-[10px] font-black text-gray-300 uppercase tracking-widest">{{ meeting.time }}</span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex -space-x-2">
                                    <div v-for="i in 3" :key="i" class="w-8 h-8 rounded-full border-2 border-white bg-gray-100 flex items-center justify-center text-[8px] font-black text-gray-500">
                                        {{ String.fromCharCode(64 + i) }}
                                    </div>
                                    <div class="w-8 h-8 rounded-full border-2 border-white bg-gray-50 flex items-center justify-center text-[8px] font-black text-gray-400">
                                        +{{ meeting.attendees_count }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span :class="['px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-widest border', meeting.platform === 'Zoom' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-indigo-50 text-indigo-600 border-indigo-100']">
                                    {{ meeting.platform }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <button class="w-8 h-8 rounded-lg bg-gray-50 text-gray-400 hover:bg-indigo-600 hover:text-white transition-all">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    meetings: { type: Array, default: () => [] },
    contacts: { type: Array, default: () => [] }
});

const sampleMeetings = [
    { id: 1, title: 'Strategic Account Review', type: 'High Priority', date: 'Mar 24, 2026', time: '10:00 AM - 11:00 AM', attendees_count: 4, platform: 'Zoom' },
    { id: 2, title: 'Product Demo: CloudSync', type: 'Sales Qualified', date: 'Mar 24, 2026', time: '02:30 PM - 03:00 PM', attendees_count: 2, platform: 'Google Meet' },
    { id: 3, title: 'Technical Onboarding', type: 'Customer Success', date: 'Mar 25, 2026', time: '11:00 AM - 12:30 PM', attendees_count: 6, platform: 'Zoom' },
    { id: 4, title: 'Partnership Exploration', type: 'Ecosystem', date: 'Mar 26, 2026', time: '04:00 PM - 04:30 PM', attendees_count: 3, platform: 'In-Person' },
];
</script>
