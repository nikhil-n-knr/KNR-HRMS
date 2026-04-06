<template>
    <Head title="Tax Declarations" />
    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                IT Declarations (FY {{ fiscal_year }})
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Regime Selection -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Tax Regime Selection</h3>
                            <p class="text-sm text-gray-500">Choose between Old (with exemptions) and New (lower rates, no exemptions) regime.</p>
                        </div>
                        <div class="flex gap-4 items-center">
                             <a :href="route('hr.tax.computation.download')" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold underline flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Download Tax Sheet
                            </a>
                            <div class="h-6 w-px bg-gray-300 mx-2"></div>
                            <span :class="{'font-bold text-green-600': regimeForm.regime === 'New'}" class="text-sm">New Regime</span>
                             <button @click="toggleRegime" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2" :class="regimeForm.regime === 'Old' ? 'bg-indigo-600' : 'bg-gray-200'" role="switch" aria-checked="false">
                                <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" :class="regimeForm.regime === 'Old' ? 'translate-x-5' : 'translate-x-0'"></span>
                            </button>
                            <span :class="{'font-bold text-indigo-600': regimeForm.regime === 'Old'}" class="text-sm">Old Regime</span>
                        </div>
                    </div>
                </div>

                <!-- Declarations Form -->
                <div v-if="regimeForm.regime === 'Old'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Investment Declarations (Old Regime Only)</h3>
                        <p class="text-sm text-gray-500 text-right">Max Deductions apply per section.<br><span class="text-xs text-red-500">* Upload Proofs for Verification</span></p>
                    </div>

                    <form @submit.prevent="submitDeclarations">
                        <div class="space-y-6">
                            <div v-for="(input, index) in declForm.declarations" :key="input.tax_section_id" class="border-b pb-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end mb-2">
                                    <div>
                                        <InputLabel :value="getSectionName(input.tax_section_id)" />
                                        <p class="text-xs text-gray-500">{{ getSectionLimit(input.tax_section_id) }}</p>
                                    </div>
                                    <div class="col-span-1">
                                        <div class="flex items-center gap-2">
                                             <TextInput type="number" v-model="input.declared_amount" class="w-full" placeholder="Amount (INR)" />
                                             <span v-if="input.status === 'Verified'" class="text-green-600 text-xs font-bold whitespace-nowrap">Verified: {{ formatCurrency(input.verified_amount) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-span-1 text-right">
                                         <!-- If saved, show proof options -->
                                         <div v-if="input.id">
                                            <button type="button" @click="openProofModal(input)" class="text-sm text-indigo-600 hover:text-indigo-800 underline">
                                                Upload Proofs ({{ input.proofs ? input.proofs.length : 0 }})
                                            </button>
                                         </div>
                                         <div v-else class="text-xs text-gray-400">Save first to upload proofs</div>
                                    </div>
                                </div>
                                <!-- Proof List -->
                                <div v-if="input.proofs && input.proofs.length > 0" class="mt-1 pl-4 border-l-2 border-indigo-100">
                                     <ul class="text-xs space-y-1">
                                        <li v-for="proof in input.proofs" :key="proof.id" class="flex justify-between items-center">
                                            <span class="text-gray-600 truncate max-w-xs">{{ proof.original_name }}</span>
                                            <div class="flex gap-2">
                                                <span :class="{'text-yellow-600': proof.status === 'Pending', 'text-green-600': proof.status === 'Approved', 'text-red-500': proof.status === 'Rejected'}">
                                                    {{ proof.status }}
                                                </span>
                                                <button type="button" @click="deleteProof(proof)" class="text-red-400 hover:text-red-600">x</button>
                                            </div>
                                        </li>
                                     </ul>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <PrimaryButton :disabled="declForm.processing">Save Declarations</PrimaryButton>
                        </div>
                    </form>
                </div>
                
                <div v-else class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center text-gray-500">
                    <p class="text-lg font-medium">No Declarations Required</p>
                    <p class="text-sm">The New Tax Regime does not allow for Chapter VI-A deductions (80C, 80D, HRA etc).</p>
                </div>

                <!-- Proof Modal -->
                <Modal :show="showProofModal" @close="showProofModal = false">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Upload Proof for {{ activeSectionName }}</h2>
                        <form @submit.prevent="submitProof">
                            <div class="space-y-4">
                                <div>
                                    <InputLabel value="Document (PDF/Image)" />
                                    <input type="file" @change="e => proofForm.file = e.target.files[0]" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50" required />
                                    <InputError :message="proofForm.errors.file" />
                                </div>
                                <div>
                                    <InputLabel value="Description (Optional)" />
                                    <TextInput v-model="proofForm.description" class="w-full mt-1" />
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end gap-3">
                                <SecondaryButton @click="showProofModal = false">Cancel</SecondaryButton>
                                <PrimaryButton :disabled="proofForm.processing">Upload</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </Modal>

            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    fiscal_year: String,
    sections: Array,
    declarations: Array, // Existing with proofs
    regime: Object
});

const regimeForm = useForm({
    regime: props.regime?.regime || 'New',
    fiscal_year: props.fiscal_year
});

// Prepare Declarations Form
const prepareDeclData = () => {
    return props.sections.map(section => {
        const existing = props.declarations.find(d => d.tax_section_id === section.id);
        return {
            id: existing ? existing.id : null, 
            tax_section_id: section.id,
            declared_amount: existing ? existing.declared_amount : 0,
            verified_amount: existing ? existing.verified_amount : 0,
            status: existing ? existing.status : null,
            proofs: existing ? existing.proofs : []
        };
    });
};

const declForm = useForm({
    fiscal_year: props.fiscal_year,
    declarations: prepareDeclData()
});

const toggleRegime = () => {
    regimeForm.regime = regimeForm.regime === 'New' ? 'Old' : 'New';
    regimeForm.post(route('hr.tax.regime.update'), { preserveScroll: true });
};

const submitDeclarations = () => {
    declForm.post(route('hr.tax.declarations.store'), {
        preserveScroll: true,
        onSuccess: () => {
             // Refresh local data from props? 
             // Inertia handles prop updates, avoiding manual mapping unless using separate state
             // But declForm.declarations is initialized once. We should watch props or reload page.
             // Rely on preserving scroll and page reload for now.
             window.location.reload(); 
        }
    });
};

// Modal Logic
const showProofModal = ref(false);
const activeSection = ref(null);
const activeSectionName = ref('');
const proofForm = useForm({
    tax_declaration_id: '',
    file: null,
    description: ''
});

const openProofModal = (item) => {
    activeSection.value = item;
    activeSectionName.value = getSectionName(item.tax_section_id);
    proofForm.tax_declaration_id = item.id;
    proofForm.reset('file', 'description');
    showProofModal.value = true;
};

const submitProof = () => {
    proofForm.post(route('hr.tax.proofs.store'), {
        onSuccess: () => {
            showProofModal.value = false;
            window.location.reload();
        }
    });
};

const deleteProof = (proof) => {
    if(confirm('Delete this proof?')) {
        router.delete(route('hr.tax.proofs.destroy', proof.id));
    }
};

const getSectionName = (id) => {
    const s = props.sections.find(x => x.id === id);
    return s ? `${s.section_code} - ${s.name}` : 'Unknown';
};

const getSectionLimit = (id) => {
    const s = props.sections.find(x => x.id === id);
    if (!s) return '';
    return s.max_deduction ? `Max: ₹${new Intl.NumberFormat('en-IN').format(s.max_deduction)}` : 'No Limit specified';
};

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val || 0);
</script>
