<template>
    <div class="min-h-screen bg-slate-50 flex flex-col p-8 relative overflow-hidden">
        <!-- Background Nature Accents -->
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-emerald-600/5 rounded-full blur-3xl"></div>

        <div class="flex-1 flex flex-col justify-center max-w-sm mx-auto w-full space-y-12 relative z-10">
            <!-- Brand -->
            <div class="text-center space-y-4">
                <div class="w-20 h-20 bg-slate-900 rounded-[2rem] flex items-center justify-center mx-auto shadow-2xl shadow-emerald-900/20 border-b-4 border-slate-950">
                    <i class="fas fa-leaf text-emerald-400 text-3xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight uppercase">LEAP OPSCORE</h1>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.4em] mt-1 italic">Operative Synchronization</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="space-y-2">
                    <label class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] px-2 italic">Neural Identifier</label>
                    <div class="relative group">
                        <i class="fas fa-at absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 text-xs transition-colors group-focus-within:text-emerald-500"></i>
                        <input 
                            v-model="form.email"
                            type="email" 
                            placeholder="OPERATIVE@KNR.COM"
                            class="w-full bg-white border border-slate-100 rounded-2xl py-5 pl-14 pr-6 text-sm font-black focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-500 outline-none transition-all shadow-sm uppercase placeholder:text-slate-200"
                        />
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex justify-between items-center px-2">
                         <label class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] italic">Security Protocol</label>
                         <Link :href="route('mobile.password.request')" class="text-[8px] font-black text-emerald-600 uppercase tracking-widest italic">Forgot Token?</Link>
                    </div>
                    <div class="relative group">
                        <i class="fas fa-key absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 text-xs transition-colors group-focus-within:text-emerald-500"></i>
                        <input 
                            v-model="form.password"
                            type="password" 
                            placeholder="••••••••"
                            class="w-full bg-white border border-slate-100 rounded-2xl py-5 pl-14 pr-6 text-sm font-black focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-500 outline-none transition-all shadow-sm uppercase placeholder:text-slate-200"
                        />
                    </div>
                </div>

                <div class="pt-4">
                    <button 
                        :disabled="form.processing"
                        class="w-full py-5 bg-slate-900 text-emerald-400 rounded-2xl font-black text-xs uppercase tracking-[0.3em] shadow-2xl active:scale-95 transition-all border-b-4 border-slate-950 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Authorizing...' : 'Initialize Session' }}
                    </button>
                    <p v-if="form.errors.email" class="text-[9px] text-rose-500 font-black uppercase text-center mt-4 tracking-widest italic">
                        {{ form.errors.email }}
                    </p>
                </div>
            </form>

            <!-- Bio-Sync Placeholder -->
            <div class="text-center">
                <button class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center justify-center gap-2 mx-auto active:scale-90 transition-transform">
                    <i class="fas fa-fingerprint text-emerald-600"></i>
                    Biometric Authentication Available
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-auto text-center py-8">
            <p class="text-[8px] font-black text-slate-300 uppercase tracking-[0.3em]">Quantum Encrypted Matrix v2.4</p>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: true,
    is_mobile: true // Flag for redirection logic
});

const submit = () => {
    form.post(route('mobile.login.post'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100;400;900&display=swap');

input {
    font-family: 'Outfit', sans-serif;
}
</style>
