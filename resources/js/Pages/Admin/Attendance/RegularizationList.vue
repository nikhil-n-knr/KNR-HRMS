<template>
  <component :is="embedded ? 'div' : AttendanceLayout" title="Corrections" activeTab="approvals" v-bind="$props">
    <Head v-if="!embedded" title="Regularization Hub" />
    
    <div :class="{'max-w-[1600px] mx-auto': !embedded}" class="space-y-8 pb-12 px-4 md:px-0 font-outfit">
      <!-- Specialized Audit Header -->
      <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
          <div class="flex items-center gap-5">
              <div class="p-4 bg-slate-900 border border-slate-800 rounded-2xl text-indigo-400 shadow-xl shadow-slate-200/50 relative overflow-hidden group">
                  <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                  <svg class="w-7 h-7 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
              </div>
              <div>
                  <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                      Correction Desk
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-indigo-50 text-indigo-600 border border-indigo-100 uppercase tracking-widest">Audit Terminal</span>
                  </h2>
                  <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mt-1">Verification protocols for temporal anomalies</p>
              </div>
          </div>
          
          <div class="flex items-center gap-3 w-full lg:w-auto">
              <div class="relative flex-1 lg:w-64 group">
                  <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                  <input v-model="filterForm.search" type="text" placeholder="AUDIT_SEARCH..." class="w-full bg-white border-none rounded-2xl py-3.5 pl-11 pr-4 text-base font-black text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all uppercase tracking-widest placeholder:text-slate-300 shadow-sm">
              </div>
              <button @click="exportData" class="h-12 w-12 bg-white text-slate-400 rounded-2xl flex items-center justify-center border border-gray-100 shadow-sm hover:text-indigo-600 hover:border-indigo-100 transition-all active:scale-90 shrink-0">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
              </button>
          </div>
      </div>

      <!-- Audit Pulse Summary Bar -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
          <div v-for="metric in [
              { label: 'Pending Review', count: (props.requests?.data || []).filter(r => r.status === 'Pending').length, color: 'amber', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
              { label: 'Verified Nodes', count: (props.requests?.data || []).filter(r => r.status === 'Approved').length, color: 'indigo', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' }
          ]" :key="metric.label" 
          class="bg-white/60 backdrop-blur-xl p-5 md:p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-5 group hover:shadow-md transition-all">
              <div :class="`w-12 h-12 rounded-xl bg-${metric.color}-50 text-${metric.color}-600 border border-${metric.color}-100 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform shrink-0`">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="metric.icon"></path></svg>
              </div>
              <div class="min-w-0">
                  <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] leading-none mb-2 truncate">{{ metric.label }}</p>
                  <p :class="`text-lg font-black text-${metric.color}-600 tracking-tighter leading-none tabular-nums`">{{ metric.count }} OBJECTS</p>
              </div>
          </div>
      </div>

      <!-- Specialized Filters -->
      <div class="bg-white/40 backdrop-blur-xl border border-white p-1.5 rounded-3xl shadow-sm">
          <EmployeeFilterBar 
              :departments="departments" 
              :locations="locations || []"
              @update="handleFilterUpdate"
              accent-color="indigo"
          >
              <template #extra>
                  <div class="flex gap-2 items-center w-full lg:w-auto">
                      <div class="relative flex-1 lg:w-48 group">
                          <select v-model="filterForm.status" class="w-full h-11 bg-white/60 border-none rounded-xl pl-4 pr-10 text-sm font-black uppercase tracking-widest text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer shadow-sm">
                              <option value="">ALL PROTOCOLS</option>
                              <option value="Approved">VERIFIED</option>
                              <option value="Pending">PENDING_AUDIT</option>
                              <option value="Rejected">REVOKED</option>
                          </select>
                          <svg class="w-3 h-3 absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                      </div>
                  </div>
              </template>
          </EmployeeFilterBar>
      </div>

        <!-- Registry Terminal -->
        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-2xl shadow-slate-200/50 overflow-hidden flex flex-col relative min-h-[400px]">
            <!-- Desktop Table -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-900 border-b border-slate-800">
                            <th class="px-6 py-5 text-left w-32">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Anchor</span>
                            </th>
                            <th class="px-6 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Operative Registry</span>
                            </th>
                            <th class="px-6 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Correction Profile</span>
                            </th>
                            <th class="px-6 py-5 text-right">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Validation</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="(requests?.data || []).length === 0" class="text-center">
                            <td colspan="4" class="p-32">
                                <div class="flex flex-col items-center gap-4 opacity-20 grayscale">
                                    <svg class="w-16 h-16 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">No Correction Nodes Located</span>
                                </div>
                            </td>
                        </tr>
                        
                        <tr v-for="req in (requests?.data || [])" :key="req.id" class="group hover:bg-slate-50 transition-all duration-300">
                            <td class="px-6 py-6">
                                <div class="flex flex-col">
                                    <span class="text-lg font-black text-slate-900 tracking-tighter leading-none tabular-nums">{{ new Date(req.date).getDate() }}</span>
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1.5 leading-none">{{ new Date(req.date).toLocaleDateString('en-US', { month: 'short' }) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-2xl bg-slate-900 flex items-center justify-center text-white font-black text-xs border-2 border-white shadow-lg flex-shrink-0 overflow-hidden group-hover:bg-indigo-600 transition-all">
                                        <img v-if="req.employee?.avatar" :src="req.employee?.avatar" class="w-full h-full object-cover">
                                        <span v-else>{{ req.employee?.first_name ? req.employee.first_name[0] : '' }}{{ req.employee?.last_name ? req.employee.last_name[0] : '' }}</span>
                                    </div>
                                    <div class="truncate max-w-[200px]">
                                        <div class="text-base font-black text-slate-900 uppercase tracking-tight truncate leading-none group-hover:text-indigo-700 transition-colors">{{ req.employee?.first_name || '' }} {{ req.employee?.last_name || '' }}</div>
                                        <div class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] truncate mt-2.5 leading-none">{{ req.employee?.department?.name || 'OPERATIONS' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex flex-col gap-3">
                                    <p class="text-base font-black text-slate-600 uppercase tracking-tight truncate max-w-[350px] leading-relaxed" :title="req.reason">{{ req.reason || 'NO_CONTEXT' }}</p>
                                    <div class="flex items-center gap-2">
                                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100 shadow-sm">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span class="text-sm font-black uppercase tracking-widest tabular-nums">{{ (req.regularized_in_time || '00:00').slice(0, 5) }} &rarr; {{ (req.regularized_out_time || '00:00').slice(0, 5) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-right">
                                <span class="px-4 py-1.5 text-xs font-black rounded-full border uppercase tracking-widest shadow-sm transition-all" 
                                    :class="{
                                        'bg-emerald-50 text-emerald-600 border-emerald-100': req.status === 'Approved', 
                                        'bg-amber-50 text-amber-600 border-amber-100': req.status === 'Pending', 
                                        'bg-rose-50 text-rose-600 border-rose-100': req.status === 'Rejected'
                                    }">
                                    {{ req.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card List -->
            <div class="lg:hidden divide-y divide-gray-50 bg-slate-50/50">
                <div v-if="(requests?.data || []).length === 0" class="p-20 text-center animate-in fade-in zoom-in duration-700">
                    <svg class="w-12 h-12 mx-auto text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">No Audit Records Located</p>
                </div>
                <div v-for="req in (requests?.data || [])" :key="req.id" class="p-5 space-y-5 group relative overflow-hidden bg-white hover:bg-slate-50 transition-all active:scale-[0.98]">
                    <div class="flex justify-between items-start relative z-10">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="w-12 h-12 rounded-2xl bg-slate-900 border-2 border-white flex items-center justify-center text-white font-black text-xs shrink-0 shadow-lg group-hover:bg-indigo-600 transition-all">
                                <img v-if="req.employee?.avatar" :src="req.employee?.avatar" class="w-full h-full object-cover rounded-2xl">
                                <span v-else>{{ req.employee?.first_name ? req.employee.first_name[0] : '?' }}</span>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight leading-none truncate">{{ req.employee?.first_name }} {{ req.employee?.last_name }}</h4>
                                <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-2 leading-none">{{ new Date(req.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 text-xs font-black rounded-full border uppercase tracking-widest shadow-sm" 
                            :class="{
                                'bg-emerald-50 text-emerald-600 border-emerald-100': req.status === 'Approved', 
                                'bg-amber-50 text-amber-600 border-amber-100': req.status === 'Pending', 
                                'bg-rose-50 text-rose-600 border-rose-100': req.status === 'Rejected'
                            }">
                            {{ req.status }}
                        </span>
                    </div>
                    
                    <div class="bg-indigo-50/40 p-4 rounded-2xl border border-indigo-100/50 group-hover:bg-white transition-all relative z-10 shadow-inner">
                        <div class="flex gap-3 mb-4">
                            <svg class="w-4 h-4 text-indigo-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                            <p class="text-base font-black text-slate-600 uppercase tracking-tight leading-normal">"{{ req.reason }}"</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-indigo-100/50">
                             <div class="space-y-1.5">
                                 <span class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] block">Shift Pulse Entry</span>
                                 <span class="text-xs font-black text-indigo-700 tabular-nums bg-white px-2 py-1 rounded-lg border border-indigo-50 shadow-sm inline-block">{{ req.regularized_in_time.slice(0, 5) }}</span>
                             </div>
                             <div class="space-y-1.5 border-l border-indigo-100/50 pl-4">
                                 <span class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] block">Shift Pulse Exit</span>
                                 <span class="text-xs font-black text-indigo-700 tabular-nums bg-white px-2 py-1 rounded-lg border border-indigo-50 shadow-sm inline-block">{{ req.regularized_out_time.slice(0, 5) }}</span>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Neural Pagination -->
            <div class="px-8 py-6 bg-slate-50/80 flex flex-col md:flex-row justify-between items-center gap-6 border-t border-gray-100" v-if="(requests?.data || []).length > 0">
                <div class="flex items-center gap-3">
                    <button @click="fetchData(requests.current_page - 1)" :disabled="requests.current_page === 1" class="w-10 h-10 bg-white text-slate-400 border border-gray-200 rounded-xl flex items-center justify-center hover:text-indigo-600 hover:border-indigo-100 disabled:opacity-30 disabled:pointer-events-none transition-all shadow-sm active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    <div class="bg-slate-900 px-5 py-2.5 rounded-xl shadow-lg border border-slate-800">
                        <span class="text-sm font-black text-indigo-400 uppercase tracking-[0.2em] whitespace-nowrap leading-none mt-0.5">Segment {{ requests.current_page }} / {{ requests.last_page }}</span>
                    </div>
                    <button @click="fetchData(requests.current_page + 1)" :disabled="requests.current_page === requests.last_page" class="w-10 h-10 bg-white text-slate-400 border border-gray-200 rounded-xl flex items-center justify-center hover:text-indigo-600 hover:border-indigo-100 disabled:opacity-30 disabled:pointer-events-none transition-all shadow-sm active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
                <div class="text-center md:text-right">
                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] block leading-none mb-2 px-1">Global Matrix Load</span>
                    <span class="text-lg font-black text-slate-900 tracking-tighter leading-none tabular-nums">{{ (requests.total || 0).toLocaleString() }} OBJECTS</span>
                </div>
            </div>
        </div>

        <!-- Correction Initiation Modal -->
        <PremiumModal 
            :show="showCreateModal" 
            @close="closeCreateModal" 
            title="Correction Protocol" 
            subtitle="Manual Anomaly Adjustment"
            icon="fa-shield-halved"
            maxWidth="xl"
        >
            <form @submit.prevent="submitCreate" class="space-y-6 pt-4 px-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2.5">
                        <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1 leading-none">Operative Identifier</label>
                        <div class="relative group mt-1">
                            <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m10 5l10 10m-5-5l5 5"></path></svg>
                            <input v-model="createForm.employee_id" type="number" required class="w-full h-12 bg-slate-50 border-2 border-gray-100 rounded-2xl pl-11 pr-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all placeholder:text-slate-300 uppercase tracking-widest shadow-sm" placeholder="CORE_ID_INDEX">
                        </div>
                    </div>
                    <div class="space-y-2.5">
                        <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1 leading-none">Anchor Date</label>
                        <div class="relative group mt-1">
                            <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <input v-model="createForm.date" type="date" required class="w-full h-12 bg-slate-50 border-2 border-gray-100 rounded-2xl pl-11 pr-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all uppercase tracking-widest shadow-sm">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-2.5">
                        <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1 leading-none">Pulse Entry</label>
                        <div class="relative group mt-1">
                            <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <input v-model="createForm.regularized_in_time" type="time" required class="w-full h-12 bg-slate-50 border-2 border-gray-100 rounded-2xl pl-11 pr-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm">
                        </div>
                    </div>
                    <div class="space-y-2.5">
                        <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1 leading-none">Pulse Exit</label>
                        <div class="relative group mt-1">
                            <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <input v-model="createForm.regularized_out_time" type="time" required class="w-full h-12 bg-slate-50 border-2 border-gray-100 rounded-2xl pl-11 pr-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm">
                        </div>
                    </div>
                </div>

                <div class="space-y-2.5">
                    <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-1 leading-none">Justification Brief</label>
                    <textarea v-model="createForm.reason" required rows="4" class="w-full bg-slate-50 border-2 border-gray-100 rounded-2xl p-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all placeholder:text-slate-300 leading-relaxed mt-1 uppercase tracking-widest shadow-sm" placeholder="PROVIDE_DETAILED_RATIONALE..."></textarea>
                </div>

                <div class="flex items-center justify-between pt-8 border-t border-gray-100 mt-8">
                    <button type="button" @click="closeCreateModal" class="text-sm font-black uppercase tracking-[0.2em] text-slate-400 hover:text-slate-600 transition-colors">Abort Mission</button>
                    <button type="submit" class="h-14 px-10 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-indigo-600 transition-all flex items-center gap-3 shadow-xl shadow-indigo-100 active:scale-95 group">
                        <svg class="w-4 h-4 text-emerald-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Commit Protocol</span>
                    </button>
                </div>
            </form>
        </PremiumModal>
    </div>
  </component>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useToastStore } from '@/stores/toast';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import EmployeeFilterBar from '@/Components/EmployeeFilterBar.vue';
