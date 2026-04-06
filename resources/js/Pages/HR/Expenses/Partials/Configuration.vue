<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    categories: Array,
    workflows: Array
});

const showModal = ref(false);
const editingCategory = ref(null);

const form = useForm({
    id: null,
    name: '',
    description: '',
    workflow_id: null,
    requires_bill_proof: false,
    require_gst: false,
    soft_limit: null,
    hard_limit: null,
    max_days_backdating: 60,
    policy_settings: {
        min_amount: null,
        per_transaction_limit: null,
        max_claims_per_day: null,
        type: 'general', // general, mileage, per_diem
        mileage_rate: null,
        daily_rate: null,
        gst_required_above: 5000,
        receipt_required_above: 500
    }
});

const openCreate = () => {
    editingCategory.value = null;
    form.reset();
    form.policy_settings = {
         min_amount: null,
         per_transaction_limit: null,
         max_claims_per_day: null,
         type: 'general',
         mileage_rate: null,
         daily_rate: null,
         gst_required_above: 5000,
         receipt_required_above: 500
    };
    showModal.value = true;
};

const openEdit = (cat) => {
    editingCategory.value = cat;
    form.id = cat.id;
    form.name = cat.name;
    form.description = cat.description;
    form.workflow_id = cat.workflow_id;
    form.requires_bill_proof = !!cat.requires_bill_proof;
    form.require_gst = !!cat.require_gst;
    form.soft_limit = cat.soft_limit;
    form.hard_limit = cat.hard_limit;
    form.max_days_backdating = cat.max_days_backdating || 60;
    
    // Merge defaults
    form.policy_settings = {
        min_amount: cat.policy_settings?.min_amount ?? null,
        per_transaction_limit: cat.policy_settings?.per_transaction_limit ?? null,
        max_claims_per_day: cat.policy_settings?.max_claims_per_day ?? null,
        type: cat.policy_settings?.type ?? 'general',
        mileage_rate: cat.policy_settings?.mileage_rate ?? null,
        daily_rate: cat.policy_settings?.daily_rate ?? null,
        gst_required_above: cat.policy_settings?.gst_required_above ?? 5000,
        receipt_required_above: cat.policy_settings?.receipt_required_above ?? 500
    };
    
    showModal.value = true;
};

const submit = () => {
    if (editingCategory.value) {
        form.put(route('admin.expense-categories.update', editingCategory.value.id), {
            onSuccess: () => showModal.value = false
        });
    } else {
        form.post(route('admin.expense-categories.store'), {
            onSuccess: () => showModal.value = false
        });
    }
};

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val || 0);

</script>

