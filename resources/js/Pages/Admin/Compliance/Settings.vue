<template>
    <Head title="Compliance Settings" />
    <MainLayout>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 space-y-8">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Compliance & Statutory Settings</h2>
                    <p class="mt-1 text-sm text-gray-500">Manage PF, ESI, and Tax rules dynamically. Changes create a new rule version.</p>
                </div>
            </div>

            <!-- PF Settings -->
            <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Provident Fund (PF)</h3>
                    <span class="text-xs text-indigo-600 bg-indigo-50 px-2 py-1 rounded">Active Rule</span>
                </div>
                <div class="px-4 py-5 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Wage Ceiling -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="pf_ceiling" v-model="pf.wage_ceiling" type="number" class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm mr-3">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="pf_ceiling" class="font-medium text-gray-700">Wage Ceiling (₹)</label>
                            <p class="text-gray-500">Standard cap for EPF contribution.</p>
                        </div>
                    </div>

                    <!-- Restrict Employer Share -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="restrict_employer" v-model="pf.restrict_employer_share_to_ceiling" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="restrict_employer" class="font-medium text-gray-700">Limit Employer Share</label>
                            <p class="text-gray-500">If ON, Employer pays 12% only up to Ceiling (₹{{ pf.wage_ceiling }}). If OFF, pays on full Basic.</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end">
                     <button @click="saveRule('PF', pf)" :disabled="processing" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ processing ? 'Saving...' : 'Save PF Rules' }}
                    </button>
                </div>
            </div>

            <!-- ESI Settings -->
            <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-100">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Employee State Insurance (ESI)</h3>
                </div>
                <div class="px-4 py-5 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-start">
                         <div class="flex items-center h-5">
                            <input id="esi_limit" v-model="esi.wage_ceiling" type="number" class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm mr-3">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="esi_limit" class="font-medium text-gray-700">Wage Limit (₹)</label>
                            <p class="text-gray-500">Employees above this gross salary are exempt.</p>
                        </div>
                    </div>
                    
                     <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="esi_directors" v-model="esi.enable_for_directors" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="esi_directors" class="font-medium text-gray-700">Enable for Directors?</label>
                            <p class="text-gray-500">Include Director profiles in ESI calculation.</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end">
                     <button @click="saveRule('ESI', esi)" :disabled="processing" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ processing ? 'Saving...' : 'Save ESI Rules' }}
                    </button>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    initialRules: Object
});

const pf = ref(props.initialRules.PF || { wage_ceiling: 15000, restrict_employer_share_to_ceiling: true });
const esi = ref(props.initialRules.ESI || { wage_ceiling: 21000, enable_for_directors: false });

const processing = ref(false);

const saveRule = (component, rules) => {
    processing.value = true;
    router.post(route('admin.compliance.settings.update'), {
        component: component,
        rules: rules
    }, {
        onFinish: () => processing.value = false,
        preserveScroll: true
    });
};
</script>
