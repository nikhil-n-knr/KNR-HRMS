<template>
    <div class="min-h-screen bg-[#F0F7F4] font-['Outfit'] flex items-center justify-center p-6 relative overflow-hidden">
        <!-- Abstract Background Orbs -->
        <div class="absolute top-[-10%] right-[-10%] w-[40%] h-[40%] bg-emerald-100/30 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[40%] h-[40%] bg-teal-100/30 rounded-full blur-[120px]"></div>

        <div class="w-full max-w-5xl bg-white/70 backdrop-blur-xl border border-white rounded-[40px] shadow-2xl shadow-emerald-900/5 overflow-hidden flex flex-col md:flex-row min-h-[700px]">
            
            <!-- Left Side: Branding & Info -->
            <div class="w-full md:w-1/2 bg-gradient-to-br from-emerald-600 to-teal-700 p-12 text-white flex flex-col justify-between relative overflow-hidden">
                <!-- Inner Glow -->
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_30%,rgba(255,255,255,0.1),transparent)]"></div>
                
                <div class="relative z-10">
                    <Link :href="route('lms.store.catalog')" class="flex items-center gap-3 mb-16">
                        <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/30">
                            <i class="fas fa-brain text-2xl"></i>
                        </div>
                        <span class="text-2xl font-black tracking-tighter uppercase">Deep LMS</span>
                    </Link>
                    
                    <h1 class="text-5xl font-black leading-[1.1] mb-6">
                        Start your <br/>
                        <span class="text-emerald-200">learning</span> <br/>
                        odyssey.
                    </h1>
                    <p class="text-emerald-50/70 text-lg max-w-md leading-relaxed">
                        Join thousands of students and corporate leaders scaling their skills with our advanced curriculum engine.
                    </p>
                </div>

                <div class="relative z-10">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="flex -space-x-3">
                            <div v-for="i in 4" :key="i" class="w-10 h-10 rounded-full border-2 border-emerald-600 bg-emerald-100 flex items-center justify-center text-[10px] font-bold text-emerald-700">
                                <span v-if="i<4">U{{i}}</span>
                                <span v-else>+2k</span>
                            </div>
                        </div>
                        <span class="text-sm font-medium text-emerald-100/80 italic">Verified professionals already enrolled</span>
                    </div>

                    <div class="pt-8 border-t border-white/10 flex items-center justify-between text-xs font-bold uppercase tracking-widest text-emerald-100/40">
                        <span>© 2026 Deep LMS</span>
                        <span>v.4.0.2</span>
                    </div>
                </div>

                <!-- Floating Elements -->
                <div class="absolute top-[20%] right-[-5%] w-32 h-32 border-4 border-white/5 rounded-full"></div>
                <div class="absolute bottom-[10%] right-[10%] w-24 h-24 bg-white/5 rounded-[30px] rotate-12"></div>
            </div>

            <!-- Right Side: Register Form -->
            <div class="w-full md:w-1/2 p-12 md:p-20 flex flex-col justify-center bg-white/40">
                <div class="max-w-md mx-auto w-full">
                    <div class="mb-10">
                        <h2 class="text-3xl font-black text-gray-900 mb-2">Create Account</h2>
                        <p class="text-gray-500 font-medium">Already have one? <Link :href="route('lms.store.login')" class="text-emerald-600 hover:text-emerald-700 font-bold transition-colors">Sign in here</Link></p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Full Name -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1">Full Name</label>
                            <div class="relative group mt-1">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400 transition-colors group-focus-within:text-emerald-500"></i>
                                </div>
                                <input 
                                    v-model="form.name"
                                    type="text" 
                                    class="block w-full pl-11 pr-4 py-4 bg-gray-50/50 border border-gray-100 rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-medium" 
                                    placeholder="Enter your name"
                                    required
                                />
                                <div v-if="errors.name" class="mt-2 text-xs text-red-500 font-bold">{{ errors.name }}</div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1">Work Email</label>
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

                        <!-- Password -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1">Secure Password</label>
                            <div class="relative group mt-1">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400 transition-colors group-focus-within:text-emerald-500"></i>
                                </div>
                                <input 
                                    v-model="form.password"
                                    type="password" 
                                    class="block w-full pl-11 pr-4 py-4 bg-gray-50/50 border border-gray-100 rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-medium" 
                                    placeholder="••••••••"
                                    required
                                />
                                <div v-if="errors.password" class="mt-2 text-xs text-red-500 font-bold">{{ errors.password }}</div>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1">Confirm Password</label>
                            <div class="relative group mt-1">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-check-double text-gray-400 transition-colors group-focus-within:text-emerald-500"></i>
                                </div>
                                <input 
                                    v-model="form.password_confirmation"
                                    type="password" 
                                    class="block w-full pl-11 pr-4 py-4 bg-gray-50/50 border border-gray-100 rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-medium" 
                                    placeholder="••••••••"
                                    required
                                />
                            </div>
                        </div>

                        <div class="flex items-center gap-3 py-2">
                            <input type="checkbox" id="terms" required class="w-5 h-5 rounded-lg border-gray-200 text-emerald-600 focus:ring-emerald-500 transition-all cursor-pointer">
                            <label for="terms" class="text-sm font-medium text-gray-500 select-none cursor-pointer">
                                I agree to the <span class="text-emerald-600 font-bold underline underline-offset-4 decoration-emerald-200">Terms of Service</span>
                            </label>
                        </div>

                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 rounded-2xl shadow-xl shadow-emerald-200 transform active:scale-[0.98] transition-all uppercase tracking-widest text-xs flex items-center justify-center gap-3 disabled:opacity-50"
                        >
                            <span>Create My Account</span>
                            <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-arrow-right"></i>
                        </button>

                        <!-- Social Login Divider -->
                        <div class="relative my-8">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-100"></div>
                            </div>
                            <div class="relative flex justify-center text-xs uppercase font-black tracking-widest">
                                <span class="bg-white/40 px-4 text-gray-400 italic">or register with</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <button type="button" class="w-full bg-white border border-gray-100 py-3 rounded-xl flex items-center justify-center gap-2 text-sm font-bold text-gray-600 hover:bg-gray-50 transition-colors">
                                <i class="fab fa-google text-red-500"></i> Google
                            </button>
                            <button type="button" class="w-full bg-white border border-gray-100 py-3 rounded-xl flex items-center justify-center gap-2 text-sm font-bold text-gray-600 hover:bg-gray-50 transition-colors">
                                <i class="fab fa-linkedin text-blue-600"></i> LinkedIn
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';

defineProps({
    errors: Object
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('lms.store.register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>
