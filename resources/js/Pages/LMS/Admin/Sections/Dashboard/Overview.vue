<template>
    <div class="space-y-12 animate-fade-in font-sans pb-20">
        <!-- Conditional View Rendering -->
        <div v-if="activeTab === 'overview' || !activeTab" class="space-y-12">
            <!-- Strategic Header: Professional Oversight -->
            <div class="flex items-center justify-between border-b border-emerald-100 pb-8">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight uppercase italic mb-1">LEARNING HUB OVERVIEW</h2>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Global strategic monitoring of all intelligence vectors</p>
                </div>
                <div class="flex items-center gap-4">
                    <button @click="router.visit(route('lms.store.catalog'))" class="px-6 py-3 bg-emerald-50 text-emerald-700 rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-emerald-100 transition-all flex items-center gap-2">
                        <i class="fas fa-external-link-alt"></i> View Catalog
                    </button>
                    <button @click="router.visit(route('lms.store.dashboard'))" class="px-6 py-3 bg-gray-900 text-white rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-black transition-all flex items-center gap-2">
                        <i class="fas fa-house-user"></i> Student Hub
                    </button>
                </div>
            </div>

            <!-- Top Strategic Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="kpi in kpiCards" :key="kpi.label" class="bg-white p-8 rounded-[3.5rem] border border-gray-100 shadow-sm hover:shadow-2xl hover:border-emerald-100 transition-all group overflow-hidden relative">
                    <div class="flex flex-col gap-6 relative z-10">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-xl transition-all shadow-sm" :class="kpi.bg">
                            <i :class="kpi.icon"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1.5 font-sans">{{ kpi.label }}</p>
                            <h3 class="text-3xl font-black text-gray-900 group-hover:text-emerald-600 transition-colors italic tracking-tighter tabular-nums">{{ kpi.value }}</h3>
                        </div>
                    </div>
                    <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-emerald-50 rounded-full opacity-40 group-hover:scale-150 transition-transform duration-1000"></div>
                </div>
            </div>

            <!-- Quick Control Vector -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                 <button v-for="btn in quickActions" :key="btn.label" class="p-6 bg-white border border-gray-100 rounded-[2.5rem] hover:border-emerald-300 hover:shadow-xl transition-all group text-left space-y-4">
                     <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                         <i :class="btn.icon"></i>
                     </div>
                     <p class="text-[9px] font-black text-gray-900 uppercase tracking-widest leading-tight">{{ btn.label }}</p>
                 </button>
            </div>

            <!-- Analytical Engine -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <div class="lg:col-span-2 bg-white p-10 rounded-[4rem] border border-gray-100 shadow-sm relative group overflow-hidden">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight uppercase italic mb-8">Growth Intelligence Feed</h3>
                    <div class="h-[350px] w-full bg-gray-50/70 rounded-[3rem] flex flex-col items-center justify-center border border-gray-100 relative overflow-hidden">
                        <i class="fas fa-chart-line text-4xl text-emerald-500 animate-pulse mb-4"></i>
                        <p class="text-sm font-black text-gray-900 uppercase tracking-widest">Visualizing 30-Day Delta Matrix...</p>
                        <!-- Mock data lines -->
                        <div class="absolute inset-x-10 bottom-10 h-32 flex items-end justify-between opacity-10">
                            <div v-for="i in 20" :key="i" class="w-2 bg-emerald-600 rounded-t-full" :style="{ height: `${Math.random()*100}%` }"></div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-900 p-10 rounded-[3.5rem] text-white shadow-2xl relative overflow-hidden group">
                    <h3 class="text-lg font-black tracking-tight uppercase italic mb-8 border-b border-white/10 pb-4">Elite Hub Assets</h3>
                    <div class="space-y-8">
                        <div v-for="course in popularCourses.slice(0, 4)" :key="course.id" class="flex items-center gap-5 group/item cursor-pointer">
                            <div class="w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex flex-col items-center justify-center group-hover/item:bg-emerald-600 transition-all duration-500">
                                <span class="text-xs font-black">{{ course.enrolled_count }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-black uppercase italic group-hover/item:text-emerald-400 transition-colors truncate">{{ course.title }}</p>
                                <p class="text-[8px] font-bold text-white/30 truncate">By {{ course.creator?.name || 'Hub' }}</p>
                            </div>
                        </div>
                    </div>
                    <!-- Ornament -->
                    <div class="absolute -right-20 -top-20 w-[300px] h-[300px] bg-emerald-500/10 rounded-full blur-[100px] pointer-events-none"></div>
                </div>
            </div>
        </div>

        <!-- Strategic Insights Section -->
        <div v-else-if="activeTab === 'insights'" class="space-y-12">
            <h3 class="text-xl font-black text-gray-900 tracking-tight uppercase italic mb-8 border-l-4 border-emerald-500 pl-6 text-gray-900">AI-Driven Strategic Insights</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div v-for="i in 4" :key="i" class="bg-gradient-to-br from-emerald-50 to-white p-10 rounded-[3.5rem] border border-emerald-100 shadow-xl shadow-emerald-900/5 group hover:border-emerald-400 transition-all flex items-start gap-8">
                    <div class="w-16 h-16 rounded-[2rem] bg-emerald-600 flex items-center justify-center text-white text-2xl group-hover:rotate-12 transition-transform shadow-lg shadow-emerald-100">
                         <i class="fas fa-brain"></i>
                    </div>
                    <div class="flex-1 space-y-4">
                        <h4 class="text-md font-black text-gray-900 uppercase italic">Recommendation Vector #{{ 700 + i }}</h4>
                        <p class="text-[11px] font-medium text-gray-600 leading-relaxed italic font-sans border-l-2 border-emerald-100 pl-6 py-2">
                            "Protocol spike identified in {{ i < 3 ? 'Backend Logic' : 'Product Design' }} clusters. Initiate cohort expansion for premium certification bundling."
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cloud Health Section -->
        <div v-else-if="activeTab === 'health'" class="p-20 bg-gray-900 rounded-[4rem] text-white shadow-2xl relative overflow-hidden group flex flex-col items-center justify-center gap-10">
            <div class="w-24 h-24 rounded-full bg-emerald-600 flex items-center justify-center text-white text-4xl shadow-lg shadow-emerald-500/20 animate-pulse">
                <i class="fas fa-heartbeat"></i>
            </div>
            <div class="text-center space-y-4 relative z-10">
                <h3 class="text-3xl font-black italic tracking-tighter uppercase leading-none">System Operational Stability: 99.99%</h3>
                <p class="text-[10px] font-bold text-emerald-400 uppercase tracking-[0.4em] font-sans">Distributed CDN Status & API Latency monitoring Active</p>
            </div>
            <div class="w-full max-w-2xl h-1 bg-white/10 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 w-[99%]"></div>
            </div>
            <!-- Glow -->
            <div class="absolute -right-40 -bottom-40 w-[600px] h-[600px] bg-emerald-500/5 rounded-full blur-[150px] pointer-events-none"></div>
        </div>

    </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    activeTab: String
});

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(val || 0);

