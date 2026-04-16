<template>
  <div class="min-h-screen pb-24">
    <div class="space-y-8">
      <header class="rounded-3xl border border-white/60 bg-gradient-to-r from-indigo-50 via-white to-sky-50 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <p class="text-[11px] font-black uppercase tracking-[0.4em] text-indigo-600">Employee Mission Hub</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900 tracking-tight">Welcome, {{ firstName }}</h1>
            <p class="mt-2 text-sm text-slate-500">Your personal command center for attendance, tasks, and growth.</p>
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-indigo-50 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-indigo-600">{{ user.designation || 'Employee' }}</span>
            <span class="rounded-full bg-slate-100 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-slate-600">Modules {{ dashboardModules.length }}</span>
          </div>
        </div>
      </header>

      <section class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
          <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Leave Balance</p>
          <p class="mt-3 text-3xl font-black text-slate-900">{{ quickStats.leave_balance }}</p>
          <p class="mt-2 text-xs font-semibold uppercase tracking-widest text-emerald-500">Days Available</p>
        </div>
        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
          <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Attendance Streak</p>
          <p class="mt-3 text-3xl font-black text-slate-900">{{ quickStats.attendance_streak }}</p>
          <p class="mt-2 text-xs font-semibold uppercase tracking-widest text-indigo-500">Days Active</p>
        </div>
        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
          <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Pending Tasks</p>
          <p class="mt-3 text-3xl font-black text-slate-900">{{ quickStats.pending_tasks }}</p>
          <p class="mt-2 text-xs font-semibold uppercase tracking-widest text-slate-500">Open Items</p>
        </div>
      </section>

      <section class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
        <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
          <div>
            <h2 class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Modules</h2>
            <p class="mt-2 text-lg font-black text-slate-900">Everything currently visible</p>
          </div>
          <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Live sync</p>
        </div>

        <div v-if="modulesLoading" class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-white/70 p-6 text-sm text-slate-400">
          Loading modules...
        </div>

        <div v-else-if="dashboardModules.length === 0" class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-white/70 p-6 text-sm text-slate-400">
          No modules available for this account.
        </div>

        <div v-else class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
          <Link
            v-for="module in dashboardModules"
            :key="module.id"
            :href="module.href"
            class="group rounded-3xl border border-slate-200/70 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg"
          >
            <div class="flex items-start justify-between gap-4">
              <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-sky-500 text-sm font-black uppercase tracking-widest text-white shadow-md">
                  {{ module.shortName }}
                </div>
                <div>
                  <p class="text-xs font-black uppercase tracking-widest text-slate-400">{{ module.group }}</p>
                  <h3 class="mt-1 text-lg font-black text-slate-900">{{ module.name }}</h3>
                </div>
              </div>
              <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-slate-500">
                {{ module.sub_modules.length }} Links
              </span>
            </div>

            <p class="mt-4 text-sm text-slate-500">
              {{ module.sub_modules.length ? 'Open this module or jump straight into one of its linked sections.' : 'Open this module from the dashboard.' }}
            </p>

            <div v-if="module.sub_modules.length" class="mt-4 flex flex-wrap gap-2">
              <span
                v-for="subModule in module.sub_modules"
                :key="subModule.id"
                class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-slate-600"
              >
                {{ subModule.name }}
              </span>
            </div>
          </Link>
        </div>
      </section>

      <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm lg:col-span-2">
          <h2 class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Strategic Intelligence</h2>
          <p class="mt-2 text-lg font-black text-slate-900">Your Focus Areas</p>
          <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-xs font-black uppercase tracking-widest text-slate-400">Priorities</p>
              <p class="mt-2 text-sm text-slate-600">Stay consistent on attendance, close tasks on time, and plan your leaves for balance.</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-xs font-black uppercase tracking-widest text-slate-400">Next Actions</p>
              <ul class="mt-2 space-y-2 text-sm text-slate-600">
                <li>Review your pending tasks and deadlines.</li>
                <li>Check attendance logs for the week.</li>
                <li>Apply for leave if balance is high.</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
          <h2 class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Module Snapshot</h2>
          <p class="mt-2 text-lg font-black text-slate-900">Your Toolkit</p>
          <div class="mt-4 flex flex-wrap gap-2">
            <span v-for="mod in enabledModules" :key="mod" class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-slate-600">
              {{ formatModule(mod) }}
            </span>
            <span v-if="enabledModules.length === 0" class="text-sm text-slate-400">No modules assigned yet.</span>
          </div>
        </div>
      </section>

      <section class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
        <h2 class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Quick Actions</h2>
        <p class="mt-2 text-lg font-black text-slate-900">Move fast</p>
        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
          <div class="rounded-2xl border border-slate-100 bg-white p-4">
            <p class="text-xs font-black uppercase tracking-widest text-slate-400">Attendance</p>
            <p class="mt-2 text-sm text-slate-600">Check in, review shifts, and track logs.</p>
          </div>
          <div class="rounded-2xl border border-slate-100 bg-white p-4">
            <p class="text-xs font-black uppercase tracking-widest text-slate-400">Leaves</p>
            <p class="mt-2 text-sm text-slate-600">Plan your time off and review balances.</p>
          </div>
          <div class="rounded-2xl border border-slate-100 bg-white p-4">
            <p class="text-xs font-black uppercase tracking-widest text-slate-400">Tasks</p>
            <p class="mt-2 text-sm text-slate-600">Focus on priority items and deadlines.</p>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import axios from 'axios';

defineOptions({ layout: MainLayout });

const props = defineProps({
  user: Object,
  enabledModules: Array,
  quickStats: Object
});

const modulesLoading = ref(true);
const sidebarModules = ref([]);

const firstName = computed(() => (props.user?.name || 'User').split(' ')[0]);

const dashboardModules = computed(() =>
  sidebarModules.value.map((module) => ({
    ...module,
    group: module.sidebar_group || 'Main Menu',
    href: normalizeHref(module.route),
    shortName: getShortName(module.name),
  }))
);

const formatModule = (key) => {
  return key.replace(/_/g, ' ');
};

const getShortName = (name = '') => {
  return name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase() || 'MD';
};

const normalizeHref = (href) => {
  if (!href || href === '#') return '#';

  try {
    const url = new URL(href, window.location.origin);
    return `${url.pathname}${url.search}${url.hash}`;
  } catch (error) {
    return href;
  }
};

onMounted(async () => {
  try {
    const response = await axios.get('/api/navigation');
    sidebarModules.value = response.data?.data?.menu || response.data?.menu || [];
  } catch (error) {
    console.error('Failed to load dashboard modules', error);
    sidebarModules.value = [];
  } finally {
    modulesLoading.value = false;
  }
});
</script>
