<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                 <h1 class="text-2xl font-bold text-gray-800">{{ isEditing ? 'Edit Employee' : 'Add New Employee' }}</h1>
                 <p class="text-sm text-gray-500 mt-1">{{ isEditing ? 'Update employee information' : 'Onboard a new employee' }}</p>
            </div>
            <Link href="/admin/employees" class="px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-xl text-sm font-medium shadow-sm hover:bg-gray-50 transition">
                Back to List
            </Link>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Info -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-emerald-800 border-b border-gray-100 pb-2">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <BaseInput v-model="form.first_name" label="First Name" required :error="form.errors.first_name" />
                        <BaseInput v-model="form.last_name" label="Last Name" required :error="form.errors.last_name" />
                        <BaseInput v-model="form.email" label="Email Address" type="email" :error="form.errors.email" />
                        <BaseInput v-model="form.phone" label="Phone Number" :error="form.errors.phone" />
                    </div>
                </div>

                <!-- Employment Details -->
                <div class="space-y-4 pt-4">
                    <h3 class="text-lg font-medium text-emerald-800 border-b border-gray-100 pb-2">Employment Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <BaseInput v-model="form.employee_code" label="Employee Code" placeholder="Auto-generated if blank" :error="form.errors.employee_code" />
                        <BaseInput v-model="form.designation" label="Designation" :error="form.errors.designation" />
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <select v-model="form.department_id" class="w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500/20 bg-white/50">
                                <option :value="null">Select Department</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                            </select>
                            <p v-if="form.errors.department_id" class="text-red-500 text-xs mt-1">{{ form.errors.department_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                            <select v-model="form.location_id" class="w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500/20 bg-white/50">
                                <option :value="null">Select Location</option>
                                <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                            </select>
                            <p v-if="form.errors.location_id" class="text-red-500 text-xs mt-1">{{ form.errors.location_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Joining</label>
                             <input type="date" v-model="form.joining_date" class="w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500/20 bg-white/50">
                            <p v-if="form.errors.joining_date" class="text-red-500 text-xs mt-1">{{ form.errors.joining_date }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Employment Type</label>
                            <select v-model="form.employment_type" class="w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500/20 bg-white/50">
                                <option value="full_time">Full Time</option>
                                <option value="part_time">Part Time</option>
                                <option value="contract">Contract</option>
                                <option value="intern">Intern</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                    <Link href="/admin/employees" class="px-5 py-2.5 text-gray-600 font-medium hover:bg-gray-100 rounded-xl transition">
                        Cancel
                    </Link>
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-medium rounded-xl shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                    >
                        {{ form.processing ? 'Saving...' : (isEditing ? 'Update Employee' : 'Create Employee') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import BaseInput from '@/Components/BaseInput.vue';
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
    first_name: props.employee?.first_name || '',
    last_name: props.employee?.last_name || '',
    email: props.employee?.email || '',
    phone: props.employee?.phone || '',
    employee_code: props.employee?.employee_code || '',
    designation: props.employee?.designation || '',
    department_id: props.employee?.department_id || null,
    location_id: props.employee?.location_id || null,
    joining_date: props.employee?.joining_date || '',
    employment_type: props.employee?.employment_type || 'full_time',
});

const submit = () => {
    if (isEditing.value) {
        form.put(`/admin/employees/${props.employee.id}`, {
            onSuccess: () => toast.success('Employee updated successfully'),
            onError: () => toast.error('Check form errors')
        });
    } else {
        form.post('/admin/employees', {
            onSuccess: () => toast.success('Employee created successfully'),
            onError: () => toast.error('Check form errors')
        });
    }
};
</script>
