<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { HomeIcon, ArrowLeftIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    message: {
        type: String,
        default: 'An unexpected error occurred.'
    },
    status: {
        type: Number,
        default: 404
    }
});

const getErrorMeta = (status) => {
    const metas = {
        404: { title: 'Way Beyond the Horizon', subtitle: 'Page Not Found' },
        403: { title: 'Access Denied', subtitle: 'Restricted Territory' },
        500: { title: 'System Pulse Error', subtitle: 'Internal Server Issue' },
        default: { title: 'Navigation Alert', subtitle: 'Operational Glitch' }
    };
    return metas[status] || metas.default;
};

const meta = getErrorMeta(props.status);
</script>

<template>
    <Head :title="meta.subtitle" />
    
    <div class="min-h-screen bg-[#0f172a] flex items-center justify-center p-6 font-['Inter'] relative overflow-hidden">
        <!-- Background Orbs -->
        <div class="absolute top-0 -left-20 w-96 h-96 bg-emerald-500/10 rounded-full blur-[120px] animate-pulse"></div>
        <div class="absolute bottom-0 -right-20 w-96 h-96 bg-blue-500/10 rounded-full blur-[120px] animate-pulse" style="animation-delay: 2s"></div>

        <div class="max-w-md w-full relative z-10">
            <!-- Glassmorphic Card -->
            <div class="bg-white/5 border border-white/10 backdrop-blur-2xl rounded-[2rem] p-8 text-center shadow-2xl transition-all duration-500 hover:border-white/20">
                
                <div class="mb-8 relative inline-block">
                    <div class="w-24 h-24 bg-gradient-to-tr from-emerald-400 to-blue-500 rounded-2xl flex items-center justify-center rotate-12 shadow-lg shadow-emerald-500/20 group transition-transform duration-500 hover:rotate-0">
                        <ExclamationTriangleIcon class="w-12 h-12 text-white" />
                    </div>
                </div>

                <h1 class="text-4xl font-black text-white mb-2 tracking-tight">
                    {{ meta.title }}
                </h1>
                <p class="text-emerald-400 font-medium uppercase tracking-[0.2em] text-xs mb-6">
                    {{ meta.subtitle }}
                </p>

                <div class="bg-white/5 rounded-2xl p-6 mb-8 border border-white/5">
                    <p class="text-slate-300 leading-relaxed italic">
                        "{{ message }}"
                    </p>
                </div>

                <div class="space-y-4">
                    <Link 
                        href="/dashboard"
                        class="flex items-center justify-center w-full px-8 py-4 bg-white text-slate-900 font-bold rounded-2xl transition-all duration-200 hover:bg-emerald-400 hover:text-white hover:scale-[1.02] active:scale-95 shadow-xl shadow-white/5"
                    >
                        <HomeIcon class="w-5 h-5 mr-2" />
                        Return to Hub
                    </Link>

                    <button 
                        @click="window.history.back()"
                        class="flex items-center justify-center w-full px-8 py-4 bg-white/5 text-white font-semibold rounded-2xl border border-white/10 transition-all duration-200 hover:bg-white/10 hover:border-white/20 active:scale-95"
                    >
                        <ArrowLeftIcon class="w-5 h-5 mr-2" />
                        Navigate Back
                    </button>
                </div>

                <div class="mt-8 pt-6 border-t border-white/5">
                    <p class="text-slate-500 text-xs font-medium">
                        System Incident ID: <span class="font-mono text-slate-400 lowercase">{{ Math.random().toString(36).substr(2, 9) }}</span>
                    </p>
                </div>
            </div>
            
            <!-- Branding -->
            <div class="mt-8 text-center">
                <p class="text-slate-600 text-sm font-semibold tracking-wider flex items-center justify-center opacity-50">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></span>
                    LEAP OPERATIONAL SYSTEM
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes pulse {
    0%, 100% { opacity: 0.1; transform: scale(1); }
    50% { opacity: 0.2; transform: scale(1.1); }
}
</style>
