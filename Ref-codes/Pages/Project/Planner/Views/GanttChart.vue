<template>
  <div class="h-full flex flex-col bg-white overflow-hidden relative" ref="container">
    <!-- Virtual Scroll Container -->
    <div 
        class="flex-1 overflow-auto relative" 
        ref="scrollContainer"
        @scroll="onScroll"
        @dragover.prevent
        @drop="onGridDrop"
    >
        <!-- Sticky Header -->
        <div 
            class="h-10 bg-gray-50 border-b border-gray-200 flex sticky top-0 z-50 select-none"
            :class="[isMobileSidebarOpen ? 'ml-[240px]' : 'ml-0 md:ml-[300px]']"
            :style="{ width: totalWidth + 'px' }"
        >
           <div 
             v-for="day in timelineDays" 
             :key="day.dateStr" 
             class="flex-shrink-0 border-r border-gray-100 flex flex-col items-center justify-center text-xs font-medium relative group/header h-full"
             :class="{
                'bg-red-100 text-red-800': day.isHoliday && day.holidayType === 'fixed',
                'bg-yellow-50 text-yellow-600': day.isHoliday && day.holidayType === 'floating',
                'bg-red-50/50 text-red-300': day.isOffDay && !day.isHoliday, 
                'bg-blue-50 ring-inset ring-2 ring-blue-500 z-10': day.isToday,
                'bg-red-200 ring-inset ring-2 ring-red-500 text-red-900': day.isOverloaded,
                'text-gray-500': !day.isHoliday && !day.isOffDay && !day.isToday && !day.isOverloaded
             }"
             :style="{ width: colWidth + 'px' }"
           >
             <div class="flex flex-col items-center leading-none">
                <span v-if="day.isToday" class="text-xs font-bold text-blue-600 uppercase mb-0.5">Today</span>
                <span v-else-if="day.isOverloaded" class="text-xs font-bold text-red-700 uppercase mb-0.5 animate-pulse">Overload</span>
                <span v-else class="text-sm uppercase tracking-wider text-gray-500 mb-0.5">{{ day.month }}</span>
                
                <span class="text-sm font-bold" :class="day.isToday ? 'text-blue-700' : 'text-gray-800'">{{ day.label }}</span>
                <span class="text-sm font-medium mt-0.5" :class="day.isToday ? 'text-blue-600' : 'text-gray-400'">{{ day.subLabel }}</span>
             </div>
             <!-- Tooltip -->
             <div v-if="day.holidayName" class="absolute top-full left-0 bg-black text-white text-sm px-2 py-1 rounded shadow-lg opacity-0 group-hover/header:opacity-100 z-50 whitespace-nowrap">
                {{ day.holidayName }}
             </div>
             <!-- Overload Tooltip -->
             <div v-if="day.overloadTooltip" class="absolute top-full left-0 bg-red-800 text-white text-sm px-2 py-1 rounded shadow-lg opacity-0 group-hover/header:opacity-100 z-50 whitespace-nowrap">
                {{ day.overloadTooltip }}
             </div>
           </div>
        </div>

        <div class="flex relative">
            <!-- Sidebar (Task List) -->
            <div 
                class="flex-shrink-0 border-r border-gray-200 bg-white sticky left-0 z-40 shadow-[4px_0_16px_rgba(0,0,0,0.02)] transition-all duration-300" 
                :class="[
                    isMobileSidebarOpen ? 'w-[240px] translate-x-0' : '-translate-x-full md:translate-x-0 w-0 md:w-[300px]'
                ]"
                :style="{ minHeight: totalContentHeight + 'px' }"
            >
                 <div class="relative overflow-hidden" :style="{ height: totalContentHeight + 'px', width: isMobileSidebarOpen ? '240px' : '300px' }">
                     <div 
                        v-for="row in visibleRows" 
                        :key="row.id" 
                        class="absolute left-0 right-0 flex items-center px-4 hover:bg-gray-50 transition-colors group h-10 border-b border-gray-200"
                        :style="{ top: row.top + 'px', height: rowHeight + 'px' }"
                     >
                        <!-- Project Folder -->
                        <template v-if="row.type === 'project'">
                            <button @click="toggleProject(row.id)" class="mr-2 text-gray-400 hover:text-indigo-600 transition-colors">
                                 <svg v-if="isExpanded(row.id)" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                 <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </button>
                            <span class="font-bold text-gray-800 text-sm truncate flex-1">{{ row.text }}</span>
                            <span class="text-sm text-gray-400 font-mono bg-gray-100 px-1.5 rounded">{{ row.duration }}d</span>
                        </template>
                        
                        <!-- Task Item -->
                        <template v-else>
                             <div class="w-6 mr-2 flex justify-center">
                                <div class="w-px h-full bg-gray-200 group-hover:bg-gray-300"></div>
                             </div>
                             <div class="w-2 h-2 rounded-full mr-2" :class="getProgressColor(row)"></div>
                             <span class="text-sm text-gray-600 truncate flex-1 cursor-pointer hover:text-indigo-600 hover:underline" @click="emit('task-click', row)">
                                 {{ row.text }}
                                 <span v-if="row.stage_name" class="ml-2 text-[10px] text-gray-400 bg-gray-100 px-1 rounded uppercase tracking-widest font-black">{{ row.stage_name }}</span>
                                 <svg v-if="row.is_locked" class="w-3 h-3 text-amber-500 inline ml-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" /></svg>
                             </span>
                             
                             <!-- Assignee Avatars -->
                             <div class="flex -space-x-1">
                                <img v-for="u in row.assignments" :key="u.id" :src="u.avatar || `https://ui-avatars.com/api/?name=${u.name}&background=random`" class="w-5 h-5 rounded-full border border-white" :title="u.name">
                             </div>
                        </template>
                    </div>
                 </div>
            </div>

            <!-- Timeline Grid -->
            <!-- Timeline Grid -->
            <!-- Using explicit SVG lines with forced width to prevent clipping -->
            <div 
                class="relative" 
                :style="{ 
                    width: totalWidth + 'px', 
                    minWidth: totalWidth + 'px',
                    height: totalContentHeight + 'px'
                }"
            >
                 <!-- Grid Background (Highlights & Lines) -->
                 <svg 
                    class="absolute inset-0 pointer-events-none"
                    :style="{ width: totalWidth + 'px', height: totalContentHeight + 'px' }"
                 >
                    
                    <!-- Today Highlight Column -->
                    <rect v-if="todayX >= 0" :x="todayX" y="0" :width="colWidth" height="100%" class="fill-blue-50/30" />

                    <!-- Highlights: Off Days (Light Red) -->
                    <rect v-for="day in offDays" :key="'off-'+day.x" :x="day.x" y="0" :width="colWidth" height="100%" fill="#fef2f2" class="opacity-100" />
                    
                    <!-- Highlights: Holidays (Red/Yellow) -->
                    <rect v-for="day in holidayZones" :key="'hol-'+day.x" :x="day.x" y="0" :width="colWidth" height="100%" 
                        :fill="day.type === 'floating' ? '#fefce8' : '#fee2e2'"
                        class="opacity-90"
                    />
                    
                    <!-- Vertical Column Lines (Explicit) -->
                    <line 
                        v-for="day in timelineDays" 
                        :key="'vline-'+day.dateStr" 
                        :x1="getX(day.dateStr) + colWidth" 
                        y1="0" 
                        :x2="getX(day.dateStr) + colWidth" 
                        y2="100%" 
                        class="stroke-gray-200" 
                        stroke-width="1"
                    />
                 </svg>

                 <!-- Dependency Lines -->
                 <svg 
                    class="absolute inset-0 pointer-events-none z-0"
                    :style="{ width: totalWidth + 'px', height: totalContentHeight + 'px' }"
                 >
                     <path 
                        v-for="link in visibleLinks" 
                        :key="link.id" 
                        :d="link.d" 
                        class="stroke-gray-300 fill-none" 
                        stroke-width="1.5" 
                        marker-end="url(#arrowhead)" 
                     />
                      <defs>
                        <marker id="arrowhead" markerWidth="6" markerHeight="6" refX="5" refY="3" orient="auto">
                          <polygon points="0 0, 6 3, 0 6" class="fill-gray-300" />
                        </marker>
                      </defs>
                 </svg>

                 <!-- Task Bars -->
                 <div class="relative w-full h-full">
                     <template v-for="row in visibleRows" :key="row.id">
                          <!-- Baseline Ghost Bar (Original Plan) -->
                          <div 
                            v-if="row.type !== 'project' && row.baseline_start_date"
                            class="absolute h-1 bg-gray-400/30 rounded-full border border-dashed border-gray-400/50 pointer-events-none"
                            :style="{ 
                                top: (row.top + rowHeight - 8) + 'px', 
                                left: getX(row.baseline_start_date) + 'px', 
                                width: getWidth(dayjs(row.baseline_due_date).diff(dayjs(row.baseline_start_date), 'day') + 1) + 'px' 
                            }"
                          ></div>

                         <div 
                           v-if="row.type !== 'project' && row.start_date"
                           class="absolute h-7 rounded shadow-sm border border-black/5 cursor-move flex items-center px-1 text-xs text-white font-medium select-none overflow-hidden hover:shadow-lg transition-all group z-10"
                           :class="[getBarColor(row), row.is_locked ? 'ring-2 ring-amber-400/50' : '']"
                           :style="{ 
                                top: (row.top + 2) + 'px', 
                                left: getX(row.start_date) + 'px', 
                                width: getWidth(row.duration) + 'px',
                                boxShadow: isExtended(row) ? '0 0 15px rgba(239, 68, 68, 0.4)' : ''
                            }"
                           draggable="true"
                           @dragstart="onBarDragStart($event, row)"
                           @click.stop="emit('task-click', row)"
                         >
                            <svg v-if="row.is_locked" class="w-3 h-3 mr-1 text-white/80 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" /></svg>
                            <div class="flex items-center gap-2 w-full transition-all duration-300" :style="{ paddingLeft: getLabelOffset(row) + 'px' }">
                                <span class="bg-black/20 px-1.5 rounded text-sm font-mono whitespace-nowrap flex items-center gap-1" title="Remaining / Total Effort">
                                    <span class="font-bold text-white">{{ getRemainingDays(row.start_date, row.duration) }}d</span>
                                    <span class="text-white/60">/</span>
                                    <span>{{ getEffortDays(row.start_date, row.duration) }}d</span>
                                </span>
                                <span class="truncate flex-1 font-semibold text-shadow-sm">{{ row.text }}</span>
                            </div>
                            
                             <!-- Drag Handles -->
                            <div class="absolute right-0 top-0 bottom-0 w-2 cursor-e-resize hover:bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            
                            <!-- Reality Bar (Actual vs Estimate) -->
                            <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-black/20" v-if="row.estimated_hours > 0">
                                <div 
                                    class="h-full transition-all duration-300"
                                    :class="getRealityColor(row)"
                                    :style="{ width: Math.min(100, (row.actual_hours / row.estimated_hours) * 100) + '%' }"
                                ></div>
                            </div>
                         </div>
                     </template>
                 </div>
            </div>
        </div>
    </div>

    <!-- Mobile Sidebar Toggle -->
    <div class="md:hidden fixed bottom-6 right-6 z-[70] flex flex-col gap-3">
        <button 
            @click="scrollToToday"
            class="bg-white text-indigo-600 p-3 rounded-full shadow-2xl border border-indigo-100 flex items-center justify-center"
            title="Jump to Today"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </button>
        <button 
            @click="isMobileSidebarOpen = !isMobileSidebarOpen"
            class="bg-indigo-600 text-white p-3 rounded-full shadow-2xl transition-all"
            :class="isMobileSidebarOpen ? 'rotate-180' : ''"
        >
            <svg v-if="isMobileSidebarOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            <svg v-else class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useToastStore } from '@/stores/toast';
