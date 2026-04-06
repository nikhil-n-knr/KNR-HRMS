<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref } from 'vue';

defineOptions({ layout: MainLayout });

const form = useForm({
    name: '',
    sku: '',
    category: '',
    min_stock_level: 5,
    current_stock: 0,
    unit_cost: 0,
    unit: 'pcs'
});

const submit = () => {
    form.post(route('admin.inventory.store'), {
        onSuccess: () => form.reset()
    });
};
</script>

<template>
    <Head title="Add Inventory Item" />
    
    <div class="max-w-3xl mx-auto py-10 px-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Add New Inventory Item</h1>
        
        <form @submit.prevent="submit" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 space-y-6">
            <div class="grid grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Item Name</label>
                    <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">SKU / Code</label>
                    <input v-model="form.sku" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <input v-model="form.category" type="text" placeholder="e.g. Stationery" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Initial Stock</label>
                    <input v-model="form.current_stock" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                     <label class="block text-sm font-medium text-gray-700">Min. Stock Level</label>
                     <input v-model="form.min_stock_level" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                 <div>
                     <label class="block text-sm font-medium text-gray-700">Unit Cost ($)</label>
                     <input v-model="form.unit_cost" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                 <div>
                     <label class="block text-sm font-medium text-gray-700">Unit (e.g. pcs, box)</label>
                     <input v-model="form.unit" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-100">
                <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                    Save Item
                </button>
            </div>
        </form>
    </div>
</template>
