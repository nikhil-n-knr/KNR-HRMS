<template>
    <MainLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-800 to-teal-700">
                    Asset Management
                </h1>
                <button @click="showModal = true" class="px-4 py-2 bg-emerald-600 text-white rounded-lg shadow-sm hover:bg-emerald-700 transition">
                    + Add New Asset
                </button>
                <Link :href="route('assets.import')" class="px-4 py-2 bg-white text-emerald-700 border border-emerald-200 rounded-lg shadow-sm hover:bg-emerald-50 transition ml-2">
                    Import CSV
                </Link>
            </div>

            <!-- Search & Filters -->
            <div class="flex gap-4 mb-6">
                <input v-model="search" type="text" placeholder="Search Assets..." class="flex-1 rounded-lg border-gray-300 focus:ring-emerald-500 shadow-sm">
                
                <select v-model="status" class="rounded-lg border-gray-300 focus:ring-emerald-500 shadow-sm w-40">
                    <option value="">All Status</option>
                    <option value="Available">Available</option>
                    <option value="Assigned">Assigned</option>
                    <option value="In Service">In Service</option>
                </select>
                
                 <select v-model="category" class="rounded-lg border-gray-300 focus:ring-emerald-500 shadow-sm w-40">
                    <option value="">All Categories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.name">{{ cat.name }}</option>
                </select>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-white/50 overflow-hidden">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-emerald-50/50 text-emerald-900 border-b border-white/20">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Asset Name</th>
                            <th class="px-6 py-4 font-semibold">Details</th>
                            <th class="px-6 py-4 font-semibold">Category</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold">Assigned To</th>
                            <th class="px-6 py-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="asset in assets.data" :key="asset.id" class="hover:bg-white/60 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ asset.name }}</td>
                            <td class="px-6 py-4">
                                <div v-if="asset.is_serialized">
                                    <span class="text-xs text-gray-500 block">S/N</span>
                                    {{ asset.serial_number }}
                                </div>
                                <div v-else>
                                    <span class="text-xs text-gray-500 block">Qty</span>
                                    <span class="font-bold text-gray-800">{{ asset.quantity }}</span> (Bulk)
                                </div>
                            </td>
                            <td class="px-6 py-4">{{ asset.category?.name }}</td>
                            <td class="px-6 py-4">
                                <span :class="{
                                    'px-2 py-1 rounded-full text-xs font-semibold': true,
                                    'bg-green-100 text-green-700': asset.status === 'Available',
                                    'bg-blue-100 text-blue-700': asset.status === 'Assigned',
                                    'bg-red-100 text-red-700': asset.status === 'In_Service'
                                }">
                                    {{ asset.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                {{ asset.assignment?.user?.name || '-' }}
                            </td>
                            <td class="px-6 py-4 flex gap-2">
                                <Link :href="route('admin.assets.show', asset.id)" class="text-emerald-600 hover:text-emerald-800 font-medium">View</Link>
                                <Link :href="route('assets.label', asset.id)" class="text-gray-500 hover:text-gray-800 font-medium text-xs flex items-center gap-1 border border-gray-200 px-2 py-1 rounded">
                                   <!-- Icon -->
                                   <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4h-4v-4H8m1-4h6m-3-4v4m-5 8h10V9H7v11zm-5-4h2m0 0v-2h2m-2 2v2H2" />
                                   </svg>
                                   Label
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="assets.data.length === 0">
                           <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                              No assets found.
                           </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Create Modal -->
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm">
                <div class="bg-white rounded-2xl p-6 w-[500px] shadow-2xl">
                    <h2 class="text-lg font-bold mb-4">Add Asset</h2>
                    
                     <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            <select v-model="form.category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Asset Name</label>
                            <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div class="flex items-center gap-4">
                             <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" v-model="form.is_serialized" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                Is Serialized? (Individual ID)
                             </label>
                        </div>

                        <div v-if="form.is_serialized">
                            <label class="block text-sm font-medium text-gray-700">Serial Number</label>
                            <input v-model="form.serial_number" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div v-else>
                             <label class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input v-model="form.quantity" type="number" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Purchase Date</label>
                                <input v-model="form.purchase_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cost</label>
                                <input v-model="form.purchase_cost" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>

                     </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <button @click="showModal = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                        <button @click="submit" :disabled="form.processing" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
                            {{ form.processing ? 'Saving...' : 'Save Asset' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, watch } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    assets: Object,
    filters: Object,
    categories: Array
});

const showModal = ref(false);

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const category = ref(props.filters?.category || '');

// Debounced search
let timeout = null;
watch([search, status, category], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('assets.index'), { 
            search: search.value, 
            status: status.value, 
            category: category.value 
        }, { preserveState: true, preserveScroll: true });
    }, 300);
});

const form = useForm({
    name: '',
    category_id: props.categories?.[0]?.id || '',
    is_serialized: true,
    serial_number: '',
    quantity: 1,
    purchase_date: '',
    purchase_cost: ''
});

const submit = () => {
    form.post(route('assets.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
            form.category_id = props.categories?.[0]?.id || '';
        }
    });
};
</script>
