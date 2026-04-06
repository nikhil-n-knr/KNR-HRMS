<template>
    <Head title="Proof Verification" />
    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Proof Verification Queue (FY {{ fiscal_year }})
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        
                        <!-- Filters could go here -->

                        <div v-if="proofs.data.length === 0" class="text-center text-gray-500 py-10">
                            No pending proofs for verification.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="proof in proofs.data" :key="proof.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ proof.uploader.user.name }}<br>
                                            <span class="text-xs text-gray-500">{{ proof.uploader.employee_code }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ proof.declaration.section.section_code }} ({{ proof.declaration.section.name }})<br>
                                            <span class="text-xs font-bold">Declared: {{ formatCurrency(proof.declaration.declared_amount) }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">
                                            <a :href="'/storage/' + proof.file_path" target="_blank" class="hover:underline flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                {{ proof.original_name }}
                                            </a>
                                            <p class="text-xs text-gray-400 mt-1">{{ proof.description }}</p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800" v-if="proof.status === 'Pending'">
                                                Pending
                                            </span>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800" v-else-if="proof.status === 'Approved'">
                                                Approved
                                            </span>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800" v-else>
                                                Rejected
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex gap-2">
                                                <button @click="openVerifyModal(proof)" class="text-indigo-600 hover:text-indigo-900">Verify</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Verification Modal -->
                <Modal :show="showVerifyModal" @close="showVerifyModal = false">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Verify Proof</h2>
                        
                        <div v-if="activeProof" class="mb-4 text-sm bg-gray-50 p-3 rounded">
                            <p><strong>Employee:</strong> {{ activeProof.uploader.user.name }}</p>
                            <p><strong>Section:</strong> {{ activeProof.declaration.section.section_code }}</p>
                            <p><strong>Declared Amount:</strong> {{ formatCurrency(activeProof.declaration.declared_amount) }}</p>
                            <p class="mt-2 text-xs text-gray-500">Review the document in a new tab before approving.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <button @click="action = 'approve'" :class="{'ring-2 ring-green-500': action === 'approve'}" class="p-4 border rounded-lg text-center hover:bg-green-50">
                                <span class="block text-green-600 font-bold">Approve</span>
                            </button>
                            <button @click="action = 'reject'" :class="{'ring-2 ring-red-500': action === 'reject'}" class="p-4 border rounded-lg text-center hover:bg-red-50">
                                <span class="block text-red-600 font-bold">Reject</span>
                            </button>
                        </div>

                        <div v-if="action === 'approve'" class="mt-4">
                            <InputLabel value="Verified Amount (Accepted)" />
                            <TextInput type="number" v-model="form.verified_amount" class="w-full mt-1" />
                            <p class="text-xs text-gray-500 mt-1">This amount will be used for tax calculation.</p>
                        </div>

                        <div v-if="action === 'reject'" class="mt-4">
                            <InputLabel value="Rejection Reason" />
                            <TextInput v-model="form.rejection_reason" class="w-full mt-1" placeholder="e.g. Blurry image, invalid date" />
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <SecondaryButton @click="showVerifyModal = false">Cancel</SecondaryButton>
                            <PrimaryButton @click="submitVerification" :disabled="form.processing">Submit</PrimaryButton>
                        </div>
                    </div>
                </Modal>

            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';

const props = defineProps({
    proofs: Object,
    fiscal_year: String
});

const showVerifyModal = ref(false);
const activeProof = ref(null);
const action = ref('approve');

const form = useForm({
    action: 'approve',
    verified_amount: 0,
    rejection_reason: ''
});

const openVerifyModal = (proof) => {
    activeProof.value = proof;
    // Default acceptable amount = declared amount? Or 0?
    // Let's set it to Declared Amount for convenience
    form.verified_amount = proof.declaration.declared_amount;
    form.rejection_reason = '';
    action.value = 'approve';
    showVerifyModal.value = true;
};

const submitVerification = () => {
    form.action = action.value;
    form.put(route('hr.tax.proofs.verify', activeProof.value.id), {
        onSuccess: () => {
            showVerifyModal.value = false;
        }
    });
};

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val || 0);
</script>