import dayjs from 'dayjs';
import axios from 'axios';

const props = defineProps(['data', 'resources', 'searchQuery', 'holidays', 'workDays', 'weekOffRules']);
const emit = defineEmits(['task-update', 'task-click']);
const toast = useToastStore();

// --- Config ---
const colWidth = 60; 
const rowHeight = 40;
const buffer = 10; 

// --- State ---
const scrollContainer = ref(null);
const scrollTop = ref(0);
const isMobileSidebarOpen = ref(false); // Mobile state
const expandedFolders = ref(new Set()); 
const currentDate = dayjs();

// --- Overload Calculation ---
const dailyResourceLoad = computed(() => {
    const load = {}; // date -> { userId: hours }
    if (!props.data) return {};

    props.data.forEach(task => {
        if (task.type === 'project' || !task.assignments || !task.start_date) return;
        
        let d = dayjs(task.start_date);
        const duration = task.duration || 1;
        
        for (let i = 0; i < duration; i++) {
            const dateStr = d.format('YYYY-MM-DD');
            if (isHoliday(d) || isWeeklyOff(d)) {
                d = d.add(1, 'day');
                continue; 
            }

            task.assignments.forEach(assignee => {
                if (!load[dateStr]) load[dateStr] = {};
                // Default 8h for full day task if not specified? 
                // Using task.allocated_hours which comes from Backend (WorkAssignment)
                const hours = assignee.allocated_hours || (8 / Math.max(1, task.assignments.length)); 
                load[dateStr][assignee.id] = (load[dateStr][assignee.id] || 0) + hours;
            });
            d = d.add(1, 'day');
        }
    });
    return load;
});

