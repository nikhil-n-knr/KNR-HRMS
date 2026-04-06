<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Top Navigation -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <h1 class="text-xl font-bold text-indigo-600 tracking-tight">Client Portal</h1>
                        </div>
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <Link :href="route('portal.dashboard')" :class="{'border-indigo-500 text-gray-900': route().current('portal.dashboard'), 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': !route().current('portal.dashboard')}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition duration-150 ease-in-out">
                                Dashboard
                            </Link>
                            <Link :href="route('portal.tickets.create')" :class="{'border-indigo-500 text-gray-900': route().current('portal.tickets.create'), 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': !route().current('portal.tickets.create')}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition duration-150 ease-in-out">
                                Report Issue
                            </Link>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="ml-3 relative">
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-700 font-medium">{{ $page.props.auth.user.name }}</span>
                                <button @click="logout" class="text-sm text-red-600 hover:text-red-800 font-medium">Logout</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>
        
        <!-- Recording Indicator (Visible only in dev/debug or to reassure client) -->
        <div class="fixed bottom-2 right-2 flex items-center gap-2 opacity-50 hover:opacity-100 transition-opacity">
            <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
            <span class="text-[10px] text-gray-400 uppercase tracking-wider font-bold">Session Recording Active</span>
        </div>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { onMounted, onBeforeUnmount } from 'vue';
import { record } from 'rrweb';

let stopFn = null;
const eventsMatrix = [];

onMounted(() => {
    // Start recording
    stopFn = record({
        emit(event) {
            // Keep last 1000 events or approx 1 minute buffer to avoid memory leaks if session is long
            if (eventsMatrix.length > 2000) {
                eventsMatrix.shift();
            }
            eventsMatrix.push(event);
            
            // Expose to window for TicketCreator to grab
            window.bugTrackerSession = eventsMatrix;
        },
        // Mask sensitive inputs
        maskAllInputs: true, 
        maskInputOptions: {
            password: true
        }
    });
    
    console.log('[Forensics] Session recording started.');
});

onBeforeUnmount(() => {
    if (stopFn) {
        stopFn();
        console.log('[Forensics] Session recording stopped.');
    }
});

const logout = () => {
    router.post(route('portal.logout'));
};
</script>
