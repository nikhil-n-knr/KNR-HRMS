<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">User Segments</h2>
                <p class="text-xs text-gray-500 mt-0.5">Build smart audience segments from CRM contacts for targeting, personalisation, and campaigns.</p>
            </div>
            <button @click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2">
                <i class="fas fa-users text-xs"></i> New Segment
            </button>
        </div>

        <div class="flex-1 overflow-hidden flex">
            <!-- Segment list -->
            <div class="w-72 border-r border-gray-200 bg-white flex flex-col shrink-0">
                <div class="p-3 border-b border-gray-100">
                    <input v-model="search" placeholder="Search segments..." class="w-full bg-gray-50 border border-gray-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:border-indigo-400" />
                </div>
                <div class="flex-1 overflow-y-auto py-2">
                    <div v-for="seg in filteredSegments" :key="seg.id"
                        @click="active = seg"
                        class="mx-2 my-0.5 px-4 py-3 rounded-xl cursor-pointer transition-all"
                        :class="active?.id === seg.id ? 'bg-indigo-50 border border-indigo-200' : 'hover:bg-gray-50 border border-transparent'">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-black" :class="active?.id === seg.id ? 'text-indigo-700' : 'text-gray-900'">{{ seg.name }}</p>
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black" :style="{background: seg.color + '22', color: seg.color}">
                                {{ seg.count }}
                            </div>
                        </div>
                        <p class="text-sm text-gray-400 mt-1 truncate">{{ seg.description }}</p>
                        <div class="flex gap-1 mt-2 flex-wrap">
                            <span v-for="tag in seg.tags" :key="tag" class="text-xs font-bold px-1.5 py-0.5 rounded-full" :style="{background: seg.color + '22', color: seg.color}">{{ tag }}</span>
                        </div>
                    </div>
                    <div v-if="!filteredSegments.length" class="p-6 text-center text-gray-400 text-xs">
                        <i class="fas fa-users text-3xl text-gray-200 mb-3 block"></i>No segments yet
                    </div>
                </div>
            </div>

            <!-- Segment detail -->
            <div class="flex-1 overflow-y-auto p-6 space-y-5">
                <div v-if="!active" class="flex flex-col items-center justify-center h-full text-gray-400">
                    <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center mb-5">
                        <i class="fas fa-users text-3xl text-gray-300"></i>
                    </div>
                    <p class="font-black text-gray-600">Select a segment to view details</p>
                </div>

                <template v-else>
                    <!-- Header -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl font-black shrink-0"
                            :style="{background: active.color + '22', color: active.color}">
                            <i :class="active.icon"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-black text-gray-900 text-lg">{{ active.name }}</h3>
                            <p class="text-xs text-gray-500 mt-0.5">{{ active.description }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-3xl font-black text-gray-900">{{ active.count }}</p>
                            <p class="text-sm font-bold text-gray-400 uppercase">Members</p>
                        </div>
                    </div>

                    <!-- Rules -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-black text-gray-900 text-sm">Segment Rules</h3>
                            <span class="text-sm font-black px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">{{ active.logic }} match</span>
                        </div>
                        <div class="space-y-2">
                            <div v-for="(rule, i) in active.rules" :key="i"
                                class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                                <span v-if="i > 0" class="text-sm font-black text-indigo-600 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-full shrink-0">{{ active.logic }}</span>
                                <div class="flex-1 flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-bold text-gray-700 px-2 py-1 bg-white rounded-lg border border-gray-200">{{ rule.field }}</span>
                                    <span class="text-sm text-gray-400 font-bold">{{ rule.operator }}</span>
                                    <span class="text-xs font-bold text-indigo-700 px-2 py-1 bg-indigo-50 rounded-lg border border-indigo-200">{{ rule.value }}</span>
                                </div>
                                <button @click="active.rules.splice(i, 1)" class="text-gray-300 hover:text-red-500 transition-colors text-xs">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Add rule -->
                        <div class="mt-3 flex gap-2">
                            <select v-model="newRule.field" class="flex-1 text-xs bg-gray-50 border border-gray-200 rounded-lg px-2 py-2 focus:outline-none focus:border-indigo-400">
                                <option v-for="f in fieldOptions" :key="f" :value="f">{{ f }}</option>
                            </select>
                            <select v-model="newRule.operator" class="w-28 text-xs bg-gray-50 border border-gray-200 rounded-lg px-2 py-2 focus:outline-none focus:border-indigo-400">
                                <option v-for="op in operators" :key="op" :value="op">{{ op }}</option>
                            </select>
                            <input v-model="newRule.value" placeholder="Value" class="flex-1 text-xs bg-gray-50 border border-gray-200 rounded-lg px-2 py-2 focus:outline-none focus:border-indigo-400" />
                            <button @click="addRule" class="px-3 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700">+ Add</button>
                        </div>
                    </div>

                    <!-- Members preview -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                        <h3 class="font-black text-gray-900 text-sm mb-4">Member Preview</h3>
                        <div class="space-y-2">
                            <div v-for="m in active.members" :key="m.email" class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
                                <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                                    <span class="text-sm font-black text-indigo-700">{{ m.name.charAt(0) }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-gray-800">{{ m.name }}</p>
                                    <p class="text-sm text-gray-400 truncate">{{ m.email }}</p>
                                </div>
                                <span class="text-sm font-bold text-gray-400">{{ m.joined }}</span>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-100 text-center">
                            <p class="text-sm text-gray-400">Showing 5 of {{ active.count }} members</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <button class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 flex items-center justify-center gap-2">
                            <i class="fas fa-paper-plane text-xs"></i> Send Campaign
                        </button>
                        <button class="flex-1 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-bold hover:bg-gray-50 flex items-center justify-center gap-2">
                            <i class="fas fa-download text-xs"></i> Export CSV
                        </button>
                        <button @click="deleteSegment(active.id)" class="px-4 py-2.5 bg-red-50 text-red-600 border border-red-200 rounded-xl text-sm font-bold hover:bg-red-100">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="modal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center" @click.self="modal = false">
            <div class="bg-white rounded-2xl shadow-2xl w-[460px] p-7">
                <h3 class="text-base font-black text-gray-900 mb-5">Create Segment</h3>
                <div class="space-y-4">
                    <div><label class="field-label">Segment Name</label><input v-model="form.name" class="field-input" placeholder="High-Value Customers" /></div>
                    <div><label class="field-label">Description</label><input v-model="form.description" class="field-input" placeholder="Customers who spent > ₹10,000" /></div>
                    <div>
                        <label class="field-label">Rule Logic</label>
                        <div class="flex gap-2">
                            <button @click="form.logic = 'ALL'" class="flex-1 py-2 rounded-xl text-xs font-black border-2 transition-all" :class="form.logic === 'ALL' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-500'">ALL rules match</button>
                            <button @click="form.logic = 'ANY'" class="flex-1 py-2 rounded-xl text-xs font-black border-2 transition-all" :class="form.logic === 'ANY' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-500'">ANY rule matches</button>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button @click="modal = false" class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button @click="createSegment" class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700">Create Segment</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps(['siteId']);

const search = ref('');
const active = ref(null);
const modal  = ref(false);
const form   = ref({ name:'', description:'', logic:'ALL' });
const newRule = ref({ field:'City', operator:'is', value:'' });

const fieldOptions = ['City','Country','Order Count','Total Spend','Lead Source','Tag','Email Domain','Last Active','Created At'];
const operators    = ['is','is not','contains','greater than','less than','is empty'];

const segments = ref([]);
const loading  = ref(false);

const filteredSegments = computed(() => {
    if (!segments.value) return [];
    if (!search.value) return segments.value;
    const q = search.value.toLowerCase();
    return segments.value.filter(s =>
        s?.name?.toLowerCase().includes(q) || 
        s?.description?.toLowerCase().includes(q) ||
        s?.logic?.toLowerCase().includes(q) ||
        (s?.rules && Array.isArray(s.rules) && s.rules.some(r => r.value?.toLowerCase().includes(q)))
    );
});

const fetchData = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('cms.segments.index'));
        segments.value = res.data;
        if (segments.value.length && !active.value) active.value = segments.value[0];
    } catch (e) { console.error('Failed to fetch segments', e); }
    finally { loading.value = false; }
};

