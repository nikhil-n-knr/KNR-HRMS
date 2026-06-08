<template>
  <component :is="embedded ? 'div' : AttendanceLayout" title="Live Monitoring" activeTab="monitor_view" v-bind="$props">
    <Head v-if="!embedded" title="Live Attendance Monitor" />
    
    <div :class="{'max-w-[1600px] mx-auto': !embedded}" class="space-y-8 pb-12 px-4 md:px-0">
        <!-- Compact Command Bar -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex items-center gap-4">
                <div class="p-4 bg-slate-900 border border-slate-800 rounded-2xl text-emerald-400 shadow-xl shadow-slate-200/50">
                    <svg class="w-7 h-7 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div>
                    <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                        Real-Time Attendance Monitor
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-widest">Live Tracking Active</span>
                    </h2>
                    <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mt-1">Live status tracking: {{ formatDate() }}</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:min-w-[300px] group">
                    <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search by Name or Code..."
                        class="w-full bg-white border-none rounded-2xl py-3.5 pl-11 pr-4 text-base font-black text-slate-700 focus:ring-4 focus:ring-emerald-500/10 transition-all uppercase tracking-widest placeholder:text-slate-300 shadow-sm"
                    >
                </div>
                <!-- Layout Mode & Actions -->
                <div class="flex items-center gap-2 shrink-0 justify-end">
                    <button 
                        @click="viewMode = 'grid'" 
                        :class="viewMode === 'grid' ? 'bg-slate-900 text-white border-slate-900 shadow-lg' : 'bg-white text-slate-400 border-gray-100 hover:text-emerald-600'"
                        class="h-12 w-12 rounded-2xl flex items-center justify-center border shadow-sm transition-all active:scale-95 shrink-0"
                        title="Grid View"
                    >
                        <i class="fas fa-th-large text-lg"></i>
                    </button>
                    <button 
                        @click="viewMode = 'table'" 
                        :class="viewMode === 'table' ? 'bg-slate-900 text-white border-slate-900 shadow-lg' : 'bg-white text-slate-400 border-gray-100 hover:text-emerald-600'"
                        class="h-12 w-12 rounded-2xl flex items-center justify-center border shadow-sm transition-all active:scale-95 shrink-0"
                        title="Table View"
                    >
                        <i class="fas fa-list text-lg"></i>
                    </button>
                    <button 
                        @click="exportLiveReport"
                        class="h-12 px-4 bg-white text-slate-500 hover:text-emerald-600 hover:border-emerald-100 rounded-2xl flex items-center justify-center gap-2 border border-gray-100 shadow-sm transition-all active:scale-95 shrink-0"
                        title="Export Live Report"
                    >
                        <i class="fas fa-file-excel text-lg text-emerald-600"></i>
                        <span class="text-xs font-black uppercase tracking-widest">Export Live</span>
                    </button>
                    <button 
                        @click="fetchData"
                        class="h-12 w-12 bg-white text-slate-400 rounded-2xl flex items-center justify-center border border-gray-100 shadow-sm hover:text-emerald-600 hover:border-emerald-100 transition-all active:scale-90 group shrink-0"
                        :class="{ 'pointer-events-none opacity-50': loading }"
                        title="Sync Live Data"
                    >
                        <svg class="w-5 h-5 group-hover:rotate-180 transition-transform duration-500" :class="{ 'animate-spin text-emerald-500': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- High-Density Intelligence Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            <div v-for="stat in statsCards" :key="stat.label" class="bg-white/60 backdrop-blur-xl rounded-2xl p-5 md:p-6 border border-gray-200 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                <div :class="['absolute right-0 top-0 h-full w-1.5 transition-all duration-500 group-hover:w-2', stat.accent]"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div :class="['w-12 h-12 rounded-xl flex items-center justify-center text-lg shadow-sm border border-white transition-all group-hover:bg-slate-900 group-hover:text-white duration-500 shrink-0', stat.bg, stat.color]">
                        <i :class="stat.icon"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] leading-none mb-2 truncate">{{ stat.label }}</p>
                        <h3 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tighter tabular-nums leading-none transition-colors truncate">{{ stat.value }}</h3>
                    </div>
                </div>
                <div class="mt-6 flex items-center gap-3 relative z-10">
                    <div class="flex-1 h-1.5 bg-slate-100/50 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full transition-all duration-1000 shadow-[0_0_8px_rgba(16,185,129,0.3)]" :style="{ width: stat.percent + '%' }"></div>
                    </div>
                    <span class="text-sm font-black text-emerald-600 tabular-nums uppercase tracking-widest shrink-0">{{ stat.percent }}%</span>
                </div>
            </div>
        </div>

        <!-- Employee Monitoring Grid (Default View Mode) -->
        <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <TransitionGroup name="list">
                <div 
                    v-for="employee in filteredEmployees" 
                    :key="employee.id" 
                    @click="openDetails(employee)"
                    class="bg-white/80 backdrop-blur-xl rounded-3xl border border-gray-100 p-5 shadow-sm hover:shadow-2xl hover:shadow-emerald-500/10 transition-all duration-500 cursor-pointer group flex flex-col relative overflow-hidden"
                >
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    
                    <!-- Header -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="relative shrink-0">
                                <div class="w-14 h-14 rounded-2xl bg-slate-900 border-2 border-white flex items-center justify-center text-white font-black text-lg uppercase overflow-hidden shadow-xl group-hover:bg-emerald-600 transition-all group-hover:-translate-y-1">
                                    <template v-if="employee.avatar">
                                        <img :src="employee.avatar" class="w-full h-full object-cover">
                                    </template>
                                    <span v-else>{{ employee.first_name ? employee.first_name[0] : (employee.name ? employee.name[0] : '?') }}</span>
                                </div>
                                <div 
                                    class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-4 border-white shadow-lg"
                                    :class="getStatusColor(employee)"
                                ></div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-lg font-black text-slate-900 leading-tight group-hover:text-emerald-700 transition-colors truncate uppercase tracking-tight">{{ employee.first_name || employee.name }} {{ employee.last_name || '' }}</h4>
                                <div class="flex items-center gap-1.5 mt-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                                    <p class="text-sm font-black text-gray-400 uppercase tracking-widest truncate leading-none">{{ employee.department?.name || employee.department || 'Operations' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                             <div class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest border shadow-sm transition-all" 
                                  :class="employee.status === 'Late' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'">
                                {{ employee.status }}
                             </div>
                        </div>
                    </div>

                    <!-- Shift Detail -->
                    <div class="mt-6 p-4 rounded-2xl bg-slate-50 border border-slate-100 group-hover:bg-emerald-50 group-hover:border-emerald-100 transition-all shadow-inner">
                        <div class="flex justify-between items-center text-sm font-black uppercase tracking-widest leading-none gap-2 mb-4">
                            <span class="text-slate-400 truncate">{{ employee.current_shift?.name || employee.shift || 'Standard' }}</span>
                            <span class="text-emerald-600 tabular-nums tracking-tighter shrink-0 bg-white px-2 py-1 rounded-lg border border-emerald-50 shadow-sm">
                                {{ employee.check_in || '--:--' }} &rarr; {{ employee.check_out || '--:--' }}
                            </span>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="relative pt-1">
                            <div class="h-1.5 bg-slate-200/50 rounded-full overflow-hidden">
                                <div 
                                    class="h-full bg-emerald-500 rounded-full transition-all duration-1000 shadow-[0_0_12px_rgba(16,185,129,0.5)]"
                                    :style="{ width: getProgress(employee) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Footer -->
                    <div class="mt-6 flex items-center justify-between border-t border-gray-50 pt-4">
                        <div class="flex items-center gap-6">
                            <div class="text-left">
                                <p class="text-xs font-black text-slate-300 uppercase leading-none tracking-widest mb-2.5 px-0.5">Code</p>
                                <p class="text-xs font-black text-slate-800 uppercase tracking-tighter leading-none group-hover:text-emerald-600 transition-colors">{{ employee.employee_code || 'N/A' }}</p>
                            </div>
                            <div class="text-left">
                                <p class="text-xs font-black text-slate-300 uppercase leading-none tracking-widest mb-2.5 px-0.5">Runtime</p>
                                <p class="text-xs font-black text-slate-800 uppercase tracking-tighter leading-none tabular-nums">{{ employee.total_hours || employee.work_duration_human || '0.0h' }}</p>
                            </div>
                        </div>
                        
                        <div class="w-10 h-10 rounded-xl bg-slate-900 group-hover:bg-emerald-600 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 translate-x-4 group-hover:translate-x-0 transition-all shadow-xl shadow-emerald-200/50 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </div>
                    </div>
                </div>
            </TransitionGroup>
        </div>

        <!-- Employee Monitoring Table (Toggleable View Mode) -->
        <div v-else-if="viewMode === 'table' && filteredEmployees.length > 0" class="bg-white/80 backdrop-blur-xl rounded-3xl border border-gray-100 shadow-xl overflow-hidden animate-in fade-in duration-300">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-900 text-white text-xs font-black uppercase tracking-widest">
                            <th class="py-5 px-6">Employee</th>
                            <th class="py-5 px-6">Dept / Designation</th>
                            <th class="py-5 px-6">Shift</th>
                            <th class="py-5 px-6">Check In</th>
                            <th class="py-5 px-6 text-center">Status</th>
                            <th class="py-5 px-6">Runtime</th>
                            <th class="py-5 px-6">Device & IP</th>
                            <th class="py-5 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm font-black text-slate-700">
                        <tr v-for="employee in filteredEmployees" :key="employee.id" class="hover:bg-emerald-50/50 transition-colors group">
                            <td class="py-4 px-6 flex items-center gap-3">
                                <div class="relative shrink-0">
                                    <div class="w-10 h-10 rounded-xl bg-slate-900 border-2 border-white flex items-center justify-center text-white font-black text-xs uppercase overflow-hidden shadow">
                                        <template v-if="employee.avatar">
                                            <img :src="employee.avatar" class="w-full h-full object-cover">
                                        </template>
                                        <span v-else>{{ employee.first_name ? employee.first_name[0] : (employee.name ? employee.name[0] : '?') }}</span>
                                    </div>
                                    <div class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full border-2 border-white shadow" :class="getStatusColor(employee)"></div>
                                </div>
                                <div>
                                    <p class="font-black text-slate-950 uppercase tracking-tight group-hover:text-emerald-700 transition-colors">{{ employee.first_name || employee.name }} {{ employee.last_name || '' }}</p>
                                    <p class="text-xs font-black text-slate-400 tracking-wider uppercase mt-0.5">{{ employee.employee_code || 'N/A' }}</p>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-slate-800 uppercase text-xs font-black">{{ employee.department?.name || employee.department || 'Operations' }}</p>
                                <p class="text-xs text-slate-400 font-bold uppercase mt-0.5">{{ employee.designation || 'Employee' }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-xs text-slate-600 border border-slate-200 uppercase tracking-wider font-black">{{ employee.current_shift?.name || employee.shift || 'Standard' }}</span>
                            </td>
                            <td class="py-4 px-6 font-mono text-xs text-slate-800">
                                {{ employee.check_in || '--:--' }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-black uppercase tracking-wider border shadow-sm" 
                                      :class="employee.status === 'Late' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'">
                                    {{ employee.status }}
                                </span>
                            </td>
                            <td class="py-4 px-6 font-mono text-xs text-slate-800">
                                {{ employee.total_hours || employee.work_duration_human || '0.0h' }}
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-xs text-slate-800 uppercase tracking-wider flex items-center gap-1.5"><i class="fas fa-desktop text-slate-400"></i> {{ employee.device || 'Desktop' }}</p>
                                <p class="text-xs text-slate-400 font-mono mt-0.5">{{ employee.ip || '-' }}</p>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <button 
                                    @click="openDetails(employee)" 
                                    class="px-4 py-2 bg-slate-900 hover:bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow hover:shadow-lg transition-all active:scale-95"
                                >
                                    Details
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Compact Zero State -->
        <div v-if="filteredEmployees.length === 0 && !loading" class="flex flex-col items-center justify-center py-40 space-y-8 animate-in fade-in zoom-in duration-700">
            <div class="w-32 h-32 bg-slate-50 rounded-[3rem] flex items-center justify-center text-slate-200 border border-slate-100 shadow-inner group transition-all relative">
                <div class="absolute inset-0 rounded-[3rem] border-2 border-emerald-500/10 animate-ping"></div>
                <svg class="w-16 h-16 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <div class="text-center">
                <h3 class="text-lg font-black text-slate-800 uppercase tracking-[0.2em]">Zero Signal Detected</h3>
                <p class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] mt-4 max-w-xs mx-auto leading-relaxed">Adjust tracking parameters or check global hardware synchronization status</p>
            </div>
            <button @click="searchQuery = ''" class="px-10 py-4 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] shadow-2xl shadow-slate-200 hover:bg-emerald-600 active:scale-95 transition-all">
                Reset Matrix Flow
            </button>
        </div>
    </div>

    <!-- Custom Yearly Details Modal -->
    <div v-if="modalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] w-full max-w-5xl shadow-2xl border border-slate-100 flex flex-col max-h-[90vh] overflow-hidden animate-in zoom-in-95 duration-300">
            <!-- Modal Header -->
            <div class="px-8 py-6 border-b border-gray-100 bg-slate-900 text-white flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shrink-0">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 bg-emerald-500 text-white rounded-lg text-xs font-black tracking-widest uppercase">{{ detailsData.employee?.code }}</span>
                        <h3 class="text-xl font-black uppercase tracking-tight">{{ detailsData.employee?.name }}</h3>
                    </div>
                    <p class="text-xs text-slate-300 font-bold uppercase tracking-wider mt-1.5">{{ detailsData.employee?.designation }} &bull; {{ detailsData.employee?.department }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <!-- Year Selector -->
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-black uppercase tracking-widest text-slate-300">Select Year:</label>
                        <select 
                            v-model="selectedYear"
                            @change="fetchYearlyDetails"
                            class="bg-slate-800 text-white border-none rounded-xl py-2 px-4 text-sm font-black focus:ring-2 focus:ring-emerald-500 cursor-pointer"
                        >
                            <option v-for="y in yearsList" :key="y" :value="y">{{ y }}</option>
                        </select>
                    </div>
                    <!-- Export Button -->
                    <button 
                        @click="exportYearlyReport"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow transition-all active:scale-95 flex items-center gap-2"
                    >
                        <i class="fas fa-file-excel text-sm"></i>
                        Export Excel
                    </button>
                    <!-- Close Button -->
                    <button 
                        @click="closeModal"
                        class="h-10 w-10 bg-slate-800 hover:bg-slate-700 text-white rounded-xl flex items-center justify-center transition-all active:scale-95 shrink-0"
                    >
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>
            
            <!-- Modal Body (Scrollable) -->
            <div class="p-8 overflow-y-auto space-y-8" v-if="!modalLoading">
                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Attended -->
                    <div class="bg-indigo-50/50 border border-indigo-100 rounded-2xl p-5 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-indigo-500 text-white flex items-center justify-center text-lg shrink-0"><i class="fas fa-calendar-check"></i></div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-indigo-400">Attended Days</p>
                            <h4 class="text-lg font-black text-slate-900 mt-1 truncate">{{ detailsData.summary?.days_attended }} Days</h4>
                        </div>
                    </div>
                    <!-- Leaves -->
                    <div class="bg-rose-50/50 border border-rose-100 rounded-2xl p-5 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-rose-500 text-white flex items-center justify-center text-lg shrink-0"><i class="fas fa-plane-departure"></i></div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-rose-400">Leaves Appr/Pend</p>
                            <h4 class="text-lg font-black text-slate-900 mt-1 truncate">{{ detailsData.summary?.approved_leave_days }}d / {{ detailsData.summary?.pending_leave_days }}d</h4>
                        </div>
                    </div>
                    <!-- WFH -->
                    <div class="bg-amber-50/50 border border-amber-100 rounded-2xl p-5 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg shrink-0"><i class="fas fa-home-laptop"></i></div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-amber-400">WFH Appr/Pend</p>
                            <h4 class="text-lg font-black text-slate-900 mt-1 truncate">{{ detailsData.summary?.approved_wfh_days }}d / {{ detailsData.summary?.pending_wfh_days }}d</h4>
                        </div>
                    </div>
                    <!-- Restricted Holidays -->
                    <div class="bg-emerald-50/50 border border-emerald-100 rounded-2xl p-5 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-lg shrink-0"><i class="fas fa-umbrella-beach"></i></div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-emerald-400">RH Appr/Pend</p>
                            <h4 class="text-lg font-black text-slate-900 mt-1 truncate">{{ detailsData.summary?.approved_rh }}d / {{ detailsData.summary?.pending_rh }}d</h4>
                        </div>
                    </div>
                </div>

                <!-- Statistics Details -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-50 border border-slate-100 rounded-2xl p-6">
                    <div class="text-center md:text-left md:border-r border-slate-200 last:border-0 p-2">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Average Check-In</p>
                        <h4 class="text-2xl font-black text-slate-900 mt-2 font-mono">{{ detailsData.summary?.avg_in_time }}</h4>
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider mt-1">Calculated from first punch of day</p>
                    </div>
                    <div class="text-center md:text-left md:border-r border-slate-200 last:border-0 p-2">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Average Cheat Time (Deviation)</p>
                        <h4 class="text-2xl font-black text-rose-600 mt-2 font-mono">{{ detailsData.summary?.avg_cheat_time }}</h4>
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider mt-1">Average Late Arrival + Early Exit mins</p>
                    </div>
                    <div class="text-center md:text-left p-2">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Average Hours Spent</p>
                        <h4 class="text-2xl font-black text-emerald-600 mt-2 font-mono">{{ detailsData.summary?.avg_work_hours }}</h4>
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider mt-1">Range: {{ detailsData.summary?.min_work_hours }} (Min) to {{ detailsData.summary?.max_work_hours }} (Max)</p>
                    </div>
                </div>

                <!-- Daily logs table -->
                <div class="space-y-4">
                    <h4 class="text-sm font-black uppercase tracking-widest text-slate-800 flex items-center gap-2">
                        <i class="fas fa-list text-emerald-500"></i>
                        Detailed Yearly Log
                    </h4>
                    
                    <div class="border border-slate-100 rounded-2xl overflow-hidden shadow-sm">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-100 text-slate-700 text-xs font-black uppercase tracking-wider">
                                    <th class="py-4 px-6">Date</th>
                                    <th class="py-4 px-6">Shift</th>
                                    <th class="py-4 px-6">Status</th>
                                    <th class="py-4 px-6">Punches (In &rarr; Out)</th>
                                    <th class="py-4 px-6">Runtime</th>
                                    <th class="py-4 px-6 text-right">Deviation</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs font-bold text-slate-600">
                                <template v-for="(log, idx) in detailsData.logs" :key="log.date">
                                    <!-- Main Row -->
                                    <tr class="hover:bg-slate-50 transition-colors cursor-pointer" @click="toggleRow(idx)">
                                        <td class="py-4 px-6 font-mono text-slate-900 font-black">
                                            <div class="flex items-center gap-2">
                                                <i class="fas" :class="expandedRows.includes(idx) ? 'fa-chevron-down text-emerald-500' : 'fa-chevron-right text-slate-400'"></i>
                                                {{ log.date }}
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">{{ log.shift }}</td>
                                        <td class="py-4 px-6">
                                            <span class="px-2.5 py-0.5 rounded-full font-black uppercase tracking-wider text-[10px] border shadow-inner"
                                                  :class="log.status === 'Late' ? 'bg-amber-50 text-amber-600 border-amber-100' : (log.status === 'Absent' ? 'bg-rose-50 text-rose-600 border-rose-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100')">
                                                {{ log.status }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 font-mono font-medium">
                                            {{ log.check_in }} &rarr; {{ log.check_out }}
                                        </td>
                                        <td class="py-4 px-6 font-mono text-slate-900 font-black">{{ log.work_duration }}</td>
                                        <td class="py-4 px-6 text-right font-mono">
                                            <span v-if="log.late_minutes > 0" class="text-rose-600 block">Late: +{{ log.late_minutes }}m</span>
                                            <span v-if="log.early_exit_minutes > 0" class="text-amber-600 block">Early Exit: -{{ log.early_exit_minutes }}m</span>
                                            <span v-if="log.late_minutes === 0 && log.early_exit_minutes === 0" class="text-slate-400">-</span>
                                        </td>
                                    </tr>
                                    <!-- Detailed sessions sub-table -->
                                    <tr v-if="expandedRows.includes(idx)" class="bg-slate-50/50 animate-in fade-in slide-in-from-top-2 duration-300">
                                        <td colspan="6" class="p-6">
                                            <div class="bg-white rounded-xl border border-slate-100 p-4 space-y-3 shadow-inner">
                                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Punch Session History</p>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4" v-if="log.sessions.length > 0">
                                                    <div v-for="(session, sIdx) in log.sessions" :key="sIdx" class="bg-slate-50 border border-slate-100 p-3 rounded-lg flex flex-col justify-between">
                                                        <p class="text-[10px] font-black uppercase text-slate-400">Punch #{{ sIdx + 1 }}</p>
                                                        <div class="flex justify-between items-center mt-2 font-mono text-slate-800 text-xs">
                                                            <span>In: {{ session.in }}</span>
                                                            <span>Out: {{ session.out }}</span>
                                                        </div>
                                                        <div class="mt-2 pt-2 border-t border-slate-200/50 text-[9px] text-slate-400 flex flex-col gap-0.5">
                                                            <span>In IP: {{ session.in_ip }}</span>
                                                            <span>Out IP: {{ session.out_ip }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p v-else class="text-xs text-slate-400 italic">No detailed sessions captured for this day.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr v-if="detailsData.logs?.length === 0">
                                    <td colspan="6" class="py-10 text-center text-slate-400 italic">No attendance records found for this year.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Loading State -->
            <div class="flex-1 flex flex-col items-center justify-center py-40 space-y-4" v-else>
                <i class="fas fa-circle-notch fa-spin text-3xl text-emerald-500"></i>
                <p class="text-xs font-black uppercase tracking-widest text-slate-400">Retrieving yearly logs archive...</p>
            </div>
        </div>
    </div>
  </component>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { router, Head, usePage } from '@inertiajs/vue3';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    embedded: Boolean
});

const loading = ref(true);
const employees = ref([]);
const searchQuery = ref('');
const stats = ref({
    total: 0,
    present: 0,
    late: 0,
    absent: 0
});

// Layout state
const viewMode = ref('grid');

// Modal states
const modalOpen = ref(false);
const modalLoading = ref(false);
const selectedEmployee = ref(null);
const selectedYear = ref(new Date().getFullYear());
const detailsData = ref({});
const expandedRows = ref([]);

const yearsList = computed(() => {
    const current = new Date().getFullYear();
    return [current, current - 1, current - 2];
});

const statsCards = computed(() => [
    { label: 'Total Employees', value: stats.value.total, icon: 'fas fa-users-viewfinder', bg: 'bg-indigo-50', color: 'text-indigo-600', accent: 'bg-gradient-to-b from-indigo-400 to-indigo-600', percent: 100 },
    { label: 'Active Employees', value: stats.value.present || stats.value.active, icon: 'fas fa-satellite-dish', bg: 'bg-emerald-50', color: 'text-emerald-600', accent: 'bg-gradient-to-b from-emerald-400 to-emerald-600', percent: Math.round(((stats.value.present || stats.value.active) / (stats.value.total || 1)) * 100) },
    { label: 'Late Arrivals', value: stats.value.late, icon: 'fas fa-user-clock', bg: 'bg-amber-50', color: 'text-amber-600', accent: 'bg-gradient-to-b from-amber-400 to-amber-600', percent: Math.round((stats.value.late / (stats.value.total || 1)) * 100) },
    { label: 'Off-Duty / Absent', value: stats.value.absent || (stats.value.total - (stats.value.present || stats.value.active)), icon: 'fas fa-user-slash', bg: 'bg-rose-50', color: 'text-rose-600', accent: 'bg-gradient-to-b from-rose-400 to-rose-600', percent: Math.round(((stats.value.absent || (stats.value.total - (stats.value.present || stats.value.active))) / (stats.value.total || 1)) * 100) },
]);

const fetchData = async () => {
    loading.value = true;
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const params = {};
        if (urlParams.has('date_from')) params.date_from = urlParams.get('date_from');
        if (urlParams.has('department_id')) params.department_id = urlParams.get('department_id');
        if (urlParams.has('location_id')) params.location_id = urlParams.get('location_id');
        
        const response = await axios.get(route('admin.attendance.monitoring.data'), { params });
        employees.value = response.data.employees;
        stats.value = {
            total: response.data.stats.total || 0,
            present: response.data.stats.present || response.data.stats.active || 0,
            late: response.data.stats.late || 0,
            absent: response.data.stats.absent || (response.data.stats.total - (response.data.stats.present || response.data.stats.active)) || 0
        };
    } catch (error) {
        console.error('Failed to sync live data', error);
    } finally {
        loading.value = false;
    }
};

const filteredEmployees = computed(() => {
    if (!searchQuery.value) return employees.value;
    const q = searchQuery.value.toLowerCase();
    return employees.value.filter(e => {
        const name = (e.first_name || e.name || '').toLowerCase();
        const lastName = (e.last_name || '').toLowerCase();
        const code = (e.employee_code || '').toLowerCase();
        return name.includes(q) || lastName.includes(q) || code.includes(q);
    });
});

const getStatusColor = (emp) => {
    if (emp.status && emp.status.includes('Present')) return 'bg-emerald-500 shadow-[0_0_12px_rgba(16,185,129,0.5)]';
    if (emp.status && emp.status.includes('Active')) return 'bg-emerald-500 shadow-[0_0_12px_rgba(16,185,129,0.5)]';
    if (emp.status && emp.status.includes('Late')) return 'bg-amber-500 shadow-[0_0_12px_rgba(245,158,11,0.5)]';
    return 'bg-slate-300 shadow-sm';
};

const getProgress = (emp) => {
    if (emp.progress) return emp.progress;
    if (!emp.check_in) return 0;
    const hours = parseFloat(emp.total_hours || emp.work_duration_human || 0);
    return Math.min(Math.max(hours * 12.5, 10), 100);
};

const formatDate = () => new Date().toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });

