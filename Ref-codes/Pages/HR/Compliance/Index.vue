<template>
  <MainLayout>
    <div class="py-2 md:py-5">
      <div class="w-full space-y-4 md:space-y-6 px-2 md:px-4 lg:px-3">

        <!-- Gradient Header Banner -->
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-indigo-700 via-violet-800 to-violet-600 px-4 md:px-6 py-5 md:py-6 text-white shadow-sm border border-white/10">
          <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-10 left-1/3 w-40 h-40 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-20 right-10 w-[24rem] h-[24rem] rounded-full bg-white/10"></div>
          </div>
          <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div>
              <h1 class="text-2xl lg:text-3xl font-bold tracking-tight">Statutory Compliance Hub</h1>
              <p class="text-sm lg:text-base text-slate-200 mt-2 max-w-2xl">
                Manage PF, ESI, PT Checks and Returns
              </p>
            </div>
            <div class="relative z-10 flex flex-wrap items-center gap-3">
              <button class="px-3 md:px-4 py-2 bg-white/10 text-white rounded-xl border border-white/20 hover:bg-white/20 transition-all shadow-sm font-semibold text-sm md:text-base">
                Audit Reports
              </button>
            </div>
          </div>
        </div>

        <!-- Tabs Section -->
    <!-- Tabs Section -->
<div class="sticky top-0 z-30 border-b border-gray-100 bg-white px-2 py-2 shadow-sm">
    
    <nav class="flex items-center gap-2 border-b border-gray-100">

        <button
            v-for="t in tabs"
            :key="t.id"
            @click="setTab(t.id)"
            class="flex items-center gap-2 px-1 py-2 rounded-lg text-sm font-semibold transition-all duration-200 border whitespace-nowrap"
            :class="
                currentTab === t.id
                    ? 'bg-indigo-50 text-indigo-700 border-indigo-200 shadow-sm'
                    : 'bg-white text-gray-500 border-transparent hover:bg-gray-50 hover:text-gray-700'
            "
        >
            <component :is="t.icon" class="w-4 h-4" />
            <span>{{ t.name }}</span>
        </button>

    </nav>

</div>

        <!-- Content Container -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <!-- Tab Content -->
          <div class="p-3 md:p-4 lg:p-6 transition-all duration-300">
             <DashboardTab v-if="currentTab === 'dashboard'" :challans="challans" :insights="insights" />
             <StatutoryHub v-if="currentTab === 'modules'" />
             <MappingTab v-if="currentTab === 'mapping'" />
             <ReturnsTab v-if="currentTab === 'returns'" />
             <RegistersTab v-if="currentTab === 'registers'" />
             <ConfigTab v-if="currentTab === 'config'" :configs="configs" />
          </div>
        </div>

      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import DashboardTab from './Tabs/DashboardTab.vue';
import StatutoryHub from './Tabs/StatutoryHub.vue';
import MappingTab from './Tabs/MappingTab.vue';
import ReturnsTab from './Tabs/ReturnsTab.vue';
import RegistersTab from './Tabs/RegistersTab.vue';
import ConfigTab from './Tabs/ConfigTab.vue';

// Icons
const ChartBarIcon = { template: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>' };
const UsersIcon = { template: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>' };
const DocumentTextIcon = { template: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" /></svg>' };
const ClipboardCheckIcon = { template: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>' };
const CogIcon = { template: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>' };
const ShieldCheckIcon = { template: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>' };

const props = defineProps({
    tab: String,
    challans: Array,
    configs: Object,
    insights: Array
});

const currentTab = ref(props.tab || 'dashboard');

watch(() => props.tab, (newTab) => {
    if (newTab) currentTab.value = newTab;
});

const tabs = [
    { id: 'dashboard', name: 'Dashboard', icon: ChartBarIcon },
    { id: 'modules', name: 'Statutory Modules', icon: ShieldCheckIcon },
    { id: 'mapping', name: 'Compliance Mapping', icon: UsersIcon },
    { id: 'returns', name: 'Monthly Returns (ECR)', icon: DocumentTextIcon },
    { id: 'registers', name: 'Statutory Registers', icon: ClipboardCheckIcon }, // Renamed from Audit
    { id: 'config', name: 'Configuration', icon: CogIcon },
];

const setTab = (id) => {
    currentTab.value = id;
    router.visit(route('hr.compliance.index', { tab: id }), {
        preserveState: true,
        replace: true
    });
};
</script>
