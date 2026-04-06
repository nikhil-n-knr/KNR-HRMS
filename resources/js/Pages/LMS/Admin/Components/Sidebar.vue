<template>
    <div class="w-20 md:w-24 bg-white border-r border-gray-100 flex flex-col h-full z-10 transition-all duration-500">
        <!-- Logo -->
        <div class="h-20 flex items-center justify-center border-b border-gray-100">
            <div class="w-10 h-10 bg-emerald-600 rounded-2xl flex items-center justify-center text-white shadow-emerald-200 shadow-lg transform hover:rotate-12 transition-transform">
                <i class="fas fa-brain text-xl"></i>
            </div>
        </div>

        <!-- Navigation Icons -->
        <nav class="flex-1 py-10 flex flex-col items-center gap-8 overflow-y-auto no-scrollbar">
            <Link 
                v-for="item in navItems" 
                :key="item.id"
                :href="route('lms.hub.index', { section: item.id })"
                class="group relative w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-300"
                :class="currentSection === item.id ? 'bg-emerald-50 text-emerald-600 shadow-sm' : 'text-gray-400 hover:bg-gray-50 hover:text-gray-600'"
            >
                <i :class="item.icon" class="text-xl"></i>
                <span class="absolute left-full ml-4 px-3 py-1 bg-gray-900 text-white text-[10px] font-bold rounded-lg opacity-0 invisible group-hover:visible group-hover:opacity-100 transition-all z-50 whitespace-nowrap uppercase tracking-widest shadow-xl">
                    {{ item.label }}
                </span>
                <!-- Active Indicator -->
                <div v-if="currentSection === item.id" class="absolute left-0 w-1 h-6 bg-emerald-600 rounded-r-full"></div>
            </Link>
        </nav>

        <!-- Bottom Actions -->
        <div class="p-6 border-t border-gray-100 flex flex-col items-center gap-6">
            <!-- Notifications Trigger -->
            <button @click="showNotifications = true" class="relative w-10 h-10 rounded-xl bg-gray-50 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition-all group">
                <i class="fas fa-bell"></i>
                <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white"></div>
                <!-- Tooltip -->
                 <span class="absolute left-full ml-4 px-3 py-1 bg-gray-900 text-white text-[10px] font-bold rounded-lg opacity-0 invisible group-hover:visible group-hover:opacity-100 transition-all z-50 whitespace-nowrap uppercase tracking-widest shadow-xl">
                    Notifications
                </span>
            </button>
            <button class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition-all group relative">
                <i class="fas fa-cog"></i>
                <!-- Tooltip -->
                <span class="absolute left-full ml-4 px-3 py-1 bg-gray-900 text-white text-[10px] font-bold rounded-lg opacity-0 invisible group-hover:visible group-hover:opacity-100 transition-all z-50 whitespace-nowrap uppercase tracking-widest shadow-xl">
                    Settings
                </span>
            </button>
            <div class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center text-white font-black shadow-lg shadow-emerald-100 overflow-hidden border-2 border-white relative group">
                <img v-if="$page.props.auth.user.avatar" :src="$page.props.auth.user.avatar" class="w-full h-full object-cover" />
                <span v-else>{{ $page.props.auth.user.name.charAt(0) }}</span>
                <!-- Tooltip -->
                <span class="absolute left-full ml-4 px-3 py-1 bg-gray-900 text-white text-[10px] font-bold rounded-lg opacity-0 invisible group-hover:visible group-hover:opacity-100 transition-all z-50 whitespace-nowrap uppercase tracking-widest shadow-xl">
                    Profile
                </span>
            </div>
        </div>

        <!-- Right Slide-Over Notification Panel -->
        <div v-if="showNotifications" class="fixed inset-0 z-50 flex justify-end pointer-events-none">
            <!-- Backdrop -->
            <div @click="showNotifications = false" class="absolute inset-0 bg-gray-900/20 backdrop-blur-sm pointer-events-auto transition-opacity"></div>
            
            <!-- Panel -->
            <div class="w-[400px] h-full bg-white shadow-2xl pointer-events-auto relative transform transition-transform border-l border-emerald-50 flex flex-col font-sans">
                <!-- Panel Header -->
                <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 uppercase italic tracking-tighter">Command Alerts</h3>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">System & Direct Neural Comm</p>
                    </div>
                    <button @click="showNotifications = false" class="w-8 h-8 rounded-full bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-900 flex items-center justify-center transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Panel Controls -->
                <div class="flex items-center p-4 bg-gray-50/50 border-b border-gray-100">
                    <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-500 hover:bg-gray-100'" class="flex-1 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">All</button>
                    <button @click="activeTab = 'system'" :class="activeTab === 'system' ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-500 hover:bg-gray-100'" class="flex-1 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">System</button>
                    <button @click="activeTab = 'direct'" :class="activeTab === 'direct' ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-500 hover:bg-gray-100'" class="flex-1 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all relative">
                        Direct
                        <span class="absolute top-1.5 right-4 w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                    </button>
                </div>

                <!-- Notifications List -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-4">
                    <div v-for="note in filteredNotifications" :key="note.id" class="p-5 rounded-2xl border" :class="note.read ? 'bg-white border-gray-100' : 'bg-emerald-50/50 border-emerald-100'">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" :class="note.type === 'system' ? 'bg-gray-900 text-white' : 'bg-emerald-100 text-emerald-600'">
                                <i :class="note.icon" class="text-sm"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-black uppercase tracking-widest mb-1" :class="note.type === 'system' ? 'text-gray-400' : 'text-emerald-600'">{{ note.author }}</p>
                                <p class="text-xs font-bold text-gray-800 leading-snug">{{ note.content }}</p>
                                <p class="text-[9px] text-gray-400 uppercase tracking-widest mt-2">{{ note.time }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-100 text-center">
                    <button class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-emerald-600 transition-colors">Mark All As Read</button>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    currentSection: String
});

