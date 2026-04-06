<template>
    <Head title="My Clearance Status" />

    <AuthenticatedLayout>
        <div class="px-6 py-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Exit Clearance</h2>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Summary Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold">Overall Status</h3>
                            <p class="text-sm text-gray-500">
                                {{ isFullyCleared ? 'All Cleared ✅' : 'Pending Actions ⏳' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <h3 class="text-lg font-bold text-red-600">Total Dues: ₹{{ totalRecovery }}</h3>
                            <p class="text-xs text-gray-500">To be deducted from FnF</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="item in clearances" :key="item.id" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4"
                        :class="{
                            'border-green-500': item.status === 'Cleared',
                            'border-yellow-500': item.status === 'Pending',
                            'border-red-500': item.status === 'Rejected'
                        }">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h4 class="font-bold text-lg">{{ item.type }}</h4>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="{
                                        'bg-green-100 text-green-800': item.status === 'Cleared',
                                        'bg-yellow-100 text-yellow-800': item.status === 'Pending',
                                        'bg-red-100 text-red-800': item.status === 'Rejected'
                                    }">
                                    {{ item.status }}
                                </span>
                            </div>
                            
                            <div class="space-y-2 text-sm">
                                <p><span class="font-medium">Remarks:</span> {{ item.remarks || 'No remarks' }}</p>
                                <p v-if="item.due_amount > 0">
                                    <span class="font-medium text-red-600">Dues: ₹{{ item.due_amount }}</span>
                                </p>
                            </div>

                            <div v-if="item.status === 'Pending'" class="mt-4 pt-4 border-t border-gray-100 text-xs text-gray-400">
                                Please contact the relevant department to resolve this.
                            </div>
                            <div v-if="item.status === 'Cleared'" class="mt-4 pt-4 border-t border-gray-100 text-xs text-gray-400">
                                Cleared on {{ new Date(item.cleared_at).toLocaleDateString() }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Empty State -->
                <div v-if="clearances.length === 0" class="text-center py-10 bg-white rounded-lg shadow">
                    <p class="text-gray-500">No clearance requests found. You are not in an active Exit process.</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/MainLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    clearances: Array,
    totalRecovery: Number
});

const isFullyCleared = computed(() => {
    return props.clearances.length > 0 && props.clearances.every(c => c.status === 'Cleared');
});
</script>
