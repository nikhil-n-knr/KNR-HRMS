<template>
    <aside class="w-64 bg-slate-900 flex flex-col h-full flex-shrink-0 relative overflow-hidden text-slate-300">
        <!-- Brand/Logo Area -->
        <div class="h-20 flex items-center px-6 border-b border-slate-800 z-10 bg-slate-900">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                    <i class="fas fa-satellite-dish text-sm"></i>
                </div>
                <span class="text-lg font-black tracking-tight text-white">Comms<span class="text-indigo-400">.hub</span></span>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 px-4 py-8 space-y-1 z-10 overflow-y-auto">
            <Link 
                :href="route('crm.meetings.hub')"
                class="group flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all mb-4"
                :class="[
                    $page.url.includes('meetings-hub') 
                        ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-500/10' 
                        : 'bg-slate-800/50 hover:bg-slate-800 hover:text-white transition-all border border-slate-700/50'
                ]"
            >
                <div class="w-6 flex justify-center mr-3">
                    <i class="fas fa-video text-base" :class="$page.url.includes('meetings-hub') ? 'text-white' : 'text-indigo-400 group-hover:text-white'"></i>
                </div>
                Meetings Hub
            </Link>

            <p class="px-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-4">Interactions</p>
            <Link v-for="item in interactionNav" :key="item.id" 
                :href="item.route ? route(item.route, { section: item.section, tab: item.tab }) : '#'"
                class="group flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all mb-1"
                :class="[
                    isActive(item) 
                        ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-500/10' 
                        : 'hover:bg-slate-800 hover:text-white transition-all'
                ]"
            >
                <div class="w-6 flex justify-center mr-3">
                    <i :class="['fas', `fa-${item.icon}`, 'text-base', isActive(item) ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400']"></i>
                </div>
                {{ item.name }}
            </Link>

            <p class="px-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mt-8 mb-4">Automation</p>
            <Link v-for="item in automationNav" :key="item.id" 
                :href="item.route ? route(item.route, { section: item.section, tab: item.tab }) : '#'"
                class="group flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all mb-1"
                :class="[
                    isActive(item) 
                        ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-500/10' 
                        : 'hover:bg-slate-800 hover:text-white transition-all'
                ]"
            >
                <div class="w-6 flex justify-center mr-3">
                    <i :class="['fas', `fa-${item.icon}`, 'text-base', isActive(item) ? 'text-white' : 'text-slate-500 group-hover:text-indigo-400']"></i>
                </div>
                {{ item.name }}
            </Link>
        </nav>
        
        <!-- Back to CRM -->
        <div class="p-4 border-t border-slate-800">
            <Link :href="route('crm.hub')" class="flex items-center justify-center gap-2 p-3 rounded-xl bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-all text-xs font-black uppercase tracking-widest">
                <i class="fas fa-arrow-left"></i> Back to Main CRM
            </Link>
        </div>
    </aside>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    section: String,
    tab: String
});

const isActive = (item) => {
    return (props.section === item.section && props.tab === item.tab);
};

const interactionNav = [
    { name: 'Inbox', id: 'inbox', section: 'communications', tab: 'inbox', icon: 'inbox', route: 'crm.comms.hub' },
    { name: 'Meetings', id: 'meetings', section: 'meetings', tab: 'calendar', icon: 'calendar-alt', route: 'crm.meetings.hub' },
    { name: 'Sent Items', id: 'sent', section: 'communications', tab: 'sent', icon: 'paper-plane', route: 'crm.comms.hub' },
];

const automationNav = [
    { name: 'App Setup', id: 'analytics', section: 'meetings', tab: 'reports', icon: 'chart-bar', route: 'crm.meetings.hub' },
    { name: 'Connect Accounts', id: 'settings', section: 'communications', tab: 'settings', icon: 'plug', route: 'crm.comms.hub' },
    { name: 'Governance & Access', id: 'governance', section: 'communications', tab: 'governance', icon: 'shield-alt', route: 'crm.comms.hub' }
];
</script>
