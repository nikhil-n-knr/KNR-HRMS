<template>
    <div class="space-y-8 p-6 lg:p-10 bg-slate-50/50 min-h-full">
        <!-- Header & Quick Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">
                    {{ viewType === 'team' ? 'Enterprise' : 'Command' }} <span class="text-indigo-600">{{ viewType === 'team' ? 'Intelligence' : 'Center' }}</span>
                </h1>
                <p class="text-slate-500 font-medium mt-1">
                    {{ viewType === 'team' ? 'Global interaction flow across all departments.' : 'Welcome back. Here\'s your interaction overview for today.' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Admin View Toggle -->
                <div v-if="isAdmin" class="bg-white p-1 rounded-2xl border border-slate-200 flex items-center mr-4 shadow-sm">
                    <button 
                        @click="viewType !== 'personal' && toggleView()"
                        class="px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all"
                        :class="viewType === 'personal' ? 'bg-slate-900 text-white' : 'text-slate-400 hover:text-slate-900'"
                    >
                        Personal
                    </button>
                    <button 
                        @click="viewType !== 'team' && toggleView()"
                        class="px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all"
                        :class="viewType === 'team' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-indigo-600'"
                    >
                        Team
                    </button>
                </div>

                <button @click="$emit('action', 'new-email')" class="flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all">
                    <i class="fas fa-plus"></i>
                    <span>New Email</span>
                </button>
                <button @click="$emit('action', 'schedule-meeting')" class="flex items-center gap-2 px-5 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl font-bold shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all">
                    <i class="fas fa-calendar-plus text-indigo-500"></i>
                    <span>Schedule Meeting</span>
                </button>
            </div>
        </div>

        <!-- KPI Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="kpi in kpis" :key="kpi.label" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50 group hover:border-indigo-100 transition-all">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-colors" :class="kpi.bgColor">
                        <i :class="[kpi.icon, kpi.textColor, 'text-xl']"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 group-hover:text-indigo-400 transition-colors">Today</span>
                </div>
                <div class="mt-5">
                    <h3 class="text-3xl font-black text-slate-900 leading-none">{{ kpi.value }}</h3>
                    <p class="text-sm font-bold text-slate-500 mt-2">{{ kpi.label }}</p>
                </div>
                <!-- Mini sparkline or indicator could go here -->
                <div class="mt-4 flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black tracking-wide" :class="kpi.trend > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-500'">
                        {{ kpi.trend > 0 ? '+' : '' }}{{ kpi.trend }}%
                    </span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">vs yesterday</span>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- My Agenda -->
            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">My Agenda</h2>
                    <button class="text-indigo-600 font-bold text-sm flex items-center gap-1 hover:gap-2 transition-all">
                        Full Schedule <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
                
                <div class="bg-white rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden">
                    <div v-if="agenda.length > 0" class="divide-y divide-slate-50">
                        <div v-for="item in agenda" :key="item.id" class="p-5 flex items-start gap-4 hover:bg-slate-50/50 transition-colors">
                            <div class="flex flex-col items-center">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-tighter">{{ item.time }}</span>
                                <div class="w-0.5 flex-1 bg-slate-100 my-2 rounded-full"></div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-slate-900">{{ item.title }}</h4>
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-500">
                                        {{ item.type }}
                                    </span>
                                </div>
                                <p class="text-sm text-slate-500 mt-1 flex items-center gap-2">
                                    <i class="fas fa-user-circle text-xs"></i> {{ item.person }}
                                </p>
                                <div class="mt-3 flex items-center gap-2">
                                    <button v-if="item.link" @click="openLink(item.link)" class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all">
                                        Join Meeting
                                    </button>
                                    <button class="px-3 py-1.5 bg-slate-50 text-slate-500 rounded-lg text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="p-12 text-center">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-mug-hot text-slate-300 text-xl"></i>
                        </div>
                        <h4 class="font-black text-slate-900">Your agenda is clear!</h4>
                        <p class="text-sm text-slate-500 mt-1 italic">Time for a coffee break or some deep work.</p>
                    </div>
                </div>
            </div>

            <!-- Top Signals / Sidebar widgets -->
            <div class="space-y-6">
                <h2 class="text-xl font-black text-slate-900 tracking-tight">Active Signals</h2>
                
                <div class="space-y-4">
                    <div v-for="signal in signals" :key="signal.id" class="bg-white p-4 rounded-2xl border border-slate-100 shadow-md flex items-center gap-4 group hover:ring-2 hover:ring-indigo-100 transition-all">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" :class="signal.typeColor">
                            <i :class="['fas', signal.icon]"></i>
                        </div>
                        <div class="flex-1 overflow-hidden text-left">
                            <h5 class="font-bold text-slate-900 truncate leading-tight">{{ signal.title }}</h5>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-tight">{{ signal.time }}</p>
                        </div>
                        <i class="fas fa-chevron-right text-slate-300 group-hover:text-indigo-400 group-hover:translate-x-1 transition-all"></i>
                    </div>
                    <div v-if="!signals || signals.length === 0" class="p-8 text-center bg-white rounded-2xl border border-slate-100 border-dashed">
                        <p class="text-xs font-black text-slate-300 uppercase tracking-widest">No active signals</p>
                    </div>
                </div>

                <!-- Churn Risk Widget -->
                <div class="bg-slate-900 p-6 rounded-3xl shadow-xl shadow-slate-900/20 relative overflow-hidden">
                    <div class="relative z-10">
                        <h4 class="text-white font-black text-lg tracking-tight">Churn <span class="text-rose-400">Alert</span></h4>
                        <p class="text-slate-400 text-xs font-bold mt-1">High-risk silent accounts</p>
                        
                        <div class="mt-6 space-y-3">
                            <div class="flex items-center justify-between p-2 rounded-xl bg-slate-800 border border-slate-700">
                                <span class="text-xs text-white font-bold">Acme Corp</span>
                                <span class="text-[10px] px-2 py-0.5 bg-rose-500/10 text-rose-400 rounded-md font-black uppercase tracking-widest">90 days</span>
                            </div>
                            <div class="flex items-center justify-between p-2 rounded-xl bg-slate-800 border border-slate-700">
                                <span class="text-xs text-white font-bold">Global Tech</span>
                                <span class="text-[10px] px-2 py-0.5 bg-rose-500/10 text-rose-400 rounded-md font-black uppercase tracking-widest">45 days</span>
                            </div>
                        </div>

                        <button class="w-full mt-6 py-2.5 bg-rose-500 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-rose-600 transition-all">
                            Start Recovery Campaign
                        </button>
                    </div>
                    <!-- Decor -->
                    <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-32 h-32 bg-rose-500/10 rounded-full blur-3xl"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    isAdmin: Boolean,
    viewType: String,
    kpis: Array,
    agenda: Array,
    signals: Array
});

const toggleView = () => {
    const nextView = props.viewType === 'personal' ? 'team' : 'personal';
    router.get(route('crm.comms.hub'), { 
        section: 'communications', 
        tab: 'dashboard', 
        view_type: nextView 
    }, { preserveState: true, preserveScroll: true });
};

const openLink = (url) => {
    window.open(url, '_blank');
};
</script>
