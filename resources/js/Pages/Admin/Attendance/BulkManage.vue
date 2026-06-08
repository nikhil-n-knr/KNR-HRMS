<template>
  <div class="min-h-screen bg-slate-50/50 pb-12">
    <Head title="Bulk Attendance Terminal" />
    
    <div class="max-w-[1500px] mx-auto p-4 lg:p-8 space-y-6">
      
      <!-- Top Title and Employee Selector -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white border border-slate-100 p-6 rounded-2xl shadow-xl shadow-slate-200/30">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-emerald-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
            <i class="fas fa-calendar-check text-xl"></i>
          </div>
          <div>
            <h1 class="text-lg font-black uppercase tracking-wider text-slate-900 leading-none">Bulk Registry Console</h1>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1.5">Direct Database Pulse Injection</p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
          <!-- Employee Selector -->
          <div class="flex flex-col gap-1 w-full sm:w-[280px]">
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Selected Operator</span>
            <select 
              v-model="employeeId" 
              @change="syncParams"
              class="w-full bg-white border border-slate-200 rounded-xl h-11 px-4 text-sm font-black text-slate-800 focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all cursor-pointer"
            >
              <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                {{ emp.name.toUpperCase() }}
              </option>
            </select>
          </div>

          <!-- Year Selector -->
          <div class="flex flex-col gap-1 w-[120px]">
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Fiscal Year</span>
            <select 
              v-model="year" 
              @change="syncParams"
              class="w-full bg-white border border-slate-200 rounded-xl h-11 px-4 text-sm font-black text-slate-800 focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all cursor-pointer"
            >
              <option v-for="y in [2025, 2026, 2027]" :key="y" :value="y">{{ y }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Main Layout Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        <!-- Left Sidebar: Months List -->
        <div class="lg:col-span-1 bg-white border border-slate-100 p-4 rounded-2xl shadow-lg shadow-slate-200/30 space-y-2 h-fit">
          <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4 px-2">Months Directory</h3>
          <div class="space-y-1">
            <button 
              v-for="m in monthList" 
              :key="m.id"
              @click="selectMonth(m.id)"
              class="w-full flex items-center justify-between px-4 py-3.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-200"
              :class="[
                selectedMonth === m.id 
                  ? 'bg-slate-900 text-white shadow-md transform scale-102 font-black' 
                  : 'text-slate-655 hover:bg-slate-50 hover:text-slate-900'
              ]"
            >
              <span class="flex items-center gap-3">
                <i :class="selectedMonth === m.id ? 'fas fa-folder-open text-emerald-400' : 'fas fa-folder text-slate-400'" class="text-sm"></i>
                {{ m.name }}
              </span>
              <i class="fas fa-chevron-right text-[10px] opacity-50"></i>
            </button>
          </div>
        </div>

        <!-- Right Content Area: Days Grid -->
        <div class="lg:col-span-3 bg-white border border-slate-100 rounded-2xl shadow-xl shadow-slate-200/30 overflow-hidden flex flex-col min-h-[500px]">
          
          <!-- Month Banner & Bulk Tools -->
          <div class="p-6 border-b border-slate-100 bg-slate-50/50 space-y-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
              <div>
                <h2 class="text-xl font-black uppercase tracking-wider text-slate-950">
                  {{ monthList.find(m => m.id === selectedMonth)?.name }} {{ year }}
                </h2>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">
                  Editing {{ localDays.length }} calendar slots
                </p>
              </div>

              <!-- Save Status / Info -->
              <div v-if="hasChanges" class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-200/60 text-[10px] font-black uppercase tracking-widest">
                <i class="fas fa-exclamation-triangle animate-pulse text-amber-550"></i>
                Pending Save
              </div>
            </div>

            <!-- Batch Override Controls -->
            <div class="flex flex-wrap items-center gap-2 p-3 bg-slate-50 rounded-xl border border-slate-100">
              <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 mr-2">Batch Commands:</span>
              <button 
                @click="batchSetWeekdays"
                class="px-3.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-500 text-emerald-700 hover:text-white text-[10px] font-black uppercase tracking-widest border border-emerald-250 hover:border-emerald-500 transition-all active:scale-95"
              >
                Auto Weekdays Present
              </button>
              <button 
                @click="batchSetWeekends"
                class="px-3.5 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-500 text-blue-700 hover:text-white text-[10px] font-black uppercase tracking-widest border border-blue-250 hover:border-blue-550 transition-all active:scale-95"
              >
                Set Weekends Off
              </button>
              <button 
                @click="batchSetAll('present')"
                class="px-3.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-900 text-slate-700 hover:text-white text-[10px] font-black uppercase tracking-widest border border-slate-200 hover:border-slate-800 transition-all active:scale-95"
              >
                Mark All Present
              </button>
              <button 
                @click="batchSetAll('wfh')"
                class="px-3.5 py-1.5 rounded-lg bg-orange-50 hover:bg-orange-500 text-orange-700 hover:text-white text-[10px] font-black uppercase tracking-widest border border-orange-250 hover:border-orange-500 transition-all active:scale-95"
              >
                Mark All WFH
              </button>
              <button 
                @click="batchSetAll('absent')"
                class="px-3.5 py-1.5 rounded-lg bg-red-50 hover:bg-red-500 text-red-700 hover:text-white text-[10px] font-black uppercase tracking-widest border border-red-250 hover:border-red-550 transition-all active:scale-95"
              >
                Mark All Absent
              </button>
            </div>
          </div>

          <!-- Table View of Days -->
          <div class="flex-1 overflow-x-auto">
            <table class="w-full border-collapse">
              <thead class="bg-slate-900 text-white">
                <tr>
                  <th class="px-6 py-4 text-left">
                    <span class="text-xs font-black uppercase tracking-widest">Date</span>
                  </th>
                  <th class="px-6 py-4 text-center">
                    <span class="text-xs font-black uppercase tracking-widest">Status</span>
                  </th>
                  <th class="px-6 py-4 text-center">
                    <span class="text-xs font-black uppercase tracking-widest">Check-In</span>
                  </th>
                  <th class="px-6 py-4 text-center">
                    <span class="text-xs font-black uppercase tracking-widest">Check-Out</span>
                  </th>
                  <th class="px-6 py-4 text-right">
                    <span class="text-xs font-black uppercase tracking-widest">Duration</span>
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr 
                  v-for="(day, index) in localDays" 
                  :key="day.date" 
                  class="hover:bg-slate-50 transition-all group"
                  :class="{'bg-slate-50/50': day.is_weekend}"
                >
                  <!-- Date Details -->
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div 
                        class="w-8 h-8 rounded-lg flex items-center justify-center font-black text-xs border"
                        :class="[
                          day.is_weekend 
                            ? 'bg-slate-100 border-slate-200 text-slate-400' 
                            : 'bg-emerald-50 border-emerald-100 text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white transition-colors'
                        ]"
                      >
                        {{ new Date(day.date).getDate() }}
                      </div>
                      <div>
                        <p class="text-sm font-black text-slate-800 leading-none uppercase tracking-tight">
                          {{ day.day_name }}
                        </p>
                        <p class="text-[10px] font-bold text-slate-450 mt-1 uppercase tracking-widest">
                          {{ day.date }}
                        </p>
                      </div>
                    </div>
                  </td>

                  <!-- Status Selector -->
                  <td class="px-6 py-4">
                    <div class="flex justify-center">
                      <select 
                        v-model="day.status"
                        @change="markDirty(index)"
                        class="bg-white border rounded-lg h-9 px-3 text-xs font-black uppercase tracking-widest cursor-pointer transition-all focus:ring-2 focus:ring-emerald-500/20"
                        :class="getStatusSelectClass(day.status)"
                      >
                        <option value="present">PRESENT</option>
                        <option value="late">LATE</option>
                        <option value="wfh">WORK FROM HOME</option>
                        <option value="half_day">HALF DAY</option>
                        <option value="absent">ABSENT</option>
                        <option value="week_off">WEEK OFF</option>
                        <option value="holiday">HOLIDAY</option>
                      </select>
                    </div>
                  </td>

                  <!-- In Time -->
                  <td class="px-6 py-4">
                    <div class="flex justify-center">
                      <input 
                        type="time" 
                        v-model="day.in_time" 
                        :disabled="!isWorkingStatus(day.status)"
                        @change="markDirty(index)"
                        class="bg-white border border-slate-200 rounded-lg h-9 px-3 text-xs font-black text-slate-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all disabled:opacity-30 disabled:cursor-not-allowed" 
                      />
                    </div>
                  </td>

                  <!-- Out Time -->
                  <td class="px-6 py-4">
                    <div class="flex justify-center">
                      <input 
                        type="time" 
                        v-model="day.out_time" 
                        :disabled="!isWorkingStatus(day.status)"
                        @change="markDirty(index)"
                        class="bg-white border border-slate-200 rounded-lg h-9 px-3 text-xs font-black text-slate-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all disabled:opacity-30 disabled:cursor-not-allowed" 
                      />
                    </div>
                  </td>

                  <!-- Computed Hours -->
                  <td class="px-6 py-4 text-right">
                    <span 
                      v-if="isWorkingStatus(day.status)"
                      class="text-xs font-black uppercase tracking-tighter tabular-nums"
                      :class="calculateDuration(day.in_time, day.out_time) === '0h 0m' ? 'text-slate-400' : 'text-slate-700'"
                    >
                      {{ calculateDuration(day.in_time, day.out_time) }}
                    </span>
                    <span v-else class="text-xs font-black uppercase tracking-tighter text-slate-400">
                      -
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Bottom Sticky Save Bar -->
          <div class="p-6 border-t border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <span class="text-xs font-bold text-slate-450 uppercase tracking-widest">
              Review details before pushing database payload
            </span>
            <button 
              @click="submitSave" 
              :disabled="savingForm.processing"
              class="h-11 px-8 bg-emerald-600 text-white hover:bg-emerald-500 rounded-xl text-xs font-black uppercase tracking-[0.2em] flex items-center gap-3 transition-all shadow-lg active:scale-95 disabled:opacity-50"
            >
              <i class="fas fa-database"></i>
              {{ savingForm.processing ? 'COMMIT INJECTING...' : 'COMMIT LOGS' }}
            </button>
          </div>

        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useToastStore } from '@/stores/toast';

