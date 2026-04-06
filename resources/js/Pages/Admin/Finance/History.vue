<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    BanknotesIcon, 
    WrenchScrewdriverIcon,
    ShoppingBagIcon,
    CubeIcon
} from '@heroicons/vue/24/outline'; // v2

defineOptions({ layout: MainLayout });

const props = defineProps({
    ledger: Array,
    stats: Object
});

const getIcon = (type) => {
    if (type === 'Purchase') return ShoppingBagIcon;
    if (type === 'Maintenance') return WrenchScrewdriverIcon;
    if (type === 'Consumption') return CubeIcon;
    return BanknotesIcon;
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString() + ' ' + new Date(dateStr).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
};
</script>

<template>
    <Head title="Transaction Ledger" />

    <div class="p-8 max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                 <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <BanknotesIcon class="h-8 w-8 text-indigo-600" />
                    Transaction Ledger
                </h1>
                <p class="text-sm text-gray-500 mt-1">Unified history of purchases, repairs, and consumption.</p>
            </div>
            
            <div class="px-6 py-3 bg-white border border-gray-200 rounded-xl shadow-sm text-right">
                <span class="block text-xs font-bold text-gray-400 uppercase">Total Tracked Spend (Recent)</span>
                <span class="block text-2xl font-bold text-gray-900">${{ stats.total_spend.toLocaleString(undefined, {minimumFractionDigits: 2}) }}</span>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-50 border-b border-gray-100 uppercase tracking-wider text-xs font-semibold text-gray-500">
                        <tr>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4">Description</th>
                            <th class="px-6 py-4 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in ledger" :key="item.type + item.reference_id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-gray-500 font-mono text-xs">
                                {{ formatDate(item.date) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="flex items-center gap-2 font-bold" :class="item.color">
                                    <component :is="getIcon(item.type)" class="h-4 w-4" />
                                    {{ item.type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-900">
                                {{ item.description }}
                            </td>
                            <td class="px-6 py-4 text-right font-mono font-medium text-gray-700">
                                ${{ parseFloat(item.amount).toLocaleString(undefined, {minimumFractionDigits: 2}) }}
                            </td>
                        </tr>
                         <tr v-if="ledger.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                No financial events recorded yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>
