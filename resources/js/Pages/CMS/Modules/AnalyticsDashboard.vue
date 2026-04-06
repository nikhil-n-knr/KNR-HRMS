<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">Analytics</h2>
                <p class="text-xs text-gray-500 mt-0.5">Live traffic, device breakdown, top pages, and ecom revenue.</p>
            </div>
            <div class="flex gap-2">
                <button v-for="r in ranges" :key="r.val" @click="range = r.val"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                    :class="range === r.val ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                    {{ r.label }}
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-8 space-y-6">
            <!-- KPI Cards -->
            <div class="grid grid-cols-4 gap-4">
                <div v-for="k in kpis" :key="k.label"
                    class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-all group">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" :style="{background: k.bg, color: k.color}">
                            <i :class="k.icon" class="text-base"></i>
                        </div>
                        <span class="text-sm font-black px-2 py-0.5 rounded-full"
                            :class="k.change >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'">
                            {{ k.change >= 0 ? '+' : '' }}{{ k.change }}%
                        </span>
                    </div>
                    <p class="text-2xl font-black text-gray-900">{{ k.value }}</p>
                    <p class="text-sm font-bold text-gray-400 uppercase tracking-wider mt-0.5">{{ k.label }}</p>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-3 gap-5">
                <!-- Sparkline traffic -->
                <div class="col-span-2 bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                    <h3 class="font-black text-gray-900 text-sm mb-4">Page Views</h3>
                    <div class="h-40 flex items-end gap-1.5">
                        <div v-for="(v, i) in chartData" :key="i"
                            class="flex-1 rounded-t-lg transition-all hover:opacity-80 cursor-pointer group relative"
                            :style="{ height: (v / maxChart * 100) + '%', background: 'linear-gradient(180deg,#6366f1,#818cf8)' }">
                            <div class="absolute -top-6 left-1/2 -translate-x-1/2 text-sm font-black text-gray-500 opacity-0 group-hover:opacity-100 whitespace-nowrap">{{ v }}</div>
                        </div>
                    </div>
                    <div class="flex justify-between mt-2 text-sm text-gray-400 font-bold">
                        <span v-for="d in chartDays" :key="d">{{ d }}</span>
                    </div>
                </div>

                <!-- Device breakdown -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                    <h3 class="font-black text-gray-900 text-sm mb-4">Devices</h3>
                    <div class="space-y-3">
                        <div v-for="d in devices" :key="d.label" class="space-y-1">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-1.5 text-xs font-bold text-gray-700">
                                    <i :class="d.icon" :style="{color:d.color}" class="text-xs"></i>{{ d.label }}
                                </div>
                                <span class="text-xs font-black text-gray-900">{{ d.pct }}%</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all" :style="{ width: d.pct + '%', background: d.color }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Pages + Sources -->
            <div class="grid grid-cols-2 gap-5">
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                    <h3 class="font-black text-gray-900 text-sm mb-4">Top Pages</h3>
                    <div class="space-y-2">
                        <div v-for="(p, i) in topPages" :key="p.url" class="flex items-center gap-3 group">
                            <span class="text-sm font-black text-gray-400 w-5 text-right">{{ i+1 }}</span>
                            <p class="flex-1 text-xs font-mono text-gray-700 truncate group-hover:text-indigo-600 transition-colors">{{ p.url }}</p>
                            <span class="text-xs font-black text-gray-900">{{ (p.views||0).toLocaleString() }}</span>
                        </div>
                        <p v-if="!topPages.length" class="text-xs text-gray-400 italic">No data yet — add tracking snippet.</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                    <h3 class="font-black text-gray-900 text-sm mb-4">Traffic Sources</h3>
                    <div class="space-y-3">
                        <div v-for="s in sources" :key="s.label" class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" :style="{background: s.bg}">
                                <i :class="s.icon" :style="{color: s.color}" class="text-xs"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between text-xs font-bold text-gray-700 mb-0.5">
                                    <span>{{ s.label }}</span><span>{{ s.pct }}%</span>
                                </div>
                                <div class="h-1 bg-gray-100 rounded-full"><div class="h-full rounded-full" :style="{width:s.pct+'%', background:s.color}"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({ analytics: { type: Object, default: () => ({}) } });
const currentAnalytics = ref({ ...props.analytics });
const range = ref('7d');
const ranges = [{ val:'7d', label:'7 Days' },{ val:'30d', label:'30 Days' },{ val:'90d', label:'90 Days' }];

const fetchAnalytics = async () => {
    try {
        const { data } = await axios.get(route('cms.analytics.overview'), { params: { range: range.value } });
        currentAnalytics.value = data;
    } catch (e) { console.error('Failed to fetch analytics', e); }
};

onMounted(() => {
    fetchAnalytics();
});

// KPIs from currentAnalytics or mock
const kpis = computed(() => [
    { label:'Page Views',    value:(currentAnalytics.value?.pageviews||12847).toLocaleString(),    change:+18, icon:'fas fa-eye',          color:'#6366f1', bg:'#eef2ff' },
    { label:'Unique Visitors', value:(currentAnalytics.value?.visitors||3291).toLocaleString(),   change:+9,  icon:'fas fa-users',         color:'#10b981', bg:'#ecfdf5' },
    { label:'Revenue',       value:'₹'+(currentAnalytics.value?.revenue||284600).toLocaleString('en-IN'), change:+34, icon:'fas fa-rupee-sign', color:'#f59e0b', bg:'#fffbeb' },
    { label:'Bounce Rate',   value:(currentAnalytics.value?.bounce_rate||38)+'%',                  change:-5,  icon:'fas fa-mouse-pointer', color:'#ec4899', bg:'#fdf2f8' },
]);

const chartData = ref([420,380,610,550,720,680,840]);
const maxChart  = computed(() => Math.max(...chartData.value));
const chartDays = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

const devices = ref([
    { label:'Mobile',  pct:58, icon:'fas fa-mobile-alt', color:'#6366f1', bg:'#eef2ff' },
    { label:'Desktop', pct:35, icon:'fas fa-laptop',     color:'#10b981', bg:'#ecfdf5' },
    { label:'Tablet',  pct:7,  icon:'fas fa-tablet-alt', color:'#f59e0b', bg:'#fffbeb' },
]);

const topPages = computed(() => currentAnalytics.value?.top_pages || [
    { url:'/', views:4200 }, { url:'/products', views:1860 }, { url:'/about', views:983 }, { url:'/contact', views:541 },
]);

const sources = [
    { label:'Organic Search', pct:42, icon:'fab fa-google',   color:'#4285F4', bg:'#eff6ff' },
    { label:'Direct',         pct:28, icon:'fas fa-link',      color:'#10b981', bg:'#ecfdf5' },
    { label:'Social',         pct:18, icon:'fas fa-share-alt', color:'#ec4899', bg:'#fdf2f8' },
    { label:'Referral',       pct:12, icon:'fas fa-external-link-alt', color:'#f59e0b', bg:'#fffbeb' },
];
</script>
