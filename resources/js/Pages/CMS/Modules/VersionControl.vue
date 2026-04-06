<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">Version Control</h2>
                <p class="text-xs text-gray-500 mt-0.5">Browse page revision history, compare versions, and restore with one click.</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="pruneOld" :disabled="pruning" class="px-3 py-2 bg-gray-100 border border-gray-200 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-200 flex items-center gap-1.5 disabled:opacity-50">
                    <i v-if="pruning" class="fas fa-spinner fa-spin text-sm"></i>
                    <i v-else class="fas fa-broom text-sm"></i>
                    Prune Old
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-hidden flex">
            <!-- Left: Page Selector -->
            <div class="w-64 border-r border-gray-200 bg-white flex flex-col shrink-0">
                <div class="p-3 border-b border-gray-100">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                        <input v-model="pageSearch" placeholder="Filter pages..." class="w-full bg-gray-50 border border-gray-200 rounded-xl py-2 pl-8 pr-3 text-xs focus:outline-none focus:border-indigo-400" />
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto py-2">
                    <button v-for="page in filteredPages" :key="page.id"
                        @click="selectPage(page)"
                        class="w-full text-left mx-2 my-0.5 px-3 py-3 rounded-xl transition-all"
                        :style="{ width: 'calc(100% - 16px)' }"
                        :class="activePage?.id === page.id ? 'bg-indigo-50 border border-indigo-200' : 'hover:bg-gray-50 border border-transparent'">
                        <p class="text-xs font-bold truncate" :class="activePage?.id === page.id ? 'text-indigo-700' : 'text-gray-900'">{{ page.title }}</p>
                        <p class="text-sm font-mono text-gray-400 mt-0.5">{{ page.slug }}</p>
                        <p class="text-sm text-indigo-500 font-black mt-1" v-if="page.versions_count">{{ page.versions_count }} versions</p>
                    </button>
                    <div v-if="!filteredPages.length" class="p-6 text-center text-gray-400 text-xs">
                        <i class="fas fa-file text-3xl text-gray-200 mb-3 block"></i>No pages found
                    </div>
                </div>
            </div>

            <!-- Right: Timeline + Diff -->
            <div class="flex-1 overflow-hidden flex">
                <div v-if="!activePage" class="flex-1 flex flex-col items-center justify-center text-gray-400">
                    <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center mb-5">
                        <i class="fas fa-history text-3xl text-gray-300"></i>
                    </div>
                    <p class="font-black text-gray-600">Select a page to view history</p>
                </div>

                <template v-else>
                    <!-- Timeline -->
                    <div class="w-80 border-r border-gray-200 bg-white flex flex-col shrink-0">
                        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-xs font-black uppercase tracking-widest text-gray-500">Revision History</h3>
                            <span class="text-sm font-black bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">
                                {{ pageVersions.length }} snapshots
                            </span>
                        </div>
                        <div v-if="loadingVersions" class="flex items-center justify-center py-10 text-gray-400">
                            <i class="fas fa-spinner fa-spin mr-2"></i><span class="text-xs">Loading...</span>
                        </div>
                        <div v-else class="flex-1 overflow-y-auto py-3 px-3 space-y-1 relative">
                            <!-- Timeline line -->
                            <div class="absolute left-7 top-6 bottom-6 w-0.5 bg-gray-100 rounded-full"></div>

                            <div v-for="(v, i) in pageVersions" :key="v.id"
                                @click="selectVersion(v)"
                                class="relative flex items-start gap-3 cursor-pointer group pl-2">
                                <!-- Timeline dot -->
                                <div class="w-4 h-4 rounded-full border-2 shrink-0 mt-2 relative z-10 transition-all"
                                    :class="selectedVersion?.id === v.id ? 'bg-indigo-600 border-indigo-600 scale-125' :
                                            i === 0 ? 'bg-emerald-500 border-emerald-500' : 'bg-white border-gray-300 group-hover:border-indigo-400'">
                                </div>
                                <div class="flex-1 px-3 py-2.5 rounded-xl transition-all"
                                    :class="selectedVersion?.id === v.id ? 'bg-indigo-50 border border-indigo-200' : 'bg-gray-50 border border-transparent group-hover:border-gray-200'">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-black text-gray-500 uppercase tracking-wider">v{{ v.version_number }}</span>
                                        <span v-if="i === 0" class="text-xs font-black bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full">CURRENT</span>
                                    </div>
                                    <p class="text-xs font-bold text-gray-800 mt-0.5">{{ v.label || 'Auto-save' }}</p>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <div class="w-4 h-4 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                                            <span class="text-xs font-black text-indigo-700">{{ (v.created_by_name || 'U').charAt(0) }}</span>
                                        </div>
                                        <span class="text-sm text-gray-500 font-medium">{{ v.created_by_name || 'Unknown' }}</span>
                                        <span class="text-sm text-gray-400 ml-auto">{{ relativeDate(v.created_at) }}</span>
                                    </div>
                                    <div v-if="v.change_summary" class="mt-1.5 text-sm text-gray-500 italic truncate">
                                        {{ v.change_summary }}
                                    </div>
                                </div>
                            </div>

                            <div v-if="!pageVersions.length" class="text-center text-gray-400 py-10">
                                <i class="fas fa-clock text-3xl text-gray-200 mb-3 block"></i>
                                <p class="text-xs font-bold">No versions yet</p>
                                <p class="text-sm mt-1">Save the page to start tracking.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Diff / Restore panel -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-5">
                        <div v-if="!selectedVersion" class="flex flex-col items-center justify-center h-full text-gray-400">
                            <i class="fas fa-code-branch text-4xl text-gray-200 mb-3"></i>
                            <p class="font-bold text-gray-500">Select a version to preview</p>
                        </div>

                        <template v-else>
                            <!-- Version header card -->
                            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex items-start gap-5">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex flex-col items-center justify-center shrink-0">
                                    <span class="text-sm font-black text-indigo-300 uppercase tracking-wider">ver</span>
                                    <span class="text-lg font-black text-white leading-none">{{ selectedVersion.version_number }}</span>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-black text-gray-900">{{ selectedVersion.label || 'Auto-save snapshot' }}</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Saved by <span class="font-bold text-gray-700">{{ selectedVersion.created_by_name || 'Unknown' }}</span>
                                        · {{ relativeDate(selectedVersion.created_at) }}
                                    </p>
                                    <p v-if="selectedVersion.change_summary" class="text-xs text-gray-400 italic mt-1">{{ selectedVersion.change_summary }}</p>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <button @click="restoreVersion" :disabled="restoring"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-black hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-1.5 shadow-sm">
                                        <i v-if="restoring" class="fas fa-spinner fa-spin text-sm"></i>
                                        <i v-else class="fas fa-undo text-sm"></i>
                                        Restore
                                    </button>
                                    <button @click="previewVersion"
                                        class="px-4 py-2 bg-gray-100 text-gray-700 border border-gray-200 rounded-xl text-xs font-bold hover:bg-gray-200 flex items-center gap-1.5">
                                        <i class="fas fa-eye text-sm"></i>
                                        Preview
                                    </button>
                                </div>
                            </div>

                            <!-- Block summary -->
                            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                                <h3 class="font-black text-gray-900 text-sm mb-4">Blocks in this version</h3>
                                <div class="space-y-2">
                                    <div v-for="(block, i) in versionBlocks" :key="i"
                                        class="flex items-center gap-3 px-3 py-2 rounded-xl bg-gray-50 border border-gray-100">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                            :style="{ background: blockBg(block.type), color: blockColor(block.type) }">
                                            <i :class="blockIcon(block.type)" class="text-sm"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-gray-800 capitalize">{{ block.type?.replace(/_/g,' ') }}</p>
                                            <p class="text-sm text-gray-400 truncate">{{ blockPreview(block) }}</p>
                                        </div>
                                        <span class="text-sm font-bold text-gray-400 uppercase">block {{ i+1 }}</span>
                                    </div>
                                    <div v-if="!versionBlocks.length" class="text-center text-gray-400 py-4 text-xs">No block data in this version.</div>
                                </div>
                            </div>

                            <!-- Restore confirmation -->
                            <div v-if="restoreSuccess" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center gap-3">
                                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                                <div>
                                    <p class="font-black text-emerald-800 text-sm">Version Restored!</p>
                                    <p class="text-xs text-emerald-600">Page has been rolled back to version {{ selectedVersion.version_number }}.</p>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({ versions: { type: Array, default: () => [] }, pages: { type: Array, default: () => [] } });