const overloadedDays = computed(() => {
    const overloaded = {}; // dateStr -> [UserNames]
    for (const [date, users] of Object.entries(dailyResourceLoad.value)) {
        for (const [uid, hours] of Object.entries(users)) {
            if (hours > 8) {
                if (!overloaded[date]) overloaded[date] = [];
                // Find resource name
                const res = props.resources?.find(r => r.id == uid);
                overloaded[date].push(res?.name || 'Unknown');
            }
        }
    }
    return overloaded;
});

// Reuse existing logic
const startDate = computed(() => {
    if (!props.data || props.data.length === 0) return currentDate.subtract(5, 'day');
    const min = props.data.reduce((acc, curr) => {
        if (!curr.start_date) return acc;
        return dayjs(curr.start_date).isBefore(acc) ? dayjs(curr.start_date) : acc;
    }, currentDate);
    return min.subtract(5, 'day');
});

const daysToShow = computed(() => {
    if (!props.data || props.data.length === 0) return 60;
    const max = props.data.reduce((acc, curr) => {
        if (!curr.start_date) return acc;
        const end = dayjs(curr.start_date).add(curr.duration || 1, 'day');
        return end.isAfter(acc) ? end : acc;
    }, dayjs().add(30, 'day'));
    
    // Provide at least 2 months buffer or reach the max date
    const diff = max.diff(startDate.value, 'day');
    return Math.max(diff + 15, 60);
}); 

