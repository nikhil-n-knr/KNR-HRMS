<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

defineProps({
    payslips: Object,
    error: String
});
</script>

<template>
    <MainLayout>
        <Head title="My Payslips" />

        <div class="min-h-screen bg-gray-50/50 flex flex-col">
            <!-- Header -->
            <div class="px-8 py-6 bg-white border-b border-gray-200 sticky top-0 z-10">
                 <h1 class="text-2xl font-black text-gray-900 tracking-tight">My Payslips</h1>
                 <p class="text-sm text-gray-500 font-medium">View and download your monthly salary slips.</p>
            </div>

            <div class="p-8">
                <!-- Error State -->
                <div v-if="error" class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium">{{ error }}</span>
                </div>

                <!-- Empty State -->
                <div v-else-if="payslips.data.length === 0" class="flex flex-col items-center justify-center p-12 bg-white rounded-2xl border border-gray-100 shadow-sm text-center">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.586l5.414 5.414a1 1 0 01.586 1.414V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">No Payslips Yet</h3>
                    <p class="text-gray-500 mt-1 max-w-sm">Your payslips will appear here once payroll is processed for the month.</p>
                </div>

                <!-- Payslip Grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="slip in payslips.data" :key="slip.id" class="group bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden relative">
                        <!-- Left Border Decor -->
                        <div class="absolute inset-y-0 left-0 w-1.5 bg-indigo-500 group-hover:bg-indigo-600 transition-colors"></div>

                        <div class="p-5 pl-7">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ slip.month_name }} {{ slip.year }}</h3>
                                    <p class="text-xs text-gray-400 font-mono mt-0.5">{{ slip.payslip_number }}</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    Paid
                                </span>
                            </div>

                            <div class="flex items-baseline gap-1 mb-4">
                                <span class="text-sm font-medium text-gray-500">Net Pay:</span>
                                <span class="text-2xl font-black text-gray-900 tracking-tight">₹{{ slip.net_pay.toLocaleString('en-IN') }}</span>
                            </div>

                            <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-xs text-gray-400 font-medium">Generated: {{ slip.generated_at }}</span>
                                
                                <a :href="slip.download_url" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold text-sm transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Download PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                 <div v-if="payslips.links && payslips.links.length > 3" class="mt-8 flex justify-center gap-2">
                    <component 
                        :is="link.url ? Link : 'span'" 
                        v-for="(link, i) in payslips.links" 
                        :key="i"
                        :href="link.url"
                        v-html="link.label"
                        class="px-3 py-1 rounded-lg text-sm font-bold transition-all"
                        :class="[
                            link.active ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-100',
                            !link.url ? 'opacity-50 cursor-default' : ''
                        ]"
                    />
                </div>
            </div>
        </div>
    </MainLayout>
</template>
