<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import BaseSelect from '@/Components/BaseSelect.vue';
import Combobox from '@/Components/Combobox.vue'; // Searchable Select
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import { ChevronLeftIcon, ChevronRightIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { useToastStore } from '@/stores/toast';

const props = defineProps({
    projects: Array, // Assigned
    all_projects: Array // All Active
});

const toast = useToastStore();
const startOfWeek = ref(new Date()); // Will adjust to Monday
const weekDays = ref([]);
const rows = ref([]); // Moved definition down
// But need it for computed, so defining logic order.
// Just placeholder here, will overwrite in loadData.
const loading = ref(false);
const processing = ref(false);
const weekRange = ref(''); 
const config = ref({ allowed_past_days: 30, allowed_future_days: 30 }); // Defaults
const lockedDates = ref([]);
const offDays = ref([]);

// New Row State
const showAddRow = ref(false);
const newRow = ref({ project_id: '', task_id: '', task_title: '', is_other: false });
const availableTasks = ref([]);

// Initialize Week (Monday to Sunday)
const initWeek = (date = new Date()) => {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1); // adjust when day is sunday
    const monday = new Date(d.setDate(diff));
    startOfWeek.value = monday;
    
    // Generate 7 days
    const days = [];
    for (let i = 0; i < 7; i++) {
        const current = new Date(monday);
        current.setDate(monday.getDate() + i);
        // Use Local YYYY-MM-DD
        const year = current.getFullYear();
        const month = String(current.getMonth() + 1).padStart(2, '0');
        const dayNum = String(current.getDate()).padStart(2, '0');
        const dateStr = `${year}-${month}-${dayNum}`;

        days.push({
            date: dateStr,
            dayName: current.toLocaleDateString('en-US', { weekday: 'short' }),
            dateNum: current.getDate(),
            fullDate: current
        });
    }
    weekDays.value = days;
    weekRange.value = `${days[0].dayName} ${days[0].dateNum}, ${days[0].fullDate.toLocaleString('default', { month: 'short' })} - ${days[6].dayName} ${days[6].dateNum}, ${days[6].fullDate.toLocaleString('default', { month: 'short' })}`;
    rows.value = []; // Reset grid (or should we keep structure? No, reload)
    loadData();
};

const canNavigateOriginal = (dir) => {
    // Check config
    const today = new Date();
    const targetDate = new Date(startOfWeek.value);
    targetDate.setDate(targetDate.getDate() + (dir * 7));
    
    const diffTime = targetDate - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (dir > 0 && diffDays > config.value.allowed_future_days) return false;
    if (dir < 0 && Math.abs(diffDays) > config.value.allowed_past_days + 7) return false; // +7 buffer for whole week logic

    return true;
};

const getActual = (userId, dateStr) => {
    if (!props.timesheets || !props.timesheets[userId]) return 0;
    return props.timesheets[userId][dateStr] || 0;
};

const navigateWeek = (dir) => {
    if (!canNavigateOriginal(dir)) {
        toast.warning("Cannot log time outside the allowed range.");
        return;
    }
    const newDate = new Date(startOfWeek.value);
    newDate.setDate(newDate.getDate() + (dir * 7));
    initWeek(newDate);
};

// Fetch Assigned Tasks & Pre-fill Rows
// Helper to find or create row
const getOrCreateRow = (t, isOther = false, otherTitle = '') => {
    let rowId = isOther ? 'other_' + t.project_id + '_' + otherTitle : t.id;
    // For regular tasks, ID is task ID. For other, it's composite or generated. 
    // Actually, stored Other entries have no task_id.
    
    // Use lenient comparison for IDs (string vs number)
    let existing = rows.value.find(r => r.id == rowId);
    if (existing) return existing;

    // Create new
    const newRow = {
        id: rowId,
        project_id: t.project_id,
        project_name: t.project ? t.project.name : (t.project_name || props.projects.find(p => p.id == t.project_id)?.name || 'Unknown'),
        project_code: t.project ? t.project.code : (t.project_code || props.projects.find(p => p.id == t.project_id)?.code || ''),
        title: isOther ? otherTitle : (t.task ? t.task.title : t.title),
        code: isOther ? '' : (t.task ? t.task.code : (t.code || '')),
        is_other: isOther,
        cells: weekDays.value.reduce((acc, day) => {
            acc[day.date] = ''; 
            return acc;
        }, {})
    };
    rows.value.push(newRow);
    // Return the reactive version from the array
    return rows.value.find(r => r.id == rowId) || newRow;
};

