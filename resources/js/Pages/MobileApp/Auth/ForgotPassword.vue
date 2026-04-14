<template>
    <div class="min-h-screen bg-slate-50 flex flex-col p-8 relative overflow-hidden">
        <!-- Background Nature Accents -->
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl"></div>
        
        <div class="flex-1 flex flex-col justify-center max-w-sm mx-auto w-full space-y-12 relative z-10">
            <!-- Header -->
            <div class="text-center space-y-4">
                <Link :href="route('login')" class="w-12 h-12 bg-white rounded-xl flex items-center justify-center mx-auto shadow-sm border border-slate-100 text-slate-400 active:scale-90 transition-transform">
                    <i class="fas fa-chevron-left text-xs"></i>
                </Link>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Token Recovery</h1>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1 italic leading-relaxed px-4">
                        Initialize secure handshake to reset your operative credentials
                    </p>
                </div>
            </div>

            <!-- Form -->
            <div v-if="status" class="nature-card p-6 bg-emerald-50 border-emerald-100 text-center animate-in fade-in zoom-in">
                <i class="fas fa-satellite-dish text-emerald-600 mb-3 text-lg animate-pulse"></i>
                <p class="text-[10px] font-black text-emerald-700 uppercase tracking-widest leading-relaxed">
                   {{ status }}
                </p>
            </div>

            <form v-else @submit.prevent="submit" class="space-y-6">
                <div class="space-y-2">
                    <label class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] px-2 italic">Recovery Address</label>
                    <div class="relative group">
                        <i class="fas fa-paper-plane absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 text-xs transition-colors group-focus-within:text-emerald-500"></i>
                        <input 
                            v-model="form.email"
                            type="email" 
                            placeholder="OPERATIVE@KNR.COM"
                            required
                            class="w-full bg-white border border-slate-100 rounded-2xl py-5 pl-14 pr-6 text-sm font-black focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-500 outline-none transition-all shadow-sm uppercase placeholder:text-slate-200"
                        />
                    </div>
                    <p v-if="form.errors.email" class="text-[8px] text-rose-500 font-black uppercase mt-2 px-2 italic tracking-widest">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="pt-4">
                    <button 
                        :disabled="form.processing"
                        class="w-full py-5 bg-slate-900 text-emerald-400 rounded-2xl font-black text-xs uppercase tracking-[0.3em] shadow-2xl active:scale-95 transition-all border-b-4 border-slate-950 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Transmitting...' : 'Send Recovery Token' }}
                    </button>
                </div>
            </form>

            <!-- Support -->
            <div class="text-center">
                <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest leading-relaxed">
                    Locked out of the Matrix? <br/>
                    <span class="text-emerald-600">Contact Command Center</span>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-auto text-center py-8">
            <p class="text-[8px] font-black text-slate-300 uppercase tracking-[0.3em]">Operational Security Level 9</p>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>
