<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    products: Array
});

const showModal = ref(false);
const editingProduct = ref(null);

const form = useForm({
    name: '',
    description: '',
    interest_type: 'Flat',
    max_amount_limit: 100000,
    max_tenure_months: 24,
    eligibility_multiplier: 1.0,
    is_active: true,
    rules: [] // Array of { min_amount, max_amount, min_tenure, max_tenure, interest_rate }
});

const openCreateModal = () => {
    editingProduct.value = null;
    form.reset();
    form.rules = []; // Start with no rules
    showModal.value = true;
};

const openEditModal = (product) => {
    editingProduct.value = product;
    form.name = product.name;
    form.description = product.description;
    form.interest_type = product.interest_type;
    form.max_amount_limit = product.max_amount_limit;
    form.max_tenure_months = product.max_tenure_months;
    form.eligibility_multiplier = product.eligibility_multiplier;
    form.is_active = Boolean(product.is_active);
    
    // Map existing rules
    form.rules = product.interest_rules.map(r => ({
        min_amount: r.min_amount,
        max_amount: r.max_amount,
        min_tenure_months: r.min_tenure_months,
        max_tenure_months: r.max_tenure_months,
        interest_rate: r.interest_rate
    }));
    
    showModal.value = true;
};

const addRule = () => {
    form.rules.push({
        min_amount: 0,
        max_amount: null,
        min_tenure_months: 0,
        max_tenure_months: null,
        interest_rate: 0
    });
};

const removeRule = (index) => {
    form.rules.splice(index, 1);
};

const submit = () => {
    if (editingProduct.value) {
        form.put(route('loan-products.update', editingProduct.value.id), {
            onSuccess: () => showModal.value = false
        });
    } else {
        form.post(route('loan-products.store'), {
            onSuccess: () => showModal.value = false
        });
    }
};

const deleteProduct = (product) => {
    if (confirm('Delete this loan product?')) {
        router.delete(route('loan-products.destroy', product.id));
    }
};
</script>

<template>
    <MainLayout title="Loan Products">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Loan Configuration
                </h2>
                <PrimaryButton @click="openCreateModal">
                    New Loan Product
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Product List -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="product in products" :key="product.id" class="bg-white p-6 rounded-lg shadow border border-gray-100 relative group">
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                             <button @click="openEditModal(product)" class="text-blue-600 hover:text-blue-800 mr-2">Edit</button>
                             <button @click="deleteProduct(product)" class="text-red-500 hover:text-red-700">Delete</button>
                        </div>
                        
                        <h3 class="text-lg font-bold text-gray-900">{{ product.name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ product.description || 'No description' }}</p>
                        
                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Max Limit:</span>
                                <span class="font-medium">₹{{ product.max_amount_limit }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Interest Type:</span>
                                <span class="font-medium">{{ product.interest_type }}</span>
                            </div>
                             <div class="flex justify-between">
                                <span class="text-gray-500">Eligibility:</span>
                                <span class="font-medium">{{ product.eligibility_multiplier }}x CTC</span>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Interest Rules ({{ product.interest_rules.length }})</p>
                            <div class="space-y-1">
                                <template v-for="(rule, i) in product.interest_rules.slice(0, 2)" :key="rule.id">
                                    <div class="text-xs flex justify-between text-gray-600">
                                        <span>> ₹{{ rule.min_amount }} ({{ rule.min_tenure_months }}m+)</span>
                                        <span class="font-bold text-green-600">{{ rule.interest_rate }}%</span>
                                    </div>
                                </template>
                                <div v-if="product.interest_rules.length > 2" class="text-xs text-gray-400 text-center pt-1">
                                    + {{ product.interest_rules.length - 2 }} more rules
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="products.length === 0" class="text-center py-12 text-gray-500 bg-white rounded-lg border border-dashed border-gray-300">
                    No loan products defined yet. Create one to get started.
                </div>

            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showModal" @close="showModal = false" maxWidth="2xl">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ editingProduct ? 'Edit Loan Product' : 'Create New Loan Product' }}
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Basic Info -->
                    <div class="col-span-2">
                         <InputLabel value="Product Name" />
                         <TextInput v-model="form.name" class="w-full mt-1" placeholder="e.g. Employee Personal Loan" />
                         <InputError :message="form.errors.name" class="mt-1" />
                    </div>
                    
                    <div class="col-span-2">
                         <InputLabel value="Description" />
                         <TextInput v-model="form.description" class="w-full mt-1" placeholder="Short description" />
                    </div>
                    
                    <div>
                        <InputLabel value="Interest Type" />
                        <select v-model="form.interest_type" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="Flat">Flat Rate</option>
                            <option value="Reducing">Reducing Balance</option>
                        </select>
                    </div>
                    
                    <div>
                        <InputLabel value="Status" />
                        <select v-model="form.is_active" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option :value="true">Active</option>
                            <option :value="false">Inactive</option>
                        </select>
                    </div>
                     
                    <div>
                         <InputLabel value="Max Amount Limit (₹)" />
                         <TextInput type="number" v-model="form.max_amount_limit" class="w-full mt-1" />
                    </div>
                    
                    <div>
                         <InputLabel value="Max Tenure (Months)" />
                         <TextInput type="number" v-model="form.max_tenure_months" class="w-full mt-1" />
                    </div>
                    
                    <div class="col-span-2">
                         <InputLabel value="Eligibility Multiplier (x Monthly CTC)" />
                         <TextInput type="number" step="0.1" v-model="form.eligibility_multiplier" class="w-full mt-1" />
                         <p class="text-xs text-gray-500 mt-1">E.g. 6.0 means employee can borrow up to 6 months of their CTC.</p>
                    </div>
                </div>
                
                <!-- Rules Builder -->
                <div class="mt-8 border-t border-gray-100 pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-medium text-gray-900">Interest Rate Rules</h4>
                        <button @click="addRule" type="button" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">+ Add Slab</button>
                    </div>
                    
                    <div class="space-y-3">
                        <div v-for="(rule, index) in form.rules" :key="index" class="flex gap-2 items-start bg-gray-50 p-3 rounded-md">
                            <div class="grid grid-cols-5 gap-2 w-full">
                                <div>
                                    <label class="text-xs text-gray-500 block">Min Amount</label>
                                    <input type="number" v-model="rule.min_amount" class="w-full text-sm border-gray-300 rounded bg-white px-2 py-1" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 block">Max Amount</label>
                                    <input type="number" v-model="rule.max_amount" placeholder="Unltd" class="w-full text-sm border-gray-300 rounded bg-white px-2 py-1" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 block">Min Tenure</label>
                                    <input type="number" v-model="rule.min_tenure_months" class="w-full text-sm border-gray-300 rounded bg-white px-2 py-1" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 block">Max Tenure</label>
                                    <input type="number" v-model="rule.max_tenure_months" placeholder="Unltd" class="w-full text-sm border-gray-300 rounded bg-white px-2 py-1" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 block text-green-600 font-bold">Rate (%)</label>
                                    <input type="number" step="0.01" v-model="rule.interest_rate" class="w-full text-sm border-gray-300 rounded bg-white px-2 py-1 font-bold text-green-700" />
                                </div>
                            </div>
                            <button @click="removeRule(index)" class="text-red-400 hover:text-red-600 mt-5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <div v-if="form.rules.length === 0" class="text-sm text-gray-400 italic text-center py-2">
                            No logic defined. Base logic (0%) will apply if no rules match.
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="submit" :disabled="form.processing">
                        {{ editingProduct ? 'Update Product' : 'Create Product' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </MainLayout>
</template>
