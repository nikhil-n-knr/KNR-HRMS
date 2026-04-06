<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import EmployeeTaxTab from '@/Pages/Employee/Tabs/EmployeeTaxTab.vue';

defineProps({
    employee: Object,
    auth: Object
});

defineOptions({ layout: MainLayout });
</script>

<template>
    <Head title="IT Declarations" />

    <div class="min-h-screen bg-[#f8fafc] py-8 pb-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-12">
                <div class="flex items-center gap-4 mb-2">
                    <div class="h-1 bg-emerald-500 w-12 rounded-full"></div>
                    <span class="text-sm font-black text-emerald-600 uppercase tracking-[0.3em]">Finance Hub</span>
                </div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">
                    IT <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500">Declarations</span>
                </h1>
                <p class="mt-2 text-slate-500 font-medium">Manage your tax regimes, sections, and investment proofs.</p>
            </div>

            <!-- Tax Tab Component -->
            <div class="bg-white rounded-[40px] p-2 border border-slate-100 shadow-sm overflow-hidden">
                <EmployeeTaxTab :employee="employee" />
            </div>
        </div>
    </div>
</template>

<style scoped>
.min-h-screen {
    background: radial-gradient(circle at top right, #f0fdf4 0%, #f8fafc 40%);
}
</style>