defineOptions({ layout: MainLayout });

const props = defineProps({
    employees: Array,
    selectedEmployeeId: Number,
    selectedMonth: Number,
    selectedYear: Number,
    days: Array,
});

const toast = useToastStore();

const employeeId = ref(props.selectedEmployeeId);
const year = ref(props.selectedYear);
const selectedMonth = ref(props.selectedMonth);

const localDays = ref(JSON.parse(JSON.stringify(props.days)));
const hasChanges = ref(false);

const monthList = [
    { id: 1, name: 'January' },
    { id: 2, name: 'February' },
    { id: 3, name: 'March' },
    { id: 4, name: 'April' },
    { id: 5, name: 'May' },
    { id: 6, name: 'June' },
    { id: 7, name: 'July' },
    { id: 8, name: 'August' },
    { id: 9, name: 'September' },
    { id: 10, name: 'October' },
    { id: 11, name: 'November' },
    { id: 12, name: 'December' },
];

watch(() => props.days, (newVal) => {
    localDays.value = JSON.parse(JSON.stringify(newVal));
    hasChanges.value = false;
}, { deep: true });

const markDirty = (index) => {
    hasChanges.value = true;
};

const syncParams = () => {
    router.get(route('admin.attendance.bulk-manage'), {
        employee_id: employeeId.value,
        month: selectedMonth.value,
        year: year.value
    }, {
        preserveState: false,
        preserveScroll: true
    });
};

