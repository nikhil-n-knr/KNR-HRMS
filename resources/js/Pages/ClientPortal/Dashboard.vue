<template>
    <div class="min-h-screen bg-[#fafafa] font-inter text-slate-900 overflow-hidden relative selection:bg-emerald-100 italic-shadows">
        <!-- Luxury Gradient Glows -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden">
            <div class="absolute -top-[10%] -right-[5%] w-[40%] h-[40%] bg-emerald-100/30 blur-[120px] rounded-full"></div>
            <div class="absolute top-[40%] -left-[10%] w-[30%] h-[30%] bg-indigo-100/20 blur-[100px] rounded-full"></div>
        </div>

        <!-- Navigation: Premium Floating -->
        <nav class="sticky top-0 z-[100] px-4 sm:px-8 py-4 pointer-events-none">
            <div class="max-w-7xl mx-auto flex items-center justify-between pointer-events-auto bg-white/70 backdrop-blur-2xl border border-white/40 shadow-[0_8px_32px_rgba(0,0,0,0.03)] px-6 sm:px-10 h-24 rounded-[2.5rem]">
                <div class="flex items-center gap-10">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 bg-emerald-600 rounded-[1.25rem] flex items-center justify-center shadow-2xl shadow-emerald-500/40 transform hover:rotate-6 transition-transform">
                            <CpuChipIcon class="h-6 w-6 text-white" />
                        </div>
                        <div class="flex flex-col">
                            <span class="font-black text-xl tracking-tighter text-slate-900 leading-none">Client Hub</span>
                            <span class="text-sm font-black text-emerald-500 uppercase tracking-widest mt-1.5 opacity-80">Managed Portal v2.4</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="showWizard = true" class="px-8 py-4 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] shadow-2xl shadow-slate-900/20 hover:scale-105 active:scale-95 transition-all">
                        Raise Ticket / Broadcast +
                    </button>
                    <div class="h-10 w-px bg-slate-200/50 mx-4"></div>
                    <Link :href="route('portal.logout')" method="post" as="button" class="h-12 w-12 flex items-center justify-center rounded-2xl hover:bg-rose-50 hover:text-rose-600 text-slate-400 transition-all">
                        <ArrowRightOnRectangleIcon class="h-5 w-5" />
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Live Pulse Engine -->
        <LivePulseTicker />

        <main class="max-w-7xl mx-auto py-12 px-6 sm:px-10 space-y-20 relative z-10 transition-all duration-700" :class="showWizard ? 'blur-2xl scale-[0.98] grayscale opacity-50' : ''">
            
            <!-- Hero Intelligence Block -->
            <div class="flex flex-col lg:flex-row justify-between items-end gap-10">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-black uppercase tracking-widest border border-emerald-200/50">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Signal Active
                    </div>
                    <h1 class="text-6xl font-black text-slate-900 tracking-tighter leading-none">Intelligence Dashboard</h1>
                    <p class="text-slate-400 font-medium text-lg max-w-xl">Real-time oversight of your deployment integrity and triage velocity.</p>
                </div>

                <!-- Strategic Stats -->
                <div class="flex gap-4">
                    <div v-for="stat in summaryStats" :key="stat.label" class="bg-white/50 border border-white p-6 rounded-[2rem] shadow-sm flex flex-col items-center min-w-[140px]">
                        <span class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">{{ stat.label }}</span>
                        <span class="text-4xl font-black text-slate-900 tracking-tighter">{{ stat.value }}</span>
                    </div>

                    <!-- Knowledge Vault Shortcut -->
                    <div @click="showVault = true" class="bg-indigo-900 px-8 py-6 rounded-[2rem] shadow-2xl shadow-indigo-200 flex flex-col items-center justify-center cursor-pointer hover:scale-105 active:scale-95 transition-all group relative overflow-hidden">
                        <span class="text-sm font-black text-indigo-300 uppercase tracking-widest mb-1 relative z-10">Library</span>
                        <div class="flex items-center gap-2 relative z-10">
                            <span class="text-xl font-black text-white tracking-tighter">Vault</span>
                            <BookOpenIcon class="h-5 w-5 text-indigo-400 group-hover:text-white transition-colors" />
                        </div>
                        <div class="absolute -right-4 -top-4 h-12 w-12 bg-indigo-500 blur-2xl rounded-full opacity-20"></div>
                    </div>
                </div>
            </div>

            <!-- Priority Verification (If any) -->
            <div v-if="awaiting_verification.length > 0" class="space-y-10">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 bg-rose-500 rounded-xl flex items-center justify-center shadow-lg shadow-rose-200">
                        <CheckBadgeIcon class="h-5 w-5 text-white" />
                    </div>
                    <h2 class="text-base font-black text-slate-900 uppercase tracking-[0.25em]">Pending Verification Gate</h2>
                    <div class="h-px bg-slate-100 flex-1"></div>
                </div>

                <div class="grid gap-8 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="ticket in awaiting_verification" :key="ticket.id" 
                         class="bg-white p-10 rounded-[3rem] shadow-2xl shadow-slate-200/40 border border-slate-50 relative overflow-hidden group hover:scale-[1.02] transition-all cursor-pointer"
                         @click="selectedTicket = ticket">
                        <div class="relative z-10 flex flex-col h-full">
                            <div class="flex justify-between items-start mb-10">
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-sm font-black uppercase tracking-widest rounded-lg">{{ ticket.module?.name }}</span>
                                <div class="text-slate-300 group-hover:text-emerald-500 transition-colors">
                                    <ArrowUpRightIcon class="h-6 w-6" />
                                </div>
                            </div>
                            <h3 class="text-2xl font-black text-slate-900 mb-4 leading-tight">{{ ticket.subject }}</h3>
                            <p class="text-slate-400 text-sm font-medium line-clamp-3 mb-12 flex-1">{{ ticket.description }}</p>
                            
                            <div class="flex gap-3">
                                <button @click.stop="verify(ticket.id, 'approve')" class="flex-1 bg-emerald-600 text-white h-12 rounded-xl text-sm font-black uppercase tracking-widest shadow-xl shadow-emerald-500/20 hover:bg-emerald-700 transition-all">Confirm</button>
                                <button @click.stop="verify(ticket.id, 'reject')" class="flex-1 bg-slate-50 text-slate-500 h-12 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-rose-50 hover:text-rose-600 transition-all">Reject</button>
                            </div>
                        </div>
                        <div class="absolute -right-6 -top-6 h-24 w-24 bg-emerald-50 blur-3xl rounded-full opacity-50 group-hover:opacity-100 transition-all"></div>
                    </div>
                </div>
            </div>

            <!-- Global Insight Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                 <!-- The Doughnut Visualization -->
                 <div class="bg-white p-12 rounded-[3.5rem] shadow-2xl shadow-slate-200/50 border border-slate-50 relative overflow-hidden group">
                    <h3 class="text-base font-black text-slate-300 uppercase tracking-[0.3em] mb-12">Quality Topology</h3>
                    <div class="h-72 flex items-center justify-center relative">
                         <Doughnut v-if="chartData.labels.length" :data="chartData" :options="chartOptions" />
                         <div v-if="chartData.labels.length" class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <component :is="activeIcon" class="h-8 w-8 text-emerald-600 mb-2 opacity-20" />
                            <span class="text-4xl font-black text-slate-900 leading-none tabular-nums">{{ recent_bugs.length }}</span>
                            <span class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1.5">Anomalies</span>
                         </div>
                    </div>
                    
                    <div class="mt-12 space-y-4">
                        <div v-for="(label, idx) in chartData.labels.slice(0, 4)" :key="idx" class="flex items-center justify-between p-4 bg-slate-50/50 rounded-2xl hover:bg-emerald-50/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="h-3 w-3 rounded-full shadow-lg" :style="{ backgroundColor: chartData.datasets[0].backgroundColor[idx] }"></div>
                                <span class="text-xs font-black uppercase tracking-tight text-slate-600">{{ label || 'General' }}</span>
                            </div>
                            <span class="text-xs font-black text-slate-900 bg-white px-3 py-1 rounded-lg border border-slate-100">{{ chartData.datasets[0].data[idx] }}</span>
                        </div>
                    </div>
                 </div>

                 <!-- Recent Signal Log -->
                 <div class="lg:col-span-2 bg-white rounded-[3.5rem] shadow-2xl shadow-slate-200/50 border border-slate-50 overflow-hidden">
                    <div class="px-12 py-10 border-b border-slate-50 flex justify-between items-center bg-slate-50/20">
                        <h3 class="text-base font-black text-slate-900 uppercase tracking-[0.3em]">Operational Signal Log</h3>
                        <button @click="exportReport" class="flex items-center gap-2 text-sm font-black uppercase tracking-widest text-slate-400 hover:text-emerald-600 transition-colors">
                            <ArrowDownTrayIcon class="h-4 w-4" />
                            Export Data
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto overflow-y-hidden">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-slate-50">
                                    <th class="px-12 py-6 text-sm font-black text-slate-400 uppercase tracking-widest">Hash</th>
                                    <th class="px-6 py-6 text-sm font-black text-slate-400 uppercase tracking-widest">Entity Signature</th>
                                    <th class="px-6 py-6 text-sm font-black text-slate-400 uppercase tracking-widest text-center">Journey</th>
                                    <th class="px-12 py-6 text-sm font-black text-slate-400 uppercase tracking-widest text-right">Updated</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50/50">
                                <tr v-for="ticket in recent_bugs" :key="ticket.id" 
                                    @click="selectedTicket = ticket"
                                    class="group cursor-pointer hover:bg-slate-50/70 transition-colors">
                                    <td class="px-12 py-8">
                                        <span class="text-sm font-mono font-black text-slate-300 group-hover:text-emerald-500 transition-colors uppercase">BT-{{ ticket.id.toString().padStart(4, '0') }}</span>
                                    </td>
                                    <td class="px-6 py-8">
                                        <div class="flex flex-col">
                                            <p class="text-base font-black text-slate-900 group-hover:translate-x-1 transition-transform tracking-tight">{{ ticket.subject }}</p>
                                            <p class="text-sm font-black text-slate-400 uppercase tracking-[0.15em] mt-1.5">{{ ticket.module?.name || 'Managed Core' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-8 text-center">
                                        <span :class="['px-4 py-2 rounded-2xl text-sm font-black uppercase tracking-[0.2em] shadow-sm', statusTheme(ticket.stage)]">
                                            {{ ticket.stage?.name || 'Tracing' }}
                                        </span>
                                    </td>
                                    <td class="px-12 py-8 text-right font-black text-sm text-slate-400 tabular-nums uppercase">
                                        {{ dayjs(ticket.updated_at).format('DD MMM, YYYY') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                 </div>
            </div>
        </main>

        <!-- Premium Overlays -->
        <transition name="fade">
            <div v-if="showWizard" class="fixed inset-0 z-[200] bg-slate-900/40 backdrop-blur-3xl flex items-center justify-center p-8">
                <div class="w-full relative animate-in zoom-in duration-300">
                    <button @click="showWizard = false" class="absolute -top-16 right-0 h-14 w-14 bg-white/10 hover:bg-white/20 text-white rounded-[1.5rem] flex items-center justify-center transition-all border border-white/10">
                        <XMarkIcon class="h-8 w-8" />
                    </button>
                    <ClientTicketWizard @ticket-created="handleSignalCreated" />
                </div>
            </div>
        </transition>

        <transition name="slide-up">
            <div v-if="selectedTicket" class="fixed inset-0 z-[200] bg-white overflow-y-auto p-4 sm:p-20 custom-scrollbar">
                <div class="max-w-7xl mx-auto relative">
                     <button @click="selectedTicket = null" class="fixed top-8 right-8 lg:right-20 h-16 w-16 bg-slate-900 text-white rounded-[2rem] flex items-center justify-center shadow-2xl hover:scale-110 active:scale-95 transition-all z-[210]">
                        <XMarkIcon class="h-8 w-8" />
                    </button>
                    <ClientTicketDetail :ticket="selectedTicket" :stages="all_stages" />
                </div>
            </div>
        </transition>
        <!-- High-Fidelity Knowledge Vault Overlay -->
        <KnowledgeVault :show="showVault" @close="showVault = false" />
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { 
    CpuChipIcon, 
    ArrowRightOnRectangleIcon, 
    CheckBadgeIcon,
    ArrowUpRightIcon,
    ShieldCheckIcon,
    ChatBubbleLeftRightIcon,
    PresentationChartLineIcon,
    BookOpenIcon,
    XMarkIcon,
    BoltIcon,
    ChartBarIcon,
    ArrowDownTrayIcon
} from '@heroicons/vue/24/outline';
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { computed, ref, onMounted } from 'vue';
import dayjs from 'dayjs';

// Components I built in previous tasks
import ClientTicketWizard from '@/Pages/Project/BugTracker/Components/ClientTicketWizard.vue';
import ClientTicketDetail from '@/Pages/Project/BugTracker/Components/ClientTicketDetail.vue';
import LivePulseTicker from '@/Pages/Project/BugTracker/Components/LivePulseTicker.vue';
import KnowledgeVault from '@/Pages/Project/BugTracker/Components/KnowledgeVault.vue';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    stats: Object,
    awaiting_verification: Array,
    recent_bugs: Array,
    all_stages: Array // Make sure to pass this from controller
});

const showWizard = ref(false);
const showVault = ref(false);
const selectedTicket = ref(null);
const activeIcon = ref(ShieldCheckIcon);

const summaryStats = computed(() => [
    { label: 'Signal Mass', value: props.recent_bugs.length },
    { label: 'Active Triage', value: props.recent_bugs.filter(b => !b.stage?.is_final).length },
    { label: 'Integrity', value: 'Optimal' }
]);

const chartData = computed(() => {
    return {
        labels: props.stats.by_module.map(m => m.module?.name || 'General'),
        datasets: [{
            backgroundColor: ['#10b981', '#6366f1', '#f59e0b', '#06b6d4', '#f43f5e'],
            borderWidth: 8,
            borderColor: '#ffffff',
            hoverOffset: 20,
            borderRadius: 10,
            data: props.stats.by_module.map(m => m.total)
        }]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '80%',
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#0f172a',
            cornerRadius: 16,
            padding: 18,
            titleFont: { weight: 'bold', size: 14 }
        }
    }
};

const statusTheme = (stage) => {
    if (stage?.is_final) return 'bg-emerald-100/50 text-emerald-600 border border-emerald-200/50';
    if (stage?.name?.toLowerCase().includes('progress')) return 'bg-indigo-100/50 text-indigo-600 border border-indigo-200/50';
    return 'bg-amber-100/50 text-amber-600 border border-amber-200/50';
};

const handleSignalCreated = () => {
    showWizard.value = false;
    router.reload();
};

const verify = (id, action) => {
    router.post(route('portal.tickets.verify', id), { action });
};

const exportReport = () => {
    alert("Exporting Operational Log... CSV format preparation in progress.");
    // In real app: window.location = route('portal.export.bugs');
};

onMounted(() => {
    // Icon cycle decoration - slightly safer
    const icons = [ShieldCheckIcon, PresentationChartLineIcon]; // Removed potentially missing icons
    let i = 0;
    setInterval(() => {
        i = (i + 1) % icons.length;
        activeIcon.value = icons[i];
    }, 3000);
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
.font-inter { font-family: 'Inter', sans-serif; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-up-enter-active, .slide-up-leave-active { transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.8s; }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(100%); opacity: 0; }

.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
