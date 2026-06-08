<template>
    <div class="min-h-screen bg-[#f8fafc] p-4 lg:p-8">
        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="space-y-2">
                    <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">
                        Project Weekly Performance
                    </h1>
                    <p class="text-slate-500 font-medium">{{ project.name }} / Planned vs Actual Drift</p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-2xl p-1 shadow-sm">
                        <input type="date" v-model="filters.start_date" @change="applyFilters" class="border-none bg-transparent text-xs font-bold text-slate-600 focus:ring-0">
                        <span class="text-slate-300">to</span>
                        <input type="date" v-model="filters.end_date" @change="applyFilters" class="border-none bg-transparent text-xs font-bold text-slate-600 focus:ring-0">
                    </div>
                    <a :href="route('projects.analytics.export', { project: project.id, ...filters })" 
                       class="px-6 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-700 hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Excel
                    </a>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-widest text-slate-400">Total Planned (Matrix)</p>
                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-900">{{ analytics.totals.planned }}</span>
                        <span class="text-sm font-bold text-slate-500">HRS</span>
                    </div>
                </div>
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-widest text-slate-400">Total Actual (Logged)</p>
                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-900">{{ analytics.totals.actual }}</span>
                        <span class="text-sm font-bold text-slate-500">HRS</span>
                    </div>
                </div>
                <div :class="analytics.totals.deviation > 0 ? 'bg-rose-50 border-rose-100' : 'bg-emerald-50 border-emerald-100'" class="rounded-3xl border p-6 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-widest text-slate-400">Total Deviation</p>
                    <div class="mt-4 flex items-baseline gap-2">
                        <span :class="analytics.totals.deviation > 0 ? 'text-rose-600' : 'text-emerald-600'" class="text-3xl font-black">
                            {{ analytics.totals.deviation > 0 ? '+' : '' }}{{ analytics.totals.deviation }}
                        </span>
                        <span class="text-sm font-bold text-slate-500">HRS</span>
                    </div>
                </div>
            </div>

            <!-- Weekly Report Table -->
            <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-700">Individual Consumption Drift</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/30">
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Resource</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Matrix Plan</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Actual Logged</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Drift (Weekly)</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="row in analytics.report" :key="row.user_id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img :src="row.avatar" class="h-8 w-8 rounded-full border border-slate-200" v-if="row.avatar"/>
                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500" v-else>{{ row.name[0] }}</div>
                                        <span class="text-sm font-bold text-slate-700">{{ row.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-xs text-slate-500">{{ row.planned }}h</td>
                                <td class="px-6 py-4 text-center font-mono text-sm font-bold text-slate-700">{{ row.actual }}h</td>
                                <td class="px-6 py-4 text-center">
                                    <span v-if="row.deviation > 0" class="text-rose-600 font-black text-xs">+{{ row.deviation }}h</span>
                                    <span v-else-if="row.deviation < 0" class="text-amber-600 font-bold text-xs">{{ row.deviation }}h</span>
                                    <span v-else class="text-emerald-500 font-bold text-xs">0h</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span v-if="row.deviation > 0" class="px-2 py-0.5 bg-rose-50 text-rose-600 text-[10px] font-black rounded-full uppercase">Overworked</span>
                                    <span v-else-if="row.deviation < 0" class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[10px] font-black rounded-full uppercase">Underutilized</span>
                                    <span v-else class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] font-black rounded-full uppercase">Exact</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import ProjectLayout from '@/Layouts/ProjectLayout.vue';

defineOptions({ layout: ProjectLayout });

const props = defineProps({
    project: Object,
    analytics: Object,
    filters: Object
});

const filters = reactive({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date
});

const applyFilters = () => {
    router.get(route('projects.analytics', props.project.id), filters, {
        preserveState: true,
        preserveScroll: true
    });
};
</script>
