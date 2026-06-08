<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    ChevronLeftIcon, 
    ArrowDownTrayIcon,
    CalendarIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    UserIcon,
    BriefcaseIcon,
    BuildingOfficeIcon,
    SparklesIcon,
    MapPinIcon,
    ClockIcon as OutlinedClockIcon,
    ArrowPathIcon,
    HomeIcon,
    CheckIcon,
    XMarkIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/outline';

// Set page layout to MainLayout to wrap it in parent portal frame
defineOptions({ layout: MainLayout });

const props = defineProps({
    employee: Object,
    selectedYear: Number,
    summary: Object,
    days: Array,
    requestsList: Array,
    holidaysList: Array,
    leaveLedgers: Array
});

// Setup states
const activeTab = ref('calendar'); // 'calendar', 'list', 'requests'
const listFilter = ref('all'); // 'all', 'present', 'late', 'absent', 'leave', 'wfh', 'off'
const hoveredDay = ref(null);
const hoveredStat = ref(null);
const tooltipX = ref(0);
const tooltipY = ref(0);
const expandedRow = ref(null);

// Pagination state
const currentPage = ref(1);
const itemsPerPage = 30;

const page = usePage();

// Hub Mode Detector
const isHubMode = computed(() => {
    const url = page.url;
    return url.includes('/attendance/hub') || url.includes('hub=1') || url.includes('hub=true');
});

// Years options
const years = [2026, 2025, 2024];
const selectedYearState = ref(props.selectedYear || 2026);

const handleYearChange = () => {
    router.visit(route('admin.attendance.monitoring.employee-details-page', { 
        employee: props.employee.id, 
        year: selectedYearState.value 
    }));
};

const goBack = () => {
    router.visit(route('admin.attendance.monitoring', { tab: 'monitor_view' }));
};

// Calendar calculations helper
const months = [
    { name: 'January', index: 0 },
    { name: 'February', index: 1 },
    { name: 'March', index: 2 },
    { name: 'April', index: 3 },
    { name: 'May', index: 4 },
    { name: 'June', index: 5 },
    { name: 'July', index: 6 },
    { name: 'August', index: 7 },
    { name: 'September', index: 8 },
    { name: 'October', index: 9 },
    { name: 'November', index: 10 },
    { name: 'December', index: 11 }
];

const getStartOfWeekOffset = (year, monthIndex) => {
    const day = new Date(year, monthIndex, 1).getDay(); // 0 is Sun, 1 is Mon
    return day === 0 ? 6 : day - 1; // Map Sun to 6, Mon to 0...Sat to 5
};

const getDaysInMonth = (year, monthIndex) => {
    return new Date(year, monthIndex + 1, 0).getDate();
};

const getDayRecord = (monthName, dayNum) => {
    return props.days.find(d => {
        return d.month_name === monthName.substring(0, 3) && d.day_num === dayNum;
    });
};

const getStatusColorClass = (status) => {
    switch (status) {
        case 'Present':
            return 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-sm border border-emerald-400/20';
        case 'Late':
            return 'bg-amber-500 hover:bg-amber-600 text-white shadow-sm border border-amber-400/20';
        case 'WFH Approved':
            return 'bg-orange-500 hover:bg-orange-600 text-white shadow-sm border border-orange-400/20';
        case 'WFH Pending':
            return 'bg-orange-50 hover:bg-orange-100 text-orange-600 border border-dashed border-orange-300';
        case 'On Leave':
            return 'bg-purple-500 hover:bg-purple-600 text-white shadow-sm border border-purple-400/20';
        case 'Leave Pending':
            return 'bg-purple-50 hover:bg-purple-100 text-purple-600 border border-dashed border-purple-300';
        case 'Holiday':
            return 'bg-sky-400 hover:bg-sky-500 text-white border border-sky-300/20';
        case 'Weekend Off':
            return 'bg-slate-100 hover:bg-slate-200 text-slate-400 border border-slate-200/50';
        case 'Absent':
            return 'bg-red-500 hover:bg-red-600 text-white border border-red-400/20 animate-[pulse_3s_infinite]';
        case 'Not Marked':
            return 'bg-red-50 hover:bg-red-100 text-red-650 border border-dashed border-red-300 animate-[pulse_4s_infinite]';
        case 'Scheduled':
            return 'bg-white hover:bg-slate-50 border border-dashed border-slate-200 text-slate-400';
        default:
            return 'bg-slate-50 text-slate-300 border border-slate-100';
    }
};

const showTooltip = (event, day) => {
    if (!day) return;
    hoveredDay.value = day;
    
    // Position tooltip relative to cursor
    tooltipX.value = event.clientX + 15;
    tooltipY.value = event.clientY + 15;
};

const hideTooltip = () => {
    hoveredDay.value = null;
};

const showStatTooltip = (event, type, title, value, details, monthName) => {
    hoveredStat.value = {
        type,
        title,
        value,
        details,
        monthName
    };
    tooltipX.value = event.clientX + 15;
    tooltipY.value = event.clientY + 15;
};

const hideStatTooltip = () => {
    hoveredStat.value = null;
};

