<template>
    <Head title="Workload Calendar" />
    <MainLayout>
        <div class="h-[calc(100vh-4rem)] flex flex-col bg-gray-50/50">
            <!-- Header & Controls -->
            <div class="px-6 py-4 bg-white border-b border-gray-200 flex justify-between items-center shadow-sm z-10">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        {{ currentMonthName }} {{ currentYear }}
                    </h1>
                    <div class="flex bg-gray-100 rounded-lg p-1">
                        <button @click="changeView('month')" :class="{'bg-white shadow-sm text-indigo-600': view === 'month', 'text-gray-500 hover:text-gray-700': view !== 'month'}" class="px-3 py-1 text-sm font-medium rounded-md transition-all">Month</button>
                        <button @click="changeView('week')" :class="{'bg-white shadow-sm text-indigo-600': view === 'week', 'text-gray-500 hover:text-gray-700': view !== 'week'}" class="px-3 py-1 text-sm font-medium rounded-md transition-all">Week</button>
                        <button @click="changeView('agenda')" :class="{'bg-white shadow-sm text-indigo-600': view === 'agenda', 'text-gray-500 hover:text-gray-700': view !== 'agenda'}" class="px-3 py-1 text-sm font-medium rounded-md transition-all">Agenda</button>
                    </div>
                </div>
                
                <div class="flex items-center space-x-2">
                    <button @click="navigate('prev')" class="p-2 hover:bg-gray-100 rounded-full text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    <button @click="navigate('today')" class="px-3 py-1 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded hover:bg-gray-50 transition">Today</button>
                    <button @click="navigate('next')" class="p-2 hover:bg-gray-100 rounded-full text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>

            <div class="flex flex-1 overflow-hidden">
                <!-- Sidebar (Filters / Quick Stats) - Desktop Only -->
                <div class="hidden lg:flex w-64 flex-col bg-white border-r border-gray-200 p-4 space-y-6">
                     <!-- Mini Calendar (Static for now or functional) -->
                     
                     <!-- Legend -->
                     <div>
                         <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Event Types</h3>
                         <div class="space-y-2">
                             <div class="flex items-center text-sm text-gray-700">
                                 <span class="w-3 h-3 rounded-full bg-indigo-500 mr-2"></span> Interviews
                             </div>
                             <div class="flex items-center text-sm text-gray-700">
                                 <span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span> Tasks (Medium)
                             </div>
                             <div class="flex items-center text-sm text-gray-700">
                                 <span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span> Tasks (High)
                             </div>
                             <div class="flex items-center text-sm text-gray-700">
                                 <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span> Completed/Approved
                             </div>
                             <div class="flex items-center text-sm text-gray-700">
                                 <span class="w-3 h-3 rounded-full bg-orange-400 mr-2"></span> Pending Leave
                             </div>
                         </div>
                     </div>

                     <!-- Upcoming (Next 24h) -->
                     <div class="flex-1 overflow-y-auto">
                         <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Up Next</h3>
                         <div v-if="upcomingEvents.length === 0" class="text-sm text-gray-400 italic">No upcoming events.</div>
                         <div v-else class="space-y-3">
                             <div v-for="event in upcomingEvents" :key="event.id" class="flex flex-col p-2 bg-gray-50 rounded border border-gray-100 hover:shadow-sm transition cursor-pointer" @click="openEvent(event)">
                                 <span class="text-xs font-bold" :class="'text-'+event.color+'-600'">{{ event.start_time }}</span>
                                 <span class="text-sm font-medium text-gray-800 truncate" :title="event.title">{{ event.title }}</span>
                             </div>
                         </div>
                     </div>
                </div>

                <!-- Main Calendar View -->
                <div class="flex-1 overflow-y-auto bg-white relative">
                    <div v-if="loading" class="absolute inset-0 bg-white/50 backdrop-blur-sm flex items-center justify-center z-20">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
                    </div>

                    <!-- Month View -->
                    <div v-if="view === 'month'" class="h-full flex flex-col">
                        <!-- Days Header -->
                        <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50">
                            <div v-for="day in weekDays" :key="day" class="py-2 text-center text-xs font-semibold text-gray-500 uppercase">
                                {{ day }}
                            </div>
                        </div>
                        <!-- Grid -->
                        <div class="flex-1 grid grid-cols-7 auto-rows-fr">
                            <div v-for="(day, index) in calendarDays" :key="index"
                                 class="min-h-[100px] border-b border-r border-gray-100 p-1 flex flex-col relative group transition-colors hover:bg-gray-50/50"
                                 :class="{'bg-gray-50 text-gray-400': !day.isCurrentMonth, 'bg-white': day.isCurrentMonth, 'bg-blue-50/30': day.isToday}"
                                 @click="handleDateClick(day.date)">
                                
                                <span class="text-xs font-medium p-1 w-6 h-6 flex items-center justify-center rounded-full" 
                                      :class="{'bg-indigo-600 text-white shadow-sm': day.isToday}">
                                    {{ day.day }}
                                </span>
                                
                                <div class="flex-1 flex flex-col gap-1 overflow-y-hidden mt-1 px-1">
                                    <template v-for="event in getEventsForDate(day.date)" :key="event.id">
                                        <button @click.stop="openEvent(event)" 
                                                class="text-left px-1.5 py-0.5 rounded text-sm font-medium truncate w-full shadow-sm transition hover:scale-[1.02]"
                                                :class="eventColorClass(event)">
                                            <span v-if="event.allDay" class="font-bold mr-1"></span>
                                            <span v-else class="mr-1 opacity-75">{{ event.start_time_short }}</span>
                                            {{ event.title }}
                                        </button>
                                    </template>
                                    <div v-if="hasMoreEvents(day.date)" class="text-sm text-gray-400 pl-1 cursor-pointer hover:text-indigo-600">
                                        + {{ getMoreCount(day.date) }} more
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Week View (Simplified Stacked) -->
                    <div v-if="view === 'week'" class="p-6">
                        <div class="space-y-6">
                            <div v-for="day in weekDaysFull" :key="day.dateStr" class="border border-gray-200 rounded-xl overflow-hidden shadow-sm bg-white">
                                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex justify-between items-center" :class="{'bg-blue-50 border-blue-100': day.isToday}">
                                    <h3 class="font-bold text-gray-900" :class="{'text-blue-700': day.isToday}">{{ day.formatted }}</h3>
                                    <span v-if="day.isToday" class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">Today</span>
                                </div>
                                <div class="p-4 min-h-[50px]">
                                    <div v-if="day.events.length === 0" class="text-sm text-gray-400 italic">No events scheduled.</div>
                                    <div v-else class="space-y-2">
                                         <div v-for="event in day.events" :key="event.id" 
                                              @click="openEvent(event)"
                                              class="flex items-center p-3 rounded-lg border hover:shadow-md transition cursor-pointer"
                                              :class="eventColorClass(event, 'bg-white')">
                                              <div class="w-16 text-xs text-gray-500 font-medium">
                                                  {{ event.allDay ? 'All Day' : event.timeRange }}
                                              </div>
                                              <div class="block w-px h-8 bg-gray-200 mx-3"></div>
                                              <div>
                                                  <h4 class="text-sm font-semibold">{{ event.title }}</h4>
                                                  <p class="text-xs opacity-75 capitalize">{{ event.type }}</p>
                                              </div>
                                         </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Agenda View (List) -->
                     <div v-if="view === 'agenda'" class="p-4 max-w-3xl mx-auto">
                        <div class="space-y-4">
                            <template v-for="(group, date) in groupedEvents" :key="date">
                                <div class="relative">
                                    <div class="sticky top-0 bg-white/95 backdrop-blur z-10 py-2 border-b border-gray-100 mb-2">
                                        <h3 class="text-sm font-bold text-gray-900">{{ formatDate(date) }}</h3>
                                    </div>
                                    <div class="pl-4 border-l-2 border-gray-200 space-y-3">
                                        <div v-for="event in group" :key="event.id" 
                                             class="flex flex-col p-3 rounded-md bg-white border border-gray-200 shadow-sm hover:border-indigo-300 transition cursor-pointer"
                                             @click="openEvent(event)">
                                            <div class="flex justify-between">
                                                <span class="text-sm font-bold text-gray-800">{{ event.title }}</span>
                                                <span class="text-xs px-2 py-0.5 rounded-full font-medium capitalize" :class="badgeClass(event.type)">{{ event.type }}</span>
                                            </div>
                                            <div class="mt-1 flex items-center text-xs text-gray-500">
                                                <span class="mr-2 font-medium" :class="'text-'+event.color+'-600'">{{ event.allDay ? 'All Day' : event.timeRange }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                     </div>

                </div>
            </div>
        </div>

        <!-- Event Detail Modal -->
        <Modal :show="!!selectedEvent" @close="selectedEvent = null" :title="getModalTitle(selectedEvent)">
             <div v-if="selectedEvent" class="space-y-6">
                 
                 <!-- Header Info -->
                 <div>
                    <h2 class="text-xl font-bold text-gray-900 leading-tight">{{ selectedEvent.title }}</h2>
                    <div class="mt-2 flex items-center text-sm text-gray-500 space-x-4">
                        <div class="flex items-center">
                             <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                             {{ selectedEvent.allDay ? 'All Day' : selectedEvent.timeRange }}
                        </div>
                        <div class="flex items-center">
                             <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                             {{ formatDate(selectedEvent.start) }}
                        </div>
                    </div>
                 </div>

                 <div class="border-t border-gray-100"></div>

                 <!-- TYPE: INTERVIEW -->
                 <div v-if="selectedEvent.type === 'interview'" class="space-y-4">
                     <div class="grid grid-cols-2 gap-4">
                         <div>
                             <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Round</span>
                             <span class="text-sm font-medium text-gray-800">{{ selectedEvent.metadata.round || 'General' }}</span>
                         </div>
                         <div>
                             <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</span>
                             <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 capitalize">{{ selectedEvent.metadata.status }}</span>
                         </div>
                     </div>
                     <div v-if="selectedEvent.metadata.link">
                         <a :href="selectedEvent.metadata.link" target="_blank" class="flex items-center justify-center w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition shadow-sm font-medium text-sm">
                             View Application & Interview
                         </a>
                     </div>
                 </div>

                 <!-- TYPE: TASK -->
                 <div v-if="selectedEvent.type === 'task'" class="space-y-4">
                     <div class="grid grid-cols-2 gap-4">
                         <div>
                             <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Priority</span>
                             <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium capitalize" 
                                   :class="selectedEvent.metadata.priority === 'High' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'">
                                 {{ selectedEvent.metadata.priority }}
                             </span>
                         </div>
                         <div>
                             <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</span>
                             <span class="text-sm font-medium text-gray-800 capitalize">{{ selectedEvent.metadata.status }}</span>
                         </div>
                     </div>
                      <div v-if="selectedEvent.metadata.link">
                         <a :href="selectedEvent.metadata.link" target="_blank" class="flex items-center justify-center w-full px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition shadow-sm font-medium text-sm">
                             View Task Details
                         </a>
                     </div>
                 </div>

                 <!-- TYPE: LEAVE -->
                 <div v-if="selectedEvent.type === 'leave'" class="space-y-4">
                     <div>
                         <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Reason</span>
                         <p class="text-sm text-gray-600 italic bg-gray-50 p-3 rounded-md border border-gray-100 mt-1">
                             "{{ selectedEvent.metadata.reason || 'No reason provided' }}"
                         </p>
                     </div>
                     <div>
                         <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</span>
                         <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 capitalize">{{ selectedEvent.metadata.status }}</span>
                     </div>
                 </div>

             </div>
             <template #footer>
                 <button @click="selectedEvent = null" class="w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:w-auto sm:text-sm transition">
                     Close
                 </button>
             </template>
        </Modal>

    </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import dayjs from 'dayjs';
import axios from 'axios';

// Logic Variables
const view = ref('month');
const currentDate = ref(dayjs());
const events = ref([]);
const loading = ref(false);
const selectedEvent = ref(null);

// Constants
const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

// Fetch Events
const fetchEvents = async () => {
    loading.value = true;
    const start = view.value === 'month' 
        ? currentDate.value.startOf('month').subtract(7, 'day').format('YYYY-MM-DD') 
        : currentDate.value.startOf('week').format('YYYY-MM-DD');
        
    const end = view.value === 'month' 
        ? currentDate.value.endOf('month').add(7, 'day').format('YYYY-MM-DD') 
        : currentDate.value.endOf('week').format('YYYY-MM-DD');

    try {
        const res = await axios.get(route('operations.calendar.events'), { params: { start, end } });
        events.value = res.data.map(e => ({
            ...e,
            start_time: dayjs(e.start).format('HH:mm'),
            start_time_short: dayjs(e.start).format('ha'),
            timeRange: `${dayjs(e.start).format('HH:mm')} - ${dayjs(e.end).format('HH:mm')}`
        }));
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

watch([currentDate, view], () => fetchEvents());
onMounted(() => fetchEvents());

// Computed Calendar Logic
const currentMonthName = computed(() => currentDate.value.format('MMMM'));
const currentYear = computed(() => currentDate.value.format('YYYY'));

const calendarDays = computed(() => {
    const startOfMonth = currentDate.value.startOf('month');
    const endOfMonth = currentDate.value.endOf('month');
    const startDay = startOfMonth.startOf('week');
    const endDay = endOfMonth.endOf('week');

    let days = [];
    let day = startDay;

    while (day.isBefore(endDay)) {
        days.push({
            date: day.format('YYYY-MM-DD'),
            day: day.format('D'),
            isCurrentMonth: day.isSame(startOfMonth, 'month'),
            isToday: day.isSame(dayjs(), 'day'),
            obj: day
        });
        day = day.add(1, 'day');
    }
    return days;
});

const weekDaysFull = computed(() => {
    // Generate 7 days starting from current week start
    const startOfWeek = currentDate.value.startOf('week');
    let days = [];
    for (let i = 0; i < 7; i++) {
        const d = startOfWeek.add(i, 'day');
        const dateStr = d.format('YYYY-MM-DD');
        days.push({
            dateStr,
            formatted: d.format('dddd, MMM D'),
            isToday: d.isSame(dayjs(), 'day'),
            events: getEventsForDate(dateStr)
        });
    }
    return days;
});

const groupedEvents = computed(() => {
    // Group by Date for Agenda View
    const groups = {};
    events.value.forEach(e => {
        const date = dayjs(e.start).format('YYYY-MM-DD');
        if (!groups[date]) groups[date] = [];
        groups[date].push(e);
    });
    // Sort keys
    return Object.keys(groups).sort().reduce((acc, key) => {
        acc[key] = groups[key];
        return acc;
    }, {});
});

const upcomingEvents = computed(() => {
    const now = dayjs();
    const tomorrow = now.add(24, 'hour');
    return events.value
        .filter(e => {
            const start = dayjs(e.start);
            return start.isAfter(now) && start.isBefore(tomorrow);
        })
        .sort((a,b) => dayjs(a.start).diff(dayjs(b.start)))
        .slice(0, 5);
});

// Logic Helpers
const navigate = (dir) => {
    if (dir === 'prev') {
        currentDate.value = currentDate.value.subtract(1, view.value === 'week' ? 'week' : 'month');
    } else if (dir === 'next') {
        currentDate.value = currentDate.value.add(1, view.value === 'week' ? 'week' : 'month');
    } else {
        currentDate.value = dayjs();
    }
};

const changeView = (v) => {
    view.value = v;
};

const getEventsForDate = (dateStr) => {
    return events.value.filter(e => {
        // Simple check: start date matches. 
        // For multi-day events, logic needs expansion
        return dayjs(e.start).format('YYYY-MM-DD') === dateStr;
    }).sort((a, b) => dayjs(a.start).diff(dayjs(b.start)));
};

const hasMoreEvents = (dateStr) => {
    return getEventsForDate(dateStr).length > 3; // Show max 3 in grid
};

const getMoreCount = (dateStr) => {
    return getEventsForDate(dateStr).length - 3;
};

const eventColorClass = (event) => {
    const map = {
        'blue': 'bg-blue-100 text-blue-700 border-blue-200 hover:bg-blue-200',
        'indigo': 'bg-indigo-100 text-indigo-700 border-indigo-200 hover:bg-indigo-200',
        'red': 'bg-red-100 text-red-700 border-red-200 hover:bg-red-200',
        'green': 'bg-green-100 text-green-700 border-green-200 hover:bg-green-200',
        'orange': 'bg-orange-100 text-orange-800 border-orange-200 hover:bg-orange-200',
        'gray': 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200',
    };
    return map[event.color] || map['blue'];
};

const badgeClass = (type) => {
    // Similar to eventColorClass but for small badges
    return 'bg-gray-100 text-gray-600'; 
};

const openEvent = (event) => {
    selectedEvent.value = event;
};

const formatDate = (d) => dayjs(d).format('dddd, MMMM D, YYYY');

const getModalTitle = (event) => {
    if (!event || !event.type) return 'Event Details';
    // Capitalize first letter
    return event.type.charAt(0).toUpperCase() + event.type.slice(1) + ' Details';
};

const handleDateClick = (dateStr) => {
    // Switch to agenda or day view for that date
    currentDate.value = dayjs(dateStr);
    view.value = 'agenda';
};

</script>
<style scoped>
/* Custom scrollbar for agenda if needed */
</style>
