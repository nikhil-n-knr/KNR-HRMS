<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { RocketLaunchIcon, SparklesIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    module: {
        type: String,
        default: 'This Feature'
    }
});
</script>

<template>
    <Head title="Coming Soon" />
    
    <div class="min-h-screen bg-[#020617] flex items-center justify-center p-6 font-['Inter'] relative overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="absolute top-1/4 -right-10 w-80 h-80 bg-blue-600/20 rounded-full blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-1/4 -left-10 w-80 h-80 bg-purple-600/20 rounded-full blur-[100px] animate-pulse" style="animation-delay: 2s"></div>

        <div class="max-w-2xl w-full relative z-10 text-center">
            <!-- Glassmorphic Container -->
            <div class="bg-white/5 border border-white/10 backdrop-blur-3xl rounded-[3rem] p-12 shadow-[0_32px_64px_-16px_rgba(0,0,0,0.5)] transition-all duration-700 hover:border-white/20">
                
                <div class="inline-flex items-center space-x-2 px-4 py-2 bg-blue-500/10 border border-blue-500/20 rounded-full text-blue-400 text-xs font-bold uppercase tracking-widest mb-8">
                    <SparklesIcon class="w-4 h-4 animate-spin-slow" />
                    <span>In Development</span>
                </div>

                <div class="mb-10 relative">
                    <div class="w-32 h-32 bg-gradient-to-br from-blue-400 via-indigo-500 to-purple-600 rounded-[2.5rem] flex items-center justify-center mx-auto shadow-2xl rotate-3 transition-transform duration-500 hover:rotate-0">
                        <RocketLaunchIcon class="w-16 h-16 text-white" />
                    </div>
                </div>

                <h1 class="text-5xl md:text-6xl font-black text-white mb-6 tracking-tighter leading-tight">
                    {{ module }} is <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-purple-500">Arriving Soon</span>
                </h1>

                <p class="text-slate-400 text-lg md:text-xl leading-relaxed max-w-lg mx-auto mb-12 font-medium">
                    We're engineering a world-class experience for this module. Our team is currently polishing the final details.
                </p>

                <div class="flex flex-col md:flex-row items-center justify-center gap-4">
                    <Link 
                        href="/dashboard"
                        class="group relative px-8 py-4 bg-white text-slate-900 font-bold rounded-2xl transition-all duration-300 hover:bg-blue-400 hover:text-white hover:scale-105 active:scale-95 shadow-lg shadow-white/5 overflow-hidden"
                    >
                        <span class="relative z-10 flex items-center">
                            Return Home
                            <ArrowLeftIcon class="w-5 h-5 ml-2 transition-transform duration-300 group-hover:-translate-x-1 rotate-180" />
                        </span>
                    </Link>
                    
                    <button 
                        @click="window.history.back()"
                        class="px-8 py-4 bg-white/5 text-white font-bold rounded-2xl border border-white/10 transition-all duration-300 hover:bg-white/10 hover:border-white/20 active:scale-95"
                    >
                        Go Back
                    </button>
                </div>

                <div class="mt-16 grid grid-cols-3 gap-8 pt-10 border-t border-white/5 opacity-50">
                    <div>
                        <p class="text-2xl font-bold text-white mb-1">94%</p>
                        <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Stable</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-white mb-1">Elite</p>
                        <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Design</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-white mb-1">Fast</p>
                        <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Latency</p>
                    </div>
                </div>
            </div>
            
            <p class="mt-12 text-slate-700 text-xs font-bold uppercase tracking-[0.4em]">
                LEAP ENTERPRISE ECOSYSTEM
            </p>
        </div>
    </div>
</template>

<style scoped>
.animate-spin-slow {
    animation: spin 8s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
