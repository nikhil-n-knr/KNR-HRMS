<template>
    <MainLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">My Assigned Assets</h1>
                    <p class="text-gray-500">Equipment currently in your possession.</p>
                </div>
                <Link :href="route('employee.assets.create')" class="px-4 py-2 bg-emerald-600 text-white rounded-lg shadow-sm hover:bg-emerald-700 font-medium text-sm">
                    + Request New Asset
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="assignment in assignments" :key="assignment.id" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="text-xs font-bold px-2 py-1 bg-blue-50 text-blue-600 rounded-full border border-blue-100">
                            {{ assignment.asset.category?.name || 'Asset' }}
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-gray-900">{{ assignment.asset.name }}</h3>
                    <p class="text-sm font-mono text-gray-400 mb-4">{{ assignment.asset.serial_number }}</p>

                    <div class="border-t border-gray-100 pt-4 flex gap-2">
                        <button class="flex-1 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">
                            Report Issue
                        </button>
                         <button class="flex-1 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-lg transition border border-gray-200">
                            Details
                        </button>
                    </div>
                </div>

                <div v-if="assignments.length === 0" class="col-span-full py-12 text-center text-gray-400 bg-gray-50 rounded-xl border-dashed border-2 border-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p>You have no assigned assets.</p>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';

defineProps({
    assignments: Array
});
</script>
