<template>
  <component :is="embedded ? 'div' : AttendanceLayout" title="Timesheet Hub" activeTab="timesheets" v-bind="$props">
    <Head v-if="!embedded" title="Timesheet Pulse" />
    
    <div :class="{'max-w-[1600px] mx-auto': !embedded}" class="space-y-8 pb-12 px-4 md:px-0 font-outfit">
        <!-- Specialized Command Header -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="flex items-center gap-5">
                <div class="p-4 bg-slate-900 border border-slate-800 rounded-2xl text-emerald-400 shadow-xl shadow-slate-200/50 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <svg class="w-7 h-7 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                        Timesheet Hub
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-widest">Temporal Log</span>
                    </h2>
                    <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mt-1">Strategic workload analysis & temporal registry</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3 w-full lg:w-auto">
                <button @click="exportData" class="h-12 w-12 bg-white text-slate-400 rounded-2xl flex items-center justify-center border border-gray-100 shadow-sm hover:text-emerald-600 hover:border-emerald-100 transition-all active:scale-90 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                </button>
                <button @click="openCreateModal" class="flex-1 lg:flex-none flex items-center justify-center gap-3 bg-slate-900 text-white h-12 px-8 rounded-2xl hover:bg-emerald-600 transition-all text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 active:scale-95 group">
                    <svg class="w-4 h-4 text-emerald-400 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Log Temporal Entry</span>
                </button>
            </div>
        </div>

        <!-- Standardized Filter Array -->
        <div class="bg-white/40 backdrop-blur-xl border border-white p-1.5 rounded-3xl shadow-sm">
            <EmployeeFilterBar 
                :departments="departments" 
                :locations="locations || []"
                @update="handleFilterUpdate"
            >
                <template #extra>
                    <div class="flex flex-col lg:flex-row gap-3 items-stretch lg:items-center flex-1 lg:flex-none">
                        <div class="flex items-center gap-3 bg-white rounded-xl px-4 h-11 shadow-sm border border-transparent focus-within:ring-4 focus-within:ring-emerald-500/10 transition-all">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"></path></svg>
                            <div class="flex items-center gap-2">
                                <input type="date" v-model="filterForm.start_date" class="bg-transparent border-none p-0 text-sm font-black text-slate-700 focus:ring-0 uppercase h-full w-28">
                                <span class="text-slate-200 text-sm font-black mx-1 opacity-50">/</span>
                                <input type="date" v-model="filterForm.end_date" class="bg-transparent border-none p-0 text-sm font-black text-slate-700 focus:ring-0 uppercase h-full w-28">
                            </div>
                        </div>
                        <button @click="fetchData(1)" class="h-11 w-11 rounded-xl bg-slate-900 text-white hover:bg-emerald-600 transition-all active:scale-95 shadow-lg shadow-slate-200 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        </button>
                    </div>
                </template>
            </EmployeeFilterBar>
        </div>

            <!-- Main Data Container -->
        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-2xl shadow-slate-200/50 overflow-hidden min-h-[400px] relative">
            <div v-if="loading" class="absolute inset-0 bg-white/60 backdrop-blur-md flex items-center justify-center z-50">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-12 h-12 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin shadow-xl"></div>
                    <span class="text-sm font-black text-emerald-600 uppercase tracking-[0.3em]">Syncing Temporal Data...</span>
                </div>
            </div>

            <!-- Desktop View -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-900 border-b border-slate-800">
                            <th class="px-6 py-5 text-left w-32">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Anchor</span>
                            </th>
                            <th class="px-6 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Active Member</span>
                            </th>
                            <th class="px-6 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Project & Engagement</span>
                            </th>
                            <th class="px-6 py-5 text-left w-32">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Load Gauge</span>
                            </th>
                            <th class="px-6 py-5 text-center w-32">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Validation</span>
                            </th>
                            <th class="px-6 py-5 text-right w-32">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Command</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="!timesheets?.data || timesheets.data.length === 0" class="text-center">
                            <td colspan="6" class="p-32">
                                 <div class="flex flex-col items-center gap-4 opacity-20 grayscale">
                                    <svg class="w-16 h-16 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">No Temporal Nodes Located</span>
                                </div>
                            </td>
                        </tr>
                        
                        <tr v-for="row in (timesheets?.data || [])" :key="row.id" class="group hover:bg-slate-50 transition-all duration-300">
                            <td class="px-6 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-2xl bg-slate-100 flex flex-col items-center justify-center leading-none text-slate-500 shadow-inner group-hover:bg-emerald-50 transition-colors">
                                        <span class="text-xs font-black uppercase text-slate-400 mb-1 leading-none">{{ new Date(row.date).toLocaleString('en-US', { month: 'short' }) }}</span>
                                        <span class="text-sm font-black text-slate-900 tabular-nums leading-none">{{ row.date.split('-')[2] }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-2xl bg-slate-900 border-2 border-white flex items-center justify-center text-white font-black text-xs shrink-0 shadow-lg group-hover:bg-emerald-600 transition-all overflow-hidden">
                                        <img v-if="row.employee?.avatar" :src="row.employee.avatar" class="w-full h-full object-cover">
                                        <span v-else>{{ row.employee?.first_name[0] }}{{ row.employee?.last_name[0] }}</span>
                                    </div>
                                    <div class="truncate max-w-[180px]">
                                        <div class="text-base font-black text-slate-900 uppercase tracking-tight truncate leading-none group-hover:text-emerald-700 transition-colors">{{ row.employee?.first_name }} {{ row.employee?.last_name }}</div>
                                        <div class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] truncate mt-2.5 leading-none">{{ row.employee?.department?.name ?? 'OPS_SYNC' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="max-w-[300px] space-y-2.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base font-black text-emerald-600 tracking-widest uppercase leading-none">{{ row.project_name }}</span>
                                        <span v-if="row.is_billable" class="px-2 py-0.5 bg-emerald-50 text-emerald-500 text-xs font-black rounded border border-emerald-100 uppercase tracking-widest leading-none shadow-xs">Billable</span>
                                    </div>
                                    <div class="text-sm text-slate-500 font-bold line-clamp-1 uppercase tracking-tight leading-none italic opacity-70">" {{ row.task_description }} "</div>
                                </div>
                            </td>
                            <td class="px-6 py-6 transition-all group-hover:scale-105 origin-left">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-xs font-black text-slate-900 font-mono tracking-tighter tabular-nums">{{ row.hours_spent }}</span>
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Hrs</span>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <span class="px-4 py-1.5 text-xs font-black rounded-full border uppercase tracking-widest shadow-sm transition-all" :class="getStatusStyles(row.status)">
                                    {{ row.status }}
                                </span>
                            </td>
                            <td class="px-6 py-6 text-right">
                                <div v-if="row.status === 'Submitted'" class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                    <button @click="approveTimesheet(row.id)" class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm active:scale-95" title="Authorize">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                    <button @click="rejectTimesheet(row.id)" class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-sm active:scale-95" title="Reject">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                                <div v-else class="flex flex-col items-end opacity-40 grayscale group-hover:grayscale-0 group-hover:opacity-100 transition-all">
                                    <span class="text-xs font-black text-slate-300 uppercase tracking-[0.2em] mb-1">Validated</span>
                                    <svg class="w-4 h-4 text-emerald-500/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile View -->
            <div class="lg:hidden divide-y divide-gray-50 bg-slate-50/50">
                <div v-if="!timesheets?.data || timesheets.data.length === 0" class="p-20 text-center animate-in fade-in zoom-in duration-700">
                    <svg class="w-12 h-12 mx-auto text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">No Temporal Nodes Located</p>
                </div>
                <div v-for="row in (timesheets?.data || [])" :key="'mb-'+row.id" class="p-5 space-y-5 group relative overflow-hidden bg-white hover:bg-slate-50 transition-all active:scale-[0.98]">
                    <!-- MRT Header -->
                    <div class="flex items-start justify-between relative z-10">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="w-12 h-12 rounded-2xl bg-slate-900 border-2 border-white flex flex-col items-center justify-center text-white shadow-lg shrink-0 group-hover:bg-emerald-600 transition-all font-black">
                                <span class="text-xs font-black uppercase text-slate-400 mb-1 leading-none">{{ new Date(row.date).toLocaleString('en-US', { month: 'short' }) }}</span>
                                <span class="text-xs font-black leading-none">{{ row.date.split('-')[2] }}</span>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight leading-none truncate">{{ row.employee?.first_name }} {{ row.employee?.last_name }}</h4>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-widest">#{{ String(row.id).padStart(4, '0') }} &bull; {{ row.employee?.department?.name ?? 'OPS_SYNC' }}</span>
                                </div>
                            </div>
                        </div>
                        <span class="px-3 py-1 text-xs font-black rounded-full border uppercase tracking-widest shadow-sm" :class="getStatusStyles(row.status)">
                            {{ row.status }}
                        </span>
                    </div>

                    <!-- Engagement Detail -->
                    <div class="bg-emerald-50/40 p-4 rounded-2xl border border-emerald-100/50 group-hover:bg-white transition-all relative z-10 shadow-inner">
                        <div class="flex items-center justify-between mb-4">
                             <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                                <span class="text-sm font-black text-slate-700 tracking-widest uppercase">{{ row.project_name }}</span>
                             </div>
                             <div class="flex items-center gap-1.5 px-3 py-1.5 bg-white rounded-xl border border-emerald-100 shadow-sm">
                                <span class="text-base font-black text-slate-900 font-mono">{{ row.hours_spent }}</span>
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">HRS</span>
                             </div>
                        </div>
                        <div class="flex gap-3 pt-4 border-t border-emerald-100/50">
                            <svg class="w-4 h-4 text-emerald-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                            <p class="text-base font-black text-slate-600 uppercase tracking-tight leading-normal italic line-clamp-2">" {{ row.task_description }} "</p>
                        </div>
                    </div>

                    <!-- Action Terminal -->
                    <div v-if="row.status === 'Submitted'" class="flex gap-2 relative z-10 pt-1">
                        <button @click="rejectTimesheet(row.id)" class="flex-1 h-12 rounded-2xl bg-white border-2 border-rose-100 text-rose-500 flex items-center justify-center gap-2 text-sm font-black uppercase tracking-[0.2em] shadow-sm active:scale-95 hover:bg-rose-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            REVOKE
                        </button>
                        <button @click="approveTimesheet(row.id)" class="flex-[1.5] h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center gap-2 text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 active:scale-95 hover:bg-emerald-600 transition-all">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            VERIFY NODE
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Pagination Control -->
            <div class="px-8 py-6 bg-slate-50/80 rounded-[2rem] flex flex-col md:flex-row justify-between items-center gap-6 border-t border-gray-100" v-if="timesheets?.data && timesheets.data.length > 0">
                 <div class="flex items-center gap-3">
                    <button @click="fetchData(timesheets.current_page - 1)" :disabled="timesheets.current_page === 1" class="w-10 h-10 bg-white text-slate-400 border border-gray-200 rounded-xl flex items-center justify-center hover:text-emerald-600 disabled:opacity-30 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    <div class="text-sm font-black text-slate-500 uppercase tracking-[0.3em] px-3">
                        Segment <span class="text-emerald-600">{{ timesheets.current_page }}</span> / {{ timesheets.last_page }}
                    </div>
                    <button @click="fetchData(timesheets.current_page + 1)" :disabled="timesheets.current_page === timesheets.last_page" class="w-10 h-10 bg-white text-slate-400 border border-gray-200 rounded-xl flex items-center justify-center hover:text-emerald-600 disabled:opacity-30 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
                <div class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] px-1">
                    Load Gauge: <span class="text-slate-800 text-base tabular-nums">{{ (timesheets.total || 0).toLocaleString() }}</span> Objects Deployed
                </div>
            </div>
        </div>

        <!-- Logging Initiation Modal -->
        <PremiumModal 
            :show="showCreateModal" 
            @close="closeCreateModal" 
            title="Temporal Logging" 
            subtitle="Record Strategic Manual Workload Engagement"
            icon="fa-stopwatch-20"
            maxWidth="2xl"
        >
            <form @submit.prevent="submitCreate" class="space-y-6 pt-4 px-2 font-outfit">
                <div class="space-y-2.5">
                    <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1 leading-none">Target Operative Index</label>
                    <div class="relative group">
                        <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:text-emerald-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <input 
                            type="text" 
                            v-model="employeeSearchQuery" 
                            @focus="showEmployeeDropdown = true"
                            @input="showEmployeeDropdown = true"
                            class="w-full h-12 bg-slate-50 border-2 border-gray-100 rounded-2xl pl-11 pr-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all placeholder:text-slate-300 uppercase tracking-widest shadow-sm"
                            placeholder="SCAN_OPERATIVE_PROFILE..."
                        >
                        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="-translate-y-2 opacity-0" enter-to-class="translate-y-0 opacity-100">
                            <div v-if="showEmployeeDropdown && filteredEmployees.length > 0" class="absolute z-50 mt-3 w-full bg-white/90 backdrop-blur-xl border-2 border-emerald-100 shadow-[0_20px_50px_rgba(16,185,129,0.15)] rounded-[2rem] py-4 max-h-64 overflow-y-auto custom-scrollbar">
                                <div v-for="emp in filteredEmployees" :key="emp.id" @click="selectEmployee(emp)" class="px-6 py-4 hover:bg-emerald-50 cursor-pointer flex items-center justify-between group/item transition-all mx-2 rounded-2xl">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-base font-black text-white group-hover/item:bg-emerald-600 transition-all uppercase shadow-md border-2 border-white">{{ emp.name[0] }}</div>
                                        <div>
                                            <span class="text-base font-black text-slate-900 uppercase tracking-tight">{{ emp.name }}</span>
                                            <span class="block text-xs font-black text-slate-400 uppercase tracking-widest mt-1 opacity-60">Operative Active</span>
                                        </div>
                                    </div>
                                    <svg class="w-4 h-4 text-emerald-500 opacity-0 group-hover/item:opacity-100 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2.5">
                        <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1 leading-none">Temporal Anchor</label>
                        <div class="relative group">
                            <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <input type="date" v-model="createForm.date" class="w-full h-12 bg-slate-50 border-2 border-gray-100 rounded-2xl px-11 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest shadow-sm">
                        </div>
                    </div>
                    <div class="space-y-2.5">
                        <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1">Accumulation Load (HRS)</label>
                        <div class="relative group">
                            <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <input type="number" step="0.5" v-model="createForm.hours" class="w-full h-12 bg-slate-50 border-2 border-gray-100 rounded-2xl px-11 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest shadow-sm" placeholder="0.0">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2.5">
                        <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1 leading-none">Strategic Project Node</label>
                        <div class="relative group">
                            <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                            <input type="text" v-model="createForm.project_name" class="w-full h-12 bg-slate-50 border-2 border-gray-100 rounded-2xl px-11 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest shadow-sm" placeholder="ASSIGN_PROJECT_CODE...">
                        </div>
                    </div>
                    <div class="space-y-2.5">
                        <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1 leading-none">Deployment Vector</label>
                        <div class="relative group">
                            <select v-model="createForm.task_type" class="w-full h-12 bg-slate-50 border-2 border-gray-100 rounded-2xl pl-11 pr-10 text-sm font-black uppercase text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all appearance-none cursor-pointer tracking-widest shadow-sm">
                                <option>Development</option>
                                <option>Meeting</option>
                                <option>Research</option>
                                <option>Other</option>
                            </select>
                            <svg class="w-3 h-3 absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="p-5 bg-emerald-50/50 rounded-[2rem] border-2 border-emerald-100/50 flex items-center justify-between group hover:bg-white transition-all shadow-inner">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 rounded-2xl bg-white border-2 border-emerald-100 flex items-center justify-center text-emerald-500 shadow-xl group-hover:bg-emerald-500 group-hover:text-white transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-base font-black text-slate-800 uppercase tracking-widest leading-none">Billable Engagement</h4>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1.5 opacity-60">Direct client allocation protocol</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="createForm.is_billable" class="sr-only peer">
                        <div class="w-14 h-8 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-600 shadow-[inset_0_2px_4px_rgba(0,0,0,0.1)]"></div>
                    </label>
                </div>

                <div class="space-y-2.5">
                    <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1 leading-none">Operation Detail Brief</label>
                    <textarea v-model="createForm.task_description" rows="4" class="w-full bg-slate-50 border-2 border-gray-100 rounded-2xl p-5 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all placeholder:text-slate-300 leading-relaxed uppercase tracking-widest shadow-sm" placeholder="DESCRIBE_ACCOMPLISHED_OBJECTIVES..."></textarea>
                </div>

                <div class="flex items-center justify-between pt-8 border-t border-gray-100 mt-8">
                    <button @click="closeCreateModal" type="button" class="text-sm font-black uppercase tracking-[0.2em] text-slate-400 hover:text-slate-600 transition-colors">Abort Payload</button>
                    <button type="submit" :disabled="createForm.processing" class="h-14 px-12 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-emerald-600 transition-all flex items-center gap-3 shadow-xl shadow-slate-200 active:scale-95 group">
                        <svg v-if="createForm.processing" class="w-4 h-4 animate-spin text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <svg v-else class="w-4 h-4 text-emerald-400 group-hover:-translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        <span>{{ createForm.processing ? 'Syncing...' : 'Deploy Node Entry' }}</span>
                    </button>
                </div>

            </form>
        </PremiumModal>
    </div>
  </component>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import { router, useForm, Head } from '@inertiajs/vue3';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';

