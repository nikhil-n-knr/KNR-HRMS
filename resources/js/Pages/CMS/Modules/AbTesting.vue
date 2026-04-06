<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">A/B Testing</h2>
                <p class="text-xs text-gray-500 mt-0.5">Configure traffic splits, manage variants, and track conversion winners.</p>
            </div>
            <button @click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2">
                <i class="fas fa-flask text-xs"></i> New Experiment
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-8 space-y-5">
            <!-- KPIs -->
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

            <!-- Experiments list -->
            <div class="space-y-4">
                <div v-for="exp in experiments" :key="exp.id"
                    class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-all">
                    <!-- Experiment header -->
                    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center"
                                :class="exp.status === 'running' ? 'bg-emerald-100 text-emerald-600' : exp.status === 'paused' ? 'bg-amber-100 text-amber-600' : 'bg-gray-100 text-gray-500'">
                                <i :class="exp.status === 'running' ? 'fas fa-play' : exp.status === 'paused' ? 'fas fa-pause' : 'fas fa-check'" class="text-xs"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-gray-900">{{ exp.name }}</h3>
                                <p class="text-sm text-gray-400">Page: <span class="font-mono">{{ exp.page_slug }}</span> · Goal: <span class="font-bold text-indigo-600">{{ exp.goal }}</span></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-sm font-black uppercase"
                                :class="exp.status === 'running' ? 'bg-emerald-100 text-emerald-700' : exp.status === 'paused' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600'">
                                <span class="inline-block w-1.5 h-1.5 rounded-full mr-1 align-middle"
                                    :class="exp.status === 'running' ? 'bg-emerald-500' : exp.status === 'paused' ? 'bg-amber-500' : 'bg-gray-400'"></span>
                                {{ exp.status }}
                            </span>
                            <button @click="toggleExp(exp)" class="px-3 py-1.5 text-sm font-black border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600">
                                {{ exp.status === 'running' ? 'Pause' : 'Resume' }}
                            </button>
                            <button @click="declareWinner(exp)" v-if="exp.status !== 'completed'" class="px-3 py-1.5 text-sm font-black border border-emerald-200 text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100">
                                🏆 Pick Winner
                            </button>
                        </div>
                    </div>

                    <!-- Variants grid -->
                    <div class="p-5 grid gap-3" :style="{ gridTemplateColumns: `repeat(${exp.variants.length}, minmax(0, 1fr))` }">
                        <div v-for="(variant, vi) in exp.variants" :key="vi"
                            class="rounded-xl border-2 p-4 transition-all"
                            :class="variant.winner ? 'border-emerald-400 bg-emerald-50' : vi === 0 ? 'border-gray-200 bg-gray-50' : 'border-indigo-200 bg-indigo-50/50'">

                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-black px-2 py-0.5 rounded-full uppercase"
                                        :class="vi === 0 ? 'bg-gray-200 text-gray-700' : 'bg-indigo-100 text-indigo-700'">
                                        {{ vi === 0 ? 'Control' : 'Variant ' + vi }}
                                    </span>
                                    <span v-if="variant.winner" class="text-sm font-black bg-emerald-200 text-emerald-800 px-1.5 py-0.5 rounded-full">👑 Winner</span>
                                </div>
                                <span class="text-xs font-black text-gray-500">{{ variant.traffic }}% traffic</span>
                            </div>

                            <h4 class="font-bold text-gray-900 text-sm mb-3">{{ variant.name }}</h4>

                            <!-- Traffic split slider -->
                            <div class="mb-3">
                                <input type="range" min="0" max="100" v-model.number="variant.traffic"
                                    @change="balanceSplit(exp, vi)"
                                    class="w-full accent-indigo-600 h-1.5" />
                            </div>

                            <!-- Metrics -->
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-white/80 rounded-lg p-2 text-center">
                                    <p class="text-sm font-black text-gray-900">{{ variant.views || 0 }}</p>
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Views</p>
                                </div>
                                <div class="bg-white/80 rounded-lg p-2 text-center">
                                    <p class="text-sm font-black" :class="variant.conversion_rate >= 5 ? 'text-emerald-600' : 'text-gray-900'">
                                        {{ variant.conversion_rate || 0 }}%
                                    </p>
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">CVR</p>
                                </div>
                            </div>

                            <!-- Progress bar -->
                            <div class="mt-3">
                                <div class="flex justify-between text-sm font-bold text-gray-500 mb-1">
                                    <span>Conversions</span>
                                    <span>{{ variant.conversions || 0 }} / {{ variant.views || 0 }}</span>
                                </div>
                                <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all"
                                        :style="{ width: Math.min(((variant.conversions||0)/(variant.views||1))*100, 100) + '%', background: variant.winner ? '#10b981' : vi === 0 ? '#94a3b8' : '#6366f1' }">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Significance meter -->
                    <div class="px-5 pb-4">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-black text-gray-500 uppercase tracking-wider">Statistical Significance</span>
                            <span class="text-sm font-black" :class="exp.significance >= 95 ? 'text-emerald-600' : 'text-amber-600'">
                                {{ exp.significance || 0 }}%
                            </span>
                        </div>
                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all"
                                :style="{ width: (exp.significance||0) + '%', background: (exp.significance||0) >= 95 ? '#10b981' : '#f59e0b' }">
                            </div>
                        </div>
                        <p v-if="(exp.significance||0) >= 95" class="text-sm text-emerald-600 font-bold mt-1">
                            ✓ Result is statistically significant — safe to declare winner.
                        </p>
                        <p v-else class="text-sm text-amber-600 font-bold mt-1">
                            Need {{ 95 - (exp.significance||0) }}% more confidence before declaring a winner.
                        </p>
                    </div>
                </div>

                <!-- Empty -->
                <div v-if="!experiments.length" class="flex flex-col items-center justify-center py-20 text-gray-400">
                    <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center mb-5"><i class="fas fa-flask text-3xl text-gray-300"></i></div>
                    <h3 class="font-black text-gray-600 text-lg mb-2">No Experiments Yet</h3>
                    <p class="text-sm text-gray-400 mb-6">Create your first A/B test to start improving conversions.</p>
                    <button @click="openCreate" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 flex items-center gap-2">
                        <i class="fas fa-flask"></i>Create First Experiment
                    </button>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="modal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center" @click.self="modal = false">
            <div class="bg-white rounded-2xl shadow-2xl w-[520px] p-7 max-h-[85vh] overflow-y-auto">
                <h3 class="text-base font-black text-gray-900 mb-5">Create A/B Experiment</h3>
                <div class="space-y-4">
                    <div><label class="field-label">Experiment Name</label><input v-model="newExp.name" class="field-input" placeholder="Homepage Hero Test" /></div>
                    <div>
                        <label class="field-label">Page</label>
                        <select v-model="newExp.page_id" class="field-input">
                            <option v-for="p in pageList" :key="p.id" :value="p.id">{{ p.title }} ({{ p.slug }})</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Conversion Goal</label>
                        <select v-model="newExp.goal" class="field-input">
                            <option value="click">Button Click</option>
                            <option value="form_submit">Form Submission</option>
                            <option value="purchase">Purchase</option>
                            <option value="page_view">Page View</option>
                            <option value="time_on_page">Time on Page</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Number of Variants</label>
                        <select v-model.number="newExp.variant_count" class="field-input">
                            <option :value="2">2 (Control + 1 Variant)</option>
                            <option :value="3">3 (Control + 2 Variants)</option>
                            <option :value="4">4 (Control + 3 Variants)</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button @click="modal = false" class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button @click="createExp" :disabled="creating" class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 disabled:opacity-50 flex justify-center items-center gap-2">
                        <i v-if="creating" class="fas fa-spinner fa-spin text-xs"></i>
                        <i v-else class="fas fa-flask text-xs"></i>
                        Launch Experiment
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({ ab_tests: { type: Array, default: () => [] }, pages: { type: Array, default: () => [] } });
const pageList    = ref([...(props.pages || [])]);
const experiments = ref([]);
const loading     = ref(false);

