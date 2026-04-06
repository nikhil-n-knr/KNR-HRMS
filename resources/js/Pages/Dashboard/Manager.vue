                                                                                                                                            <template>
    <div class="min-h-screen pb-24 relative overflow-hidden bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-blue-50/50 via-white to-slate-50/50">
        <!-- Dashboard Header: Squad Control HUD -->
        <header class="mb-14 px-8 pt-6 flex flex-col md:flex-row md:items-center justify-between gap-10">
            <div class="space-y-4">
                <div class="flex items-center gap-3 animate-fade-in">
                    <div class="h-10 w-10 bg-slate-900 rounded-2xl flex items-center justify-center shadow-2xl shadow-slate-900/30">
                        <UsersIcon class="w-6 h-6 text-emerald-500 animate-pulse-slow" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 bg-slate-900/10 text-slate-900 text-xs font-black rounded-full uppercase tracking-[0.2em] border border-slate-900/20">Active_Operations</span>
                            <span class="text-slate-400 text-xs font-bold uppercase tracking-widest pl-2 border-l border-slate-200">Team Control v4.0</span>
                        </div>
                        <h1 class="text-4xl font-black text-slate-900 tracking-tighter flex items-center gap-4">
                            Squad_Ops
                            <span class="text-emerald-600 font-mono text-sm tracking-tighter bg-emerald-500/10 px-3 py-1 rounded-xl border border-emerald-500/20 shadow-sm">L1_COMMAND</span>
                        </h1>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                 <div class="p-4 bg-white/60 border border-white/80 rounded-[2rem] flex items-center gap-12 shadow-2xl shadow-black/5 backdrop-blur-3xl group transition-all hover:bg-white/80">
                    <div class="flex flex-col border-r border-slate-200/60 pr-12">
                        <span class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1 group-hover:text-emerald-600 transition">Squad Velocity</span>
                        <div class="flex items-baseline gap-2">
                             <span class="text-3xl font-black text-slate-900 tracking-tighter leading-none">{{ team_performance.velocity }}%</span>
                             <span class="text-sm font-black text-emerald-600 bg-emerald-500/10 px-1.5 py-0.5 rounded shadow-sm">+12.2%</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-black text-slate-400 tracking-widest uppercase mb-1">Squad Presence</span>
                        <div class="flex items-center gap-3">
                             <span class="text-3xl font-black text-slate-900 tracking-tighter leading-none">{{ presentCount }}/{{ teamCount }}</span>
                             <div class="flex gap-0.5">
                                 <div v-for="i in 5" :key="i" class="w-1 h-4 bg-emerald-500 rounded-full animate-pulse" :style="{ animationDelay: `${i*150}ms` }"></div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Advanced Manager Grid -->
        <div class="px-8 grid grid-cols-12 gap-10">
            <!-- Left Col: Strategic Timelines -->
            <div class="col-span-12 lg:col-span-4 space-y-10">
                <GlassCard class="h-[500px]" accent accentColor="bg-emerald-500">
                    <IntelligencePulse 
                        subtitle="Operational Stream"
                        :metrics="[
                            { label: 'Active Tasks', value: 24, growth: '+5', icon: CheckIcon },
                            { label: 'Cloud Incidents', value: team_performance.active_incidents, growth: 'Minimal', icon: AlertCircleIcon },
                        ]"
                        :events="upcomingDeadlines.map(d => ({
                            title: d.title,
                            time: d.date,
                            description: `Priority: ${d.priority.toUpperCase()} - Requires squad sync by EOD.`
                        }))"
                    />
                </GlassCard>

                <GlassCard class="h-[300px]" accent accentColor="bg-slate-900">
                    <div class="space-y-6">
                        <h4 class="text-sm font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-2">
                            <ActivityIcon class="w-4 h-4 text-slate-900" />
                            Workforce_Peak_Index
                        </h4>
                        <div class="p-4 bg-slate-900/5 rounded-3xl border border-slate-900/10 flex items-center justify-center py-10">
                            <span class="text-5xl font-black text-slate-900 tracking-tighter">88.4</span>
                            <span class="text-sm font-black text-emerald-600 uppercase tracking-widest ml-3">HIGH_FLOW</span>
                        </div>
                        <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                             <div class="h-full bg-slate-900 transition-all duration-1000" :style="{ width: `${team_performance.sprint_completion}%` }"></div>
                        </div>
                    </div>
                </GlassCard>
            </div>

            <!-- Right Col: Sprint Analytics & Team Distribution -->
            <div class="col-span-12 lg:col-span-8 space-y-10">
                 <GlassCard class="flex-1 h-[450px]" accent accentColor="bg-blue-500">
                    <AdvancedAnalytics 
                       title="Squad_Agility_Audit"
                       subtitle="12-Week Sprint Velocity Matrix"
                       :chartData="team_performance.weekly_engagement"
                       themeColor="#10b981"
                       :metrics="[
                           { label: 'Avg Velocity', value: team_performance.velocity + '%', trend: 14.5 },
                           { label: 'Sprint Comp', value: team_performance.sprint_completion + '%', trend: -2.1 },
                           { label: 'Blockers Resolved', value: '42', trend: 22.5 }
                       ]"
                    />
                </GlassCard>

                <div class="grid grid-cols-2 gap-10">
                     <GlassCard class="h-[350px]">
                        <div class="space-y-6">
                            <h4 class="text-sm font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-2">
                                <RadarIcon class="w-4 h-4 text-emerald-600" />
                                Squad_Presence_Map
                            </h4>
                            <div class="space-y-4">
                                <div v-for="att in teamAttendance" :key="att.status" class="flex flex-col gap-1">
                                    <div class="flex justify-between text-sm font-black uppercase text-slate-500 tracking-widest">
                                        <span>{{ att.status }}</span>
                                        <span class="text-slate-900 font-mono">{{ att.count }}</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-emerald-500 transition-all" :style="{ width: `${(att.count / teamCount) * 100}%` }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </GlassCard>

                    <GlassCard class="h-[350px]" accent accentColor="bg-sky-500">
                        <header class="mb-6">
                            <h4 class="text-sm font-black text-slate-900 uppercase tracking-[0.2em]">Skill_Matrix_Density</h4>
                        </header>
                         <div class="flex-1 flex items-center justify-center py-10 relative">
                             <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-emerald-500/5 blur-3xl p-10"></div>
                             <div class="z-10 grid grid-cols-4 gap-2">
                                 <div v-for="i in 16" :key="i" class="w-8 h-8 rounded-lg shadow-sm transition-all hover:scale-125" :class="Math.random() > 0.3 ? 'bg-emerald-500' : 'bg-slate-200'"></div>
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
    UsersIcon, CheckIcon, AlertCircleIcon, ActivityIcon, RadarIcon 
} from 'lucide-vue-next';
import GlassCard from '@/Components/Common/GlassCard.vue';
import IntelligencePulse from '@/Components/Dashboard/Advanced/IntelligencePulse.vue';
import AdvancedAnalytics from '@/Components/Dashboard/Advanced/AdvancedAnalytics.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { computed } from 'vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    teamCount: Number,
    teamAttendance: Array,
    team_performance: Object,
    upcomingDeadlines: Array
});

const presentCount = computed(() => {
    return props.teamAttendance.find(a => a.status === 'Present')?.count || 0;
});
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.8s ease-out forwards; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
.animate-pulse-slow { animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
</style>
