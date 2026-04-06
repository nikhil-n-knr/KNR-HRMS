<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-800 to-teal-700">
            Organization Configuration
            </h1>
            <p class="text-emerald-600/80 text-sm mt-1">Manage global structural units</p>
        </div>
    </div>

    <div class="border-b border-gray-200">
      <nav class="-mb-px flex space-x-8" aria-label="Tabs">
        <button
          @click="changeTab('departments')"
          :class="[section === 'departments' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all']"
        >
          Departments
        </button>
        <button
          @click="changeTab('locations')"
          :class="[section === 'locations' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all']"
        >
          Locations
        </button>
        <button
          @click="changeTab('tenants')"
          :class="[section === 'tenants' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all']"
        >
          Tenants
        </button>
      </nav>
    </div>

    <!-- Content -->
    <div class="mt-6">
        <component :is="currentComponent" v-bind="$props" />
    </div>

  </div>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    section: {
        type: String,
        default: 'departments'
    },
    departments: Array,
    locations: Array,
    tenants: Array
});

// Using dynamic async components to load tabs only when visited
const components = {
    'departments': defineAsyncComponent(() => import('./DepartmentList.vue')),
    'locations':   defineAsyncComponent(() => import('./LocationList.vue')),
    'tenants':     defineAsyncComponent(() => import('./TenantList.vue'))
};

const currentComponent = computed(() => {
    return components[props.section] || components['departments'];
});

const changeTab = (tab) => {
    router.visit('/admin/departments', { 
        data: { section: tab }, 
        preserveScroll: true,
        preserveState: true,
        only: ['section', 'departments', 'locations', 'tenants']
    });
};
</script>
