<script setup>
import { ref, onMounted } from 'vue';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import LiveFeed from '@/Components/Attendance/LiveFeed.vue';
import BulkAttendanceModal from './Components/BulkAttendanceModal.vue';
import { 
    UserGroupIcon, 
    ClockIcon, 
    ExclamationCircleIcon,
    CheckCircleIcon,
    BoltIcon,
    ArrowTrendingUpIcon,
    CpuChipIcon,
    SignalIcon,
    FireIcon,
    ExclamationTriangleIcon,
    ArrowRightIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    embedded: Boolean
});

const stats = ref({
    total_employees: 0,
    present_count: 0,
    late_count: 0,
    absent_count: 0,
    pending_requests: 0
});

const recentPunches = ref([]);
const lateComers = ref([]);
const departments = ref([]);
const showBulkModal = ref(false);
const topStreaks = ref([]);
const sandwichViolations = ref([]);

const fetchDashboardData = async () => {
    try {
        const response = await axios.get(route('admin.attendance.monitoring.stats'));
        stats.value = response.data.stats;
        recentPunches.value = response.data.recent_punches;
        lateComers.value = response.data.late_comers;
        departments.value = response.data.departments;
        topStreaks.value = response.data.top_streaks;
        sandwichViolations.value = response.data.sandwich_violations;
    } catch (error) {
        console.error("Critical Analysis Protocol Failed", error);
    }
};

onMounted(() => {
    fetchDashboardData();
    const timer = setInterval(fetchDashboardData, 30000);
    return () => clearInterval(timer);
});
</script>

