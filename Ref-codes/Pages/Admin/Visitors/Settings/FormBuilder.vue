<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    purposes: Array
});

const editingId = ref(null);
const form = useForm({
    form_config: {},
    is_active: true
});

const fieldOptions = [
    { key: 'phone', label: 'Phone Number' },
    { key: 'email', label: 'Email Address' },
    { key: 'photo', label: 'Photo / Selfie' },
    { key: 'company', label: 'Company Name' },
    { key: 'business_card', label: 'Business Card Scan' },
    { key: 'resume', label: 'Resume Upload' },
    { key: 'material_entry', label: 'Material Declaration' },
    { key: 'vehicle_no', label: 'Vehicle Number' }
];

const stateOptions = [
    { value: 'required', label: 'Mandatory', color: 'text-red-600' },
    { value: 'optional', label: 'Optional', color: 'text-blue-600' },
    { value: 'hidden', label: 'Hidden', color: 'text-gray-400' }
];

const editPurpose = (purpose) => {
    editingId.value = purpose.id;
    form.form_config = purpose.form_config || {};
    // Ensure all fields exist with default 'hidden' if not present
    fieldOptions.forEach(f => {
        if (!form.form_config[f.key]) {
            form.form_config[f.key] = 'hidden';
        }
    });
    form.is_active = !!purpose.is_active;
};

const save = () => {
    form.put(route('admin.visitors.settings.purposes.update', editingId.value), {
        onSuccess: () => {
            editingId.value = null;
        }
    });
};

const cancel = () => {
    editingId.value = null;
    form.reset();
};
</script>

<template>
    <MainLayout>
        <Head title="Visitor Form Configuration" />
        
        <div class="p-8 max-w-7xl mx-auto">
            <div class="mb-8">
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Smart Entry Configuration</h1>
                <p class="text-gray-500 mt-2">Customize the check-in form for each visitor type.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Purpose List -->
                <div class="lg:col-span-1 space-y-4">
                    <div 
                        v-for="purpose in purposes" 
                        :key="purpose.id"
                        @click="editPurpose(purpose)"
                        class="p-4 rounded-xl border transition-all cursor-pointer group"
                        :class="editingId === purpose.id ? 'bg-indigo-50 border-indigo-500 shadow-md ring-1 ring-indigo-500' : 'bg-white border-gray-200 hover:border-indigo-300 hover:shadow-sm'"
                    >
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-gray-900" :class="editingId === purpose.id ? 'text-indigo-900' : ''">{{ purpose.name }}</h3>
                                <p class="text-xs font-mono mt-1 text-gray-500 uppercase tracking-wider">{{ purpose.workflow_type }} WORKFLOW</p>
                            </div>
                            <div v-if="editingId === purpose.id" class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
                        </div>
                    </div>
                </div>

                <!-- Editor Panel -->
                <div class="lg:col-span-2">
                    <div v-if="editingId" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden animate-fade-in">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                            <h2 class="font-bold text-gray-800">Configure Fields</h2>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-600">Status:</span>
                                <button type="button" @click="form.is_active = !form.is_active" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none" :class="form.is_active ? 'bg-emerald-500' : 'bg-gray-200'">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" :class="form.is_active ? 'translate-x-5' : 'translate-x-0'"></span>
                                </button>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div v-for="field in fieldOptions" :key="field.key" class="border border-gray-100 rounded-xl p-4 hover:bg-gray-50 transition-colors">
                                    <label class="block text-sm font-bold text-gray-700 mb-3">{{ field.label }}</label>
                                    <div class="flex gap-2">
                                        <button 
                                            v-for="opt in stateOptions" 
                                            :key="opt.value"
                                            type="button"
                                            @click="form.form_config[field.key] = opt.value"
                                            class="flex-1 py-1.5 text-xs font-bold rounded-lg border transition-all"
                                            :class="form.form_config[field.key] === opt.value 
                                                ? 'bg-white shadow-sm border-gray-300 ' + opt.color 
                                                : 'bg-transparent border-transparent text-gray-400 hover:bg-white hover:shadow-sm'"
                                        >
                                            {{ opt.label }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                            <button @click="cancel" class="px-4 py-2 text-gray-600 font-bold hover:bg-gray-200 rounded-lg transition-colors">Cancel</button>
                            <button @click="save" :disabled="form.processing" class="px-6 py-2 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 shadow-lg shadow-indigo-200 disabled:opacity-50 transition-all">
                                {{ form.processing ? 'Saving...' : 'Save Configuration' }}
                            </button>
                        </div>
                    </div>

                    <div v-else class="h-64 flex flex-col items-center justify-center text-gray-400 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="font-medium">Select a visitor type to configure</span>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
