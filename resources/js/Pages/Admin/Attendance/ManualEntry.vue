<template>
  <component :is="embedded ? 'div' : AttendanceLayout" title="Manual Terminal" activeTab="manual" v-bind="$props">
    <Head v-if="!embedded" title="Manual Records" />
    
    <div :class="{'max-w-[1200px] mx-auto': !embedded}" class="space-y-6 pb-12">
        <!-- Compact Module Navigation Header -->
        <div class="sticky top-0 z-40 bg-white/90 backdrop-blur-md rounded-2xl border border-slate-200 p-2 shadow-sm flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex items-center gap-3 px-2">
                <div class="w-9 h-9 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg group-hover:bg-emerald-600 transition-colors">
                    <i class="fas fa-keyboard text-sm"></i>
                </div>
                <div>
                    <h2 class="text-xs font-black text-slate-800 uppercase tracking-tight leading-none">Entry Terminal</h2>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-1.5 leading-none px-0.5">Manual Pulse Injection</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button @click="showSettingsModal = true" class="w-10 h-10 bg-slate-100 text-slate-500 rounded-xl flex items-center justify-center hover:bg-slate-200 transition-all border border-slate-200 group">
                    <i class="fas fa-cog group-hover:rotate-90 transition-transform"></i>
                </button>
             
            <nav class="flex bg-slate-100 p-1 rounded-xl border border-slate-200 shadow-inner overflow-x-auto no-scrollbar">
                <button 
                    v-for="tab in ['single', 'bulk', 'bulk_mark']"
                    :key="tab"
                    @click="activeTab = tab"
                    class="px-5 py-2 text-sm font-black uppercase tracking-widest rounded-lg transition-all whitespace-nowrap"
                    :class="activeTab === tab ? 'bg-white text-emerald-600 shadow-sm border border-slate-200/50' : 'text-slate-400 hover:text-slate-600'"
                >
                    <i :class="getTabIcon(tab)" class="mr-2 text-sm"></i>
                    {{ tab.replace('_', ' ') }}
                </button>
            </nav>
            </div>
        </div>

        <!-- Compact Registry Terminal -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 transition-all hover:border-emerald-500/30">
            
            <!-- SINGLE ENTRY PROTOCOL -->
            <div v-if="activeTab === 'single'" class="space-y-6 max-w-xl mx-auto">
                  <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                    <div class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-user-plus text-sm"></i>
                    </div>
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-tight">Individual Record Entry</h3>
                  </div>
                 
                 <form @submit.prevent="submitSingle" class="space-y-5">
                    <div class="space-y-1.5">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1 leading-none">Active Member</label>
                        <div class="relative group mt-1">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald-500 transition-colors text-sm"></i>
                            <select v-model="singleForm.employee_id" class="w-full bg-slate-50 border border-slate-200 rounded-lg h-10 pl-10 pr-8 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all appearance-none cursor-pointer tracking-tight">
                                <option value="">Identify Member Profile...</option>
                                <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                    {{ emp.first_name }} {{ emp.last_name }} [{{ emp.employee_code }}]
                                </option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                        </div>
                    </div>

                    <div v-if="singleForm.employee_id" class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100 flex justify-between items-center shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-emerald-600 border border-emerald-200 shadow-sm transition-transform hover:rotate-12 group">
                                <i class="fas fa-id-card text-base"></i>
                            </div>
                            <div v-if="fetchingShift" class="flex items-center gap-2">
                                <i class="fas fa-circle-notch animate-spin text-emerald-500"></i>
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Syncing Shift...</span>
                            </div>
                            <div v-else-if="currentShiftInfo">
                                <span class="block text-xs font-black text-emerald-600 uppercase tracking-[0.2em] leading-none mb-1.5">Active Assignment</span>
                                <span class="text-sm font-black text-emerald-800 uppercase tracking-tighter leading-none">
                                    {{ currentShiftInfo.name }} [{{ currentShiftInfo.start_time }} - {{ currentShiftInfo.end_time }}]
                                    <span v-if="currentShiftInfo.is_non_working" class="ml-2 text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">OFF DAY</span>
                                </span>
                            </div>
                            <div v-else>
                                <span class="block text-xs font-black text-slate-400 uppercase tracking-[0.2em] leading-none mb-1.5">No Assignment</span>
                                <span class="text-sm font-black text-slate-600 uppercase tracking-tighter leading-none">Standard fallback applies</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1 leading-none">Log Date</label>
                            <input type="date" v-model="singleForm.date" class="w-full bg-slate-50 border border-slate-200 rounded-lg h-10 px-4 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest mt-1">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1 leading-none">Status Protocol</label>
                            <select v-model="singleForm.status" class="w-full bg-slate-50 border border-slate-200 rounded-lg h-10 px-4 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all appearance-none uppercase tracking-widest mt-1">
                                <option value="Present">PRESENT</option>
                                <option value="Absent">ABSENT</option>
                                <option value="Half Day">HALF DAY</option>
                                <option value="On Leave">ON LEAVE</option>
                                <option value="Holiday">HOLIDAY</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1 leading-none">Check-In</label>
                            <input type="time" v-model="singleForm.in_time" class="w-full bg-slate-50 border border-slate-200 rounded-lg h-10 px-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all mt-1">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1 leading-none">Check-Out</label>
                            <input type="time" v-model="singleForm.out_time" class="w-full bg-slate-50 border border-slate-200 rounded-lg h-10 px-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all mt-1">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1 leading-none">Terminal Justification</label>
                        <textarea 
                            v-model="singleForm.remarks" 
                            rows="3" 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-tight min-h-[100px] mt-1"
                            placeholder="State reason for manual override..."
                        ></textarea>
                    </div>

                    <div class="flex justify-end pt-6">
                        <button 
                            @click="submitSingle" 
                            :disabled="singleForm.processing"
                            class="h-11 px-10 bg-slate-900 text-white rounded-xl text-sm font-black uppercase tracking-[0.2em] flex items-center gap-3 hover:bg-emerald-600 transition-all shadow-lg active:scale-95 disabled:opacity-50 group"
                        >
                            <i class="fas fa-microchip text-emerald-400 group-hover:rotate-12 transition-transform"></i>
                            {{ singleForm.processing ? 'INJECTING...' : 'INJECT PULSE' }}
                        </button>
                    </div>
                 </form>
            </div>

            <!-- BATCH UPLOAD PROTOCOL -->
            <div v-if="activeTab === 'bulk'" class="space-y-6 max-w-2xl mx-auto">
                <div class="flex justify-between items-center border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-lg">
                            <i class="fas fa-file-csv text-sm"></i>
                        </div>
                        <h3 class="text-xs font-black text-slate-800 uppercase tracking-tight">Bulk Matrix Ingest</h3>
                    </div>
                    <button @click="downloadTemplate" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-black text-emerald-600 uppercase tracking-[0.2em] hover:bg-emerald-50 transition-all shadow-sm active:scale-95 group">
                        <i class="fas fa-download text-sm group-hover:translate-y-0.5 transition-transform"></i>
                        Protocol Template
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-4 bg-slate-50/50 rounded-xl border border-slate-100 space-y-4 shadow-inner">
                        <h4 class="text-sm font-black text-slate-700 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-info-circle text-emerald-500"></i>
                            Ingest Protocol
                        </h4>
                        <ul class="space-y-2">
                            <li v-for="inst in ['Strict template compliance', 'Use 24h Time (HH:MM)', 'Validate Member Codes', 'Auto-map Date Formats']" :key="inst" class="flex items-start gap-2.5">
                                <div class="w-3.5 h-3.5 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="fas fa-check text-xs text-emerald-600"></i>
                                </div>
                                <span class="text-sm font-black text-slate-500 uppercase tracking-tight leading-tight">{{ inst }}</span>
                            </li>
                        </ul>
                    </div>

                    <form @submit.prevent="submitBulk" class="space-y-5">
                        <div class="space-y-1.5">
                            <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Logic File</label>
                            <label class="group relative block">
                                <input type="file" class="hidden" accept=".csv" @change="handleFileUpload">
                                <div class="border-2 border-dashed border-slate-200 rounded-xl p-4 flex flex-col items-center justify-center gap-2 hover:bg-emerald-50/30 hover:border-emerald-300 transition-all cursor-pointer text-center bg-slate-50 shadow-inner">
                                    <i class="fas fa-cloud-upload-alt text-lg text-slate-300 group-hover:text-emerald-500 transition-all"></i>
                                    <div>
                                        <p class="text-sm font-black text-slate-700 truncate max-w-[150px] uppercase tracking-tight">{{ bulkForm.file ? bulkForm.file.name : 'Select CSV Deployment' }}</p>
                                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Matrix Payload Only</p>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Temporal Alignment</label>
                            <select v-model="bulkForm.date_format" class="w-full bg-slate-50 border border-slate-200 rounded-xl h-11 px-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all appearance-none cursor-pointer uppercase tracking-widest h-12">
                                <option value="d-m-Y">DD-MM-YYYY</option>
                                <option value="Y-m-d">YYYY-MM-DD</option>
                                <option value="m/d/Y">MM/DD/YYYY</option>
                            </select>
                        </div>

                        <button :disabled="bulkForm.processing || !bulkForm.file" class="w-full h-11 bg-slate-900 text-white rounded-lg text-sm font-black uppercase tracking-widest hover:bg-slate-800 active:scale-95 transition-all shadow-md mt-2">
                            Release Data Ingest
                        </button>
                    </form>
                </div>
            </div>

            <!-- BATCH PROCESSING TERMINAL -->
            <div v-if="activeTab === 'bulk_mark'" class="space-y-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-lg">
                            <i class="fas fa-users-cog text-sm"></i>
                        </div>
                        <h3 class="text-xs font-black text-slate-800 uppercase tracking-tight">Batch Deployment Matrix</h3>
                    </div>
                     <div class="flex gap-2">
                        <button @click="submitBulkMark" :disabled="bulkMarkForm.processing" class="h-10 px-8 bg-slate-900 text-white rounded-xl text-sm font-black uppercase tracking-widest hover:bg-emerald-600 transition-all active:scale-95 shadow-lg disabled:opacity-50">
                            Commit Batch Deployment
                        </button>
                    </div>
                </div>

                <!-- Strategic Filters -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50/50 p-3 rounded-2xl border border-slate-200 shadow-inner">
                    <div class="space-y-1.5">
                        <label class="text-xs font-black text-slate-400 uppercase tracking-widest px-1">Deploy Date</label>
                        <input type="date" v-model="bulkFilters.date" class="w-full bg-white border border-slate-200 rounded-lg h-9 px-3 text-sm font-black uppercase tracking-tight focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all h-10 shadow-sm">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-black text-slate-400 uppercase tracking-widest px-1">Department</label>
                        <select v-model="bulkFilters.department_id" class="w-full bg-white border border-slate-200 rounded-lg h-9 px-3 text-sm font-black uppercase tracking-tight focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all appearance-none cursor-pointer h-10 shadow-sm">
                            <option value="">ALL UNITS</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name.toUpperCase() }}</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-black text-slate-400 uppercase tracking-widest px-1">Tactical Rank</label>
                        <select v-model="bulkFilters.role_name" class="w-full bg-white border border-slate-200 rounded-lg h-9 px-3 text-sm font-black uppercase tracking-tight focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all appearance-none cursor-pointer h-10 shadow-sm">
                            <option value="">ALL RANKS</option>
                            <option v-for="role in ['ADMIN', 'MANAGER', 'OPERATIVE']" :key="role" :value="role">{{ role }}</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button @click="fetchBulkMarkList" class="w-full h-10 bg-slate-900 text-white rounded-lg text-sm font-black uppercase tracking-widest hover:bg-emerald-600 transition-all active:scale-95 shadow-md">
                            Sync Operative Matrix
                        </button>
                    </div>
                </div>

                <!-- Bulk Management Protocols -->
                <div v-if="bulkMarkList.length > 0" class="space-y-6">
                    <div class="bg-emerald-50/80 p-5 rounded-2xl border border-emerald-100 flex flex-col sm:flex-row items-center gap-5 shadow-sm">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white shadow-lg shrink-0">
                            <i class="fas fa-bolt text-sm"></i>
                        </div>
                        <div class="flex-1 flex flex-wrap items-center gap-4">
                             <span class="text-sm font-black text-emerald-700 uppercase tracking-widest">Global Matrix Override:</span>
                             <div class="flex flex-wrap gap-2 overflow-x-auto pb-2 sm:pb-0 no-scrollbar">
                                <button 
                                    v-for="status in ['Present', 'Absent', 'Half Day', 'On Leave']" 
                                    :key="status"
                                    @click="batchStatus = status; applyBatchStatus()"
                                    class="px-4 py-2 rounded-xl bg-white text-xs font-black uppercase tracking-widest border border-emerald-100 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all shadow-sm active:scale-95 whitespace-nowrap"
                                >
                                    {{ status }}
                                </button>
                             </div>
                        </div>
                    </div>

                    <!-- Batch Matrix Terminal -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden min-h-[400px]">
                        <!-- Desktop Table View -->
                        <div class="hidden lg:block overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead class="bg-slate-900 border-b border-slate-800">
                                    <tr class="bg-slate-900 border-b border-slate-800">
                                        <th class="px-5 py-4 text-left">
                                            <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Active Member</span>
                                        </th>
                                        <th class="px-5 py-4 text-left">
                                            <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Deployment</span>
                                        </th>
                                        <th class="px-5 py-4 text-center">
                                            <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Protocol</span>
                                        </th>
                                        <th class="px-5 py-4 text-center">
                                            <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Check-In</span>
                                        </th>
                                        <th class="px-5 py-4 text-center">
                                            <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Check-Out</span>
                                        </th>
                                        <th class="px-5 py-4 text-left">
                                            <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Context</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="item in bulkMarkList" :key="item.id" class="hover:bg-emerald-50/20 transition-all duration-150 group border-b border-slate-50 last:border-0">
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3.5">
                                                <div class="w-9 h-9 rounded-xl bg-slate-900 flex items-center justify-center text-white font-black text-sm uppercase shadow-sm border border-white group-hover:bg-emerald-600 transition-colors">
                                                    {{ item.name ? item.name[0] : 'U' }}
                                                </div>
                                                <div class="truncate max-w-[150px]">
                                                    <p class="text-sm font-black text-slate-800 uppercase tracking-tighter leading-none truncate group-hover:text-emerald-700 transition-colors">{{ item.name }}</p>
                                                    <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-2 leading-none truncate">{{ item.code }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <span class="text-sm font-black text-slate-600 uppercase tracking-tighter tabular-nums">{{ bulkFilters.date }}</span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex justify-center">
                                                <select v-model="item.status" class="bg-slate-50 border border-slate-200 rounded-lg h-9 px-3 text-sm font-black uppercase tracking-tight focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all cursor-pointer shadow-sm">
                                                    <option value="Present">PRESENT</option>
                                                    <option value="Absent">ABSENT</option>
                                                    <option value="Half Day">HALF DAY</option>
                                                    <option value="On Leave">ON LEAVE</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex justify-center">
                                                <input type="time" v-model="item.in_time" class="bg-slate-50 border border-slate-200 rounded-lg h-9 px-3 text-sm font-black text-slate-700 focus:bg-white transition-all shadow-sm" />
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex justify-center">
                                                <input type="time" v-model="item.out_time" class="bg-slate-50 border border-slate-200 rounded-lg h-9 px-3 text-sm font-black text-slate-700 focus:bg-white transition-all shadow-sm" />
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="relative group/input">
                                                <input type="text" v-model="item.remarks" class="w-full bg-slate-50 border border-slate-200 rounded-lg h-9 px-3 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-tight placeholder:text-slate-300 shadow-inner" placeholder="PULSE_MSG" />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Mobile Card View -->
                        <div class="lg:hidden divide-y divide-slate-100">
                            <div v-for="item in bulkMarkList" :key="'mb-'+item.id" class="p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center text-white font-black text-sm uppercase shadow-sm">
                                            {{ item.name ? item.name[0] : 'U' }}
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight leading-none">{{ item.name }}</h4>
                                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">{{ item.code }}</p>
                                        </div>
                                    </div>
                                    <span class="text-sm font-black text-slate-400 tabular-nums">{{ bulkFilters.date }}</span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="relative group">
                                         <label class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1 block px-1">Protocol Status</label>
                                         <select v-model="item.status" class="w-full bg-slate-50 border border-slate-200 rounded-lg h-9 px-3 text-sm font-black uppercase tracking-tight focus:bg-white transition-all shadow-sm">
                                            <option value="Present">PRESENT</option>
                                            <option value="Absent">ABSENT</option>
                                            <option value="Half Day">HALF DAY</option>
                                            <option value="On Leave">ON LEAVE</option>
                                        </select>
                                    </div>
                                    <div class="relative group">
                                         <label class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1 block px-1">Context Mark</label>
                                         <input type="text" v-model="item.remarks" class="w-full bg-slate-50 border border-slate-200 rounded-lg h-9 px-3 text-sm font-black text-slate-700 focus:bg-white transition-all shadow-inner" placeholder="PULSE_MSG" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-20 opacity-40 items-center flex flex-col justify-center gap-3">
                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400">
                        <i class="fas fa-terminal text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Terminal Idle</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Sync protocol required</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terminal Settings Modal -->
    <PremiumModal 
        :show="showSettingsModal" 
        @close="showSettingsModal = false" 
        title="Terminal Configuration" 
        subtitle="Operational Restrictions"
        icon="fa-cog"
        maxWidth="md"
    >
        <div class="space-y-6 p-2">
            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-200">
                <div>
                  <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Restrict Holiday Entry</h4>
                  <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Prevent manual logs on holidays</p>
                </div>
                <Toggle v-model="settings.restrict_holidays" />
            </div>

            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-200">
                <div>
                  <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Force Shift Pre-fill</h4>
                  <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Auto-sync times with shift profile</p>
                </div>
                <Toggle v-model="settings.force_shift_prefill" />
            </div>

            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-200">
                <div>
                  <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">Status Lock</h4>
                  <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Ignore off-day markers</p>
                </div>
                <Toggle v-model="settings.ignore_off_day_status" />
            </div>

            <button @click="showSettingsModal = false" class="w-full h-12 bg-slate-900 text-white rounded-xl text-sm font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-lg active:scale-95">
                Commit Config
            </button>
        </div>
    </PremiumModal>
  </component>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import Toggle from '@/Components/Toggle.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    embedded: Boolean,
    locations: Array,
    departments: Array
});

