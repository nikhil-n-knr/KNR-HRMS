<template>
  <Head title="Project Management Dashboard" />

  <div class="min-h-screen bg-[#f3f4f8] p-4 lg:p-8 font-sans">
    <!-- Top Header & Summary -->
    <div class="mb-8 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between bg-white p-6 rounded-3xl shadow-sm border border-slate-100/50 backdrop-blur-xl">
      <div>
        <h1 class="text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-indigo-900 to-indigo-600 tracking-tight">PM Intelligence Dashboard</h1>
        <p class="mt-2 text-slate-500 font-bold text-sm tracking-wide">Real-time productivity, workload, and health monitoring.</p>
      </div>
      
      <div class="flex items-center gap-3">
         <div class="bg-emerald-50 px-5 py-2.5 rounded-2xl shadow-sm border border-emerald-100 flex items-center gap-3">
            <div class="relative flex h-3 w-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
            </div>
            <span class="text-xs font-black text-emerald-800 uppercase tracking-widest">Live System Active</span>
         </div>
         <button @click="refreshData" class="p-3 bg-indigo-50 rounded-2xl shadow-sm border border-indigo-100 hover:bg-indigo-100 transition-all text-indigo-600">
            <RefreshCwIcon class="w-5 h-5" :class="{'animate-spin': refreshing}" />
         </button>
      </div>
    </div>

    <!-- Analytics Cards Grid (4 & 5 layout mix) -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4 mb-8">
      <div class="col-span-2 relative overflow-hidden bg-gradient-to-br from-indigo-600 to-purple-700 p-6 rounded-3xl border border-indigo-500/30 shadow-lg text-white group">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-all"></div>
        <div class="flex items-start justify-between mb-4 relative z-10">
          <div class="p-3 rounded-2xl bg-white/20 backdrop-blur-md">
            <TrendingUpIcon class="w-6 h-6 text-white" />
          </div>
          <span class="text-xs font-black px-3 py-1 rounded-xl bg-white/20 backdrop-blur-md text-white border border-white/20 shadow-sm">
            +5.4% vs last week
          </span>
        </div>
        <div class="text-4xl font-black tracking-tight relative z-10">{{ productivity.productivity_index }}%</div>
        <div class="text-sm font-black text-indigo-200 mt-1 uppercase tracking-widest relative z-10">Productivity Index</div>
      </div>

      <div class="col-span-1 bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
         <div class="flex justify-between items-start">
            <div class="p-3 rounded-2xl bg-indigo-50 text-indigo-600"><UsersIcon class="w-6 h-6" /></div>
         </div>
         <div class="mt-4">
            <div class="text-3xl font-black text-slate-800">{{ attendanceStats.checked_in_today }}</div>
            <div class="text-[10px] font-black text-slate-400 mt-1 uppercase tracking-widest">In Office</div>
         </div>
      </div>

      <div class="col-span-1 bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
         <div class="flex justify-between items-start">
            <div class="p-3 rounded-2xl bg-rose-50 text-rose-600"><UserMinusIcon class="w-6 h-6" /></div>
         </div>
         <div class="mt-4">
            <div class="text-3xl font-black text-slate-800">{{ attendanceStats.not_checked_in }}</div>
            <div class="text-[10px] font-black text-slate-400 mt-1 uppercase tracking-widest">Not Checked-In</div>
         </div>
      </div>

      <div class="col-span-1 bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
         <div class="flex justify-between items-start">
            <div class="p-3 rounded-2xl bg-amber-50 text-amber-600"><ClockIcon class="w-6 h-6" /></div>
         </div>
         <div class="mt-4">
            <div class="text-3xl font-black text-slate-800">{{ attendanceStats.not_checked_out }}</div>
            <div class="text-[10px] font-black text-slate-400 mt-1 uppercase tracking-widest">Not Checked-Out</div>
         </div>
      </div>

      <div class="col-span-1 bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
         <div class="flex justify-between items-start">
            <div class="p-3 rounded-2xl bg-orange-50 text-orange-600"><CalendarIcon class="w-6 h-6" /></div>
         </div>
         <div class="mt-4">
            <div class="text-3xl font-black text-slate-800">{{ attendanceStats.on_leave }}</div>
            <div class="text-[10px] font-black text-slate-400 mt-1 uppercase tracking-widest">On Leave Today</div>
         </div>
      </div>

      <div class="col-span-1 bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
         <div class="flex justify-between items-start">
            <div class="p-3 rounded-2xl bg-red-50 text-red-600"><AlertCircleIcon class="w-6 h-6" /></div>
         </div>
         <div class="mt-4">
            <div class="text-3xl font-black text-rose-600">{{ attendanceStats.absent_without_leave }}</div>
            <div class="text-[10px] font-black text-slate-400 mt-1 uppercase tracking-widest leading-tight">Absent Without Leave</div>
         </div>
      </div>

      <div class="col-span-1 bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
         <div class="flex justify-between items-start">
            <div class="p-3 rounded-2xl bg-emerald-50 text-emerald-600"><CheckCircle2Icon class="w-6 h-6" /></div>
         </div>
         <div class="mt-4">
            <div class="text-3xl font-black text-slate-800">{{ productivity.completed_today }}</div>
            <div class="text-[10px] font-black text-slate-400 mt-1 uppercase tracking-widest">Tasks Done Today</div>
         </div>
      </div>
    </div>

    <!-- Main Grid Content -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      
      <!-- Left Column: Employee Workload Control -->
      <div class="lg:col-span-8 space-y-6">
        
        <!-- Toolbar -->
        <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4">
           <div class="flex items-center gap-3">
              <h2 class="text-lg font-black text-slate-800 uppercase tracking-tight flex items-center gap-2 px-2">
                 <UsersIcon class="w-5 h-5 text-indigo-500" /> Daily Work Assignments
              </h2>
           </div>
           
           <div class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto">
              <div class="relative flex-1 md:w-64">
                 <SearchIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                 <input 
                   v-model="searchQuery"
                   type="text" 
                   placeholder="Search employees..."
                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm font-bold text-slate-700"
                 />
              </div>
              <div class="flex items-center gap-2">
                 <select v-model="filterStatus" class="bg-slate-50 border-none rounded-xl py-2.5 px-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                   <option value="all">All Roles</option>
                   <option value="management">Management</option>
                   <option value="active">Operational</option>
                   <option value="unassigned">No Tasks Assigned</option>
                 </select>
                 <button @click="showHidden = !showHidden" 
                         class="px-4 py-2.5 bg-slate-50 rounded-xl text-sm font-bold text-slate-700 hover:bg-slate-200 transition-all flex items-center gap-2 border border-slate-200">
                   <EyeIcon v-if="!showHidden" class="w-4 h-4" />
                   <EyeOffIcon v-else class="w-4 h-4" />
                 </button>
              </div>
           </div>
        </div>

        <!-- Highlighted Unassigned Section -->
        <div v-if="unassignedEmployees.length > 0 && (filterStatus === 'all' || filterStatus === 'unassigned')" class="bg-rose-50/50 border border-rose-200 rounded-3xl p-6">
           <div class="flex items-center justify-between mb-4">
              <h3 class="text-sm font-black text-rose-800 uppercase tracking-widest flex items-center gap-2">
                 <AlertCircleIcon class="w-4 h-4" /> Action Required: No Tasks Assigned Today
              </h3>
              <span class="px-3 py-1 bg-rose-200 text-rose-800 rounded-lg text-xs font-black">{{ unassignedEmployees.length }} Employees</span>
           </div>
           <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
               <div v-for="emp in unassignedEmployees" :key="emp.id"
                    class="bg-white p-4 rounded-2xl shadow-sm border border-rose-100 hover:shadow-md transition-all cursor-pointer relative group"
                    @click="openEmployeeModal(emp)">
                    <div class="flex items-center justify-between gap-3">
                       <div class="flex items-center gap-3 flex-1 min-w-0">
                          <img :src="emp.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(emp.name || 'U')}&background=random`" @error="$event.target.src=`https://ui-avatars.com/api/?name=${encodeURIComponent(emp.name || 'U')}&background=random`" class="w-10 h-10 rounded-xl object-cover ring-2 ring-rose-50" />
                          <div class="flex-1 min-w-0">
                             <h4 class="font-black text-slate-800 truncate text-sm">{{ emp.name }}</h4>
                             <p class="text-[10px] font-bold text-slate-500 uppercase truncate">{{ emp.designation || 'N/A' }}</p>
                          </div>
                       </div>
                       <button @click.stop="openSettings(emp)" class="text-slate-300 hover:text-rose-600 bg-slate-50 hover:bg-rose-50 p-2 rounded-xl transition-all opacity-0 group-hover:opacity-100 shrink-0">
                          <SettingsIcon class="w-4 h-4" />
                        </button>
                    </div>
               </div>
           </div>
        </div>

        <!-- Main Employee Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
          <div v-for="emp in workingEmployees" :key="emp.id"
               class="bg-white rounded-3xl border shadow-sm hover:shadow-xl transition-all cursor-pointer relative group flex flex-col"
               :class="{
                  'border-rose-300 ring-2 ring-rose-50': emp.is_over_allocated,
                  'border-amber-300 ring-2 ring-amber-50': emp.is_under_allocated && emp.today_hours > 0,
                  'border-slate-100 hover:border-indigo-200': !emp.is_over_allocated && (!emp.is_under_allocated || emp.today_hours === 0)
               }"
               @click="openEmployeeModal(emp)">
            
            <div class="p-5 flex-1 relative z-10">
               <div class="flex justify-between items-start mb-4">
                  <div class="flex items-center gap-3">
                     <div class="relative">
                        <img :src="emp.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(emp.name || 'U')}&background=random`" @error="$event.target.src=`https://ui-avatars.com/api/?name=${encodeURIComponent(emp.name || 'U')}&background=random`" class="w-12 h-12 rounded-2xl object-cover" />
                        <div :class="`absolute -bottom-1 -right-1 w-3 h-3 rounded-full border-2 border-white ${emp.has_checked_in ? 'bg-emerald-500' : 'bg-slate-300'}`"></div>
                     </div>
                     <div>
                        <h3 class="font-black text-slate-800 leading-tight text-[15px]">{{ emp.name }}</h3>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">{{ emp.designation || 'N/A' }}</p>
                     </div>
                  </div>
                  <button @click.stop="openSettings(emp)" class="text-slate-300 hover:text-indigo-600 bg-slate-50 hover:bg-indigo-50 p-2 rounded-xl transition-colors">
                     <SettingsIcon class="w-4 h-4" />
                  </button>
               </div>

               <!-- Workload Badges -->
               <div class="flex items-center justify-between mb-2">
                  <span class="text-[10px] font-black uppercase text-slate-500 tracking-widest">Workload Status</span>
                  <span v-if="emp.is_over_allocated" class="text-[10px] font-black bg-rose-100 text-rose-700 px-2 py-0.5 rounded-md uppercase tracking-wider">Over Allocated</span>
                  <span v-else-if="emp.is_under_allocated && emp.today_hours > 0" class="text-[10px] font-black bg-amber-100 text-amber-700 px-2 py-0.5 rounded-md uppercase tracking-wider">Under Allocated</span>
                  <span v-else-if="emp.today_hours > 0" class="text-[10px] font-black bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-md uppercase tracking-wider">Optimal</span>
               </div>

               <!-- Progress -->
               <div class="flex items-center gap-3">
                  <div class="h-2 flex-1 bg-slate-100 rounded-full overflow-hidden">
                     <div :class="`h-full rounded-full transition-all duration-500 ${getWorkloadProgressColor(emp.today_hours)}`" 
                          :style="`width: ${Math.min(100, (emp.today_hours / 10) * 100)}%`"></div>
                  </div>
                  <span :class="`text-sm font-black ${getWorkloadClass(emp.today_hours)} w-8 text-right`">{{ emp.today_hours }}h</span>
               </div>
            </div>

            <!-- Bottom Status Bar -->
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 rounded-b-3xl flex items-center justify-between relative z-10">
               <div class="flex items-center gap-2">
                  <span v-if="emp.is_management" class="text-[9px] font-black px-2 py-1 rounded bg-purple-100 text-purple-700 uppercase tracking-widest">MGMT</span>
                  <span v-if="emp.is_non_working" class="text-[9px] font-black px-2 py-1 rounded bg-slate-200 text-slate-600 uppercase tracking-widest">Inactive</span>
                  <span v-if="emp.is_forced_visible" class="text-[9px] font-black px-2 py-1 rounded bg-amber-100 text-amber-700 uppercase tracking-widest flex items-center gap-1">
                    <ZapIcon class="w-3 h-3" /> Excluded (Active)
                  </span>
               </div>
               <span class="text-[10px] font-black uppercase text-slate-400">View 360 →</span>
            </div>

            <div v-if="emp.visibility === 'hidden' && !emp.is_forced_visible" class="absolute inset-0 bg-white/70 backdrop-blur-[2px] rounded-3xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all z-20">
               <span class="bg-slate-800 text-white px-4 py-2 rounded-xl text-xs font-black shadow-xl tracking-widest uppercase">Hidden Role</span>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Quick Look & Monitoring -->
      <div class="lg:col-span-4 space-y-6">
        
        <!-- Quick Look: Delays & Monitoring -->
        <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden flex flex-col">
          <div class="p-6 bg-gradient-to-r from-orange-50 to-rose-50 border-b border-rose-100/50">
             <h2 class="font-black text-rose-900 flex items-center gap-2 uppercase tracking-tight text-sm">
               <AlertCircleIcon class="w-5 h-5 text-rose-500" /> Delay & Extension Monitor
             </h2>
          </div>
          
          <div class="p-6 grid grid-cols-2 gap-4 border-b border-slate-50">
             <div class="text-center">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Delayed Tasks</span>
                <div class="text-3xl font-black text-rose-600 mt-1">{{ productivity.delayed_tasks }}</div>
             </div>
             <div class="text-center border-l border-slate-100">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Extended Timelines</span>
                <div class="text-3xl font-black text-orange-500 mt-1">{{ productivity.extended_timelines }}</div>
             </div>
          </div>

          <div class="p-6 space-y-5 bg-slate-50/50 flex-1">
             <div>
                <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">Tasks Nearing Deadlines</h3>
                <div class="space-y-3">
                   <div v-for="task in quickLook.nearing_deadlines" :key="task.id" class="bg-white p-3 rounded-xl shadow-sm border border-slate-100 flex justify-between items-center group cursor-pointer hover:border-indigo-300">
                      <span class="text-xs font-bold text-slate-700 truncate pr-4">{{ task.title }}</span>
                      <span class="shrink-0 px-2 py-1 bg-amber-100 text-amber-800 text-[10px] font-black rounded-lg">DUE SOON</span>
                   </div>
                   <div v-if="!quickLook.nearing_deadlines.length" class="text-xs font-bold text-slate-400 text-center py-2">No impending deadlines</div>
                </div>
             </div>
             
             <div>
                <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">High Priority Action</h3>
                <div class="space-y-3">
                   <div v-for="task in quickLook.important" :key="task.id" class="bg-rose-50/50 p-3 rounded-xl border border-rose-100 flex justify-between items-center group cursor-pointer hover:bg-rose-50">
                      <span class="text-xs font-bold text-slate-700 truncate pr-4">{{ task.title }}</span>
                      <ZapIcon class="w-4 h-4 text-rose-500 shrink-0" />
                   </div>
                   <div v-if="!quickLook.important.length" class="text-xs font-bold text-slate-400 text-center py-2">No critical tasks pending</div>
                </div>
             </div>
          </div>
        </div>

        <!-- Bug Tracker Summary -->
        <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden flex flex-col h-[500px]">
          <div class="p-6 bg-slate-900 border-b border-slate-800 flex items-center justify-between shrink-0">
            <h2 class="font-black text-white flex items-center gap-2 uppercase tracking-tight text-sm">
              <BugIcon class="w-5 h-5 text-emerald-400" /> Bug Tracker Intelligence
            </h2>
            <div class="flex items-center gap-3">
               <span class="px-2 py-1 bg-white/10 rounded text-[10px] font-black text-white tracking-widest">{{ bugStats.today.raised }} Raised Today</span>
            </div>
          </div>
          
          <div class="flex-1 overflow-y-auto p-4 space-y-2 bg-slate-50">
            <!-- Bug List -->
            <div v-for="bug in recentBugs" :key="bug.id" class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm hover:border-slate-300 transition-colors cursor-pointer">
               <div class="flex items-start justify-between mb-2">
                  <div class="flex items-center gap-2">
                     <span :class="`w-2 h-2 rounded-full ${getPriorityBg(bug.priority)}`"></span>
                     <span class="text-xs font-black text-slate-500 uppercase tracking-wider">#{{ bug.id }}</span>
                     <span v-if="bug.severity" class="text-[9px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase">{{ bug.severity }}</span>
                  </div>
                  <span class="text-[10px] font-bold text-slate-400">{{ formatDate(bug.created_at) }}</span>
               </div>
               <p class="text-sm font-bold text-slate-800 leading-snug mb-3">{{ bug.subject }}</p>
               <div class="flex items-center justify-between">
                  <div class="flex -space-x-2">
                     <img v-if="bug.reporter" :src="bug.reporter.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(bug.reporter.name || 'U')}&background=random`" @error="$event.target.src=`https://ui-avatars.com/api/?name=${encodeURIComponent(bug.reporter.name || 'U')}&background=random`" class="w-6 h-6 rounded-full ring-2 ring-white bg-slate-200" :title="`Raised by ${bug.reporter.name}`" />
                  </div>
                  <span :class="`px-2 py-1 text-[9px] font-black uppercase rounded-lg ${bug.status === 'Closed' ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700'}`">
                     {{ bug.status }}
                  </span>
               </div>
            </div>
            
            <div v-if="!recentBugs.length" class="h-full flex flex-col items-center justify-center text-slate-400">
               <BugIcon class="w-12 h-12 mb-2 opacity-20" />
               <p class="text-sm font-bold">No recent bugs found.</p>
            </div>
          </div>
          <div class="p-3 bg-white border-t border-slate-100 shrink-0">
             <Link :href="route('bugs.index')" class="block w-full py-2 bg-slate-50 hover:bg-slate-100 text-center rounded-xl text-xs font-black text-slate-600 uppercase tracking-widest transition-colors">
                View All Bug Reports →
             </Link>
          </div>
        </div>

      </div>
    </div>

    <!-- Settings Modal -->
    <Modal :show="!!editingEmp" @close="editingEmp = null" max-width="md">
       <div class="p-8">
          <div class="flex justify-between items-center mb-6">
             <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">Visibility Controls</h2>
             <button @click="editingEmp = null" class="p-2 bg-slate-50 rounded-xl hover:bg-slate-100"><XIcon class="w-5 h-5 text-slate-400"/></button>
          </div>
          
          <div v-if="editingEmp" class="space-y-6">
             <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <img :src="editingEmp.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(editingEmp.name || 'U')}&background=random`" @error="$event.target.src=`https://ui-avatars.com/api/?name=${encodeURIComponent(editingEmp.name || 'U')}&background=random`" class="w-12 h-12 rounded-xl object-cover" />
                <div>
                   <h3 class="font-black text-slate-900 text-sm">{{ editingEmp.name }}</h3>
                   <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">{{ editingEmp.designation }}</p>
                </div>
             </div>

             <div class="space-y-4">
                <label class="flex items-center gap-3 p-4 bg-white border-2 border-slate-100 rounded-2xl cursor-pointer hover:border-indigo-200 transition-all" :class="{'border-indigo-500 bg-indigo-50/50': settingsForm.is_management}">
                   <input type="checkbox" v-model="settingsForm.is_management" class="w-5 h-5 rounded-lg border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                   <div>
                      <span class="block text-sm font-black text-slate-900">Mark as Management</span>
                      <span class="block text-[10px] font-bold text-slate-500 uppercase">Hide workload alerts for this user</span>
                   </div>
                </label>

                <label class="flex items-center gap-3 p-4 bg-white border-2 border-slate-100 rounded-2xl cursor-pointer hover:border-indigo-200 transition-all" :class="{'border-rose-500 bg-rose-50/50': settingsForm.is_non_working}">
                   <input type="checkbox" v-model="settingsForm.is_non_working" class="w-5 h-5 rounded-lg border-slate-300 text-rose-600 focus:ring-rose-500" />
                   <div>
                      <span class="block text-sm font-black text-slate-900">Non-working / Inactive</span>
                      <span class="block text-[10px] font-bold text-slate-500 uppercase">Excludes from all workload tracking</span>
                   </div>
                </label>

                <div class="space-y-2 pt-2">
                   <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Dashboard Default View</label>
                   <div class="grid grid-cols-2 gap-2">
                      <button 
                        @click="settingsForm.dashboard_visibility = 'visible'"
                        :class="`py-3 rounded-2xl font-black text-[10px] tracking-widest transition-all ${settingsForm.dashboard_visibility === 'visible' ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'}`">
                        VISIBLE
                      </button>
                      <button 
                        @click="settingsForm.dashboard_visibility = 'hidden'"
                        :class="`py-3 rounded-2xl font-black text-[10px] tracking-widest transition-all ${settingsForm.dashboard_visibility === 'hidden' ? 'bg-slate-800 text-white shadow-md' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'}`">
                        HIDDEN
                      </button>
                   </div>
                   <p class="text-[9px] text-slate-400 font-bold px-1 mt-1 text-center">Hidden employees automatically appear if assigned tasks.</p>
                </div>
             </div>

             <div class="pt-4 flex gap-3">
                <button @click="saveSettings" class="w-full py-4 bg-indigo-600 text-white font-black rounded-2xl shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all uppercase tracking-widest text-xs">Update Settings</button>
             </div>
          </div>
       </div>
    </Modal>

    <!-- Employee 360 Modal -->
    <Employee360Modal :show="showEmployee360" :employee="selectedEmployee" @close="closeEmployeeModal" />

  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
  UsersIcon, BugIcon, ClockIcon, AlertCircleIcon, TrendingUpIcon, 
  SearchIcon, EyeIcon, EyeOffIcon, SettingsIcon, RefreshCwIcon,
  ZapIcon, CalendarIcon, UserMinusIcon, CheckCircle2Icon, XIcon
} from 'lucide-vue-next';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import Modal from '@/Components/Modal.vue';
import Employee360Modal from './Components/Employee360Modal.vue';
import { useToastStore } from '@/stores/toast';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

