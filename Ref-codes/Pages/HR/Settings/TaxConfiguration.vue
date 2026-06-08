<template>
    <div class="space-y-6 pt-6 px-6">
        <!-- Gradient Header Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-700 via-violet-800 to-violet-600 px-6 py-6 text-white shadow-sm border border-white/10">
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-12 left-1/4 h-32 w-32 rounded-full bg-white/10 blur-2xl"></div>
                <div class="absolute -bottom-16 right-10 h-44 w-44 rounded-full bg-white/10 blur-2xl"></div>
            </div>
            <div class="relative z-10">
                <h1 class="text-2xl lg:text-3xl font-bold tracking-tight">Tax Configuration</h1>
                <p class="text-sm lg:text-base text-slate-200 mt-2 font-medium max-w-2xl">
                    Manage tax slabs, deduction limits, and compliance timelines
                </p>
            </div>
        </div>

        <!-- Tabs Header -->
      <div class="sticky top-0 z-30 border-b border-gray-100 bg-white px-2 py-2 shadow-sm">
                      <nav class="flex items-center gap-3 overflow-x-auto">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            activeTab === tab.id
                                ? 'bg-blue-50 text-blue-700 border-blue-200 shadow-sm'
                                : 'bg-white text-gray-600 border-transparent hover:bg-gray-50 hover:text-gray-900',
                            'inline-flex items-center px-5 h-[48px] rounded-lg border text-sm font-black transition-all duration-200 whitespace-nowrap'
                        ]"
                    >
                        {{ tab.name }}
                    </button>
                </nav>
            </div>

        <!-- MAIN CONTAINER -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

    

            <!-- TAB CONTENT AREA -->
          

                <!-- TAB A -->
           <!-- TAB A -->
<div v-if="activeTab === 'scheduler'" class="space-y-6">

    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">

        <!-- Header -->
        <div class="mb-6">
            <h3 class="text-lg font-black text-gray-900">Financial Year & Timelines</h3>
            <p class="text-sm text-gray-500 mt-1">Configure declaration and proof submission windows</p>
        </div>

        <!-- FORM GRID -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Financial Year -->
            <div class="space-y-1">
                <label class="text-sm font-semibold text-gray-600">Financial Year</label>
                <select v-model="settingsForm.financial_year"
                    class="w-full h-11 px-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="2025-2026">2025-2026</option>
                    <option value="2026-2027">2026-2027</option>
                </select>
            </div>

            <!-- Default Regime -->
            <div class="space-y-1">
                <label class="text-sm font-semibold text-gray-600">Default Regime</label>
                <select v-model="settingsForm.default_regime"
                    class="w-full h-11 px-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="New">New</option>
                    <option value="Old">Old</option>
                </select>
            </div>

            <!-- Declaration Window -->
            <div class="md:col-span-2 mt-2">
                <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                    <h4 class="text-sm font-bold text-gray-800 mb-3">Declaration Window</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs text-gray-500">Start Date</label>
                            <input type="date"
                                v-model="settingsForm.declaration_window_start"
                                class="w-full h-11 px-3 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs text-gray-500">End Date</label>
                            <input type="date"
                                v-model="settingsForm.declaration_window_end"
                                class="w-full h-11 px-3 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Proof Window -->
            <div class="md:col-span-2">
                <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                    <h4 class="text-sm font-bold text-gray-800 mb-3">Proof Submission Window</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs text-gray-500">Start Date</label>
                            <input type="date"
                                v-model="settingsForm.proof_window_start"
                                class="w-full h-11 px-3 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs text-gray-500">End Date</label>
                            <input type="date"
                                v-model="settingsForm.proof_window_end"
                                class="w-full h-11 px-3 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ACTION -->
        <div class="mt-8 flex justify-end">
            <button 
                @click="saveSettings"
                class="px-6 h-11 rounded-xl bg-blue-600 text-white text-sm font-bold shadow-sm hover:bg-blue-700 transition"
            >
                Save Timelines
            </button>
        </div>

    </div>
</div>

              <!-- TAB B -->
