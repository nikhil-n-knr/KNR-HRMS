<template>
  <div class="min-h-screen pb-24">
    <div class="space-y-8">
      <header class="rounded-3xl border border-white/60 bg-gradient-to-r from-indigo-50 via-white to-emerald-50 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <p class="text-[11px] font-black uppercase tracking-[0.4em] text-indigo-600">Manager Command View</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900 tracking-tight">Team Performance</h1>
            <p class="mt-2 text-sm text-slate-500">Keep your team aligned on velocity, workload, and delivery risk.</p>
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-indigo-50 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-indigo-600">Team Size {{ teamCount }}</span>
            <span class="rounded-full bg-slate-100 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-slate-600">Sprint {{ team_performance.sprint_completion }}%</span>
            <span class="rounded-full bg-slate-100 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-slate-600">Incidents {{ team_performance.active_incidents }}</span>
          </div>
        </div>
      </header>

      <section class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
          <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Velocity</p>
          <p class="mt-3 text-3xl font-black text-slate-900">{{ team_performance.velocity }}</p>
        </div>
        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
          <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Sprint Completion</p>
          <p class="mt-3 text-3xl font-black text-slate-900">{{ team_performance.sprint_completion }}%</p>
        </div>
        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
          <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Critical Incidents</p>
          <p class="mt-3 text-3xl font-black text-slate-900">{{ team_performance.active_incidents }}</p>
        </div>
        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
          <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Weekly Engagement</p>
          <p class="mt-3 text-3xl font-black text-slate-900">{{ avgEngagement }}%</p>
        </div>
      </section>

      <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm lg:col-span-2">
          <h2 class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Strategic Intelligence</h2>
          <p class="mt-2 text-lg font-black text-slate-900">Engagement Trend</p>
          <div class="mt-6 grid h-40 grid-cols-7 items-end gap-2">
            <div v-for="(value, idx) in engagementTrend" :key="idx" class="flex items-end">
              <div class="w-4 rounded-full bg-indigo-500/80" :style="{ height: value + '%' }"></div>
            </div>
          </div>
          <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Team Size</p>
              <p class="mt-2 text-xl font-black text-slate-900">{{ teamCount }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Velocity</p>
              <p class="mt-2 text-xl font-black text-slate-900">{{ team_performance.velocity }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Completion</p>
              <p class="mt-2 text-xl font-black text-slate-900">{{ team_performance.sprint_completion }}%</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Incidents</p>
              <p class="mt-2 text-xl font-black text-slate-900">{{ team_performance.active_incidents }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
          <h2 class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Team Attendance</h2>
          <p class="mt-2 text-lg font-black text-slate-900">Today</p>
          <div class="mt-6 space-y-4">
            <div v-for="status in teamAttendance" :key="status.status" class="rounded-2xl border border-slate-100 bg-white p-4">
              <div class="flex items-center justify-between text-xs font-black uppercase tracking-widest text-slate-500">
                <span>{{ status.status }}</span>
                <span class="text-slate-900">{{ status.count }}</span>
              </div>
              <div class="mt-2 h-2 w-full rounded-full bg-slate-100">
                <div class="h-2 rounded-full bg-emerald-500" :style="{ width: attendancePercent(status.count) + '%' }"></div>
              </div>
            </div>
            <div v-if="teamAttendance.length === 0" class="rounded-2xl border border-dashed border-slate-200 bg-white p-6 text-center text-sm text-slate-400">
              No attendance data yet.
            </div>
          </div>
        </div>
      </section>

      <section class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
        <h2 class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Upcoming Deadlines</h2>
        <p class="mt-2 text-lg font-black text-slate-900">Next Deliverables</p>
        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
          <div v-for="task in upcomingDeadlines" :key="task.title + task.date" class="rounded-2xl border border-slate-100 bg-white p-4">
            <p class="text-sm font-black text-slate-900">{{ task.title }}</p>
            <div class="mt-2 flex items-center justify-between text-xs font-semibold uppercase tracking-widest text-slate-500">
              <span>{{ task.date }}</span>
              <span class="rounded-full bg-slate-100 px-3 py-1">{{ task.priority }}</span>
            </div>
          </div>
          <div v-if="upcomingDeadlines.length === 0" class="rounded-2xl border border-dashed border-slate-200 bg-white p-6 text-center text-sm text-slate-400">
            No deadlines scheduled.
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
  teamCount: Number,
  team_performance: Object,
  teamAttendance: Array,
  upcomingDeadlines: Array
});

const engagementTrend = computed(() => {
  const raw = props.team_performance?.weekly_engagement || [];
  if (!raw.length) return [];
  const max = Math.max(...raw, 1);
  return raw.map(val => Math.max(5, Math.round((val / max) * 100)));
});

const avgEngagement = computed(() => {
  const raw = props.team_performance?.weekly_engagement || [];
  if (!raw.length) return 0;
  const sum = raw.reduce((acc, val) => acc + val, 0);
  return Math.round(sum / raw.length);
});

const attendancePercent = (count) => {
  const max = Math.max(...(props.teamAttendance || []).map(s => s.count || 0), 1);
  return Math.round((count / max) * 100);
};
</script>
