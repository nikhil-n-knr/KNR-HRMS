<template>
  <MainLayout>
    <Head title="LMS Admin Hub" />

    <div class="h-[calc(100vh-80px)] bg-[#F0F7F4]/30 flex font-sans text-slate-900 overflow-hidden rounded-[2rem] border border-white/40 shadow-2xl relative">
      <!-- Vanta-like ambient glow (internal) -->
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.05),transparent_50%)] pointer-events-none"></div>

      <!-- Left Dock: Strategic Command -->
      <Sidebar 
        :current-section="activeSection" 
        @navigate="activeSection = $event"
      />

      <!-- Main Command Deck -->
      <div class="flex-1 flex flex-col min-w-0 bg-white/40 backdrop-blur-md relative z-10">
        <!-- Intelligent Header -->
        <Header 
          :title="activeModuleLabel"
          :active-section="activeSection"
          :active-tab="activeTab"
          :tabs="currentTabs"
          :global-kpis="hubStats.global_kpis"
          @tab-change="handleTabChange"
        />

        <!-- High-Control Action Strip -->
        <LmsActionStrip 
          v-if="activeSection !== 'config'"
          :role="userRole"
          @action="handleGlobalAction"
        />

        <!-- Dynamic Content Engine -->
        <main class="flex-1 overflow-y-auto p-8 scroll-smooth custom-scrollbar">
          <transition 
            name="fade-slide" 
            mode="out-in"
          >
            <component 
              :is="renderedSection" 
              v-bind="sectionProps"
              @action="handleSectionAction"
            />
          </transition>
        </main>

        <!-- Command Footer Bar -->
        <footer class="h-16 border-t border-emerald-100/50 flex items-center justify-between px-8 bg-white/60 backdrop-blur-xl">
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-[10px] font-black text-emerald-800 uppercase tracking-widest">Core Status: Optimal</span>
                </div>
                <div class="h-4 w-px bg-emerald-100"></div>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest italic">Knowledge is the Anti-Gravity of HRMS</p>
            </div>
            
            <div class="flex items-center gap-3">
                 <button class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                    Generate Audit
                 </button>
                 <button class="p-2 bg-slate-50 text-slate-400 rounded-xl hover:text-emerald-600 transition-colors">
                    <i class="fas fa-expand text-xs"></i>
                 </button>
            </div>
        </footer>
      </div>

      <!-- Right Wing: Institutional Intelligence (Quick View) -->
      <aside v-if="showIntelligence" class="w-80 border-l border-emerald-100/50 bg-white/40 backdrop-blur-md p-6 overflow-y-auto hidden xl:block">
           <div class="space-y-8">
               <div>
                   <h4 class="text-[10px] font-black text-emerald-800 uppercase tracking-[0.2em] mb-4">Neural Activity</h4>
                   <div class="space-y-4">
                       <div v-for="i in 3" :key="i" class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0 text-emerald-600">
                                <i class="fas fa-bolt text-[10px]"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold text-slate-800 leading-tight">New Course Published: "Advanced HR Compliance"</p>
                                <p class="text-[9px] text-slate-400 mt-0.5">2 mins ago</p>
                            </div>
                       </div>
                   </div>
               </div>

               <div class="bg-gradient-to-br from-emerald-600 to-teal-700 p-6 rounded-3xl text-white shadow-xl shadow-emerald-200">
                   <h4 class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-4">Storage Quota</h4>
                   <p class="text-2xl font-black mb-1">84.2 GB</p>
                   <p class="text-[10px] opacity-70 mb-4">CDN Utilization: 42%</p>
                   <div class="h-1 bg-white/20 rounded-full overflow-hidden">
                       <div class="h-full bg-white w-[42%]"></div>
                   </div>
               </div>
           </div>
      </aside>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, defineAsyncComponent, onMounted } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import MainLayout from '../../../Layouts/MainLayout.vue';
import Sidebar from './Components/Sidebar.vue';
import Header from './Components/Header.vue';
import LmsActionStrip from './Components/LmsActionStrip.vue';

const props = defineProps({
    hubStats: Object,
    initialSection: { type: String, default: 'dashboard' },
    initialTab: { type: String, default: 'overview' },
    courses: Array,
    learners: Array,
    institutions: Array,
    plans: Array
});

const activeSection = ref(props.initialSection);
const activeTab = ref(props.initialTab);
const showIntelligence = ref(true);

