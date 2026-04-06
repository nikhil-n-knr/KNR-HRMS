<template>
    <div class="min-h-screen bg-white font-sans text-gray-900 selection:bg-emerald-100 selection:text-emerald-900">
        <!-- Premium Navigation -->
        <nav class="h-20 bg-white/80 backdrop-blur-2xl border-b border-gray-100 sticky top-0 z-50 px-6 md:px-12 flex items-center justify-between">
            <div class="flex items-center gap-10">
                <Link :href="route('lms.store.catalog')" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-emerald-100 group-hover:rotate-12 transition-all">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <div>
                        <span class="text-xl font-black tracking-tighter leading-none block">DEEP LMS</span>
                        <span class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.2em] leading-none">Intelligence Engine</span>
                    </div>
                </Link>
                
                <div class="hidden lg:flex items-center gap-8">
                    <button v-for="cat in categories.slice(0, 5)" 
                        :key="cat.id" 
                        @click="setCategory(cat.slug)"
                        :class="['text-xs font-black uppercase tracking-widest transition-all',
                        activeCategory === cat.slug ? 'text-emerald-600' : 'text-gray-400 hover:text-emerald-600']">
                        {{ cat.name }}
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <div class="hidden md:flex relative group">
                    <input 
                        type="text" 
                        v-model="searchQuery"
                        placeholder="What do you want to master today?" 
                        class="pl-12 pr-6 py-3 bg-gray-50 border-none rounded-2xl text-[10px] font-bold text-gray-700 w-80 focus:ring-2 focus:ring-emerald-100 transition-all group-hover:bg-white border-transparent focus:border-emerald-100 border"
                    />
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-emerald-500 transition-colors"></i>
                </div>
                
                <template v-if="!$page.props.auth.user">
                    <Link :href="route('lms.store.login')" class="text-xs font-black text-gray-900 uppercase tracking-widest hover:text-emerald-600 transition-colors">Log In</Link>
                    <Link :href="route('lms.store.register')" class="text-xs font-black text-gray-900 uppercase tracking-widest hover:text-emerald-600 transition-colors">Register</Link>
                    <Link :href="route('lms.store.pricing')" class="px-8 py-3 bg-gray-900 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-black transition-all shadow-xl shadow-gray-200">Start Free Trial</Link>
                </template>
                <template v-else>
                    <Link :href="route('lms.store.dashboard')" class="px-8 py-3 bg-emerald-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-xl shadow-emerald-100">My Dashboard</Link>
                </template>
            </div>
        </nav>

        <!-- Hero Section: High Impact -->
        <section class="relative pt-24 pb-20 px-6 md:px-12 bg-white overflow-hidden group">
            <div class="absolute inset-0 bg-[radial-gradient(#10b981_0.5px,transparent_0.5px)] [background-size:20px_20px] opacity-[0.05]"></div>
            
            <div class="max-w-7xl mx-auto flex flex-col items-center text-center space-y-10 relative z-10">
                <div class="inline-flex items-center gap-3 px-6 py-2 bg-emerald-50 rounded-full border border-emerald-100 shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[10px] font-black text-emerald-700 uppercase tracking-[0.2em] italic">System Status: 1,429 Active Nodes</span>
                </div>
                
                <h2 class="text-6xl md:text-8xl font-black text-gray-900 tracking-tighter leading-[0.85] italic uppercase">
                    YOUR FUTURE, <br/>
                    <span class="text-emerald-600 underline underline-offset-[12px] decoration-[16px] decoration-emerald-100/50">ENGINEERED.</span>
                </h2>
                
                <p class="text-xl font-medium text-gray-400 max-w-2xl leading-relaxed italic">
                    The advanced LMS for those who demand mastery. From neural networks to quantum logic, accelerate your career with deep-tech immersion.
                </p>

                <div class="flex items-center gap-6 pt-4">
                    <button class="px-12 py-5 bg-gray-900 text-white rounded-[2rem] text-sm font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-2xl transform active:scale-95 group/btn flex items-center gap-4">
                        Explore Protocols
                        <i class="fas fa-arrow-down group-hover/btn:translate-y-1 transition-transform"></i>
                    </button>
                    <div class="flex -space-x-4">
                        <div v-for="i in 5" :key="i" class="w-12 h-12 rounded-full border-4 border-white bg-gray-100 flex items-center justify-center text-[10px] font-black text-gray-400 shadow-md">JD</div>
                        <div class="w-12 h-12 rounded-full border-4 border-white bg-emerald-600 flex items-center justify-center text-[10px] font-black text-white shadow-md">+8k</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Course Discovery Hub -->
        <main class="max-w-7xl mx-auto px-6 md:px-12 py-32 space-y-20">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 border-b border-gray-100 pb-12">
                <div class="space-y-2">
                    <h3 class="text-3xl font-black text-gray-900 tracking-tighter uppercase italic leading-none">DEEP REPOSITORY</h3>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest italic">Filtering {{ courses.total }} active intelligence tracks</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto overflow-x-auto pb-4 md:pb-0 custom-scrollbar">
                    <button v-for="cat in filterCategories" :key="cat.name"
                        @click="setCategory(cat.slug)"
                        :class="['px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all border shadow-sm whitespace-nowrap',
                        activeCategory === cat.slug ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-400 border-gray-100 hover:border-emerald-200 hover:text-emerald-600']">
                        {{ cat.name }}
                    </button>
                </div>
            </div>

            <!-- Course Grid -->
            <div :class="[
                'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-12 transition-all duration-1000 ease-in-out',
                isLoading ? 'opacity-20 scale-[0.98] blur-xl pointer-events-none' : 'opacity-100 scale-100 blur-0'
            ]">
                <div 
                    v-for="course in courses.data" 
                    :key="course.id" 
                    @click="router.visit(route('lms.store.course.show', { course: course.slug }))"
                    class="group flex flex-col cursor-pointer relative"
                >
                    <!-- Card Media -->
                    <div class="aspect-[4/5] bg-gray-100 rounded-[4rem] overflow-hidden relative shadow-sm group-hover:shadow-[0_40px_80px_-20px_rgba(16,185,129,0.15)] group-hover:-translate-y-4 transition-all duration-700 border border-gray-50">
                        <img v-if="course.thumbnail" :src="course.thumbnail" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 grayscale group-hover:grayscale-0" alt="Thumbnail" />
                        <div v-else class="w-full h-full bg-gradient-to-tr from-gray-900 to-gray-800 flex items-center justify-center text-white/10 group-hover:text-emerald-500/20 transition-colors">
                             <i class="fas fa-microchip text-6xl"></i>
                        </div>

                        <!-- Action HUD Overlay -->
                        <div class="absolute inset-x-8 bottom-8 z-20 translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-500 delay-75">
                             <button class="w-full py-5 bg-white text-gray-900 rounded-[2rem] text-[10px] font-black uppercase tracking-widest shadow-2xl hover:bg-emerald-600 hover:text-white transition-all transform hover:scale-105 active:scale-95">
                                 Initiate Mastery
                             </button>
                        </div>

                        <!-- Info Badges -->
                        <div class="absolute top-8 left-8 flex flex-col gap-2">
                             <span class="text-[9px] font-black text-white uppercase tracking-widest px-4 py-2 bg-black/40 backdrop-blur-xl border border-white/10 rounded-xl">
                                {{ course.category?.name || 'GEN' }}
                             </span>
                        </div>
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
                    </div>

                    <div class="mt-8 space-y-4 px-4 text-center">
                        <h4 class="text-xl font-black text-gray-900 tracking-tighter uppercase italic leading-tight group-hover:text-emerald-700 transition-colors">
                            {{ course.title }}
                        </h4>
                        <div class="flex items-center justify-center gap-4">
                            <div class="flex items-center gap-1.5 text-orange-400">
                                <i class="fas fa-star text-[10px]"></i>
                                <span class="text-[10px] font-black italic">4.9</span>
                            </div>
                            <span class="w-1 h-1 bg-gray-200 rounded-full"></span>
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest italic leading-none">By Master Architect</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State Case -->
            <div v-if="!courses.data.length" class="py-24 flex flex-col items-center justify-center gap-6 border-2 border-dashed border-gray-100 rounded-[3rem]">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center text-gray-300">
                    <i class="fas fa-search text-3xl"></i>
                </div>
                <div class="text-center">
                    <p class="text-sm font-black text-gray-900 uppercase tracking-widest mb-1">No Courses Detected</p>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Try adjusting your spectral filters</p>
                </div>
            </div>
        </main>

        <!-- Premium Footer -->
        <footer class="bg-gray-900 text-white py-24 px-6 md:px-12 mt-24">
            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-12 border-b border-white/10 pb-20">
                <div class="col-span-1 md:col-span-2 space-y-8">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <span class="text-xl font-black tracking-tighter italic">DEEP LMS</span>
                    </div>
                    <p class="text-sm text-white/50 max-w-sm leading-relaxed font-medium">
                        Empowering the world's most ambitious professionals to master complex skills through deep immersion and high-integrity learning.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-white/10 transition-colors"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-white/10 transition-colors"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-white/10 transition-colors"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                
                <div class="space-y-6">
                    <h5 class="text-xs font-black uppercase tracking-[0.3em]">Knowledge Alpha</h5>
                    <ul class="space-y-4">
                        <li><a href="#" class="text-xs font-bold text-white/50 hover:text-white transition-colors uppercase tracking-widest">All Courses</a></li>
                        <li><a href="#" class="text-xs font-bold text-white/50 hover:text-white transition-colors uppercase tracking-widest">Enterprise Hub</a></li>
                        <li><a href="#" class="text-xs font-bold text-white/50 hover:text-white transition-colors uppercase tracking-widest">Become an Instructor</a></li>
                    </ul>
                </div>

                <div class="space-y-6">
                    <h5 class="text-xs font-black uppercase tracking-[0.3em]">Legal & Core</h5>
                    <ul class="space-y-4">
                        <li><a href="#" class="text-xs font-bold text-white/50 hover:text-white transition-colors uppercase tracking-widest">Privacy Protocol</a></li>
                        <li><a href="#" class="text-xs font-bold text-white/50 hover:text-white transition-colors uppercase tracking-widest">Terms of Service</a></li>
                        <li><a href="#" class="text-xs font-bold text-white/50 hover:text-white transition-colors uppercase tracking-widest">License Agreement</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="max-w-7xl mx-auto pt-10 flex flex-col md:flex-row items-center justify-between gap-6">
                <p class="text-[9px] font-black uppercase tracking-[0.4em] text-white/30">© 2026 DEEP LMS. ALL RIGHTS SECURED.</p>
                <div class="flex items-center gap-2">
                    <i class="fas fa-wifi text-emerald-500 text-[8px] animate-pulse"></i>
                    <p class="text-[9px] font-black uppercase tracking-[0.4em] text-white/30">SYSTEMS FULLY OPERATIONAL</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';

const props = defineProps({
    courses: Object,
    categories: Array,
    filters: Object
});

const searchQuery = ref(props.filters.search || '');
const activeCategory = ref(props.filters.category || '');
const isLoading = ref(false);

const filterCategories = computed(() => {
    return [{ name: 'All Tracks', slug: '' }, ...props.categories];
});

const fetchResults = debounce(() => {
    isLoading.value = true;
    router.get(route('lms.store.catalog'), { search: searchQuery.value, category: activeCategory.value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
        onFinish: () => { isLoading.value = false; }
    });
}, 300);

const setCategory = (slug) => {
    activeCategory.value = slug;
    fetchResults();
};

watch(searchQuery, () => fetchResults());
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');

.font-sans {
    font-family: 'Outfit', sans-serif;
}

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
</style>