import { Head, router } from '@inertiajs/vue3';
import PremiumModal from '@/Components/PremiumModal.vue';

defineOptions({ layout: MainLayout });

const toast = useToastStore();
const props = defineProps({
    embedded: Boolean,
    requests: { 
        type: Object,
        default: () => ({ 
            data: [], 
            current_page: 1, 
            last_page: 1,
            total: 0
        })
    },
    filters: Object,
    departments: Array,
    locations: Array
});

const filterForm = ref({
    status: props.filters?.status || '',
    search: props.filters?.search || '',
    department_id: props.filters?.department_id || '',
    location_id: props.filters?.location_id || '',
});

const handleFilterUpdate = (newFilters) => {
    reload({
        ...filterForm.value,
        ...newFilters
    });
};

watch(() => filterForm.value.status, () => {
    reload(filterForm.value);
});

const reload = (query) => {
    router.visit(route('admin.attendance.regularization'), {
        method: 'get',
        data: query,
        preserveState: true,
        preserveScroll: true,
        only: ['requests', 'filters']
    });
};

const fetchData = (page) => {
     router.visit(route('admin.attendance.regularization'), {
         method: 'get',
        data: { 
            page, 
            ...filterForm.value 
        },
        preserveState: true,
        preserveScroll: true,
        only: ['requests']
     });
};

const exportData = () => {
    const params = new URLSearchParams(filterForm.value).toString();
    window.location.href = `/admin/attendance/regularization/export?${params}`;
    toast.success("Excel export initiated");
};

// Create Logic
const showCreateModal = ref(false);
const createForm = ref({
    employee_id: '',
    date: '',
    regularized_in_time: '',
    regularized_out_time: '',
    reason: ''
});

const openCreateModal = () => {
    createForm.value = { 
        employee_id: '', 
        date: new Date().toISOString().split('T')[0], 
        regularized_in_time: '', 
        regularized_out_time: '', 
        reason: '' 
    };
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
};

const submitCreate = () => {
    router.post('/admin/attendance/regularization/store', createForm.value, {
        onSuccess: () => {
            closeCreateModal();
            toast.success("Correction request saved");
        },
        onError: () => toast.error("Failed to save correction")
    });
};
</script>

<style scoped>
input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(0.4) sepia(1) saturate(5) hue-rotate(200deg);
    cursor: pointer;
}
</style>