<div v-if="activeTab === 'slabs'" class="space-y-6">

    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-lg font-black text-gray-900">Tax Slabs</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Configure income ranges and tax percentages
                </p>
            </div>

            <!-- REGIME SWITCH -->
            <div class="bg-gray-100 p-1 rounded-xl flex">
                <button 
                    @click="slabRegime='New'" 
                    :class="slabRegime==='New' ? 'tab-pill-active' : 'tab-pill'"
                >
                    New
                </button>

                <button 
                    @click="slabRegime='Old'" 
                    :class="slabRegime==='Old' ? 'tab-pill-active' : 'tab-pill'"
                >
                    Old
                </button>
            </div>
        </div>

        <!-- TABLE CONTAINER -->
        <div class="overflow-hidden border border-gray-100 rounded-xl">

            <table class="min-w-full text-sm">

                <!-- TABLE HEADER -->
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Min Income (₹)</th>
                        <th class="px-4 py-3 text-left">Max Income (₹)</th>
                        <th class="px-4 py-3 text-left">Tax Rate (%)</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>

                <!-- TABLE BODY -->
                <tbody class="divide-y divide-gray-100 bg-white">

                 <tr 
    v-for="(slab,i) in activeSlabs" 
    :key="i"
    class="hover:bg-gray-50 transition"
>

    <td class="px-3 py-1.5">
        <input type="number" v-model="slab.min_income" class="input" />
    </td>

    <td class="px-3 py-1.5">
        <input type="number" v-model="slab.max_income" class="input" />
    </td>

    <td class="px-3 py-1.5">
        <div class="relative">
            <input type="number" v-model="slab.tax_rate_percentage" class="input pr-8" />
            <span class="absolute right-2 top-1.5 text-gray-400 text-xs">%</span>
        </div>
    </td>

    <td class="px-3 py-1.5 text-right">
        <button 
            @click="removeSlab(i)"
            class="w-7 h-7 flex items-center justify-center rounded-md bg-red-50 text-red-500 hover:bg-red-100 transition"
        >
            ×
        </button>
    </td>

</tr>
                    <!-- EMPTY STATE -->
                    <tr v-if="activeSlabs.length === 0">
                        <td colspan="4" class="text-center py-8 text-gray-400">
                            No slabs added yet
                        </td>
                    </tr>

                </tbody>
            </table>

        </div>

        <!-- ACTIONS -->
   <!-- ACTIONS -->
<div class="flex justify-between items-center mt-6">

    <!-- ADD BUTTON (NEW STYLE) -->
    <button 
        @click="addSlab" 
        class="flex items-center gap-2 px-4 h-11 rounded-xl border border-blue-200 bg-blue-50 text-blue-700 font-semibold hover:bg-blue-100 transition"
    >
        <span class="text-lg">+</span>
        Add Slab
    </button>

    <!-- SAVE BUTTON -->
    <button 
        @click="saveSlabs" 
        class="px-6 h-11 rounded-xl bg-blue-600 text-white font-bold shadow-sm hover:bg-blue-700 transition"
    >
        Save Slabs
    </button>

</div>

    </div>
</div>

                <!-- TAB C -->
<div v-if="activeTab === 'limits'" class="space-y-6">

    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">

        <!-- HEADER -->
        <div class="mb-6">
            <h3 class="text-lg font-black text-gray-900">Section Limits</h3>
            <p class="text-sm text-gray-500 mt-1">
                Configure deduction limits and enable/disable sections
            </p>
        </div>

        <!-- TABLE -->
        <div class="overflow-hidden border border-gray-100 rounded-xl">

            <div class="max-h-[420px] overflow-y-auto">

                <table class="min-w-full text-sm">

                    <!-- HEADER -->
                    <thead class="bg-blue-50 text-blue-700 uppercase text-xs tracking-wide sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold">Section</th>
                            <th class="px-3 py-2 text-left font-semibold">Description</th>
                            <th class="px-3 py-2 text-left font-semibold">Max Deduction (₹)</th>
                            <th class="px-3 py-2 text-center font-semibold">Active</th>
                        </tr>
                    </thead>

                    <!-- BODY -->
                    <tbody class="divide-y divide-gray-100 bg-white">

                        <tr 
                            v-for="s in sectionsLocal" 
                            :key="s.id"
                            class="hover:bg-blue-50/40 transition"
                        >

                            <!-- CODE -->
                            <td class="px-3 py-2 font-semibold text-gray-800">
                                {{ s.section_code }}
                            </td>

                            <!-- NAME -->
                            <td class="px-3 py-2 text-gray-600">
                                {{ s.name }}
                            </td>

                            <!-- INPUT -->
                            <td class="px-3 py-2">
                                <input 
                                    v-model="s.max_deduction" 
                                    class="input text-left"
                                    placeholder="Unlimited"
                                />
                            </td>

                            <!-- CHECKBOX -->
                            <td class="px-3 py-2 text-center">
                                <input 
                                    type="checkbox" 
                                    v-model="s.is_active"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>
        </div>

        <!-- ACTION -->
        <div class="flex justify-end mt-6">
            <button 
                @click="saveLimits" 
                class="px-6 h-11 rounded-xl bg-blue-600 text-white font-bold shadow-sm hover:bg-blue-700 transition"
            >
                Save
            </button>
        </div>

    </div>
