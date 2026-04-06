<template>
    <MainLayout>
        <div class="max-w-3xl mx-auto space-y-6">
            <h1 class="text-2xl font-bold text-gray-800">Bulk Asset Import</h1>
            
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <div class="mb-6">
                    <p class="text-sm text-gray-500 mb-2">Upload a CSV file with the following columns:</p>
                    <div class="bg-gray-50 p-3 rounded font-mono text-xs text-gray-600">
                        Name, Serial Number, Category, Purchase Cost, Purchase Date (YYYY-MM-DD)
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:bg-gray-50 transition" @dragover.prevent @drop.prevent="handleDrop">
                        <div v-if="!form.file">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <p class="text-gray-600">Drag & Drop your CSV here or</p>
                            <label class="cursor-pointer text-emerald-600 font-bold hover:underline">
                                Browse File
                                <input type="file" @change="handleFile" class="hidden" accept=".csv">
                            </label>
                        </div>
                        <div v-else class="flex items-center justify-between bg-emerald-50 p-4 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="p-2 bg-emerald-100 rounded text-emerald-600 font-bold">CSV</span>
                                <span class="text-emerald-800 font-medium">{{ form.file.name }}</span>
                            </div>
                            <button @click="form.file = null" type="button" class="text-red-500 hover:text-red-700">Remove</button>
                        </div>
                    </div>
                    
                    <p v-if="form.errors.file" class="text-red-500 text-sm">{{ form.errors.file }}</p>

                    <div class="flex justify-end gap-3 pt-4">
                        <Link :href="route('admin.assets.index')" class="px-5 py-2.5 text-gray-600 hover:bg-gray-50 rounded-lg">Cancel</Link>
                        <button type="submit" :disabled="form.processing || !form.file" class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700 shadow-sm disabled:opacity-50 flex items-center gap-2">
                             <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                               <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                               <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                             </svg>
                            Start Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    file: null
});

const handleFile = (e) => {
    form.file = e.target.files[0];
};

const handleDrop = (e) => {
    form.file = e.dataTransfer.files[0];
};

const submit = () => {
    form.post(route('assets.import.store'));
};
</script>
