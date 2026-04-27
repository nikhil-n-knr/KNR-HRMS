<template>
   <div class="h-full bg-white overflow-hidden flex flex-col relative">
     <!-- Controls Bar -->
     <div class="h-auto md:h-12 border-b border-gray-200 bg-white flex flex-col md:flex-row items-stretch md:items-center justify-between px-4 py-2 gap-3 sticky top-0 z-50 shadow-sm">
         <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1 md:pb-0">
             <button @click="setView('today')" class="px-3 py-1 text-xs font-bold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all flex items-center gap-1 whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Today
             </button>
             <div class="h-4 w-px bg-gray-200 mx-1 hidden md:block"></div>
             <div class="flex items-center bg-gray-50 rounded-lg p-0.5 border border-gray-100">
                <button @click="shiftView(-7)" class="p-1.5 hover:bg-white rounded-md text-gray-500 transition-all shadow-sm" title="-7 Days">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <input type="date" v-model="viewStart" class="text-sm sm:text-xs font-bold border-none bg-transparent rounded py-1 px-2 focus:ring-0 w-24 sm:w-32">
                <button @click="shiftView(7)" class="p-1.5 hover:bg-white rounded-md text-gray-500 transition-all shadow-sm" title="+7 Days">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
             </div>
         </div>

         <div class="flex items-center justify-between md:justify-end gap-3">
             <div class="flex-1 md:flex-none flex items-center bg-gray-100/80 rounded-xl p-0.5 border border-gray-200/50">
                 <button @click="setView('today')" class="flex-1 md:flex-none px-3 py-1.5 text-sm font-black uppercase tracking-widest rounded-lg transition-all" :class="viewDuration === 7 && viewStart === dayjs().format('YYYY-MM-DD') ? 'bg-white shadow text-indigo-600' : 'text-gray-500 hover:text-gray-700'">1W</button>
                 <button @click="setView('2_weeks')" class="flex-1 md:flex-none px-3 py-1.5 text-sm font-black uppercase tracking-widest rounded-lg transition-all" :class="viewDuration === 14 && viewStart === dayjs().format('YYYY-MM-DD') ? 'bg-white shadow text-indigo-600' : 'text-gray-500 hover:text-gray-700'">2W</button>
                 <button @click="setView('next_30')" class="flex-1 md:flex-none px-3 py-1.5 text-sm font-black uppercase tracking-widest rounded-lg transition-all" :class="viewDuration === 30 ? 'bg-white shadow text-indigo-600' : 'text-gray-500 hover:text-gray-700'">30D</button>
             </div>
             
             <!-- Bug Overlay Toggle -->
             <div class="flex items-center gap-2 border-l border-gray-200 pl-3">
                <Switch v-model="showBugOverlay" :class="showBugOverlay ? 'bg-red-500' : 'bg-gray-200'" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none">
                    <span :class="showBugOverlay ? 'translate-x-5' : 'translate-x-1'" class="inline-block h-3 w-3 transform rounded-full bg-white transition-transform" />
                </Switch>
                <span class="text-sm font-black uppercase tracking-widest text-red-600/80 hidden sm:inline">Heatmap</span>
             </div>
         </div>
     </div>
     
     <!-- Main Scroll Area -->
     <div class="flex-1 overflow-auto relative" ref="matrixContainer">
         <div class="min-w-max"> <!-- Container for content width -->
             
             <!-- Header Row -->
             <div class="flex h-14 bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
                 <!-- Top-Left Corner (Search) -->
                 <div class="w-[140px] md:w-[280px] flex-shrink-0 bg-white border-r border-gray-100 p-2 sticky left-0 z-40 shadow-[4px_0_24px_-4px_rgba(0,0,0,0.05)] transition-all">
                    <div class="relative h-full">
                        <input 
                            v-model="resourceSearch" 
                            type="text" 
                            placeholder="Search resource..." 
                            class="w-full h-full pl-8 pr-3 text-xs border border-gray-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50/50 transition-all hover:bg-white"
                        >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                 </div>

                 <!-- Date Columns -->
                 <div class="flex divide-x divide-gray-100 bg-white">
                     <div 
                        v-for="day in matrixDays" 
                        :key="day.dateStr" 
                        :id="'header-' + day.dateStr"
                        class="flex-shrink-0 flex flex-col items-center justify-center text-xs w-24 transition-colors duration-300 relative group/header"
                        :class="[
                            day.isWeekend ? 'bg-red-50/50 text-red-300' : 'text-gray-700 font-medium',
                            day.isHoliday ? 'bg-amber-50 text-amber-600' : '',
                            day.isToday ? 'bg-blue-50 ring-inset ring-2 ring-blue-500 text-blue-700 z-10' : ''
                        ]"
                     >
                        <span class="text-sm opacity-70">{{ day.dateStr.split('-')[2] }}</span> <!-- Day Num -->
                        <span>{{ day.label.split(' ')[2] }} {{ day.label.split(' ')[1] }}</span> <!-- Day Name -->
                        
                        <!-- Tooltip for Holiday -->
                        <div v-if="day.isHoliday" class="absolute bottom-full left-0 bg-black text-white text-[10px] px-2 py-1 rounded shadow-lg opacity-0 group-hover/header:opacity-100 z-50 whitespace-nowrap mb-1">
                           {{ isGlobalHoliday(day.dateStr)?.name }}
                        </div>
                     </div>
                 </div>
             </div>

             <!-- Resource Rows -->
             <div class="divide-y divide-gray-100 relative">
                 <div v-for="res in filteredResources" :key="res.id" class="flex h-16 group hover:bg-gray-50/30 transition-colors">
                     
                     <!-- Sticky Name Column -->
                     <div class="w-[140px] md:w-[280px] flex-shrink-0 bg-white border-r border-gray-100 flex items-center px-2 md:px-4 sticky left-0 z-20 group-hover:bg-gray-50/30 transition-all">
                         <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs ring-2 ring-white shadow-sm overflow-hidden flex-shrink-0">
                            <img 
                                v-if="res.avatar" 
                                :src="res.avatar" 
                                class="w-full h-full object-cover"
                                @error="$event.target.style.display='none'"
                                loading="lazy"
                            >
                            <span class="text-xs font-bold">{{ res.name.substring(0, 2).toUpperCase() }}</span>
                         </div>
                         <div class="ml-3 min-w-0">
                             <p class="text-sm font-bold text-gray-800 truncate select-none">{{ res.name }}</p>
                             <div class="flex items-center gap-1.5 opacity-60 group-hover:opacity-100 transition-opacity">
                                 <span class="text-sm text-gray-500 uppercase tracking-wider truncate max-w-[80px]">{{ res.department }}</span>
                                 <span class="text-sm bg-gray-100 px-1 rounded text-gray-500">#{{ res.employee_id || res.id }}</span>
                             </div>
                         </div>
                     </div>

                     <!-- Matrix Cells -->
                     <div class="flex divide-x divide-gray-100">
                         <div 
                           v-for="day in matrixDays" 
                           :key="day.dateStr" 
                           class="w-24 relative group/cell transition-all flex items-center justify-center cursor-pointer border-b border-transparent hover:border-indigo-100"
                           :class="getCellClass(res.id, day.dateStr)"
                           @click="handleCellClick(res, day)"
                           :title="getCellTooltip(res.id, day.dateStr)"
                           :style="isExtendedCell(res.id, day.dateStr) ? { boxShadow: 'inset 0 0 12px rgba(239, 68, 68, 0.4)' } : {}"
                         >
                             <!-- Load Indicator -->
                              <span v-if="getLoad(res.id, day.dateStr) > 0 || getActual(res.id, day.dateStr) > 0" class="text-sm font-bold relative z-10 flex flex-col items-center leading-none text-center" :class="getLoadTextClass(res.id, day.dateStr)">
                                  <!-- Display: Plan | Act if Acts exist -->
                                  <span v-if="getActual(res.id, day.dateStr) > 0" class="flex items-center gap-1">
                                    <span class="opacity-70">{{ getLoad(res.id, day.dateStr) }}h</span>
                                    <span class="opacity-50">|</span>
                                    <span>{{ getActual(res.id, day.dateStr) }}h</span>
                                  </span>
                                  <span v-else>
                                    {{ getLoad(res.id, day.dateStr) }}h
                                  </span>
                                  
                                  <svg v-if="isCompletedDay(res.id, day.dateStr)" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-emerald-600 absolute -top-1 -right-1" viewBox="0 0 20 20" fill="currentColor">
                                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                  </svg>
                              </span>
                              
                              <!-- Hover Add -->
                              <div v-if="getLoad(res.id, day.dateStr) === 0 && !getAvailability(res.id, day.dateStr)" class="absolute inset-0 flex items-center justify-center opacity-0 group-hover/cell:opacity-100 transition-opacity">
                                  <span class="text-indigo-400 font-bold text-lg">+</span>
                              </div>
                          </div>
                     </div>

                 </div>
             </div>
         </div>
     </div>

     <!-- Details Modal -->
     <Modal :show="showDetailsModal" @close="showDetailsModal = false" maxWidth="sm">
        <div class="p-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                    Assignments <span class="text-xs font-normal text-gray-500">for {{ selectedDate }}</span>
                </h3>
                <button @click="openNewAssignmentFromDetails" class="text-xs bg-indigo-50 text-indigo-600 px-2 py-1 rounded hover:bg-indigo-100 font-medium">
                    + Add New
                </button>
            </div>
            
            <div class="space-y-2">
                <div v-for="task in cellTasks" :key="task.id" class="p-3 bg-gray-50 rounded-lg border border-gray-100 hover:border-indigo-300 transition-colors cursor-pointer group" @click="editAssignment(task)">
                    <div class="flex justify-between items-start">
                        <span class="text-sm font-semibold text-gray-800 group-hover:text-indigo-600">{{ task.text }}</span>
                        <!-- FIX: Use specific allocated hours -->
                        <span class="text-sm px-1.5 py-0.5 rounded bg-white border border-gray-200 text-gray-500">
                             {{ task.userAssignment ? task.userAssignment.allocated_hours : 8 }}h/day
                        </span>
                    </div>
                    <div class="flex justify-between items-center mt-2 text-xs text-gray-500">
                         <span>{{ dayjs(task.userAssignment?.start_date || task.start_date).format('MMM D') }} - {{ dayjs(task.userAssignment?.end_date || task.due_date).format('MMM D') }}</span>
                         <span class="text-indigo-500 font-medium opacity-0 group-hover:opacity-100 transition-opacity">Edit My Allocation &rarr;</span>
                    </div>
                </div>
            </div>
            <p v-if="cellTasks.length === 0" class="text-sm text-gray-500 text-center py-4">No tasks found.</p>
            <div class="mt-4 pt-3 border-t border-gray-100 flex justify-between items-center">
                <button v-if="cellTasks.length > 0" @click="editAllAssignments(cellTasks[0])" class="text-xs text-indigo-600 hover:text-indigo-800 underline">
                    Edit All Assignees
                </button>
                <SecondaryButton @click="showDetailsModal = false">Close</SecondaryButton>
            </div>
        </div>
     </Modal>

     <!-- Assignment Modal -->
     <Modal :show="showAssignmentModal" @close="showAssignmentModal = false" maxWidth="lg">
         <div class="p-6">
             <h2 class="text-xl font-bold text-gray-900 mb-6 border-b pb-2 flex justify-between items-center">
                <span>{{ isEditing ? (form.assignment_id ? 'Edit My Allocation' : 'Edit All Allocations') : 'New Assignment' }}</span>
                <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-1 rounded">
                    {{ selectedDateRange }}
                </span>
             </h2>

             <form @submit.prevent="submitAssignment">
                 <div class="space-y-5">
                     <!-- Task -->
                     <div>
                         <InputLabel value="Task" />
                         <select v-model="form.task_id" :disabled="isEditing" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm bg-gray-50 disabled:text-gray-500 cursor-not-allowed">
                             <option value="" disabled>-- Choose a Task --</option>
                             <option v-for="t in (isEditing ? [editingTask] : pendingTasks)" :key="t.id" :value="t.id">{{ t.text }}</option>
                         </select>
                         <p v-if="!isEditing && pendingTasks.length === 0" class="text-xs text-orange-500 mt-1">No pending tasks found.</p>
                     </div>

                     <!-- Resource Multi-Select -->
                     <div>
                         <InputLabel value="Assign To" class="mb-2" />
                         <!-- Locked State for Single User Edit -->
                         <div v-if="isEditing && form.assignment_id" class="text-sm font-medium text-gray-700 bg-gray-50 p-2 rounded border border-gray-200">
                             Locked to: {{ getResourceName(form.user_ids[0]) }}
                         </div>
                         <div v-else>
                             <input v-model="modalSearch" type="text" placeholder="Filter resources..." class="w-full text-xs border-gray-200 rounded mb-2 px-2 py-1 focus:ring-indigo-500">
                             <div class="border border-gray-200 rounded-lg max-h-40 overflow-y-auto p-2 space-y-1 bg-gray-50/50">
                                 <label v-for="res in modalResources" :key="res.id" class="flex items-center gap-2 p-2 rounded hover:bg-white cursor-pointer transition-colors">
                                     <input type="checkbox" :value="res.id" v-model="form.user_ids" class="rounded text-indigo-600 focus:ring-indigo-500">
                                     <span class="text-sm font-medium text-gray-700">{{ res.name }}</span>
                                     <span v-if="isUnavailable(res.id)" class="text-sm text-red-500 font-bold ml-auto uppercase tracking-wider">Busy/Off</span>
                                 </label>
                             </div>
                             <p class="text-xs text-gray-500 mt-1">{{ form.user_ids.length }} resources selected</p>
                         </div>
                     </div>

                     <!-- Dates -->
                     <div class="grid grid-cols-2 gap-4">
                         <div>
                             <InputLabel value="Start Date" />
                             <TextInput v-model="form.start_date" type="date" class="mt-1 block w-full" required />
                         </div>
                         <div>
                             <InputLabel value="End Date" />
                             <TextInput v-model="form.end_date" type="date" class="mt-1 block w-full" required />
                         </div>
                     </div>
                     <div v-if="form.errors.date_range" class="text-sm text-red-600">{{ form.errors.date_range }}</div>

                     <!-- Capacity -->
                 <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-100">
                         <div>
                             <InputLabel value="Daily Allocation" class="mb-0" />
                             <p class="text-sm text-gray-500">Hours per person/day (Max: 8)</p>
                         </div>
                         <TextInput v-model="form.hours" type="number" min="1" max="12" class="w-20 text-center" />
                     </div>

                     <div class="flex items-center gap-2 mt-2">
                       <input type="checkbox" v-model="form.force_allocation" id="force_alloc" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                       <label for="force_alloc" class="text-xs text-gray-600">Force Allocation <span class="text-gray-400">(Ignore Holidays/Leaves)</span></label>
                     </div>
                 </div>

                 <div class="mt-8 flex justify-between pt-4 border-t border-gray-100">
                      <div>
                          <button v-if="isEditing" type="button" @click="deleteAssignment" class="text-xs text-red-500 hover:text-red-700 font-medium underline">
                              {{ form.assignment_id ? 'Remove Assignment' : 'Delete Task' }}
                          </button>
                      </div>
                      <div class="flex gap-3">
                         <SecondaryButton @click="showAssignmentModal = false">Cancel</SecondaryButton>
                         <PrimaryButton :disabled="form.processing" @click="submitAssignment">
                             {{ isEditing ? 'Update' : 'Assign' }}
                         </PrimaryButton>
                      </div>
                 </div>
             </form>
         </div>
     </Modal>
  </div>
