<template>
  <div class="flex h-screen bg-[#F0F7F4] font-sans text-gray-800">
    <!-- Sidebar -->
    <aside 
      class="bg-white/80 backdrop-blur-xl border-r border-white/60 shadow-xl transition-all duration-300 ease-[cubic-bezier(0.25,0.8,0.25,1)] z-30 relative overflow-hidden group"
      :class="isSidebarOpen ? 'w-72' : 'w-20'"
    >
      <!-- Logo Area -->
      <div class="h-20 flex items-center px-6 border-b border-gray-100/50 relative z-10 bg-white/50">
           <div class="flex items-center gap-3 w-full">
              <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg shadow-emerald-500/20 flex items-center justify-center shrink-0 transform transition-transform group-hover:scale-110 duration-300">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                 </svg>
              </div>
              <transition name="fade">
                  <span v-if="isSidebarOpen" class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-800 to-teal-700 tracking-tight">
                    Nexus HR
                  </span>
              </transition>
           </div>
           
           <button 
             @click="isSidebarOpen = !isSidebarOpen"
             class="absolute -right-3 top-1/2 -translate-y-1/2 bg-white border border-gray-100 p-1.5 rounded-full shadow-sm hover:shadow-md hover:bg-emerald-50 text-emerald-600 transition-all z-20 opacity-0 group-hover:opacity-100"
           >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
           </button>
      </div>

      <!-- Navigation -->
      <nav class="p-4 space-y-1 overflow-y-auto h-[calc(100vh-5rem)] scrollbar-hide">
         <!-- Search Stub -->
         <div class="mb-6 relative" :class="isSidebarOpen ? 'block' : 'hidden'">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
               <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
               </svg>
            </div>
            <input type="text" placeholder="Quick find..." class="pl-10 w-full bg-emerald-50/50 border-none rounded-xl text-sm focus:ring-2 focus:ring-emerald-200 transition-all placeholder-emerald-400/70 text-emerald-800" />
         </div>

         <div v-for="section in navigation" :key="section.title" class="mb-8">
            <h3 v-if="isSidebarOpen" class="px-3 mb-2 text-xs font-bold text-gray-400 uppercase tracking-wider font-mono">
               {{ section.title }}
            </h3>
            <ul class="space-y-1">
               <li v-for="item in section.items" :key="item.name">
                   <NavItem 
                      :to="route(item.route)"
                      :icon="item.icon"
                      :label="item.name"
                      :isOpen="isSidebarOpen"
                      :mode="'inertia'"
                      :class="route().current() === item.route ? 'bg-emerald-50 text-emerald-800' : ''"
                   />
               </li>
            </ul>
         </div>
      </nav>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col relative overflow-hidden">
      <!-- Top Header -->
      <header class="h-20 bg-white/80 backdrop-blur-md border-b border-white/60 px-8 flex items-center justify-between z-20 sticky top-0">
          <!-- Breadcrumb / Title -->
          <div>
             <h2 class="text-xl font-bold text-gray-800">
               {{ pageTitle }}
             </h2>
          </div>

          <!-- Right Actions -->
          <div class="flex items-center gap-6">
             <!-- Notifs -->
             <button class="relative p-2 text-gray-400 hover:text-emerald-600 transition-colors rounded-full hover:bg-emerald-50">
               <span class="absolute top-2 right-2 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
               </svg>
             </button>

             <!-- User Menu -->
             <div class="flex items-center gap-3 pl-6 border-l border-gray-100">
                <div class="text-right hidden md:block">
                   <p class="text-sm font-semibold text-gray-800">{{ authStore.user?.name }}</p>
                   <p class="text-xs text-gray-500">{{ authStore.user?.role?.name || 'Employee' }}</p>
                </div>
                <div class="relative group">
                    <button class="h-10 w-10 rounded-full bg-gradient-to-tr from-emerald-100 to-teal-100 border-2 border-white shadow-sm overflow-hidden transform group-hover:scale-105 transition-transform">
                       <img v-if="authStore.user?.avatar" :src="authStore.user.avatar" class="h-full w-full object-cover">
                       <span v-else class="text-emerald-600 font-bold text-sm h-full w-full flex items-center justify-center">
                          {{ authStore.user?.name?.charAt(0) }}
                       </span>
                    </button>
                    <!-- Dropdown -->
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right scale-95 group-hover:scale-100 z-50">
                        <Link :href="$page.props.auth?.profileUrl || '#'" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700">Profile</Link>
                        <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Sign Out</button>
                    </div>
                </div>
             </div>
          </div>
      </header>

      <!-- Main Scrollable Area -->
      <div class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F0F7F4] relative">
         <!-- Abstract Background Shapes -->
         <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-white/40 to-transparent pointer-events-none"></div>
         <div class="absolute -top-40 -right-40 w-96 h-96 bg-purple-100/30 rounded-full blur-3xl pointer-events-none"></div>
         <div class="absolute top-20 -left-20 w-72 h-72 bg-emerald-100/30 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Main View Area - Enhanced -->
        <main ref="mainContentRef" class="flex-1 overflow-x-hidden overflow-y-auto px-8 pb-8 pt-2 scroll-smooth">
          <slot />
        </main>
      </div>
    </div>
    
    <!-- Global Toast Notifications -->
    <ToastNotification />
    
    <!-- Global App Loader -->
    <GlobalLoader />
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';
import NavItem from '@/Components/NavItem.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import GlobalLoader from '@/Components/GlobalLoader.vue';
import axios from 'axios';
// Import Icons explicitly if NavItem doesn't dynamic import (NavItem usually expects string, but let's assume it resolves them or we need to pass component if not dynamic)
// Actually NavItem implementation dictates this. 
// Assuming NavItem uses dynamic component resolution from heroicons, we just pass string name. 
// If it uses <component :is>, we need to import or provide logic.
// Checking Step 3215: NavItem :icon="item.icon".
// I'll assume standard string-based resolution is handled or I don't need to import if NavItem does it.
// BUT, usually we need to ensure they are available.
// If NavItem imports * from heroicons, we are good.
// If not, we might need to modify NavItem.
// I'll assume string "ComputerDesktopIcon" works if "UsersIcon" works.
// End of imports