const selectMonth = (mId) => {
    selectedMonth.value = mId;
    syncParams();
};

const isWorkingStatus = (status) => {
    return ['present', 'late', 'half_day', 'wfh', 'work_from_home'].includes(status.toLowerCase());
};

const getStatusSelectClass = (status) => {
    switch (status.toLowerCase()) {
        case 'present':
            return 'border-emerald-500/30 text-emerald-600 bg-emerald-50';
        case 'wfh':
        case 'work_from_home':
            return 'border-orange-500/30 text-orange-600 bg-orange-50';
        case 'late':
            return 'border-amber-500/30 text-amber-600 bg-amber-50';
        case 'half_day':
        case 'halfday':
            return 'border-indigo-500/30 text-indigo-650 bg-indigo-50';
        case 'absent':
            return 'border-red-500/30 text-red-600 bg-red-50';
        case 'week_off':
            return 'border-blue-500/30 text-blue-600 bg-blue-50';
        case 'holiday':
            return 'border-teal-500/30 text-teal-650 bg-teal-55/30';
        default:
            return 'border-slate-200 text-slate-700 bg-white';
    }
};

const calculateDuration = (inTime, outTime) => {
    if (!inTime || !outTime) return '0h 0m';
    try {
        const [inH, inM] = inTime.split(':').map(Number);
        const [outH, outM] = outTime.split(':').map(Number);
        
        const inMins = inH * 60 + inM;
        const outMins = outH * 60 + outM;
        
        let diff = outMins - inMins;
        if (diff < 0) diff = 0;
        
        const h = Math.floor(diff / 60);
        const m = diff % 60;
        return `${h}h ${m}m`;
    } catch (e) {
        return '0h 0m';
    }
};

// Batch Action Commands
const batchSetWeekdays = () => {
    localDays.value.forEach((day) => {
        if (!day.is_weekend) {
            day.status = 'present';
            day.in_time = '09:00';
            day.out_time = '18:00';
        }
    });
    hasChanges.value = true;
    toast.success("Weekdays populated to Present (09:00 - 18:00)");
};

const batchSetWeekends = () => {
    localDays.value.forEach((day) => {
        if (day.is_weekend) {
            day.status = 'week_off';
            day.in_time = '09:00';
            day.out_time = '18:00';
        }
    });
    hasChanges.value = true;
    toast.success("Weekends set to Week Off status");
};

const batchSetAll = (status) => {
    localDays.value.forEach((day) => {
        day.status = status;
        if (isWorkingStatus(status)) {
            day.in_time = '09:00';
            day.out_time = '18:00';
        }
    });
    hasChanges.value = true;
    toast.success(`Broadcasting '${status.toUpperCase()}' override to all entries`);
};

// Save logic
const savingForm = useForm({
    employee_id: null,
    days: []
});

const submitSave = () => {
    savingForm.employee_id = employeeId.value;
    savingForm.days = localDays.value.map(day => ({
        date: day.date,
        status: day.status,
        in_time: day.in_time,
        out_time: day.out_time
    }));

    savingForm.post(route('admin.attendance.bulk-manage.save'), {
        onSuccess: () => {
            toast.success("Database commits completed successfully");
            hasChanges.value = false;
        },
        onError: () => {
            toast.error("Internal transaction failed");
        }
    });
};
</script>