// Modified LoadData
const loadData = async () => {
    loading.value = true;
    rows.value = []; // Clear
    lockedDates.value = [];
    try {
        // 1. Fetch Assigned Tasks (Default rows)
        const [assignedRes, logRes] = await Promise.all([
            axios.get('/api/employee/attendance/timesheets/assigned-tasks'),
            axios.get('/api/employee/attendance/timesheets/weekly-log', {
                params: { 
                    start_date: weekDays.value[0].date, 
                    end_date: weekDays.value[6].date 
                }
            })
        ]);

        const assignedTasks = assignedRes.data.tasks || [];
        if (assignedRes.data.config) config.value = assignedRes.data.config;

        // Init rows with assigned tasks
        assignedTasks.forEach(t => {
            getOrCreateRow(t);
        });

        // 2. Fill with Log Data
        const logs = logRes.data.entries ? logRes.data.entries : (Array.isArray(logRes.data) ? logRes.data : []);
        offDays.value = logRes.data.off_days || [];
        console.log('Weekly Logs:', logs, 'Off Days:', offDays.value);

        lockedDates.value = [...new Set(
            logs
                .filter((entry) => String(entry.status || '').toLowerCase() === 'approved')
                .map((entry) => String(entry.date).split('T')[0])
        )];
        
        logs.forEach(entry => {
            // Determine if Other
            const isOther = !entry.task_id;
            const title = entry.task_description || (entry.task ? entry.task.title : 'Unknown'); // Fallback
            
            // Construct pseudo-task object for helper
            const tObj = {
                id: entry.task_id,
                project_id: entry.project_id,
                project: entry.project,
                task: entry.task,
                // fallback for assigned structure match
                project_name: entry.project?.name,
                title: isOther ? entry.task_description : entry.task?.title
            };

            const row = getOrCreateRow(tObj, isOther, entry.task_description);
            
            // Set hours (strip date time part if needed, assuming YYYY-MM-DD matches)
            const dateKey = entry.date.split('T')[0];
            if (row.cells[dateKey] !== undefined) {
                row.cells[dateKey] = parseFloat(entry.hours_spent); // Use hours_spent from DB
            }
        });

    } catch (e) {
        console.error(e);
        toast.error("Failed to load timesheet data");
    } finally {
        loading.value = false;
    }
};

const dailyTotals = computed(() => {
    const totals = {};
    weekDays.value.forEach(day => {
        let sum = 0;
        rows.value.forEach(row => {
            const h = parseFloat(row.cells[day.date] || 0);
            if (!isNaN(h)) sum += h;
        });
        totals[day.date] = sum;
    });
    return totals;
});

const getCellColor = (hours) => {
    if (!hours) return 'bg-white';
    return 'bg-blue-50'; // Simple active indicator
};

const getTotalColor = (hours) => {
    if (hours > 8) return 'text-red-600 font-bold'; // Warning
    if (hours > 0) return 'text-emerald-600 font-bold'; // Good
    return 'text-gray-400';
};

const saveWeek = async () => {
    processing.value = true;
    try {
        // Flatten rows to entries
        const entries = [];
        const skippedLockedDates = new Set();
        rows.value.forEach(row => {
            weekDays.value.forEach(day => {
                let h = parseFloat(row.cells[day.date]);
                if (isNaN(h)) h = 0;
                
                if (h >= 0) {
                    if (isDateLocked(day.date)) {
                        skippedLockedDates.add(day.date);
                        return;
                    }
                    if (isFutureDate(day.date) && h > 0) {
                        toast.warning(`Cannot log time for future date: ${day.date}`);
                        throw new Error("Future date logging blocked");
                    }

                   entries.push({
                       date: day.date,
                       project_id: row.project_id,
                       task_id: row.is_other ? null : row.id,
                       task_title: row.is_other ? row.title : null,
                       description: row.is_other ? row.title : (row.title || 'Weekly Log'),
                       hours: h
                   });
                }
            });
        });

        if (skippedLockedDates.size > 0) {
            toast.warning(`Approved dates are locked: ${Array.from(skippedLockedDates).join(', ')}`);
        }

        if (entries.length === 0) {
            toast.warning("No hours entered to save.");
            return;
        }

        await axios.post('/api/employee/attendance/timesheets/bulk', { entries });
        toast.success("Weekly timesheet saved!");
        loadData(); // Sync with server after save
    } catch (e) {
        console.error(e);
        toast.error(e.response?.data?.message || "Failed to save week");
    } finally {
        processing.value = false;
    }
};

// Add Row Logic
const fetchProjectTasks = async () => {
    if (!newRow.value.project_id) return;
    availableTasks.value = []; // Clear
    try {
        const res = await axios.get(`/api/employee/attendance/timesheets/project-tasks/${newRow.value.project_id}?all=${newRow.value.show_all ? '1' : '0'}`);
        availableTasks.value = res.data.assigned_tasks || res.data.all_tasks; 
        
        // If show_all, we might get both or need to merge? 
        // The API returns 'assigned_tasks' and 'all_tasks' if all=true.
        // Let's adjust logic.
        if (newRow.value.show_all) {
            availableTasks.value = res.data.all_tasks;
        } else {
            availableTasks.value = res.data.assigned_tasks;
        }

    } catch(e) {}
};