dayjs.extend(relativeTime);

const props = defineProps({
  employees: Array,
  productivity: Object,
  attendanceStats: Object,
  bugStats: Object,
  recentBugs: Array,
  quickLook: Object,
});

const toast = useToastStore();
const refreshing = ref(false);
const searchQuery = ref('');
const filterStatus = ref('all');
const showHidden = ref(false);
const editingEmp = ref(null);

const showEmployee360 = ref(false);
const selectedEmployee = ref(null);

const settingsForm = reactive({
  is_management: false,
  is_non_working: false,
  dashboard_visibility: 'visible'
});

const filteredEmployeesRaw = computed(() => {
  return props.employees.filter(emp => {
    // Search
    const matchesSearch = (emp.name || '').toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                        (emp.designation || '').toLowerCase().includes(searchQuery.value.toLowerCase());
    
    // Visibility (If forced_visible is true from backend, they always pass visibility check unless strictly hidden requested)
    const isExcluded = (emp.visibility === 'hidden' || emp.visibility === 'excluded') && !emp.is_forced_visible;
    const visibilityPass = showHidden.value || !isExcluded;
    
    // Status Filter
    let statusPass = true;
    if (filterStatus.value === 'management') statusPass = emp.is_management;
    if (filterStatus.value === 'active') statusPass = !emp.is_management && !emp.is_non_working;
    if (filterStatus.value === 'unassigned') statusPass = emp.today_hours === 0 && !emp.is_non_working && !emp.is_management;

    return matchesSearch && visibilityPass && statusPass;
  });
});

