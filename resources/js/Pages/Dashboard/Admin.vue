<template>
    <div class="min-h-screen pb-24 relative overflow-hidden bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-blue-50/50 via-white to-emerald-50/50">
        <!-- Dashboard Header: Cyber HUD -->
        <header class="mb-14 px-8 pt-6 flex flex-col md:flex-row md:items-center justify-between gap-10">
            <div class="space-y-4">
                <div class="flex items-center gap-3 animate-fade-in">
                    <div class="h-10 w-10 bg-slate-900 rounded-2xl flex items-center justify-center shadow-2xl shadow-slate-900/30">
                        <CommandIcon class="w-6 h-6 text-emerald-500 animate-pulse-slow" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 bg-slate-900/10 text-slate-900 text-xs font-black rounded-full uppercase tracking-[0.2em] border border-slate-900/20">Operational_L1</span>
                            <span class="text-slate-400 text-xs font-bold uppercase tracking-widest pl-2 border-l border-slate-200">Session_Active: 142ms</span>
                        </div>
                        <h1 class="text-4xl font-black text-slate-900 tracking-tighter flex items-center gap-4">
                            System_Control
                            <span class="text-emerald-600 font-mono text-sm tracking-tighter bg-emerald-500/10 px-3 py-1 rounded-xl border border-emerald-500/20 shadow-sm">v.42.0-Alpha</span>
                        </h1>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <!-- Advanced Metrics Hub -->
                <div class="p-4 bg-white/60 border border-white/80 rounded-[2rem] flex items-center gap-12 shadow-2xl shadow-black/5 backdrop-blur-3xl group transition-all hover:bg-white/80">
                    <div class="flex flex-col border-r border-slate-200/60 pr-12">
                        <span class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1 group-hover:text-emerald-600 transition">Global Users</span>
                        <div class="flex items-baseline gap-2">
                             <span class="text-3xl font-black text-slate-900 tracking-tighter leading-none">{{ totalUsers }}</span>
                             <span class="text-sm font-black text-emerald-600 bg-emerald-500/10 px-1.5 py-0.5 rounded shadow-sm">+8.2%</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-black text-slate-400 tracking-widest uppercase mb-1">Service Integrity</span>
                        <div class="flex items-center gap-3">
                             <span class="text-3xl font-black text-slate-900 tracking-tighter leading-none">{{ system_health.uptime }}</span>
                             <div class="flex gap-0.5">
                                 <div v-for="i in 5" :key="i" class="w-1 h-4 bg-emerald-500 rounded-full animate-pulse" :style="{ animationDelay: `${i*150}ms` }"></div>
                             </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <button class="h-14 w-14 bg-slate-900 text-white rounded-[1.5rem] flex items-center justify-center hover:bg-emerald-600 transition-all shadow-xl shadow-slate-900/20 group active:scale-95">
                        <SettingsIcon class="w-6 h-6 group-hover:rotate-45 transition-transform" />
                    </button>
                </div>
            </div>
        </header>

        <!-- Advanced Admin Grid -->
        <div class="px-8 grid grid-cols-12 gap-10">
            <!-- Left Col: Intelligence Pulse -->
            <div class="col-span-12 lg:col-span-4 space-y-10">
                <GlassCard class="h-[500px]" accent accentColor="bg-blue-500">
                    <div class="flex-1">
                        <IntelligencePulse 
                            subtitle="Infrastructure Stream"
                            :metrics="[
                                { label: 'Node Clusters', value: system_health.node_active, growth: 'Stable', icon: ServerIcon },
                                { label: 'Active Projects', value: totalProjects, growth: '+2', icon: BoxIcon },
                            ]"
                            :events="[
                                { title: 'User Scaling Out', time: '8m ago', description: 'Instance_04 successfully replicated to Region_West_02.' },
                                { title: 'Security Pass', time: '1h ago', description: 'Monthly vulnerability scan completed with 100% hygiene.' },
                                { title: 'Tenant Sync', time: '3h ago', description: 'Global data consistency audit matched all shards.' },
                            ]"
                        />
                    </div>
                </GlassCard>

                <GlassCard class="h-[300px]" accent accentColor="bg-amber-500">
                    <div class="space-y-6">
                        <header>
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tighter">Node_Telemetry</h3>
                            <p class="text-xs text-slate-500 font-bold tracking-widest">Active Packet Stream</p>
                        </header>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-slate-900/5 rounded-3xl border border-slate-900/10">
                                <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1 text-center">Avg Latency</p>
                                <p class="text-2xl font-black text-slate-900 text-center tracking-tighter">{{ nodeTelemetry.avg_latency }}</p>
                            </div>
                            <div class="p-4 bg-slate-900/5 rounded-3xl border border-slate-900/10">
                                <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1 text-center">Load Index</p>
                                <p class="text-2xl font-black text-slate-900 text-center tracking-tighter">{{ system_health.cpu }}%</p>
                            </div>
                        </div>
                        <div class="h-2 w-full bg-slate-200 rounded-full overflow-hidden">
                             <div class="h-full bg-emerald-500 transition-all duration-1000" :style="{ width: `${system_health.memory}%` }"></div>
                        </div>
                    </div>
                </GlassCard>
            </div>

            <!-- Right Col: Throughput & Analytics -->
            <div class="col-span-12 lg:col-span-8 space-y-10">
                <GlassCard class="flex-1 h-[450px]" accent accentColor="bg-emerald-500">
                    <AdvancedAnalytics 
                       title="Global_Throughput"
                       subtitle="12-Hour Activity Matrix"
                       :chartData="nodeTelemetry.load_trend"
                       themeColor="#10b981"
                       :metrics="[
                           { label: 'Cloud Load', value: '42.4 GB/s', trend: 12.8 },
                           { label: 'Active Sessions', value: totalUsers, trend: 4.2 },
                           { label: 'DB Requests', value: '1.2M', trend: 22.5 }
                       ]"
                    />
                </GlassCard>

                <div class="grid grid-cols-2 gap-10">
                    <GlassCard class="h-[350px]">
                        <div class="space-y-6">
                            <h4 class="text-sm font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-2">
                                <UsersIcon class="w-4 h-4 text-emerald-600" />
                                Portfolio_Distribution
                            </h4>
                            <div class="space-y-4">
                                <div v-for="stat in projectStats" :key="stat.status" class="flex flex-col gap-1">
                                    <div class="flex justify-between text-sm font-black uppercase text-slate-500 tracking-widest">
                                        <span>{{ stat.status }}</span>
                                        <span class="text-slate-900 font-mono">{{ stat.count }}</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-slate-900 transition-all hover:bg-emerald-500" :style="{ width: `${(stat.count / totalProjects) * 100}%` }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </GlassCard>

                    <GlassCard class="h-[350px]" accent accentColor="bg-purple-500">
                        <div class="space-y-6">
                            <h4 class="text-sm font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-2">
                                <ZapIcon class="w-4 h-4 text-amber-500" />
                                Mission_Velocity
                            </h4>
                            <div class="flex-1 flex items-center justify-center relative py-10">
                                <div class="w-32 h-32 border-8 border-slate-900 rounded-full flex items-center justify-center animate-spin-slow">
                                    <div class="absolute inset-0 border-8 border-emerald-500 rounded-full border-t-transparent animate-reverse"></div>
                                </div>
                                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-center pointer-events-none">
                                    <span class="text-3xl font-black text-slate-900 tracking-tighter">84.2</span>
                                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Efficiency</p>
                                </div>
                            </div>
                        </div>
                    </GlassCard>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { 
    CommandIcon, SettingsIcon, ServerIcon, BoxIcon, ZapIcon, UsersIcon, ShieldCheckIcon 
} from 'lucide-vue-next';
import GlassCard from '@/Components/Common/GlassCard.vue';
import IntelligencePulse from '@/Components/Dashboard/Advanced/IntelligencePulse.vue';
import AdvancedAnalytics from '@/Components/Dashboard/Advanced/AdvancedAnalytics.vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

defineProps({
    totalUsers: Number,
    totalProjects: Number,
    totalClients: Number,
    activeTenants: Number,
    tenantUsage: Array,
    projectStats: Array,
    system_health: Object,
    nodeTelemetry: Object
});
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.8s ease-out forwards; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
.animate-spin-slow { animation: spin 8s linear infinite; }
.animate-reverse { animation: spin 4s linear infinite reverse; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.animate-pulse-slow { animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
</style>
