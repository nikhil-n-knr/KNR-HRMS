<template>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="text-center">
                 <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Access Your Offer</h2>
                 <p class="mt-2 text-sm text-gray-600">
                     Secure verification required for <span class="font-medium text-indigo-600">{{ candidate_name }}</span>
                 </p>
            </div>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                
                <!-- Single Step: Email + PIN -->
                <form @submit.prevent="verifyOtp" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input id="email" v-model="form.email" type="email" autocomplete="email" required class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md" placeholder="Enter your registered email">
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Hint: {{ candidate_email_mask }}</p>
                    </div>

                    <div>
                         <label for="otp" class="block text-sm font-medium text-gray-700">Security PIN</label>
                         <div class="mt-1">
                             <input id="otp" v-model="form.otp" type="text" maxlength="6" required class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-lg text-center tracking-[0.5em] font-mono" placeholder="000000">
                         </div>
                         <p class="mt-2 text-xs text-gray-500">Enter the 6-digit PIN mentioned in your offer email.</p>
                    </div>

                    <div>
                        <button type="submit" :disabled="form.processing" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition-colors">
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Verify & Access Offer
                        </button>
                    </div>
                </form>
                
                 <!-- Notifications -->
                <div v-if="$page.props.flash.success" class="mt-4 p-3 bg-green-50 text-green-700 text-sm rounded border border-green-200">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.errors.otp" class="mt-4 p-3 bg-red-50 text-red-700 text-sm rounded border border-red-200">
                    {{ $page.props.errors.otp }}
                </div>
                <div v-if="$page.props.errors.email" class="mt-4 p-3 bg-red-50 text-red-700 text-sm rounded border border-red-200">
                    {{ $page.props.errors.email }}
                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    token: String,
    candidate_email_mask: String,
    candidate_name: String
});

const form = useForm({
    email: '',
    otp: ''
});

// Auto-Fill from Query Param
onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const encryptedEmail = params.get('e');
    if (encryptedEmail) {
        try {
            form.email = atob(encryptedEmail);
        } catch(e) { console.error('Failed to decode email', e); }
    }
});

const sendOtp = () => {
    form.post(route('portal.offer.otp.send', props.token), {
        onSuccess: () => {
            step.value = 'otp';
        },
        preserveScroll: true
    });
};

const verifyOtp = () => {
    form.post(route('portal.offer.otp.verify', props.token), {
        onSuccess: () => {
            // Redirect happens server side
        },
        onError: () => {
            form.otp = ''; 
        }
    });
};
</script>
