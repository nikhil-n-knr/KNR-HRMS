<template>
  <Head title="Ops360" />

  <div class="min-h-screen bg-slate-50 py-8 pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
      <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Employee Work</p>
            <h1 class="text-3xl font-black text-slate-900 mt-1">Ops360 Hub</h1>
            <p class="text-sm text-slate-500 mt-2">Unified navigation for Employee 360 and Squad_Ops with trend snapshots.</p>
          </div>
          <div class="flex items-center gap-2">
            <a href="/employee/work" class="h-10 px-4 rounded-xl border border-slate-200 text-slate-700 text-xs font-black uppercase tracking-wider inline-flex items-center justify-center">Back To My Work</a>
            <a v-if="opsLaunchers.employee360_url" :href="opsLaunchers.employee360_url" class="h-10 px-4 rounded-xl bg-indigo-600 text-white text-xs font-black uppercase tracking-wider inline-flex items-center justify-center">Employee 360</a>
            <a v-if="opsLaunchers.devops_global_url" :href="opsLaunchers.devops_global_url" class="h-10 px-4 rounded-xl bg-emerald-600 text-white text-xs font-black uppercase tracking-wider inline-flex items-center justify-center">Squad_Ops</a>
          </div>
        </div>
      </section>

      <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex bg-slate-100 rounded-xl p-1 w-full lg:w-fit">
          <button type="button" @click="setTab('employee360')" class="px-4 py-2 rounded-lg text-xs font-black uppercase tracking-widest transition-all" :class="activeTabValue === 'employee360' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'">Employee 360</button>
          <button type="button" @click="setTab('squad_ops')" class="px-4 py-2 rounded-lg text-xs font-black uppercase tracking-widest transition-all" :class="activeTabValue === 'squad_ops' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'">Squad_Ops</button>
        </div>
      </section>

      <section class="rounded-3xl border border-slate-200 bg-white p-5 lg:p-6 shadow-sm space-y-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
          <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Ops360 Live Scoreboard</p>
            <h2 class="text-lg font-black text-slate-900 mt-1">Real-time Team Stats</h2>
          </div>
          <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider" :class="trackStatusClass(overview.track_status)">
            {{ overview.track_status || 'On Track' }}
          </span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Projects</p>
            <p class="text-xl font-black text-slate-900 mt-1">{{ overview.total_projects || 0 }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Teams</p>
            <p class="text-xl font-black text-slate-900 mt-1">{{ overview.total_teams || 0 }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Members</p>
            <p class="text-xl font-black text-slate-900 mt-1">{{ overview.total_members || 0 }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-emerald-50 p-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-emerald-700">Present Today</p>
            <p class="text-xl font-black text-emerald-900 mt-1">{{ overview.present_today || 0 }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-cyan-50 p-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-cyan-700">Timesheet Filled</p>
            <p class="text-xl font-black text-cyan-900 mt-1">{{ overview.timesheet_filled_today || 0 }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-indigo-50 p-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-indigo-700">Task Completed</p>
            <p class="text-xl font-black text-indigo-900 mt-1">{{ overview.tasks_completed || 0 }} / {{ overview.tasks_total || 0 }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-3">
          <div class="rounded-2xl border border-slate-200 bg-white p-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Approvals Raised</p>
            <p class="text-lg font-black text-slate-900 mt-1">{{ overview.approvals_raised_today || 0 }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white p-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Pending</p>
            <p class="text-lg font-black text-amber-700 mt-1">{{ overview.approvals_pending || 0 }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white p-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Checked</p>
            <p class="text-lg font-black text-emerald-700 mt-1">{{ overview.approvals_checked || 0 }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white p-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Not Checked</p>
            <p class="text-lg font-black text-rose-700 mt-1">{{ overview.approvals_unchecked || 0 }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white p-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Off Plan</p>
            <p class="text-lg font-black text-rose-700 mt-1">{{ overview.not_working_as_planned || 0 }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white p-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Leave Applied</p>
            <p class="text-lg font-black text-slate-900 mt-1">{{ overview.leave_applied_today || 0 }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white p-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">WFH Applied</p>
            <p class="text-lg font-black text-slate-900 mt-1">{{ overview.wfh_applied_today || 0 }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <div class="rounded-2xl border border-slate-200 bg-rose-50 p-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-rose-700">Bugs Open</p>
            <p class="text-lg font-black text-rose-900 mt-1">{{ overview.bugs_open_total || 0 }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-emerald-50 p-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-emerald-700">Bugs Closed Today</p>
            <p class="text-lg font-black text-emerald-900 mt-1">{{ overview.bugs_closed_today || 0 }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-amber-50 p-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-amber-700">Bugs Pending</p>
            <p class="text-lg font-black text-amber-900 mt-1">{{ overview.bugs_pending_total || 0 }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Bugs Total</p>
            <p class="text-lg font-black text-slate-900 mt-1">{{ overview.bugs_total_count || 0 }}</p>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
          <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-3">Last 7 Days Team Ops Trend</p>
          <div class="grid grid-cols-7 gap-2 h-44 items-end">
            <div v-for="day in dailyOpsSeries" :key="`daily-ops-${day.date}`" class="flex flex-col items-center justify-end gap-1">
              <div class="w-full grid grid-cols-4 gap-1 items-end">
                <div class="rounded-t bg-emerald-500" :style="{ height: `${dailyOpsBarHeight(day.present)}px`, minHeight: '4px' }" title="Present"></div>
                <div class="rounded-t bg-cyan-500" :style="{ height: `${dailyOpsBarHeight(day.timesheet_filled)}px`, minHeight: '4px' }" title="Timesheet"></div>
                <div class="rounded-t bg-indigo-500" :style="{ height: `${dailyOpsBarHeight(day.tasks_completed)}px`, minHeight: '4px' }" title="Tasks Completed"></div>
                <div class="rounded-t bg-amber-500" :style="{ height: `${dailyOpsBarHeight(day.approvals_raised)}px`, minHeight: '4px' }" title="Approvals"></div>
              </div>
              <p class="text-[10px] font-black text-slate-500">{{ day.label }}</p>
            </div>
          </div>
          <div class="flex flex-wrap gap-3 mt-3 text-[10px] font-black uppercase tracking-wider text-slate-500">
            <span class="inline-flex items-center gap-1"><span class="inline-block w-2 h-2 rounded bg-emerald-500"></span> Present</span>
            <span class="inline-flex items-center gap-1"><span class="inline-block w-2 h-2 rounded bg-cyan-500"></span> Timesheet</span>
            <span class="inline-flex items-center gap-1"><span class="inline-block w-2 h-2 rounded bg-indigo-500"></span> Completed</span>
            <span class="inline-flex items-center gap-1"><span class="inline-block w-2 h-2 rounded bg-amber-500"></span> Approvals</span>
          </div>
        </div>
      </section>

      <!-- Employee 360 Search & Profile -->
      <section v-if="activeTabValue === 'employee360'" class="space-y-4">

        <!-- Search Panel -->
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-700 mb-3">Employee 360 Profile Search</p>
          <div v-if="!props.can_view_360" class="flex items-center gap-2 text-sm text-slate-500 font-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            You need Manager, HR Manager, Admin, or Super Admin access to search employee 360 profiles.
          </div>
          <div v-else class="flex flex-col lg:flex-row gap-4 items-end flex-wrap">
            <!-- Employee Search Input -->
            <div class="flex-1 min-w-[220px]">
              <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 block mb-1.5">Search Employee</label>
              <div class="relative">
                <input
                  v-model="empSearch"
                  type="text"
                  placeholder="Type name or designation..."
                  class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 pr-8"
                  @focus="showDropdown = true"
                  @blur="delayHideDropdown"
                />
                <svg xmlns="http://www.w3.org/2000/svg" class="absolute right-2.5 top-3 h-4 w-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                <ul v-if="showDropdown && filteredEmployees.length" class="absolute z-20 w-full mt-1 max-h-52 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl divide-y divide-slate-50">
                  <li
                    v-for="emp in filteredEmployees"
                    :key="emp.id"
                    @mousedown.prevent="selectEmployee(emp)"
                    class="px-4 py-2.5 hover:bg-indigo-50 cursor-pointer"
                  >
                    <p class="text-sm font-bold text-slate-800">{{ emp.name }}</p>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase">{{ emp.designation }}</p>
                  </li>
                </ul>
                <p v-if="showDropdown && empSearch && !filteredEmployees.length" class="absolute z-20 w-full mt-1 rounded-xl border border-slate-200 bg-white shadow-xl px-4 py-3 text-xs text-slate-400 font-semibold">No employees match your search.</p>
              </div>
            </div>
            <!-- Date Range -->
            <div>
              <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 block mb-1.5">From</label>
              <input v-model="dateStart" type="date" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-400" />
            </div>
            <div>
              <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 block mb-1.5">To</label>
              <input v-model="dateEnd" type="date" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-400" />
            </div>
            <!-- Load Button -->
            <button
              @click="load360Profile"
              :disabled="!selectedEmployee || profile360Loading"
              class="h-10 px-5 rounded-xl bg-indigo-600 text-white text-xs font-black uppercase tracking-wider inline-flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all hover:bg-indigo-700"
            >
              <svg v-if="profile360Loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
              {{ profile360Loading ? 'Loading…' : 'Load 360 Profile' }}
            </button>
            <!-- Export Button -->
            <button
              v-if="profile360Data"
              @click="export360"
              class="h-10 px-5 rounded-xl bg-emerald-600 text-white text-xs font-black uppercase tracking-wider inline-flex items-center gap-2 hover:bg-emerald-700 transition-all"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
              Export Dossier
            </button>
          </div>
          <p v-if="profile360Error" class="mt-3 text-xs font-bold text-rose-600">{{ profile360Error }}</p>
        </div>

        <!-- Empty State -->
        <div v-if="props.can_view_360 && !profile360Data && !profile360Loading" class="rounded-3xl border border-slate-200 bg-white p-12 shadow-sm flex flex-col items-center justify-center text-slate-400 gap-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <p class="text-base font-bold">Search and select an employee to view their complete 360 profile.</p>
        </div>

        <!-- 360 Profile Display -->
        <div v-if="profile360Data" class="space-y-4">

          <!-- Hero Card -->
          <div class="rounded-3xl bg-gradient-to-r from-indigo-900 to-indigo-800 p-6 text-white shadow-xl overflow-hidden relative">
            <div class="absolute right-0 top-0 opacity-5 scale-150 translate-x-10 -translate-y-10">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-64 w-64" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            </div>
            <div class="flex items-center gap-6 relative z-10">
              <div class="h-20 w-20 rounded-2xl bg-white/20 backdrop-blur-md overflow-hidden ring-4 ring-white/10 flex items-center justify-center shadow-2xl shrink-0">
                <img v-if="profile360Data.profile.avatar" :src="profile360Data.profile.avatar" class="h-full w-full object-cover rounded-xl" />
                <span v-else class="text-3xl font-black text-indigo-100 flex items-center h-full w-full bg-indigo-500/50 rounded-xl justify-center">{{ profile360Data.profile.name.charAt(0) }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 flex-wrap">
                  <h2 class="text-2xl font-black tracking-tight">{{ profile360Data.profile.name }}</h2>
                  <span :class="profile360Data.metrics.overview.burnout_risk === 'High' ? 'bg-red-500/20 text-red-200 border-red-500/30' : 'bg-emerald-500/20 text-emerald-200 border-emerald-500/30'" class="text-[10px] px-3 py-1 rounded-full border uppercase tracking-widest font-bold">
                    {{ profile360Data.metrics.overview.burnout_risk === 'High' ? '⚠ High Burnout Risk' : '✓ Optimal Path' }}
                  </span>
                </div>
                <p class="text-indigo-200 font-semibold mt-1">{{ profile360Data.profile.designation }} &bull; {{ profile360Data.profile.department }}</p>
              </div>
            </div>
          </div>

          <!-- KPI Row -->
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
              <p class="text-[10px] font-black uppercase text-slate-400">Scrum Velocity</p>
              <h3 class="text-3xl font-black text-indigo-600 mt-2">{{ profile360Data.metrics.overview.scrum_velocity }} <span class="text-sm font-medium text-slate-400">pts</span></h3>
              <p class="text-xs text-slate-400 mt-2 font-medium bg-slate-50 p-2 rounded-lg text-center">{{ profile360Data.metrics.overview.hours_burned }} hrs burned</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
              <p class="text-[10px] font-black uppercase text-slate-400">Attendance Reliability</p>
              <h3 class="text-3xl font-black mt-2" :class="profile360Data.metrics.overview.reliability_score > 90 ? 'text-emerald-600' : 'text-amber-600'">{{ profile360Data.metrics.overview.reliability_score }}%</h3>
              <p class="text-[10px] text-slate-400 mt-2 font-bold uppercase">{{ profile360Data.metrics.timesheet.total_hours }} hrs logged</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
              <p class="text-[10px] font-black uppercase text-slate-400">Delivery Output</p>
              <h3 class="text-3xl font-black text-blue-600 mt-2">{{ profile360Data.metrics.projects.completed }} <span class="text-sm font-medium text-slate-400">tasks</span></h3>
              <p class="text-xs font-medium mt-2 bg-rose-50 text-rose-600 p-2 rounded-lg text-center">{{ profile360Data.metrics.projects.overdue }} overdue</p>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 p-5 shadow-sm">
              <p class="text-[10px] font-black uppercase text-amber-700">Quality Deep-Dive</p>
              <h3 class="text-3xl font-black text-amber-600 mt-2">{{ profile360Data.metrics.quality.resolved }} <span class="text-xs font-medium text-amber-500">fixed</span></h3>
              <div class="flex justify-between mt-2 text-[10px] font-bold uppercase">
                <span class="text-rose-600">{{ profile360Data.metrics.quality.open || 0 }} open</span>
                <span class="text-amber-800">{{ profile360Data.metrics.quality.sla_breaches }} breaches</span>
              </div>
              <p class="text-[10px] font-bold uppercase text-amber-700 mt-2">Critical open {{ profile360Data.metrics.quality.critical_open || 0 }} | Reopened {{ profile360Data.metrics.quality.reopened || 0 }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Approvals (Raised By Employee)</p>
              <div class="grid grid-cols-2 gap-3 mt-3">
                <div class="rounded-xl bg-slate-50 p-3">
                  <p class="text-[10px] font-bold uppercase text-slate-500">Total</p>
                  <p class="text-xl font-black text-slate-900 mt-1">{{ profile360Data.metrics.approval_stats?.raised_total || 0 }}</p>
                </div>
                <div class="rounded-xl bg-emerald-50 p-3">
                  <p class="text-[10px] font-bold uppercase text-emerald-600">Accepted</p>
                  <p class="text-xl font-black text-emerald-700 mt-1">{{ profile360Data.metrics.approval_stats?.raised_approved || 0 }}</p>
                </div>
                <div class="rounded-xl bg-amber-50 p-3">
                  <p class="text-[10px] font-bold uppercase text-amber-600">Pending</p>
                  <p class="text-xl font-black text-amber-700 mt-1">{{ profile360Data.metrics.approval_stats?.raised_pending || 0 }}</p>
                </div>
                <div class="rounded-xl bg-rose-50 p-3">
                  <p class="text-[10px] font-bold uppercase text-rose-600">Rejected</p>
                  <p class="text-xl font-black text-rose-700 mt-1">{{ profile360Data.metrics.approval_stats?.raised_rejected || 0 }}</p>
                </div>
              </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Approvals (To Approve By Employee)</p>
              <div class="grid grid-cols-2 gap-3 mt-3">
                <div class="rounded-xl bg-slate-50 p-3">
                  <p class="text-[10px] font-bold uppercase text-slate-500">Total</p>
                  <p class="text-xl font-black text-slate-900 mt-1">{{ profile360Data.metrics.approval_stats?.action_total || 0 }}</p>
                </div>
                <div class="rounded-xl bg-emerald-50 p-3">
                  <p class="text-[10px] font-bold uppercase text-emerald-600">Accepted</p>
                  <p class="text-xl font-black text-emerald-700 mt-1">{{ profile360Data.metrics.approval_stats?.action_approved || 0 }}</p>
                </div>
                <div class="rounded-xl bg-amber-50 p-3">
                  <p class="text-[10px] font-bold uppercase text-amber-600">Pending</p>
                  <p class="text-xl font-black text-amber-700 mt-1">{{ profile360Data.metrics.approval_stats?.action_pending || 0 }}</p>
                </div>
                <div class="rounded-xl bg-rose-50 p-3">
                  <p class="text-[10px] font-bold uppercase text-rose-600">Rejected</p>
                  <p class="text-xl font-black text-rose-700 mt-1">{{ profile360Data.metrics.approval_stats?.action_rejected || 0 }}</p>
                </div>
              </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Workload Snapshot</p>
              <div class="space-y-2 mt-3 text-xs font-bold">
                <div class="flex items-center justify-between">
                  <span class="text-slate-500">Projects</span>
                  <span class="text-slate-900">{{ profile360Data.metrics.workload?.projects_total || 0 }} ({{ profile360Data.metrics.workload?.projects_active || 0 }} active)</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-500">Tasks</span>
                  <span class="text-slate-900">{{ profile360Data.metrics.workload?.tasks_completed || 0 }} / {{ profile360Data.metrics.workload?.tasks_total || 0 }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-500">Bugs</span>
                  <span class="text-slate-900">{{ profile360Data.metrics.workload?.bugs_open || 0 }} open / {{ profile360Data.metrics.workload?.bugs_closed || 0 }} closed</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-500">Timesheet Days Filled</span>
                  <span class="text-slate-900">{{ profile360Data.metrics.workload?.timesheet_days_filled || 0 }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-slate-500">Timesheet Days Missing</span>
                  <span class="text-rose-600">{{ profile360Data.metrics.workload?.timesheet_days_missing || 0 }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 xl:grid-cols-[1.4fr_1fr] gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
              <div class="px-5 py-4 border-b border-slate-50 flex items-center justify-between gap-3">
                <div>
                  <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Task Execution Board</p>
                  <p class="text-xs text-slate-500 mt-1">Tasks assigned to the employee with progress, effort, due dates, and latest activity.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-indigo-50 border border-indigo-100 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-indigo-700">{{ taskBoard.pagination?.total || 0 }} tasks</span>
              </div>
              <div class="px-5 py-4 border-b border-slate-50 bg-white flex flex-col lg:flex-row lg:items-end gap-3">
                <div>
                  <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 block mb-1.5">Task Filter</label>
                  <select v-model="taskStatusFilter" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="all">All</option>
                    <option value="open">Open</option>
                    <option value="overdue">Overdue</option>
                    <option value="closed">Closed</option>
                  </select>
                </div>
                <div>
                  <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 block mb-1.5">Priority</label>
                  <select v-model="taskPriorityFilter" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">All</option>
                    <option value="critical">Critical</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                  </select>
                </div>
                <button type="button" @click="applyTaskFilters" class="h-10 px-4 rounded-xl bg-slate-900 text-white text-xs font-black uppercase tracking-wide">Apply Task Filters</button>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3 p-5 border-b border-slate-50 bg-slate-50/70">
                <div class="rounded-xl bg-white border border-slate-200 p-3">
                  <p class="text-[10px] font-bold uppercase text-slate-500">Open Tasks</p>
                  <p class="text-2xl font-black text-slate-900 mt-1">{{ profile360Data.metrics.workload?.tasks_open || 0 }}</p>
                </div>
                <div class="rounded-xl bg-white border border-slate-200 p-3">
                  <p class="text-[10px] font-bold uppercase text-slate-500">Completed Tasks</p>
                  <p class="text-2xl font-black text-emerald-700 mt-1">{{ profile360Data.metrics.workload?.tasks_completed || 0 }}</p>
                </div>
                <div class="rounded-xl bg-white border border-slate-200 p-3">
                  <p class="text-[10px] font-bold uppercase text-slate-500">Timesheet Entries</p>
                  <p class="text-2xl font-black text-cyan-700 mt-1">{{ profile360Data.metrics.timesheet?.entries_total || 0 }}</p>
                </div>
              </div>
              <div class="divide-y divide-slate-100">
                <div v-for="task in paginatedTaskDetails" :key="`task-${task.id}`" class="px-5 py-4 space-y-3 hover:bg-slate-50 transition-colors">
                  <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
                    <div class="min-w-0">
                      <div class="flex flex-wrap items-center gap-2">
                        <a v-if="task.task_url" :href="task.task_url" class="text-sm font-black text-slate-900 truncate hover:text-indigo-700 hover:underline">{{ task.title }}</a>
                        <p v-else class="text-sm font-black text-slate-900 truncate">{{ task.title }}</p>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-black uppercase tracking-wide" :class="task.is_closed ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : (task.is_overdue ? 'bg-rose-100 text-rose-700 border border-rose-200' : 'bg-amber-100 text-amber-700 border border-amber-200')">{{ task.is_closed ? 'Closed' : (task.is_overdue ? 'Overdue' : 'Active') }}</span>
                      </div>
                      <p class="text-xs text-slate-500 mt-1">{{ task.project }} · {{ task.stage }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-[10px] font-black uppercase tracking-wide">
                      <span class="rounded-full bg-slate-100 border border-slate-200 px-2 py-1 text-slate-600">{{ task.status }}</span>
                      <span class="rounded-full bg-indigo-100 border border-indigo-200 px-2 py-1 text-indigo-700">{{ task.priority }}</span>
                      <span class="rounded-full bg-cyan-100 border border-cyan-200 px-2 py-1 text-cyan-700">{{ task.scrum_points }} pts</span>
                    </div>
                  </div>
                  <div>
                    <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-wide text-slate-500 mb-1.5">
                      <span>Progress</span>
                      <span>{{ task.progress }}%</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                      <div class="h-full rounded-full" :class="task.is_closed ? 'bg-emerald-500' : 'bg-indigo-500'" :style="{ width: `${task.progress}%` }"></div>
                    </div>
                  </div>
                  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 text-xs font-semibold text-slate-600">
                    <div class="rounded-xl bg-slate-50 p-3">
                      <p class="text-[10px] font-black uppercase text-slate-400">Est. Hours</p>
                      <p class="mt-1 text-slate-900">{{ task.estimated_hours || 0 }}h</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                      <p class="text-[10px] font-black uppercase text-slate-400">Allocated</p>
                      <p class="mt-1 text-slate-900">{{ task.allocated_hours || 0 }}h</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                      <p class="text-[10px] font-black uppercase text-slate-400">Actual</p>
                      <p class="mt-1 text-slate-900">{{ task.actual_hours || 0 }}h</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                      <p class="text-[10px] font-black uppercase text-slate-400">Due</p>
                      <p class="mt-1 text-slate-900">{{ task.due_date || 'No due date' }}</p>
                    </div>
                  </div>
                  <div class="rounded-xl border border-slate-200 bg-white p-3">
                    <p class="text-[10px] font-black uppercase tracking-wide text-slate-400">Latest Task Activity</p>
                    <p class="text-xs font-semibold text-slate-600 mt-1">{{ task.latest_activity }}</p>
                  </div>
                </div>
                <div v-if="!taskDetails.length" class="px-5 py-8 text-center text-slate-400 font-medium italic">No assigned tasks found for this filter and date range.</div>
              </div>
              <div v-if="taskDetails.length" class="px-5 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-between gap-3">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Page {{ taskCurrentPage }} of {{ taskTotalPages }} · Total {{ taskBoard.pagination?.total || 0 }}</p>
                <div class="flex items-center gap-2">
                  <button type="button" @click="goToTaskPage(taskCurrentPage - 1)" :disabled="taskCurrentPage <= 1" class="h-8 px-3 rounded-lg border border-slate-200 bg-white text-[10px] font-black uppercase tracking-wide text-slate-600 disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                  <button type="button" @click="goToTaskPage(taskCurrentPage + 1)" :disabled="taskCurrentPage >= taskTotalPages" class="h-8 px-3 rounded-lg border border-slate-200 bg-white text-[10px] font-black uppercase tracking-wide text-slate-600 disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                </div>
              </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
              <div class="px-5 py-4 border-b border-slate-50 flex items-center justify-between gap-3">
                <div>
                  <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Bug Resolution Board</p>
                  <p class="text-xs text-slate-500 mt-1">Assigned bugs with severity, priority, linked task, and resolution state.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-amber-50 border border-amber-100 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-amber-700">{{ bugBoard.pagination?.total || 0 }} bugs</span>
              </div>
              <div class="px-5 py-4 border-b border-slate-50 bg-white flex flex-col lg:flex-row lg:items-end gap-3">
                <div>
                  <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 block mb-1.5">Bug Filter</label>
                  <select v-model="bugStatusFilter" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="all">All</option>
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
                  </select>
                </div>
                <div>
                  <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 block mb-1.5">Priority</label>
                  <select v-model="bugPriorityFilter" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">All</option>
                    <option value="critical">Critical</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                  </select>
                </div>
                <button type="button" @click="applyBugFilters" class="h-10 px-4 rounded-xl bg-slate-900 text-white text-xs font-black uppercase tracking-wide">Apply Bug Filters</button>
              </div>
              <div class="grid grid-cols-3 gap-3 p-5 border-b border-slate-50 bg-slate-50/70">
                <div class="rounded-xl bg-white border border-slate-200 p-3 text-center">
                  <p class="text-[10px] font-bold uppercase text-slate-500">Open</p>
                  <p class="text-2xl font-black text-rose-700 mt-1">{{ profile360Data.metrics.quality?.open || 0 }}</p>
                </div>
                <div class="rounded-xl bg-white border border-slate-200 p-3 text-center">
                  <p class="text-[10px] font-bold uppercase text-slate-500">Closed</p>
                  <p class="text-2xl font-black text-emerald-700 mt-1">{{ profile360Data.metrics.quality?.resolved || 0 }}</p>
                </div>
                <div class="rounded-xl bg-white border border-slate-200 p-3 text-center">
                  <p class="text-[10px] font-bold uppercase text-slate-500">Critical</p>
                  <p class="text-2xl font-black text-amber-700 mt-1">{{ profile360Data.metrics.quality?.critical_open || 0 }}</p>
                </div>
              </div>
              <div class="divide-y divide-slate-100">
                <div v-for="bug in paginatedBugDetails" :key="`bug-${bug.id}`" class="px-5 py-4 space-y-3 hover:bg-slate-50 transition-colors">
                  <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                      <a v-if="bug.bug_url" :href="bug.bug_url" class="text-sm font-black text-slate-900 hover:text-amber-700 hover:underline">{{ bug.subject }}</a>
                      <p v-else class="text-sm font-black text-slate-900">{{ bug.subject }}</p>
                      <p class="text-xs text-slate-500 mt-1">{{ bug.project }} · {{ bug.module }}</p>
                    </div>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-black uppercase tracking-wide" :class="bug.is_closed ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-rose-100 text-rose-700 border border-rose-200'">{{ bug.status }}</span>
                  </div>
                  <div class="flex flex-wrap gap-2 text-[10px] font-black uppercase tracking-wide">
                    <span class="rounded-full bg-amber-100 border border-amber-200 px-2 py-1 text-amber-700">{{ bug.severity }}</span>
                    <span class="rounded-full bg-indigo-100 border border-indigo-200 px-2 py-1 text-indigo-700">{{ bug.priority }}</span>
                    <span v-if="bug.is_sla_breached" class="rounded-full bg-rose-100 border border-rose-200 px-2 py-1 text-rose-700">SLA Breached</span>
                  </div>
                  <div class="grid grid-cols-2 gap-3 text-xs font-semibold text-slate-600">
                    <div class="rounded-xl bg-slate-50 p-3">
                      <p class="text-[10px] font-black uppercase text-slate-400">Linked Task</p>
                      <p class="mt-1 text-slate-900">{{ bug.linked_task || 'Not linked' }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                      <p class="text-[10px] font-black uppercase text-slate-400">Effort / Closed At</p>
                      <p class="mt-1 text-slate-900">{{ bug.hours_spent || 0 }}h{{ bug.resolved_at ? ` · ${bug.resolved_at}` : '' }}</p>
                    </div>
                  </div>
                </div>
                <div v-if="!bugDetails.length" class="px-5 py-8 text-center text-slate-400 font-medium italic">No assigned bugs found for this filter and date range.</div>
              </div>
              <div v-if="bugDetails.length" class="px-5 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-between gap-3">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Page {{ bugCurrentPage }} of {{ bugTotalPages }} · Total {{ bugBoard.pagination?.total || 0 }}</p>
                <div class="flex items-center gap-2">
                  <button type="button" @click="goToBugPage(bugCurrentPage - 1)" :disabled="bugCurrentPage <= 1" class="h-8 px-3 rounded-lg border border-slate-200 bg-white text-[10px] font-black uppercase tracking-wide text-slate-600 disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                  <button type="button" @click="goToBugPage(bugCurrentPage + 1)" :disabled="bugCurrentPage >= bugTotalPages" class="h-8 px-3 rounded-lg border border-slate-200 bg-white text-[10px] font-black uppercase tracking-wide text-slate-600 disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Efficiency + Requests Row -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
              <div class="flex items-center justify-between mb-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Efficiency Matrix</p>
                <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-black rounded-full">{{ profile360Data.metrics.deviation.efficiency }}% Eff</span>
              </div>
              <div class="space-y-4">
                <div>
                  <div class="flex justify-between text-xs font-bold mb-1.5">
                    <span class="text-slate-500">Actual Hours</span>
                    <span class="text-indigo-600">{{ profile360Data.metrics.deviation.actual }}h</span>
                  </div>
                  <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-indigo-500 h-full rounded-full transition-all" :style="{ width: Math.min(100, (profile360Data.metrics.deviation.actual / Math.max(1, profile360Data.metrics.deviation.estimated)) * 100) + '%' }"></div>
                  </div>
                  <p class="text-[10px] text-slate-400 mt-1">Allocated Goal: {{ profile360Data.metrics.deviation.estimated }}h</p>
                </div>
                <div class="pt-3 border-t border-slate-50">
                  <span class="text-[10px] font-bold text-rose-500 uppercase">Unassigned: {{ profile360Data.metrics.deviation.unassigned }}h</span>
                  <p class="text-[10px] text-slate-400 italic mt-0.5">Shadow effort without task linkage</p>
                </div>
              </div>
            </div>

            <div class="lg:col-span-2 rounded-2xl bg-slate-900 p-5 text-white shadow-xl relative overflow-hidden">
              <div class="flex items-center justify-between mb-5">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-indigo-400">Request & History Hub</p>
                <span class="text-[10px] font-bold text-indigo-300">{{ profile360Data.metrics.requests.pending_approvals }} Pending Approvals</span>
              </div>
              <div class="grid grid-cols-3 gap-4 mb-5 text-center">
                <div>
                  <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Leaves</p>
                  <p class="text-2xl font-black">{{ profile360Data.metrics.requests.leaves }}</p>
                  <p class="text-[10px] text-slate-400 mt-1">Taken {{ profile360Data.metrics.requests.leave_days_taken || 0 }} days</p>
                </div>
                <div class="border-x border-slate-800">
                  <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">WFH</p>
                  <p class="text-2xl font-black">{{ profile360Data.metrics.requests.wfh }}</p>
                  <p class="text-[10px] text-slate-400 mt-1">Approved {{ profile360Data.metrics.requests.wfh_summary?.approved || 0 }}</p>
                </div>
                <div>
                  <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Swaps</p>
                  <p class="text-2xl font-black">{{ profile360Data.metrics.requests.swaps }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-4">
                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                  <p class="text-[10px] font-black uppercase tracking-widest text-indigo-300">Leave Balance</p>
                  <div class="grid grid-cols-3 gap-2 mt-2 text-center">
                    <div>
                      <p class="text-[10px] text-slate-400">Allowed</p>
                      <p class="text-sm font-black">{{ profile360Data.metrics.requests.leave_balance?.allowed || 0 }}</p>
                    </div>
                    <div>
                      <p class="text-[10px] text-slate-400">Used</p>
                      <p class="text-sm font-black">{{ profile360Data.metrics.requests.leave_balance?.used || 0 }}</p>
                    </div>
                    <div>
                      <p class="text-[10px] text-slate-400">Remaining</p>
                      <p class="text-sm font-black text-emerald-300">{{ profile360Data.metrics.requests.leave_balance?.remaining || 0 }}</p>
                    </div>
                  </div>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                  <p class="text-[10px] font-black uppercase tracking-widest text-indigo-300">WFH Balance</p>
                  <div class="grid grid-cols-3 gap-2 mt-2 text-center">
                    <div>
                      <p class="text-[10px] text-slate-400">Allowed</p>
                      <p class="text-sm font-black">{{ profile360Data.metrics.requests.wfh_summary?.allowed ?? 'N/A' }}</p>
                    </div>
                    <div>
                      <p class="text-[10px] text-slate-400">Used</p>
                      <p class="text-sm font-black">{{ profile360Data.metrics.requests.wfh_summary?.used || 0 }}</p>
                    </div>
                    <div>
                      <p class="text-[10px] text-slate-400">Remaining</p>
                      <p class="text-sm font-black text-emerald-300">{{ profile360Data.metrics.requests.wfh_summary?.remaining ?? 'N/A' }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white/5 rounded-xl p-3 border border-white/10 space-y-2">
                <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Recent Leaves</p>
                <p v-if="!profile360Data.metrics.requests.recent_requests.length" class="text-xs text-slate-500">No recent results.</p>
                <div v-for="req in profile360Data.metrics.requests.recent_requests" :key="req.id" class="flex justify-between items-center text-xs">
                  <span class="font-medium">{{ req.start_date }} → {{ req.end_date }}</span>
                  <span class="px-2 py-0.5 rounded text-[9px] font-black" :class="req.status === 'approved' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400'">{{ req.status.toUpperCase() }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Project Allocation Table -->
          <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-50 flex items-center justify-between">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Project Allocation & Progress</p>
              <span class="text-[10px] font-bold text-indigo-400 bg-indigo-50 px-2 py-1 rounded-md">Estimated vs Actual</span>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left text-sm">
                <thead><tr class="bg-slate-50 text-[10px] font-black uppercase text-slate-400 tracking-wider">
                  <th class="px-5 py-3">Project</th><th class="px-5 py-3 text-center">Est. Days</th>
                  <th class="px-5 py-3 text-center">Planned Hrs</th><th class="px-5 py-3 text-center">Actual Hrs</th>
                  <th class="px-5 py-3">Progress</th><th class="px-5 py-3 text-right">Status</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                  <tr v-for="proj in profile360Data.metrics.project_details" :key="proj.id" class="hover:bg-slate-50 transition-colors">
                    <td class="px-5 py-3"><p class="font-bold text-slate-700">{{ proj.name }}</p><p class="text-[10px] text-slate-400 uppercase">#{{ proj.id }}</p></td>
                    <td class="px-5 py-3 text-center text-slate-500 font-medium">{{ proj.estimated_days }}d</td>
                    <td class="px-5 py-3 text-center font-bold text-indigo-600">{{ proj.estimated_hours }}h</td>
                    <td class="px-5 py-3 text-center font-bold" :class="proj.actual_hours > proj.estimated_hours ? 'text-rose-600' : 'text-emerald-600'">{{ proj.actual_hours }}h</td>
                    <td class="px-5 py-3"><div class="flex items-center gap-2"><div class="w-20 h-1.5 bg-slate-100 rounded-full overflow-hidden"><div class="bg-indigo-500 h-full rounded-full" :style="{ width: proj.progress + '%' }"></div></div><span class="text-[10px] font-bold text-slate-400">{{ proj.progress }}%</span></div></td>
                    <td class="px-5 py-3 text-right"><span class="px-2 py-1 rounded text-[9px] font-black bg-slate-100 text-slate-600 uppercase border border-slate-200">{{ proj.status }}</span></td>
                  </tr>
                  <tr v-if="!profile360Data.metrics.project_details.length"><td colspan="6" class="px-5 py-8 text-center text-slate-400 font-medium italic">No project assignments found for this period.</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Attendance Grid Table -->
          <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-50">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Operational Precision (Attendance Log)</p>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left text-sm">
                <thead><tr class="bg-slate-50 text-[10px] font-black uppercase text-slate-400 tracking-wider">
                  <th class="px-5 py-3">Date</th><th class="px-5 py-3">Status</th>
                  <th class="px-5 py-3">Check In</th><th class="px-5 py-3">Check Out</th>
                  <th class="px-5 py-3">Duration</th><th class="px-5 py-3 text-right">Note</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                  <tr v-for="log in profile360Data.metrics.attendance_grid" :key="log.date" class="hover:bg-slate-50 transition-colors">
                    <td class="px-5 py-3 font-bold text-slate-600">{{ log.date }}</td>
                    <td class="px-5 py-3"><span class="px-2 py-1 rounded-lg text-[10px] font-bold border" :class="log.status === 'present' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'">{{ log.status?.toUpperCase() }}</span></td>
                    <td class="px-5 py-3 text-slate-500 font-medium">{{ log.check_in || '-' }}</td>
                    <td class="px-5 py-3 text-slate-500 font-medium">{{ log.check_out || '-' }}</td>
                    <td class="px-5 py-3"><div class="flex items-center gap-2"><div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden"><div class="bg-indigo-400 h-full rounded-full" :style="{ width: Math.min(100, (log.duration / 8) * 100) + '%' }"></div></div><span class="text-[10px] font-bold text-slate-400">{{ log.duration }}h</span></div></td>
                    <td class="px-5 py-3 text-right font-black text-[10px] text-slate-400">{{ log.note || '-' }}</td>
                  </tr>
                  <tr v-if="!profile360Data.metrics.attendance_grid.length"><td colspan="6" class="px-5 py-8 text-center text-slate-400 font-medium italic">No attendance records found for this period.</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-50">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">History & Activity Logs</p>
            </div>
            <div class="divide-y divide-slate-100">
              <div v-for="(event, idx) in paginatedActivityLogs" :key="`activity-${idx}-${event.occurred_at}`" class="px-5 py-3 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-2">
                <div>
                  <p class="text-sm font-bold text-slate-800">{{ event.title }}</p>
                  <p class="text-xs text-slate-500 mt-0.5">{{ event.meta }}</p>
                </div>
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center rounded-full bg-slate-100 border border-slate-200 px-2 py-0.5 text-[10px] font-black uppercase tracking-wide text-slate-600">{{ event.event_type }}</span>
                  <span class="text-[10px] font-bold text-slate-500">{{ event.occurred_at }}</span>
                </div>
              </div>
              <div v-if="!activityLogs.length" class="px-5 py-8 text-center text-slate-400 font-medium italic">No activity found for this date range.</div>
            </div>
            <div v-if="activityLogs.length" class="px-5 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-between gap-3">
              <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Page {{ activityCurrentPage }} of {{ activityTotalPages }}</p>
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  @click="goToActivityPage(activityCurrentPage - 1)"
                  :disabled="activityCurrentPage <= 1"
                  class="h-8 px-3 rounded-lg border border-slate-200 bg-white text-[10px] font-black uppercase tracking-wide text-slate-600 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Previous
                </button>
                <button
                  type="button"
                  @click="goToActivityPage(activityCurrentPage + 1)"
                  :disabled="activityCurrentPage >= activityTotalPages"
                  class="h-8 px-3 rounded-lg border border-slate-200 bg-white text-[10px] font-black uppercase tracking-wide text-slate-600 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Next
                </button>
              </div>
            </div>
          </div>

        </div>
      </section>

      <section v-else class="grid grid-cols-1 xl:grid-cols-[1fr_1fr] gap-4">
        <article class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm space-y-3">
          <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-700">People + Delivery Health</p>
          <h2 class="text-xl font-black text-slate-900">Squad_Ops Status</h2>
          <p class="text-sm font-bold text-slate-700">PR Throughput {{ opsCards.squad_ops?.pr_throughput || 0 }}/week</p>
          <p class="text-sm font-bold text-slate-700">Review Lag {{ opsCards.squad_ops?.review_lag_hours || 0 }}h</p>
          <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-cyan-100 text-cyan-700 border border-cyan-200">Repos {{ opsCards.squad_ops?.repos_linked || 0 }}</span>
            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-slate-100 text-slate-700 border border-slate-200">Open PRs {{ opsCards.squad_ops?.open_prs || 0 }}</span>
            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide" :class="opsRiskBadgeClass(opsCards.squad_ops?.deployment_risk)">{{ opsCards.squad_ops?.deployment_risk || 'Low' }} Risk</span>
          </div>
          <div class="pt-2 space-y-1 max-h-24 overflow-auto pr-1">
            <a v-for="project in opsLaunchers.project_devops || []" :key="`devops-project-${project.id}`" :href="project.url" class="block text-xs font-bold text-emerald-700 hover:text-emerald-800 truncate">{{ project.name }} DevOps</a>
            <p v-if="!(opsLaunchers.project_devops || []).length" class="text-xs font-semibold text-slate-400">No project DevOps links available.</p>
          </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-3">Squad_Ops Weekly Pulse</p>
          <div class="grid grid-cols-8 gap-2 items-end h-36">
            <div v-for="item in devopsTrendSeries" :key="`devops-week-${item.label}`" class="flex flex-col items-center justify-end gap-2">
              <p class="text-[10px] font-black text-slate-600">{{ item.value }}</p>
              <div class="w-full rounded-t-lg bg-emerald-500" :style="{ height: `${devopsTrendBarHeight(item.value)}px`, minHeight: '8px' }"></div>
              <p class="text-[10px] font-black text-slate-500">{{ item.label }}</p>
            </div>
          </div>
        </article>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
  activeTab:    { type: String, default: 'employee360' },
  ops360:       { type: Object, default: () => ({}) },
  employees:    { type: Array,  default: () => [] },
  can_view_360: { type: Boolean, default: false },
  date_start:   { type: String, default: '' },
  date_end:     { type: String, default: '' },
});

const activeTabValue = computed(() => (props.activeTab === 'squad_ops' ? 'squad_ops' : 'employee360'));
const opsLaunchers = computed(() => props.ops360?.launchers || {});
const opsCards = computed(() => props.ops360?.cards || {});
const devopsTrendSeries = computed(() => props.ops360?.insights?.devops_pulse_trend || []);
const overview = computed(() => props.ops360?.overview || {});
const dailyOpsSeries = computed(() => props.ops360?.graphs?.daily_ops || []);

// ── Employee 360 Search ──────────────────────────────────────────────────────
const empSearch = ref('');
const showDropdown = ref(false);
const selectedEmployee = ref(null);
const dateStart = ref(props.date_start || new Date(Date.now() - 30 * 86400000).toISOString().slice(0, 10));
const dateEnd   = ref(props.date_end   || new Date().toISOString().slice(0, 10));
const profile360Data    = ref(null);
const profile360Loading = ref(false);
const profile360Error   = ref('');
const taskCurrentPage = ref(1);
const bugCurrentPage = ref(1);
const taskStatusFilter = ref('all');
const taskPriorityFilter = ref('');
const bugStatusFilter = ref('all');
const bugPriorityFilter = ref('');
const activityPageSize = 10;
const activityCurrentPage = ref(1);

const filteredEmployees = computed(() => {
  const q = empSearch.value.toLowerCase().trim();
  if (!q) return props.employees.slice(0, 20);
  return props.employees.filter(
    (e) => e.name.toLowerCase().includes(q) || (e.designation || '').toLowerCase().includes(q)
  ).slice(0, 20);
});

const selectEmployee = (emp) => {
  selectedEmployee.value = emp;
  empSearch.value = emp.name;
  showDropdown.value = false;
  taskCurrentPage.value = 1;
  bugCurrentPage.value = 1;
  activityCurrentPage.value = 1;
};

const delayHideDropdown = () => setTimeout(() => { showDropdown.value = false; }, 150);

const taskBoard = computed(() => profile360Data.value?.metrics?.task_details || { items: [], pagination: { current_page: 1, last_page: 1, total: 0, per_page: 4 } });
const taskDetails = computed(() => taskBoard.value.items || []);

const taskTotalPages = computed(() => {
  return Math.max(1, Number(taskBoard.value.pagination?.last_page || 1));
});

const paginatedTaskDetails = computed(() => taskDetails.value);

const goToTaskPage = (page) => {
  taskCurrentPage.value = Math.min(taskTotalPages.value, Math.max(1, page));
  load360Profile();
};

const bugBoard = computed(() => profile360Data.value?.metrics?.bug_details || { items: [], pagination: { current_page: 1, last_page: 1, total: 0, per_page: 4 } });
const bugDetails = computed(() => bugBoard.value.items || []);

const bugTotalPages = computed(() => {
  return Math.max(1, Number(bugBoard.value.pagination?.last_page || 1));
});

const paginatedBugDetails = computed(() => bugDetails.value);

const goToBugPage = (page) => {
  bugCurrentPage.value = Math.min(bugTotalPages.value, Math.max(1, page));
  load360Profile();
};

const activityLogs = computed(() => profile360Data.value?.metrics?.activity_logs || []);

const activityTotalPages = computed(() => {
  return Math.max(1, Math.ceil(activityLogs.value.length / activityPageSize));
});

const paginatedActivityLogs = computed(() => {
  const start = (activityCurrentPage.value - 1) * activityPageSize;
  return activityLogs.value.slice(start, start + activityPageSize);
});

const goToActivityPage = (page) => {
  activityCurrentPage.value = Math.min(activityTotalPages.value, Math.max(1, page));
};

const load360Profile = async () => {
  if (!selectedEmployee.value) return;
  profile360Loading.value = true;
  profile360Error.value = '';
  profile360Data.value = null;
  activityCurrentPage.value = 1;
  try {
    const res = await axios.get(
      route('hr.employee-360.metrics', selectedEmployee.value.id),
      {
        params: {
          start_date: dateStart.value,
          end_date: dateEnd.value,
          task_page: taskCurrentPage.value,
          task_per_page: 4,
          task_status: taskStatusFilter.value,
          task_priority: taskPriorityFilter.value,
          bug_page: bugCurrentPage.value,
          bug_per_page: 4,
          bug_status: bugStatusFilter.value,
          bug_priority: bugPriorityFilter.value,
        },
      }
    );
    profile360Data.value = res.data;
    taskCurrentPage.value = Number(res.data?.metrics?.task_details?.pagination?.current_page || 1);
    bugCurrentPage.value = Number(res.data?.metrics?.bug_details?.pagination?.current_page || 1);
  } catch (e) {
    profile360Error.value = e?.response?.data?.message || 'Failed to load employee profile. Check your access permissions.';
  } finally {
    profile360Loading.value = false;
  }
};

const applyTaskFilters = () => {
  taskCurrentPage.value = 1;
  load360Profile();
};

const applyBugFilters = () => {
  bugCurrentPage.value = 1;
  load360Profile();
};

const export360 = () => {
  if (!selectedEmployee.value) return;
  const params = new URLSearchParams({ start_date: dateStart.value, end_date: dateEnd.value }).toString();
  window.location.href = route('hr.employee-360.export', selectedEmployee.value.id) + '?' + params;
};
// ────────────────────────────────────────────────────────────────────────────

const maxDailyOpsValue = computed(() => {
  const values = dailyOpsSeries.value.flatMap((day) => [
    Number(day.present || 0),
    Number(day.timesheet_filled || 0),
    Number(day.tasks_completed || 0),
    Number(day.approvals_raised || 0),
  ]);
  return Math.max(1, ...values);
});

const maxDevOpsTrendValues = computed(() => {
  const values = devopsTrendSeries.value.map((item) => Number(item.value || 0));
  return Math.max(1, ...values);
});

const devopsTrendBarHeight = (value) => {
  const current = Number(value || 0);
  return Math.round((current / maxDevOpsTrendValues.value) * 110);
};

const dailyOpsBarHeight = (value) => {
  const current = Number(value || 0);
  return Math.round((current / maxDailyOpsValue.value) * 120);
};

const opsRiskBadgeClass = (risk) => {
  if (risk === 'High') return 'bg-rose-100 text-rose-700 border border-rose-200';
  if (risk === 'Medium') return 'bg-amber-100 text-amber-700 border border-amber-200';
  return 'bg-emerald-100 text-emerald-700 border border-emerald-200';
};

const trackStatusClass = (status) => {
  if (status === 'Off Track') return 'bg-rose-100 text-rose-700 border border-rose-200';
  if (status === 'At Risk') return 'bg-amber-100 text-amber-700 border border-amber-200';
  return 'bg-emerald-100 text-emerald-700 border border-emerald-200';
};

const setTab = (tab) => {
  router.get('/employee/work/ops360', { tab }, { preserveState: true, preserveScroll: true });
};
</script>