<template>
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Expense Policies & Categories</h3>
                <p class="text-sm text-gray-500">Configure limits, caps, and compliance rules.</p>
            </div>
            <button @click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                + New Category
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="cat in categories" :key="cat.id" class="border rounded-lg p-5 hover:border-indigo-200 transition-colors relative group bg-gray-50/50">
                <button @click="openEdit(cat)" class="absolute top-4 right-4 text-indigo-600 opacity-0 group-hover:opacity-100 font-medium text-sm">Edit</button>
                
                <h4 class="font-bold text-gray-800">{{ cat.name }}</h4>
                <p class="text-xs text-gray-500 mb-4 h-4">{{ cat.workflow?.name || 'No Workflow' }}</p>
                
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Limits:</span>
                        <span class="font-medium">
                            <span v-if="cat.hard_limit" class="text-red-600">Max {{ formatCurrency(cat.hard_limit) }}</span>
                            <span v-else class="text-gray-400">Uncapped</span>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Backdating:</span>
                        <span class="font-medium">{{ cat.max_days_backdating }} Days</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Receipts:</span>
                        <span :class="cat.requires_bill_proof ? 'text-green-600' : 'text-gray-400'">
                            {{ cat.requires_bill_proof ? 'Mandatory' : 'Optional' }}
                        </span>
                    </div>
                     <div class="mt-2 pt-2 border-t text-xs text-gray-500">
                         <span v-if="cat.policy_settings?.type === 'mileage'">🚗 Mileage: {{ formatCurrency(cat.policy_settings?.mileage_rate) }}/km</span>
                         <span v-if="cat.policy_settings?.type === 'per_diem'">📅 Per Diem: {{ formatCurrency(cat.policy_settings?.daily_rate) }}/day</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal -->
        <Modal :show="showModal" @close="showModal = false">
            <div class="p-6 h-[80vh] overflow-y-auto">
                <h2 class="text-lg font-bold mb-4">{{ editingCategory ? 'Edit Policy' : 'New Category' }}</h2>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Info -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                             <InputLabel value="Category Name" />
                             <TextInput v-model="form.name" class="w-full" required />
                        </div>
                         <div class="col-span-2">
                             <InputLabel value="Approval Workflow" />
                             <select v-model="form.workflow_id" class="w-full border-gray-300 rounded shadow-sm">
                                 <option :value="null">-- No Workflow --</option>
                                 <option v-for="wf in workflows" :key="wf.id" :value="wf.id">{{ wf.name }}</option>
                             </select>
                        </div>
                    </div>
                    
                    <hr />
                    
                    <!-- Policy Settings -->
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Policy Type</h4>
                        <div class="flex gap-4">
                            <label class="flex items-center"><input type="radio" v-model="form.policy_settings.type" value="general" class="mr-2" /> General</label>
                            <label class="flex items-center"><input type="radio" v-model="form.policy_settings.type" value="mileage" class="mr-2" /> Mileage (Fuel)</label>
                            <label class="flex items-center"><input type="radio" v-model="form.policy_settings.type" value="per_diem" class="mr-2" /> Per Diem (Fixed)</label>
                        </div>
                    </div>

                    <div v-if="form.policy_settings.type === 'mileage'" class="bg-blue-50 p-4 rounded">
                         <InputLabel value="Rate per KM (INR)" />
                         <TextInput type="number" step="0.01" v-model="form.policy_settings.mileage_rate" class="w-full" />
                    </div>
                    
                    <div v-if="form.policy_settings.type === 'per_diem'" class="bg-blue-50 p-4 rounded">
                         <InputLabel value="Fixed Daily Allowance (INR)" />
                         <TextInput type="number" step="0.01" v-model="form.policy_settings.daily_rate" class="w-full" />
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Dates -->
                        <div>
                             <InputLabel value="Max Backdating (Days)" />
                             <TextInput type="number" v-model="form.max_days_backdating" class="w-full" />
                        </div>
                        <div>
                             <InputLabel value="Max Claims Per Day" />
                             <TextInput type="number" v-model="form.policy_settings.max_claims_per_day" class="w-full" placeholder="Default: Unlimited" />
                        </div>

                        <!-- Amounts -->
                        <div>
                             <InputLabel value="Overall Transaction Cap (Hard Limit)" />
                             <TextInput type="number" v-model="form.hard_limit" class="w-full" placeholder="e.g. 50000" />
                        </div>
                         <div>
                             <InputLabel value="Soft Limit (Warning Only)" />
                             <TextInput type="number" v-model="form.soft_limit" class="w-full" placeholder="e.g. 40000" />
                        </div>
                         <div>
                             <InputLabel value="Min Claim Amount" />
                             <TextInput type="number" v-model="form.policy_settings.min_amount" class="w-full" placeholder="e.g. 50" />
                        </div>
                    </div>
                    
                    <hr />
                    
                    <!-- Compliance -->
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Compliance & Proofs</h4>
                        <div class="space-y-3">
                             <div class="flex items-center justify-between">
                                 <span class="text-sm">Require Receipt Upload?</span>
                                 <input type="checkbox" v-model="form.requires_bill_proof" class="toggle" />
                             </div>
                             <div v-if="!form.requires_bill_proof" class="flex items-center gap-2">
                                  <span class="text-sm text-gray-500">Or require if amount ></span>
                                  <TextInput type="number" v-model="form.policy_settings.receipt_required_above" class="w-24 text-sm p-1" />
                             </div>
                             
                             <div class="flex items-center justify-between pt-2">
                                 <span class="text-sm">Require GST Number?</span>
                                 <input type="checkbox" v-model="form.require_gst" class="toggle" />
                             </div>
                             <div v-if="!form.require_gst" class="flex items-center gap-2">
                                  <span class="text-sm text-gray-500">Or require if amount ></span>
                                  <TextInput type="number" v-model="form.policy_settings.gst_required_above" class="w-24 text-sm p-1" />
                             </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="form.processing">Save Configuration</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>
