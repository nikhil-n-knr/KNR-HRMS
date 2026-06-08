<template>
    <Head title="Tax Reports" />
    <MainLayout>
        <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Tax Reports & Exports</h1>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Master Report -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-10 w-10 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.586l4 4a1 1 0 01.586 1.414V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-2 py-1 rounded">Recommended</span>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg">Master Verification Report</h3>
                    <p class="text-sm text-gray-500 mt-2 mb-6">
                        Complete download of all claims (HRA, 80C, etc.) with status, claimed amount vs verified amount. Use this for final payroll processing.
                    </p>
                    <a :href="route('hr.tax.reports.download', { type: 'master' })" class="inline-flex justify-center w-full px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-bold hover:bg-black transition">
                        Download CSV
                    </a>
                </div>

                <!-- Defaulters -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-10 w-10 bg-red-100 text-red-600 rounded-lg flex items-center justify-center">
                             <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        </div>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg">Non-Submission List (Defaulters)</h3>
                    <p class="text-sm text-gray-500 mt-2 mb-6">
                        List of employees who declared investments but have not uploaded specific proofs yet. Use this to send follow-up emails.
                    </p>
                    <a :href="route('hr.tax.reports.download', { type: 'defaulters' })" class="inline-flex justify-center w-full px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg text-sm font-bold hover:bg-gray-50 transition">
                         Download List
                    </a>
                </div>
                
                <!-- Rejection Analysis (Placeholder for now) -->
                 <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 opacity-60">
                    <div class="flex items-center justify-between mb-4">
                         <div class="h-10 w-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /></svg>
                        </div>
                        <span class="text-xs font-bold text-gray-400 uppercase">Coming Soon</span>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg">Rejection Analysis</h3>
                    <p class="text-sm text-gray-500 mt-2 mb-6">
                        Breakdown of rejection reasons (e.g., 'Invalid Format', 'Blurry') to identify common employee errors.
                    </p>
                    <button disabled class="w-full px-4 py-2 bg-gray-100 text-gray-400 rounded-lg text-sm font-bold cursor-not-allowed">
                        Export Analysis
                    </button>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
</script>
