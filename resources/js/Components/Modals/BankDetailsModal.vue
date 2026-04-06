<template>
    <Modal :show="show" @close="close">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Bank Details</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2 md:col-span-1">
                    <InputLabel for="account_holder_name" value="Account Holder Name" />
                    <TextInput id="account_holder_name" v-model="form.account_holder_name" class="mt-1 block w-full" placeholder="As per bank records" />
                    <InputError :message="form.errors.account_holder_name" class="mt-2" />
                </div>
                
                <div class="col-span-2 md:col-span-1">
                    <InputLabel for="bank_name" value="Bank Name" />
                    <TextInput id="bank_name" v-model="form.bank_name" class="mt-1 block w-full" placeholder="e.g. HDFC Bank" />
                    <InputError :message="form.errors.bank_name" class="mt-2" />
                </div>

                <div class="col-span-2 md:col-span-1">
                    <InputLabel for="account_number" value="Account Number" />
                    <TextInput id="account_number" v-model="form.account_number" class="mt-1 block w-full" placeholder="Enter bank account number" />
                    <InputError :message="form.errors.account_number" class="mt-2" />
                </div>

                <div class="col-span-2 md:col-span-1">
                    <InputLabel for="ifsc_code" value="IFSC Code" />
                    <TextInput id="ifsc_code" v-model="form.ifsc_code" class="mt-1 block w-full uppercase" placeholder="Enter IFSC code" />
                    <InputError :message="form.errors.ifsc_code" class="mt-2" />
                </div>

                <div class="col-span-2 md:col-span-1">
                    <InputLabel for="branch_name" value="Branch Name" />
                    <TextInput id="branch_name" v-model="form.branch_name" class="mt-1 block w-full" placeholder="Enter branch location" />
                    <InputError :message="form.errors.branch_name" class="mt-2" />
                </div>

                <div class="col-span-2 md:col-span-1">
                    <InputLabel for="account_type" value="Account Type" />
                    <select 
                        id="account_type" 
                        v-model="form.account_type" 
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    >
                        <option value="savings">Savings</option>
                        <option value="current">Current</option>
                        <option value="other">Other</option>
                    </select>
                    <InputError :message="form.errors.account_type" class="mt-2" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton @click="close">Cancel</SecondaryButton>
                <PrimaryButton @click="submit" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">Save Details</PrimaryButton>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: Boolean,
    employee: Object
});

const emit = defineEmits(['close', 'saved']);

const form = useForm({
    account_holder_name: '',
    bank_name: '',
    account_number: '',
    ifsc_code: '',
    branch_name: '',
    account_type: 'savings',
    bic_code: ''
});

watch(() => props.employee, (newVal) => {
    if (newVal && newVal.bank_details) {
        const primary = newVal.bank_details.find(b => b.is_primary) || newVal.bank_details[0] || {};
        form.account_holder_name = primary.account_holder_name || '';
        form.bank_name = primary.bank_name || '';
        form.account_number = primary.account_number || '';
        form.ifsc_code = primary.ifsc_code || '';
        form.branch_name = primary.branch_name || '';
        form.account_type = primary.account_type || 'savings';
        form.bic_code = primary.bic_code || '';
    }
}, { immediate: true });

const submit = () => {
    form.put(route('admin.employees.bank.update', props.employee.id), {
        onSuccess: () => {
            emit('saved');
            close();
        },
        preserveScroll: true
    });
};

const close = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};
</script>
