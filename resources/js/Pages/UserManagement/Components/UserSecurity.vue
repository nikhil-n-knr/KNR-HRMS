<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    modelValue: Object
});

const emit = defineEmits(['update:modelValue']);

const update = (key, value) => {
    emit('update:modelValue', { ...props.modelValue, [key]: value });
};
</script>

<template>
    <div class="space-y-6 animate-fade-in">
        <div class="p-5 bg-white/60 backdrop-blur-md rounded-xl border border-gray-200 shadow-sm">
            <h4 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                Change Password
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">New Password</label>
                    <input 
                        :value="modelValue.password"
                        @input="update('password', $event.target.value)"
                        type="password" 
                        placeholder="Leave blank to keep current" 
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                    >
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Confirm Password</label>
                    <input 
                        :value="modelValue.password_confirmation"
                        @input="update('password_confirmation', $event.target.value)"
                        type="password" 
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                    >
                </div>
            </div>
        </div>

        <div class="p-5 bg-white/60 backdrop-blur-md rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <h4 class="font-medium text-gray-900">Force Two-Factor Authentication</h4>
                <p class="text-xs text-gray-500 mt-1">User must set up 2FA on next login.</p>
            </div>
            <button 
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none" 
                :class="modelValue.force_mfa ? 'bg-emerald-500' : 'bg-gray-200'"
                @click="update('force_mfa', !modelValue.force_mfa)"
            >
                <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" :class="modelValue.force_mfa ? 'translate-x-5' : 'translate-x-0'"></span>
            </button>
        </div>
    </div>
</template>