// --- Date Helpers ---
const isHoliday = (dateObj) => {
    if (!props.holidays) return null;
    const dateStr = dateObj.format('YYYY-MM-DD');
    return props.holidays.find(h => h.date === dateStr);
};

const isWeeklyOff = (dateObj) => {
    if (!props.workDays) return false;

    if (Array.isArray(props.weekOffRules) && props.weekOffRules.length > 0) {
        const dayName = dateObj.format('ddd');
        const weekOfMonth = Math.ceil(dateObj.date() / 7);
        const isLastOccurrence = dateObj.add(7, 'day').month() !== dateObj.month();

        const matchedNthRule = props.weekOffRules.some(rule => {
            if (!rule || !rule.weekday || !Array.isArray(rule.weeks)) return false;
            if (String(rule.weekday).toLowerCase() !== dayName.toLowerCase()) return false;

            const weeks = rule.weeks.map(w => String(w).toLowerCase());
            return weeks.includes(String(weekOfMonth)) || (isLastOccurrence && weeks.includes('last'));
        });

        if (matchedNthRule) {
            return true;
        }
    }
    
    // Handle Array of strings (e.g. ["Mon", "Tue"]) - API response format
    if (Array.isArray(props.workDays) && typeof props.workDays[0] === 'string') {
        const dayName = dateObj.format('ddd'); // "Mon", "Sun"
        // If the day is NOT in the list, it's an OFF day
        return !props.workDays.some(d => d.toLowerCase() === dayName.toLowerCase());
    }

    // Handle Object config (Legacy/Fallback)
    const dayName = dateObj.format('ddd').toLowerCase(); 
    const config = props.workDays[dayName];

    if (config === undefined) return false;
    if (config === false) return true;
    if (config === true) return false;

    if (Array.isArray(config)) {
         const dom = dateObj.date();
         const weekIdx = Math.ceil(dom / 7);
         return !config.includes(weekIdx);
    }

    return false;
};

