<script setup>
import KioskLayout from '@/Layouts/KioskLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

// Screensaver Logic
const idle = ref(false);
let idleTimer = null;

const resetIdle = () => {
    idle.value = false;
    clearTimeout(idleTimer);
    idleTimer = setTimeout(() => {
        idle.value = true;
    }, 60000); // 1 minute idle
};

onMounted(() => {
    document.addEventListener('touchstart', resetIdle);
    document.addEventListener('click', resetIdle);
    resetIdle();
});

onUnmounted(() => {
    document.removeEventListener('touchstart', resetIdle);
    document.removeEventListener('click', resetIdle);
});
</script>

<template>
    <Head title="Welcome" />
    <KioskLayout>
        <!-- Screensaver Layer -->
        <div 
            v-if="idle" 
            class="fixed inset-0 z-50 bg-black flex items-center justify-center transition-opacity duration-1000"
            @click="resetIdle"
        >
            <div class="text-center animate-pulse">
                <img src="https://ui-avatars.com/api/?name=HRMS&background=000&color=fff&size=128" alt="Logo" class="h-24 mx-auto mb-8 opacity-50" />
                <h1 class="text-white text-4xl font-light tracking-[0.5em]">TOUCH TO START</h1>
            </div>
        </div>

        <!-- Main Standby Screen -->
        <div class="h-full flex flex-col relative overflow-hidden bg-gradient-to-br from-indigo-900 to-purple-900">
            <!-- Background Elements -->
            <div class="absolute inset-0 opacity-20">
                <div class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] rounded-full bg-purple-500 blur-3xl filter mix-blend-overlay"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-[600px] h-[600px] rounded-full bg-indigo-500 blur-3xl filter mix-blend-overlay"></div>
            </div>

            <!-- Header -->
            <div class="relative z-10 p-8 flex justify-between items-center text-white/80">
                <div class="flex items-center gap-3">
                     <div class="w-10 h-10 rounded-lg bg-white/10 backdrop-blur-md flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                     </div>
                     <span class="text-lg font-medium tracking-wide">{{ new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
                </div>
                <!-- Weather or Date -->
                <div class="text-lg font-light">
                    {{ new Date().toLocaleDateString(undefined, {weekday: 'long', month: 'long', day: 'numeric'}) }}
                </div>
            </div>

            <!-- Center Content -->
            <div class="flex-1 flex flex-col items-center justify-center relative z-10 text-center px-4">
                
                <h2 class="text-white text-opacity-80 text-xl font-medium tracking-widest uppercase mb-4">Welcome to</h2>
                <h1 class="text-6xl md:text-8xl font-black text-white tracking-tight mb-16 drop-shadow-2xl">
                    ACME <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-200 to-indigo-200">CORP</span>
                </h1>

                <Link :href="route('kiosk.check-in')" class="group relative">
                    <!-- Pulse Wrapper -->
                    <div class="absolute -inset-4 bg-white/20 rounded-full blur-xl animate-pulse group-hover:bg-white/30 transition-all"></div>
                    
                    <button class="relative w-24 h-24 md:w-32 md:h-32 bg-white rounded-full flex items-center justify-center shadow-[0_0_40px_rgba(255,255,255,0.3)] transition-transform hover:scale-110 active:scale-95">
                        <svg class="w-10 h-10 md:w-12 md:h-12 text-indigo-900 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                    
                    <div class="mt-6 text-white font-bold text-lg tracking-widest uppercase opacity-80 group-hover:opacity-100 transition-opacity">
                        Tap to Check In
                    </div>
                </Link>

            </div>

            <!-- Footer -->
            <div class="relative z-10 p-8 text-center">
                 <div class="inline-flex items-center gap-2 bg-black/20 backdrop-blur-md px-6 py-3 rounded-full border border-white/10">
                     <span class="text-white/60 text-sm">Have an Invite Code?</span>
                     <Link :href="route('kiosk.scan')" class="text-white font-bold text-sm tracking-wide hover:underline decoration-purple-400 decoration-2 underline-offset-4">
                        SCAN QR CODE
                     </Link>
                 </div>
            </div>
        </div>
    </KioskLayout>
</template>
