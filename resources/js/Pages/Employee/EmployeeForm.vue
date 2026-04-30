<template>
    <!-- ░░ Outer Page Shell ░░ -->
    <div class="min-h-screen bg-[#f4f5fa]">

        <!-- ▓▓ GRADIENT HERO HEADER ▓▓ -->
        <div class="relative overflow-hidden sm:rounded-2xl mx-0 sm:mx-6 mt-0 sm:mt-6
                    bg-gradient-to-br from-[#3d27b4] via-[#6b3fd4] to-[#a855f7]">
            <div class="absolute -top-20 -right-20 w-80 h-80 bg-white/5 rounded-full pointer-events-none"></div>
            <div class="absolute bottom-0 left-1/3 w-56 h-56 bg-white/5 rounded-full pointer-events-none"></div>

            <div class="relative z-10 px-6 sm:px-10 py-8
                        flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <p class="text-xs font-bold text-white/50 uppercase tracking-widest mb-2">Employees</p>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                        {{ isEditing ? 'Edit Employee' : 'Add New Employee' }}
                    </h1>
                    <p class="mt-2 text-sm text-white/60 max-w-md leading-relaxed">
                        {{ isEditing ? 'Update the information for this employee record.' : 'Fill in the details to onboard a new team member.' }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-3 shrink-0">
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[130px]">
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Mode</p>
                        <p class="text-2xl font-extrabold text-white leading-none">{{ isEditing ? 'Edit' : 'Create' }}</p>
                    </div>
                    <div v-if="isEditing" class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[130px]">
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Employee</p>
                        <p class="text-lg font-extrabold text-white leading-none truncate">{{ employee?.first_name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ▓▓ INNER CONTENT BODY ▓▓ -->
        <div class="mx-0 sm:mx-6 mt-5 pb-12">

            <!-- Back link -->
            <div class="mb-4">
                <Link href="/admin/employees"
                    class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-indigo-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Workforce Registry
                </Link>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <form @submit.prevent="submit" class="divide-y divide-slate-100">

                    <!-- Section 1: Basic Info -->
                    <div class="p-6 sm:p-8 space-y-5">
                        <div class="flex items-center gap-3 mb-1">
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Basic Information</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Name and contact details</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-600">First Name <span class="text-rose-500">*</span></label>
                                <input v-model="form.first_name" type="text" placeholder="First name" required
                                    class="w-full h-10 rounded-xl border px-3 text-sm font-medium text-slate-800 placeholder-slate-300 outline-none transition-all bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400"
                                    :class="form.errors.first_name ? 'border-rose-300' : 'border-slate-200'" />
                                <p v-if="form.errors.first_name" class="text-xs text-rose-500 font-medium">{{ form.errors.first_name }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-600">Last Name <span class="text-rose-500">*</span></label>
                                <input v-model="form.last_name" type="text" placeholder="Last name" required
                                    class="w-full h-10 rounded-xl border px-3 text-sm font-medium text-slate-800 placeholder-slate-300 outline-none transition-all bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400"
                                    :class="form.errors.last_name ? 'border-rose-300' : 'border-slate-200'" />
                                <p v-if="form.errors.last_name" class="text-xs text-rose-500 font-medium">{{ form.errors.last_name }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-600">Email Address</label>
                                <input v-model="form.email" type="email" placeholder="name@company.com"
                                    class="w-full h-10 rounded-xl border px-3 text-sm font-medium text-slate-800 placeholder-slate-300 outline-none transition-all bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400"
                                    :class="form.errors.email ? 'border-rose-300' : 'border-slate-200'" />
                                <p v-if="form.errors.email" class="text-xs text-rose-500 font-medium">{{ form.errors.email }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-600">Phone Number</label>
                                <input v-model="form.phone" type="tel" placeholder="+91 98765 43210"
                                    class="w-full h-10 rounded-xl border px-3 text-sm font-medium text-slate-800 placeholder-slate-300 outline-none transition-all bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400"
                                    :class="form.errors.phone ? 'border-rose-300' : 'border-slate-200'" />
                                <p v-if="form.errors.phone" class="text-xs text-rose-500 font-medium">{{ form.errors.phone }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Employment Details -->
                    <div class="p-6 sm:p-8 space-y-5">
                        <div class="flex items-center gap-3 mb-1">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Employment Details</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Position, department, and terms</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-600">Employee Code</label>
                                <input v-model="form.employee_code" type="text" placeholder="Auto-generated if blank"
                                    class="w-full h-10 rounded-xl border px-3 text-sm font-medium text-slate-800 placeholder-slate-300 outline-none transition-all bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400"
                                    :class="form.errors.employee_code ? 'border-rose-300' : 'border-slate-200'" />
                                <p v-if="form.errors.employee_code" class="text-xs text-rose-500 font-medium">{{ form.errors.employee_code }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-600">Designation</label>
                                <input v-model="form.designation" type="text" placeholder="e.g. Software Engineer"
                                    class="w-full h-10 rounded-xl border px-3 text-sm font-medium text-slate-800 placeholder-slate-300 outline-none transition-all bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400"
                                    :class="form.errors.designation ? 'border-rose-300' : 'border-slate-200'" />
                                <p v-if="form.errors.designation" class="text-xs text-rose-500 font-medium">{{ form.errors.designation }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-600">Department</label>
                                <select v-model="form.department_id"
                                    class="w-full h-10 rounded-xl border px-3 text-sm font-medium text-slate-800 outline-none transition-all bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 cursor-pointer"
                                    :class="form.errors.department_id ? 'border-rose-300' : 'border-slate-200'">
                                    <option :value="null">Select Department</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                                <p v-if="form.errors.department_id" class="text-xs text-rose-500 font-medium">{{ form.errors.department_id }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-600">Location</label>
                                <select v-model="form.location_id"
                                    class="w-full h-10 rounded-xl border px-3 text-sm font-medium text-slate-800 outline-none transition-all bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 cursor-pointer"
                                    :class="form.errors.location_id ? 'border-rose-300' : 'border-slate-200'">
                                    <option :value="null">Select Location</option>
                                    <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                                </select>
                                <p v-if="form.errors.location_id" class="text-xs text-rose-500 font-medium">{{ form.errors.location_id }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-600">Date of Joining</label>
                                <input type="date" v-model="form.joining_date"
                                    class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-medium text-slate-800 outline-none transition-all bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400" />
                                <p v-if="form.errors.joining_date" class="text-xs text-rose-500 font-medium">{{ form.errors.joining_date }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-600">Employment Type</label>
                                <select v-model="form.employment_type"
                                    class="w-full h-10 rounded-xl border border-slate-200 px-3 text-sm font-medium text-slate-800 outline-none transition-all bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 cursor-pointer">
                                    <option value="full_time">Full Time</option>
                                    <option value="part_time">Part Time</option>
                                    <option value="contract">Contract</option>
                                    <option value="intern">Intern</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex items-center justify-end gap-3 px-6 sm:px-8 py-4 bg-slate-50">
                        <Link href="/admin/employees"
                            class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-600 hover:bg-slate-100 transition-all">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 active:scale-[0.98] transition-all shadow-sm shadow-indigo-200 disabled:opacity-60 disabled:cursor-not-allowed">
                            <div v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ isEditing ? 'Update Employee' : 'Create Employee' }}
                        </button>
                    </div>

                </form>
            </div>
        </div><!-- /inner body -->
    </div><!-- /outer shell -->
</template>

<script setup>
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useToastStore } from '@/stores/toast';

defineOptions({ layout: MainLayout });

const props = defineProps({
    employee: Object,
    departments: Array,
    locations: Array
});

const toast = useToastStore();
const isEditing = computed(() => !!props.employee);

const form = useForm({
    first_name:      props.employee?.first_name      || '',
    last_name:       props.employee?.last_name       || '',
    email:           props.employee?.email           || '',
    phone:           props.employee?.phone           || '',
    employee_code:   props.employee?.employee_code   || '',
    designation:     props.employee?.designation     || '',
    department_id:   props.employee?.department_id   || null,
    location_id:     props.employee?.location_id     || null,
    joining_date:    props.employee?.joining_date    || '',
    employment_type: props.employee?.employment_type || 'full_time',
});

const submit = () => {
    if (isEditing.value) {
        form.put(`/admin/employees/${props.employee.id}`, {
            onSuccess: () => toast.success('Employee updated successfully'),
            onError:   () => toast.error('Check form errors')
        });
    } else {
        form.post('/admin/employees', {
            onSuccess: () => toast.success('Employee created successfully'),
            onError:   () => toast.error('Check form errors')
        });
    }
};
</script>