const toast = useToastStore();
const activeTab = ref('single');
const showSettingsModal = ref(false);

const settings = ref({
    restrict_holidays: true,
    force_shift_prefill: true,
    ignore_off_day_status: false
});

const getTabIcon = (tab) => {
    switch(tab) {
        case 'single': return 'fas fa-user-plus';
        case 'bulk': return 'fas fa-cloud-arrow-up';
        case 'bulk_mark': return 'fas fa-users-cog';
        default: return 'fas fa-terminal';
    }
};

// --- Single Entry ---
const singleForm = useForm({
    employee_id: '',
    date: new Date().toISOString().slice(0, 10),
    in_time: '09:00',
    out_time: '18:00',
    status: 'Present',
    remarks: ''
});

const employees = ref([]);
const searching = ref(false);
const fetchingShift = ref(false);
const currentShiftInfo = ref(null);

const fetchShiftInfo = async () => {
    if (!singleForm.employee_id || !singleForm.date) return;
    
    fetchingShift.value = true;
    try {
        const res = await axios.get(route('admin.attendance.manual.shift-info'), {
            params: {
                employee_id: singleForm.employee_id,
                date: singleForm.date
            }
        });
        currentShiftInfo.value = res.data;
        
        if (settings.value.force_shift_prefill) {
            singleForm.in_time = res.data.start_time;
            singleForm.out_time = res.data.end_time;
            if (res.data.is_non_working && !settings.value.ignore_off_day_status) {
                singleForm.status = 'Absent';
            } else {
                singleForm.status = 'Present';
            }
        }
    } catch (e) {
        toast.error('Failed to sync shift info');
    } finally {
        fetchingShift.value = false;
    }
};

