<template>
  <Head title="Admin Dashboard" />
  <div class="bg-[#f4f5fa] pb-16">
    <GradientHeroHeader
      kicker="Administration"
      title="Admin Command Center"
      subtitle="Live operational metrics across tenants, projects, and infrastructure health."
    >
      <template #right>
        <div class="flex flex-wrap items-center gap-3 shrink-0">
          <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[140px] text-center">
            <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Users</p>
            <p class="text-4xl font-extrabold text-white leading-none">{{ totalUsers }}</p>
          </div>
          <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[140px] text-center">
            <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Projects</p>
            <p class="text-4xl font-extrabold text-white leading-none">{{ totalProjects }}</p>
          </div>
          <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[140px] text-center">
            <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Clients</p>
            <p class="text-4xl font-extrabold text-white leading-none">{{ totalClients }}</p>
          </div>
          <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[140px] text-center">
            <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Tenants</p>
            <p class="text-4xl font-extrabold text-white leading-none">{{ activeTenants }}</p>
          </div>
        </div>
      </template>
    </GradientHeroHeader>

    <div class="mx-0 sm:mx-6 mt-5 space-y-8">

      <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">System Health</p>
              <h2 class="mt-2 text-xl font-black text-slate-900">Infrastructure Pulse</h2>
            </div>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-600">Live</span>
          </div>

          <div class="mt-6 grid grid-cols-2 gap-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">CPU Load</p>
              <p class="mt-2 text-2xl font-black text-slate-900">{{ system_health.cpu }}%</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Memory</p>
              <p class="mt-2 text-2xl font-black text-slate-900">{{ system_health.memory }}%</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Storage</p>
              <p class="mt-2 text-2xl font-black text-slate-900">{{ system_health.storage }}%</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Uptime</p>
              <p class="mt-2 text-2xl font-black text-slate-900">{{ system_health.uptime }}</p>
            </div>
          </div>

          <div class="mt-6 rounded-2xl border border-slate-100 bg-white p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Nodes Online</p>
            <p class="mt-2 text-lg font-black text-slate-900">{{ system_health.node_active }} / {{ system_health.node_total }}</p>
            <div class="mt-3 h-2 w-full rounded-full bg-slate-100">
              <div class="h-2 rounded-full bg-emerald-500" :style="{ width: nodeHealthPercent + '%' }"></div>
            </div>
          </div>
        </div>

        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm lg:col-span-2">
          <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
              <p class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Strategic Intelligence</p>
              <h2 class="mt-2 text-xl font-black text-slate-900">Throughput Trend</h2>
              <p class="mt-1 text-sm text-slate-500">Activity signal from recent system traffic.</p>
            </div>
            <div class="flex items-center gap-3 text-[10px] font-black uppercase tracking-widest text-slate-500">
              <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-600">Avg Latency {{ nodeTelemetry.avg_latency }}</span>
              <span class="rounded-full bg-slate-100 px-3 py-1">Uptime {{ nodeTelemetry.uptime }}</span>
            </div>
          </div>

          <div class="mt-6 grid grid-cols-12 gap-2 items-end h-40">
            <div v-for="(value, idx) in loadTrend" :key="idx" class="col-span-1 flex items-end">
              <div class="w-full rounded-full bg-emerald-500/80" :style="{ height: value + '%' }"></div>
            </div>
          </div>

          <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Active Users</p>
              <p class="mt-2 text-xl font-black text-slate-900">{{ totalUsers }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Projects</p>
              <p class="mt-2 text-xl font-black text-slate-900">{{ totalProjects }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Node Count</p>
              <p class="mt-2 text-xl font-black text-slate-900">{{ system_health.node_total }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Integrity</p>
              <p class="mt-2 text-xl font-black text-slate-900">{{ system_health.uptime }}</p>
            </div>
          </div>
        </div>
      </section>

      <section class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
        <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
          <div>
            <h2 class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">OPSCORE</h2>
          </div>
          <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Synced with navigation</p>
        </div>

        <div v-if="modulesLoading" class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-white/70 p-6 text-sm text-slate-400">
          Loading modules...
        </div>

        <div v-else-if="dashboardModules.length === 0" class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-white/70 p-6 text-sm text-slate-400">
          No modules available for this account.
        </div>

        <div v-else class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
          <template v-for="module in dashboardModules" :key="module.id">
            <Link
              v-if="!module.sub_modules.length"
              :href="module.href"
              class="group rounded-3xl border border-slate-200/70 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg"
            >
              <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                  <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-sky-500 text-sm font-black uppercase tracking-widest text-white shadow-md">
                    {{ module.shortName }}
                  </div>
                  <div>
                    <p class="text-xs font-black uppercase tracking-widest text-slate-400">{{ module.group }}</p>
                    <h3 class="mt-1 text-lg font-black text-slate-900">{{ module.name }}</h3>
                  </div>
                </div>
              </div>
            </Link>

            <div
              v-else
              class="group rounded-3xl border border-slate-200/70 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg"
            >
              <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                  <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-sky-500 text-sm font-black uppercase tracking-widest text-white shadow-md">
                    {{ module.shortName }}
                  </div>
                  <div>
                    <p class="text-xs font-black uppercase tracking-widest text-slate-400">{{ module.group }}</p>
                    <h3 class="mt-1 text-lg font-black text-slate-900">{{ module.name }}</h3>
                  </div>
                </div>

                <div class="flex flex-col items-end gap-2">
                  <button
                    type="button"
                    @click.prevent="toggleSubmodules(module.id)"
                    class="rounded-full border border-slate-200 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-slate-600 transition hover:border-emerald-200 hover:text-emerald-700"
                  >
                    {{ expandedModuleId === module.id ? 'Hide submodules' : 'submodules' }}
                  </button>
                </div>
              </div>

              <div v-if="expandedModuleId === module.id" class="mt-4 space-y-3">
                <Link
                  v-for="subModule in module.sub_modules"
                  :key="subModule.id"
                  :href="getSubmoduleHref(subModule)"
                  class="block rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-emerald-200 hover:bg-white"
                >
                  {{ subModule.name }}
                </Link>
              </div>
            </div>
          </template>
        </div>
      </section>

      <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm lg:col-span-2">
          <h3 class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Project Portfolio</h3>
          <p class="mt-2 text-lg font-black text-slate-900">Status Distribution</p>

          <div class="mt-6 space-y-4">
            <div v-for="stat in projectStats" :key="stat.status" class="space-y-2">
              <div class="flex items-center justify-between text-xs font-black uppercase tracking-widest text-slate-500">
                <span>{{ stat.status }}</span>
                <span class="text-slate-900">{{ stat.count }}</span>
              </div>
              <div class="h-2 w-full rounded-full bg-slate-100">
                <div class="h-2 rounded-full bg-slate-900" :style="{ width: projectPercent(stat.count) + '%' }"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="rounded-3xl border border-white/60 bg-white/80 p-6 shadow-sm">
          <h3 class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Tenant Usage</h3>
          <p class="mt-2 text-lg font-black text-slate-900">Active Seats</p>
          <div class="mt-6 space-y-4">
            <div v-for="tenant in tenantUsage" :key="tenant.id" class="rounded-2xl border border-slate-100 bg-white p-4">
              <div class="flex items-center justify-between">
                <p class="text-xs font-black uppercase tracking-widest text-slate-500">{{ tenant.name || 'Tenant' }}</p>
                <span class="text-sm font-black text-slate-900">{{ tenant.users_count }}</span>
              </div>
              <div class="mt-3 h-1.5 w-full rounded-full bg-slate-100">
                <div class="h-1.5 rounded-full bg-emerald-500" :style="{ width: tenantPercent(tenant.users_count) + '%' }"></div>
              </div>
            </div>
            <div v-if="tenantUsage.length === 0" class="rounded-2xl border border-dashed border-slate-200 bg-white p-6 text-center text-sm text-slate-400">
              No tenant usage data available.
            </div>
          </div>
        </div>
       </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { Link, Head } from '@inertiajs/vue3';
import axios from 'axios';
import MainLayout from '@/Layouts/MainLayout.vue';
import GradientHeroHeader from '@/Components/UI/GradientHeroHeader.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
  totalUsers: Number,
  totalProjects: Number,
  totalClients: Number,
  activeTenants: Number,
  tenantUsage: Array,
  projectStats: Array,
  system_health: Object,
  nodeTelemetry: Object
});

const modulesLoading = ref(true);
const sidebarModules = ref([]);
const expandedModuleId = ref(null);

const nodeHealthPercent = computed(() => {
  if (!props.system_health || !props.system_health.node_total) return 0;
  return Math.round((props.system_health.node_active / props.system_health.node_total) * 100);
});

const loadTrend = computed(() => {
  const raw = props.nodeTelemetry?.load_trend || [];
  if (!raw.length) return [];
  const max = Math.max(...raw, 1);
  return raw.map(val => Math.max(5, Math.round((val / max) * 100)));
});

const projectPercent = (count) => {
  const total = props.totalProjects || 1;
  return Math.round((count / total) * 100);
};

const tenantPercent = (count) => {
  const max = Math.max(...(props.tenantUsage || []).map(t => t.users_count || 0), 1);
  return Math.round((count / max) * 100);
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

const getShortName = (name = '') => {
  return name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase() || 'MD';
};

const dashboardModules = computed(() =>
  sidebarModules.value.map((module) => ({
    ...module,
    group: module.sidebar_group || 'Main Menu',
    href: normalizeHref(module.route),
    shortName: getShortName(module.name),
  }))
);

const toggleSubmodules = (moduleId) => {
  expandedModuleId.value = expandedModuleId.value === moduleId ? null : moduleId;
};

const getSubmoduleHref = (subModule) => {
  return normalizeHref(subModule.route || subModule.href || '#');
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
