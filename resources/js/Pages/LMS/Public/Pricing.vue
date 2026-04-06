<template>
    <div class="min-h-screen bg-white font-sans text-gray-900 selection:bg-emerald-100 selection:text-emerald-900">
        <!-- Premium Navigation (Storefront Style) -->
        <nav class="h-20 bg-white/80 backdrop-blur-2xl border-b border-gray-100 sticky top-0 z-50 px-6 md:px-12 flex items-center justify-between">
            <Link :href="route('lms.store.catalog')" class="flex items-center gap-3 group">
                <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-emerald-100 group-hover:rotate-12 transition-all">
                    <i class="fas fa-rocket"></i>
                </div>
                <div>
                    <span class="text-xl font-black tracking-tighter leading-none block italic uppercase">DEEP LMS</span>
                    <span class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.2em] leading-none">Intelligence Engine</span>
                </div>
            </Link>
            <div class="flex items-center gap-6">
                <Link :href="route('lms.store.login')" class="text-[10px] font-black text-gray-900 uppercase tracking-widest hover:text-emerald-600 transition-colors">Log In</Link>
                <Link :href="route('lms.store.register')" class="text-[10px] font-black text-gray-900 uppercase tracking-widest hover:text-emerald-600 transition-colors">Register</Link>
                <div class="h-4 w-px bg-gray-100"></div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest hidden md:block">SELECT YOUR PROTOCOL</p>
            </div>
        </nav>

        <!-- Minimal Header -->
        <div class="pt-24 pb-12 px-6 text-center space-y-8">
             <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 rounded-full border border-emerald-100 animate-pulse">
                <i class="fas fa-gem text-emerald-500 text-xs"></i>
                <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Pricing Strategy Alpha</span>
            </div>
            <h2 class="text-5xl md:text-7xl font-black tracking-tighter italic uppercase border-b-8 border-emerald-100 inline-block px-12 pb-6">
                CHOOSE YOUR PROTOCOL.
            </h2>
            <p class="text-xl font-medium text-gray-500 max-w-2xl mx-auto leading-relaxed font-sans">
                Whether you're just starting your journey or looking to dominate your entire field, we have a plan designed for your growth velocity.
            </p>
            
            <!-- Monthly/Yearly Toggle -->
            <div class="flex items-center justify-center gap-6 pt-12">
                <span class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400 transition-colors" :class="!isYearly ? 'text-emerald-600' : ''">Monthly</span>
                <button 
                    @click="isYearly = !isYearly"
                    class="w-20 h-10 bg-gray-900 rounded-full p-1.5 transition-all relative border border-white/10"
                >
                    <div 
                        class="w-7 h-7 bg-white rounded-full transition-all flex items-center justify-center shadow-2xl shadow-black/50"
                        :class="isYearly ? 'translate-x-10 text-emerald-600' : 'translate-x-0 text-gray-400'"
                    >
                         <i class="fas fa-check text-[10px]"></i>
                    </div>
                </button>
                <div class="flex items-center gap-4">
                    <span class="text-[11px] font-black uppercase tracking-[0.2em]" :class="isYearly ? 'text-emerald-600' : 'text-gray-900'">Yearly</span>
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-[9px] font-black uppercase tracking-widest leading-none border border-emerald-200">Save 35%</span>
                </div>
            </div>
        </div>

        <!-- Pricing Cards -->
        <main class="max-w-7xl mx-auto px-6 md:px-12 py-16 grid grid-cols-1 md:grid-cols-3 gap-12 font-sans">
            <!-- Free / Explorer -->
            <div class="bg-gray-50 p-10 rounded-[3.5rem] border border-gray-100 flex flex-col justify-between hover:bg-white hover:shadow-2xl hover:border-emerald-100 transition-all duration-700 group">
                <div class="space-y-12">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 group-hover:text-emerald-500 transition-colors">Protocol 01</p>
                            <h4 class="text-2xl font-black italic uppercase tracking-tighter">Explorer</h4>
                        </div>
                        <i class="fas fa-paper-plane text-gray-200 text-xl group-hover:text-emerald-200 transition-colors"></i>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-5xl font-black tracking-tighter tabular-nums italic uppercase">FREE</h3>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Lifelong access to 10+ core tracks</p>
                    </div>
                    <ul class="space-y-6">
                        <li v-for="f in ['Access to Public Catalog', 'Community Support Hub', 'Basic Video Playback', 'Standard Progress Tracking']" :key="f" class="flex items-start gap-4">
                            <i class="fas fa-check-circle text-emerald-400 text-sm mt-0.5 group-hover:scale-125 transition-transform"></i>
                            <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest italic leading-tight">{{ f }}</span>
                        </li>
                    </ul>
                </div>
                <button class="w-full mt-12 py-5 border-2 border-gray-200 text-gray-900 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white hover:border-emerald-600 transition-all shadow-sm">
                    Initiate Sequence
                </button>
            </div>

            <!-- Pro / Architect (Featured) -->
            <div class="bg-gray-900 p-12 rounded-[4rem] flex flex-col justify-between text-white relative shadow-2xl scale-105 z-10 overflow-hidden transform hover:-translate-y-2 transition-transform duration-700 ring-4 ring-emerald-500/10">
                <div class="space-y-12 relative z-10">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-2 italic">Protocol 02 (Recommended)</p>
                            <h4 class="text-3xl font-black italic uppercase tracking-tighter">Architect</h4>
                        </div>
                        <i class="fas fa-crown text-emerald-400 text-2xl animate-pulse"></i>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-6xl font-black tracking-tighter tabular-nums italic">
                            {{ isYearly ? '₹2,499' : '₹499' }}
                            <span class="text-xs text-white/40 uppercase tracking-widest align-middle ml-2 font-black">/ {{ isYearly ? 'Yr' : 'Mo' }}</span>
                        </h3>
                        <p class="text-[10px] font-black text-emerald-400/80 uppercase tracking-widest italic">Complete deep library unlocked</p>
                    </div>
                    <ul class="space-y-6">
                        <li v-for="f in ['All Explorer Features', 'Verified Certificates', 'Exclusive Q&A Support', 'Quiz & Assessment Hub', 'Deep Offline Viewing', 'Early Access to Alphas']" :key="f" class="flex items-start gap-4 text-slate-100">
                            <i class="fas fa-star text-emerald-400 text-sm mt-0.5"></i>
                            <span class="text-[10px] font-black text-white/80 uppercase tracking-widest italic leading-tight">{{ f }}</span>
                        </li>
                    </ul>
                </div>
                <button class="w-full mt-12 py-5 bg-emerald-600 text-white rounded-[2rem] text-[10px] font-black uppercase tracking-widest hover:bg-emerald-700 hover:scale-[1.02] transition-all shadow-2xl shadow-emerald-500/20 relative z-10">
                    Subscribe to Master Hub
                </button>
                <!-- Neural BG Glow -->
                <div class="absolute -right-20 -top-20 w-80 h-80 bg-emerald-500/10 rounded-full blur-[100px]"></div>
                <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-teal-500/10 rounded-full blur-[100px]"></div>
            </div>

            <!-- Elite / Institution -->
            <div class="bg-gray-50 p-10 rounded-[3.5rem] border border-gray-100 flex flex-col justify-between hover:bg-white hover:shadow-2xl hover:border-emerald-100 transition-all duration-700 group">
                <div class="space-y-12">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 group-hover:text-emerald-500 transition-colors">Protocol 03</p>
                            <h4 class="text-2xl font-black italic uppercase tracking-tighter">Elite Hub</h4>
                        </div>
                        <i class="fas fa-building-columns text-gray-200 text-xl group-hover:text-emerald-200 transition-colors"></i>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-5xl font-black tracking-tighter tabular-nums italic uppercase">CUSTOM</h3>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Built for high-stakes organizations</p>
                    </div>
                    <ul class="space-y-6">
                        <li v-for="f in ['Unlimited Users', 'White-label LMS Instance', 'Custom Content Engine', 'ERP & HRIS Deep Sync', 'Multi-tenant Hierarchies', 'Strategic Deployment Support']" :key="f" class="flex items-start gap-4">
                            <i class="fas fa-chess-knight text-teal-500 text-sm mt-0.5"></i>
                            <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest italic leading-tight">{{ f }}</span>
                        </li>
                    </ul>
                </div>
                <button class="w-full mt-12 py-5 border-2 border-gray-900 text-gray-900 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-900 hover:text-white transition-all shadow-sm">
                    Contact Deployment Lab
                </button>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const isYearly = ref(true);
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');

.font-sans {
    font-family: 'Outfit', sans-serif;
}
</style>
