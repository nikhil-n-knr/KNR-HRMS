<template>
    <div class="space-y-8">
        <section class="grid grid-cols-2 gap-4 xl:grid-cols-6">
            <article v-for="card in headlineCards" :key="card.label" class="rounded-3xl border p-5 shadow-sm"
                :class="card.shellClass">
                <p class="text-[10px] font-black uppercase tracking-[0.2em]" :class="card.labelClass">{{ card.label }}</p>
                <p class="mt-3 text-3xl font-black" :class="card.valueClass">{{ card.value }}</p>
                <p class="mt-2 text-[10px] font-bold uppercase tracking-wider" :class="card.noteClass">{{ card.note }}</p>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <article v-for="card in categoryCards" :key="card.key" class="rounded-3xl border p-5 shadow-sm" :class="card.shellClass">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-widest" :class="card.labelClass">{{ card.label }}</p>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-gray-500">{{ card.subtitle }}</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest" :class="card.badgeClass">
                        {{ card.count }} rounds
                    </span>
                </div>
                <div class="mt-5 grid grid-cols-3 gap-3 text-center">
                    <div class="rounded-2xl bg-white/70 p-3">
                        <p class="text-lg font-black text-gray-900">{{ card.days }}</p>
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Days</p>
                    </div>
                    <div class="rounded-2xl bg-white/70 p-3">
                        <p class="text-lg font-black text-gray-900">{{ card.hours }}</p>
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Hours</p>
                    </div>
                    <div class="rounded-2xl bg-white/70 p-3">
                        <p class="text-lg font-black text-gray-900">{{ card.tertiaryValue }}</p>
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">{{ card.tertiaryLabel }}</p>
                    </div>
                </div>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <article class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-700">Cumulative Effort Growth</h3>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">Baseline to each extension round and current total</p>
                    </div>
                </div>
                <div class="mt-6 h-64">
                    <Line :data="effortChartData" :options="lineOptions" />
                </div>
            </article>

            <article class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-700">Timeline Drift History</h3>
                    <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">Days added at each extension event</p>
                </div>
                <div class="mt-6 h-64">
                    <Bar :data="timelineChartData" :options="barOptions" />
                </div>
            </article>

            <article class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-700">Category Breakdown</h3>
                    <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">Distribution of extension rounds by governance cause</p>
                </div>
                <div class="mt-6 flex h-64 items-center justify-center">
                    <Doughnut v-if="hasCategoryData" :data="categoryChartData" :options="doughnutOptions" />
                    <p v-else class="text-xs font-black uppercase tracking-widest text-gray-300">No extension history</p>
                </div>
            </article>

            <article class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-700">Person-Level Delay</h3>
                    <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">Assignees attached to the highest extension drift</p>
                </div>
                <div class="mt-6 h-64">
                    <Bar v-if="personDelayData.labels.length" :data="personDelayData" :options="horizontalBarOptions" />
                    <div v-else class="flex h-full items-center justify-center text-xs font-black uppercase tracking-widest text-gray-300">No delayed contributors</div>
                </div>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <article class="overflow-hidden rounded-3xl border border-rose-100 bg-white shadow-sm">
                <div class="flex items-center gap-3 border-b border-rose-100 bg-rose-50 px-6 py-4">
                    <span class="text-lg">Delayed</span>
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] text-rose-700">Who Delayed</h3>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-rose-400">Sorted by total days delayed through extensions</p>
                    </div>
                </div>
                <div v-if="personPerformance?.delayed?.length" class="divide-y divide-rose-50">
                    <div v-for="person in personPerformance.delayed" :key="person.id" class="flex items-center gap-4 px-6 py-4">
                        <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-rose-100 text-xs font-black uppercase text-rose-600">
                            <img v-if="person.avatar" :src="person.avatar" class="h-full w-full object-cover" />
                            <span v-else>{{ initials(person.name) }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-sm font-black text-gray-900">{{ person.name }}</p>
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[9px] font-black uppercase tracking-widest text-gray-500">{{ person.team || 'No Team' }}</span>
                            </div>
                            <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                {{ person.on_time_pct }}% on time · {{ person.late_pct }}% late · avg variance {{ signedDays(person.avg_variance_days) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-black text-rose-600">+{{ person.total_extension_days || person.total_delay_days }}d</p>
                            <p class="text-[10px] font-black uppercase tracking-widest text-rose-400">Drift</p>
                        </div>
                    </div>
                </div>
                <div v-else class="px-6 py-12 text-center text-xs font-black uppercase tracking-widest text-gray-300">No delayed contributors recorded</div>
            </article>

            <article class="overflow-hidden rounded-3xl border border-emerald-100 bg-white shadow-sm">
                <div class="flex items-center gap-3 border-b border-emerald-100 bg-emerald-50 px-6 py-4">
                    <span class="text-lg">Fast</span>
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] text-emerald-700">Who Was Fast</h3>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-emerald-400">Completed ahead of due date from approved timesheet history</p>
                    </div>
                </div>
                <div v-if="personPerformance?.fast?.length" class="divide-y divide-emerald-50">
                    <div v-for="person in personPerformance.fast" :key="person.id" class="flex items-center gap-4 px-6 py-4">
                        <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-emerald-100 text-xs font-black uppercase text-emerald-600">
                            <img v-if="person.avatar" :src="person.avatar" class="h-full w-full object-cover" />
                            <span v-else>{{ initials(person.name) }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-sm font-black text-gray-900">{{ person.name }}</p>
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[9px] font-black uppercase tracking-widest text-gray-500">{{ person.team || 'No Team' }}</span>
                            </div>
                            <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                {{ person.fast_tasks || person.faster }} early tasks · {{ person.on_time_pct }}% on time · avg variance {{ signedDays(person.avg_variance_days) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-black text-emerald-600">-{{ person.total_saved_days }}d</p>
                            <p class="text-[10px] font-black uppercase tracking-widest text-emerald-400">Saved</p>
                        </div>
                    </div>
                </div>
                <div v-else class="px-6 py-12 text-center text-xs font-black uppercase tracking-widest text-gray-300">No ahead-of-schedule completions yet</div>
            </article>
        </section>

        <section class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-gray-100 bg-gray-50/70 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-900">Extension Log</h3>
                    <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">Category, original plan, new commitment, and extension metadata</p>
                </div>
                <div class="flex gap-2">
                    <button @click="$emit('record')" class="rounded-2xl bg-indigo-600 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-white hover:bg-indigo-700">
                        Record Extension
                    </button>
                    <button @click="$emit('export')" class="rounded-2xl bg-emerald-600 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-white hover:bg-emerald-700">
                        Export Excel
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 text-[10px] font-black uppercase tracking-widest text-gray-400">
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Task</th>
                            <th class="px-4 py-3">Original Start</th>
                            <th class="px-4 py-3">Original End</th>
                            <th class="px-4 py-3">Extended End</th>
                            <th class="px-4 py-3">+Days</th>
                            <th class="px-4 py-3">+Hours</th>
                            <th class="px-4 py-3">Meta Preview</th>
                            <th class="px-4 py-3">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="extension in extensionTimeline" :key="extension.id" class="hover:bg-indigo-50/30">
                            <td class="px-4 py-4 font-black text-gray-300">{{ extension.round }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-wide" :class="badgeClass(extension.category)">
                                    {{ extension.category_label }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-black text-gray-800">{{ extension.task_title || 'Project Wide' }}</p>
                                <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ extension.reason }}</p>
                            </td>
                            <td class="px-4 py-4 font-bold text-gray-500">{{ formatShortDate(extension.original_start_date) }}</td>
                            <td class="px-4 py-4 font-bold text-gray-500">{{ formatShortDate(extension.original_end_date) }}</td>
                            <td class="px-4 py-4 font-black text-rose-600">{{ formatShortDate(extension.extended_end_date) }}</td>
                            <td class="px-4 py-4 font-black text-amber-700">+{{ extension.days }}</td>
                            <td class="px-4 py-4 font-black text-rose-700">+{{ extension.hours }}</td>
                            <td class="px-4 py-4">
                                <p class="max-w-[240px] text-[10px] font-bold uppercase tracking-wider text-gray-500">{{ metaPreview(extension) }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-black text-gray-800">{{ extension.user }}</p>
                                <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ formatShortDate(extension.date) }}</p>
                            </td>
                        </tr>
                        <tr v-if="!extensionTimeline.length">
                            <td colspan="10" class="px-4 py-12 text-center text-xs font-black uppercase tracking-widest text-gray-300">No extension records available</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Line, Bar, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    ArcElement,
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, LineElement, BarElement, CategoryScale, LinearScale, PointElement, ArcElement);

const props = defineProps({
    project: { type: Object, required: true },
    extensions: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
    personPerformance: { type: Object, default: () => ({ delayed: [], fast: [], all: [] }) },
});

defineEmits(['record', 'export']);

const extensionTimeline = computed(() => props.stats?.timeline || []);

const headlineCards = computed(() => [
    {
        label: 'Estimated Man-Hours',
        value: `${numberValue(props.project.original_hours)}h`,
        note: 'Baseline effort',
        shellClass: 'border-gray-100 bg-white',
        labelClass: 'text-gray-400',
        valueClass: 'text-gray-900',
        noteClass: 'text-gray-400',
    },
    {
        label: 'Allocated Man-Hours',
        value: `${numberValue(props.project.allocated_hours || props.stats.allocated_hours)}h`,
        note: 'Grant and matrix allocation',
        shellClass: 'border-indigo-100 bg-indigo-50',
        labelClass: 'text-indigo-400',
        valueClass: 'text-indigo-700',
        noteClass: 'text-indigo-400',
    },
    {
        label: 'Actual Man-Hours Taken',
        value: `${numberValue(props.project.actual_hours || props.stats.actual_hours)}h`,
        note: 'Approved timesheets',
        shellClass: 'border-emerald-100 bg-emerald-50',
        labelClass: 'text-emerald-400',
        valueClass: 'text-emerald-700',
        noteClass: 'text-emerald-400',
    },
    {
        label: 'Extended Period',
        value: `+${numberValue(props.stats.total_days_added, 0)}d`,
        note: 'Total days added',
        shellClass: 'border-amber-100 bg-amber-50',
        labelClass: 'text-amber-500',
        valueClass: 'text-amber-700',
        noteClass: 'text-amber-400',
    },
    {
        label: 'Extended Efforts',
        value: `+${numberValue(props.stats.total_hours_added)}h`,
        note: 'Extra hours approved',
        shellClass: 'border-rose-100 bg-rose-50',
        labelClass: 'text-rose-400',
        valueClass: 'text-rose-700',
        noteClass: 'text-rose-400',
    },
    {
        label: 'Drift Percentage',
        value: `${numberValue(props.stats.drift_pct)}%`,
        note: `${numberValue(props.stats.original_days, 0)} baseline days`,
        shellClass: 'border-orange-100 bg-orange-50',
        labelClass: 'text-orange-400',
        valueClass: 'text-orange-700',
        noteClass: 'text-orange-400',
    },
]);

const categoryCards = computed(() => {
    const categories = props.stats.by_category || {};
    return [
        {
            key: 'priority_conflict',
            label: 'Priority Conflict',
            subtitle: 'Time lost to higher-priority work',
            shellClass: 'border-violet-100 bg-violet-50/70',
            labelClass: 'text-violet-700',
            badgeClass: 'bg-violet-100 text-violet-700',
            tertiaryLabel: 'Deficit',
            tertiaryValue: `${numberValue(dataValue(categories.priority_conflict, 'hours'), 0)}h`,
            ...normalizeCategory(categories.priority_conflict),
        },
        {
            key: 'scope_change',
            label: 'Scope Change',
            subtitle: 'New work or underestimated scope',
            shellClass: 'border-amber-100 bg-amber-50/70',
            labelClass: 'text-amber-700',
            badgeClass: 'bg-amber-100 text-amber-700',
            tertiaryLabel: 'Resources',
            tertiaryValue: numberValue(dataValue(categories.scope_change, 'resources_added'), 0),
            ...normalizeCategory(categories.scope_change),
        },
        {
            key: 'complexity_drag',
            label: 'Complexity Drag',
            subtitle: 'Execution slower than forecast',
            shellClass: 'border-rose-100 bg-rose-50/70',
            labelClass: 'text-rose-700',
            badgeClass: 'bg-rose-100 text-rose-700',
            tertiaryLabel: 'Avg Slowdown',
            tertiaryValue: `${numberValue(dataValue(categories.complexity_drag, 'avg_slowdown'))}x`,
            ...normalizeCategory(categories.complexity_drag),
        },
    ];
});

const hasCategoryData = computed(() => Object.values(props.stats.by_category || {}).some((category) => Number(category?.count || 0) > 0));

const effortChartData = computed(() => {
    const labels = ['Baseline'];
    const data = [Number(props.project.original_hours || 0)];
    let cumulative = Number(props.project.original_hours || 0);

    extensionTimeline.value.forEach((extension) => {
        cumulative += Number(extension.hours || 0);
        labels.push(`Round ${extension.round}`);
        data.push(Number(cumulative.toFixed(2)));
    });

    labels.push('Current');
    data.push(Number(numberValue(props.project.current_hours || cumulative)));

    return {
        labels,
        datasets: [{
            label: 'Hours',
            data,
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79, 70, 229, 0.08)',
            fill: true,
            tension: 0.35,
            pointRadius: 4,
            pointBorderWidth: 2,
            pointBackgroundColor: '#ffffff',
        }],
    };
});

const timelineChartData = computed(() => ({
    labels: extensionTimeline.value.map((extension) => `${extension.date} · R${extension.round}`),
    datasets: [{
        label: 'Days Added',
        data: extensionTimeline.value.map((extension) => Number(extension.days || 0)),
        backgroundColor: extensionTimeline.value.map((extension) => colorForCategory(extension.category, 0.75)),
        borderRadius: 10,
    }],
}));

const categoryChartData = computed(() => {
    const categories = props.stats.by_category || {};
    return {
        labels: ['Priority Conflict', 'Scope Change', 'Complexity Drag'],
        datasets: [{
            data: [
                Number(categories.priority_conflict?.count || 0),
                Number(categories.scope_change?.count || 0),
                Number(categories.complexity_drag?.count || 0),
            ],
            backgroundColor: ['#8b5cf6', '#f59e0b', '#f43f5e'],
            borderWidth: 0,
        }],
    };
});

const personDelayData = computed(() => ({
    labels: (props.personPerformance?.delayed || []).slice(0, 8).map((person) => person.name),
    datasets: [{
        label: 'Delay Days',
        data: (props.personPerformance?.delayed || []).slice(0, 8).map((person) => Number(person.total_extension_days || person.total_delay_days || 0)),
        backgroundColor: 'rgba(244, 63, 94, 0.72)',
        borderRadius: 8,
    }],
}));

const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
        y: { beginAtZero: true, grid: { color: '#eef2ff' }, ticks: { font: { size: 10 } } },
    },
};

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
        y: { beginAtZero: true, grid: { color: '#f8fafc' }, ticks: { font: { size: 10 } } },
    },
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '64%',
    plugins: {
        legend: {
            position: 'bottom',
            labels: { boxWidth: 12, font: { size: 10, weight: 'bold' } },
        },
    },
};

