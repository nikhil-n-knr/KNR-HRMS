<template>
  <Head title="My Work" />

  <div class="min-h-screen bg-slate-50 py-8 pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
      <section class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-teal-600">Execution Desk</p>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mt-1">My Work</h1>
            <p class="text-sm text-slate-500 mt-2">Assigned tasks and bugs with focused employee visibility.</p>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div class="rounded-2xl bg-teal-50 border border-teal-100 px-4 py-3">
              <p class="text-[10px] font-black uppercase tracking-[0.2em] text-teal-600">Tasks</p>
              <p class="text-2xl font-black text-teal-700">{{ summary.tasks_count }}</p>
            </div>
            <div class="rounded-2xl bg-rose-50 border border-rose-100 px-4 py-3">
              <p class="text-[10px] font-black uppercase tracking-[0.2em] text-rose-600">Bugs</p>
              <p class="text-2xl font-black text-rose-700">{{ summary.bugs_count }}</p>
            </div>
          </div>
        </div>
      </section>

      <section class="rounded-3xl border border-indigo-200 bg-gradient-to-br from-indigo-50 via-blue-50 to-white p-5 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
          <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-700">Ops + 360 Launcher</p>
            <h2 class="text-xl font-black text-slate-900 mt-1">Employee 360, Squad_Ops, Project DevOps</h2>
            <p class="text-xs font-semibold text-slate-500 mt-1">Quick links + role-aware visibility + deep-link to combined Ops360 view.</p>
          </div>
          <a :href="opsLaunchers.ops360_url || '/employee/work/ops360'" class="inline-flex items-center justify-center h-10 px-4 rounded-xl bg-slate-900 text-white text-xs font-black uppercase tracking-wider hover:bg-slate-800">
            Open Ops360
          </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <a v-if="opsLaunchers.can_view_employee360 && opsLaunchers.employee360_url" :href="opsLaunchers.employee360_url" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 hover:border-indigo-300 transition-colors">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Employee 360</p>
            <p class="text-lg font-black text-indigo-700 mt-1">Open Module</p>
            <p class="text-xs font-semibold text-slate-500 mt-1">Workforce health, delivery quality, and request audit.</p>
          </a>
          <div v-else class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Employee 360</p>
            <p class="text-lg font-black text-slate-400 mt-1">No Access</p>
            <p class="text-xs font-semibold text-slate-400 mt-1">Role permission required.</p>
          </div>

          <a v-if="false" :href="opsLaunchers.devops_global_url" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 hover:border-emerald-300 transition-colors">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Squad_Ops Global</p>
            <p class="text-lg font-black text-emerald-700 mt-1">Open Dashboard</p>
            <p class="text-xs font-semibold text-slate-500 mt-1">Throughput, review intelligence, and release risk.</p>
          </a>
          <div v-else class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Squad_Ops Global</p>
            <p class="text-lg font-black text-slate-400 mt-1">No Access</p>
            <p class="text-xs font-semibold text-slate-400 mt-1">Admin-level visibility required.</p>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Project DevOps</p>
            <p class="text-lg font-black text-cyan-700 mt-1">Assigned Projects</p>
            <div class="mt-2 space-y-1 max-h-20 overflow-auto pr-1">
              <a v-if="false" v-for="project in opsLaunchers.project_devops || []" :key="`devops-link-${project.id}`" :href="project.url" class="block text-xs font-bold text-cyan-700 hover:text-cyan-800 truncate">
                {{ project.name }}
              </a>
              <p v-if="false" class="text-xs font-semibold text-slate-400">No linked project dashboards.</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">People Health (Employee 360)</p>
            <p class="text-sm font-bold text-slate-700 mt-2">Attendance {{ opsCards.employee360?.attendance_score || 0 }}% | Productivity {{ opsCards.employee360?.productivity_score || 0 }}%</p>
            <div class="mt-2 flex flex-wrap gap-1">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-indigo-100 text-indigo-700 border border-indigo-200">30d filled {{ opsCards.employee360?.filled_days_30 || 0 }}</span>
              <span v-for="flag in (opsCards.employee360?.compliance_flags || []).slice(0, 2)" :key="`people-flag-${flag}`" class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-amber-100 text-amber-700 border border-amber-200">{{ flag }}</span>
            </div>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Delivery Health (Squad_Ops)</p>
            <p class="text-sm font-bold text-slate-700 mt-2">PR Throughput {{ opsCards.squad_ops?.pr_throughput || 0 }}/wk | Review Lag {{ opsCards.squad_ops?.review_lag_hours || 0 }}h</p>
            <div class="mt-2 flex flex-wrap gap-1">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-cyan-100 text-cyan-700 border border-cyan-200">Repos {{ opsCards.squad_ops?.repos_linked || 0 }}</span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-slate-100 text-slate-700 border border-slate-200">Open PRs {{ opsCards.squad_ops?.open_prs || 0 }}</span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide" :class="opsRiskBadgeClass(opsCards.squad_ops?.deployment_risk)">{{ opsCards.squad_ops?.deployment_risk || 'Low' }} Risk</span>
            </div>
          </div>
        </div>
      </section>

      <section class="grid grid-cols-1 xl:grid-cols-[1.4fr_1fr] gap-4">
        <article class="rounded-3xl border border-sky-200 bg-gradient-to-br from-sky-50 via-cyan-50 to-white p-5 shadow-sm">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
              <p class="text-[10px] font-black uppercase tracking-[0.2em] text-sky-700">Today Command Center</p>
              <h2 class="text-2xl font-black text-slate-900 mt-1">{{ todayCommand.date || '-' }}</h2>
              <p class="text-xs font-semibold text-slate-500 mt-1">Range context: {{ rangeSummary.label || 'All Dates' }}</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-center">
              <div class="rounded-xl border border-sky-200 bg-white px-3 py-2">
                <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Due Today</p>
                <p class="text-xl font-black text-slate-900">{{ todayCommand.tasks_due_today || 0 }}</p>
              </div>
              <div class="rounded-xl border border-cyan-200 bg-white px-3 py-2">
                <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Touched</p>
                <p class="text-xl font-black text-cyan-700">{{ todayCommand.tasks_touched_today || 0 }}</p>
              </div>
              <div class="rounded-xl border border-emerald-200 bg-white px-3 py-2">
                <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Done Today</p>
                <p class="text-xl font-black text-emerald-700">{{ todayCommand.tasks_done_today || 0 }}</p>
              </div>
              <div class="rounded-xl border border-rose-200 bg-white px-3 py-2">
                <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Attention</p>
                <p class="text-xl font-black text-rose-700">{{ (todayCommand.overdue_tasks || 0) + (todayCommand.blocked_tasks || 0) }}</p>
              </div>
            </div>
          </div>

          <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
              <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Today Pace</p>
              <p class="text-sm font-bold text-slate-700 mt-2">Logged today {{ todayCommand.hours_logged_today || 0 }}h / Planned today {{ todayCommand.hours_planned_today || 0 }}h</p>
              <p class="text-xs font-semibold mt-1" :class="todayPaceClass">{{ todayPaceText }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
              <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Risk Snapshot</p>
              <p class="text-sm font-bold text-slate-700 mt-2">Overdue {{ todayCommand.overdue_tasks || 0 }} | Blocked {{ todayCommand.blocked_tasks || 0 }}</p>
              <p class="text-xs font-semibold text-slate-500 mt-1">Keep this at 0 by resolving blockers first.</p>
            </div>
          </div>

          <div class="mt-4 rounded-2xl border border-slate-200 bg-white overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
              <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Top Attention Tasks</p>
              <p class="text-xs font-semibold text-slate-500">Today + overdue + blocked</p>
            </div>
            <div class="divide-y divide-slate-100">
              <div v-for="item in todayAttentionTasks" :key="`today-task-${item.id}`" class="px-4 py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div>
                  <p class="text-sm font-bold text-slate-800">{{ item.title }}</p>
                  <p class="text-xs font-semibold text-slate-500">{{ item.project_name || 'No project' }} | Due {{ formatDate(item.due_date) }} | {{ item.priority || '-' }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-1">
                  <span v-if="item.is_due_today" class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-sky-100 text-sky-700 border border-sky-200">Due Today</span>
                  <span v-if="item.worked_today" class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-cyan-100 text-cyan-700 border border-cyan-200">Worked</span>
                  <span v-if="item.is_overdue" class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-rose-100 text-rose-700 border border-rose-200">Overdue</span>
                  <span v-if="item.is_blocked" class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-amber-100 text-amber-700 border border-amber-200">Blocked</span>
                </div>
              </div>
              <div v-if="!todayAttentionTasks.length" class="px-4 py-7 text-sm font-semibold text-slate-400 text-center">No tasks in attention queue for today.</div>
            </div>
          </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm space-y-3">
          <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Date Range Snapshot</p>
            <h2 class="text-lg font-black text-slate-900 mt-1">{{ rangeSummary.label || 'All Dates' }}</h2>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
              <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Tasks</p>
              <p class="text-xl font-black text-slate-900">{{ rangeSummary.task_count || 0 }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
              <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Projects</p>
              <p class="text-xl font-black text-slate-900">{{ rangeSummary.project_count || 0 }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
              <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Planned</p>
              <p class="text-xl font-black text-slate-900">{{ rangeSummary.task_estimated_hours || 0 }}h</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
              <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Timesheet Today</p>
              <p class="text-xl font-black text-slate-900">{{ rangeSummary.timesheet_hours || 0 }}h</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
              <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Sub Plan</p>
              <p class="text-xl font-black text-slate-900">{{ rangeSummary.checklist_planned_hours || 0 }}h</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
              <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Sub Actual</p>
              <p class="text-xl font-black" :class="(rangeSummary.checklist_actual_hours || 0) > (rangeSummary.checklist_planned_hours || 0) * 1.2 && (rangeSummary.checklist_planned_hours || 0) > 0 ? 'text-rose-700' : 'text-slate-900'">{{ rangeSummary.checklist_actual_hours || 0 }}h</p>
            </div>
          </div>
          <div class="rounded-xl border px-3 py-2" :class="(rangeSummary.variance_hours || 0) > 0 ? 'border-rose-200 bg-rose-50' : 'border-emerald-200 bg-emerald-50'">
            <p class="text-[10px] font-black uppercase tracking-wider" :class="(rangeSummary.variance_hours || 0) > 0 ? 'text-rose-600' : 'text-emerald-600'">Variance Today</p>
            <p class="text-2xl font-black" :class="(rangeSummary.variance_hours || 0) > 0 ? 'text-rose-700' : 'text-emerald-700'">{{ rangeSummary.variance_hours || 0 }}h</p>
            <p class="text-[11px] font-semibold text-slate-500 mt-1">All-date variance {{ rangeSummary.variance_hours_total || 0 }}h</p>
          </div>
        </article>
      </section>

      <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
        <article v-for="card in planCards" :key="card.key" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{{ card.label }}</p>
          <div class="mt-3 flex items-end justify-between gap-3">
            <p class="text-3xl font-black text-slate-900">{{ card.value }}</p>
            <p class="text-[11px] font-semibold text-slate-500 text-right">{{ card.caption }}</p>
          </div>
          <div class="mt-3 text-[11px] font-semibold text-slate-500 space-y-1">
            <p v-if="card.meta?.today_estimated_hours !== undefined">Today Est {{ card.meta.today_estimated_hours }}h | Today TS {{ card.meta.today_timesheet_hours }}h</p>
            <p v-if="card.meta?.today_work_count !== undefined">Tasks touched {{ card.meta.today_work_count }} | Planned {{ card.meta.today_planned_hours }}h</p>
            <p v-if="card.meta?.checklist_plan_hours !== undefined">Sub Plan {{ card.meta.checklist_plan_hours }}h | Sub Actual {{ card.meta.checklist_actual_hours }}h</p>
            <p v-if="card.meta?.overdue !== undefined">Overdue {{ card.meta.overdue }} | Blocked {{ card.meta.blocked }}</p>
            <p v-if="card.meta?.names?.length">{{ card.meta.names.join(', ') }}</p>
          </div>
        </article>
      </section>

      <section class="rounded-3xl border border-indigo-200 bg-gradient-to-br from-indigo-50 via-sky-50 to-white p-5 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
          <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-700">Timesheet Pulse</p>
            <h2 class="text-xl font-black text-slate-900 mt-1">Daily Fill Status And Weekly Analytics</h2>
            <p class="text-xs font-semibold text-slate-500 mt-1">Track if today is filled, review last week, and jump to timesheet instantly.</p>
          </div>
          <a :href="timesheetInsights.timesheet_url || '/attendance?tab=timesheets'" class="inline-flex items-center justify-center h-10 px-4 rounded-xl bg-indigo-600 text-white text-xs font-black uppercase tracking-wider hover:bg-indigo-700">
            Fill Timesheet
          </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
          <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Today</p>
            <p class="text-xl font-black mt-1" :class="timesheetInsights.today?.filled ? 'text-emerald-700' : 'text-rose-700'">
              {{ timesheetInsights.today?.filled ? 'Filled' : 'Not Filled' }}
            </p>
            <p class="text-xs font-semibold text-slate-500 mt-1">{{ timesheetInsights.today?.hours || 0 }}h logged on {{ formatDate(timesheetInsights.today?.date) }}</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Last Week</p>
            <p class="text-xl font-black text-slate-900 mt-1">{{ timesheetInsights.last_week?.hours || 0 }}h</p>
            <p class="text-xs font-semibold text-slate-500 mt-1">Filled {{ timesheetInsights.last_week?.filled_days || 0 }}/{{ timesheetInsights.last_week?.target_days || 7 }} days</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">7-Day Avg</p>
            <p class="text-xl font-black text-slate-900 mt-1">{{ timesheetInsights.analytics?.avg_hours_last_7 || 0 }}h</p>
            <p class="text-xs font-semibold text-slate-500 mt-1">Consistency {{ timesheetInsights.analytics?.consistency_pct || 0 }}%</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Best Day</p>
            <p class="text-xl font-black text-slate-900 mt-1">{{ timesheetInsights.analytics?.best_day_hours || 0 }}h</p>
            <p class="text-xs font-semibold text-slate-500 mt-1">{{ timesheetInsights.analytics?.best_day_label || '-' }} in last 7 days</p>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
          <div class="flex items-center justify-between gap-2 mb-3">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Last 7 Days Logged Hours</p>
            <p class="text-xs font-semibold text-slate-500">Missing days: {{ timesheetInsights.analytics?.missing_last_7 || 0 }}</p>
          </div>
          <div class="grid grid-cols-7 gap-2 items-end h-36">
            <div v-for="item in last7TimesheetSeries" :key="`ts-day-${item.date}`" class="flex flex-col items-center justify-end gap-2">
              <p class="text-[10px] font-black" :class="item.filled ? 'text-slate-700' : 'text-slate-300'">{{ item.hours }}h</p>
              <div class="w-full rounded-t-lg" :class="item.filled ? 'bg-indigo-500' : 'bg-slate-200'" :style="{ height: `${barHeight(item.hours)}px`, minHeight: '8px' }"></div>
              <p class="text-[10px] font-black uppercase tracking-wide" :class="item.filled ? 'text-slate-600' : 'text-slate-400'">{{ item.label }}</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
          <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
            <div class="flex items-center justify-between gap-2 mb-3">
              <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Project Contribution (Last 7 Days)</p>
              <p class="text-xs font-semibold text-slate-500">Where the effort went</p>
            </div>
            <div class="space-y-3">
              <div v-for="project in projectBreakdownSeries" :key="`proj-breakdown-${project.project_id || project.project_name}`" class="space-y-1">
                <div class="flex items-center justify-between gap-2">
                  <p class="text-xs font-bold text-slate-700 truncate">{{ project.project_name }}</p>
                  <p class="text-xs font-black text-slate-900">{{ project.hours }}h</p>
                </div>
                <div class="h-2.5 rounded-full bg-slate-100 overflow-hidden">
                  <div class="h-full rounded-full bg-cyan-500" :style="{ width: `${projectShareWidth(project.hours)}%` }"></div>
                </div>
                <div class="grid grid-cols-7 gap-1">
                  <div v-for="(dayHours, idx) in project.daily" :key="`proj-day-${idx}`" class="h-1.5 rounded" :class="dayHours > 0 ? 'bg-indigo-300' : 'bg-slate-100'"></div>
                </div>
              </div>
              <p v-if="!projectBreakdownSeries.length" class="text-xs font-semibold text-slate-400">No project hours found in last 7 days.</p>
            </div>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
            <div class="flex items-center justify-between gap-2 mb-3">
              <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Weekly Overall Trend (8 Weeks)</p>
              <p class="text-xs font-semibold text-slate-500">Avg {{ timesheetInsights.weekly_analytics?.avg_hours_last_8_weeks || 0 }}h/week</p>
            </div>
            <div class="grid grid-cols-8 gap-2 items-end h-36">
              <div v-for="week in weeklyOverallSeries" :key="`weekly-overall-${week.week_start}`" class="flex flex-col items-center justify-end gap-2">
                <p class="text-[10px] font-black" :class="week.hours > 0 ? 'text-slate-700' : 'text-slate-300'">{{ week.hours }}h</p>
                <div class="w-full rounded-t-lg" :class="week.hours > 0 ? 'bg-emerald-500' : 'bg-slate-200'" :style="{ height: `${weeklyBarHeight(week.hours)}px`, minHeight: '8px' }"></div>
                <p class="text-[10px] font-black uppercase tracking-wide" :class="week.hours > 0 ? 'text-slate-600' : 'text-slate-400'">{{ week.label }}</p>
              </div>
            </div>
            <p class="text-xs font-semibold text-slate-500 mt-3">Best week {{ timesheetInsights.weekly_analytics?.best_week_label || '-' }} at {{ timesheetInsights.weekly_analytics?.best_week_hours || 0 }}h</p>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
          <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <div>
              <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Team Insights · Employee 360 Trend</p>
              <div class="grid grid-cols-8 gap-2 items-end h-28">
                <div v-for="item in employeeTrendSeries" :key="`emp-trend-${item.label}`" class="flex flex-col items-center justify-end gap-1">
                  <div class="w-full rounded-t-md bg-indigo-400" :style="{ height: `${employeeTrendBarHeight(item.hours)}px`, minHeight: '6px' }"></div>
                  <p class="text-[10px] font-black text-slate-500">{{ item.label }}</p>
                </div>
              </div>
            </div>
            <div>
              <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Team Insights · Squad_Ops Pulse</p>
              <div class="grid grid-cols-8 gap-2 items-end h-28">
                <div v-for="item in devopsTrendSeries" :key="`devops-trend-${item.label}`" class="flex flex-col items-center justify-end gap-1">
                  <div class="w-full rounded-t-md bg-emerald-400" :style="{ height: `${devopsTrendBarHeight(item.value)}px`, minHeight: '6px' }"></div>
                  <p class="text-[10px] font-black text-slate-500">{{ item.label }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="bg-white rounded-3xl border border-slate-200 p-4 shadow-sm space-y-3">
        <div class="flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between">
          <div class="flex bg-slate-100 rounded-xl p-1 w-full lg:w-auto">
            <button
              type="button"
              @click="setTab('tasks')"
              class="flex-1 lg:flex-none px-4 py-2 rounded-lg text-xs font-black uppercase tracking-widest transition-all"
              :class="activeTabValue === 'tasks' ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
            >
              My Tasks
            </button>
            <button
              type="button"
              @click="setTab('bugs')"
              class="flex-1 lg:flex-none px-4 py-2 rounded-lg text-xs font-black uppercase tracking-widest transition-all"
              :class="activeTabValue === 'bugs' ? 'bg-white text-rose-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
            >
              My Bugs
            </button>
          </div>

          <div class="flex items-center gap-2 flex-wrap justify-end">
            <button type="button" class="h-10 px-3 rounded-xl text-xs font-black uppercase tracking-wider border" :class="localFilters.focus_mode ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200'" @click="toggleFocusMode">
              My Focus Mode {{ localFilters.focus_mode ? 'On' : 'Off' }}
            </button>
            <button type="button" class="h-10 px-3 rounded-xl bg-slate-100 text-slate-700 text-xs font-black uppercase tracking-wider disabled:opacity-50" :disabled="isLoading('defaults-save')" @click="saveDefaultFilters">Save My Default</button>
            <button type="button" class="h-10 px-3 rounded-xl border border-slate-200 text-slate-600 text-xs font-black uppercase tracking-wider disabled:opacity-50" :disabled="isLoading('defaults-clear')" @click="clearDefaultFilters">Clear Default</button>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <button v-for="chip in quickFilterChips" :key="chip.value" type="button" class="h-9 px-3 rounded-full text-xs font-black uppercase tracking-wider border transition-colors" :class="quickFilterChipClass(chip.value)" @click="setQuickFilter(chip.value)">
            {{ chip.label }}
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-3">
          <input
            v-model="localFilters.q"
            @keyup.enter="applyFilters"
            type="text"
            class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-700 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500"
            placeholder="Search title, subject, description"
          >

          <select v-model="localFilters.project_id" class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-700">
            <option value="">All Projects</option>
            <option v-for="project in projectOptions" :key="project.id" :value="String(project.id)">{{ project.name }}</option>
          </select>

          <select v-model="localFilters.priority" class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-700">
            <option value="">All Priorities</option>
            <option v-for="priority in activePriorityOptions" :key="priority" :value="priority">{{ priority }}</option>
          </select>

          <div class="grid grid-cols-2 gap-2">
            <input v-model="localFilters.date_from" type="date" class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-700" title="Date from">
            <input v-model="localFilters.date_to" type="date" class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-700" title="Date to">
          </div>

          <select v-if="activeTabValue === 'tasks'" v-model="localFilters.task_status" class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-700">
            <option value="">All Stages</option>
            <option v-for="stage in flattenedTaskStageOptions" :key="`task-stage-${stage.id}`" :value="String(stage.id)">{{ stage.name }}</option>
          </select>
          <select v-else v-model="localFilters.bug_stage" class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-700">
            <option value="">All Stages</option>
            <option v-for="stage in bugStageOptions" :key="`bug-stage-${stage.id}`" :value="String(stage.id)">{{ stage.name }}</option>
          </select>

          <div class="flex items-center gap-2">
            <button type="button" class="h-10 flex-1 rounded-xl bg-teal-600 text-white text-xs font-black uppercase tracking-wider" @click="applyFilters">Apply</button>
            <button type="button" class="h-10 flex-1 rounded-xl border border-slate-200 text-slate-600 text-xs font-black uppercase tracking-wider" @click="resetFilters">Reset</button>
          </div>
        </div>

        <p class="text-[11px] font-semibold text-slate-500">
          Saved default: project {{ savedDefaults.project_id || 'all' }}, priority {{ savedDefaults.priority || 'all' }}, task stage {{ savedDefaults.task_status || 'all' }}, bug stage {{ savedDefaults.bug_stage || 'all' }}, quick filter {{ savedDefaults.quick_filter || 'none' }}, range {{ savedDefaults.date_from || '-' }} to {{ savedDefaults.date_to || '-' }}, focus {{ savedDefaults.focus_mode ? 'on' : 'off' }}.
        </p>

        <div class="grid grid-cols-1 lg:grid-cols-[220px_1fr_auto_auto] gap-2 pt-1">
          <select v-model="presetState.selectedKey" class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-700">
            <option value="">Select preset</option>
            <option v-for="preset in savedPresets" :key="preset.key" :value="preset.key">{{ preset.name }}</option>
          </select>
          <input v-model="presetState.name" type="text" class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-700" placeholder="New preset name (Testing, Urgent, Project A)">
          <button
            type="button"
            class="h-10 px-3 rounded-xl bg-slate-900 text-white text-xs font-black uppercase tracking-wider disabled:opacity-50"
            :disabled="isLoading('preset-save')"
            @click="saveCurrentAsPreset"
          >
            Save Preset
          </button>
          <div class="flex items-center gap-2">
            <button
              type="button"
              class="h-10 px-3 rounded-xl border border-slate-200 text-slate-600 text-xs font-black uppercase tracking-wider disabled:opacity-50"
              :disabled="!presetState.selectedKey"
              @click="applySelectedPreset"
            >
              Apply
            </button>
            <button
              type="button"
              class="h-10 px-3 rounded-xl border border-rose-200 text-rose-600 text-xs font-black uppercase tracking-wider disabled:opacity-50"
              :disabled="!presetState.selectedKey || isLoading('preset-delete')"
              @click="deleteSelectedPreset"
            >
              Delete
            </button>
          </div>
        </div>
      </section>

      <section class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between gap-3">
          <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Work Plan</p>
            <h2 class="text-lg font-black text-slate-900 mt-1">Projects And Effort View</h2>
          </div>
          <p class="text-xs font-semibold text-slate-500">Estimate vs sub tasks plan vs actual vs timesheet</p>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full min-w-[860px]">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Project</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Tasks</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Today</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Task Est</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Sub Plan</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Sub Actual</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Timesheet</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Variance</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in projectPlanRows" :key="row.project_id || row.project_name" class="border-t border-slate-100 hover:bg-slate-50/70">
                <td class="px-4 py-3 text-sm font-bold text-slate-800">{{ row.project_name }}</td>
                <td class="px-4 py-3 text-sm text-slate-600">{{ row.task_count }}</td>
                <td class="px-4 py-3 text-sm text-slate-600">{{ row.today_due_count }}</td>
                <td class="px-4 py-3 text-sm font-semibold text-slate-700">{{ row.estimated_hours }}h</td>
                <td class="px-4 py-3 text-sm font-semibold text-slate-700">{{ row.checklist_planned_hours }}h</td>
                <td class="px-4 py-3 text-sm font-semibold" :class="row.checklist_actual_hours > row.checklist_planned_hours * 1.2 && row.checklist_planned_hours > 0 ? 'text-rose-600' : 'text-slate-700'">{{ row.checklist_actual_hours }}h</td>
                <td class="px-4 py-3 text-sm font-semibold text-slate-700">{{ row.timesheet_hours }}h</td>
                <td class="px-4 py-3 text-sm font-bold" :class="row.variance_hours > 0 ? 'text-rose-600' : 'text-emerald-600'">{{ row.variance_hours }}h</td>
              </tr>
              <tr v-if="!projectPlanRows.length">
                <td colspan="8" class="px-4 py-8 text-center text-sm font-semibold text-slate-400">No project planning data available.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section v-if="activeTabValue === 'tasks'" class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full min-w-[1100px]">
            <thead class="bg-slate-900">
              <tr>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Done</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Task</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Project</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Stage</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Priority</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Due</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Checklist</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="task in tasks.data" :key="task.id" class="border-b last:border-b-0" :class="taskRowClass(task)">
                <td class="px-4 py-3 text-sm">
                  <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-sm font-black" :class="task.is_done ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-400 border border-slate-200'">{{ task.is_done ? '✓' : '·' }}</span>
                </td>
                <td class="px-4 py-3 text-sm font-bold text-slate-800">{{ task.title }}</td>
                <td class="px-4 py-3 text-sm text-slate-600">{{ task.project?.name || '-' }}</td>
                <td class="px-4 py-3 text-sm">
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-black"
                        :style="{ backgroundColor: ((task.stage?.color || '#64748b') + '22'), color: task.stage?.color || '#334155' }">
                    {{ task.stage?.name || 'Unknown' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm font-bold uppercase">
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-black" :class="priorityBadgeClass(task.priority)">{{ task.priority || '-' }}</span>
                </td>
                <td class="px-4 py-3 text-sm text-slate-600">{{ formatDate(task.due_date) }}</td>
                <td class="px-4 py-3 text-sm font-bold text-slate-700">
                  <div class="flex items-center justify-between gap-2">
                    <span>{{ task.checklist.completed }}/{{ task.checklist.total }}</span>
                    <span class="text-xs text-slate-500">{{ task.checklist.progress_percent }}%</span>
                  </div>
                  <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full bg-emerald-500" :style="{ width: `${task.checklist.progress_percent || 0}%` }"></div>
                  </div>
                  <div class="mt-2 text-[11px] text-slate-500 font-semibold">
                    Plan {{ minutesToHours(task.checklist.planned_minutes_total) }}h | Actual {{ minutesToHours(task.checklist.actual_minutes_total) }}h
                  </div>
                  <div class="mt-2">
                    <button
                      type="button"
                      class="h-7 px-2 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-black uppercase tracking-wider"
                      @click="openChecklistModal(task)"
                    >
                      Sub Tasks
                    </button>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <div class="space-y-2">
                    <div class="flex items-center gap-2">
                      <select
                        v-model="taskStageDraft[task.id]"
                        class="h-8 rounded-lg border border-slate-200 px-2 text-xs font-bold text-slate-700"
                      >
                        <option value="">Stage</option>
                        <option v-for="stage in stageOptionsForTask(task)" :key="stage.id" :value="String(stage.id)">
                          {{ stage.name }}
                        </option>
                      </select>
                      <button
                        type="button"
                        class="h-8 px-2 rounded-lg bg-teal-600 text-white text-xs font-black uppercase tracking-wider disabled:opacity-50"
                        :disabled="isLoading(`task-status-${task.id}`)"
                        @click="updateTaskStatus(task.id)"
                      >
                        Update
                      </button>
                      <button
                        type="button"
                        class="h-8 px-2 rounded-lg bg-slate-900 text-white text-xs font-black uppercase tracking-wider disabled:opacity-50"
                        :disabled="isLoading(`task-next-${task.id}`)"
                        @click="moveTaskNext(task.id)"
                      >
                        Next
                      </button>
                    </div>
                    <div class="flex items-center gap-2">
                      <input
                        v-model="taskCommentDraft[task.id]"
                        type="text"
                        class="h-8 rounded-lg border border-slate-200 px-2 text-xs font-semibold text-slate-700 w-44"
                        placeholder="Comment"
                      >
                      <button
                        type="button"
                        class="h-8 px-2 rounded-lg bg-indigo-600 text-white text-xs font-black uppercase tracking-wider disabled:opacity-50"
                        :disabled="isLoading(`task-comment-${task.id}`)"
                        @click="addTaskComment(task.id)"
                      >
                        Send
                      </button>
                      <button
                        type="button"
                        class="h-8 px-2 rounded-lg bg-amber-500 text-white text-xs font-black uppercase tracking-wider"
                        @click="openDriftModal('task', task)"
                      >
                        Drift
                      </button>
                    </div>
                    <p class="text-[11px] font-semibold text-slate-500">
                      Est {{ task.reporting?.task_estimated_hours ?? 0 }}h | Sub Plan {{ task.reporting?.checklist_estimated_hours ?? 0 }}h | Sub Actual {{ task.reporting?.checklist_actual_hours ?? 0 }}h | TS {{ task.reporting?.timesheet_hours_me ?? task.reporting?.timesheet_hours_total ?? 0 }}h
                    </p>
                    <div class="flex flex-wrap gap-1">
                      <span v-if="task.signals?.worked_today" class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-sky-100 text-sky-700 border border-sky-200">Worked Today</span>
                      <span v-if="task.signals?.done_today" class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-emerald-100 text-emerald-700 border border-emerald-200">Done Today</span>
                      <span v-if="task.signals?.blocked" class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-amber-100 text-amber-700 border border-amber-200">Blocked</span>
                      <span v-if="task.signals?.overdue" class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-rose-100 text-rose-700 border border-rose-200">Overdue</span>
                      <span v-if="task.signals?.no_effort_logged" class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-slate-100 text-slate-700 border border-slate-200">No Effort</span>
                      <span v-if="task.signals?.overrun_warning" class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-fuchsia-100 text-fuchsia-700 border border-fuchsia-200">Overrun 20%+</span>
                    </div>
                  </div>
                </td>
              </tr>
              <tr v-if="!tasks.data.length">
                <td colspan="8" class="px-4 py-10 text-center text-sm font-semibold text-slate-400">No assigned tasks found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section v-else class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full min-w-[980px]">
            <thead class="bg-slate-900">
              <tr>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Done</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Bug</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Project</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Stage</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Severity</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Priority</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Created</th>
                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="bug in bugs.data" :key="bug.id" class="border-b last:border-b-0" :class="bugRowClass(bug)">
                <td class="px-4 py-3 text-sm">
                  <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-sm font-black" :class="bug.is_done ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-400 border border-slate-200'">{{ bug.is_done ? '✓' : '·' }}</span>
                </td>
                <td class="px-4 py-3 text-sm font-bold text-slate-800">{{ bug.subject }}</td>
                <td class="px-4 py-3 text-sm text-slate-600">{{ bug.project?.name || '-' }}</td>
                <td class="px-4 py-3 text-sm text-slate-700 font-semibold">{{ bug.stage?.name || 'Unknown' }}</td>
                <td class="px-4 py-3 text-sm uppercase font-bold"><span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-black" :class="severityBadgeClass(bug.severity)">{{ bug.severity || '-' }}</span></td>
                <td class="px-4 py-3 text-sm uppercase font-bold"><span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-black" :class="priorityBadgeClass(bug.priority)">{{ bug.priority || '-' }}</span></td>
                <td class="px-4 py-3 text-sm text-slate-600">{{ formatDate(bug.created_at) }}</td>
                <td class="px-4 py-3">
                  <div class="space-y-2">
                    <div class="flex items-center gap-2">
                      <select
                        v-model="bugStageDraft[bug.id]"
                        class="h-8 rounded-lg border border-slate-200 px-2 text-xs font-bold text-slate-700"
                      >
                        <option value="">Stage</option>
                        <option v-for="stage in bugStageOptions" :key="stage.id" :value="String(stage.id)">
                          {{ stage.name }}
                        </option>
                      </select>
                      <button
                        type="button"
                        class="h-8 px-2 rounded-lg bg-rose-600 text-white text-xs font-black uppercase tracking-wider disabled:opacity-50"
                        :disabled="isLoading(`bug-status-${bug.id}`)"
                        @click="updateBugStatus(bug.id)"
                      >
                        Update
                      </button>
                    </div>
                    <div class="flex items-center gap-2">
                      <input
                        v-model="bugCommentDraft[bug.id]"
                        type="text"
                        class="h-8 rounded-lg border border-slate-200 px-2 text-xs font-semibold text-slate-700 w-44"
                        placeholder="Comment"
                      >
                      <button
                        type="button"
                        class="h-8 px-2 rounded-lg bg-indigo-600 text-white text-xs font-black uppercase tracking-wider disabled:opacity-50"
                        :disabled="isLoading(`bug-comment-${bug.id}`)"
                        @click="addBugComment(bug.id)"
                      >
                        Send
                      </button>
                      <button
                        type="button"
                        class="h-8 px-2 rounded-lg bg-amber-500 text-white text-xs font-black uppercase tracking-wider"
                        @click="openDriftModal('bug', bug)"
                      >
                        Drift
                      </button>
                    </div>
                  </div>
                </td>
              </tr>
              <tr v-if="!bugs.data.length">
                <td colspan="8" class="px-4 py-10 text-center text-sm font-semibold text-slate-400">No assigned bugs found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section v-if="flashMessage" class="bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-3">
        <p class="text-sm font-bold text-emerald-700">{{ flashMessage }}</p>
      </section>
      <section v-if="errorMessage" class="bg-rose-50 border border-rose-200 rounded-2xl px-4 py-3">
        <p class="text-sm font-bold text-rose-700">{{ errorMessage }}</p>
      </section>
    </div>
  </div>

  <div v-if="checklistModal.open" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="w-full max-w-5xl max-h-[92vh] overflow-hidden bg-white rounded-3xl border border-slate-200 shadow-2xl flex flex-col">
      <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div>
          <h3 class="text-xl font-black text-slate-900">Sub Tasks Workspace</h3>
          <p class="text-sm text-slate-500">{{ checklistModal.task?.title || '-' }} | {{ checklistModal.summary.completed }}/{{ checklistModal.summary.total }} done</p>
        </div>
        <button type="button" class="h-10 px-4 rounded-xl border border-slate-200 text-slate-600 text-xs font-black uppercase tracking-wider" @click="closeChecklistModal">Close</button>
      </div>

      <div class="px-6 py-4 border-b border-slate-200 grid grid-cols-1 lg:grid-cols-3 gap-3">
        <input
          v-model="checklistModal.search"
          @keyup.enter="loadChecklistPage(1)"
          type="text"
          class="h-10 rounded-xl border border-slate-200 px-3 text-sm font-semibold"
          placeholder="Search sub task content"
        >
        <button type="button" class="h-10 rounded-xl bg-slate-900 text-white text-xs font-black uppercase tracking-wider" @click="loadChecklistPage(1)">Search</button>
        <button type="button" class="h-10 rounded-xl bg-slate-100 text-slate-700 text-xs font-black uppercase tracking-wider" @click="refreshChecklist">Refresh</button>
      </div>

      <div v-if="isChecklistEditingLocked" class="px-6 py-3 border-b border-rose-200 bg-rose-50">
        <p class="text-xs font-black uppercase tracking-[0.12em] text-rose-700">Task Locked</p>
        <p class="text-sm font-semibold text-rose-700 mt-1">Sub task marking and effort updates are disabled because this task is locked.</p>
      </div>

      <div class="px-6 py-4 border-b border-slate-200 grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="rounded-2xl border border-slate-200 p-3 space-y-2">
          <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Add Single</p>
          <div class="grid grid-cols-1 md:grid-cols-[1fr_120px_auto] gap-2">
            <input v-model="checklistModal.newItem.content" type="text" class="h-9 rounded-lg border border-slate-200 px-2 text-sm font-semibold" placeholder="Sub task item content">
            <input v-model.number="checklistModal.newItem.planned_minutes" min="0" type="number" class="h-9 rounded-lg border border-slate-200 px-2 text-sm font-semibold" placeholder="Minutes">
            <button type="button" class="h-9 px-3 rounded-lg bg-emerald-600 text-white text-xs font-black uppercase tracking-wider disabled:opacity-50" :disabled="isChecklistEditingLocked || isLoading('checklist-add-single')" @click="addSingleChecklistItem">Add</button>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200 p-3 space-y-2">
          <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Bulk Add</p>
          <textarea v-model="checklistModal.bulkText" rows="3" class="w-full rounded-lg border border-slate-200 px-2 py-2 text-xs font-semibold" placeholder="One per line. Format: Item text | 30"></textarea>
          <div class="flex items-center justify-between">
            <p class="text-[11px] text-slate-500">Supports up to 500 lines per upload.</p>
            <button type="button" class="h-9 px-3 rounded-lg bg-teal-600 text-white text-xs font-black uppercase tracking-wider disabled:opacity-50" :disabled="isChecklistEditingLocked || isLoading('checklist-add-bulk')" @click="addBulkChecklistItems">Add Bulk</button>
          </div>
        </div>
      </div>

      <div class="flex-1 overflow-auto px-6 py-4">
        <div class="grid grid-cols-1 gap-2">
          <div v-for="item in checklistModal.items" :key="item.id" class="rounded-xl border border-slate-200 px-3 py-3">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
              <div>
                <p class="text-sm font-bold text-slate-800">{{ item.content }}</p>
                <p class="text-[11px] text-slate-500 font-semibold">Plan {{ minutesToHours(item.planned_minutes) }}h | Actual {{ minutesToHours(item.actual_minutes) }}h | Work Date {{ formatDate(item.work_date) }}</p>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-6 gap-2 lg:w-[920px]">
                <input
                  :value="checklistDraft(item.id).actual_minutes"
                  @input="setChecklistDraft(item.id, 'actual_minutes', $event.target.value)"
                  min="0"
                  type="number"
                  class="h-9 rounded-lg border border-slate-200 px-2 text-xs font-semibold"
                  :disabled="isChecklistEditingLocked"
                  placeholder="Actual min"
                >
                <input
                  :value="checklistDraft(item.id).work_date"
                  @input="setChecklistDraft(item.id, 'work_date', $event.target.value)"
                  type="date"
                  :class="[
                    'h-9 rounded-lg px-2 text-xs font-semibold',
                    isChecklistDateOverwritten(item)
                      ? 'border border-rose-400 bg-rose-50 text-rose-700'
                      : 'border border-slate-200',
                  ]"
                  :disabled="isChecklistEditingLocked"
                >
                <select
                  :value="checklistDraft(item.id).assigned_to"
                  @change="setChecklistDraft(item.id, 'assigned_to', $event.target.value)"
                  class="h-9 rounded-lg border border-slate-200 px-2 text-xs font-semibold bg-white"
                  :disabled="isChecklistEditingLocked"
                >
                  <option :value="null">Select Verifier...</option>
                  <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
                </select>
                <button
                  type="button"
                  class="h-9 rounded-lg text-xs font-black uppercase tracking-wider"
                  :class="item.is_completed ? 'bg-slate-200 text-slate-700' : 'bg-emerald-600 text-white'"
                  :disabled="isChecklistEditingLocked || isLoading(`task-checklist-${checklistModal.task?.id}-${item.id}`)"
                  @click="toggleTaskChecklist(checklistModal.task.id, item)"
                >
                  {{ item.is_completed ? 'Mark Open' : 'Mark Done' }}
                </button>
                <button
                  type="button"
                  class="h-9 rounded-lg bg-slate-100 text-slate-700 text-xs font-black uppercase tracking-wider"
                  :disabled="isChecklistEditingLocked || isLoading(`task-checklist-save-${checklistModal.task?.id}-${item.id}`)"
                  @click="saveChecklistEffort(checklistModal.task.id, item.id)"
                >
                  Save Effort
                </button>
                <!-- Verification Section: only shown when assignee is set -->
                <div class="flex flex-col gap-2 w-full">
                  <template v-if="!item.is_completed">
                    <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider text-center">Must Mark Done</span>
                  </template>
                  <template v-else-if="item.is_completed && !checklistDraft(item.id).assigned_to">
                    <span class="text-[10px] text-slate-400 italic text-center">Assign a member to enable verification</span>
                  </template>
                  <template v-else>
                    <div class="flex items-center gap-2 w-full">
                      <!-- Status badge or action button -->
                      <template v-if="!item.verification_status">
                        <button
                          type="button"
                          class="h-9 flex-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-wider transition-all"
                          :disabled="isChecklistEditingLocked || isLoading(`task-checklist-verify-${item.id}`)"
                          @click="openVerifyModal(item)"
                        >
                          Request Verify
                        </button>
                      </template>
                      <template v-else-if="item.verification_status === 'pending'">
                        <span class="h-9 flex-1 flex items-center justify-center bg-yellow-50 text-yellow-800 border border-yellow-200 rounded-lg text-[10px] font-black uppercase tracking-wide">
                          ⏳ Pending
                        </span>
                      </template>
                      <template v-else-if="item.verification_status === 'approved'">
                        <span class="h-9 flex-1 flex items-center justify-center bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg text-[10px] font-black uppercase tracking-wide">
                          ✅ Approved
                        </span>
                      </template>
                      <template v-else-if="item.verification_status === 'needs_correction'">
                        <button
                          type="button"
                          class="h-9 flex-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-black uppercase tracking-wider transition-all"
                          title="Needs Correction. Click to submit corrections."
                          :disabled="isChecklistEditingLocked || isLoading(`task-checklist-verify-${item.id}`)"
                          @click="openVerifyModal(item)"
                        >
                          ⚠️ Fix & Retry
                        </button>
                      </template>
                      <template v-else-if="item.verification_status === 'rejected'">
                        <button
                          type="button"
                          class="h-9 flex-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-black uppercase tracking-wider"
                          title="Rejected. Click to re-request."
                          :disabled="isChecklistEditingLocked || isLoading(`task-checklist-verify-${item.id}`)"
                          @click="openVerifyModal(item)"
                        >
                          ❌ Re-request
                        </button>
                      </template>

                      <!-- View Feedback Log Button -->
                      <button
                        v-if="item.verification_status"
                        type="button"
                        class="h-9 px-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-500 hover:text-slate-800 transition-colors"
                        title="View Feedback & Verification History"
                        @click="openFeedbackLogModal(item)"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                      </button>
                    </div>
                  </template>
                </div>
              </div>
              <p v-if="isChecklistDateOverwritten(item)" class="text-[11px] font-bold text-rose-600">
                Work date was already saved as {{ formatDate(item.work_date) }} and is now being overwritten.
              </p>
            </div>
          </div>
          <div v-if="!checklistModal.items.length" class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm font-semibold text-slate-400">
            No sub tasks found.
          </div>
        </div>
      </div>

      <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between">
        <p class="text-xs font-semibold text-slate-500">
          Planned {{ minutesToHours(checklistModal.summary.planned_minutes_total) }}h | Actual {{ minutesToHours(checklistModal.summary.actual_minutes_total) }}h
        </p>
        <div class="flex items-center gap-2">
          <button type="button" class="h-9 px-3 rounded-lg border border-slate-200 text-xs font-black uppercase tracking-wider disabled:opacity-50" :disabled="!checklistModal.pagination.prev_page_url" @click="loadChecklistPage(checklistModal.pagination.current_page - 1)">Prev</button>
          <span class="text-xs font-black text-slate-600">Page {{ checklistModal.pagination.current_page || 1 }} / {{ checklistModal.pagination.last_page || 1 }}</span>
          <button type="button" class="h-9 px-3 rounded-lg border border-slate-200 text-xs font-black uppercase tracking-wider disabled:opacity-50" :disabled="!checklistModal.pagination.next_page_url" @click="loadChecklistPage(checklistModal.pagination.current_page + 1)">Next</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ====== Feedback Log Modal ====== -->
  <div v-if="feedbackLogModal.show" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="feedbackLogModal.show = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl mx-4 overflow-hidden flex flex-col max-h-[85vh]">
      <!-- Header -->
      <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-violet-600 flex items-center justify-between">
        <div>
          <h3 class="text-white font-black text-base">Verification History & Feedback</h3>
          <p class="text-indigo-100 text-xs mt-0.5 truncate max-w-xs">{{ feedbackLogModal.checklist?.content }}</p>
        </div>
        <button @click="feedbackLogModal.show = false" class="text-white/70 hover:text-white transition-colors">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      
      <!-- Body -->
      <div class="px-6 py-5 flex-1 overflow-y-auto space-y-6">
        <div v-if="feedbackLogModal.loading" class="flex flex-col items-center justify-center py-10 space-y-3">
          <svg class="animate-spin w-8 h-8 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
          <span class="text-xs font-semibold text-slate-500">Retrieving verification log...</span>
        </div>
        
        <div v-else-if="!feedbackLogModal.history || !feedbackLogModal.history.length" class="text-center py-10 text-slate-400 font-semibold text-sm">
          No verification attempts or feedback found for this sub-task.
        </div>
        
        <div v-else class="space-y-6">
          <div v-for="(log, idx) in feedbackLogModal.history" :key="log.id || idx" class="border border-slate-100 rounded-2xl p-5 bg-slate-50/50 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
              <div>
                <span class="text-xs font-black text-slate-400 uppercase tracking-wider block">Submitted By</span>
                <span class="text-sm font-bold text-slate-800">{{ log.initiator }}</span>
              </div>
              <div class="text-right">
                <span class="text-xs font-black text-slate-400 uppercase tracking-wider block">Date</span>
                <span class="text-xs font-bold text-slate-600">{{ new Date(log.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }}</span>
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 text-xs">
              <div class="bg-white p-2.5 rounded-lg border border-slate-100">
                <span class="block text-[9px] font-black uppercase text-slate-400">Git Branch</span>
                <span class="font-bold text-slate-700 font-mono">{{ log.payload?.git_branch || '—' }}</span>
              </div>
              <div class="bg-white p-2.5 rounded-lg border border-slate-100">
                <span class="block text-[9px] font-black uppercase text-slate-400">Git Commit</span>
                <span class="font-bold text-slate-700 font-mono">{{ log.payload?.git_commit ? log.payload.git_commit.substring(0, 10) : '—' }}</span>
              </div>
            </div>
            
            <div v-if="log.payload?.feedback" class="bg-white p-3 rounded-lg border border-slate-100 text-xs">
              <span class="block text-[9px] font-black uppercase text-slate-400 mb-1">Developer Notes</span>
              <p class="text-slate-700 font-medium whitespace-pre-wrap">{{ log.payload.feedback }}</p>
            </div>
            
            <div class="space-y-2">
              <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Approver Timeline / Feedback</span>
              <div v-if="!log.approvals || !log.approvals.length" class="text-xs italic text-slate-400 font-medium">No reviews logged yet.</div>
              <div v-else class="space-y-2">
                <div v-for="appr in log.approvals" :key="appr.id" class="bg-white border border-slate-100 p-3 rounded-xl flex items-start gap-3">
                  <div :class="['w-8 h-8 rounded-xl flex items-center justify-center shrink-0 border', 
                    appr.status === 'approved' ? 'bg-emerald-50 border-emerald-200 text-emerald-600' :
                    appr.status === 'rejected' ? 'bg-rose-50 border-rose-200 text-rose-600' : 'bg-amber-50 border-amber-200 text-amber-600']">
                    <svg v-if="appr.status === 'approved'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    <svg v-else-if="appr.status === 'rejected'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <span v-else class="text-xs font-black">⏳</span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                      <span class="text-xs font-black text-slate-700">{{ appr.stage_name || 'Approver' }}</span>
                      <span :class="['text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider', 
                        appr.status === 'approved' ? 'bg-emerald-100 text-emerald-800' :
                        appr.status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800']">
                        {{ appr.status }}
                      </span>
                    </div>
                    <p class="text-[10px] text-slate-400 font-bold mt-0.5">By: {{ appr.approver_name || 'System' }}</p>
                    <p v-if="appr.comments" class="text-xs font-semibold italic text-slate-600 mt-1.5 bg-slate-50 p-2 rounded-lg border border-slate-100/60">
                      "{{ appr.comments }}"
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Footer -->
      <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end">
        <button @click="feedbackLogModal.show = false" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-black rounded-xl transition-all">Close History</button>
      </div>
    </div>
  </div>

  <!-- ====== Verification Detail Modal ====== -->
  <div v-if="verifyDetailModal.show" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="verifyDetailModal.show = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
      <!-- Header -->
      <div class="px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-500 flex items-center justify-between">
        <div>
          <h3 class="text-white font-black text-base">Request Verification</h3>
          <p class="text-amber-100 text-xs mt-0.5 truncate max-w-xs">{{ verifyDetailModal.item?.content }}</p>
        </div>
        <button @click="verifyDetailModal.show = false" class="text-white/70 hover:text-white transition-colors">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <!-- Body -->
      <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
        <!-- Error message -->
        <div v-if="verifyDetailModal.errorMsg" class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs font-semibold text-red-700">
          {{ verifyDetailModal.errorMsg }}
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wider">Completion Notes / Feedback</label>
          <textarea
            v-model="verifyForm.feedback"
            rows="3"
            placeholder="Describe what was done, blockers resolved, or notes for the reviewer..."
            class="w-full text-sm border border-gray-200 rounded-xl focus:ring-amber-400 focus:border-amber-400 px-3 py-2 resize-none"
          ></textarea>
        </div>
        <div class="p-4 bg-gray-50 rounded-xl space-y-3 border border-gray-100">
          <p class="text-[11px] font-black text-gray-500 uppercase tracking-widest">Git / Code Reference</p>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[10px] font-bold text-gray-500 mb-1">Commit Hash / ID</label>
              <input v-model="verifyForm.git_commit" type="text" placeholder="e.g. a3f1b2c" class="w-full text-xs border border-gray-200 rounded-lg focus:ring-amber-400 focus:border-amber-400 px-2 py-1.5">
            </div>
            <div>
              <label class="block text-[10px] font-bold text-gray-500 mb-1">Branch Name</label>
              <input v-model="verifyForm.git_branch" type="text" placeholder="e.g. feature/auth-fix" class="w-full text-xs border border-gray-200 rounded-lg focus:ring-amber-400 focus:border-amber-400 px-2 py-1.5">
            </div>
          </div>
          <div>
            <label class="block text-[10px] font-bold text-gray-500 mb-1">PR / MR URL</label>
            <input v-model="verifyForm.git_pr_url" type="url" placeholder="https://github.com/.../pull/42" class="w-full text-xs border border-gray-200 rounded-lg focus:ring-amber-400 focus:border-amber-400 px-2 py-1.5">
          </div>
          <div>
            <label class="block text-[10px] font-bold text-gray-500 mb-1">Frameworks / Libraries Used</label>
            <input v-model="verifyForm.frameworks" type="text" placeholder="e.g. Vue 3, Laravel, TailwindCSS" class="w-full text-xs border border-gray-200 rounded-lg focus:ring-amber-400 focus:border-amber-400 px-2 py-1.5">
          </div>
          <div>
            <label class="block text-[10px] font-bold text-gray-500 mb-1">Code References / Files Changed</label>
            <textarea v-model="verifyForm.code_references" rows="2" placeholder="e.g. app/Http/Controllers/Auth.php, resources/js/Pages/Login.vue" class="w-full text-xs border border-gray-200 rounded-xl focus:ring-amber-400 focus:border-amber-400 px-2 py-1.5 resize-none"></textarea>
          </div>
        </div>
      </div>
      <!-- Footer -->
      <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-3">
        <button @click="verifyDetailModal.show = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition-colors">Cancel</button>
        <button
          @click="submitChecklistVerification"
          :disabled="verifyDetailModal.loading"
          class="px-5 py-2 bg-amber-500 hover:bg-amber-600 disabled:opacity-60 text-white text-sm font-black rounded-xl transition-all flex items-center gap-2"
        >
          <svg v-if="verifyDetailModal.loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
          <span>{{ verifyDetailModal.loading ? 'Sending...' : 'Send Verification Request' }}</span>
        </button>
      </div>
    </div>
  </div>

  <div v-if="driftModal.open" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="w-full max-w-xl bg-white rounded-3xl border border-slate-200 shadow-2xl p-6 space-y-4">
      <h3 class="text-xl font-black text-slate-900">Submit Drift</h3>
      <p class="text-sm text-slate-500">Capture delay/effort drift for review.</p>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
          <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Category</label>
          <select v-model="driftModal.category" class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-semibold">
            <option value="priority_conflict">Priority Conflict</option>
            <option value="scope_change">Scope Change</option>
            <option value="complexity_drag">Complexity Drag</option>
          </select>
        </div>
        <div>
          <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Extended End Date</label>
          <input v-model="driftModal.extended_end_date" type="date" class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-semibold">
        </div>
        <div>
          <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Days Added</label>
          <input v-model.number="driftModal.days_added" min="0" type="number" class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-semibold">
        </div>
        <div>
          <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Hours Added</label>
          <input v-model.number="driftModal.hours_added" min="0" step="0.5" type="number" class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-semibold">
        </div>
      </div>

      <div>
        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Reason</label>
        <textarea v-model="driftModal.reason" rows="3" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold" placeholder="Brief reason"></textarea>
      </div>

      <div>
        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Notes (Optional)</label>
        <textarea v-model="driftModal.notes" rows="2" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold" placeholder="Additional context"></textarea>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" class="h-10 px-4 rounded-xl border border-slate-200 text-slate-600 text-xs font-black uppercase tracking-wider" @click="closeDriftModal">
          Cancel
        </button>
        <button type="button" class="h-10 px-4 rounded-xl bg-amber-500 text-white text-xs font-black uppercase tracking-wider disabled:opacity-50" :disabled="isLoading('drift-submit')" @click="submitDrift">
          Submit Drift
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
  activeTab: { type: String, default: 'tasks' },
  summary: { type: Object, default: () => ({ tasks_count: 0, bugs_count: 0 }) },
  filters: { type: Object, default: () => ({ q: '', project_id: '', priority: '', task_status: '', bug_stage: '', quick_filter: '', focus_mode: false, tab: 'tasks' }) },
  savedDefaults: { type: Object, default: () => ({}) },
  savedPresets: { type: Array, default: () => [] },
  todayCommand: { type: Object, default: () => ({}) },
  rangeSummary: { type: Object, default: () => ({}) },
  timesheetInsights: { type: Object, default: () => ({}) },
  ops360: { type: Object, default: () => ({}) },
  planCards: { type: Array, default: () => [] },
  projectPlanRows: { type: Array, default: () => [] },
  tasks: { type: Object, default: () => ({ data: [] }) },
  bugs: { type: Object, default: () => ({ data: [] }) },
  projectOptions: { type: Array, default: () => [] },
  taskPriorityOptions: { type: Array, default: () => [] },
  bugPriorityOptions: { type: Array, default: () => [] },
  taskStageOptions: { type: Object, default: () => ({}) },
  bugStageOptions: { type: Array, default: () => [] },
  employees: { type: Array, default: () => [] },
});

const localFilters = reactive({
  q: props.filters?.q || '',
  project_id: props.filters?.project_id ? String(props.filters.project_id) : '',
  priority: props.filters?.priority || '',
  task_status: props.filters?.task_status || '',
  bug_stage: props.filters?.bug_stage || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
  quick_filter: props.filters?.quick_filter || '',
  focus_mode: Boolean(props.filters?.focus_mode),
});

const presetState = reactive({
  name: '',
  selectedKey: '',
});

const taskStageDraft = reactive({});
const bugStageDraft = reactive({});
const taskCommentDraft = reactive({});
const bugCommentDraft = reactive({});
const loadingMap = reactive({});

const flashMessage = ref('');
const errorMessage = ref('');

const driftModal = reactive({
  open: false,
  entityType: 'task',
  entityId: null,
  category: 'priority_conflict',
  days_added: 0,
  hours_added: 0,
  reason: '',
  notes: '',
  extended_end_date: '',
});

const checklistModal = reactive({
  open: false,
  task: null,
  items: [],
  search: '',
  bulkText: '',
  newItem: {
    content: '',
    planned_minutes: '',
  },
  summary: {
    total: 0,
    completed: 0,
    planned_minutes_total: 0,
    actual_minutes_total: 0,
  },
  pagination: {
    current_page: 1,
    last_page: 1,
    prev_page_url: null,
    next_page_url: null,
  },
});

const checklistDrafts = reactive({});

const activeTabValue = computed(() => (props.activeTab === 'bugs' ? 'bugs' : 'tasks'));
const isChecklistEditingLocked = computed(() => Boolean(checklistModal.task?.is_locked));

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

watch(
  () => props.tasks.data,
  (tasks) => {
    tasks.forEach((task) => {
      taskStageDraft[task.id] = task.stage?.id ? String(task.stage.id) : '';
      if (typeof taskCommentDraft[task.id] === 'undefined') {
        taskCommentDraft[task.id] = '';
      }
    });
  },
  { immediate: true },
);

watch(
  () => props.bugs.data,
  (bugs) => {
    bugs.forEach((bug) => {
      bugStageDraft[bug.id] = bug.stage?.id ? String(bug.stage.id) : '';
      if (typeof bugCommentDraft[bug.id] === 'undefined') {
        bugCommentDraft[bug.id] = '';
      }
    });
  },
  { immediate: true },
);

watch(
  () => props.filters,
  (filters) => {
    localFilters.q = filters?.q || '';
    localFilters.project_id = filters?.project_id ? String(filters.project_id) : '';
    localFilters.priority = filters?.priority || '';
    localFilters.task_status = filters?.task_status ? String(filters.task_status) : '';
    localFilters.bug_stage = filters?.bug_stage ? String(filters.bug_stage) : '';
    localFilters.date_from = filters?.date_from || '';
    localFilters.date_to = filters?.date_to || '';
    localFilters.quick_filter = filters?.quick_filter || '';
    localFilters.focus_mode = Boolean(filters?.focus_mode);
  },
  { deep: true, immediate: true },
);

watch(
  () => props.savedPresets,
  (presets) => {
    if (!presetState.selectedKey && presets?.length) {
      presetState.selectedKey = presets[0].key;
    }
  },
  { immediate: true },
);

const quickFilterChips = [
  { label: 'All', value: '' },
  { label: 'Overdue', value: 'overdue' },
  { label: 'High Priority', value: 'high_priority' },
  { label: 'No Progress Today', value: 'no_progress_today' },
  { label: 'Done Today', value: 'done_today' },
];

const activePriorityOptions = computed(() => (activeTabValue.value === 'bugs' ? props.bugPriorityOptions : props.taskPriorityOptions));

const flattenedTaskStageOptions = computed(() => Object.values(props.taskStageOptions || {}).flat());

const todayAttentionTasks = computed(() => props.todayCommand?.attention_tasks || []);
const opsLaunchers = computed(() => props.ops360?.launchers || {});
const opsCards = computed(() => props.ops360?.cards || {});
const employeeTrendSeries = computed(() => props.ops360?.insights?.employee360_trend || []);
const devopsTrendSeries = computed(() => []);

const last7TimesheetSeries = computed(() => props.timesheetInsights?.last_7_days || []);
const projectBreakdownSeries = computed(() => props.timesheetInsights?.project_breakdown || []);
const weeklyOverallSeries = computed(() => props.timesheetInsights?.weekly_overall || []);

const maxTimesheetBarHours = computed(() => {
  const values = last7TimesheetSeries.value.map((item) => Number(item.hours || 0));
  return Math.max(1, ...values);
});

const maxProjectContributionHours = computed(() => {
  const values = projectBreakdownSeries.value.map((item) => Number(item.hours || 0));
  return Math.max(1, ...values);
});

const maxWeeklyOverallHours = computed(() => {
  const values = weeklyOverallSeries.value.map((item) => Number(item.hours || 0));
  return Math.max(1, ...values);
});

const maxEmployeeTrendHours = computed(() => {
  const values = employeeTrendSeries.value.map((item) => Number(item.hours || 0));
  return Math.max(1, ...values);
});

const maxDevOpsTrendValues = computed(() => {
  const values = devopsTrendSeries.value.map((item) => Number(item.value || 0));
  return Math.max(1, ...values);
});

const barHeight = (hours) => {
  const value = Number(hours || 0);
  return Math.round((value / maxTimesheetBarHours.value) * 110);
};

const projectShareWidth = (hours) => {
  const value = Number(hours || 0);
  return Math.max(4, Math.round((value / maxProjectContributionHours.value) * 100));
};

const weeklyBarHeight = (hours) => {
  const value = Number(hours || 0);
  return Math.round((value / maxWeeklyOverallHours.value) * 110);
};

const employeeTrendBarHeight = (hours) => {
  const value = Number(hours || 0);
  return Math.round((value / maxEmployeeTrendHours.value) * 90);
};

const devopsTrendBarHeight = (value) => {
  const current = Number(value || 0);
  return Math.round((current / maxDevOpsTrendValues.value) * 90);
};

const opsRiskBadgeClass = (risk) => {
  if (risk === 'High') return 'bg-rose-100 text-rose-700 border border-rose-200';
  if (risk === 'Medium') return 'bg-amber-100 text-amber-700 border border-amber-200';
  return 'bg-emerald-100 text-emerald-700 border border-emerald-200';
};

const todayPaceRatio = computed(() => {
  const planned = Number(props.todayCommand?.hours_planned_today || 0);
  const logged = Number(props.todayCommand?.hours_logged_today || 0);
  if (planned <= 0) return logged > 0 ? 1 : 0;
  return logged / planned;
});

const todayPaceText = computed(() => {
  const ratio = todayPaceRatio.value;
  if (ratio >= 1) return 'On or above planned pace for today.';
  if (ratio >= 0.75) return 'Slightly behind, but still on track.';
  if (ratio > 0) return 'Work has started; pace needs push.';
  return 'No effort logged yet today.';
});

const todayPaceClass = computed(() => {
  const ratio = todayPaceRatio.value;
  if (ratio >= 1) return 'text-emerald-600';
  if (ratio >= 0.75) return 'text-amber-600';
  return 'text-rose-600';
});

const savedDefaultProjectName = computed(() => {
  if (!props.savedDefaults?.project_id) return 'all';
  const match = props.projectOptions.find((project) => String(project.id) === String(props.savedDefaults.project_id));
  return match?.name || props.savedDefaults.project_id;
});

const buildFilterPayload = () => ({
  q: localFilters.q,
  project_id: localFilters.project_id || null,
  priority: localFilters.priority || null,
  task_status: localFilters.task_status || null,
  bug_stage: localFilters.bug_stage || null,
  date_from: localFilters.date_from || null,
  date_to: localFilters.date_to || null,
  quick_filter: localFilters.quick_filter || null,
  focus_mode: Boolean(localFilters.focus_mode),
  tab: activeTabValue.value,
});

const setQuickFilter = (value) => {
  localFilters.quick_filter = value;
  applyFilters();
};

const quickFilterChipClass = (value) => {
  const active = (localFilters.quick_filter || '') === value;
  return active ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400';
};

const toggleFocusMode = () => {
  localFilters.focus_mode = !localFilters.focus_mode;
  applyFilters();
};

const applySelectedPreset = () => {
  if (!presetState.selectedKey) return;
  const preset = props.savedPresets.find((item) => item.key === presetState.selectedKey);
  if (!preset) return;

  localFilters.q = preset.filters?.q || '';
  localFilters.project_id = preset.filters?.project_id ? String(preset.filters.project_id) : '';
  localFilters.priority = preset.filters?.priority || '';
  localFilters.task_status = preset.filters?.task_status ? String(preset.filters.task_status) : '';
  localFilters.bug_stage = preset.filters?.bug_stage ? String(preset.filters.bug_stage) : '';
  localFilters.date_from = preset.filters?.date_from || '';
  localFilters.date_to = preset.filters?.date_to || '';
  localFilters.quick_filter = preset.filters?.quick_filter || '';
  localFilters.focus_mode = Boolean(preset.filters?.focus_mode);
  setTab(preset.filters?.tab === 'bugs' ? 'bugs' : 'tasks');
};

const saveCurrentAsPreset = async () => {
  const name = presetState.name.trim();
  if (!name) {
    setError('Preset name is required.');
    return;
  }

  try {
    setLoading('preset-save', true);
    const result = await postJson(route('employee.work.defaults.save'), {
      ...buildFilterPayload(),
      save_as_preset: true,
      preset_name: name,
    });
    setFlash(result.message || 'Preset saved.');
    presetState.name = '';
    refreshWorkbench();
  } catch (error) {
    setError(error.message);
  } finally {
    setLoading('preset-save', false);
  }
};

const deleteSelectedPreset = async () => {
  if (!presetState.selectedKey) {
    setError('Select a preset first.');
    return;
  }

  try {
    setLoading('preset-delete', true);
    const response = await axios.delete(`${route('employee.work.defaults.clear')}?preset_key=${encodeURIComponent(presetState.selectedKey)}`);
    setFlash(response.data.message || 'Preset deleted.');
    presetState.selectedKey = '';
    refreshWorkbench();
  } catch (error) {
    setError(error.response?.data?.message || error.message);
  } finally {
    setLoading('preset-delete', false);
  }
};

const setTab = (tab) => {
  router.get(route('employee.work.index'), {
    ...buildFilterPayload(),
    tab,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const applyFilters = () => {
  router.get(route('employee.work.index'), {
    ...buildFilterPayload(),
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
  localFilters.q = '';
  localFilters.project_id = '';
  localFilters.priority = '';
  localFilters.task_status = '';
  localFilters.bug_stage = '';
  localFilters.date_from = '';
  localFilters.date_to = '';
  localFilters.quick_filter = '';
  localFilters.focus_mode = false;
  applyFilters();
};

const setLoading = (key, value) => {
  loadingMap[key] = value;
};

const isLoading = (key) => Boolean(loadingMap[key]);

const setFlash = (message) => {
  flashMessage.value = message || '';
  errorMessage.value = '';
};

const setError = (message) => {
  flashMessage.value = '';
  errorMessage.value = message || 'Something went wrong.';
};

const saveDefaultFilters = async () => {
  try {
    setLoading('defaults-save', true);
    const result = await postJson(route('employee.work.defaults.save'), buildFilterPayload());
    setFlash(result.message || 'Defaults saved.');
    refreshWorkbench();
  } catch (error) {
    setError(error.message);
  } finally {
    setLoading('defaults-save', false);
  }
};

const clearDefaultFilters = async () => {
  try {
    setLoading('defaults-clear', true);
    const response = await axios.delete(route('employee.work.defaults.clear'));
    setFlash(response.data.message || 'Defaults cleared.');
    refreshWorkbench();
  } catch (error) {
    setError(error.response?.data?.message || error.message);
  } finally {
    setLoading('defaults-clear', false);
  }
};

const priorityBadgeClass = (priority) => {
  const value = String(priority || '').toLowerCase();
  if (['critical', 'urgent', 'highest', 'p0', 'high'].includes(value)) return 'bg-rose-100 text-rose-700 border border-rose-200';
  if (['medium', 'normal', 'p1', 'moderate'].includes(value)) return 'bg-amber-100 text-amber-700 border border-amber-200';
  if (['low', 'minor', 'p2', 'p3'].includes(value)) return 'bg-emerald-100 text-emerald-700 border border-emerald-200';
  return 'bg-slate-100 text-slate-700 border border-slate-200';
};

const severityBadgeClass = (severity) => {
  const value = String(severity || '').toLowerCase();
  if (['critical', 'blocker', 'sev1', 'high'].includes(value)) return 'bg-fuchsia-100 text-fuchsia-700 border border-fuchsia-200';
  if (['major', 'sev2', 'medium'].includes(value)) return 'bg-orange-100 text-orange-700 border border-orange-200';
  if (['minor', 'low', 'sev3', 'sev4'].includes(value)) return 'bg-sky-100 text-sky-700 border border-sky-200';
  return 'bg-slate-100 text-slate-700 border border-slate-200';
};

const taskRowClass = (task) => {
  if (task?.is_done) return 'border-emerald-100 bg-emerald-50/40';
  const value = String(task?.priority || '').toLowerCase();
  if (['critical', 'urgent', 'highest', 'p0', 'high'].includes(value)) return 'border-rose-100 bg-rose-50/35 hover:bg-rose-50/50';
  if (['medium', 'normal', 'p1'].includes(value)) return 'border-amber-100 bg-amber-50/25 hover:bg-amber-50/40';
  return 'border-slate-100 hover:bg-slate-50/70';
};

const bugRowClass = (bug) => {
  if (bug?.is_done) return 'border-emerald-100 bg-emerald-50/40';
  const severity = String(bug?.severity || '').toLowerCase();
  if (['critical', 'blocker', 'sev1', 'high'].includes(severity)) return 'border-fuchsia-100 bg-fuchsia-50/35 hover:bg-fuchsia-50/50';
  const priority = String(bug?.priority || '').toLowerCase();
  if (['critical', 'urgent', 'highest', 'p0', 'high'].includes(priority)) return 'border-rose-100 bg-rose-50/35 hover:bg-rose-50/50';
  return 'border-slate-100 hover:bg-slate-50/70';
};

const stageOptionsForTask = (task) => {
  if (!task?.project_id) return [];
  return props.taskStageOptions[String(task.project_id)] || props.taskStageOptions[task.project_id] || [];
};

const postJson = async (url, payload) => {
  try {
    const response = await axios.post(url, payload || {});
    return response.data;
  } catch (error) {
    throw new Error(error.response?.data?.message || error.message || 'Request failed.');
  }
};

const refreshWorkbench = () => {
  router.reload({
    preserveScroll: true,
    only: ['filters', 'savedDefaults', 'savedPresets', 'todayCommand', 'rangeSummary', 'planCards', 'projectPlanRows', 'summary', 'tasks', 'bugs', 'projectOptions', 'taskPriorityOptions', 'bugPriorityOptions', 'taskStageOptions', 'bugStageOptions'],
  });
};

const updateTaskStatus = async (taskId) => {
  if (!taskStageDraft[taskId]) {
    setError('Select a stage before updating task status.');
    return;
  }

  const key = `task-status-${taskId}`;
  try {
    setLoading(key, true);
    const result = await postJson(route('employee.work.tasks.status', taskId), {
      stage_id: Number(taskStageDraft[taskId]),
    });
    setFlash(result.message || 'Task status updated.');
    refreshWorkbench();
  } catch (error) {
    setError(error.message);
  } finally {
    setLoading(key, false);
  }
};

const moveTaskNext = async (taskId) => {
  const key = `task-next-${taskId}`;
  try {
    setLoading(key, true);
    const result = await postJson(route('employee.work.tasks.move-next', taskId), {});
    setFlash(result.message || 'Task moved to next stage.');
    refreshWorkbench();
  } catch (error) {
    setError(error.message);
  } finally {
    setLoading(key, false);
  }
};

const addTaskComment = async (taskId) => {
  const body = (taskCommentDraft[taskId] || '').trim();
  if (!body) {
    setError('Comment cannot be empty.');
    return;
  }

  const key = `task-comment-${taskId}`;
  try {
    setLoading(key, true);
    const result = await postJson(route('employee.work.tasks.comments.store', taskId), { body });
    taskCommentDraft[taskId] = '';
    setFlash(result.message || 'Comment added.');
  } catch (error) {
    setError(error.message);
  } finally {
    setLoading(key, false);
  }
};

const checklistDraft = (itemId) => {
  if (!checklistDrafts[itemId]) {
    checklistDrafts[itemId] = {
      actual_minutes: '',
      work_date: '',
      assigned_to: null,
    };
  }

  return checklistDrafts[itemId];
};

const setChecklistDraft = (itemId, key, value) => {
  checklistDraft(itemId)[key] = value;
};

const isChecklistDateOverwritten = (item) => {
  const savedDate = toInputDate(item?.work_date) || '';
  if (!savedDate) return false;
  return checklistDraft(item.id).work_date !== savedDate;
};

const minutesToHours = (minutes) => {
  const value = Number(minutes || 0);
  return (value / 60).toFixed(2);
};

const applyChecklistItems = (items) => {
  checklistModal.items = items || [];
  checklistModal.items.forEach((item) => {
    checklistDrafts[item.id] = {
      actual_minutes: item.actual_minutes ?? '',
      work_date: toInputDate(item.work_date) || '',
      assigned_to: item.assigned_to ?? null,
    };
  });
};

const loadChecklistPage = async (page = 1) => {
  if (!checklistModal.task?.id) return;

  const key = `task-checklist-load-${checklistModal.task.id}`;
  try {
    setLoading(key, true);
    const params = new URLSearchParams({
      page: String(page),
      per_page: '50',
    });

    if (checklistModal.search.trim()) {
      params.set('q', checklistModal.search.trim());
    }

    const response = await axios.get(`${route('employee.work.tasks.checklists.index', checklistModal.task.id)}?${params.toString()}`);
    const data = response.data;

    applyChecklistItems(data.items?.data || []);
    checklistModal.pagination = {
      current_page: data.items?.current_page || 1,
      last_page: data.items?.last_page || 1,
      prev_page_url: data.items?.prev_page_url || null,
      next_page_url: data.items?.next_page_url || null,
    };
    checklistModal.summary = {
      total: data.summary?.total || 0,
      completed: data.summary?.completed || 0,
      planned_minutes_total: data.summary?.planned_minutes_total || 0,
      actual_minutes_total: data.summary?.actual_minutes_total || 0,
    };
  } catch (error) {
    setError(error.message);
  } finally {
    setLoading(key, false);
  }
};

const openChecklistModal = async (task) => {
  checklistModal.open = true;
  checklistModal.task = task;
  checklistModal.search = '';
  checklistModal.bulkText = '';
  checklistModal.newItem = {
    content: '',
    planned_minutes: '',
  };
  await loadChecklistPage(1);
};

const closeChecklistModal = () => {
  checklistModal.open = false;
  checklistModal.task = null;
  checklistModal.items = [];
};

const refreshChecklist = async () => {
  if (!checklistModal.open) return;
  await loadChecklistPage(checklistModal.pagination.current_page || 1);
};

const toggleTaskChecklist = async (taskId, item) => {
  if (isChecklistEditingLocked.value) {
    setError('Task is locked. You cannot mark checklist items for a locked task.');
    return;
  }

  const key = `task-checklist-${taskId}-${item.id}`;
  const draft = checklistDraft(item.id);
  if (!item.is_completed && !draft.work_date) {
    draft.work_date = toInputDate(new Date());
  }
  try {
    setLoading(key, true);
    const result = await postJson(route('employee.work.tasks.checklists.toggle', [taskId, item.id]), {
      is_completed: !item.is_completed,
      actual_minutes: draft.actual_minutes === '' ? null : Number(draft.actual_minutes),
      work_date: draft.work_date || null,
      assigned_to: draft.assigned_to || null,
    });
    setFlash(result.message || 'Checklist item updated.');
    refreshWorkbench();
    await refreshChecklist();
  } catch (error) {
    setError(error.message);
  } finally {
    setLoading(key, false);
  }
};

const saveChecklistEffort = async (taskId, checklistId) => {
  if (isChecklistEditingLocked.value) {
    setError('Task is locked. You cannot save checklist effort for a locked task.');
    return;
  }

  const key = `task-checklist-save-${taskId}-${checklistId}`;
  const draft = checklistDraft(checklistId);

  try {
    setLoading(key, true);
    const result = await postJson(route('employee.work.tasks.checklists.toggle', [taskId, checklistId]), {
      is_completed: undefined,
      actual_minutes: draft.actual_minutes === '' ? null : Number(draft.actual_minutes),
      work_date: draft.work_date || null,
      assigned_to: draft.assigned_to || null,
    });
    setFlash(result.message || 'Checklist effort updated.');
    refreshWorkbench();
    await refreshChecklist();
  } catch (error) {
    setError(error.message);
  } finally {
    setLoading(key, false);
  }
};

// Verification detail modal state
const verifyDetailModal = ref({ show: false, item: null, loading: false, errorMsg: '' });
const verifyForm = reactive({
  feedback: '',
  git_commit: '',
  git_branch: '',
  git_pr_url: '',
  frameworks: '',
  code_references: '',
});

const feedbackLogModal = ref({
  show: false,
  loading: false,
  checklist: null,
  history: []
});

const openFeedbackLogModal = async (item) => {
  feedbackLogModal.value = {
    show: true,
    loading: true,
    checklist: item,
    history: []
  };
  try {
    const response = await axios.get(route('employee.work.tasks.checklists.verification-log', [checklistModal.task.id, item.id]));
    feedbackLogModal.value.history = response.data.history || [];
  } catch (error) {
    console.error("Failed to load verification log", error);
  } finally {
    feedbackLogModal.value.loading = false;
  }
};

const openVerifyModal = (item) => {
  if (isChecklistEditingLocked.value) {
    setError('Task is locked. You cannot request verification.');
    return;
  }
  verifyDetailModal.value = { show: true, item, loading: false, errorMsg: '' };
  Object.assign(verifyForm, { feedback: '', git_commit: '', git_branch: '', git_pr_url: '', frameworks: '', code_references: '' });
};

const requestChecklistVerification = async (item) => {
  // Legacy fallback — opens modal now
  openVerifyModal(item);
};

const submitChecklistVerification = async () => {
  const item = verifyDetailModal.value.item;
  if (!item) return;

  if (isChecklistEditingLocked.value) {
    setError('Task is locked. You cannot request verification.');
    return;
  }

  const key = `task-checklist-verify-${item.id}`;
  verifyDetailModal.value.loading = true;
  verifyDetailModal.value.errorMsg = '';
  try {
    setLoading(key, true);

    // Auto-save any effort/assignee changes from the draft first
    const draft = checklistDraft(item.id);
    await postJson(route('employee.work.tasks.checklists.toggle', [checklistModal.task.id, item.id]), {
      is_completed: item.is_completed,
      actual_minutes: draft.actual_minutes === '' ? null : Number(draft.actual_minutes),
      work_date: draft.work_date || null,
      assigned_to: draft.assigned_to || null,
    });

    item.actual_minutes = draft.actual_minutes === '' ? null : Number(draft.actual_minutes);
    item.work_date = draft.work_date || null;
    item.assigned_to = draft.assigned_to || null;

    const result = await postJson(
      route('employee.work.tasks.checklists.request-verification', [checklistModal.task.id, item.id]),
      { ...verifyForm }
    );
    setFlash(result.message || 'Verification request sent.');
    item.verification_status = 'pending';
    verifyDetailModal.value.show = false;
    await refreshChecklist();
  } catch (error) {
    verifyDetailModal.value.errorMsg = error.message || 'Failed to request verification.';
    setError(error.message || 'Failed to request verification.');
  } finally {
    setLoading(key, false);
    verifyDetailModal.value.loading = false;
  }
};


const addSingleChecklistItem = async () => {
  if (!checklistModal.task?.id) return;
  if (isChecklistEditingLocked.value) {
    setError('Task is locked. You cannot add checklist items for a locked task.');
    return;
  }
  if (!checklistModal.newItem.content.trim()) {
    setError('Checklist content is required.');
    return;
  }

  try {
    setLoading('checklist-add-single', true);
    const result = await postJson(route('employee.work.tasks.checklists.store', checklistModal.task.id), {
      content: checklistModal.newItem.content.trim(),
      planned_minutes: checklistModal.newItem.planned_minutes === '' ? null : Number(checklistModal.newItem.planned_minutes),
    });
    setFlash(result.message || 'Checklist item added.');
    checklistModal.newItem.content = '';
    checklistModal.newItem.planned_minutes = '';
    refreshWorkbench();
    await loadChecklistPage(1);
  } catch (error) {
    setError(error.message);
  } finally {
    setLoading('checklist-add-single', false);
  }
};

const addBulkChecklistItems = async () => {
  if (!checklistModal.task?.id) return;
  if (isChecklistEditingLocked.value) {
    setError('Task is locked. You cannot add checklist items for a locked task.');
    return;
  }

  const rows = checklistModal.bulkText
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean);

  if (!rows.length) {
    setError('Add at least one checklist line for bulk import.');
    return;
  }

  const items = rows.map((line) => {
    const [contentPart, minutePart] = line.split('|').map((token) => token?.trim());
    const plannedMinutes = minutePart && !Number.isNaN(Number(minutePart)) ? Number(minutePart) : null;
    return {
      content: contentPart,
      planned_minutes: plannedMinutes,
    };
  }).filter((row) => row.content);

  try {
    setLoading('checklist-add-bulk', true);
    const result = await postJson(route('employee.work.tasks.checklists.bulk-store', checklistModal.task.id), {
      items,
    });
    setFlash(result.message || 'Checklist items added.');
    checklistModal.bulkText = '';
    refreshWorkbench();
    await loadChecklistPage(1);
  } catch (error) {
    setError(error.message);
  } finally {
    setLoading('checklist-add-bulk', false);
  }
};

const updateBugStatus = async (bugId) => {
  if (!bugStageDraft[bugId]) {
    setError('Select a stage before updating bug status.');
    return;
  }

  const key = `bug-status-${bugId}`;
  try {
    setLoading(key, true);
    const result = await postJson(route('employee.work.bugs.status', bugId), {
      stage_id: Number(bugStageDraft[bugId]),
    });
    setFlash(result.message || 'Bug status updated.');
    refreshWorkbench();
  } catch (error) {
    setError(error.message);
  } finally {
    setLoading(key, false);
  }
};

const addBugComment = async (bugId) => {
  const body = (bugCommentDraft[bugId] || '').trim();
  if (!body) {
    setError('Comment cannot be empty.');
    return;
  }

  const key = `bug-comment-${bugId}`;
  try {
    setLoading(key, true);
    const result = await postJson(route('employee.work.bugs.comments.store', bugId), { body });
    bugCommentDraft[bugId] = '';
    setFlash(result.message || 'Comment added.');
  } catch (error) {
    setError(error.message);
  } finally {
    setLoading(key, false);
  }
};

const toInputDate = (value) => {
  if (!value) return '';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '';
  const year = date.getFullYear();
  const month = `${date.getMonth() + 1}`.padStart(2, '0');
  const day = `${date.getDate()}`.padStart(2, '0');
  return `${year}-${month}-${day}`;
};

const openDriftModal = (entityType, entity) => {
  driftModal.open = true;
  driftModal.entityType = entityType;
  driftModal.entityId = entity.id;
  driftModal.category = 'priority_conflict';
  driftModal.days_added = 0;
  driftModal.hours_added = 0;
  driftModal.reason = '';
  driftModal.notes = '';
  driftModal.extended_end_date = toInputDate(entityType === 'task' ? entity.due_date : null);
};

const closeDriftModal = () => {
  driftModal.open = false;
};

const submitDrift = async () => {
  if (!driftModal.reason.trim()) {
    setError('Reason is required for drift submission.');
    return;
  }

  const routeName = driftModal.entityType === 'task'
    ? 'employee.work.tasks.drift.store'
    : 'employee.work.bugs.drift.store';

  try {
    setLoading('drift-submit', true);
    const result = await postJson(route(routeName, driftModal.entityId), {
      category: driftModal.category,
      days_added: Number(driftModal.days_added || 0),
      hours_added: Number(driftModal.hours_added || 0),
      reason: driftModal.reason,
      notes: driftModal.notes || null,
      extended_end_date: driftModal.extended_end_date || null,
    });
    setFlash(result.message || 'Drift submitted.');
    closeDriftModal();
  } catch (error) {
    setError(error.message);
  } finally {
    setLoading('drift-submit', false);
  }
};

const formatDate = (value) => {
  if (!value) return '-';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '-';
  return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

</script>