const unassignedEmployees = computed(() => {
    return filteredEmployeesRaw.value.filter(emp => emp.today_hours === 0 && !emp.is_management && !emp.is_non_working);
});

const workingEmployees = computed(() => {
    if (filterStatus.value === 'unassigned') return [];
    return filteredEmployeesRaw.value.filter(emp => emp.today_hours > 0 || emp.is_management || emp.is_non_working);
});

const getWorkloadClass = (hours) => {
  if (hours === 0) return 'text-slate-400';
  if (hours < 7) return 'text-amber-500';
  if (hours > 9) return 'text-rose-500';
  return 'text-emerald-500';
};

const getWorkloadProgressColor = (hours) => {
  if (hours === 0) return 'bg-slate-300';
  if (hours < 7) return 'bg-amber-400 shadow-[0_0_10px_rgba(251,191,36,0.5)]';
  if (hours > 9) return 'bg-rose-500 shadow-[0_0_10px_rgba(244,63,94,0.5)]';
  return 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]';
};

const getPriorityBg = (priority) => {
  switch (priority) {
    case 'Critical': return 'bg-rose-500';
    case 'High':     return 'bg-orange-500';
    case 'Medium':   return 'bg-indigo-500';
    default:         return 'bg-slate-400';
  }
};

const formatDate = (date) => dayjs(date).fromNow();

const openEmployeeModal = (emp) => {
  selectedEmployee.value = emp;
  showEmployee360.value = true;
};

const closeEmployeeModal = () => {
  showEmployee360.value = false;
  setTimeout(() => { selectedEmployee.value = null; }, 300); // Wait for modal animation
};

const openSettings = (emp) => {
  editingEmp.value = emp;
  settingsForm.is_management = emp.is_management;
  settingsForm.is_non_working = emp.is_non_working;
  settingsForm.dashboard_visibility = emp.visibility;
};

const saveSettings = () => {
  router.post(route('projects.management-dashboard.employee.update', editingEmp.value.id), settingsForm, {
    preserveScroll: true,
    onSuccess: () => {
      editingEmp.value = null;
      toast.success('Preferences updated successfully');
    }
  });
};

const refreshData = () => {
  refreshing.value = true;
  router.reload({ 
    only: ['employees', 'productivity', 'attendanceStats', 'bugStats', 'recentBugs', 'quickLook'],
    onFinish: () => refreshing.value = false 
  });
};
</script>

<style scoped>
/* Any custom styles */
</style>
