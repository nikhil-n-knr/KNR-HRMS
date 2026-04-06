<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">Multi-Site Manager</h2>
                <p class="text-xs text-gray-500 mt-0.5">Manage multiple websites from one dashboard — each with its own domain, pages, and settings.</p>
            </div>
            <button @click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2">
                <i class="fas fa-plus text-xs"></i> New Site
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-8 space-y-6">
            <!-- Summary KPIs -->
            <div class="grid grid-cols-4 gap-4">
                <div v-for="k in kpis" :key="k.label" class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" :style="{background:k.bg,color:k.color}">
                        <i :class="k.icon" class="text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ k.value }}</p>
                        <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">{{ k.label }}</p>
                    </div>
                </div>
            </div>

            <!-- Sites grid -->
            <div class="grid grid-cols-3 gap-5">
                <div v-for="site in sites" :key="site.id"
                    class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-all group">
                    <!-- Site header banner -->
                    <div class="h-20 relative flex items-end p-4"
                        :style="{ background: `linear-gradient(135deg, ${site.color}22, ${site.color}44)`, borderBottom: `3px solid ${site.color}` }">
                        <div class="absolute top-3 right-3 flex gap-1.5">
                            <span v-if="isDefault(site)" class="px-2 py-0.5 rounded-full text-sm font-black bg-amber-400 text-amber-900">
                                <i class="fas fa-star mr-0.5 text-xs"></i>DEFAULT
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-sm font-black"
                                :class="site.status === 'live' ? 'bg-emerald-500 text-white' : 'bg-gray-400 text-white'">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-white mr-1 align-middle" :class="site.status === 'live' ? 'animate-pulse' : ''"></span>
                                {{ site.status }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-black bg-white/80 text-gray-700 border">
                                {{ site.type }}
                            </span>
                        </div>
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-black text-lg shadow-lg" :style="{ background: site.color }">
                            {{ site.name.charAt(0).toUpperCase() }}
                        </div>
                    </div>

                    <div class="p-5">
                        <h3 class="font-black text-gray-900">{{ site.name }}</h3>
                        <a :href="'https://' + site.domain" target="_blank" class="text-xs font-mono text-indigo-600 hover:text-indigo-800 mt-0.5 flex items-center gap-1">
                            <i class="fas fa-external-link-alt text-xs"></i>{{ site.domain }}
                        </a>

                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-2 mt-4 mb-4">
                            <div class="text-center">
                                <p class="text-lg font-black text-gray-900">{{ site.pages }}</p>
                                <p class="text-xs font-bold text-gray-400 uppercase">Pages</p>
                            </div>
                            <div class="text-center">
                                <p class="text-lg font-black text-gray-900">{{ site.visitors }}</p>
                                <p class="text-xs font-bold text-gray-400 uppercase">Visitors</p>
                            </div>
                            <div class="text-center">
                                <p class="text-lg font-black text-gray-900">{{ site.products || '—' }}</p>
                                <p class="text-xs font-bold text-gray-400 uppercase">Products</p>
                            </div>
                        </div>

                        <!-- Mode toggle -->
                        <div class="flex items-center gap-2 mb-4 p-2.5 bg-gray-50 rounded-xl border border-gray-200">
                            <button @click="updateSiteType(site, 'static')"
                                class="flex-1 py-1 rounded-lg text-sm font-black transition-all"
                                :class="site.type === 'static' ? 'bg-white shadow-sm text-gray-900 border border-gray-100' : 'text-gray-400 hover:text-gray-600'">
                                <i class="fas fa-file-alt mr-1"></i>Static
                            </button>
                            <button @click="updateSiteType(site, 'ecommerce')"
                                class="flex-1 py-1 rounded-lg text-sm font-black transition-all"
                                :class="site.type === 'ecommerce' ? 'bg-white shadow-sm text-indigo-700 border border-gray-100' : 'text-gray-400 hover:text-indigo-500'">
                                <i class="fas fa-shopping-cart mr-1"></i>E-commerce
                            </button>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2 mb-2">
                            <!-- Preview button -->
                            <a :href="previewUrl(site)" target="_blank"
                                class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl text-sm font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-all border border-emerald-200">
                                <i class="fas fa-eye text-xs"></i> View
                            </a>
                            <button @click="toggleStatus(site)" 
                                :class="site.status === 'live' ? 'bg-amber-50 text-amber-600 border border-amber-200 hover:bg-amber-100' : 'bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 border border-gray-200'" class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl text-sm font-bold transition-all">
                                <i :class="site.status === 'live' ? 'fas fa-pause' : 'fas fa-rocket'" class="text-xs"></i> {{ site.status === 'live' ? 'Pause' : 'Publish' }}
                            </button>
                        </div>
                        <div class="flex gap-2">
                            <button @click="editSite(site)" class="flex-1 py-2 bg-indigo-50 text-indigo-700 rounded-xl text-xs font-bold hover:bg-indigo-100 transition-all border border-indigo-100">
                                <i class="fas fa-cog mr-1 text-sm"></i>Settings
                            </button>
                            <button @click="cloneSite(site)" class="px-3 py-2 bg-gray-100 text-gray-600 rounded-xl text-xs font-bold hover:bg-gray-200 transition-all" title="Clone site">
                                <i class="fas fa-copy text-sm"></i>
                            </button>
                            <button v-if="!isDefault(site)" @click="deleteSite(site)"
                                class="px-3 py-2 bg-red-50 text-red-500 rounded-xl text-xs font-bold hover:bg-red-100 transition-all" title="Delete site">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                            <div v-else class="px-3 py-2 bg-gray-50 text-gray-300 rounded-xl text-xs font-bold cursor-not-allowed" title="Default site cannot be deleted">
                                <i class="fas fa-lock text-sm"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add site card -->
                <button @click="openCreate"
                    class="bg-white rounded-2xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center py-16 text-gray-400 hover:border-indigo-300 hover:text-indigo-500 hover:bg-indigo-50/40 transition-all group">
                    <div class="w-12 h-12 rounded-2xl bg-gray-100 group-hover:bg-indigo-100 flex items-center justify-center mb-3 transition-all">
                        <i class="fas fa-plus text-xl text-gray-300 group-hover:text-indigo-500 transition-colors"></i>
                    </div>
                    <p class="font-black text-sm group-hover:text-indigo-600 transition-colors">Add New Site</p>
                    <p class="text-sm mt-1 text-gray-400">Static or Ecommerce</p>
                </button>
            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="modal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center" @click.self="modal = false">
            <div class="bg-white rounded-2xl shadow-2xl w-[460px] p-7">
                <h3 class="text-base font-black text-gray-900 mb-5">Create New Site</h3>
                <div class="space-y-4">
                    <div><label class="field-label">Site Name</label><input v-model="newSite.name" class="field-input" placeholder="My New Website" /></div>
                    <div><label class="field-label">Domain</label><input v-model="newSite.domain" class="field-input font-mono" placeholder="mysite.com" /></div>
                    <div>
                        <label class="field-label">Site Type</label>
                        <div class="grid grid-cols-2 gap-3 mt-1">
                            <button @click="newSite.type = 'static'" class="py-3 rounded-xl border-2 text-sm font-bold transition-all flex flex-col items-center gap-1"
                                :class="newSite.type === 'static' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                                <i class="fas fa-file-alt text-lg"></i>Static Site
                            </button>
                            <button @click="newSite.type = 'ecommerce'" class="py-3 rounded-xl border-2 text-sm font-bold transition-all flex flex-col items-center gap-1"
                                :class="newSite.type === 'ecommerce' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                                <i class="fas fa-shopping-cart text-lg"></i>E-commerce
                            </button>
                        </div>
                    </div>
                    <div><label class="field-label">Brand Color</label>
                        <div class="flex gap-2">
                            <input type="color" v-model="newSite.color" class="w-10 h-9 rounded-lg border border-gray-200 p-0.5 cursor-pointer" />
                            <input v-model="newSite.color" class="flex-1 field-input font-mono" />
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button @click="modal = false" class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button @click="createSite" :disabled="creating" class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 disabled:opacity-50 flex justify-center items-center gap-2">
                        <i v-if="creating" class="fas fa-spinner fa-spin text-xs"></i>
                        Create Site
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

const siteList = ref(props.sites.length ? [...props.sites] : [
    { id:1, name:'Main Website',    domain:'www.mycompany.com',  type:'static',    status:'live', color:'#6366f1', pages:12, visitors:'2.4K', products:null },
    { id:2, name:'Online Store',    domain:'shop.mycompany.com', type:'ecommerce', status:'live', color:'#10b981', pages:8,  visitors:'1.1K', products:'48' },
    { id:3, name:'Landing Pages',   domain:'go.mycompany.com',   type:'static',    status:'draft',color:'#f59e0b', pages:5,  visitors:'340',  products:null },
]);

const sites = ref([...siteList.value]);
const modal   = ref(false);
const creating = ref(false);
const newSite  = ref({ name:'', domain:'', type:'static', color:'#6366f1' });

const colors = ['#6366f1','#10b981','#f59e0b','#ec4899','#0ea5e9','#ef4444'];

const kpis = computed(() => [
    { label:'Total Sites',     value: sites.value.length,                      icon:'fas fa-globe',          color:'#6366f1', bg:'#eef2ff' },
    { label:'Live',            value: sites.value.filter(s=>s.status==='live').length,  icon:'fas fa-signal',  color:'#10b981', bg:'#ecfdf5' },
    { label:'E-commerce',      value: sites.value.filter(s=>s.type==='ecommerce').length, icon:'fas fa-shopping-bag', color:'#f59e0b', bg:'#fffbeb' },
    { label:'Total Pages',     value: sites.value.reduce((s,x)=>s+(x.pages||0),0), icon:'fas fa-file-alt',  color:'#6366f1', bg:'#eef2ff' },
]);

const openCreate = () => {
    newSite.value = { name:'', domain:'', type:'static', color: colors[sites.value.length % colors.length] };
    modal.value = true;
};

const createSite = async () => {
    if (!newSite.value.name || !newSite.value.domain) return;
    creating.value = true;
    try {
        const { data } = await axios.post('/cms/sites', newSite.value);
        sites.value.push({ ...data, pages:0, visitors:'0', products:null });
    } catch {
        sites.value.push({ id: Date.now(), ...newSite.value, status:'draft', pages:0, visitors:'0', products:null });
    }
    modal.value = false;
    creating.value = false;
};

const cloneSite = async (site) => {
    const clone = { ...site, id: Date.now(), name: site.name + ' (Copy)', domain: 'copy-' + site.domain, status: 'draft' };
    sites.value.push(clone);
};

const deleteSite = async (site) => {
    if (!confirm(`Delete "${site.name}"? This cannot be undone.`)) return;
    try {
        if (!String(site.id).includes(Date.now().toString().slice(0, 5))) {
            await axios.delete('/cms/sites/' + site.id);
        }
        sites.value = sites.value.filter(s => s.id !== site.id);
    } catch {}
};

const updateSiteType = async (site, type) => {
    const backup = site.type;
    site.type = type;
    try {
        await axios.post('/cms/sites/' + site.id + '/toggle-mode');
    } catch {
        site.type = backup; // Revert on failure
    }
};

const toggleStatus = async (site) => {
    const backup = site.status;
    site.status = (site.status === 'live' ? 'draft' : 'live');
    try {
        await axios.post('/cms/sites/' + site.id + '/publish');
    } catch {
        site.status = backup;
    }
};

const editSite  = (site) => { /* open settings panel */ };

// The default site = ecommerce type with the lowest numeric id
const defaultSiteId = computed(() => {
    const ecomSites = sites.value.filter(s => s.type === 'ecommerce');
    if (!ecomSites.length) return null;
    return ecomSites.reduce((min, s) => s.id < min ? s.id : min, ecomSites[0].id);
});

const isDefault = (site) => site.id === defaultSiteId.value;

// Preview URL: use custom domain if live + domain set, otherwise localhost
const previewUrl = (site) => {
    if (site.domain && site.status === 'live') return 'https://' + site.domain;
    if (site.slug) return window.location.origin + '/?preview_site=' + site.slug;
    return window.location.origin + '/';
};
</script>

<style scoped>
.field-label { display:block; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#374151; margin-bottom:0.25rem; }
.field-input { width:100%; background:#f9fafb; border:1px solid #e5e7eb; border-radius:0.5rem; padding:0.5rem 0.75rem; font-size:0.875rem; outline:none; transition:border-color 0.15s; }
.field-input:focus { border-color:#6366f1; box-shadow:0 0 0 2px rgba(99,102,241,0.15); }
</style>
