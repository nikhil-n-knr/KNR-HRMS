<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, computed } from 'vue';
import {
    CpuChipIcon,
    ScaleIcon,
    PlusIcon,
    ChevronDownIcon,
    ChevronUpIcon,
    CurrencyRupeeIcon,
    InformationCircleIcon,
    PencilIcon,
    TrashIcon
} from '@heroicons/vue/24/outline';

defineOptions({ layout: MainLayout });

const props = defineProps({
    tab: String,
    expanded_category_id: [String, Number],
    categories: Array,
    rules: Array,
    settings: Object  // currency/general settings
});

// --- State ---
const activeTab = ref(props.tab || 'attributes');
const expandedCategory = ref(props.expanded_category_id ? Number(props.expanded_category_id) : null);
const showAttributeModal = ref(false);
const showRuleModal = ref(false);
const showInfoModal = ref(false);
const infoContent = ref({ title: '', body: '' });
const editingAttribute = ref(null);
const editingRule = ref(null);

// --- FORMS ---
const formAttr = useForm({
    asset_category_id: '',
    name: '',
    field_type: 'text',
    is_required: false,
    options: []
});

const formRule = useForm({
    name: '',
    module: 'Asset',
    trigger_event: 'Create',
    conditions: [{ field: 'cost', operator: '>', value: 0 }],
    actions: [{ type: 'notify', target: 'admin' }],
    is_active: true
});

const formSettings = useForm({
    currency_code:   props.settings?.currency_code   ?? 'INR',
    currency_symbol: props.settings?.currency_symbol ?? '₹',
    currency_name:   props.settings?.currency_name   ?? 'Indian Rupee',
    date_format:     props.settings?.date_format     ?? 'DD/MM/YYYY',
    timezone:        props.settings?.timezone        ?? 'Asia/Kolkata',
});

const currencies = [
    { code: 'INR', symbol: '₹',  name: 'Indian Rupee' },
    { code: 'USD', symbol: '$',  name: 'US Dollar' },
    { code: 'EUR', symbol: '€',  name: 'Euro' },
    { code: 'GBP', symbol: '£',  name: 'British Pound' },
    { code: 'AED', symbol: 'د.إ', name: 'UAE Dirham' },
    { code: 'SGD', symbol: 'S$', name: 'Singapore Dollar' },
    { code: 'JPY', symbol: '¥',  name: 'Japanese Yen' },
];

const onCurrencyChange = (code) => {
    const found = currencies.find(c => c.code === code);
    if (found) {
        formSettings.currency_symbol = found.symbol;
        formSettings.currency_name   = found.name;
    }
};

// --- ACTIONS ---
const switchTab = (id) => {
    activeTab.value = id;
};

const toggleCategory = (id) => {
    expandedCategory.value = expandedCategory.value === id ? null : id;
};

const openAddAttribute = (catId) => {
    editingAttribute.value = null;
    formAttr.reset();
    formAttr.asset_category_id = catId;
    showAttributeModal.value = true;
};

const openEditAttribute = (attr, catId) => {
    editingAttribute.value = attr;
    formAttr.clearErrors();
    formAttr.asset_category_id = catId;
    formAttr.name = attr.name;
    formAttr.field_type = attr.field_type;
    formAttr.is_required = !!attr.is_required;
    formAttr.options = attr.options || [];
    showAttributeModal.value = true;
};

const deleteAttribute = (id) => {
    if (confirm('Are you sure you want to delete this attribute slot?')) {
        router.delete(route('admin.assets.configs.attributes.destroy', id));
    }
};

const submitAttribute = () => {
    if (editingAttribute.value) {
        formAttr.put(route('admin.assets.configs.attributes.update', editingAttribute.value.id), {
            onSuccess: () => showAttributeModal.value = false
        });
    } else {
        formAttr.post(route('admin.assets.configs.attributes.store'), {
            onSuccess: () => showAttributeModal.value = false
        });
    }
};

const showInfoHub = () => showInfo('Configuration Hub',
    'This hub lets you customize how the entire Asset Management system behaves.\n\n' +
    '• Attributes (Slots): Add custom fields to any asset category (e.g. Screen Size for Laptops).\n' +
    '• Business Rules: Inject conditional automation — e.g. When a new asset costs more than 50,000, notify the finance admin.\n' +
    '• Settings: Set your currency, timezone, and date format preferences.');

