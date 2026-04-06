<template>
    <Modal :show="show" @close="close">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-2">Bulk Update Bank Details</h2>
            <p class="text-sm text-gray-500 mb-6">Upload a CSV file to update bank accounts for multiple employees at once.</p>
            
            <div class="mb-6 p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                <h3 class="text-xs font-bold text-indigo-700 uppercase mb-2 tracking-wider">CSV Format Guide</h3>
                <ul class="text-xs text-indigo-600 space-y-1 font-medium">
                    <li>• Column 1: Employee Code (Required)</li>
                    <li>• Column 2: Account Holder Name (Required)</li>
                    <li>• Column 3: Bank Name (Required)</li>
                    <li>• Column 4: Account Number (Required)</li>
                    <li>• Column 5: IFSC Code (Required)</li>
                    <li>• Column 6: Branch Name (Optional)</li>
                    <li>• Column 7: Account Type (Optional: savings, current)</li>
                </ul>
                <div class="mt-4">
                    <a href="#" @click.prevent="downloadTemplate" class="text-indigo-700 hover:text-indigo-900 font-bold flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download Sample Template
                    </a>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <InputLabel for="csv_file" value="Select CSV File" />
                    <input 
                        type="file" 
                        id="csv_file" 
                        ref="fileInput"
                        @change="handleFileChange"
                        accept=".csv"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all cursor-pointer border border-gray-200 rounded-xl"
                    />
                    <InputError :message="form.errors.file" class="mt-2" />
                </div>
            </div>

            <div v-if="form.errors.errors" class="mt-4 p-4 bg-red-50 rounded-xl border border-red-100">
                <h3 class="text-xs font-bold text-red-700 uppercase mb-2">Import Errors</h3>
                <ul class="text-xs text-red-600 space-y-1 list-disc pl-4">
                    <li v-for="(error, index) in form.errors.errors" :key="index">{{ error }}</li>
                </ul>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <SecondaryButton @click="close">Cancel</SecondaryButton>
                <PrimaryButton 
                    @click="submit" 
                    :disabled="form.processing || !form.file" 
                    :class="{ 'opacity-25': form.processing || !form.file }"
                >
                    {{ form.processing ? 'Importing...' : 'Start Import' }}
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: Boolean
});

const emit = defineEmits(['close', 'success']);

const fileInput = ref(null);

const form = useForm({
    file: null
});

const handleFileChange = (e) => {
    form.file = e.target.files[0];
};

const submit = () => {
    form.post(route('employees.bank.bulk'), {
        onSuccess: () => {
            emit('success');
            close();
        },
        preserveScroll: true
    });
};

const close = () => {
    form.reset();
    form.clearErrors();
    if (fileInput.value) fileInput.value.value = '';
    emit('close');
};

const downloadTemplate = () => {
    const csvContent = "data:text/csv;charset=utf-8,EmpCode,AccountHolder,BankName,AccountNumber,IFSC,Branch,AccountType\nEMP001,John Doe,HDFC Bank,50100123456789,HDFC0001234,Mumbai,savings";
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "bank_import_template.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>
