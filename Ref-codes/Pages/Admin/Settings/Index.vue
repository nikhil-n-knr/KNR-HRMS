<template>
    <Head title="System Settings" />
    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                System Configurations
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Payslip Settings -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Payslip Configuration</h3>
                        
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <!-- Header HTML -->
                            <div>
                                <InputLabel for="header" value="Payslip Header (HTML)" />
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <textarea 
                                        id="header" 
                                        v-model="form.payslip_header" 
                                        rows="4" 
                                        class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-md sm:text-sm border-gray-300"
                                        placeholder="<div style='text-align:center'><h1>Company Name</h1></div>"
                                    ></textarea>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Supports Basic HTML. Leaving empty uses default App Name.</p>
                            </div>

                            <!-- Footer HTML -->
                             <div>
                                <InputLabel for="footer" value="Payslip Footer (HTML)" />
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <textarea 
                                        id="footer" 
                                        v-model="form.payslip_footer" 
                                        rows="3" 
                                        class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-md sm:text-sm border-gray-300"
                                        placeholder="<p>Confidential Document</p>"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Watermark Image -->
                             <div>
                                <InputLabel for="watermark" value="Watermark Image" />
                                <div class="mt-2 flex items-center gap-x-3">
                                    <div v-if="props.settings.payslip_watermark" class="h-16 w-16 overflow-hidden rounded bg-gray-100 border border-gray-200 flex items-center justify-center">
                                        <img :src="props.settings.payslip_watermark" alt="Current" class="h-full w-full object-contain opacity-50" />
                                    </div>
                                    <input 
                                        type="file" 
                                        @change="e => form.payslip_watermark = e.target.files[0]" 
                                        class="block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-full file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100"
                                    />
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Recommended: Transparent PNG, Low Opacity.</p>
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Save Changes</PrimaryButton>
                                <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                                </Transition>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    settings: Object
});

const form = useForm({
    payslip_header: props.settings.payslip_header || '',
    payslip_footer: props.settings.payslip_footer || '',
    payslip_watermark: null, // File
});

const submit = () => {
    form.post(route('admin.settings.store'), {
        preserveScroll: true,
        forceFormData: true, // Required for file upload
    });
};
</script>
