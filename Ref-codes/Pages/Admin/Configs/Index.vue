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
import GradientHeroHeader from "@/Components/UI/GradientHeroHeader.vue";


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
const showDeleteAttributeModal = ref(false);
const showRuleModal = ref(false);
const showDeleteRuleModal = ref(false);
const showInfoModal = ref(false);
const infoContent = ref({ title: '', body: '' });
const editingAttribute = ref(null);
const editingRule = ref(null);
const attributeToDelete = ref(null);
const ruleToDelete = ref(null);

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
    currency_code: props.settings?.currency_code ?? 'INR',
    currency_symbol: props.settings?.currency_symbol ?? '₹',
    currency_name: props.settings?.currency_name ?? 'Indian Rupee',
    date_format: props.settings?.date_format ?? 'DD/MM/YYYY',
    timezone: props.settings?.timezone ?? 'Asia/Kolkata',
});

const currencies = [
    { code: 'INR', symbol: '₹', name: 'Indian Rupee' },
    { code: 'USD', symbol: '$', name: 'US Dollar' },
    { code: 'EUR', symbol: '€', name: 'Euro' },
    { code: 'GBP', symbol: '£', name: 'British Pound' },
    { code: 'AED', symbol: 'د.إ', name: 'UAE Dirham' },
    { code: 'SGD', symbol: 'S$', name: 'Singapore Dollar' },
    { code: 'JPY', symbol: '¥', name: 'Japanese Yen' },
];

