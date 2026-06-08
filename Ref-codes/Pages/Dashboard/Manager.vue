<template>
    <Head title="Manager Dashboard" />
    <div class="bg-[#f4f5fa] pb-16">
        <GradientHeroHeader
            kicker="Manager"
            title="Squad Ops"
            subtitle="Team velocity, attendance, and operational intelligence in one view."
        >
            <template #right>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[170px]">
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Squad Velocity</p>
                        <p class="text-3xl font-extrabold text-white leading-none">{{ team_performance.velocity }}%</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[170px]">
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Completion</p>
                        <p class="text-3xl font-extrabold text-white leading-none">{{ overview.completion_pct || 0 }}%</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[170px]">
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Presence</p>
                        <p class="text-3xl font-extrabold text-white leading-none">{{ presentCount }}/{{ teamCount }}</p>
                    </div>
                </div>
            </template>
        </GradientHeroHeader>

        <div class="mx-0 sm:mx-6 mt-5">

        <section class="px-8 mb-10 space-y-6">
            <div class="rounded-3xl border border-indigo-100 bg-gradient-to-r from-indigo-50 via-white to-cyan-50 p-5 lg:p-6 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-700">Ops + 360 Launcher</p>
                        <h2 class="text-xl font-black text-slate-900 mt-1">Employee 360, Squad_Ops, Team DevOps</h2>
                        <p class="text-xs font-semibold text-slate-500 mt-1">Quick jump panel embedded into Manager command view.</p>
                    </div>
                    <a :href="opsLaunchers.ops360_url" class="inline-flex items-center justify-center h-10 px-4 rounded-xl bg-slate-900 text-white text-xs font-black uppercase tracking-wider hover:bg-slate-800 transition-colors">
                        Open Ops360
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4">
                    <a :href="opsLaunchers.employee360_url" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 hover:border-indigo-300 transition-colors">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Employee 360</p>
                        <p class="text-sm font-bold text-slate-800 mt-2">People signals and compliance overview</p>
                    </a>

                    <a v-if="false" :href="opsLaunchers.devops_global_url" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 hover:border-emerald-300 transition-colors">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Squad_Ops Global</p>
                        <p class="text-sm font-bold text-slate-800 mt-2">Delivery pulse and release risk board</p>
                    </a>

                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Project DevOps</p>
                        <div class="mt-2 space-y-1">
                            <a :href="opsLaunchers.team_board_url" class="block text-xs font-bold text-cyan-700 hover:text-cyan-800 truncate">Open team work board</a>
                            <a :href="opsLaunchers.attendance_url" class="block text-xs font-bold text-cyan-700 hover:text-cyan-800 truncate">Open attendance command</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">People Health (Employee 360)</p>
                    <p class="text-sm font-bold text-slate-700 mt-2">Attendance {{ opsCards.employee360.attendance_score }}% | Productivity {{ opsCards.employee360.productivity_score }}%</p>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-indigo-100 text-indigo-700 border border-indigo-200">Team {{ teamCount }}</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-slate-100 text-slate-700 border border-slate-200">Present {{ presentCount }}</span>
                    </div>
                    <div class="mt-4 space-y-2">
                        <p v-for="(flag, idx) in opsCards.employee360.compliance_flags" :key="`compliance-${idx}`" class="text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                            {{ flag }}
                        </p>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Delivery Health (Squad_Ops)</p>
                    <p class="text-sm font-bold text-slate-700 mt-2">PR Throughput {{ opsCards.squad_ops.pr_throughput }}/wk | Review Lag {{ opsCards.squad_ops.review_lag_hours }}h</p>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-cyan-100 text-cyan-700 border border-cyan-200">Repos {{ opsCards.squad_ops.repos_linked }}</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-slate-100 text-slate-700 border border-slate-200">Open PRs {{ opsCards.squad_ops.open_prs }}</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wide" :class="opsRiskBadgeClass(opsCards.squad_ops.deployment_risk)">{{ opsCards.squad_ops.deployment_risk }} Risk</span>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 lg:p-6 shadow-sm space-y-5">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Live Team Scoreboard</p>
                        <h3 class="text-lg font-black text-slate-900 mt-1">Accurate Today Snapshot</h3>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider" :class="trackStatusClass(overview.track_status)">
                        {{ overview.track_status || 'On Track' }}
                    </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Projects</p>
                        <p class="text-xl font-black text-slate-900 mt-1">{{ overview.total_projects || 0 }}</p>
                        <p class="text-[10px] font-bold text-slate-500 mt-1">Active {{ overview.active_projects || 0 }} | Inactive {{ overview.inactive_projects || 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Teams</p>
                        <p class="text-xl font-black text-slate-900 mt-1">{{ overview.total_teams || 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Members</p>
                        <p class="text-xl font-black text-slate-900 mt-1">{{ overview.total_members || 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-emerald-50 p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-emerald-700">Present</p>
                        <p class="text-xl font-black text-emerald-900 mt-1">{{ overview.present_today || 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-cyan-50 p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-cyan-700">Timesheet Filled</p>
                        <p class="text-xl font-black text-cyan-900 mt-1">{{ overview.timesheet_filled_today || 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-indigo-50 p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-indigo-700">Tasks Completed</p>
                        <p class="text-xl font-black text-indigo-900 mt-1">{{ overview.tasks_completed || 0 }} / {{ overview.tasks_total || 0 }}</p>
                        <p class="text-[10px] font-bold text-slate-500 mt-1">All Projects: Active {{ overview.tasks_active_all_projects || 0 }} | Inactive {{ overview.tasks_inactive_all_projects || 0 }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Approvals Raised</p>
                        <p class="text-lg font-black text-slate-900 mt-1">{{ overview.approvals_raised_today || 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Pending</p>
                        <p class="text-lg font-black text-amber-700 mt-1">{{ overview.approvals_pending || 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Checked</p>
                        <p class="text-lg font-black text-emerald-700 mt-1">{{ overview.approvals_checked || 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Not Checked</p>
                        <p class="text-lg font-black text-rose-700 mt-1">{{ overview.approvals_unchecked || 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Not As Planned</p>
                        <p class="text-lg font-black text-rose-700 mt-1">{{ overview.not_working_as_planned || 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Leave Applied</p>
                        <p class="text-lg font-black text-slate-900 mt-1">{{ overview.leave_applied_today || 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">WFH Applied</p>
                        <p class="text-lg font-black text-slate-900 mt-1">{{ overview.wfh_applied_today || 0 }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="rounded-2xl border border-slate-200 bg-rose-50 p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-rose-700">Bugs Open</p>
                        <p class="text-lg font-black text-rose-900 mt-1">{{ overview.bugs_open_total || 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-emerald-50 p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-emerald-700">Bugs Closed Today</p>
                        <p class="text-lg font-black text-emerald-900 mt-1">{{ overview.bugs_closed_today || 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-amber-50 p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-amber-700">Bugs Pending</p>
                        <p class="text-lg font-black text-amber-900 mt-1">{{ overview.bugs_pending_total || 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Bugs Total</p>
                        <p class="text-lg font-black text-slate-900 mt-1">{{ overview.bugs_total_count || 0 }}</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-3">Last 7 Days Team Ops Trend</p>
                    <div class="grid grid-cols-7 gap-2 h-44 items-end">
                        <div v-for="day in dailyOpsSeries" :key="`daily-ops-${day.date}`" class="flex flex-col items-center justify-end gap-1">
                            <div class="w-full grid grid-cols-4 gap-1 items-end">
                                <div class="rounded-t bg-emerald-500" :style="{ height: `${dailyOpsBarHeight(day.present)}px`, minHeight: '4px' }" title="Present"></div>
                                <div class="rounded-t bg-cyan-500" :style="{ height: `${dailyOpsBarHeight(day.timesheet_filled)}px`, minHeight: '4px' }" title="Timesheet"></div>
                                <div class="rounded-t bg-indigo-500" :style="{ height: `${dailyOpsBarHeight(day.tasks_completed)}px`, minHeight: '4px' }" title="Tasks Completed"></div>
                                <div class="rounded-t bg-amber-500" :style="{ height: `${dailyOpsBarHeight(day.approvals_raised)}px`, minHeight: '4px' }" title="Approvals"></div>
                            </div>
                            <p class="text-[10px] font-black text-slate-500">{{ day.label }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 lg:p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Team Insights</p>
                        <h3 class="text-lg font-black text-slate-900 mt-1">People + Delivery Trend Pulse</h3>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-5">
                    <div class="rounded-2xl border border-indigo-100 bg-indigo-50/40 p-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-700 mb-3">Employee 360 Weekly Trend</p>
                        <div class="h-36 flex items-end gap-2">
                            <div v-for="(point, idx) in employeeTrendSeries" :key="`emp-trend-${idx}`" class="flex-1 flex flex-col items-center gap-2">
                                <div class="w-full rounded-t-md bg-indigo-500" :style="{ height: `${employeeTrendBarHeight(point.hours)}%` }"></div>
                                <span class="text-[10px] font-black text-slate-500">{{ point.label }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/40 p-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-700 mb-3">Squad_Ops Weekly Pulse</p>
                        <div class="h-36 flex items-end gap-2">
                            <div v-for="(point, idx) in devopsTrendSeries" :key="`ops-trend-${idx}`" class="flex-1 flex flex-col items-center gap-2">
                                <div class="w-full rounded-t-md bg-emerald-500" :style="{ height: `${devopsTrendBarHeight(point.value)}%` }"></div>
                                <span class="text-[10px] font-black text-slate-500">{{ point.label }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Advanced Manager Grid -->
        <div class="px-8 grid grid-cols-12 gap-10">
            <!-- Left Col: Strategic Timelines -->
            <div class="col-span-12 lg:col-span-4 space-y-10">
                <GlassCard class="h-[500px]" accent accentColor="bg-emerald-500">
                    <IntelligencePulse 
                        subtitle="Operational Stream"
                        :metrics="[
                            { label: 'Active Tasks', value: overview.tasks_total || 0, growth: (overview.tasks_completed || 0) + ' done', icon: CheckIcon },
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
                            <span class="text-5xl font-black text-slate-900 tracking-tighter">{{ overview.presence_pct || 0 }}</span>
                            <span class="text-sm font-black text-emerald-600 uppercase tracking-widest ml-3">PRESENCE_%</span>
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
                           { label: 'Tasks Done', value: (overview.tasks_completed || 0) + '/' + (overview.tasks_total || 0), trend: 22.5 }
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
                                        <div class="h-full bg-emerald-500 transition-all" :style="{ width: `${(att.count / safeTeamCount) * 100}%` }"></div>
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
                                 <div v-for="i in 16" :key="i" class="w-8 h-8 rounded-lg shadow-sm transition-all hover:scale-125" :class="i <= activeSkillCells ? 'bg-emerald-500' : 'bg-slate-200'"></div>
                             </div>
                         </div>
                    </GlassCard>
                </div>
            </div>
        </div>
        </div>
    </div>
</template>

<script setup>
import { 
    UsersIcon, CheckIcon, AlertCircleIcon, ActivityIcon, RadarIcon 
} from 'lucide-vue-next';
import { Head } from '@inertiajs/vue3';
import GlassCard from '@/Components/Common/GlassCard.vue';
import IntelligencePulse from '@/Components/Dashboard/Advanced/IntelligencePulse.vue';
import AdvancedAnalytics from '@/Components/Dashboard/Advanced/AdvancedAnalytics.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { computed } from 'vue';
import GradientHeroHeader from '@/Components/UI/GradientHeroHeader.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    teamCount: Number,
    teamAttendance: Array,
    team_performance: Object,
    upcomingDeadlines: Array,
    ops_launchers: Object,
    ops_cards: Object,
    ops_overview: Object,
    ops_graphs: Object,
    team_insights: Object,
});

const presentCount = computed(() => {
    return props.teamAttendance.find(a => a.status === 'Present')?.count || 0;
});

const safeTeamCount = computed(() => (props.teamCount > 0 ? props.teamCount : 1));

const opsLaunchers = computed(() => props.ops_launchers || {});
const opsCards = computed(() => props.ops_cards || { employee360: { compliance_flags: [] }, squad_ops: {} });
const overview = computed(() => props.ops_overview || {});
const dailyOpsSeries = computed(() => props.ops_graphs?.daily_ops || []);
const employeeTrendSeries = computed(() => props.team_insights?.employee360_trend || []);
const devopsTrendSeries = computed(() => []);

const maxEmployeeTrendHours = computed(() => {
    const maxValue = Math.max(...employeeTrendSeries.value.map((item) => item.hours), 1);
    return maxValue;
});

const maxDevOpsTrendValues = computed(() => {
    const maxValue = Math.max(...devopsTrendSeries.value.map((item) => item.value), 1);
    return maxValue;
});

const maxDailyOpsValue = computed(() => {
    const values = dailyOpsSeries.value.flatMap((day) => [
        Number(day.present || 0),
        Number(day.timesheet_filled || 0),
        Number(day.tasks_completed || 0),
        Number(day.approvals_raised || 0),
    ]);
    return Math.max(1, ...values);
});

const employeeTrendBarHeight = (hours) => {
    return Math.max(8, Math.round((Number(hours || 0) / maxEmployeeTrendHours.value) * 100));
};

const devopsTrendBarHeight = (value) => {
    return Math.max(8, Math.round((Number(value || 0) / maxDevOpsTrendValues.value) * 100));
};

const dailyOpsBarHeight = (value) => {
    return Math.max(4, Math.round((Number(value || 0) / maxDailyOpsValue.value) * 120));
};

const activeSkillCells = computed(() => {
    const pct = Number(overview.value.completion_pct || 0);
    return Math.max(0, Math.min(16, Math.round((pct / 100) * 16)));
});

const opsRiskBadgeClass = (risk) => {
    if (risk === 'High') {
        return 'bg-rose-100 text-rose-700 border border-rose-200';
    }
    if (risk === 'Medium') {
        return 'bg-amber-100 text-amber-700 border border-amber-200';
    }
    return 'bg-emerald-100 text-emerald-700 border border-emerald-200';
};

const trackStatusClass = (status) => {
    if (status === 'Off Track') {
        return 'bg-rose-100 text-rose-700 border border-rose-200';
    }
    if (status === 'At Risk') {
        return 'bg-amber-100 text-amber-700 border border-amber-200';
    }
    return 'bg-emerald-100 text-emerald-700 border border-emerald-200';
};
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.8s ease-out forwards; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
.animate-pulse-slow { animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
</style>