// --- Computed ---

// Helpers for Today
const todayX = computed(() => {
    const diff = dayjs().diff(startDate.value, 'day');
    if (diff < 0 || diff >= daysToShow.value) return -1;
    return diff * colWidth;
});

const timelineDays = computed(() => {
    let days = [];
    const maxDays = daysToShow.value;
    const start = startDate.value;
    const todayStr = dayjs().format('YYYY-MM-DD');
    const overloads = overloadedDays.value;

    for (let i = 0; i < maxDays; i++) {
        const d = start.add(i, 'day');
        const dStr = d.format('YYYY-MM-DD');
        const holiday = isHoliday(d);
        const off = isWeeklyOff(d);
        const isToday = dStr === todayStr;
        const overloadUsers = overloads[dStr];

        days.push({
            dateStr: dStr,
            month: d.format('MMM'),
            label: d.format('DD'),
            subLabel: d.format('ddd'),
            isHoliday: !!holiday,
            holidayName: holiday?.name,
            holidayType: holiday?.type?.toLowerCase() || 'fixed',
            isOffDay: off,
            isToday: isToday,
            isOverloaded: !!overloadUsers,
            overloadTooltip: overloadUsers ? `Overloaded: ${overloadUsers.join(', ')}` : null
        });
    }
    return days;
});

// SVG Highlight Zones
const holidayZones = computed(() => {
    return timelineDays.value
        .filter(d => d.isHoliday)
        .map((d, i) => ({
             x: getX(d.dateStr),
             type: d.holidayType
        }));
});

const offDays = computed(() => {
    return timelineDays.value
        .filter(d => d.isOffDay && !d.isHoliday) // Don't double paint if already holiday
        .map((d, i) => ({
             x: getX(d.dateStr)
        }));
});

// Flat List generation
const flattenedRows = computed(() => {
    let flat = [];
    const search = props.searchQuery?.toLowerCase().trim();

    if (!props.data) return [];

    props.data.forEach(item => {
        if (item.type === 'project') {
            const children = props.data.filter(t => t.parent === item.id);
            const matchesSearch = !search || item.text.toLowerCase().includes(search) || children.some(c => c.text.toLowerCase().includes(search));
            
            if (matchesSearch) {
                flat.push(item);
                if (search || expandedFolders.value.has(item.id)) {
                    flat.push(...(search ? children.filter(c => c.text.toLowerCase().includes(search)) : children));
                }
            }
        }
    });
    return flat;
});

const totalContentHeight = computed(() => flattenedRows.value.length * rowHeight);
const totalWidth = computed(() => daysToShow.value * colWidth);