const toggleShowAll = () => {
    newRow.value.show_all = !newRow.value.show_all;
    fetchProjectTasks();
};

const confirmAddRow = () => {
    if (!newRow.value.project_id) return;
    
    if (newRow.value.is_other) {
        if (!newRow.value.task_title) {
            toast.warning("Please enter a task title");
            return;
        }
        getOrCreateRow({ project_id: newRow.value.project_id }, true, newRow.value.task_title);
    } else {
        if (!newRow.value.task_id) {
            toast.warning("Please select a task");
            return;
        }
        const task = availableTasks.value.find(t => t.id == newRow.value.task_id);
        if (task) {
             // Ensure project_id is passed even if missing from task object
             getOrCreateRow({ ...task, project_id: newRow.value.project_id });
        }
    }
    showAddRow.value = false;
    newRow.value = { project_id: '', task_id: '', is_other: false };
};

const removeRow = (rowId) => {
    rows.value = rows.value.filter(r => r.id !== rowId);
};

const WrapperTitle = (t) => {
    return t.code ? `${t.code} - ${t.title}` : t.title;
};

const isDateLocked = (date) => lockedDates.value.includes(date);
const isFutureDate = (date) => {
    const d = new Date(date);
    d.setHours(0,0,0,0);
    const today = new Date();
    today.setHours(0,0,0,0);
    return d > today;
};

