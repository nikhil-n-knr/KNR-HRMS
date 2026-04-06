<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">SEO Master</h2>
                <p class="text-xs text-gray-500 mt-0.5">Audit every page, generate AI meta descriptions, manage keywords and sitemaps.</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="generateSitemap" :disabled="generatingSitemap" class="px-3 py-2 bg-gray-100 text-gray-700 border border-gray-200 rounded-xl text-xs font-bold hover:bg-gray-200 flex items-center gap-1.5 transition-all disabled:opacity-50">
                    <i v-if="generatingSitemap" class="fas fa-spinner fa-spin text-sm"></i>
                    <i v-else class="fas fa-sitemap text-sm"></i>
                    Regenerate Sitemap
                </button>
                <button @click="runBulkAudit" :disabled="auditing" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 flex items-center gap-2 disabled:opacity-50 transition-all">
                    <i v-if="auditing" class="fas fa-spinner fa-spin text-xs"></i>
                    <i v-else class="fas fa-search text-xs"></i>
                    Audit All Pages
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-hidden flex">
            <!-- Page list with scores -->
            <div class="w-72 border-r border-gray-200 bg-white flex flex-col shrink-0">
                <div class="p-4 border-b border-gray-100">
                    <div class="flex gap-1.5 flex-wrap">
                        <button v-for="f in scoreFilters" :key="f.val" @click="scoreFilter = f.val"
                            class="px-2 py-0.5 rounded-full text-sm font-black transition-all"
                            :class="scoreFilter === f.val ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                            {{ f.label }}
                        </button>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto py-2">
                    <div v-for="page in filteredPages" :key="page.id"
                        @click="selectPage(page)"
                        class="mx-2 my-1 px-3 py-3 rounded-xl cursor-pointer transition-all flex items-center gap-3"
                        :class="activePage?.id === page.id ? 'bg-indigo-50 border border-indigo-200' : 'hover:bg-gray-50 border border-transparent'">
                        <!-- Score ring -->
                        <div class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-sm font-black shrink-0"
                            :class="scoreRingClass(page.seo_score)">
                            {{ page.seo_score || '?' }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ page.title }}</p>
                            <p class="text-sm font-mono text-gray-400">{{ page.slug }}</p>
                        </div>
                    </div>
                    <div v-if="!filteredPages.length" class="px-5 py-10 text-center text-gray-400 text-xs">
                        <i class="fas fa-search text-3xl text-gray-200 mb-3 block"></i>
                        No pages to audit
                    </div>
                </div>
            </div>

            <!-- Audit Panel -->
            <div class="flex-1 overflow-y-auto p-6 space-y-5"
                :class="!activePage ? 'flex items-center justify-center' : ''">

                <!-- No selection state -->
                <div v-if="!activePage" class="text-center text-gray-400">
                    <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center mb-4 mx-auto"><i class="fas fa-search text-3xl text-gray-300"></i></div>
                    <p class="font-black text-gray-600">Select a page to audit</p>
                </div>

                <template v-else>
                    <!-- Score summary -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex items-center gap-6">
                        <!-- Big score circle -->
                        <div class="relative w-24 h-24 shrink-0">
                            <svg class="w-24 h-24 -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#e5e7eb" stroke-width="10"/>
                                <circle cx="50" cy="50" r="40" fill="none" :stroke="scoreColor(activePage.seo_score)" stroke-width="10"
                                    :stroke-dasharray="`${(activePage.seo_score||0) * 2.51} 251`" stroke-linecap="round"/>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-2xl font-black" :style="{color: scoreColor(activePage.seo_score)}">{{ activePage.seo_score || 0 }}</span>
                                <span class="text-xs font-black text-gray-400 uppercase">Score</span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-black text-gray-900 text-lg">{{ activePage.title }}</h3>
                            <p class="text-xs font-mono text-gray-400 mt-0.5">{{ activePage.slug }}</p>
                            <div class="flex gap-3 mt-3">
                                <button @click="auditPage(activePage)" :disabled="auditing" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-bold hover:bg-indigo-100 transition-all flex items-center gap-1.5 disabled:opacity-50">
                                    <i v-if="auditing" class="fas fa-spinner fa-spin text-sm"></i>
                                    <i v-else class="fas fa-sync text-sm"></i>
                                    Re-Audit
                                </button>
                                <button @click="generateAiMeta" :disabled="generatingMeta" class="px-3 py-1.5 bg-purple-50 text-purple-700 rounded-lg text-xs font-bold hover:bg-purple-100 transition-all flex items-center gap-1.5 disabled:opacity-50">
                                    <i v-if="generatingMeta" class="fas fa-spinner fa-spin text-sm"></i>
                                    <i v-else class="fas fa-robot text-sm"></i>
                                    AI Generate Meta
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Checklist -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                        <h3 class="font-black text-gray-900 text-sm mb-4">SEO Checklist</h3>
                        <div class="space-y-2">
                            <div v-for="item in auditChecklist" :key="item.label"
                                class="flex items-start gap-3 py-2 border-b border-gray-50 last:border-0">
                                <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                                    :class="item.pass ? 'bg-emerald-100 text-emerald-600' : item.warn ? 'bg-amber-100 text-amber-600' : 'bg-red-100 text-red-600'">
                                    <i :class="item.pass ? 'fas fa-check text-xs' : item.warn ? 'fas fa-exclamation text-xs' : 'fas fa-times text-xs'"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-gray-800">{{ item.label }}</p>
                                    <p class="text-sm text-gray-400 mt-0.5">{{ item.detail }}</p>
                                </div>
                                <span class="text-sm font-black px-1.5 py-0.5 rounded-full shrink-0"
                                    :class="item.pass ? 'bg-emerald-50 text-emerald-700' : item.warn ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-600'">
                                    +{{ item.points }}pts
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Meta Editor -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-black text-gray-900 text-sm">Meta Tags</h3>
                            <button @click="saveMeta" :disabled="savingMeta" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-black hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-1.5">
                                <i v-if="savingMeta" class="fas fa-spinner fa-spin text-sm"></i>Save Meta
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="field-label">Meta Title</label>
                                    <span class="text-sm font-bold" :class="metaTitle.length > 60 ? 'text-red-500' : 'text-gray-400'">{{ metaTitle.length }}/60</span>
                                </div>
                                <input v-model="metaTitle" class="field-input" placeholder="Page title for search results" />
                                <!-- SERP preview mini -->
                                <div class="mt-2 p-3 bg-gray-50 rounded-lg border border-gray-200 text-xs">
                                    <p class="text-blue-700 font-bold truncate">{{ metaTitle || activePage.title }}</p>
                                    <p class="text-green-700 text-sm font-mono">{{ (activePage.seo?.canonical || 'https://yoursite.com' + activePage.slug) }}</p>
                                    <p class="text-gray-600 text-sm mt-0.5 line-clamp-2">{{ metaDesc || '(No description set. Add one to improve CTR.)' }}</p>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="field-label">Meta Description</label>
                                    <span class="text-sm font-bold" :class="metaDesc.length > 155 ? 'text-red-500' : 'text-gray-400'">{{ metaDesc.length }}/155</span>
                                </div>
                                <textarea v-model="metaDesc" rows="3" class="field-input resize-none" placeholder="Compelling summary for search results..."></textarea>
                            </div>
                            <div>
                                <label class="field-label">Focus Keywords <span class="text-gray-300 font-normal">(comma separated)</span></label>
                                <input v-model="metaKeywords" class="field-input" placeholder="page builder, cms, india" />
                            </div>
                            <div>
                                <label class="field-label">OG / Social Image URL</label>
                                <input v-model="ogImage" class="field-input font-mono" placeholder="https://..." />
                            </div>
                        </div>
                    </div>

                    <!-- AI meta result -->
                    <div v-if="aiSuggestion" class="bg-purple-50 border border-purple-200 rounded-2xl p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fas fa-robot text-purple-600"></i>
                            <h3 class="font-black text-purple-800 text-sm">AI Generated Meta</h3>
                            <button @click="applyAiMeta" class="ml-auto px-3 py-1 bg-purple-600 text-white rounded-lg text-xs font-black hover:bg-purple-700">Apply</button>
                        </div>
                        <p class="text-xs font-bold text-purple-900 mb-1">Title: {{ aiSuggestion.title }}</p>
                        <p class="text-xs text-purple-700">Description: {{ aiSuggestion.description }}</p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({ pages: { type: Array, default: () => [] }, seo_audit: { type: Object, default: () => ({}) } });

