<template>
    <TransitionRoot appear :show="true" as="template">
        <Dialog as="div" class="relative z-50" @close="$emit('close')">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black bg-opacity-25" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex items-center justify-center min-h-full p-4 text-center">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl"
                        >
                            <DialogTitle
                                as="h3"
                                class="text-lg font-medium leading-6 text-gray-900"
                            >
                                Grant Access
                            </DialogTitle>

                            <div class="mt-4 space-y-4">
                                <!-- User Info -->
                                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center justify-center w-12 h-12 font-medium text-white bg-blue-500 rounded-full">
                                        {{ getInitials(user.name) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ user.name }}</p>
                                        <p class="text-sm text-gray-500">{{ user.email }}</p>
                                    </div>
                                </div>

                                <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                    <p class="text-sm text-blue-800">
                                        <i class="mr-2 fas fa-info-circle"></i>
                                        Granting access to <strong>{{ module.display_name }}</strong>
                                    </p>
                                </div>

                                <!-- Role Selection (Optional) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        Module Role (Optional)
                                    </label>
                                    <input
                                        v-model="moduleRole"
                                        type="text"
                                        placeholder="e.g., Sales Manager, Analyst"
                                        class="block w-full px-3 py-2 mt-1 border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">
                                        Assign a specific role within this module (optional)
                                    </p>
                                </div>

                                <!-- Error Message -->
                                <div
                                    v-if="errorMessage"
                                    class="p-3 text-sm text-red-800 bg-red-50 rounded-lg border border-red-200"
                                >
                                    <i class="mr-2 fas fa-exclamation-circle"></i>
                                    {{ errorMessage }}
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-2 mt-6">
                                    <button
                                        @click="$emit('close')"
                                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        @click="grantAccess"
                                        :disabled="loading"
                                        class="flex-1 px-4 py-2 text-sm font-medium text-white transition-colors bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 disabled:opacity-50"
                                    >
                                        <span v-if="loading">
                                            <i class="mr-2 fas fa-spinner fa-spin"></i>
                                            Granting...
                                        </span>
                                        <span v-else>
                                            <i class="mr-2 fas fa-check"></i>
                                            Grant Access
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref } from 'vue';
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
} from '@headlessui/vue';
import axios from 'axios';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    module: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close', 'granted']);

const moduleRole = ref('');
const loading = ref(false);
const errorMessage = ref('');

const getInitials = (name) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

const grantAccess = async () => {
    loading.value = true;
    errorMessage.value = '';

    try {
        await axios.post(
            route('admin.modules.grant-access', {
                module: props.module.id,
                user: props.user.id,
            }),
            {
                module_role: moduleRole.value || null,
            }
        );

        emit('granted');
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message || 'Failed to grant access. Please try again.';
    } finally {
        loading.value = false;
    }
};
</script>