const showInfoAttributes = () => showInfo('Attribute Slots',
    'Attribute slots add custom data fields to asset categories.\n\n' +
    'Example: For the Laptop category, you might add:\n' +
    '  • Screen Size (Number)\n  • RAM (Text)\n  • Is Touchscreen? (Yes/No)\n\n' +
    'When creating or editing a Laptop, these extra fields appear automatically.');

const showInfoRules = () => showInfo('Business Rules',
    'Business rules inject conditional logic without touching code.\n\n' +
    'Example Rule:\n  Name: High Value Alert\n  Trigger: On Create (Asset)\n  Condition: cost > 50000\n  Action: Notify admin\n\n' +
    'Action types:\n  • Notify User — Send a dashboard notification\n  • Block Action — Prevent the save entirely\n  • Flag for Review — Mark for manual review');

const openEditRule = (rule) => {
    editingRule.value = rule;
    formRule.clearErrors();
    formRule.name = rule.name;
    formRule.module = rule.module;
    formRule.trigger_event = rule.trigger_event;
    formRule.conditions = rule.conditions || [{ field: 'cost', operator: '>', value: 0 }];
    formRule.actions = rule.actions || [{ type: 'notify', target: 'admin' }];
    formRule.is_active = !!rule.is_active;
    showRuleModal.value = true;
};

const deleteRule = (id) => {
    if (confirm('Are you sure you want to delete this business rule?')) {
        router.delete(route('admin.assets.configs.rules.destroy', id), {
            onSuccess: () => router.reload()
        });
    }
};

const submitRule = () => {
    const action = editingRule.value 
        ? () => formRule.put(route('admin.assets.configs.rules.update', editingRule.value.id))
        : () => formRule.post(route('admin.assets.configs.rules.store'));

    action().then(response => {
        showRuleModal.value = false;
        formRule.reset();
        formRule.conditions = [{ field: 'cost', operator: '>', value: 0 }];
        formRule.actions    = [{ type: 'notify', target: 'admin' }];
        editingRule.value = null;
    });
};

const saveSettings = () => {
    formSettings.post(route('admin.assets.configs.settings.save'), {
        preserveScroll: true
    });
};

const showInfo = (title, body) => {
    infoContent.value = { title, body };
    showInfoModal.value = true;
};

const ruleActionLabel = (action) => {
    const map = { notify: 'Notify', block: 'Block', flag: 'Flag' };
    return `${map[action.type] ?? action.type} → ${action.target}`;
};

const closeAttributeModal = () => {
    showAttributeModal.value = false;
    editingAttribute.value = null;
    formAttr.reset();
};

const closeRuleModal = () => {
    showRuleModal.value = false;
    editingRule.value = null;
    formRule.reset();
    formRule.conditions = [{ field: 'cost', operator: '>', value: 0 }];
    formRule.actions    = [{ type: 'notify', target: 'admin' }];
};
</script>

