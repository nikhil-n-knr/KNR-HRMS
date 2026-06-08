<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    CreditCardIcon, 
    PlusIcon, 
    PencilSquareIcon, 
    ChartBarIcon, 
    ShieldCheckIcon,
    Bars3CenterLeftIcon,
    CurrencyRupeeIcon,
    AcademicCapIcon
} from '@heroicons/vue/24/outline';

defineProps({
    structures: Object
});
</script>

<template>
    <Head title="Salary Architect Console" />

    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 md:px-2 py-6 space-y-10 font-['Inter']">

<!-- HEADER -->
<div class="mb-6">
    <div
        class="relative overflow-hidden rounded-[20px] bg-gradient-to-r from-indigo-700 via-violet-800 to-violet-600 px-8 md:px-6 py-6"
    >

        <!-- Background Circles -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-10 left-1/3 w-40 h-40 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-20 right-10 w-[24rem] h-[24rem] rounded-full bg-white/10"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8">

            <!-- LEFT -->
            <div>
                <p class="uppercase tracking-[0.2em] text-white/60 text-xs font-bold mb-2">
                    Administration
                </p>

                <h1 class="text-4xl md:text-4xl font-black text-white leading-tight">
                    Structure Architect
                </h1>

                <p class="mt-4 text-white/70 text-base md:text-lg font-medium max-w-2xl">
                    Define global salary templates & compensation blueprints
                </p>
            </div>

            <!-- BUTTON -->
            <Link 
                :href="route('admin.salary-structures.create')" 
                class="h-14 px-8 rounded-xl bg-white text-slate-900 font-bold uppercase tracking-[0.15em] flex items-center gap-3 shadow-2xl hover:scale-105 hover:bg-indigo-50 transition-all duration-300"
            >
                <PlusIcon class="w-5 h-5 text-indigo-600" />
                New Structure
            </Link>

        </div>

    </div>