</div>

                <!-- TAB D -->
         <div v-if="activeTab === 'hra'">

    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">

        <!-- TITLE -->
        <h3 class="font-black text-gray-900 mb-4">HRA Rules</h3>

        <!-- TEXTAREA -->
        <textarea 
            v-model="settingsForm.metro_cities"
            class="w-full h-24 px-3 py-2 border border-gray-200 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
            placeholder="Enter metro cities (e.g. Mumbai, Delhi, Bangalore)"
        ></textarea>

        <!-- INPUT -->
        <div class="mt-3">
            <input 
                v-model="settingsForm.rent_receipt_limit"
                class="w-full px-3 py-2 border border-gray-200 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Rent Receipt Limit"
            />
        </div>

        <!-- BUTTON -->
        <div class="flex justify-end mt-4">
            <button 
                @click="saveSettings" 
                class="px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition"
            >
                Save
            </button>
        </div>

    </div>

</div>

            </div>
        </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useToastStore } from '@/stores/toast';

defineOptions({ layout: MainLayout });

const props = defineProps(['settings', 'regimes', 'sections']);
const toast = useToastStore();

const activeTab = ref('scheduler');
const slabRegime = ref('New');

const tabs = [
    { id: 'scheduler', name: 'Scheduler (Year & Dates)' },
    { id: 'slabs', name: 'Tax Slabs' },
    { id: 'limits', name: 'Section Limits' },
    { id: 'hra', name: 'HRA & Rules' },
];

// Settings Form
const settingsForm = useForm({
    financial_year: props.settings.financial_year || '2025-2026',
    declaration_window_start: props.settings.declaration_window_start,
    declaration_window_end: props.settings.declaration_window_end,
    proof_window_start: props.settings.proof_window_start,
    proof_window_end: props.settings.proof_window_end,
    default_regime: props.settings.default_regime || 'New',
    metro_cities: props.settings.metro_cities || 'Mumbai, Delhi, Kolkata, Chennai, Bangalore',
    rent_receipt_limit: props.settings.rent_receipt_limit || 8333
});

// Slabs Logic
// We clone standard props to local state for editing
const localSlabs = reactive({
    'Old': props.regimes.find(r => r.name === 'Old')?.slabs || [],
    'New': props.regimes.find(r => r.name === 'New')?.slabs || []
});

const activeSlabs = computed(() => localSlabs[slabRegime.value]);

const addSlab = () => {
    activeSlabs.value.push({ min_income: 0, max_income: null, tax_rate_percentage: 0 });
};

const removeSlab = (idx) => {
    activeSlabs.value.splice(idx, 1);
};

// Section Limits Logic
const sectionsLocal = ref([...props.sections]);

// Actions
const saveSettings = () => {
    settingsForm.post(route('hr.tax.configuration.settings'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Settings Saved')
    });
};

const saveSlabs = () => {
    const regimeId = props.regimes.find(r => r.name === slabRegime.value)?.id;
    if (!regimeId) return;

    useForm({
        regime_id: regimeId,
        slabs: activeSlabs.value
    }).post(route('hr.tax.configuration.slabs'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Slabs Saved')
    });
};

const saveLimits = () => {
    useForm({
        sections: sectionsLocal.value
    }).post(route('hr.tax.configuration.limits'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Limits Saved')
    });
};

</script>