// Layout & UI Components
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import EmployeeFilterBar from '@/Components/EmployeeFilterBar.vue';
import PremiumModal from '@/Components/PremiumModal.vue';

defineOptions({ layout: MainLayout });

const toast = useToastStore();
const props = defineProps({
    embedded: Boolean,
    filters: Object,
    departments: Array,
    locations: Array,
    employees: Array,
    projects: Array
});

const loading = ref(false);
const timesheets = ref({ data: [], current_page: 1, last_page: 1 });

const filterForm = ref({
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    search: props.filters?.search || '',
    department_id: props.filters?.department_id || '',
    location_id: props.filters?.location_id || '',
});

const fetchData = async (page = 1) => {
    loading.value = true;
    try {
        const res = await axios.get(route('admin.attendance.timesheets_data'), {
            params: { page, ...filterForm.value }
        });
        timesheets.value = res.data;
    } catch (e) {
        toast.error("Registry sync failed");
    } finally {
        loading.value = false;
    }
};

const handleFilterUpdate = (newFilters) => {
    filterForm.value = { ...filterForm.value, ...newFilters };
    fetchData(1);
};

onMounted(() => fetchData(1));

const exportData = () => {
    const params = new URLSearchParams(filterForm.value).toString();
    window.location.href = route('admin.attendance.timesheets.export') + `?${params}`;
    toast.success("EXPORT_PROTOCOL_INITIATED");
};

