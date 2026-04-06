<template>
  <div class="h-screen flex items-center justify-center bg-[#F0F7F4]">
    <div class="bg-white/60 backdrop-blur-xl w-full max-w-md p-8 rounded-2xl shadow-xl border border-white/60">
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white mb-4 shadow-lg shadow-emerald-500/30">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Welcome Back</h1>
        <p class="text-emerald-600/70 font-medium">Sign in to Nexus HR</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-6">
        <BaseInput 
            v-model="form.email"
            class="mb-4"
            label="Email Address"
            type="email"
            placeholder="admin@test.com"
            required
        />

        <BaseInput 
            v-model="form.password"
            class="mb-2"
            label="Password"
            type="password"
            placeholder="••••••••"
            required
        />
        
        <div class="flex justify-end mb-6">
            <Link href="/forgot-password" class="text-xs text-emerald-600 hover:text-emerald-800 font-medium transition-colors">
                Forgot Password?
            </Link>
        </div>

        <div v-if="error" class="p-3 bg-red-50 border border-red-100 text-red-600 text-sm rounded-xl flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ error }}
        </div>

        <button 
          type="submit" 
          :disabled="loading"
          class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 hover:-translate-y-0.5 active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2"
        >
          <svg v-if="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span v-else>Sign In</span>
        </button>
      </form>
      
      <div class="mt-8 text-center text-xs text-gray-400">
        <p>Demo Credentials: admin@test.com / secret</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3'; // Use Inertia
import { useAuthStore } from '@/stores/auth';
import BaseInput from '@/Components/BaseInput.vue';

// const router = useRouter(); // Removed
const authStore = useAuthStore();

const form = reactive({
    email: 'admin@test.com',
    password: 'secret'
});

const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
    loading.value = true;
    error.value = '';
    
    try {
        await authStore.login(form);
        // Route to the universally managed dashboard endpoint
        router.visit('/dashboard'); 
    } catch (e) {
        error.value = e.message || 'Login failed';
    } finally {
        loading.value = false;
    }
};
</script>