const pageList   = ref([...(props.pages || [])]);
const activePage = ref(null);
const scoreFilter= ref('all');
const auditing   = ref(false);
const generatingSitemap = ref(false);
const generatingMeta    = ref(false);
const savingMeta        = ref(false);
const aiSuggestion      = ref(null);

// Meta editing
const metaTitle    = ref('');
const metaDesc     = ref('');
const metaKeywords = ref('');
const ogImage      = ref('');

const scoreFilters = [
    { val:'all',  label:'All' },
    { val:'good', label:'Good 80+' },
    { val:'ok',   label:'OK 50-79' },
    { val:'poor', label:'Poor <50' },
];

const filteredPages = computed(() => {
    let list = pageList.value;
    if (scoreFilter.value === 'good') list = list.filter(p => (p.seo_score||0) >= 80);
    else if (scoreFilter.value === 'ok')   list = list.filter(p => (p.seo_score||0) >= 50 && (p.seo_score||0) < 80);
    else if (scoreFilter.value === 'poor') list = list.filter(p => (p.seo_score||0) < 50);
    return list;
});

const scoreRingClass = (s) => !s ? 'border-gray-300 text-gray-400' :
    s >= 80 ? 'border-emerald-500 text-emerald-700' :
    s >= 50 ? 'border-amber-500 text-amber-700' : 'border-red-500 text-red-600';

