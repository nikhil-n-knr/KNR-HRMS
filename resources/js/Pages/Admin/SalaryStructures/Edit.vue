<template>
    <Head :title="form.id ? 'Edit Structure' : 'Create Salary Structure'" />
    <MainLayout>
        <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ form.id ? 'Edit' : 'Create' }} Salary Structure</h1>
            
            <div class="bg-white shadow rounded-lg p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Info -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Structure Name</label>
                            <input type="text" v-model="form.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g. India Standard">
                            <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <input type="text" v-model="form.description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Optional notes">
                        </div>
                    </div>

                    <!-- Tax Configuration -->
                    <div class="border-t border-gray-200 pt-6">
                       <h3 class="text-lg font-medium text-gray-900 mb-4">Tax Configuration</h3>
                       <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                           <div>
                               <label class="block text-sm font-medium text-gray-700">TDS Calculation Method</label>
                               <select v-model="form.tds_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="Manual">None / Manual (Admin Entry)</option>
                                    <option value="Auto_New">Auto-Calculate (New Regime)</option>
                                    <option value="Auto_Old">Auto-Calculate (Old Regime)</option>
                                    <option value="Employee_Choice">Employee Choice (Portal Declaration)</option>
                               </select>
                               <p class="mt-1 text-xs text-gray-500">Determines how Income Tax is computed for employees assigned to this structure.</p>
                           </div>
                       </div>
                    </div>

                    <!-- Components Builder -->
                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Salary Components</h3>
                            <button type="button" @click="addComponent" class="text-sm text-indigo-600 hover:text-indigo-900 font-semibold flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Add Component
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div v-for="(comp, index) in form.components" :key="index" class="flex items-start space-x-4 p-4 bg-gray-50 rounded-md border border-gray-200">
                                <div class="flex-1 grid grid-cols-1 gap-4 sm:grid-cols-4">
                                    <div class="sm:col-span-1">
                                        <label class="block text-xs font-medium text-gray-500 uppercase">Name</label>
                                        <input type="text" v-model="comp.name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-8" placeholder="Basic">
                                    </div>
                                    <div class="sm:col-span-1">
                                        <label class="block text-xs font-medium text-gray-500 uppercase">Type</label>
                                        <select v-model="comp.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-8">
                                            <option value="earning">Earning</option>
                                            <option value="deduction">Deduction</option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <label class="block text-xs font-medium text-gray-500 uppercase">Calc Type</label>
                                        <select v-model="comp.calculation_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-8">
                                            <option value="percentage">% of CTC</option>
                                            <option value="fixed">Fixed Amount</option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <label class="block text-xs font-medium text-gray-500 uppercase">Value</label>
                                        <input type="number" step="0.01" v-model="comp.value" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-8">
                                    </div>
                                </div>
                                <button type="button" @click="removeComponent(index)" class="text-red-400 hover:text-red-600 mt-6">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                            
                             <div v-if="form.components.length === 0" class="text-center py-6 text-gray-400 italic bg-gray-50 rounded-md border border-dashed border-gray-300">
                                No components added. Add at least one (e.g. Basic Salary).
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 flex justify-end space-x-3">
                        <Link :href="route('admin.salary-structures.index')" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</Link>
                        <button type="submit" :disabled="form.processing" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                            {{ form.id ? 'Update' : 'Create' }} Structure
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
    structure: Object,
    components: Array
});

const form = useForm({
    id: props.structure?.id || null,
    name: props.structure?.name || '',
    description: props.structure?.description || '',
    tds_method: props.structure?.tds_method || 'Auto_New',
    components: props.components && props.components.length > 0 ? props.components.map(c => ({
        name: c.name,
        type: c.type,
        calculation_type: c.calculation_type,
        value: c.value
    })) : [
        { name: 'Basic', type: 'earning', calculation_type: 'percentage', value: 40 },
        { name: 'HRA', type: 'earning', calculation_type: 'percentage', value: 20 },
        { name: 'Special Allowance', type: 'earning', calculation_type: 'percentage', value: 30 },
        { name: 'PF', type: 'deduction', calculation_type: 'percentage', value: 1.8 }
    ] // Default template
});

const addComponent = () => {
    form.components.push({
        name: '',
        type: 'earning',
        calculation_type: 'fixed',
        value: 0
    });
};

const removeComponent = (index) => {
    form.components.splice(index, 1);
};

const submit = () => {
    if (form.id) {
        form.put(route('admin.salary-structures.update', form.id));
    } else {
        form.post(route('admin.salary-structures.store'));
    }
};
</script>
