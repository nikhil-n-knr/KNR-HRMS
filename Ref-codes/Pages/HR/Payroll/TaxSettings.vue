<script setup>
import { ref } from 'vue';
import { useForm, router, Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import { 
    Cog6ToothIcon, 
    PlusIcon, 
    PencilSquareIcon, 
    TrashIcon,
    Bars3CenterLeftIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    regimes: Array
});

const regimeForm = useForm({
    name: '',
    is_default: false
});

const slabForm = useForm({
    regime_id: null,
    min_income: 0,
    max_income: null,
    tax_rate_percentage: 0
});

const selectedRegime = ref(props.regimes.length > 0 ? props.regimes[0] : null);
const showRegimeModal = ref(false);
const showSlabModal = ref(false);
const editingSlab = ref(null);

const selectRegime = (regime) => {
    selectedRegime.value = regime;
};

const createRegime = () => {
    regimeForm.post(route('hr.payroll.tax-regimes.store'), {
        onSuccess: () => {
            showRegimeModal.value = false;
            regimeForm.reset();
            if (props.regimes.length) selectedRegime.value = props.regimes[props.regimes.length - 1];
        }
    });
};

const openSlabModal = (slab = null) => {
    editingSlab.value = slab;
    if (slab) {
        slabForm.regime_id = slab.regime_id;
        slabForm.min_income = slab.min_income;
        slabForm.max_income = slab.max_income;
        slabForm.tax_rate_percentage = slab.tax_rate_percentage;
    } else {
        slabForm.reset();
        slabForm.regime_id = selectedRegime.value.id;
    }
    showSlabModal.value = true;
};

const submitSlab = () => {
    if (editingSlab.value) {
        slabForm.put(route('hr.payroll.tax-slabs.update', editingSlab.value.id), {
            onSuccess: () => showSlabModal.value = false
        });
    } else {
        slabForm.post(route('hr.payroll.tax-slabs.store'), {
            onSuccess: () => showSlabModal.value = false
        });
    }
};

const deleteSlab = (slab) => {
    if(confirm('Are you sure you want to delete this tax slab?')) {
        router.delete(route('hr.payroll.tax-slabs.destroy', slab.id));
    }
}
</script>

