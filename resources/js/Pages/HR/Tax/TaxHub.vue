<template>
    <Head title="Tax Intelligence Hub" />
    <MainLayout>
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Hub Header -->
            <div class="mb-8 flex justify-between items-end">
                <div>
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight">Tax Intelligence Hub</h1>
                    <p class="text-gray-500 font-medium">Statutory Compliance & Yearly Tax Planning Center</p>
                </div>
                <div class="flex space-x-2">
                    <div class="bg-indigo-50 border border-indigo-100 px-4 py-2 rounded-2xl">
                        <p class="text-sm font-bold text-indigo-500 uppercase">Assessment Year</p>
                        <p class="text-sm font-black text-indigo-900">2026-2027</p>
                    </div>
                </div>
            </div>

            <!-- Hub Tabs -->
            <div class="bg-white/80 backdrop-blur-xl border border-white/20 shadow-2xl rounded-3xl overflow-hidden transition-all duration-300">
                <div class="border-b border-gray-100 px-8 py-2 bg-gray-50/50 flex space-x-8">
                    <button 
                        v-for="tab in tabs" 
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            activeTab === tab.id 
                            ? 'border-indigo-500 text-indigo-600' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'py-4 px-1 border-b-2 font-bold text-sm transition-all duration-200 uppercase tracking-widest'
                        ]"
                    >
                        {{ tab.name }}
                    </button>
                </div>

                <div class="p-8">
                    <!-- Tab 1: Verification Queue -->
                    <div v-if="activeTab === 'verification'" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-800">Pending Proof Verification</h3>
                            <div class="flex space-x-2">
                                <button class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50">Filter by Dept</button>
                                <button class="px-4 py-2 bg-indigo-600 rounded-xl text-xs font-bold text-white shadow-lg hover:bg-indigo-700">Audit Bulk</button>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-2xl border border-gray-100">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-500 uppercase">Employee</th>
                                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-500 uppercase">Section</th>
                                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-500 uppercase">Planned</th>
                                        <th class="px-6 py-3 text-left text-sm font-bold text-gray-500 uppercase">Proof Amt</th>
                                        <th class="px-6 py-3 text-right text-sm font-bold text-gray-500 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-50">
                                    <tr v-for="item in pending_verifications" :key="item.id" class="hover:bg-indigo-50/30 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900">{{ item.employee?.name }}</div>
                                            <div class="text-sm text-gray-500">{{ item.employee?.employee_code }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="bg-gray-100 px-2 py-0.5 rounded text-sm font-black">{{ item.section?.section_code }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-500">₹{{ item.declared_amount }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">₹{{ item.proof_amount || 0 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <button class="text-indigo-600 font-bold text-xs hover:underline">Review Proof</button>
                                        </td>
                                    </tr>
                                    <tr v-if="pending_verifications.length === 0">
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-400 font-medium">No pending proofs found in queue.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: Form 16 Generator -->
                    <div v-if="activeTab === 'form16'" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="md:col-span-2 space-y-6">
                                <div class="p-6 bg-indigo-50/50 rounded-3xl border border-indigo-100 flex items-center justify-between">
                                    <div>
                                        <h4 class="font-bold text-indigo-900">Batch Generation</h4>
                                        <p class="text-xs text-indigo-700">Generate Part B statutory PDFs for selected employees.</p>
                                    </div>
                                    <button class="px-6 py-3 bg-indigo-600 rounded-2xl text-xs font-bold text-white shadow-xl shadow-indigo-100 hover:bg-indigo-700">Start Batch Run</button>
                                </div>

                                <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
                                     <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center">
                                        <span class="text-xs font-black text-gray-600 uppercase tracking-widest">Generation Status</span>
                                        <span class="text-sm font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Updated 1m ago</span>
                                     </div>
                                     <div class="p-6 space-y-4">
                                         <div v-for="emp in employees" :key="emp.id" class="flex items-center justify-between group">
                                             <div class="flex items-center space-x-3">
                                                 <div class="h-8 w-8 rounded-lg bg-gray-100 flex items-center justify-center text-sm font-bold">{{ emp.name.charAt(0) }}</div>
                                                 <span class="text-sm font-bold text-gray-700 group-hover:text-indigo-600">{{ emp.name }}</span>
                                             </div>
                                             <div class="flex items-center space-x-4">
                                                 <span class="text-sm font-bold text-gray-400">Not Generated</span>
                                                 <button class="p-2 hover:bg-gray-100 rounded-lg"><ArrowDownTrayIcon class="h-4 w-4 text-gray-400" /></button>
                                             </div>
                                         </div>
                                     </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="p-6 bg-white rounded-3xl border border-gray-100 shadow-sm space-y-4">
                                    <h4 class="text-sm font-black text-gray-900 uppercase">Statutory Control</h4>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-medium text-gray-500">AY Cycle</span>
                                            <span class="text-xs font-bold text-gray-900">2026-27</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-medium text-gray-500">Template</span>
                                            <span class="text-xs font-bold text-indigo-600">Standard Part B</span>
                                        </div>
                                    </div>
                                    <hr class="border-gray-50">
                                    <button class="w-full py-3 border-2 border-dashed border-gray-200 rounded-2xl text-sm font-bold text-gray-400 hover:border-indigo-300 hover:text-indigo-500 transition-all">
                                        Upload Digitally Signed Part A
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ArrowDownTrayIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    pending_verifications: Array,
    employees: Array,
    stats: Object
});

const activeTab = ref('verification');
const tabs = [
    { id: 'verification', name: 'Proof Audit' },
    { id: 'tax_config', name: 'Tax Config' },
    { id: 'form16', name: 'Form 16' },
    { id: 'analytics', name: 'Analytics' }
];
</script>