const visibleRows = computed(() => {
    const startIdx = Math.max(0, Math.floor(scrollTop.value / rowHeight) - buffer);
    const endIdx = Math.min(flattenedRows.value.length, Math.ceil((scrollTop.value + 800) / rowHeight) + buffer);
    
    return flattenedRows.value.slice(startIdx, endIdx).map((row, idx) => ({
        ...row,
        top: (startIdx + idx) * rowHeight
    }));
});

const visibleLinks = computed(() => {
    const links = [];
    const rowMap = new Map();
    flattenedRows.value.forEach((row, i) => rowMap.set(row.id, {  
        y: i * rowHeight + (rowHeight / 2),
        start: getX(row.start_date),
        end: getX(row.start_date) + getWidth(row.duration)
    }));

    visibleRows.value.forEach(row => {
        if (row.blocked_by_task_id && rowMap.has(row.blocked_by_task_id)) {
            const from = rowMap.get(row.blocked_by_task_id);
            const to = { 
                y: row.top + (rowHeight / 2),
                start: getX(row.start_date)
            };
            const x1 = from.end;
            const y1 = from.y;
            const x2 = to.start;
            const y2 = to.y;
            const c1x = x1 + 20;
            const c1y = y1;
            const c2x = x2 - 20;
            const c2y = y2;

            links.push({
                id: `${row.blocked_by_task_id}-${row.id}`,
                d: `M ${x1} ${y1} C ${c1x} ${c1y}, ${c2x} ${c2y}, ${x2} ${y2}`
            });
        }
    });
    return links;
});

// --- Actions ---

const scrollToToday = () => {
    if (!scrollContainer.value || !startDate.value) return;
    
    const diff = dayjs().diff(startDate.value, 'day');
    if (diff < 0) return;
    
    // Scroll to today (minus 100px buffer to center it a bit or show yesterday)
    const x = (diff * colWidth) - 100;
    
    scrollContainer.value.scrollTo({
        left: Math.max(0, x),
        behavior: 'smooth'
    });
};

const toggleProject = (id) => {
    if (expandedFolders.value.has(id)) {
        expandedFolders.value.delete(id);
    } else {
        expandedFolders.value.add(id);
    }
};

const isExpanded = (id) => expandedFolders.value.has(id);

const onScroll = (e) => {
    scrollTop.value = e.target.scrollTop;
};

const onBarDragStart = (evt, task) => {
    evt.dataTransfer.effectAllowed = 'move';
    evt.dataTransfer.setData('task_id', task.id);
    evt.dataTransfer.setData('source', 'timeline');
};

