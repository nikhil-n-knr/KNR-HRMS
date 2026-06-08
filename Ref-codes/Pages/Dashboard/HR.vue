<template>
  <Head title="HR Dashboard" />
  <div class="bg-[#f4f5fa] pb-16">
    <GradientHeroHeader
      kicker="Human Resources"
      title="People Intelligence"
      subtitle="Real-time workforce signals powered by your HR data."
    >
      <template #right>
        <div class="flex flex-wrap items-center gap-3">
          <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[150px] text-center">
            <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Engagement</p>
            <p class="text-3xl font-extrabold text-white leading-none">{{ analytics.engagement_score }}%</p>
          </div>
          <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[150px] text-center">
            <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Retention</p>
            <p class="text-3xl font-extrabold text-white leading-none">{{ analytics.retention_rate }}%</p>
          </div>
          <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[150px] text-center">
            <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Wellness</p>
            <p class="text-3xl font-extrabold text-white leading-none">{{ analytics.wellness_score }}</p>
          </div>
        </div>
      </template>
    </GradientHeroHeader>

    <div class="mx-0 sm:mx-6 mt-5 space-y-8">

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
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import GradientHeroHeader from '@/Components/UI/GradientHeroHeader.vue';

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
