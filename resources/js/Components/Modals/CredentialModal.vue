<template>
    <Modal :show="show" title="Login Credentials Created" maxWidth="md" @close="$emit('close')">
        <div class="space-y-6">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-green-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h4 class="text-sm font-semibold text-green-800">Success!</h4>
                    <p class="text-xs text-green-600 mt-1">System login has been enabled for this employee.</p>
                </div>
            </div>

            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 space-y-3">
                <div>
                     <label class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Email</label>
                     <div class="flex items-center justify-between mt-1">
                        <span class="text-sm font-medium text-gray-900 font-mono">{{ email }}</span>
                     </div>
                </div>
                <div class="pt-3 border-t border-gray-200">
                     <label class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Password</label>
                     <div class="flex items-center justify-between mt-1">
                        <span class="text-lg font-bold text-gray-900 font-mono tracking-widest">{{ password }}</span>
                     </div>
                </div>
            </div>

            <p class="text-xs text-center text-gray-500">
                Please share these credentials securely with the employee.<br>
                They will be required to change their password upon first login.
            </p>
        </div>

        <template #footer>
            <button 
                @click="$emit('close')" 
                class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium shadow-md hover:bg-indigo-700 transition-all"
            >
                Done
            </button>
        </template>
    </Modal>
</template>

<script setup>
import Modal from '@/Components/Modal.vue';

defineProps({
    show: Boolean,
    email: String,
    password: String
});

defineEmits(['close']);
</script>