</template>

<script setup>
import { computed, ref, reactive, onMounted } from 'vue';
import dayjs from 'dayjs';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

import { Switch } from '@headlessui/vue';

const props = defineProps(['data', 'resources', 'availability', 'holidays', 'timesheets', 'workDays', 'weekOffRules']);
const emit = defineEmits(['task-update']);
const toast = useToastStore();

const showAssignmentModal = ref(false);
const showDetailsModal = ref(false);
const showBugOverlay = ref(false); // Default off
const resourceSearch = ref('');
const modalSearch = ref('');

const selectedDate = ref('');
const cellTasks = ref([]);
const isEditing = ref(false);
const editingTask = ref(null);

const viewStart = ref(dayjs().format('YYYY-MM-DD'));
const viewDuration = ref(7); // Default 7 days

const matrixDays = computed(() => {
    let days = [];
    const start = dayjs(viewStart.value);
    const count = Math.min(Math.max(parseInt(viewDuration.value) || 7, 1), 60); // Clamp 1-60
    
    for (let i = 0; i < count; i++) { 
        const d = start.add(i, 'day');
        const dateStr = d.format('YYYY-MM-DD');
        days.push({
            dateStr: dateStr,
            label: d.format('DD MMM ddd'),
            isWeekend: isNonWorkDay(dateStr),
            isToday: d.isSame(dayjs(), 'day'),
            isHoliday: !!isGlobalHoliday(dateStr)
        });
    }
    return days;
});

