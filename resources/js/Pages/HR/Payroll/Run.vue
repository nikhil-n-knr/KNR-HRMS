<template>
    <Head title="Run Payroll" />
    <MainLayout>
        <div class="max-w-3xl mx-auto py-12 px-6">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden relative">
                <div class="absolute top-0 left-0 w-full h-2 bg-indigo-600"></div>
                <div class="p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Run Payroll</h2>
                    <p class="text-gray-500 mb-8">Process monthly salaries or handle full & final settlements.</p>

                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <!-- Error Alert -->
                        <div v-if="form.errors.message || Object.keys(form.errors).length > 0" class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <p v-if="form.errors.message">{{ form.errors.message }}</p>
                                        <ul v-if="Object.keys(form.errors).length > 1 || !form.errors.message" class="list-disc pl-5 space-y-1">
                                            <li v-for="(error, key) in form.errors" :key="key">
                                                <span v-if="key !== 'message'">{{ error }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Run Type -->
                         <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payroll Type</label>
                            <div class="grid grid-cols-2 gap-4">
                                <button type="button" @click="form.type = 'monthly'" 
                                        :class="{'bg-indigo-50 border-indigo-500 text-indigo-700 ring-1 ring-indigo-500': form.type === 'monthly', 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50': form.type !== 'monthly'}"
                                        class="relative flex flex-col items-center p-4 border rounded-lg shadow-sm focus:outline-none transition-all">
                                    <span class="font-bold">Monthly Run</span>
                                    <span class="text-xs mt-1 opacity-75">All Employees</span>
                                </button>
                                <button type="button" @click="form.type = 'fnf'" 
                                        :class="{'bg-indigo-50 border-indigo-500 text-indigo-700 ring-1 ring-indigo-500': form.type === 'fnf', 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50': form.type !== 'fnf'}"
                                        class="relative flex flex-col items-center p-4 border rounded-lg shadow-sm focus:outline-none transition-all">
                                    <span class="font-bold">FnF / Exit</span>
                                    <span class="text-xs mt-1 opacity-75">Single Employee</span>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <!-- Year Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                                <select v-model="form.year" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                                </select>
                            </div>

                            <!-- Month Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                                <select v-model="form.month" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Options -->
                        <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input v-model="form.ignore_attendance" id="ignore_attendance" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="ignore_attendance" class="font-medium text-gray-700">Ignore Attendance (No LOP)</label>
                                    <p class="text-gray-500">Enable this if you want to skip loss-of-pay deductions for this run.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Employee Selector (FnF Only) -->
                        <div v-if="form.type === 'fnf'" class="bg-gray-50 p-4 rounded-md border border-gray-200 animate-fade-in-down mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Employee for Settlement</label>
                            <select v-model="form.employee_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="" disabled>Select Employee...</option>
                                <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-2">This will generate a separate payroll batch for this employee.</p>
                        </div>
                        
                        <!-- Pre-Flight Checks & Button Group -->
                        <div v-if="!processingState.running">
                             <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            Click "Start Run" to perform pre-flight validation before generating payslips.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-100">
                                <Link :href="route('hr.payroll.index')" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Cancel</Link>
                                <button type="button" @click="startPreFlight" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                                   Start Run
                                </button>
                            </div>
                        </div>

                         <!-- Processing UI -->
                        <div v-else class="space-y-4 pt-4 border-t border-gray-100">
                            <!-- Checklist -->
                            <div class="bg-gray-50 rounded p-4 font-mono text-xs text-gray-600 space-y-2">
                                <div v-for="(step, idx) in checklist" :key="idx" class="flex items-center">
                                    <span v-if="step.status === 'pending'" class="w-4 h-4 mr-2 border rounded-full border-gray-300"></span>
                                    <svg v-else-if="step.status === 'done'" class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <svg v-else-if="step.status === 'error'" class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    <span :class="{'text-gray-400': step.status === 'pending', 'text-green-700': step.status === 'done', 'font-bold': step.status === 'running'}">
                                        {{ step.label }}
                                    </span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                             <div class="relative pt-1">
                                <div class="flex mb-2 items-center justify-between">
                                    <div>
                                    <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-indigo-600 bg-indigo-200">
                                        Processing
                                    </span>
                                    </div>
                                    <div class="text-right">
                                    <span class="text-xs font-semibold inline-block text-indigo-600">
                                        {{ processingState.progress }}%
                                    </span>
                                    </div>
                                </div>
                                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-200">
                                    <div :style="`width: ${processingState.progress}%`" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500 transition-all duration-300"></div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { reactive, ref } from 'vue';

const props = defineProps({
    months: Array,
    years: Array,
    employees: Array // Passed from controller
});

const form = useForm({
    type: 'monthly', // monthly or fnf
    month: new Date().getMonth() + 1,
    year: new Date().getFullYear(),
    employee_id: '',
    ignore_attendance: false
});

const processingState = reactive({
    running: false,
    progress: 0,
});

const checklist = reactive([
    { label: 'Checking Employee Records...', status: 'pending' },
    { label: 'Verifying Attendance Data...', status: 'pending' },
    { label: 'Calculating Loan Deductions...', status: 'pending' },
    { label: 'Applying Tax Slabs...', status: 'pending' },
    { label: 'Optimizing Payouts...', status: 'pending' },
]);

const startPreFlight = () => {
    if (form.type === 'fnf' && !form.employee_id) {
        alert('Please select an employee for FnF settlement.');
        return;
    }
    
    // Clear employee_id if monthly
    if (form.type === 'monthly') form.employee_id = '';

    processingState.running = true;
    runChecklist(0);
};

const runChecklist = (index) => {
    if (index >= checklist.length) {
        // All done, submit form
        processingState.progress = 100;
        setTimeout(() => {
             form.post(route('hr.payroll.store'), {
                 onFinish: () => processingState.running = false
             });
        }, 500);
        return;
    }

    checklist[index].status = 'running';
    
    // Simulate step time (300-600ms)
    setTimeout(() => {
        checklist[index].status = 'done';
        processingState.progress = Math.round(((index + 1) / checklist.length) * 90);
        runChecklist(index + 1);
    }, Math.random() * 300 + 300);
};

</script>

<style scoped>
.animate-fade-in-down {
    animation: fadeInDown 0.3s ease-out;
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