// Filtered List View
const filteredDays = computed(() => {
    // Show only today and past dates in the list view
    const todayStr = new Date().toISOString().split('T')[0];
    const pastOrTodayDays = props.days.filter(d => d.date <= todayStr);
    
    let sorted = [...pastOrTodayDays].reverse(); // Show latest first in list view
    if (listFilter.value === 'all') return sorted;
    if (listFilter.value === 'present') return sorted.filter(d => d.status === 'Present' || d.status === 'Late');
    if (listFilter.value === 'late') return sorted.filter(d => d.status === 'Late');
    if (listFilter.value === 'absent') return sorted.filter(d => d.status === 'Absent');
    if (listFilter.value === 'leave') return sorted.filter(d => d.status === 'On Leave' || d.status === 'Leave Pending');
    if (listFilter.value === 'wfh') return sorted.filter(d => d.status === 'WFH Approved' || d.status === 'WFH Pending');
    if (listFilter.value === 'notmarked') return sorted.filter(d => d.status === 'Not Marked');
    if (listFilter.value === 'off') return sorted.filter(d => d.status === 'Weekend Off' || d.status === 'Holiday');
    return sorted;
});

// Paginated Days list
const totalPages = computed(() => {
    return Math.ceil(filteredDays.value.length / itemsPerPage) || 1;
});

const paginatedDays = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredDays.value.slice(start, start + itemsPerPage);
});

// Watch filters & activeTab to reset page to 1
watch([listFilter, activeTab], () => {
    currentPage.value = 1;
    expandedRow.value = null;
});

const toggleRow = (index) => {
    expandedRow.value = expandedRow.value === index ? null : index;
};

const getMonthCounts = (monthName) => {
    const monthShort = monthName.substring(0, 3);
    const monthDays = props.days.filter(d => d.month_name === monthShort);
    
    let present = 0;
    let late = 0;
    let absent = 0;
    let leaves = 0;
    let approvedLeaves = 0;
    let pendingLeaves = 0;
    let wfh = 0;
    let approvedWfh = 0;
    let pendingWfh = 0;
    let notMarked = 0;
    let workingDays = 0;
    
    const leaveTypesCount = {};
    
    monthDays.forEach(d => {
        if (!d.is_weekoff && !d.is_holiday) {
            workingDays++;
        }
        
        if (d.status === 'Present') {
            present++;
        } else if (d.status === 'Late') {
            present++;
            late++;
        } else if (d.status === 'Absent') {
            absent++;
        } else if (d.status === 'On Leave') {
            leaves++;
            approvedLeaves++;
            const typeName = d.leave_title || 'Leave';
            leaveTypesCount[typeName] = (leaveTypesCount[typeName] || 0) + 1;
        } else if (d.status === 'Leave Pending') {
            leaves++;
            pendingLeaves++;
            const typeName = (d.leave_title || 'Leave') + ' (Pending)';
            leaveTypesCount[typeName] = (leaveTypesCount[typeName] || 0) + 1;
        } else if (d.status === 'WFH Approved') {
            wfh++;
            approvedWfh++;
        } else if (d.status === 'WFH Pending') {
            wfh++;
            pendingWfh++;
        } else if (d.status === 'Not Marked') {
            notMarked++;
        }
    });
    
    // Build leave breakdown string
    let leaveDetails = 'No leaves';
    if (leaves > 0) {
        const parts = Object.entries(leaveTypesCount).map(([name, count]) => `${count} ${name}`);
        leaveDetails = parts.join(', ');
    }
    
    // Build present breakdown string
    let presentDetails = `Present: ${present - late}, Late: ${late}`;
    
    // Build WFH breakdown string
    let wfhDetails = `Approved: ${approvedWfh}, Pending: ${pendingWfh}`;
    
    return { 
        present, 
        late,
        absent, 
        leaves, 
        wfh, 
        notMarked, 
        workingDays,
        leaveDetails,
        presentDetails,
        wfhDetails
    };
};

const exportYearlyReport = () => {
    window.location.href = route('admin.attendance.monitoring.employee-details.export', { 
        employee: props.employee.id, 
        year: selectedYearState.value 
    });
};
</script>

