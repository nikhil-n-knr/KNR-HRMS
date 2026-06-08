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
    <div class="space-y-6">

        <!-- Header -->
        <div class="flex justify-between items-center px-4">
            <div>
                <h3 class="text-base font-black text-gray-900">Loan Policy Configuration</h3>
                <p class="text-xs text-gray-500">Manage loan rules, eligibility, and interest structures.</p>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-600 font-medium">Employee Applications</span>

                <!-- Toggle -->
                <button 
                    @click="toggleLoanAccess"
                    :class="globalSettings.employee_loans_enabled ? 'bg-indigo-600' : 'bg-gray-300'"
                    class="relative inline-flex h-5 w-10 rounded-full transition"
                >
                    <span 
                        :class="globalSettings.employee_loans_enabled ? 'translate-x-5' : 'translate-x-0'"
                        class="inline-block h-5 w-5 transform bg-white rounded-full shadow transition"
                    />
                </button>
            </div>
        </div>

        <!-- Cards -->
        <div v-if="loading" class="p-8 text-center text-gray-500 text-sm">
            Loading Policies...
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 px-4">

            <!-- Policy Card -->
            <div 
                v-for="product in products" 
                :key="product.id" 
                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm hover:shadow-md transition"
            >
                <div class="flex justify-between items-start">
                    <h4 class="text-sm font-black text-gray-900">{{ product.name }}</h4>

                    <span 
                        :class="product.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'"
                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                    >
                        {{ product.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <!-- Details -->
                <div class="mt-4 space-y-2 text-xs text-gray-600">
                    <div class="flex justify-between">
                        <span>Max Amount</span>
                        <span class="font-bold text-gray-900">
                            ₹{{ product.max_amount_limit.toLocaleString() }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span>Interest</span>
                        <span class="font-bold text-gray-900">
                            {{ product.interest_rate }}% ({{ product.interest_type }})
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span>Tenure</span>
                        <span class="font-bold text-gray-900">
                            {{ product.max_tenure_months }} Months
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span>Multiplier</span>
                        <span class="font-bold text-indigo-600">
                            {{ product.eligibility_multiplier }}x
                        </span>
                    </div>
                </div>

                <!-- Action -->
                <div class="mt-4 pt-3 border-t text-right">
                    <button 
                        @click="editProduct(product)" 
                        class="text-xs font-bold text-indigo-600 hover:underline"
                    >
                        Edit Policy
                    </button>
                </div>
            </div>

            <!-- Add Card -->
            <div 
                @click="createProduct" 
                class="rounded-xl border-2 border-dashed border-gray-200 p-5 flex flex-col items-center justify-center text-gray-400 hover:border-indigo-300 hover:text-indigo-500 cursor-pointer transition min-h-[160px]"
            >
                <span class="text-3xl">+</span>
                <span class="text-xs font-semibold mt-2">Create New Policy</span>
            </div>

        </div>

        <!-- MODAL -->
        <Modal :show="showModal" @close="showModal = false">
            <div class="rounded-xl overflow-hidden">

                <!-- Modal Header -->
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="text-base font-black text-gray-900">
                        {{ editingProduct ? 'Edit Policy' : 'Create Policy' }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Configure loan rules and eligibility settings
                    </p>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-5">

                    <!-- Name -->
                    <div>
                        <InputLabel value="Policy Name" />
                        <TextInput 
                            type="text" 
                            v-model="form.name" 
                            class="w-full mt-1 text-sm" 
                            placeholder="e.g. Personal Loan" 
                        />
                    </div>

                    <!-- Row -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Max Amount (INR)" />
                            <TextInput type="number" v-model="form.max_amount_limit" class="w-full mt-1 text-sm" />
                        </div>

                        <div>
                            <InputLabel value="Interest Rate (%)" />
                            <TextInput type="number" step="0.1" v-model="form.interest_rate" class="w-full mt-1 text-sm" />
                        </div>
                    </div>

                    <!-- Row -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Interest Type" />
                            <select 
                                v-model="form.interest_type"
                                class="w-full mt-1 text-sm border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="Flat">Flat Rate</option>
                                <option value="Reducing">Reducing Balance</option>
                            </select>
                        </div>

                        <div>
                            <InputLabel value="Max Tenure (Months)" />
                            <TextInput type="number" v-model="form.max_tenure_months" class="w-full mt-1 text-sm" />
                        </div>
                    </div>

                    <!-- Multiplier -->
                    <div>
                        <InputLabel value="Salary Multiplier (e.g. 6x)" />
                        <TextInput type="number" step="0.5" v-model="form.eligibility_multiplier" class="w-full mt-1 text-sm" />
                        <p class="text-[11px] text-gray-400 mt-1">
                            Max eligibility = Monthly Salary × Multiplier
                        </p>
                    </div>

                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-3">
                    <SecondaryButton @click="showModal = false" class="text-xs">
                        Cancel
                    </SecondaryButton>

                    <PrimaryButton @click="saveProduct" class="text-xs">
                        Save Changes
                    </PrimaryButton>
                </div>

            </div>
        </Modal>

    </div>
</template>