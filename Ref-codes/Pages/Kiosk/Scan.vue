<script setup>
import KioskLayout from '@/Layouts/KioskLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

// Mock Scanner Logic (Real implementation would use html5-qrcode)
const scanning = ref(true);
const cameraError = ref(null);
const scannerVideo = ref(null);
const form = useForm({
    code: ''
});

const submitScan = () => {
    form.post(route('kiosk.process-scan'), {
        onSuccess: () => {
            // Success audio?
        }
    });
};

// Simulation of a scan for Demo purposes (Clicking video)
const simulateScan = () => {
    // In real life, onDecode(code) -> form.code = code; submitScan();
    const demoCode = prompt("Simulate Scan (Enter Pass Code):", "ABCD1234");
    if (demoCode) {
        form.code = demoCode;
        submitScan();
    }
};

onMounted(() => {
    // navigator.mediaDevices.getUserMedia... setup
});
</script>

<template>
    <Head title="Scan QR" />
    <KioskLayout>
        <div class="h-full flex flex-col bg-black text-white p-8">
            <div class="flex justify-between items-center mb-8">
                 <h1 class="text-2xl font-bold">Position QR Code in Frame</h1>
                 <Link :href="route('kiosk.standby')" class="px-4 py-2 bg-white/10 rounded-full font-bold text-sm">CANCEL</Link>
            </div>

            <div class="flex-1 flex items-center justify-center relative">
                <!-- Camera Viewport -->
                <div class="relative w-full max-w-md aspect-square bg-gray-900 rounded-3xl overflow-hidden border-4 border-white/20 shadow-2xl" @click="simulateScan">
                     
                     <div class="absolute inset-0 flex items-center justify-center text-gray-500">
                        <span v-if="cameraError">{{ cameraError }}</span>
                        <span v-else>Camera Loading... (Tap to simulate)</span>
                     </div>
                     
                     <!-- Overlay -->
                     <div class="absolute inset-0 border-[40px] border-black/50"></div>
                     <div class="absolute inset-0 flex items-center justify-center">
                         <div class="w-64 h-64 border-2 border-white/50 rounded-lg relative">
                             <!-- Corners -->
                             <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-green-400 -mt-1 -ml-1"></div>
                             <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-green-400 -mt-1 -mr-1"></div>
                             <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-green-400 -mb-1 -ml-1"></div>
                             <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-green-400 -mb-1 -mr-1"></div>
                             
                             <!-- Scan Line -->
                             <div class="absolute top-0 left-0 right-0 h-1 bg-green-500 shadow-[0_0_10px_#22c55e] animate-scan"></div>
                         </div>
                     </div>
                </div>
            </div>

            <div class="text-center mt-8">
                <p class="text-white/50 text-sm">Or enter code manually:</p>
                <form @submit.prevent="submitScan" class="mt-4 flex justify-center gap-2">
                    <input v-model="form.code" type="text" placeholder="Pass Code" class="bg-gray-800 border-gray-700 text-white rounded-lg focus:ring-green-500 text-center uppercase font-mono tracking-widest w-48">
                    <button type="submit" class="p-2 bg-green-600 rounded-lg font-bold">GO</button>
                </form>
                <p v-if="form.errors.code" class="text-red-500 text-sm mt-2 font-bold bg-white/10 inline-block px-2 py-1 rounded">{{ form.errors.code }}</p>
            </div>
        </div>
    </KioskLayout>
</template>

<style>
@keyframes scan {
    0% { top: 0%; opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { top: 100%; opacity: 0; }
}
.animate-scan {
    animation: scan 2s linear infinite;
}
</style>