const setView = (mode) => {
    const today = dayjs();
    if (mode === 'today') {
        viewStart.value = today.format('YYYY-MM-DD');
        viewDuration.value = 7;
    } else if (mode === '2_weeks') {
        viewStart.value = today.format('YYYY-MM-DD');
        viewDuration.value = 14;
    } else if (mode === 'last_week') {
        viewStart.value = today.subtract(7, 'day').format('YYYY-MM-DD');
        viewDuration.value = 14; // Contextual
    } else if (mode === 'next_30') {
        viewStart.value = today.format('YYYY-MM-DD');
        viewDuration.value = 30;
    }
    // Auto-scroll to today after render
    setTimeout(() => scrollToToday(), 100);
};

const shiftView = (days) => {
    viewStart.value = dayjs(viewStart.value).add(days, 'day').format('YYYY-MM-DD');
};

// Scroll Logic
const matrixContainer = ref(null);

const scrollToToday = () => {
    const todayStr = dayjs().format('YYYY-MM-DD');
    const el = document.getElementById(`header-${todayStr}`);
    if (el && matrixContainer.value) {
        // Scroll logic: Center it or Left align?
        // User said "Start with today", so left align if possible, unless it's in the middle of "Last Week" view.
        // Let's scroll into view smoothly.
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }
};

