<template>
    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- AI Summary Area -->
        <div class="bg-gray-900 rounded-[2.5rem] p-10 text-white relative overflow-hidden shadow-2xl">
            <!-- Matrix Style Code Background -->
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500/20 rounded-full blur-[120px]"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white text-2xl shadow-xl shadow-emerald-500/20">
                        <i class="fas fa-magic"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black tracking-tight flex items-center gap-3">
                            CRM.module Insights
                            <span class="px-3 py-1 rounded-full bg-emerald-500 text-black text-sm font-black uppercase tracking-widest">Active Intelligence</span>
                        </h2>
                        <p class="text-gray-400 text-sm font-bold mt-1 uppercase tracking-widest leading-none">Prescriptive AI Recommendations & Insights Engine</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mt-12">
                    <div class="space-y-4">
                        <div v-for="insight in displayInsights" :key="insight.title" 
                            class="p-6 rounded-3xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all group backdrop-blur-md cursor-pointer">
                            <div class="flex gap-5 items-center">
                                <div class="w-12 h-12 rounded-2xl bg-white/10 text-emerald-400 flex items-center justify-center text-lg">
                                    <i :class="['fas', insight.icon]"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-black text-white pr-8">{{ insight.title }}</h4>
                                    <div class="flex items-center gap-3 mt-2">
                                        <p class="text-sm text-gray-400 font-bold uppercase tracking-widest">{{ insight.impact }} Impact</p>
                                        <div class="h-1 flex-1 bg-white/5 rounded-full overflow-hidden">
                                            <div class="h-full bg-emerald-500" :style="{ width: (insight.confidence || 0) + '%' }"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-sm text-emerald-500 font-black px-2 py-1 rounded-md bg-emerald-500/10">
                                    {{ insight.confidence || 0 }}% AI CONFIDENCE
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/5 p-8 rounded-[2rem] border border-white/10 flex flex-col justify-between backdrop-blur-md shadow-inner">
                        <div class="space-y-8">
                            <div>
                                <h3 class="text-lg font-black tracking-tight">Prescriptive Actions</h3>
                                <p class="text-gray-400 text-sm font-bold mt-1 uppercase tracking-widest">Recommended Execution Pathway</p>
                            </div>

                            <div class="space-y-4">
                                <div v-for="action in displayActions" :key="action.title" class="flex gap-4 group cursor-pointer">
                                    <div class="w-5 h-5 rounded-full bg-emerald-500/20 border border-emerald-500/50 flex flex-shrink-0 items-center justify-center text-xs text-emerald-400 mt-1">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <p class="text-xs font-bold text-gray-200 group-hover:text-emerald-400 transition-all">{{ action.title }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-12 grid grid-cols-2 gap-4">
                            <button class="px-6 py-4 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-black text-xs font-black transition-all shadow-xl shadow-emerald-500/20">Execute All Approved</button>
                            <button class="px-6 py-4 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-black transition-all">Customize AI Model</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Stats Area -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div v-for="stat in displayAiStats" :key="stat.label" class="bg-white/60 backdrop-blur-xl p-6 rounded-3xl border border-white shadow-xl shadow-emerald-500/5 group hover:border-emerald-200 transition-all cursor-pointer">
                <p class="text-gray-500 text-sm font-black uppercase tracking-widest leading-none">{{ stat.label }}</p>
                <div class="flex items-end justify-between mt-3">
                    <p class="text-2xl font-black text-gray-900 leading-none">{{ stat.value }}</p>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs group-hover:bg-emerald-500 group-hover:text-white transition-all">
                        <i :class="['fas', stat.icon]"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    insights: { type: Array, default: () => [] },
    actions: { type: Array, default: () => [] },
    ai_stats: { type: Array, default: () => [] }
});

const displayInsights = computed(() => props.insights.length > 0 ? props.insights : [
    { title: '3 Deals at high risk of dropoff due to WhatsApp non-responsiveness', impact: '₹12.4L', confidence: 94, icon: 'fa-comments-dollar' },
    { title: '2 Enterprise customers showing usage decline (HealthScale, FinLeap)', impact: '₹4.2Cr', confidence: 88, icon: 'fa-user-nurse' },
    { title: 'Upsell opportunity in Education Segment identified from historical behavior', impact: '₹18.7L', confidence: 76, icon: 'fa-graduation-cap' },
]);

const displayActions = computed(() => props.actions.length > 0 ? props.actions : [
    { title: 'Auto-schedule a Strategic QBR for at-risk enterprise accounts next Monday.' },
    { title: 'Deploy a personalized retention WhatsApp sequence for "LMS Pro" users with low logins.' },
    { title: 'Adjust pipeline forecast factor by -8% for mid-market deals in "In Review" stage.' },
]);

const displayAiStats = computed(() => props.ai_stats.length > 0 ? props.ai_stats : [
    { label: 'Predicted Yield', value: '₹5.8Cr', icon: 'fa-chart-area' },
    { label: 'Forecast Accuracy', value: '92.4%', icon: 'fa-bullseye' },
    { label: 'AI Optimization Lift', value: '18.2%', icon: 'fa-rocket' },
    { label: 'Total Sync Nodes', value: '1,424', icon: 'fa-network-wired' },
]);
</script>
