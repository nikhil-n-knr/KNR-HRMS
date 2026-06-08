<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref } from 'vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    asset: Object,
    isAssignedToMe: Boolean
});

const reportForm = useForm({
    description: '',
    priority: 'Normal'
});

const showReportModal = ref(false);

const submitReport = () => {
    // In a real app, this would hit a dedicated endpoint
    // reportForm.post(...)
    showReportModal.value = false;
    alert("Issue Reported (Simulation)");
};

</script>

<template>
  <Head :title="asset.name" />

  <div class="p-4 bg-gray-50 min-h-screen">
    <!-- Mobile Header -->
    <div class="mb-6 flex justify-between items-center">
        <Link :href="route('employee.assets.index')" class="text-indigo-600 font-medium text-sm">&larr; My Assets</Link>
        <div v-if="asset.status === 'Available'" class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
            Available
        </div>
        <div v-else-if="asset.status === 'Assigned'" class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
            Assigned
        </div>
    </div>

    <!-- Asset Card -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden p-6 text-center">
         <!-- Icon/Image Placeholder -->
        <div class="w-24 h-24 bg-gray-100 rounded-full mx-auto flex items-center justify-center mb-4">
             <span class="text-3xl">💻</span>
        </div>

        <h1 class="text-xl font-bold text-gray-900">{{ asset.name }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ asset.serial_number }}</p>
        <p class="text-xs text-indigo-600 font-medium mt-1">{{ asset.category.name }}</p>

        <!-- Actions -->
        <div class="mt-6 space-y-3">
             <div v-if="isAssignedToMe">
                 <button @click="showReportModal = true" class="w-full py-3 bg-red-50 text-red-600 font-medium rounded-lg border border-red-100 hover:bg-red-100">
                    Report Issue
                 </button>
             </div>
             <div v-else-if="asset.status === 'Available'">
                 <button class="w-full py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700">
                    Request This Asset
                 </button>
             </div>
             <div v-else>
                 <p class="text-sm text-gray-400 italic">This asset is currently {{ asset.status }}</p>
             </div>
        </div>
    </div>

    <!-- Details -->
    <div class="mt-6 bg-white rounded-xl shadow-sm p-4">
        <h3 class="text-sm font-semibold text-gray-900 mb-3 border-b pb-2">Specs & Details</h3>
        <div class="space-y-2 text-sm">
             <div class="flex justify-between">
                <span class="text-gray-500">Model</span>
                <span class="font-medium text-gray-900">{{ asset.name }}</span>
             </div>
             <div class="flex justify-between">
                <span class="text-gray-500">Warranty</span>
                <span class="font-medium text-gray-900">{{ asset.warranty_expiry || 'N/A' }}</span>
             </div>
        </div>
    </div>
    
  </div>
</template>