const horizontalBarOptions = {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y',
    plugins: { legend: { display: false } },
    scales: {
        y: { grid: { display: false }, ticks: { font: { size: 10 } } },
        x: { beginAtZero: true, grid: { color: '#fff1f2' }, ticks: { font: { size: 10 } } },
    },
};

function normalizeCategory(category = {}) {
    return {
        count: Number(category.count || 0),
        days: `${numberValue(dataValue(category, 'days'), 0)}d`,
        hours: `${numberValue(dataValue(category, 'hours'))}h`,
    };
}

function dataValue(category, key) {
    if (!category) return 0;
    if (category[key] !== undefined) return category[key];
    if (key === 'days') return category.days_added || 0;
    if (key === 'hours') return category.hours_added || 0;
    return 0;
}

function numberValue(value, digits = 1) {
    const numeric = Number(value || 0);
    return numeric % 1 === 0 ? numeric.toFixed(0) : numeric.toFixed(digits);
}

function colorForCategory(category, alpha = 1) {
    if (category === 'priority_conflict') return `rgba(139, 92, 246, ${alpha})`;
    if (category === 'scope_change') return `rgba(245, 158, 11, ${alpha})`;
    if (category === 'complexity_drag') return `rgba(244, 63, 94, ${alpha})`;
    return `rgba(148, 163, 184, ${alpha})`;
}