</div>

            <!-- METRIC CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div 
                    v-for="stat in [
                        { label: 'Active Templates', val: structures.data?.filter(s => s.is_active).length || 0, sub: 'Operational Blueprints', icon: ShieldCheckIcon, color: 'emerald' },
                        { label: 'Global Delta', val: '12.4%', sub: 'Avg Variance Node', icon: ChartBarIcon, color: 'indigo' },
                        { label: 'Pending Updates', val: '0', sub: 'Lifecycle Sync Status', icon: AcademicCapIcon, color: 'amber' }
                    ]"
                    :key="stat.label"
                    class="group relative overflow-hidden bg-white rounded-[28px] border border-slate-200 shadow-lg hover:shadow-2xl transition-all duration-500 p-7 hover:-translate-y-1"
                >

                    <div class="absolute top-0 right-0 w-32 h-32 bg-slate-100 rounded-full blur-3xl opacity-50"></div>

                    <div class="relative z-10 flex items-center gap-5">

                        <div 
                            :class="`
                                w-16 h-16 rounded-2xl flex items-center justify-center shadow-inner
                                ${stat.color === 'emerald' ? 'bg-emerald-100 text-emerald-700' : ''}
                                ${stat.color === 'indigo' ? 'bg-indigo-100 text-indigo-700' : ''}
                                ${stat.color === 'amber' ? 'bg-amber-100 text-amber-700' : ''}
                            `"
                        >
                            <component :is="stat.icon" class="w-8 h-8" />
                        </div>

                        <div>
                            <div class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400 mb-2">
                                {{ stat.label }}
                            </div>

                            <div class="text-3xl font-black text-slate-900">
                                {{ stat.val }}
                            </div>

                            <div class="text-sm text-slate-500 font-medium mt-1">
                                {{ stat.sub }}
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <!-- MAIN TABLE -->
            <div class="bg-white rounded-[32px] border border-slate-200 shadow-2xl overflow-hidden">

                <!-- HEADER -->
                <div class="px-8 py-6 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white flex items-center justify-between">

                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-100 flex items-center justify-center">
                            <Bars3CenterLeftIcon class="w-6 h-6 text-indigo-700" />
                        </div>

                        <div>
                            <h3 class="text-lg font-black text-slate-900">
                                Template Manifest
                            </h3>

                            <p class="text-sm text-slate-500">
                                Salary structure registry & compensation mappings
                            </p>
                        </div>
                    </div>

                    <div class="px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold uppercase tracking-[0.15em] border border-emerald-200">
                        Live Sync Enabled
                    </div>

                </div>

                <!-- TABLE -->
                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead>
                            <tr class="bg-blue-50">

                                <th class="px-8 py-5 text-left text-xs font-bold uppercase tracking-[0.2em] text-blue-800">
                                    Structure
                                </th>

                                <th class="px-8 py-5 text-left text-xs font-bold uppercase tracking-[0.2em] text-blue-800">
                                    Components
                                </th>

                                <th class="px-8 py-5 text-center text-xs font-bold uppercase tracking-[0.2em] text-blue-800">
                                    Status
                                </th>

                                <th class="px-8 py-5 text-right text-xs font-bold uppercase tracking-[0.2em] text-blue-800">
                                    Actions
                                </th>

                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            <tr 
                                v-for="structure in structures.data" 
                                :key="structure.id"
                                class="hover:bg-slate-50 transition-all duration-300"
                            >

                                <!-- STRUCTURE -->
                                <td class="px-8 py-6">

                                    <div class="flex items-center gap-4">

                                        <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center">
                                            <CurrencyRupeeIcon class="w-7 h-7 text-slate-700" />
                                        </div>

                                        <div>

                                            <div class="text-base font-bold text-slate-900">
                                                {{ structure.name }}
                                            </div>

                                            <div class="text-sm text-slate-500 mt-1">
                                                {{ structure.description || 'No description available' }}
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <!-- COMPONENTS -->
                                <td class="px-8 py-6">

                                    <div class="space-y-3">

                                        <div class="text-sm font-bold text-slate-700">
                                            {{ structure.components ? structure.components.length : 0 }} Components
                                        </div>

                                        <div class="flex flex-wrap gap-2">

                                            <div class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold border border-emerald-200">
                                                Earnings: {{ structure.components?.filter(c => c.type === 'earning').length || 0 }}
                                            </div>

                                            <div class="px-3 py-1 rounded-full bg-rose-100 text-rose-700 text-xs font-bold border border-rose-200">
                                                Deductions: {{ structure.components?.filter(c => c.type === 'deduction').length || 0 }}
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <!-- STATUS -->
                                <td class="px-8 py-6 text-center">

                                    <span 
                                        class="inline-flex px-4 py-2 rounded-full text-xs font-bold uppercase tracking-[0.15em] border"
                                        :class="structure.is_active
                                            ? 'bg-emerald-100 text-emerald-700 border-emerald-200'
                                            : 'bg-rose-100 text-rose-700 border-rose-200'"
                                    >
                                        {{ structure.is_active ? 'Operational' : 'Offline' }}
                                    </span>

                                </td>

                                <!-- ACTION -->
                                <td class="px-8 py-6 text-right">

                                    <Link 
                                        :href="route('admin.salary-structures.edit', structure.id)"
                                        class="inline-flex items-center gap-2 h-11 px-5 rounded-xl border border-slate-300 bg-white text-slate-700 font-bold text-sm hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all duration-300 shadow-sm"
                                    >
                                        <PencilSquareIcon class="w-4 h-4" />
                                        Edit
                                    </Link>

                                </td>

                            </tr>

                            <!-- EMPTY -->
                            <tr v-if="structures.data.length === 0">

                                <td colspan="4" class="py-28 text-center">

                                    <AcademicCapIcon class="w-24 h-24 mx-auto text-slate-300 mb-6" />

                                    <p class="text-slate-400 text-lg font-bold">
                                        No salary structures found
                                    </p>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

         

            </div>

        </div>
    </MainLayout>
</template>

<style scoped>
* {
    font-family: 'Inter', sans-serif;
}
</style>