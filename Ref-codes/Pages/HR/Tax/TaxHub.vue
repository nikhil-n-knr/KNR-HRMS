<template>
    <Head title="Tax Intelligence Hub" />
    <MainLayout>

        <div class="py-4 md:py-5">
            <div class="w-full space-y-4 md:space-y-6 px-3 md:px-4 lg:px-6">

                <!-- Gradient Header Banner -->
                <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-indigo-700 via-violet-800 to-violet-600 px-4 md:px-6 py-5 md:py-6 text-white shadow-sm border border-white/10">
                    <div class="absolute inset-0 overflow-hidden pointer-events-none">
                        <div class="absolute -top-10 left-1/3 w-40 h-40 rounded-full bg-white/5"></div>
                        <div class="absolute -bottom-20 right-10 w-[24rem] h-[24rem] rounded-full bg-white/10"></div>
                    </div>
                    <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3 md:gap-4">
                        <div>
                            <h1 class="text-lg md:text-xl lg:text-2xl font-bold tracking-tight">Tax Intelligence Hub</h1>
                            <p class="text-xs md:text-sm text-slate-200 mt-1 md:mt-2 max-w-2xl">
                                Statutory Compliance & Yearly Tax Planning Center
                            </p>
                        </div>
                        <div class="bg-white/10 border border-white/20 px-3 md:px-4 py-2 rounded-xl backdrop-blur-sm">
                            <p class="text-xs font-bold text-white/80 uppercase">Assessment Year</p>
                            <p class="text-sm font-black text-white">2026-2027</p>
                        </div>
                    </div>
                </div>

                   <!-- TABS -->
      <div class="sticky top-0 z-30 border-b border-gray-100 bg-white px-2 py-2 shadow-sm">                       
       <div class="flex items-center gap-2 md:gap-3 overflow-x-auto">

                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                :class="[
                                    activeTab === tab.id
                                        ? 'bg-blue-50 text-blue-700 border-blue-200 shadow-sm'
                                        : 'bg-white text-gray-600 border-transparent hover:bg-gray-50',
                                    'px-3 md:px-5 h-9 md:h-11 rounded-lg border text-xs md:text-sm font-semibold transition whitespace-nowrap'
                                ]"
                            >
                                {{ tab.name }}
                            </button>

                        </div>
                    </div>

                <!-- MAIN CONTAINER -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                 

                    <!-- CONTENT -->
                    <div class="p-3 md:p-4 lg:p-6">

                        <!-- TAB 1: VERIFICATION -->
                        <div v-if="activeTab === 'verification'">

                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-lg font-bold text-gray-800">Pending Proof Verification</h3>

                            <div class="flex gap-2">
                                <button class="px-4 py-2 border border-gray-200 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-50">
                                    Filter by Dept
                                </button>

                                <button class="px-4 py-2 bg-blue-700 text-white rounded-lg text-xs font-semibold hover:bg-blue-700">
                                    Audit Bulk
                                </button>
                            </div>
                        </div>

                        <div class="overflow-hidden border border-gray-100 rounded-xl">
                            <table class="min-w-full text-sm">

                                <thead class="bg-blue-50 text-blue-800 uppercase text-xs">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Employee</th>
                                        <th class="px-4 py-2 text-left">Section</th>
                                        <th class="px-4 py-2 text-left">Planned</th>
                                        <th class="px-4 py-2 text-left">Proof Amt</th>
                                        <th class="px-4 py-2 text-right">Action</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100">

                                    <tr v-for="item in pending_verifications" :key="item.id" class="hover:bg-blue-50/40">

                                        <td class="px-4 py-2">
                                            <div class="font-semibold text-gray-900">{{ item.employee?.name }}</div>
                                            <div class="text-xs text-gray-400">{{ item.employee?.employee_code }}</div>
                                        </td>

                                        <td class="px-4 py-2">
                                            <span class="bg-gray-100 px-2 py-0.5 rounded text-xs font-bold">
                                                {{ item.section?.section_code }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-2 text-gray-500">
                                            ₹{{ item.declared_amount }}
                                        </td>

                                        <td class="px-4 py-2 text-blue-800 font-bold">
                                            ₹{{ item.proof_amount || 0 }}
                                        </td>

                                        <td class="px-4 py-2 text-right">
                                            <button class="text-blue-800 text-xs font-semibold hover:underline">
                                                Review Proof
                                            </button>
                                        </td>

                                    </tr>

                                    <tr v-if="pending_verifications.length === 0">
                                        <td colspan="5" class="text-center py-8 text-gray-400">
                                            No pending proofs found in queue.
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>

                    </div>

                    <!-- TAB 2: FORM 16 (FULL PRESERVED) -->
                    <div v-if="activeTab === 'form16'">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                            <!-- LEFT -->
                            <div class="md:col-span-2 space-y-6">

                                <div class="p-6 bg-blue-50 border border-blue-100 rounded-xl flex justify-between items-center">
                                    <div>
                                        <h4 class="font-bold text-blue-900">Batch Generation</h4>
                                        <p class="text-xs text-blue-700">Generate Part B statutory PDFs</p>
                                    </div>

                                    <button class="px-6 py-3 bg-blue-800 text-white rounded-xl text-xs font-bold hover:bg-blue-700">
                                        Start Batch Run
                                    </button>
                                </div>

                                <!-- STATUS -->
                                <div class="bg-white rounded-xl border border-gray-100">

                                    <div class="px-6 py-3 border-b text-xs font-bold text-gray-500 flex justify-between">
                                        <span>Generation Status</span>
                                        <span class="text-green-600">Updated</span>
                                    </div>

                                    <div class="p-6 space-y-4">

                                        <div 
                                            v-for="emp in employees" 
                                            :key="emp.id" 
                                            class="flex justify-between items-center group"
                                        >

                                            <div class="flex items-center gap-3">
                                                <div class="h-8 w-8 bg-gray-100 rounded flex items-center justify-center text-sm font-bold">
                                                    {{ emp.name.charAt(0) }}
                                                </div>
                                                <span class="font-semibold text-gray-700 group-hover:text-blue-700">
                                                    {{ emp.name }}
                                                </span>
                                            </div>

                                            <div class="flex items-center gap-4">

                                                <span class="text-xs text-gray-400">Not Generated</span>

                                                <!-- DOWNLOAD BUTTON (KEPT ✅) -->
                                                <button class="p-2 hover:bg-gray-100 rounded">
                                                    <ArrowDownTrayIcon class="h-4 w-4 text-gray-400" />
                                                </button>

                                            </div>

                                        </div>

                                    </div>

                                </div>

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
