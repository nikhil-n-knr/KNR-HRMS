<template>
  <div class="h-screen flex items-center justify-center bg-[#F0F7F4]">
    <div class="bg-white/60 backdrop-blur-xl w-full max-w-md p-8 rounded-2xl shadow-xl border border-white/60 relative overflow-hidden">
      
      <!-- Header -->
      <div class="text-center mb-8 relative z-10">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white mb-4 shadow-lg shadow-emerald-500/30">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Reset Password</h1>
        <p class="text-emerald-600/70 font-medium text-sm">Follow the steps to recover access</p>
      </div>

      <!-- Step Wizard -->
      <div class="relative z-10">
          <!-- Step 1: Email -->
          <form v-if="step === 1" @submit.prevent="sendOtp" class="space-y-6 animate-fade-in">
             <div class="text-sm text-gray-600 mb-4 bg-emerald-50/50 p-3 rounded-lg border border-emerald-100">
                Enter your registered email address. We'll send you an OTP code.
             </div>
             
             <BaseInput 
                v-model="form.email"
                label="Email Address"
                type="email"
                placeholder="you@company.com"
                required
             />

             <button 
                type="submit" 
                :disabled="loading"
                class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold py-3 rounded-xl shadow-lg shadow-emerald-500/30 transition-all hover:-translate-y-0.5 disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center gap-2"
             >
                <span v-if="loading">Sending...</span>
                <span v-else>Send OTP</span>
             </button>
          </form>

          <!-- Step 2: OTP -->
          <form v-if="step === 2" @submit.prevent="verifyOtp" class="space-y-6 animate-fade-in">
             <div class="text-sm text-gray-600 mb-4 bg-blue-50/50 p-3 rounded-lg border border-blue-100 flex items-start gap-2">
                <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>OTP sent to <strong>{{ form.email }}</strong>.</span>
             </div>

             <BaseInput 
                v-model="form.otp"
                label="Enter 6-Digit OTP"
                type="text"
                placeholder="123456"
                required
                class="text-center tracking-widest font-mono text-lg"
             />

             <button 
                type="submit" 
                :disabled="loading"
                class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-500/30 transition-all hover:-translate-y-0.5 disabled:opacity-70"
             >
                <span v-if="loading">Verifying...</span>
                <span v-else>Verify Code</span>
             </button>
             
             <button type="button" @click="step = 1" class="w-full text-xs text-gray-500 hover:text-gray-800 underline">Wrong email? Go back</button>
          </form>

          <!-- Step 3: New Password -->
          <form v-if="step === 3" @submit.prevent="resetPassword" class="space-y-6 animate-fade-in">
             <BaseInput 
                v-model="form.password"
                label="New Password"
                type="password"
                placeholder="••••••••"
                required
             />
             
             <BaseInput 
                v-model="form.password_confirmation"
                label="Confirm Password"
                type="password"
                placeholder="••••••••"
                required
             />

             <button 
                type="submit" 
                :disabled="loading"
                class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold py-3 rounded-xl shadow-lg shadow-emerald-500/30 transition-all hover:-translate-y-0.5 disabled:opacity-70"
             >
                 <span v-if="loading">Resetting...</span>
                 <span v-else>Set New Password</span>
             </button>
          </form>
          
          <!-- Success State -->
          <div v-if="step === 4" class="text-center space-y-4 animate-fade-in">
              <div class="bg-green-100 h-20 w-20 rounded-full flex items-center justify-center mx-auto text-green-600 mb-6">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-800">Password Reset!</h3>
              <p class="text-gray-500 text-sm">You can now login with your new password.</p>
              
              <button 
                @click="router.visit('/login')" 
                class="w-full mt-4 bg-gray-800 text-white font-bold py-3 rounded-xl hover:bg-gray-900 transition-colors"
              >
                  Return to Login
              </button>
          </div>
      </div>

      <!-- Footer -->
      <div class="mt-8 text-center" v-if="step < 4">
        <Link href="/login" class="text-sm font-medium text-emerald-700 hover:text-emerald-800 flex items-center justify-center gap-1 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Login
        </Link>
      </div>
      
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import BaseInput from '@/Components/BaseInput.vue';
import { useToastStore } from '@/stores/toast';

// router is already imported from inertia
const toast = useToastStore(); // Optional: if available, otherwise fallback to alert

const step = ref(1);
const loading = ref(false);

const form = reactive({
    email: '',
    otp: '',
    password: '',
    password_confirmation: ''
});

const sendOtp = async () => {
    loading.value = true;
    try {
        await axios.post('/forgot-password', { email: form.email });
        step.value = 2;
        // Use toast if set up
        if (toast) toast.success('OTP Sent! Check your email.');
    } catch (e) {
        if (toast) toast.error(e.response?.data?.message || 'Failed to send OTP');
        else alert(e.response?.data?.message || 'Failed to send OTP');
    } finally {
        loading.value = false;
    }
};

const verifyOtp = async () => {
    loading.value = true;
    try {
        await axios.post('/verify-otp', { email: form.email, otp: form.otp });
        step.value = 3;
        if (toast) toast.success('OTP Verified');
    } catch (e) {
        if (toast) toast.error(e.response?.data?.message || 'Invalid OTP');
        else alert('Invalid OTP');
    } finally {
        loading.value = false;
    }
};

const resetPassword = async () => {
    if (form.password !== form.password_confirmation) {
        if (toast) toast.error("Passwords do not match"); else alert("Passwords do not match");
        return;
    }

    loading.value = true;
    try {
        await axios.post('/reset-password', {
            email: form.email,
            otp: form.otp,
            password: form.password,
            password_confirmation: form.password_confirmation
        });
        step.value = 4;
        if (toast) toast.success('Password Successfully Reset');
    } catch (e) {
        if (toast) toast.error(e.response?.data?.message || 'Reset Failed');
        else alert(e.response?.data?.message || 'Reset Failed');
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