<template>
    <Head title="Tax Engine Settings" />
    <MainLayout>
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Glass Header -->
            <div class="mb-8 flex justify-between items-end">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <Cog6ToothIcon class="h-5 w-5 text-indigo-500" />
                        <span class="text-sm font-bold text-indigo-500 uppercase tracking-widest">System Configuration</span>
                    </div>
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight">Tax Engine Settings</h1>
                    <p class="text-gray-500 font-medium">Configure statutory tax regimes and bracket thresholds.</p>
                </div>
                <div class="flex space-x-3">
                    <button 
                        @click="showRegimeModal = true"
                        class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 rounded-2xl text-xs font-bold text-gray-700 shadow-sm hover:shadow-md hover:bg-gray-50 transition-all duration-200"
                    >
                        <PlusIcon class="h-4 w-4" />
                        New Regime
                    </button>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Sidebar: Regimes Card -->
                <div class="w-full lg:w-1/3">
                    <div class="bg-white/80 backdrop-blur-xl border border-white/20 shadow-2xl rounded-3xl overflow-hidden sticky top-8">
                        <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-xs font-black text-gray-600 uppercase tracking-widest flex items-center gap-2">
                                <Bars3CenterLeftIcon class="h-4 w-4" />
                                Tax Regimes
                            </h3>
                        </div>
                        
                        <div class="p-4 space-y-2">
                            <button 
                                v-for="regime in regimes" 
                                :key="regime.id"
                                @click="selectRegime(regime)"
                                class="w-full text-left p-4 rounded-2xl transition-all duration-300 group overflow-hidden relative"
                                :class="selectedRegime?.id === regime.id 
                                    ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-200 scale-[1.02]' 
                                    : 'hover:bg-indigo-50 text-gray-700 hover:text-indigo-600'"
                            >
                                <div class="flex justify-between items-center relative z-10">
                                    <span class="font-bold text-sm tracking-tight">{{ regime.name }}</span>
                                    <span v-if="regime.is_default" 
                                        :class="selectedRegime?.id === regime.id ? 'bg-indigo-400 text-white' : 'bg-green-100 text-green-700'"
                                        class="text-sm font-black uppercase px-2 py-0.5 rounded-full"
                                    >
                                        Default
                                    </span>
                                </div>
                                <!-- Background Glow for active -->
                                <div v-if="selectedRegime?.id === regime.id" class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-violet-600 opacity-100"></div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Main: Slabs Content -->
                <div class="w-full lg:w-2/3">
                    <div v-if="selectedRegime" class="bg-white/80 backdrop-blur-xl border border-white/20 shadow-2xl rounded-3xl overflow-hidden transition-all duration-300">
                        <div class="px-8 py-6 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-black text-gray-900 tracking-tight">{{ selectedRegime.name }}</h3>
                                <p class="text-xs text-gray-500 font-medium">Income tax calculation brackets for AY 2026-27</p>
                            </div>
                            <button 
                                @click="openSlabModal()"
                                class="px-5 py-2.5 bg-indigo-600 rounded-2xl text-xs font-bold text-white shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95"
                            >
                                Add Slab
                            </button>
                        </div>

                        <div class="p-8">
                            <div class="overflow-hidden rounded-2xl border border-gray-100">
                                <table class="min-w-full divide-y divide-gray-100">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-sm font-black text-gray-400 uppercase tracking-widest">Taxable Range</th>
                                            <th class="px-6 py-4 text-left text-sm font-black text-gray-400 uppercase tracking-widest">Rate (%)</th>
                                            <th class="px-6 py-4 text-right text-sm font-black text-gray-400 uppercase tracking-widest">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-50">
                                        <tr v-for="slab in selectedRegime.slabs" :key="slab.id" class="hover:bg-indigo-50/30 transition-colors group">
                                            <td class="px-6 py-5 whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex flex-col">
                                                        <span class="text-sm font-bold text-gray-800 tracking-tight">
                                                            ₹{{ Number(slab.min_income).toLocaleString() }} 
                                                            <span v-if="slab.max_income" class="text-gray-400 font-medium mx-1">to</span>
                                                            <span v-if="slab.max_income">₹{{ Number(slab.max_income).toLocaleString() }}</span>
                                                            <span v-else class="text-indigo-600">and above</span>
                                                        </span>
                                                        <span class="text-sm text-gray-400 font-black uppercase">Net Taxable Income</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-5 whitespace-nowrap">
                                                <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full font-black text-xs">
                                                    {{ slab.tax_rate_percentage }}%
                                                </span>
                                            </td>
                                            <td class="px-6 py-5 whitespace-nowrap text-right space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button @click="openSlabModal(slab)" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-white rounded-lg transition-all shadow-sm">
                                                    <PencilSquareIcon class="h-4 w-4" />
                                                </button>
                                                <button @click="deleteSlab(slab)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-white rounded-lg transition-all shadow-sm">
                                                    <TrashIcon class="h-4 w-4" />
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="!selectedRegime.slabs || selectedRegime.slabs.length === 0">
                                            <td colspan="3" class="px-6 py-12 text-center text-gray-300 font-medium italic">
                                                No tax slabs defined for this regime.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div v-else class="h-[400px] bg-white/40 backdrop-blur-md rounded-3xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-center p-10">
                        <Bars3CenterLeftIcon class="h-12 w-12 text-gray-300 mb-4" />
                        <h4 class="text-gray-900 font-bold">No Regime Selected</h4>
                        <p class="text-gray-500 text-sm max-w-xs mx-auto">Please select a tax regime from the sidebar to view and configure its tax slabs.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Regime Modal -->
        <Modal :show="showRegimeModal" @close="showRegimeModal = false">
            <div class="p-8 bg-white rounded-3xl">
                <h2 class="text-2xl font-black text-gray-900 tracking-tight mb-6">Create Tax Regime</h2>
                
                <div class="space-y-6">
                    <div>
                        <InputLabel for="name" value="Regime Identifier Name" class="text-sm font-black uppercase text-gray-400 tracking-widest mb-2" />
                        <TextInput id="name" v-model="regimeForm.name" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-indigo-500" placeholder="e.g. Finance Act 2026 (New)" />
                    </div>
                    
                    <div class="flex items-center gap-3 p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100/50">
                        <div class="bg-white p-2 rounded-xl shadow-sm">
                            <input type="checkbox" id="default" v-model="regimeForm.is_default" class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="default" class="text-sm font-bold text-indigo-900">Set as System Default</label>
                            <p class="text-sm text-indigo-700/60 font-medium">New employees will be automatically assigned this regime.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8">
                    <button @click="showRegimeModal = false" class="px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors">Cancel</button>
                    <PrimaryButton @click="createRegime" :disabled="regimeForm.processing" class="!rounded-2xl !px-8 shadow-xl shadow-indigo-100">Create Regime</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Slab Modal -->
        <Modal :show="showSlabModal" @close="showSlabModal = false">
            <div class="p-8 bg-white rounded-3xl">
                <h2 class="text-2xl font-black text-gray-900 tracking-tight mb-6">{{ editingSlab ? 'Edit Tax Slab' : 'Add New Bracket' }}</h2>
                
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <InputLabel value="Minimum Annual Income (₹)" class="text-sm font-black uppercase text-gray-400 tracking-widest mb-2" />
                        <TextInput type="number" v-model="slabForm.min_income" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-indigo-500" />
                    </div>
                    <div>
                        <InputLabel value="Maximum Annual Income" class="text-sm font-black uppercase text-gray-400 tracking-widest mb-2" />
                        <TextInput type="number" v-model="slabForm.max_income" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-indigo-500" placeholder="Leave empty for No Limit" />
                    </div>
                </div>

                <div class="mb-6">
                    <InputLabel value="Tax Rate (Percentage %)" class="text-sm font-black uppercase text-gray-400 tracking-widest mb-2" />
                    <div class="relative">
                        <TextInput type="number" step="0.01" v-model="slabForm.tax_rate_percentage" class="w-full bg-gray-50 border-gray-100 rounded-2xl focus:ring-indigo-500 pr-10" />
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">%</span>
                    </div>
                </div>

                <p class="text-sm text-gray-400 font-medium mb-8">
                    <span class="font-black text-indigo-500 uppercase">Note:</span> High-earner surcharges and health & education cess are calculated separately during payroll run.
                </p>

                <div class="flex justify-end gap-3">
                    <button @click="showSlabModal = false" class="px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors">Cancel</button>
                    <PrimaryButton @click="submitSlab" :disabled="slabForm.processing" class="!rounded-2xl !px-8 shadow-xl shadow-indigo-100">Save Bracket</PrimaryButton>
                </div>
            </div>
        </Modal>
    </MainLayout>
</template>