<template>
    <AttendanceLayout 
        title="Yearly Analytics Hub" 
        activeTab="monitor_view"
        :hideSidebar="!isHubMode"
    >
        <Head :title="`${employee.name} - Yearly Attendance Matrix`" />

        <div class="space-y-8 pb-20 font-outfit max-w-[1500px] mx-auto animate-content-fade">
            <!-- Command Bar Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-900 text-white rounded-[2rem] p-6 shadow-xl border border-slate-800">
                <div class="flex items-center gap-4">
                    <button 
                        @click="goBack"
                        class="h-10 w-10 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl flex items-center justify-center border border-slate-700 transition-all active:scale-95 shrink-0"
                    >
                        <ChevronLeftIcon class="w-5 h-5" />
                    </button>
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg md:text-xl font-black uppercase tracking-wider text-white">Yearly Attendance Matrix</h2>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase tracking-widest">{{ selectedYear }} Status</span>
                        </div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Personnel Attendance Records</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    <!-- Year selector -->
                    <div class="relative">
                        <select 
                            v-model="selectedYearState" 
                            @change="handleYearChange"
                            class="bg-slate-800 text-white border-slate-700 hover:border-emerald-500/50 rounded-xl px-4 py-2.5 pr-8 text-xs font-black uppercase tracking-widest focus:ring-2 focus:ring-emerald-500 transition-all shadow-sm cursor-pointer appearance-none"
                        >
                            <option v-for="y in years" :key="y" :value="y">{{ y }} Records</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-[10px]"></i>
                    </div>

                    <!-- Export Report -->
                    <button 
                        @click="exportYearlyReport"
                        class="h-10 px-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl flex items-center justify-center gap-2 border border-emerald-500/20 shadow-md transition-all active:scale-95 text-xs font-black uppercase tracking-wider shrink-0"
                    >
                        <ArrowDownTrayIcon class="w-4 h-4" />
                        <span>Export Excel</span>
                    </button>
                </div>
            </div>

            <!-- Employee Info Header Card -->
            <div class="bg-white rounded-[2rem] border border-slate-100 p-6 md:p-8 shadow-xl shadow-slate-200/40 relative overflow-hidden group">
                <div class="absolute -right-16 -top-16 w-48 h-48 bg-slate-50 rounded-full blur-3xl opacity-60 group-hover:bg-emerald-50/50 transition-colors duration-700"></div>
                
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 relative z-10">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center text-white text-xl font-black shadow-lg shadow-slate-200">
                            {{ employee.name.charAt(0) }}
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-900 tracking-tight leading-none uppercase">{{ employee.name }}</h3>
                            <p class="text-xs font-black text-emerald-600 uppercase tracking-widest mt-2 flex items-center gap-1.5">
                                <SparklesIcon class="w-3.5 h-3.5" />
                                Employee Code: {{ employee.code }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 w-full lg:w-auto">
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 min-w-[120px] md:min-w-[150px]">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-2">Department</p>
                            <p class="text-sm font-black text-slate-800 uppercase tracking-wider leading-none truncate">{{ employee.department }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 min-w-[120px] md:min-w-[150px]">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-2">Designation</p>
                            <p class="text-sm font-black text-slate-800 uppercase tracking-wider leading-none truncate">{{ employee.designation }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 min-w-[120px] md:min-w-[150px]">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-2">Assigned Shift</p>
                            <p class="text-sm font-black text-slate-800 uppercase tracking-wider leading-none truncate">{{ summary.avg_work_hours !== '0 hrs' ? 'Full Time Schedule' : 'Flex Shift' }}</p>
                        </div>
                        <div class="bg-emerald-500/10 border border-emerald-500/10 rounded-2xl p-4 min-w-[120px] md:min-w-[150px]">
                            <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest leading-none mb-2">Attendance Rate (So Far)</p>
                            <p class="text-lg font-black text-emerald-700 leading-none">{{ summary.attendance_percentage }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Insights Dashboard Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-10 gap-4">
                <div class="bg-white/70 p-4 rounded-2xl border border-white/60 shadow-sm backdrop-blur-sm">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider leading-none">Present Days</p>
                    <p class="text-xl font-black text-emerald-600 mt-2">{{ summary.present_days }}</p>
                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Attended Shift</p>
                </div>
                <div class="bg-white/70 p-4 rounded-2xl border border-white/60 shadow-sm backdrop-blur-sm">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider leading-none">Late Arrivals</p>
                    <p class="text-xl font-black text-amber-500 mt-2">{{ summary.late_days }}</p>
                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Grace Exceeded</p>
                </div>
                <div class="bg-white/70 p-4 rounded-2xl border border-white/60 shadow-sm backdrop-blur-sm">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider leading-none">Explicit Absent</p>
                    <p class="text-xl font-black text-red-500 mt-2">{{ summary.absent_days }}</p>
                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Explicitly Logged</p>
                </div>
                <div class="bg-white/70 p-4 rounded-2xl border border-white/60 shadow-sm backdrop-blur-sm">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider leading-none">Not Marked</p>
                    <p class="text-xl font-black text-slate-700 mt-2">{{ summary.not_marked_days }}</p>
                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Missing logs</p>
                </div>
                <div class="bg-white/70 p-4 rounded-2xl border border-white/60 shadow-sm backdrop-blur-sm">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider leading-none">Leave Approved</p>
                    <p class="text-xl font-black text-purple-700 mt-2">{{ summary.leave_days }}</p>
                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Time Off Taken</p>
                </div>
                <div class="bg-white/70 p-4 rounded-2xl border border-white/60 shadow-sm backdrop-blur-sm">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider leading-none">Leave Pending</p>
                    <p class="text-xl font-black text-purple-400 mt-2">{{ summary.pending_leave_days }}</p>
                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Awaiting Signoff</p>
                </div>
                <div class="bg-white/70 p-4 rounded-2xl border border-white/60 shadow-sm backdrop-blur-sm">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider leading-none">WFH Approved</p>
                    <p class="text-xl font-black text-orange-600 mt-2">{{ summary.wfh_days }}</p>
                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Remote Worked</p>
                </div>
                <div class="bg-white/70 p-4 rounded-2xl border border-white/60 shadow-sm backdrop-blur-sm">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider leading-none">WFH Pending</p>
                    <p class="text-xl font-black text-orange-400 mt-2">{{ summary.pending_wfh_days }}</p>
                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Awaiting Signoff</p>
                </div>
                <div class="bg-white/70 p-4 rounded-2xl border border-white/60 shadow-sm backdrop-blur-sm">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider leading-none">Paid Holidays</p>
                    <p class="text-xl font-black text-sky-500 mt-2">{{ summary.holiday_days }}</p>
                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Company Calendar</p>
                </div>
                <div class="bg-white/70 p-4 rounded-2xl border border-white/60 shadow-sm backdrop-blur-sm">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider leading-none">Weekend Offs</p>
                    <p class="text-xl font-black text-slate-500 mt-2">{{ summary.week_off_days }}</p>
                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Shift Weekends</p>
                </div>
            </div>

            <!-- Leave Balance Dossier (Dynamic & Compact) -->
            <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-xl shadow-slate-200/20">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-hand-holding-heart text-emerald-500"></i>
                        Leave Balance & Allocation Ledgers
                    </h4>
                    <span class="text-[9px] font-black uppercase tracking-wider bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full border border-slate-200">
                        {{ selectedYear }} Season
                    </span>
                </div>
                
                <div v-if="leaveLedgers && leaveLedgers.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="ledger in leaveLedgers" :key="ledger.id" 
                        class="bg-slate-50/50 rounded-2xl p-4 border border-slate-100 relative overflow-hidden group hover:border-slate-200 transition-all duration-300">
                        <div class="absolute -right-4 -top-4 w-12 h-12 rounded-full blur-xl opacity-20 transition-colors" :style="{ backgroundColor: ledger.color }"></div>
                        
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-2.5 h-2.5 rounded-full shadow-sm" :style="{ backgroundColor: ledger.color }"></span>
                            <h5 class="text-xs font-black text-slate-800 uppercase tracking-wider truncate max-w-[150px]">{{ ledger.name }}</h5>
                            <span class="ml-auto text-[9px] font-black uppercase bg-slate-200/60 text-slate-650 px-2 py-0.5 rounded">
                                {{ ledger.code }}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-4 gap-1.5 text-center">
                            <div class="bg-white/80 p-1.5 rounded-xl border border-slate-100">
                                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider leading-none">Allowed</p>
                                <p class="text-xs font-black text-slate-700 mt-1">{{ ledger.allowed }}</p>
                            </div>
                            <div class="bg-emerald-50/30 p-1.5 rounded-xl border border-emerald-100/10">
                                <p class="text-[8px] font-bold text-emerald-600 uppercase tracking-wider leading-none">Utilized</p>
                                <p class="text-xs font-black text-emerald-700 mt-1">{{ ledger.utilized }}</p>
                            </div>
                            <div class="bg-amber-50/30 p-1.5 rounded-xl border border-amber-100/10">
                                <p class="text-[8px] font-bold text-amber-600 uppercase tracking-wider leading-none">Pending</p>
                                <p class="text-xs font-black text-amber-700 mt-1">{{ ledger.pending }}</p>
                            </div>
                            <div class="bg-rose-50/30 p-1.5 rounded-xl border border-rose-100/10"
                                 :class="ledger.remaining <= 1 ? 'bg-rose-100/20' : ''">
                                <p class="text-[8px] font-bold text-rose-650 uppercase tracking-wider leading-none">Remaining</p>
                                <p class="text-xs font-black text-rose-700 mt-1">{{ ledger.remaining.toFixed(1) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="flex flex-col items-center justify-center p-8 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">No active leave type allocations found for this year</p>
                </div>
            </div>

            <!-- working days calculation details -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Working Days So Far -->
                <div class="bg-gradient-to-br from-emerald-950 to-slate-900 border border-emerald-900/50 rounded-3xl p-6 shadow-xl text-white">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-xs font-black uppercase tracking-widest text-emerald-400">WORKING DAYS SO FAR</h4>
                        <CalendarIcon class="w-5 h-5 text-emerald-500 animate-pulse" />
                    </div>
                    <p class="text-3xl font-black text-white leading-none">{{ summary.working_days_so_far }}</p>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-3">From Jan 1st till Today</p>
                </div>

                <!-- Remaining Working Days -->
                <div class="bg-gradient-to-br from-slate-950 to-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl text-white">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-xs font-black uppercase tracking-widest text-indigo-400">REMAINING DAYS</h4>
                        <OutlinedClockIcon class="w-5 h-5 text-indigo-500" />
                    </div>
                    <p class="text-3xl font-black text-white leading-none">{{ summary.remaining_working_days }}</p>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-3">Until December 31st</p>
                </div>

                <!-- Punch timing dossier -->
                <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-xl shadow-slate-200/20 col-span-1 md:col-span-2">
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <CalendarIcon class="w-4 h-4 text-emerald-500" />
                        CALENDAR COLOR RULES & LEGEND
                    </h4>
                    <div class="grid grid-cols-2 gap-3 text-[10px] font-black uppercase tracking-wider">
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded bg-emerald-500 shrink-0"></span>
                            <span>Present / On-Time</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded bg-amber-500 shrink-0"></span>
                            <span>Late Arrival</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded bg-orange-500 shrink-0"></span>
                            <span>WFH Approved</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded bg-orange-50 border border-dashed border-orange-300 shrink-0"></span>
                            <span>WFH Pending</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded bg-purple-500 shrink-0"></span>
                            <span>Approved Leave</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded bg-purple-50 border border-dashed border-purple-300 shrink-0"></span>
                            <span>Leave Pending</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded bg-red-500 shrink-0"></span>
                            <span>Explicit Absent</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded bg-red-50 border border-dashed border-red-300 shrink-0"></span>
                            <span>Not Marked / Missing</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded bg-sky-400 shrink-0"></span>
                            <span>Paid Holiday</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded bg-slate-100 border border-slate-200 shrink-0"></span>
                            <span>Weekend Off</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded bg-white border border-dashed border-slate-300 shrink-0"></span>
                            <span>Scheduled Future Day</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Switching Layout: 12-Month Calendar vs List View vs Requests Dossier -->
            <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-xl overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center bg-slate-900 text-white gap-4">
                    <div class="flex items-center gap-3">
                        <h3 class="text-sm font-black uppercase tracking-[0.2em] text-white">YEARLY ATTENDANCE GRID</h3>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-1 bg-slate-800 p-1 rounded-xl border border-slate-700/50">
                        <button 
                            @click="activeTab = 'calendar'"
                            :class="activeTab === 'calendar' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400 hover:text-white'"
                            class="px-4 py-2 rounded-lg text-xs font-black uppercase tracking-wider transition-all"
                        >
                            12-Month Grid
                        </button>
                        <button 
                            @click="activeTab = 'list'"
                            :class="activeTab === 'list' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400 hover:text-white'"
                            class="px-4 py-2 rounded-lg text-xs font-black uppercase tracking-wider transition-all"
                        >
                            Log Journal View
                        </button>
                        <button 
                            @click="activeTab = 'requests'"
                            :class="activeTab === 'requests' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400 hover:text-white'"
                            class="px-4 py-2 rounded-lg text-xs font-black uppercase tracking-wider transition-all"
                        >
                            Requests History ({{ requestsList.length }})
                        </button>
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    <!-- 12-Month Grid View -->
                    <div v-if="activeTab === 'calendar'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                        <div 
                            v-for="month in months" 
                            :key="month.name" 
                            class="bg-slate-50/50 border border-slate-100 rounded-3xl p-5 hover:shadow-md transition-all"
                        >
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest text-center mb-4 pb-2 border-b border-slate-100">
                                {{ month.name }}
                            </h4>

                            <!-- Weekday Headers -->
                            <div class="grid grid-cols-7 gap-1 text-[9px] font-black text-slate-400 text-center uppercase tracking-widest mb-2">
                                <span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span><span>S</span>
                            </div>

                            <!-- Calendar Days -->
                            <div class="grid grid-cols-7 gap-1.5 text-center">
                                <!-- empty spaces for start offset of month -->
                                <div 
                                    v-for="offset in getStartOfWeekOffset(selectedYearState, month.index)" 
                                    :key="'offset-'+offset" 
                                    class="w-full aspect-square"
                                ></div>

                                <!-- days loop -->
                                <div 
                                    v-for="dayNum in getDaysInMonth(selectedYearState, month.index)" 
                                    :key="'day-'+dayNum"
                                    class="w-full aspect-square flex items-center justify-center text-[10px] font-black rounded-lg transition-all cursor-pointer relative"
                                    :class="getStatusColorClass(getDayRecord(month.name, dayNum)?.status)"
                                    @mouseenter="showTooltip($event, getDayRecord(month.name, dayNum))"
                                    @mouseleave="hideTooltip"
                                >
                                    {{ dayNum }}
                                </div>
                            </div>

                             <!-- Month Stats Summary counts (Compact) -->
                             <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between text-[9px] font-black tracking-wider select-none">
                                 <div class="grid grid-cols-6 gap-1 w-full text-center">
                                     <div 
                                         class="cursor-help bg-slate-50 text-slate-700 px-1 py-1 rounded border border-slate-200/50 hover:bg-slate-100 transition-colors"
                                         @mouseenter="showStatTooltip($event, 'WD', 'Total Working Days', getMonthCounts(month.name).workingDays, 'Total scheduled working days in the month (excluding weekends and holidays)', month.name)"
                                         @mouseleave="hideStatTooltip"
                                     >
                                         <span class="block text-[8px] text-slate-400 uppercase leading-none">WD</span>
                                         <span class="block mt-0.5 text-xs font-black">{{ getMonthCounts(month.name).workingDays }}</span>
                                     </div>
                                     <div 
                                         class="cursor-help bg-emerald-50 text-emerald-700 px-1 py-1 rounded border border-emerald-100/50 hover:bg-emerald-100 transition-colors"
                                         @mouseenter="showStatTooltip($event, 'PRE', 'Present Days (incl. Late)', getMonthCounts(month.name).present, getMonthCounts(month.name).presentDetails, month.name)"
                                         @mouseleave="hideStatTooltip"
                                     >
                                         <span class="block text-[8px] text-emerald-500 uppercase leading-none">PRE</span>
                                         <span class="block mt-0.5 text-xs font-black">{{ getMonthCounts(month.name).present }}</span>
                                     </div>
                                     <div 
                                         class="cursor-help bg-red-50 text-red-700 px-1 py-1 rounded border border-red-100/50 hover:bg-red-100 transition-colors"
                                         @mouseenter="showStatTooltip($event, 'ABS', 'Absent Days', getMonthCounts(month.name).absent, null, month.name)"
                                         @mouseleave="hideStatTooltip"
                                     >
                                         <span class="block text-[8px] text-red-500 uppercase leading-none">ABS</span>
                                         <span class="block mt-0.5 text-xs font-black">{{ getMonthCounts(month.name).absent }}</span>
                                     </div>
                                     <div 
                                         class="cursor-help bg-purple-50 text-purple-700 px-1 py-1 rounded border border-purple-100/50 hover:bg-purple-100 transition-colors"
                                         @mouseenter="showStatTooltip($event, 'LV', 'Leaves (Approved & Pending)', getMonthCounts(month.name).leaves, getMonthCounts(month.name).leaveDetails, month.name)"
                                         @mouseleave="hideStatTooltip"
                                     >
                                         <span class="block text-[8px] text-purple-500 uppercase leading-none">LV</span>
                                         <span class="block mt-0.5 text-xs font-black">{{ getMonthCounts(month.name).leaves }}</span>
                                     </div>
                                     <div 
                                         class="cursor-help bg-orange-50 text-orange-700 px-1 py-1 rounded border border-orange-100/50 hover:bg-orange-100 transition-colors"
                                         @mouseenter="showStatTooltip($event, 'WFH', 'WFH (Approved & Pending)', getMonthCounts(month.name).wfh, getMonthCounts(month.name).wfhDetails, month.name)"
                                         @mouseleave="hideStatTooltip"
                                     >
                                         <span class="block text-[8px] text-orange-500 uppercase leading-none">WFH</span>
                                         <span class="block mt-0.5 text-xs font-black">{{ getMonthCounts(month.name).wfh }}</span>
                                     </div>
                                     <div 
                                         class="cursor-help bg-slate-100 text-slate-700 px-1 py-1 rounded border border-slate-200/50 hover:bg-slate-200 transition-colors"
                                         @mouseenter="showStatTooltip($event, 'NM', 'Not Marked / Missing Logs', getMonthCounts(month.name).notMarked, null, month.name)"
                                         @mouseleave="hideStatTooltip"
                                     >
                                         <span class="block text-[8px] text-slate-500 uppercase leading-none">NM</span>
                                         <span class="block mt-0.5 text-xs font-black">{{ getMonthCounts(month.name).notMarked }}</span>
                                     </div>
                                 </div>
                             </div>
                        </div>
                    </div>

                    <!-- List View -->
                    <div v-else-if="activeTab === 'list'" class="space-y-6">
                        <!-- Filters Strip -->
                        <div class="flex flex-wrap gap-2 pb-4 border-b border-slate-100">
                            <button 
                                v-for="f in ['all', 'present', 'late', 'absent', 'leave', 'wfh', 'notmarked', 'off']" 
                                :key="f"
                                @click="listFilter = f"
                                :class="listFilter === f ? 'bg-slate-900 text-white font-black' : 'bg-slate-50 hover:bg-slate-100 text-slate-600'"
                                class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all"
                            >
                                {{ f === 'notmarked' ? 'not marked' : f }} logs
                            </button>
                        </div>

                        <!-- Logs list -->
                        <div class="bg-white rounded-3xl overflow-hidden border border-slate-100">
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse text-left">
                                    <thead>
                                        <tr class="bg-slate-950 text-white text-[10px] font-black uppercase tracking-[0.2em]">
                                            <th class="px-6 py-4">Date</th>
                                            <th class="px-6 py-4">Assigned Shift</th>
                                            <th class="px-6 py-4">Timing</th>
                                            <th class="px-6 py-4">Work Duration</th>
                                            <th class="px-6 py-4 text-center">Status</th>
                                            <th class="px-6 py-4 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template v-for="(day, index) in paginatedDays" :key="day.date">
                                            <tr 
                                                class="hover:bg-slate-50/50 transition-colors"
                                                :class="{'bg-red-50/20': day.status === 'Absent'}"
                                            >
                                                <td class="px-6 py-4">
                                                    <p class="text-sm font-black text-slate-880 leading-none">{{ day.day_num }} {{ day.month_name }} {{ selectedYearState }}</p>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1.5">{{ day.day_name }}</p>
                                                </td>
                                                <td class="px-6 py-4 text-xs font-black text-slate-600 uppercase tracking-wider">
                                                    {{ day.shift }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div v-if="day.check_in" class="flex flex-col gap-1">
                                                        <span class="text-xs font-black text-slate-800">IN: {{ day.check_in }}</span>
                                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">OUT: {{ day.check_out }}</span>
                                                    </div>
                                                    <span v-else class="text-xs font-black text-slate-300 uppercase tracking-widest">--:--</span>
                                                </td>
                                                <td class="px-6 py-4 text-xs font-black text-slate-800">
                                                    {{ day.work_duration || '--' }}
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <span 
                                                        :class="[
                                                            day.status === 'Present' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : '',
                                                            day.status === 'Late' ? 'bg-amber-50 text-amber-700 border-amber-100' : '',
                                                            day.status === 'Absent' ? 'bg-red-50 text-red-700 border-red-100' : '',
                                                            day.status === 'On Leave' ? 'bg-purple-50 text-purple-700 border-purple-100' : '',
                                                            day.status === 'Leave Pending' ? 'bg-purple-50 text-purple-600 border-purple-200 border-dashed animate-pulse' : '',
                                                            day.status === 'WFH Approved' ? 'bg-orange-50 text-orange-700 border-orange-100' : '',
                                                            day.status === 'WFH Pending' ? 'bg-orange-50 text-orange-600 border-orange-200 border-dashed animate-pulse' : '',
                                                            day.status === 'Weekend Off' ? 'bg-slate-50 text-slate-500 border-slate-100' : '',
                                                            day.status === 'Holiday' ? 'bg-sky-50 text-sky-700 border-sky-100' : '',
                                                            day.status === 'Not Marked' ? 'bg-red-50 text-red-750 border-red-200 border-dashed animate-pulse' : '',
                                                            day.status === 'Scheduled' ? 'bg-slate-50 text-slate-400 border-dashed border-slate-200' : '',
                                                        ]"
                                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-black border uppercase tracking-widest"
                                                    >
                                                        {{ day.status }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <button 
                                                        v-if="day.sessions && day.sessions.length > 0"
                                                        @click="toggleRow(index)"
                                                        class="text-xs font-black text-slate-500 hover:text-slate-800 uppercase tracking-widest"
                                                    >
                                                        {{ expandedRow === index ? 'Collapse' : 'Punches (' + day.sessions.length + ')' }}
                                                    </button>
                                                    <span v-else class="text-xs font-black text-slate-300 uppercase tracking-widest">No Logs</span>
                                                </td>
                                            </tr>

                                            <!-- Expanded sessions row -->
                                            <tr v-if="expandedRow === index" :key="'sessions-'+day.date" class="bg-slate-50/50">
                                                <td colspan="6" class="px-8 py-6">
                                                    <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Detailed punch telemetry sessions</h5>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                        <div 
                                                            v-for="(session, sIdx) in day.sessions" 
                                                            :key="sIdx"
                                                            class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm"
                                                        >
                                                            <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mb-3 leading-none">Session #{{ sIdx + 1 }}</p>
                                                            <div class="space-y-2 text-xs font-black text-slate-700">
                                                                <div class="flex justify-between border-b border-slate-50 pb-1">
                                                                    <span class="text-slate-400">Clock In:</span>
                                                                    <span>{{ session.in }}</span>
                                                                </div>
                                                                <div class="flex justify-between border-b border-slate-50 pb-1">
                                                                    <span class="text-slate-400">Clock Out:</span>
                                                                    <span>{{ session.out }}</span>
                                                                </div>
                                                                <div class="flex justify-between border-b border-slate-50 pb-1">
                                                                    <span class="text-slate-400">In IP:</span>
                                                                    <span class="font-mono text-[10px] text-slate-500">{{ session.in_ip }}</span>
                                                                </div>
                                                                <div class="flex justify-between">
                                                                    <span class="text-slate-400">Out IP:</span>
                                                                    <span class="font-mono text-[10px] text-slate-500">{{ session.out_ip }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>

                                        <tr v-if="filteredDays.length === 0">
                                            <td colspan="6" class="text-center py-20 text-sm font-black text-slate-400 uppercase tracking-widest">
                                                No attendance logs match the current filter
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pagination Footer Controls -->
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-slate-50 p-6 rounded-3xl border border-slate-100" v-if="totalPages > 1">
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">
                                Showing {{ ((currentPage - 1) * itemsPerPage) + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredDays.length) }} of {{ filteredDays.length }} Daily Logs
                            </span>
                            
                            <div class="flex items-center gap-2">
                                <button 
                                    @click="currentPage = Math.max(1, currentPage - 1)"
                                    :disabled="currentPage === 1"
                                    class="h-9 w-9 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-600 hover:text-emerald-600 disabled:opacity-30 disabled:pointer-events-none transition-all"
                                >
                                    <ChevronLeftIcon class="w-4 h-4" />
                                </button>

                                <span class="text-xs font-black text-slate-700 px-4">
                                    Page {{ currentPage }} of {{ totalPages }}
                                </span>

                                <button 
                                    @click="currentPage = Math.min(totalPages, currentPage + 1)"
                                    :disabled="currentPage === totalPages"
                                    class="h-9 w-9 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-600 hover:text-emerald-600 disabled:opacity-30 disabled:pointer-events-none transition-all"
                                >
                                    <ChevronRightIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Requests Dossier View -->
                    <div v-else class="space-y-6">
                        <div class="bg-white rounded-3xl overflow-hidden border border-slate-100">
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse text-left">
                                    <thead>
                                        <tr class="bg-slate-950 text-white text-[10px] font-black uppercase tracking-[0.2em]">
                                            <th class="px-6 py-4">Request Category</th>
                                            <th class="px-6 py-4">Submitted At</th>
                                            <th class="px-6 py-4">Target Date / Period</th>
                                            <th class="px-6 py-4">Duration</th>
                                            <th class="px-6 py-4 text-center">Status</th>
                                            <th class="px-6 py-4">Reason / Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr v-for="req in requestsList" :key="req.type + '-' + req.id" class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-4">
                                                <p class="text-sm font-black text-slate-800 leading-none uppercase">{{ req.category }}</p>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1.5">{{ req.type }}</p>
                                            </td>
                                            <td class="px-6 py-4 text-xs font-black text-slate-600">
                                                {{ req.submitted_at }}
                                            </td>
                                            <td class="px-6 py-4 text-xs font-black text-slate-800 uppercase tracking-wider">
                                                {{ req.date_range }}
                                            </td>
                                            <td class="px-6 py-4 text-xs font-black text-slate-600 uppercase tracking-widest">
                                                {{ req.days }}
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span 
                                                    :class="[
                                                        req.status === 'Approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : '',
                                                        req.status === 'Pending' ? 'bg-amber-50 text-amber-700 border-amber-100 animate-pulse' : '',
                                                        req.status === 'Rejected' ? 'bg-rose-50 text-rose-700 border-rose-100' : '',
                                                        req.status === 'Cancelled' ? 'bg-slate-100 text-slate-600 border-slate-200' : ''
                                                    ]"
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-black border uppercase tracking-widest"
                                                >
                                                    {{ req.status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-xs font-medium text-slate-500 max-w-xs truncate" :title="req.reason">
                                                {{ req.reason }}
                                            </td>
                                        </tr>

                                        <tr v-if="requestsList.length === 0">
                                            <td colspan="6" class="text-center py-20 text-sm font-black text-slate-400 uppercase tracking-widest">
                                                No WFH or Leave requests logged for this year
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Float Calendar hover Day Tooltip -->
        <Transition name="fade">
            <div 
                v-if="hoveredDay" 
                :style="{ top: tooltipY + 'px', left: tooltipX + 'px' }"
                class="fixed z-50 pointer-events-none bg-slate-900 border border-slate-800 text-white rounded-2xl p-4 shadow-2xl max-w-xs font-outfit"
            >
                <div class="border-b border-slate-800 pb-2 mb-2">
                    <p class="text-[10px] font-black text-emerald-400 uppercase tracking-widest leading-none">{{ hoveredDay.day_name }}, {{ hoveredDay.day_num }} {{ hoveredDay.month_name }}</p>
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mt-1.5 leading-none">Shift: {{ hoveredDay.shift }}</p>
                </div>

                <div class="space-y-1.5 text-xs font-black">
                    <div class="flex justify-between">
                        <span class="text-slate-400 uppercase tracking-widest text-[9px]">Status:</span>
                        <span class="uppercase tracking-widest text-[9px] text-emerald-400">{{ hoveredDay.status }}</span>
                    </div>

                    <!-- timing info if present -->
                    <template v-if="hoveredDay.check_in">
                        <div class="flex justify-between">
                            <span class="text-slate-400 uppercase tracking-widest text-[9px]">In Time:</span>
                            <span>{{ hoveredDay.check_in }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 uppercase tracking-widest text-[9px]">Out Time:</span>
                            <span>{{ hoveredDay.check_out }}</span>
                        </div>
                        <div v-if="hoveredDay.work_duration" class="flex justify-between">
                            <span class="text-slate-400 uppercase tracking-widest text-[9px]">Work Time:</span>
                            <span>{{ hoveredDay.work_duration }}</span>
                        </div>
                        <div v-if="hoveredDay.late_minutes > 0" class="flex justify-between text-amber-400">
                            <span class="uppercase tracking-widest text-[9px]">Late mark:</span>
                            <span>{{ hoveredDay.late_minutes }} mins</span>
                        </div>
                    </template>

                    <!-- holiday details -->
                    <div v-if="hoveredDay.status === 'Holiday' || hoveredDay.holiday_title" class="pt-1.5 border-t border-slate-800 text-[10px] text-sky-400 uppercase tracking-widest">
                        <i class="fas fa-umbrella-beach mr-1"></i>
                        {{ hoveredDay.holiday_title || 'Public Holiday' }}
                    </div>

                    <!-- leave details -->
                    <div v-if="hoveredDay.leave_title" class="pt-1.5 border-t border-slate-800 text-[10px] text-purple-400 uppercase tracking-widest">
                        <i class="fas fa-plane-departure mr-1"></i>
                        Leave: {{ hoveredDay.leave_title }}
                    </div>

                    <!-- WFH details -->
                    <div v-if="hoveredDay.wfh_status" class="pt-1.5 border-t border-slate-800 text-[10px] text-orange-400 uppercase tracking-widest">
                        <i class="fas fa-house-laptop mr-1"></i>
                        WFH: {{ hoveredDay.wfh_status }}
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Float Month Stat hover Tooltip -->
        <Transition name="fade">
            <div 
                v-if="hoveredStat" 
                :style="{ top: tooltipY + 'px', left: tooltipX + 'px' }"
                class="fixed z-50 pointer-events-none bg-slate-900 border border-slate-800 text-white rounded-2xl p-4 shadow-2xl max-w-xs font-outfit"
            >
                <div class="border-b border-slate-800 pb-2 mb-2">
                    <p class="text-[10px] font-black text-emerald-400 uppercase tracking-widest leading-none">{{ hoveredStat.monthName }} Summary</p>
                    <p class="text-xs font-black text-white mt-1.5 leading-none uppercase tracking-wide">{{ hoveredStat.title }}</p>
                </div>

                <div class="space-y-1.5 text-xs font-black">
                    <div class="flex justify-between gap-4">
                        <span class="text-slate-400 uppercase tracking-widest text-[9px]">Total Count:</span>
                        <span class="text-emerald-400 font-black text-xs">{{ hoveredStat.value }}</span>
                    </div>

                    <div v-if="hoveredStat.details" class="pt-1.5 border-t border-slate-800 text-[10px] text-slate-350">
                        <span class="block text-[8px] text-slate-500 uppercase tracking-widest leading-none mb-1 font-black">Breakdown:</span>
                        <div class="text-[10px] font-black text-slate-200 uppercase tracking-wider">
                            {{ hoveredStat.details }}
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </AttendanceLayout>
</template>

<style scoped>
.animate-content-fade {
    animation: content-in 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes content-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
