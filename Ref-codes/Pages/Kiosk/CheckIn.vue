<script setup>
import KioskLayout from '@/Layouts/KioskLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    purposes: Array
});

const getIcon = (type) => {
    switch (type) {
        case 'interview': return 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z';
        case 'client': return 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z';
        case 'delivery': return 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4';
        case 'contractor': return 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z';
        default: return 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z';
    }
};

const getColors = (type) => {
    switch(type) {
         case 'interview': return 'bg-cyan-50 border-cyan-200 text-cyan-700 hover:border-cyan-500';
         case 'client': return 'bg-purple-50 border-purple-200 text-purple-700 hover:border-purple-500';
         case 'delivery': return 'bg-orange-50 border-orange-200 text-orange-700 hover:border-orange-500';
         case 'contractor': return 'bg-amber-50 border-amber-200 text-amber-700 hover:border-amber-500';
         default: return 'bg-gray-50 border-gray-200 text-gray-700 hover:border-indigo-500';
    }
}
</script>

<template>
    <Head title="Check In" />
    <KioskLayout>
        <div class="h-full flex flex-col p-8 md:p-12">
            
            <div class="flex-shrink-0 flex justify-between items-center mb-10">
                <div>
                     <h1 class="text-4xl font-bold text-gray-900">What brings you here?</h1>
                     <p class="text-lg text-gray-500 mt-2">Tap the option that best describes your visit.</p>
                </div>
                <Link :href="route('kiosk.standby')" class="text-gray-400 font-bold tracking-wider text-sm hover:text-red-500">CANCEL</Link>
            </div>

            <div class="flex-1 grid grid-cols-2 lg:grid-cols-3 gap-8 overflow-y-auto pb-8">
                <Link 
                    v-for="purpose in purposes" 
                    :key="purpose.id"
                    :href="route('kiosk.walk-in', { purpose: purpose.id })" 
                    class="group relative flex flex-col items-center justify-center p-8 border-2 rounded-3xl transition-all duration-300 transform hover:-translate-y-2 hover:shadow-xl cursor-pointer bg-white"
                    :class="getColors(purpose.name.toLowerCase().includes('interview') ? 'interview' : (purpose.name.toLowerCase().includes('delivery') ? 'delivery' : 'default'))"
                >
                     <!-- Simulating Link but normally would open internal form step. For now we link to the Web Form? 
                          Wait, the Kiosk should likely have its own flow or reuse Guest Form logic but tailored.
                          For MVP, directing to 'guest.pre-checkin' might require a 'pass' which we don't have yet.
                          We need a flow: Select Purpose -> Enter Details -> Print.
                          So, we should PROBABLY just create a new 'CheckInStep2.vue' or handle steps here.
                          OR, we can treat this as starting a 'Walk-in' visit.
                          Let's link to a new Kiosk Flow Step 2.
                     -->
                    <div class="w-20 h-20 rounded-full bg-white shadow-sm flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIcon(purpose.name.toLowerCase())" />
                        </svg>
                    </div>
                    <span class="text-2xl font-bold">{{ purpose.name }}</span>
                </Link>

                <!-- Fallback Scan Option if missed on standby -->
                <Link :href="route('kiosk.scan')" class="flex flex-col items-center justify-center p-8 border-2 border-dashed border-gray-300 rounded-3xl text-gray-400 hover:border-gray-500 hover:text-gray-600 transition-colors">
                     <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                     <span class="font-bold">Have a QR Code?</span>
                </Link>
            </div>
            
        </div>
    </KioskLayout>
</template>