const tabRegistry = {
    'dashboard': [
        { id: 'overview', label: 'Executive Overview' },
        { id: 'insights', label: 'Strategic Insights' },
        { id: 'health', label: 'Cloud Health' }
    ],
    'analytics': [
        { id: 'realtime', label: 'Live Telemetry' },
        { id: 'mastery', label: 'Mastery Matrix' },
        { id: 'revenue', label: 'Revenue Diagnostic' }
    ],
    'courses': [
        { id: 'manager', label: 'Syllabus Registry' },
        { id: 'programs', label: 'Programs & Tracks' },
        { id: 'architect', label: 'Advanced Builder' },
        { id: 'bulk', label: 'Mass-Update Engine' }
    ],
    'enrollment': [
        { id: 'batches', label: 'Cohort Registry' },
        { id: 'mass', label: 'Mass Enrollment' },
        { id: 'erp-link', label: 'ERP Bridge' }
    ],
    'certificates': [
        { id: 'ledger', label: 'Issued Ledger' },
        { id: 'designer', label: 'Visual Forge' },
        { id: 'verify', label: 'Verification Protocol' }
    ],
    'erp': [
        { id: 'sync', label: 'Synchronicity' },
        { id: 'logs', label: 'Audit Stream' },
        { id: 'gateway', label: 'Bridge Config' }
    ],
    'config': [
        { id: 'profile', label: 'Meta-Identity' },
        { id: 'logic', label: 'Logic Thresholds' },
        { id: 'roles', label: 'Access Control' }
    ]
};

const currentTabs = computed(() => tabRegistry[activeSection.value] || []);

const activeModuleLabel = computed(() => {
    const modules = {
        'dashboard': 'Learning Hub',
        'analytics': 'Command Center',
        'courses': 'Course Builder',
        'enrollment': 'Enrollment & Batches',
        'certificates': 'Certificates Wallet',
        'erp': 'ERP Integration',
        'config': 'System Profile',
        'bulk-manager': 'Structural Matrix'
    };
    return modules[activeSection.value] || 'LMS Module';
});

// High-Res Section Map
const sectionComponents = {
    'dashboard':    defineAsyncComponent(() => import('./Sections/Dashboard/Overview.vue')),
    'analytics':    defineAsyncComponent(() => import('./Sections/Dashboard/RealtimeAnalytics.vue')),
    'courses':      defineAsyncComponent(() => import('./Sections/Courses/AdvancedBuilder.vue')),
    'enrollment':   defineAsyncComponent(() => import('./Sections/Enrollment/BatchManager.vue')),
    'certificates': defineAsyncComponent(() => import('./Sections/Learners/Certificates.vue')),
    'erp':          defineAsyncComponent(() => import('./Sections/Erp/SyncStatus.vue')),
    'config':       defineAsyncComponent(() => import('./Sections/Config/LmsProfile.vue')),
    'bulk-manager': defineAsyncComponent(() => import('./Sections/Courses/BulkManager.vue')),
};

const renderedSection = computed(() => sectionComponents[activeSection.value] || sectionComponents['dashboard']);

const sectionProps = computed(() => {
    return {
        stats: props.hubStats,
        activeTab: activeTab.value,
        courses: props.courses,
        learners: props.learners,
        institutions: props.institutions,
        plans: props.plans
    };
});

const userRole = ref('State Admin');

const handleGlobalAction = (actionId) => {
    console.log('Global Action Triggered:', actionId);
    if (actionId === 'create-course') {
        router.visit(route('lms.admin.courses.create'));
    } else if (actionId === 'create-program') {
        activeSection.value = 'courses';
        activeTab.value = 'programs';
    } else if (actionId === 'mass-enroll') {
        activeSection.value = 'enrollment';
        activeTab.value = 'mass';
    } else if (actionId === 'certificates') {
        activeSection.value = 'certificates';
        activeTab.value = 'designer'; // Go straight to visually forge certificates
    }
};

const handleSectionAction = (event) => {
    if (event.type === 'navigate') {
        activeSection.value = event.id;
        // Reset tab to first one of new section
        activeTab.value = tabRegistry[event.id]?.[0]?.id || 'overview';
    }
    console.log('Hub Action:', event);
};

const handleTabChange = (tabId) => {
    activeTab.value = tabId;
    console.log('Tab Changed To:', tabId);
};

onMounted(() => {
    console.log('LMS Hub Initialized - Strategic View Activated');
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');

.font-sans {
    font-family: 'Outfit', sans-serif;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(16, 185, 129, 0.1);
    border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(16, 185, 129, 0.3);
}

.fade-slide-enter-active, .fade-slide-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.fade-slide-enter-from {
    opacity: 0;
    transform: translateY(20px) scale(0.98);
}
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-20px) scale(1.02);
}
</style>
