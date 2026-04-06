<template>
    <div class="h-full flex flex-col bg-gray-50">
        <!-- Header -->
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">Site Builder</h2>
                <p class="text-xs text-gray-500 mt-0.5">Manage sites — static or full ecommerce with Razorpay.</p>
            </div>
            <button @click="openCreate" class="px-4 py-2 bg-emerald-500 text-white rounded-xl text-sm font-bold shadow-md shadow-emerald-500/20 hover:bg-emerald-600 transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> New Site
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-8 space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4">
                <div v-for="s in kpis" :key="s.label" class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" :style="{ backgroundColor: s.bg }">
                        <i :class="[s.icon, 'text-lg']" :style="{ color: s.color }"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-900">{{ s.value }}</p>
                        <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">{{ s.label }}</p>
                    </div>
                </div>
            </div>

            <!-- Sites grid -->
            <div v-if="siteList.length" class="grid grid-cols-2 xl:grid-cols-3 gap-5">
                <div v-for="site in siteList" :key="site.id"
                    class="bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-lg hover:border-indigo-200 transition-all group overflow-hidden">
                    <!-- Thumbnail / gradient header -->
                    <div class="h-36 relative flex items-center justify-center"
                        :style="{ background: site.type === 'ecommerce' ? 'linear-gradient(135deg,#4f46e5,#7c3aed)' : 'linear-gradient(135deg,#0ea5e9,#06b6d4)' }">
                        <i :class="site.type === 'ecommerce' ? 'fas fa-shopping-cart' : 'fas fa-globe'" class="text-white/30 text-5xl group-hover:scale-110 transition-transform duration-300"></i>
                        <!-- badges -->
                        <div class="absolute top-3 left-3 flex gap-1.5">
                            <span class="px-2 py-0.5 rounded-full text-sm font-black uppercase tracking-wider bg-white/20 text-white backdrop-blur-sm">
                                {{ site.type }}
                            </span>
                        </div>
                        <div class="absolute top-3 right-3">
                            <span class="px-2 py-0.5 rounded-full text-sm font-black uppercase"
                                :class="site.status === 'live' ? 'bg-emerald-400/30 text-emerald-100' : 'bg-amber-400/30 text-amber-100'">
                                <span class="inline-block w-1.5 h-1.5 rounded-full mr-1 align-middle"
                                    :class="site.status === 'live' ? 'bg-emerald-400' : 'bg-amber-400'"></span>
                                {{ site.status }}
                            </span>
                        </div>
                    </div>

                    <div class="p-4">
                        <h3 class="font-black text-gray-900 group-hover:text-indigo-600 transition-colors truncate">{{ site.name }}</h3>
                        <p class="text-xs text-gray-400 font-mono mt-0.5 truncate">{{ site.domain || site.slug || 'No domain' }}</p>

                        <div class="grid grid-cols-3 gap-1.5 mt-4">
                            <button @click="editSite(site)" class="py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-bold text-gray-600 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-600 transition-all text-center">
                                <i class="fas fa-pen mr-1"></i>Edit
                            </button>
                            <button @click="togglePublish(site)" class="py-1.5 rounded-lg text-sm font-bold transition-all text-center border"
                                :class="site.status === 'live' ? 'bg-amber-50 border-amber-200 text-amber-600 hover:bg-amber-100' : 'bg-emerald-50 border-emerald-200 text-emerald-600 hover:bg-emerald-100'">
                                <i :class="site.status === 'live' ? 'fas fa-pause' : 'fas fa-rocket'" class="mr-1"></i>
                                {{ site.status === 'live' ? 'Pause' : 'Publish' }}
                            </button>
                            <button @click="deleteSite(site)" class="py-1.5 bg-red-50 border border-red-200 rounded-lg text-sm font-bold text-red-500 hover:bg-red-100 transition-all text-center">
                                <i class="fas fa-trash mr-1"></i>Del
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty -->
            <div v-else class="flex flex-col items-center justify-center py-24 text-gray-400">
                <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center mb-5"><i class="fas fa-globe text-3xl text-gray-300"></i></div>
                <h3 class="text-lg font-black text-gray-600 mb-1">No Sites Yet</h3>
                <p class="text-sm text-gray-400 mb-6">Create your first site to get started.</p>
                <button @click="openCreate" class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2">
                    <i class="fas fa-plus"></i>Create Your First Site
                </button>
            </div>
        </div>

        <!-- Create / Edit Modal -->
        <div v-if="modal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center" @click.self="modal = null">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-[520px] p-8 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-black text-gray-900 mb-1">{{ modal.id ? 'Edit Site' : 'Create New Site' }}</h3>
                <p class="text-xs text-gray-400 mb-6">Configure your site details and mode.</p>

                <div class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="field-label">Site Name</label>
                        <input v-model="modal.name" type="text" placeholder="My Awesome Site" class="field-input" />
                    </div>
                    <!-- Slug -->
                    <div>
                        <label class="field-label">URL Slug <span class="text-gray-300 font-normal normal-case tracking-normal">(subdomain identifier)</span></label>
                        <input v-model="modal.slug" type="text" placeholder="my-site" class="field-input font-mono" />
                    </div>
                    <!-- Domain -->
                    <div>
                        <label class="field-label">Custom Domain <span class="text-gray-300 font-normal">(optional)</span></label>
                        <input v-model="modal.domain" type="text" placeholder="shop.example.com" class="field-input font-mono" />
                    </div>
                    <!-- Type selector -->
                    <div>
                        <label class="field-label mb-2">Site Type</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button v-for="t in siteTypes" :key="t.value" @click="modal.type = t.value"
                                class="p-4 border-2 rounded-xl text-left transition-all"
                                :class="modal.type === t.value ? `border-${t.color}-500 bg-${t.color}-50` : 'border-gray-200 hover:border-gray-300'">
                                <i :class="[t.icon, `text-${t.color}-500`, 'text-xl mb-2 block']"></i>
                                <p class="font-black text-sm text-gray-900">{{ t.label }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ t.desc }}</p>
                            </button>
                        </div>
                    </div>
                    <!-- Currency (ecom only) -->
                    <div v-if="modal.type === 'ecommerce'">
                        <label class="field-label">Currency</label>
                        <select v-model="modal.currency" class="field-input">
                            <option value="INR">INR — Indian Rupee (₹)</option>
                            <option value="USD">USD — US Dollar ($)</option>
                            <option value="EUR">EUR — Euro (€)</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button @click="modal = null" class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button @click="saveSite" :disabled="saving" class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all disabled:opacity-50 flex items-center justify-center gap-2">
                        <i v-if="saving" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-save"></i>
                        {{ modal.id ? 'Update Site' : 'Create Site' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({ sites: { type: Array, default: () => [] } });

const siteList = ref([...(props.sites || [])]);
const modal    = ref(null);
const saving   = ref(false);

const kpis = computed(() => [
    { label: 'Total Sites',  value: siteList.value.length,                                       icon: 'fas fa-globe',         color: '#3b82f6', bg: '#eff6ff' },
    { label: 'Live',         value: siteList.value.filter(s => s.status === 'live').length,       icon: 'fas fa-signal',        color: '#10b981', bg: '#ecfdf5' },
    { label: 'Ecom Stores',  value: siteList.value.filter(s => s.type === 'ecommerce').length,    icon: 'fas fa-shopping-cart', color: '#6366f1', bg: '#eef2ff' },
]);

const siteTypes = [
    { value: 'static',    label: 'Static Site',      desc: 'Brochure / landing pages',   icon: 'fas fa-file-alt',      color: 'blue' },
    { value: 'ecommerce', label: 'Ecommerce Store',   desc: 'Products + Razorpay cart',   icon: 'fas fa-shopping-cart', color: 'indigo' },
];

const openCreate = () => { modal.value = { name: '', slug: '', domain: '', type: 'static', currency: 'INR' }; };
const editSite   = (site) => { modal.value = { ...site }; };

const saveSite = async () => {
    saving.value = true;
    try {
        if (modal.value.id) {
            const { data } = await axios.put(route('cms.sites.update', modal.value.id), modal.value);
            const idx = siteList.value.findIndex(s => s.id === data.id);
            if (idx !== -1) siteList.value[idx] = data;
        } else {
            const { data } = await axios.post(route('cms.sites.store'), modal.value);
            siteList.value.unshift(data);
        }
        modal.value = null;
    } catch (e) { alert(e?.response?.data?.message || 'Save failed'); }
    finally { saving.value = false; }
};

const togglePublish = async (site) => {
    try {
        await axios.post(route('cms.sites.publish', site.id));
        site.status = site.status === 'live' ? 'draft' : 'live';
    } catch (e) { alert('Action failed'); }
};

const deleteSite = async (site) => {
    if (!confirm(`Delete "${site.name}"? This cannot be undone.`)) return;
    try {
        await axios.delete(route('cms.sites.destroy', site.id));
        siteList.value = siteList.value.filter(s => s.id !== site.id);
    } catch (e) { alert('Delete failed'); }
};
</script>

<style scoped>
.field-label { display: block; font-size: 0.7rem; font-weight: 700; color: #374151; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.05em; }
.field-input { width: 100%; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: border-color 0.15s, box-shadow 0.15s; outline: none; }
.field-input:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,0.15); }
</style>
