<template>
  <div class="min-h-full bg-slate-50 px-3 py-3 sm:px-5 lg:px-6">
    <div class="mx-auto w-full max-w-7xl space-y-4">
      <!-- Purple Module Header -->
      <section
        class="relative overflow-hidden rounded-[20px] bg-gradient-to-r from-indigo-700 via-violet-800 to-violet-600 px-5 py-5 shadow-xl shadow-violet-900/10 sm:px-8"
      >
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
          <div class="absolute -top-12 left-1/3 h-44 w-44 rounded-full bg-white/5"></div>
          <div class="absolute -bottom-28 right-4 h-[24rem] w-[24rem] rounded-full bg-white/10"></div>
          <div class="absolute -right-10 top-0 h-full w-80 bg-white/5 skew-x-[-18deg]"></div>
        </div>

        <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
          <div class="flex min-w-0 items-center gap-4">
            <div
              class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white/15 text-white ring-1 ring-white/20 backdrop-blur"
            >
              <BuildingOffice2Icon class="h-8 w-8" />
            </div>

            <div class="min-w-0">
              <p class="text-[11px] font-black uppercase tracking-[0.28em] text-violet-100/90">
                HR Module
              </p>
              <h1 class="mt-1 text-2xl font-black tracking-tight text-white sm:text-3xl">
                Organization Management
              </h1>
              <p class="mt-1.5 max-w-2xl text-sm font-semibold leading-6 text-violet-100/85">
                Manage departments, locations, and tenant structure from one
                organization configuration hub.
              </p>
            </div>
          </div>

          <div class="grid w-full grid-cols-3 gap-3 sm:w-auto sm:min-w-[380px]">
            <div
              v-for="metric in summaryMetrics"
              :key="metric.label"
              class="rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-center text-white backdrop-blur"
            >
              <p class="text-2xl font-black tabular-nums">
                {{ metric.value }}
              </p>
              <p class="mt-1 text-[10px] font-black uppercase tracking-widest text-violet-100/80">
                {{ metric.label }}
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- Top Tabs -->
      <div class="sticky top-0 z-30 rounded-2xl border border-slate-100 bg-white px-3 py-2 shadow-sm">
        <nav class="flex flex-wrap items-center gap-3 overflow-visible" aria-label="Organization sections">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            type="button"
            @click="changeTab(tab.id)"
            :class="[
              section === tab.id
                ? 'bg-violet-50 text-violet-700 shadow-sm'
                : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900',
              'relative flex h-11 items-center gap-2 rounded-xl px-4 text-sm font-black transition-all',
            ]"
          >
            <component :is="tab.icon" class="h-4 w-4" />
            <span>{{ tab.label }}</span>
            <span
              v-if="section === tab.id"
              class="absolute inset-x-4 -bottom-2 h-1 rounded-full bg-violet-600"
            ></span>
          </button>
        </nav>
      </div>

      <!-- Content -->
      <section
        class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm shadow-slate-900/5"
      >
        <div class="border-b border-slate-100 bg-violet-50/60 px-5 py-4 sm:px-6">
          <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <p class="text-[10px] font-black uppercase tracking-[0.28em] text-violet-600">
                {{ activeTab.eyebrow }}
              </p>
              <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                {{ activeTab.heading }}
              </h2>
            </div>
            <p class="max-w-xl text-sm font-semibold text-slate-500">
              {{ activeTab.description }}
            </p>
          </div>
        </div>

        <div class="organization-table-shell p-4 sm:p-6">
          <component :is="currentComponent" v-bind="$props" />
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, defineAsyncComponent } from "vue";
import { router } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
import {
  BuildingOffice2Icon,
  MapPinIcon,
  Squares2X2Icon,
  UsersIcon,
} from "@heroicons/vue/24/outline";

defineOptions({ layout: MainLayout });

const props = defineProps({
  section: {
    type: String,
    default: "departments",
  },
  departments: {
    type: Array,
    default: () => [],
  },
  locations: {
    type: Array,
    default: () => [],
  },
  tenants: {
    type: Array,
    default: () => [],
  },
});

const tabs = [
  {
    id: "departments",
    label: "Departments",
    heading: "Department Directory",
    eyebrow: "Workforce Structure",
    description: "Create and maintain the teams used across employee records and HR workflows.",
    icon: UsersIcon,
  },
  {
    id: "locations",
    label: "Locations",
    heading: "Location Directory",
    eyebrow: "Workplace Structure",
    description: "Manage office, branch, and operating locations connected to your organization.",
    icon: MapPinIcon,
  },
  {
    id: "tenants",
    label: "Tenants",
    heading: "Tenant Directory",
    eyebrow: "Entity Structure",
    description: "Control tenant-level organization details used across this HRMS environment.",
    icon: Squares2X2Icon,
  },
];

const components = {
  departments: defineAsyncComponent(() => import("./DepartmentList.vue")),
  locations: defineAsyncComponent(() => import("./LocationList.vue")),
  tenants: defineAsyncComponent(() => import("./TenantList.vue")),
};

const activeTab = computed(() => {
  return tabs.find((tab) => tab.id === props.section) || tabs[0];
});

const currentComponent = computed(() => {
  return components[props.section] || components.departments;
});

const summaryMetrics = computed(() => [
  {
    label: "Departments",
    value: props.departments?.length || 0,
  },
  {
    label: "Locations",
    value: props.locations?.length || 0,
  },
  {
    label: "Tenants",
    value: props.tenants?.length || 0,
  },
]);

const changeTab = (tab) => {
  router.visit("/admin/departments", {
    data: { section: tab },
    preserveScroll: true,
    preserveState: true,
    only: ["section", "departments", "locations", "tenants"],
  });
};
</script>

<style scoped>
.organization-table-shell :deep(.hidden.md\:block.overflow-x-auto) {
  max-height: 430px;
  overflow-y: auto;
  border: 1px solid rgb(241 245 249);
  border-radius: 1rem;
}

.organization-table-shell :deep(thead) {
  top: 0;
  background: rgb(245 243 255 / 0.98);
}

.organization-table-shell :deep(thead th) {
  background: rgb(245 243 255 / 0.98);
  color: rgb(76 29 149);
}

.organization-table-shell :deep(tbody tr:hover) {
  background: rgb(245 243 255 / 0.45);
}
</style>
