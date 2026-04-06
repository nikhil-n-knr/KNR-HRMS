<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

defineProps({
    appraisals: Array,
    cycle: Object
});
</script>

<template>
    <Head title="Team Reviews" />

    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Team Appraisals: {{ cycle?.name || 'No Active Cycle' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="cycle" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <div v-if="appraisals.length === 0" class="text-center py-10 text-gray-500">
                            No active appraisals found for your direct reports.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Designation</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Self Rating</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stage</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="appraisal in appraisals" :key="appraisal.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ appraisal.employee.first_name }} {{ appraisal.employee.last_name }}
                                            </div>
                                            <div class="text-sm text-gray-500">{{ appraisal.employee.employee_code }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ appraisal.employee.designation }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                            {{ appraisal.self_rating || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="{
                                                    'bg-yellow-100 text-yellow-800': appraisal.stage === 'Self Review',
                                                    'bg-blue-100 text-blue-800': appraisal.stage === 'Manager Review',
                                                    'bg-purple-100 text-purple-800': appraisal.stage === 'HR Review',
                                                    'bg-green-100 text-green-800': appraisal.stage === 'Closed'
                                                }">
                                                {{ appraisal.stage }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('performance.appraisals.show', appraisal.id)" 
                                                  class="text-indigo-600 hover:text-indigo-900">
                                                {{ appraisal.stage === 'Manager Review' ? 'Review Now' : 'View Details' }}
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div v-else class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-6 rounded-lg text-center">
                    <strong class="text-lg">No Active Appraisal Cycle</strong>
                    <p class="mt-2">Please contact HR to initiate a new cycle.</p>
                </div>

            </div>
        </div>
    </MainLayout>
</template>