<template>
    <Head title="Configuration Hub" />
    <div class="space-y-6 animate-fade-in font-outfit">
        <!-- Integrated Premium Header -->
        <div class="bg-white/80 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/40 relative overflow-hidden group">
            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-fuchsia-100/30 rounded-full blur-3xl group-hover:bg-fuchsia-200/40 transition-colors duration-700"></div>
            
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div class="flex items-center gap-5">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-fuchsia-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-fuchsia-500/20">
                        <CpuChipIcon class="h-8 w-8" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Configuration Hub</h1>
                        <p class="text-sm text-slate-500 font-medium">The "Meta-Brain" of the system. Define global logic and data structures.</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button v-for="t in ['attributes', 'rules', 'settings']" :key="t"
                            @click="switchTab(t)"
                            class="px-5 py-2 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all shadow-sm border"
                            :class="activeTab === t 
                                ? 'bg-fuchsia-600 text-white border-fuchsia-500 shadow-xl shadow-fuchsia-200' 
                                : 'bg-white text-slate-500 border-slate-200 hover:border-fuchsia-300 hover:text-fuchsia-600'">
                        {{ t }}
                    </button>
                    <button @click="showInfoHub" class="w-10 h-10 rounded-2xl bg-slate-50 border border-slate-200 text-slate-400 flex items-center justify-center hover:bg-fuchsia-50 hover:text-fuchsia-600 transition-all">
                        <InformationCircleIcon class="h-6 w-6" />
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 pt-8 border-t border-slate-100/50">
                <div class="flex flex-col">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Hooks</span>
                    <span class="text-xl font-black text-fuchsia-600 tabular-nums">{{ categories.reduce((acc, c) => acc + (c.attribute_definitions?.length || 0), 0) }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Active Rules</span>
                    <span class="text-xl font-black text-indigo-600 tabular-nums">{{ rules?.length || 0 }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">System Health</span>
                    <span class="text-xl font-black text-emerald-500 uppercase">Optimal</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Last Update</span>
                    <span class="text-xl font-black text-slate-800 tabular-nums uppercase">NOW</span>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto">

            <!-- ─── ATTRIBUTES TAB ─── -->
            <div v-if="activeTab === 'attributes'" class="space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white/40 p-6 rounded-[2rem] border border-white/60 shadow-sm backdrop-blur-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-fuchsia-100 flex items-center justify-center text-fuchsia-600">
                            <CpuChipIcon class="h-6 w-6" />
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-800 tracking-tight">Custom Attribute Slots</h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Define category-specific data fields</p>
                        </div>
                    </div>
                    <button @click="showInfoAttributes" class="px-4 py-2 bg-white rounded-xl border border-slate-200 text-slate-400 hover:text-fuchsia-600 hover:border-fuchsia-200 transition-all flex items-center gap-2 text-[10px] font-black uppercase tracking-widest">
                        <InformationCircleIcon class="h-4 w-4" /> Documentation
                    </button>
                </div>
                <div v-for="cat in categories" :key="cat.id" class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/40 border border-white overflow-hidden transition-all hover:shadow-2xl">
                    <div @click="toggleCategory(cat.id)" class="px-8 py-6 bg-slate-50/50 flex justify-between items-center cursor-pointer hover:bg-fuchsia-50/50 transition">
                        <div>
                            <h3 class="text-lg font-black text-slate-800 tracking-tight">{{ cat.name }}</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ cat.attribute_definitions?.length ?? 0 }} Registered Slots</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-white border border-slate-100 flex items-center justify-center text-slate-400 shadow-sm">
                             <component :is="expandedCategory === cat.id ? ChevronUpIcon : ChevronDownIcon" class="h-5 w-5" />
                        </div>
                    </div>

                    <div v-if="expandedCategory === cat.id" class="p-8 bg-white border-t border-slate-100 animate-fade-in">
                        <div v-if="cat.attribute_definitions?.length" class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-8">
                            <div v-for="def in cat.attribute_definitions" :key="def.id"
                                class="flex justify-between items-center p-4 bg-slate-50 rounded-2xl border border-slate-100 group transition-all hover:bg-white hover:shadow-lg hover:border-fuchsia-100">
                                <span class="text-xs font-black text-slate-600 uppercase tracking-widest">{{ def.name }}</span>
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-2">
                                        <span v-if="def.is_required" class="text-[9px] font-black uppercase text-rose-500 bg-rose-50 px-2 py-1 rounded-md">Required</span>
                                        <span class="text-[9px] font-black uppercase text-fuchsia-600 bg-fuchsia-50 px-2 py-1 rounded-md">{{ def.field_type }}</span>
                                    </div>
                                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click.stop="openEditAttribute(def, cat.id)" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-100 text-slate-400 hover:text-fuchsia-600 transition shadow-sm">
                                            <PencilIcon class="h-4 w-4" />
                                        </button>
                                        <button @click.stop="deleteAttribute(def.id)" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-100 text-slate-400 hover:text-rose-600 transition shadow-sm">
                                            <TrashIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200 mb-8">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">No hooks defined for this categorical branch</p>
                        </div>

                        <button @click="openAddAttribute(cat.id)"
                            class="px-5 py-2.5 bg-fuchsia-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-fuchsia-200 transition-all hover:scale-105 active:scale-95 flex items-center gap-2">
                            <PlusIcon class="h-4 w-4" /> Register Slot
                        </button>
                    </div>
                </div>
            </div>

            <!-- ─── RULES TAB ─── -->
            <!-- ─── RULES TAB ─── -->
            <div v-if="activeTab === 'rules'" class="space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white/40 p-6 rounded-[2rem] border border-white/60 shadow-sm backdrop-blur-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600">
                            <ScaleIcon class="h-6 w-6" />
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-800 tracking-tight">Business Logic Rules</h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Automated triggers & conditions</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="showInfoRules" class="px-4 py-2 bg-white rounded-xl border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all flex items-center gap-2 text-[10px] font-black uppercase tracking-widest">
                            <InformationCircleIcon class="h-4 w-4" /> Rules Guide
                        </button>
                        <button @click="showRuleModal = true"
                            class="px-5 py-2.5 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 font-black shadow-lg shadow-indigo-200 flex items-center gap-2 text-[11px] uppercase tracking-widest transition-all active:scale-95">
                            <PlusIcon class="h-5 w-5" /> Inject Rule
                        </button>
                    </div>
                </div>

                <div class="grid gap-6">
                    <div v-for="rule in rules" :key="rule.id"
                        class="bg-white p-8 rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/40 hover:shadow-2xl transition-all group relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50/50 rounded-full -mr-16 -mt-16 blur-3xl group-hover:bg-indigo-100/60 transition-colors"></div>
                        
                        <div class="flex flex-col lg:flex-row justify-between lg:items-center gap-6 relative z-10">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-3 h-3 rounded-full ring-4 ring-white shadow-sm" :class="rule.is_active ? 'bg-emerald-500 shadow-emerald-200' : 'bg-slate-300 shadow-slate-200'"></div>
                                    <h3 class="text-lg font-black text-slate-800 tracking-tight">{{ rule.name }}</h3>
                                    <span class="text-[9px] font-black uppercase tracking-wider px-3 py-1 bg-slate-100 rounded-full text-slate-500">{{ rule.module }}</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 font-mono text-[11px] bg-slate-50/80 p-4 rounded-2xl border border-slate-100/50">
                                    <span class="text-slate-400 font-bold uppercase tracking-widest text-[9px]">Trigger:</span>
                                    <span class="text-indigo-600 font-black uppercase">{{ rule.trigger_event }}</span>
                                    <template v-if="rule.conditions?.[0]">
                                        <span class="mx-2 text-slate-300">|</span>
                                        <span class="text-slate-400 font-bold uppercase tracking-widest text-[9px]">Condition:</span>
                                        <span class="text-slate-800 font-black uppercase tracking-tighter">{{ rule.conditions[0].field }}</span>
                                        <span class="text-indigo-500 font-bold">{{ rule.conditions[0].operator }}</span>
                                        <span class="text-slate-800 font-black">{{ rule.conditions[0].value }}</span>
                                    </template>
                                </div>
                            </div>

                            <div class="flex flex-col items-end gap-3">
                                <div class="flex flex-col items-end gap-1">
                                    <div v-for="(action, i) in rule.actions" :key="i" 
                                        class="bg-fuchsia-600 text-white px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-fuchsia-200">
                                        → {{ ruleActionLabel(action) }}
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="openEditRule(rule)" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 border border-slate-100 text-slate-400 hover:bg-white hover:text-fuchsia-600 hover:shadow-lg transition-all">
                                        <PencilIcon class="h-4 w-4" />
                                    </button>
                                    <button @click="deleteRule(rule.id)" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 border border-slate-100 text-slate-400 hover:bg-rose-50 hover:text-rose-600 hover:shadow-lg transition-all">
                                        <TrashIcon class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="!rules?.length" class="text-center py-24 bg-white rounded-[2.5rem] border border-dashed border-slate-200">
                        <ScaleIcon class="h-16 w-16 mx-auto mb-4 text-slate-200" />
                        <h3 class="text-xl font-black text-slate-800 uppercase tracking-tighter">No logic hooks detected</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">The core is running in standard linear mode</p>
                    </div>
                </div>
            </div>

            <!-- ─── SETTINGS TAB ─── -->
            <!-- ─── SETTINGS TAB ─── -->
            <div v-if="activeTab === 'settings'" class="space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white/40 p-6 rounded-[2rem] border border-white/60 shadow-sm backdrop-blur-sm shadow-slate-200/50">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600">
                            <CurrencyRupeeIcon class="h-6 w-6" />
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-800 tracking-tight">System Settings</h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Currency, Regional & formats</p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="saveSettings" class="space-y-8">
                    <!-- Currency Card -->
                    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-white p-8 relative overflow-hidden group">
                        <div class="absolute top-0 left-0 w-32 h-32 bg-emerald-50/50 rounded-full -ml-16 -mt-16 blur-3xl group-hover:bg-emerald-100/60 transition-colors"></div>
                        
                        <div class="flex items-center gap-5 mb-8 relative z-10">
                            <div class="h-14 w-14 rounded-2xl bg-emerald-500 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                                <CurrencyRupeeIcon class="h-8 w-8" />
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-slate-800 tracking-tight">Financial Anchor</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Primary currency & cost rendering</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Currecny Type</label>
                                <select v-model="formSettings.currency_code" @change="onCurrencyChange(formSettings.currency_code)"
                                    class="w-full h-12 px-4 rounded-2xl border-slate-200 bg-slate-50 text-sm font-bold focus:ring-emerald-500 focus:bg-white transition-all">
                                    <option v-for="c in currencies" :key="c.code" :value="c.code">
                                        {{ c.symbol }} — {{ c.name }} ({{ c.code }})
                                    </option>
                                </select>
                            </div>
                            <div class="space-y-2 text-center md:text-left">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Symbol</label>
                                <input v-model="formSettings.currency_symbol" type="text"
                                    class="w-full h-12 px-4 rounded-2xl border-slate-200 bg-slate-50 text-center text-2xl font-black focus:ring-emerald-500 focus:bg-white transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">System Label</label>
                                <input v-model="formSettings.currency_name" type="text"
                                    class="w-full h-12 px-4 rounded-2xl border-slate-200 bg-slate-50 text-sm font-bold focus:ring-emerald-500 focus:bg-white transition-all">
                            </div>
                        </div>

                        <!-- Preview -->
                        <div class="mt-8 p-6 bg-slate-900 rounded-[1.5rem] shadow-2xl relative z-10 group/preview transition-all hover:scale-[1.01]">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[8px] font-black text-emerald-500 uppercase tracking-[0.3em]">Live Rendering Preview</span>
                                <div class="flex gap-1">
                                    <div class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></div>
                                    <div class="w-1 h-1 rounded-full bg-emerald-500/50"></div>
                                </div>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-black text-white tabular-nums">
                                    {{ formSettings.currency_symbol }}1,20,000.00
                                </span>
                                <span class="text-xs font-black text-emerald-400 uppercase tracking-widest">
                                    {{ formSettings.currency_code }}
                                </span>
                            </div>
                            <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-2 border-t border-slate-800 pt-2 italic">
                                One Lakh Twenty Thousand {{ formSettings.currency_name }}
                            </p>
                        </div>
                    </div>

                    <!-- Regional Settings -->
                    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-white p-8 relative overflow-hidden">
                        <div class="flex items-center gap-5 mb-8">
                             <div class="h-14 w-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100 shadow-sm">
                                <InformationCircleIcon class="h-8 w-8" />
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-slate-800 tracking-tight">Regional Protocol</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Chronological & spatial formats</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Date Sequence</label>
                                <select v-model="formSettings.date_format"
                                    class="w-full h-12 px-4 rounded-2xl border-slate-200 bg-slate-50 text-sm font-bold focus:ring-indigo-500 focus:bg-white transition-all">
                                    <option value="DD/MM/YYYY">DD/MM/YYYY (Indian Standard)</option>
                                    <option value="MM/DD/YYYY">MM/DD/YYYY (USA Format)</option>
                                    <option value="YYYY-MM-DD">YYYY-MM-DD (ISO 8601)</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Temporal Zone</label>
                                <select v-model="formSettings.timezone"
                                    class="w-full h-12 px-4 rounded-2xl border-slate-200 bg-slate-50 text-sm font-bold focus:ring-indigo-500 focus:bg-white transition-all">
                                    <option value="Asia/Kolkata">Asia/Kolkata (IST +5:30)</option>
                                    <option value="UTC">Universal Time Coordinated (UTC)</option>
                                    <option value="America/New_York">America/New_York (EST)</option>
                                    <option value="Europe/London">Europe/London (GMT)</option>
                                    <option value="Asia/Dubai">Asia/Dubai (GST +4)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" :disabled="formSettings.processing"
                            class="px-10 py-4 bg-slate-900 text-white font-black rounded-[2rem] shadow-2xl shadow-slate-400/20 hover:bg-black hover:-translate-y-1 transition-all duration-300 disabled:opacity-50 flex items-center gap-3 text-xs uppercase tracking-[0.2em]">
                            <span v-if="formSettings.processing">Syncing Protocols...</span>
                            <span v-else>Update System Meta</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- ─── MODAL: ADD ATTRIBUTE ─── -->
    <div v-if="showAttributeModal" 
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-md p-4 transition-all"
        @click.self="closeAttributeModal">
        <form @submit.prevent="submitAttribute" 
            class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md animate-fade-in border border-slate-200">
            <h2 class="text-lg font-bold mb-1">{{ editingAttribute ? 'Edit Attribute Slot' : 'Define New Slot' }}</h2>
            <p class="text-xs text-gray-500 mb-4">{{ editingAttribute ? 'Update the details of this attribute slot.' : 'Add a custom field to this asset category. It will appear when creating/editing any asset of this type.' }}</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Attribute Name</label>
                    <input v-model="formAttr.name" type="text" placeholder="e.g. Screen Size, Warranty Period" class="mt-1 w-full rounded-md border-gray-300" required>
                    <span v-if="formAttr.errors.name" class="text-red-500 text-xs mt-1">{{ formAttr.errors.name }}</span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Field Type</label>
                    <select v-model="formAttr.field_type" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="text">Text (free input)</option>
                        <option value="number">Number</option>
                        <option value="boolean">Yes / No</option>
                        <option value="date">Date</option>
                        <option value="select">Select (dropdown)</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <input v-model="formAttr.is_required" type="checkbox" id="req" class="rounded text-fuchsia-600">
                    <label for="req" class="text-sm text-gray-700">Required Field</label>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-slate-100">
                <button type="button" @click="closeAttributeModal" class="px-4 py-2 text-sm font-bold text-slate-400 hover:text-slate-600 transition">Cancel</button>
                <button type="submit" :disabled="formAttr.processing" class="bg-fuchsia-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-fuchsia-700 disabled:opacity-50">
                    {{ formAttr.processing ? 'Saving...' : (editingAttribute ? 'Update Slot' : 'Save Slot') }}
                </button>
            </div>
        </form>
    </div>

    <!-- ─── MODAL: INJECT RULE ─── -->
    <div v-if="showRuleModal" 
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-md p-4 transition-all"
        @click.self="closeRuleModal">
        <form @submit.prevent="submitRule" 
            class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-2xl animate-fade-in border border-slate-200">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold">{{ editingRule ? 'Update Logic Rule' : 'Inject Logic Rule' }}</h2>
                    <p class="text-xs text-gray-500 mt-1">{{ editingRule ? 'Modify the triggers and conditions for this rule.' : 'Create an automation trigger that runs on a system event.' }}</p>
                </div>
                <button type="button" @click="closeRuleModal" class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-rose-50 hover:text-rose-500 transition-all">&times;</button>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Rule Name</label>
                    <input v-model="formRule.name" type="text" placeholder="e.g. High Value Alert" class="mt-1 w-full rounded-md border-gray-300" required>
                    <span v-if="formRule.errors.name" class="text-red-500 text-xs">{{ formRule.errors.name }}</span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Module</label>
                    <select v-model="formRule.module" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="Asset">Asset</option>
                        <option value="Inventory">Inventory</option>
                        <option value="Procurement">Procurement</option>
                        <option value="Document">Document</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Trigger Event</label>
                    <select v-model="formRule.trigger_event" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="Create">On Create</option>
                        <option value="Update">On Update</option>
                        <option value="Delete">On Delete</option>
                        <option value="Threshold">On Threshold</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 mt-5">
                    <input v-model="formRule.is_active" type="checkbox" id="ruleActive" class="rounded text-fuchsia-600">
                    <label for="ruleActive" class="text-sm text-gray-700 font-medium">Active immediately</label>
                </div>
            </div>

            <!-- Condition Builder -->
            <div class="bg-amber-50 border border-amber-100 p-4 rounded-xl mb-4">
                <p class="text-xs font-bold text-amber-700 uppercase mb-3 flex items-center gap-1">
                    Condition — IF
                </p>
                <div class="flex gap-2 items-center">
                    <div class="flex-1">
                        <label class="text-xs text-gray-400 mb-1 block">Field</label>
                        <input v-model="formRule.conditions[0].field" placeholder="e.g. cost" class="w-full rounded-lg text-sm border-gray-300 focus:ring-amber-400">
                    </div>
                    <div class="w-24">
                        <label class="text-xs text-gray-400 mb-1 block">Operator</label>
                        <select v-model="formRule.conditions[0].operator" class="w-full rounded-lg text-sm border-gray-300">
                            <option value=">">&gt;</option>
                            <option value="<">&lt;</option>
                            <option value="=">==</option>
                            <option value="!=">!=</option>
                            <option value=">=">≥</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="text-xs text-gray-400 mb-1 block">Value</label>
                        <input v-model="formRule.conditions[0].value" placeholder="e.g. 50000" type="number" class="w-full rounded-lg text-sm border-gray-300 focus:ring-amber-400">
                    </div>
                </div>
                <p class="text-xs text-amber-600 mt-2 italic">
                    Example: field = "cost", operator = "&gt;", value = "50000" → triggers when asset cost exceeds ₹50,000
                </p>
            </div>

            <!-- Action Builder -->
            <div class="bg-fuchsia-50 border border-fuchsia-100 p-4 rounded-xl mb-4">
                <p class="text-xs font-bold text-fuchsia-700 uppercase mb-3">Action — THEN</p>
                <div class="flex gap-2">
                    <div class="w-1/3">
                        <label class="text-xs text-gray-400 mb-1 block">Action Type</label>
                        <select v-model="formRule.actions[0].type" class="w-full rounded-lg text-sm border-fuchsia-200 focus:ring-fuchsia-400">
                            <option value="notify">Notify User</option>
                            <option value="block">Block Action</option>
                            <option value="flag">Flag for Review</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="text-xs text-gray-400 mb-1 block">Target (User/Role)</label>
                        <input v-model="formRule.actions[0].target" placeholder="e.g. admin, finance_head" class="w-full rounded-lg text-sm border-fuchsia-200 focus:ring-fuchsia-400">
                    </div>
                </div>
                <p class="text-xs text-fuchsia-600 mt-2 italic">
                    Example: Notify → admin (sends a dashboard notification to the admin user)
                </p>
            </div>

            <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-slate-100">
                <button type="button" @click="closeRuleModal" class="px-4 py-2 text-sm font-bold text-slate-400 hover:text-slate-600 transition">Cancel</button>
                <button type="submit" :disabled="formRule.processing"
                    class="bg-fuchsia-600 text-white px-5 py-2 rounded-lg font-bold hover:bg-fuchsia-700 disabled:opacity-50 flex items-center gap-2">
                    <span v-if="formRule.processing">{{ editingRule ? 'Updating...' : 'Injecting...' }}</span>
                    <span v-else>{{ editingRule ? '⚡ Update Rule' : '⚡ Inject Rule' }}</span>
                </button>
            </div>

            <div v-if="formRule.errors.name || formRule.errors.conditions || formRule.errors.actions" class="mt-3 text-red-600 text-sm">
                Please fill all required fields.
            </div>
        </form>
    </div>

    <!-- ─── INFO MODAL ─── -->
    <div v-if="showInfoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showInfoModal = false">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-[520px] max-h-[80vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <InformationCircleIcon class="h-5 w-5 text-blue-500" />
                    {{ infoContent.title }}
                </h2>
                <button @click="showInfoModal = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>
            <div class="text-sm text-gray-700 whitespace-pre-line leading-relaxed bg-blue-50 rounded-xl p-4 border border-blue-100">
                {{ infoContent.body }}
            </div>
            <div class="flex justify-end mt-4">
                <button @click="showInfoModal = false" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">Got it</button>
            </div>
        </div>
    </div>

</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.2s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>
