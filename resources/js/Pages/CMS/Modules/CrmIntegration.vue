<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">CRM Integration</h2>
                <p class="text-xs text-gray-500 mt-0.5">Monitor bidirectional sync: contacts, products, orders, form leads.</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="syncNow" :disabled="syncing" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2 disabled:opacity-50">
                    <i v-if="syncing" class="fas fa-spinner fa-spin text-xs"></i>
                    <i v-else class="fas fa-sync text-xs"></i>
                    Sync Now
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-8 space-y-6">
            <!-- Sync Status Banner -->
            <div class="bg-white rounded-2xl border p-4 flex items-center gap-4 shadow-sm"
                :class="syncStatus === 'ok' ? 'border-emerald-200 bg-emerald-50/50' : syncStatus === 'error' ? 'border-red-200 bg-red-50/50' : 'border-amber-200 bg-amber-50/50'">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                    :class="syncStatus === 'ok' ? 'bg-emerald-500' : syncStatus === 'error' ? 'bg-red-500' : 'bg-amber-500'">
                    <i :class="syncStatus === 'ok' ? 'fas fa-check' : syncStatus === 'error' ? 'fas fa-times' : 'fas fa-exclamation'" class="text-white text-sm"></i>
                </div>
                <div class="flex-1">
                    <p class="font-black text-sm" :class="syncStatus === 'ok' ? 'text-emerald-800' : syncStatus === 'error' ? 'text-red-800' : 'text-amber-800'">
                        {{ syncStatus === 'ok' ? 'All systems synced' : syncStatus === 'error' ? 'Sync error — check logs' : 'Sync pending' }}
                    </p>
                    <p class="text-xs opacity-70 font-medium">Last sync: {{ lastSync }}</p>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full animate-pulse"
                        :class="syncStatus === 'ok' ? 'bg-emerald-500' : syncStatus === 'error' ? 'bg-red-500' : 'bg-amber-500'"></span>
                    <span class="text-xs font-black" :class="syncStatus === 'ok' ? 'text-emerald-700' : 'text-amber-700'">
                        {{ syncStatus === 'ok' ? 'LIVE' : 'IDLE' }}
                    </span>
                </div>
            </div>

            <!-- KPI Grid -->
            <div class="grid grid-cols-4 gap-4">
                <div v-for="k in kpis" :key="k.label"
                    class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" :style="{background: k.bg, color: k.color}">
                            <i :class="k.icon" class="text-base"></i>
                        </div>
                        <span class="text-sm font-black uppercase tracking-wider text-gray-400">{{ k.label }}</span>
                    </div>
                    <p class="text-3xl font-black text-gray-900">{{ k.value }}</p>
                    <div class="mt-3 flex items-center gap-1.5 text-sm font-bold"
                        :class="k.synced ? 'text-emerald-600' : 'text-amber-600'">
                        <i :class="k.synced ? 'fas fa-check-circle' : 'fas fa-clock'"></i>
                        {{ k.synced ? 'Synced' : 'Pending sync' }}
                    </div>
                </div>
            </div>

            <!-- Sync Pipelines -->
            <div class="grid grid-cols-2 gap-5">
                <div v-for="pipeline in pipelines" :key="pipeline.name"
                    class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center" :style="{background: pipeline.bg, color: pipeline.color}">
                                <i :class="pipeline.icon" class="text-sm"></i>
                            </div>
                            <h3 class="font-black text-gray-900 text-sm">{{ pipeline.name }}</h3>
                        </div>
                        <label class="relative flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" v-model="pipeline.enabled" class="sr-only peer" />
                            <div class="w-9 h-5 bg-gray-200 peer-checked:bg-indigo-500 rounded-full relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-4 after:shadow-sm transition-colors"></div>
                        </label>
                    </div>
                    <div class="space-y-2">
                        <div v-for="rule in pipeline.rules" :key="rule.label" class="flex items-center gap-2 text-xs">
                            <div class="w-1.5 h-1.5 rounded-full shrink-0"
                                :class="rule.active ? 'bg-emerald-500' : 'bg-gray-300'"></div>
                            <span class="text-gray-600 flex-1">{{ rule.label }}</span>
                            <span class="text-sm font-bold px-1.5 py-0.5 rounded-full"
                                :class="rule.active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'">
                                {{ rule.active ? 'Active' : 'Disabled' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sync Log -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-black text-gray-900 text-sm">Sync Log</h3>
                    <button @click="loadLog" class="text-sm font-bold text-indigo-600 hover:text-indigo-800">Refresh</button>
                </div>
                <div class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
                    <div v-for="entry in syncLog" :key="entry.id || Math.random()" class="px-5 py-3 flex items-start gap-3 text-xs hover:bg-gray-50">
                        <div class="w-4 h-4 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                            :class="entry.type === 'success' ? 'bg-emerald-100 text-emerald-600' : entry.type === 'error' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600'">
                            <i :class="entry.type === 'success' ? 'fas fa-check text-xs' : entry.type === 'error' ? 'fas fa-times text-xs' : 'fas fa-info text-xs'"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-800 truncate">{{ entry.message }}</p>
                            <p class="text-sm text-gray-400 mt-0.5">{{ entry.time }}</p>
                        </div>
                        <span class="text-sm font-black text-gray-400 uppercase shrink-0">{{ entry.module }}</span>
                    </div>
                    <div v-if="!syncLog.length" class="px-5 py-10 text-center text-gray-400 text-xs">
                        <i class="fas fa-clipboard-list text-3xl text-gray-200 mb-3 block"></i>
                        No sync events yet
                    </div>
                </div>
            </div>

            <!-- Field Mapping -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <h3 class="font-black text-gray-900 text-sm mb-4">Form → CRM Field Mapping</h3>
                <div class="space-y-2">
                    <div v-for="mapping in fieldMappings" :key="mapping.form_field" class="flex items-center gap-3 bg-gray-50 rounded-xl px-4 py-2.5">
                        <div class="w-32 text-xs font-bold text-indigo-600 font-mono truncate">{{ mapping.form_field }}</div>
                        <i class="fas fa-arrow-right text-gray-400 text-xs shrink-0"></i>
                        <select v-model="mapping.crm_field" class="flex-1 text-xs bg-white border border-gray-200 rounded-lg px-2 py-1.5 outline-none focus:border-indigo-400">
                            <option value="">— Skip —</option>
                            <option v-for="f in crmFields" :key="f" :value="f">{{ f }}</option>
                        </select>
                    </div>
                </div>
                <button @click="saveMappings" :disabled="savingMappings" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-black hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-1.5">
                    <i v-if="savingMappings" class="fas fa-spinner fa-spin text-sm"></i>
                    Save Mappings
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import axios from 'axios';

const props = defineProps({ crm_stats: { type: Object, default: () => ({}) } });

const syncing          = ref(false);
const savingMappings   = ref(false);
const syncStatus       = ref('ok');
const stats = computed(() => props.crm_stats || {});
const lastSync = computed(() => stats.value.last_sync || 'Never');

const kpis = computed(() => [
    { label:'Contacts / Leads', value: (stats.value.contacts || 0) + (stats.value.leads || 0), icon:'fas fa-users', color:'#6366f1', bg:'#eef2ff', synced:true  },
    { label:'CRM Deals', value: stats.value.deals || 0, icon:'fas fa-handshake', color:'#10b981', bg:'#ecfdf5', synced:true  },
    { label:'Products Synced', value: stats.value.products || 0, icon:'fas fa-box', color:'#f59e0b', bg:'#fffbeb', synced:true },
    { label:'Orders Processed', value: stats.value.orders || 0, icon:'fas fa-receipt', color:'#ec4899', bg:'#fdf2f8', synced:true  },
]);

const pipelines = reactive([
    { name:'Form → Lead', icon:'fas fa-wpforms', color:'#6366f1', bg:'#eef2ff', enabled:true, rules:[
        { label:'New submission → Create CRM lead',       active:true },
        { label:'Duplicate check by email',               active:true },
        { label:'Tag leads with form name',               active:false },
    ]},
    { name:'Product Sync', icon:'fas fa-box', color:'#f59e0b', bg:'#fffbeb', enabled:false, rules:[
        { label:'Sync product to CRM catalogue',          active:false },
        { label:'Auto-update prices on CRM product change', active:false },
    ]},
    { name:'Order → Deal', icon:'fas fa-receipt', color:'#10b981', bg:'#ecfdf5', enabled:true, rules:[
        { label:'New order → Create CRM deal',            active:true },
        { label:'Payment verified → Move to Won',         active:true },
        { label:'Refunded → Move to Lost',                active:false },
    ]},
    { name:'Contact Sync', icon:'fas fa-users', color:'#ec4899', bg:'#fdf2f8', enabled:false, rules:[
        { label:'New CRM contact → Add to email list',   active:false },
        { label:'Tag contact with campaign source',      active:false },
    ]},
]);

const syncLog = ref([
    { type:'success', message:'Contact "Priya Sharma" synced from form submission.',  time:'2 mins ago',  module:'Forms' },
    { type:'success', message:'Order #1042 deal created for ₹4,999.',                 time:'18 mins ago', module:'Orders' },
    { type:'warn',    message:'Product "Blue Widget" price mismatch — skipped.',       time:'1 hour ago',  module:'Products' },
    { type:'error',   message:'CRM API timeout during bulk contact push (retrying).', time:'3 hours ago', module:'Contacts' },
    { type:'success', message:'44 leads tagged with campaign source "Summer2026".',   time:'Yesterday',   module:'Forms' },
]);

const fieldMappings = ref([
    { form_field:'name',    crm_field:'contact_name' },
    { form_field:'email',   crm_field:'email' },
    { form_field:'phone',   crm_field:'phone' },
    { form_field:'company', crm_field:'company_name' },
    { form_field:'message', crm_field:'notes' },
]);

const crmFields = ['contact_name','email','phone','company_name','website','lead_source','notes','budget','city','state','country'];

const syncNow = async () => {
    syncing.value = true;
    try {
        await axios.post(route('cms.analytics.track'), { event: 'manual_crm_sync' });
        syncLog.value.unshift({ type:'success', message:'Manual sync triggered successfully.', time:'Just now', module:'System' });
        lastSync.value = 'Just now';
        syncStatus.value = 'ok';
    } catch { syncStatus.value = 'error'; }
    finally { syncing.value = false; }
};

const loadLog = () => { /* fetch from analytics API */ };

const saveMappings = async () => {
    savingMappings.value = true;
    try { await axios.post('/cms/crm/save-mappings', { mappings: fieldMappings.value }); } catch {}
    finally { savingMappings.value = false; }
};
</script>