watch(() => singleForm.employee_id, fetchShiftInfo);
watch(() => singleForm.date, fetchShiftInfo);

const searchEmployees = async (query = '') => {
    searching.value = true;
    try {
        const res = await axios.get(route('admin.attendance.roster.data'), { params: { search: query, json: true } });
        employees.value = res.data.employees?.data || res.data.employees || [];
    } catch (e) {
        toast.error('Tactical comms failed');
    } finally {
        searching.value = false;
    }
};

onMounted(searchEmployees);

const submitSingle = () => {
    singleForm.post(route('admin.attendance.manual.store'), {
        onSuccess: () => {
             toast.success('Record committed to registry');
             singleForm.reset('employee_id', 'remarks');
        },
        onError: () => toast.error('Record injection failed')
    });
};

// --- Bulk Import ---
const bulkForm = useForm({
    file: null,
    date_format: 'd-m-Y'
});

const handleFileUpload = (e) => {
    bulkForm.file = e.target.files[0];
};

const submitBulk = () => {
    bulkForm.post(route('admin.attendance.manual.bulk'), {
        onSuccess: () => {
            toast.success('Matrix ingestion started');
            bulkForm.reset();
        },
        onError: () => toast.error('Matrix ingestion failed')
    });
};

const downloadTemplate = () => {
    const csvContent = "data:text/csv;charset=utf-8,Employee Code,Date,In Time,Out Time,Status\nEMP001,25-01-2026,09:00,18:00,Present";
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "attendance_protocol_template.csv");
    document.body.appendChild(link);
    link.click();
    toast.success("Protocol template downloaded");
};