onMounted(() => {
    setTimeout(() => scrollToToday(), 500);
});

// Search Logic
const filteredResources = computed(() => {
    if (!resourceSearch.value) return props.resources;
    const lower = resourceSearch.value.toLowerCase();
    return props.resources.filter(r => 
        r.name.toLowerCase().includes(lower) || 
        (r.employee_id && String(r.employee_id).includes(lower))
    );
});

const modalResources = computed(() => {
    if (!modalSearch.value) return props.resources;
    const lower = modalSearch.value.toLowerCase();
    return props.resources.filter(r => r.name.toLowerCase().includes(lower));
});

// Form Logic
const form = useForm({
    task_id: '',
    user_ids: [],
    start_date: '',
    end_date: '',
    hours: 8,
    assignment_id: null, // Track specific segment ID
    ignore_pending: false,
    force_allocation: false
});

// Computed Helper for filtered tasks
const pendingTasks = computed(() => {
    return props.data.filter(t => t.type !== 'project');
});

const selectedDateRange = computed(() => {
    if (form.start_date && form.end_date) {
        return `${dayjs(form.start_date).format('MMM D')} - ${dayjs(form.end_date).format('MMM D')}`;
    }
    return '';
});
// Helper for Work Days
const isNonWorkDay = (dateStr) => {
    const d = dayjs(dateStr);
    const dayName = d.format('ddd').toLowerCase();

    if (Array.isArray(props.weekOffRules) && props.weekOffRules.length > 0) {
        const weekOfMonth = Math.ceil(d.date() / 7);
        const isLastOccurrence = d.add(7, 'day').month() !== d.month();
        const matchedNthRule = props.weekOffRules.some(rule => {
            if (!rule || !rule.weekday || !Array.isArray(rule.weeks)) return false;
            if (String(rule.weekday).toLowerCase() !== d.format('ddd').toLowerCase()) return false;
            const weeks = rule.weeks.map(w => String(w).toLowerCase());
            return weeks.includes(String(weekOfMonth)) || (isLastOccurrence && weeks.includes('last'));
        });

        if (matchedNthRule) {
            return true;
        }
    }
    
    // If props.workDays is an object {mon: true, ...}
    if (props.workDays && typeof props.workDays === 'object' && !Array.isArray(props.workDays)) {
        if (props.workDays[dayName] !== undefined) {
            return props.workDays[dayName] === false;
        }
    }
    
    // Fallback if it's an array ["Mon", "Tue"...]
    if (props.workDays && Array.isArray(props.workDays) && props.workDays.length > 0) {
        return !props.workDays.includes(d.format('ddd'));
    }

    // Default Sat/Sun
    return [0, 6].includes(d.day());
};

