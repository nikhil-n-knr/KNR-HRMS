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
    <div class="space-y-6">
        <div class="rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50 via-white to-indigo-50 p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-blue-700">Configuration</p>
                <h3 class="mt-1 text-xl font-black text-slate-900">Expense Policies & Categories</h3>
                <p class="mt-1 text-sm font-medium text-slate-500">Configure limits, caps, and compliance rules.</p>
            </div>
            <button @click="openCreate" class="inline-flex h-10 items-center justify-center px-4 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 shadow-sm transition-colors">
                + New Category
            </button>
        </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            <div v-for="cat in categories" :key="cat.id" class="group relative rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
                <button @click="openEdit(cat)" class="absolute top-4 right-4 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-black text-blue-700 opacity-0 transition-all hover:bg-blue-100 group-hover:opacity-100">Edit</button>
                
                <div class="pr-16">
                    <h4 class="text-base font-black text-slate-900">{{ cat.name }}</h4>
                    <p class="mt-1 text-xs font-bold uppercase tracking-wide text-blue-600">{{ cat.workflow?.name || 'No Workflow' }}</p>
                </div>
                
                <div class="mt-5 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-1 py-2">
                        <span class="text-xs font-black uppercase tracking-wide text-gray-500">Limits</span>
                        <span class="text-sm font-bold">
                            <span v-if="cat.hard_limit" class="text-red-600">Max {{ formatCurrency(cat.hard_limit) }}</span>
                            <span v-else class="text-gray-400">Uncapped</span>
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-1 py-2">
                        <span class="text-xs font-black uppercase tracking-wide text-gray-500">Backdating</span>
                        <span class="text-sm font-bold text-slate-800">{{ cat.max_days_backdating }} Days</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-1 py-2">
                        <span class="text-xs font-black uppercase tracking-wide text-gray-500">Receipts</span>
                        <span class="text-sm font-bold" :class="cat.requires_bill_proof ? 'text-green-600' : 'text-gray-400'">
                            {{ cat.requires_bill_proof ? 'Mandatory' : 'Optional' }}
                        </span>
                    </div>
                     <div class="min-h-8 border-t border-gray-100 pt-3 text-xs font-bold text-gray-500">
                         <span v-if="cat.policy_settings?.type === 'mileage'">🚗 Mileage: {{ formatCurrency(cat.policy_settings?.mileage_rate) }}/km</span>
                         <span v-if="cat.policy_settings?.type === 'per_diem'">📅 Per Diem: {{ formatCurrency(cat.policy_settings?.daily_rate) }}/day</span>
                         <span v-if="!['mileage', 'per_diem'].includes(cat.policy_settings?.type)" class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-gray-600">General Policy</span>
                    </div>
                </div>
            </div>
            <div v-if="!categories || categories.length === 0" class="rounded-xl border border-dashed border-blue-200 bg-blue-50/50 p-8 text-center md:col-span-2 xl:col-span-3">
                <p class="text-sm font-bold text-blue-900">No expense categories configured yet.</p>
                <button @click="openCreate" class="mt-4 inline-flex h-10 items-center justify-center rounded-lg bg-indigo-600 px-4 text-sm font-bold text-white hover:bg-indigo-700">
                    + New Category
                </button>
            </div>
        </div>
        
        <!-- Modal -->
        <Modal :show="showModal" @close="showModal = false">
            <div class="h-[80vh] overflow-y-auto bg-slate-50">
                <div class="sticky top-0 z-10 border-b border-gray-100 bg-white px-6 py-4">
                    <p class="text-xs font-black uppercase tracking-widest text-blue-700">Policy Setup</p>
                    <h2 class="mt-1 text-lg font-black text-slate-900">{{ editingCategory ? 'Edit Policy' : 'New Category' }}</h2>
                </div>
                
                <form @submit.prevent="submit" class="space-y-6 p-6">
                    <!-- Basic Info -->
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <h4 class="mb-4 text-xs font-black uppercase tracking-widest text-blue-900">Basic Info</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-2">
                             <InputLabel value="Category Name" />
                             <TextInput v-model="form.name" class="w-full h-10 rounded-lg border-gray-200 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500" required />
                        </div>
                         <div class="col-span-2">
                             <InputLabel value="Approval Workflow" />
                             <select v-model="form.workflow_id" class="w-full h-10 rounded-lg border-gray-200 px-3 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                 <option :value="null">-- No Workflow --</option>
                                 <option v-for="wf in workflows" :key="wf.id" :value="wf.id">{{ wf.name }}</option>
                             </select>
                        </div>
                    </div>
                    </div>
                    
                    <!-- Policy Settings -->
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                        <h4 class="mb-4 text-xs font-black uppercase tracking-widest text-blue-900">Policy Type</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label class="flex items-center rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm font-bold text-gray-700"><input type="radio" v-model="form.policy_settings.type" value="general" class="mr-2 text-blue-600" /> General</label>
                            <label class="flex items-center rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm font-bold text-gray-700"><input type="radio" v-model="form.policy_settings.type" value="mileage" class="mr-2 text-blue-600" /> Mileage (Fuel)</label>
                            <label class="flex items-center rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm font-bold text-gray-700"><input type="radio" v-model="form.policy_settings.type" value="per_diem" class="mr-2 text-blue-600" /> Per Diem (Fixed)</label>
                        </div>
                    </div>

                    <div v-if="form.policy_settings.type === 'mileage'" class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                         <InputLabel value="Rate per KM (INR)" />
                         <TextInput type="number" step="0.01" v-model="form.policy_settings.mileage_rate" class="w-full h-10 rounded-lg border-blue-100 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    
                    <div v-if="form.policy_settings.type === 'per_diem'" class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                         <InputLabel value="Fixed Daily Allowance (INR)" />
                         <TextInput type="number" step="0.01" v-model="form.policy_settings.daily_rate" class="w-full h-10 rounded-lg border-blue-100 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <h4 class="mb-4 text-xs font-black uppercase tracking-widest text-blue-900">Limits & Caps</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Dates -->
                        <div>
                             <InputLabel value="Max Backdating (Days)" />
                             <TextInput type="number" v-model="form.max_days_backdating" class="w-full h-10 rounded-lg border-gray-200 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div>
                             <InputLabel value="Max Claims Per Day" />
                             <TextInput type="number" v-model="form.policy_settings.max_claims_per_day" class="w-full h-10 rounded-lg border-gray-200 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Default: Unlimited" />
                        </div>

                        <!-- Amounts -->
                        <div>
                             <InputLabel value="Overall Transaction Cap (Hard Limit)" />
                             <TextInput type="number" v-model="form.hard_limit" class="w-full h-10 rounded-lg border-gray-200 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. 50000" />
                        </div>
                         <div>
                             <InputLabel value="Soft Limit (Warning Only)" />
                             <TextInput type="number" v-model="form.soft_limit" class="w-full h-10 rounded-lg border-gray-200 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. 40000" />
                        </div>
                         <div>
                             <InputLabel value="Min Claim Amount" />
                             <TextInput type="number" v-model="form.policy_settings.min_amount" class="w-full h-10 rounded-lg border-gray-200 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. 50" />
                        </div>
                    </div>
                    </div>
                    
                    <!-- Compliance -->
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                        <h4 class="mb-4 text-xs font-black uppercase tracking-widest text-blue-900">Compliance & Proofs</h4>
                        <div class="space-y-3">
                             <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2">
                                 <span class="text-sm font-bold text-gray-700">Require Receipt Upload?</span>
                                 <input type="checkbox" v-model="form.requires_bill_proof" class="toggle" />
                             </div>
                             <div v-if="!form.requires_bill_proof" class="flex flex-col sm:flex-row sm:items-center gap-2">
                                  <span class="text-sm font-medium text-gray-500">Or require if amount ></span>
                                  <TextInput type="number" v-model="form.policy_settings.receipt_required_above" class="w-full sm:w-32 h-9 rounded-lg border-gray-200 px-3 text-sm shadow-sm" />
                             </div>
                             
                             <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2">
                                 <span class="text-sm font-bold text-gray-700">Require GST Number?</span>
                                 <input type="checkbox" v-model="form.require_gst" class="toggle" />
                             </div>
                             <div v-if="!form.require_gst" class="flex flex-col sm:flex-row sm:items-center gap-2">
                                  <span class="text-sm font-medium text-gray-500">Or require if amount ></span>
                                  <TextInput type="number" v-model="form.policy_settings.gst_required_above" class="w-full sm:w-32 h-9 rounded-lg border-gray-200 px-3 text-sm shadow-sm" />
                             </div>
                        </div>
                    </div>

                    <div class="sticky bottom-0 -mx-6 -mb-6 flex justify-end gap-3 border-t border-gray-100 bg-white px-6 py-4">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="form.processing">Save Configuration</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>
