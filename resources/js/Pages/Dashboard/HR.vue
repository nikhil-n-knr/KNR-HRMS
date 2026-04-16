<template>
  <div class="min-h-screen pb-24">
    <div class="space-y-8">
      <header class="rounded-3xl border border-white/60 bg-gradient-to-r from-sky-50 via-white to-emerald-50 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <p class="text-[11px] font-black uppercase tracking-[0.4em] text-emerald-600">HR Insight Hub</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900 tracking-tight">People Intelligence</h1>
            <p class="mt-2 text-sm text-slate-500">Real-time workforce signals powered by your HR data.</p>
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-emerald-50 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-emerald-600">Engagement {{ analytics.engagement_score }}%</span>
            <span class="rounded-full bg-slate-100 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-slate-600">Retention {{ analytics.retention_rate }}%</span>
            <span class="rounded-full bg-slate-100 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-slate-600">Wellness {{ analytics.wellness_score }}</span>
          </div>
        </div>
      </header>

      <section class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
          <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Active Employees</p>
          <p class="mt-3 text-3xl font-black text-slate-900">{{ totalEmployees }}</p>
        </div>
        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
          <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Absent Today</p>
          <p class="mt-3 text-3xl font-black text-slate-900">{{ absentToday }}</p>
        </div>
        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
          <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Pending Leaves</p>
          <p class="mt-3 text-3xl font-black text-slate-900">{{ pendingHRLeaves }}</p>
        </div>
        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
          <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Attrition Risk</p>
          <p class="mt-3 text-3xl font-black text-slate-900">{{ analytics.attrition_risk }}%</p>
        </div>
      </section>

      <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm lg:col-span-2">
          <h2 class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Strategic Intelligence</h2>
          <p class="mt-2 text-lg font-black text-slate-900">Hiring Trend</p>
          <div class="mt-6 grid h-40 grid-cols-12 items-end gap-2">
            <div v-for="(value, idx) in hiringTrend" :key="idx" class="flex items-end">
              <div class="w-2.5 rounded-full bg-emerald-500/80" :style="{ height: value + '%' }"></div>
            </div>
          </div>
          <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div v-for="stage in analytics.hiring_pipeline" :key="stage.stage" class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ stage.stage }}</p>
              <p class="mt-2 text-xl font-black text-slate-900">{{ stage.count }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
          <h2 class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Headcount Mix</h2>
          <p class="mt-2 text-lg font-black text-slate-900">Departments</p>
          <div class="mt-6 space-y-4">
            <div v-for="dept in headcountByDept" :key="dept.name" class="space-y-2">
              <div class="flex items-center justify-between text-xs font-black uppercase tracking-widest text-slate-500">
                <span>{{ dept.name }}</span>
                <span class="text-slate-900">{{ dept.count }}</span>
              </div>
              <div class="h-2 w-full rounded-full bg-slate-100">
                <div class="h-2 rounded-full bg-emerald-500" :style="{ width: headcountPercent(dept.count) + '%' }"></div>
              </div>
            </div>
            <div v-if="headcountByDept.length === 0" class="rounded-2xl border border-dashed border-slate-200 bg-white p-6 text-center text-sm text-slate-400">
              No department data available.
            </div>
          </div>
        </div>
      </section>

      <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
          <h2 class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Culture Pulse</h2>
          <div class="mt-4 space-y-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Culture Growth</p>
              <p class="mt-2 text-2xl font-black text-slate-900">{{ analytics.culture_growth }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Engagement Score</p>
              <p class="mt-2 text-2xl font-black text-slate-900">{{ analytics.engagement_score }}%</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Wellness Score</p>
              <p class="mt-2 text-2xl font-black text-slate-900">{{ analytics.wellness_score }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm lg:col-span-2">
          <h2 class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Upcoming Birthdays</h2>
          <p class="mt-2 text-lg font-black text-slate-900">Celebrate the team</p>
          <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div v-for="person in upcomingBirthdays" :key="person.first_name + person.department.name" class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-sm font-black text-slate-900">{{ person.first_name }}</p>
              <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-slate-400">{{ person.department.name }}</p>
            </div>
            <div v-if="upcomingBirthdays.length === 0" class="rounded-2xl border border-dashed border-slate-200 bg-white p-6 text-center text-sm text-slate-400">
              No upcoming birthdays.
            </div>
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
  totalEmployees: Number,
  headcountByDept: Array,
  absentToday: Number,
  pendingHRLeaves: Number,
  analytics: Object,
  upcomingBirthdays: Array
});

const hiringTrend = computed(() => {
  const raw = props.analytics?.hiring_trend || [];
  if (!raw.length) return [];
  const max = Math.max(...raw, 1);
  return raw.map(val => Math.max(5, Math.round((val / max) * 100)));
});

const headcountPercent = (count) => {
  const max = Math.max(...(props.headcountByDept || []).map(d => d.count || 0), 1);
  return Math.round((count / max) * 100);
};
</script>