const showNotifications = ref(false);
const activeTab = ref('all');

// Mock data for notifications
const notifications = ref([
    { id: 1, type: 'system', icon: 'fas fa-rocket', author: 'System Matrix', content: 'Mass enrollment protocol (Batch #2026-A) successfully executed.', time: '2 mins ago', read: false },
    { id: 2, type: 'direct', icon: 'fas fa-envelope', author: 'Nikhil Soni', content: 'Requested an extension for the final hydrogen cell logic assessment.', time: '1 hour ago', read: false },
    { id: 3, type: 'system', icon: 'fas fa-award', author: 'Ledger Engine', content: 'Issued 45 verified certificates for Completion of Safety protocols.', time: '3 hours ago', read: true },
    { id: 4, type: 'direct', icon: 'fas fa-exclamation-triangle', author: 'Instructor AI', content: 'Attention Threshold low for Module M.3 in recent 24h metrics.', time: '1 day ago', read: true },
]);

const filteredNotifications = computed(() => {
    if (activeTab.value === 'all') return notifications.value;
    return notifications.value.filter(n => n.type === activeTab.value);
});

const navItems = [
    { id: 'dashboard', label: 'Learning Hub', icon: 'fas fa-rocket' },
    { id: 'analytics', label: 'Command Center', icon: 'fas fa-chart-line' },
    { id: 'courses', label: 'Course Builder', icon: 'fas fa-cubes' },
    { id: 'enrollment', label: 'Enrollments & Batches', icon: 'fas fa-users-viewfinder' },
    { id: 'certificates', label: 'Certificates Wallet', icon: 'fas fa-award' },
    { id: 'erp', label: 'ERP Integration', icon: 'fas fa-project-diagram' },
    { id: 'config', label: 'System Profile', icon: 'fas fa-sliders-h' },
];
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }

.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
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
</style>
