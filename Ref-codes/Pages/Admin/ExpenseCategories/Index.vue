<template>
    <Head title="Expense Categories" />
    <MainLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Expense Categories</h2>
                    <p class="text-sm text-gray-500">Define expense policies, limits, and approval workflows.</p>
                </div>
                <button @click="openModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 shadow flex items-center">
                    <svg class="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    New Category
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                     <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Code / GL</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Policy Limits</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Workflow</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="cat in categories.data" :key="cat.id" class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-xl mr-3">{{ cat.icon || '📝' }}</span>
                                        <div>
                                            <div class="text-sm font-bold text-gray-900">{{ cat.name }}</div>
                                            <div class="text-xs text-gray-500">{{ cat.description }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span v-if="cat.code" class="font-mono bg-gray-100 px-2 py-1 rounded text-xs">{{ cat.code }}</span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        <span v-if="cat.unit_type !== 'FIXED'" class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded w-max">
                                            {{ cat.unit_type }} @ {{ cat.unit_rate }}/unit
                                        </span>
                                        <span v-if="cat.limits?.max_amount_per_claim" class="text-xs text-gray-600">
                                            Max: ₹{{ cat.limits.max_amount_per_claim }}
                                        </span>
                                        <span v-if="cat.requires_bill_proof" class="text-xs flex items-center text-orange-600">
                                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            Receipt Req.
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span v-if="cat.workflow" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        {{ cat.workflow.name }}
                                    </span>
                                    <span v-else class="text-xs text-red-500 italic">No Workflow</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="cat.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ cat.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="openModal(cat)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                </td>
                            </tr>
                        </tbody>
                     </table>
                     <div v-if="categories.data.length === 0" class="p-8 text-center text-gray-500">
                         No expense categories defined yet.
                     </div>
                     
                     <div v-if="categories.links.length > 3" class="px-6 py-4 border-t border-gray-200">
                         <Pagination :links="categories.links" />
                     </div>
                </div>

            </div>
        </div>

        <!-- Configuration Modal -->
        <Modal :show="showModal" :title="form.id ? 'Edit Configuration' : 'New Expense Category'" @close="showModal = false" maxWidth="2xl">
            <form @submit.prevent="submit" class="pb-2">
                <!-- Smart Tabs -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button type="button" @click="activeTab = 'basic'" :class="[activeTab === 'basic' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                            Basic & GL
                        </button>
                        <button type="button" @click="activeTab = 'policy'" :class="[activeTab === 'policy' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                            Policy & Limits
                        </button>
                        <button type="button" @click="activeTab = 'visibility'" :class="[activeTab === 'visibility' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                            Visibility & Rules
                        </button>
                    </nav>
                </div>

                <!-- Tab A: Basic -->
                <div v-show="activeTab === 'basic'" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                         <div>
                            <InputLabel value="Category Name" />
                            <TextInput v-model="form.name" class="w-full" required placeholder="e.g. Travel" />
                        </div>
                        <div>
                            <InputLabel value="Icon (Emoji)" />
                            <TextInput v-model="form.icon" class="w-full" placeholder="e.g. ✈️" />
                        </div>
                    </div>
                    
                    <div>
                        <InputLabel value="Description / Hint" />
                        <TextInput v-model="form.description" class="w-full" placeholder="Shown to employees" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="GL Code (Account)" />
                            <TextInput v-model="form.code" class="w-full font-mono" placeholder="EXP-001" />
                        </div>
                        <div>
                            <InputLabel value="Approval Workflow" />
                            <select v-model="form.workflow_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                <option :value="null">-- Select Workflow --</option>
                                <option v-for="wf in workflows" :key="wf.id" :value="wf.id">{{ wf.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Default Payout Method" />
                        <select v-model="form.default_payout_method" class="w-full mt-2 border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="payroll">Via Payroll (Salary Sheet)</option>
                            <option value="direct">Direct Reimbursement (Bank Transfer)</option>
                        </select>
                        <p class="text-sm text-gray-500 mt-1">Initial value shown to employees during claim submission.</p>
                    </div>
                </div>

                <!-- Tab B: Policy -->
                <div v-show="activeTab === 'policy'" class="space-y-6">
                    <div>
                        <InputLabel value="Expense Unit Type" />
                        <div class="flex gap-4 mt-2">
                            <label class="flex items-center"><input type="radio" value="FIXED" v-model="form.unit_type" class="mr-2"> Fixed Amount</label>
                            <label class="flex items-center"><input type="radio" value="MILEAGE" v-model="form.unit_type" class="mr-2"> Mileage (Rate/Unit)</label>
                            <label class="flex items-center"><input type="radio" value="PER_DIEM" v-model="form.unit_type" class="mr-2"> Per Diem (Daily)</label>
                        </div>
                    </div>

                    <div v-if="form.unit_type !== 'FIXED'" class="bg-blue-50 p-4 rounded-lg">
                        <InputLabel value="Rate per Unit (₹)" />
                        <TextInput type="number" step="0.01" v-model="form.unit_rate" class="w-32" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Max per Claim (₹)" />
                            <TextInput type="number" v-model="form.limits.max_amount_per_claim" class="w-full" placeholder="Optional" />
                        </div>
                        <div>
                            <InputLabel value="Max Monthly Cap (₹)" />
                            <TextInput type="number" v-model="form.limits.max_amount_monthly" class="w-full" placeholder="Optional" />
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <div class="flex items-center justify-between">
                            <label class="flex items-center font-medium text-sm text-gray-700">
                                <input type="checkbox" v-model="form.requires_bill_proof" class="rounded border-gray-300 text-indigo-600 mr-2" />
                                Require Receipt / Proof
                            </label>
                            
                            <div v-if="form.requires_bill_proof" class="flex items-center gap-2">
                                <span class="text-xs text-gray-500">Only if Amount > </span>
                                <TextInput type="number" v-model="form.rules.receipt_threshold" class="w-20 text-sm py-1" placeholder="0" />
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- Tab C: Visibility & Rules -->
                <div v-show="activeTab === 'visibility'" class="space-y-6">
                    <div>
                         <InputLabel value="Advanced Features" />
                         <div class="space-y-2 mt-2">
                             <label class="flex items-center">
                                 <input type="checkbox" v-model="form.rules.gst_eligible" class="rounded border-gray-300 text-indigo-600 mr-2" />
                                 <span class="text-sm">GST Input Credit Eligible (Ask for GSTIN)</span>
                             </label>
                             <label class="flex items-center">
                                 <input type="checkbox" v-model="form.rules.require_attendees" class="rounded border-gray-300 text-indigo-600 mr-2" />
                                 <span class="text-sm">Require Guest/Attendees List</span>
                             </label>
                             <label class="flex items-center">
                                 <input type="checkbox" v-model="form.rules.project_linking" class="rounded border-gray-300 text-indigo-600 mr-2" />
                                 <span class="text-sm">Link to Project (Billable)</span>
                             </label>
                         </div>
                    </div>

                    <div>
                        <InputLabel value="Department Restrictions" />
                        <p class="text-xs text-gray-500 mb-2">Leave unselected to allow all.</p>
                        <div class="h-32 overflow-y-auto border rounded p-2 grid grid-cols-2 gap-2 bg-gray-50">
                            <label v-for="dept in departments" :key="dept.id" class="flex items-center">
                                <input type="checkbox" :value="dept.id" v-model="form.visibility.departments" class="rounded border-gray-300 text-indigo-600 mr-2" />
                                <span class="text-xs">{{ dept.name }}</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="border-t pt-4 flex items-center gap-2">
                         <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-indigo-600" />
                         <span class="font-bold text-sm">Category Active</span>
                    </div>
                </div>
            </form>
            
            <template #footer>
                <div class="flex justify-between w-full">
                    <button v-if="form.id" type="button" @click="deleteCategory" class="text-red-500 text-sm font-medium hover:text-red-700">Delete</button>
                    <div v-else></div> <!-- Spacer -->
                    
                    <div class="flex gap-3">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton @click="submit" :disabled="form.processing">Save Configuration</PrimaryButton>
                    </div>
                </div>
            </template>
        </Modal>

    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';

const props = defineProps({
    categories: Object,
    workflows: Array,
    departments: Array
});

const showModal = ref(false);
const activeTab = ref('basic');

const form = useForm({
    id: null,
    name: '',
    code: '',
    icon: '',
    description: '',
    workflow_id: null,
    unit_type: 'FIXED',
    unit_rate: null,
    requires_bill_proof: false,
    limits: {
        max_amount_per_claim: null,
        max_amount_monthly: null
    },
    rules: {
        receipt_threshold: null,
        gst_eligible: false,
        require_attendees: false,
        project_linking: false
    },
    visibility: {
        departments: []
    },
    default_payout_method: 'payroll',
    is_active: true
});

const openModal = (cat = null) => {
    activeTab.value = 'basic';
    if (cat) {
        form.id = cat.id;
        form.name = cat.name;
        form.code = cat.code;
        form.icon = cat.icon;
        form.description = cat.description;
        form.workflow_id = cat.workflow_id;
        form.unit_type = cat.unit_type;
        form.unit_rate = cat.unit_rate;
        form.requires_bill_proof = !!cat.requires_bill_proof;
        form.default_payout_method = cat.default_payout_method || 'payroll';
        form.is_active = !!cat.is_active;
        
        // Deep copy JSONs to avoid reference binding issues or nulls
        form.limits = { 
            max_amount_per_claim: cat.limits?.max_amount_per_claim ?? null,
            max_amount_monthly: cat.limits?.max_amount_monthly ?? null
        };
        form.rules = {
            receipt_threshold: cat.rules?.receipt_threshold ?? null,
            gst_eligible: !!cat.rules?.gst_eligible,
            require_attendees: !!cat.rules?.require_attendees,
            project_linking: !!cat.rules?.project_linking
        };
        form.visibility = {
            departments: cat.visibility?.departments ?? []
        };
    } else {
        form.reset();
        form.id = null;
        // Reset JSONs explicitly
        form.limits = { max_amount_per_claim: null, max_amount_monthly: null };
        form.rules = { gst_eligible: false, require_attendees: false, project_linking: false };
        form.visibility = { departments: [] };
        form.default_payout_method = 'payroll';
    }
    showModal.value = true;
};

const submit = () => {
    if (form.id) {
        form.put(route('admin.expense-categories.update', form.id), {
            onSuccess: () => showModal.value = false
        });
    } else {
        form.post(route('admin.expense-categories.store'), {
            onSuccess: () => showModal.value = false
        });
    }
};

const deleteCategory = () => {
    if (confirm('Delete this category permanently?')) {
        router.delete(route('admin.expense-categories.destroy', form.id), {
            onSuccess: () => showModal.value = false
        });
    }
};
</script>
