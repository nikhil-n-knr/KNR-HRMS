
<template>
    <Head :title="form.id ? 'Edit Structure' : 'Create Salary Structure'" />

    <MainLayout>
<!-- HEADER -->
<div class="mb-6 mt-6">
    <div
        class="relative overflow-hidden rounded-[20px] bg-gradient-to-r from-indigo-700 via-violet-800 to-violet-600 px-8 md:px-10 py-8"
    >

        <!-- Background Circles -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-10 left-1/3 w-40 h-40 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-20 right-10 w-[24rem] h-[24rem] rounded-full bg-white/10"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10">
        
   <p class="uppercase tracking-[0.2em] text-white/60 text-xs font-bold mb-2">
                Administration
            </p>

            <h1 class="text-4xl md:text-4xl font-black text-white leading-tight">
                {{ form.id ? 'Edit Salary Structure' : 'Create Salary Structure' }}
            </h1>

            <p class="mt-4 text-white/70 text-base md:text-lg font-medium max-w-2xl">
                Configure salary templates, tax settings and payroll components.
            </p>

        </div>
    </div>
</div>          


                <!-- MAIN FORM CARD -->
                <div class="bg-white border border-slate-200 rounded-[2rem] shadow-2xl shadow-slate-200/50 overflow-hidden">

                    

                    <form @submit.prevent="submit" class="p-8 space-y-10">

                        <!-- BASIC INFO -->
                        <div>

                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>

                                <div>
                                    <h3 class="text-lg font-black text-slate-900">
                                        Basic Information
                                    </h3>

                                    <p class="text-sm text-slate-500">
                                        Enter structure name and notes.
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <!-- NAME -->
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">
                                        Structure Name
                                    </label>

                                    <input
                                        type="text"
                                        v-model="form.name"
                                        placeholder="e.g. India Standard"
                                        class="w-full h-14 rounded-2xl border-2 border-slate-200 bg-white px-5 text-slate-800 font-semibold shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                                    >

                                    <div v-if="form.errors.name" class="text-red-500 text-sm mt-2">
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <!-- DESCRIPTION -->
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">
                                        Description
                                    </label>

                                    <input
                                        type="text"
                                        v-model="form.description"
                                        placeholder="Optional notes"
                                        class="w-full h-14 rounded-2xl border-2 border-slate-200 bg-white px-5 text-slate-800 font-semibold shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                                    >
                                </div>

                            </div>
                        </div>

                        <!-- TAX CONFIG -->
                        <div class="border-t border-slate-100 pt-6">

                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V3m0 18v-5" />
                                    </svg>
                                </div>

                                <div>
                                    <h3 class="text-lg font-black text-slate-900">
                                        Tax Configuration
                                    </h3>

                                    <p class="text-sm text-slate-500">
                                        Select tax deduction calculation method.
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">
                                        TDS Calculation Method
                                    </label>

                                    <select
                                        v-model="form.tds_method"
                                        class="w-full h-14 rounded-2xl border-2 border-slate-200 bg-white px-5 text-slate-800 font-semibold shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                                    >
                                        <option value="Manual">
                                            None / Manual (Admin Entry)
                                        </option>

                                        <option value="Auto_New">
                                            Auto-Calculate (New Regime)
                                        </option>

                                        <option value="Auto_Old">
                                            Auto-Calculate (Old Regime)
                                        </option>

                                        <option value="Employee_Choice">
                                            Employee Choice (Portal Declaration)
                                        </option>
                                    </select>

                                    <p class="mt-3 text-sm text-slate-500">
                                        Determines how Income Tax is calculated for employees assigned to this structure.
                                    </p>
                                </div>

                            </div>
                        </div>

                        <!-- COMPONENTS -->
                      <!-- COMPONENTS TABLE -->
<div class="border-t border-slate-100 pt-10">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">

        <div>
            <h3 class="text-2xl font-black text-slate-900">
                Salary Components
            </h3>

            <p class="text-sm text-slate-500 mt-1">
                Configure earnings and deduction components in table format.
            </p>
        </div>

        <button
            type="button"
            @click="addComponent"
            class="h-12 px-6 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-bold shadow-lg hover:scale-105 transition-all flex items-center gap-2"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4v16m8-8H4" />
            </svg>

            Add Component
        </button>
    </div>

    <!-- TABLE -->
    <div class="overflow-x-auto rounded-3xl border border-slate-200 shadow-sm">

        <table class="min-w-full bg-white">

            <!-- TABLE HEADER -->
       <thead>

    <tr class="bg-blue-50">

        <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-widest text-blue-800">
            Component Name
        </th>

        <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-widest text-blue-800">
            Type
        </th>

        <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-widest text-blue-800">
            Calculation Type
        </th>

        <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-widest text-blue-800">
            Value
        </th>

        <th class="px-6 py-4 text-center text-xs font-black uppercase tracking-widest text-blue-800">
            Action
        </th>

    </tr>

