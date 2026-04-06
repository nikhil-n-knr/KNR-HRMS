<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const products = ref([]);
const loading = ref(true);
const showModal = ref(false);
const editingProduct = ref(null);

const form = ref({
    name: '',
    max_amount_limit: 0,
    interest_rate: 0,
    interest_type: 'Flat',
    max_tenure_months: 12,
    eligibility_multiplier: 1
});

const fetchProducts = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('hr.loans.api.products'));
        products.value = res.data;
    } finally {
        loading.value = false;
    }
};

const globalSettings = ref({
    employee_loans_enabled: true
});

const fetchSettings = async () => {
    try {
        const res = await axios.get(route('admin.loan-products.settings'));
        globalSettings.value = res.data;
    } catch (e) {
        console.error("Failed to fetch settings");
    }
};

const toggleLoanAccess = async () => {
    const newState = !globalSettings.value.employee_loans_enabled;
    try {
        await axios.post(route('admin.loan-products.settings.update'), {
            employee_loans_enabled: newState
        });
        globalSettings.value.employee_loans_enabled = newState;
    } catch (e) {
        console.error("Failed to update settings");
    }
};

onMounted(() => {
    fetchProducts();
    fetchSettings();
});

const editProduct = (product) => {
    editingProduct.value = product;
    form.value = { ...product };
    showModal.value = true;
};

const createProduct = () => {
    editingProduct.value = null; // Create Mode
    form.value = {
        name: '',
        max_amount_limit: 100000,
        interest_rate: 10,
        interest_type: 'Flat',
        max_tenure_months: 12,
        eligibility_multiplier: 1,
        is_active: true
    };
    showModal.value = true;
};

const saveProduct = () => {
    if (editingProduct.value) {
        // Update
        router.put(route('admin.loan-products.update', editingProduct.value.id), form.value, {
            onSuccess: () => {
                showModal.value = false;
                fetchProducts();
            }
        });
    } else {
        // Create
        router.post(route('admin.loan-products.store'), form.value, {
            onSuccess: () => {
                showModal.value = false;
                fetchProducts();
            }
        });
    }
};
</script>

<template>
    <div>
        <div class="flex justify-between items-center mb-4 px-4">
            <h3 class="font-bold text-gray-700">Loan Policy Configuration</h3>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-600 font-medium">Employee Applications:</span>
                <button 
                    @click="toggleLoanAccess"
                    :class="globalSettings.employee_loans_enabled ? 'bg-indigo-600' : 'bg-gray-200'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    <span 
                        :class="globalSettings.employee_loans_enabled ? 'translate-x-5' : 'translate-x-0'"
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                    />
                </button>
            </div>
        </div>
        
        <div v-if="loading" class="p-8 text-center text-gray-500">Loading Policies...</div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 px-4">
            <div v-for="product in products" :key="product.id" class="bg-white border rounded-lg p-5 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-bold text-lg">{{ product.name }}</h4>
                    <span :class="product.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" class="px-2 py-0.5 rounded text-xs font-bold">
                        {{ product.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                
                <div class="text-sm space-y-2 mt-4 text-gray-600">
                    <div class="flex justify-between">
                        <span>Max Amount:</span>
                        <span class="font-medium">INR {{ product.max_amount_limit.toLocaleString() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Interest:</span>
                        <span class="font-medium">{{ product.interest_rate }}% ({{ product.interest_type }})</span>
                    </div>
                     <div class="flex justify-between">
                        <span>Max Tenure:</span>
                        <span class="font-medium">{{ product.max_tenure_months }} Months</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Salary Multiplier:</span>
                        <span class="font-medium text-indigo-600 font-bold">{{ product.eligibility_multiplier }}x</span>
                    </div>
                </div>
                
                <div class="mt-4 pt-4 border-t">
                    <button @click="editProduct(product)" class="w-full text-center text-indigo-600 text-sm font-medium hover:underline">Edit Rules</button>
                </div>
            </div>
            
            <!-- Add New Card -->
            <div @click="createProduct" class="border-2 border-dashed border-gray-200 rounded-lg p-5 flex flex-col items-center justify-center text-gray-400 cursor-pointer hover:border-indigo-300 hover:text-indigo-500 transition-colors h-full min-h-[200px]">
                <span class="text-4xl mb-2">+</span>
                <span class="text-sm font-medium">Create New Policy</span>
            </div>
        </div>

        <Modal :show="showModal" @close="showModal = false">
             <div class="p-6">
                 <h3 class="text-lg font-bold mb-4">{{ editingProduct ? 'Edit ' + editingProduct.name : 'Create New Policy' }}</h3>
                 <div class="space-y-4">
                     <div>
                         <InputLabel value="Policy Name" />
                         <TextInput type="text" v-model="form.name" class="w-full mt-1" placeholder="e.g. Personal Loan" />
                     </div>
                     <div class="grid grid-cols-2 gap-4">
                         <div>
                             <InputLabel value="Max Amount (INR)" />
                             <TextInput type="number" v-model="form.max_amount_limit" class="w-full mt-1" />
                         </div>
                         <div>
                             <InputLabel value="Interest Rate (%)" />
                             <TextInput type="number" step="0.1" v-model="form.interest_rate" class="w-full mt-1" />
                         </div>
                     </div>
                     <div class="grid grid-cols-2 gap-4">
                          <div>
                             <InputLabel value="Interest Type" />
                             <select v-model="form.interest_type" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1">
                                 <option value="Flat">Flat Rate</option>
                                 <option value="Reducing">Reducing Balance</option>
                             </select>
                         </div>
                         <div>
                             <InputLabel value="Max Tenure (Months)" />
                             <TextInput type="number" v-model="form.max_tenure_months" class="w-full mt-1" />
                         </div>
                     </div>
                      <div>
                         <InputLabel value="Salary Multiplier (e.g. 6x)" />
                         <TextInput type="number" step="0.5" v-model="form.eligibility_multiplier" class="w-full mt-1" />
                         <p class="text-xs text-gray-400 mt-1">Max eligibility = Monthly Gross Salary x Multiplier</p>
                     </div>
                 </div>
                 <div class="mt-6 flex justify-end gap-3">
                     <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                     <PrimaryButton @click="saveProduct">Save Changes</PrimaryButton>
                 </div>
             </div>
        </Modal>
    </div>
</template>