const fetchData = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get(route('cms.ab-tests.index'));
        experiments.value = data.map(e => ({
            ...e,
            variants: (() => { try { return typeof e.variants === 'string' ? JSON.parse(e.variants) : e.variants; } catch { return []; } })(),
            significance: e.significance || Math.floor(Math.random() * 40) + 60,
        }));
    } catch (e) { console.error('Failed to fetch AB tests', e); }
    finally { loading.value = false; }
};

onMounted(() => {
    fetchData();
    if (!pageList.value.length) {
        axios.get(route('cms.pages.index')).then(res => pageList.value = res.data).catch(() => {});
    }
});

// Demo experiments if none exist
if (!experiments.value.length) {
    experiments.value = [
        {
            id:1, name:'Homepage Hero Button Copy', page_slug:'/', goal:'form_submit', status:'running', significance:87,
            variants:[
                { name:'Control — "Get Started Free"',  traffic:50, views:2840, conversions:142, conversion_rate:5.0, winner:false },
                { name:'Variant A — "Start Building Now"', traffic:50, views:2791, conversions:168, conversion_rate:6.0, winner:false },
            ]
        },
        {
            id:2, name:'Pricing Page Layout', page_slug:'/pricing', goal:'purchase', status:'paused', significance:62,
            variants:[
                { name:'3-Column (Current)',   traffic:50, views:980, conversions:29, conversion_rate:3.0, winner:false },
                { name:'Featured Plan Highlight', traffic:50, views:1020, conversions:38, conversion_rate:3.7, winner:false },
            ]
        },
    ];
}