const pageList       = ref([...(props.pages || [])]);
const activePage     = ref(null);
const pageVersions   = ref([]);
const selectedVersion = ref(null);
const pageSearch     = ref('');
const loadingVersions = ref(false);
const restoring      = ref(false);
const pruning        = ref(false);
const restoreSuccess = ref(false);

const filteredPages = computed(() =>
    !pageSearch.value ? pageList.value : pageList.value.filter(p =>
        p.title?.toLowerCase().includes(pageSearch.value.toLowerCase()) || p.slug?.includes(pageSearch.value)
    )
);

const relativeDate = (d) => {
    if (!d) return '';
    const diff = Math.floor((Date.now() - new Date(d)) / 1000);
    if (diff < 60)    return 'Just now';
    if (diff < 3600)  return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
    return new Date(d).toLocaleDateString('en-IN');
};

const selectPage = async (page) => {
    activePage.value     = page;
    selectedVersion.value = null;
    restoreSuccess.value  = false;
    loadingVersions.value = true;
    try {
        const { data } = await axios.get(route('cms.pages.versions', page.id));
        pageVersions.value = data;
    } catch (e) {
        // Fallback to prop versions filtered by page
        pageVersions.value = (props.versions || []).filter(v => v.page_id === page.id);
    }
    finally { loadingVersions.value = false; }
};