onMounted(() => {
    initWeek();
});
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <!-- Toolbar -->
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <div class="flex items-center gap-4">
                <button @click="navigateWeek(-1)" class="p-1 hover:bg-gray-200 rounded text-gray-600" :class="{'opacity-50 cursor-not-allowed': !canNavigateOriginal(-1)}"><ChevronLeftIcon class="w-5 h-5" /></button>
                <div class="text-center min-w-[150px]">
                    <span class="block text-sm font-bold text-gray-900">
                        {{ weekRange }}
                    </span>
                    <span class="text-xs text-gray-500">{{ weekDays[0]?.fullDate.getFullYear() }}</span>
                </div>
                <button @click="navigateWeek(1)" class="p-1 hover:bg-gray-200 rounded text-gray-600" :class="{'opacity-50 cursor-not-allowed': !canNavigateOriginal(1)}"><ChevronRightIcon class="w-5 h-5" /></button>
            </div>
            
            <div class="flex gap-2">
                 <SecondaryButton @click="loadData" title="Reset/Reload"><span class="text-xs">Reset</span></SecondaryButton>
                 <PrimaryButton @click="saveWeek" :disabled="processing">Save Week</PrimaryButton>
            </div>
        </div>

        <!-- Grid -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 min-w-[250px]">Project / Task</th>
                        <th v-for="day in weekDays" :key="day.date" class="px-2 py-3 text-center min-w-[60px]" :class="{'bg-orange-50': offDays.includes(day.date)}">
                            <div class="font-bold">{{ day.dayName }}</div>
                            <div class="text-sm">{{ day.dateNum }}</div>
                        </th>
                        <th class="px-2 py-3 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="row in rows" :key="row.id" class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-2">
                             <div class="font-medium text-gray-900 truncate max-w-[200px]" :title="row.title">{{ row.title }}</div>
                             <div class="text-xs text-gray-500 flex gap-2">
                                 <span v-if="row.code" class="font-mono bg-gray-100 px-1 rounded">{{ row.code }}</span>
                                 <span class="text-gray-400">{{ row.project_name }}</span>
                             </div>
                        </td>
                        <td v-for="day in weekDays" :key="day.date" class="px-1 py-1 text-center" :class="{'bg-orange-50/50': offDays.includes(day.date)}">
                            <input 
                                type="number" 
                                v-model="row.cells[day.date]" 
                                class="w-full text-center border-gray-200 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500 p-1 h-8"
                                :class="{ 'bg-gray-100 text-gray-500 cursor-not-allowed': isDateLocked(day.date) || isFutureDate(day.date) }"
                                placeholder="-"
                                min="0" 
                                max="24"
                                step="0.5"
                                :disabled="isDateLocked(day.date) || isFutureDate(day.date)"
                                :title="isDateLocked(day.date) ? 'Approved date is locked' : (isFutureDate(day.date) ? 'Cannot log for future date' : '')"
                            />
                        </td>
                        <td class="px-2 text-center">
                            <button @click="removeRow(row.id)" class="text-gray-400 hover:text-red-500"><TrashIcon class="w-4 h-4" /></button>
                        </td>
                    </tr>
                    
                    <!-- Empty State -->
                     <tr v-if="rows.length === 0 && !loading">
                         <td colspan="9" class="text-center py-8 text-gray-400">
                             No tasks assigned. Click "Add Row" to start logging.
                         </td>
                     </tr>
                </tbody>
                <!-- Footer (Totals) -->
                <tfoot class="bg-gray-50 font-bold border-t border-gray-200">
                    <tr>
                        <td class="px-4 py-3 text-right text-gray-600 uppercase text-xs">Total Hours</td>
                        <td v-for="day in weekDays" :key="day.date" class="px-2 py-3 text-center">
                            <span :class="getTotalColor(dailyTotals[day.date])">{{ dailyTotals[day.date] || '-' }}</span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <!-- Add Row Action -->
        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
             <button @click="showAddRow = true" class="text-indigo-600 text-sm font-semibold hover:underline flex items-center gap-2 px-2 py-1 hover:bg-indigo-50 rounded">
                 <PlusIcon class="w-4 h-4" /> Add Task Row
             </button>
             
             <!-- Add Row Modal -->
             <Modal :show="showAddRow" @close="showAddRow = false">
                 <div class="p-6">
                     <h3 class="text-lg font-bold text-gray-800 mb-4">Add New Entry Row</h3>
                     
                     <div class="space-y-4">
                         <!-- Project Select -->
                         <div>
                            <div class="flex justify-between items-center mb-1">
                                 <label class="block text-sm font-medium text-gray-700">Select Project</label>
                                 <button @click="newRow.show_all_projects = !newRow.show_all_projects" class="text-xs text-indigo-600 hover:underline" :class="{'font-bold': newRow.show_all_projects}">
                                     {{ newRow.show_all_projects ? 'Assigned' : 'Show All' }}
                                 </button>
                            </div>
                            <Combobox 
                                v-model="newRow.project_id" 
                                :items="newRow.show_all_projects ? all_projects : projects"
                                labelKey="name"
                                valueKey="id"
                                placeholder="Search Project..."
                                @update:modelValue="fetchProjectTasks"
                            />
                         </div>

                         <!-- Activity Type Toggle -->
                         <div class="border-t border-b border-gray-100 py-3">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-medium text-gray-700">Activity Type</label>
                                <div class="flex items-center gap-3 text-sm">
                                    <label class="flex items-center gap-1 cursor-pointer">
                                        <input type="radio" :value="false" v-model="newRow.is_other" class="text-indigo-600 focus:ring-indigo-500">
                                        Project Task
                                    </label>
                                    <label class="flex items-center gap-1 cursor-pointer">
                                        <input type="radio" :value="true" v-model="newRow.is_other" class="text-indigo-600 focus:ring-indigo-500">
                                        Other / Ad-hoc
                                    </label>
                                </div>
                            </div>

                             <!-- Task Select (If Project Task) -->
                             <div v-if="!newRow.is_other" class="relative">
                                <div class="flex justify-between items-center mb-1">
                                     <label class="block text-sm font-medium text-gray-700">Select Task</label>
                                     <button @click="toggleShowAll" class="text-xs text-indigo-600 hover:underline" :class="{'font-bold': newRow.show_all}">
                                         {{ newRow.show_all ? 'Show Assigned Only' : 'Show All Tasks' }}
                                     </button>
                                </div>
                                <Combobox
                                    v-model="newRow.task_id"
                                    :items="availableTasks"
                                    :displayFormat="(t) => t.code ? `${t.code} - ${t.title}` : t.title"
                                    valueKey="id"
                                    placeholder="Search Task..."
                                    :disabled="!newRow.project_id"
                                />
                             </div>

                             <!-- Other Title (If Other) -->
                             <div v-else>
                                 <label class="block text-sm font-medium text-gray-700">Task Title / Description</label>
                                 <input type="text" v-model="newRow.task_title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm h-[38px]" placeholder="e.g. Ad-hoc meeting...">
                                 <div class="mt-1 text-xs text-yellow-600 bg-yellow-50 p-1 rounded">
                                     Logging time for work not defined in the project plan.
                                 </div>
                             </div>
                         </div>

                         <!-- Actions -->
                         <div class="flex justify-end gap-3 mt-4">
                             <SecondaryButton @click="showAddRow = false; newRow={}" class="">Cancel</SecondaryButton>
                             <PrimaryButton @click="confirmAddRow" :disabled="!newRow.project_id" class="">Add to Grid</PrimaryButton>
                         </div>
                     </div>
                 </div>
             </Modal>
        </div>
    </div>
</template>

<script>
// Helper script block for functions not in setup
// Actually moving WrapperTitle to setup is better
</script>