<script setup>
import BaseInput from '@/Components/BaseInput.vue';
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    modelValue: Object, // The form object
    departments: Array,
    locations: Array
});

const emit = defineEmits(['update:modelValue']);

// Simple proxy to update parent form
const update = (key, value) => {
    emit('update:modelValue', { ...props.modelValue, [key]: value });
};
</script>

<template>
    <div class="bg-white/60 backdrop-blur-md border border-white/60 rounded-2xl shadow-sm p-8 animate-fade-in">
        <h3 class="text-lg font-bold text-gray-800 mb-6">Basic Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <BaseInput 
                :modelValue="modelValue.name"
                @update:modelValue="update('name', $event)"
                label="Full Name"
                minlength="2"
                maxlength="100"
                required
            />
            <BaseInput 
                :modelValue="modelValue.email"
                @update:modelValue="update('email', $event)"
                label="Email Address"
                type="email"
                maxlength="100"
                required
            />
            <BaseInput 
                :modelValue="modelValue.employee_id"
                @update:modelValue="update('employee_id', $event)"
                label="Employee ID"
                maxlength="50"
            />
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select 
                    :value="modelValue.status"
                    @input="update('status', $event.target.value)"
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-all text-sm"
                >
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                <select 
                    :value="modelValue.department_id"
                    @input="update('department_id', $event.target.value)"
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-all text-sm"
                >
                    <option value="">No Department</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <select 
                    :value="modelValue.location_id"
                    @input="update('location_id', $event.target.value)"
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-all text-sm"
                >
                    <option value="">No Location</option>
                    <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                </select>
            </div>
        </div>
    </div>
</template>