// Helper for global holidays
const isGlobalHoliday = (dateStr) => {
    if (!props.holidays) return null;
    return props.holidays.find(h => h.date === dateStr);
};

// --- Availability Logic ---

const getAvailability = (userId, dateStr) => {
    if (!props.availability || !props.availability[userId]) return null;
    return props.availability[userId][dateStr];
};

const getLoad = (userId, dateStr) => {
    // 1. Check Global Holidays (Highest Priority)
    const holiday = isGlobalHoliday(dateStr);
    const nonWork = isNonWorkDay(dateStr);
    
    // We only ignore the holiday if NO assignment explicitly forces it.
    // However, getLoad sums ALL assignments. 
    // If ANY assignment on this day is "forced", we should probably show it?
    // Or closer: We iterate assignments. If an assignment is NOT forced, and it's a holiday, contribution is 0.
    // If it IS forced, contribution is hours.

    let load = 0;
    props.data.forEach(t => {
        if (t.type !== 'project' && t.assignments) {
             const userAssignments = t.assignments.filter(a => a.id === userId);
             userAssignments.forEach(assignment => {
                 const start = assignment.start_date || t.start_date;
                 const end = assignment.end_date || t.due_date;
                 
                 if (dateStr >= start && dateStr <= end) {
                    // Check logic:
                    // If it's a holiday/approved leave AND NOT forced -> 0
                    // But we don't have 'force' on assignment object yet in the JSON response shown.
                    // Assuming we will add it, or default to ignoring holidays.
                    
                    const isHolidayDay = !!holiday;
                    const avail = getAvailability(userId, dateStr);
                    const isLeave = (avail && avail.type === 'leave' && avail.status === 'approved');
                    const isWeekendBlock = nonWork;

                    // If Force is enabled on the assignment (we'll need to fetch this), allow it.
                    // For now, let's assume strict blocking unless we see a 'force' flag (future proofing).
                    const isForced = assignment.force_allocation || false;

                    if ((isHolidayDay || isLeave || isWeekendBlock) && !isForced) {
                        return; // Ignore
                    }
                    
                    load += parseFloat(assignment.allocated_hours || 8);
                 }
             });
        }
    });
    return load;
};

const getActual = (userId, dateStr) => {
    if (!props.timesheets || !props.timesheets[userId]) return 0;
    return props.timesheets[userId][dateStr] || 0;
};

const isCompletedDay = (userId, dateStr) => {
    let hasTask = false;
    let allCompleted = true;

    props.data.forEach(t => {
        if (t.type !== 'project' && t.assignments) {
             const userAssignments = t.assignments.filter(a => a.id === userId);
             userAssignments.forEach(assignment => {
                 const start = assignment.start_date || t.start_date;
                 const end = assignment.end_date || t.due_date;
                 if (dateStr >= start && dateStr <= end) {
                     hasTask = true;
                     const isDone = ['completed', 'done', 'closed'].includes(t.status?.toLowerCase()) || (t.stage?.type === 'done');
                     if (!isDone) allCompleted = false;
                 }
             });
        }
    });
    return hasTask && allCompleted;
};

// --- Styling Logic ---

// --- Styling Logic (Deep Contrast) ---

// --- Styling Logic (Deep Contrast) ---