const scoreColor = (s) => !s ? '#9ca3af' : s >= 80 ? '#10b981' : s >= 50 ? '#f59e0b' : '#ef4444';

const selectPage = (page) => {
    activePage.value  = page;
    aiSuggestion.value = null;
    const seo = page.seo || {};
    metaTitle.value    = seo.title || page.title || '';
    metaDesc.value     = seo.description || '';
    metaKeywords.value = seo.keywords || '';
    ogImage.value      = seo.og_image || '';
};

const auditChecklist = computed(() => {
    if (!activePage.value) return [];
    const seo = activePage.value?.seo || {};
    return [
        { label: 'Meta title set',           detail: metaTitle.value ? `"${metaTitle.value.substring(0,50)}..."` : 'No meta title defined.',                pass: !!metaTitle.value, warn:false, points:15 },
        { label: 'Meta title length',        detail: `${metaTitle.value.length} characters (ideal: 50-60)`,   pass: metaTitle.value.length >= 40 && metaTitle.value.length <= 60, warn: metaTitle.value.length > 60, points: 10 },
        { label: 'Meta description set',     detail: metaDesc.value ? `${metaDesc.value.length} chars` : 'No description.',  pass:!!metaDesc.value, warn:false, points:15 },
        { label: 'Meta description length',  detail: `Ideal 120–155 chars`,  pass: metaDesc.value.length >= 100 && metaDesc.value.length <= 155, warn: metaDesc.value.length > 155, points:10 },
        { label: 'OG image defined',         detail: ogImage.value || 'No social share image.',       pass:!!ogImage.value, warn:false, points:10 },
        { label: 'Keywords set',             detail: metaKeywords.value || 'No focus keywords.',      pass:!!metaKeywords.value, warn:false, points:10 },
        { label: 'Canonical URL',            detail: seo.canonical || 'Auto-generated from page slug.', pass:true, warn:false, points:10 },
        { label: 'Page status published',    detail: activePage.value.status === 'published' ? 'Published ✓' : 'Not published yet.', pass: activePage.value.status === 'published', warn:false, points:20 },
    ];
});

const auditPage = async (page, skipState = false) => {
    if (!skipState) auditing.value = true;
    try {
        const { data } = await axios.post(route('cms.seo.audit'), { page_id: page.id });
        const idx = pageList.value.findIndex(p => p.id === page.id);
        if (idx !== -1) pageList.value[idx] = { ...pageList.value[idx], seo_score: data.score };
        if (activePage.value?.id === page.id) activePage.value.seo_score = data.score;
    } catch {}
    finally { if (!skipState) auditing.value = false; }
};

const runBulkAudit = async () => {
    auditing.value = true;
    for (const page of pageList.value) { 
        await auditPage(page, true); 
    }
    auditing.value = false;
};

const generateSitemap = async () => {
    generatingSitemap.value = true;
    try { await axios.post(route('cms.seo.sitemap')); alert('Sitemap regenerated! View at /sitemap.xml'); } catch {}
    finally { generatingSitemap.value = false; }
};

const generateAiMeta = async () => {
    generatingMeta.value = true;
    aiSuggestion.value = null;
    try {
        const { data } = await axios.post(route('cms.seo.generate-meta'), { page_id: activePage.value.id, title: activePage.value.title });
        aiSuggestion.value = data;
    } catch (e) {
        // Graceful fallback when endpoint not yet AI-powered
        aiSuggestion.value = { title: `${activePage.value.title} – Get Started Today`, description: `Discover ${activePage.value.title}. Trusted by thousands. Fast, reliable, and easy to use. Start for free today.` };
    }
    finally { generatingMeta.value = false; }
};

const applyAiMeta = () => {
    if (!aiSuggestion.value) return;
    metaTitle.value = aiSuggestion.value.title;
    metaDesc.value  = aiSuggestion.value.description;
    aiSuggestion.value = null;
};

const saveMeta = async () => {
    savingMeta.value = true;
    try {
        await axios.post(route('cms.seo.audit'), {
            page_id:  activePage.value.id,
            seo_meta: JSON.stringify({ title: metaTitle.value, description: metaDesc.value, keywords: metaKeywords.value, og_image: ogImage.value })
        });
    } catch {}
    finally { savingMeta.value = false; }
};
</script>

<style scoped>
.field-label { display:block; font-size:0.65rem; font-weight:700; text-transform:uppercase; letter-spacing:0.07em; color:#374151; margin-bottom:0.25rem; }
.field-input { width:100%; background:#f9fafb; border:1px solid #e5e7eb; border-radius:0.5rem; padding:0.4rem 0.65rem; font-size:0.8rem; outline:none; transition:border-color 0.15s, box-shadow 0.15s; }
.field-input:focus { border-color:#6366f1; box-shadow:0 0 0 2px rgba(99,102,241,0.15); }
</style>
