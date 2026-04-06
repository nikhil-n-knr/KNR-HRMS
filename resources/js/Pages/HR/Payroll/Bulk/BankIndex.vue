<template>
    <Head title="Bulk Bank Update" />
    <MainLayout>
        <PayrollTabs class="-mt-6 -mx-4 sm:-mx-6 lg:-mx-8 mb-6" />
        <div class="px-4 sm:px-6 lg:px-8 py-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-xl font-semibold text-gray-900">Bulk Bank Details Update</h1>
                    <p class="mt-2 text-sm text-gray-700">Update bank account information for multiple employees at once. You can edit inline or use CSV import.</p>
                </div>
                <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none flex items-center space-x-3">
                    <a :href="route('payroll.bulk-bank.sample')" 
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Template
                    </a>
                    <button @click="showImportModal = true" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        Import CSV
                    </button>
                    <button @click="save" :disabled="form.processing || !hasChanges" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto disabled:opacity-50">
                        Save Changes
                    </button>
                </div>
            </div>

            <!-- Import Modal -->
            <Modal :show="showImportModal" @close="showImportModal = false">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Import Bank Details (CSV)</h2>
                    <form @submit.prevent="submitImport" class="space-y-4">
                        <div class="p-4 border-2 border-dashed border-gray-200 rounded-lg text-center">
                            <input type="file" @input="importForm.file = $event.target.files[0]" accept=".csv" class="hidden" id="bank-csv-upload" />
                            <label for="bank-csv-upload" class="cursor-pointer">
                                <div v-if="!importForm.file" class="text-gray-500">
                                    <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    <p class="text-sm font-bold">Click to upload CSV</p>
                                    <p class="text-xs">Only .csv files allowed</p>
                                </div>
                                <div v-else class="text-indigo-600 font-bold">
                                    {{ importForm.file.name }}
                                </div>
                            </label>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <SecondaryButton @click="showImportModal = false">Cancel</SecondaryButton>
                            <PrimaryButton type="submit" :disabled="importForm.processing || !importForm.file">Upload & Import</PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>

            <div class="mt-8 flex flex-col">
                <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Employee</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Bank Name</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Account Number</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">IFSC Code</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Branch Name</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">A/C Holder Name</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="emp in form.employees" :key="emp.id">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                            {{ emp.name }}
                                            <div class="text-xs text-gray-500 font-normal">{{ emp.department }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <input v-model="emp.bank_name" type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs py-1">
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                             <input v-model="emp.account_number" type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs py-1">
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                             <input v-model="emp.ifsc_code" type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs py-1 uppercase">
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                             <input v-model="emp.branch_name" type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs py-1">
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                             <input v-model="emp.account_holder_name" type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs py-1">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import PayrollTabs from '@/Components/PayrollTabs.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    employees: Array,
    departments: Array
});

const form = useForm({
    employees: props.employees.map(e => ({ ...e }))
});

const importForm = useForm({
    file: null
});

const showImportModal = ref(false);

const hasChanges = computed(() => {
    return form.employees.some((e, i) => {
        const orig = props.employees[i];
        return e.bank_name !== orig.bank_name || 
               e.account_number !== orig.account_number ||
               e.ifsc_code !== orig.ifsc_code ||
               e.branch_name !== orig.branch_name ||
               e.account_holder_name !== orig.account_holder_name;
    });
});

const save = () => {
    const modified = form.employees.filter((e, i) => {
        const orig = props.employees[i];
        return e.bank_name !== orig.bank_name || 
               e.account_number !== orig.account_number ||
               e.ifsc_code !== orig.ifsc_code ||
               e.branch_name !== orig.branch_name ||
               e.account_holder_name !== orig.account_holder_name;
    });

    if (modified.length === 0) return;

    form.transform((data) => ({
        updates: modified.map(e => ({
            id: e.id,
            bank_name: e.bank_name,
            account_number: e.account_number,
            ifsc_code: e.ifsc_code,
            branch_name: e.branch_name,
            account_holder_name: e.account_holder_name
        }))
    })).post(route('payroll.bulk-bank.store'), {
        onSuccess: () => {
            // Success handled by flash messages
        }
    });
};

const submitImport = () => {
    importForm.post(route('payroll.bulk-bank.import'), {
        onSuccess: () => {
            showImportModal.value = false;
            importForm.reset();
        }
    });
};
</script>
