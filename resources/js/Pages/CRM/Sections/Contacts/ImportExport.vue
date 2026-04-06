<template>
    <div class="max-w-4xl mx-auto space-y-8">
        
        <!-- Import Section -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-file-import mr-3 text-blue-500"></i> Import Data
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">Upload CSV or Excel files to bulk create records.</p>
                    </div>
                    <a :href="route('crm.contacts.imports.sample')" download class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-2 rounded-lg border border-blue-100 flex items-center transition-all">
                        <i class="fas fa-download mr-2"></i> DOWNLOAD SAMPLE TEMPLATE
                    </a>
                </div>
            </div>
            <div class="p-6 space-y-6">
                <!-- Dropzone -->
                 <div
                    @click="$refs.fileInput.click()"
                    @dragover.prevent
                    @drop.prevent="handleDrop"
                    class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-blue-500 transition-colors cursor-pointer bg-gray-50 hover:bg-white relative"
                >
                    <input 
                        type="file" 
                        ref="fileInput" 
                        class="hidden" 
                        accept=".csv,.txt,.xls,.xlsx"
                        @change="handleFileSelect"
                    >
                    <div v-if="form.processing" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75">
                         <i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i>
                    </div>
                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-sm font-medium text-gray-900">Click to upload or drag and drop</h3>
                    <p class="text-xs text-gray-500 mt-1">CSV, XLS, XLSX up to 10MB</p>
                    <p v-if="form.errors.file" class="text-red-500 text-xs mt-2">{{ form.errors.file }}</p>
                </div>

                <!-- Recent Imports -->
                <div>
                     <h3 class="text-sm font-medium text-gray-700 mb-3">Recent Imports</h3>
                     <div class="bg-white border border-gray-200 rounded-md overflow-hidden">
                        <ul class="divide-y divide-gray-200">
                             <li v-if="imports.length === 0" class="px-4 py-3 text-sm text-gray-500 text-center">
                                No recent imports found.
                            </li>
                            <li v-for="imp in imports" :key="imp.id" class="px-4 py-3 hover:bg-gray-50 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 rounded bg-green-100 flex items-center justify-center text-green-600">
                                        <i class="fas fa-file-csv"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ imp.file_name }}</p>
                                        <p class="text-xs text-gray-500">{{ new Date(imp.created_at).toLocaleString() }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <span :class="[
                                        'px-2 py-0.5 rounded text-xs font-medium mr-3',
                                        getStatusClass(imp.status)
                                    ]">
                                        {{ imp.status }}
                                    </span>
                                    <span class="text-sm text-gray-500">{{ imp.total_records }} records</span>
                                </div>
                            </li>
                        </ul>
                     </div>
                </div>
            </div>
            <!-- (Content remains same) -->
        </div>
        <!-- (Export Section remains same) -->
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    imports: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    file: null,
});

const fileInput = ref(null);

const handleFileSelect = (e) => {
    const file = e.target.files[0];
    if (file) {
        uploadFile(file);
    }
};

const handleDrop = (e) => {
    const file = e.dataTransfer.files[0];
    if (file) {
        uploadFile(file);
    }
};

const uploadFile = (file) => {
    form.file = file;
    form.post(route('crm.contacts.imports.store'), {
        preserveScroll: true,
        onSuccess: () => {
             form.reset();
             // Optionally show toast
        },
    });
};

const getStatusClass = (status) => {
    switch (status) {
        case 'completed': return 'bg-green-100 text-green-800';
        case 'failed': return 'bg-red-100 text-red-800';
        case 'processing': return 'bg-blue-100 text-blue-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};
</script>
