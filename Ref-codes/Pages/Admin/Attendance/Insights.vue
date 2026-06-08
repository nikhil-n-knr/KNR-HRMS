<template>
  <component :is="embedded ? 'div' : AttendanceLayout" title="Organizational Intelligence Matrix" :activeTab="activeTab || 'analytics'" @update:activeTab="handleTabChange" :hideSidebar="!isHubMode" v-bind="$props">
    <Head v-if="!embedded" title="Intelligence Pulse" />
    
    <div :class="{'max-w-[1600px] mx-auto': !embedded}" class="space-y-10 pb-20">
        <!-- Intelligence Mode Selector -->
        <div class="sticky top-0 z-30 bg-white/60 backdrop-blur-2xl px-1 rounded-2xl border border-white/50 shadow-2xl shadow-emerald-500/10 flex justify-between items-center mb-10 transition-all duration-500 max-w-2xl mx-auto">
            <div class="flex p-1.5 gap-2 w-full">
                <button 
                    @click="switchTab('monitor')"
                    class="flex-1 px-6 py-3 rounded-xl text-sm font-black uppercase tracking-[0.2em] transition-all duration-500 flex items-center justify-center gap-3 group"
                    :class="activeTab === 'monitor' ? 'bg-slate-900 text-white shadow-xl shadow-slate-900/20 active-glow' : 'text-slate-400 hover:bg-emerald-50/50 hover:text-emerald-600'"
                >
                    <i class="fas fa-radar text-base group-hover:rotate-12 transition-transform"></i>
                    Live Telemetry
                </button>
                <button 
                    @click="switchTab('analytics')"
                    class="flex-1 px-6 py-3 rounded-xl text-sm font-black uppercase tracking-[0.2em] transition-all duration-500 flex items-center justify-center gap-3 group"
                    :class="activeTab === 'analytics' ? 'bg-emerald-600 text-white shadow-xl shadow-emerald-600/20' : 'text-slate-400 hover:bg-emerald-50/50 hover:text-emerald-600/80'"
                >
                    <i class="fas fa-brain-circuit text-base group-hover:scale-110 transition-transform"></i>
                    Deep Intelligence
                </button>
            </div>
        </div>

        <!-- Intelligence Matrix -->
        <div class="relative min-h-[800px]">
            <Transition
                enter-active-class="transition ease-out duration-700 delay-100"
                enter-from-class="opacity-0 translate-y-12 scale-95"
                enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition ease-in duration-300 absolute w-full top-0"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-90 blur-xl"
                mode="out-in"
            >
                <LiveMonitor v-if="activeTab === 'monitor'" :key="'monitor'" />
                <Analytics v-else :departments="departments" :key="'analytics'" />
            </Transition>
        </div>
    </div>
  </component>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import LiveMonitor from './Partials/LiveMonitor.vue';
import Analytics from './Partials/Analytics.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    embedded: Boolean,
    tab: String, // Ensure tab is here to match the URL query param
    departments: Array,
    filters: Object
});

const page = usePage();
const isHubMode = computed(() => {
    const url = page.url;
    return url.includes('/attendance/hub') || url.includes('hub=1') || url.includes('hub=true');
});

const activeTab = ref(props.tab || 'analytics');

const handleTabChange = (tabId) => {
    // If it's one of OUR internal tabs (monitor/analytics), just switch locally
    if (tabId === 'monitor' || tabId === 'analytics') {
        activeTab.value = tabId;
        return;
    }
    
    // Global Hub tabs are now handled automatically by AttendanceLayout.vue
    // unless we are embedded.
};

const switchTab = (tab) => {
    activeTab.value = tab;
};
</script>

<style scoped>
.active-glow {
    position: relative;
    overflow: hidden;
}
.active-glow::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 150%;
    height: 150%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    transform: translate(-50%, -50%);
    pointer-events: none;
}
</style>
