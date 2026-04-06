<template>
    <header class="bg-white/80 backdrop-blur-xl border-b border-gray-100 px-8 py-4 flex flex-col gap-6 sticky top-0 z-40 shadow-sm">
        <div class="flex items-center justify-between gap-8">
            <!-- Title & Search -->
            <div class="flex items-center gap-10 flex-1">
                <div>
                    <h1 class="text-xl font-black text-gray-900 tracking-tight leading-none mb-1">{{ title }}</h1>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none">LMS Strategic Command</p>
                </div>
                <!-- Global Metrics Rapid View -->
                <div class="hidden lg:flex items-center gap-10">
                    <div v-for="(val, key) in hKpis" :key="key" class="flex flex-col">
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1.5">{{ key.replace(/_/g, ' ') }}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-black text-gray-800 leading-none tabular-nums tracking-tight">
                                {{ formatKpi(key, val) }}
                            </span>
                            <div v-if="key === 'total_revenue'" class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-lg shadow-emerald-200"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-4">
                <div class="relative group">
                    <input 
                        type="text" 
                        placeholder="Neural search data..." 
                        class="pl-10 pr-4 py-2.5 bg-gray-50/50 border-none rounded-2xl text-[10px] font-bold text-gray-600 focus:ring-2 focus:ring-emerald-100 transition-all w-64 group-hover:bg-white"
                    />
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-xs group-hover:text-emerald-500 transition-colors"></i>
                </div>
                <button class="w-11 h-11 rounded-2xl bg-white border border-gray-100 flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:border-emerald-100 transition-all group">
                    <i class="far fa-bell transform group-hover:rotate-12"></i>
                </button>
                <div class="h-10 w-px bg-gray-100 mx-2"></div>
                
                <div class="flex items-center gap-2">
                    <Link :href="route('lms.store.catalog')" class="px-5 py-2.5 bg-white border border-emerald-100 text-emerald-600 rounded-2xl text-[10px] font-black hover:bg-emerald-50 transition-all flex items-center gap-2 uppercase tracking-widest shadow-sm">
                        <i class="fas fa-shopping-bag"></i>
                        Student Catalog
                    </Link>
                    <Link :href="route('lms.learn.hub')" class="px-5 py-2.5 bg-white border border-emerald-100 text-emerald-600 rounded-2xl text-[10px] font-black hover:bg-emerald-50 transition-all flex items-center gap-2 uppercase tracking-widest shadow-sm">
                        <i class="fas fa-graduation-cap"></i>
                        Student Hub
                    </Link>
                </div>

                <div class="h-10 w-px bg-gray-100 mx-2"></div>

                <button class="px-5 py-2.5 bg-emerald-600 text-white rounded-2xl text-[10px] font-black shadow-lg shadow-emerald-100 hover:bg-emerald-700 hover:-translate-y-0.5 transition-all flex items-center gap-2 uppercase tracking-widest">
                    <i class="fas fa-sparkles"></i>
                    AI Generator
                </button>
            </div>
        </div>

        <!-- Tab Strip -->
        <div class="flex items-center gap-8 -mb-4 overflow-x-auto no-scrollbar">
            <button 
                v-for="t in tabs" 
                :key="t.id"
                @click="$emit('tab-change', t.id)"
                class="pb-3 text-[10px] font-black uppercase tracking-widest transition-all relative whitespace-nowrap"
                :class="activeTab === t.id ? 'text-emerald-600' : 'text-gray-400 hover:text-gray-600'"
            >
                {{ t.label }}
                <!-- Underline Indicator -->
                <div 
                    v-if="activeTab === t.id" 
                    class="absolute bottom-0 left-0 right-0 h-1 bg-emerald-600 rounded-t-full shadow-lg shadow-emerald-200"
                ></div>
            </button>
        </div>
    </header>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    title: String,
    activeTab: String,
    tabs: Array,
    activeSection: String,
    globalKpis: Object
});

const emit = defineEmits(['tab-change']);

const hKpis = computed(() => {
    return {
        'total_revenue': props.globalKpis.total_revenue || 0,
        'active_learners': props.globalKpis.active_learners || 0,
        'completion_rate': props.globalKpis.completion_rate || 0,
        'total_courses': props.globalKpis.course_count || 0
    };
});

const formatKpi = (key, val) => {
    if (key === 'total_revenue') {
        return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(val);
    }
    if (key === 'completion_rate') {
        return `${Number(val).toFixed(1)}%`;
    }
    return val.toLocaleString();
};
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
