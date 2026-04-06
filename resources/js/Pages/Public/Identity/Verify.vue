<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    status: String, // Granted, Denied, Expired, Revoked, NotFound
    card: Object,
    message: String,
    timestamp: String
});
</script>

<template>
    <Head title="Security Verification" />

    <div class="min-h-screen flex flex-col items-center justify-center p-4 bg-gray-900 font-sans">
        
        <!-- Result Card -->
        <div class="w-full max-w-md bg-white rounded-3xl overflow-hidden shadow-2xl relative">
            
            <!-- Header Status -->
            <div class="h-32 flex items-center justify-center relative overflow-hidden"
                :class="{
                    'bg-green-500': status === 'Granted',
                    'bg-red-600': status === 'Revoked' || status === 'Expired' || status === 'NotFound',
                }"
            >
                <div class="absolute inset-0 opacity-20">
                    <div class="w-full h-full bg-[url('https://www.transparenttextures.com/patterns/diagonal-stripes.png')]"></div>
                </div>
                
                <div class="text-center text-white z-10">
                    <div v-if="status === 'Granted'" class="text-4xl font-black tracking-widest uppercase">Access Granted</div>
                    <div v-else class="text-3xl font-black tracking-widest uppercase">Access Denied</div>
                    <p class="text-white/80 font-mono text-sm mt-1">{{ timestamp }}</p>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8 text-center" v-if="card">
                <div class="w-32 h-32 mx-auto rounded-full bg-gray-200 border-8 border-white shadow-xl -mt-20 overflow-hidden relative z-20">
                    <img v-if="card.user?.employee?.avatar" :src="`/storage/${card.user.employee.avatar}`" class="w-full h-full object-cover shadow-inner">
                    <img v-else :src="`https://ui-avatars.com/api/?name=${card.details.name}&background=random&size=256`" class="w-full h-full object-cover">
                </div>

                <h2 class="mt-6 text-3xl font-bold text-gray-900">{{ card.details.name }}</h2>
                <p class="text-lg text-gray-500 font-medium">{{ card.details.role }}</p>
                
                <div class="mt-8 space-y-4">
                    <div class="flex justify-between p-4 bg-gray-50 rounded-xl">
                        <span class="text-gray-500 font-medium">Card Type</span>
                        <span class="font-bold text-gray-900 uppercase">{{ card.type }}</span>
                    </div>
                    <div class="flex justify-between p-4 bg-gray-50 rounded-xl">
                        <span class="text-gray-500 font-medium">Valid Until</span>
                        <span class="font-bold text-gray-900">{{ card.valid_until || 'No Expiry' }}</span>
                    </div>
                     <div v-if="card.type === 'Vendor'" class="flex justify-between p-4 bg-orange-50 rounded-xl border border-orange-100">
                        <span class="text-orange-600 font-medium">Agency</span>
                        <span class="font-bold text-orange-800">{{ card.details.vendor_name || 'N/A' }}</span>
                    </div>
                </div>

                <div v-if="status !== 'Granted'" class="mt-8 p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 font-bold text-sm">
                    ⚠️ STOP: {{ status === 'Revoked' ? 'This card has been reported LOST/STOLEN.' : 'This card has EXPIRED.' }}
                </div>
            </div>

            <!-- Not Found State -->
            <div class="p-12 text-center" v-else>
                 <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto text-red-600 mb-6">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                     </svg>
                 </div>
                 <h2 class="text-xl font-bold text-gray-900">Invalid QR Code</h2>
                 <p class="text-gray-500 mt-2">System could not find any record for this code.</p>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 p-4 text-center border-t border-gray-100">
                <p class="text-xs text-gray-400">Security Verification System v2.0</p>
            </div>
        </div>
    </div>
</template>
