<template>
    <div class="h-14 bg-white/95 backdrop-blur-sm border-b border-gray-100 px-4 flex items-center justify-between sticky top-0 z-20 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)]">
        
        <!-- Left Section: Title & Global Search -->
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-gray-50 rounded-xl border border-gray-100 shadow-sm relative group cursor-pointer">
                    <i :class="['fas fa-layer-group text-emerald-500 text-sm']"></i>
                    
                    <!-- Site Switcher Dropdown (Hover) -->
                    <div v-if="sites && sites.length > 1" class="absolute top-8 left-0 w-56 bg-white border border-gray-200 shadow-xl rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden transform origin-top-left scale-95 group-hover:scale-100">
                        <div class="px-3 py-2 bg-gray-50 border-b border-gray-100">
                            <p class="text-sm font-black tracking-widest uppercase text-gray-500">Switch Workspace</p>
                        </div>
                        <div class="max-h-60 overflow-y-auto">
                            <Link v-for="site in sites" :key="site.id" 
                               :href="`/cms/hub?section=${currentSection}&site_id=${site.id}`"
                               class="flex flex-col px-3 py-2 border-b border-gray-50 hover:bg-emerald-50 cursor-pointer transition-colors"
                               :class="activeSite?.id === site.id ? 'bg-emerald-50/50' : ''">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full" :class="site.status === 'live' ? 'bg-emerald-500' : 'bg-amber-400'"></div>
                                    <span class="text-xs font-bold text-gray-800" :class="activeSite?.id === site.id ? 'text-emerald-700' : ''">{{ site.name }}</span>
                                </div>
                                <span class="pl-4 text-sm text-gray-400 uppercase tracking-wider">{{ site.type }}</span>
                            </Link>
                        </div>
                    </div>
                </div>
                <div>
                    <h2 class="text-sm font-black text-gray-900 tracking-tight">{{ sectionName }}</h2>
                    <div class="flex items-center gap-2 mt-0.5">
                        <p class="text-sm text-emerald-500 font-extrabold uppercase tracking-widest">KNR Office Builder</p>
                        <span v-if="activeSite" class="text-sm font-bold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200 cursor-help" title="Active Site Workspace">
                            <i class="fas fa-globe mr-1 text-gray-400"></i>{{ activeSite.name }}
                            <i v-if="sites && sites.length > 1" class="fas fa-chevron-down ml-1 text-xs opacity-70"></i>
                        </span>
                    </div>
                </div>
            </div>

            <!-- AI Global Search -->
            <div class="hidden xl:flex items-center bg-gray-50 border border-gray-100 rounded-2xl px-4 py-1.5 w-80 group focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all relative">
                <i class="fas fa-search text-gray-400 text-sm group-focus-within:text-emerald-500"></i>
                <input v-model="searchQuery" autocomplete="off" type="text" placeholder='AI Search: "Find hero sections"' class="bg-transparent border-none text-xs font-bold text-gray-800 focus:ring-0 ml-2 w-full placeholder:text-gray-400">
                
                <!-- Results Dropdown -->
                <div v-if="searchQuery && results.length" class="absolute top-11 left-0 w-96 bg-white border border-gray-200 shadow-2xl rounded-2xl overflow-hidden z-50">
                    <div class="px-4 py-3 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                        <span class="text-sm font-black uppercase tracking-widest text-gray-400">Search Results</span>
                        <span class="text-sm font-bold text-gray-500">{{ results.length }} found</span>
                    </div>
                    <div class="max-h-[400px] overflow-y-auto">
                        <div v-for="r in results" :key="r.id" @click="navigate(r)" class="px-4 py-3 border-b border-gray-50 hover:bg-indigo-50 cursor-pointer flex items-center gap-3 group transition-colors">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" :style="{background: r.bg, color: r.color}">
                                <i :class="r.icon" class="text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-gray-900 group-hover:text-indigo-600 truncate">{{ r.name }}</p>
                                <p class="text-sm text-gray-400 font-medium uppercase tracking-wider">{{ r.type }}</p>
                            </div>
                            <i class="fas fa-chevron-right text-sm text-gray-300 group-hover:text-indigo-400"></i>
                        </div>
                    </div>
                    <div class="px-4 py-2 bg-indigo-600 text-white text-sm font-black text-center cursor-pointer hover:bg-indigo-700">
                        View All Results
                    </div>
                </div>
            </div>
        </div>

        <!-- Center: Builder Stats (Performance/Traffic) -->
        <div class="hidden lg:flex items-center bg-gray-50/80 backdrop-blur rounded-2xl border border-gray-100 p-1 divide-x divide-gray-200/60 shadow-inner">
            <div class="px-4 flex items-center gap-2">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.8)]"></div>
                <div>
                    <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Live Status</p>
                    <p class="text-xs font-black text-gray-900 leading-none mt-0.5">Online</p>
                </div>
            </div>
            
            <div class="px-4 flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-blue-50 flex items-center justify-center">
                    <i class="fas fa-chart-line text-blue-500 text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Today's Traffic</p>
                    <p class="text-xs font-black text-blue-600 leading-none mt-0.5">{{ kpis?.views_today?.toLocaleString() || '0' }} Views</p>
                </div>
            </div>

            <div class="px-4 flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-orange-50 flex items-center justify-center">
                    <i class="fas fa-save text-orange-500 text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Auto-Saved</p>
                    <p class="text-xs font-black text-orange-600 leading-none mt-0.5">{{ kpis?.last_saved || 'Just now' }}</p>
                </div>
            </div>
        </div>

        <!-- Right Section: Actions & Profile -->
        <div class="flex items-center gap-3">
            <button class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:text-emerald-500 hover:bg-emerald-50 transition-colors border border-gray-100">
                <i class="fas fa-bell text-base"></i>
                <div class="absolute top-1.5 right-1.5 w-1.5 h-1.5 rounded-full bg-red-500 border-2 border-white"></div>
            </button>
            <div class="pl-3 border-l border-gray-100">
                <div class="flex items-center gap-2 cursor-pointer group">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-black text-gray-900 group-hover:text-emerald-600 transition-colors">{{ $page.props.auth.user.name }}</p>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-0.5">Master Builder</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    currentSection: { type: String, required: true },
    kpis: Object,
    sites: Array,
    activeSite: Object
});

