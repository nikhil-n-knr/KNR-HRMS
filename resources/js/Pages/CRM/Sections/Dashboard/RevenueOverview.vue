<template>
    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- Key Revenue Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div v-for="card in revenueCards" :key="card.label" 
                class="bg-white/60 backdrop-blur-xl p-6 rounded-3xl border border-white shadow-xl shadow-emerald-500/5 group hover:scale-[1.02] transition-all cursor-pointer">
                <div class="flex items-center justify-between mb-4">
                    <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-lg', card.color]">
                        <i :class="['fas', card.icon]"></i>
                    </div>
                    <span class="text-sm font-black px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 uppercase tracking-widest">+{{ card.trend }}%</span>
                </div>
                <h3 class="text-gray-500 text-xs font-black uppercase tracking-widest">{{ card.label }}</h3>
                <p class="text-2xl font-black text-gray-900 mt-1">{{ card.value }}</p>
                <div class="mt-4 h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-400" :style="{ width: card.progress + '%' }"></div>
                </div>
            </div>
        </div>

        <!-- Revenue Forecast Chart Area -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-gray-900 rounded-[2.5rem] p-8 text-white relative overflow-hidden shadow-2xl">
                <!-- Abstract Background Shapes -->
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-emerald-500/20 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl"></div>

                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-xl font-black tracking-tight">AI Revenue Forecast</h2>
                            <p class="text-gray-400 text-xs font-bold mt-1 uppercase tracking-widest">Next 12 Months Projection</p>
                        </div>
                        <div class="flex gap-2">
                             <button class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-black transition-all">Monthly</button>
                             <button class="px-4 py-2 rounded-xl bg-emerald-500 text-black text-xs font-black transition-all">Quarterly</button>
                        </div>
                    </div>

                    <!-- Simplified Chart Visualization -->
                    <div class="h-64 flex items-end gap-3 px-4">
                        <div v-for="(v, i) in displayForecast" :key="i"
                            class="flex-1 rounded-t-lg bg-gradient-to-t transition-all duration-1000 delay-[i*50] hover:brightness-125 cursor-pointer"
                            :class="i > 8 ? 'from-emerald-500/40 to-emerald-400' : 'from-blue-500/40 to-blue-400'"
                            :style="{ height: v + 'px' }"
                        >
                            <div v-if="i === 11" class="absolute -top-8 left-1/2 -translate-x-1/2 bg-white text-black px-2 py-1 rounded-md text-sm font-black shadow-xl">₹15.4Cr</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white shadow-xl flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-black text-gray-900 tracking-tight">Strategic Insights</h3>
                    <p class="text-gray-400 text-xs font-bold mt-1 uppercase tracking-widest">Revenue Leak Detection</p>
                </div>

                <div class="space-y-4 my-8">
                    <div v-for="leak in displayLeaks" :key="leak.title" class="p-4 rounded-2xl bg-gray-50 border border-gray-100 hover:border-rose-200 transition-all group cursor-pointer">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-xs">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-gray-800">{{ leak.title }}</h4>
                                <p class="text-sm text-gray-500 font-bold mt-0.5">{{ leak.impact }} Impact</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button class="px-4 py-3 rounded-xl bg-gray-900 text-white text-xs font-black hover:bg-black transition-all shadow-lg">Forecast Full Report</button>
                    <button class="px-4 py-3 rounded-xl bg-emerald-500 text-white text-xs font-black hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-200">Capacity Plan</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    metrics: { type: Object, default: () => ({}) },
    leaks: { type: Array, default: () => [] },
    forecast_data: { type: Array, default: () => [] }
});

const revenueCards = computed(() => [
    { label: 'Annual Recurring (ARR)', value: props.metrics?.arr || '₹0', trend: 12.4, progress: 85, color: 'bg-emerald-500', icon: 'fa-calendar-check' },
    { label: 'Monthly Recurring (MRR)', value: props.metrics?.mrr || '₹0', trend: 8.2, progress: 72, color: 'bg-blue-500', icon: 'fa-sync' },
    { label: 'Churn Rate', value: props.metrics?.churn || '0%', trend: -2.1, progress: 15, color: 'bg-rose-500', icon: 'fa-user-minus' },
    { label: 'Pipeline Coverage', value: props.metrics?.pipeline_coverage || '0x', trend: 5.4, progress: 92, color: 'bg-amber-500', icon: 'fa-funnel-dollar' },
]);

// Use props.leaks directly or with default
const displayLeaks = computed(() => props.leaks.length > 0 ? props.leaks : [
    { title: 'Expansion Opportunity', impact: '₹12.4L' },
    { title: 'Unused Seat Leak', impact: '₹4.2L' },
    { title: 'Price Plan Lag', impact: '₹8.7L' },
]);

const displayForecast = computed(() => props.forecast_data.length > 0 ? props.forecast_data : [40, 60, 45, 90, 65, 80, 100, 85, 95, 110, 130, 150]);
</script>