const getCellClass = (userId, dateStr) => {
    // 0. Calculate Load First (This handles the force logic internally)
    const load = getLoad(userId, dateStr);

    // 1. Check Environmental Factors
    const holiday = isGlobalHoliday(dateStr);
    const nonWork = isNonWorkDay(dateStr);
    const avail = getAvailability(userId, dateStr);
    const isLeave = (avail && avail.type === 'leave' && avail.status === 'approved');
    const isBlocked = !!holiday || isLeave || nonWork;

    // 2. High Priority: Forced Work (Load > 0 on a Blocked Day)
    if (load > 0 && isBlocked) {
        // "Highlighted in dark border and different background"
        return 'bg-emerald-900 border-2 border-red-500 text-white font-bold shadow-inner';
    }

    // 3. Blocked Days (If Load is 0)
    if (isBlocked) {
        if (holiday) {
             if (holiday.name && holiday.name.toLowerCase().includes('floating')) {
                 return 'bg-yellow-100 text-yellow-800 pattern-diagonal-lines-sm cursor-not-allowed';
             }
             // "Reduce darkness" -> Slate 200/300
             return 'bg-slate-200 text-slate-600 font-medium cursor-not-allowed border border-slate-300 pattern-diagonal-lines-sm';
        }
        if (isLeave) {
             return 'bg-blue-900 text-blue-100 border-blue-800 cursor-not-allowed font-medium opacity-90';
        }
        if (nonWork) {
             return 'bg-gray-100 text-gray-400 pattern-diagonal-lines-sm cursor-not-allowed';
        }
    }

    // 4. Standard Load Heatmap (Working Days)
    // 4. Standard Load Heatmap (Working Days)
    const actual = getActual(userId, dateStr);
    
    if (actual > 0) {
        if (actual > load) {
             // HARSH (Late/Overbudget) -> RED/ORANGE
             return 'bg-red-100 border-red-300 text-red-800 font-bold border-2'; 
        } else {
             // EASY (On Track) -> GREEN/BLUE
             return 'bg-blue-100 border-blue-300 text-blue-800 font-bold border-2';
        }
    }

    if (load > 0) {
        // CHECK COMPLETED STATUS
        if (isCompletedDay(userId, dateStr)) {
            return 'bg-emerald-50 border-emerald-100 text-emerald-600 line-through decoration-emerald-400/50';
        }

        // Safe (0-8h) - Pastel Greens
        if (load <= 2) return 'bg-emerald-100 border-emerald-200 hover:bg-emerald-200';
        if (load <= 4) return 'bg-emerald-200 border-emerald-300 hover:bg-emerald-300';
        if (load <= 6) return 'bg-emerald-300 border-emerald-400 hover:bg-emerald-400 font-medium';
        if (load <= 8) return 'bg-emerald-400 border-emerald-500 hover:bg-emerald-500 font-bold text-white';
        
        // Warning (8-12h) - Bright Yellows
        if (load <= 10) return 'bg-yellow-300 border-yellow-400 hover:bg-yellow-400 font-bold text-yellow-900';
        if (load <= 12) return 'bg-yellow-400 border-yellow-500 hover:bg-yellow-500 font-extrabold text-yellow-900';

        // Overload (>12h) - Deep Reds
        return 'bg-red-500 border-red-600 hover:bg-red-600 font-black text-white animate-pulse';
    }

    // 5. Bug / Urgent Work Check
    let hasBug = false;
    let bugSeverity = 'low';
    
    props.data.forEach(t => {
        if (t.is_bug && t.assignments) {
             const userAssignments = t.assignments.filter(a => a.id === userId);
             userAssignments.forEach(assignment => {
                 const start = assignment.start_date || t.start_date;
                 const end = assignment.end_date || t.due_date;
                 if (dateStr >= start && dateStr <= end) {
                     hasBug = true;
                     bugSeverity = t.bug_severity;
                 }
             });
        }
    });

    // Only show red stripe if Toggle is ON or severity is Critical/Urgent (always show criticals?)
    // User requested "Toggle to highlight", so let's stick to that, but maybe always show criticals as a small dot?
    // For now, full stripe only if toggle ON.
    if (hasBug && showBugOverlay.value) {
        // Red Stripe Pattern for Bugs
        return 'bg-red-50 border-red-200 text-red-700 pattern-diagonal-lines-sm font-bold border-2 shadow-inner';
    }

    // 6. Base State
    const dayObj = matrixDays.value.find(d => d.dateStr === dateStr);
    if (dayObj && dayObj.isToday) return 'bg-blue-50 ring-2 ring-inset ring-blue-400';

    return 'bg-white hover:bg-gray-50';
};

const getLoadTextClass = (userId, dateStr) => {
    // If Bug Overlay is ON and has bug, text should be red
    if (showBugOverlay.value) {
         let hasBug = false;
         props.data.forEach(t => {
            if (t.is_bug && t.assignments) {
                const userAssignments = t.assignments.filter(a => a.id === userId);
                userAssignments.forEach(assignment => {
                     const start = assignment.start_date || t.start_date;
                     const end = assignment.end_date || t.due_date;
                     if (dateStr >= start && dateStr <= end) hasBug = true;
                });
            }
        });
        if (hasBug) return 'text-red-800';
    }

    const load = getLoad(userId, dateStr);
    if (load > 12) return 'text-white';
    if (load > 8) return 'text-yellow-900';
    if (load === 8) return 'text-white';
    
    // Forced Highlight text color check
    const holiday = isGlobalHoliday(dateStr);
    const nonWork = isNonWorkDay(dateStr);
    const avail = getAvailability(userId, dateStr);
    const isLeave = (avail && avail.type === 'leave' && avail.status === 'approved');
    if ((!!holiday || isLeave || nonWork) && load > 0) return 'text-white';
    
    // If showing Actuals, rely on getCellClass text colors
    if (getActual(userId, dateStr) > 0) return '';

    return 'text-emerald-900';
};

