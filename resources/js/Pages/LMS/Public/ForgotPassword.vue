<template>
    <div class="min-h-screen bg-[#F0F7F4] font-['Outfit'] flex items-center justify-center p-6 relative overflow-hidden">
        <!-- Abstract Background Orbs -->
        <div class="absolute top-[-10%] right-[-10%] w-[40%] h-[40%] bg-emerald-100/30 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[40%] h-[40%] bg-teal-100/30 rounded-full blur-[120px]"></div>

        <div class="w-full max-w-[500px] relative z-10">
            <!-- Back Button -->
            <Link :href="route('lms.store.login')" class="inline-flex items-center gap-2 text-emerald-600 font-bold mb-8 hover:gap-4 transition-all">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Login</span>
            </Link>

            <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[40px] shadow-2xl shadow-emerald-900/5 p-10 md:p-14">
                <div class="text-center mb-10">
                    <div class="w-20 h-20 bg-emerald-50 rounded-3xl flex items-center justify-center text-emerald-600 mx-auto mb-6 shadow-emerald-100 shadow-xl border border-emerald-100">
                        <i class="fas fa-key text-3xl"></i>
                    </div>
                    <h1 class="text-3xl font-black text-gray-900 mb-3">Forgot Password?</h1>
                    <p class="text-gray-500 font-medium">Enter your email and we'll send you instructions to reset your password.</p>
                </div>

                <div v-if="status" class="mb-8 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-emerald-700 text-sm font-bold text-center">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1">Account Email</label>
                        <div class="relative group mt-1">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400 transition-colors group-focus-within:text-emerald-500"></i>
                            </div>
                            <input 
                                v-model="form.email"
                                type="email" 
                                class="block w-full pl-11 pr-4 py-4 bg-gray-50/50 border border-gray-100 rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-medium" 
                                placeholder="your@email.com"
                                required
                            />
                            <div v-if="errors.email" class="mt-2 text-xs text-red-500 font-bold">{{ errors.email }}</div>
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-5 rounded-2xl shadow-xl shadow-emerald-200 transform active:scale-[0.98] transition-all uppercase tracking-widest text-xs flex items-center justify-center gap-3 disabled:opacity-50"
                    >
                        <span>Send Reset Link</span>
                        <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-paper-plane"></i>
                    </button>
                </form>

                <div class="mt-12 pt-8 border-t border-gray-50 text-center">
                    <p class="text-sm text-gray-400 font-medium">
                        Remembered it? 
                        <Link :href="route('lms.store.login')" class="text-emerald-600 font-black hover:underline underline-offset-4 decoration-emerald-200">Try signing in</Link>
                    </p>
                </div>
            </div>

            <!-- Footer Small -->
            <div class="mt-10 flex items-center justify-center gap-8 text-[10px] font-black text-emerald-900/20 uppercase tracking-widest">
                <span class="hover:text-emerald-600 transition-colors cursor-pointer">Terms</span>
                <span class="hover:text-emerald-600 transition-colors cursor-pointer">Privacy</span>
                <span class="hover:text-emerald-600 transition-colors cursor-pointer">Support</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: String,
    errors: Object
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('lms.store.password.email'));
};
</script>
