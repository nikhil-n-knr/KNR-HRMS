<template>
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-800">Tax Configuration</h1>

        <!-- Tabs -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    :class="[
                        activeTab === tab.id
                            ? 'border-indigo-500 text-indigo-600'
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                    ]"
                >
                    {{ tab.name }}
                </button>
            </nav>
        </div>

        <!-- TAB A: SCHEDULER -->
        <div v-if="activeTab === 'scheduler'" class="space-y-6">
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <h3 class="font-bold text-gray-800 mb-4">Financial Year & Timelines</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Financial Year</label>
                        <select v-model="settingsForm.financial_year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="2025-2026">2025-2026</option>
                            <option value="2026-2027">2026-2027</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Default Regime for New Joinees</label>
                        <select v-model="settingsForm.default_regime" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="New">New Regime</option>
                            <option value="Old">Old Regime</option>
                        </select>
                    </div>

                    <!-- Declaration Window -->
                     <div class="border-t pt-4 md:col-span-2">
                        <h4 class="text-sm font-bold text-gray-900 mb-2">Declaration Window</h4>
                        <div class="grid grid-cols-2 gap-4">
                             <div>
                                <label class="block text-xs text-gray-500">Start Date</label>
                                <input type="date" v-model="settingsForm.declaration_window_start" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500">End Date</label>
                                <input type="date" v-model="settingsForm.declaration_window_end" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Proof Window -->
                    <div class="border-t pt-4 md:col-span-2">
                        <h4 class="text-sm font-bold text-gray-900 mb-2">Proof Submission Window</h4>
                        <div class="grid grid-cols-2 gap-4">
                             <div>
                                <label class="block text-xs text-gray-500">Start Date</label>
                                <input type="date" v-model="settingsForm.proof_window_start" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500">End Date</label>
                                <input type="date" v-model="settingsForm.proof_window_end" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button @click="saveSettings" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Save Timelines</button>
                </div>
            </div>
        </div>

        <!-- TAB B: SLABS -->
        <div v-if="activeTab === 'slabs'" class="space-y-6">
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-800">Tax Slabs Configuration</h3>
                    <div class="bg-gray-100 p-1 rounded-lg flex">
                         <button 
                            @click="slabRegime = 'New'" 
                            :class="[slabRegime === 'New' ? 'bg-white shadow text-indigo-600' : 'text-gray-500', 'px-3 py-1 rounded-md text-sm font-medium transition']"
                        >New Regime</button>
                        <button 
                            @click="slabRegime = 'Old'" 
                            :class="[slabRegime === 'Old' ? 'bg-white shadow text-indigo-600' : 'text-gray-500', 'px-3 py-1 rounded-md text-sm font-medium transition']"
                        >Old Regime</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min Income (₹)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Income (₹)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tax Rate (%)</th>
                                <th class="px-6 py-3 text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(slab, index) in activeSlabs" :key="index">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" v-model="slab.min_income" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 sm:text-sm">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" v-model="slab.max_income" placeholder="Above" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 sm:text-sm">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="relative rounded-md shadow-sm">
                                        <input type="number" v-model="slab.tax_rate_percentage" class="block w-full rounded-md border-gray-300 focus:ring-indigo-500 sm:text-sm pr-12">
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                            <span class="text-gray-500 sm:text-sm">%</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <button @click="removeSlab(index)" class="text-red-600 hover:text-red-900">&times;</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 flex justify-between">
                    <button @click="addSlab" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">+ Add Slab</button>
                    <button @click="saveSlabs" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Save Slabs</button>
                </div>
            </div>
        </div>
        
        <!-- TAB C: LIMITS -->
        <div v-if="activeTab === 'limits'" class="space-y-6">
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <h3 class="font-bold text-gray-800 mb-4">Section Deduction Limits (Chapter VI-A)</h3>
                <p class="text-sm text-gray-500 mb-4">Set the maximum deduction allowed for each section. Leave 'Max Deduction' empty for Unlimited.</p>

                <div class="overflow-hidden border rounded-lg">
                    <div class="max-h-[500px] overflow-y-auto">
                         <table class="min-w-full divide-y divide-gray-200">
                             <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Section</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Max Deduction (₹)</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Active</th>
                                </tr>
                             </thead>
                             <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="section in sectionsLocal" :key="section.id">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ section.section_code }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ section.name }}</td>
                                    <td class="px-6 py-4">
                                        <input type="number" v-model="section.max_deduction" placeholder="Unlimited" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 sm:text-sm">
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                         <input type="checkbox" v-model="section.is_active" :true-value="1" :false-value="0" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                    </td>
                                </tr>
                             </tbody>
                         </table>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button @click="saveLimits" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Save Limits</button>
                </div>
            </div>
        </div>

        <!-- TAB D: HRA -->
        <div v-if="activeTab === 'hra'" class="space-y-6">
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <h3 class="font-bold text-gray-800 mb-4">HRA & Metro City Rules</h3>

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Metro Cities Definition</label>
                        <p class="text-xs text-gray-500 mb-2">Employees in these cities get 50% HRA exemption. Others get 40%.</p>
                        <textarea v-model="settingsForm.metro_cities" rows="4" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Mumbai, Delhi, Kolkata, Chennai, Bangalore..."></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                             <label class="block text-sm font-medium text-gray-700 mb-1">Rent Receipt Audit Limit (Monthly)</label>
                             <div class="relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 sm:text-sm">₹</span>
                                </div>
                                <input type="number" v-model="settingsForm.rent_receipt_limit" class="block w-full rounded-md border-gray-300 pl-7 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                             </div>
                             <p class="text-xs text-gray-500 mt-1">If Rent > Limit, Landlord PAN is mandatory.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button @click="saveSettings" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Save HRA Rules</button>
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
