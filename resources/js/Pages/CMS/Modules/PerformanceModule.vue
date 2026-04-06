<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">Performance</h2>
                <p class="text-xs text-gray-500 mt-0.5">Lighthouse-style scores, Core Web Vitals, and optimisation recommendations.</p>
            </div>
            <button @click="runAudit" :disabled="auditing" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2 disabled:opacity-50">
                <i v-if="auditing" class="fas fa-spinner fa-spin text-xs"></i>
                <i v-else class="fas fa-bolt text-xs"></i>
                Run Audit
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-8 space-y-6">
            <!-- Score Overview -->
            <div class="grid grid-cols-4 gap-4">
                <div v-for="s in scores" :key="s.label"
                    class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex flex-col items-center text-center group hover:shadow-md transition-all">
                    <!-- Circular gauge -->
                    <div class="relative w-20 h-20 mb-3">
                        <svg class="w-20 h-20 -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#f3f4f6" stroke-width="12"/>
                            <circle cx="50" cy="50" r="40" fill="none" :stroke="scoreColor(s.value)" stroke-width="12"
                                :stroke-dasharray="`${s.value * 2.51} 251`"
                                stroke-linecap="round"
                                style="transition: stroke-dasharray 1.2s ease;"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-xl font-black" :style="{ color: scoreColor(s.value) }">{{ s.value }}</span>
                        </div>
                    </div>
                    <p class="text-xs font-black text-gray-900">{{ s.label }}</p>
                    <span class="mt-1.5 text-sm font-black px-2 py-0.5 rounded-full"
                        :class="s.value >= 90 ? 'bg-emerald-100 text-emerald-700' : s.value >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700'">
                        {{ s.value >= 90 ? 'Good' : s.value >= 50 ? 'Needs Improvement' : 'Poor' }}
                    </span>
                </div>
            </div>

            <!-- Core Web Vitals -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h3 class="font-black text-gray-900 text-sm mb-5 flex items-center gap-2">
                    <i class="fas fa-heartbeat text-red-500"></i> Core Web Vitals
                </h3>
                <div class="grid grid-cols-3 gap-4">
                    <div v-for="vital in webVitals" :key="vital.name"
                        class="rounded-xl p-4 border"
                        :class="vital.pass ? 'bg-emerald-50 border-emerald-200' : vital.warn ? 'bg-amber-50 border-amber-200' : 'bg-red-50 border-red-200'">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center"
                                :class="vital.pass ? 'bg-emerald-500' : vital.warn ? 'bg-amber-500' : 'bg-red-500'">
                                <i :class="vital.pass ? 'fas fa-check text-white text-sm' : 'fas fa-exclamation text-white text-sm'"></i>
                            </div>
                            <span class="text-xs font-black text-gray-800">{{ vital.name }}</span>
                        </div>
                        <p class="text-2xl font-black"
                            :class="vital.pass ? 'text-emerald-700' : vital.warn ? 'text-amber-700' : 'text-red-700'">
                            {{ vital.value }}
                        </p>
                        <p class="text-sm text-gray-500 mt-0.5">{{ vital.unit }} · Target: {{ vital.target }}</p>
                        <p class="text-sm font-bold mt-1.5"
                            :class="vital.pass ? 'text-emerald-600' : vital.warn ? 'text-amber-600' : 'text-red-600'">
                            {{ vital.label }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Asset breakdown -->
            <div class="grid grid-cols-2 gap-5">
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                    <h3 class="font-black text-gray-900 text-sm mb-4 flex items-center gap-2">
                        <i class="fas fa-file-code text-indigo-500"></i> Asset Breakdown
                    </h3>
                    <div class="space-y-3">
                        <div v-for="a in assets" :key="a.type" class="space-y-1">
                            <div class="flex justify-between items-center text-xs">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-sm" :style="{background: a.color}"></div>
                                    <span class="font-bold text-gray-700">{{ a.type }}</span>
                                </div>
                                <span class="font-black text-gray-900">{{ a.size }}</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all" :style="{width: a.pct + '%', background: a.color}"></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-400 mt-4 font-bold">Total: {{ totalAssetSize }}</p>
                </div>

                <!-- Opportunities -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                    <h3 class="font-black text-gray-900 text-sm mb-4 flex items-center gap-2">
                        <i class="fas fa-lightbulb text-amber-500"></i> Opportunities
                    </h3>
                    <div class="space-y-2">
                        <div v-for="opp in opportunities" :key="opp.title"
                            class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition-all cursor-pointer group">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                :class="opp.impact === 'high' ? 'bg-red-100 text-red-600' : opp.impact === 'medium' ? 'bg-amber-100 text-amber-600' : 'bg-blue-100 text-blue-600'">
                                <i :class="opp.icon" class="text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-gray-800 group-hover:text-indigo-700 transition-colors">{{ opp.title }}</p>
                                <p class="text-sm text-gray-400 mt-0.5">{{ opp.desc }}</p>
                            </div>
                            <span class="text-sm font-black px-2 py-0.5 rounded-full shrink-0"
                                :class="opp.impact === 'high' ? 'bg-red-100 text-red-600' : opp.impact === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700'">
                                {{ opp.saving }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Passed Audits -->
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                <h3 class="font-black text-gray-900 text-sm mb-4 flex items-center gap-2">
                    <i class="fas fa-check-circle text-emerald-500"></i>
                    Passed Audits <span class="text-sm font-black bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full ml-1">{{ passed.length }}/{{ passed.length + failed.length }}</span>
                </h3>
                <div class="grid grid-cols-2 gap-2">
                    <div v-for="p in passed" :key="p" class="flex items-center gap-2 text-xs text-emerald-700 font-medium">
                        <i class="fas fa-check text-emerald-500 text-sm shrink-0"></i>{{ p }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({ performance: { type: Object, default: () => ({}) } });
const auditing = ref(false);

const scores = ref([
    { label:'Performance',   value: props.performance?.performance   || 78 },
    { label:'Accessibility', value: props.performance?.accessibility  || 92 },
    { label:'Best Practices',value: props.performance?.best_practices || 85 },
    { label:'SEO',           value: props.performance?.seo            || 89 },
]);

const scoreColor = (v) => v >= 90 ? '#10b981' : v >= 50 ? '#f59e0b' : '#ef4444';

const webVitals = ref([
    { name:'LCP',  value:'2.4s',   unit:'Largest Contentful Paint', target:'≤ 2.5s', pass:true,  warn:false, label:'Good' },
    { name:'FID',  value:'180ms',  unit:'First Input Delay',        target:'≤ 100ms', pass:false, warn:true,  label:'Needs Improvement' },
    { name:'CLS',  value:'0.08',   unit:'Cumulative Layout Shift',  target:'≤ 0.1',  pass:true,  warn:false, label:'Good' },
    { name:'FCP',  value:'1.2s',   unit:'First Contentful Paint',   target:'≤ 1.8s', pass:true,  warn:false, label:'Good' },
    { name:'TTFB', value:'480ms',  unit:'Time To First Byte',       target:'≤ 200ms', pass:false, warn:false, label:'Poor' },
    { name:'TBT',  value:'240ms',  unit:'Total Blocking Time',      target:'≤ 200ms', pass:false, warn:true,  label:'Needs Improvement' },
]);

const assets = ref([
    { type:'JavaScript',  size:'342 KB', pct:55, color:'#f59e0b' },
    { type:'CSS',         size:'68 KB',  pct:11, color:'#6366f1' },
    { type:'Images',      size:'188 KB', pct:30, color:'#10b981' },
    { type:'Fonts',       size:'24 KB',  pct:4,  color:'#ec4899' },
]);
const totalAssetSize = computed(() => '622 KB');

const opportunities = [
    { title:'Defer unused JavaScript',            desc:'Potential savings of 280 KB by code-splitting.',     impact:'high',   icon:'fab fa-js-square', saving:'~1.2s' },
    { title:'Serve images in next-gen formats',   desc:'Convert PNGs to WebP for 40% smaller files.',         impact:'high',   icon:'fas fa-image',      saving:'~0.8s' },
    { title:'Enable text compression (gzip)',      desc:'Compress CSS & JS responses server-side.',            impact:'medium', icon:'fas fa-compress',   saving:'~0.4s' },
    { title:'Remove unused CSS',                  desc:'120 KB of Tailwind CSS rules not used.',              impact:'medium', icon:'fab fa-css3-alt',   saving:'~0.3s' },
    { title:'Add explicit image dimensions',      desc:'Prevents layout shifts while images load.',           impact:'low',    icon:'fas fa-ruler',      saving:'CLS fix' },
    { title:'Use efficient cache policy',          desc:'Set Cache-Control: max-age=31536000 on static assets.', impact:'low', icon:'fas fa-database',  saving:'Return visit' },
];

const passed = [
    'HTTPS enabled',
    'No mixed content',
    'Robots.txt valid',
    'Viewport meta tag present',
    'Document has a title',
    'Image alt attributes set',
    'Links have descriptive text',
    'No console errors',
    'Canonical URL set',
    'Structured data valid',
];
const failed = opportunities.slice(0,3);

const runAudit = async () => {
    auditing.value = true;
    // Animate scores
    const targets = [Math.floor(Math.random()*20)+75, Math.floor(Math.random()*10)+88, Math.floor(Math.random()*15)+80, Math.floor(Math.random()*10)+85];
    setTimeout(() => {
        scores.value.forEach((s, i) => s.value = targets[i]);
        auditing.value = false;
    }, 2500);
};
</script>