const selectVersion = (v) => { selectedVersion.value = v; restoreSuccess.value = false; };

const versionBlocks = computed(() => {
    if (!selectedVersion.value?.layout_data) return [];
    const ld = selectedVersion.value.layout_data;
    // layout_data may be already an array (JSON cast) or a JSON string
    if (Array.isArray(ld)) return ld;
    if (ld?.blocks && Array.isArray(ld.blocks)) return ld.blocks;
    try { const p = JSON.parse(ld); return Array.isArray(p) ? p : (p?.blocks || []); } catch { return []; }
});

const blockIcon  = (type) => ({ hero:'fas fa-image', feature_grid:'fas fa-th', text_block:'fas fa-font', testimonial:'fas fa-quote-left', pricing:'fas fa-tag', gallery:'fas fa-images', cta:'fas fa-bullhorn', navbar:'fas fa-bars', footer:'fas fa-shoe-prints' }[type] || 'fas fa-puzzle-piece');
const blockBg    = (type) => ({ hero:'#eef2ff', feature_grid:'#f0f9ff', text_block:'#f9fafb', testimonial:'#fdf2f8', pricing:'#fffbeb', gallery:'#ecfdf5', cta:'#fef2f2' }[type] || '#f3f4f6');
const blockColor = (type) => ({ hero:'#6366f1', feature_grid:'#0ea5e9', text_block:'#6b7280', testimonial:'#ec4899', pricing:'#f59e0b', gallery:'#10b981', cta:'#ef4444' }[type] || '#9ca3af');
const blockPreview = (b) => b?.content?.title || b?.content?.text || b?.content?.heading || '(no preview)';

const restoreVersion = async () => {
    if (!confirm(`Restore this version? Current content will be saved as a new snapshot first.`)) return;
    restoring.value = true;
    try {
        await axios.post(route('cms.pages.versions.restore', { version: selectedVersion.value.id }));
        restoreSuccess.value = true;
        setTimeout(() => restoreSuccess.value = false, 5000);
        await selectPage(activePage.value); // Reload versions
    } catch (e) { alert('Restore failed: ' + (e?.response?.data?.message || e.message)); }
    finally { restoring.value = false; }
};

const previewVersion = () => {
    window.open('/sites/?version=' + selectedVersion.value.id, '_blank');
};

const pruneOld = async () => {
    if (!confirm('Delete all but the latest 20 versions per page?')) return;
    pruning.value = true;
    try { await axios.post(route('cms.pages.versions.prune')); alert('Old versions pruned!'); } catch {}
    finally { pruning.value = false; }
};
</script>
