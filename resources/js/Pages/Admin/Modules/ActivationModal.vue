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
                                Activate {{ module.display_name }}
                            </DialogTitle>

                            <div class="mt-4">
                                <!-- Activation Method Selection (Step 1) -->
                                <div v-if="step === 1" class="space-y-4">
                                    <p class="text-sm text-gray-500">
                                        Select an activation method to enable this module
                                    </p>

                                    <div class="space-y-3">
                                        <!-- OTP Method -->
                                        <button
                                            @click="selectMethod('otp')"
                                            class="flex items-start w-full p-4 text-left transition-colors border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50"
                                            :class="{
                                                'border-blue-500 bg-blue-50': selectedMethod === 'otp',
                                            }"
                                        >
                                            <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 mr-3 bg-blue-100 rounded-lg">
                                                <i class="text-blue-600 fas fa-envelope"></i>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">Email OTP</p>
                                                <p class="text-sm text-gray-500">
                                                    Receive a verification code via email
                                                </p>
                                            </div>
                                        </button>

                                        <!-- Dev Mode -->
                                        <button
                                            @click="selectMethod('dev')"
                                            class="flex items-start w-full p-4 text-left transition-colors border border-gray-200 rounded-lg hover:border-yellow-500 hover:bg-yellow-50"
                                            :class="{
                                                'border-yellow-500 bg-yellow-50':
                                                    selectedMethod === 'dev',
                                            }"
                                        >
                                            <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 mr-3 bg-yellow-100 rounded-lg">
                                                <i class="text-yellow-600 fas fa-code"></i>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">Dev Mode</p>
                                                <p class="text-sm text-gray-500">
                                                    Use development key (0000) for testing
                                                </p>
                                            </div>
                                        </button>
                                    </div>

                                    <button
                                        @click="proceedToActivation"
                                        :disabled="!selectedMethod"
                                        class="w-full px-4 py-2 mt-4 text-sm font-medium text-white transition-colors bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Continue
                                    </button>
                                </div>

                                <!-- OTP Verification (Step 2a) -->
                                <div v-if="step === 2 && selectedMethod === 'otp'" class="space-y-4">
                                    <div
                                        v-if="!otpSent"
                                        class="p-4 bg-blue-50 rounded-lg border border-blue-200"
                                    >
                                        <p class="text-sm text-blue-800">
                                            Click below to send a verification code to your email
                                        </p>
                                    </div>

                                    <div v-else class="space-y-4">
                                        <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                                            <p class="text-sm text-green-800">
                                                <i class="mr-2 fas fa-check-circle"></i>
                                                OTP sent successfully! Check your email
                                            </p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">
                                                Enter 6-Digit Code
                                            </label>
                                            <input
                                                v-model="otpCode"
                                                type="text"
                                                maxlength="6"
                                                placeholder="000000"
                                                class="block w-full px-4 py-3 mt-1 text-lg tracking-widest text-center border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                                @input="otpCode = otpCode.replace(/\D/g, '')"
                                            />
                                        </div>
                                    </div>

                                    <div class="flex gap-2">
                                        <button
                                            @click="step = 1"
                                            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                                        >
                                            Back
                                        </button>
                                        <button
                                            v-if="!otpSent"
                                            @click="sendOTP"
                                            :disabled="loading"
                                            class="flex-1 px-4 py-2 text-sm font-medium text-white transition-colors bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 disabled:opacity-50"
                                        >
                                            <span v-if="loading">
                                                <i class="mr-2 fas fa-spinner fa-spin"></i>
                                                Sending...
                                            </span>
                                            <span v-else>Send OTP</span>
                                        </button>
                                        <button
                                            v-else
                                            @click="verifyOTP"
                                            :disabled="loading || otpCode.length !== 6"
                                            class="flex-1 px-4 py-2 text-sm font-medium text-white transition-colors bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 disabled:opacity-50"
                                        >
                                            <span v-if="loading">
                                                <i class="mr-2 fas fa-spinner fa-spin"></i>
                                                Verifying...
                                            </span>
                                            <span v-else>Verify & Activate</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Dev Key Entry (Step 2b) -->
                                <div v-if="step === 2 && selectedMethod === 'dev'" class="space-y-4">
                                    <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                        <p class="text-sm text-yellow-800">
                                            <i class="mr-2 fas fa-exclamation-triangle"></i>
                                            Development mode is for testing only. Use key: <strong>0000</strong>
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">
                                            Enter Dev Key
                                        </label>
                                        <input
                                            v-model="devKey"
                                            type="text"
                                            placeholder="0000"
                                            class="block w-full px-4 py-2 mt-1 border-gray-300 rounded-md focus:ring-yellow-500 focus:border-yellow-500"
                                        />
                                    </div>

                                    <div class="flex gap-2">
                                        <button
                                            @click="step = 1"
                                            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                                        >
                                            Back
                                        </button>
                                        <button
                                            @click="activateDevMode"
                                            :disabled="loading || !devKey"
                                            class="flex-1 px-4 py-2 text-sm font-medium text-white transition-colors bg-yellow-600 border border-transparent rounded-md shadow-sm hover:bg-yellow-700 disabled:opacity-50"
                                        >
                                            <span v-if="loading">
                                                <i class="mr-2 fas fa-spinner fa-spin"></i>
                                                Activating...
                                            </span>
                                            <span v-else>Activate</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Error Message -->
                                <div
                                    v-if="errorMessage"
                                    class="p-3 mt-4 text-sm text-red-800 bg-red-50 rounded-lg border border-red-200"
                                >
                                    <i class="mr-2 fas fa-exclamation-circle"></i>
                                    {{ errorMessage }}
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
import { router } from '@inertiajs/vue3';
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
} from '@headlessui/vue';
import axios from 'axios';

const props = defineProps({
    module: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close', 'activated']);

const step = ref(1);
const selectedMethod = ref(null);
const otpSent = ref(false);
const otpCode = ref('');
const devKey = ref('');
const loading = ref(false);
const errorMessage = ref('');
const requestId = ref(null);

const selectMethod = (method) => {
    selectedMethod.value = method;
};

const proceedToActivation = () => {
    step.value = 2;
    errorMessage.value = '';
};

const sendOTP = async () => {
    loading.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.post(
            route('admin.modules.request-activation', { module: props.module.id }),
            { method: 'otp' }
        );

        requestId.value = response.data.request_id;
        otpSent.value = true;
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message || 'Failed to send OTP. Please try again.';
    } finally {
        loading.value = false;
    }
};

const verifyOTP = async () => {
    loading.value = true;
    errorMessage.value = '';

    try {
        await axios.post(route('admin.modules.verify'), {
            request_id: requestId.value,
            otp_code: otpCode.value,
        });

        emit('activated');
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message || 'Invalid OTP code. Please try again.';
    } finally {
        loading.value = false;
    }
};

const activateDevMode = async () => {
    loading.value = true;
    errorMessage.value = '';

    try {
        await axios.post(
            route('admin.modules.dev-activate', { module: props.module.id }),
            { key: devKey.value }
        );

        emit('activated');
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message || 'Invalid dev key. Use 0000 for testing.';
    } finally {
        loading.value = false;
    }
};
</script>
