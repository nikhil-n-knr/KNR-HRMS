<template>
    <div class="py-12 bg-slate-50 min-h-screen font-inter overflow-hidden relative">
        <!-- Background Grain/Gradient -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.05),transparent)] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10 relative z-10">
            
            <!-- Breadcrumbs if in detail -->
            <transition name="fade">
                <button v-if="selectedTicket" @click="selectedTicket = null" class="flex items-center gap-2 text-sm font-black uppercase tracking-widest text-slate-400 hover:text-emerald-600 transition-all mb-4 group">
                    <ArrowLeftIcon class="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
                    Back to Command Center
                </button>
            </transition>

            <transition name="slide-fade" mode="out-in">
                <!-- Detail View -->
                <div v-if="selectedTicket" key="detail">
                    <ClientTicketDetail :ticket="selectedTicket" :stages="stages" />
                </div>

                <!-- Dashboard View -->
                <div v-else key="dashboard" class="space-y-10">
                    <!-- Partial Header / Actions -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                        <div>
                            <h2 class="text-4xl font-black text-slate-900 tracking-tighter">Premium Support Portal</h2>
                            <p class="text-sm text-slate-400 font-medium mt-1">Real-time status of your managed project environment.</p>
                        </div>
                        <button 
                            @click="showWizard = true"
                            class="bg-emerald-600 text-white px-10 py-5 rounded-[2rem] font-black text-base uppercase tracking-[0.2em] shadow-2xl shadow-emerald-500/30 hover:bg-emerald-700 hover:scale-105 active:scale-95 transition-all flex items-center gap-3"
                        >
                            <PlusIcon class="w-5 h-5" />
                            Report New Issue
                        </button>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div v-for="stat in stats" :key="stat.label" class="bg-white p-10 rounded-[2.5rem] shadow-xl shadow-slate-200/20 border border-slate-100 group hover:border-emerald-500/30 transition-all">
                            <div :class="['h-14 w-14 rounded-2xl flex items-center justify-center mb-8 transition-transform group-hover:scale-110', stat.bg]">
                                <component :is="stat.icon" :class="['w-7 h-7', stat.color]" />
                            </div>
                            <div class="text-sm font-black text-slate-400 uppercase tracking-widest">{{ stat.label }}</div>
                            <div class="text-5xl font-black text-slate-900 mt-2 tracking-tighter">{{ stat.value }}</div>
                        </div>
                    </div>

                    <!-- Active Tickets Table -->
                    <div class="bg-white rounded-[3rem] shadow-2xl shadow-slate-200/20 border border-slate-100 overflow-hidden">
                        <div class="px-12 py-10 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-[0.25em]">Operational Signal Log</h3>
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-sm font-black text-emerald-600 uppercase tracking-widest">Live Updates Engine</span>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-50">
                                        <th class="px-12 py-6 text-sm font-black text-slate-400 uppercase tracking-widest">Reference</th>
                                        <th class="px-6 py-6 text-sm font-black text-slate-400 uppercase tracking-widest">Subject</th>
                                        <th class="px-6 py-6 text-sm font-black text-slate-400 uppercase tracking-widest">Environment</th>
                                        <th class="px-6 py-6 text-sm font-black text-slate-400 uppercase tracking-widest text-center">Journey Status</th>
                                        <th class="px-12 py-6 text-sm font-black text-slate-400 uppercase tracking-widest text-right">Modified</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="bug in (bugs?.data || [])" :key="bug.id" 
                                        @click="selectTicket(bug)"
                                        class="group cursor-pointer hover:bg-slate-50 transition-all relative">
                                        <td class="px-12 py-8">
                                            <span class="text-sm font-black text-slate-300 group-hover:text-emerald-500 transition-colors">#BT-{{ bug.id.toString().padStart(4, '0') }}</span>
                                        </td>
                                        <td class="px-6 py-8">
                                            <div class="text-base font-black text-slate-900 mb-1 tracking-tight group-hover:translate-x-1 transition-transform">{{ bug.subject }}</div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-black text-slate-400 uppercase tracking-widest">{{ bug.project?.name }}</span>
                                                <span class="text-xs text-slate-300">•</span>
                                                <span class="text-sm font-black text-emerald-600/60 uppercase tracking-widest">{{ bug.module?.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-8">
                                            <div class="flex gap-2">
                                                <span v-if="bug.environment_metadata?.platform" class="px-2 py-0.5 bg-slate-100 rounded text-sm font-bold text-slate-500">{{ bug.environment_metadata.platform }}</span>
                                                <span :class="['px-2 py-0.5 rounded text-sm font-bold uppercase tracking-widest', severityStyle(bug.severity)]">{{ bug.severity }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-8 text-center">
                                            <div class="inline-flex items-center gap-3 px-4 py-2 bg-slate-50 rounded-2xl group-hover:bg-white transition-colors border border-transparent group-hover:border-slate-100">
                                                <div :class="['h-2 w-2 rounded-full', bug.stage?.is_final ? 'bg-emerald-500' : 'bg-amber-500 pulse']"></div>
                                                <span class="text-sm font-black uppercase tracking-widest text-slate-600">{{ bug.stage?.name || 'Processing' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-12 py-8 text-right">
                                            <div class="flex flex-col items-end">
                                                <span class="text-sm font-bold text-slate-900">{{ formatDate(bug.updated_at) }}</span>
                                                <span class="text-xs font-black text-slate-300 uppercase tracking-widest">Last Activity</span>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Empty Signal -->
                                    <tr v-if="!bugs?.data?.length">
                                        <td colspan="5" class="py-32 text-center">
                                            <div class="flex flex-col items-center">
                                                <div class="h-24 w-24 bg-slate-50 rounded-[2.5rem] flex items-center justify-center mb-6 text-slate-200">
                                                    <ShieldCheckIcon class="w-12 h-12" />
                                                </div>
                                                <h3 class="text-xl font-black text-slate-900 tracking-tight">All Systems Operational</h3>
                                                <p class="text-slate-400 text-sm font-medium mt-1">We haven't detected any active hazards in your project modules.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Wizard Overlay -->
        <transition name="fade">
            <div v-if="showWizard" class="fixed inset-0 z-[110] bg-slate-900/90 backdrop-blur-xl flex items-center justify-center p-4 lg:p-12 overflow-y-auto">
                <div class="w-full">
                    <div class="max-w-4xl mx-auto flex justify-end mb-8">
                        <button @click="showWizard = false" class="h-14 w-14 bg-white/10 hover:bg-white/20 text-white rounded-[1.5rem] flex items-center justify-center transition-all">
                            <XMarkIcon class="w-8 h-8" />
                        </button>
                    </div>
                    <ClientTicketWizard @ticket-created="handleCreated" />
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    PlusIcon, 
    BugAntIcon, 
    CheckCircleIcon, 
    HeartIcon, 
    ShieldCheckIcon,
    ArrowLeftIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import ClientTicketWizard from './ClientTicketWizard.vue';
import ClientTicketDetail from './ClientTicketDetail.vue';
import dayjs from 'dayjs';

const props = defineProps(['bugs', 'projects', 'open_critical_count', 'stages']);

const showWizard = ref(false);
const selectedTicket = ref(null);

const stats = computed(() => [
    { 
        label: 'Active Hazards', 
        value: props.bugs?.data?.filter(b => b.stage && !b.stage.is_final).length || 0,
        icon: BugAntIcon,
        bg: 'bg-amber-50',
        color: 'text-amber-500'
    },
    { 
        label: 'Stabilized Issues', 
        value: props.bugs?.data?.filter(b => b.stage && b.stage.is_final).length || 0,
        icon: CheckCircleIcon,
        bg: 'bg-emerald-50',
        color: 'text-emerald-500'
    },
    { 
        label: 'Operational Integrity', 
        value: props.open_critical_count === 0 ? 'Optimal' : 'Compromised',
        icon: HeartIcon,
        bg: props.open_critical_count === 0 ? 'bg-emerald-600' : 'bg-rose-500',
        color: 'text-white'
    }
]);

const selectTicket = (bug) => {
    selectedTicket.value = bug;
};

const handleCreated = () => {
    showWizard.value = false;
    router.reload();
};

const severityStyle = (sev) => {
    if (sev === 'critical') return 'bg-rose-100 text-rose-600';
    if (sev === 'high') return 'bg-orange-100 text-orange-600';
    return 'bg-emerald-100 text-emerald-600';
};

const formatDate = (date) => dayjs(date).format('MMM D, YYYY');

</script>

<style scoped>
.slide-fade-enter-active, .slide-fade-leave-active { transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-fade-enter-from { opacity: 0; transform: translateY(20px); }
.slide-fade-leave-to { opacity: 0; transform: translateY(-20px); }

.fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.pulse {
    animation: p 2s infinite;
}
@keyframes p {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.5); opacity: 0.5; }
    100% { transform: scale(1); opacity: 1; }
}
</style>