// Create Modal logic
const showCreateModal = ref(false);
const createForm = useForm({
    employee_id: '',
    date: '',
    project_name: '',
    task_description: '',
    task_type: 'Development',
    is_billable: true,
    hours: ''
});

const employeeSearchQuery = ref('');
const showEmployeeDropdown = ref(false);

const filteredEmployees = computed(() => {
    if (!employeeSearchQuery.value) return props.employees || [];
    const lower = employeeSearchQuery.value.toLowerCase();
    return (props.employees || []).filter(e => e.name.toLowerCase().includes(lower));
});

const selectEmployee = (emp) => {
    createForm.employee_id = emp.id;
    employeeSearchQuery.value = emp.name;
    showEmployeeDropdown.value = false;
};

const openCreateModal = () => {
    createForm.reset();
    createForm.date = new Date().toISOString().split('T')[0];
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
};

const submitCreate = () => {
    createForm.post(route('admin.attendance.timesheets.store'), {
        onSuccess: () => {
             closeCreateModal();
             fetchData(1);
             toast.success('Temporal node logged successfully');
        }
    });
};

const approveTimesheet = (id) => {
    router.post(route('admin.attendance.timesheets.approve', id), { status: 'Approved' }, {
        preserveScroll: true,
        onSuccess: () => { toast.success('Protocol Verified'); fetchData(timesheets.value.current_page); }
    });
};

const rejectTimesheet = (id) => {
    router.post(route('admin.attendance.timesheets.approve', id), { status: 'Rejected' }, {
        preserveScroll: true,
        onSuccess: () => { toast.info('Protocol Denied'); fetchData(timesheets.value.current_page); }
    });
};

const getStatusStyles = (status) => {
    switch(status) {
        case 'Approved': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'Rejected': return 'bg-rose-50 text-rose-600 border-rose-100';
        case 'Submitted': return 'bg-amber-50 text-amber-600 border-amber-100';
        default: return 'bg-slate-50 text-slate-400 border-gray-100';
    }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}

input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(0.4) sepia(1) saturate(5) hue-rotate(200deg);
    cursor: pointer;
}
</style>