// --- Bulk Mark Logic ---
const bulkMarkForm = useForm({
    date: '',
    entries: [] 
});

const bulkFilters = ref({
    date: new Date().toISOString().slice(0, 10),
    department_id: '',
    role_name: ''
});

const bulkMarkList = ref([]);
const batchStatus = ref('');

const fetchBulkMarkList = async () => {
    try {
        const res = await axios.get(route('admin.attendance.roster.data'), { 
            params: { 
                date: bulkFilters.value.date, 
                department_id: bulkFilters.value.department_id,
                role_name: bulkFilters.value.role_name,
                json: true
            } 
        });
        
        const employeesEmployees = res.data.employees.data || res.data.employees;
        const roster = res.data.roster || {};
        const shifts = res.data.shifts || [];
        
        bulkMarkList.value = employeesEmployees.map(emp => {
            const dateStr = bulkFilters.value.date;
            const shiftId = roster[emp.id]?.[dateStr] || 1; 
            const shift = shifts.find(s => s.id === shiftId) || { name: 'General', start_time: '09:00', end_time: '18:00' };
            
            return {
                selected: true,
                employee_id: emp.id,
                name: `${emp.first_name} ${emp.last_name}`,
                code: emp.employee_code,
                shift_id: shiftId,
                shift_name: shift.name,
                in_time: shift.start_time?.slice(0,5),
                out_time: shift.end_time?.slice(0,5),
                status: 'Present',
                remarks: ''
            };
        });
        toast.success("Operative matrix synced");
    } catch (e) {
        toast.error('Matrix sync failed');
    }
};

const toggleAll = (e) => {
    bulkMarkList.value.forEach(item => item.selected = e.target.checked);
};

const applyBatchStatus = () => {
    if (!batchStatus.value) return;
    bulkMarkList.value.forEach(item => {
        if (item.selected) item.status = batchStatus.value;
    });
    toast.success(`Broadcasting '${batchStatus.value}' override`);
};

const submitBulkMark = () => {
    const selectedItems = bulkMarkList.value.filter(item => item.selected);
    if (selectedItems.length === 0) {
        toast.error('No operatives identified');
        return;
    }
    
    bulkMarkForm.date = bulkFilters.value.date;
    bulkMarkForm.entries = selectedItems.map(item => ({
        employee_id: item.employee_id,
        status: item.status,
        in_time: item.in_time,
        out_time: item.out_time,
        shift_id: item.shift_id,
        remarks: item.remarks
    }));

    bulkMarkForm.post(route('admin.attendance.manual.bulk-mark.store'), {
        onSuccess: () => {
            toast.success(`Tactical deployment successful for ${selectedItems.length} operatives`);
            bulkMarkList.value = [];
        },
        onError: () => toast.error('Tactical deployment failed')
    });
};
</script>
