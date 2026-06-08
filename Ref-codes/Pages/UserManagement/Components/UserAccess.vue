<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    modelValue: Object, // Form
    roles: Array
});

const emit = defineEmits(['update:modelValue']);

const toggleRole = (id) => {
    let ids = [...props.modelValue.role_ids];
    if (ids.includes(id)) {
        ids = ids.filter(r => r !== id);
    } else {
        ids.push(id);
    }
    emit('update:modelValue', { ...props.modelValue, role_ids: ids });
};
</script>

<template>
    <div class="space-y-6 animate-fade-in">
        <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <div>
            <h4 class="text-sm font-semibold text-blue-800">Role Management</h4>
            <p class="text-sm text-blue-600">Assign roles to define what this user can do in the system.</p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">Assigned Roles</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div 
                v-for="role in roles" 
                :key="role.id" 
                class="p-4 rounded-xl border cursor-pointer transition-all hover:-translate-y-0.5"
                :class="modelValue.role_ids.includes(role.id) ? 'border-emerald-500 bg-emerald-50 ring-1 ring-emerald-500 shadow-sm' : 'border-gray-200 hover:border-emerald-300 hover:bg-white/80'"
                @click="toggleRole(role.id)"
            >
                <div class="flex items-center justify-between">
                    <span class="font-medium text-gray-900">{{ role.name }}</span>
                    <div v-if="modelValue.role_ids.includes(role.id)" class="h-5 w-5 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</template>