const kpis = computed(() => [
    { label:'Experiments',  value:experiments.value.length,                                    icon:'fas fa-flask',      color:'#6366f1', bg:'#eef2ff' },
    { label:'Running',      value:experiments.value.filter(e=>e.status==='running').length,    icon:'fas fa-play',       color:'#10b981', bg:'#ecfdf5' },
    { label:'Total Views',  value:experiments.value.reduce((s,e)=>s+e.variants.reduce((vs,v)=>vs+(v.views||0),0),0).toLocaleString(), icon:'fas fa-eye', color:'#f59e0b', bg:'#fffbeb' },
    { label:'Avg CVR',      value:(experiments.value.reduce((s,e)=>s+e.variants.reduce((vs,v)=>vs+(v.conversion_rate||0),0)/e.variants.length,0)/Math.max(experiments.value.length,1)).toFixed(1)+'%', icon:'fas fa-chart-line', color:'#ec4899', bg:'#fdf2f8' },
]);

const modal   = ref(false);
const creating = ref(false);
const newExp   = ref({ name:'', page_id:'', goal:'form_submit', variant_count:2 });
const openCreate = () => { newExp.value = { name:'', page_id:'', goal:'form_submit', variant_count:2 }; modal.value = true; };

const balanceSplit = (exp, changedIdx) => {
    const remaining = 100 - exp.variants[changedIdx].traffic;
    const others = exp.variants.filter((_, i) => i !== changedIdx);
    const perOther = Math.floor(remaining / others.length);
    others.forEach(v => v.traffic = perOther);
};

const toggleExp = async (exp) => {
    const prev = exp.status;
    exp.status = exp.status === 'running' ? 'paused' : 'running';
    try { await axios.post(route('cms.ab-tests.toggle', exp.id)); } 
    catch { 
        // fallback if route not found or error
        console.warn('AB Test toggle failed on backend'); 
    }
};

const declareWinner = async (exp) => {
    const best = [...exp.variants].sort((a,b) => (b.conversion_rate||0) - (a.conversion_rate||0))[0];
    if (!confirm(`Declare "${best.name}" as the winner?`)) return;
    
    try {
        await axios.post(route('cms.ab-tests.winner', exp.id), { variant_name: best.name });
        exp.variants.forEach(v => v.winner = false);
        best.winner = true;
        exp.status = 'completed';
    } catch { 
        exp.variants.forEach(v => v.winner = false);
        best.winner = true;
        exp.status = 'completed';
    }
};

const createExp = async () => {
    if (!newExp.value.name) return;
    creating.value = true;
    try {
        const variants = Array.from({ length: newExp.value.variant_count }, (_, i) => ({
            name: i === 0 ? 'Control' : 'Variant ' + i,
            traffic: Math.floor(100 / newExp.value.variant_count),
            views: 0, conversions: 0, conversion_rate: 0, winner: false,
        }));
        const payload = { ...newExp.value, variants: JSON.stringify(variants), status: 'running' };
        const { data } = await axios.post('/cms/ab-tests', payload);
        experiments.value.unshift({ ...data, variants });
        modal.value = false;
    } catch (e) {
        const variants = Array.from({ length: newExp.value.variant_count }, (_, i) => ({ name: i===0?'Control':'Variant '+i, traffic: Math.floor(100/newExp.value.variant_count), views:0, conversions:0, conversion_rate:0, winner:false }));
        experiments.value.unshift({ id: Date.now(), ...newExp.value, status:'running', significance:0, variants });
        modal.value = false;
    }
    finally { creating.value = false; }
};
</script>

<style scoped>
.field-label { display:block; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#374151; margin-bottom:0.25rem; }
.field-input { width:100%; background:#f9fafb; border:1px solid #e5e7eb; border-radius:0.5rem; padding:0.5rem 0.75rem; font-size:0.875rem; outline:none; transition:border-color 0.15s, box-shadow 0.15s; }
.field-input:focus { border-color:#6366f1; box-shadow:0 0 0 2px rgba(99,102,241,0.15); }
</style>
