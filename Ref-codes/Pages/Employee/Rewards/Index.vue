<script setup>
import { computed, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import BaseChart from '@/Components/BaseChart.vue';
import { ChevronLeftIcon, SparklesIcon, TrophyIcon, FireIcon, BoltIcon, ArrowTrendingUpIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    employee: Object,
    summary: Object,
    charts: Object,
    badges: Object,
    ledger: Object,
    filters: Object,
    sources: Array,
});

const filterForm = reactive({
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    source: props.filters?.source || 'all',
});

const applyFilters = () => {
    router.get(
        route('employee.rewards.index', { uuid: props.employee?.uuid }),
        {
            date_from: filterForm.date_from,
            date_to: filterForm.date_to,
            source: filterForm.source,
        },
        { preserveScroll: true, preserveState: true }
    );
};

const resetFilters = () => {
    filterForm.source = 'all';
    filterForm.date_from = '';
    filterForm.date_to = '';
    router.get(route('employee.rewards.index', { uuid: props.employee?.uuid }), {}, { preserveScroll: true, preserveState: false });
};

const pointsTrendData = computed(() => ({
    labels: props.charts?.points_trend?.labels || [],
    datasets: [
        {
            label: 'Points',
            data: props.charts?.points_trend?.data || [],
            borderColor: '#0f766e',
            backgroundColor: 'rgba(20, 184, 166, 0.16)',
            fill: true,
            tension: 0.35,
            pointRadius: 2,
            pointHoverRadius: 4,
        },
    ],
}));

const sourceChartData = computed(() => ({
    labels: props.charts?.source_distribution?.labels || [],
    datasets: [
        {
            data: props.charts?.source_distribution?.data || [],
            backgroundColor: ['#0f766e', '#2563eb', '#f59e0b', '#7c3aed', '#db2777', '#475569', '#16a34a'],
            borderWidth: 0,
        },
    ],
}));

const scrumTrendData = computed(() => ({
    labels: props.charts?.scrum_trend?.labels || [],
    datasets: [
        {
            label: 'Scrum Points',
            data: props.charts?.scrum_trend?.data || [],
            backgroundColor: '#2563eb',
            borderRadius: 8,
            maxBarThickness: 28,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                font: { family: 'Outfit', weight: '700', size: 11 },
            },
        },
    },
    scales: {
        x: {
            ticks: { color: '#64748b', font: { family: 'Outfit', weight: '700', size: 10 } },
            grid: { display: false },
        },
        y: {
            beginAtZero: true,
            ticks: { color: '#64748b', font: { family: 'Outfit', weight: '700', size: 10 } },
            grid: { color: 'rgba(148, 163, 184, 0.18)' },
        },
    },
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '70%',
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                font: { family: 'Outfit', weight: '800', size: 10 },
                padding: 14,
            },
        },
    },
};

const badgeTimeline = computed(() => props.charts?.badge_timeline || []);
const hasRewardsData = computed(() => (props.summary?.lifetime_points || 0) > 0 || (props.badges?.earned?.length || 0) > 0);
</script>