const isExtendedCell = (userId, dateStr) => {
    let extended = false;
    props.data.forEach(t => {
        if (t.type !== 'project' && t.assignments) {
             const userAssignments = t.assignments.filter(a => a.id === userId);
             userAssignments.forEach(assignment => {
                 const start = assignment.start_date || t.start_date;
                 const end = assignment.end_date || t.due_date;
                 if (dateStr >= start && dateStr <= end) {
                     if (t.baseline_due_date && t.due_date && dayjs(t.due_date).isAfter(dayjs(t.baseline_due_date))) {
                         extended = true;
                     }
                 }
             });
        }
    });
    return extended;
};

const getCellTooltip = (userId, dateStr) => {
    const load = getLoad(userId, dateStr); // Helper to check if forced
    const holiday = isGlobalHoliday(dateStr);
    const nonWork = isNonWorkDay(dateStr);
    const avail = getAvailability(userId, dateStr);
    
    // Logic: If blocked AND load is 0 -> Show Block Reason only.
    if ((holiday || nonWork || (avail && avail.type === 'leave' && avail.status === 'approved')) && load === 0) {
        if (holiday) return `Holiday: ${holiday.name}`;
        if (avail) return `${avail.name} (${avail.status || 'Fixed'})`;
        if (nonWork) return 'Non-working day';
    }

    // List Tasks (Handle Multi-segment)
    const tasks = [];
    props.data.forEach(t => {
        if (t.type !== 'project' && t.assignments) {
             const userAssignments = t.assignments.filter(a => a.id === userId);
             userAssignments.forEach(a => {
                 const start = a.start_date || t.start_date;
                 const end = a.end_date || t.due_date;
                 if (dateStr >= start && dateStr <= end) {
                     let txt = '';
                     if (t.is_bug && t.bug_id) {
                        txt = `[BUG #${t.bug_id}] `; // Explicit Bug ID
                     }
                     txt += `${t.text} (${parseFloat(a.allocated_hours || 8)}h)`;
                     if (t.stage_name) txt += ` [${t.stage_name}]`;
                     if (a.force_allocation) txt += ' [FORCED]';
                     tasks.push(txt);
                 }
             });
        }
    });
    
    if (tasks.length === 0) return '';
    return tasks.join(', ');
};



const isUnavailable = (userId) => {
    // Check if user has hard block (holiday/approved leave) in selected range
    if (!form.start_date || !form.end_date) return false;
    const start = dayjs(form.start_date);
    const end = dayjs(form.end_date);
    // Limit check to reasonable range to avoid perf issues
    const diff = end.diff(start, 'day');
    if (diff > 30) return false;

    for (let i = 0; i <= diff; i++) {
        const d = start.add(i, 'day').format('YYYY-MM-DD');
        const avail = getAvailability(userId, d);
        if (avail && (avail.type === 'holiday' || avail.status === 'approved')) return true;
    }
    return false;
};

// ... isUnavailable (omitted if unused) ...

// --- Actions ---

const getResourceName = (id) => {
    const res = props.resources.find(r => r.id === id);
    return res ? res.name : 'Unknown Resource';
};

const openNewAssignmentFromDetails = () => {
    // Current context: selectedDate, and we can infer user from the first task?
    // Actually the details modal was opened for a SPECIFIC user (res) and date.
    // We didn't store 'res' in a reactive var, only cellTasks.
    // We need to store 'selectedResource' when opening details.

    if (!selectedRes.value) return;

    showDetailsModal.value = false;
    isEditing.value = false;
    editingTask.value = null;

    form.reset();
    form.clearErrors();

    // Parse date from selectedDate string? No, simpler to store dateStr too.
    // We will update handleCellClick to store these.

    form.user_ids = [selectedRes.value.id];
    form.start_date = selectedDateStr.value; // Need to create this ref
    form.end_date = selectedDateStr.value;
    form.hours = 8;
    form.assignment_id = null; // New segment

    showAssignmentModal.value = true;
};

// --- Actions ---

const selectedRes = ref(null);
const selectedDateStr = ref('');

const handleCellClick = (res, day) => {
     const load = getLoad(res.id, day.dateStr);

     if (load > 0) {
         // Open Details Modal
         selectedRes.value = res;
         selectedDateStr.value = day.dateStr;
         selectedDate.value = dayjs(day.dateStr).format('ddd, MMM D');

         // Fix: Handle multiple segments for same task
         const flatTasks = [];

         props.data.forEach(t => {
            if (t.type === 'project') return;
            if (!t.assignments) return;

            const userAssignments = t.assignments.filter(a => a.id === res.id);
            userAssignments.forEach(a => {
                const start = a.start_date || t.start_date;
                const end = a.end_date || t.due_date;
                if (day.dateStr >= start && day.dateStr <= end) {
                    // Create entry for this specific segment
                    flatTasks.push({
                        ...t,
                        userAssignment: a, // Specific segment data
                        // Override task dates with segment dates for display
                        displayStart: start,
                        displayEnd: end
                    });
                }
            });
         });

         cellTasks.value = flatTasks;
         showDetailsModal.value = true;
     } else {
         // Open New Assignment
         selectedRes.value = res; // Track context
         selectedDateStr.value = day.dateStr;

         isEditing.value = false;
         editingTask.value = null;

         form.reset();
         form.clearErrors();

         form.user_ids = [res.id];
         form.start_date = day.dateStr;
         form.end_date = day.dateStr;
         form.hours = 8;
         form.assignment_id = null; // New segment

         showAssignmentModal.value = true;
     }
};