// Modal methods
const openDetails = (employee) => {
    const params = { employee: employee.id };
    const url = usePage().url;
    if (url.includes('hub=1') || url.includes('hub=true')) {
        params.hub = 1;
    }
    router.visit(route('admin.attendance.monitoring.employee-details-page', params));
};

const closeModal = () => {
    modalOpen.value = false;
    selectedEmployee.value = null;
    detailsData.value = {};
    expandedRows.value = [];
};

const fetchYearlyDetails = async () => {
    if (!selectedEmployee.value) return;
    modalLoading.value = true;
    try {
        const response = await axios.get(route('admin.attendance.monitoring.employee-details', { 
            employee: selectedEmployee.value.id 
        }), {
            params: { year: selectedYear.value }
        });
        detailsData.value = response.data;
        expandedRows.value = [];
    } catch (e) {
        console.error('Failed to load employee details', e);
    } finally {
        modalLoading.value = false;
    }
};

const toggleRow = (idx) => {
    if (expandedRows.value.includes(idx)) {
        expandedRows.value = expandedRows.value.filter(x => x !== idx);
    } else {
        expandedRows.value.push(idx);
    }
};

const exportYearlyReport = () => {
    if (!selectedEmployee.value) return;
    const exportUrl = route('admin.attendance.monitoring.employee-details.export', { 
        employee: selectedEmployee.value.id 
    }) + `?year=${selectedYear.value}`;
    window.open(exportUrl, '_blank');
};

const exportLiveReport = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const params = {};
    if (urlParams.has('date_from')) params.date_from = urlParams.get('date_from');
    if (urlParams.has('department_id')) params.department_id = urlParams.get('department_id');
    if (urlParams.has('location_id')) params.location_id = urlParams.get('location_id');
    const queryString = new URLSearchParams(params).toString();
    window.open(`${route('admin.attendance.monitoring.export-live')}?${queryString}`, '_blank');
};

let timer;
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('search')) {
        searchQuery.value = urlParams.get('search');
    }
    fetchData();
    timer = setInterval(fetchData, 30000);
});

onUnmounted(() => clearInterval(timer));
</script>

<style scoped>
.list-move,
.list-enter-active,
.list-leave-active {
  transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: scale(0.9) translateY(30px);
}

.list-leave-active {
  position: absolute;
}
</style>
