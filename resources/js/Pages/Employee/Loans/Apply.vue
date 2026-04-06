<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import MainLayout from '@/Layouts/MainLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    products: Array,
    employee: Object
});

const currentStep = ref(1);
const selectedProduct = ref(null);
const simulationResult = ref(null);

const form = useForm({
    loan_product_id: '',
    amount: '',
    tenure: 12, // Default
    reason: '',
    signature_hash: '', // Simulated
    agreed_to_terms: false
});

// Step 1: Select Product
const selectProduct = (product) => {
    selectedProduct.value = product;
    form.loan_product_id = product.id;
    form.amount = product.max_amount_limit < 50000 ? product.max_amount_limit : 50000; // Sensible default
    form.tenure = Math.min(12, product.max_tenure_months);
    currentStep.value = 2;
    runSimulation();
};

// Step 2: Simulation
const isSimulating = ref(false);
const simulationError = ref(null);

const runSimulation = () => {
    if (!form.amount || !form.tenure) return;
    
    isSimulating.value = true;
    simulationError.value = null;

    axios.post(route('employee.loans.simulate'), {
        loan_product_id: form.loan_product_id,
        amount: form.amount,
        tenure: form.tenure
    }).then(res => {
        simulationResult.value = res.data;
        if (!res.data.eligibility.eligible) {
             simulationError.value = res.data.eligibility.reason;
        }
    }).catch(err => {
        simulationResult.value = null;
        simulationError.value = err.response?.data?.message || 'Simulation Failed';
    }).finally(() => {
        isSimulating.value = false;
    });
};

// Debounce simulation on input
let timeout;
watch(() => [form.amount, form.tenure], () => {
    if (timeout) clearTimeout(timeout);
    timeout = setTimeout(runSimulation, 500);
});

// Step 3: E-Sign
const signatureInput = ref('');
const signCanvas = ref(null); // Placeholder for actual canvas if implemented? Keeping it simple text matching for now or simple click-to-sign.

const signDocument = () => {
    if (signatureInput.value.trim().toLowerCase() === props.employee.first_name.toLowerCase()) {
         // Create a simple hash simulation
         form.signature_hash = btoa('SIGNED_' + new Date().toISOString() + '_' + props.employee.id);
         submitApplication();
    } else {
         alert('Signature must match your First Name exactly to verify identity.');
    }
};

const submitApplication = () => {
    form.post(route('employee.loans.store'), {
        onSuccess: () => {
            // Redirected automatically
        }
    });
};
</script>

