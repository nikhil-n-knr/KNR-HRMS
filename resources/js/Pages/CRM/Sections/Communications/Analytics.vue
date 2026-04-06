<template>
    <div class="flex flex-col h-full bg-slate-50/30 overflow-y-auto custom-scrollbar">
        <!-- Header -->
        <div class="p-8 bg-white border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Performance Analytics</h2>
                <p class="text-slate-500 text-sm font-medium">Enterprise-wide engagement and conversion funnels.</p>
            </div>
            <div class="flex items-center gap-3">
                <select class="bg-slate-50 border-none rounded-xl px-4 py-2.5 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-indigo-500/20">
                    <option>Last 30 Days</option>
                    <option>Last 90 Days</option>
                    <option>This Year</option>
                </select>
                <button class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-indigo-200 hover:bg-black transition-all">
                    Export Report
                </button>
            </div>
        </div>

        <div class="p-8 space-y-8 max-w-7xl mx-auto w-full">
            <!-- Funnel Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/40 p-8">
                    <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-8">Outbound Engagement Funnel</h3>
                    
                    <div class="space-y-6">
                        <div v-for="(step, index) in funnel" :key="index" class="relative group">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black text-slate-500 uppercase tracking-tighter">{{ step.label }}</span>
                                <span class="text-sm font-black text-slate-900">{{ step.value }} ({{ step.percentage }}%)</span>
                            </div>
                            <!-- Funnel Bar -->
                            <div class="w-full h-8 bg-slate-50 rounded-xl overflow-hidden relative border border-slate-100 shadow-inner">
                                <div :class="['h-full transition-all duration-1000 ease-out', step.color]" 
                                    :style="{ width: step.percentage + '%' }">
                                    <div class="absolute inset-0 bg-white/10 group-hover:bg-white/20 transition-all"></div>
                                </div>
                            </div>
                            
                            <!-- Dropoff Indicator -->
                            <div v-if="index < funnel.length - 1" class="absolute -bottom-5 right-4 z-10 flex items-center gap-1.5 text-[10px] font-black text-rose-500 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-100">
                                <i class="fas fa-caret-down"></i>
                                {{ (funnel[index].percentage - funnel[index+1].percentage).toFixed(1) }}% dropoff
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Summary -->
                <div class="space-y-6">
                    <div class="bg-indigo-600 rounded-3xl p-8 text-white shadow-xl shadow-indigo-100 overflow-hidden relative">
                        <i class="fas fa-chart-line absolute -right-4 -bottom-4 text-8xl text-indigo-500 opacity-20"></i>
                        <h4 class="text-xs font-black uppercase tracking-widest opacity-70 mb-1">Global Response Rate</h4>
                        <div class="text-4xl font-black mb-2">12.4%</div>
                        <p class="text-[10px] font-bold opacity-80 uppercase tracking-tighter">
                            <i class="fas fa-arrow-up mr-1"></i> 2.1% higher than last month
                        </p>
                    </div>

                    <div class="bg-white rounded-3xl border border-slate-100 p-8 shadow-xl shadow-slate-200/40">
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">Interaction Heatmap</h3>
                        <div class="grid grid-cols-7 gap-2">
                            <div v-for="i in 28" :key="i" 
                                class="aspect-square rounded-md transition-all hover:scale-110 cursor-pointer"
                                :class="heatmapColor(i)"
                            ></div>
                        </div>
                        <div class="flex justify-between mt-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <span>Mon</span>
                            <span>Sun</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leaderboard -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden">
                <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/20">
                    <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">Team Performance Leaderboard</h3>
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">Live Updates</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-50">
                                <th class="px-8 py-5">Rank & Employee</th>
                                <th class="px-8 py-5 text-center">Interactions</th>
                                <th class="px-8 py-5 text-center">Avg Response Time</th>
                                <th class="px-8 py-5 text-center">Engagement Rate</th>
                                <th class="px-8 py-5 text-right">Trend</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(user, index) in leaderboard" :key="index" class="group hover:bg-slate-50/50 transition-all border-b border-slate-50/50">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <span class="text-lg font-black text-slate-200 italic">#{{ index + 1 }}</span>
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center font-black text-slate-600 text-sm">
                                            {{ user.name.split(' ').map(n=>n[0]).join('') }}
                                        </div>
                                        <div>
                                            <div class="font-black text-slate-900 tracking-tight">{{ user.name }}</div>
                                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter italic">Enterprise Sales</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="font-black text-slate-900">{{ user.score }}</span>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="text-xs font-bold text-slate-600">42m</span>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span class="text-xs font-black text-indigo-600">{{ user.rate }}</span>
                                        <div class="w-12 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-indigo-500" :style="{ width: user.rate }"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <i class="fas fa-chart-line text-emerald-500"></i>
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
const props = defineProps({
    funnel: Array,
    leaderboard: Array,
});

const heatmapColor = (i) => {
    const intensity = Math.random();
    if (intensity > 0.8) return 'bg-indigo-600 shadow-lg shadow-indigo-100';
    if (intensity > 0.5) return 'bg-indigo-400';
    if (intensity > 0.2) return 'bg-indigo-200';
    return 'bg-slate-100';
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
