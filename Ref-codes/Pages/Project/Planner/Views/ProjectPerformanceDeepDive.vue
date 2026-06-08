<template>
    <div class="space-y-8">
        <section class="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <article v-for="card in summaryCards" :key="card.label" class="rounded-3xl border p-5 shadow-sm" :class="card.shellClass">
                <p class="text-[10px] font-black uppercase tracking-[0.2em]" :class="card.labelClass">{{ card.label }}</p>
                <p class="mt-3 text-3xl font-black" :class="card.valueClass">{{ card.value }}</p>
                <p class="mt-2 text-[10px] font-bold uppercase tracking-wider" :class="card.noteClass">{{ card.note }}</p>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-800">Average Analysis</h3>
                    <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Planned, actual, deviation, and burn-rate averages</p>
                </div>
                <div class="mt-6 space-y-4">
                    <div v-for="row in averageRows" :key="row.label" class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50/70 px-4 py-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">{{ row.label }}</p>
                            <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ row.note }}</p>
                        </div>
                        <p class="text-2xl font-black text-slate-900">{{ row.value }}</p>
                    </div>
                </div>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-800">Effort Snapshot</h3>
                    <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Baseline versus allocated, actual, and extended effort</p>
                </div>
                <div class="mt-6 space-y-4">
                    <div v-for="bar in effortBars" :key="bar.label">
                        <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest">
                            <span class="text-slate-500">{{ bar.label }}</span>
                            <span :class="bar.valueClass">{{ bar.value }}</span>
                        </div>
                        <div class="mt-2 h-3 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full" :class="bar.fillClass" :style="{ width: `${bar.width}%` }"></div>
                        </div>
                    </div>
                </div>
            </article>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/70 px-6 py-4">
                <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-800">Task Variance Detail</h3>
                <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Planned versus actual effort, delivery status, and burn rate per task</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400">
                            <th class="px-5 py-3">Task</th>
                            <th class="px-5 py-3">Assignees</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Planned</th>
                            <th class="px-5 py-3">Allocated</th>
                            <th class="px-5 py-3">Actual</th>
                            <th class="px-5 py-3">Deviation</th>
                            <th class="px-5 py-3">Efficiency</th>
                            <th class="px-5 py-3">Burn Rate</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="task in metrics.performance.tasks" :key="task.id" class="hover:bg-slate-50/80">
                            <td class="px-5 py-4">
                                <p class="font-black text-slate-800">{{ task.title }}</p>
                                <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    <span v-if="task.completion_variance_days !== null">{{ signedDays(task.completion_variance_days) }} vs due date</span>
                                    <span v-else>No delivery variance yet</span>
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-black uppercase tracking-widest text-slate-500">
                                    {{ task.assignees?.length ? task.assignees.join(', ') : 'Unassigned' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2 py-1 text-[10px] font-black uppercase tracking-widest" :class="statusClass(task.status_label)">
                                    {{ task.status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-black text-slate-700">{{ formatHours(task.planned_hours) }}</td>
                            <td class="px-5 py-4 font-black text-indigo-700">{{ formatHours(task.allocated_hours) }}</td>
                            <td class="px-5 py-4 font-black text-rose-700">{{ formatHours(task.actual_hours) }}</td>
                            <td class="px-5 py-4 font-black" :class="Number(task.deviation_hours) > 0 ? 'text-rose-600' : 'text-emerald-600'">
                                {{ signedHours(task.deviation_hours) }}
                            </td>
                            <td class="px-5 py-4 font-black text-slate-700">{{ task.efficiency_ratio ? `${formatNumber(task.efficiency_ratio)}x` : '—' }}</td>
                            <td class="px-5 py-4 font-black text-slate-700">{{ formatNumber(task.burn_rate) }}h/day</td>
                            <td class="px-5 py-4 text-right">
                                <button @click="$emit('task-click', task)" class="rounded-2xl bg-indigo-50 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:bg-indigo-100">
                                    Inspect
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!metrics.performance.tasks?.length">
                            <td colspan="10" class="px-5 py-12 text-center text-xs font-black uppercase tracking-widest text-slate-300">No performance data available</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    metrics: { type: Object, required: true },
});

defineEmits(['task-click']);

const summaryCards = computed(() => [
    {
        label: 'Avg Efficiency Ratio',
        value: `${formatNumber(props.metrics.summary.avg_efficiency_ratio)}x`,
        note: 'Planned divided by actual hours',
        shellClass: 'border-indigo-100 bg-indigo-50',
        labelClass: 'text-indigo-400',
        valueClass: 'text-indigo-700',
        noteClass: 'text-indigo-400',
    },
    {
        label: 'Tasks On Time',
        value: `${formatNumber(props.metrics.summary.tasks_completed_on_time_pct)}%`,
        note: 'Completed exactly on due date',
        shellClass: 'border-emerald-100 bg-emerald-50',
        labelClass: 'text-emerald-400',
        valueClass: 'text-emerald-700',
        noteClass: 'text-emerald-400',
    },
    {
        label: 'Tasks Ahead',
        value: `${formatNumber(props.metrics.summary.tasks_completed_ahead_pct)}%`,
        note: 'Finished before due date',
        shellClass: 'border-sky-100 bg-sky-50',
        labelClass: 'text-sky-400',
        valueClass: 'text-sky-700',
        noteClass: 'text-sky-400',
    },
    {
        label: 'Tasks Overdue',
        value: `${formatNumber(props.metrics.summary.tasks_overdue_pct)}%`,
        note: 'Late or currently past due',
        shellClass: 'border-rose-100 bg-rose-50',
        labelClass: 'text-rose-400',
        valueClass: 'text-rose-700',
        noteClass: 'text-rose-400',
    },
]);

const averageRows = computed(() => [
    {
        label: 'Planned Hours per Task',
        note: 'Average baseline effort per task',
        value: formatHours(props.metrics.averages.planned_hours_per_task),
    },
    {
        label: 'Actual Hours per Task',
        note: 'Average approved effort per task',
        value: formatHours(props.metrics.averages.actual_hours_per_task),
    },
    {
        label: 'Average Deviation',
        note: 'Actual minus planned',
        value: signedHours(props.metrics.averages.avg_deviation_hours),
    },
    {
        label: 'Burn Rate',
        note: 'Actual hours per calendar day',
        value: `${formatNumber(props.metrics.averages.burn_rate)}h/day`,
    },
]);

const effortBars = computed(() => {
    const baseline = Math.max(Number(props.metrics.summary.estimated || 0), 1);
    return [
        {
            label: 'Estimated',
            value: formatHours(props.metrics.summary.estimated),
            width: 100,
            fillClass: 'bg-slate-500',
            valueClass: 'text-slate-700',
        },
        {
            label: 'Allocated',
            value: formatHours(props.metrics.summary.allocated),
            width: Math.min(100, (Number(props.metrics.summary.allocated || 0) / baseline) * 100),
            fillClass: 'bg-indigo-500',
            valueClass: 'text-indigo-700',
        },
        {
            label: 'Actual',
            value: formatHours(props.metrics.summary.actual),
            width: Math.min(100, (Number(props.metrics.summary.actual || 0) / baseline) * 100),
            fillClass: 'bg-rose-500',
            valueClass: 'text-rose-700',
        },
        {
            label: 'Extended',
            value: `${formatHours(props.metrics.summary.extended_hours)} · +${formatNumber(props.metrics.summary.extended_days, 0)}d`,
            width: Math.min(100, (Number(props.metrics.summary.extended_hours || 0) / baseline) * 100),
            fillClass: 'bg-amber-500',
            valueClass: 'text-amber-700',
        },
    ];
});

function formatNumber(value, digits = 1) {
    const numeric = Number(value || 0);
    return numeric % 1 === 0 ? numeric.toFixed(0) : numeric.toFixed(digits);
}

function formatHours(value) {
    return `${formatNumber(value)}h`;
}

function signedHours(value) {
    const numeric = Number(value || 0);
    if (!numeric) return '0h';
    return `${numeric > 0 ? '+' : ''}${formatNumber(numeric)}h`;
}

function signedDays(value) {
    const numeric = Number(value || 0);
    if (!numeric) return '0d';
    return `${numeric > 0 ? '+' : ''}${formatNumber(numeric)}d`;
}

function statusClass(status) {
    if (status === 'On Time') return 'bg-emerald-100 text-emerald-700';
    if (status === 'Ahead') return 'bg-sky-100 text-sky-700';
    if (status === 'Late' || status === 'Overdue') return 'bg-rose-100 text-rose-700';
    return 'bg-slate-100 text-slate-600';
}
</script>