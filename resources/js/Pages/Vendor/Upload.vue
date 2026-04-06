<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    token: String,
    categories: Array
});

const form = useForm({
    token: props.token,
    po_number: '',
    csv_file: null
});

const submit = () => {
    form.post(route('vendor.process'), {
        forceFormData: true,
        onSuccess: () => form.reset('csv_file')
    });
};
</script>

<template>
    <Head title="Vendor Portal" />

    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Vendor Self-Service
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Upload Asset Manifest for Incoming Orders
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Purchase Order (PO) Number</label>
                        <div class="mt-1">
                            <input v-model="form.po_number" type="text" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Upload CSV Manifest</label>
                         <p class="text-xs text-gray-500 mb-2">Format: SerialNo, ModelName, Category, Cost</p>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label class="relative cursor-pointer bg-white rounded-md font-medium text-emerald-600 hover:text-emerald-500 focus-within:outline-none">
                                        <span>Upload a file</span>
                                        <input @input="form.csv_file = $event.target.files[0]" type="file" class="sr-only" accept=".csv,.txt">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">
                                    CSV up to 10MB
                                </p>
                                <p v-if="form.csv_file" class="text-sm font-bold text-emerald-600">
                                    Selected: {{ form.csv_file.name }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <button type="submit" :disabled="form.processing" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                            {{ form.processing ? 'Uploading...' : 'Process Manifest' }}
                        </button>
                    </div>
                </form>
                
                 <div v-if="form.recentlySuccessful" class="mt-4 p-4 bg-green-50 text-green-700 rounded-md text-sm text-center">
                    Upload Complete! Assets imported as Drafts.
                </div>
            </div>
        </div>
    </div>
</template>