const kpiCards = computed(() => [
    { label: 'Total Treasury', value: formatCurrency(props.stats?.global_kpis?.total_revenue), icon: 'fas fa-sack-dollar', bg: 'bg-emerald-50 text-emerald-600' },
    { label: 'Active Minds', value: (props.stats?.global_kpis?.active_learners || 0).toLocaleString(), icon: 'fas fa-brain', bg: 'bg-emerald-50 text-emerald-600' },
    { label: 'Content Assets', value: props.stats?.global_kpis?.course_count || 0, icon: 'fas fa-cubes', bg: 'bg-emerald-50 text-emerald-600' },
    { label: 'Mastery Quota', value: `${Number(props.stats?.global_kpis?.completion_rate || 0).toFixed(1)}%`, icon: 'fas fa-shield-check', bg: 'bg-emerald-50 text-emerald-600' },
]);

const popularCourses = computed(() => props.stats?.popular_courses || []);

const quickActions = [
    { label: 'Global Syllabus Forge', icon: 'fas fa-plus-circle' },
    { label: 'Mass Enrollment Matrix', icon: 'fas fa-users-cog' },
    { label: 'Revenue Diagnostic', icon: 'fas fa-file-invoice-dollar' },
    { label: 'Audit Mastery Ledger', icon: 'fas fa-clipboard-check' },
    { label: 'Broadcast Neural Alert', icon: 'fas fa-bell' },
];
</script>


<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }
</style>