const onCurrencyChange = (code) => {
    const found = currencies.find(c => c.code === code);
    if (found) {
        formSettings.currency_symbol = found.symbol;
        formSettings.currency_name = found.name;
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
    router.delete(route('admin.assets.configs.attributes.destroy', id), {
        onSuccess: () => {
            closeDeleteAttributeConfirm();
            router.reload();
        },
    });
};

const openDeleteAttributeConfirm = (attr) => {
    attributeToDelete.value = attr;
    showDeleteAttributeModal.value = true;
};

const closeDeleteAttributeConfirm = () => {
    showDeleteAttributeModal.value = false;
    attributeToDelete.value = null;
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

const openDeleteRuleConfirm = (rule) => {
    ruleToDelete.value = rule;
    showDeleteRuleModal.value = true;
};

const closeDeleteRuleConfirm = () => {
    showDeleteRuleModal.value = false;
    ruleToDelete.value = null;
};

const deleteRule = (id) => {
    router.delete(route('admin.assets.configs.rules.destroy', id), {
        onSuccess: () => {
            closeDeleteRuleConfirm();
            router.reload();
        }
    });
};

const submitRule = () => {
    const resetRuleForm = () => {
        showRuleModal.value = false;
        formRule.reset();
        formRule.conditions = [{ field: 'cost', operator: '>', value: 0 }];
        formRule.actions = [{ type: 'notify', target: 'admin' }];
        editingRule.value = null;
    };

    const options = {
        onSuccess: () => {
            resetRuleForm();
        }
    };

    if (editingRule.value) {
        formRule.put(route('admin.assets.configs.rules.update', editingRule.value.id), options);
    } else {
        formRule.post(route('admin.assets.configs.rules.store'), options);
    }
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

const confirmDeleteAttribute = () => {
    if (!attributeToDelete.value) return;

    const attributeId = attributeToDelete.value.id;
    deleteAttribute(attributeId);
};

const confirmDeleteRule = () => {
    if (!ruleToDelete.value) return;

    const ruleId = ruleToDelete.value.id;
    deleteRule(ruleId);
};

const closeRuleModal = () => {
    showRuleModal.value = false;
    editingRule.value = null;
    formRule.reset();
    formRule.conditions = [{ field: 'cost', operator: '>', value: 0 }];
    formRule.actions = [{ type: 'notify', target: 'admin' }];
};
</script>

<template>

    <Head title="Configuration Hub" />

    <GradientHeroHeader kicker="" title=" Configuration Hub"
        subtitle="The Meta-Brain of the system. Define global logic and data structures.">
        <template #right>
            <div class="flex items-center gap-3 shrink-0 whitespace-nowrap">
                <div class="flex items-center gap-3 shrink-0 whitespace-nowrap">
                    <div
                        class="px-4 py-3 bg-slate-40 border border-slate-200 rounded-2xl flex flex-col items-end shadow-sm">
                        <span
                            class="text-slate-400 block text-[9px] font-bold uppercase tracking-widest mb-1 leading-none">Category
                            Hooks</span>
                        <span class="text-xl font-black text-slate-900 font-mono tracking-tight leading-none">{{
                            categories.reduce((acc, c) => acc + (c.attribute_definitions?.length || 0), 0)}}</span>
                    </div>
                    <div
                        class="px-4 py-3 bg-fuchsia-50 border border-fuchsia-100 rounded-2xl flex flex-col items-end shadow-sm">
                        <span
                            class="text-fuchsia-600 block text-[9px] font-bold uppercase tracking-widest mb-1 leading-none">Active
                            Rules</span>
                        <span class="text-xl font-black text-slate-900 font-mono tracking-tight leading-none">{{
                            rules?.length || 0 }}</span>
                    </div>
                </div>
            </div>
        </template>
    </GradientHeroHeader>

    <div class="p-6">

        <div
            class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col xl:flex-row gap-4 items-center mb-6 z-20 relative p-2">
            <div class="flex flex-wrap items-center gap-2 w-full xl:w-auto">
                <button v-for="t in ['attributes', 'rules', 'settings']" :key="t" @click="switchTab(t)"
                    class="h-10 px-4 rounded-xl text-[10px] font-bold uppercase tracking-widest transition-all shadow-sm border cursor-pointer"
                    :class="activeTab === t
                        ? 'bg-indigo-600 text-white border-indigo-500 shadow-md shadow-fuchsia-100'
                        : 'bg-slate-50 text-slate-500 border-slate-200 hover:border-fuchsia-300 hover:text-fuchsia-600 hover:bg-white'">
                    {{ t }}
                </button>
            </div>
            <button @click="showInfoHub"
                class="h-10 px-6 ml-auto text-[10px] font-bold uppercase tracking-widest text-fuchsia-600 shadow-sm rounded-xl transition-all hover:bg-slate-50 flex items-center gap-2 cursor-pointer active:scale-95">
                <InformationCircleIcon class="w-4 h-4" />
                Hub Notes
            </button>
        </div>

        <div class="flex-1 relative z-10 px-1 pb-8 w-full">

            <!-- ─── ATTRIBUTES TAB ─── -->
            <div v-if="activeTab === 'attributes'" class="space-y-6">
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-4 rounded-3xl border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-fuchsia-100 flex items-center justify-center text-fuchsia-600">
                            <CpuChipIcon class="h-6 w-6" />
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-800 tracking-tight">Custom Attribute Slots</h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Define
                                category-specific data fields</p>
                        </div>
                    </div>
                    <button @click="showInfoAttributes"
                        class="px-4 py-2 bg-slate-50 rounded-xl border border-slate-200 text-slate-400 hover:border-fuchsia-400 transition-all flex items-center gap-2 text-[10px] font-black uppercase tracking-widest cursor-pointer">
                        <InformationCircleIcon class="h-4 w-4" /> Documentation
                    </button>
                </div>
                <div v-for="cat in categories" :key="cat.id"
                    class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden transition-all hover:shadow-md">
                    <div @click="toggleCategory(cat.id)"
                        class="px-4 py-4 bg-slate-50 flex justify-between items-center cursor-pointer hover:bg-fuchsia-50 transition">
                        <div>
                            <h3 class="text-lg font-black text-slate-800 tracking-tight">{{ cat.name }}</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{
                                cat.attribute_definitions?.length ?? 0 }} Registered Slots</p>
                        </div>
                        <div
                            class="w-10 h-10 rounded-full bg-white border border-slate-100 flex items-center justify-center text-slate-400 shadow-sm">
                            <component :is="expandedCategory === cat.id ? ChevronUpIcon : ChevronDownIcon"
                                class="h-5 w-5" />
                        </div>
                    </div>

                    <div v-if="expandedCategory === cat.id"
                        class="p-3 bg-white border-t border-slate-100 animate-fade-in">
                        <div v-if="cat.attribute_definitions?.length"
                            class="grid grid-cols-1 md:grid-cols-2 gap-2.5 mb-5">
                            <div v-for="def in cat.attribute_definitions" :key="def.id"
                                class="flex justify-between items-center p-3 bg-slate-50 rounded-2xl border border-slate-100 group transition-all hover:bg-white hover:shadow-md hover:border-fuchsia-100">
                                <span class="text-[11px] font-black text-slate-600 uppercase tracking-widest">{{
                                    def.name
                                }}</span>
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-1.5">
                                        <span v-if="def.is_required"
                                            class="text-[8px] font-black uppercase text-rose-500 bg-rose-50 px-2 py-0.5 rounded-md">Required</span>
                                        <span
                                            class="text-[8px] font-black uppercase text-fuchsia-600 bg-fuchsia-50 px-2 py-0.5 rounded-md">{{
                                                def.field_type }}</span>
                                    </div>
                                    <div class="flex gap-3">
                                        <button @click.stop="openEditAttribute(def, cat.id)"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-white border border-slate-100 text-slate-400 hover:text-fuchsia-600 transition shadow-sm cursor-pointer hover:bg-fuchsia-50">
                                            <PencilIcon class="h-3.5 w-3.5" />
                                        </button>
                                        <button @click.stop="openDeleteAttributeConfirm(def)"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-white border border-slate-100 text-rose-600 transition shadow-sm cursor-pointer hover:bg-rose-50 hover:text-rose-700">
                                            <TrashIcon class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else
                            class="text-center py-6 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200 mb-5">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">No hooks
                                defined for this categorical branch</p>
                        </div>

                        <button @click="openAddAttribute(cat.id)"
                            class="px-3 py-2 bg-fuchsia-600 text-white rounded-2xl text-[9px] font-black uppercase tracking-widest shadow-lg shadow-fuchsia-200 transition-all hover:scale-105 active:scale-95 flex items-center gap-1 cursor-pointer">
                            <PlusIcon class="h-3.5 w-3.5" /> Register Slot
                        </button>
                    </div>
                </div>
            </div>

            <!-- ─── RULES TAB ─── -->
            <!-- ─── RULES TAB ─── -->
            <div v-if="activeTab === 'rules'" class="space-y-6">
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-4 rounded-3xl border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-fuchsia-100 flex items-center justify-center text-fuchsia-600">
                            <ScaleIcon class="h-6 w-6" />
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-800 tracking-tight">Business Logic Rules</h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Automated
                                triggers & conditions</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="showInfoRules"
                            class="px-3 py-2 bg-slate-50 rounded-xl border border-slate-200 text-slate-400 hover:text-fuchsia-600 hover:border-fuchsia-200 transition-all flex items-center gap-2 text-[10px] font-black uppercase tracking-widest cursor-pointer">
                            <InformationCircleIcon class="h-4 w-4" /> Rules Guide
                        </button>
                        <button @click="showRuleModal = true"
                            class="px-3 py-2 bg-fuchsia-600 text-white rounded-2xl hover:bg-fuchsia-700 font-black shadow-lg shadow-fuchsia-200 flex items-center gap-2 text-[11px] uppercase tracking-widest transition-all active:scale-95 cursor-pointer">
                            <PlusIcon class="h-5 w-5" /> Inject Rule
                        </button>
                    </div>
                </div>

                <div class="grid gap-6">
                    <div v-for="rule in rules" :key="rule.id"
                        class="bg-white py-3 px-4 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all group relative overflow-hidden">
                        <div
                            class="absolute top-0 right-0 w-32 h-32 bg-fuchsia-50/50 rounded-full -mr-16 -mt-16 blur-3xl group-hover:bg-fuchsia-100/60 transition-colors">
                        </div>

                        <div class="flex flex-col lg:flex-row justify-between lg:items-center gap-6 relative z-10">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-3 h-3 rounded-full ring-4 ring-white shadow-sm"
                                        :class="rule.is_active ? 'bg-emerald-500 shadow-emerald-200' : 'bg-slate-300 shadow-slate-200'">
                                    </div>
                                    <h3 class="text-lg font-black text-slate-800 tracking-tight">{{ rule.name }}</h3>
                                    <span
                                        class="text-[9px] font-black uppercase tracking-wider px-3 py-1 bg-slate-100 rounded-full text-slate-500">{{
                                            rule.module }}</span>
                                </div>
                                <div
                                    class="flex flex-wrap items-center gap-2 font-mono text-[11px] bg-slate-100/80 p-3 rounded-2xl border border-slate-100/50">
                                    <span
                                        class="text-slate-500 font-bold uppercase tracking-widest text-[9px]">Trigger:</span>
                                    <span class="text-fuchsia-600 font-black uppercase">{{ rule.trigger_event }}</span>
                                    <template v-if="rule.conditions?.[0]">
                                        <span class="mx-2 text-slate-300">|</span>
                                        <span
                                            class="text-slate-400 font-bold uppercase tracking-widest text-[9px]">Condition:</span>
                                        <span class="text-slate-800 font-black uppercase tracking-tighter">{{
                                            rule.conditions[0].field }}</span>
                                        <span class="text-fuchsia-500 font-bold">{{ rule.conditions[0].operator
                                            }}</span>
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
                                    <button @click="openEditRule(rule)"
                                        class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 border border-slate-100 text-slate-400 hover:bg-white hover:text-fuchsia-600 hover:shadow-md transition-all cursor-pointer">
                                        <PencilIcon class="h-4 w-4" />
                                    </button>
                                    <button @click="openDeleteRuleConfirm(rule)"
                                        class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 border border-slate-100 text-rose-600 hover:bg-rose-50 hover:text-rose-700 hover:shadow-lg transition-all cursor-pointer">
                                        <TrashIcon class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="!rules?.length"
                        class="text-center py-24 bg-white rounded-3xl border border-dashed border-slate-200">
                        <ScaleIcon class="h-16 w-16 mx-auto mb-4 text-slate-200" />
                        <h3 class="text-xl font-black text-slate-800 uppercase tracking-tighter">No logic hooks detected
                        </h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">The core is
                            running in standard
                            linear mode</p>
                    </div>
                </div>
            </div>

            <!-- ─── SETTINGS TAB ─── -->
            <!-- ─── SETTINGS TAB ─── -->
            <div v-if="activeTab === 'settings'" class="space-y-6">
                <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white px-2 py-3 shadow-sm">
                    <div
                        class="absolute -right-24 -top-24 h-56 w-56 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none">
                    </div>
                    <div
                        class="absolute -left-24 bottom-0 h-56 w-56 rounded-full bg-fuchsia-500/10 blur-3xl pointer-events-none">
                    </div>
                    <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/20 shrink-0">
                                <CurrencyRupeeIcon class="h-6 w-6" />
                            </div>
                            <div>
                                <div
                                    class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-[8px] font-black uppercase tracking-[0.22em] text-emerald-700">
                                    System Settings
                                </div>
                                <h2 class="mt-2 text-2xl font-black uppercase tracking-tight text-slate-950">
                                    Configuration Studio
                                </h2>
                                <p class="mt-1 text-xs font-medium text-slate-500">
                                    Tune currency, date, and timezone behavior across the asset hub.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="saveSettings" class="space-y-6">
                    <div
                        class="relative overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div
                            class="absolute -right-20 -top-20 h-48 w-48 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none">
                        </div>
                        <div class="relative flex items-center gap-4 mb-5">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/20">
                                <CurrencyRupeeIcon class="h-7 w-7" />
                            </div>
                            <div>
                                <h3 class="text-lg font-black uppercase tracking-tight text-slate-900">Financial Anchor
                                </h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Primary
                                    currency and cost rendering</p>
                            </div>
                        </div>

                        <div class="relative grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="space-y-1.5">
                                <label
                                    class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Currency
                                    Type</label>
                                <div class="relative">
                                    <select v-model="formSettings.currency_code"
                                        @change="onCurrencyChange(formSettings.currency_code)"
                                        class="mt-1 w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 pr-10 text-xs font-semibold text-slate-900 outline-none transition-all focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10">
                                        <option v-for="c in currencies" :key="c.code" :value="c.code">
                                            {{ c.symbol }} - {{ c.name }} ({{ c.code }})
                                        </option>
                                    </select>
                                    <ChevronDownIcon
                                        class="pointer-events-none absolute right-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label
                                    class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Symbol</label>
                                <input v-model="formSettings.currency_symbol" type="text"
                                    class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 text-xs font-semibold text-slate-900 outline-none transition-all focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 appearance-none">
                            </div>
                            <div class="space-y-1.5">
                                <label
                                    class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">System
                                    Label</label>
                                <input v-model="formSettings.currency_name" type="text"
                                    class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 text-xs font-semibold text-slate-900 outline-none transition-all focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 appearance-none">
                            </div>
                        </div>

                        <div
                            class="relative mt-6 overflow-hidden rounded-[1.5rem] border border-slate-700 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700 p-5 shadow-lg shadow-slate-900/20">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[8px] font-black uppercase tracking-[0.3em] text-slate-200">Live
                                    Rendering
                                    Preview</span>
                                <div class="flex gap-1">
                                    <div class="h-1 w-1 rounded-full bg-emerald-500 animate-pulse"></div>
                                    <div class="h-1 w-1 rounded-full bg-emerald-500/50"></div>
                                </div>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-black text-white tabular-nums">{{
                                    formSettings.currency_symbol
                                }}</span>
                                <span class="text-2xs font-black uppercase tracking-widest text-slate-200">{{
                                    formSettings.currency_code }}</span>
                            </div>
                            <p
                                class="mt-2 border-t border-slate-600 pt-2 text-[9px] font-bold uppercase tracking-widest text-slate-100/80 italic">
                                {{ formSettings.currency_name }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="relative overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="relative flex items-center gap-4 mb-5">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-fuchsia-50 text-fuchsia-600 border border-fuchsia-100 shadow-sm">
                                <InformationCircleIcon class="h-7 w-7" />
                            </div>
                            <div>
                                <h3 class="text-lg font-black uppercase tracking-tight text-slate-900">Regional Protocol
                                </h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                                    Chronological
                                    and spatial formats</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <label
                                    class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Date
                                    Sequence</label>
                                <div class="relative">
                                    <select v-model="formSettings.date_format"
                                        class="mt-1 w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 pr-10 text-xs font-semibold text-slate-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
                                        <option value="DD/MM/YYYY">DD/MM/YYYY (Indian Standard)</option>
                                        <option value="MM/DD/YYYY">MM/DD/YYYY (USA Format)</option>
                                        <option value="YYYY-MM-DD">YYYY-MM-DD (ISO 8601)</option>
                                    </select>
                                    <ChevronDownIcon
                                        class="pointer-events-none absolute right-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label
                                    class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Temporal
                                    Zone</label>
                                <div class="relative">
                                    <select v-model="formSettings.timezone"
                                        class="mt-1 w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 pr-10 text-xs font-semibold text-slate-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
                                        <option value="Asia/Kolkata">Asia/Kolkata (IST +5:30)</option>
                                        <option value="UTC">Universal Time Coordinated (UTC)</option>
                                        <option value="America/New_York">America/New_York (EST)</option>
                                        <option value="Europe/London">Europe/London (GMT)</option>
                                        <option value="Asia/Dubai">Asia/Dubai (GST +4)</option>
                                    </select>
                                    <ChevronDownIcon
                                        class="pointer-events-none absolute right-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" :disabled="formSettings.processing"
                            class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-gradient-to-r from-fuchsia-600 to-violet-600 px-3.5 text-[10px] font-black uppercase tracking-[0.16em] text-white shadow-[0_12px_30px_-10px_rgba(217,70,239,0.6)] transition-all hover:scale-[1.01] hover:from-fuchsia-500 hover:to-violet-500 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100 cursor-pointer">
                            <span class="h-1.5 w-1.5 rounded-full bg-white/80"></span>
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
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/55 backdrop-blur-md p-4 transition-all"
        @click.self="closeAttributeModal">
        <form @submit.prevent="submitAttribute"
            class="relative w-full max-w-lg overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-[0_30px_90px_-30px_rgba(15,23,42,0.5)]">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-fuchsia-500 via-violet-500 to-indigo-500">
            </div>
            <div
                class="absolute -right-20 -top-20 h-52 w-52 rounded-full bg-fuchsia-500/10 blur-3xl pointer-events-none">
            </div>
            <div
                class="absolute -left-24 bottom-0 h-56 w-56 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none">
            </div>

            <div class="relative flex items-start justify-between gap-3 border-b border-slate-100 px-5 py-4">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-fuchsia-500 to-violet-600 text-white shadow-lg shadow-fuchsia-500/20">
                        <PlusIcon v-if="!editingAttribute" class="h-5 w-5" />
                        <PencilIcon v-else class="h-4 w-4" />
                    </div>
                    <div>
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-fuchsia-100 bg-fuchsia-50 px-2.5 py-1 text-[8px] font-black uppercase tracking-[0.22em] text-fuchsia-600">
                            {{ editingAttribute ? 'Update Slot' : 'Attribute Slot' }}
                        </div>
                        <h2 class="mt-2.5 text-xl font-black uppercase tracking-tight text-slate-950">
                            {{ editingAttribute ? 'Edit Attribute Slot' : 'Define New Slot' }}
                        </h2>
                        <p class="mt-1.5 max-w-md text-xs font-medium leading-relaxed text-slate-500">
                            {{ editingAttribute ? 'Refine the field label and type for this category.' : 'Create a polished custom field that appears automatically when assets of this category are created or edited.'}}
                        </p>
                    </div>
                </div>

                <button type="button" @click="closeAttributeModal"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500 active:scale-95 cursor-pointer"
                    aria-label="Close attribute modal">
                    &times;
                </button>
            </div>

            <div class="relative p-5 space-y-4">
                <div class="space-y-1.5">
                    <label class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Attribute
                        Name</label>
                    <input v-model="formAttr.name" type="text" placeholder="e.g. Screen Size, Warranty Period"
                        class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 text-xs font-semibold text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-fuchsia-500 focus:bg-white focus:ring-4 focus:ring-fuchsia-500/10 appearance-none"
                        required>
                    <span v-if="formAttr.errors.name" class="text-red-500 text-xs mt-1">{{ formAttr.errors.name
                    }}</span>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1.5">
                        <label class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Field
                            Type</label>
                        <div class="relative">
                            <select v-model="formAttr.field_type"
                                class="mt-1 w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 pr-10 text-xs font-semibold text-slate-900 outline-none transition-all focus:border-fuchsia-500 focus:bg-white focus:ring-4 focus:ring-fuchsia-500/10">
                                <option value="text">Text (free input)</option>
                                <option value="number">Number</option>
                                <option value="boolean">Yes / No</option>
                                <option value="date">Date</option>
                                <option value="select">Select (dropdown)</option>
                            </select>
                            <ChevronDownIcon
                                class="pointer-events-none absolute right-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label
                            class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Visibility</label>
                        <div class="flex h-11 items-center rounded-xl border border-slate-200 bg-slate-50/80 px-3">
                            <input v-model="formAttr.is_required" type="checkbox" id="req"
                                class="h-4 w-4 rounded border-slate-300 text-fuchsia-600 focus:ring-fuchsia-500">
                            <label for="req" class="ml-2.5 text-xs font-semibold text-slate-700">Required field</label>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="flex flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50/70 px-5 py-3 sm:flex-row sm:justify-end">
                <button type="button" @click="closeAttributeModal"
                    class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3.5 text-[10px] font-black uppercase tracking-[0.16em] text-slate-500 shadow-sm transition-all hover:border-slate-300 hover:text-slate-700 active:scale-95 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" :disabled="formAttr.processing"
                    class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-gradient-to-r from-fuchsia-600 to-violet-600 px-3.5 text-[10px] font-black uppercase tracking-[0.16em] text-white shadow-[0_12px_30px_-10px_rgba(217,70,239,0.6)] transition-all hover:scale-[1.01] hover:from-fuchsia-500 hover:to-violet-500 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100 cursor-pointer">
                    <span class="h-1.5 w-1.5 rounded-full bg-white/80"></span>
                    {{ formAttr.processing ? 'Saving...' : (editingAttribute ? 'Update Slot' : 'Save Slot') }}
                </button>
            </div>
        </form>
    </div>

    <div v-if="showDeleteAttributeModal"
        class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4 transition-all"
        @click.self="closeDeleteAttributeConfirm">
        <div
            class="relative w-full max-w-sm overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-[0_30px_90px_-30px_rgba(15,23,42,0.55)]">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-rose-500 via-red-500 to-orange-500"></div>
            <div class="p-4">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600 border border-rose-100 shrink-0">
                        <TrashIcon class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-[0.22em] text-rose-500">Delete Attribute</p>
                        <h3 class="mt-2 text-lg font-black uppercase tracking-tight text-slate-950">
                            Remove this slot?
                        </h3>
                        <p class="mt-2 text-xs font-medium leading-relaxed text-slate-500">
                            This will permanently delete
                            <span class="font-black text-slate-900">
                                "{{ attributeToDelete?.name || 'this attribute' }}"
                            </span>
                            from the category.
                        </p>
                    </div>
                </div>

                <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" @click="closeDeleteAttributeConfirm"
                        class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3.5 text-[10px] font-black uppercase tracking-[0.16em] text-slate-500 shadow-sm transition-all hover:border-slate-300 hover:text-slate-700 active:scale-95 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" @click="confirmDeleteAttribute"
                        class="inline-flex h-9 items-center justify-center rounded-lg bg-gradient-to-r from-rose-600 to-red-600 px-3.5 text-[10px] font-black uppercase tracking-[0.16em] text-white shadow-[0_12px_30px_-10px_rgba(239,68,68,0.55)] transition-all hover:scale-[1.01] hover:from-rose-500 hover:to-red-500 active:scale-95 cursor-pointer">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div v-if="showDeleteRuleModal"
        class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4 transition-all"
        @click.self="closeDeleteRuleConfirm">
        <div
            class="relative w-full max-w-sm overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-[0_30px_90px_-30px_rgba(15,23,42,0.55)]">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-rose-500 via-red-500 to-orange-500"></div>
            <div class="p-5">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600 border border-rose-100 shrink-0">
                        <TrashIcon class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-[0.22em] text-rose-500">Delete Rule</p>
                        <h3 class="mt-2 text-lg font-black uppercase tracking-tight text-slate-950">
                            Remove this rule?
                        </h3>
                        <p class="mt-2 text-xs font-medium leading-relaxed text-slate-500">
                            This will permanently delete
                            <span class="font-black text-slate-900">
                                "{{ ruleToDelete?.name || 'this rule' }}"
                            </span>
                            from the configuration hub.
                        </p>
                    </div>
                </div>

                <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" @click="closeDeleteRuleConfirm"
                        class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3.5 text-[10px] font-black uppercase tracking-[0.16em] text-slate-500 shadow-sm transition-all hover:border-slate-300 hover:text-slate-700 active:scale-95 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" @click="confirmDeleteRule"
                        class="inline-flex h-9 items-center justify-center rounded-lg bg-gradient-to-r from-rose-600 to-red-600 px-3.5 text-[10px] font-black uppercase tracking-[0.16em] text-white shadow-[0_12px_30px_-10px_rgba(239,68,68,0.55)] transition-all hover:scale-[1.01] hover:from-rose-500 hover:to-red-500 active:scale-95 cursor-pointer">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── MODAL: INJECT RULE ─── -->
    <div v-if="showRuleModal"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/55 backdrop-blur-md p-4 transition-all"
        @click.self="closeRuleModal">
        <form @submit.prevent="submitRule"
            class="relative w-full max-w-2xl max-h-[calc(100vh-2rem)] overflow-y-auto overflow-x-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-[0_30px_90px_-30px_rgba(15,23,42,0.55)]">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-fuchsia-500 via-violet-500 to-indigo-500">
            </div>
            <div
                class="absolute -right-24 -top-24 h-56 w-56 rounded-full bg-fuchsia-500/10 blur-3xl pointer-events-none">
            </div>
            <div
                class="absolute -left-24 bottom-0 h-56 w-56 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none">
            </div>
            <div class="relative flex items-start justify-between gap-3 border-b border-slate-100 px-5 py-4">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-fuchsia-500 to-violet-600 text-white shadow-lg shadow-fuchsia-500/20 shrink-0">
                        <ScaleIcon class="h-5 w-5" />
                    </div>
                    <div>
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-fuchsia-100 bg-fuchsia-50 px-2.5 py-1 text-[8px] font-black uppercase tracking-[0.22em] text-fuchsia-600">
                            {{ editingRule ? 'Update Rule' : 'Business Rule' }}
                        </div>
                        <h2 class="mt-2.5 text-xl font-black uppercase tracking-tight text-slate-950">
                            {{ editingRule ? 'Update Logic Rule' : 'Inject Logic Rule' }}
                        </h2>
                        <p class="mt-1.5 max-w-2xl text-xs font-medium leading-relaxed text-slate-500">
                            {{ editingRule ? 'Modify the trigger, conditions, and action for this automation rule.' :
                                'Create a compact automation trigger that runs when your selected condition is met.' }}
                        </p>
                    </div>
                </div>
                <button type="button" @click="closeRuleModal"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500 active:scale-95 cursor-pointer"
                    aria-label="Close rule modal">&times;</button>
            </div>

            <div class="relative p-5 space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Rule
                            Name</label>
                        <input v-model="formRule.name" type="text" placeholder="e.g. High Value Alert"
                            class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 text-xs font-semibold text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-fuchsia-500 focus:bg-white focus:ring-4 focus:ring-fuchsia-500/10 appearance-none"
                            required>
                        <span v-if="formRule.errors.name" class="text-red-500 text-xs">{{ formRule.errors.name }}</span>
                    </div>

                    <div class="space-y-1.5">
                        <label
                            class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Module</label>
                        <div class="relative">
                            <select v-model="formRule.module"
                                class="mt-1 w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 pr-10 text-xs font-semibold text-slate-900 outline-none transition-all focus:border-fuchsia-500 focus:bg-white focus:ring-4 focus:ring-fuchsia-500/10">
                                <option value="Asset">Asset</option>
                                <option value="Inventory">Inventory</option>
                                <option value="Procurement">Procurement</option>
                                <option value="Document">Document</option>
                            </select>
                            <ChevronDownIcon
                                class="pointer-events-none absolute right-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Trigger
                            Event</label>
                        <div class="relative">
                            <select v-model="formRule.trigger_event"
                                class="mt-1 w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 pr-10 text-xs font-semibold text-slate-900 outline-none transition-all focus:border-fuchsia-500 focus:bg-white focus:ring-4 focus:ring-fuchsia-500/10">
                                <option value="Create">On Create</option>
                                <option value="Update">On Update</option>
                                <option value="Delete">On Delete</option>
                                <option value="Threshold">On Threshold</option>
                            </select>
                            <ChevronDownIcon
                                class="pointer-events-none absolute right-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label
                            class="mb-1.5 block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Status</label>
                        <div class="flex h-11 items-center rounded-xl border border-slate-200 bg-slate-50/80 px-3">
                            <input v-model="formRule.is_active" type="checkbox" id="ruleActive"
                                class="h-4 w-4 rounded border-slate-300 text-fuchsia-600 focus:ring-fuchsia-500">
                            <label for="ruleActive" class="ml-2.5 text-xs font-semibold text-slate-700">Active
                                immediately</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Condition Builder -->
            <div class="rounded-2xl border border-amber-100 bg-amber-50/70 p-4">
                <p class="text-xs font-bold text-amber-700 uppercase mb-3 flex items-center gap-1">
                    Condition — IF
                </p>
                <div class="grid grid-cols-1 gap-3 lg:grid-cols-[1fr_110px_1fr]">
                    <div class="space-y-1.5">
                        <label
                            class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Field</label>
                        <input v-model="formRule.conditions[0].field" placeholder="e.g. cost"
                            class="mt-1 w-full appearance-none rounded-xl border border-amber-200 bg-white/90 px-3 py-2.5 text-xs font-semibold text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-400/10">
                    </div>
                    <div class="space-y-1.5">
                        <label
                            class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Operator</label>
                        <select v-model="formRule.conditions[0].operator"
                            class="mt-1 w-full appearance-none rounded-xl border border-amber-200 bg-white/90 px-3 py-2.5 pr-9 text-xs font-semibold text-slate-900 outline-none transition-all focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-400/10">
                            <option value=">">&gt;</option>
                            <option value="<">&lt;</option>
                            <option value="=">==</option>
                            <option value="!=">!=</option>
                            <option value=">=">≥</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label
                            class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Value</label>
                        <input v-model="formRule.conditions[0].value" placeholder="e.g. 50000" type="number"
                            class="mt-1 w-full appearance-none rounded-xl border border-amber-200 bg-white/90 px-3 py-2.5 text-xs font-semibold text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-400/10">
                    </div>
                </div>
                <p class="text-xs text-amber-600 mt-2 italic">
                    Example: field = "cost", operator = "&gt;", value = "50000" → triggers when asset cost exceeds
                    ₹50,000
                </p>
            </div>

            <!-- Action Builder -->
            <div class="rounded-2xl border border-fuchsia-100 bg-fuchsia-50/70 p-4">
                <p class="text-xs font-bold text-fuchsia-700 uppercase mb-3">Action — THEN</p>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-[180px_1fr]">
                    <div class="space-y-1.5">
                        <label class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Action
                            Type</label>
                        <select v-model="formRule.actions[0].type"
                            class="mt-1 w-full appearance-none rounded-xl border border-fuchsia-200 bg-white/90 px-3 py-2.5 pr-10 text-xs font-semibold text-slate-900 outline-none transition-all focus:border-fuchsia-400 focus:bg-white focus:ring-4 focus:ring-fuchsia-400/10">
                            <option value="notify">Notify User</option>
                            <option value="block">Block Action</option>
                            <option value="flag">Flag for Review</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Target
                            (User/Role)</label>
                        <input v-model="formRule.actions[0].target" placeholder="e.g. admin, finance_head"
                            class="mt-1 w-full appearance-none rounded-xl border border-fuchsia-200 bg-white/90 px-3 py-2.5 text-xs font-semibold text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-fuchsia-400 focus:bg-white focus:ring-4 focus:ring-fuchsia-400/10">
                    </div>
                </div>
                <p class="text-xs text-fuchsia-600 mt-2 italic">
                    Example: Notify → admin (sends a dashboard notification to the admin user)
                </p>
            </div>

            <div
                class="flex flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50/70 px-5 py-3 sm:flex-row sm:justify-end">
                <button type="button" @click="closeRuleModal"
                    class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3.5 text-[10px] font-black uppercase tracking-[0.16em] text-slate-500 shadow-sm transition-all hover:border-slate-300 hover:text-slate-700 active:scale-95 cursor-pointer">Cancel</button>
                <button type="submit" :disabled="formRule.processing"
                    class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-gradient-to-r from-fuchsia-600 to-violet-600 px-3.5 text-[10px] font-black uppercase tracking-[0.16em] text-white shadow-[0_12px_30px_-10px_rgba(217,70,239,0.6)] transition-all hover:scale-[1.01] hover:from-fuchsia-500 hover:to-violet-500 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100 cursor-pointer">
                    <span class="h-1.5 w-1.5 rounded-full bg-white/80"></span>
                    <span v-if="formRule.processing">{{ editingRule ? 'Updating...' : 'Injecting...' }}</span>
                    <span v-else>{{ editingRule ? 'Update Rule' : 'Inject Rule' }}</span>
                </button>
            </div>

            <div v-if="formRule.errors.name || formRule.errors.conditions || formRule.errors.actions"
                class="px-5 pb-4 text-xs text-red-600">
                Please fill all required fields.
            </div>
        </form>
    </div>

    <!-- ─── INFO MODAL ─── -->
    <div v-if="showInfoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
        @click.self="showInfoModal = false">
        <div class="bg-white rounded-3xl shadow-2xl p-6 w-[520px] max-h-[80vh] overflow-y-auto border border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    {{ infoContent.title }}
                </h2>
            </div>
            <div
                class="text-sm text-gray-700 whitespace-pre-line leading-relaxed bg-blue-50 rounded-xl p-4 border border-blue-100">
                {{ infoContent.body }}
            </div>
            <div class="flex justify-end mt-4">
                <button @click="showInfoModal = false"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 cursor-pointer">Got
                    it</button>
            </div>
        </div>
    </div>

</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-5px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
