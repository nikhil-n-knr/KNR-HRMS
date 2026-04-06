<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div class="flex items-center gap-6">
                <div>
                    <h2 class="text-xl font-black text-gray-900 tracking-tight">Site Content</h2>
                    <p class="text-sm text-gray-500 font-bold uppercase tracking-widest mt-0.5">Pages & Categories</p>
                </div>
                
                <!-- Tabs -->
                <div class="flex bg-gray-100 rounded-xl p-1 gap-1 border border-gray-200" v-if="activeSite?.type === 'ecommerce'">
                    <button @click="activeTab = 'pages'" 
                        class="px-5 py-1.5 rounded-lg text-xs font-black uppercase tracking-widest transition-all"
                        :class="activeTab === 'pages' ? 'bg-white shadow relative z-10 text-indigo-700' : 'text-gray-400 hover:text-gray-700'">
                        Static Pages
                    </button>
                    <button @click="activeTab = 'categories'" 
                        class="px-5 py-1.5 rounded-lg text-xs font-black uppercase tracking-widest transition-all"
                        :class="activeTab === 'categories' ? 'bg-white shadow relative z-10 text-indigo-700' : 'text-gray-400 hover:text-gray-700'">
                        E-commerce Categories
                    </button>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                    <input v-model="search" type="text" :placeholder="activeTab === 'pages' ? 'Search pages...' : 'Search categories...'" class="w-56 bg-gray-50 border border-gray-200 rounded-xl py-2 pl-9 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" />
                </div>
                <button @click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2">
                    <i class="fas fa-plus"></i>{{ activeTab === 'pages' ? 'New Page' : 'New Category' }}
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-8">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="py-3 px-5 text-sm font-black uppercase tracking-widest text-gray-500 w-2/5">{{ activeTab === 'pages' ? 'Page' : 'Category' }}</th>
                            <th class="py-3 px-5 text-sm font-black uppercase tracking-widest text-gray-500">Status</th>
                            <th v-if="activeTab === 'pages'" class="py-3 px-5 text-sm font-black uppercase tracking-widest text-gray-500">SEO</th>
                            <th v-if="activeTab === 'categories'" class="py-3 px-5 text-sm font-black uppercase tracking-widest text-gray-500">Sub-category Of</th>
                            <th class="py-3 px-5 text-sm font-black uppercase tracking-widest text-gray-500">Modified</th>
                            <th class="py-3 px-5 text-sm font-black uppercase tracking-widest text-gray-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in filtered" :key="item.id" class="hover:bg-gray-50 transition-colors group">
                            <!-- Title / Info -->
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                                        :style="{ background: pageColor(item.slug).bg, color: pageColor(item.slug).text }">
                                        <i v-if="activeTab === 'pages'" :class="item.slug === '/' ? 'fas fa-home' : 'fas fa-file-alt'" class="text-sm"></i>
                                        <i v-else class="fas fa-folder text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors text-sm">{{ item.title || item.name }}</p>
                                        <p class="text-sm text-gray-400 font-mono mt-0.5">{{ item.slug }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-5">
                                <button v-if="activeTab === 'pages'" @click="togglePublish(item)">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-sm font-black border transition-all"
                                        :class="item.status === 'published' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100'">
                                        <span class="w-1.5 h-1.5 rounded-full inline-block" :class="item.status === 'published' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                                        {{ item.status === 'published' ? 'Live' : 'Draft' }}
                                    </span>
                                </button>
                                <button v-else @click="toggleCategoryStatus(item)">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-sm font-black border transition-all"
                                        :class="item.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100'">
                                        <span class="w-1.5 h-1.5 rounded-full inline-block" :class="item.is_active ? 'bg-emerald-500' : 'bg-red-500'"></span>
                                        {{ item.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </button>
                            </td>

                            <!-- Extra Column (SEO or Parent) -->
                            <td v-if="activeTab === 'pages'" class="py-3.5 px-5">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-black"
                                        :class="seoColor(item.seo_score)">
                                        {{ item.seo_score || '—' }}
                                    </div>
                                    <span class="text-sm text-gray-400">{{ seoLabel(item.seo_score) }}</span>
                                </div>
                            </td>
                            <td v-else class="py-3.5 px-5">
                                <span v-if="item.parent_id" class="px-2 py-0.5 rounded border border-gray-200 bg-gray-50 text-sm font-bold text-gray-500">
                                    {{ getCategoryName(item.parent_id) }}
                                </span>
                                <span v-else class="text-sm font-bold text-gray-400 uppercase tracking-widest">Root</span>
                            </td>

                            <!-- Date -->
                            <td class="py-3.5 px-5">
                                <p class="text-xs font-semibold text-gray-700">{{ relativeDate(item.updated_at) }}</p>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button @click="editItem(item)" title="Edit" class="action-btn hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50"><i class="fas fa-pen text-sm"></i></button>
                                    <button v-if="activeTab === 'pages'" @click="clonePage(item)" title="Clone" class="action-btn hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50"><i class="fas fa-copy text-sm"></i></button>
                                    <button @click="deleteItem(item)" title="Delete" class="action-btn hover:text-red-600 hover:border-red-200 hover:bg-red-50"><i class="fas fa-trash text-sm"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!filtered.length">
                            <td :colspan="activeTab === 'pages' ? 5 : 5" class="py-16 text-center text-gray-400">
                                <i :class="activeTab === 'pages' ? 'fas fa-file-alt' : 'fas fa-folder-open'" class="text-3xl text-gray-200 mb-3 block"></i>
                                <p class="font-bold">No {{ activeTab }} found</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create / Edit Modal -->
        <div v-if="modal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto" @click.self="modal = null">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-7">
                <h3 class="text-base font-black text-gray-900 mb-5">{{ modal.id ? 'Edit' : 'Create' }} {{ activeTab === 'pages' ? 'Page' : 'Category' }}</h3>
                <div class="space-y-4">
                    <template v-if="activeTab === 'pages'">
                        <div>
                            <label class="field-label">Page Title</label>
                            <input v-model="modal.title" type="text" placeholder="About Us" class="field-input" @input="autoSlug" />
                        </div>
                        <div>
                            <label class="field-label">URL Slug</label>
                            <input v-model="modal.slug" type="text" placeholder="/about-us" class="field-input font-mono" />
                        </div>
                        <div>
                            <label class="field-label">Description <span class="text-gray-300 font-normal">(for SEO)</span></label>
                            <textarea v-model="modal.seo_description" rows="2" class="field-input resize-none" placeholder="Short description for search engines..."></textarea>
                        </div>
                    </template>
                    <template v-else>
                        <div>
                            <label class="field-label">Category Name</label>
                            <input v-model="modal.name" type="text" placeholder="Electronics" class="field-input" @input="autoSlug" />
                        </div>
                        <div>
                            <label class="field-label">URL Slug</label>
                            <input v-model="modal.slug" type="text" placeholder="/category/electronics" class="field-input font-mono" />
                        </div>
                        <div>
                            <label class="field-label">Parent Category</label>
                            <select v-model="modal.parent_id" class="field-input mt-1">
                                <option :value="null">None (Root Level)</option>
                                <option v-for="c in categoriesList.filter(x => x.id !== modal.id)" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Description</label>
                            <textarea v-model="modal.description" rows="2" class="field-input resize-none" placeholder="Category description..."></textarea>
                        </div>
                    </template>
                </div>
                <div class="flex gap-3 mt-6">
                    <button @click="modal = null" class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button @click="saveItem" :disabled="saving" class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 disabled:opacity-50 flex justify-center items-center gap-2">
                        <i v-if="saving" class="fas fa-spinner fa-spin text-xs"></i>
                        {{ modal.id ? 'Save Changes' : 'Create' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props  = defineProps({ 
    pages: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    activeSite: { type: Object, default: null }
});

const activeTab = ref('pages');
const pagesList = ref([...(props.pages || [])]);
const categoriesList = ref([...(props.categories || [])]);

watch(() => props.pages, (val) => { pagesList.value = [...(val || [])]; }, { deep: true });
watch(() => props.categories, (val) => { categoriesList.value = [...(val || [])]; }, { deep: true });

const search = ref('');
const modal  = ref(null);
const saving = ref(false);

const currentList = computed(() => activeTab.value === 'pages' ? pagesList.value : categoriesList.value);

const filtered = computed(() =>
    !search.value ? currentList.value : currentList.value.filter(p => {
        const title = (p.title || p.name || '').toLowerCase();
        const slug = (p.slug || '').toLowerCase();
        const q = search.value.toLowerCase();
        return title.includes(q) || slug.includes(q);
    })
);

const pageColors = ['#eef2ff/#6366f1','#ecfdf5/#10b981','#fffbeb/#f59e0b','#fdf2f8/#ec4899','#f0f9ff/#0ea5e9'];
const pageColor = (slug) => {
    if (!slug) return { bg: '#f3f4f6', text: '#9ca3af' };
    const i = Math.abs([...slug].reduce((a, c) => a + c.charCodeAt(0), 0)) % pageColors.length;
    const [bg, text] = pageColors[i].split('/');
    return { bg, text };
};

const seoColor = (score) => {
    if (!score) return 'border-gray-200 text-gray-400';
    if (score >= 80) return 'border-emerald-500 text-emerald-600';
    if (score >= 50) return 'border-amber-500 text-amber-600';
    return 'border-red-500 text-red-600';
};
const seoLabel = (score) => !score ? '' : score >= 80 ? 'Good' : score >= 50 ? 'Needs work' : 'Poor';
const relativeDate = (d) => {
    if (!d) return '';
    const diff = Math.floor((Date.now() - new Date(d)) / 1000);
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    return new Date(d).toLocaleDateString('en-IN');
};

const getCategoryName = (id) => categoriesList.value.find(c => c.id === id)?.name || 'Unknown';

const openCreate = () => { 
    if (activeTab.value === 'pages') {
        modal.value = { title: '', slug: '/', seo_description: '' }; 
    } else {
        modal.value = { name: '', slug: '/category/', parent_id: null, description: '' };
    }
};

const editItem = (item) => { modal.value = { ...item }; };

const autoSlug = () => {
    if (!modal.value.id) {
        if (activeTab.value === 'pages') {
            modal.value.slug = '/' + modal.value.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        } else {
            const prefix = modal.value.parent_id ? '/category/sub/' : '/category/';
            modal.value.slug = prefix + modal.value.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        }
    }
};

const saveItem = async () => {
    saving.value = true;
    try {
        if (activeTab.value === 'pages') {
            const payload = { ...modal.value, site_id: props.activeSite?.id };
            if (modal.value.id) {
                const { data } = await axios.put(route('cms.pages.update', modal.value.id), payload);
                const idx = pagesList.value.findIndex(p => p.id === data.id); if (idx !== -1) pagesList.value[idx] = data;
            } else {
                const { data } = await axios.post(route('cms.pages.store'), payload);
                pagesList.value.unshift(data);
            }
        } else {
            const payload = { ...modal.value, site_id: props.activeSite?.id };
            if (modal.value.id) {
                const { data } = await axios.put(route('cms.product-categories.update', modal.value.id), payload);
                const idx = categoriesList.value.findIndex(p => p.id === data.id); if (idx !== -1) categoriesList.value[idx] = data;
            } else {
                const { data } = await axios.post(route('cms.product-categories.store'), payload);
                categoriesList.value.unshift(data);
            }
        }
        modal.value = null;
    } catch (e) { alert(e?.response?.data?.message || 'Save failed'); }
    finally { saving.value = false; }
};

const togglePublish = async (page) => {
    try {
        await axios.post(route('cms.pages.publish', page.id));
        page.status = page.status === 'published' ? 'draft' : 'published';
    } catch {}
};

const toggleCategoryStatus = async (cat) => {
    try {
        const payload = { is_active: !cat.is_active };
        const { data } = await axios.put(route('cms.product-categories.update', cat.id), payload);
        cat.is_active = data.is_active;
    } catch {}
};

const clonePage = async (page) => {
    try {
        const { data } = await axios.post(route('cms.pages.clone', page.id));
        pagesList.value.unshift(data);
    } catch {}
};

const deleteItem = async (item) => {
    const title = item.title || item.name;
    if (!confirm(`Delete "${title}"?`)) return;
    try {
        if (activeTab.value === 'pages') {
            await axios.delete(route('cms.pages.destroy', item.id));
            pagesList.value = pagesList.value.filter(p => p.id !== item.id);
        } else {
            await axios.delete(route('cms.product-categories.destroy', item.id));
            categoriesList.value = categoriesList.value.filter(p => p.id !== item.id);
        }
    } catch {}
};
</script>

<style scoped>
.field-label { display: block; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #374151; margin-bottom: 0.25rem; }
.field-input { width: 100%; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 0.5rem 0.75rem; font-size: 0.875rem; outline: none; transition: border-color 0.15s, box-shadow 0.15s; }
.field-input:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,0.15); }
.action-btn { width: 1.75rem; height: 1.75rem; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; border: 1px solid #e5e7eb; background: #fff; color: #9ca3af; transition: all 0.15s; cursor: pointer; }
</style>