<template>
    <Head :title="`${employee?.name || 'Employee'} Rewards`" />
    <MainLayout>
        <div class="font-outfit min-h-screen bg-[radial-gradient(circle_at_top_left,_#f0fdfa,_#eff6ff_35%,_#fff7ed_70%,_#ffffff_100%)] pb-16">
            <div class="max-w-[96%] mx-auto px-4 xl:px-8 pt-8 space-y-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <Link :href="route('employee.profile', employee?.uuid)" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-[0.2em] text-slate-500 hover:text-teal-700 transition-colors">
                            <ChevronLeftIcon class="w-4 h-4" />
                            Back To Profile
                        </Link>
                        <h1 class="text-3xl md:text-4xl font-black text-slate-900 mt-3 tracking-tight">Rewards Command Center</h1>
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-slate-500 mt-2">Points, Scrum Velocity, Badges, And Growth Signals</p>
                    </div>

                    <div class="rounded-3xl border border-white/70 shadow-2xl shadow-teal-500/10 bg-white/80 backdrop-blur px-5 py-4 min-w-[290px]">
                        <p class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-400">Operative</p>
                        <div class="mt-2 flex items-center gap-3">
                            <img v-if="employee?.avatar_url" :src="employee.avatar_url" class="h-11 w-11 rounded-2xl object-cover border border-teal-100" :alt="employee?.name" />
                            <div v-else class="h-11 w-11 rounded-2xl bg-teal-100 text-teal-700 flex items-center justify-center font-black">
                                {{ (employee?.name || 'E').charAt(0) }}
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-900">{{ employee?.name }}</p>
                                <p class="text-xs font-bold text-slate-500">{{ employee?.designation || 'Team Member' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur border border-white rounded-[2rem] p-5 shadow-lg shadow-slate-300/20">
                    <div class="grid grid-cols-1 md:grid-cols-4 xl:grid-cols-8 gap-3 items-end">
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-1">From</label>
                            <input v-model="filterForm.date_from" type="date" class="w-full h-11 rounded-xl border-slate-200 bg-white text-sm font-semibold focus:ring-teal-500 focus:border-teal-500" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-1">To</label>
                            <input v-model="filterForm.date_to" type="date" class="w-full h-11 rounded-xl border-slate-200 bg-white text-sm font-semibold focus:ring-teal-500 focus:border-teal-500" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-1">Source</label>
                            <select v-model="filterForm.source" class="w-full h-11 rounded-xl border-slate-200 bg-white text-sm font-semibold focus:ring-teal-500 focus:border-teal-500">
                                <option v-for="s in sources || []" :key="s" :value="s">{{ s }}</option>
                            </select>
                        </div>
                        <button @click="applyFilters" class="h-11 rounded-xl bg-teal-600 text-white text-xs font-black uppercase tracking-[0.18em] hover:bg-teal-700 transition-colors">Apply</button>
                        <button @click="resetFilters" class="h-11 rounded-xl bg-slate-100 text-slate-700 text-xs font-black uppercase tracking-[0.18em] hover:bg-slate-200 transition-colors">Reset</button>
                    </div>
                </div>

                <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
                    <article class="rounded-3xl p-5 bg-white border border-slate-200 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Lifetime Points</p>
                        <p class="text-3xl font-black text-slate-900 mt-3">{{ summary?.lifetime_points || 0 }}</p>
                    </article>
                    <article class="rounded-3xl p-5 bg-gradient-to-br from-teal-50 to-white border border-teal-100 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-teal-700">This Month</p>
                        <p class="text-3xl font-black text-teal-700 mt-3">{{ summary?.points_this_month || 0 }}</p>
                    </article>
                    <article class="rounded-3xl p-5 bg-gradient-to-br from-blue-50 to-white border border-blue-100 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-700">Scrum Points</p>
                        <p class="text-3xl font-black text-blue-700 mt-3">{{ summary?.scrum_points_this_month || 0 }}</p>
                    </article>
                    <article class="rounded-3xl p-5 bg-gradient-to-br from-amber-50 to-white border border-amber-100 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-700">Badges Earned</p>
                        <p class="text-3xl font-black text-amber-700 mt-3">{{ summary?.badges_earned || 0 }}</p>
                    </article>
                    <article class="rounded-3xl p-5 bg-white border border-slate-200 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Current Streak</p>
                        <div class="mt-3 flex items-end gap-2">
                            <FireIcon class="w-6 h-6 text-rose-500" />
                            <p class="text-3xl font-black text-slate-900">{{ summary?.current_streak || 0 }}</p>
                        </div>
                    </article>
                    <article class="rounded-3xl p-5 bg-white border border-slate-200 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Best Streak</p>
                        <div class="mt-3 flex items-end gap-2">
                            <BoltIcon class="w-6 h-6 text-indigo-500" />
                            <p class="text-3xl font-black text-slate-900">{{ summary?.best_streak || 0 }}</p>
                        </div>
                    </article>
                </section>

                <section v-if="hasRewardsData" class="grid grid-cols-1 xl:grid-cols-12 gap-5">
                    <article class="xl:col-span-8 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-black text-slate-900 uppercase tracking-[0.15em]">Points Momentum</h3>
                            <ArrowTrendingUpIcon class="w-5 h-5 text-teal-600" />
                        </div>
                        <div class="h-80"><BaseChart type="line" :data="pointsTrendData" :options="chartOptions" /></div>
                    </article>

                    <article class="xl:col-span-4 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-base font-black text-slate-900 uppercase tracking-[0.15em] mb-4">Points Source Mix</h3>
                        <div class="h-80"><BaseChart type="doughnut" :data="sourceChartData" :options="doughnutOptions" /></div>
                    </article>

                    <article class="xl:col-span-7 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-base font-black text-slate-900 uppercase tracking-[0.15em] mb-4">Scrum Trend</h3>
                        <div class="h-72"><BaseChart type="bar" :data="scrumTrendData" :options="chartOptions" /></div>
                    </article>

                    <article class="xl:col-span-5 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-base font-black text-slate-900 uppercase tracking-[0.15em] mb-4">Badge Timeline</h3>
                        <div class="space-y-3 max-h-72 overflow-auto pr-2">
                            <div v-for="item in badgeTimeline" :key="`${item.name}-${item.date}`" class="flex items-center gap-3 rounded-xl bg-slate-50 border border-slate-100 px-3 py-2">
                                <span class="text-2xl">{{ item.icon }}</span>
                                <div>
                                    <p class="text-sm font-black text-slate-900">{{ item.name }}</p>
                                    <p class="text-[11px] font-semibold text-slate-500">{{ item.date || '-' }}</p>
                                </div>
                            </div>
                            <p v-if="!badgeTimeline.length" class="text-sm font-semibold text-slate-400">No badge awards in selected range.</p>
                        </div>
                    </article>
                </section>

                <section class="grid grid-cols-1 xl:grid-cols-3 gap-5">
                    <article class="xl:col-span-2 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center gap-2 mb-5">
                            <TrophyIcon class="w-5 h-5 text-amber-500" />
                            <h3 class="text-base font-black text-slate-900 uppercase tracking-[0.15em]">Badge Vault</h3>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <p class="text-xs font-black text-emerald-700 uppercase tracking-[0.18em] mb-3">Earned</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div v-for="badge in badges?.earned || []" :key="`earned-${badge.id}-${badge.awarded_at}`" class="rounded-2xl border border-emerald-100 bg-emerald-50/60 px-4 py-3">
                                        <div class="flex items-start gap-3">
                                            <span class="text-3xl">{{ badge.icon }}</span>
                                            <div class="min-w-0">
                                                <p class="text-sm font-black text-slate-900 truncate">{{ badge.name }}</p>
                                                <p class="text-xs font-semibold text-slate-500 mt-1 line-clamp-2">{{ badge.description }}</p>
                                                <p class="text-[11px] font-black text-emerald-700 mt-2">Awarded {{ badge.awarded_at || '-' }} | +{{ badge.points_bonus }} pts</p>
                                            </div>
                                        </div>
                                    </div>
                                    <p v-if="!(badges?.earned || []).length" class="text-sm font-semibold text-slate-400">No badges earned yet. Keep pushing.</p>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs font-black text-blue-700 uppercase tracking-[0.18em] mb-3">In Progress</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div v-for="badge in badges?.in_progress || []" :key="`progress-${badge.id}`" class="rounded-2xl border border-blue-100 bg-blue-50/60 px-4 py-3">
                                        <div class="flex items-start gap-3">
                                            <span class="text-3xl">{{ badge.icon }}</span>
                                            <div class="w-full">
                                                <p class="text-sm font-black text-slate-900">{{ badge.name }}</p>
                                                <p class="text-xs font-semibold text-slate-500 mt-1 line-clamp-2">{{ badge.description }}</p>
                                                <div class="mt-2 h-2 rounded-full bg-blue-100 overflow-hidden">
                                                    <div class="h-full bg-blue-600" :style="{ width: `${badge.progress?.percent || 0}%` }"></div>
                                                </div>
                                                <p class="text-[11px] font-black text-blue-700 mt-2">{{ badge.progress?.current || 0 }} / {{ badge.progress?.target || 0 }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <p v-if="!(badges?.in_progress || []).length" class="text-sm font-semibold text-slate-400">No measurable in-progress badges right now.</p>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <SparklesIcon class="w-5 h-5 text-violet-500" />
                            <h3 class="text-base font-black text-slate-900 uppercase tracking-[0.15em]">Locked Next</h3>
                        </div>
                        <div class="space-y-3 max-h-[520px] overflow-auto pr-1">
                            <div v-for="badge in badges?.locked || []" :key="`locked-${badge.id}`" class="rounded-2xl border border-slate-100 bg-slate-50 px-3 py-3">
                                <div class="flex items-start gap-3">
                                    <span class="text-2xl opacity-70">{{ badge.icon }}</span>
                                    <div>
                                        <p class="text-sm font-black text-slate-900">{{ badge.name }}</p>
                                        <p class="text-xs font-semibold text-slate-500 mt-1 line-clamp-2">{{ badge.description }}</p>
                                    </div>
                                </div>
                            </div>
                            <p v-if="!(badges?.locked || []).length" class="text-sm font-semibold text-slate-400">No locked badges left. Legendary run.</p>
                        </div>
                    </article>
                </section>

                <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-black text-slate-900 uppercase tracking-[0.15em] mb-4">Points Ledger</h3>
                    <div class="overflow-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 border-b border-slate-100">
                                    <th class="py-3 px-2 text-left">When</th>
                                    <th class="py-3 px-2 text-left">Event</th>
                                    <th class="py-3 px-2 text-left">Source</th>
                                    <th class="py-3 px-2 text-left">Reason</th>
                                    <th class="py-3 px-2 text-right">Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in ledger?.data || []" :key="row.id" class="border-b border-slate-50 hover:bg-slate-50/60 transition-colors">
                                    <td class="py-2.5 px-2 text-xs font-bold text-slate-600">{{ row.when }}</td>
                                    <td class="py-2.5 px-2 text-xs font-black text-slate-900">{{ row.rule_name || row.event_key || '-' }}</td>
                                    <td class="py-2.5 px-2 text-xs font-black uppercase text-slate-600">{{ row.source }}</td>
                                    <td class="py-2.5 px-2 text-xs font-semibold text-slate-500">{{ row.reason || '-' }}</td>
                                    <td class="py-2.5 px-2 text-sm font-black text-right" :class="row.points >= 0 ? 'text-emerald-700' : 'text-rose-700'">{{ row.points >= 0 ? '+' : '' }}{{ row.points }}</td>
                                </tr>
                                <tr v-if="!(ledger?.data || []).length">
                                    <td colspan="5" class="py-8 text-center text-sm font-semibold text-slate-400">No points entries in this filter range.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </MainLayout>
</template>