const searchQuery = ref('');

const modules = [
    { id: 'products', name: 'Product Catalog', icon: 'fas fa-boxes', type: 'Module', color: '#6366f1', bg: '#eef2ff' },
    { id: 'orders', name: 'Orders Manager', icon: 'fas fa-shopping-bag', type: 'Module', color: '#10b981', bg: '#ecfdf5' },
    { id: 'user_segments', name: 'User Segments', icon: 'fas fa-users', type: 'Module', color: '#ec4899', bg: '#fdf2f8' },
    { id: 'analytics', name: 'Analytics Stats', icon: 'fas fa-chart-line', type: 'Module', color: '#6366f1', bg: '#eef2ff' },
];

const results = computed(() => {
    if (!searchQuery.value) return [];
    const q = searchQuery.value.toLowerCase();
    const res = [];

    // Search Sites
    props.sites?.forEach(s => {
        if (s.name.toLowerCase().includes(q)) {
            res.push({ id: `site_${s.id}`, name: s.name, type: 'Site', icon: 'fas fa-globe', color: s.color, bg: `${s.color}11`, meta: { site_id: s.id } });
        }
    });

    // Search Modules
    modules.forEach(m => {
        if (m.name.toLowerCase().includes(q)) {
            res.push({ ...m, id: `mod_${m.id}` });
        }
    });

    return res.slice(0, 8);
});

const navigate = (r) => {
    searchQuery.value = '';
    if (r.type === 'Site') {
        router.visit(`/cms/hub?section=${props.currentSection}&site_id=${r.meta.site_id}`);
    } else if (r.type === 'Module') {
        router.visit(`/cms/hub?section=${r.id}`);
    }
};

const sectionName = computed(() => {
    const formatted = props.currentSection.replace(/_/g, ' ');
    return formatted.charAt(0).toUpperCase() + formatted.slice(1);
});
</script>
