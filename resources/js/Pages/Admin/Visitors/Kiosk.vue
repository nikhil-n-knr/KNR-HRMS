<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { CameraIcon, CheckBadgeIcon } from '@heroicons/vue/24/solid';

const form = useForm({
    name: '',
    phone: '',
    host_name: '',
    purpose: '',
    photo: null,
    nda_signed: false
});

const submit = () => {
    // In a real app, this would hit an API endpoint
    alert('Welcome! Your badge is printing...');
    form.reset();
};
</script>

<template>
    <Head title="Visitor Kiosk" />
    
    <div class="min-h-screen bg-gradient-to-br from-purple-900 to-indigo-900 flex items-center justify-center p-8">
        <div class="w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row h-[600px]">
            
            <!-- Left: Branding -->
            <div class="w-full md:w-1/3 bg-gray-50 flex flex-col items-center justify-center p-8 border-r border-gray-100">
                <div class="h-24 w-24 bg-purple-600 rounded-2xl flex items-center justify-center text-white text-4xl font-bold shadow-lg mb-6">
                    HR
                </div>
                <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Acme Corp</h1>
                <p class="text-gray-500 mt-2 text-center">Visitor Check-In</p>
                <div class="mt-auto text-xs text-gray-400">Powered by Agentic HRMS</div>
            </div>

            <!-- Right: Form -->
            <div class="w-full md:w-2/3 p-12 flex flex-col justify-center">
                <h2 class="text-4xl font-light text-gray-800 mb-8">Welcome, Guest.</h2>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <input v-model="form.name" type="text" placeholder="Full Name" class="w-full text-xl px-4 py-4 rounded-xl border-gray-300 focus:ring-4 focus:ring-purple-200 focus:border-purple-500" required>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <input v-model="form.phone" type="tel" placeholder="Phone Number" class="w-full text-xl px-4 py-4 rounded-xl border-gray-300 focus:ring-4 focus:ring-purple-200 focus:border-purple-500" required>
                        <input v-model="form.host_name" type="text" placeholder="Who are you meeting?" class="w-full text-xl px-4 py-4 rounded-xl border-gray-300 focus:ring-4 focus:ring-purple-200 focus:border-purple-500" required>
                    </div>

                    <!-- NDA Toggle -->
                    <label class="flex items-center p-4 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                        <input v-model="form.nda_signed" type="checkbox" class="w-6 h-6 text-purple-600 rounded focus:ring-purple-500" required>
                        <span class="ml-4 text-gray-600">I agree to the <span class="text-purple-600 font-bold underline">NDA & Safety Policy</span></span>
                    </label>

                    <button type="submit" class="w-full bg-black text-white text-xl font-bold py-5 rounded-xl shadow-lg hover:scale-[1.02] transition-transform flex items-center justify-center">
                        <CheckBadgeIcon class="w-8 h-8 mr-3 text-purple-400"/>
                        Check In & Print Badge
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