function badgeClass(category) {
    if (category === 'priority_conflict') return 'bg-violet-100 text-violet-700';
    if (category === 'scope_change') return 'bg-amber-100 text-amber-700';
    if (category === 'complexity_drag') return 'bg-rose-100 text-rose-700';
    return 'bg-gray-100 text-gray-500';
}

function initials(name) {
    return String(name || 'NA')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('');
}

function signedDays(value) {
    const numeric = Number(value || 0);
    if (!numeric) return '0d';
    return `${numeric > 0 ? '+' : ''}${numberValue(numeric)}d`;
}

function formatShortDate(value) {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function metaPreview(extension) {
    const meta = extension.extension_meta || {};

    if (meta.conflicting_priority || meta.deficit_hours) {
        return `${meta.conflicting_priority || 'Priority conflict'} · deficit ${numberValue(meta.deficit_hours, 0)}h`;
    }
    if (meta.scope_change_type || meta.additional_resources) {
        const label = String(meta.scope_change_type || 'scope change').replace(/_/g, ' ');
        return `${label} · +${numberValue(meta.additional_resources, 0)} resource(s)`;
    }
    if (meta.complexity_factor || meta.slowdown_ratio) {
        const label = String(meta.complexity_factor || 'complexity').replace(/_/g, ' ');
        return `${label} · ${numberValue(meta.slowdown_ratio)}x slowdown`;
    }

    return extension.notes || 'No additional metadata';
}
</script>