const onGridDrop = async (evt) => {
    // ... existing logic ...
    const taskId = evt.dataTransfer.getData('task_id');
    const source = evt.dataTransfer.getData('source');
    
    if (!taskId || !scrollContainer.value) return;

    const rect = scrollContainer.value.getBoundingClientRect();
    const x = evt.clientX - rect.left - 300; 
    
    const dayIndex = Math.floor(x / colWidth);
    const newDate = startDate.value.add(dayIndex, 'day');
    
    if (dayIndex < 0) return; 

    const jsonHeaders = { headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' } };

    try {
        if (source === 'backlog') {
             await axios.post(route('planner.move', { id: taskId }), {
                start_date: newDate.format('YYYY-MM-DD'),
                duration: 5 
            }, jsonHeaders);
            emit('task-update', { id: parseInt(taskId), start_date: newDate.format('YYYY-MM-DD'), duration: 5 });
            toast.success('Task scheduled');
        } else {
             const task = props.data.find(t => t.id == taskId);
             const duration = task ? task.duration : 1;
             
             await axios.post(route('planner.move', { id: taskId }), {
                start_date: newDate.format('YYYY-MM-DD'),
                duration: duration
            }, jsonHeaders);
            emit('task-update', { id: parseInt(taskId), start_date: newDate.format('YYYY-MM-DD') });
            toast.success('Task moved');
        }
    } catch (e) {
        toast.error('Move failed');
        console.error(e);
    }
};

// --- Helpers ---
const getX = (dateStr) => {
    if (!dateStr) return 0;
    const diff = dayjs(dateStr).diff(startDate.value, 'day');
    return Math.max(0, diff * colWidth);
};

const getWidth = (days) => (days || 1) * colWidth;

const getBarColor = (row) => {
    if (row.progress === 1) return 'bg-emerald-500 from-emerald-500 to-emerald-600 bg-gradient-to-br';
    if (row.status === 'Done') return 'bg-emerald-500'; 
    if (isExtended(row)) return 'bg-red-500 from-red-600 to-rose-700 bg-gradient-to-br ring-1 ring-red-400';
    if (dayjs(row.start_date).add(row.duration, 'day').isBefore(dayjs())) return 'bg-red-500 from-red-500 to-red-600 bg-gradient-to-br'; 
    return 'bg-indigo-500 from-indigo-500 to-violet-600 bg-gradient-to-br';
};

const isExtended = (row) => {
    if (!row.baseline_due_date || !row.due_date) return false;
    return dayjs(row.due_date).isAfter(dayjs(row.baseline_due_date));
};

const getRealityColor = (row) => {
    const pct = (row.actual_hours / row.estimated_hours) * 100;
    if (pct > 100) return 'bg-red-300'; // Overburn
    if (pct > 80) return 'bg-yellow-300';
    return 'bg-emerald-300';
};

const getProgressColor = (row) => row.status === 'Done' ? 'bg-emerald-500' : 'bg-gray-300';

const getLabelOffset = (row) => {
    if (!row.start_date || !row.duration) return 0;
    
    // Logic: If task spans across "Today", push text to start at "Today"
    const start = dayjs(row.start_date);
    const end = start.add(row.duration, 'day');
    const today = dayjs();

    // If today is NOT within the task range (or task hasn't started, or matches start)
    if (today.isBefore(start) || today.isAfter(end)) return 0;
    
    const taskX = getX(row.start_date);
    const splitX = todayX.value; 
    
    if (splitX < 0) return 0; // Today not visible in timeline

    return Math.max(0, splitX - taskX);
};

const getEffortDays = (startStr, duration) => {
    // ... logic for total effort ...
    return calculateBusinessDays(startStr, duration);
};

const getRemainingDays = (startStr, duration) => {
    // Calculate remaining days from TODAY (or start if in future) to end
    const start = dayjs(startStr);
    const end = start.add(duration, 'day');
    const today = dayjs();
    
    // If task ended, 0
    if (end.isBefore(today)) return 0;
    
    // If not started yet, it's full duration
    const calcStart = start.isAfter(today) ? start : today;
    
    // Diff in days
    const days = end.diff(calcStart, 'day');
    return calculateBusinessDays(calcStart.format('YYYY-MM-DD'), days);
};

const calculateBusinessDays = (startStr, daysCount) => {
    if (!startStr || !daysCount || daysCount <= 0) return 0;
    let count = 0;
    let d = dayjs(startStr);
    
    for (let k = 0; k < Math.ceil(daysCount); k++) {
        const holiday = isHoliday(d);
        const off = isWeeklyOff(d);
        const isFixedHoliday = holiday && holiday.type?.toLowerCase() === 'fixed';
        const isOff = off;
        
        if (!isFixedHoliday && !isOff) {
            count++;
        }
        d = d.add(1, 'day');
    }
    return count;
};


watch(() => props.data, (newData) => {
    if (expandedFolders.value.size === 0 && newData && newData.length > 0) {
        newData.filter(i => i.type === 'project').forEach(p => expandedFolders.value.add(p.id));
    }
    // Auto Scroll on data load
    if (newData && newData.length > 0) {
        setTimeout(scrollToToday, 500); 
    }
}, { immediate: true });

</script>
