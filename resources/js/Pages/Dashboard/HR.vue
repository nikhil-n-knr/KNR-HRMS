<template>
    <div class="min-h-screen pb-24 relative overflow-hidden bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-50/50 via-white to-rose-50/50">
        <!-- Dashboard Header: People Ops HUD -->
        <header class="mb-14 px-8 pt-6 flex flex-col md:flex-row md:items-center justify-between gap-10">
            <div class="space-y-4">
                <div class="flex items-center gap-3 animate-fade-in">
                    <div class="h-10 w-10 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-2xl shadow-indigo-600/30">
                        <UsersIcon class="w-6 h-6 text-white animate-pulse-slow" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 bg-indigo-500/10 text-indigo-600 text-xs font-black rounded-full uppercase tracking-[0.2em] border border-indigo-500/20">HR_COMMAND_L2</span>
                            <span class="text-slate-400 text-xs font-bold uppercase tracking-widest pl-2 border-l border-slate-200">Fiscal Q1 Domain</span>
                        </div>
                        <h1 class="text-4xl font-black text-slate-900 tracking-tighter flex items-center gap-4">
                            Talent_Command
                            <span class="text-indigo-600 font-mono text-sm tracking-tighter bg-indigo-500/10 px-3 py-1 rounded-xl border border-indigo-500/20 shadow-sm">EXE_L2</span>
                        </h1>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="p-4 bg-white/60 border border-white/80 rounded-[2rem] flex items-center gap-12 shadow-2xl shadow-black/5 backdrop-blur-3xl group transition-all hover:bg-white/80">
                    <div class="flex flex-col border-r border-slate-200/60 pr-12">
                        <span class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1 group-hover:text-indigo-600 transition">Total Workforce</span>
                        <div class="flex items-baseline gap-2">
                             <span class="text-3xl font-black text-slate-900 tracking-tighter leading-none">{{ totalEmployees }}</span>
                             <span class="text-sm font-black text-emerald-600 bg-emerald-500/10 px-1.5 py-0.5 rounded shadow-sm">{{ analytics.culture_growth }}%</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-black text-slate-400 tracking-widest uppercase mb-1">Retention Risk</span>
                        <div class="flex items-center gap-3">
                             <span class="text-3xl font-black text-rose-500 tracking-tighter leading-none">{{ analytics.attrition_risk }}%</span>
                             <span class="text-sm font-black text-slate-400 opacity-50 uppercase tracking-widest">MINIMAL</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Advanced HR Grid -->
        <div class="px-8 grid grid-cols-12 gap-10">
            <!-- Left Col: Talent Intelligence -->
            <div class="col-span-12 lg:col-span-4 space-y-10">
                <GlassCard class="h-[500px]" accent accentColor="bg-indigo-500">
                    <IntelligencePulse 
                        subtitle="Talent Operational Pulse"
                        :metrics="[
                            { label: 'Absent Today', value: absentToday, growth: 'Stable', icon: UserXIcon },
                            { label: 'Pending Appr', value: pendingHRLeaves, growth: 'High', icon: ClipboardListIcon },
                        ]"
                        :events="upcomingBirthdays.length ? upcomingBirthdays.map(b => ({
                            title: 'Birthday Alert',
                            time: 'Incoming',
                            description: `${b.first_name || 'Team member'} from ${b.department?.name || 'Org'} is celebrating soon.`
                        })) : [
                            { title: 'Training Milestone', time: '2h ago', description: 'Global compliance training at 92% completion.' },
                            { title: 'Contract Renewal', time: '5h ago', description: 'Employee #44 contract updated for Fiscal Q2.' }
                        ]"
                    />
                </GlassCard>

                <div class="grid grid-cols-1 gap-10">
                    <GlassCard class="h-[300px]" accent accentColor="bg-rose-500">
                        <div class="space-y-6">
                            <h4 class="text-sm font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-2">
                                <ActivityIcon class="w-4 h-4 text-rose-500" />
                                Wellness_Index
                            </h4>
                            <div class="p-4 bg-rose-500/5 rounded-3xl border border-rose-500/10 flex flex-col items-center justify-center py-10">
                                <span class="text-5xl font-black text-indigo-900 tracking-tighter">{{ analytics.wellness_score }}</span>
                                <span class="text-sm font-black text-slate-400 uppercase tracking-widest mt-2">Optimal Engagement</span>
                            </div>
                            <div class="flex gap-1 justify-center">
                                <div v-for="i in 12" :key="i" class="w-full h-1.5 rounded-full overflow-hidden" :class="[i < 10 ? 'bg-indigo-500' : 'bg-slate-100']"></div>
                            </div>
                        </div>
                    </GlassCard>
                </div>
            </div>

            <!-- Right Col: Hiring & Distribution -->
            <div class="col-span-12 lg:col-span-8 space-y-10">
                <GlassCard class="flex-1 h-[450px]" accent accentColor="bg-sky-500">
                    <AdvancedAnalytics 
                       title="Recruitment_Velocity"
                       subtitle="12-Month Performance Audit"
                       :chartData="analytics.hiring_trend"
                       themeColor="#6366f1"
                       :metrics="[
                           { label: 'Avg Time to Hire', value: '22 Days', trend: -4.5 },
                           { label: 'Offer Acceptance', value: '94%', trend: 1.2 },
                           { label: 'Pipeline Load', value: analytics.hiring_pipeline[0].count, trend: 18.2 }
                       ]"
                    />
                </GlassCard>

                <div class="grid grid-cols-2 gap-10">
                    <GlassCard class="h-[350px]">
                        <div class="space-y-6">
                             <h4 class="text-sm font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-2">
                                <PieChartIcon class="w-4 h-4 text-indigo-600" />
                                Workforce_Distribution
                            </h4>
                            <div class="space-y-4">
                                <div v-for="dept in headcountByDept" :key="dept.name" class="flex flex-col gap-1">
                                    <div class="flex justify-between text-sm font-black uppercase text-slate-500 tracking-widest">
                                        <span>{{ dept.name }}</span>
                                        <span class="text-slate-900 font-mono">{{ dept.count }}</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500 transition-all hover:bg-rose-500" :style="{ width: `${(dept.count / totalEmployees) * 100}%` }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </GlassCard>

                    <GlassCard class="h-[350px]" accent accentColor="bg-indigo-500">
                        <header class="mb-6">
                            <h4 class="text-sm font-black text-slate-900 uppercase tracking-[0.2em]">Hiring_Pipeline</h4>
                        </header>
                        <div class="space-y-4">
                             <div v-for="stage in analytics.hiring_pipeline" :key="stage.stage" class="p-4 bg-indigo-500/5 rounded-2xl border border-indigo-500/10 flex justify-between items-center group transition-colors hover:bg-indigo-500/10">
                                 <div>
                                     <p class="text-sm font-black text-slate-400 uppercase tracking-widest">{{ stage.stage }}</p>
                                     <p class="text-xl font-black text-indigo-900">{{ stage.count }}</p>
                                 </div>
                                 <div class="h-8 w-8 bg-indigo-600/10 rounded-xl flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform">
                                      <ChevronRightIcon class="w-5 h-5" />
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
    UsersIcon, UserXIcon, ClipboardListIcon, ActivityIcon, PieChartIcon, ChevronRightIcon 
} from 'lucide-vue-next';
import GlassCard from '@/Components/Common/GlassCard.vue';
import IntelligencePulse from '@/Components/Dashboard/Advanced/IntelligencePulse.vue';
import AdvancedAnalytics from '@/Components/Dashboard/Advanced/AdvancedAnalytics.vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

defineProps({
    totalEmployees: Number,
    headcountByDept: Array,
    absentToday: Number,
    pendingHRLeaves: Number,
    analytics: Object,
    upcomingBirthdays: Array
});
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.8s ease-out forwards; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
.animate-pulse-slow { animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
</style>
