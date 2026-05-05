<template>
  <div class="relative h-screen overflow-hidden font-sans text-[color:var(--text-primary)]">
    <!-- Vanta Background Container -->
    <div id="vanta-bg" ref="vantaRef"></div>

    <div class="flex h-full relative z-10">
      <!-- Glass Sidebar -->
      <aside 
        v-if="!isGuestRoute"
        :class="[
          'transition-all duration-300 flex flex-col fixed lg:sticky top-0 h-screen z-[60] lg:z-30 backdrop-blur-md border-r border-white/50 shadow-[4px_0_24px_rgba(0,0,0,0.02)]',
          isSidebarOpen ? 'w-[280px] translate-x-0' : 'w-[280px] -translate-x-full lg:translate-x-0 lg:w-[80px]'
        ]"
        :style="{ background: 'var(--sidebar-bg)', color: 'var(--sidebar-text)' }"
      >
        <!-- Logo Area & Mobile Controls -->
        <div class="h-20 flex items-center justify-between px-6 lg:px-8 border-b border-white/40 sticky top-0 z-10 bg-[var(--sidebar-bg)]">
           <Link :href="route('dashboard')" class="flex w-full items-center justify-center">
             <div class="flex flex-col items-center text-center">
               <img 
                 src="https://knrint-website.blr1.digitaloceanspaces.com/KNR-WEBSITE/2026/site_logo/KNR-WEBSITE_f817360c-0c15-4992-bc1b-4df24f071612_KNR-Logo.png"
                 alt="KNR Logo"
                 class="h-8 w-auto sm:h-9 lg:h-10 object-contain"
               >
               <span v-show="isSidebarOpen" class="mt-1 text-[11px] sm:text-[12px] font-black tracking-tight text-emerald-800">
                 OPSCORE
               </span>
             </div>
           </Link>

           <!-- Sidebar Internal Close (Mobile) -->
           <button @click="isSidebarOpen = false" class="lg:hidden w-8 h-8 flex items-center justify-center rounded-lg bg-slate-50 text-slate-400 active:scale-90 transition-all">
              <i class="fas fa-chevron-left text-[12px]"></i>
           </button>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-6 sidebar-scroll">
          
          <div v-for="(groupMods, groupName) in groupedModules" :key="groupName">
              <p v-show="isSidebarOpen" class="px-3 text-xs font-bold text-emerald-800/50 uppercase tracking-wider mb-2">{{ groupName }}</p>
              
              <div class="space-y-1">
                 <!-- Dynamic Modules -->
                 <template v-for="mod in groupMods" :key="mod.id">
                    <!-- Render as Group if has submodules -->
                    <div v-if="shouldShowModule(mod) && mod.sub_modules && mod.sub_modules.length" class="space-y-1">
                        <!-- Parent Item -->
                        <button 
                          @click="toggleModule(mod.key)"
                          class="flex items-center justify-between w-full px-4 py-3 text-left rounded-xl transition-all duration-200 group relative overflow-hidden text-slate-600 hover:bg-white/50 hover:text-emerald-700 hover:shadow-sm"
                          :class="expandedModules[mod.key] ? 'bg-white/50 text-emerald-800' : ''"
                        >
                           <div class="flex items-center gap-3">
                               <!-- Dynamic Icon -->
                               <component :is="getIconComponent(mod.icon)" v-if="mod.icon && !mod.icon.includes('/')" class="h-5 w-5" />
                               <span v-else-if="mod.icon === 'monitor'" class="material-symbols-outlined text-[20px]">desktop_windows</span>
                               <span v-else-if="mod.icon === 'archive'" class="material-symbols-outlined text-[20px]">archive</span> <!-- Material Symbols fallback -->
                               <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                               </svg>

                               <span v-if="isSidebarOpen" class="font-medium tracking-wide text-sm">{{ mod.name }}</span>
                           </div>
                           <svg 
                              v-if="isSidebarOpen"
                              xmlns="http://www.w3.org/2000/svg" 
                              class="h-4 w-4 transition-transform duration-200"
                              :class="expandedModules[mod.key] ? 'rotate-180' : ''"
                              fill="none" viewBox="0 0 24 24" stroke="currentColor"
                           >
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                           </svg>
                        </button>

                        <!-- Sub Items -->
                        <div v-if="expandedModules[mod.key] && isSidebarOpen" class="pl-4 space-y-1 animate-fade-in-down">
                           <NavItem 
                              v-for="sub in mod.sub_modules"
                              :key="sub.id"
                              :to="resolveRoute(sub.route) || getRouteForSubModule(mod.key, sub.key)" 
                              :icon="sub.icon || 'MinusSmallIcon'" 
                              :label="sub.name" 
                              :isOpen="isSidebarOpen" 
                              class="text-sm py-2 px-3"
                            />
                        </div>
                    </div>

                    <!-- Fallback to Single Item -->
                    <NavItem 
                      v-else-if="shouldShowModule(mod)"
                      :to="mod.route || getRouteForModule(mod.key)" 
                      :icon="mod.icon || 'CubeIcon'" 
                      :label="mod.name" 
                      :isOpen="isSidebarOpen" 
                    />
                 </template>
              </div>
          </div>

          <!-- Static Preferences -->
          <div class="pt-4 border-t border-white/40">
            <p v-show="isSidebarOpen" class="px-3 text-xs font-bold text-emerald-800/50 uppercase tracking-wider mb-2">Preferences</p>
            <NavItem
              :to="themeSettingsUrl"
              icon="PaintBrushIcon"
              label="Theme Settings"
              :isOpen="isSidebarOpen"
            />
          </div>
        </nav>

        <!-- Session Controls (Bottom Anchor) -->
        <div class="p-2 lg:p-4 border-t border-white/40 bg-white/30 backdrop-blur-sm mx-2 lg:mx-4 mb-4 rounded-2xl flex justify-center">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 p-0.5 shadow-lg">
               <div class="h-full w-full rounded-[10px] bg-white flex items-center justify-center text-emerald-700 font-black text-[10px]">
                 {{ getInitials(authStore.user?.name) }}
               </div>
            </div>
            <div v-show="isSidebarOpen" class="flex-1 min-w-0 transition-opacity duration-300">
               <p class="text-[10px] font-black text-slate-800 uppercase tracking-tighter truncate leading-none">{{ authStore.user?.name || 'User' }}</p>
               <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest truncate mt-1 leading-none">{{ authStore.user?.email || 'email@example.com' }}</p>
            </div>
            <button v-show="isSidebarOpen" @click="logout" class="w-9 h-9 flex items-center justify-center rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all shadow-sm active:scale-95 border border-rose-100/50">
               <i class="fas fa-power-off text-[14px]"></i>
            </button>
          </div>
        </div>

      </aside>

      <!-- Mobile Overlay -->
      <div 
        v-if="isSidebarOpen && !isGuestRoute" 
        @click="isSidebarOpen = false" 
        class="fixed inset-0 bg-emerald-900/20 backdrop-blur-sm z-20 lg:hidden"
      ></div>

      <!-- Main Content Block -->
      <div class="flex-1 flex flex-col min-w-0 h-full relative">
        <!-- Unified Premium Header (Dynamic Context) -->
        <header 
          v-if="!isGuestRoute" 
          class="h-14 lg:h-20 flex items-center justify-between px-4 lg:px-8 flex-shrink-0 z-50 transition-all duration-300 border-b border-white/50 shadow-sm"
          :class="isNavbarSticky ? 'sticky top-0' : 'relative'"
          :style="{ background: 'var(--header-bg)', color: 'var(--header-text)' }"
        >
          <div class="flex items-center gap-3">
            <!-- Sidebar Trigger: Mobile Primary -->
            <button 
              @click="isSidebarOpen = !isSidebarOpen" 
              class="w-10 h-10 lg:w-11 lg:h-11 flex items-center justify-center rounded-xl bg-emerald-600 lg:bg-white/50 text-white lg:text-emerald-800 shadow-lg lg:shadow-sm shadow-emerald-500/20 ring-1 ring-white/60 active:scale-95 transition-all hover:bg-emerald-700 lg:hover:bg-white group"
            >
              <i class="fas fa-bars-staggered text-[14px] lg:text-[16px] group-hover:rotate-12 transition-transform"></i>
            </button>

            <!-- Home Button -->
            <Link 
              :href="route('dashboard')" 
              class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/70 border border-white/60 text-emerald-800 shadow-sm hover:bg-white hover:text-emerald-700 transition-all active:scale-95"
            >
              <i class="fas fa-house text-[12px]"></i>
              <span class="hidden lg:inline text-[11px] font-black uppercase tracking-widest">Home</span>
            </Link>
            
            <!-- Mobile Identity Core removed -->
          </div>

          <!-- Search / Breadcrumbs Area (Desktop only) -->
          <div class="hidden lg:flex items-center gap-4 flex-1 max-w-xl">
             <div class="relative w-full group">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-emerald-600/50 group-focus-within:text-emerald-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                </span>
                <input 
                  type="text" 
                  class="block w-full pl-10 pr-3 py-2.5 border-none rounded-xl leading-5 bg-white/40 text-emerald-900 placeholder-emerald-700/40 focus:outline-none focus:bg-white/80 focus:ring-2 focus:ring-emerald-400/30 transition-all backdrop-blur-sm shadow-sm hover:bg-white/60 sm:text-sm" 
                  placeholder="Search..."
                >
             </div>
          </div>

          <!-- Top Actions (Synced with Identity) -->
          <div class="flex items-center gap-2 lg:gap-4 ml-2 lg:ml-6">
            <div id="header-context"></div>
            <CheckInButton v-if="!isGuestRoute" />
            <slot name="header" />
            


            <!-- Notification Pulse -->
            <Link :href="route('notifications.index')" class="relative p-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-500 hover:text-emerald-600 transition-all shadow-sm border border-slate-200/60 group flex items-center active:scale-95">
               <i class="fas fa-bell text-[14px] group-hover:rotate-12 transition-transform"></i>
               <span v-if="page.props.auth?.unreadNotificationsCount > 0" class="absolute -top-1 -right-1 w-4 h-4 bg-rose-500 text-white text-[8px] font-black rounded-full flex items-center justify-center border-2 border-white animate-pulse">
                   {{ page.props.auth.unreadNotificationsCount }}
               </span>
            </Link>
            <!-- Profiler Anchor -->
            <div class="relative">
                <!-- Desktop Dropdown Toggle -->
                <button type="button" @click="showMobileProfileMenu = true" class="hidden lg:flex group items-center gap-2 p-1 pl-1 bg-white border border-slate-200 rounded-full shadow-sm hover:border-emerald-200 transition-all active:scale-95">
                    <div class="h-8 w-8 rounded-full bg-gradient-to-tr from-emerald-500 to-teal-400 p-0.5 shadow-md">
                        <div class="h-full w-full rounded-full bg-white flex items-center justify-center text-teal-700 font-black text-[10px]">
                          {{ getInitials(authStore.user?.name) }}
                        </div>
                    </div>
                    <div class="pr-3">
                        <span class="text-[10px] font-black text-slate-800 uppercase tracking-tighter">{{ authStore.user?.name?.split(' ')[0] }}</span>
                    </div>
                </button>

                <!-- Mobile Dropdown Toggle -->
                <button type="button" @click="showMobileProfileMenu = true" class="flex lg:hidden group items-center gap-2 p-1 pl-1 bg-white border border-slate-200 rounded-full shadow-sm hover:border-emerald-200 transition-all active:scale-95">
                    <div class="h-8 w-8 rounded-full bg-gradient-to-tr from-emerald-500 to-teal-400 p-0.5 shadow-md">
                        <div class="h-full w-full rounded-full bg-white flex items-center justify-center text-teal-700 font-black text-[10px]">
                          {{ getInitials(authStore.user?.name) }}
                        </div>
                    </div>
                </button>

                <!-- Universal Dropdown Menu (Fixed Overlay to prevent clipping) -->
                <div v-if="showMobileProfileMenu" class="fixed inset-0 z-[100]">
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-slate-900/10 backdrop-blur-[2px]" @click="showMobileProfileMenu = false"></div>
                    
                    <!-- Menu Panel -->
                    <div class="absolute top-16 right-4 lg:right-8 w-64 bg-white border border-slate-200 shadow-2xl rounded-2xl overflow-hidden origin-top-right transition-all animate-fade-in-down">
                        <div class="p-4 bg-slate-50/80 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-emerald-500 to-teal-400 p-0.5 shadow-md">
                                    <div class="h-full w-full rounded-full bg-white flex items-center justify-center text-teal-700 font-black text-xs">
                                        {{ getInitials(authStore.user?.name) }}
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[11px] font-black text-slate-800 uppercase tracking-tighter truncate">{{ authStore.user?.name || 'System User' }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 tracking-widest truncate mt-0.5">{{ authStore.user?.email || 'user@example.com' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-2 space-y-1">
                            <Link :href="profileUrl" @click="showMobileProfileMenu = false" class="flex items-center w-full text-left px-4 py-2.5 text-[10px] font-black text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-colors uppercase tracking-widest">
                                <i class="fas fa-user-circle w-6 opacity-75"></i> My Profile
                            </Link>
                            <Link :href="route('employee.assets.index')" @click="showMobileProfileMenu = false" class="flex items-center w-full text-left px-4 py-2.5 text-[10px] font-black text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-colors uppercase tracking-widest">
                                <i class="fas fa-laptop w-6 opacity-75"></i> My Assets
                            </Link>
                            <Link :href="route('employee.my-approvals.index')" @click="showMobileProfileMenu = false" class="flex items-center w-full text-left px-4 py-2.5 text-[10px] font-black text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-colors uppercase tracking-widest">
                                <i class="fas fa-check-double w-6 opacity-75"></i> My Approvals
                            </Link>
                            <Link :href="safeRoute('employee.work.index', '#')" @click="showMobileProfileMenu = false" class="flex items-center w-full text-left px-4 py-2.5 text-[10px] font-black text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-colors uppercase tracking-widest">
                                <i class="fas fa-list-check w-6 opacity-75"></i> My Work
                            </Link>
                            <Link :href="route('employee.referrals.index')" @click="showMobileProfileMenu = false" class="flex items-center w-full text-left px-4 py-2.5 text-[10px] font-black text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-colors uppercase tracking-widest">
                                <i class="fas fa-users-viewfinder w-6 opacity-75"></i> My Referrals
                            </Link>
                            <div class="h-px bg-slate-100 my-1"></div>
                            <Link :href="route('profile.edit')" @click="showMobileProfileMenu = false" class="flex items-center w-full text-left px-4 py-2.5 text-[10px] font-black text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-colors uppercase tracking-widest">
                                <i class="fas fa-sliders-h w-6 opacity-75"></i> Settings
                            </Link>
                            <button @click="logout" class="flex items-center w-full text-left px-4 py-2.5 text-[10px] font-black text-rose-500 hover:text-white hover:bg-rose-500 rounded-xl transition-colors uppercase tracking-widest">
                                <i class="fas fa-power-off w-6 opacity-75"></i> Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
          </div>
        </header>

        <!-- Main View Area - Enhanced -->
        <main ref="mainContentRef" class="flex-1 overflow-x-hidden overflow-y-auto pb-32 lg:pb-8" :class="{'px-4 lg:px-8 pt-2': !isGuestRoute, 'px-0 pt-0': isAttendanceHub && !isGuestRoute}">
          
           <!-- Inertia Content (Unified) -->
          <div class="h-full" :class="isBoxedLayout ? 'max-w-6xl mx-auto w-full' : ''">
                <slot />
                <AppFooter />
          </div>

        </main>

        <!-- Mobile Bottom Nav -->
        <BottomNav 
          v-if="!isGuestRoute"
          :currentContext="currentContext" 
          @toggle-sidebar="isSidebarOpen = !isSidebarOpen"
        />
      </div>
    </div>
    
    <!-- Global FAB (Speed & Flow) -->
    <div class="fixed bottom-8 right-8 z-50 flex flex-col-reverse items-end gap-3 group">
        <!-- Main Trigger -->
        <button class="h-14 w-14 rounded-full bg-emerald-600 text-white shadow-2xl flex items-center justify-center hover:bg-emerald-700 transition-all hover:scale-110 active:scale-95 group-hover:rotate-45">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
        </button>

        <!-- Quick Actions (Reveal on Hover) -->
        <!-- <div class="flex flex-col gap-2 items-end opacity-0 group-hover:opacity-100 translate-y-4 group-hover:translate-y-0 transition-all duration-300 pointer-events-none group-hover:pointer-events-auto">
             <Link :href="route('admin.assets.dashboard')" class="flex items-center gap-2 bg-white px-4 py-2 rounded-full shadow-lg text-sm font-semibold text-gray-700 hover:text-emerald-600 hover:bg-emerald-50 transition-colors">
                <span>Add Asset</span>
                <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </Link>
            <Link :href="route('admin.inventory.index')" class="flex items-center gap-2 bg-white px-4 py-2 rounded-full shadow-lg text-sm font-semibold text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                <span>Add Stock</span>
                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                     </svg>
                </div>
            </Link>
             <Link :href="route('attendance.requests.index')" class="flex items-center gap-2 bg-white px-4 py-2 rounded-full shadow-lg text-sm font-semibold text-gray-700 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                <span>New Request</span>
                 <div class="h-8 w-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                     </svg>
                </div>
            </Link>
        </div> -->
    </div>

    <!-- Global Toast Notifications -->
    <ToastNotification />
    
    <!-- Global App Loader -->
    <GlobalLoader />

    <!-- Global SlideOver Drawer -->
    <SlideOver />
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3'; 
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';
import { useAnalyticsStore } from '@/stores/analytics'; // Data Store
import { useThemeStore } from '@/stores/theme';
import NavItem from '@/Components/NavItem.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import GlobalLoader from '@/Components/GlobalLoader.vue';
import SlideOver from '@/Components/SlideOver.vue'; // Imported
import BottomNav from '@/Components/Mobile/BottomNav.vue';
import CheckInButton from '@/Components/CheckInButton.vue';
import AppFooter from '@/Components/UI/AppFooter.vue';
import axios from 'axios';
import { 
    CubeIcon, 
    UsersIcon, 
    UserGroupIcon, 
    BriefcaseIcon, 
    CurrencyDollarIcon, 
    CalendarIcon, 
    ClockIcon, 
    DocumentTextIcon, 
    ShieldCheckIcon, 
    ChatBubbleLeftRightIcon, 
    ExclamationCircleIcon, 
    ScaleIcon, 
    ArrowTrendingUpIcon, 
    ChartBarIcon, 
    DevicePhoneMobileIcon, 
    FolderIcon, 
    SparklesIcon, 
    MapIcon, 
    BuildingOfficeIcon, 
    BanknotesIcon,
    CurrencyRupeeIcon,
    PaintBrushIcon,
    MinusSmallIcon,
    CommandLineIcon,
    BugAntIcon
} from '@heroicons/vue/24/outline';

// 1. State & Refs
const isSidebarOpen = ref(true); 
const showMobileProfileMenu = ref(false);
const currentContext = ref('employee'); // Default
const modules = ref([]);
const mainContentRef = ref(null);
const fetchedMenu = ref([]);
const groupedModules = ref({});
const expandedModules = ref({});
const vantaRef = ref(null);
let vantaEffect = null;

// 2. Router & Store
const page = usePage();
const authStore = useAuthStore();
const toastStore = useToastStore();
const analyticsStore = useAnalyticsStore(); // Init logic

const hasRoute = (name) => {
    try {
        return route().has(name);
    } catch (error) {
        return false;
    }
};

const safeRoute = (name, fallback = '#', params = {}) => {
    return hasRoute(name) ? route(name, params) : fallback;
};

const profileUrl = computed(() => {
    // 1. Priority: Server-Side Shared Link (Most Accurate)
    if (page.props.auth?.profileUrl) {
        return page.props.auth.profileUrl;
    }

    // 2. Client-Side Resolution fallback
    if (authStore.user?.employee?.uuid) {
        return page.props.auth?.profileUrl || '#';
    }

    // 3. Absolute Fallback to Hub
    return route('employee.hub');
});

// 2.5 Theme Store
const themeStore = useThemeStore();
const themeSettingsUrl = computed(() => {
    try {
        return route('admin.theme-settings');
    } catch (e) {
        return '/admin/theme-settings';
    }
});

// 3. Computed Properties
const hasAdminRole = computed(() => {
    const r = authStore.user?.role?.name;
    return r === 'Super Admin' || r === 'Admin' || r === 'Manager';
});

const isProjectContext = computed(() => {
    return route().current('projects.*') && !route().current('projects.dashboard') && !route().current('projects.index') && !route().current('projects.create');
});

const isGuestRoute = computed(() => {
    const path = window.location.pathname;
    return path === '/login' || path === '/register' || path === '/forgot-password';
});

const isAttendanceHub = computed(() => {
    return window.location.pathname.includes('/attendance');
});

const isNavbarSticky = computed(() => true);
const isBoxedLayout = computed(() => themeStore.currentTheme.layout.layoutWidth === 'boxed');

// 4. Watchers
watch(() => page.props.flash, (flash) => {
    if (flash?.success) toastStore.success(flash.success);
    if (flash?.error) toastStore.error(flash.error);
    if (flash?.message) toastStore.info(flash.message);
}, { deep: true });

watch(
    () => themeStore.currentTheme.layout.sidebarStyle,
    (val) => {
        isSidebarOpen.value = val !== 'collapsed';
    },
    { immediate: true }
);

// 5. Methods
const switchContext = (ctx) => {
    currentContext.value = ctx;
    const path = window.location.pathname; 
    let target = '';

    if (ctx === 'admin') {
        target = '/admin/attendance/data'; 
        if (path.includes('timesheets')) target = '/admin/attendance/timesheets';
        else if (path.includes('floating-holidays') || path.includes('holidays')) target = '/admin/attendance/floating-holidays';
        else if (path.includes('swaps') || path.includes('shift-swaps')) target = '/admin/attendance/swaps';
        else if (path.includes('regularization')) target = '/admin/attendance/regularization';
        else if (path.includes('leaves') || path.includes('leave')) target = '/admin/leave-types';
    } else {
        target = '/attendance';
        if (path.includes('timesheets')) target = '/attendance/timesheets';
        else if (path.includes('floating-holidays')) target = '/attendance/floating-holidays';
        else if (path.includes('swaps')) target = '/attendance/swaps';
        else if (path.includes('leave-types') || path.includes('leave')) target = '/leave-management?tab=my_leaves';
        else if (path.includes('projects')) target = '/projects/dashboard';
    }
    router.visit(target);
};

const logout = async () => {
    await authStore.logout();
    window.location.href = '/login'; 
};

const getInitials = (name) => {
    if (!name) return 'U';
    const parts = name.split(' ');
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
};

const updateMenu = () => {
    let m = [...fetchedMenu.value];
    
    const grouped = {};
    m.forEach(mod => {
        const group = mod.sidebar_group || 'Main Menu';
        if (!grouped[group]) grouped[group] = [];
        grouped[group].push(mod);
    });
    groupedModules.value = grouped;
};

const toggleModule = (key) => {
    expandedModules.value[key] = !expandedModules.value[key];
};

const shouldShowModule = (mod) => {
    return true;
};

const getRouteForModule = (key) => {
    const map = {
        'dashboard': '/dashboard',
        'user_management': '/admin/users',
        'employee_management': '/admin/employees', // Phase 1 Hub
        'attendance': currentContext.value === 'admin' ? '/admin/attendance/hub' : '/attendance', // Phase 2 Hub
        'leave': currentContext.value === 'admin' ? '/admin/leave-types' : '/leave-management',
        'project_management': '/projects/dashboard', // Phase 4 Hub
        'payroll': '/hr/payroll', // Phase 5 Hub
        'settings': '/admin/settings',
        'kits': '/kits',
        'assets_module': currentContext.value === 'admin' ? '/assets/dashboard' : '/my-assets',
        'docs': '/admin/physical-documents',
        'vendor_management': '/admin/vendors',
        'identity_cards': '/admin/identity-cards',
        'module_manager': '/admin/modules',
        'modules': '/admin/modules',
        'crm': '/admin/crm/hub',
        'compliance': '/hr/compliance',
        'cms': '/cms/hub',
        'family': currentContext.value === 'admin' ? route('admin.employees.reports.family') : (profileUrl.value + '?tab=family'),
        'documents': currentContext.value === 'admin' ? route('admin.employees.reports.documents') : (profileUrl.value + '?tab=documents'),
        'history': currentContext.value === 'admin' ? route('admin.employees.reports.history') : (profileUrl.value + '?tab=history'),
    };
    return map[key] || '#';
};

const getRouteForSubModule = (modKey, subKey) => {
    try {
        if (modKey === 'project_management') {
             if (subKey === 'dashboard') return '/projects/dashboard';
             if (subKey === 'my_tasks') return '/projects/my-tasks';
             if (subKey === 'clients') return '/projects/clients';
             if (subKey === 'bugs') return '/projects/bugs';
             if (subKey === 'client_portal') return '/projects/bugs?view=client';
             if (subKey === 'vault') return '/admin/vault';
             if (subKey === 'intelligence') return route('bugs.analytics');
             if (subKey === 'pulse') return '/projects/bugs?view=pulse';
        }
        if (modKey === 'employee_management') {
             if (currentContext.value === 'admin') {
                 if (subKey === 'index' || subKey === 'employees' || subKey === 'employee_master') return route('admin.employees.index');
                 if (subKey === 'documents') return route('admin.employees.reports.documents');
                 if (subKey === 'family') return route('admin.employees.reports.family');
                 if (subKey === 'history') return route('admin.employees.reports.history');
             } else {
                 if (subKey === 'index' || subKey === 'employees' || subKey === 'employee_master') return profileUrl.value;
                 if (subKey === 'documents') return profileUrl.value + '?tab=documents';
                 if (subKey === 'family') return profileUrl.value + '?tab=family';
                 if (subKey === 'history') return profileUrl.value + '?tab=history';
             }
        }
        if (modKey === 'organization') {
            if (subKey === 'departments') return '/admin/departments';
            if (subKey === 'locations') return '/admin/locations';
        }
        if (modKey === 'user_management') {
             if (subKey === 'users') return '/admin/users';
             if (subKey === 'roles') return '/admin/roles';
             if (subKey === 'permissions' || subKey === 'permission_management') return '/admin/roles/matrix'; 
             if (subKey === 'scopes' || subKey === 'scope_management') return '/admin/roles/matrix'; 
             if (subKey === 'access_review') return '/admin/users/access-review';
        }
        if (modKey === 'attendance') {
            if (currentContext.value === 'admin') {
                 if (subKey === 'timesheets') return '/admin/attendance/timesheets';
                 if (subKey === 'floating_holidays') return '/admin/attendance/floating-holidays';
                 if (subKey === 'shift_swaps' || subKey === 'swaps') return '/admin/attendance/swaps';
                 if (subKey === 'regularization' || subKey === 'approvals') return '/admin/attendance/regularization';
                 if (subKey === 'roster') return '/admin/attendance/roster';
                 if (subKey === 'policies' || subKey === 'policy_builder') return '/admin/attendance/policies';
                 if (subKey === 'analytics') return '/admin/attendance/analytics';
                 if (subKey === 'workflows' || subKey === 'workflow_builder') return '/admin/attendance/workflows';
                 if (subKey === 'teams') return '/admin/attendance/teams';
                 if (subKey === 'gamification' || subKey === 'gamification_rules') return '/admin/attendance/gamification';
                 if (subKey === 'biometric') return '/admin/attendance/devices';
                 if (subKey === 'shifts') return '/admin/attendance/shifts';
                 if (subKey === 'ai_logs') return '/admin/attendance/ai-logs';
                 if (subKey === 'monitoring' || subKey === 'live_monitor') return '/admin/attendance/monitoring';
                 if (subKey === 'team_approvals') return '/manager/approvals';
            } else {
                 if (subKey === 'timesheets') return '/attendance/timesheets';
                 if (subKey === 'floating_holidays') return '/attendance/floating-holidays';
                 if (subKey === 'shift_swaps' || subKey === 'swaps') return '/attendance/swaps';
                 if (subKey === 'regularization') return '/attendance'; 
                 if (subKey === 'team_approvals') return '/manager/approvals';
            }
        }
        if (modKey === 'compliance') {
            return route('hr.compliance.index', { tab: 'modules', sub: subKey });
        }
        if (modKey === 'leave') {
            if (currentContext.value === 'admin') {
                 if (subKey === 'leave_requests') return '/manager/approvals'; 
                 if (subKey === 'leave_types') return '/admin/leave-types';
                 if (subKey === 'holidays') return '/admin/attendance/floating-holidays'; 
                 if (subKey === 'my_leaves') return '/leave-management'; 
            } else {
                 if (subKey === 'my_leaves') return '/leave-management?tab=my_leaves';
                 if (subKey === 'apply') return '/leave-management?tab=my_leaves'; 
                 if (subKey === 'restricted') return '/leave-management?tab=restricted'; 
            }
        }
    } catch (e) {
        return '#';
    }
    return '#'; 
};

// Helper to resolve dynamic DB routes
const resolveRoute = (routeOrUrl) => {
    // If it looks like a route name (no slashes, contains dots, NO protocol)
    if (routeOrUrl && !routeOrUrl.startsWith('/') && routeOrUrl.includes('.') && !routeOrUrl.includes('://')) {
        try {
            // Check for project context
            if (routeOrUrl.startsWith('projects.')) {
                // Try to get project ID from page props (Inertia) OR Route Params OR URL Regex
                let projectId = page.props.project?.id || page.props.task?.project_id || route().params.project;
                
                // Fallback: Extract from URL if missing (Robustness)
                if (!projectId && page.url) {
                    const match = page.url.match(/\/projects\/(\d+)/);
                    if (match) {
                        projectId = match[1];
                    }
                }

                if (projectId) {
                    return route(routeOrUrl, { project: projectId });
                } else {
                    // If project specific but no ID, fallback to project list
                    return route('projects.index');
                }
            }
            return route(routeOrUrl);
        } catch (e) {
            return null;
        }
    }
    return routeOrUrl;
};

// Icon Mapping Helper
const getIconComponent = (iconName) => {
    const icons = {
        'CubeIcon': CubeIcon,
        'UsersIcon': UsersIcon,
        'UserGroupIcon': UserGroupIcon,
        'BriefcaseIcon': BriefcaseIcon,
        'CurrencyDollarIcon': CurrencyDollarIcon,
        'CalendarIcon': CalendarIcon,
        'ClockIcon': ClockIcon,
        'DocumentTextIcon': DocumentTextIcon,
        'ShieldCheckIcon': ShieldCheckIcon,
        'ChatAlt2Icon': ChatBubbleLeftRightIcon, // Mapped for backward compat
        'ChatBubbleLeftRightIcon': ChatBubbleLeftRightIcon,
        'ExclamationCircleIcon': ExclamationCircleIcon,
        'ScaleIcon': ScaleIcon,
        'TrendingUpIcon': ArrowTrendingUpIcon, // Mapped for backward compat
        'ArrowTrendingUpIcon': ArrowTrendingUpIcon,
        'ChartBarIcon': ChartBarIcon,
        'DeviceMobileIcon': DevicePhoneMobileIcon, // Mapped
        'DevicePhoneMobileIcon': DevicePhoneMobileIcon,
        'FolderIcon': FolderIcon,
        'SparklesIcon': SparklesIcon,
        'MapIcon': MapIcon,
        'OfficeBuildingIcon': BuildingOfficeIcon,
        'BanknotesIcon': BanknotesIcon,
        'CurrencyRupeeIcon': CurrencyRupeeIcon,
        'PaintBrushIcon': PaintBrushIcon,
        'CommandLineIcon': CommandLineIcon,
        'BugAntIcon': BugAntIcon,
    };
    return icons[iconName] || CubeIcon;
};

router.on('navigate', (event) => {
    const url = event.detail.page.url;
    if (url.startsWith('/admin')) {
        currentContext.value = 'admin';
    } else {
        currentContext.value = 'employee';
    }
    if (mainContentRef.value) {
        mainContentRef.value.scrollTop = 0;
    }
});

// 6. Lifecycle Hooks
onMounted(async () => {
    themeStore.init();

    // Context Logic
    if (window.location.pathname.startsWith('/admin')) {
        currentContext.value = 'admin';
    } else if (hasAdminRole.value && !window.location.pathname.startsWith('/attendance') && !window.location.pathname.startsWith('/leave-management')) {
        currentContext.value = 'admin';
    }

    // Auth Check
    if (!authStore.user) {
        try {
            await authStore.fetchUser();
        } catch (e) {
            console.error("Failed to fetch user", e);
        }
    }

    // Load Menu
    try {
        const res = await axios.get('/api/navigation');
        fetchedMenu.value = res.data.data ? res.data.data.menu : (res.data.menu || []);
        updateMenu(); 
    } catch(e) {
        console.error("Failed to load nav", e);
    }

    // Vanta Background - DISABLED
    /*
    if (window.VANTA && vantaRef.value) {
        try {
            vantaEffect = window.VANTA.BIRDS({
                el: vantaRef.value,
                mouseControls: true,
                touchControls: true,
                gyroControls: false,
                minHeight: 200.00,
                minWidth: 200.00,
                scale: 1.00,
                scaleMobile: 1.00,
                backgroundColor: 0xF8FAFC,     
                color1: 0x4F46E5,               
                color2: 0x6366F1,               
                birdSize: 1.40,
                quantity: 3.00,
                separation: 30.00
            });
        } catch (e) {
            console.warn("Vanta failed to init", e);
        }
    }
    */

});

onBeforeUnmount(() => {
  if (vantaEffect) {
    // vantaEffect.destroy();
  }
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
