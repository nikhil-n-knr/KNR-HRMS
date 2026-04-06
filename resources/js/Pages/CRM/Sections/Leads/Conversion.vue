<script setup>
const props = defineProps({
    stats: {
        type: Array,
        default: () => []
    }
});

const totalConverted = props.stats.reduce((acc, s) => acc + s.converted, 0);
const totalLeads = props.stats.reduce((acc, s) => acc + s.total, 0);
const globalRate = totalLeads > 0 ? ((totalConverted / totalLeads) * 100).toFixed(1) : 0;
</script>

<template>
    <div class="space-y-12 text-left">
        <!-- New Metrics Head -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                <div class="text-sm font-black text-gray-400 uppercase tracking-widest mb-2">Total Yield</div>
                <div class="text-3xl font-black text-gray-900">{{ totalConverted }} <span class="text-sm font-bold text-gray-400">Contacts</span></div>
            </div>
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                <div class="text-sm font-black text-gray-400 uppercase tracking-widest mb-2">Conversion Rate</div>
                <div class="text-3xl font-black text-emerald-600">{{ globalRate }}%</div>
            </div>
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                <div class="text-sm font-black text-gray-400 uppercase tracking-widest mb-2">Lead Flow</div>
                <div class="text-3xl font-black text-gray-900">{{ totalLeads }}</div>
            </div>
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                <div class="text-sm font-black text-gray-400 uppercase tracking-widest mb-2">Active Channels</div>
                <div class="text-3xl font-black text-blue-600">{{ stats.length }}</div>
            </div>
        </div>

        <!-- Channel Performance Table -->
        <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-10 py-6 text-left text-sm font-black text-gray-400 uppercase tracking-widest">Source Channel</th>
                        <th class="px-10 py-6 text-center text-sm font-black text-gray-400 uppercase tracking-widest">Inbound Flow</th>
                        <th class="px-10 py-6 text-center text-sm font-black text-gray-400 uppercase tracking-widest">Qualified Yield</th>
                        <th class="px-10 py-6 text-right text-sm font-black text-gray-400 uppercase tracking-widest">Efficiency</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="source in stats" :key="source.source" class="hover:bg-blue-50/30 transition-colors group">
                        <td class="px-10 py-8 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-blue-600 group-hover:text-white transition-all">
                                    <i class="fas fa-bullseye text-xs"></i>
                                </div>
                                <span class="ml-4 font-black text-gray-900">{{ source.source }}</span>
                            </div>
                        </td>
                        <td class="px-10 py-8 text-center font-bold text-gray-600">{{ source.total }}</td>
                        <td class="px-10 py-8 text-center font-black text-gray-900">{{ source.converted }}</td>
                        <td class="px-10 py-8 text-right">
                            <span class="px-4 py-1.5 rounded-xl bg-emerald-50 text-emerald-600 text-sm font-black border border-emerald-100">
                                {{ source.rate }}% RATIO
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
