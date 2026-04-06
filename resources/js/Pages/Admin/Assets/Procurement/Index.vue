<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref } from 'vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    purchase_requests: Object,
    vendors: Array
});

// --- New PO Modal ---
const showModal = ref(false);
const form = useForm({
    vendor_id: '',
    items: [{ name: '', quantity: 1, unit_cost: 0 }]
});

const addItem = () => form.items.push({ name: '', quantity: 1, unit_cost: 0 });
const removeItem = (i) => form.items.splice(i, 1);

const totalCost = () => form.items.reduce((sum, i) => sum + (parseFloat(i.unit_cost) || 0) * (parseInt(i.quantity) || 0), 0);

const submit = () => {
    form.post(route('procurement.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
            form.items = [{ name: '', quantity: 1, unit_cost: 0 }];
        }
    });
};

const statusColor = (status) => ({
    'Draft':    'bg-gray-100 text-gray-700',
    'Approved': 'bg-blue-100 text-blue-700',
    'Ordered':  'bg-amber-100 text-amber-800',
    'Received': 'bg-emerald-100 text-emerald-700',
}[status] ?? 'bg-gray-100 text-gray-700');
</script>

<template>
    <Head title="Procurement" />

    <div class="min-h-screen bg-gray-50 p-8">

        <!-- Header -->
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Procurement</h1>
                    <p class="text-sm text-gray-500 mt-1">Manage Purchase Orders and Vendor Requests</p>
                </div>
                <button @click="showModal = true"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-xl shadow-sm hover:bg-indigo-700 font-semibold text-sm transition-colors flex items-center gap-2">
                    <span class="text-lg leading-none">+</span>
                    New Purchase Request
                </button>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
                <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                    <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Total Orders</p>
                    <p class="text-3xl font-extrabold text-gray-900">{{ purchase_requests.total }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                    <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Draft</p>
                    <p class="text-3xl font-extrabold text-amber-600">
                        {{ purchase_requests.data.filter(r => r.status === 'Draft' || r.status === 'Pending').length }}
                    </p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                    <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Approved</p>
                    <p class="text-3xl font-extrabold text-blue-600">
                        {{ purchase_requests.data.filter(r => r.status === 'Approved').length }}
                    </p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                    <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Total Value</p>
                    <p class="text-3xl font-extrabold text-emerald-600">
                        ${{ purchase_requests.data.reduce((s, r) => s + parseFloat(r.total_cost || 0), 0).toLocaleString() }}
                    </p>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                    <h2 class="font-bold text-gray-800">Purchase Orders</h2>
                    <span class="text-xs text-gray-400">{{ purchase_requests.total }} total records</span>
                </div>

                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">PO #</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Vendor</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Total Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">GST</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Created By</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="pr in purchase_requests.data" :key="pr.id" class="hover:bg-indigo-50/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-bold text-gray-700">
                                {{ pr.po_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                {{ pr.vendor?.name || '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ pr.items?.length || 0 }} line items
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                ${{ Number(pr.total_cost || 0).toLocaleString() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${{ Number(pr.gst_amount || 0).toLocaleString() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="statusColor(pr.status)">
                                    {{ pr.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ pr.created_by?.name || 'System' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <Link v-if="pr.status === 'Received'"
                                    :href="route('procurement.convert', pr.id)"
                                    method="post" as="button"
                                    class="px-3 py-1 text-xs font-bold text-purple-700 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100">
                                    → Create Assets
                                </Link>
                                <span v-else class="text-xs text-gray-300">—</span>
                            </td>
                        </tr>
                        <tr v-if="purchase_requests.data.length === 0">
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="text-4xl mb-3">📋</div>
                                <p class="text-gray-500 font-medium">No purchase requests yet.</p>
                                <p class="text-sm text-gray-400 mt-1">Click "New Purchase Request" to raise your first PO.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="purchase_requests.last_page > 1" class="px-6 py-4 border-t border-gray-100 flex justify-between items-center">
                    <span class="text-sm text-gray-500">
                        Page {{ purchase_requests.current_page }} of {{ purchase_requests.last_page }}
                    </span>
                    <div class="flex gap-2">
                        <Link v-if="purchase_requests.prev_page_url" :href="purchase_requests.prev_page_url"
                            class="px-3 py-1 text-sm bg-white border border-gray-200 rounded-lg hover:bg-gray-50">← Prev</Link>
                        <Link v-if="purchase_requests.next_page_url" :href="purchase_requests.next_page_url"
                            class="px-3 py-1 text-sm bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Next →</Link>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New PO Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">New Purchase Request</h2>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>
            <form @submit.prevent="submit" class="p-6 space-y-5">
                <!-- Vendor -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Vendor</label>
                    <select v-model="form.vendor_id" required class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 text-sm">
                        <option value="" disabled>Select a vendor...</option>
                        <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
                    </select>
                    <span v-if="form.errors.vendor_id" class="text-red-500 text-xs mt-1">{{ form.errors.vendor_id }}</span>
                </div>

                <!-- Line Items -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-semibold text-gray-700">Line Items</label>
                        <button type="button" @click="addItem"
                            class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">+ Add Item</button>
                    </div>
                    <div v-for="(item, i) in form.items" :key="i" class="flex gap-3 items-start mb-3">
                        <input v-model="item.name" placeholder="Item name" required
                            class="flex-1 rounded-lg border-gray-300 shadow-sm text-sm focus:ring-indigo-500">
                        <input v-model.number="item.quantity" type="number" min="1" placeholder="Qty" required
                            class="w-20 rounded-lg border-gray-300 shadow-sm text-sm focus:ring-indigo-500 text-center">
                        <input v-model.number="item.unit_cost" type="number" step="0.01" min="0" placeholder="Unit $" required
                            class="w-28 rounded-lg border-gray-300 shadow-sm text-sm focus:ring-indigo-500 text-right">
                        <button v-if="form.items.length > 1" type="button" @click="removeItem(i)"
                            class="text-red-400 hover:text-red-600 mt-1 text-lg leading-none" title="Remove">&times;</button>
                    </div>
                </div>

                <!-- Estimated Total -->
                <div class="flex justify-between items-center bg-gray-50 rounded-lg px-4 py-3 border border-gray-200">
                    <span class="text-sm text-gray-600 font-semibold">Estimated Total (excl. GST)</span>
                    <span class="text-lg font-extrabold text-gray-900">${{ totalCost().toLocaleString() }}</span>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false"
                        class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                    <button type="submit" :disabled="form.processing"
                        class="px-5 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 shadow-sm disabled:opacity-50">
                        {{ form.processing ? 'Submitting...' : 'Create Purchase Request' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