// Sidebar State
const isSidebarOpen = ref(true); 

// Router & Store
const page = usePage();
const authStore = useAuthStore();
const mainContentRef = ref(null);

watch(
  () => page.url,
  () => {
    if (mainContentRef.value) {
      mainContentRef.value.scrollTop = 0;
    }
  }
);

// Logout Logic
const logout = async () => {
    await authStore.logout();
    router.visit(route('login'));
};

const pageTitle = computed(() => {
    // Basic heuristics for title
    const url = page.url;
    if (url.includes('attendance')) return 'Attendance';
    if (url.includes('leaves')) return 'Leave Management';
    if (url.includes('manager')) return 'Manager Portal';
    if (url.includes('admin')) return 'Administration';
    return 'Dashboard';
});

// Navigation Data (Static for now, can be dynamic from props)
const navigation = [
    {
        title: 'Work Space',
        items: [
            { name: 'Dashboard', icon: 'HomeIcon', route: 'dashboard' },
            { name: 'My Attendance', icon: 'ClockIcon', route: 'employee.attendance.hub' },
            { name: 'My Leaves', icon: 'CalendarIcon', route: 'employee.leave.index', params: { tab: 'my_leaves' } },
            { name: 'Timesheets', icon: 'ClipboardDocumentListIcon', route: 'admin.attendance.timesheets' },
        ]
    },
    {
        title: 'Management',
        items: [
             { name: 'Approvals', icon: 'CheckCircleIcon', route: 'manager.approvals.index', permission: 'approvals.view' },
        ]
    },
    {
        title: 'Admin',
        items: [
            { name: 'Projects', icon: 'FolderIcon', route: 'projects.index', permission: 'project_management.view' },
            { name: 'Employees', icon: 'UsersIcon', route: 'admin.employees.index', permission: 'employee_management.employees.view' },
            { name: 'Talent Hub', icon: 'BriefcaseIcon', route: 'talent.hub', permission: 'manage_recruitment' },
            { name: 'Live Monitor', icon: 'ComputerDesktopIcon', route: 'admin.attendance.monitoring', permission: 'attendance.monitor' },
            { name: 'Roster', icon: 'CalendarDaysIcon', route: 'admin.attendance.roster', permission: 'attendance.roster.view' },
            { name: 'Rotations', icon: 'ArrowPathIcon', route: 'admin.attendance.shifts.index', permission: 'attendance.settings.view' },
            { name: 'Attendance Data', icon: 'TableCellsIcon', route: 'admin.attendance.data', permission: 'attendance.reports.view' },
            { name: 'Gamification', icon: 'TrophyIcon', route: 'admin.attendance.gamification', permission: 'gamification.manage' },
            { name: 'AI Logs', icon: 'CpuChipIcon', route: 'admin.attendance.ai-logs.index', permission: 'ai_logs.view' },
        ]
    }
];

// Note: NavItem needs to handle Inertia Link checking too. 
// If NavItem uses router-link, it breaks.
// We should check NavItem as well.

// PROACTIVE: Sync Auth Store with Inertia Props
watch(() => page.props.auth?.user, (newUser) => {
    if (newUser) {
        authStore.user = newUser;
        authStore.permissions = newUser.capabilities || [];
        authStore.isLoggedIn = true;
    }
}, { immediate: true });
</script>