<template>
    <MainLayout title="Apply for Loan">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Progress Bar -->
                <div class="mb-8 mx-auto max-w-3xl">
                    <div class="flex items-center justify-between relative">
                        <div class="w-full absolute top-1/2 transform -translate-y-1/2 left-0 h-1 bg-gray-200 -z-10"></div>
                        <div class="w-full absolute top-1/2 transform -translate-y-1/2 left-0 h-1 bg-indigo-500 transition-all duration-300 -z-10" :style="{ width: ((currentStep - 1) / 2) * 100 + '%' }"></div>
                        
                        <div v-for="step in 3" :key="step" class="flex flex-col items-center bg-white p-2">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm transition-colors', currentStep >= step ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500']">
                                {{ step }}
                            </div>
                            <div class="text-xs mt-1 font-medium text-gray-500 uppercase">
                                {{ step === 1 ? 'Product' : step === 2 ? 'Customize' : 'Sign' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Wizard Content -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-100">
                    
                    <!-- Step 1: Select Product -->
                    <div v-if="currentStep === 1" class="p-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Choose a Loan Product</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div v-for="product in products" :key="product.id" @click="selectProduct(product)" class="cursor-pointer border-2 border-transparent hover:border-indigo-500 bg-gray-50 p-6 rounded-xl transition-all hover:shadow-lg relative group">
                                <div class="absolute top-0 right-0 bg-indigo-100 text-indigo-700 text-xs font-bold px-3 py-1 rounded-bl-lg">
                                    {{ product.interest_type }}
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">{{ product.name }}</h3>
                                <p class="text-sm text-gray-500 mt-2 h-12">{{ product.description }}</p>
                                
                                <div class="mt-4 pt-4 border-t border-gray-200 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">Max Limit</span>
                                        <span class="font-bold">₹{{ product.max_amount_limit.toLocaleString() }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">Max Tenure</span>
                                        <span class="font-bold">{{ product.max_tenure_months }} Months</span>
                                    </div>
                                </div>
                                <button class="mt-6 w-full py-2 bg-white border border-indigo-200 text-indigo-600 font-semibold rounded-lg group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                    Select
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Simulator -->
                    <div v-if="currentStep === 2" class="p-8">
                        <div class="flex justify-between items-center mb-6">
                             <h2 class="text-2xl font-bold text-gray-800">Customize Your {{ selectedProduct.name }}</h2>
                             <button @click="currentStep = 1" class="text-sm text-gray-500 hover:text-gray-700">Change Product</button>
                        </div>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Controls -->
                            <div class="lg:col-span-2 space-y-6">
                                <div>
                                    <InputLabel value="Loan Amount (₹)" />
                                    <div class="flex items-center gap-4">
                                        <input type="range" v-model.number="form.amount" min="1000" :max="selectedProduct.max_amount_limit" step="1000" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                                        <TextInput type="number" v-model.number="form.amount" class="w-32 font-bold" />
                                    </div>
                                    <InputError :message="simulationError" />
                                </div>
                                
                                <div>
                                    <InputLabel value="Tenure (Months)" />
                                    <div class="flex items-center gap-4">
                                        <input type="range" v-model.number="form.tenure" min="1" :max="selectedProduct.max_tenure_months" step="1" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                                        <TextInput type="number" v-model.number="form.tenure" class="w-32 font-bold" />
                                    </div>
                                </div>
                                
                                <div>
                                    <InputLabel value="Reason for Loan" />
                                    <textarea v-model="form.reason" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mt-1" rows="3" placeholder="Briefly explain the purpose..."></textarea>
                                    <InputError :message="form.errors.reason" />
                                </div>
                            </div>
                            
                            <!-- Summary Card -->
                            <div class="bg-indigo-50 rounded-xl p-6 border border-indigo-100 h-fit">
                                <h3 class="text-lg font-bold text-indigo-900 mb-4">Repayment Preview</h3>
                                
                                <div v-if="simulationResult && !simulationError" class="space-y-4">
                                     <div class="flex justify-between items-end">
                                         <span class="text-gray-600 text-sm">Monthly EMI</span>
                                         <span class="text-3xl font-bold text-indigo-600">₹{{ simulationResult.emi.toLocaleString() }}</span>
                                     </div>
                                     
                                     <div class="pt-4 border-t border-indigo-200 mt-4 space-y-2 text-sm">
                                         <div class="flex justify-between">
                                             <span class="text-gray-600">Interest Rate</span>
                                             <span class="font-medium">{{ simulationResult.rate }}% ({{ selectedProduct.interest_type }})</span>
                                         </div>
                                         <div class="flex justify-between">
                                             <span class="text-gray-600">Total Interest</span>
                                             <span class="font-medium text-orange-600">₹{{ simulationResult.total_interest.toLocaleString() }}</span>
                                         </div>
                                          <div class="flex justify-between font-bold text-gray-800 pt-2 border-t border-indigo-200">
                                             <span>Total Payable</span>
                                             <span>₹{{ simulationResult.total_payable.toLocaleString() }}</span>
                                         </div>
                                         
                                         <!-- Risk Warnings -->
                                         <div v-if="simulationResult.risk_analysis && simulationResult.risk_analysis.score > 0" class="mt-4 pt-4 border-t border-red-200">
                                              <div class="text-xs font-bold text-red-600 mb-1">Risk Factors:</div>
                                              <ul class="text-xs text-red-600 space-y-1">
                                                  <li v-for="(flag, i) in simulationResult.risk_analysis.flags" :key="i">• {{ flag }}</li>
                                              </ul>
                                              <p v-if="simulationResult.risk_analysis.score > 50" class="text-xs text-red-700 italic mt-2">
                                                  High probability of rejection. Consider reducing amount or tenure.
                                              </p>
                                         </div>
                                     </div>
                                     
                                     <PrimaryButton @click="currentStep = 3" class="w-full justify-center mt-6">
                                         Proceed to Sign
                                     </PrimaryButton>
                                </div>
                                
                                <div v-else-if="simulationError" class="text-red-500 text-sm italic py-4">
                                    {{ simulationError }}
                                </div>
                                
                                <div v-else class="text-gray-400 italic text-sm py-4">
                                    Loading simulation...
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: E-Sign -->
                    <div v-if="currentStep === 3" class="p-8 max-w-2xl mx-auto text-center">
                        <div class="mb-6">
                            <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-800">Review & Sign</h2>
                            <p class="text-gray-600 mt-2">By signing below, you agree to the repayment terms and authorize salary deductions.</p>
                        </div>
                        
                        <div class="bg-gray-50 p-6 rounded-lg text-left text-sm text-gray-600 mb-8 border border-gray-200 max-h-48 overflow-y-auto">
                            <p><strong>Terms and Conditions:</strong></p>
                            <ul class="list-disc pl-5 mt-2 space-y-1">
                                <li>The loan amount of ₹{{ form.amount }} will be disbursed to your registered bank account.</li>
                                <li>Monthly EMI of ₹{{ simulationResult?.emi }} will be deducted starting next payroll cycle.</li>
                                <li>Interest is charged at {{ simulationResult?.rate }}% per annum.</li>
                                <li>In case of separation, the outstanding balance must be settled in full.</li>
                            </ul>
                        </div>
                        
                        <div class="space-y-4">
                            <label class="flex items-center justify-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="form.agreed_to_terms" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="text-sm font-medium text-gray-700">I agree to the Terms & Conditions</span>
                            </label>
                            
                            <div v-if="form.agreed_to_terms" class="space-y-4 animate-fade-in-up">
                                <div>
                                    <InputLabel value="Type your First Name to Sign" />
                                    <TextInput v-model="signatureInput" class="text-center font-handwriting text-2xl tracking-widest mt-1 w-full max-w-xs mx-auto placeholder:font-sans" :placeholder="employee.first_name" />
                                    <p class="text-xs text-gray-400 mt-1">E-Signature Verification</p>
                                </div>
                                
                                <div class="flex justify-center gap-4 mt-6">
                                    <SecondaryButton @click="currentStep = 2">Back</SecondaryButton>
                                    <PrimaryButton @click="signDocument" :class="{ 'opacity-50 cursor-not-allowed': !signatureInput }" :disabled="!signatureInput || form.processing">
                                        Submit Application
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.font-handwriting {
    font-family: 'Courier New', Courier, monospace; /* Placeholder for signature font */
    font-style: italic;
}
</style>