onMounted(fetchData);

const addRule = async () => {
    if (!active.value || !newRule.value.value) return;
    active.value.rules.push({ ...newRule.value });
    newRule.value.value = '';
    await axios.put(route('cms.segments.update', active.value.id), { rules: active.value.rules });
};

const openCreate = () => { form.value = { name:'', description:'', logic:'ALL' }; modal.value = true; };

const createSegment = async () => {
    if (!form.value.name) return;
    const colors = ['#6366f1','#10b981','#f59e0b','#ec4899','#0ea5e9'];
    const icons  = ['fas fa-users','fas fa-star','fas fa-tag','fas fa-flame','fas fa-bolt'];
    const idx    = segments.value.length;
    
    try {
        const res = await axios.post(route('cms.segments.store'), {
            ...form.value,
            color: colors[idx % colors.length],
            icon: icons[idx % icons.length],
            rules: []
        });
        segments.value.push({ ...res.data, rules: [] });
        modal.value = false;
        active.value = segments.value[segments.value.length - 1];
    } catch (e) {
        alert('Failed to create segment');
    }
};

const deleteSegment = async (id) => {
    if (!confirm('Are you sure?')) return;
    try {
        await axios.delete(route('cms.segments.destroy', id));
        segments.value = segments.value.filter(s => s.id !== id);
        if (active.value?.id === id) active.value = segments.value[0] || null;
    } catch (e) { alert('Delete failed'); }
}
</script>

<style scoped>
.field-label { display:block; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#374151; margin-bottom:0.25rem; }
.field-input { width:100%; background:#f9fafb; border:1px solid #e5e7eb; border-radius:0.5rem; padding:0.5rem 0.75rem; font-size:0.875rem; outline:none; transition:border-color 0.15s; }
.field-input:focus { border-color:#6366f1; box-shadow:0 0 0 2px rgba(99,102,241,0.15); }
</style>