</thead>

            <!-- TABLE BODY -->
            <tbody class="divide-y divide-slate-100">

                <tr
                    v-for="(comp, index) in form.components"
                    :key="index"
                    class="hover:bg-indigo-50/40 transition-all"
                >

                    <!-- NAME -->
                    <td class="px-6 py-5">
                        <input
                            type="text"
                            v-model="comp.name"
                            required
                            placeholder="Basic Salary"
                            class="w-full h-12 rounded-xl border-2 border-slate-200 px-4 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                        >
                    </td>

                    <!-- TYPE -->
                    <td class="px-6 py-5">
                        <select
                            v-model="comp.type"
                            class="w-full h-12 rounded-xl border-2 border-slate-200 px-4 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                        >
                            <option value="earning">
                                Earning
                            </option>

                            <option value="deduction">
                                Deduction
                            </option>
                        </select>
                    </td>

                    <!-- CALC TYPE -->
                    <td class="px-6 py-5">
                        <select
                            v-model="comp.calculation_type"
                            class="w-full h-12 rounded-xl border-2 border-slate-200 px-4 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                        >
                            <option value="percentage">
                                % of CTC
                            </option>

                            <option value="fixed">
                                Fixed Amount
                            </option>
                        </select>
                    </td>

                    <!-- VALUE -->
                    <td class="px-6 py-5">
                        <input
                            type="number"
                            step="0.01"
                            v-model="comp.value"
                            required
                            class="w-full h-12 rounded-xl border-2 border-slate-200 px-4 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                        >
                    </td>

                    <!-- DELETE -->
                    <td class="px-6 py-5 text-center">

                        <button
                            type="button"
                            @click="removeComponent(index)"
                            class="w-11 h-11 rounded-xl bg-red-50 border border-red-100 flex items-center justify-center text-red-500 hover:bg-red-500 hover:text-white transition-all mx-auto"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>

                    </td>

                </tr>

                <!-- EMPTY -->
                <tr v-if="form.components.length === 0">

                    <td colspan="5" class="py-16 text-center bg-slate-50">

                        <div class="text-slate-400">

                            <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 4v16m8-8H4" />
                            </svg>

                            <p class="font-bold text-lg">
                                No Components Added
                            </p>

                            <p class="text-sm mt-2">
                                Click "Add Component" to create salary components.
                            </p>

                        </div>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>
</div>

                        <!-- FOOTER -->
                        <div class="pt-8 border-t border-slate-100 flex flex-col sm:flex-row justify-end gap-4">

                            <Link
                                :href="route('admin.salary-structures.index')"
                                class="h-14 px-8 rounded-2xl border-2 border-slate-200 bg-white text-slate-700 font-bold flex items-center justify-center hover:bg-slate-100 transition"
                            >
                                Cancel
                            </Link>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="h-14 px-10 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-bold shadow-2xl shadow-indigo-200 hover:scale-105 transition-all disabled:opacity-50"
                            >
                                {{ form.id ? 'Update Structure' : 'Create Structure' }}
                            </button>

                        </div>

                    </form>
                </div>
       
    </MainLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
    structure: Object,
    components: Array
});

const form = useForm({
    id: props.structure?.id || null,
    name: props.structure?.name || '',
    description: props.structure?.description || '',
    tds_method: props.structure?.tds_method || 'Auto_New',
    components: props.components && props.components.length > 0 ? props.components.map(c => ({
        name: c.name,
        type: c.type,
        calculation_type: c.calculation_type,
        value: c.value
    })) : [
        { name: 'Basic', type: 'earning', calculation_type: 'percentage', value: 40 },
        { name: 'HRA', type: 'earning', calculation_type: 'percentage', value: 20 },
        { name: 'Special Allowance', type: 'earning', calculation_type: 'percentage', value: 30 },
        { name: 'PF', type: 'deduction', calculation_type: 'percentage', value: 1.8 }
    ]
});

const addComponent = () => {
    form.components.push({
        name: '',
        type: 'earning',
        calculation_type: 'fixed',
        value: 0
    });
};

const removeComponent = (index) => {
    form.components.splice(index, 1);
};

const submit = () => {
    if (form.id) {
        form.put(route('admin.salary-structures.update', form.id));
    } else {
        form.post(route('admin.salary-structures.store'));
    }
};
</script>

<style scoped>
input,
select {
    outline: none;
}

* {
    font-family: 'Inter', sans-serif;
}
</style>