const editAssignment = (task) => {
    showDetailsModal.value = false;
    isEditing.value = true;
    editingTask.value = task;

    // We are editing a SPECIFIC segment (task.userAssignment)
    const specificSegment = task.userAssignment;

    // Granular Edit Mode
    if (specificSegment) {
        form.task_id = task.id;
        form.user_ids = [specificSegment.id]; // The user ID attached to assignment (User ID is mapped to id prop in backend resource map)
        form.hours = specificSegment.allocated_hours || 8;
        form.start_date = specificSegment.start_date || task.start_date;
        form.end_date = specificSegment.end_date || (task.due_date || task.start_date);
        form.assignment_id = specificSegment.assignment_id; // Need this ID! (Will fix backend mapping in next step if missing)

        // Wait, backend mapping needs to include actual WorkAssignment ID to be useful
        // Currently `PlannerApiController` maps 'id' => $userId. It needs to map 'assignment_id' => $a->id.
    }

    showAssignmentModal.value = true;
};

const editAllAssignments = (task) => {
    // Bulk Edit Mode
    showDetailsModal.value = false;
    isEditing.value = true;
    editingTask.value = task;

    form.task_id = task.id;
    // Get ALL unique users assigned to this task (across all segments)
    const uniqueUserIds = [...new Set(task.assignments.map(a => a.id))];
    form.user_ids = uniqueUserIds;

    form.hours = 8; // Reset/Default for bulk
    form.start_date = task.start_date;
    form.end_date = task.due_date || task.start_date;
    form.assignment_id = null; // Clear ID to signify bulk/replace

    showAssignmentModal.value = true;
};

const deleteAssignment = async () => {
    if (!confirm('Are you sure you want to remove this assignment?')) return;

    form.processing = true;
    try {
        // 1. Delete Specific Segment
        if (form.assignment_id) {
            await axios.post(route('planner.assign'), {
                 task_id: form.task_id, 
                 strategy: 'delete',
                 assignment_id: form.assignment_id
            });
            toast.success('Assignment removed');
        } 
        // 2. Delete Entire Task (from Bulk Edit)
        else {
            await axios.delete(route('planner.destroy', form.task_id));
            toast.success('Task deleted');
        }
        
        showAssignmentModal.value = false;
        window.location.reload();
    } catch (e) {
        toast.error('Delete failed');
        console.error(e);
    } finally {
        form.processing = false;
    }
};

// JSON headers for axios
const jsonHeaders = { headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' } };

const submitAssignment = async () => {
    form.clearErrors();

    if (form.user_ids.length === 0) {
        toast.error('Pick a resource');
        return;
    }
    if (dayjs(form.end_date).isBefore(dayjs(form.start_date))) {
        form.setError('date_range', 'Invalid dates');
        return;
    }

    form.processing = true;
    try {
        // Strategy Decision
        let strategy = 'replace';
        let updateTask = false;

        if (form.assignment_id) {
             strategy = 'merge'; // Specific Update
        } else if (isEditing.value && form.user_ids.length === 1 && !form.assignment_id) {
             strategy = 'merge'; // Add New Segment
        } else if (isEditing.value && !form.assignment_id) {
             // Bulk Edit Mode
             strategy = 'replace';
             updateTask = true; // Update main task dates too
        }

        await axios.post(route('planner.assign'), {
            task_id: form.task_id,
            user_ids: form.user_ids,
            hours: form.hours,
            start_date: form.start_date,
            end_date: form.end_date,
            strategy: strategy,
            assignment_id: form.assignment_id,
            update_task_dates: updateTask,
            force_allocation: form.force_allocation
        }, jsonHeaders);

        toast.success(`Saved!`);
        showAssignmentModal.value = false;
        window.location.reload();

    } catch (e) {
        toast.error('Failed.');
        console.error(e);
    } finally {
        form.processing = false;
    }
};

onMounted(() => {
    //
});

</script>

<style scoped>
.pattern-diagonal-lines-sm {
  background-image: repeating-linear-gradient(45deg, rgba(0,0,0,0.05) 0, rgba(0,0,0,0.05) 1px, transparent 0, transparent 50%);
  background-size: 10px 10px;
}
.pattern-diagonal-indigo {
    background-image: repeating-linear-gradient(45deg, transparent, transparent 5px, rgba(99, 102, 241, 0.1) 5px, rgba(99, 102, 241, 0.1) 10px);
}
</style>