<template>
    <component :is="embedded ? 'div' : AttendanceLayout" title="Attendance Dashboard" activeTab="dashboard" v-bind="$props">
        <Head v-if="!embedded" title="Strategic Metrics Interface" />

        <div class="space-y-10 pb-20 font-outfit animate-in fade-in slide-in-from-bottom-5 duration-700">
            <!-- Strategic Intelligence Header -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8 bg-slate-900 rounded-[2.5rem] p-10 shadow-2xl shadow-indigo-500/20 relative overflow-hidden group">
                <div class="absolute -right-16 -top-16 w-64 h-64 bg-indigo-500/10 rounded-full blur-[100px] group-hover:bg-indigo-500/20 transition-all duration-1000"></div>
                
                <div class="flex items-center gap-6 relative z-10">
                    <div class="w-16 h-16 bg-white/10 backdrop-blur-xl rounded-2xl flex items-center justify-center text-indigo-400 border border-white/10 shadow-2xl group-hover:rotate-6 transition-transform">
                        <CpuChipIcon class="w-10 h-10" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-white uppercase tracking-tight flex items-center gap-4">
                            Biometric Intelligence
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-black bg-white/10 text-indigo-400 border border-white/5 uppercase tracking-[0.3em] backdrop-blur-md">Live_Matrix</span>
                        </h1>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] mt-2.5 flex items-center gap-2">
                            <SignalIcon class="w-4 h-4 text-indigo-500 animate-pulse" />
                            Global attendance vector analysis & real-time synchronization
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 relative z-10 w-full lg:w-auto">
                    <button @click="showBulkModal = true" class="flex-1 lg:flex-none h-14 px-10 bg-white text-slate-900 rounded-2xl text-sm font-black uppercase tracking-[0.3em] shadow-2xl hover:bg-emerald-500 hover:text-white transition-all active:scale-95 flex items-center justify-center gap-4 group">
                        <BoltIcon class="w-5 h-5 group-hover:rotate-12 transition-transform" />
                        Bulk Operations
                    </button>
                </div>
            </div>

            <!-- Intelligence Metrics Strip -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="stat in [
                    { label: 'Tactical Presence', val: stats.present_count, sub: `/ ${stats.total_employees}`, icon: UserGroupIcon, color: 'text-emerald-500', bg: 'bg-emerald-500/10 border-emerald-500/20' },
                    { label: 'Latency Drift (Late)', val: stats.late_count, icon: ExclamationTriangleIcon, color: 'text-amber-500', bg: 'bg-amber-500/10 border-amber-500/20' },
                    { label: 'Dormant Nodes (Absent)', val: stats.absent_count, icon: ClockIcon, color: 'text-rose-500', bg: 'bg-rose-500/10 border-rose-500/20' },
                    { label: 'Authorization Log', val: stats.pending_requests, icon: CheckCircleIcon, color: 'text-indigo-500', bg: 'bg-indigo-500/10 border-indigo-500/20' }
                ]" :key="stat.label" class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/40 group hover:scale-[1.02] transition-all relative overflow-hidden">
                    <div class="flex flex-col gap-4 relative z-10">
                        <div :class="[stat.bg, stat.color]" class="w-12 h-12 rounded-2xl border flex items-center justify-center shadow-lg group-hover:rotate-12 transition-transform">
                            <component :is="stat.icon" class="w-6 h-6" />
                        </div>
                        <div>
                            <span class="text-sm font-black text-slate-400 uppercase tracking-widest block mb-1 leading-none">{{ stat.label }}</span>
                            <div class="flex items-baseline gap-2">
                                <span class="text-2xl font-black text-slate-900 tabular-nums leading-none">{{ stat.val }}</span>
                                <span v-if="stat.sub" class="text-sm font-black text-slate-300 uppercase leading-none">{{ stat.sub }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Latency Matrix Table -->
                <div class="lg:col-span-2 bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/40 overflow-hidden flex flex-col group">
                    <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-rose-500/10 border border-rose-500/20 rounded-xl flex items-center justify-center text-rose-500">
                                <ClockIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Latency Detection Matrix</h3>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Real-time arrival variance</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                            <span class="text-sm font-black text-slate-400 uppercase tracking-widest">Active_Probe</span>
                        </div>
                    </div>

                    <div class="flex-1 overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-900 border-b border-slate-800">
                                    <th class="px-8 py-5 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Operative Identifier</th>
                                    <th class="px-8 py-5 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Temporal Pattern</th>
                                    <th class="px-8 py-5 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Arrival UTC</th>
                                    <th class="px-8 py-5 text-right text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Drift (Min)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="emp in lateComers" :key="emp.id" class="group/row hover:bg-slate-50/80 transition-all duration-300">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-white font-black text-sm border-2 border-white shadow-lg group-hover/row:bg-indigo-600 transition-all">
                                                {{ emp.first_name[0] }}
                                            </div>
                                            <div>
                                                <div class="text-base font-black text-slate-900 uppercase tracking-tight group-hover/row:text-indigo-700 transition-colors">{{ emp.first_name }} {{ emp.last_name }}</div>
                                                <div class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1 opacity-60">{{ emp.department?.name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <span class="text-sm font-black text-slate-500 uppercase tracking-widest px-3 py-1 bg-slate-100 rounded-lg">{{ emp.shift_name }}</span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="text-base font-black text-rose-600 font-mono tracking-tighter uppercase tabular-nums">{{ emp.check_in_time }}</div>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-rose-50 text-rose-600 rounded-lg border border-rose-100 shadow-sm transition-transform group-hover/row:scale-110">
                                            <span class="text-base font-black tabular-nums">+{{ emp.late_minutes }}m</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="lateComers.length === 0">
                                    <td colspan="4" class="py-24 text-center grayscale opacity-30">
                                        <CheckCircleIcon class="w-16 h-16 mx-auto text-emerald-500/30 mb-4 animate-pulse" />
                                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em]">Zero latency detected in current cycle</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 bg-slate-50/50 border-t border-slate-100 flex justify-center">
                        <Link href="/admin/attendance/monitoring" class="h-12 px-8 bg-white text-slate-900 rounded-2xl text-sm font-black uppercase tracking-[0.3em] border border-slate-200 hover:border-indigo-600 hover:text-indigo-600 transition-all shadow-sm flex items-center gap-3 active:scale-95 group/btn">
                            Full Analysis Protocol
                            <ArrowRightIcon class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" />
                        </Link>
                    </div>
                </div>

                <!-- Right Intelligence Sidebar -->
                <div class="space-y-8">
                    <!-- Live Pulse Feed -->
                    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/40 overflow-hidden flex flex-col h-[480px] group/pulse">
                        <div class="p-6 bg-slate-900 text-white flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center text-white shadow-lg animate-pulse">
                                    <BoltIcon class="w-5 h-5" />
                                </div>
                                <h3 class="text-base font-black uppercase tracking-[0.2em] italic">Real-time Pulse</h3>
                            </div>
                            <span class="text-xs font-black text-emerald-400 uppercase tracking-widest border border-emerald-500/30 px-2 py-0.5 rounded">Live</span>
                        </div>
                        <div class="flex-1 overflow-y-auto no-scrollbar p-2">
                            <LiveFeed :events="recentPunches" />
                        </div>
                    </div>

                    <!-- Punctuality Streaks -->
                    <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white shadow-2xl shadow-indigo-500/30 relative overflow-hidden group/streaks">
                        <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-white/10 rounded-full blur-3xl transition-transform duration-1000 group-hover/streaks:scale-110"></div>
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-10 h-10 bg-white/10 border border-white/20 rounded-xl flex items-center justify-center text-indigo-200 shadow-xl group-hover/streaks:rotate-12 transition-all">
                                <FireIcon class="w-6 h-6" />
                            </div>
                            <h3 class="text-base font-black uppercase tracking-[0.2em]">Punctual Streaks</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div v-for="(streak, idx) in topStreaks" :key="idx" class="flex items-center justify-between bg-white/5 border border-white/5 backdrop-blur-md p-4 rounded-2xl hover:bg-white/10 transition-all cursor-default group/item">
                                <div class="flex items-center gap-4">
                                    <div class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.8)]" :class="idx === 0 ? 'animate-ping' : ''"></div>
                                    <span class="text-base font-black uppercase tracking-tight">{{ streak.employee?.first_name }}</span>
                                </div>
                                <div class="px-3 py-1 bg-white/10 rounded-lg border border-white/10 text-sm font-black tabular-nums tracking-tighter">
                                    {{ streak.streak }} Cycles
                                </div>
                            </div>
                            <div v-if="topStreaks.length === 0" class="py-12 text-center opacity-30 grayscale italic">
                                <p class="text-sm font-black uppercase tracking-[0.5em]">Calibrating metrics...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sandwich Matrix Alert -->
                    <div v-if="sandwichViolations.length > 0" class="bg-rose-500 rounded-[2.5rem] p-6 text-white shadow-2xl shadow-rose-500/20 animate-in zoom-in duration-500">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center border border-white/20">
                                <ExclamationTriangleIcon class="w-6 h-6" />
                            </div>
                            <h3 class="text-base font-black uppercase tracking-[0.2em]">Sandwich Pulse</h3>
                        </div>
                        <div class="space-y-3">
                            <div v-for="(vio, idx) in sandwichViolations" :key="idx" class="p-4 bg-white/10 border border-white/10 rounded-2xl backdrop-blur-sm">
                                <span class="text-base font-black uppercase block leading-none mb-2">{{ vio.reason }}</span>
                                <span class="text-sm font-black text-rose-200 uppercase tracking-widest tabular-nums italic opacity-80">{{ vio.date }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <BulkAttendanceModal 
            :show="showBulkModal" 
            :departments="departments"
            @close="showBulkModal = false"
            @success="fetchDashboardData"
        />
    </component>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
</style>
