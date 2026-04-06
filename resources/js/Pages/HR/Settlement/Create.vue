<template>
    <Head title="FnF Settlement" />
    <MainLayout>
        <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Full & Final Settlement</h1>
            
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="flex items-center space-x-4">
                     <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center text-red-700 font-bold text-lg">
                        {{ employee.user.name.charAt(0) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ employee.user.name }}</h2>
                        <p class="text-sm text-gray-500">{{ employee.designation }} • {{ employee.department?.name }}</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white shadow rounded-lg p-6 space-y-6">
                <!-- Exit Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Working Day</label>
                        <input type="date" v-model="form.last_working_day" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                        <p class="text-xs text-gray-500 mt-1">Salary will be processed up to this date.</p>
                    </div>
                    <div>
                         <label class="block text-sm font-medium text-gray-700">Notice Period Shortfall (Days)</label>
                        <input type="number" v-model="form.notice_shortfall_days" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                         <p class="text-xs text-red-500 mt-1">Deduction: Pro-rated salary for these days.</p>
                    </div>
                </div>
                
                <hr class="border-gray-100">

                <!-- Additions -->
                <!-- Additions -->
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest">Credits</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Leave Encashment (Days)</label>
                        <input type="number" v-model="form.leave_encashment_days" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Gratuity Amount</label>
                         <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">₹</span>
                            </div>
                            <input type="number" v-model="form.gratuity_amount" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <p v-if="gratuity_eligible" class="text-xs text-green-600 mt-1 font-bold">Employee is eligible (5+ Years).</p>
                    </div>
                </div>

                <hr class="border-gray-100">

                <!-- Deductions -->
                 <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest">Recoveries</h3>
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Recovery Amount (Asset/Other)</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">₹</span>
                            </div>
                            <input type="number" v-model="form.recovery_amount" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div>
                         <label class="block text-sm font-medium text-gray-700">Remarks / Reason</label>
                         <textarea v-model="form.remarks" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" rows="1"></textarea>
                    </div>
                </div>
                
                <div class="pt-4 flex justify-end space-x-3">
                    <Link :href="route('admin.employees.show', employee.id)" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-lg">
                        Process FnF Settlement
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
    employee: Object,
    last_working_day: String,
    gratuity_eligible: Boolean
});

const form = useForm({
    last_working_day: props.last_working_day,
    notice_shortfall_days: 0,
    leave_encashment_days: 0,
    gratuity_amount: 0,
    recovery_amount: '',
    remarks: ''
});

const submit = () => {
    if(!confirm("This will mark the employee as Terminated/Exited and generate their final payroll. Continue?")) return;
    
    form.post(route('hr.settlement.store', props.employee.id));
};
</script>
