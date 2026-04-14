<template>
    <div class="mobile-app-root min-h-screen bg-[#f8fafc] font-outfit select-none">
        <!-- Structural Header -->
        <header class="mobile-header">
            <div class="header-left flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white shadow-lg flex-shrink-0">
                    <i class="fas fa-leaf text-sm"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xs font-black uppercase tracking-tighter text-slate-900 leading-none">HR.MS</h1>
                    <span class="text-[8px] font-black text-emerald-500 uppercase tracking-widest mt-1 leading-none">{{ currentPageTitle }}</span>
                </div>
            </div>

            <div class="header-right flex items-center gap-2">
                <!-- Notifications -->
                <Link :href="route('mobile.notifications')" class="w-9 h-9 rounded-lg bg-white border border-slate-100 flex items-center justify-center relative shadow-sm">
                    <i class="fas fa-bell text-slate-400 text-xs text-emerald-600/60"></i>
                    <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 text-white text-[7px] font-black rounded-full flex items-center justify-center border-2 border-white">
                        {{ unreadCount }}
                    </span>
                </Link>

                <!-- Profile -->
                <Link :href="route('mobile.profile')" class="w-9 h-9 rounded-lg bg-emerald-50 border border-emerald-100 overflow-hidden flex-shrink-0">
                    <img v-if="user.avatar" :src="user.avatar" @error="user.avatar = null" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center bg-emerald-600 text-white text-[10px] font-black">
                        {{ user.name?.charAt(0) || 'U' }}
                    </div>
                </Link>
            </div>
        </header>

        <!-- Main Viewport -->
        <main class="animate-nature-fade px-6 py-4 pb-32">
            <slot />
        </main>

        <!-- Command Matrix (Static Bottom) -->
        <nav class="bottom-nav">
            <Link 
                v-for="item in [
                    { id: 'dashboard', icon: 'fas fa-th-large', label: 'Matrix', route: 'mobile.dashboard' },
                    { id: 'tasks', icon: 'fas fa-tasks-alt', label: 'Tasks', route: 'mobile.tasks' },
                    { id: 'hub', icon: 'fas fa-satellite-dish', label: 'Hub', route: 'mobile.chat' },
                    { id: 'profile', icon: 'fas fa-user-shield', label: 'Space', route: 'mobile.profile' }
                ]" 
                :key="item.id" 
                :href="route(item.route)" 
                class="nav-item" 
                :class="{ 'active': route().current(item.route) }"
            >
                <div class="w-10 h-10 flex items-center justify-center rounded-xl transition-all" :class="route().current(item.route) ? 'bg-emerald-50' : ''">
                    <i :class="item.icon" class="text-sm"></i>
                </div>
                <span class="nav-text">{{ item.label }}</span>
            </Link>
        </nav>
    </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import { usePage, Link, Head } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();
const user = computed(() => page.props.auth.user);
const unreadCount = ref(0);

const fetchUnreadCount = async () => {
    try {
        const response = await axios.get('/api/mobile/v1/notifications/unread-count');
        unreadCount.value = response.data.count;
    } catch (err) {
        console.error("Failed to fetch unread count");
    }
};

const currentPageTitle = computed(() => {
    const current = route().current();
    if (current.includes('dashboard')) return 'Command Center';
    if (current.includes('tasks')) return 'My Tasks';
    if (current.includes('timesheet')) return 'Work Logs';
    if (current.includes('chat')) return 'Communication Hub';
    if (current.includes('approvals')) return 'Decision Center';
    if (current.includes('profile')) return 'My Space';
    if (current.includes('notifications')) return 'Activity Feed';
    return 'LEAP';
});

onMounted(fetchUnreadCount);
</script>

<style>
@import "/resources/css/mobile_theme.css";

/* Emergency Layout Hardening */
.mobile-header {
    display: flex !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 1000 !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: space-between !important;
    width: 100% !important;
}

.bottom-nav {
    display: flex !important;
    position: fixed !important;
    bottom: 24px !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: space-around !important;
    width: calc(100% - 48px) !important;
}
</style>
